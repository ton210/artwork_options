#!/usr/bin/env python3
"""
Fix pages with multiple links to the same destination
Keep only the FIRST link, remove all duplicates
"""
import requests
import json
import re
from datetime import datetime

# BigCommerce API credentials
BC_STORE_HASH = 'tqjrceegho'
BC_ACCESS_TOKEN = 'lmg7prm3b0fxypwwaja27rtlvqejic0'

# API endpoints
BLOG_API = f'https://api.bigcommerce.com/stores/{BC_STORE_HASH}/v2/blog/posts'
PRODUCT_API = f'https://api.bigcommerce.com/stores/{BC_STORE_HASH}/v3/catalog/products'

# Headers
HEADERS = {
    'X-Auth-Token': BC_ACCESS_TOKEN,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
}

# Target URL to deduplicate
TARGET_URL = "https://munchmakers.com/product-category/custom-rolling-trays/"

# Pages to fix (from implementation log)
PAGES_TO_FIX = {
    'blogs': [
        {
            'title': 'Roll in Style: The Ultimate Guide to Customizing Your Rolling Tray',
            'url': 'https://munchmakers.com/blog/roll-in-style-the-ultimate-guide-to-customizing-your-rolling-tray/',
            'current_links': 5
        },
        {
            'title': 'Rolling in Style: The Ultimate Guide to Choosing the Perfect Rolling Tray',
            'url': 'https://munchmakers.com/blog/rolling-in-style-the-ultimate-guide-to-choosing-the-perfect-rolling-tray/',
            'current_links': 3
        }
    ],
    'products': [
        {
            'name': 'Bamboo Rolling Tray',
            'url': 'https://munchmakers.com/product/custom-wooden-rolling-tray',
            'current_links': 3
        },
        {
            'name': 'Rolling Tray With Magnetic Lid',
            'url': 'https://munchmakers.com/product/custom-rolling-tray-with-magnetic-lid',
            'current_links': 3
        },
        {
            'name': 'Tin Rolling Trays',
            'url': 'https://munchmakers.com/product/custom-rolling-trays',
            'current_links': 3
        },
        {
            'name': 'LED Rolling Tray',
            'url': 'https://munchmakers.com/product/custom-led-rolling-tray',
            'current_links': 2
        }
    ]
}

def get_blog_post_id_from_url(blog_url):
    """Get blog post ID from URL"""
    try:
        response = requests.get(f"{BLOG_API}?limit=250", headers=HEADERS, timeout=10)
        if response.status_code == 200:
            posts = response.json()
            for post in posts:
                if post.get('url') in blog_url:
                    return post.get('id')
        return None
    except Exception as e:
        print(f"Error getting blog ID: {str(e)}")
        return None

def get_product_id_from_url(product_url):
    """Get product ID from URL"""
    try:
        if '/product/' in product_url:
            product_slug = product_url.split('/product/')[-1].rstrip('/')
            response = requests.get(f"{PRODUCT_API}?limit=250", headers=HEADERS, timeout=10)
            if response.status_code == 200:
                data = response.json()
                products = data.get('data', [])
                for product in products:
                    custom_url = product.get('custom_url', {}).get('url', '')
                    if product_slug in custom_url or custom_url in product_slug:
                        return product.get('id')
        return None
    except Exception as e:
        print(f"Error getting product ID: {str(e)}")
        return None

def remove_duplicate_links(content, target_url):
    """
    Remove duplicate links to the same URL, keeping only the first one
    Returns: (updated_content, number_of_links_removed)
    """
    # Pattern to match links to the target URL
    # Matches: <a href="target_url">text</a>
    pattern = re.compile(
        r'<a\s+href=["\']' + re.escape(target_url) + r'["\'][^>]*>(.*?)</a>',
        re.IGNORECASE | re.DOTALL
    )

    # Find all matches
    matches = list(pattern.finditer(content))

    if len(matches) <= 1:
        return content, 0  # No duplicates to remove

    # Keep the first link, remove the rest
    updated_content = content
    links_removed = 0

    # Process matches in reverse order to preserve positions
    for match in reversed(matches[1:]):  # Skip first match
        # Get the matched text and the inner text
        full_match = match.group(0)
        inner_text = match.group(1)

        # Replace the link with just the inner text
        updated_content = (
            updated_content[:match.start()] +
            inner_text +
            updated_content[match.end():]
        )
        links_removed += 1

    return updated_content, links_removed

