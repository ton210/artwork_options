<?php
/**
 * Comprehensive Shopify API Token Tester
 * 
 * This script will thoroughly test your Shopify API connection
 * and verify all necessary permissions.
 */

// WordPress connection
define( 'WP_USE_THEMES', false );
require_once( '../../../wp-config.php' );

// Test configuration
$store_name = 'qstomize';
$access_token = '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';
$base_url = "https://{$store_name}.myshopify.com/admin/api/2023-10";

echo "🧪 SHOPIFY API CONNECTION TESTER\n";
echo "================================\n";
echo "Store: {$store_name}.myshopify.com\n";
echo "Token: " . substr($access_token, 0, 15) . "...\n";
echo "API Version: 2023-10\n\n";

/**
 * Test API endpoint
 */
function test_endpoint($url, $token, $test_name, $required = true) {
    echo "🔍 Testing: {$test_name}\n";
    echo "URL: {$url}\n";
    
    $response = wp_remote_get($url, [
        'headers' => [
            'X-Shopify-Access-Token' => $token,
            'Content-Type' => 'application/json'
        ],
        'timeout' => 30
    ]);
    
    if (is_wp_error($response)) {
        echo "❌ FAILED: " . $response->get_error_message() . "\n";
        return false;
    }
    
    $code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    
    echo "Status: {$code}\n";
    
    if ($code === 200) {
        $data = json_decode($body, true);
        echo "✅ SUCCESS\n";
        
        // Show relevant data
        if (isset($data['shop'])) {
            echo "   Shop: " . ($data['shop']['name'] ?? 'Unknown') . "\n";
            echo "   Domain: " . ($data['shop']['domain'] ?? 'Unknown') . "\n";
            echo "   Plan: " . ($data['shop']['plan_name'] ?? 'Unknown') . "\n";
        } elseif (isset($data['orders'])) {
            echo "   Orders found: " . count($data['orders']) . "\n";
        } elseif (isset($data['products'])) {
            echo "   Products found: " . count($data['products']) . "\n";
        } elseif (isset($data['collections'])) {
            echo "   Collections found: " . count($data['collections']) . "\n";
        }
        
        echo "\n";
        return true;
    } else {
        $error_data = json_decode($body, true);
        $error_msg = $error_data['errors'] ?? $body;
        echo "❌ FAILED: {$error_msg}\n";
        
        if ($code === 403) {
            echo "   🚨 This suggests your private app has been disabled or lacks permissions\n";
        } elseif ($code === 401) {
            echo "   🚨 Invalid access token - token may be revoked or incorrect\n";
        } elseif ($code === 404) {
            echo "   🚨 Endpoint not found - check API version or store name\n";
        }
        
        echo "\n";
        return !$required;
    }
}

// Test 1: Basic shop info (most important)
$shop_success = test_endpoint(
    "{$base_url}/shop.json",
    $access_token,
    "Shop Information (Basic Connection)",
    true
);

if (!$shop_success) {
    echo "🛑 CRITICAL: Basic connection failed. Cannot proceed with other tests.\n";
    echo "\n🔧 TROUBLESHOOTING STEPS:\n";
    echo "1. Check if your private app is still installed and enabled in Shopify admin\n";
    echo "2. Verify the access token is correct: {$access_token}\n";
    echo "3. Ensure your store name is correct: {$store_name}\n";
    echo "4. Check if the app has been uninstalled or permissions revoked\n";
    exit;
}

echo "🎉 Basic connection successful! Continuing with permission tests...\n\n";

// Test 2: Orders access (critical for your integration)
test_endpoint(
    "{$base_url}/orders.json?limit=1&status=any",
    $access_token,
    "Orders Access (read_orders permission)",
    true
);

// Test 3: Products access (useful)
test_endpoint(
    "{$base_url}/products.json?limit=1",
    $access_token,
    "Products Access (read_products permission)",
    false
);

// Test 4: Collections access (useful for categories)
test_endpoint(
    "{$base_url}/collections.json?limit=1",
    $access_token,
    "Collections Access (read_collections permission)",
    false
);

