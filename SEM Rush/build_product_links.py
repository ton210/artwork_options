#!/usr/bin/env python3
"""
Build internal links to top product pages
10-20 links per product from blog posts and other products
"""
import requests
import json
import re
import random

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

# Top 10 products configuration
TOP_PRODUCTS = {
    'custom-wooden-rolling-tray': {
        'name': 'Custom Wooden Rolling Tray',
        'url': 'https://munchmakers.com/product/custom-wooden-rolling-tray',
        'keywords': ['bamboo rolling tray', 'wooden rolling tray', 'wood tray', 'bamboo tray', 'natural rolling tray'],
        'anchor_variations': [
            'custom wooden rolling tray', 'bamboo rolling tray', 'wooden rolling tray',
            'natural bamboo tray', 'eco-friendly rolling tray', 'premium wooden tray',
            'sustainable rolling tray', 'bamboo smoking tray', 'wood weed tray',
            'check out our bamboo tray', 'shop wooden rolling trays', 'explore bamboo options',
            'premium bamboo rolling tray', 'natural wood tray', 'branded bamboo tray'
        ],
        'target_links': 15
    },
    'custom-big-grinder': {
        'name': 'Custom Big Grinder',
        'url': 'https://munchmakers.com/product/custom-big-grinder',
        'keywords': ['big grinder', 'large grinder', 'biggest grinder', 'large weed grinder', 'xl grinder'],
        'anchor_variations': [
            'large grinder', 'big weed grinder', 'XL grinder', 'oversized grinder',
            'large herb grinder', 'biggest grinder', 'extra large grinder', 'jumbo grinder',
            'premium large grinder', 'wholesale big grinders', 'check out our large grinder',
            'shop big grinders', 'explore XL grinder options', 'custom big grinder',
            'branded large grinder', 'commercial size grinder', 'bulk grinding solution'
        ],
        'target_links': 20
    },
    'easygrind': {
        'name': 'Easygrind',
        'url': 'https://munchmakers.com/product/easygrind',
        'keywords': ['weed dispenser', 'custom smoking', 'easy grinder', 'automatic grinder'],
        'anchor_variations': [
            'EasyGrind', 'automatic grinder', 'weed dispenser', 'custom smoking accessories',
            'innovative grinder', 'easy grinding solution', 'check out EasyGrind',
            'shop automatic grinders', 'convenient grinder', 'premium grinder'
        ],
        'target_links': 10
    },
    'custom-dab-mat': {
        'name': 'Custom Dab Mat',
        'url': 'https://munchmakers.com/product/custom-dab-mat',
        'keywords': ['dab mat', 'dab mats', 'silicone mat', 'concentrate mat'],
        'anchor_variations': [
            'custom dab mat', 'dab mats', 'silicone dab mat', 'concentrate mat',
            'branded dab mat', 'custom silicone mat', 'wax mat', 'dabbing mat',
            'check out our dab mats', 'shop dab mats', 'premium dab mat'
        ],
        'target_links': 10
    },
    'custom-rolling-tray-magnetic-lid': {
        'name': 'Rolling Tray With Magnetic Lid',
        'url': 'https://munchmakers.com/product/custom-rolling-tray-with-magnetic-lid',
        'keywords': ['rolling tray magnetic', 'tray with lid', 'magnetic lid tray', 'rolling tray lid'],
        'anchor_variations': [
            'rolling tray with magnetic lid', 'magnetic lid rolling tray', 'secure rolling tray',
            'covered rolling tray', 'tray with storage lid', 'check out our magnetic tray',
            'shop magnetic rolling trays', 'premium magnetic tray', 'custom magnetic tray'
        ],
        'target_links': 10
    },
    'custom-glow-dark-ashtray': {
        'name': 'Custom Glow In Dark Ashtray',
        'url': 'https://munchmakers.com/product/custom-glow-in-dark-ashtray',
        'keywords': ['glow dark ashtray', 'glow ashtray', 'light up ashtray', 'luminous ashtray'],
        'anchor_variations': [
            'glow in the dark ashtray', 'luminous ashtray', 'light-up ashtray',
            'glow ashtray', 'novelty ashtray', 'check out our glow ashtray',
            'shop glow in dark ashtrays', 'unique ashtray'
        ],
        'target_links': 10
    },
    'custom-ceramic-ashtray': {
        'name': 'Custom Ceramic Ashtray',
        'url': 'https://munchmakers.com/product/custom-ceramic-ashtray',
        'keywords': ['ceramic ashtray', 'ceramic ash tray', 'custom ceramic', 'ashtray ceramic'],
        'anchor_variations': [
            'ceramic ashtray', 'custom ceramic ashtray', 'premium ceramic ashtray',
            'branded ceramic ashtray', 'ceramic ash tray', 'check out our ceramic ashtray',
            'shop ceramic ashtrays', 'elegant ceramic ashtray', 'custom ceramic ash tray'
        ],
        'target_links': 10
    },
    'custom-glow-dark-grinder': {
        'name': 'Custom Glow In The Dark Grinder',
        'url': 'https://munchmakers.com/product/custom-glow-in-the-dark-grinder',
        'keywords': ['glow dark grinder', 'glow grinder', 'luminous grinder', 'light up grinder'],
        'anchor_variations': [
            'glow in the dark grinder', 'luminous grinder', 'glow grinder',
            'novelty grinder', 'light-up grinder', 'check out our glow grinder',
            'shop glow in dark grinders', 'unique grinder'
        ],
        'target_links': 10
    },
    'custom-folded-rolling-tray': {
        'name': 'Custom Folded Rolling Tray',
        'url': 'https://munchmakers.com/product/custom-folded-rolling-tray',
        'keywords': ['folded tray', 'foldable tray', 'folding rolling tray', 'portable tray'],
        'anchor_variations': [
            'foldable rolling tray', 'folding rolling tray', 'portable rolling tray',
            'travel rolling tray', 'collapsible tray', 'check out our foldable tray',
            'shop foldable trays', 'compact rolling tray'
        ],
        'target_links': 10
    },
    'custom-square-joint-case': {
        'name': 'Custom Square Joint Case',
        'url': 'https://munchmakers.com/product/custom-square-joint-case',
        'keywords': ['joint case', 'joint holder', 'joint container', 'pre roll case', 'smell proof case'],
        'anchor_variations': [
            'custom joint case', 'joint holder', 'pre-roll case', 'joint storage case',
            'smell proof joint case', 'joint container', 'branded joint case',
            'check out our joint case', 'shop joint cases', 'premium joint holder',
            'doob tube case', 'joint protection case'
        ],
        'target_links': 15
    }
}

