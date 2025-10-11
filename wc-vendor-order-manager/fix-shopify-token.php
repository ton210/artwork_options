<?php
/**
 * Quick fix script to update Shopify token
 * Run this once to fix the token issue
 */

// WordPress database connection
define( 'WP_USE_THEMES', false );
require_once( '../../../wp-config.php' );

// Update the Shopify token
$new_token = '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';
$store_name = 'qstomize';

update_option( 'vss_shopify_access_token', $new_token );
update_option( 'vss_shopify_store_name', $store_name );

// Clear any existing API errors
delete_option( 'vss_shopify_api_errors' );

echo "✅ Shopify credentials updated successfully!\n";
echo "Store: {$store_name}.myshopify.com\n";
echo "Token: " . substr( $new_token, 0, 10 ) . "...\n";
echo "\nYou can now test the connection in the WordPress admin.\n";

// Test the connection immediately
$test_url = "https://{$store_name}.myshopify.com/admin/api/2023-10/shop.json";

$response = wp_remote_get( $test_url, [
    'headers' => [
        'X-Shopify-Access-Token' => $new_token,
        'Content-Type' => 'application/json'
    ],
    'timeout' => 15
] );

if ( is_wp_error( $response ) ) {
    echo "\n❌ Connection test failed: " . $response->get_error_message() . "\n";
} else {
    $response_code = wp_remote_retrieve_response_code( $response );
    if ( $response_code === 200 ) {
        $shop_data = json_decode( wp_remote_retrieve_body( $response ), true );
        echo "\n✅ Connection test successful!\n";
        echo "Shop: " . ( $shop_data['shop']['name'] ?? 'Unknown' ) . "\n";
        echo "Connected to: " . ( $shop_data['shop']['domain'] ?? 'Unknown' ) . "\n";
    } else {
        $error_body = wp_remote_retrieve_body( $response );
        echo "\n❌ Connection test failed with code {$response_code}\n";
        echo "Response: {$error_body}\n";
    }
}
?>