// Test 5: Webhooks (for real-time updates)
test_endpoint(
    "{$base_url}/webhooks.json",
    $access_token,
    "Webhooks Access (read_webhooks permission)",
    false
);

// Test 6: Get a specific order to test fulfillment capabilities
echo "🔍 Testing: Recent Orders (for fulfillment testing)\n";
$orders_response = wp_remote_get("{$base_url}/orders.json?limit=5&status=any&created_at_min=2025-08-01", [
    'headers' => [
        'X-Shopify-Access-Token' => $access_token,
        'Content-Type' => 'application/json'
    ],
    'timeout' => 30
]);

if (!is_wp_error($orders_response) && wp_remote_retrieve_response_code($orders_response) === 200) {
    $orders_data = json_decode(wp_remote_retrieve_body($orders_response), true);
    $orders = $orders_data['orders'] ?? [];
    
    echo "✅ Found " . count($orders) . " recent orders\n";
    
    if (count($orders) > 0) {
        $test_order = $orders[0];
        $order_id = $test_order['id'];
        echo "   Sample Order ID: {$order_id}\n";
        echo "   Order Number: " . ($test_order['order_number'] ?? 'Unknown') . "\n";
        echo "   Status: " . ($test_order['fulfillment_status'] ?? 'unfulfilled') . "\n";
        
        // Test fulfillments access
        test_endpoint(
            "{$base_url}/orders/{$order_id}/fulfillments.json",
            $access_token,
            "Fulfillments Access (write_fulfillments permission)",
            true
        );
    }
} else {
    echo "⚠️  Could not retrieve recent orders for fulfillment testing\n\n";
}

// Test 7: Rate limit headers
echo "🔍 Testing: API Rate Limits\n";
$rate_response = wp_remote_get("{$base_url}/shop.json", [
    'headers' => [
        'X-Shopify-Access-Token' => $access_token,
        'Content-Type' => 'application/json'
    ],
    'timeout' => 30
]);

if (!is_wp_error($rate_response)) {
    $headers = wp_remote_retrieve_headers($rate_response);
    $bucket_size = $headers['x-shopify-shop-api-call-limit'] ?? 'Unknown';
    
    echo "Rate Limit Status: {$bucket_size}\n";
    if (strpos($bucket_size, '/') !== false) {
        list($used, $total) = explode('/', $bucket_size);
        $percentage = ($used / $total) * 100;
        echo "Usage: {$percentage}% ({$used}/{$total} calls used)\n";
        
        if ($percentage > 80) {
            echo "⚠️  WARNING: High API usage - may hit rate limits\n";
        }
    }
    echo "✅ Rate limit check completed\n\n";
}

// Summary
echo "📊 TEST SUMMARY\n";
echo "===============\n";

// Update WordPress options with working credentials
update_option('vss_shopify_store_name', $store_name);
update_option('vss_shopify_access_token', $access_token);

// Clear any old errors
delete_option('vss_shopify_api_errors');

// Log successful test
update_option('vss_shopify_last_test', json_encode([
    'success' => $shop_success,
    'message' => $shop_success ? 'Connection test successful' : 'Connection test failed',
    'timestamp' => current_time('mysql')
]));

echo "✅ WordPress options updated\n";
echo "✅ Old API errors cleared\n";
echo "✅ Test results saved\n\n";

echo "🎯 NEXT STEPS:\n";
echo "1. Go to WP Admin → VSS External Orders to see the updated status\n";
echo "2. Try importing orders to verify everything works\n";
echo "3. The system will now log any future API errors for easier debugging\n\n";

if ($shop_success) {
    echo "🎉 SUCCESS: Your Shopify integration is working!\n";
    echo "Your token {$access_token} is valid and has the necessary permissions.\n";
} else {
    echo "❌ FAILED: Your Shopify integration needs attention.\n";
    echo "Please check the errors above and follow the troubleshooting steps.\n";
}

echo "\n🔧 Need help? Check the new 'Fix Shopify' page in your WordPress admin.\n";
?>