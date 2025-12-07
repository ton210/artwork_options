#!/usr/bin/env python3
"""
Analyze top category pages from SEMrush data
Build internal linking strategy focused on boosting category rankings
"""
import csv
from collections import defaultdict
import json

# Input file
semrush_csv = "/Users/tomernahumi/Documents/Plugins/SEM Rush/munchmakers.com-organic-UPDATED.csv"
url_status_json = "/Users/tomernahumi/Documents/Plugins/SEM Rush/url_status_report.json"

# Output file
output_file = "/Users/tomernahumi/Documents/Plugins/SEM Rush/top_categories_analysis.json"

def analyze_top_categories():
    """Analyze top category pages by traffic and keywords"""
    print("="*70)
    print("ANALYZING TOP CATEGORY PAGES")
    print("="*70)

    # Data structures
    category_data = defaultdict(lambda: {
        'total_traffic': 0,
        'keywords': [],
        'top_positions': [],
        'url': ''
    })

    blog_data = defaultdict(lambda: {
        'total_traffic': 0,
        'keywords': [],
        'url': ''
    })

    product_data = defaultdict(lambda: {
        'total_traffic': 0,
        'keywords': [],
        'url': ''
    })

    # Read SEMrush CSV
    print(f"\nReading SEMrush data...")
    with open(semrush_csv, 'r', encoding='utf-8') as f:
        reader = csv.DictReader(f)

        for row in reader:
            url = row.get('URL', '').strip()
            keyword = row.get('Keyword', '').strip()
            position = row.get('Position', '').strip()
            traffic = row.get('Traffic', '').strip()

            # Parse traffic as float
            try:
                traffic_val = float(traffic) if traffic else 0
            except:
                traffic_val = 0

            # Parse position
            try:
                position_val = int(float(position)) if position else 999
            except:
                position_val = 999

            # Categorize URLs
            if '/product-category/' in url:
                # It's a category page
                category_data[url]['total_traffic'] += traffic_val
                category_data[url]['keywords'].append({
                    'keyword': keyword,
                    'position': position_val,
                    'traffic': traffic_val
                })
                category_data[url]['url'] = url

                if position_val <= 10:
                    category_data[url]['top_positions'].append({
                        'keyword': keyword,
                        'position': position_val,
                        'traffic': traffic_val
                    })

            elif '/blog/' in url:
                # It's a blog post
                blog_data[url]['total_traffic'] += traffic_val
                blog_data[url]['keywords'].append({
                    'keyword': keyword,
                    'position': position_val,
                    'traffic': traffic_val
                })
                blog_data[url]['url'] = url

            elif '/product/' in url:
                # It's a product page
                product_data[url]['total_traffic'] += traffic_val
                product_data[url]['keywords'].append({
                    'keyword': keyword,
                    'position': position_val,
                    'traffic': traffic_val
                })
                product_data[url]['url'] = url

    # Sort categories by traffic
    sorted_categories = sorted(
        category_data.items(),
        key=lambda x: x[1]['total_traffic'],
        reverse=True
    )

    # Get top 10 categories
    top_categories = sorted_categories[:10]

    print(f"\n✓ Found {len(category_data)} category pages")
    print(f"✓ Found {len(blog_data)} blog posts")
    print(f"✓ Found {len(product_data)} product pages")

    # Display top categories
    print(f"\n{'='*70}")
    print("TOP 10 CATEGORY PAGES BY TRAFFIC")
    print(f"{'='*70}")

    for i, (url, data) in enumerate(top_categories, 1):
        # Extract category name from URL
        category_name = url.split('/product-category/')[-1].rstrip('/').replace('-', ' ').title()

        print(f"\n{i}. {category_name}")
        print(f"   URL: {url}")
        print(f"   Total Traffic: {data['total_traffic']:.1f}")
        print(f"   Total Keywords: {len(data['keywords'])}")
        print(f"   Top 10 Positions: {len(data['top_positions'])}")

        # Show top keywords
        top_keywords = sorted(data['keywords'], key=lambda x: x['traffic'], reverse=True)[:3]
        print(f"   Top Keywords:")
        for kw in top_keywords:
            print(f"     - '{kw['keyword']}' (Pos: {kw['position']}, Traffic: {kw['traffic']:.1f})")

    # Analyze linking opportunities
    print(f"\n{'='*70}")
    print("ANALYZING LINKING OPPORTUNITIES")
    print(f"{'='*70}")

    # For each top category, find relevant blog posts and products
    linking_plan = {}

    for url, data in top_categories:
        category_name = url.split('/product-category/')[-1].rstrip('/').replace('-', ' ').title()

        print(f"\nAnalyzing: {category_name}")

        # Extract main keywords from category
        category_keywords = set()
        for kw_data in data['keywords']:
            # Extract key terms from keyword
            terms = kw_data['keyword'].lower().split()
            category_keywords.update(terms)

        # Remove common words
        common_words = {'custom', 'wholesale', 'bulk', 'best', 'top', 'for', 'the', 'and', 'with', 'your', 'a', 'an', 'to', 'of', 'in', 'on'}
        category_keywords = {kw for kw in category_keywords if kw not in common_words and len(kw) > 3}

        print(f"  Key terms: {', '.join(list(category_keywords)[:5])}")

        # Find relevant blog posts
        relevant_blogs = []
        for blog_url, blog_data_item in blog_data.items():
            # Check if blog keywords overlap with category keywords
            blog_keywords = ' '.join([kw['keyword'].lower() for kw in blog_data_item['keywords']])

            # Score relevance
            relevance_score = sum(1 for term in category_keywords if term in blog_keywords)

            if relevance_score > 0:
                relevant_blogs.append({
                    'url': blog_url,
                    'traffic': blog_data_item['total_traffic'],
                    'relevance': relevance_score,
                    'title': blog_url.split('/blog/')[-1].rstrip('/').replace('-', ' ').title()[:60]
                })

        # Sort by relevance and traffic
        relevant_blogs.sort(key=lambda x: (x['relevance'], x['traffic']), reverse=True)

        # Find relevant products
        relevant_products = []
        for prod_url, prod_data_item in product_data.items():
            # Check if product keywords overlap with category keywords
            prod_keywords = ' '.join([kw['keyword'].lower() for kw in prod_data_item['keywords']])

            # Score relevance
            relevance_score = sum(1 for term in category_keywords if term in prod_keywords)

            if relevance_score > 0:
                relevant_products.append({
                    'url': prod_url,
                    'traffic': prod_data_item['total_traffic'],
                    'relevance': relevance_score,
                    'title': prod_url.split('/product/')[-1].rstrip('/').replace('-', ' ').title()[:60]
                })

        # Sort by relevance and traffic
        relevant_products.sort(key=lambda x: (x['relevance'], x['traffic']), reverse=True)

        print(f"  Found {len(relevant_blogs)} relevant blog posts")
        print(f"  Found {len(relevant_products)} relevant products")

        # Store in linking plan
        linking_plan[url] = {
            'category_name': category_name,
            'traffic': data['total_traffic'],
            'keywords': [kw['keyword'] for kw in data['keywords']],
            'top_keywords': [kw['keyword'] for kw in sorted(data['keywords'], key=lambda x: x['traffic'], reverse=True)[:5]],
            'relevant_blogs': relevant_blogs[:15],  # Top 15 most relevant blogs
            'relevant_products': relevant_products[:10],  # Top 10 most relevant products
            'total_potential_links': len(relevant_blogs[:15]) + len(relevant_products[:10])
        }

    # Save to JSON
    with open(output_file, 'w', encoding='utf-8') as f:
        json.dump(linking_plan, f, indent=2, ensure_ascii=False)

    print(f"\n{'='*70}")
    print("ANALYSIS COMPLETE")
    print(f"{'='*70}")
    print(f"\n✓ Linking plan saved to: {output_file}")
    print(f"\nSummary:")

    total_links = 0
    for url, plan in linking_plan.items():
        total_links += plan['total_potential_links']
        print(f"  {plan['category_name']}: {plan['total_potential_links']} potential internal links")

    print(f"\n  TOTAL POTENTIAL LINKS: {total_links}")
    print(f"{'='*70}\n")

    return linking_plan

if __name__ == '__main__':
    analyze_top_categories()
