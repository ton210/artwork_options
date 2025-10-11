#!/bin/bash
# Quick Shopify API Token Test Script

echo "🧪 TESTING SHOPIFY API TOKEN"
echo "============================="

STORE="qstomize"
TOKEN="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"
API_URL="https://${STORE}.myshopify.com/admin/api/2023-10"

echo "Store: ${STORE}.myshopify.com"
echo "Token: ${TOKEN:0:15}..."
echo ""

# Test 1: Shop info (basic connection)
echo "🔍 Test 1: Basic Connection (Shop Info)"
echo "URL: ${API_URL}/shop.json"

RESPONSE=$(curl -s -w "\nHTTP_CODE:%{http_code}" \
  -H "X-Shopify-Access-Token: ${TOKEN}" \
  -H "Content-Type: application/json" \
  "${API_URL}/shop.json")

HTTP_CODE=$(echo "$RESPONSE" | grep "HTTP_CODE:" | cut -d: -f2)
BODY=$(echo "$RESPONSE" | sed '/HTTP_CODE:/d')

echo "Status Code: $HTTP_CODE"

if [ "$HTTP_CODE" = "200" ]; then
    echo "✅ SUCCESS - Token is working!"
    
    # Extract shop info
    SHOP_NAME=$(echo "$BODY" | python3 -c "
import sys, json
try:
    data = json.load(sys.stdin)
    print('Shop Name:', data['shop']['name'])
    print('Domain:', data['shop']['domain'])
    print('Plan:', data['shop']['plan_name'])
except:
    print('Could not parse shop data')
" 2>/dev/null)
    echo "$SHOP_NAME"
    
else
    echo "❌ FAILED"
    echo "Response: $BODY"
    
    if [ "$HTTP_CODE" = "403" ]; then
        echo ""
        echo "🚨 ERROR 403: API Access Disabled"
        echo "This means your private app has been disabled or lacks permissions"
    elif [ "$HTTP_CODE" = "401" ]; then
        echo ""
        echo "🚨 ERROR 401: Unauthorized" 
        echo "Your access token is invalid or revoked"
    fi
    
    exit 1
fi

echo ""

# Test 2: Orders access (critical for integration)
echo "🔍 Test 2: Orders Access"
echo "URL: ${API_URL}/orders.json?limit=1"

ORDERS_RESPONSE=$(curl -s -w "\nHTTP_CODE:%{http_code}" \
  -H "X-Shopify-Access-Token: ${TOKEN}" \
  -H "Content-Type: application/json" \
  "${API_URL}/orders.json?limit=1")

ORDERS_HTTP_CODE=$(echo "$ORDERS_RESPONSE" | grep "HTTP_CODE:" | cut -d: -f2)
ORDERS_BODY=$(echo "$ORDERS_RESPONSE" | sed '/HTTP_CODE:/d')

echo "Status Code: $ORDERS_HTTP_CODE"

if [ "$ORDERS_HTTP_CODE" = "200" ]; then
    echo "✅ SUCCESS - Orders access working!"
    
    # Count orders
    ORDER_COUNT=$(echo "$ORDERS_BODY" | python3 -c "
import sys, json
try:
    data = json.load(sys.stdin)
    print('Orders found:', len(data['orders']))
    if data['orders']:
        order = data['orders'][0]
        print('Sample Order #:', order.get('order_number', 'Unknown'))
        print('Status:', order.get('fulfillment_status', 'unfulfilled'))
except:
    print('Could not parse orders data')
" 2>/dev/null)
    echo "$ORDER_COUNT"
else
    echo "❌ FAILED - Cannot access orders"
    echo "Response: $ORDERS_BODY"
fi

echo ""
echo "📊 SUMMARY"
echo "=========="

if [ "$HTTP_CODE" = "200" ] && [ "$ORDERS_HTTP_CODE" = "200" ]; then
    echo "🎉 SUCCESS: Your Shopify API token is working correctly!"
    echo "✅ Basic connection: OK"
    echo "✅ Orders access: OK"
    echo ""
    echo "Your WordPress plugin should work now."
else
    echo "❌ FAILED: Your token has issues"
    echo ""
    echo "🔧 NEXT STEPS:"
    echo "1. Go to: https://qstomize.myshopify.com/admin"
    echo "2. Navigate to: Settings → Apps and sales channels → Develop apps"
    echo "3. Check if your private app is enabled"
    echo "4. Verify these permissions are enabled:"
    echo "   - read_orders"
    echo "   - write_fulfillments"
    echo "5. If needed, generate a new access token"
fi

echo ""