#!/usr/bin/env python3
"""
Fix Product 548 bulk pricing specifically
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

def create_bulk_pricing_rule(product_id, min_qty, max_qty, price):
    """Create a new bulk pricing rule"""
    url = f'{BASE_URL}/catalog/products/{product_id}/bulk-pricing-rules'

    payload = {
        'quantity_min': min_qty,
        'quantity_max': max_qty,
        'type': 'fixed',
        'amount': price
    }

    try:
        response = requests.post(url, headers=HEADERS, json=payload)
        response.raise_for_status()
        return response.json()['data']
    except requests.exceptions.RequestException as e:
        print(f"Error creating bulk pricing rule: {e}")
        if hasattr(e, 'response') and e.response is not None:
            print(f"Response: {e.response.text}")
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

def update_bulk_pricing_rule(product_id, rule_id, updated_rule):
    """Update a bulk pricing rule"""
    url = f'{BASE_URL}/catalog/products/{product_id}/bulk-pricing-rules/{rule_id}'

    try:
        response = requests.put(url, headers=HEADERS, json=updated_rule)
        response.raise_for_status()
        return True
    except requests.exceptions.RequestException as e:
        print(f"Error updating bulk pricing rule {rule_id}: {e}")
        if hasattr(e, 'response') and e.response is not None:
            print(f"Response: {e.response.text}")
        return False

def main():
    product_id = 548

    print("Fixing Product 548 Bulk Pricing")
    print("=" * 40)

    # Get current bulk pricing rules
    bulk_rules = get_product_bulk_pricing(product_id)

    print(f"Current bulk pricing rules: {len(bulk_rules)}")

    # Check if there's a rule for quantities 1-24
    has_low_qty_rule = False
    for rule in bulk_rules:
        min_qty = rule.get('quantity_min', 0)
        if min_qty <= 10:
            has_low_qty_rule = True
            break

    if not has_low_qty_rule:
        print("Creating missing bulk pricing rule for quantities 1-24...")

        # Create a rule for 1-24 at $24.00 (the current base price)
        new_rule = create_bulk_pricing_rule(product_id, 1, 24, 24.00)

        if new_rule:
            print(f"✅ Created rule for 1-24 qty at $24.00")
        else:
            print("❌ Failed to create low quantity rule")

    # Also ensure all existing rules are properly updated
    print("\nUpdating existing rules to ensure correct pricing...")

    # Expected pricing based on 20% increase and quarter rounding
    expected_prices = {
        25: 24.00,   # Base price for 25-49
        50: 16.80,   # $14 * 1.2 = 16.8
        100: 14.40,  # $12 * 1.2 = 14.4
        250: 12.00,  # $10 * 1.2 = 12.0
        500: 10.80,  # $9 * 1.2 = 10.8
        1000: 9.60   # $8 * 1.2 = 9.6
    }

    for rule in bulk_rules:
        rule_id = rule.get('id')
        min_qty = rule.get('quantity_min')
        current_amount = float(rule.get('amount', 0))

        # Find expected price for this tier
        expected_amount = None
        for qty_threshold, price in expected_prices.items():
            if min_qty == qty_threshold:
                expected_amount = round_to_quarter(price)
                break

        if expected_amount and abs(current_amount - expected_amount) > 0.01:
            print(f"Updating rule {rule_id} (qty {min_qty}+): ${current_amount} → ${expected_amount}")

            updated_rule = {
                'quantity_min': rule.get('quantity_min'),
                'quantity_max': rule.get('quantity_max'),
                'type': rule.get('type'),
                'amount': expected_amount
            }

            success = update_bulk_pricing_rule(product_id, rule_id, updated_rule)
            if success:
                print(f"✅ Updated rule {rule_id}")
            else:
                print(f"❌ Failed to update rule {rule_id}")

    print(f"\n🎉 Product 548 bulk pricing update complete!")
    print(f"The 10-24 range should now show $24.00 instead of $18.35")

if __name__ == "__main__":
    main()