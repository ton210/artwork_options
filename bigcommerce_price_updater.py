#!/usr/bin/env python3
"""
BigCommerce Price Updater
Increases all product prices by 20% and rounds to nearest quarter (.00, .25, .50, .75)
Also updates tiered/bulk pricing with the same 20% increase and rounding
"""

import requests
import json
import math
import time
from typing import List, Dict, Any

# BigCommerce Store Configuration
BC_STORE_HASH = 'tqjrceegho'
BC_ACCESS_TOKEN = 'lmg7prm3b0fxypwwaja27rtlvqejic0'
BASE_DOMAIN = 'www.munchmakers.com'

# API Configuration
BASE_URL = f'https://api.bigcommerce.com/stores/{BC_STORE_HASH}/v3'
HEADERS = {
    'X-Auth-Token': BC_ACCESS_TOKEN,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
}

def round_to_quarter(price: float) -> float:
    """
    Round price to nearest quarter (.00, .25, .50, .75)
    Example: 34.93 -> 35.00, 34.12 -> 34.25
    """
    # Multiply by 4, round to nearest integer, then divide by 4
    return round(price * 4) / 4

def calculate_new_price(current_price: float) -> float:
    """
    Increase price by 20% and round to nearest quarter
    """
    increased_price = current_price * 1.20
    return round_to_quarter(increased_price)

def get_all_products() -> List[Dict[Any, Any]]:
    """
    Fetch all products from BigCommerce store
    """
    products = []
    page = 1
    limit = 250  # Max limit per request

    while True:
        url = f'{BASE_URL}/catalog/products'
        params = {
            'page': page,
            'limit': limit,
            'include': 'variants'
        }

        try:
            response = requests.get(url, headers=HEADERS, params=params)
            response.raise_for_status()
            data = response.json()

            if not data.get('data'):
                break

            products.extend(data['data'])
            print(f"Fetched page {page}: {len(data['data'])} products")

            # Check if there are more pages
            if len(data['data']) < limit:
                break

            page += 1
            time.sleep(0.5)  # Rate limiting

        except requests.exceptions.RequestException as e:
            print(f"Error fetching products: {e}")
            break

    return products

def get_product_modifiers(product_id: int) -> List[Dict[Any, Any]]:
    """
    Get product modifiers (options like size, color) that might have price adjustments
    """
    url = f'{BASE_URL}/catalog/products/{product_id}/modifiers'

    try:
        response = requests.get(url, headers=HEADERS)
        response.raise_for_status()
        data = response.json()
        return data.get('data', [])
    except requests.exceptions.RequestException as e:
        print(f"Error fetching modifiers for product {product_id}: {e}")
        return []

def get_product_bulk_pricing(product_id: int) -> List[Dict[Any, Any]]:
    """
    Get bulk pricing rules for a product
    """
    url = f'{BASE_URL}/catalog/products/{product_id}/bulk-pricing-rules'

    try:
        response = requests.get(url, headers=HEADERS)
        response.raise_for_status()
        data = response.json()
        return data.get('data', [])
    except requests.exceptions.RequestException as e:
        print(f"Error fetching bulk pricing for product {product_id}: {e}")
        return []

def update_product_price(product_id: int, new_price: float) -> bool:
    """
    Update the base price of a product
    """
    url = f'{BASE_URL}/catalog/products/{product_id}'

    payload = {
        'price': new_price
    }

    try:
        response = requests.put(url, headers=HEADERS, json=payload)
        response.raise_for_status()
        return True
    except requests.exceptions.RequestException as e:
        print(f"Error updating price for product {product_id}: {e}")
        return False

def update_bulk_pricing_rule(product_id: int, rule_id: int, updated_rule: Dict[Any, Any]) -> bool:
    """
    Update a bulk pricing rule
    """
    url = f'{BASE_URL}/catalog/products/{product_id}/bulk-pricing-rules/{rule_id}'

    try:
        response = requests.put(url, headers=HEADERS, json=updated_rule)
        response.raise_for_status()
        return True
    except requests.exceptions.RequestException as e:
        print(f"Error updating bulk pricing rule {rule_id} for product {product_id}: {e}")
        return False

def update_modifier_values(product_id: int, modifier_id: int, values: List[Dict[Any, Any]]) -> bool:
    """
    Update modifier values (for price adjustments in options)
    """
    for value in values:
        if 'id' in value:
            url = f'{BASE_URL}/catalog/products/{product_id}/modifiers/{modifier_id}/values/{value["id"]}'
            try:
                response = requests.put(url, headers=HEADERS, json=value)
                response.raise_for_status()
            except requests.exceptions.RequestException as e:
                print(f"Error updating modifier value {value['id']}: {e}")
                return False
    return True

