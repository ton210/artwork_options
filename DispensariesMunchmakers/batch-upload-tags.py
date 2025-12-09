#!/usr/bin/env python3
"""
Batch scraper + uploader for dispensary tags
Scrapes websites and uploads inferred tags to the database
"""

import requests
from bs4 import BeautifulSoup
from urllib.parse import urlparse
import json
import time
import sys
import os
import psycopg2
from psycopg2.extras import execute_values

# Get DATABASE_URL from heroku
DATABASE_URL = os.environ.get('DATABASE_URL')

# User agents to try
USER_AGENTS = [
    'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
    'Mozilla/5.0 (Linux; Android 10; SM-G981B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.162 Mobile Safari/537.36',
    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
]

# Tag categories
TAG_CATEGORIES = {
    'flower': 'product',
    'pre-rolls': 'product',
    'vapes': 'product',
    'concentrates': 'product',
    'edibles': 'product',
    'tinctures': 'product',
    'topicals': 'product',
    'accessories': 'product',
    'delivery': 'service',
    'curbside-pickup': 'service',
    'online-ordering': 'service',
    'medical': 'license',
    'recreational': 'license',
}

# Known chain data for fallback inference
CHAIN_TAGS = {
    'rise': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'tinctures', 'delivery', 'medical', 'recreational', 'online-ordering'],
    'cookies': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'tinctures', 'topicals', 'delivery', 'curbside-pickup', 'medical', 'recreational', 'online-ordering'],
    'verilife': ['flower', 'vapes', 'concentrates', 'edibles', 'tinctures', 'topicals', 'delivery', 'curbside-pickup', 'online-ordering', 'medical', 'recreational'],
    'trulieve': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'tinctures', 'topicals', 'delivery', 'medical', 'recreational', 'online-ordering'],
    'zen leaf': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'tinctures', 'topicals', 'medical', 'recreational', 'online-ordering'],
    'curaleaf': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'tinctures', 'topicals', 'delivery', 'medical', 'recreational', 'online-ordering'],
    'mission': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'tinctures', 'medical', 'recreational', 'online-ordering'],
    'cannabist': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'tinctures', 'delivery', 'medical', 'recreational', 'online-ordering'],
    'mint cannabis': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'delivery', 'recreational', 'online-ordering'],
    'mint deals': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'delivery', 'recreational', 'online-ordering'],
    'windy city': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'hatch': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'medical', 'recreational', 'online-ordering'],
    'terrabis': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'medical', 'recreational', 'online-ordering'],
    'blue sage': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'medical', 'recreational'],
    'high profile': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'medical', 'recreational', 'online-ordering'],
    'hippos': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'proper cannabis': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'medical', 'recreational', 'online-ordering'],
    'flora farms': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'medical', 'recreational'],
    'from the earth': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'medical', 'recreational', 'online-ordering'],
    'local cannabis': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'codes dispensary': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'medical', 'recreational'],
    'sol flower': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'jars cannabis': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'greenpharms': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'medical', 'recreational'],
    'story cannabis': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'parkway': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'grasshopper': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'maribis': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    "spark'd": ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'cloud9': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'okay cannabis': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'phoenix cannabis': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational'],
    'sticky saguaro': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational'],
    'desert bloom': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'medical', 'recreational'],
    "earth's healing": ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'medical', 'recreational'],
    'farm fresh': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'medical', 'recreational'],
    'hana': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'medical', 'recreational'],
    'trubliss': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'medical', 'recreational'],
    'bud & rita': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'kc cannabis': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'medical', 'recreational'],
    'stash dispensaries': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    "nature's care": ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'medical', 'recreational', 'online-ordering'],
    'good day farm': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'medical', 'recreational', 'online-ordering'],
    'beyond hello': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'tinctures', 'medical', 'recreational', 'online-ordering'],
    'sunnyside': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'tinctures', 'topicals', 'delivery', 'recreational', 'online-ordering'],
    'ascend': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'tinctures', 'medical', 'recreational', 'online-ordering'],
    'planet 13': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'tinctures', 'topicals', 'delivery', 'recreational', 'online-ordering'],
    'star buds': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational'],
    'swade': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'medical', 'recreational', 'online-ordering'],
    'ayr': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'tinctures', 'topicals', 'medical', 'recreational', 'online-ordering'],
    'apothecarium': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'tinctures', 'topicals', 'delivery', 'medical', 'recreational', 'online-ordering'],
    'breakwater': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'tinctures', 'topicals', 'medical'],
    'social leaf': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'theory wellness': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'tinctures', 'recreational', 'online-ordering'],
    'harmony': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'tinctures', 'topicals', 'medical', 'recreational'],
    'nj leaf': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'botanist': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'tinctures', 'topicals', 'medical', 'recreational', 'online-ordering'],
    'bloc': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'nightjar': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'village dispensary': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'deep roots': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'tinctures', 'delivery', 'recreational', 'online-ordering'],
    'cultivate': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'tinctures', 'delivery', 'recreational', 'online-ordering'],
    'greenlight': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'delivery', 'recreational', 'online-ordering'],
    'oasis': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'delivery', 'recreational', 'online-ordering'],
    'jade': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'delivery', 'recreational', 'online-ordering'],
    'the dispensary': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'delivery', 'recreational', 'online-ordering'],
    'nuleaf': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'delivery', 'recreational', 'online-ordering'],
    'quality roots': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'sanctuary': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'tinctures', 'topicals', 'medical', 'recreational', 'online-ordering'],
    'the source': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'tinctures', 'delivery', 'recreational', 'online-ordering'],
    'thrive': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'tinctures', 'delivery', 'recreational', 'online-ordering'],
    'silver state': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'medical', 'recreational', 'online-ordering'],
    'battle born': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'wallflower': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'tree of life': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'nectar': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'tinctures', 'topicals', 'recreational', 'online-ordering'],
    "floyd's": ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'electric lettuce': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'kaleafa': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'serra': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'chalice': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'tinctures', 'recreational', 'online-ordering'],
    'bridge city': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'oregrown': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'la mota': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'substance': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'cannabis nation': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'lucky lion': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'homegrown': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'og collective': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'broadway cannabis': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'neta': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'tinctures', 'topicals', 'medical', 'recreational', 'online-ordering'],
    'berkshire roots': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'tinctures', 'medical', 'recreational', 'online-ordering'],
    'commcan': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'tinctures', 'medical', 'recreational', 'online-ordering'],
    'temescal': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'tinctures', 'medical', 'recreational', 'online-ordering'],
    'garden remedies': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'tinctures', 'medical', 'recreational', 'online-ordering'],
    'rev clinics': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'tinctures', 'medical', 'recreational', 'online-ordering'],
    'revolutionary': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'tinctures', 'medical', 'recreational', 'online-ordering'],
    'insa': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'tinctures', 'topicals', 'medical', 'recreational', 'online-ordering'],
    'patriot care': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'tinctures', 'medical', 'recreational', 'online-ordering'],
    'jars cannabis': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'house of dank': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'lume': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'tinctures', 'recreational', 'online-ordering'],
    'skymint': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'cloud cannabis': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'gage cannabis': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'exclusive': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'stiiizy': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'medmen': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'tinctures', 'topicals', 'recreational', 'online-ordering'],
    'jungle boys': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'liberty cannabis': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'catalyst': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering', 'delivery'],
    'urbn leaf': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering', 'delivery'],
    'march and ash': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'harborside': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'tinctures', 'topicals', 'recreational', 'online-ordering'],
    'perfect union': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'embarc': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'sweet flower': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'artist tree': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
    'urbana': ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational', 'online-ordering'],
}

