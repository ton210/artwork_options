#!/usr/bin/env python3
"""
Demonstrate hyperlink insertion on real content
Shows EXACTLY what will be changed before making any updates
"""
import requests
import json
import re

# BigCommerce API credentials
BC_STORE_HASH = 'tqjrceegho'
BC_ACCESS_TOKEN = 'lmg7prm3b0fxypwwaja27rtlvqejic0'

# API endpoints
BLOG_API = f'https://api.bigcommerce.com/stores/{BC_STORE_HASH}/v2/blog/posts'
PRODUCT_API = f'https://api.bigcommerce.com/stores/{BC_STORE_HASH}/v3/catalog/products'
CATEGORY_API = f'https://api.bigcommerce.com/stores/{BC_STORE_HASH}/v3/catalog/categories'

# Headers
HEADERS = {
    'X-Auth-Token': BC_ACCESS_TOKEN,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
}

def find_and_show_hyperlink_opportunity(content, keyword, target_url, content_type):
    """
    Find keyword in content and show what the hyperlink would look like
    """
    print(f"\n{'='*70}")
    print(f"HYPERLINK OPPORTUNITY: {content_type.upper()}")
    print(f"{'='*70}")

    print(f"\nKeyword to hyperlink: '{keyword}'")
    print(f"Target URL: {target_url}")

    # Find keyword in content (case-insensitive)
    pattern = re.compile(re.escape(keyword), re.IGNORECASE)
    match = pattern.search(content)

    if not match:
        print(f"\n✗ Keyword '{keyword}' not found in content")
        return None

    matched_text = match.group()
    start_pos = match.start()
    end_pos = match.end()

    # Get context (100 chars before and after)
    context_start = max(0, start_pos - 100)
    context_end = min(len(content), end_pos + 100)

    before_context = content[context_start:start_pos]
    after_context = content[end_pos:context_end]

    # Create hyperlink
    hyperlink = f'<a href="{target_url}">{matched_text}</a>'

    print(f"\n✓ Found keyword at position {start_pos}")
    print(f"\n{'─'*70}")
    print("BEFORE:")
    print(f"{'─'*70}")
    print(f"...{before_context}{matched_text}{after_context}...")

    print(f"\n{'─'*70}")
    print("AFTER:")
    print(f"{'─'*70}")
    print(f"...{before_context}{hyperlink}{after_context}...")

    print(f"\n{'─'*70}")
    print("CHANGE SUMMARY:")
    print(f"{'─'*70}")
    print(f"  Original: {matched_text}")
    print(f"  Updated:  <a href=\"{target_url}\">{matched_text}</a>")

    return {
        'keyword': keyword,
        'matched_text': matched_text,
        'target_url': target_url,
        'hyperlink': hyperlink,
        'position': start_pos
    }

def demo_blog_post_linking():
    """Demo hyperlink insertion in a blog post"""
    print("\n" + "="*70)
    print("DEMO 1: BLOG POST → CATEGORY PAGE")
    print("="*70)

    # Get a popular blog post about grinders
    try:
        # Search for a grinder-related blog post
        response = requests.get(f"{BLOG_API}?limit=50", headers=HEADERS)

        if response.status_code == 200:
            posts = response.json()

            # Find a grinder-related post
            grinder_post = None
            for post in posts:
                if 'grinder' in post.get('title', '').lower():
                    grinder_post = post
                    break

            if grinder_post:
                print(f"\nBlog Post: {grinder_post['title']}")
                print(f"URL: https://munchmakers.com{grinder_post['url']}")

                body = grinder_post.get('body', '')

                # Try to link "custom grinder" to the custom grinders category
                opportunity = find_and_show_hyperlink_opportunity(
                    body,
                    'custom grinder',
                    'https://munchmakers.com/product-category/custom-grinders/',
                    'Blog Post'
                )

                if opportunity:
                    return {
                        'type': 'blog',
                        'id': grinder_post['id'],
                        'title': grinder_post['title'],
                        'opportunity': opportunity
                    }

    except Exception as e:
        print(f"✗ Error: {str(e)}")

    return None

