#!/usr/bin/env python3
import requests
import json

# BigCommerce Store Configuration
bc_store_hash = 'tqjrceegho'
bc_access_token = 'lmg7prm3b0fxypwwaja27rtlvqejic0'

# API endpoint for scripts
api_url = f'https://api.bigcommerce.com/stores/{bc_store_hash}/v3/content/scripts'

# Headers for authentication
headers = {
    'X-Auth-Token': bc_access_token,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
}

print("Fetching all installed scripts...")
print("=" * 60)

try:
    response = requests.get(api_url, headers=headers)
    if response.status_code == 200:
        scripts = response.json()
        all_scripts = scripts.get('data', [])
        print(f"\n✓ Found {len(all_scripts)} installed script(s)\n")

        # Find GTM scripts
        gtm_scripts = [s for s in all_scripts if 'Tag Manager' in s.get('name', '')]

        if gtm_scripts:
            print(f"Google Tag Manager Scripts Found: {len(gtm_scripts)}\n")
            for script in gtm_scripts:
                print(f"📍 Script: {script.get('name')}")
                print(f"   UUID: {script.get('uuid')}")
                print(f"   Location: {script.get('location')}")
                print(f"   Visibility: {script.get('visibility')}")
                print(f"   Enabled: {script.get('enabled')}")
                print(f"   Created: {script.get('date_created')}")
                print(f"   Description: {script.get('description')}")
                print("-" * 60)
        else:
            print("⚠ No Google Tag Manager scripts found")

        print(f"\nAll Scripts on Store:")
        for idx, script in enumerate(all_scripts, 1):
            enabled_status = "✓" if script.get('enabled') else "✗"
            print(f"{idx}. [{enabled_status}] {script.get('name')} - {script.get('location')}")

    else:
        print(f"✗ Failed to fetch scripts. Status: {response.status_code}")
        print(f"Response: {response.text}")
except Exception as e:
    print(f"✗ Error: {str(e)}")

print("\n" + "=" * 60)
print("Verification complete!")
print(f"Visit: https://www.munchmakers.com")
print("Right-click > View Page Source to verify GTM is in the <head>")
