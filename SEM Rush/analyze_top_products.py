#!/usr/bin/env python3
"""
Analyze top-performing product pages from SEMrush data
Find products that deserve more internal links
"""
import csv
from collections import defaultdict

# Input file
semrush_csv = "/Users/tomernahumi/Documents/Plugins/SEM Rush/munchmakers.com-organic-UPDATED.csv"

def analyze_top_products():
    """Analyze product pages by traffic and keywords"""
    print("="*70)
    print("ANALYZING TOP PRODUCT PAGES")
    print("="*70)

    # Data structures
    product_data = defaultdict(lambda: {
        'total_traffic': 0,
        'keywords': [],
        'top_positions': [],
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

            # Only process product pages
            if '/product/' not in url:
                continue

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

            # Aggregate data
            product_data[url]['total_traffic'] += traffic_val
            product_data[url]['keywords'].append({
                'keyword': keyword,
                'position': position_val,
                'traffic': traffic_val
            })
            product_data[url]['url'] = url

            if position_val <= 10:
                product_data[url]['top_positions'].append({
                    'keyword': keyword,
                    'position': position_val,
                    'traffic': traffic_val
                })

    # Sort products by traffic
    sorted_products = sorted(
        product_data.items(),
        key=lambda x: x[1]['total_traffic'],
        reverse=True
    )

    print(f"\n✓ Found {len(product_data)} product pages")

    # Display top products
    print(f"\n{'='*70}")
    print("TOP 20 PRODUCT PAGES BY TRAFFIC")
    print(f"{'='*70}")

    for i, (url, data) in enumerate(sorted_products[:20], 1):
        # Extract product name from URL
        product_name = url.split('/product/')[-1].rstrip('/').replace('-', ' ').title()

        print(f"\n{i}. {product_name}")
        print(f"   URL: {url}")
        print(f"   Total Traffic: {data['total_traffic']:.1f}")
        print(f"   Total Keywords: {len(data['keywords'])}")
        print(f"   Top 10 Positions: {len(data['top_positions'])}")

        # Show top keywords
        top_keywords = sorted(data['keywords'], key=lambda x: x['traffic'], reverse=True)[:3]
        print(f"   Top Keywords:")
        for kw in top_keywords:
            print(f"     - '{kw['keyword']}' (Pos: {kw['position']}, Traffic: {kw['traffic']:.1f})")

    # Identify products with high potential
    print(f"\n{'='*70}")
    print("HIGH POTENTIAL PRODUCTS (Good rankings, need more links)")
    print(f"{'='*70}")

    high_potential = []
    for url, data in sorted_products[:30]:  # Top 30 products
        # Products with good traffic OR lots of keywords OR good positions
        if (data['total_traffic'] > 10 or
            len(data['keywords']) > 20 or
            len(data['top_positions']) > 3):

            high_potential.append({
                'url': url,
                'name': url.split('/product/')[-1].rstrip('/').replace('-', ' ').title(),
                'traffic': data['total_traffic'],
                'keywords': len(data['keywords']),
                'top_positions': len(data['top_positions'])
            })

    print(f"\nFound {len(high_potential)} high-potential products:\n")

    for i, product in enumerate(high_potential[:15], 1):
        print(f"{i}. {product['name']}")
        print(f"   Traffic: {product['traffic']:.1f} | Keywords: {product['keywords']} | Top 10 Ranks: {product['top_positions']}")
        print(f"   URL: {product['url']}")
        print("")

    # Recommendations
    print(f"{'='*70}")
    print("RECOMMENDATIONS")
    print(f"{'='*70}")

    print(f"\n🎯 PRIORITY PRODUCTS FOR INTERNAL LINKING:\n")

    top_5 = high_potential[:5]
    for i, product in enumerate(top_5, 1):
        print(f"{i}. **{product['name']}** ({product['traffic']:.0f} traffic)")
        print(f"   - Already ranking well with {product['top_positions']} top-10 positions")
        print(f"   - {product['keywords']} total keywords = lots of long-tail potential")
        print(f"   - Strategy: Add 5-10 internal links from relevant blog posts")
        print("")

    print("\n💡 WHY LINK TO THESE PRODUCTS?")
    print("- They're already getting traffic (proven winners)")
    print("- More internal links = push rankings from positions 5-10 → 1-3")
    print("- Could easily double or triple their current traffic")
    print("")

    return high_potential

if __name__ == '__main__':
    analyze_top_products()
