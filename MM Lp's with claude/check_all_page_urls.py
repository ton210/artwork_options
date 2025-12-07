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

# All page IDs we created
page_ids = [48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, 62]

print("VERIFYING ALL MUNCHMAKERS LANDING PAGE URLs")
print("=" * 80)
print()

valid_pages = []
missing_pages = []

for page_id in page_ids:
    url = f'{api_base_url}/content/pages/{page_id}'
    response = requests.get(url, headers=headers)

    if response.status_code == 200:
        page = response.json()['data']
        page_name = page.get('name', 'N/A')
        page_url = page.get('url', 'N/A')
        is_visible = page.get('is_visible', False)

        # Build complete URL
        if page_url.startswith('/'):
            complete_url = f'https://www.munchmakers.com{page_url}'
        else:
            complete_url = f'https://www.munchmakers.com/{page_url}'

        valid_pages.append({
            'id': page_id,
            'name': page_name,
            'url': complete_url,
            'is_visible': is_visible
        })

        print(f"✅ Page ID {page_id}")
        print(f"   Name: {page_name}")
        print(f"   URL: {complete_url}")
        print(f"   Visible: {is_visible}")
        print()
    else:
        missing_pages.append(page_id)
        print(f"❌ Page ID {page_id} - NOT FOUND (404)")
        print()

print("=" * 80)
print("SUMMARY")
print("=" * 80)
print(f"✅ Valid Pages Found: {len(valid_pages)}")
print(f"❌ Missing Pages: {len(missing_pages)}")

if missing_pages:
    print(f"\nMissing Page IDs: {missing_pages}")

print("\n" + "=" * 80)
print("COMPLETE LIST OF WORKING URLs")
print("=" * 80)

# Sort by ID for easy reference
valid_pages.sort(key=lambda x: x['id'])

for page in valid_pages:
    print(f"\nPage ID {page['id']}: {page['name']}")
    print(f"URL: {page['url']}")
    if not page['is_visible']:
        print("⚠️  Note: Page is hidden (not publicly visible)")

print("\n" + "=" * 80)
print("URLs FOR TESTING")
print("=" * 80)
print("\nCopy these URLs to test in your browser:")
print("(Note: Hidden pages may require admin login to view)")
print()

for page in valid_pages:
    print(page['url'])