import requests
import json

# BigCommerce API credentials
bc_store_hash = 'tqjrceegho'
bc_access_token = 'lmg7prm3b0fxypwwaja27rtlvqejic0'
base_domain = 'www.munchmakers.com'

# API base URL
api_base_url = f'https://api.bigcommerce.com/stores/{bc_store_hash}/v3'

# Headers for API requests
headers = {
    'X-Auth-Token': bc_access_token,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
}

def fetch_categories():
    """Fetch all categories from BigCommerce"""
    categories = []
    page = 1

    while True:
        url = f'{api_base_url}/catalog/categories?page={page}&limit=100'
        response = requests.get(url, headers=headers)

        if response.status_code == 200:
            data = response.json()
            categories.extend(data['data'])

            # Check if there are more pages
            if 'meta' in data and 'pagination' in data['meta']:
                if page >= data['meta']['pagination']['total_pages']:
                    break
                page += 1
            else:
                break
        else:
            print(f"Error fetching categories: {response.status_code}")
            break

    return categories

def fetch_products():
    """Fetch all products from BigCommerce"""
    products = []
    page = 1

    while True:
        url = f'{api_base_url}/catalog/products?page={page}&limit=100&include=primary_image'
        response = requests.get(url, headers=headers)

        if response.status_code == 200:
            data = response.json()
            products.extend(data['data'])

            # Check if there are more pages
            if 'meta' in data and 'pagination' in data['meta']:
                if page >= data['meta']['pagination']['total_pages']:
                    break
                page += 1
            else:
                break
        else:
            print(f"Error fetching products: {response.status_code}")
            break

    return products

# Fetch data
print("Fetching categories...")
categories = fetch_categories()
print(f"Found {len(categories)} categories")

print("\nFetching products...")
products = fetch_products()
print(f"Found {len(products)} products")

# Save to JSON files for reference
with open('categories.json', 'w') as f:
    json.dump(categories, f, indent=2)

with open('products.json', 'w') as f:
    json.dump(products, f, indent=2)

# Print summary
print("\n=== CATEGORIES ===")
for cat in categories:
    print(f"- {cat['name']} (ID: {cat['id']}, URL: {cat['custom_url']['url']})")

print("\n=== PRODUCTS (First 20) ===")
for prod in products[:20]:
    print(f"- {prod['name']} (ID: {prod['id']}, SKU: {prod.get('sku', 'N/A')})")

print(f"\nTotal Categories: {len(categories)}")
print(f"Total Products: {len(products)}")
print("\nData saved to categories.json and products.json")