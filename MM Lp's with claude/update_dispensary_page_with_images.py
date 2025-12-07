import requests
import json

# BigCommerce API credentials
bc_store_hash = 'tqjrceegho'
bc_access_token = 'lmg7prm3b0fxypwwaja27rtlvqejic0'

# API base URL
api_base_url = f'https://api.bigcommerce.com/stores/{bc_store_hash}/v3'

# Headers for API requests
headers = {
    'X-Auth-Token': bc_access_token,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
}

# The page ID we created
page_id = 46

# Get the current page content
url = f'{api_base_url}/content/pages/{page_id}'

response = requests.get(url, headers=headers)
if response.status_code == 200:
    current_page = response.json()['data']

    # Debug: print the page structure
    print("Page structure:")
    print(list(current_page.keys()) if isinstance(current_page, dict) else "Not a dict")

    # Try different field names
    current_body = current_page.get('body') or current_page.get('content') or current_page.get('html_body') or ""

    if not current_body:
        print("Warning: No body content found in page")
        print("Full page data:", json.dumps(current_page, indent=2)[:1000])

    # Replace placeholder image URLs with actual uploaded images
    updated_body = current_body.replace(
        'https://via.placeholder.com/800x400/2C3E50/8BC34A?text=Premium+Dispensary+Display',
        'https://store-tqjrceegho.mybigcommerce.com/product_images/dispensary_hero.png'
    ).replace(
        'https://via.placeholder.com/600x400/8BC34A/FFFFFF?text=Happy+Customer',
        'https://store-tqjrceegho.mybigcommerce.com/product_images/dispensary_customer.png'
    ).replace(
        'https://via.placeholder.com/400x300/4CAF50/FFFFFF?text=Profit+Growth',
        'https://store-tqjrceegho.mybigcommerce.com/product_images/dispensary_profit.png'
    )

    # Update the page with real images
    update_data = {
        "body": updated_body
    }

    update_response = requests.put(url, headers=headers, json=update_data)

    if update_response.status_code == 200:
        print("✓ Page updated successfully with real images!")
        print(f"\nView your updated page at:")
        print(f"https://www.munchmakers.com/custom-cannabis-accessories-for-dispensaries/")
    else:
        print(f"Error updating page: {update_response.status_code}")
        print(update_response.text)
else:
    print(f"Error fetching page: {response.status_code}")