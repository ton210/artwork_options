#!/usr/bin/env python3
import requests
import json

# BigCommerce Store Configuration
bc_store_hash = 'tqjrceegho'
bc_access_token = 'lmg7prm3b0fxypwwaja27rtlvqejic0'
base_domain = 'www.munchmakers.com'
gtm_id = 'GTM-T46X9HW'

# API endpoint for scripts
api_url = f'https://api.bigcommerce.com/stores/{bc_store_hash}/v3/content/scripts'

# Headers for authentication
headers = {
    'X-Auth-Token': bc_access_token,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
}

# GTM script to be installed in the head
gtm_head_script = f"""<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){{w[l]=w[l]||[];w[l].push({{'gtm.start':
new Date().getTime(),event:'gtm.js'}});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
}})(window,document,'script','dataLayer','{gtm_id}');</script>
<!-- End Google Tag Manager -->"""

# GTM noscript to be installed in the body
gtm_body_script = f"""<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id={gtm_id}"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->"""

# Script data for head
head_script_data = {
    "name": "Google Tag Manager - Head",
    "description": "GTM script for head section",
    "html": gtm_head_script,
    "auto_uninstall": True,
    "load_method": "default",
    "location": "head",
    "visibility": "all_pages",
    "kind": "script_tag",
    "consent_category": "essential"
}

# Script data for body
body_script_data = {
    "name": "Google Tag Manager Body Noscript",
    "description": "GTM noscript fallback for body section",
    "html": gtm_body_script,
    "auto_uninstall": True,
    "load_method": "default",
    "location": "footer",  # This is the closest to body tag opening in BC
    "visibility": "all_pages",
    "kind": "script_tag",
    "consent_category": "essential"
}

print("Installing Google Tag Manager scripts...")
print(f"GTM ID: {gtm_id}")
print(f"Store Hash: {bc_store_hash}")
print("-" * 50)

# Install head script
print("\n1. Installing GTM head script...")
try:
    response_head = requests.post(api_url, headers=headers, json=head_script_data)
    if response_head.status_code == 200 or response_head.status_code == 201:
        print("✓ GTM head script installed successfully!")
        print(f"Response: {json.dumps(response_head.json(), indent=2)}")
    else:
        print(f"✗ Failed to install head script. Status: {response_head.status_code}")
        print(f"Response: {response_head.text}")
except Exception as e:
    print(f"✗ Error installing head script: {str(e)}")

# Install body script
print("\n2. Installing GTM body (noscript) script...")
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

# List all installed scripts to verify
print("\n3. Listing all installed scripts...")
try:
    response_list = requests.get(api_url, headers=headers)
    if response_list.status_code == 200:
        scripts = response_list.json()
        print(f"✓ Found {len(scripts.get('data', []))} installed script(s)")
        for script in scripts.get('data', []):
            print(f"  - {script.get('name')} (UUID: {script.get('uuid')})")
    else:
        print(f"✗ Failed to list scripts. Status: {response_list.status_code}")
except Exception as e:
    print(f"✗ Error listing scripts: {str(e)}")

print("\n" + "-" * 50)
print("Installation complete! Visit your store to verify GTM is working.")
print(f"Store URL: https://{base_domain}")
