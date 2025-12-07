#!/usr/bin/env python3
"""
Test updating a single product to verify the process works
"""

import requests
import json
import math
import time

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

def update_product_price(product_id: int, new_price: float) -> bool:
    """Update the base price of a product"""
    url = f'{BASE_URL}/catalog/products/{product_id}'
    payload = {'price': new_price}

    try:
        print(f"Updating product {product_id} price to ${new_price:.2f}...")
        response = requests.put(url, headers=HEADERS, json=payload)
        response.raise_for_status()
        print(f"✅ Successfully updated product {product_id} price!")
        return True
    except requests.exceptions.RequestException as e:
        print(f"❌ Error updating price for product {product_id}: {e}")
        if hasattr(e, 'response') and e.response is not None:
            print(f"Response: {e.response.text}")
        return False

def main():
    print("Testing Single Product Update")
    print("=" * 40)

    # Get first product
    url = f'{BASE_URL}/catalog/products'
    params = {'page': 1, 'limit': 1}

    try:
        response = requests.get(url, headers=HEADERS, params=params)
        response.raise_for_status()
        data = response.json()
        products = data.get('data', [])

        if not products:
            print("No products found!")
            return

        product = products[0]
        product_id = product['id']
        name = product.get('name', 'Unknown')
        current_price = float(product.get('price', 0))

        print(f"Product: {name}")
        print(f"ID: {product_id}")
        print(f"Current Price: ${current_price:.2f}")

        if current_price <= 0:
            print("Product has no valid price, skipping.")
            return

        new_price = calculate_new_price(current_price)
        print(f"New Price: ${new_price:.2f} (+20%)")

        # Update the price
        success = update_product_price(product_id, new_price)

        if success:
            print("\n🎉 Test completed successfully!")
            print("The main script should work correctly for all products.")
        else:
            print("\n❌ Test failed!")
            print("There may be an issue with permissions or API access.")

    except requests.exceptions.RequestException as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    main()