#!/usr/bin/env python3
"""
BigCommerce Sale Price Updater
Updates sale prices for products that have them
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

def update_product_sale_price(product_id: int, new_sale_price: float) -> bool:
    """Update the sale price of a product"""
    url = f'{BASE_URL}/catalog/products/{product_id}'

    payload = {
        'sale_price': new_sale_price
    }

    try:
        response = requests.put(url, headers=HEADERS, json=payload)
        response.raise_for_status()
        return True
    except requests.exceptions.RequestException as e:
        print(f"Error updating sale price for product {product_id}: {e}")
        return False

def main():
    print("BigCommerce Sale Price Updater")
    print("=" * 50)
    print("Updating sale prices for products that have them")
    print("=" * 50)

    # Fetch all products
    print("Fetching all products...")
    products = get_all_products()
    print(f"Found {len(products)} products")

    products_with_sale_prices = []

    # Find products with sale prices
    for product in products:
        sale_price = product.get('sale_price')
        if sale_price and float(sale_price) > 0:
            products_with_sale_prices.append(product)

    print(f"\nFound {len(products_with_sale_prices)} products with sale prices:")
    print("-" * 80)
    print(f"{'Product Name':<40} {'Current Sale':<12} {'New Sale':<12} {'Status'}")
    print("-" * 80)

    updated_count = 0
    failed_count = 0

    for product in products_with_sale_prices:
        product_id = product['id']
        name = product.get('name', 'Unknown')[:38]
        current_sale_price = float(product.get('sale_price', 0))

        if current_sale_price <= 0:
            continue

        new_sale_price = calculate_new_price(current_sale_price)

        # Update the sale price
        success = update_product_sale_price(product_id, new_sale_price)

        if success:
            status = "✅ UPDATED"
            updated_count += 1
        else:
            status = "❌ FAILED"
            failed_count += 1

        print(f"{name:<40} ${current_sale_price:<11.2f} ${new_sale_price:<11.2f} {status}")
        time.sleep(0.2)  # Rate limiting

    print("-" * 80)
    print(f"\nSUMMARY:")
    print(f"Products with sale prices: {len(products_with_sale_prices)}")
    print(f"Successfully updated: {updated_count}")
    print(f"Failed: {failed_count}")

    print(f"\n🎉 Sale price updates complete!")
    print(f"Your products should now show the correct increased prices on the website.")

if __name__ == "__main__":
    main()