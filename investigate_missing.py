#!/usr/bin/env python3
"""
Investigate why some products weren't updated
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

def get_total_products():
    """Get total count of products in store"""
    url = f'{BASE_URL}/catalog/products'
    params = {'page': 1, 'limit': 1}

    try:
        response = requests.get(url, headers=HEADERS, params=params)
        response.raise_for_status()
        data = response.json()

        # Check pagination info
        meta = data.get('meta', {})
        pagination = meta.get('pagination', {})
        total = pagination.get('total', 0)

        return total
    except requests.exceptions.RequestException as e:
        print(f"Error getting total products: {e}")
        return 0

def search_product_by_name(search_term):
    """Search for products by name"""
    products = []
    page = 1

    while True:
        url = f'{BASE_URL}/catalog/products'
        params = {
            'page': page,
            'limit': 250,
            'keyword': search_term
        }

        try:
            response = requests.get(url, headers=HEADERS, params=params)
            response.raise_for_status()
            data = response.json()

            if not data.get('data'):
                break

            products.extend(data['data'])

            if len(data['data']) < 250:
                break

            page += 1

        except requests.exceptions.RequestException as e:
            print(f"Error searching products: {e}")
            break

    return products

def get_all_products_paginated():
    """Get ALL products with proper pagination"""
    products = []
    page = 1
    limit = 250

    print("Fetching ALL products with proper pagination...")

    while True:
        url = f'{BASE_URL}/catalog/products'
        params = {
            'page': page,
            'limit': limit
        }

        try:
            response = requests.get(url, headers=HEADERS, params=params)
            response.raise_for_status()
            data = response.json()

            current_batch = data.get('data', [])
            if not current_batch:
                break

            products.extend(current_batch)
            print(f"Fetched page {page}: {len(current_batch)} products (Total so far: {len(products)})")

            # Check if we've reached the end
            if len(current_batch) < limit:
                break

            page += 1

        except requests.exceptions.RequestException as e:
            print(f"Error fetching products page {page}: {e}")
            break

    return products

def main():
    print("Investigating Missing Product Updates")
    print("=" * 50)

    # Get total product count
    total_products = get_total_products()
    print(f"Total products in store: {total_products}")

    if total_products > 142:
        print(f"⚠️  WARNING: Store has {total_products} products, but we only processed 142!")
        print("This explains why some products weren't updated.")

    # Search for the specific ceramic grinder
    print(f"\nSearching for 'Ceramic Grinder 63mm'...")
    ceramic_grinders = search_product_by_name("Ceramic Grinder")

    print(f"Found {len(ceramic_grinders)} ceramic grinder products:")
    for product in ceramic_grinders:
        name = product.get('name', 'Unknown')
        current_price = product.get('price', 0)
        product_id = product.get('id')

        if '63mm' in name or '63' in name:
            print(f"🎯 FOUND: {name}")
            print(f"   ID: {product_id}")
            print(f"   Current Price: ${current_price}")
            print(f"   Expected Price: ${float(current_price) * 1.2:.2f}")

            # Check if this was in our original 142 products
            if product_id <= 432 + 141:  # Rough estimate based on our first product ID
                print("   ✅ Should have been in our update batch")
            else:
                print("   ❌ Likely NOT in our original 142 product batch")

    # Get ALL products to see the real scope
    print(f"\nFetching ALL products to see the real scope...")
    all_products = get_all_products_paginated()

    print(f"\nActual total products found: {len(all_products)}")

    if len(all_products) > 142:
        print(f"\n🚨 ISSUE CONFIRMED:")
        print(f"   - Our script processed: 142 products")
        print(f"   - Actual total products: {len(all_products)}")
        print(f"   - Products missed: {len(all_products) - 142}")
        print(f"\nWe need to run the price update script again to catch the remaining {len(all_products) - 142} products.")

    # Show some examples of products that were likely missed
    if len(all_products) > 142:
        print(f"\nExamples of products that were likely missed:")
        missed_products = all_products[142:152]  # Show first 10 missed products
        for i, product in enumerate(missed_products, 143):
            name = product.get('name', 'Unknown')
            price = product.get('price', 0)
            print(f"   {i}. {name[:50]:50} ${price}")

if __name__ == "__main__":
    main()