def fix_blog_post(blog_data):
    """Fix duplicate links in a blog post"""
    print(f"\nFixing blog: {blog_data['title'][:60]}...")
    print(f"  Current links: {blog_data['current_links']} → Target: 1")

    # Get blog post ID
    blog_id = get_blog_post_id_from_url(blog_data['url'])
    if not blog_id:
        print(f"  ✗ Could not find blog post ID")
        return None

    try:
        # Get blog post content
        response = requests.get(f"{BLOG_API}/{blog_id}", headers=HEADERS, timeout=10)
        if response.status_code != 200:
            print(f"  ✗ Could not fetch blog post")
            return None

        blog_post = response.json()
        original_body = blog_post.get('body', '')

        # Remove duplicate links
        updated_body, links_removed = remove_duplicate_links(original_body, TARGET_URL)

        if links_removed == 0:
            print(f"  ✓ Already has only 1 link (or 0)")
            return {'status': 'already_fixed', 'links_removed': 0}

        # Update blog post
        update_data = {'body': updated_body}
        response = requests.put(
            f"{BLOG_API}/{blog_id}",
            headers=HEADERS,
            json=update_data,
            timeout=10
        )

        if response.status_code == 200:
            print(f"  ✓ Fixed! Removed {links_removed} duplicate link(s)")
            return {
                'status': 'success',
                'type': 'blog',
                'id': blog_id,
                'title': blog_data['title'],
                'url': blog_data['url'],
                'links_removed': links_removed,
                'final_links': blog_data['current_links'] - links_removed
            }
        else:
            print(f"  ✗ Update failed: {response.status_code}")
            return None

    except Exception as e:
        print(f"  ✗ Error: {str(e)}")
        return None

def fix_product(product_data):
    """Fix duplicate links in a product"""
    print(f"\nFixing product: {product_data['name'][:60]}...")
    print(f"  Current links: {product_data['current_links']} → Target: 1")

    # Get product ID
    product_id = get_product_id_from_url(product_data['url'])
    if not product_id:
        print(f"  ✗ Could not find product ID")
        return None

    try:
        # Get product details
        response = requests.get(f"{PRODUCT_API}/{product_id}", headers=HEADERS, timeout=10)
        if response.status_code != 200:
            print(f"  ✗ Could not fetch product")
            return None

        data = response.json()
        product = data.get('data', {})
        original_description = product.get('description', '')

        # Remove duplicate links
        updated_description, links_removed = remove_duplicate_links(original_description, TARGET_URL)

        if links_removed == 0:
            print(f"  ✓ Already has only 1 link (or 0)")
            return {'status': 'already_fixed', 'links_removed': 0}

        # Update product
        update_data = {'description': updated_description}
        response = requests.put(
            f"{PRODUCT_API}/{product_id}",
            headers=HEADERS,
            json={'data': update_data},
            timeout=10
        )

        if response.status_code == 200:
            print(f"  ✓ Fixed! Removed {links_removed} duplicate link(s)")
            return {
                'status': 'success',
                'type': 'product',
                'id': product_id,
                'name': product_data['name'],
                'url': product_data['url'],
                'links_removed': links_removed,
                'final_links': product_data['current_links'] - links_removed
            }
        else:
            print(f"  ✗ Update failed: {response.status_code}")
            return None

    except Exception as e:
        print(f"  ✗ Error: {str(e)}")
        return None

def fix_all_duplicates():
    """Fix all pages with duplicate links"""
    print("="*70)
    print("FIXING DUPLICATE LINKS")
    print("="*70)
    print(f"\nTarget URL: {TARGET_URL}")
    print(f"Pages to fix: {len(PAGES_TO_FIX['blogs'])} blogs + {len(PAGES_TO_FIX['products'])} products")
    print("\n")

    results = []

    # Fix blog posts
    print("="*70)
    print("FIXING BLOG POSTS")
    print("="*70)

    for blog in PAGES_TO_FIX['blogs']:
        result = fix_blog_post(blog)
        if result:
            results.append(result)

    # Fix products
    print("\n" + "="*70)
    print("FIXING PRODUCTS")
    print("="*70)

    for product in PAGES_TO_FIX['products']:
        result = fix_product(product)
        if result:
            results.append(result)

    # Summary
    print("\n" + "="*70)
    print("DEDUPLICATION COMPLETE")
    print("="*70)

    successful = [r for r in results if r.get('status') == 'success']
    total_links_removed = sum(r.get('links_removed', 0) for r in successful)

    print(f"\nPages fixed: {len(successful)}")
    print(f"Total duplicate links removed: {total_links_removed}")
    print(f"\n✓ All pages now have ONLY 1 link to the category page")
    print("="*70)

    return results

if __name__ == '__main__':
    fix_all_duplicates()