def get_all_blog_posts():
    """Get all blog posts"""
    try:
        response = requests.get(f"{BLOG_API}?limit=250", headers=HEADERS, timeout=15)
        if response.status_code == 200:
            return response.json()
        return []
    except:
        return []

def get_all_products():
    """Get all products"""
    try:
        response = requests.get(f"{PRODUCT_API}?limit=250", headers=HEADERS, timeout=15)
        if response.status_code == 200:
            data = response.json()
            return data.get('data', [])
        return []
    except:
        return []

def is_relevant_content(content, keywords):
    """Check if content is relevant based on keywords"""
    content_lower = content.lower()
    for keyword in keywords:
        if keyword.lower() in content_lower:
            return True
    return False

def already_has_link(content, target_url):
    """Check if content already has a link to target URL"""
    pattern = re.compile(r'<a\s+href=["\']' + re.escape(target_url) + r'["\']', re.IGNORECASE)
    return bool(pattern.search(content))

def add_link_to_content(content, target_url, search_keywords):
    """
    Add ONE link by hyperlinking existing text
    Searches for search_keywords in content and hyperlinks the matched text
    Returns: (updated_content, anchor_text_used, success)
    """
    # Try to find any of the search keywords in the content
    for keyword in search_keywords:
        # Use word boundaries for exact matches
        pattern = re.compile(r'\b' + re.escape(keyword) + r's?\b', re.IGNORECASE)
        match = pattern.search(content)

        if not match:
            continue

        matched_text = match.group()
        start_pos = match.start()

        # Check if inside a link or tag
        before_match = content[:start_pos]
        open_tags = before_match.count('<a ')
        close_tags = before_match.count('</a>')
        if open_tags > close_tags:
            continue

        last_open = before_match.rfind('<')
        last_close = before_match.rfind('>')
        if last_open > last_close:
            continue

        # Create hyperlink using the MATCHED text as anchor (natural!)
        hyperlink = f'<a href="{target_url}">{matched_text}</a>'

        # Replace the matched text with hyperlink
        updated_content = (
            content[:start_pos] +
            hyperlink +
            content[start_pos + len(matched_text):]
        )

        return updated_content, matched_text, True

    return content, None, False

def update_blog_post_with_link(blog_post, target_url, search_keywords):
    """Update a blog post with a product link"""
    blog_id = blog_post.get('id')
    body = blog_post.get('body', '')

    updated_body, anchor_used, success = add_link_to_content(body, target_url, search_keywords)
    if not success:
        return None

    try:
        response = requests.put(
            f"{BLOG_API}/{blog_id}",
            headers=HEADERS,
            json={'body': updated_body},
            timeout=10
        )
        if response.status_code == 200:
            return {
                'type': 'blog',
                'id': blog_id,
                'title': blog_post.get('title', ''),
                'anchor': anchor_used
            }
    except:
        pass
    return None

def update_product_with_link(product, target_url, search_keywords):
    """Update a product description with a link to another product"""
    product_id = product.get('id')
    description = product.get('description', '')

    updated_description, anchor_used, success = add_link_to_content(description, target_url, search_keywords)
    if not success:
        return None

    try:
        response = requests.put(
            f"{PRODUCT_API}/{product_id}",
            headers=HEADERS,
            json={'data': {'description': updated_description}},
            timeout=10
        )
        if response.status_code == 200:
            return {
                'type': 'product',
                'id': product_id,
                'name': product.get('name', ''),
                'anchor': anchor_used
            }
    except:
        pass
    return None

