#!/usr/bin/env python3
"""
Investigate specific product ID 548 pricing
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

def main():
    product_id = 548

    print("Investigating Product ID 548")
    print("=" * 50)

    # Get product details
    product = get_product_details(product_id)

    if not product:
        print("Product not found!")
        return

    print(f"Product Name: {product.get('name', 'Unknown')}")
    print(f"Product ID: {product_id}")
    print(f"SKU: {product.get('sku', 'N/A')}")
    print()

    print("PRICING BREAKDOWN:")
    print("-" * 30)
    print(f"Base Price: ${product.get('price', 0)}")
    print(f"Sale Price: ${product.get('sale_price', 'None')}")
    print(f"Cost Price: ${product.get('cost_price', 'None')}")
    print(f"Retail Price: ${product.get('retail_price', 'None')}")
    print(f"Calculated Price: ${product.get('calculated_price', 'None')}")
    print()

    # Get bulk pricing rules
    bulk_rules = get_product_bulk_pricing(product_id)

    if bulk_rules:
        print(f"BULK PRICING RULES ({len(bulk_rules)} rules):")
        print("-" * 50)
        print(f"{'ID':<8} {'Type':<8} {'Min Qty':<8} {'Max Qty':<8} {'Amount':<12} {'Formatted':<15}")
        print("-" * 50)

        for rule in bulk_rules:
            rule_id = rule.get('id', 'N/A')
            rule_type = rule.get('type', 'N/A')
            min_qty = rule.get('quantity_min', 'N/A')
            max_qty = rule.get('quantity_max', 'unlimited')
            amount = rule.get('amount', 'N/A')

            # Try to get formatted discount
            discount = rule.get('discount', {})
            if isinstance(discount, dict):
                formatted = discount.get('formatted', discount.get('value', 'N/A'))
            else:
                formatted = str(discount)

            print(f"{rule_id:<8} {rule_type:<8} {min_qty:<8} {max_qty:<8} ${amount:<11} {formatted:<15}")

        print()
        print("ANALYSIS:")
        print("🔍 The bulk pricing table shows the VALUES from these rules")
        print("🔍 If showing $18.35, check which rule has amount=18.35")
        print("🔍 Expected values should all be ~20% higher than original")

        # Check for the specific $18.35 price
        found_1835 = False
        for rule in bulk_rules:
            amount = float(rule.get('amount', 0))
            if abs(amount - 18.35) < 0.01:
                found_1835 = True
                print(f"❌ FOUND $18.35 in rule ID {rule.get('id')} - this is likely the OLD price!")
                original_price = 18.35 / 1.2
                expected_new = original_price * 1.2
                print(f"   Original: ${original_price:.2f} → Expected: ${expected_new:.2f}")

        if not found_1835:
            print("✅ No $18.35 found in bulk pricing rules")

    else:
        print("❌ No bulk pricing rules found for this product")
        print("This suggests the issue might be elsewhere")

    print()
    print("RECOMMENDATION:")
    if bulk_rules:
        print("Run the bulk pricing update script again specifically for this product")
        print("The bulk pricing rules may not have been updated correctly")
    else:
        print("Check if this product's bulk pricing was accidentally removed")

if __name__ == "__main__":
    main()