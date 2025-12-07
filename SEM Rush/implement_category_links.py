#!/usr/bin/env python3
"""
Implement internal links for Custom Rolling Trays category
Updates blog posts and products via BigCommerce API
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

# Input file
linking_plan_file = "/Users/tomernahumi/Documents/Plugins/SEM Rush/top_categories_analysis.json"

# Output file
changes_log_file = "/Users/tomernahumi/Documents/Plugins/SEM Rush/implementation_changes_log.md"

# Category to work on
TARGET_CATEGORY_URL = "https://munchmakers.com/product-category/custom-rolling-trays/"

# Keywords to search for and hyperlink
TARGET_KEYWORDS = [
    'custom rolling tray',
    'custom rolling trays',
    'rolling tray',
    'rolling trays',
    'weed tray',
    'weed trays',
    'custom tray',
    'custom trays'
]

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
        # Extract product slug from URL
        if '/product/' in product_url:
            product_slug = product_url.split('/product/')[-1].rstrip('/')

            # Search for product by custom URL
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

def add_links_to_content(content, target_url, keywords, max_links=1):
    """
    Add hyperlinks to content
    IMPORTANT: Only adds ONE link per page to avoid over-optimization
    Returns: (updated_content, list of changes made)
    """
    changes = []
    updated_content = content
    links_added = 0

    for keyword in keywords:
        if links_added >= max_links:
            break

        # Create case-insensitive pattern
        pattern = re.compile(r'\b' + re.escape(keyword) + r'\b', re.IGNORECASE)

        # Find all matches
        matches = list(pattern.finditer(updated_content))

        for match in matches:
            if links_added >= max_links:
                break

            matched_text = match.group()
            start_pos = match.start()

            # Check if already within a link
            # Look backwards for <a and forwards for </a>
            before_match = updated_content[:start_pos]
            after_match = updated_content[start_pos:]

            # Count <a> tags before this position
            open_tags_before = before_match.count('<a ')
            close_tags_before = before_match.count('</a>')

            # If there are more open tags than close tags, we're inside a link
            if open_tags_before > close_tags_before:
                continue

            # Also check if we're inside any HTML tag
            last_open_bracket = before_match.rfind('<')
            last_close_bracket = before_match.rfind('>')
            if last_open_bracket > last_close_bracket:
                continue  # We're inside a tag

            # Create hyperlink
            hyperlink = f'<a href="{target_url}">{matched_text}</a>'

            # Replace this occurrence
            updated_content = (
                updated_content[:start_pos] +
                hyperlink +
                updated_content[start_pos + len(matched_text):]
            )

            # Record the change
            context_start = max(0, start_pos - 50)
            context_end = min(len(content), start_pos + len(matched_text) + 50)

            changes.append({
                'keyword': matched_text,
                'before': content[context_start:context_end],
                'after': updated_content[context_start:context_end + len(hyperlink) - len(matched_text)],
                'position': start_pos
            })

            links_added += 1

            # Only add link once per keyword to avoid over-optimization
            break

    return updated_content, changes

def update_blog_post(blog_url):
    """Update a blog post with internal links"""
    print(f"\nProcessing blog: {blog_url[:80]}...")

    # Get blog post ID
    blog_id = get_blog_post_id_from_url(blog_url)
    if not blog_id:
        print(f"  ✗ Could not find blog post ID")
        return None

    # Get blog post content
    try:
        response = requests.get(f"{BLOG_API}/{blog_id}", headers=HEADERS, timeout=10)
        if response.status_code != 200:
            print(f"  ✗ Could not fetch blog post")
            return None

        blog_post = response.json()
        original_body = blog_post.get('body', '')

        if not original_body:
            print(f"  ✗ Blog post has no content")
            return None

        # Add links (only 1 link per page to same destination)
        updated_body, changes = add_links_to_content(
            original_body,
            TARGET_CATEGORY_URL,
            TARGET_KEYWORDS,
            max_links=1
        )

        if not changes:
            print(f"  ⚠ No suitable keywords found")
            return None

        # Update blog post
        update_data = {'body': updated_body}
        response = requests.put(
            f"{BLOG_API}/{blog_id}",
            headers=HEADERS,
            json=update_data,
            timeout=10
        )

        if response.status_code == 200:
            print(f"  ✓ Updated! Added {len(changes)} link(s)")
            return {
                'type': 'blog',
                'id': blog_id,
                'url': blog_url,
                'title': blog_post.get('title', ''),
                'changes': changes,
                'links_added': len(changes)
            }
        else:
            print(f"  ✗ Update failed: {response.status_code}")
            return None

    except Exception as e:
        print(f"  ✗ Error: {str(e)}")
        return None

def update_product(product_url):
    """Update a product with internal links"""
    print(f"\nProcessing product: {product_url[:80]}...")

    # Get product ID
    product_id = get_product_id_from_url(product_url)
    if not product_id:
        print(f"  ✗ Could not find product ID")
        return None

    # Get product details
    try:
        response = requests.get(f"{PRODUCT_API}/{product_id}", headers=HEADERS, timeout=10)
        if response.status_code != 200:
            print(f"  ✗ Could not fetch product")
            return None

        data = response.json()
        product = data.get('data', {})
        original_description = product.get('description', '')

        if not original_description:
            print(f"  ✗ Product has no description")
            return None

        # Add links (only 1 link per page to same destination)
        updated_description, changes = add_links_to_content(
            original_description,
            TARGET_CATEGORY_URL,
            TARGET_KEYWORDS,
            max_links=1  # Only ONE link per page
        )

        if not changes:
            print(f"  ⚠ No suitable keywords found")
            return None

        # Update product
        update_data = {'description': updated_description}
        response = requests.put(
            f"{PRODUCT_API}/{product_id}",
            headers=HEADERS,
            json={'data': update_data},
            timeout=10
        )

        if response.status_code == 200:
            print(f"  ✓ Updated! Added {len(changes)} link(s)")
            return {
                'type': 'product',
                'id': product_id,
                'url': product_url,
                'name': product.get('name', ''),
                'changes': changes,
                'links_added': len(changes)
            }
        else:
            print(f"  ✗ Update failed: {response.status_code}")
            return None

    except Exception as e:
        print(f"  ✗ Error: {str(e)}")
        return None

def implement_linking_plan():
    """Implement the linking plan for Custom Rolling Trays"""
    print("="*70)
    print("IMPLEMENTING INTERNAL LINKS - CUSTOM ROLLING TRAYS")
    print("="*70)

    # Load linking plan
    with open(linking_plan_file, 'r', encoding='utf-8') as f:
        linking_plan = json.load(f)

    category_data = linking_plan.get(TARGET_CATEGORY_URL)
    if not category_data:
        print("✗ Category not found in linking plan")
        return

    print(f"\nCategory: {category_data['category_name']}")
    print(f"Current Traffic: {category_data['traffic']}")
    print(f"Target URL: {TARGET_CATEGORY_URL}")
    print(f"\nBlog posts to update: {len(category_data['relevant_blogs'])}")
    print(f"Products to update: {len(category_data['relevant_products'])}")
    print("\nStarting updates...\n")

    # Track results
    successful_updates = []
    failed_updates = []

    # Update blog posts
    print("\n" + "="*70)
    print("UPDATING BLOG POSTS")
    print("="*70)

    for blog in category_data['relevant_blogs']:
        result = update_blog_post(blog['url'])
        if result:
            successful_updates.append(result)
        else:
            failed_updates.append({'type': 'blog', 'url': blog['url']})

    # Update products
    print("\n" + "="*70)
    print("UPDATING PRODUCTS")
    print("="*70)

    for product in category_data['relevant_products']:
        result = update_product(product['url'])
        if result:
            successful_updates.append(result)
        else:
            failed_updates.append({'type': 'product', 'url': product['url']})

    # Generate report
    generate_changes_log(successful_updates, failed_updates, category_data)

    # Print summary
    print("\n" + "="*70)
    print("IMPLEMENTATION COMPLETE")
    print("="*70)

    total_links = sum(item['links_added'] for item in successful_updates)

    print(f"\nSuccessful Updates: {len(successful_updates)}")
    print(f"Failed Updates: {len(failed_updates)}")
    print(f"Total Links Added: {total_links}")
    print(f"\nChanges log saved to: {changes_log_file}")
    print("="*70)

def generate_changes_log(successful_updates, failed_updates, category_data):
    """Generate a markdown log of all changes"""
    log = []
    log.append("# INTERNAL LINKING IMPLEMENTATION LOG")
    log.append(f"**Date:** {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    log.append(f"**Category:** {category_data['category_name']}")
    log.append(f"**Category URL:** {TARGET_CATEGORY_URL}")
    log.append("")

    # Summary
    total_links = sum(item['links_added'] for item in successful_updates)
    blog_updates = [u for u in successful_updates if u['type'] == 'blog']
    product_updates = [u for u in successful_updates if u['type'] == 'product']

    log.append("## SUMMARY")
    log.append("")
    log.append(f"- **Total Updates:** {len(successful_updates)}")
    log.append(f"- **Blog Posts Updated:** {len(blog_updates)}")
    log.append(f"- **Products Updated:** {len(product_updates)}")
    log.append(f"- **Total Links Added:** {total_links}")
    log.append(f"- **Failed Updates:** {len(failed_updates)}")
    log.append("")

    # Blog post changes
    if blog_updates:
        log.append("## BLOG POST UPDATES")
        log.append("")

        for update in blog_updates:
            log.append(f"### {update['title']}")
            log.append(f"**URL:** {update['url']}")
            log.append(f"**Links Added:** {update['links_added']}")
            log.append("")

            for i, change in enumerate(update['changes'], 1):
                log.append(f"#### Link {i}: '{change['keyword']}'")
                log.append(f"**Before:** ...{change['before']}...")
                log.append("")
                log.append(f"**After:** ...{change['after']}...")
                log.append("")

            log.append("---")
            log.append("")

    # Product changes
    if product_updates:
        log.append("## PRODUCT UPDATES")
        log.append("")

        for update in product_updates:
            log.append(f"### {update['name']}")
            log.append(f"**URL:** {update['url']}")
            log.append(f"**Links Added:** {update['links_added']}")
            log.append("")

            for i, change in enumerate(update['changes'], 1):
                log.append(f"#### Link {i}: '{change['keyword']}'")
                log.append(f"**Before:** ...{change['before']}...")
                log.append("")
                log.append(f"**After:** ...{change['after']}...")
                log.append("")

            log.append("---")
            log.append("")

    # Failed updates
    if failed_updates:
        log.append("## FAILED UPDATES")
        log.append("")
        for failed in failed_updates:
            log.append(f"- [{failed['type'].upper()}] {failed['url']}")
        log.append("")

    # Save log
    with open(changes_log_file, 'w', encoding='utf-8') as f:
        f.write('\n'.join(log))

if __name__ == '__main__':
    implement_linking_plan()
