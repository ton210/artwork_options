#!/usr/bin/env python3
"""
BigCommerce Price Test - Quick test with first 5 products
"""

import requests
import json
import math
import time

# BigCommerce Store Configuration
BC_STORE_HASH = 'tqjrceegho'
BC_ACCESS_TOKEN = 'lmg7prm3b0fxypwwaja27rtlvqejic0'
BASE_DOMAIN = 'www.munchmakers.com'

# API Configuration
BASE_URL = f'https://api.bigcommerce.com/stores/{BC_STORE_HASH}/v3'
HEADERS = {
    'X-Auth-Token': BC_ACCESS_TOKEN,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
}

def round_to_quarter(price: float) -> float:
    """
    Round price to nearest quarter (.00, .25, .50, .75)
    """
    return round(price * 4) / 4

def calculate_new_price(current_price: float) -> float:
    """
    Increase price by 20% and round to nearest quarter
    """
    increased_price = current_price * 1.20
    return round_to_quarter(increased_price)

def main():
    print("BigCommerce Price Test - First 5 Products")
    print("=" * 50)

    # Fetch first 5 products
    url = f'{BASE_URL}/catalog/products'
    params = {'page': 1, 'limit': 5}

    try:
        response = requests.get(url, headers=HEADERS, params=params)
        response.raise_for_status()
        data = response.json()
        products = data.get('data', [])

        print(f"Found {len(products)} products to test")
        print("\nPrice calculations:")
        print("-" * 80)
        print(f"{'Product Name':<40} {'Current':<10} {'New Price':<10} {'Increase':<10}")
        print("-" * 80)

        for product in products:
            name = product.get('name', 'Unknown')[:38]
            current_price = float(product.get('price', 0))

            if current_price > 0:
                new_price = calculate_new_price(current_price)
                increase = new_price - current_price

                print(f"{name:<40} ${current_price:<9.2f} ${new_price:<9.2f} ${increase:<9.2f}")
            else:
                print(f"{name:<40} {'No price':<10} {'Skipped':<10} {'N/A':<10}")

        print("-" * 80)
        print("\nRounding examples:")
        test_prices = [34.93, 25.67, 12.11, 99.88, 15.00]
        for price in test_prices:
            new_price = calculate_new_price(price)
            print(f"${price:.2f} -> ${price*1.2:.2f} -> ${new_price:.2f}")

    except requests.exceptions.RequestException as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    main()