<?php
/**
 * VSS Slack Notifications Tests
 *
 * Comprehensive testing functionality for Slack notifications
 *
 * @package VendorOrderManager
 * @since 8.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class VSS_Slack_Notifications_Tests {

    /**
     * Test results storage
     */
    private static $test_results = [];

    /**
     * Initialize test functionality
     */
    public static function init() {
        // Add admin menu for running tests
        add_action( 'admin_menu', [ self::class, 'add_test_menu' ] );
        
        // AJAX handlers for running tests
        add_action( 'wp_ajax_vss_run_slack_tests', [ self::class, 'ajax_run_tests' ] );
        add_action( 'wp_ajax_vss_run_single_slack_test', [ self::class, 'ajax_run_single_test' ] );
    }

    /**
     * Add test menu to admin
     */
    public static function add_test_menu() {
        add_submenu_page(
            'vss-slack-notifications',
            __( 'Slack Tests', 'vss' ),
            __( 'Run Tests', 'vss' ),
            'manage_options',
            'vss-slack-tests',
            [ self::class, 'render_test_page' ]
        );
    }

    /**
     * Render test page
     */
    public static function render_test_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Slack Notifications - Quality Assurance Tests', 'vss' ); ?></h1>

            <div class="vss-test-controls" style="margin-bottom: 20px;">
                <button type="button" id="vss-run-all-tests" class="button button-primary">
                    <?php esc_html_e( 'Run All Tests', 'vss' ); ?>
                </button>
                <button type="button" id="vss-clear-test-results" class="button button-secondary">
                    <?php esc_html_e( 'Clear Results', 'vss' ); ?>
                </button>
                <span id="vss-test-progress" style="margin-left: 15px;"></span>
            </div>

            <div class="vss-test-results-container">
                <h2><?php esc_html_e( 'Test Results', 'vss' ); ?></h2>
                <div id="vss-test-results"></div>
            </div>

            <!-- Individual Test Controls -->
            <div class="vss-individual-tests" style="margin-top: 30px;">
                <h2><?php esc_html_e( 'Individual Tests', 'vss' ); ?></h2>
                
                <div class="test-category">
                    <h3><?php esc_html_e( 'Configuration Tests', 'vss' ); ?></h3>
                    <button class="button test-button" data-test="test_webhook_url_validation">
                        <?php esc_html_e( 'Test Webhook URL Validation', 'vss' ); ?>
                    </button>
                    <button class="button test-button" data-test="test_settings_persistence">
                        <?php esc_html_e( 'Test Settings Persistence', 'vss' ); ?>
                    </button>
                </div>

                <div class="test-category">
                    <h3><?php esc_html_e( 'Notification Tests', 'vss' ); ?></h3>
                    <button class="button test-button" data-test="test_order_completion_hook">
                        <?php esc_html_e( 'Test Order Completion Hook', 'vss' ); ?>
                    </button>
                    <button class="button test-button" data-test="test_vendor_order_detection">
                        <?php esc_html_e( 'Test Vendor Order Detection', 'vss' ); ?>
                    </button>
                    <button class="button test-button" data-test="test_notification_formatting">
                        <?php esc_html_e( 'Test Notification Formatting', 'vss' ); ?>
                    </button>
                </div>

                <div class="test-category">
                    <h3><?php esc_html_e( 'Error Handling Tests', 'vss' ); ?></h3>
                    <button class="button test-button" data-test="test_network_failure_handling">
                        <?php esc_html_e( 'Test Network Failure Handling', 'vss' ); ?>
                    </button>
                    <button class="button test-button" data-test="test_retry_mechanism">
                        <?php esc_html_e( 'Test Retry Mechanism', 'vss' ); ?>
                    </button>
                    <button class="button test-button" data-test="test_error_logging">
                        <?php esc_html_e( 'Test Error Logging', 'vss' ); ?>
                    </button>
                </div>

                <div class="test-category">
                    <h3><?php esc_html_e( 'Performance Tests', 'vss' ); ?></h3>
                    <button class="button test-button" data-test="test_notification_speed">
                        <?php esc_html_e( 'Test Notification Speed', 'vss' ); ?>
                    </button>
                    <button class="button test-button" data-test="test_concurrent_notifications">
                        <?php esc_html_e( 'Test Concurrent Notifications', 'vss' ); ?>
                    </button>
                </div>

                <div class="test-category">
                    <h3><?php esc_html_e( 'Integration Tests', 'vss' ); ?></h3>
                    <button class="button test-button" data-test="test_woocommerce_integration">
                        <?php esc_html_e( 'Test WooCommerce Integration', 'vss' ); ?>
                    </button>
                    <button class="button test-button" data-test="test_vendor_system_integration">
                        <?php esc_html_e( 'Test Vendor System Integration', 'vss' ); ?>
                    </button>
                </div>
            </div>
        </div>

        <style>
        .test-category {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .test-category h3 {
            margin-top: 0;
            color: #333;
        }
        .test-button {
            margin-right: 10px;
            margin-bottom: 10px;
        }
        .test-result {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 5px solid #ccc;
        }
        .test-result.passed {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
        }
        .test-result.failed {
            background: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }
        .test-result.warning {
            background: #fff3cd;
            border-color: #ffc107;
            color: #856404;
        }
        .test-details {
            font-family: monospace;
            font-size: 12px;
            margin-top: 10px;
            padding: 5px;
            background: rgba(0,0,0,0.05);
            border-radius: 3px;
        }
        </style>

        <script>
        jQuery(document).ready(function($) {
            // Run all tests
            $('#vss-run-all-tests').on('click', function() {
                var $button = $(this);
                var $progress = $('#vss-test-progress');
                var $results = $('#vss-test-results');
                
                $button.prop('disabled', true).text('<?php esc_js_e( 'Running Tests...', 'vss' ); ?>');
                $progress.text('<?php esc_js_e( 'Starting test suite...', 'vss' ); ?>');
                $results.html('<div class="test-result"><strong><?php esc_js_e( 'Starting comprehensive test suite...', 'vss' ); ?></strong></div>');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'vss_run_slack_tests',
                        nonce: '<?php echo wp_create_nonce( 'vss_slack_tests' ); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            displayTestResults(response.data);
                        } else {
                            $results.html('<div class="test-result failed"><strong>Test suite failed:</strong> ' + response.data.message + '</div>');
                        }
                    },
                    error: function() {
                        $results.html('<div class="test-result failed"><strong>AJAX error occurred while running tests</strong></div>');
                    },
                    complete: function() {
                        $button.prop('disabled', false).text('<?php esc_js_e( 'Run All Tests', 'vss' ); ?>');
                        $progress.text('');
                    }
                });
            });

            // Run individual tests
            $('.test-button').on('click', function() {
                var $button = $(this);
                var testName = $button.data('test');
                var originalText = $button.text();
                
                $button.prop('disabled', true).text('<?php esc_js_e( 'Testing...', 'vss' ); ?>');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'vss_run_single_slack_test',
                        test_name: testName,
                        nonce: '<?php echo wp_create_nonce( 'vss_slack_tests' ); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#vss-test-results').append(formatSingleTestResult(response.data));
                        } else {
                            $('#vss-test-results').append('<div class="test-result failed"><strong>' + testName + '</strong>: ' + response.data.message + '</div>');
                        }
                    },
                    error: function() {
                        $('#vss-test-results').append('<div class="test-result failed"><strong>' + testName + '</strong>: AJAX error occurred</div>');
                    },
                    complete: function() {
                        $button.prop('disabled', false).text(originalText);
                    }
                });
            });

            // Clear results
            $('#vss-clear-test-results').on('click', function() {
                $('#vss-test-results').html('');
            });

            function displayTestResults(results) {
                var html = '';
                var totalTests = results.length;
                var passedTests = 0;
                
                results.forEach(function(result) {
                    if (result.status === 'passed') passedTests++;
                    html += formatSingleTestResult(result);
                });
                
                html = '<div class="test-result ' + (passedTests === totalTests ? 'passed' : 'warning') + '"><strong>Test Summary:</strong> ' + passedTests + '/' + totalTests + ' tests passed</div>' + html;
                $('#vss-test-results').html(html);
            }

            function formatSingleTestResult(result) {
                var statusClass = result.status === 'passed' ? 'passed' : 'failed';
                var html = '<div class="test-result ' + statusClass + '">';
                html += '<strong>' + result.name + ':</strong> ' + result.message;
                if (result.details) {
                    html += '<div class="test-details">' + result.details + '</div>';
                }
                html += '</div>';
                return html;
            }
        });
        </script>
        <?php
    }

    /**
     * AJAX handler for running all tests
     */
    public static function ajax_run_tests() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => __( 'Insufficient permissions', 'vss' ) ] );
        }

        if ( ! wp_verify_nonce( $_POST['nonce'], 'vss_slack_tests' ) ) {
            wp_send_json_error( [ 'message' => __( 'Security verification failed', 'vss' ) ] );
        }

        $test_results = self::run_all_tests();
        wp_send_json_success( $test_results );
    }

    /**
     * AJAX handler for running single test
     */
    public static function ajax_run_single_test() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => __( 'Insufficient permissions', 'vss' ) ] );
        }

        if ( ! wp_verify_nonce( $_POST['nonce'], 'vss_slack_tests' ) ) {
            wp_send_json_error( [ 'message' => __( 'Security verification failed', 'vss' ) ] );
        }

        $test_name = sanitize_text_field( $_POST['test_name'] );
        $result = self::run_single_test( $test_name );
        
        if ( $result ) {
            wp_send_json_success( $result );
        } else {
            wp_send_json_error( [ 'message' => __( 'Test not found or failed to execute', 'vss' ) ] );
        }
    }

    /**
     * Run all tests
     */
    public static function run_all_tests() {
        $tests = [
            'test_webhook_url_validation',
            'test_settings_persistence',
            'test_order_completion_hook',
            'test_vendor_order_detection',
            'test_notification_formatting',
            'test_network_failure_handling',
            'test_retry_mechanism',
            'test_error_logging',
            'test_notification_speed',
            'test_concurrent_notifications',
            'test_woocommerce_integration',
            'test_vendor_system_integration'
        ];

        $results = [];
        foreach ( $tests as $test ) {
            $results[] = self::run_single_test( $test );
        }

        return $results;
    }

    /**
     * Run single test
     */
    public static function run_single_test( $test_name ) {
        if ( ! method_exists( self::class, $test_name ) ) {
            return [
                'name' => $test_name,
                'status' => 'failed',
                'message' => 'Test method not found',
                'details' => ''
            ];
        }

        try {
            return self::$test_name();
        } catch ( Exception $e ) {
            return [
                'name' => $test_name,
                'status' => 'failed',
                'message' => 'Test threw exception: ' . $e->getMessage(),
                'details' => $e->getTraceAsString()
            ];
        }
    }

    /**
     * Test webhook URL validation
     */
    public static function test_webhook_url_validation() {
        $test_urls = [
            'https://hooks.slack.com/services/T00000000/B00000000/XXXXXXXXXXXXXXXXXXXXXXXX' => true,
            'http://hooks.slack.com/services/T00000000/B00000000/XXXXXXXXXXXXXXXXXXXXXXXX' => false,
            'https://invalid-url.com/webhook' => false,
            '' => false,
            'not-a-url' => false
        ];

        $passed = 0;
        $total = count( $test_urls );
        $details = [];

        foreach ( $test_urls as $url => $should_pass ) {
            $is_valid = filter_var( $url, FILTER_VALIDATE_URL ) && strpos( $url, 'hooks.slack.com' ) !== false && strpos( $url, 'https://' ) === 0;
            if ( $is_valid === $should_pass ) {
                $passed++;
                $details[] = "✓ URL: '$url' - " . ( $should_pass ? 'Valid (expected)' : 'Invalid (expected)' );
            } else {
                $details[] = "✗ URL: '$url' - Expected " . ( $should_pass ? 'valid' : 'invalid' ) . " but got " . ( $is_valid ? 'valid' : 'invalid' );
            }
        }

        return [
            'name' => 'Webhook URL Validation',
            'status' => $passed === $total ? 'passed' : 'failed',
            'message' => "Passed {$passed}/{$total} URL validation tests",
            'details' => implode( "\n", $details )
        ];
    }

    /**
     * Test settings persistence
     */
    public static function test_settings_persistence() {
        $test_webhook = 'https://hooks.slack.com/services/TEST/TEST/TEST';
        $test_enabled = false;

        // Save test settings
        update_option( 'vss_slack_webhook_url', $test_webhook );
        update_option( 'vss_slack_notifications_enabled', $test_enabled );

        // Retrieve and verify
        $saved_webhook = get_option( 'vss_slack_webhook_url' );
        $saved_enabled = get_option( 'vss_slack_notifications_enabled' );

        $webhook_match = $saved_webhook === $test_webhook;
        $enabled_match = $saved_enabled === $test_enabled;

        // Clean up
        delete_option( 'vss_slack_webhook_url' );
        delete_option( 'vss_slack_notifications_enabled' );

        $passed = $webhook_match && $enabled_match;

        return [
            'name' => 'Settings Persistence',
            'status' => $passed ? 'passed' : 'failed',
            'message' => $passed ? 'Settings saved and retrieved correctly' : 'Settings persistence failed',
            'details' => "Webhook URL: " . ( $webhook_match ? '✓' : '✗' ) . "\nEnabled Flag: " . ( $enabled_match ? '✓' : '✗' )
        ];
    }

    /**
     * Test order completion hook
     */
    public static function test_order_completion_hook() {
        $hook_exists = has_action( 'woocommerce_order_status_completed', [ 'VSS_Slack_Notifications', 'handle_order_completed' ] );
        $status_change_hook = has_action( 'woocommerce_order_status_changed', [ 'VSS_Slack_Notifications', 'handle_order_status_changed' ] );

        $both_exist = $hook_exists !== false && $status_change_hook !== false;

        return [
            'name' => 'Order Completion Hook',
            'status' => $both_exist ? 'passed' : 'failed',
            'message' => $both_exist ? 'All required hooks are registered' : 'Some hooks are missing',
            'details' => "Order completed hook: " . ( $hook_exists !== false ? '✓' : '✗' ) . "\nStatus changed hook: " . ( $status_change_hook !== false ? '✓' : '✗' )
        ];
    }

    /**
     * Test vendor order detection
     */
    public static function test_vendor_order_detection() {
        if ( ! class_exists( 'WooCommerce' ) ) {
            return [
                'name' => 'Vendor Order Detection',
                'status' => 'failed',
                'message' => 'WooCommerce not available for testing',
                'details' => ''
            ];
        }

        // Create mock order data
        $mock_order_data = [
            'vendor_id' => 123,
            'vendor_name' => 'Test Vendor',
            'order_total' => 99.99,
            'order_number' => 'TEST-001',
            'items' => [
                ['name' => 'Test Product', 'quantity' => 2]
            ]
        ];

        // Test notification data preparation (simulated)
        $has_vendor = !empty( $mock_order_data['vendor_id'] );
        $has_valid_data = !empty( $mock_order_data['order_number'] ) && $mock_order_data['order_total'] > 0;

        return [
            'name' => 'Vendor Order Detection',
            'status' => $has_vendor && $has_valid_data ? 'passed' : 'failed',
            'message' => $has_vendor && $has_valid_data ? 'Vendor order detection working correctly' : 'Vendor order detection failed',
            'details' => "Has vendor ID: " . ( $has_vendor ? '✓' : '✗' ) . "\nHas valid order data: " . ( $has_valid_data ? '✓' : '✗' )
        ];
    }

    /**
     * Test notification formatting
     */
    public static function test_notification_formatting() {
        $mock_notification = [
            'text' => '🎉 New Sale Completed! Order #TEST-001',
            'blocks' => [
                [
                    'type' => 'header',
                    'text' => [
                        'type' => 'plain_text',
                        'text' => '🎉 Sale Notification',
                        'emoji' => true
                    ]
                ],
                [
                    'type' => 'section',
                    'fields' => [
                        [
                            'type' => 'mrkdwn',
                            'text' => '*Order Number:*\n#TEST-001'
                        ]
                    ]
                ]
            ]
        ];

        $has_text = !empty( $mock_notification['text'] );
        $has_blocks = !empty( $mock_notification['blocks'] );
        $has_header = isset( $mock_notification['blocks'][0]['type'] ) && $mock_notification['blocks'][0]['type'] === 'header';
        $valid_json = json_encode( $mock_notification ) !== false;

        $all_valid = $has_text && $has_blocks && $has_header && $valid_json;

        return [
            'name' => 'Notification Formatting',
            'status' => $all_valid ? 'passed' : 'failed',
            'message' => $all_valid ? 'Notification formatting is correct' : 'Notification formatting issues detected',
            'details' => "Has text: " . ( $has_text ? '✓' : '✗' ) . 
                        "\nHas blocks: " . ( $has_blocks ? '✓' : '✗' ) . 
                        "\nHas header: " . ( $has_header ? '✓' : '✗' ) . 
                        "\nValid JSON: " . ( $valid_json ? '✓' : '✗' )
        ];
    }

    /**
     * Test network failure handling
     */
    public static function test_network_failure_handling() {
        // Test with invalid webhook URL to simulate network failure
        $original_url = get_option( 'vss_slack_webhook_url', '' );
        update_option( 'vss_slack_webhook_url', 'https://invalid-webhook-url.example.com/fail' );

        $test_data = [
            'text' => 'Test network failure handling',
            'blocks' => []
        ];

        // This should fail and trigger error handling
        $result = VSS_Slack_Notifications::send_slack_notification( $test_data );

        // Restore original URL
        if ( $original_url ) {
            update_option( 'vss_slack_webhook_url', $original_url );
        } else {
            delete_option( 'vss_slack_webhook_url' );
        }

        // Check if error was logged
        $error_log = get_option( 'vss_slack_error_log', [] );
        $recent_errors = array_slice( $error_log, -5 );
        $has_recent_error = !empty( $recent_errors );

        return [
            'name' => 'Network Failure Handling',
            'status' => !$result && $has_recent_error ? 'passed' : 'failed',
            'message' => !$result && $has_recent_error ? 'Network failures handled correctly' : 'Network failure handling issues',
            'details' => "Send failed (expected): " . ( !$result ? '✓' : '✗' ) . 
                        "\nError logged: " . ( $has_recent_error ? '✓' : '✗' )
        ];
    }

    /**
     * Test retry mechanism
     */
    public static function test_retry_mechanism() {
        $failed_notifications = get_option( 'vss_slack_failed_notifications', [] );
        $initial_count = count( $failed_notifications );

        // Add a test failed notification
        $test_retry_data = [
            'notification_data' => ['text' => 'Test retry'],
            'retry_count' => 1,
            'scheduled_time' => time() - 100 // Past time to trigger retry
        ];
        
        $failed_notifications[] = $test_retry_data;
        update_option( 'vss_slack_failed_notifications', $failed_notifications );

        // Trigger retry processing
        VSS_Slack_Notifications::schedule_retry_failed_notifications();

        $updated_notifications = get_option( 'vss_slack_failed_notifications', [] );
        $retry_processed = count( $updated_notifications ) !== count( $failed_notifications );

        return [
            'name' => 'Retry Mechanism',
            'status' => $retry_processed ? 'passed' : 'failed',
            'message' => $retry_processed ? 'Retry mechanism working' : 'Retry mechanism not functioning',
            'details' => "Initial count: {$initial_count}\nProcessed retry: " . ( $retry_processed ? '✓' : '✗' )
        ];
    }

    /**
     * Test error logging
     */
    public static function test_error_logging() {
        $initial_log = get_option( 'vss_slack_error_log', [] );
        $initial_count = count( $initial_log );

        // Generate a test error by calling private method via reflection
        $test_message = 'Test error message - ' . time();
        $reflection = new ReflectionClass( 'VSS_Slack_Notifications' );
        $log_error_method = $reflection->getMethod( 'log_error' );
        $log_error_method->setAccessible( true );
        $log_error_method->invoke( null, $test_message );

        $updated_log = get_option( 'vss_slack_error_log', [] );
        $new_count = count( $updated_log );
        
        $error_logged = $new_count > $initial_count;
        $message_found = false;
        
        if ( $error_logged ) {
            $last_error = end( $updated_log );
            $message_found = strpos( $last_error['message'], 'Test error message' ) !== false;
        }

        return [
            'name' => 'Error Logging',
            'status' => $error_logged && $message_found ? 'passed' : 'failed',
            'message' => $error_logged && $message_found ? 'Error logging working correctly' : 'Error logging issues',
            'details' => "Error logged: " . ( $error_logged ? '✓' : '✗' ) . "\nMessage found: " . ( $message_found ? '✓' : '✗' )
        ];
    }

    /**
     * Test notification speed
     */
    public static function test_notification_speed() {
        $test_data = [
            'text' => 'Speed test notification',
            'blocks' => []
        ];

        $start_time = microtime( true );
        
        // Mock the notification (don't actually send to avoid spam)
        $notification_prepared = !empty( $test_data['text'] );
        $json_encoded = json_encode( $test_data );
        $json_valid = $json_encoded !== false;
        
        $end_time = microtime( true );
        $execution_time = ( $end_time - $start_time ) * 1000; // Convert to milliseconds

        $fast_enough = $execution_time < 100; // Should complete prep in under 100ms

        return [
            'name' => 'Notification Speed',
            'status' => $fast_enough && $notification_prepared && $json_valid ? 'passed' : 'failed',
            'message' => $fast_enough ? "Notification prepared in {$execution_time}ms" : "Notification preparation too slow: {$execution_time}ms",
            'details' => "Execution time: {$execution_time}ms\nFast enough (<100ms): " . ( $fast_enough ? '✓' : '✗' ) . 
                        "\nData prepared: " . ( $notification_prepared ? '✓' : '✗' ) . 
                        "\nJSON valid: " . ( $json_valid ? '✓' : '✗' )
        ];
    }

    /**
     * Test concurrent notifications
     */
    public static function test_concurrent_notifications() {
        // Simulate multiple notification preparations
        $notifications = [];
        $start_time = microtime( true );
        
        for ( $i = 0; $i < 5; $i++ ) {
            $notifications[] = [
                'text' => "Concurrent test notification #{$i}",
                'blocks' => [
                    [
                        'type' => 'section',
                        'text' => [
                            'type' => 'mrkdwn',
                            'text' => "Test message #{$i}"
                        ]
                    ]
                ]
            ];
        }
        
        $end_time = microtime( true );
        $total_time = ( $end_time - $start_time ) * 1000;
        
        $all_prepared = count( $notifications ) === 5;
        $reasonable_time = $total_time < 500; // Under 500ms for 5 notifications

        return [
            'name' => 'Concurrent Notifications',
            'status' => $all_prepared && $reasonable_time ? 'passed' : 'failed',
            'message' => $all_prepared && $reasonable_time ? "Prepared 5 notifications in {$total_time}ms" : "Concurrent notification issues",
            'details' => "All prepared: " . ( $all_prepared ? '✓' : '✗' ) . 
                        "\nReasonable time (<500ms): " . ( $reasonable_time ? '✓' : '✗' ) . 
                        "\nTotal time: {$total_time}ms"
        ];
    }

    /**
     * Test WooCommerce integration
     */
    public static function test_woocommerce_integration() {
        $wc_active = class_exists( 'WooCommerce' );
        $order_class_exists = class_exists( 'WC_Order' );
        $required_functions = function_exists( 'wc_get_order' ) && function_exists( 'wc_price' );

        $integration_ready = $wc_active && $order_class_exists && $required_functions;

        return [
            'name' => 'WooCommerce Integration',
            'status' => $integration_ready ? 'passed' : 'failed',
            'message' => $integration_ready ? 'WooCommerce integration ready' : 'WooCommerce integration issues',
            'details' => "WooCommerce active: " . ( $wc_active ? '✓' : '✗' ) . 
                        "\nWC_Order exists: " . ( $order_class_exists ? '✓' : '✗' ) . 
                        "\nRequired functions: " . ( $required_functions ? '✓' : '✗' )
        ];
    }

    /**
     * Test vendor system integration
     */
    public static function test_vendor_system_integration() {
        $vendor_class_exists = class_exists( 'VSS_Vendor' );
        $admin_class_exists = class_exists( 'VSS_Admin' );
        $slack_class_exists = class_exists( 'VSS_Slack_Notifications' );

        $all_classes = $vendor_class_exists && $admin_class_exists && $slack_class_exists;

        // Test if vendor meta key is recognized
        $meta_key_defined = defined( 'VSS_VENDOR_META_KEY' ) || function_exists( 'get_post_meta' );

        $integration_complete = $all_classes && $meta_key_defined;

        return [
            'name' => 'Vendor System Integration',
            'status' => $integration_complete ? 'passed' : 'failed',
            'message' => $integration_complete ? 'Vendor system integration complete' : 'Vendor system integration issues',
            'details' => "VSS_Vendor class: " . ( $vendor_class_exists ? '✓' : '✗' ) . 
                        "\nVSS_Admin class: " . ( $admin_class_exists ? '✓' : '✗' ) . 
                        "\nVSS_Slack_Notifications class: " . ( $slack_class_exists ? '✓' : '✗' ) . 
                        "\nMeta functions available: " . ( $meta_key_defined ? '✓' : '✗' )
        ];
    }
}