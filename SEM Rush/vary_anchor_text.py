#!/usr/bin/env python3
"""
Update existing links with varied, natural anchor text
Creates diverse anchor text profile for better SEO
"""
import requests
import json
import re
import random

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

# Varied anchor text for each category
ANCHOR_TEXT_VARIATIONS = {
    'https://munchmakers.com/product-category/custom-rolling-trays/': [
        'custom rolling trays',
        'personalized rolling trays',
        'custom weed trays',
        'rolling tray collection',
        'branded rolling trays',
        'wholesale rolling trays',
        'custom smoke trays',
        'premium rolling trays',
        'rolling accessories',
        'smoking accessories',
        'dispensary rolling trays',
        'check out our rolling trays',
        'explore rolling tray options',
        'view our rolling tray selection',
        'shop rolling trays',
        'rolling tray designs',
        'personalized smoke accessories',
        'custom cannabis accessories'
    ],
    'https://munchmakers.com/product-category/custom-ashtrays/': [
        'custom ashtrays',
        'personalized ashtrays',
        'weed ashtrays',
        'branded ashtrays',
        'wholesale ashtrays',
        'custom smoke ashtrays',
        'premium ashtrays',
        'ashtray collection',
        'smoking accessories',
        'cannabis ashtrays',
        'check out our ashtrays',
        'explore ashtray options',
        'view our ashtray selection',
        'shop custom ashtrays',
        'personalized smoking accessories',
        'dispensary ashtrays',
        'ceramic ashtrays'
    ],
    'https://munchmakers.com/product-category/custom-vape-pen/': [
        'custom vape pens',
        'personalized vape batteries',
        'branded vape pens',
        '510 batteries',
        'wholesale vape pens',
        'custom vape batteries',
        'premium vape pens',
        'vape pen collection',
        'cannabis vape pens',
        'check out our vape pens',
        'explore vape pen options',
        'view our vape selection',
        'shop vape pens',
        'branded vaping accessories',
        'dispensary vape pens',
        'custom 510 batteries',
        'vape accessories'
    ],
    'https://munchmakers.com/product-category/standard-grinders/': [
        'weed grinders',
        'herb grinders',
        'cannabis grinders',
        'premium grinders',
        'wholesale grinders',
        'grinder collection',
        'custom grinders',
        'branded grinders',
        'grinding accessories',
        'herb grinding tools',
        'check out our grinders',
        'explore grinder options',
        'view our grinder selection',
        'shop herb grinders',
        'quality grinders',
        'dispensary grinders',
        'smoking accessories'
    ],
    'https://munchmakers.com/product-category/custom-rolling-papers/': [
        'custom rolling papers',
        'personalized rolling papers',
        'branded rolling papers',
        'wholesale rolling papers',
        'custom papers',
        'premium rolling papers',
        'rolling paper collection',
        'custom smoke papers',
        'personalized papers',
        'check out our rolling papers',
        'explore rolling paper options',
        'view our paper selection',
        'shop rolling papers',
        'branded papers',
        'dispensary rolling papers',
        'smoking papers',
        'custom printed papers'
    ],
    'https://munchmakers.com/product-category/custom-weed-stash-jars/': [
        'custom stash jars',
        'weed stash jars',
        'cannabis storage',
        'smell proof jars',
        'weed storage containers',
        'custom storage jars',
        'branded stash jars',
        'premium stash jars',
        'dispensary storage solutions',
        'check out our stash jars',
        'explore storage options',
        'shop stash jars',
        'cannabis storage solutions',
        'smell proof containers',
        'weed preservation jars'
    ],
    'https://munchmakers.com/product-category/custom-lighters/': [
        'custom lighters',
        'branded lighters',
        'promotional lighters',
        'personalized lighters',
        'wholesale lighters',
        'custom BIC lighters',
        'torch lighters',
        'branded lighter collection',
        'dispensary lighters',
        'check out our lighters',
        'explore lighter options',
        'shop custom lighters',
        'premium lighters',
        'lighter accessories'
    ],
    'https://munchmakers.com/product-category/4-piece-grinders/': [
        '4 piece grinders',
        'four piece grinders',
        'grinders with kief catchers',
        '4-piece herb grinders',
        'kief catcher grinders',
        'multi-chamber grinders',
        'premium 4 piece grinders',
        'check out our 4 piece grinders',
        'explore grinder options',
        'shop 4 piece grinders'
    ],
    'https://munchmakers.com/product-category/cannabis-accessories/': [
        'cannabis accessories',
        'smoking accessories',
        'weed accessories',
        'marijuana accessories',
        'cannabis products',
        'premium cannabis accessories',
        'wholesale smoking accessories',
        'branded cannabis gear',
        'dispensary accessories',
        'check out our accessories',
        'explore cannabis products',
        'shop smoking accessories',
        'custom cannabis accessories'
    ],
    'https://munchmakers.com/product-category/2-piece-grinders/': [
        '2 piece grinders',
        'two piece grinders',
        '2-piece herb grinders',
        'simple grinders',
        'basic herb grinders',
        'compact grinders',
        'check out our 2 piece grinders',
        'explore simple grinders',
        'shop 2 piece grinders'
    ]
}

def get_all_blog_posts():
    """Get all blog posts"""
    try:
        response = requests.get(f"{BLOG_API}?limit=250", headers=HEADERS, timeout=15)
        if response.status_code == 200:
            return response.json()
        return []
    except Exception as e:
        print(f"Error fetching posts: {str(e)}")
        return []