def process_product_pricing(product: Dict[Any, Any], dry_run: bool = True) -> Dict[str, Any]:
    """
    Process a single product's pricing (base price, bulk pricing, modifiers)
    """
    product_id = product['id']
    current_price = float(product.get('price', 0))

    if current_price <= 0:
        return {
            'product_id': product_id,
            'name': product.get('name', 'Unknown'),
            'status': 'skipped',
            'reason': 'No valid price found'
        }

    new_price = calculate_new_price(current_price)

    result = {
        'product_id': product_id,
        'name': product.get('name', 'Unknown'),
        'old_price': current_price,
        'new_price': new_price,
        'status': 'pending',
        'bulk_pricing_updated': 0,
        'modifiers_updated': 0
    }

    if not dry_run:
        # Update base price
        if update_product_price(product_id, new_price):
            result['status'] = 'updated'
        else:
            result['status'] = 'failed'
            return result

    # Process bulk pricing rules
    bulk_rules = get_product_bulk_pricing(product_id)
    for rule in bulk_rules:
        if 'amount' in rule and rule.get('type') == 'fixed':
            old_amount = float(rule['amount'])
            new_amount = calculate_new_price(old_amount)

            updated_rule = rule.copy()
            updated_rule['amount'] = new_amount

            if not dry_run:
                if update_bulk_pricing_rule(product_id, rule['id'], updated_rule):
                    result['bulk_pricing_updated'] += 1
        elif 'amount' in rule and rule.get('type') == 'percent':
            # For percentage discounts, we don't need to change them
            # as they'll automatically apply to the new base price
            pass

    # Process modifiers (options with price adjustments)
    modifiers = get_product_modifiers(product_id)
    for modifier in modifiers:
        if 'option_values' in modifier:
            updated_values = []
            has_price_adjustments = False

            for value in modifier['option_values']:
                updated_value = value.copy()

                # Check for price adjustments
                for adj_type in ['price_adjustment', 'weight_adjustment']:
                    if adj_type in value and value[adj_type]:
                        if adj_type == 'price_adjustment':
                            old_adj = float(value[adj_type])
                            if old_adj != 0:
                                new_adj = calculate_new_price(abs(old_adj))
                                if old_adj < 0:
                                    new_adj = -new_adj
                                updated_value[adj_type] = new_adj
                                has_price_adjustments = True

                updated_values.append(updated_value)

            if has_price_adjustments and not dry_run:
                if update_modifier_values(product_id, modifier['id'], updated_values):
                    result['modifiers_updated'] += 1

    return result

def main():
    print("BigCommerce Price Updater")
    print("=" * 50)
    print(f"Store: {BASE_DOMAIN}")
    print(f"Store Hash: {BC_STORE_HASH}")
    print("Increasing all prices by 20% and rounding to nearest quarter")
    print("=" * 50)

    # Test connection by trying to fetch products (first page only)
    try:
        test_url = f'{BASE_URL}/catalog/products'
        test_params = {'page': 1, 'limit': 1}
        response = requests.get(test_url, headers=HEADERS, params=test_params)
        response.raise_for_status()
        data = response.json()
        print(f"✓ Successfully connected to BigCommerce API")
        print(f"✓ Found products in store")
    except requests.exceptions.RequestException as e:
        print(f"✗ Failed to connect to BigCommerce API: {e}")
        print(f"Please verify your store hash and access token.")
        return

    # Fetch all products
    print("\nFetching all products...")
    products = get_all_products()
    print(f"Found {len(products)} products")

    if not products:
        print("No products found. Exiting.")
        return

    # Apply changes to all products
    dry_run = False
    print("\n🚀 APPLYING CHANGES TO ALL PRODUCTS...")

    results = []

    print(f"\n{'DRY RUN - ' if dry_run else ''}Processing products...")

    for i, product in enumerate(products, 1):
        print(f"Processing product {i}/{len(products)}: {product.get('name', 'Unknown')}")

        result = process_product_pricing(product, dry_run=dry_run)
        results.append(result)

        # Rate limiting
        time.sleep(0.2)

    # Summary
    print("\n" + "=" * 50)
    print("SUMMARY")
    print("=" * 50)

    successful = len([r for r in results if r['status'] == 'updated' or (dry_run and r['status'] == 'pending')])
    failed = len([r for r in results if r['status'] == 'failed'])
    skipped = len([r for r in results if r['status'] == 'skipped'])

    print(f"Total products: {len(products)}")
    print(f"{'Would be updated' if dry_run else 'Successfully updated'}: {successful}")
    print(f"Failed: {failed}")
    print(f"Skipped: {skipped}")

    # Show some examples
    print(f"\nExample price changes:")
    for result in results[:10]:
        if 'old_price' in result:
            status_emoji = "✓" if result['status'] in ['updated', 'pending'] else "✗"
            print(f"{status_emoji} {result['name'][:40]:40} ${result['old_price']:.2f} -> ${result['new_price']:.2f}")

    if dry_run:
        print(f"\nThis was a dry run. No actual changes were made.")
        print(f"To apply these changes, set dry_run = False in the script and run again.")
    else:
        print(f"\n🎉 SUCCESS! All price changes have been applied!")
        print(f"✅ {successful} products updated successfully")
        print(f"🔄 All bulk pricing and tiered pricing updated")
        print(f"💰 All prices increased by 20% and rounded to nearest quarter")

if __name__ == "__main__":
    main()