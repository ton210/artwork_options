#!/usr/bin/env python3
"""
Test scraper for dispensary websites
Extracts: meta tags, sitemap analysis, product detection
"""

import requests
from bs4 import BeautifulSoup
from urllib.parse import urljoin, urlparse
import re
import json
import time

# Test dispensary websites
TEST_URLS = [
    ("Pisos", "https://www.pisoslv.com/"),
    ("Cookies San Bernardino", "https://cookiesdispensary.com/locations/san-bernardino/"),
    ("JARS Cannabis", "https://jarscannabis.com/"),
    ("Planet 13", "https://planet13.com/stores/planet-13-dispensary"),
    ("Curaleaf Las Vegas", "https://curaleaf.com/dispensary/nevada/reef-dispensary-las-vegas-strip"),
    ("Feel State", "https://feelstate.com/location/florissant/"),
    ("Jardin Las Vegas", "https://jardinlasvegas.com/"),
    ("Cloud Cannabis", "https://cloudcannabis.com/utica-order-online/"),
    ("Maggie's Farm", "https://www.maggiesfarmmarijuana.com/locations/manitou-springs/"),
    ("Lume Cannabis", "https://www.lume.com/"),
]

# Product category keywords to look for
PRODUCT_KEYWORDS = {
    'flower': ['flower', 'bud', 'cannabis flower', 'indica', 'sativa', 'hybrid', 'strain'],
    'edibles': ['edible', 'gummy', 'gummies', 'chocolate', 'beverage', 'drink', 'candy'],
    'concentrates': ['concentrate', 'wax', 'shatter', 'live resin', 'rosin', 'dab', 'extract'],
    'vapes': ['vape', 'cartridge', 'cart', 'pod', 'vaporizer', 'pen'],
    'pre_rolls': ['pre-roll', 'preroll', 'pre roll', 'joint', 'blunt'],
    'tinctures': ['tincture', 'oil', 'drops', 'sublingual'],
    'topicals': ['topical', 'cream', 'lotion', 'balm', 'salve'],
    'accessories': ['accessory', 'accessories', 'pipe', 'bong', 'grinder', 'rolling paper'],
}

HEADERS = {
    'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
    'Accept-Language': 'en-US,en;q=0.5',
}

def get_base_url(url):
    """Extract base URL from full URL"""
    parsed = urlparse(url)
    return f"{parsed.scheme}://{parsed.netloc}"

def fetch_page(url, timeout=15):
    """Fetch a page and return BeautifulSoup object"""
    try:
        response = requests.get(url, headers=HEADERS, timeout=timeout, allow_redirects=True)
        response.raise_for_status()
        return BeautifulSoup(response.text, 'html.parser'), response.text
    except Exception as e:
        return None, str(e)

def extract_meta_tags(soup):
    """Extract relevant meta tags"""
    meta = {}

    # Title
    title_tag = soup.find('title')
    meta['title'] = title_tag.get_text().strip() if title_tag else None

    # Meta description
    desc_tag = soup.find('meta', attrs={'name': 'description'})
    meta['description'] = desc_tag.get('content', '').strip() if desc_tag else None

    # Meta keywords
    kw_tag = soup.find('meta', attrs={'name': 'keywords'})
    meta['keywords'] = kw_tag.get('content', '').strip() if kw_tag else None

    # OG tags
    og_title = soup.find('meta', attrs={'property': 'og:title'})
    meta['og_title'] = og_title.get('content', '').strip() if og_title else None

    og_desc = soup.find('meta', attrs={'property': 'og:description'})
    meta['og_description'] = og_desc.get('content', '').strip() if og_desc else None

    return meta

