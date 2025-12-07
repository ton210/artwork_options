#!/usr/bin/env python3
import requests
import json

# BigCommerce Store Configuration
bc_store_hash = 'tqjrceegho'
bc_access_token = 'lmg7prm3b0fxypwwaja27rtlvqejic0'
gtm_id = 'GTM-T46X9HW'

# API endpoint for scripts
api_url = f'https://api.bigcommerce.com/stores/{bc_store_hash}/v3/content/scripts'

# Headers for authentication
headers = {
    'X-Auth-Token': bc_access_token,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
}

# GTM noscript to be installed in the body
gtm_body_script = f"""<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id={gtm_id}"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->"""

# Script data for body
body_script_data = {
    "name": "Google Tag Manager Body Noscript",
    "description": "GTM noscript fallback for body section",
    "html": gtm_body_script,
    "auto_uninstall": True,
    "load_method": "default",
    "location": "footer",
    "visibility": "all_pages",
    "kind": "script_tag",
    "consent_category": "essential"
}

print("Installing GTM body (noscript) script...")
try:
    response_body = requests.post(api_url, headers=headers, json=body_script_data)
    if response_body.status_code == 200 or response_body.status_code == 201:
        print("✓ GTM body script installed successfully!")
        print(f"Response: {json.dumps(response_body.json(), indent=2)}")
    else:
        print(f"✗ Failed to install body script. Status: {response_body.status_code}")
        print(f"Response: {response_body.text}")
except Exception as e:
    print(f"✗ Error installing body script: {str(e)}")