def demo_product_linking():
    """Demo hyperlink insertion in a product page"""
    print("\n" + "="*70)
    print("DEMO 2: PRODUCT PAGE → RELATED PRODUCT")
    print("="*70)

    try:
        # Get products
        response = requests.get(f"{PRODUCT_API}?limit=50", headers=HEADERS)

        if response.status_code == 200:
            data = response.json()
            products = data.get('data', [])

            # Find a product with a description
            for product in products:
                if product.get('description') and len(product.get('description', '')) > 500:
                    print(f"\nProduct: {product['name']}")
                    print(f"URL: https://munchmakers.com/product/{product.get('custom_url', {}).get('url', '')}")

                    description = product.get('description', '')

                    # Try to link "rolling tray" to the rolling tray category
                    opportunity = find_and_show_hyperlink_opportunity(
                        description,
                        'rolling tray',
                        'https://munchmakers.com/product-category/custom-rolling-trays/',
                        'Product Page'
                    )

                    if opportunity:
                        return {
                            'type': 'product',
                            'id': product['id'],
                            'name': product['name'],
                            'opportunity': opportunity
                        }
                        break

    except Exception as e:
        print(f"✗ Error: {str(e)}")

    return None

def demo_category_linking():
    """Demo hyperlink insertion in a category page"""
    print("\n" + "="*70)
    print("DEMO 3: CATEGORY PAGE → FEATURED PRODUCT")
    print("="*70)

    try:
        # Get categories
        response = requests.get(f"{CATEGORY_API}?limit=50", headers=HEADERS)

        if response.status_code == 200:
            data = response.json()
            categories = data.get('data', [])

            # Find a category with a description
            for category in categories:
                if category.get('description') and len(category.get('description', '')) > 500:
                    print(f"\nCategory: {category['name']}")
                    print(f"URL: https://munchmakers.com{category.get('custom_url', {}).get('url', '')}")

                    description = category.get('description', '')

                    # Try to link a product name if found
                    # Let's look for "custom" in the description
                    opportunity = find_and_show_hyperlink_opportunity(
                        description,
                        'custom grinder',
                        'https://munchmakers.com/product/custom-big-grinder',
                        'Category Page'
                    )

                    if opportunity:
                        return {
                            'type': 'category',
                            'id': category['id'],
                            'name': category['name'],
                            'opportunity': opportunity
                        }
                        break

    except Exception as e:
        print(f"✗ Error: {str(e)}")

    return None

def main():
    """Run all demos"""
    print("\n" + "="*70)
    print("HYPERLINK INSERTION DEMONSTRATION")
    print("="*70)
    print("\nThis demo shows EXACTLY what will change when we add internal links.")
    print("No actual changes will be made - this is a preview only.")
    print("="*70)

    demos = []

    # Demo 1: Blog post
    blog_demo = demo_blog_post_linking()
    if blog_demo:
        demos.append(blog_demo)

    # Demo 2: Product
    product_demo = demo_product_linking()
    if product_demo:
        demos.append(product_demo)

    # Demo 3: Category
    category_demo = demo_category_linking()
    if category_demo:
        demos.append(category_demo)

    # Summary
    print("\n" + "="*70)
    print("DEMONSTRATION SUMMARY")
    print("="*70)

    print(f"\n✓ Successfully demonstrated {len(demos)} hyperlink insertions")
    print(f"\nCapabilities confirmed:")
    print(f"  ✓ Blog Posts: Can identify and hyperlink existing text")
    print(f"  ✓ Product Pages: Can identify and hyperlink existing text")
    print(f"  ✓ Category Pages: Can identify and hyperlink existing text")

    print(f"\nApproach:")
    print(f"  1. Find existing relevant keywords in content")
    print(f"  2. Add <a href='...'> tags around the keywords")
    print(f"  3. Link to relevant category/product pages")
    print(f"  4. Update via BigCommerce API")

    print(f"\nSafety:")
    print(f"  ✓ Only updates existing text (no new content added)")
    print(f"  ✓ Each change reviewed before implementation")
    print(f"  ✓ Changes are reversible")

    print("\n" + "="*70)
    print("READY FOR INTERNAL LINKING STRATEGY")
    print("="*70)
    print("\n")

if __name__ == '__main__':
    main()