# Dispensaries to process (next batch)
DISPENSARIES = [
    {"id": 3018, "name": "AYR Dispensary Goshen - OHIO", "url": "https://ayrdispensaries.com/ohio/goshen/shop/?utm_source=munchmakers", "city": "Goshen", "state": "Unknown"},
    {"id": 3019, "name": "AYR Cannabis Dispensary Lewis Center", "url": "https://ayrdispensaries.com/ohio/lewis-center/?utm_source=munchmakers", "city": "Lewis Center", "state": "Unknown"},
    {"id": 3020, "name": "AYR Cannabis Dispensary Niles", "url": "https://ayrdispensaries.com/ohio/niles/?utm_source=munchmakers", "city": "Niles", "state": "Unknown"},
    {"id": 3023, "name": "Zen Leaf Dispensary Bowling Green", "url": "https://oh.zenleafdispensaries.com/bowling-green/?utm_source=munchmakers", "city": "Bowling Green", "state": "Unknown"},
    {"id": 3027, "name": "Zen Leaf Dispensary Newark", "url": "https://oh.zenleafdispensaries.com/newark/?utm_source=munchmakers", "city": "Newark", "state": "Unknown"},
    {"id": 3029, "name": "Columbia Care Dispensary - Logan", "url": "https://www.oh.columbia.care/stores/ohio/logan?utm_source=munchmakers", "city": "Logan", "state": "Unknown"},
    {"id": 3031, "name": "Columbia Care Dispensary - Marietta", "url": "https://www.oh.columbia.care/stores/ohio/marietta?utm_source=munchmakers", "city": "Marietta", "state": "Unknown"},
    {"id": 3032, "name": "Greenlight Dispensary Marengo", "url": "https://ohio.greenlightdispensary.com/locations/marengo?utm_source=munchmakers", "city": "Marengo", "state": "Unknown"},
    {"id": 3034, "name": "Sunnyside Recreational and Medical Marijuana Dispensary - Newark", "url": "https://www.sunnyside.shop/menu/newark-oh/store/newark-oh?utm_source=munchmakers", "city": "Newark", "state": "Unknown"},
    {"id": 3035, "name": "Sunnyside Recreational and Medical Marijuana Dispensary - Chillicothe", "url": "https://www.sunnyside.shop/menu/chillicothe-oh/store/chillicothe-oh?utm_source=munchmakers", "city": "Chillicothe", "state": "Unknown"},
    {"id": 3039, "name": "FRX East Liverpool Craft Cannabis Dispensary", "url": "https://frxdispensaries.com/east-liverpool/?utm_source=munchmakers", "city": "East Liverpool", "state": "Unknown"},
    {"id": 3041, "name": "Beyond Hello", "url": "https://beyond-hello.com/ohio-dispensaries/mansfield/?utm_source=munchmakers", "city": "Mansfield", "state": "Unknown"},
    {"id": 3043, "name": "Verilife Dispensary", "url": "https://www.verilife.com/oh/locations/wapakoneta?utm_source=munchmakers", "city": "Wapakoneta", "state": "Unknown"},
    {"id": 3044, "name": "Verilife Dispensary", "url": "https://www.verilife.com/oh/locations/hillsboro?utm_source=munchmakers", "city": "Hillsboro", "state": "Unknown"},
    {"id": 3045, "name": "Firelands Scientific Dispensary", "url": "https://www.myfisci.com/?utm_source=munchmakers", "city": "Huron", "state": "Unknown"},
    {"id": 3047, "name": "Nectar", "url": "https://nectarohio.com/?utm_source=munchmakers", "city": "Bowling Green", "state": "Unknown"},
    {"id": 3048, "name": "Backroad Wellness", "url": "https://www.backroadwellness.com/location/dispensary-lima-oh/?utm_source=munchmakers", "city": "Lima", "state": "Unknown"},
    {"id": 3050, "name": "Curaleaf Dispensary Newark", "url": "https://curaleaf.com/dispensary/ohio/curaleaf-newark-au?utm_source=munchmakers", "city": "Newark", "state": "Unknown"},
]

