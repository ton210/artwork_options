#!/usr/bin/env python3
"""
Find blog posts by keyword relevance and add internal links
Smarter approach: search for relevant content, not just matching URLs
"""
import requests
import json
import re

# BigCommerce API credentials
BC_STORE_HASH = 'tqjrceegho'
BC_ACCESS_TOKEN = 'lmg7prm3b0fxypwwaja27rtlvqejic0'

# API endpoint
BLOG_API = f'https://api.bigcommerce.com/stores/{BC_STORE_HASH}/v2/blog/posts'

# Headers
HEADERS = {
    'X-Auth-Token': BC_ACCESS_TOKEN,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
}

# Category configuration
CATEGORY_CONFIG = {
    'rolling-trays': {
        'name': 'Custom Rolling Trays',
        'url': 'https://munchmakers.com/product-category/custom-rolling-trays/',
        'keywords': ['rolling tray', 'rolling trays', 'weed tray', 'custom tray', 'smoke tray'],
        'anchor_keywords': ['custom rolling tray', 'custom rolling trays', 'rolling tray', 'rolling trays', 'weed tray'],
        'target_links': 10
    },
    'custom-ashtrays': {
        'name': 'Custom Ashtrays',
        'url': 'https://munchmakers.com/product-category/custom-ashtrays/',
        'keywords': ['ashtray', 'ashtrays', 'weed ashtray', 'custom ashtray', 'smoke ashtray'],
        'anchor_keywords': ['custom ashtray', 'custom ashtrays', 'ashtray', 'ashtrays', 'weed ashtray'],
        'target_links': 10
    },
    'custom-vape-pen': {
        'name': 'Custom Vape Pen',
        'url': 'https://munchmakers.com/product-category/custom-vape-pen/',
        'keywords': ['vape pen', 'vape pens', 'vape battery', 'custom vape', '510 battery'],
        'anchor_keywords': ['custom vape pen', 'vape pen', 'vape pens', 'vape battery', '510 battery'],
        'target_links': 10
    },
    'standard-grinders': {
        'name': 'Standard Grinders',
        'url': 'https://munchmakers.com/product-category/standard-grinders/',
        'keywords': ['grinder', 'grinders', 'weed grinder', 'herb grinder', 'cannabis grinder'],
        'anchor_keywords': ['grinder', 'grinders', 'weed grinder', 'herb grinder', 'cannabis grinder'],
        'target_links': 10
    },
    'custom-rolling-papers': {
        'name': 'Custom Rolling Papers',
        'url': 'https://munchmakers.com/product-category/custom-rolling-papers/',
        'keywords': ['rolling papers', 'rolling paper', 'custom papers', 'raw papers', 'joint papers'],
        'anchor_keywords': ['custom rolling papers', 'rolling papers', 'rolling paper', 'custom papers'],
        'target_links': 10
    },
    'custom-weed-stash-jars': {
        'name': 'Custom Weed Stash Jars',
        'url': 'https://munchmakers.com/product-category/custom-weed-stash-jars/',
        'keywords': ['stash jar', 'stash jars', 'weed jar', 'cannabis storage', 'smell proof jar', 'storage container'],
        'anchor_keywords': ['custom stash jars', 'weed stash jars', 'stash jar', 'cannabis storage', 'smell proof jars', 'weed storage'],
        'target_links': 10
    },
    'custom-lighters': {
        'name': 'Custom Lighters',
        'url': 'https://munchmakers.com/product-category/custom-lighters/',
        'keywords': ['lighter', 'lighters', 'custom lighter', 'branded lighter', 'bic lighter', 'torch lighter'],
        'anchor_keywords': ['custom lighters', 'branded lighters', 'lighters', 'custom lighter', 'promotional lighters', 'personalized lighters'],
        'target_links': 10
    },
    '4-piece-grinders': {
        'name': '4 Piece Grinders',
        'url': 'https://munchmakers.com/product-category/4-piece-grinders/',
        'keywords': ['4 piece grinder', '4 piece grinders', 'four piece grinder', 'kief catcher', 'grinder with kief'],
        'anchor_keywords': ['4 piece grinders', 'four piece grinder', 'grinders with kief catchers', '4-piece herb grinder', 'kief catcher grinders'],
        'target_links': 10
    },
    'cannabis-accessories': {
        'name': 'Cannabis Accessories',
        'url': 'https://munchmakers.com/product-category/cannabis-accessories/',
        'keywords': ['cannabis accessories', 'weed accessories', 'smoking accessories', 'marijuana accessories', 'cannabis products'],
        'anchor_keywords': ['cannabis accessories', 'smoking accessories', 'weed accessories', 'cannabis products', 'marijuana accessories'],
        'target_links': 10
    },
    '2-piece-grinders': {
        'name': '2 Piece Grinders',
        'url': 'https://munchmakers.com/product-category/2-piece-grinders/',
        'keywords': ['2 piece grinder', '2 piece grinders', 'two piece grinder', 'simple grinder', 'basic grinder'],
        'anchor_keywords': ['2 piece grinders', 'two piece grinder', '2-piece herb grinder', 'simple grinders', 'basic herb grinders'],
        'target_links': 10
    }
}

def get_all_blog_posts():
    """Get all blog posts from BigCommerce"""
    print("Fetching all blog posts from BigCommerce...")

    try:
        response = requests.get(f"{BLOG_API}?limit=250", headers=HEADERS, timeout=15)
        if response.status_code == 200:
            posts = response.json()
            print(f"✓ Found {len(posts)} blog posts")
            return posts
        else:
            print(f"✗ Failed to fetch blog posts: {response.status_code}")
            return []
    except Exception as e:
        print(f"✗ Error: {str(e)}")
        return []