def fetch_sitemap(base_url):
    """Try to fetch and parse sitemap.xml"""
    sitemap_urls = [
        f"{base_url}/sitemap.xml",
        f"{base_url}/sitemap_index.xml",
        f"{base_url}/wp-sitemap.xml",
    ]

    for sitemap_url in sitemap_urls:
        try:
            response = requests.get(sitemap_url, headers=HEADERS, timeout=10)
            if response.status_code == 200 and 'xml' in response.headers.get('content-type', '').lower():
                return parse_sitemap(response.text, sitemap_url)
            elif response.status_code == 200 and '<urlset' in response.text.lower():
                return parse_sitemap(response.text, sitemap_url)
        except:
            continue

    return None

def parse_sitemap(xml_content, sitemap_url):
    """Parse sitemap XML and extract product-related URLs"""
    soup = BeautifulSoup(xml_content, 'xml')
    urls = []

    # Get all URLs from sitemap
    for loc in soup.find_all('loc'):
        urls.append(loc.get_text().strip())

    # Also check for nested sitemaps
    for sitemap in soup.find_all('sitemap'):
        loc = sitemap.find('loc')
        if loc:
            urls.append(loc.get_text().strip())

    # Categorize URLs
    product_urls = []
    menu_urls = []
    other_urls = []

    product_patterns = ['product', 'menu', 'flower', 'edible', 'vape', 'concentrate', 'strain', 'shop']

    for url in urls:
        url_lower = url.lower()
        if any(p in url_lower for p in product_patterns):
            product_urls.append(url)
        elif '/menu' in url_lower or '/shop' in url_lower:
            menu_urls.append(url)
        else:
            other_urls.append(url)

    return {
        'sitemap_url': sitemap_url,
        'total_urls': len(urls),
        'product_urls': product_urls[:20],  # Limit to first 20
        'menu_urls': menu_urls[:10],
        'sample_urls': urls[:10],
    }

def detect_products_from_text(text):
    """Analyze text for product category mentions"""
    text_lower = text.lower()
    detected = {}

    for category, keywords in PRODUCT_KEYWORDS.items():
        count = sum(text_lower.count(kw) for kw in keywords)
        if count > 0:
            detected[category] = count

    return detected

def detect_platform(soup, html):
    """Detect if they use a known menu platform"""
    platforms = {
        'dutchie': ['dutchie', 'dutchie.com', 'iframe.dutchie.com'],
        'jane': ['iheartjane', 'jane.co', 'api.iheartjane.com'],
        'weedmaps': ['weedmaps', 'weedmaps.com/embed'],
        'leafly': ['leafly', 'leafly.com/embed'],
        'treez': ['treez', 'treez.io'],
        'meadow': ['getmeadow', 'meadow.com'],
    }

    html_lower = html.lower()
    detected = []

    for platform, indicators in platforms.items():
        if any(ind in html_lower for ind in indicators):
            detected.append(platform)

    # Check for iframes
    iframes = soup.find_all('iframe')
    for iframe in iframes:
        src = iframe.get('src', '').lower()
        for platform, indicators in platforms.items():
            if any(ind in src for ind in indicators):
                if platform not in detected:
                    detected.append(platform)

    return detected

def find_menu_links(soup, base_url):
    """Find links that look like menu/shop links"""
    menu_patterns = ['menu', 'shop', 'products', 'order', 'store', 'dispensary-menu']
    menu_links = []

    for a in soup.find_all('a', href=True):
        href = a.get('href', '').lower()
        text = a.get_text().lower().strip()

        if any(p in href or p in text for p in menu_patterns):
            full_url = urljoin(base_url, a.get('href'))
            menu_links.append({
                'url': full_url,
                'text': a.get_text().strip()[:50]
            })

    # Dedupe by URL
    seen = set()
    unique_links = []
    for link in menu_links:
        if link['url'] not in seen:
            seen.add(link['url'])
            unique_links.append(link)

    return unique_links[:10]

