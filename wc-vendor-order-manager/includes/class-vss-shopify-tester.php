<?php
/**
 * VSS Shopify API Tester
 * 
 * Comprehensive testing tool for Shopify API integration
 * 
 * @package VendorOrderManager
 * @since 8.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class VSS_Shopify_Tester {

    /**
     * Initialize tester
     */
    public static function init() {
        // Add AJAX handler
        add_action( 'wp_ajax_vss_comprehensive_shopify_test', [ __CLASS__, 'run_comprehensive_test' ] );
        
        // Add admin page
        add_action( 'admin_menu', [ __CLASS__, 'add_tester_page' ] );
    }

    /**
     * Add tester page to admin menu
     */
    public static function add_tester_page() {
        add_submenu_page(
            'vss_external_orders',
            'Test Shopify API',
            'API Tester',
            'manage_options',
            'vss_shopify_tester',
            [ __CLASS__, 'render_tester_page' ]
        );
    }

    /**
     * Render tester page
     */
    public static function render_tester_page() {
        ?>
        <div class="wrap">
            <h1>🧪 Shopify API Comprehensive Tester</h1>
            <p>This tool will test your Shopify API connection and verify all necessary permissions.</p>
            
            <div class="card">
                <h2>Current Configuration</h2>
                <table class="form-table">
                    <tr>
                        <th>Store Name:</th>
                        <td><?php echo esc_html( get_option( 'vss_shopify_store_name', 'Not set' ) ); ?>.myshopify.com</td>
                    </tr>
                    <tr>
                        <th>Access Token:</th>
                        <td><?php 
                        $token = get_option( 'vss_shopify_access_token', '' );
                        echo $token ? esc_html( substr( $token, 0, 15 ) ) . '...' : 'Not set';
                        ?></td>
                    </tr>
                </table>
                
                <p>
                    <button type="button" class="button button-primary" id="run-comprehensive-test">
                        🧪 Run Comprehensive API Test
                    </button>
                    <button type="button" class="button button-secondary" id="test-with-custom-token">
                        🔧 Test Custom Token
                    </button>
                </p>
            </div>
            
            <!-- Custom Token Test Form -->
            <div class="card" id="custom-token-form" style="display: none;">
                <h2>Test Custom Token</h2>
                <form id="custom-test-form">
                    <table class="form-table">
                        <tr>
                            <th><label for="test-store-name">Store Name</label></th>
                            <td>
                                <input type="text" id="test-store-name" value="qstomize" class="regular-text" />
                                <span>.myshopify.com</span>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="test-token">Access Token</label></th>
                            <td>
                                <input type="text" id="test-token" value="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" class="regular-text" />
                                <p class="description">Enter the token you want to test</p>
                            </td>
                        </tr>
                    </table>
                    <p>
                        <button type="button" class="button button-primary" id="run-custom-test">
                            Test This Token
                        </button>
                        <button type="button" class="button button-secondary" id="cancel-custom-test">
                            Cancel
                        </button>
                    </p>
                </form>
            </div>

            <!-- Test Results -->
            <div class="card" id="test-results" style="display: none;">
                <h2>🧪 Test Results</h2>
                <div id="test-output">
                    <!-- Results will be populated here -->
                </div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('#run-comprehensive-test').on('click', function() {
                runShopifyTest(false);
            });
            
            $('#test-with-custom-token').on('click', function() {
                $('#custom-token-form').show();
            });
            
            $('#cancel-custom-test').on('click', function() {
                $('#custom-token-form').hide();
            });
            
            $('#run-custom-test').on('click', function() {
                runShopifyTest(true);
            });
            
            function runShopifyTest(useCustom) {
                const $button = useCustom ? $('#run-custom-test') : $('#run-comprehensive-test');
                const originalText = $button.text();
                
                $button.prop('disabled', true).text('🔄 Testing...');
                $('#test-results').show();
                $('#test-output').html('<div class="notice notice-info"><p>🔄 Running comprehensive API tests...</p></div>');
                
                const data = {
                    action: 'vss_comprehensive_shopify_test',
                    nonce: '<?php echo wp_create_nonce( 'vss_shopify_test' ); ?>'
                };
                
                if (useCustom) {
                    data.custom_store = $('#test-store-name').val();
                    data.custom_token = $('#test-token').val();
                }
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: data,
                    timeout: 60000, // 1 minute timeout
                    success: function(response) {
                        if (response.success) {
                            $('#test-output').html(response.data.html);
                        } else {
                            $('#test-output').html('<div class="notice notice-error"><p>❌ Test failed: ' + (response.data.message || 'Unknown error') + '</p></div>');
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#test-output').html('<div class="notice notice-error"><p>❌ Request failed: ' + error + '</p></div>');
                    },
                    complete: function() {
                        $button.prop('disabled', false).text(originalText);
                        if (useCustom) {
                            $('#custom-token-form').hide();
                        }
                    }
                });
            }
        });
        </script>
        
        <style>
        .test-section {
            background: #f9f9f9;
            border-left: 4px solid #2271b1;
            padding: 15px;
            margin: 10px 0;
        }
        .test-success {
            border-left-color: #00a32a;
            background: #f0f6fc;
        }
        .test-failed {
            border-left-color: #d63638;
            background: #fcf0f1;
        }
        .test-warning {
            border-left-color: #dba617;
            background: #fcf9e8;
        }
        .test-details {
            margin-left: 20px;
            font-family: monospace;
            font-size: 12px;
            color: #666;
        }
        .permission-check {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
            margin-right: 5px;
        }
        .permission-ok {
            background: #d1e7dd;
            color: #0f5132;
        }
        .permission-missing {
            background: #f8d7da;
            color: #721c24;
        }
        </style>
        <?php
    }

    /**
     * Run comprehensive test via AJAX
     */
    public static function run_comprehensive_test() {
        check_ajax_referer( 'vss_shopify_test', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Insufficient permissions' );
        }

        // Get credentials
        if ( isset( $_POST['custom_store'] ) && isset( $_POST['custom_token'] ) ) {
            $store_name = sanitize_text_field( $_POST['custom_store'] );
            $access_token = sanitize_text_field( $_POST['custom_token'] );
            $is_custom = true;
        } else {
            $store_name = get_option( 'vss_shopify_store_name' );
            $access_token = get_option( 'vss_shopify_access_token' );
            $is_custom = false;
        }

        if ( ! $store_name || ! $access_token ) {
            wp_send_json_error( 'Store name and access token are required' );
        }

        $results = self::run_api_tests( $store_name, $access_token );
        
        // If custom test was successful, offer to save
        if ( $is_custom && $results['overall_success'] ) {
            update_option( 'vss_shopify_store_name', $store_name );
            update_option( 'vss_shopify_access_token', $access_token );
            delete_option( 'vss_shopify_api_errors' );
            $results['saved_message'] = '✅ Custom credentials tested successfully and saved to WordPress!';
        }

        $html = self::format_test_results( $results );
        
        wp_send_json_success( [ 'html' => $html ] );
    }

    /**
     * Run API tests
     */
    private static function run_api_tests( $store_name, $access_token ) {
        $base_url = "https://{$store_name}.myshopify.com/admin/api/2023-10";
        $results = [];
        $overall_success = true;

        // Test 1: Shop info (critical)
        $shop_result = self::test_api_endpoint( 
            "{$base_url}/shop.json", 
            $access_token, 
            'Shop Information', 
            'Basic connection test'
        );
        $results['shop'] = $shop_result;
        
        if ( ! $shop_result['success'] ) {
            $overall_success = false;
            $results['critical_failure'] = true;
            return [ 'tests' => $results, 'overall_success' => $overall_success ];
        }

        // Test 2: Orders (critical for integration)
        $orders_result = self::test_api_endpoint(
            "{$base_url}/orders.json?limit=1&status=any",
            $access_token,
            'Orders Access',
            'Required for order importing'
        );
        $results['orders'] = $orders_result;
        if ( ! $orders_result['success'] ) $overall_success = false;

        // Test 3: Products
        $products_result = self::test_api_endpoint(
            "{$base_url}/products.json?limit=1",
            $access_token,
            'Products Access',
            'Useful for product management'
        );
        $results['products'] = $products_result;

        // Test 4: Collections
        $collections_result = self::test_api_endpoint(
            "{$base_url}/collections.json?limit=1",
            $access_token,
            'Collections Access',
            'Useful for categories'
        );
        $results['collections'] = $collections_result;

        // Test 5: Fulfillments (critical for tracking sync)
        if ( $orders_result['success'] && isset( $orders_result['data']['orders'] ) && ! empty( $orders_result['data']['orders'] ) ) {
            $test_order_id = $orders_result['data']['orders'][0]['id'];
            $fulfillments_result = self::test_api_endpoint(
                "{$base_url}/orders/{$test_order_id}/fulfillments.json",
                $access_token,
                'Fulfillments Access',
                'Required for tracking sync'
            );
            $results['fulfillments'] = $fulfillments_result;
            if ( ! $fulfillments_result['success'] ) $overall_success = false;
        }

        // Test 6: Rate limits
        $rate_info = self::check_rate_limits( "{$base_url}/shop.json", $access_token );
        $results['rate_limits'] = $rate_info;

        return [ 'tests' => $results, 'overall_success' => $overall_success ];
    }

    /**
     * Test individual API endpoint
     */
    private static function test_api_endpoint( $url, $token, $name, $description ) {
        $response = wp_remote_get( $url, [
            'headers' => [
                'X-Shopify-Access-Token' => $token,
                'Content-Type' => 'application/json'
            ],
            'timeout' => 30
        ] );

        if ( is_wp_error( $response ) ) {
            return [
                'success' => false,
                'name' => $name,
                'description' => $description,
                'error' => $response->get_error_message(),
                'type' => 'connection_error'
            ];
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );

        if ( $code === 200 ) {
            $data = json_decode( $body, true );
            return [
                'success' => true,
                'name' => $name,
                'description' => $description,
                'code' => $code,
                'data' => $data
            ];
        } else {
            $error_data = json_decode( $body, true );
            $error_message = $error_data['errors'] ?? $body;
            
            return [
                'success' => false,
                'name' => $name,
                'description' => $description,
                'code' => $code,
                'error' => $error_message,
                'type' => self::classify_error( $code )
            ];
        }
    }

    /**
     * Check rate limits
     */
    private static function check_rate_limits( $url, $token ) {
        $response = wp_remote_get( $url, [
            'headers' => [
                'X-Shopify-Access-Token' => $token,
                'Content-Type' => 'application/json'
            ],
            'timeout' => 30
        ] );

        if ( ! is_wp_error( $response ) ) {
            $headers = wp_remote_retrieve_headers( $response );
            $bucket_size = $headers['x-shopify-shop-api-call-limit'] ?? 'Unknown';
            
            return [
                'bucket_size' => $bucket_size,
                'success' => true
            ];
        }

        return [ 'success' => false ];
    }

    /**
     * Classify error type
     */
    private static function classify_error( $code ) {
        switch ( $code ) {
            case 401:
                return 'invalid_token';
            case 403:
                return 'permission_denied';
            case 404:
                return 'not_found';
            case 429:
                return 'rate_limited';
            default:
                return 'api_error';
        }
    }

    /**
     * Format test results as HTML
     */
    private static function format_test_results( $results ) {
        $html = '<div class="test-results-container">';
        
        if ( isset( $results['saved_message'] ) ) {
            $html .= '<div class="notice notice-success"><p>' . esc_html( $results['saved_message'] ) . '</p></div>';
        }

        if ( $results['overall_success'] ) {
            $html .= '<div class="test-section test-success">';
            $html .= '<h3>🎉 Overall Status: SUCCESS</h3>';
            $html .= '<p>Your Shopify API integration is working correctly!</p>';
            $html .= '</div>';
        } else {
            $html .= '<div class="test-section test-failed">';
            $html .= '<h3>❌ Overall Status: ISSUES DETECTED</h3>';
            $html .= '<p>Some tests failed. Please review the details below.</p>';
            $html .= '</div>';
        }

        // Individual test results
        foreach ( $results['tests'] as $test_key => $test ) {
            if ( $test_key === 'rate_limits' ) {
                $html .= self::format_rate_limit_result( $test );
                continue;
            }

            $class = $test['success'] ? 'test-success' : 'test-failed';
            $icon = $test['success'] ? '✅' : '❌';
            
            $html .= "<div class='test-section {$class}'>";
            $html .= "<h4>{$icon} {$test['name']}</h4>";
            $html .= "<p>{$test['description']}</p>";
            
            if ( $test['success'] ) {
                if ( isset( $test['data']['shop'] ) ) {
                    $shop = $test['data']['shop'];
                    $html .= "<div class='test-details'>";
                    $html .= "Shop: " . esc_html( $shop['name'] ?? 'Unknown' ) . "<br>";
                    $html .= "Domain: " . esc_html( $shop['domain'] ?? 'Unknown' ) . "<br>";
                    $html .= "Plan: " . esc_html( $shop['plan_name'] ?? 'Unknown' ) . "<br>";
                    $html .= "</div>";
                } elseif ( isset( $test['data']['orders'] ) ) {
                    $html .= "<div class='test-details'>";
                    $html .= "Orders found: " . count( $test['data']['orders'] ) . "<br>";
                    $html .= "</div>";
                } elseif ( isset( $test['data']['products'] ) ) {
                    $html .= "<div class='test-details'>";
                    $html .= "Products found: " . count( $test['data']['products'] ) . "<br>";
                    $html .= "</div>";
                }
            } else {
                $html .= "<div class='test-details'>";
                $html .= "Error: " . esc_html( $test['error'] ) . "<br>";
                if ( isset( $test['code'] ) ) {
                    $html .= "HTTP Code: " . esc_html( $test['code'] ) . "<br>";
                }
                
                // Add troubleshooting tips
                if ( isset( $test['type'] ) ) {
                    $html .= self::get_troubleshooting_tip( $test['type'] );
                }
                $html .= "</div>";
            }
            
            $html .= "</div>";
        }

        $html .= '</div>';
        
        return $html;
    }

    /**
     * Format rate limit results
     */
    private static function format_rate_limit_result( $rate_info ) {
        if ( ! $rate_info['success'] ) {
            return '';
        }

        $html = "<div class='test-section'>";
        $html .= "<h4>📊 API Rate Limits</h4>";
        
        $bucket_size = $rate_info['bucket_size'];
        if ( strpos( $bucket_size, '/' ) !== false ) {
            list( $used, $total ) = explode( '/', $bucket_size );
            $percentage = ( $used / $total ) * 100;
            
            $html .= "<p>Usage: {$percentage}% ({$used}/{$total} calls used)</p>";
            
            if ( $percentage > 80 ) {
                $html .= "<p style='color: orange;'>⚠️ High API usage - may hit rate limits soon</p>";
            } else {
                $html .= "<p style='color: green;'>✅ Rate limit usage is healthy</p>";
            }
        } else {
            $html .= "<p>Rate limit info: " . esc_html( $bucket_size ) . "</p>";
        }
        
        $html .= "</div>";
        
        return $html;
    }

    /**
     * Get troubleshooting tip for error type
     */
    private static function get_troubleshooting_tip( $error_type ) {
        switch ( $error_type ) {
            case 'invalid_token':
                return "<strong>💡 Tip:</strong> Your access token is invalid or revoked. Generate a new one in your Shopify private app.<br>";
            case 'permission_denied':
                return "<strong>💡 Tip:</strong> Your private app lacks the required permissions or has been disabled.<br>";
            case 'not_found':
                return "<strong>💡 Tip:</strong> Check your store name or API endpoint URL.<br>";
            case 'rate_limited':
                return "<strong>💡 Tip:</strong> You're hitting Shopify's rate limits. Wait a moment before testing again.<br>";
            default:
                return "<strong>💡 Tip:</strong> Check your Shopify private app configuration.<br>";
        }
    }
}

// Initialize the tester
add_action( 'plugins_loaded', [ 'VSS_Shopify_Tester', 'init' ] );