def build_links_to_product(product_key, all_blogs, all_products):
    """Build 10-20 internal links to a single product"""
    config = TOP_PRODUCTS[product_key]

    print("\n" + "="*70)
    print(f"PRODUCT: {config['name'].upper()}")
    print("="*70)
    print(f"Target URL: {config['url']}")
    print(f"Target links: {config['target_links']}")

    successful_updates = []
    anchor_variations = config['anchor_variations'].copy()
    random.shuffle(anchor_variations)

    # Find relevant blog posts
    print(f"\nSearching for relevant blog posts...")
    relevant_blogs = []
    for blog in all_blogs:
        title_and_body = (blog.get('title', '') + ' ' + blog.get('body', '')).lower()
        if is_relevant_content(title_and_body, config['keywords']):
            if not already_has_link(blog.get('body', ''), config['url']):
                relevant_blogs.append(blog)

    print(f"✓ Found {len(relevant_blogs)} relevant blogs without links")

    # Find relevant products
    print(f"Searching for relevant products...")
    relevant_products = []
    for product in all_products:
        # Skip the target product itself
        product_url = product.get('custom_url', {}).get('url', '')
        if config['url'].endswith(product_url) or product_url in config['url']:
            continue

        name_and_desc = (product.get('name', '') + ' ' + product.get('description', '')).lower()
        if is_relevant_content(name_and_desc, config['keywords']):
            if not already_has_link(product.get('description', ''), config['url']):
                relevant_products.append(product)

    print(f"✓ Found {len(relevant_products)} relevant products without links")

    # Calculate how many from each source
    target_total = config['target_links']
    target_from_blogs = int(target_total * 0.7)  # 70% from blogs
    target_from_products = target_total - target_from_blogs  # 30% from products

    # Limit to available
    blogs_to_update = relevant_blogs[:target_from_blogs]
    products_to_update = relevant_products[:target_from_products]

    print(f"\nWill add links from:")
    print(f"  - {len(blogs_to_update)} blog posts")
    print(f"  - {len(products_to_update)} products")
    print(f"  - Total: {len(blogs_to_update) + len(products_to_update)} links")
    print("")

    # Update blog posts
    print("Adding links from blog posts...")
    for i, blog in enumerate(blogs_to_update, 1):
        title = blog.get('title', '')[:50]
        print(f"  [{i}/{len(blogs_to_update)}] {title}... ", end='')

        result = update_blog_post_with_link(blog, config['url'], config['keywords'])
        if result:
            print(f"✓ '{result['anchor']}'")
            successful_updates.append(result)
        else:
            print(f"✗")

    # Update products
    if products_to_update:
        print(f"\nAdding links from product pages...")

        for i, product in enumerate(products_to_update, 1):
            name = product.get('name', '')[:50]
            print(f"  [{i}/{len(products_to_update)}] {name}... ", end='')

            result = update_product_with_link(product, config['url'], config['keywords'])
            if result:
                print(f"✓ '{result['anchor']}'")
                successful_updates.append(result)
            else:
                print(f"✗")

    # Summary
    print("\n" + "="*70)
    print(f"COMPLETE: {config['name']}")
    print("="*70)
    print(f"Links added: {len(successful_updates)}")
    print(f"  - From blogs: {len([u for u in successful_updates if u['type'] == 'blog'])}")
    print(f"  - From products: {len([u for u in successful_updates if u['type'] == 'product'])}")
    print("")

    return successful_updates

def main():
    """Build links to all top 10 products"""
    print("\n" + "="*70)
    print("BUILDING INTERNAL LINKS TO TOP 10 PRODUCTS")
    print("="*70)

    # Fetch all content once
    print("\nFetching all blog posts and products...")
    all_blogs = get_all_blog_posts()
    all_products = get_all_products()

    print(f"✓ Loaded {len(all_blogs)} blog posts")
    print(f"✓ Loaded {len(all_products)} products")

    # Process each product
    all_results = {}

    for product_key in TOP_PRODUCTS.keys():
        results = build_links_to_product(product_key, all_blogs, all_products)
        all_results[product_key] = results

    # Final summary
    print("\n" + "="*70)
    print("FINAL SUMMARY - PRODUCT INTERNAL LINKING")
    print("="*70)

    total_links = 0
    for product_key, results in all_results.items():
        config = TOP_PRODUCTS[product_key]
        blog_links = len([r for r in results if r['type'] == 'blog'])
        product_links = len([r for r in results if r['type'] == 'product'])

        print(f"\n{config['name']}:")
        print(f"  Total links: {len(results)} (Blogs: {blog_links}, Products: {product_links})")

        total_links += len(results)

    print(f"\n{'='*70}")
    print(f"✓ TOTAL PRODUCT LINKS ADDED: {total_links}")
    print(f"{'='*70}\n")

if __name__ == '__main__':
    main()
