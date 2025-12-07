#!/usr/bin/env python3
"""
Debug Product 540 - Custom 4-piece Aluminum Grinder
Should be $35.00 but showing $30.00
"""

import requests
import json

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

def get_product_details(product_id):
    """Get full product details"""
    url = f'{BASE_URL}/catalog/products/{product_id}'
    try:
        response = requests.get(url, headers=HEADERS)
        response.raise_for_status()
        return response.json()['data']
    except requests.exceptions.RequestException as e:
        print(f"Error getting product details: {e}")
        return None

def get_product_bulk_pricing(product_id):
    """Get bulk pricing rules for a product"""
    url = f'{BASE_URL}/catalog/products/{product_id}/bulk-pricing-rules'
    try:
        response = requests.get(url, headers=HEADERS)
        response.raise_for_status()
        data = response.json()
        return data.get('data', [])
    except requests.exceptions.RequestException as e:
        print(f"Error fetching bulk pricing for product {product_id}: {e}")
        return []

def get_product_variants(product_id):
    """Get product variants"""
    url = f'{BASE_URL}/catalog/products/{product_id}/variants'
    try:
        response = requests.get(url, headers=HEADERS)
        response.raise_for_status()
        data = response.json()
        return data.get('data', [])
    except requests.exceptions.RequestException as e:
        print(f"Error fetching variants for product {product_id}: {e}")
        return []

def main():
    product_id = 540

    print("🔍 DEBUGGING PRODUCT 540 - Custom 4-piece Aluminum Grinder")
    print("=" * 60)
    print("Expected: $35.00, Currently showing: $30.00")
    print()

    # Get product details
    product = get_product_details(product_id)
    if not product:
        print("❌ Product not found!")
        return

    print(f"Product: {product.get('name', 'Unknown')}")
    print(f"Default Price: ${product.get('price', 0)}")
    print(f"Sale Price: ${product.get('sale_price', 'None')}")
    print(f"Calculated Price: ${product.get('calculated_price', 'None')}")
    print(f"Cost Price: ${product.get('cost_price', 'None')}")
    print(f"Retail Price: ${product.get('retail_price', 'None')}")
    print()

    # Check if prices are correct
    expected_price = 35.00
    current_price = float(product.get('price', 0))
    sale_price = product.get('sale_price')

    if sale_price and float(sale_price) != expected_price:
        print(f"❌ ISSUE: Sale price is ${sale_price}, should be ${expected_price}")
    elif current_price != expected_price:
        print(f"❌ ISSUE: Default price is ${current_price}, should be ${expected_price}")
    else:
        print(f"✅ Prices look correct in API")

    # Get bulk pricing rules
    bulk_rules = get_product_bulk_pricing(product_id)
    print(f"\n🔍 BULK PRICING RULES ({len(bulk_rules)} total):")
    print("-" * 60)

    for rule in bulk_rules:
        rule_id = rule.get('id')
        rule_type = rule.get('type')
        min_qty = rule.get('quantity_min')
        max_qty = rule.get('quantity_max', 'unlimited')
        amount = rule.get('amount')

        # Check if this rule has $30.00 (the wrong price)
        if amount and abs(float(amount) - 30.00) < 0.01:
            print(f"❌ FOUND $30.00! → Rule {rule_id}: {min_qty}-{max_qty} qty at ${amount}")
        else:
            print(f"Rule {rule_id}: {min_qty}-{max_qty} qty = ${amount}")

    # Check variants
    variants = get_product_variants(product_id)
    print(f"\n🔍 VARIANTS ({len(variants)} total):")
    print("-" * 60)

    variant_issues = False
    for variant in variants:
        variant_id = variant.get('id')
        variant_price = variant.get('price')
        if variant_price and abs(float(variant_price) - 30.00) < 0.01:
            variant_issues = True
            print(f"❌ FOUND $30.00 in variant {variant_id}!")
        elif variant_price and abs(float(variant_price) - 35.00) < 0.01:
            print(f"✅ Variant {variant_id}: ${variant_price} (correct)")
        else:
            print(f"Variant {variant_id}: ${variant_price}")

    print(f"\n💡 DIAGNOSIS:")
    if sale_price and float(sale_price) != expected_price:
        print(f"- Sale price needs to be updated from ${sale_price} to ${expected_price}")
    if current_price != expected_price:
        print(f"- Default price needs to be updated from ${current_price} to ${expected_price}")
    if variant_issues:
        print(f"- Some variants have incorrect $30.00 pricing")

    print(f"\n🔧 NEXT STEPS:")
    print(f"1. Update product pricing to $35.00")
    print(f"2. Update any incorrect variants")
    print(f"3. Clear cache to force frontend refresh")

if __name__ == "__main__":
    main()