#!/usr/bin/env python3
"""
API Verification Script for BigCommerce, Klaviyo, and WooCommerce
"""

import requests
from requests.auth import HTTPBasicAuth

# BigCommerce Credentials
bc_store_hash = 'tqjrceegho'
bc_access_token = 'lmg7prm3b0fxypwwaja27rtlvqejic0'
bc_base_url = f'https://api.bigcommerce.com/stores/{bc_store_hash}/v3'

# Klaviyo Credentials
klaviyo_api_key = "pk_4168739985882153c9855917afa491667a"
klaviyo_base_url = "https://a.klaviyo.com/api"

# WooCommerce Credentials
woo_website = "www.multidash.io"
woo_username = "info@munchmakers.com"
woo_password = "XnqV 2oHQ CeZD LsZm oEPU YQ7M"
woo_base_url = f"https://{woo_website}/wp-json/wc/v3"

def verify_bigcommerce():
    """Test BigCommerce API connection"""
    print("Testing BigCommerce API...")
    headers = {
        'X-Auth-Token': bc_access_token,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }

    try:
        # Try products endpoint instead
        response = requests.get(f'{bc_base_url}/catalog/products?limit=1', headers=headers, timeout=10)
        if response.status_code == 200:
            products = response.json()
            print(f"✓ BigCommerce API: Connected successfully")
            print(f"  Store Hash: {bc_store_hash}")
            print(f"  Products endpoint accessible")
            if 'data' in products:
                print(f"  Products found: {len(products['data'])}")
            return True
        else:
            print(f"✗ BigCommerce API: Failed (Status {response.status_code})")
            print(f"  Response: {response.text}")
            return False
    except Exception as e:
        print(f"✗ BigCommerce API: Error - {str(e)}")
        return False

def verify_klaviyo():
    """Test Klaviyo API connection"""
    print("\nTesting Klaviyo API...")
    headers = {
        'Authorization': f'Klaviyo-API-Key {klaviyo_api_key}',
        'revision': '2024-10-15',
        'Accept': 'application/json'
    }

    try:
        response = requests.get(f'{klaviyo_base_url}/accounts/', headers=headers, timeout=10)
        if response.status_code == 200:
            account_info = response.json()
            print(f"✓ Klaviyo API: Connected successfully")
            if 'data' in account_info and len(account_info['data']) > 0:
                account = account_info['data'][0]
                print(f"  Account ID: {account.get('id', 'N/A')}")
            return True
        else:
            print(f"✗ Klaviyo API: Failed (Status {response.status_code})")
            print(f"  Response: {response.text}")
            return False
    except Exception as e:
        print(f"✗ Klaviyo API: Error - {str(e)}")
        return False

def verify_woocommerce():
    """Test WooCommerce API connection"""
    print("\nTesting WooCommerce API...")
    # Remove spaces from password
    password = woo_password.replace(" ", "")

    try:
        response = requests.get(
            f'{woo_base_url}/system_status',
            auth=HTTPBasicAuth(woo_username, password),
            timeout=10
        )

        if response.status_code == 200:
            system_info = response.json()
            print(f"✓ WooCommerce API: Connected successfully")
            print(f"  Website: {woo_website}")
            if 'environment' in system_info:
                env = system_info['environment']
                print(f"  WooCommerce Version: {env.get('version', 'N/A')}")
            return True
        else:
            print(f"✗ WooCommerce API: Failed (Status {response.status_code})")
            print(f"  Response: {response.text}")
            return False
    except Exception as e:
        print(f"✗ WooCommerce API: Error - {str(e)}")
        return False

if __name__ == "__main__":
    print("=" * 50)
    print("API Verification Test")
    print("=" * 50)

    results = {
        'BigCommerce': verify_bigcommerce(),
        'Klaviyo': verify_klaviyo(),
        'WooCommerce': verify_woocommerce()
    }

    print("\n" + "=" * 50)
    print("Summary:")
    print("=" * 50)
    for api, status in results.items():
        status_icon = "✓" if status else "✗"
        print(f"{status_icon} {api}: {'Working' if status else 'Failed'}")
