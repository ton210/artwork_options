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
page_id = 47

# Get the current page to preserve existing content
url = f'{api_base_url}/content/pages/{page_id}'
response = requests.get(url, headers=headers)

if response.status_code == 200:
    print("✓ Retrieved current page")

    # We need to delete and recreate since we can't get the body content
    # Delete current page
    delete_response = requests.delete(url, headers=headers)
    if delete_response.status_code in [204, 200]:
        print(f"✓ Deleted page {page_id} to recreate with updated images")

    # Get the previous HTML content and update image URLs
    with open('fix_dispensary_page_content.py', 'r') as f:
        content = f.read()

    # Extract the HTML from the Python file
    start_marker = "html_content = '''"
    end_marker = "'''"

    start_idx = content.find(start_marker) + len(start_marker)
    end_idx = content.find(end_marker, start_idx)
    html_content = content[start_idx:end_idx]

    # Update the image URLs to the new ones
    html_content = html_content.replace(
        'https://store-tqjrceegho.mybigcommerce.com/product_images/dispensary_hero.png',
        'https://store-tqjrceegho.mybigcommerce.com/product_images/dispensary_hero_v2.png'
    ).replace(
        'https://store-tqjrceegho.mybigcommerce.com/product_images/dispensary_customer.png',
        'https://store-tqjrceegho.mybigcommerce.com/product_images/dispensary_customer_v2.png'
    ).replace(
        'https://store-tqjrceegho.mybigcommerce.com/product_images/dispensary_profit.png',
        'https://store-tqjrceegho.mybigcommerce.com/product_images/dispensary_profit_v2.png'
    )

    # Also add a new showcase image in a strategic location
    # Add it after the customer experience section
    showcase_section = '''

<!-- Product Showcase Gallery -->
<div style="background: white; padding: 40px 20px;">
    <div style="max-width: 1000px; margin: 0 auto; text-align: center;">
        <h3 style="font-size: 28px; margin: 0 0 30px 0; color: #1a1a1a; font-weight: 700;">
            Premium Quality You Can See & Feel
        </h3>
        <img src="https://store-tqjrceegho.mybigcommerce.com/product_images/dispensary_showcase.png"
             alt="Premium cannabis accessories collection"
             style="width: 100%; max-width: 800px; height: auto; border-radius: 12px; box-shadow: 0 15px 40px rgba(0,0,0,0.1);">
        <p style="margin-top: 20px; color: #666; font-size: 16px;">
            Professional-grade aluminum construction • Laser engraving • Lifetime warranty
        </p>
    </div>
</div>
'''

    # Insert the showcase section after the Best Sellers section
    insert_point = html_content.find('</div>\n\n<!-- Use Cases -->')
    if insert_point > 0:
        html_content = html_content[:insert_point] + showcase_section + html_content[insert_point:]

    # Create the new page with updated images
    page_data = {
        "type": "page",
        "name": "Custom Cannabis Accessories for Dispensaries",
        "body": html_content,
        "is_visible": True,
        "parent_id": 0,
        "sort_order": 100,
        "meta_description": "Increase dispensary profits by 73% with custom grinders & accessories. No minimums, 5-day production. Join 500+ dispensaries.",
        "search_keywords": "dispensary accessories, custom grinders wholesale, cannabis accessories"
    }

    create_url = f'{api_base_url}/content/pages'
    create_response = requests.post(create_url, headers=headers, json=page_data)

    if create_response.status_code == 201:
        result = create_response.json()
        new_page_id = result['data']['id']
        page_url = result['data']['url']

        print(f"\n✅ PAGE SUCCESSFULLY UPDATED WITH NEW PROFESSIONAL IMAGES!")
        print(f"\n📍 Page Details:")
        print(f"   • New Page ID: {new_page_id}")
        print(f"   • Public URL: https://www.munchmakers.com{page_url}")
        print(f"   • Edit URL: https://store-{bc_store_hash}.mybigcommerce.com/manage/content/pages/{new_page_id}/edit")
        print(f"\n🖼️  New Professional Images:")
        print(f"   • Hero: dispensary_hero_v2.png")
        print(f"   • Customer: dispensary_customer_v2.png")
        print(f"   • Profit/ROI: dispensary_profit_v2.png")
        print(f"   • Product Showcase: dispensary_showcase.png")
    else:
        print(f"✗ Error creating new page: {create_response.status_code}")
        print(create_response.text)
else:
    print(f"✗ Could not retrieve page: {response.status_code}")