def scrape_dispensary(name, url):
    """Main function to scrape a dispensary website"""
    print(f"\n{'='*60}")
    print(f"SCRAPING: {name}")
    print(f"URL: {url}")
    print('='*60)

    results = {
        'name': name,
        'url': url,
        'success': False,
        'meta': {},
        'sitemap': None,
        'platforms': [],
        'product_mentions': {},
        'menu_links': [],
        'error': None
    }

    # Fetch main page
    soup, html = fetch_page(url)
    if soup is None:
        results['error'] = html
        print(f"  ERROR: {html}")
        return results

    results['success'] = True

    # Extract meta tags
    results['meta'] = extract_meta_tags(soup)
    print(f"\n  META TAGS:")
    title = results['meta'].get('title') or 'N/A'
    desc = results['meta'].get('description') or 'N/A'
    kw = results['meta'].get('keywords') or 'N/A'
    print(f"    Title: {title[:60]}")
    print(f"    Description: {desc[:80]}")
    print(f"    Keywords: {kw[:80]}")

    # Try sitemap
    base_url = get_base_url(url)
    results['sitemap'] = fetch_sitemap(base_url)
    if results['sitemap']:
        print(f"\n  SITEMAP FOUND: {results['sitemap']['sitemap_url']}")
        print(f"    Total URLs: {results['sitemap']['total_urls']}")
        print(f"    Product URLs: {len(results['sitemap']['product_urls'])}")
        if results['sitemap']['product_urls']:
            print(f"    Sample product URLs:")
            for purl in results['sitemap']['product_urls'][:5]:
                print(f"      - {purl[:70]}")
    else:
        print(f"\n  SITEMAP: Not found")

    # Detect platforms
    results['platforms'] = detect_platform(soup, html)
    if results['platforms']:
        print(f"\n  PLATFORMS DETECTED: {', '.join(results['platforms'])}")
    else:
        print(f"\n  PLATFORMS: None detected")

    # Detect product mentions
    results['product_mentions'] = detect_products_from_text(html)
    if results['product_mentions']:
        print(f"\n  PRODUCT MENTIONS:")
        for cat, count in sorted(results['product_mentions'].items(), key=lambda x: -x[1]):
            print(f"    {cat}: {count} mentions")

    # Find menu links
    results['menu_links'] = find_menu_links(soup, base_url)
    if results['menu_links']:
        print(f"\n  MENU LINKS FOUND:")
        for link in results['menu_links'][:5]:
            print(f"    - {link['text']}: {link['url'][:60]}")

    return results

def main():
    print("="*60)
    print("DISPENSARY WEBSITE SCRAPER TEST")
    print("="*60)

    all_results = []

    for name, url in TEST_URLS:
        result = scrape_dispensary(name, url)
        all_results.append(result)
        time.sleep(1)  # Be nice to servers

    # Summary
    print("\n" + "="*60)
    print("SUMMARY")
    print("="*60)

    successful = [r for r in all_results if r['success']]
    with_sitemap = [r for r in all_results if r['sitemap']]
    with_platform = [r for r in all_results if r['platforms']]
    with_products = [r for r in all_results if r['product_mentions']]

    print(f"\nTotal tested: {len(all_results)}")
    print(f"Successfully fetched: {len(successful)} ({len(successful)*100//len(all_results)}%)")
    print(f"Has sitemap: {len(with_sitemap)} ({len(with_sitemap)*100//len(all_results)}%)")
    print(f"Platform detected: {len(with_platform)} ({len(with_platform)*100//len(all_results)}%)")
    print(f"Product mentions found: {len(with_products)} ({len(with_products)*100//len(all_results)}%)")

    # Product category coverage
    print("\nPRODUCT CATEGORY DETECTION:")
    all_categories = {}
    for r in all_results:
        for cat in r.get('product_mentions', {}):
            all_categories[cat] = all_categories.get(cat, 0) + 1

    for cat, count in sorted(all_categories.items(), key=lambda x: -x[1]):
        print(f"  {cat}: detected on {count}/{len(all_results)} sites")

    # Save results
    with open('scraper-test-results.json', 'w') as f:
        json.dump(all_results, f, indent=2)
    print(f"\nFull results saved to scraper-test-results.json")

if __name__ == '__main__':
    main()
