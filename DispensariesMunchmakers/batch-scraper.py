#!/usr/bin/env python3
"""
Batch scraper for dispensary websites - outputs JSON for analysis
"""

import requests
from bs4 import BeautifulSoup
from urllib.parse import urljoin, urlparse
import json
import time
import sys

HEADERS = {
    'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
}

def get_base_url(url):
    parsed = urlparse(url)
    return f"{parsed.scheme}://{parsed.netloc}"

def fetch_page(url, timeout=15):
    try:
        # Remove UTM params for cleaner fetch
        clean_url = url.split('?')[0] if '?utm_source' in url else url
        response = requests.get(clean_url, headers=HEADERS, timeout=timeout, allow_redirects=True)
        response.raise_for_status()
        return BeautifulSoup(response.text, 'html.parser'), response.text
    except Exception as e:
        return None, str(e)

def extract_meta(soup):
    meta = {}
    title = soup.find('title')
    meta['title'] = title.get_text().strip() if title else None

    desc = soup.find('meta', attrs={'name': 'description'})
    meta['description'] = desc.get('content', '').strip() if desc else None

    kw = soup.find('meta', attrs={'name': 'keywords'})
    meta['keywords'] = kw.get('content', '').strip() if kw else None

    return meta

def fetch_sitemap(base_url):
    sitemap_urls = [f"{base_url}/sitemap.xml", f"{base_url}/sitemap_index.xml", f"{base_url}/wp-sitemap.xml"]

    for sitemap_url in sitemap_urls:
        try:
            response = requests.get(sitemap_url, headers=HEADERS, timeout=10)
            if response.status_code == 200 and ('<urlset' in response.text.lower() or '<sitemapindex' in response.text.lower()):
                soup = BeautifulSoup(response.text, 'xml')
                urls = [loc.get_text().strip() for loc in soup.find_all('loc')]

                # Categorize URLs
                product_patterns = ['product', 'menu', 'flower', 'edible', 'vape', 'concentrate', 'strain', 'shop', 'category', 'pre-roll', 'tincture', 'topical']
                product_urls = [u for u in urls if any(p in u.lower() for p in product_patterns)]

                return {
                    'found': True,
                    'total_urls': len(urls),
                    'product_urls': product_urls[:30],
                    'sample_urls': urls[:15]
                }
        except:
            continue
    return {'found': False}

def detect_platforms(html):
    platforms = {
        'dutchie': ['dutchie', 'iframe.dutchie.com'],
        'jane': ['iheartjane', 'jane.co'],
        'weedmaps': ['weedmaps.com'],
        'leafly': ['leafly.com'],
        'treez': ['treez.io'],
    }
    html_lower = html.lower()
    return [p for p, indicators in platforms.items() if any(ind in html_lower for ind in indicators)]

def detect_products(html):
    keywords = {
        'flower': ['flower', 'bud', 'indica', 'sativa', 'hybrid', 'strain'],
        'edibles': ['edible', 'gummy', 'gummies', 'chocolate', 'beverage'],
        'concentrates': ['concentrate', 'wax', 'shatter', 'live resin', 'rosin', 'dab'],
        'vapes': ['vape', 'cartridge', 'cart', 'pod', 'vaporizer'],
        'pre_rolls': ['pre-roll', 'preroll', 'pre roll', 'joint'],
        'tinctures': ['tincture', 'oil', 'drops'],
        'topicals': ['topical', 'cream', 'lotion', 'balm'],
        'accessories': ['accessory', 'accessories', 'pipe', 'bong', 'grinder'],
    }
    html_lower = html.lower()
    return {cat: sum(html_lower.count(kw) for kw in kws) for cat, kws in keywords.items() if sum(html_lower.count(kw) for kw in kws) > 0}

def detect_services(html):
    services = {
        'delivery': ['delivery', 'deliver', 'door-to-door'],
        'curbside': ['curbside', 'curb-side', 'pickup'],
        'online_ordering': ['order online', 'online order', 'shop online', 'order now'],
        'medical': ['medical', 'med card', 'patient'],
        'recreational': ['recreational', 'adult-use', 'adult use', '21+'],
    }
    html_lower = html.lower()
    return [svc for svc, keywords in services.items() if any(kw in html_lower for kw in keywords)]

def find_menu_links(soup, base_url):
    patterns = ['menu', 'shop', 'products', 'order', 'store']
    links = []
    for a in soup.find_all('a', href=True):
        href = a.get('href', '').lower()
        text = a.get_text().lower().strip()
        if any(p in href or p in text for p in patterns):
            links.append({'url': urljoin(base_url, a.get('href')), 'text': a.get_text().strip()[:40]})
    seen = set()
    return [l for l in links if l['url'] not in seen and not seen.add(l['url'])][:10]