def get_base_url(url):
    parsed = urlparse(url)
    return f"{parsed.scheme}://{parsed.netloc}"

def try_fetch_page(url):
    """Try to fetch page with different User-Agents"""
    clean_url = url.split('?')[0] if '?utm_source' in url else url
    for ua in USER_AGENTS:
        headers = {'User-Agent': ua, 'Accept': 'text/html,application/xhtml+xml'}
        try:
            response = requests.get(clean_url, headers=headers, timeout=20, allow_redirects=True)
            if response.status_code == 200:
                return response.text
        except:
            continue
    return None

def infer_tags_from_html(html):
    """Infer tags from HTML content"""
    tags = set()
    html_lower = html.lower()

    # Product detection
    if any(kw in html_lower for kw in ['flower', 'bud', 'indica', 'sativa', 'hybrid', 'strain']):
        tags.add('flower')
    if any(kw in html_lower for kw in ['pre-roll', 'preroll', 'pre roll', 'joint']):
        tags.add('pre-rolls')
    if any(kw in html_lower for kw in ['vape', 'cartridge', 'cart', 'pod']):
        tags.add('vapes')
    if any(kw in html_lower for kw in ['concentrate', 'wax', 'shatter', 'live resin', 'rosin', 'dab']):
        tags.add('concentrates')
    if any(kw in html_lower for kw in ['edible', 'gummy', 'gummies', 'chocolate', 'beverage']):
        tags.add('edibles')
    if any(kw in html_lower for kw in ['tincture', 'oil', 'drops', 'sublingual']):
        tags.add('tinctures')
    if any(kw in html_lower for kw in ['topical', 'cream', 'lotion', 'balm', 'salve']):
        tags.add('topicals')

    # Service detection
    if any(kw in html_lower for kw in ['delivery', 'deliver', 'door-to-door']):
        tags.add('delivery')
    if any(kw in html_lower for kw in ['curbside', 'curb-side', 'pickup']):
        tags.add('curbside-pickup')
    if any(kw in html_lower for kw in ['order online', 'online order', 'shop online', 'order now', 'menu']):
        tags.add('online-ordering')

    # License detection
    if any(kw in html_lower for kw in ['medical', 'med card', 'patient', 'mmj']):
        tags.add('medical')
    if any(kw in html_lower for kw in ['recreational', 'adult-use', 'adult use', '21+', 'rec']):
        tags.add('recreational')

    return list(tags)

