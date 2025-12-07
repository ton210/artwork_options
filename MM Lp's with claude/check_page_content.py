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

# Check a few pages to see their content
test_pages = [48, 49, 51]

print("CHECKING PAGE CONTENT STATUS")
print("=" * 80)

for page_id in test_pages:
    url = f'{api_base_url}/content/pages/{page_id}'
    response = requests.get(url, headers=headers)

    if response.status_code == 200:
        page = response.json()['data']
        page_name = page.get('name', 'N/A')
        page_body = page.get('body', '')
        is_visible = page.get('is_visible', False)

        print(f"\nPage ID {page_id}: {page_name}")
        print(f"Visible: {is_visible}")
        print(f"Body Length: {len(page_body)} characters")

        if len(page_body) < 100:
            print(f"⚠️ WARNING: Page has very little or no content!")
            print(f"Body content: {page_body[:100] if page_body else 'EMPTY'}")
        else:
            print(f"✅ Page has content")
            print(f"First 200 chars: {page_body[:200]}...")

            # Check for specific content markers
            has_hero = '<div class="hero-section">' in page_body or 'hero' in page_body.lower()
            has_faq = 'Frequently Asked Questions' in page_body or 'FAQ' in page_body
            has_cta = 'Get Started' in page_body or 'REQUEST' in page_body

            print(f"  Has Hero Section: {has_hero}")
            print(f"  Has FAQ Section: {has_faq}")
            print(f"  Has CTA: {has_cta}")
    else:
        print(f"❌ Error fetching page {page_id}: {response.status_code}")

print("\n" + "=" * 80)
print("DIAGNOSIS:")
print("If pages are empty, we need to re-add the content.")
print("This might have happened during the meta update process.")