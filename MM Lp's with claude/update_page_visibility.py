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

# Current page ID
page_id = 48

# Update the page to not be visible in navigation
url = f'{api_base_url}/content/pages/{page_id}'

update_data = {
    "is_visible": False  # This removes it from navigation but keeps it accessible via direct URL
}

response = requests.put(url, headers=headers, json=update_data)

if response.status_code == 200:
    print("✅ Successfully updated page visibility settings!")
    print(f"\n📍 Page ID {page_id} is now:")
    print("   • Hidden from navigation menu")
    print("   • Still accessible at: https://www.munchmakers.com/custom-cannabis-accessories-for-dispensaries/")
    print("   • Perfect for landing page campaigns and direct links")
else:
    print(f"❌ Error updating page: {response.status_code}")
    print(response.text)