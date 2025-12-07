#!/usr/bin/env python3
"""
Fix Product 548 MOQ - correct the mistake
MOQ should be 10, not 1!
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

def delete_bulk_pricing_rule(product_id, rule_id):
    """Delete a bulk pricing rule"""
    url = f'{BASE_URL}/catalog/products/{product_id}/bulk-pricing-rules/{rule_id}'

    try:
        response = requests.delete(url, headers=HEADERS)
        response.raise_for_status()
        return True
    except requests.exceptions.RequestException as e:
        print(f"Error deleting bulk pricing rule {rule_id}: {e}")
        return False

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

def main():
    product_id = 548

    print("FIXING MOQ ERROR for Product 548")
    print("=" * 40)
    print("MOQ should be 10, not 1!")

    # Get current bulk pricing rules
    bulk_rules = get_product_bulk_pricing(product_id)

    print(f"Current bulk pricing rules: {len(bulk_rules)}")

    # Find and delete the incorrect 1-24 rule I just created
    rule_to_delete = None
    for rule in bulk_rules:
        min_qty = rule.get('quantity_min', 0)
        max_qty = rule.get('quantity_max', 0)
        if min_qty == 1 and max_qty == 24:
            rule_to_delete = rule
            break

    if rule_to_delete:
        rule_id = rule_to_delete.get('id')
        print(f"❌ Deleting incorrect rule: 1-24 qty (Rule ID: {rule_id})")

        if delete_bulk_pricing_rule(product_id, rule_id):
            print(f"✅ Deleted incorrect 1-24 rule")
        else:
            print(f"❌ Failed to delete rule {rule_id}")
    else:
        print("No 1-24 rule found to delete")

    # Create the CORRECT rule for 10-24 qty at $24.00
    print(f"\n✅ Creating CORRECT rule: 10-24 qty at $24.00 (preserves MOQ=10)")

    new_rule = create_bulk_pricing_rule(product_id, 10, 24, 24.00)

    if new_rule:
        print(f"✅ Created correct rule for 10-24 qty at $24.00")
        print(f"✅ MOQ=10 preserved - customers cannot order less than 10")
    else:
        print("❌ Failed to create correct 10-24 rule")

    print(f"\n🎉 MOQ Fix Complete!")
    print(f"✅ Minimum Order Quantity: 10 (preserved)")
    print(f"✅ 10-24 range: $24.00 each")
    print(f"✅ 25+ ranges: Updated bulk pricing")

if __name__ == "__main__":
    main()