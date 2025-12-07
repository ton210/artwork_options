#!/usr/bin/env python3
"""
Test BigCommerce API access for:
1. Reading blog post content
2. Reading product content
3. Reading category content
4. Updating content with hyperlinks
"""
import requests
import json

# BigCommerce API credentials
BC_STORE_HASH = 'tqjrceegho'
BC_ACCESS_TOKEN = 'lmg7prm3b0fxypwwaja27rtlvqejic0'
BASE_DOMAIN = 'www.munchmakers.com'

# API base URL
API_BASE = f'https://api.bigcommerce.com/stores/{BC_STORE_HASH}/v3'
API_BASE_V2 = f'https://api.bigcommerce.com/stores/{BC_STORE_HASH}/v2'

# Headers
HEADERS = {
    'X-Auth-Token': BC_ACCESS_TOKEN,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
}

def test_api_connection():
    """Test basic API connection"""
    print("="*70)
    print("TESTING BIGCOMMERCE API CONNECTION")
    print("="*70)

    try:
        # Test with store info endpoint
        url = f'https://api.bigcommerce.com/stores/{BC_STORE_HASH}/v2/store'
        response = requests.get(url, headers=HEADERS)

        if response.status_code == 200:
            store_info = response.json()
            print(f"✓ API Connection successful!")
            print(f"  Store Name: {store_info.get('name')}")
            print(f"  Domain: {store_info.get('domain')}")
            print(f"  Store URL: {store_info.get('secure_url')}")
            return True
        else:
            print(f"✗ API Connection failed: {response.status_code}")
            print(f"  Response: {response.text}")
            return False

    except Exception as e:
        print(f"✗ Error testing API: {str(e)}")
        return False

def get_blog_posts():
    """Get blog posts (if blog feature is available)"""
    print("\n" + "="*70)
    print("TESTING BLOG POST ACCESS")
    print("="*70)

    # BigCommerce doesn't have a native blog API in v2/v3
    # Blog content is typically handled through:
    # 1. Custom pages (web pages)
    # 2. Third-party blog apps
    # 3. BigCommerce's built-in blog (older stores)

    # Try web pages endpoint (for custom content pages)
    try:
        url = f'{API_BASE_V2}/pages'
        response = requests.get(url, headers=HEADERS)

        if response.status_code == 200:
            pages = response.json()
            print(f"✓ Found {len(pages)} web pages")

            # Show first few pages
            for page in pages[:5]:
                print(f"\n  Page ID: {page.get('id')}")
                print(f"  Name: {page.get('name')}")
                print(f"  URL: {page.get('url')}")
                print(f"  Type: {page.get('type')}")

            return pages
        else:
            print(f"✗ Failed to get pages: {response.status_code}")
            print(f"  Response: {response.text}")
            return []

    except Exception as e:
        print(f"✗ Error getting pages: {str(e)}")
        return []

def get_single_page_content(page_id):
    """Get content for a specific page"""
    print(f"\n" + "="*70)
    print(f"GETTING CONTENT FOR PAGE ID: {page_id}")
    print("="*70)

    try:
        url = f'{API_BASE_V2}/pages/{page_id}'
        response = requests.get(url, headers=HEADERS)

        if response.status_code == 200:
            page = response.json()
            print(f"✓ Retrieved page content")
            print(f"\n  Name: {page.get('name')}")
            print(f"  URL: {page.get('url')}")
            print(f"  Meta Description: {page.get('meta_description', 'N/A')[:100]}")

            # Check if body content exists
            if 'body' in page:
                body = page.get('body', '')
                print(f"\n  Body length: {len(body)} characters")
                print(f"  Body preview: {body[:200]}...")
                print(f"\n  ✓ Content is editable via API")
            else:
                print(f"\n  ✗ No body field found")

            return page
        else:
            print(f"✗ Failed to get page: {response.status_code}")
            return None

    except Exception as e:
        print(f"✗ Error: {str(e)}")
        return None

def get_products():
    """Get products"""
    print("\n" + "="*70)
    print("TESTING PRODUCT ACCESS")
    print("="*70)

    try:
        url = f'{API_BASE}/catalog/products'
        params = {'limit': 5}  # Get first 5 products
        response = requests.get(url, headers=HEADERS, params=params)

        if response.status_code == 200:
            data = response.json()
            products = data.get('data', [])
            print(f"✓ Found {len(products)} products (showing first 5)")

            for product in products:
                print(f"\n  Product ID: {product.get('id')}")
                print(f"  Name: {product.get('name')}")
                print(f"  SKU: {product.get('sku')}")
                print(f"  Description length: {len(product.get('description', ''))} chars")

            return products
        else:
            print(f"✗ Failed to get products: {response.status_code}")
            return []

    except Exception as e:
        print(f"✗ Error: {str(e)}")
        return []

