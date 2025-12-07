#!/usr/bin/env python3
"""
BigCommerce Bulk Pricing Test - Test one product's bulk pricing rules
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

def calculate_new_price(current_price: float) -> float:
    """Increase price by 20% and round to nearest quarter"""
    increased_price = current_price * 1.20
    return round_to_quarter(increased_price)

def get_product_bulk_pricing(product_id: int):
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

def analyze_product_pricing(product_id: int):
    """Analyze a specific product's pricing structure"""

    # Get product details
    url = f'{BASE_URL}/catalog/products/{product_id}'
    try:
        response = requests.get(url, headers=HEADERS)
        response.raise_for_status()
        product = response.json()['data']
    except requests.exceptions.RequestException as e:
        print(f"Error fetching product {product_id}: {e}")
        return

    name = product.get('name', 'Unknown')
    current_price = float(product.get('price', 0))
    new_base_price = calculate_new_price(current_price)

    print(f"Product: {name}")
    print(f"Product ID: {product_id}")
    print(f"Current Base Price: ${current_price:.2f}")
    print(f"New Base Price: ${new_base_price:.2f} (+20%)")
    print("=" * 60)

    # Get bulk pricing rules
    bulk_rules = get_product_bulk_pricing(product_id)

    if not bulk_rules:
        print("❌ No bulk pricing rules found for this product.")
        return

    print(f"✅ Found {len(bulk_rules)} bulk pricing rule(s):")
    print("\nBULK PRICING ANALYSIS:")
    print("-" * 60)

    for i, rule in enumerate(bulk_rules, 1):
        print(f"\nRule #{i}:")
        print(f"  Type: {rule.get('type', 'unknown')}")
        print(f"  Quantity Min: {rule.get('quantity_min', 'N/A')}")
        print(f"  Quantity Max: {rule.get('quantity_max', 'unlimited')}")

        if rule.get('type') == 'fixed':
            # Fixed price per unit
            current_bulk_price = float(rule.get('amount', 0))
            new_bulk_price = calculate_new_price(current_bulk_price)
            savings_current = current_price - current_bulk_price
            savings_new = new_base_price - new_bulk_price

            print(f"  Current Bulk Price: ${current_bulk_price:.2f}")
            print(f"  New Bulk Price: ${new_bulk_price:.2f} (+20%)")
            print(f"  Current Savings: ${savings_current:.2f} per unit")
            print(f"  New Savings: ${savings_new:.2f} per unit")

        elif rule.get('type') == 'percent':
            # Percentage discount
            discount_percent = float(rule.get('amount', 0))
            current_bulk_price = current_price * (1 - discount_percent / 100)
            new_bulk_price = new_base_price * (1 - discount_percent / 100)

            print(f"  Discount: {discount_percent}% off")
            print(f"  Current Bulk Price: ${current_bulk_price:.2f}")
            print(f"  New Bulk Price: ${new_bulk_price:.2f}")
            print(f"  Note: Percentage discounts automatically scale with base price")

        elif rule.get('type') == 'price':
            # Fixed discount amount
            discount_amount = float(rule.get('amount', 0))
            current_bulk_price = current_price - discount_amount
            new_bulk_price = new_base_price - discount_amount

            print(f"  Discount Amount: ${discount_amount:.2f}")
            print(f"  Current Bulk Price: ${current_bulk_price:.2f}")
            print(f"  New Bulk Price: ${new_bulk_price:.2f}")
            print(f"  Note: Fixed discounts stay the same, so relative savings decrease")

def main():
    print("BigCommerce Bulk Pricing Test")
    print("=" * 60)

    # Get first few products to find one with bulk pricing
    url = f'{BASE_URL}/catalog/products'
    params = {'page': 1, 'limit': 20}  # Check first 20 products

    try:
        response = requests.get(url, headers=HEADERS, params=params)
        response.raise_for_status()
        data = response.json()
        products = data.get('data', [])

        print(f"Checking {len(products)} products for bulk pricing...")
        print("-" * 60)

        products_with_bulk = []

        for product in products:
            product_id = product['id']
            name = product.get('name', 'Unknown')
            bulk_rules = get_product_bulk_pricing(product_id)

            if bulk_rules:
                products_with_bulk.append((product_id, name, len(bulk_rules)))
                print(f"✅ {name[:50]:50} ({len(bulk_rules)} rule(s))")
            else:
                print(f"❌ {name[:50]:50} (no bulk pricing)")

        if products_with_bulk:
            print(f"\n🎯 Found {len(products_with_bulk)} product(s) with bulk pricing!")
            print(f"Let's analyze the first one in detail...\n")

            # Analyze the first product with bulk pricing
            product_id, name, rule_count = products_with_bulk[0]
            analyze_product_pricing(product_id)

        else:
            print("\n❌ No products found with bulk pricing in the first 20 products.")
            print("The script will still work - it just means these products only have base pricing.")

    except requests.exceptions.RequestException as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    main()