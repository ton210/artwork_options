#!/usr/bin/env python3
"""
Check extended progress - check first 20 products instead of 10
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
    print("Extended Progress Check - First 20 Products")
    print("=" * 60)

    # Original prices from our dry run for comparison
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
        "All Over Print Stash Jar": 29.21,
        "Mini Keychain Stash Jar": 17.85,
        "Branded Matchbook": 1.85,
        "Branded Matchbox": 1.85,
        "Plastic Joint Tube": 1.65,
        "Magnify Light-Up Jar": 31.45,
        "4\" Glass Pipe": 15.75,
        "Round Edges Stash Jar": 22.95,
        "Ovo Vape Pen": 9.95,
        "Timberflow Wood Vape Pen": 15.75,
        "TriVolt 510 Battery": 8.95
    }

    # Get first 20 products to check progress
    url = f'{BASE_URL}/catalog/products'
    params = {'page': 1, 'limit': 20}

    try:
        response = requests.get(url, headers=HEADERS, params=params)
        response.raise_for_status()
        data = response.json()
        products = data.get('data', [])

        updated_count = 0
        total_checked = len(products)

        print(f"Checking first {total_checked} products:")
        print("-" * 80)
        print(f"{'Product Name':<35} {'Original':<10} {'Current':<10} {'Expected':<10} {'Status':<10}")
        print("-" * 80)

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

                print(f"{name[:34]:35} ${original:<9.2f} ${current_price:<9.2f} ${expected:<9.2f} {status}")
            else:
                # For products without known original price, just show current
                print(f"{name[:34]:35} {'Unknown':<10} ${current_price:<9.2f} {'Unknown':<10} {'Unknown'}")

        print("-" * 80)

        known_products = sum(1 for p in products if p.get('name', 'Unknown') in original_prices)
        print(f"Known Products: {known_products}/{total_checked}")
        print(f"Updated: {updated_count}/{known_products} known products")

        completion_rate = (updated_count / known_products * 100) if known_products > 0 else 0
        print(f"Progress: {completion_rate:.1f}% of known products updated")

        if updated_count == known_products:
            print("\n🎉 All known products in first 20 have been updated!")
        elif updated_count > 0:
            print(f"\n⏳ Update in progress... {updated_count} completed so far")
        else:
            print("\n🔄 Updates starting soon...")

    except requests.exceptions.RequestException as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    main()