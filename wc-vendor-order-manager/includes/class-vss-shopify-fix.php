<?php
/**
 * VSS Shopify API Fix
 * 
 * Fixes Shopify API connection issues and provides proper error handling
 * 
 * @package VendorOrderManager
 * @since 8.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class VSS_Shopify_Fix {

    /**
     * Initialize Shopify fixes
     */
    public static function init() {
        // Add admin notice for API issues
        add_action( 'admin_notices', [ __CLASS__, 'show_api_notices' ] );
        
        // Add AJAX handler for token validation
        add_action( 'wp_ajax_vss_validate_shopify_token', [ __CLASS__, 'validate_shopify_token' ] );
        
        // Add admin menu for fixing Shopify settings
        add_action( 'admin_menu', [ __CLASS__, 'add_fix_menu' ] );
        
        // Hook to update settings
        add_action( 'admin_init', [ __CLASS__, 'handle_settings_update' ] );
        
        // Check token validity periodically
        if ( ! wp_next_scheduled( 'vss_check_shopify_token' ) ) {
            wp_schedule_event( time(), 'daily', 'vss_check_shopify_token' );
        }
        add_action( 'vss_check_shopify_token', [ __CLASS__, 'check_token_validity' ] );
    }

    /**
     * Show admin notices for API issues
     */
    public static function show_api_notices() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // Check if we have API errors
        $api_errors = get_option( 'vss_shopify_api_errors', [] );
        if ( ! empty( $api_errors ) ) {
            $recent_errors = array_slice( $api_errors, -3 ); // Show last 3 errors
            
            echo '<div class="notice notice-error is-dismissible">';
            echo '<h3>🚨 Shopify API Connection Issues</h3>';
            echo '<p><strong>Your Shopify integration is currently experiencing problems:</strong></p>';
            echo '<ul>';
            foreach ( $recent_errors as $error ) {
                echo '<li>' . esc_html( $error['message'] ) . ' <em>(' . esc_html( $error['timestamp'] ) . ')</em></li>';
            }
            echo '</ul>';
            echo '<p><strong>Common causes and solutions:</strong></p>';
            echo '<ol>';
            echo '<li><strong>Disabled Private App:</strong> Check if your Shopify private app is still active in your Shopify admin.</li>';
            echo '<li><strong>Invalid Token:</strong> Your access token may have been regenerated or revoked.</li>';
            echo '<li><strong>Insufficient Permissions:</strong> Your private app needs "read_orders" and "write_fulfillments" permissions.</li>';
            echo '<li><strong>Store Name Changed:</strong> Verify your store URL is correct.</li>';
            echo '</ol>';
            echo '<p><a href="' . admin_url( 'admin.php?page=vss_shopify_fix' ) . '" class="button button-primary">Fix Shopify Settings</a></p>';
            echo '</div>';
        }
    }

    /**
     * Add fix menu
     */
    public static function add_fix_menu() {
        add_submenu_page(
            'vss_external_orders',
            'Fix Shopify Connection',
            'Fix Shopify',
            'manage_options',
            'vss_shopify_fix',
            [ __CLASS__, 'render_fix_page' ]
        );
    }

    /**
     * Render fix page
     */
    public static function render_fix_page() {
        ?>
        <div class="wrap">
            <h1>🔧 Fix Shopify Connection</h1>
            
            <div class="card">
                <h2>Current Status</h2>
                <?php self::display_current_status(); ?>
            </div>

            <div class="card">
                <h2>Update Shopify Credentials</h2>
                <form method="post" action="">
                    <?php wp_nonce_field( 'vss_update_shopify_settings' ); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="shopify_store_url">Store URL</label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="shopify_store_url" 
                                       name="shopify_store_url" 
                                       value="<?php echo esc_attr( get_option( 'vss_shopify_store_name', 'qstomize' ) ); ?>" 
                                       class="regular-text" 
                                       placeholder="your-store-name" />
                                <span>.myshopify.com</span>
                                <p class="description">
                                    Just the store name part (e.g., "qstomize" from qstomize.myshopify.com)
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="shopify_access_token">Access Token</label>
                            </th>
                            <td>
                                <input type="password" 
                                       id="shopify_access_token" 
                                       name="shopify_access_token" 
                                       value="" 
                                       class="regular-text" 
                                       placeholder="shpat_..." />
                                <p class="description">
                                    Your new Shopify private app access token (starts with "shpat_")
                                </p>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <input type="submit" name="update_shopify_settings" class="button-primary" value="Update & Test Connection" />
                        <input type="submit" name="test_current_settings" class="button-secondary" value="Test Current Settings" />
                        <input type="submit" name="clear_api_errors" class="button-secondary" value="Clear Error Log" />
                    </p>
                </form>
            </div>

            <div class="card">
                <h2>📋 How to Fix Your Shopify Connection</h2>
                <div class="vss-fix-steps">
                    <h3>Step 1: Check Your Private App Status</h3>
                    <ol>
                        <li>Go to your Shopify admin: <code>https://qstomize.myshopify.com/admin</code></li>
                        <li>Navigate to <strong>Settings</strong> → <strong>Apps and sales channels</strong></li>
                        <li>Click <strong>Develop apps</strong></li>
                        <li>Find your private app and check if it's enabled</li>
                    </ol>

                    <h3>Step 2: Create New Private App (if needed)</h3>
                    <ol>
                        <li>In Shopify admin, go to <strong>Settings</strong> → <strong>Apps and sales channels</strong></li>
                        <li>Click <strong>Develop apps</strong></li>
                        <li>Click <strong>Create an app</strong></li>
                        <li>Name it "Order Management Integration"</li>
                        <li>Click <strong>Configure Admin API scopes</strong></li>
                        <li>Enable these permissions:
                            <ul>
                                <li><code>read_orders</code> - Read orders</li>
                                <li><code>write_fulfillments</code> - Modify fulfillments</li>
                                <li><code>read_products</code> - Read products (optional)</li>
                            </ul>
                        </li>
                        <li>Click <strong>Save</strong></li>
                        <li>Click <strong>Install app</strong></li>
                        <li>Copy the <strong>Admin API access token</strong></li>
                    </ol>

                    <h3>Step 3: Update Credentials Above</h3>
                    <p>Paste your new access token in the form above and click "Update & Test Connection".</p>
                </div>
            </div>

            <div class="card">
                <h2>🔍 Recent API Errors</h2>
                <?php self::display_recent_errors(); ?>
            </div>
        </div>

        <style>
        .vss-fix-steps {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .vss-fix-steps h3 {
            color: #0073aa;
            margin-top: 20px;
        }
        .vss-fix-steps code {
            background: #fff;
            padding: 2px 6px;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        .vss-status-good { color: #46b450; font-weight: bold; }
        .vss-status-bad { color: #dc3232; font-weight: bold; }
        .vss-error-log {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 3px;
            padding: 10px;
            max-height: 300px;
            overflow-y: auto;
            font-family: monospace;
            font-size: 12px;
        }
        .vss-error-item {
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .vss-error-time {
            color: #666;
            font-size: 11px;
        }
        </style>
        <?php
    }

    /**
     * Display current status
     */
    private static function display_current_status() {
        $store_name = get_option( 'vss_shopify_store_name' );
        $access_token = get_option( 'vss_shopify_access_token' );
        
        echo '<table class="form-table">';
        echo '<tr>';
        echo '<th>Store Name:</th>';
        echo '<td>' . ( $store_name ? esc_html( $store_name ) . '.myshopify.com' : '<span class="vss-status-bad">Not configured</span>' ) . '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th>Access Token:</th>';
        echo '<td>' . ( $access_token ? 
            '<span class="vss-status-good">Configured</span> (' . esc_html( substr( $access_token, 0, 10 ) ) . '...)' : 
            '<span class="vss-status-bad">Not configured</span>' 
        ) . '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th>Last Test:</th>';
        echo '<td>';
        
        $last_test = get_option( 'vss_shopify_last_test', '' );
        if ( $last_test ) {
            $test_data = json_decode( $last_test, true );
            if ( $test_data && isset( $test_data['success'] ) ) {
                echo $test_data['success'] ? 
                    '<span class="vss-status-good">✓ Connected</span>' : 
                    '<span class="vss-status-bad">✗ Failed</span>';
                echo ' - ' . esc_html( $test_data['timestamp'] );
                if ( ! $test_data['success'] ) {
                    echo '<br><small>' . esc_html( $test_data['error'] ) . '</small>';
                }
            }
        } else {
            echo 'Never tested';
        }
        echo '</td>';
        echo '</tr>';
        echo '</table>';
    }

    /**
     * Display recent errors
     */
    private static function display_recent_errors() {
        $errors = get_option( 'vss_shopify_api_errors', [] );
        
        if ( empty( $errors ) ) {
            echo '<p class="vss-status-good">No recent API errors! 🎉</p>';
            return;
        }

        echo '<div class="vss-error-log">';
        $recent_errors = array_reverse( array_slice( $errors, -20 ) ); // Last 20 errors, newest first
        
        foreach ( $recent_errors as $error ) {
            echo '<div class="vss-error-item">';
            echo '<div class="vss-error-time">' . esc_html( $error['timestamp'] ) . '</div>';
            echo '<div>' . esc_html( $error['message'] ) . '</div>';
            if ( ! empty( $error['details'] ) ) {
                echo '<div style="color: #666; font-size: 11px; margin-top: 5px;">' . esc_html( $error['details'] ) . '</div>';
            }
            echo '</div>';
        }
        echo '</div>';
        
        echo '<p><em>Showing last ' . count( $recent_errors ) . ' of ' . count( $errors ) . ' total errors</em></p>';
    }

    /**
     * Handle settings update
     */
    public static function handle_settings_update() {
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'vss_update_shopify_settings' ) ) {
            return;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        if ( isset( $_POST['clear_api_errors'] ) ) {
            delete_option( 'vss_shopify_api_errors' );
            add_action( 'admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>API error log cleared.</p></div>';
            } );
            return;
        }

        if ( isset( $_POST['test_current_settings'] ) ) {
            $result = self::test_connection();
            add_action( 'admin_notices', function() use ( $result ) {
                $class = $result['success'] ? 'notice-success' : 'notice-error';
                echo '<div class="notice ' . $class . ' is-dismissible"><p>' . esc_html( $result['message'] ) . '</p></div>';
            } );
            return;
        }

        if ( isset( $_POST['update_shopify_settings'] ) ) {
            $store_url = sanitize_text_field( $_POST['shopify_store_url'] );
            $access_token = sanitize_text_field( $_POST['shopify_access_token'] );

            if ( $store_url ) {
                update_option( 'vss_shopify_store_name', $store_url );
            }

            if ( $access_token ) {
                update_option( 'vss_shopify_access_token', $access_token );
            }

            // Test the new settings
            $result = self::test_connection();
            
            add_action( 'admin_notices', function() use ( $result ) {
                $class = $result['success'] ? 'notice-success' : 'notice-error';
                $message = $result['success'] ? 
                    'Settings updated and connection test successful!' : 
                    'Settings updated but connection test failed: ' . $result['message'];
                echo '<div class="notice ' . $class . ' is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
            } );
        }
    }

    /**
     * Test Shopify connection
     */
    public static function test_connection() {
        $store_name = get_option( 'vss_shopify_store_name' );
        $access_token = get_option( 'vss_shopify_access_token' );

        if ( ! $store_name || ! $access_token ) {
            $result = [
                'success' => false,
                'message' => 'Store name and access token are required',
                'error' => 'Missing credentials',
                'timestamp' => current_time( 'mysql' )
            ];
            update_option( 'vss_shopify_last_test', json_encode( $result ) );
            return $result;
        }

        // Test with a simple API call to get shop info
        $test_url = "https://{$store_name}.myshopify.com/admin/api/2023-10/shop.json";
        
        $response = wp_remote_get( $test_url, [
            'headers' => [
                'X-Shopify-Access-Token' => $access_token,
                'Content-Type' => 'application/json'
            ],
            'timeout' => 15
        ] );

        if ( is_wp_error( $response ) ) {
            $result = [
                'success' => false,
                'message' => 'Connection failed: ' . $response->get_error_message(),
                'error' => $response->get_error_message(),
                'timestamp' => current_time( 'mysql' )
            ];
        } else {
            $response_code = wp_remote_retrieve_response_code( $response );
            $response_body = wp_remote_retrieve_body( $response );
            
            if ( $response_code === 200 ) {
                $shop_data = json_decode( $response_body, true );
                $shop_name = $shop_data['shop']['name'] ?? 'Unknown';
                
                $result = [
                    'success' => true,
                    'message' => "Successfully connected to {$shop_name}",
                    'timestamp' => current_time( 'mysql' ),
                    'shop_name' => $shop_name
                ];
                
                // Clear API errors on successful connection
                delete_option( 'vss_shopify_api_errors' );
            } else {
                $error_data = json_decode( $response_body, true );
                $error_message = $error_data['errors'] ?? $response_body;
                
                $result = [
                    'success' => false,
                    'message' => "API returned error {$response_code}: {$error_message}",
                    'error' => $error_message,
                    'response_code' => $response_code,
                    'timestamp' => current_time( 'mysql' )
                ];
                
                // Log this error
                self::log_api_error( $result );
            }
        }

        update_option( 'vss_shopify_last_test', json_encode( $result ) );
        return $result;
    }

    /**
     * Log API error
     */
    public static function log_api_error( $error_data ) {
        $errors = get_option( 'vss_shopify_api_errors', [] );
        
        $errors[] = [
            'message' => $error_data['message'] ?? 'Unknown error',
            'details' => $error_data['error'] ?? '',
            'response_code' => $error_data['response_code'] ?? '',
            'timestamp' => current_time( 'mysql' )
        ];
        
        // Keep only last 100 errors
        if ( count( $errors ) > 100 ) {
            $errors = array_slice( $errors, -100 );
        }
        
        update_option( 'vss_shopify_api_errors', $errors );
    }

    /**
     * Check token validity (scheduled task)
     */
    public static function check_token_validity() {
        $result = self::test_connection();
        
        // If connection fails, try to notify admin
        if ( ! $result['success'] ) {
            // You could send an email here or create an admin notice
            error_log( 'VSS Shopify API Error: ' . $result['message'] );
        }
    }

    /**
     * Validate Shopify token via AJAX
     */
    public static function validate_shopify_token() {
        check_ajax_referer( 'vss_validate_token', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Insufficient permissions' );
        }

        $store_name = sanitize_text_field( $_POST['store_name'] );
        $access_token = sanitize_text_field( $_POST['access_token'] );

        if ( ! $store_name || ! $access_token ) {
            wp_send_json_error( 'Store name and access token are required' );
        }

        // Test the provided credentials
        $test_url = "https://{$store_name}.myshopify.com/admin/api/2023-10/shop.json";
        
        $response = wp_remote_get( $test_url, [
            'headers' => [
                'X-Shopify-Access-Token' => $access_token,
                'Content-Type' => 'application/json'
            ],
            'timeout' => 15
        ] );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( 'Connection failed: ' . $response->get_error_message() );
        }

        $response_code = wp_remote_retrieve_response_code( $response );
        
        if ( $response_code === 200 ) {
            $shop_data = json_decode( wp_remote_retrieve_body( $response ), true );
            wp_send_json_success( [
                'message' => 'Connection successful!',
                'shop_name' => $shop_data['shop']['name'] ?? 'Unknown'
            ] );
        } else {
            $error_data = json_decode( wp_remote_retrieve_body( $response ), true );
            $error_message = $error_data['errors'] ?? 'Unknown error';
            wp_send_json_error( "API Error {$response_code}: {$error_message}" );
        }
    }
}

// Initialize the fix
add_action( 'plugins_loaded', [ 'VSS_Shopify_Fix', 'init' ] );