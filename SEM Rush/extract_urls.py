#!/usr/bin/env python3
"""
Extract all unique URLs from SEMrush CSV file
"""
import csv
import json

# Input CSV file
csv_file = "/Users/tomernahumi/Documents/Plugins/SEM Rush/munchmakers.com-organic.Positions-us-20251121-2025-11-22T07_15_44Z.csv"

# Output file for unique URLs
output_file = "/Users/tomernahumi/Documents/Plugins/SEM Rush/unique_urls.json"

def extract_unique_urls():
    unique_urls = set()
    url_keywords = {}  # Store keywords associated with each URL

    print(f"Reading CSV file: {csv_file}")

    with open(csv_file, 'r', encoding='utf-8') as f:
        reader = csv.DictReader(f)

        for row in reader:
            url = row.get('URL', '').strip()
            keyword = row.get('Keyword', '').strip()
            position = row.get('Position', '').strip()
            traffic = row.get('Traffic', '').strip()

            if url:
                unique_urls.add(url)

                # Track keywords for each URL
                if url not in url_keywords:
                    url_keywords[url] = []

                url_keywords[url].append({
                    'keyword': keyword,
                    'position': position,
                    'traffic': traffic
                })

    # Sort URLs alphabetically
    sorted_urls = sorted(unique_urls)

    # Prepare data for output
    url_data = {
        'total_urls': len(sorted_urls),
        'urls': sorted_urls,
        'url_details': {}
    }

    # Add details for each URL (top 5 keywords by traffic)
    for url in sorted_urls:
        keywords = url_keywords[url]
        # Sort by traffic (convert to float for sorting)
        keywords_sorted = sorted(keywords,
                                key=lambda x: float(x['traffic']) if x['traffic'] else 0,
                                reverse=True)
        url_data['url_details'][url] = keywords_sorted[:5]  # Top 5 keywords

    # Save to JSON
    with open(output_file, 'w', encoding='utf-8') as f:
        json.dump(url_data, f, indent=2, ensure_ascii=False)

    print(f"\n✓ Extraction complete!")
    print(f"Total unique URLs found: {len(sorted_urls)}")
    print(f"Output saved to: {output_file}")

    # Print URL breakdown by type
    categorize_urls(sorted_urls)

    return sorted_urls

def categorize_urls(urls):
    """Categorize URLs by type"""
    categories = {
        'homepage': [],
        'blog': [],
        'product': [],
        'category': [],
        'shop': [],
        'international': [],
        'other': []
    }

    for url in urls:
        url_lower = url.lower()

        if url_lower.endswith('.com/') or url_lower.endswith('.com'):
            categories['homepage'].append(url)
        elif '/blog/' in url_lower:
            categories['blog'].append(url)
        elif '/product/' in url_lower:
            categories['product'].append(url)
        elif '/product-category/' in url_lower:
            categories['category'].append(url)
        elif '/shop/' in url_lower:
            categories['shop'].append(url)
        elif any(lang in url_lower for lang in ['/es/', '/it/', '/fr/', '/de/', '/ja/', '/pl/', '/fi/', '/iw/']):
            categories['international'].append(url)
        else:
            categories['other'].append(url)

    print("\n" + "="*60)
    print("URL BREAKDOWN BY TYPE")
    print("="*60)
    for category, urls_list in categories.items():
        print(f"{category.upper()}: {len(urls_list)}")
    print("="*60)

if __name__ == '__main__':
    extract_unique_urls()
