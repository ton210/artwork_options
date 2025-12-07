#!/usr/bin/env python3
"""
BigCommerce Variant Price Updater
Updates variant prices to match either sale price (if set) or base price
"""

import requests
import json
import math
import time
from typing import List, Dict, Any

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

def get_all_products() -> List[Dict[Any, Any]]:
    """Fetch all products from BigCommerce store"""
    products = []
    page = 1
    limit = 250

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

            if not data.get('data'):
                break

            products.extend(data['data'])
            print(f"Fetched page {page}: {len(data['data'])} products")

            if len(data['data']) < limit:
                break

            page += 1
            time.sleep(0.1)

        except requests.exceptions.RequestException as e:
            print(f"Error fetching products: {e}")
            break

    return products

def get_product_variants(product_id: int) -> List[Dict[Any, Any]]:
    """Get all variants for a product"""
    url = f'{BASE_URL}/catalog/products/{product_id}/variants'

    try:
        response = requests.get(url, headers=HEADERS)
        response.raise_for_status()
        data = response.json()
        return data.get('data', [])
    except requests.exceptions.RequestException as e:
        print(f"Error fetching variants for product {product_id}: {e}")
        return []

def update_variant_price(product_id: int, variant_id: int, new_price: float) -> bool:
    """Update the price of a specific variant"""
    url = f'{BASE_URL}/catalog/products/{product_id}/variants/{variant_id}'

    payload = {
        'price': new_price
    }

    try:
        response = requests.put(url, headers=HEADERS, json=payload)
        response.raise_for_status()
        return True
    except requests.exceptions.RequestException as e:
        print(f"Error updating variant {variant_id} for product {product_id}: {e}")
        return False

def main():
    print("BigCommerce Variant Price Updater")
    print("=" * 60)
    print("Updating variant prices to match sale/base prices")
    print("=" * 60)

    # Fetch all products
    print("Fetching all products...")
    products = get_all_products()
    print(f"Found {len(products)} products")

    total_variants_processed = 0
    total_variants_updated = 0
    products_with_variants = 0

    print(f"\nProcessing products for variant pricing...")
    print("-" * 100)
    print(f"{'Product':<40} {'Target Price':<12} {'Variants':<10} {'Updated':<10} {'Status'}")
    print("-" * 100)

    for i, product in enumerate(products, 1):
        product_id = product['id']
        name = product.get('name', 'Unknown')[:38]
        base_price = float(product.get('price', 0))
        sale_price = product.get('sale_price')

        # Determine the target price for variants
        if sale_price and float(sale_price) > 0:
            target_price = float(sale_price)
            price_source = "sale"
        else:
            target_price = base_price
            price_source = "base"

        # Get variants for this product
        variants = get_product_variants(product_id)

        if not variants:
            # No variants, skip
            continue

        products_with_variants += 1
        variants_updated = 0

        for variant in variants:
            variant_id = variant.get('id')
            variant_price = variant.get('price')

            # Handle None/null variant prices
            if variant_price is None:
                current_variant_price = 0
            else:
                current_variant_price = float(variant_price)

            # Update variant price to match target price
            success = update_variant_price(product_id, variant_id, target_price)

            if success:
                variants_updated += 1
                total_variants_updated += 1

            total_variants_processed += 1

        status = f"✅ {variants_updated}/{len(variants)}" if variants_updated == len(variants) else f"⚠️  {variants_updated}/{len(variants)}"

        print(f"{name:<40} ${target_price:<11.2f} {len(variants):<10} {variants_updated:<10} {status}")

        # Rate limiting
        time.sleep(0.3)

    print("-" * 100)
    print(f"\nSUMMARY:")
    print(f"Total products: {len(products)}")
    print(f"Products with variants: {products_with_variants}")
    print(f"Total variants processed: {total_variants_processed}")
    print(f"Total variants updated: {total_variants_updated}")

    if total_variants_processed > 0:
        success_rate = (total_variants_updated / total_variants_processed) * 100
        print(f"Success rate: {success_rate:.1f}%")

    print(f"\n🎉 Variant price updates complete!")
    print(f"All variant prices now match their product's sale price (if set) or base price.")

if __name__ == "__main__":
    main()