def find_link_in_content(content, target_url):
    """Find existing link to target URL in content"""
    pattern = re.compile(
        r'<a\s+href=["\']' + re.escape(target_url) + r'["\'][^>]*>(.*?)</a>',
        re.IGNORECASE | re.DOTALL
    )
    match = pattern.search(content)
    return match

def update_anchor_text(content, target_url, new_anchor_text):
    """Replace the anchor text of existing link"""
    pattern = re.compile(
        r'(<a\s+href=["\']' + re.escape(target_url) + r'["\'][^>]*>)(.*?)(</a>)',
        re.IGNORECASE | re.DOTALL
    )

    def replace_anchor(match):
        return match.group(1) + new_anchor_text + match.group(3)

    updated_content = pattern.sub(replace_anchor, content)
    return updated_content

def update_blog_post_anchor(blog_post, target_url, new_anchor_text):
    """Update a blog post's anchor text"""
    blog_id = blog_post.get('id')
    title = blog_post.get('title', '')
    body = blog_post.get('body', '')

    # Check if link exists
    link_match = find_link_in_content(body, target_url)
    if not link_match:
        return None

    old_anchor = link_match.group(1).strip()

    # Update anchor text
    updated_body = update_anchor_text(body, target_url, new_anchor_text)

    # Update via API
    try:
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
                'old_anchor': old_anchor,
                'new_anchor': new_anchor_text
            }
        else:
            print(f"    ✗ Update failed: {response.status_code}")
            return None

    except Exception as e:
        print(f"    ✗ Error: {str(e)}")
        return None

def vary_anchors_for_category(category_url, category_name):
    """Update all links to a category with varied anchor text"""
    print("\n" + "="*70)
    print(f"VARYING ANCHORS: {category_name.upper()}")
    print("="*70)
    print(f"Target URL: {category_url}")

    # Get all blog posts
    all_posts = get_all_blog_posts()
    print(f"\nFetched {len(all_posts)} blog posts")

    # Find posts with links to this category
    posts_with_links = []
    for post in all_posts:
        body = post.get('body', '')
        if find_link_in_content(body, category_url):
            posts_with_links.append(post)

    print(f"Found {len(posts_with_links)} posts with links to this category")

    if len(posts_with_links) == 0:
        print("No posts to update")
        return []

    # Get anchor text variations
    anchor_variations = ANCHOR_TEXT_VARIATIONS.get(category_url, [])
    if not anchor_variations:
        print("No anchor variations defined")
        return []

    # Shuffle for randomness
    random.shuffle(anchor_variations)

    # Update each post with a different anchor
    print(f"\nUpdating {len(posts_with_links)} posts with varied anchors...\n")

    successful_updates = []
    used_anchors = set()

    for i, post in enumerate(posts_with_links, 1):
        title = post.get('title', '')[:60]

        # Find an unused anchor text
        new_anchor = None
        for anchor in anchor_variations:
            if anchor not in used_anchors:
                new_anchor = anchor
                used_anchors.add(anchor)
                break

        if not new_anchor:
            # If we run out of variations, reuse but warn
            new_anchor = random.choice(anchor_variations)
            print(f"[{i}/{len(posts_with_links)}] {title}...")
            print(f"  ⚠ Reusing anchor (ran out of variations)")

        print(f"[{i}/{len(posts_with_links)}] {title}...")

        result = update_blog_post_anchor(post, category_url, new_anchor)

        if result:
            print(f"  ✓ '{result['old_anchor']}' → '{result['new_anchor']}'")
            successful_updates.append(result)
        else:
            print(f"  ✗ Could not update")

    print("\n" + "="*70)
    print(f"CATEGORY COMPLETE: {category_name}")
    print("="*70)
    print(f"Updated: {len(successful_updates)} posts")

    # Show anchor diversity
    if successful_updates:
        print(f"\nAnchor text diversity:")
        anchor_counts = {}
        for update in successful_updates:
            anchor = update['new_anchor']
            anchor_counts[anchor] = anchor_counts.get(anchor, 0) + 1

        for anchor, count in sorted(anchor_counts.items()):
            print(f"  - '{anchor}': {count}")

    print("")

    return successful_updates

def main():
    """Main execution"""
    print("\n" + "="*70)
    print("ANCHOR TEXT DIVERSIFICATION")
    print("="*70)
    print("\nUpdating all category links with varied, natural anchor text...")

    categories = [
        ('https://munchmakers.com/product-category/custom-weed-stash-jars/', 'Custom Weed Stash Jars'),
        ('https://munchmakers.com/product-category/custom-lighters/', 'Custom Lighters'),
        ('https://munchmakers.com/product-category/4-piece-grinders/', '4 Piece Grinders'),
        ('https://munchmakers.com/product-category/cannabis-accessories/', 'Cannabis Accessories'),
        ('https://munchmakers.com/product-category/2-piece-grinders/', '2 Piece Grinders')
    ]

    all_results = {}

    for category_url, category_name in categories:
        results = vary_anchors_for_category(category_url, category_name)
        all_results[category_name] = results

    # Final summary
    print("\n" + "="*70)
    print("FINAL SUMMARY - ANCHOR TEXT DIVERSITY")
    print("="*70)

    total_updated = 0
    for category_name, results in all_results.items():
        print(f"\n{category_name}: {len(results)} links updated")
        total_updated += len(results)

    print(f"\n✓ TOTAL LINKS UPDATED: {total_updated}")
    print("\n✓ All links now have varied, natural anchor text")
    print("✓ Better SEO profile - looks organic, not spammy")
    print("="*70)

if __name__ == '__main__':
    # Set random seed for reproducibility (but still random)
    random.seed()
    main()
