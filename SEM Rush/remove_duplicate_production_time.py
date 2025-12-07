#!/usr/bin/env python3
"""
Remove duplicate Production Time custom fields from BigCommerce products.
Keeps only the most recent Production Time entry.
"""

import requests
import time
from datetime import datetime

# BigCommerce API credentials
BC_STORE_HASH = 'tqjrceegho'
BC_ACCESS_TOKEN = 'lmg7prm3b0fxypwwaja27rtlvqejic0'
BASE_URL = f'https://api.bigcommerce.com/stores/{BC_STORE_HASH}/v3'

headers = {
    'X-Auth-Token': BC_ACCESS_TOKEN,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
}

def get_all_products():
    """Fetch all products from the store."""
    products = []
    page = 1
    limit = 250

    print("Fetching products from BigCommerce...")

    while True:
        url = f'{BASE_URL}/catalog/products?page={page}&limit={limit}&include=custom_fields'
        response = requests.get(url, headers=headers)

        if response.status_code != 200:
            print(f"Error fetching products: {response.status_code}")
            print(response.text)
            break

        data = response.json()
        batch = data.get('data', [])

        if not batch:
            break

        products.extend(batch)
        print(f"Fetched page {page}: {len(batch)} products (Total: {len(products)})")

        # Check if there are more pages
        meta = data.get('meta', {})
        pagination = meta.get('pagination', {})
        if pagination.get('current_page', 0) >= pagination.get('total_pages', 0):
            break

        page += 1
        time.sleep(0.2)  # Rate limiting

    return products

def get_product_custom_fields(product_id):
    """Get custom fields for a specific product."""
    url = f'{BASE_URL}/catalog/products/{product_id}/custom-fields'
    response = requests.get(url, headers=headers)

    if response.status_code == 200:
        return response.json().get('data', [])
    else:
        print(f"Error fetching custom fields for product {product_id}: {response.status_code}")
        return []

def delete_custom_field(product_id, field_id):
    """Delete a specific custom field."""
    url = f'{BASE_URL}/catalog/products/{product_id}/custom-fields/{field_id}'
    response = requests.delete(url, headers=headers)

    if response.status_code == 204:
        return True
    else:
        print(f"Error deleting custom field {field_id} from product {product_id}: {response.status_code}")
        print(response.text)
        return False

def clean_duplicate_production_times():
    """Remove duplicate Production Time custom fields, keeping only the last one."""
    products = get_all_products()
    print(f"\nTotal products found: {len(products)}\n")

    products_with_duplicates = []
    products_cleaned = 0

    for product in products:
        product_id = product['id']
        product_name = product['name']

        # Get custom fields for this product
        custom_fields = get_product_custom_fields(product_id)

        # Find all Production Time fields
        production_time_fields = [
            cf for cf in custom_fields
            if cf.get('name', '').strip().lower() == 'production time'
        ]

        if len(production_time_fields) > 1:
            print(f"\nProduct {product_id} - {product_name}")
            print(f"  Found {len(production_time_fields)} Production Time fields:")

            for i, field in enumerate(production_time_fields):
                print(f"    {i+1}. ID: {field['id']}, Value: {field['value']}")

            # Delete all but the last one
            fields_to_delete = production_time_fields[:-1]  # All except the last

            for field in fields_to_delete:
                print(f"  Deleting: {field['value']} (ID: {field['id']})")
                if delete_custom_field(product_id, field['id']):
                    print(f"    ✓ Successfully deleted")
                else:
                    print(f"    ✗ Failed to delete")
                time.sleep(0.2)  # Rate limiting

            print(f"  Keeping: {production_time_fields[-1]['value']} (ID: {production_time_fields[-1]['id']})")
            products_with_duplicates.append(product_id)
            products_cleaned += 1

    print(f"\n{'='*60}")
    print(f"SUMMARY:")
    print(f"  Total products processed: {len(products)}")
    print(f"  Products with duplicate Production Time: {len(products_with_duplicates)}")
    print(f"  Products cleaned: {products_cleaned}")

    if products_with_duplicates:
        print(f"\n  Product IDs cleaned: {', '.join(map(str, products_with_duplicates))}")

    print(f"{'='*60}")

if __name__ == "__main__":
    print("Starting cleanup of duplicate Production Time custom fields...")
    print(f"Date: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print("="*60)

    clean_duplicate_production_times()

    print("\nCleanup complete!")
