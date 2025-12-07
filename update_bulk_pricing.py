#!/usr/bin/env python3
"""
BigCommerce Bulk Pricing Updater
Updates product custom fields with correct bulk pricing information
"""

import requests
import json
import time
from typing import Dict, List, Optional, Tuple

# BigCommerce API credentials
BC_STORE_HASH = 'tqjrceegho'
BC_ACCESS_TOKEN = 'lmg7prm3b0fxypwwaja27rtlvqejic0'
BC_API_VERSION = 'v3'
BC_BASE_URL = f'https://api.bigcommerce.com/stores/{BC_STORE_HASH}/{BC_API_VERSION}'

# Headers for API requests
HEADERS = {
    'X-Auth-Token': BC_ACCESS_TOKEN,
    'Accept': 'application/json',
    'Content-Type': 'application/json'
}

def get_all_products() -> List[Dict]:
    """Fetch all products with pagination"""
    all_products = []
    page = 1

    while True:
        url = f'{BC_BASE_URL}/catalog/products'
        params = {
            'page': page,
            'limit': 250,
            'include': 'custom_fields,bulk_pricing_rules'
        }

        response = requests.get(url, headers=HEADERS, params=params)

        if response.status_code == 200:
            data = response.json()
            products = data.get('data', [])

            if products:
                all_products.extend(products)
                page += 1

                # Check if there are more pages
                meta = data.get('meta', {})
                pagination = meta.get('pagination', {})
                if page > pagination.get('total_pages', 0):
                    break
            else:
                break
        else:
            print(f"Error fetching products page {page}: {response.status_code}")
            break

    return all_products

def calculate_bulk_pricing(product: Dict) -> Tuple[float, int, float, int, bool]:
    """
    Calculate bulk pricing information for a product
    Returns: (lowest_price, lowest_price_qty, highest_qty_price, highest_qty, has_bulk_pricing)
    """
    bulk_rules = product.get('bulk_pricing_rules', [])
    base_price = float(product.get('price', 0))
    moq = product.get('order_quantity_minimum', 1)

    if not bulk_rules:
        return base_price, 1, base_price, 1, False

    lowest_price = base_price
    lowest_price_qty = 1
    highest_qty = 1
    highest_qty_price = base_price

    for rule in bulk_rules:
        rule_price = base_price
        qty_min = rule.get('quantity_min', 1)

        # Calculate the price based on rule type
        if rule['type'] == 'price':
            # Direct price override
            rule_price = float(rule['amount'])
        elif rule['type'] == 'percent':
            # Percentage discount off base price
            discount_percent = float(rule['amount']) / 100
            rule_price = base_price * (1 - discount_percent)
        elif rule['type'] == 'fixed':
            # Fixed price per unit (NOT a discount - this IS the price)
            rule_price = float(rule['amount'])

        # Find the actual lowest price (regardless of quantity)
        if rule_price > 0 and rule_price < lowest_price:
            lowest_price = rule_price
            lowest_price_qty = qty_min

        # Find the highest quantity tier
        if qty_min > highest_qty:
            highest_qty = qty_min
            highest_qty_price = rule_price if rule_price > 0 else base_price

    has_bulk_pricing = len(bulk_rules) > 0 and lowest_price < base_price and lowest_price > 0

    return lowest_price, lowest_price_qty, highest_qty_price, highest_qty, has_bulk_pricing

