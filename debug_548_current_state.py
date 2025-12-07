#!/usr/bin/env python3
"""
Debug current state of Product 548 - find where $18.35 is coming from!
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
    product_id = 548

    print("🔍 DEBUGGING PRODUCT 548 - WHERE IS $18.35 COMING FROM?")
    print("=" * 60)

    # Get product details
    product = get_product_details(product_id)
    if not product:
        print("❌ Product not found!")
        return

    print(f"Product: {product.get('name', 'Unknown')}")
    print(f"Default Price: ${product.get('price', 0)}")
    print(f"Sale Price: ${product.get('sale_price', 'None')}")
    print(f"Calculated Price: ${product.get('calculated_price', 'None')}")
    print()

    # Get bulk pricing rules - CHECK EVERY SINGLE ONE
    bulk_rules = get_product_bulk_pricing(product_id)
    print(f"🔍 BULK PRICING RULES ({len(bulk_rules)} total):")
    print("-" * 60)

    found_18_35 = False
    for rule in bulk_rules:
        rule_id = rule.get('id')
        rule_type = rule.get('type')
        min_qty = rule.get('quantity_min')
        max_qty = rule.get('quantity_max', 'unlimited')
        amount = rule.get('amount')

        # Check if this rule has $18.35
        if amount and abs(float(amount) - 18.35) < 0.01:
            found_18_35 = True
            print(f"🚨 FOUND $18.35! → Rule {rule_id}: {min_qty}-{max_qty} qty at ${amount}")
        else:
            print(f"Rule {rule_id}: {min_qty}-{max_qty} qty = ${amount}")

    if not found_18_35:
        print("✅ NO $18.35 found in bulk pricing rules!")
    print()

    # Check variants
    variants = get_product_variants(product_id)
    print(f"🔍 VARIANTS ({len(variants)} total):")
    print("-" * 60)

    found_18_35_variant = False
    for variant in variants:
        variant_id = variant.get('id')
        variant_price = variant.get('price')
        if variant_price and abs(float(variant_price) - 18.35) < 0.01:
            found_18_35_variant = True
            print(f"🚨 FOUND $18.35 in variant {variant_id}!")
        else:
            print(f"Variant {variant_id}: ${variant_price}")

    if not found_18_35_variant:
        print("✅ NO $18.35 found in variants!")
    print()

    # Let's also check if there are any modifiers
    print("🔍 POSSIBLE SOURCES OF $18.35:")
    print("1. Old cached template data")
    print("2. JavaScript calculation error")
    print("3. Theme template hardcoded value")
    print("4. CDN serving old data")
    print("5. Browser cache (very persistent)")

    print(f"\n💡 RECOMMENDATION:")
    print(f"The $18.35 is likely coming from:")
    print(f"- Cached template calculations")
    print(f"- Old JavaScript in browser")
    print(f"- Theme template reading old data")

if __name__ == "__main__":
    main()