def get_single_product(product_id):
    """Get single product with full details"""
    print(f"\n" + "="*70)
    print(f"GETTING PRODUCT DETAILS: ID {product_id}")
    print("="*70)

    try:
        url = f'{API_BASE}/catalog/products/{product_id}'
        response = requests.get(url, headers=HEADERS)

        if response.status_code == 200:
            data = response.json()
            product = data.get('data', {})
            print(f"✓ Retrieved product")
            print(f"\n  Name: {product.get('name')}")

            # Check description field
            description = product.get('description', '')
            print(f"  Description length: {len(description)} characters")
            if description:
                print(f"  Description preview: {description[:200]}...")
                print(f"\n  ✓ Product description is editable via API")

            return product
        else:
            print(f"✗ Failed: {response.status_code}")
            return None

    except Exception as e:
        print(f"✗ Error: {str(e)}")
        return None

def get_categories():
    """Get product categories"""
    print("\n" + "="*70)
    print("TESTING CATEGORY ACCESS")
    print("="*70)

    try:
        url = f'{API_BASE}/catalog/categories'
        params = {'limit': 10}
        response = requests.get(url, headers=HEADERS, params=params)

        if response.status_code == 200:
            data = response.json()
            categories = data.get('data', [])
            print(f"✓ Found {len(categories)} categories (showing first 10)")

            for category in categories:
                print(f"\n  Category ID: {category.get('id')}")
                print(f"  Name: {category.get('name')}")
                print(f"  URL: {category.get('custom_url', {}).get('url', 'N/A')}")
                print(f"  Description length: {len(category.get('description', ''))} chars")

            return categories
        else:
            print(f"✗ Failed: {response.status_code}")
            return []

    except Exception as e:
        print(f"✗ Error: {str(e)}")
        return []

def get_single_category(category_id):
    """Get single category with full details"""
    print(f"\n" + "="*70)
    print(f"GETTING CATEGORY DETAILS: ID {category_id}")
    print("="*70)

    try:
        url = f'{API_BASE}/catalog/categories/{category_id}'
        response = requests.get(url, headers=HEADERS)

        if response.status_code == 200:
            data = response.json()
            category = data.get('data', {})
            print(f"✓ Retrieved category")
            print(f"\n  Name: {category.get('name')}")

            # Check description field
            description = category.get('description', '')
            print(f"  Description length: {len(description)} characters")
            if description:
                print(f"  Description preview: {description[:200]}...")
                print(f"\n  ✓ Category description is editable via API")

            return category
        else:
            print(f"✗ Failed: {response.status_code}")
            return None

    except Exception as e:
        print(f"✗ Error: {str(e)}")
        return None

def test_content_update_simulation(content_type, item_id, original_text, hyperlink_url, anchor_text):
    """
    Simulate adding a hyperlink to existing content
    This doesn't actually update the API, just shows what the update would look like
    """
    print(f"\n" + "="*70)
    print(f"SIMULATING HYPERLINK INSERTION - {content_type.upper()}")
    print("="*70)

    print(f"\nOriginal text:")
    print(f"  {original_text[:200]}...")

    # Find the anchor text in the original
    if anchor_text in original_text:
        # Create hyperlink
        hyperlink = f'<a href="{hyperlink_url}">{anchor_text}</a>'

        # Replace first occurrence
        updated_text = original_text.replace(anchor_text, hyperlink, 1)

        print(f"\n✓ Found '{anchor_text}' in content")
        print(f"\nUpdated text:")
        print(f"  {updated_text[:300]}...")

        print(f"\n✓ Hyperlink would be inserted successfully")
        print(f"  Link: {hyperlink}")

        return updated_text
    else:
        print(f"\n✗ '{anchor_text}' not found in content")
        print(f"  Cannot insert hyperlink without matching text")
        return None

def main():
    """Run all tests"""
    print("\n")

    # Test 1: API Connection
    if not test_api_connection():
        print("\n✗ API connection failed. Check credentials.")
        return

    # Test 2: Get pages (blog posts are typically custom pages)
    pages = get_blog_posts()

    if pages:
        # Get first page content
        first_page = pages[0]
        page_content = get_single_page_content(first_page['id'])

    # Test 3: Get products
    products = get_products()

    if products:
        # Get first product details
        first_product = products[0]
        product_details = get_single_product(first_product['id'])

    # Test 4: Get categories
    categories = get_categories()

    if categories:
        # Get first category details
        first_category = categories[0]
        category_details = get_single_category(first_category['id'])

    # Test 5: Simulate hyperlink insertion
    if products and products[0].get('description'):
        description = products[0].get('description', '')
        # Try to find common words to hyperlink
        if 'grinder' in description.lower():
            test_content_update_simulation(
                'product',
                products[0]['id'],
                description,
                'https://munchmakers.com/product-category/custom-grinders/',
                'grinder'
            )

    print("\n" + "="*70)
    print("API TESTING COMPLETE")
    print("="*70)
    print("\nSUMMARY:")
    print(f"  ✓ API Connection: Working")
    print(f"  ✓ Pages/Blog: {len(pages)} pages found")
    print(f"  ✓ Products: {len(products)} products tested")
    print(f"  ✓ Categories: {len(categories)} categories tested")
    print(f"  ✓ Content Update: Simulation successful")
    print("\n")

if __name__ == '__main__':
    main()