def is_relevant_blog(blog_post, keywords):
    """Check if blog post is relevant based on keywords"""
    title = blog_post.get('title', '').lower()
    body = blog_post.get('body', '').lower()

    # Check if any keyword appears in title or body
    for keyword in keywords:
        if keyword.lower() in title or keyword.lower() in body:
            return True

    return False

def already_has_link(content, target_url):
    """Check if content already has a link to the target URL"""
    pattern = re.compile(r'<a\s+href=["\']' + re.escape(target_url) + r'["\']', re.IGNORECASE)
    return bool(pattern.search(content))

def add_link_to_content(content, target_url, anchor_keywords):
    """
    Add ONE link to content using the first available keyword
    Returns: (updated_content, keyword_used, success)
    """
    for keyword in anchor_keywords:
        # Create case-insensitive pattern
        pattern = re.compile(r'\b' + re.escape(keyword) + r'\b', re.IGNORECASE)

        match = pattern.search(content)
        if not match:
            continue

        matched_text = match.group()
        start_pos = match.start()

        # Check if already within a link or HTML tag
        before_match = content[:start_pos]

        # Count <a> tags before this position
        open_tags_before = before_match.count('<a ')
        close_tags_before = before_match.count('</a>')

        # If inside a link, skip
        if open_tags_before > close_tags_before:
            continue

        # Check if inside HTML tag
        last_open_bracket = before_match.rfind('<')
        last_close_bracket = before_match.rfind('>')
        if last_open_bracket > last_close_bracket:
            continue

        # Create hyperlink
        hyperlink = f'<a href="{target_url}">{matched_text}</a>'

        # Replace
        updated_content = (
            content[:start_pos] +
            hyperlink +
            content[start_pos + len(matched_text):]
        )

        return updated_content, matched_text, True

    return content, None, False

def update_blog_post(blog_post, target_url, anchor_keywords):
    """Update a blog post with a link"""
    blog_id = blog_post.get('id')
    title = blog_post.get('title', '')
    body = blog_post.get('body', '')

    # Add link
    updated_body, keyword_used, success = add_link_to_content(body, target_url, anchor_keywords)

    if not success:
        return None

    try:
        # Update via API
        update_data = {'body': updated_body}
        response = requests.put(
            f"{BLOG_API}/{blog_id}",
            headers=HEADERS,
            json=update_data,
            timeout=10
        )

        if response.status_code == 200:
            return {
                'id': blog_id,
                'title': title,
                'url': blog_post.get('url', ''),
                'keyword_used': keyword_used
            }
        else:
            print(f"    ✗ Update failed: {response.status_code}")
            return None

    except Exception as e:
        print(f"    ✗ Error updating: {str(e)}")
        return None

def find_and_link_category(category_key):
    """Find relevant blog posts and add links for a category"""
    config = CATEGORY_CONFIG[category_key]

    print("\n" + "="*70)
    print(f"CATEGORY: {config['name'].upper()}")
    print("="*70)
    print(f"Target URL: {config['url']}")
    print(f"Target links: {config['target_links']}")
    print("")

    # Get all blog posts
    all_posts = get_all_blog_posts()
    if not all_posts:
        return

    # Find relevant posts
    print(f"\nSearching for relevant blog posts...")
    relevant_posts = []

    for post in all_posts:
        if is_relevant_blog(post, config['keywords']):
            # Check if already has link
            body = post.get('body', '')
            if already_has_link(body, config['url']):
                # Skip - already has link
                continue

            relevant_posts.append(post)

    print(f"✓ Found {len(relevant_posts)} relevant blog posts without links")

    if len(relevant_posts) == 0:
        print("  All relevant blogs already have links!")
        return

    # Limit to target number
    posts_to_update = relevant_posts[:config['target_links']]

    print(f"\nAdding links to {len(posts_to_update)} blog posts...\n")

    # Update posts
    successful_updates = []

    for i, post in enumerate(posts_to_update, 1):
        title = post.get('title', '')[:60]
        print(f"[{i}/{len(posts_to_update)}] {title}...")

        result = update_blog_post(post, config['url'], config['anchor_keywords'])

        if result:
            print(f"  ✓ Added link using keyword: '{result['keyword_used']}'")
            successful_updates.append(result)
        else:
            print(f"  ✗ Could not add link")

    # Summary
    print("\n" + "="*70)
    print(f"CATEGORY COMPLETE: {config['name']}")
    print("="*70)
    print(f"Links added: {len(successful_updates)}")
    print("")

    return successful_updates

def main():
    """Main execution"""
    print("\n" + "="*70)
    print("SMART INTERNAL LINKING - ALL CATEGORIES")
    print("="*70)

    # Process all categories (Priority 1 & 2)
    categories_to_process = [
        'custom-weed-stash-jars',  # Priority 1
        'custom-lighters',          # Priority 1
        '4-piece-grinders',         # Priority 2
        'cannabis-accessories',     # Priority 2
        '2-piece-grinders'          # Priority 2
    ]

    all_results = {}

    for category_key in categories_to_process:
        results = find_and_link_category(category_key)
        all_results[category_key] = results if results else []

    # Final summary
    print("\n" + "="*70)
    print("FINAL SUMMARY - ALL CATEGORIES")
    print("="*70)

    total_links = 0
    for category_key, results in all_results.items():
        config = CATEGORY_CONFIG[category_key]
        print(f"\n{config['name']}: {len(results)} links added")
        total_links += len(results)

    print(f"\n✓ TOTAL LINKS ADDED: {total_links}")
    print("="*70)

if __name__ == '__main__':
    main()