def scrape_dispensary(id, name, url, city, state):
    result = {
        'id': id,
        'name': name,
        'url': url,
        'city': city,
        'state': state,
        'success': False,
        'error': None
    }

    soup, html = fetch_page(url)
    if soup is None:
        result['error'] = html[:100]
        return result

    result['success'] = True
    result['meta'] = extract_meta(soup)
    result['sitemap'] = fetch_sitemap(get_base_url(url))
    result['platforms'] = detect_platforms(html)
    result['products'] = detect_products(html)
    result['services'] = detect_services(html)
    result['menu_links'] = find_menu_links(soup, get_base_url(url))

    return result

def main():
    # Batch 3: More Arizona dispensaries
    dispensaries = [
        {"id": 29, "name": "Health for Life - Crismon", "url": "https://healthforlifeaz.com/crismon/", "city": "Mesa", "state": "Arizona"},
        {"id": 30, "name": "Ponderosa Dispensary Mesa", "url": "https://www.pondyaz.com/locations", "city": "Mesa", "state": "Arizona"},
        {"id": 31, "name": "Ponderosa Dispensary Queen Creek", "url": "https://www.pondyaz.com/locations", "city": "Mesa", "state": "Arizona"},
        {"id": 32, "name": "Ponderosa Dispensary Flagstaff", "url": "https://www.pondyaz.com/locations", "city": "Flagstaff", "state": "Arizona"},
        {"id": 34, "name": "JARS Cannabis Cave Creek", "url": "https://jarscannabis.com/", "city": "Phoenix", "state": "Arizona"},
        {"id": 35, "name": "JARS Cannabis El Mirage", "url": "https://jarscannabis.com/", "city": "El Mirage", "state": "Arizona"},
        {"id": 38, "name": "JARS Cannabis New River", "url": "https://jarscannabis.com/", "city": "New River", "state": "Arizona"},
        {"id": 39, "name": "JARS Cannabis North Phoenix", "url": "https://jarscannabis.com/", "city": "Phoenix", "state": "Arizona"},
        {"id": 41, "name": "JARS Cannabis Peoria", "url": "https://jarscannabis.com/", "city": "Peoria", "state": "Arizona"},
        {"id": 42, "name": "JARS Cannabis Yuma", "url": "https://jarscannabis.com/", "city": "Somerton", "state": "Arizona"},
        {"id": 43, "name": "JARS Cannabis East Tucson", "url": "https://jarscannabis.com/", "city": "Tucson", "state": "Arizona"},
        {"id": 44, "name": "Kind Meds", "url": "http://kindmedsaz.com/", "city": "Mesa", "state": "Arizona"},
        {"id": 45, "name": "Key Cannabis Dispensary Phoenix", "url": "https://keycannabis.com/shop/phoenix-az/", "city": "Phoenix", "state": "Arizona"},
        {"id": 46, "name": "Mint Cannabis - Buckeye/Verado", "url": "http://mintdeals.com/", "city": "Buckeye", "state": "Arizona"},
        {"id": 48, "name": "Mint Cannabis - Northern Ave", "url": "https://mintdeals.com/phoenix-az/", "city": "Phoenix", "state": "Arizona"},
        {"id": 49, "name": "Mint Cannabis - Bell Road Phoenix AZ", "url": "https://mintdeals.com/phoenix-az/", "city": "Phoenix", "state": "Arizona"},
        {"id": 50, "name": "Story Cannabis Dispensary McDowell", "url": "https://storycannabis.com/shop/arizona/phoenix-mcdowell-dispensary/rec-menu/", "city": "Phoenix", "state": "Arizona"},
        {"id": 52, "name": "NatureMed", "url": "https://naturemedaz.com/", "city": "Tucson", "state": "Arizona"},
        {"id": 53, "name": "Nirvana Cannabis - Apache Junction", "url": "https://nirvanacannabis.com/", "city": "Apache Junction", "state": "Arizona"},
        {"id": 54, "name": "Nirvana Cannabis - Florence", "url": "https://nirvanacannabis.com/", "city": "Florence", "state": "Arizona"},
        {"id": 55, "name": "Backpack Boyz - Phoenix", "url": "https://www.backpackboyz.com/content/arizona", "city": "Phoenix", "state": "Arizona"},
        {"id": 56, "name": "Nirvana Cannabis - Prescott Valley", "url": "https://nirvanacannabis.com/", "city": "Prescott Valley", "state": "Arizona"},
        {"id": 57, "name": "Cookies Cannabis Dispensary Tempe", "url": "https://tempe.cookies.co/", "city": "Tempe", "state": "Arizona"},
        {"id": 58, "name": "Noble Herb Flagstaff Dispensary", "url": "http://www.nobleherbaz.com/", "city": "Flagstaff", "state": "Arizona"},
        {"id": 59, "name": "Ponderosa Dispensary Tempe / Mesa", "url": "https://www.pondyaz.com/locations", "city": "Mesa", "state": "Arizona"},
    ]

    results = []
    for i, d in enumerate(dispensaries):
        print(f"Scraping {i+1}/25: {d['name']}...", file=sys.stderr)
        result = scrape_dispensary(d['id'], d['name'], d['url'], d['city'], d['state'])
        results.append(result)
        time.sleep(1)

    print(json.dumps(results, indent=2))

if __name__ == '__main__':
    main()