def update_product_custom_fields(product_id: int, custom_fields: List[Dict]) -> bool:
    """Update custom fields for a product"""
    try:
        # First, get existing custom fields
        url = f'{BC_BASE_URL}/catalog/products/{product_id}'
        params = {'include': 'custom_fields'}
        response = requests.get(url, headers=HEADERS, params=params)

        if response.status_code != 200:
            print(f"Error getting product {product_id}: {response.status_code}")
            return False

        product_data = response.json()['data']
        existing_fields = product_data.get('custom_fields', [])

        # Create a map of existing fields
        existing_field_map = {field['name']: field for field in existing_fields}

        # Build updated fields array
        updated_fields = []

        # Keep existing fields that aren't being updated
        for field in existing_fields:
            if not field['name'].startswith('__bulk_') and field['name'] != '__moq':
                updated_fields.append(field)

        # Add or update our custom fields
        for new_field in custom_fields:
            existing = existing_field_map.get(new_field['name'])

            if existing:
                # Update existing field if value is different
                if existing['value'] != new_field['value']:
                    updated_fields.append({
                        'id': existing['id'],
                        'name': new_field['name'],
                        'value': new_field['value']
                    })
                else:
                    # Keep existing field as-is
                    updated_fields.append(existing)
            else:
                # Add new field
                updated_fields.append(new_field)

        # Only update if there are actual changes
        fields_changed = False
        if len(updated_fields) != len(existing_fields):
            fields_changed = True
        else:
            for i, field in enumerate(updated_fields):
                if i >= len(existing_fields):
                    fields_changed = True
                    break
                existing = existing_fields[i]
                if field.get('name') != existing.get('name') or field.get('value') != existing.get('value'):
                    fields_changed = True
                    break

        if fields_changed:
            # Update the product
            url = f'{BC_BASE_URL}/catalog/products/{product_id}'
            data = {'custom_fields': updated_fields}
            response = requests.put(url, headers=HEADERS, json=data)

            if response.status_code not in [200, 207]:
                print(f"Error updating product {product_id}: {response.status_code} - {response.text}")
                return False

        return True
    except Exception as e:
        print(f"Error updating product {product_id}: {e}")
        return False

def main():
    """Main function"""
    print('🚀 Starting bulk pricing update script...\n')
    print(f'Store Hash: {BC_STORE_HASH}')
    print('=' * 40 + '\n')

    # Fetch all products
    print('📦 Fetching all products...')
    products = get_all_products()
    print(f'✅ Found {len(products)} products\n')

    updated = 0
    skipped = 0
    failed = 0

    # Process each product
    for product in products:
        product_id = product['id']
        product_name = product['name']
        base_price = float(product.get('price', 0))
        moq = product.get('order_quantity_minimum', 1)

        # Calculate bulk pricing
        lowest_price, lowest_price_qty, highest_qty_price, highest_qty, has_bulk_pricing = calculate_bulk_pricing(product)

        # Only update if there's meaningful data to add
        bulk_rules = product.get('bulk_pricing_rules', [])
        if moq > 1 or len(bulk_rules) > 0:
            print(f'\n📝 Processing: {product_name} (ID: {product_id})')
            print(f'   MOQ: {moq}')
            print(f'   Base Price: ${base_price:.2f}')

            custom_fields = []

            # Add MOQ field (always add, even if MOQ is 1 for "No MOQ" badge)
            custom_fields.append({
                'name': '__moq',
                'value': str(moq)
            })

            # Add bulk pricing fields if available
            if has_bulk_pricing:
                print(f'   Lowest Price: ${lowest_price:.2f} (at qty {lowest_price_qty})')
                print(f'   Highest Qty: {highest_qty} at ${highest_qty_price:.2f}')

                # Store the lowest price and its quantity
                custom_fields.append({
                    'name': '__bulk_price_min',
                    'value': f'{lowest_price:.2f}'
                })

                custom_fields.append({
                    'name': '__bulk_lowest_qty',
                    'value': str(lowest_price_qty)
                })

                # Also store the highest quantity tier info
                custom_fields.append({
                    'name': '__bulk_highest_qty',
                    'value': str(highest_qty)
                })

                custom_fields.append({
                    'name': '__bulk_highest_qty_price',
                    'value': f'{highest_qty_price:.2f}'
                })
            elif len(bulk_rules) > 0:
                print(f'   ⚠️ Bulk pricing rules exist but no valid lower price found')

            if custom_fields:
                success = update_product_custom_fields(product_id, custom_fields)

                if success:
                    print(f'   ✅ Updated successfully')
                    updated += 1
                else:
                    print(f'   ❌ Failed to update')
                    failed += 1

                # Add delay to avoid rate limiting
                time.sleep(0.2)
            else:
                skipped += 1
        else:
            # Still add MOQ field for "No MOQ" badge
            custom_fields = [{
                'name': '__moq',
                'value': str(moq)
            }]

            success = update_product_custom_fields(product_id, custom_fields)
            if success:
                updated += 1
            else:
                failed += 1

            time.sleep(0.2)

    print('\n' + '=' * 40)
    print('📊 Update Summary:')
    print(f'   ✅ Updated: {updated} products')
    print(f'   ⏭️  Skipped: {skipped} products')
    print(f'   ❌ Failed: {failed} products')
    print('=' * 40 + '\n')

    print('✨ Script completed successfully!')

if __name__ == '__main__':
    main()