import requests
import json

bc_store_hash = 'tqjrceegho'
bc_access_token = 'lmg7prm3b0fxypwwaja27rtlvqejic0'
api_base_url = f'https://api.bigcommerce.com/stores/{bc_store_hash}/v3'

headers = {
    'X-Auth-Token': bc_access_token,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
}

url = f'{api_base_url}/content/pages/50'
response = requests.get(url, headers=headers)

if response.status_code == 200:
    page = response.json()['data']
    print(f'Page ID 50 Analysis:')
    print(f'='*60)
    print(f'Name: {page.get("name", "N/A")}')
    print(f'URL: {page.get("url", "N/A")}')
    print(f'Meta Description: {page.get("meta_description", "N/A")[:80]}...')

    body = page.get('body', '')

    # Look for key identifying phrases
    print(f'\nContent Indicators:')
    print(f'- Contains "Tour" or "Musicians": {"Tour" in body or "Musicians" in body}')
    print(f'- Contains "70% profit": {"70% profit" in body}')
    print(f'- Contains "delivery" or "doorstep": {"delivery" in body.lower() or "doorstep" in body.lower()}')
    print(f'- Contains "unboxing": {"unboxing" in body.lower()}')
    print(f'- Contains "$22,000": {"$22,000" in body}')

    # Check the first heading
    if '<h1' in body:
        h1_start = body.find('<h1')
        h1_end = body.find('</h1>', h1_start)
        h1_content = body[h1_start:h1_end+5]
        print(f'\nFirst H1 heading:')
        print(h1_content[:200])

    # Determine which page it is
    print(f'\n' + '='*60)
    if 'Tour Merch' in body or '$22,000' in body or '70% profit margins' in body:
        print('✅ CONFIRMED: This is the MUSICIANS & ARTISTS page')
        print('➡️  Need to recreate: Cannabis Delivery Services page')
    elif 'doorstep' in body.lower() or 'Turn Every Delivery' in body:
        print('✅ CONFIRMED: This is the CANNABIS DELIVERY SERVICES page')
        print('➡️  Need to recreate: Musicians & Artists page')
    else:
        print('⚠️  Cannot determine page content - showing more details:')
        print(f'First 500 chars: {body[:500]}')
else:
    print(f'Error: {response.status_code}')
    print(response.text)