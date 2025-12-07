#!/usr/bin/env python3
"""
Check how many products have been updated so far
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

def round_to_quarter(price: float) -> float:
    """Round price to nearest quarter (.00, .25, .50, .75)"""
    return round(price * 4) / 4

def main():
    print("Checking Update Progress...")
    print("=" * 40)

    # Original prices from our dry run
    original_prices = {
        "Square Ceramic Ashtray": 6.40,
        "Magnetic Closure Booklet with Tips": 5.45,
        "1 1/4 Size 5-in-1 Grinder Tray Booklet": 2.70,
        "King Size 5-in-1 Grinder Tray Booklet": 2.70,
        "Folded Rolling Tray": 2.75,
        "1 1/4 Size Cones 3-Pack": 3.60,
        "King Size Cones 3-Pack": 2.70,
        "Paper Rolling Tray": 0.85,
        "King Size Cones (100 Per Box)": 36.80,
        "All Over Print Stash Jar": 29.21
    }

    # Get first 10 products to check progress
    url = f'{BASE_URL}/catalog/products'
    params = {'page': 1, 'limit': 10}

    try:
        response = requests.get(url, headers=HEADERS, params=params)
        response.raise_for_status()
        data = response.json()
        products = data.get('data', [])

        updated_count = 0
        total_checked = len(products)

        print(f"Checking first {total_checked} products:")
        print("-" * 60)

        for product in products:
            name = product.get('name', 'Unknown')
            current_price = float(product.get('price', 0))

            if name in original_prices:
                original = original_prices[name]
                expected = round_to_quarter(original * 1.20)

                if abs(current_price - expected) < 0.01:  # Account for rounding
                    status = "✅ UPDATED"
                    updated_count += 1
                else:
                    status = "🔄 PENDING"

                print(f"{name[:35]:35} ${original:.2f} -> ${current_price:.2f} {status}")
            else:
                print(f"{name[:35]:35} ${current_price:.2f} (unknown original)")

        print("-" * 60)
        print(f"Progress: {updated_count}/{total_checked} products confirmed updated")

        if updated_count == total_checked:
            print("🎉 All checked products have been updated!")
        elif updated_count > 0:
            print(f"⏳ Update in progress... {updated_count} completed so far")
        else:
            print("🔄 Updates starting soon...")

    except requests.exceptions.RequestException as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    main()