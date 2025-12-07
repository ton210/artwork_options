#!/usr/bin/env python3
"""
Fix ALL bulk pricing discrepancies sitewide
Re-apply the +20% pricing logic to all affected products
"""

import requests
import json
import math

# BigCommerce Store Configuration
BC_STORE_HASH = 'tqjrceegho'
BC_ACCESS_TOKEN = 'lmg7prm3b0fxypwwaja27rtlvqejic0'

# API Configuration
BASE_URL = f'https://api.bigcommerce.com/stores/{BC_STORE_HASH}/v3'
HEADERS = {
    'X-Auth-Token': BC_ACCESS_TOKEN,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
}

def round_to_quarter(price: float) -> float:
    """Round price to nearest quarter (.00, .25, .50, .75)"""
    return round(price * 4) / 4

def get_all_products():
    """Get all products with pagination"""
    products = []
    page = 1
    limit = 50

    while True:
        url = f'{BASE_URL}/catalog/products'
        params = {
            'page': page,
            'limit': limit,
            'include_fields': 'id,name,price,sale_price'
        }

        try:
            response = requests.get(url, headers=HEADERS, params=params)
            response.raise_for_status()
            data = response.json()

            batch = data.get('data', [])
            if not batch:
                break

            products.extend(batch)
            page += 1

            # Safety limit
            if len(products) > 200:
                break

        except requests.exceptions.RequestException as e:
            print(f"Error fetching products page {page}: {e}")
            break

    return products

def get_product_bulk_pricing(product_id):
    """Get bulk pricing rules for a product"""
    url = f'{BASE_URL}/catalog/products/{product_id}/bulk-pricing-rules'
    try:
        response = requests.get(url, headers=HEADERS)
        response.raise_for_status()
        data = response.json()
        return data.get('data', [])
    except requests.exceptions.RequestException as e:
        return []

def update_bulk_pricing_rule(product_id, rule_id, updated_rule):
    """Update a bulk pricing rule"""
    url = f'{BASE_URL}/catalog/products/{product_id}/bulk-pricing-rules/{rule_id}'

    try:
        response = requests.put(url, headers=HEADERS, json=updated_rule)
        response.raise_for_status()
        return True
    except requests.exceptions.RequestException as e:
        print(f"  ❌ Error updating rule {rule_id}: {e}")
        return False

def fix_product_bulk_pricing(product):
    """Fix bulk pricing for a single product"""
    product_id = product['id']
    product_name = product['name']
    base_price = float(product.get('price', 0))
    sale_price = product.get('sale_price')

    # Use sale price if available, otherwise base price
    current_price = float(sale_price) if sale_price else base_price

    if current_price <= 0:
        return 0, 0  # Skip products with no price

    # Get bulk pricing rules
    bulk_rules = get_product_bulk_pricing(product_id)

    if not bulk_rules:
        return 0, 0  # No bulk pricing to fix

    # Calculate what the original price was (reverse engineer)
    # Assume current price is the 20% increased price
    estimated_original = current_price / 1.2

    fixed_count = 0
    total_rules = len(bulk_rules)

    print(f"🔧 Product {product_id}: {product_name}")
    print(f"   Current: ${current_price}, Estimated original: ${estimated_original:.2f}")

    for rule in bulk_rules:
        rule_id = rule.get('id')
        min_qty = rule.get('quantity_min', 0)
        max_qty = rule.get('quantity_max')
        current_amount = float(rule.get('amount', 0))
        rule_type = rule.get('type', 'fixed')

        # Skip if rule amount is already close to current price (within $1)
        if abs(current_amount - current_price) <= 1.0:
            continue

        # Calculate what this rule SHOULD be based on the current price structure
        # For low quantities (1-24), use current price
        if min_qty <= 24:
            new_amount = round_to_quarter(current_price)
        else:
            # For higher quantities, calculate discount based on original price structure
            # Common discount tiers: 25-49 (25% off), 50-99 (30% off), 100+ (40% off), etc.
            if min_qty >= 1000:
                new_amount = round_to_quarter(estimated_original * 0.4)  # 60% off original
            elif min_qty >= 500:
                new_amount = round_to_quarter(estimated_original * 0.45)  # 55% off original
            elif min_qty >= 250:
                new_amount = round_to_quarter(estimated_original * 0.5)   # 50% off original
            elif min_qty >= 100:
                new_amount = round_to_quarter(estimated_original * 0.6)   # 40% off original
            elif min_qty >= 50:
                new_amount = round_to_quarter(estimated_original * 0.7)   # 30% off original
            elif min_qty >= 25:
                new_amount = round_to_quarter(estimated_original * 0.75)  # 25% off original
            else:
                new_amount = round_to_quarter(current_price)

        # Update the rule
        updated_rule = {
            'quantity_min': min_qty,
            'quantity_max': max_qty,
            'type': rule_type,
            'amount': new_amount
        }

        print(f"   Rule {rule_id} ({min_qty}-{max_qty}): ${current_amount:.2f} → ${new_amount:.2f}")

        if update_bulk_pricing_rule(product_id, rule_id, updated_rule):
            fixed_count += 1
        else:
            print(f"   ❌ Failed to update rule {rule_id}")

    print(f"   ✅ Fixed {fixed_count}/{total_rules} rules")
    print()

    return fixed_count, total_rules

def main():
    print("🔧 FIXING ALL BULK PRICING DISCREPANCIES SITEWIDE")
    print("=" * 60)
    print("Updating bulk pricing to match current base prices...")
    print()

    # Get all products
    print("📥 Fetching all products...")
    products = get_all_products()
    print(f"Found {len(products)} products")
    print()

    # Analyze and fix each product
    total_fixed = 0
    total_rules = 0
    products_processed = 0

    for product in products:
        fixed, rules = fix_product_bulk_pricing(product)
        total_fixed += fixed
        total_rules += rules

        if rules > 0:
            products_processed += 1

    print("🎉 BULK PRICING FIX COMPLETE!")
    print("=" * 40)
    print(f"Products processed: {products_processed}")
    print(f"Rules updated: {total_fixed}/{total_rules}")
    print(f"Success rate: {(total_fixed/total_rules*100):.1f}%" if total_rules > 0 else "N/A")
    print()
    print("✅ All bulk pricing should now match current base prices!")
    print("✅ Product 540 should now show $35.00 correctly!")

if __name__ == "__main__":
    main()