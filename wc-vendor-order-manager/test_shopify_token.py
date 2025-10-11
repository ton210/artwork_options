#!/usr/bin/env python3
"""
Shopify API Token Tester
Simple script to test if your Shopify API token is working
"""

import requests
import json
from datetime import datetime

# Configuration
STORE_NAME = "qstomize"
ACCESS_TOKEN = "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"
BASE_URL = f"https://{STORE_NAME}.myshopify.com/admin/api/2023-10"

def test_endpoint(endpoint, description):
    """Test a specific Shopify API endpoint"""
    url = f"{BASE_URL}/{endpoint}"
    headers = {
        'X-Shopify-Access-Token': ACCESS_TOKEN,
        'Content-Type': 'application/json'
    }
    
    print(f"\n🔍 Testing: {description}")
    print(f"URL: {url}")
    
    try:
        response = requests.get(url, headers=headers, timeout=30)
        
        print(f"Status Code: {response.status_code}")
        
        if response.status_code == 200:
            print("✅ SUCCESS")
            data = response.json()
            
            # Show relevant info based on endpoint
            if 'shop' in data:
                shop = data['shop']
                print(f"   Shop: {shop.get('name', 'Unknown')}")
                print(f"   Domain: {shop.get('domain', 'Unknown')}")
                print(f"   Plan: {shop.get('plan_name', 'Unknown')}")
            elif 'orders' in data:
                print(f"   Orders found: {len(data['orders'])}")
                if data['orders']:
                    print(f"   Sample order: #{data['orders'][0].get('order_number', 'Unknown')}")
            elif 'products' in data:
                print(f"   Products found: {len(data['products'])}")
            
            # Check rate limits
            if 'X-Shopify-Shop-Api-Call-Limit' in response.headers:
                rate_info = response.headers['X-Shopify-Shop-Api-Call-Limit']
                print(f"   Rate Limit: {rate_info}")
            
            return True
            
        else:
            print(f"❌ FAILED")
            try:
                error_data = response.json()
                error_msg = error_data.get('errors', response.text)
                print(f"   Error: {error_msg}")
            except:
                print(f"   Raw Response: {response.text[:200]}")
            
            # Specific error guidance
            if response.status_code == 403:
                print("   🚨 This means your private app is disabled or lacks permissions")
            elif response.status_code == 401:
                print("   🚨 Your access token is invalid or revoked")
            elif response.status_code == 404:
                print("   🚨 Store not found or API endpoint incorrect")
            
            return False
            
    except requests.exceptions.RequestException as e:
        print(f"❌ CONNECTION ERROR: {e}")
        return False

def main():
    print("🧪 SHOPIFY API TOKEN TESTER")
    print("=" * 40)
    print(f"Store: {STORE_NAME}.myshopify.com")
    print(f"Token: {ACCESS_TOKEN[:15]}...")
    print(f"Test Time: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    
    # Critical tests
    print("\n🔥 CRITICAL TESTS (Must Pass)")
    shop_success = test_endpoint("shop.json", "Shop Information (Basic Connection)")
    
    if not shop_success:
        print("\n🛑 CRITICAL FAILURE: Cannot connect to shop")
        print("\n🔧 TROUBLESHOOTING:")
        print("1. Check if your private app is installed and enabled")
        print("2. Verify your access token is correct")
        print("3. Ensure your store name is 'qstomize'")
        print("4. Check if the app has been uninstalled")
        return False
    
    orders_success = test_endpoint("orders.json?limit=1&status=any", "Orders Access (Required for Integration)")
    
    # Optional but useful tests
    print("\n📋 OPTIONAL TESTS")
    test_endpoint("products.json?limit=1", "Products Access")
    test_endpoint("collections.json?limit=1", "Collections Access")
    
    # Test with recent orders for fulfillment testing
    print("\n🚚 FULFILLMENT CAPABILITY TEST")
    try:
        response = requests.get(f"{BASE_URL}/orders.json?limit=5&status=any", 
                              headers={'X-Shopify-Access-Token': ACCESS_TOKEN}, 
                              timeout=30)
        if response.status_code == 200:
            data = response.json()
            if data.get('orders'):
                test_order_id = data['orders'][0]['id']
                test_endpoint(f"orders/{test_order_id}/fulfillments.json", 
                            "Fulfillments Access (Required for Tracking Sync)")
            else:
                print("No orders found to test fulfillments")
        else:
            print("Could not retrieve orders for fulfillment testing")
    except Exception as e:
        print(f"Fulfillment test error: {e}")
    
    # Summary
    print("\n📊 TEST SUMMARY")
    print("=" * 20)
    
    if shop_success and orders_success:
        print("🎉 SUCCESS: Your token is working!")
        print("✅ Basic connection: OK")
        print("✅ Orders access: OK")
        print("\nYour Shopify integration should work now.")
    else:
        print("❌ FAILED: Your token has issues")
        print("❌ Check the errors above")
        print("\n🔧 Next steps:")
        print("1. Go to Shopify Admin → Settings → Apps and sales channels")
        print("2. Click 'Develop apps'")
        print("3. Check your private app status")
        print("4. Regenerate access token if needed")
    
    return shop_success and orders_success

if __name__ == "__main__":
    try:
        main()
    except KeyboardInterrupt:
        print("\n\nTest interrupted by user")
    except Exception as e:
        print(f"\n❌ Unexpected error: {e}")