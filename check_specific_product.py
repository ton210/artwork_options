#!/usr/bin/env python3
"""
Check specific product details including variants
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

def get_product_variants(product_id):
    """Get product variants"""
    url = f'{BASE_URL}/catalog/products/{product_id}/variants'

    try:
        response = requests.get(url, headers=HEADERS)
        response.raise_for_status()
        return response.json()['data']
    except requests.exceptions.RequestException as e:
        print(f"Error getting product variants: {e}")
        return []

def search_ceramic_grinder_63mm():
    """Find the exact ceramic grinder 63mm product"""
    url = f'{BASE_URL}/catalog/products'
    params = {'keyword': 'Ceramic Grinder', 'limit': 250}

    try:
        response = requests.get(url, headers=HEADERS, params=params)
        response.raise_for_status()
        products = response.json()['data']

        for product in products:
            name = product.get('name', '')
            if '63mm' in name:
                return product

        return None
    except requests.exceptions.RequestException as e:
        print(f"Error searching products: {e}")
        return None

def main():
    print("Checking Specific Product: Ceramic Grinder 63mm")
    print("=" * 60)

    # Find the ceramic grinder 63mm
    product = search_ceramic_grinder_63mm()

    if not product:
        print("Product not found!")
        return

    product_id = product['id']
    name = product.get('name', 'Unknown')

    print(f"Product Found: {name}")
    print(f"Product ID: {product_id}")
    print(f"Basic Price: ${product.get('price', 0)}")

    # Get full product details
    details = get_product_details(product_id)
    if details:
        print(f"\nDetailed Product Information:")
        print(f"Name: {details.get('name')}")
        print(f"SKU: {details.get('sku')}")
        print(f"Base Price: ${details.get('price', 0)}")
        print(f"Sale Price: ${details.get('sale_price', 'None')}")
        print(f"Cost Price: ${details.get('cost_price', 'None')}")
        print(f"Retail Price: ${details.get('retail_price', 'None')}")
        print(f"Calculated Price: ${details.get('calculated_price', 'None')}")
        print(f"Product Type: {details.get('type', 'Unknown')}")

        # Check if it has inventory tracking
        print(f"Inventory Tracking: {details.get('inventory_tracking', 'None')}")

    # Get product variants
    variants = get_product_variants(product_id)

    if variants:
        print(f"\n🔍 PRODUCT VARIANTS FOUND ({len(variants)} variants):")
        print("-" * 70)
        print(f"{'Variant ID':<12} {'SKU':<15} {'Price':<10} {'Sale Price':<12} {'Options'}")
        print("-" * 70)

        for variant in variants:
            variant_id = variant.get('id', 'N/A')
            sku = variant.get('sku', 'N/A')
            price = variant.get('price', 0)
            sale_price = variant.get('sale_price', 'None')

            # Get option values
            option_values = variant.get('option_values', [])
            options_text = ", ".join([f"{ov.get('option_display_name', 'Unknown')}: {ov.get('label', 'Unknown')}"
                                    for ov in option_values])

            print(f"{variant_id:<12} {sku:<15} ${price:<9.2f} {sale_price:<12} {options_text}")

        print("\n🚨 ISSUE IDENTIFIED:")
        print("This product has VARIANTS with individual pricing!")
        print("Our script only updated the BASE product price, not the variant prices.")
        print("The website is likely showing the variant price, not the base price.")

    else:
        print(f"\n✅ No variants found - this is a simple product.")
        print(f"Current API price: ${details.get('price', 0)}")
        print(f"Expected price after 20% increase: ${float(details.get('price', 0)) / 1.2:.2f} -> ${details.get('price', 0)}")

        print(f"\nIf website shows ${32.15}, there might be:")
        print(f"1. Browser/CDN caching issue")
        print(f"2. Different price display logic on storefront")
        print(f"3. Sale price or promotional pricing active")

if __name__ == "__main__":
    main()