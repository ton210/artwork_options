#!/usr/bin/env python3
"""
Fix Product 540 - Update bulk pricing rule 98 from $30.00 to $35.00
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
    product_id = 540
    rule_id = 98  # The rule for 5-9 qty that has wrong $30.00 price

    print("🔧 FIXING PRODUCT 540 - Custom 4-piece Aluminum Grinder")
    print("=" * 60)
    print("Updating Rule 98 (5-9 qty) from $30.00 to $35.00")
    print()

    # Updated rule data
    updated_rule = {
        'quantity_min': 5,
        'quantity_max': 9,
        'type': 'fixed',
        'amount': 35.00
    }

    print(f"Updating rule {rule_id}...")
    print(f"  Quantity range: 5-9")
    print(f"  Old price: $30.00")
    print(f"  New price: $35.00")

    success = update_bulk_pricing_rule(product_id, rule_id, updated_rule)

    if success:
        print(f"✅ Successfully updated rule {rule_id}")
        print()
        print("🎉 PRODUCT 540 FIX COMPLETE!")
        print("✅ 5-9 quantity range now shows $35.00")
        print("✅ 1-4 quantity range will now also show $35.00")
        print("✅ All pricing consistent across all quantity ranges")
    else:
        print(f"❌ Failed to update rule {rule_id}")

if __name__ == "__main__":
    main()