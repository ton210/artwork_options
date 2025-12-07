#!/usr/bin/env python3
"""
Create detailed implementation plan for internal linking
Shows exactly which text will be hyperlinked on which pages
"""
import json
import requests

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
implementation_plan_file = "/Users/tomernahumi/Documents/Plugins/SEM Rush/implementation_plan.md"

def get_blog_post_id_from_url(blog_url):
    """Get blog post ID from URL by searching the API"""
    try:
        # Get all blog posts
        response = requests.get(f"{BLOG_API}?limit=250", headers=HEADERS, timeout=10)

        if response.status_code == 200:
            posts = response.json()

            for post in posts:
                if post.get('url') in blog_url:
                    return post.get('id'), post.get('title')

        return None, None

    except Exception as e:
        print(f"Error getting blog post ID: {str(e)}")
        return None, None

def create_implementation_plan():
    """Create detailed plan for implementing internal links"""

    print("="*70)
    print("CREATING IMPLEMENTATION PLAN")
    print("="*70)

    # Load linking plan
    with open(linking_plan_file, 'r', encoding='utf-8') as f:
        linking_plan = json.load(f)

    # Focus on top 5 categories
    sorted_categories = sorted(
        linking_plan.items(),
        key=lambda x: x[1]['traffic'],
        reverse=True
    )[:5]

    # Create markdown report
    report = []
    report.append("# INTERNAL LINKING IMPLEMENTATION PLAN")
    report.append("")
    report.append("## Overview")
    report.append("")
    report.append("This plan details the internal links that will be added to boost category page rankings.")
    report.append("")
    report.append("**Strategy:** Add 3-5 contextual links from high-traffic blog posts and relevant products to each category page.")
    report.append("")
    report.append(f"**Total Categories:** {len(sorted_categories)}")
    report.append(f"**Estimated Total Links:** {len(sorted_categories) * 25}")
    report.append("")
    report.append("---")
    report.append("")

    # Process each category
    for i, (category_url, data) in enumerate(sorted_categories, 1):
        print(f"\nProcessing: {data['category_name']}")

        report.append(f"## CATEGORY {i}: {data['category_name'].upper()}")
        report.append("")
        report.append(f"**Category URL:** `{category_url}`")
        report.append(f"**Current Traffic:** {data['traffic']:.1f}")
        report.append(f"**Total Keywords Ranking:** {len(data['keywords'])}")
        report.append("")

        # Top keywords to use as anchor text
        report.append("### Target Keywords (for anchor text):")
        report.append("")
        for kw in data['top_keywords'][:5]:
            report.append(f"- `{kw}`")
        report.append("")

        # Blog posts that will link here
        report.append("### Blog Posts That Will Link Here:")
        report.append("")
        report.append(f"**Total:** {len(data['relevant_blogs'])} blog posts")
        report.append("")

        # Show top 10 blogs
        report.append("#### Top 10 Most Relevant Blog Posts:")
        report.append("")
        report.append("| # | Blog Post Title | Traffic | Relevance |")
        report.append("|---|----------------|---------|-----------|")

        for j, blog in enumerate(data['relevant_blogs'][:10], 1):
            title = blog['title'][:50]
            report.append(f"| {j} | {title}... | {blog['traffic']:.1f} | {blog['relevance']} |")

        report.append("")

        # Products that will link here
        report.append("### Products That Will Link Here:")
        report.append("")
        report.append(f"**Total:** {len(data['relevant_products'])} products")
        report.append("")

        # Show top 10 products
        report.append("#### Top 10 Most Relevant Products:")
        report.append("")
        report.append("| # | Product Name | Traffic | Relevance |")
        report.append("|---|-------------|---------|-----------|")

        for j, product in enumerate(data['relevant_products'][:10], 1):
            title = product['title'][:50]
            report.append(f"| {j} | {title}... | {product['traffic']:.1f} | {product['relevance']} |")

        report.append("")
        report.append("---")
        report.append("")

    # Summary
    report.append("## IMPLEMENTATION APPROACH")
    report.append("")
    report.append("### For Each Blog Post:")
    report.append("1. Scan content for target keywords (e.g., 'custom rolling tray', 'rolling trays')")
    report.append("2. Find first 3-5 instances of these keywords")
    report.append("3. Add hyperlink: `<a href='[category-url]'>[keyword]</a>`")
    report.append("4. Update via BigCommerce Blog API (v2)")
    report.append("")
    report.append("### For Each Product:")
    report.append("1. Scan description for target keywords")
    report.append("2. Find first 2-3 instances")
    report.append("3. Add hyperlink to category page")
    report.append("4. Update via BigCommerce Catalog API (v3)")
    report.append("")
    report.append("### Safety Measures:")
    report.append("- ✓ Only hyperlink existing text (no new content added)")
    report.append("- ✓ Maximum 5 links per page to avoid over-optimization")
    report.append("- ✓ Links are contextually relevant")
    report.append("- ✓ All changes are reversible")
    report.append("- ✓ Preview each change before applying")
    report.append("")
    report.append("## NEXT STEPS")
    report.append("")
    report.append("**Option 1:** Start with Category #1 (Custom Rolling Trays) - implement all 25 links")
    report.append("**Option 2:** Do a test run with 5 blog posts first to validate approach")
    report.append("**Option 3:** Show specific link placements for approval before implementing")
    report.append("")

    # Save report
    with open(implementation_plan_file, 'w', encoding='utf-8') as f:
        f.write('\n'.join(report))

    print(f"\n{'='*70}")
    print("IMPLEMENTATION PLAN CREATED")
    print(f"{'='*70}")
    print(f"\nReport saved to: {implementation_plan_file}")
    print("\nTop 5 Categories for Internal Linking:")

    for i, (category_url, data) in enumerate(sorted_categories, 1):
        print(f"  {i}. {data['category_name']}")
        print(f"     - Traffic: {data['traffic']:.1f}")
        print(f"     - Blog posts linking: {len(data['relevant_blogs'])}")
        print(f"     - Products linking: {len(data['relevant_products'])}")
        print(f"     - Total potential links: {len(data['relevant_blogs']) + len(data['relevant_products'])}")

    print(f"\n{'='*70}\n")

if __name__ == '__main__':
    create_implementation_plan()