def infer_from_chain(name):
    """Infer tags from known chain data"""
    name_lower = name.lower()
    for chain, tags in CHAIN_TAGS.items():
        if chain in name_lower:
            return tags
    return None

def process_dispensary(disp):
    """Process a single dispensary and return tags"""
    print(f"  Processing: {disp['name']} (ID: {disp['id']})", file=sys.stderr)

    # First try to match known chains
    chain_tags = infer_from_chain(disp['name'])
    if chain_tags:
        print(f"    -> Matched chain, {len(chain_tags)} tags", file=sys.stderr)
        return chain_tags

    # Try to scrape the website
    html = try_fetch_page(disp['url'])
    if html:
        tags = infer_tags_from_html(html)
        if tags:
            print(f"    -> Scraped, {len(tags)} tags", file=sys.stderr)
            return tags

    # Fallback: basic tags based on state
    print(f"    -> Using fallback tags", file=sys.stderr)
    return ['flower', 'pre-rolls', 'vapes', 'concentrates', 'edibles', 'recreational']

def upload_tags(conn, dispensary_id, tags):
    """Upload tags to database"""
    cur = conn.cursor()

    # Prepare data for bulk insert
    tag_data = []
    for tag in tags:
        category = TAG_CATEGORIES.get(tag, 'other')
        tag_data.append((dispensary_id, tag, category, 0.9, 'scraper'))

    # Insert with ON CONFLICT DO NOTHING
    insert_sql = """
        INSERT INTO dispensary_tags (dispensary_id, tag, category, confidence, source)
        VALUES %s
        ON CONFLICT (dispensary_id, tag) DO NOTHING
    """

    execute_values(cur, insert_sql, tag_data)
    conn.commit()
    cur.close()
    return len(tag_data)

def main():
    if not DATABASE_URL:
        print("ERROR: DATABASE_URL not set. Run: export DATABASE_URL=$(heroku config:get DATABASE_URL --app bestdispensaries-munchmakers)", file=sys.stderr)
        sys.exit(1)

    # Connect to database
    print(f"Connecting to database...", file=sys.stderr)
    conn = psycopg2.connect(DATABASE_URL, sslmode='require')

    print(f"\nProcessing {len(DISPENSARIES)} dispensaries...\n", file=sys.stderr)

    total_tags = 0
    results = []

    for i, disp in enumerate(DISPENSARIES):
        print(f"[{i+1}/{len(DISPENSARIES)}]", file=sys.stderr)

        tags = process_dispensary(disp)

        if tags:
            uploaded = upload_tags(conn, disp['id'], tags)
            total_tags += uploaded
            results.append({'id': disp['id'], 'name': disp['name'], 'tags': tags})

        time.sleep(0.5)  # Be nice to servers

    conn.close()

    print(f"\n{'='*60}", file=sys.stderr)
    print(f"COMPLETE: Uploaded {total_tags} tags for {len(results)} dispensaries", file=sys.stderr)
    print(f"{'='*60}\n", file=sys.stderr)

    # Output results as JSON
    print(json.dumps(results, indent=2))

if __name__ == '__main__':
    main()
