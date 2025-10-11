<?php
/**
 * VSS Slack Notifications Class
 *
 * Handles Slack webhook notifications for sales events
 *
 * @package VendorOrderManager
 * @since 8.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class VSS_Slack_Notifications {

    /**
     * Slack webhook URL
     */
    const WEBHOOK_URL = 'https://hooks.slack.com/services/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';

    /**
     * Maximum retry attempts for failed notifications
     */
    const MAX_RETRIES = 3;

    /**
     * Initialize Slack notifications
     */
    public static function init() {
        // Hook into WooCommerce order completion
        add_action( 'woocommerce_order_status_completed', [ self::class, 'handle_order_completed' ], 10, 2 );
        
        // Hook into vendor-specific order completion
        add_action( 'woocommerce_order_status_changed', [ self::class, 'handle_order_status_changed' ], 10, 4 );
        
        // Add admin menu for notification settings
        add_action( 'admin_menu', [ self::class, 'add_admin_menu' ] );
        
        // Handle admin form submissions
        add_action( 'admin_post_vss_save_slack_settings', [ self::class, 'save_slack_settings' ] );
        
        // Add test notification functionality
        add_action( 'wp_ajax_vss_test_slack_notification', [ self::class, 'ajax_test_notification' ] );
        
        // Log failed notifications for retry
        add_action( 'init', [ self::class, 'schedule_retry_failed_notifications' ] );
    }

    /**
     * Handle order completion
     *
     * @param int $order_id Order ID
     * @param WC_Order $order Order object
     */
    public static function handle_order_completed( $order_id, $order = null ) {
        if ( ! $order ) {
            $order = wc_get_order( $order_id );
        }

        if ( ! $order ) {
            return;
        }

        // Check if notifications are enabled
        if ( ! self::is_notifications_enabled() ) {
            return;
        }

        // Get vendor information
        $vendor_id = get_post_meta( $order_id, '_vss_vendor_user_id', true );
        $vendor_name = $vendor_id ? get_userdata( $vendor_id )->display_name : __( 'Unknown Vendor', 'vss' );

        // Prepare notification data
        $notification_data = self::prepare_order_notification_data( $order, $vendor_name );

        // Send notification
        self::send_slack_notification( $notification_data );

        // Log the notification
        self::log_notification( $order_id, 'order_completed', $notification_data );
    }

    /**
     * Handle order status changes to catch vendor-specific completions
     *
     * @param int $order_id Order ID
     * @param string $old_status Old status
     * @param string $new_status New status
     * @param WC_Order $order Order object
     */
    public static function handle_order_status_changed( $order_id, $old_status, $new_status, $order ) {
        // Only trigger on completion
        if ( $new_status !== 'completed' ) {
            return;
        }

        // Check if this is a vendor order
        $vendor_id = get_post_meta( $order_id, '_vss_vendor_user_id', true );
        if ( ! $vendor_id ) {
            return;
        }

        // Delegate to main handler if not already handled
        if ( ! get_post_meta( $order_id, '_vss_slack_notified', true ) ) {
            self::handle_order_completed( $order_id, $order );
        }
    }

    /**
     * Prepare notification data for an order
     *
     * @param WC_Order $order Order object
     * @param string $vendor_name Vendor name
     * @return array Notification data
     */
    private static function prepare_order_notification_data( $order, $vendor_name ) {
        $order_items = $order->get_items();
        $item_count = count( $order_items );
        $first_item = reset( $order_items );
        $item_names = [];

        foreach ( $order_items as $item ) {
            $item_names[] = $item->get_name() . ' (x' . $item->get_quantity() . ')';
        }

        $items_text = $item_count <= 3 
            ? implode( ', ', $item_names )
            : implode( ', ', array_slice( $item_names, 0, 3 ) ) . ' and ' . ( $item_count - 3 ) . ' more items';

        return [
            'text' => sprintf( '🎉 New Sale Completed! Order #%s', $order->get_order_number() ),
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
                            'text' => '*Order Number:*\n#' . $order->get_order_number()
                        ],
                        [
                            'type' => 'mrkdwn',
                            'text' => '*Vendor:*\n' . esc_html( $vendor_name )
                        ],
                        [
                            'type' => 'mrkdwn',
                            'text' => '*Customer:*\n' . $order->get_billing_first_name() . ' ' . $order->get_billing_last_name()
                        ],
                        [
                            'type' => 'mrkdwn',
                            'text' => '*Total Amount:*\n' . html_entity_decode( strip_tags( $order->get_formatted_order_total() ) )
                        ]
                    ]
                ],
                [
                    'type' => 'section',
                    'text' => [
                        'type' => 'mrkdwn',
                        'text' => '*Items:*\n' . $items_text
                    ]
                ],
                [
                    'type' => 'context',
                    'elements' => [
                        [
                            'type' => 'mrkdwn',
                            'text' => '📅 Completed: ' . $order->get_date_completed()->format( 'M j, Y g:i A' )
                        ],
                        [
                            'type' => 'mrkdwn',
                            'text' => '🌐 Site: ' . get_site_url()
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Send notification to Slack
     *
     * @param array $data Notification data
     * @param int $retry_count Current retry attempt
     * @return bool Success status
     */
    public static function send_slack_notification( $data, $retry_count = 0 ) {
        $webhook_url = self::get_webhook_url();
        
        if ( empty( $webhook_url ) ) {
            self::log_error( 'Slack webhook URL is not configured' );
            return false;
        }

        $args = [
            'body' => wp_json_encode( $data ),
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'timeout' => 30,
            'blocking' => true
        ];

        $response = wp_remote_post( $webhook_url, $args );

        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            self::log_error( 'Slack notification failed: ' . $error_message );
            
            // Schedule retry if we haven't exceeded max retries
            if ( $retry_count < self::MAX_RETRIES ) {
                self::schedule_retry_notification( $data, $retry_count + 1 );
            }
            
            return false;
        }

        $response_code = wp_remote_retrieve_response_code( $response );
        $response_body = wp_remote_retrieve_body( $response );

        if ( $response_code !== 200 ) {
            self::log_error( "Slack notification failed with status {$response_code}: {$response_body}" );
            
            // Schedule retry if we haven't exceeded max retries
            if ( $retry_count < self::MAX_RETRIES ) {
                self::schedule_retry_notification( $data, $retry_count + 1 );
            }
            
            return false;
        }

        return true;
    }

    /**
     * Schedule retry for failed notification
     *
     * @param array $data Notification data
     * @param int $retry_count Retry attempt number
     */
    private static function schedule_retry_notification( $data, $retry_count ) {
        $retry_data = [
            'notification_data' => $data,
            'retry_count' => $retry_count,
            'scheduled_time' => time() + ( $retry_count * 300 ) // 5 minutes * retry count
        ];

        $failed_notifications = get_option( 'vss_slack_failed_notifications', [] );
        $failed_notifications[] = $retry_data;
        update_option( 'vss_slack_failed_notifications', $failed_notifications );
    }

    /**
     * Schedule retry of failed notifications
     */
    public static function schedule_retry_failed_notifications() {
        $failed_notifications = get_option( 'vss_slack_failed_notifications', [] );
        $current_time = time();
        $remaining_notifications = [];

        foreach ( $failed_notifications as $notification ) {
            if ( $current_time >= $notification['scheduled_time'] ) {
                // Attempt to resend
                $success = self::send_slack_notification( 
                    $notification['notification_data'], 
                    $notification['retry_count'] 
                );
                
                if ( ! $success && $notification['retry_count'] < self::MAX_RETRIES ) {
                    // Keep for another retry
                    $remaining_notifications[] = $notification;
                }
            } else {
                // Not time to retry yet
                $remaining_notifications[] = $notification;
            }
        }

        update_option( 'vss_slack_failed_notifications', $remaining_notifications );
    }

    /**
     * Get webhook URL
     *
     * @return string Webhook URL
     */
    private static function get_webhook_url() {
        $saved_url = get_option( 'vss_slack_webhook_url', '' );
        return ! empty( $saved_url ) ? $saved_url : self::WEBHOOK_URL;
    }

    /**
     * Check if notifications are enabled
     *
     * @return bool Whether notifications are enabled
     */
    private static function is_notifications_enabled() {
        return get_option( 'vss_slack_notifications_enabled', true );
    }

    /**
     * Log notification
     *
     * @param int $order_id Order ID
     * @param string $type Notification type
     * @param array $data Notification data
     */
    private static function log_notification( $order_id, $type, $data ) {
        $log_entry = [
            'timestamp' => current_time( 'mysql' ),
            'order_id' => $order_id,
            'type' => $type,
            'status' => 'sent',
            'data_hash' => md5( wp_json_encode( $data ) )
        ];

        $notification_logs = get_option( 'vss_slack_notification_logs', [] );
        $notification_logs[] = $log_entry;

        // Keep only last 100 logs
        if ( count( $notification_logs ) > 100 ) {
            $notification_logs = array_slice( $notification_logs, -100 );
        }

        update_option( 'vss_slack_notification_logs', $notification_logs );
        
        // Mark order as notified
        update_post_meta( $order_id, '_vss_slack_notified', true );
    }

    /**
     * Log error
     *
     * @param string $message Error message
     */
    private static function log_error( $message ) {
        $error_log = get_option( 'vss_slack_error_log', [] );
        $error_log[] = [
            'timestamp' => current_time( 'mysql' ),
            'message' => $message
        ];

        // Keep only last 50 errors
        if ( count( $error_log ) > 50 ) {
            $error_log = array_slice( $error_log, -50 );
        }

        update_option( 'vss_slack_error_log', $error_log );
        
        // Also log to WordPress error log if enabled
        if ( WP_DEBUG_LOG ) {
            error_log( 'VSS Slack Notifications: ' . $message );
        }
    }

    /**
     * Add admin menu
     */
    public static function add_admin_menu() {
        add_submenu_page(
            'vss-vendor-management',
            __( 'Slack Notifications', 'vss' ),
            __( 'Slack Notifications', 'vss' ),
            'manage_options',
            'vss-slack-notifications',
            [ self::class, 'render_admin_page' ]
        );
    }

    /**
     * Render admin page
     */
    public static function render_admin_page() {
        $webhook_url = get_option( 'vss_slack_webhook_url', self::WEBHOOK_URL );
        $notifications_enabled = get_option( 'vss_slack_notifications_enabled', true );
        $error_log = get_option( 'vss_slack_error_log', [] );
        $notification_logs = get_option( 'vss_slack_notification_logs', [] );
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Slack Notifications Settings', 'vss' ); ?></h1>

            <?php if ( isset( $_GET['settings-updated'] ) ) : ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php esc_html_e( 'Settings saved successfully!', 'vss' ); ?></p>
                </div>
            <?php endif; ?>

            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <?php wp_nonce_field( 'vss_slack_settings', 'vss_slack_nonce' ); ?>
                <input type="hidden" name="action" value="vss_save_slack_settings" />

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Enable Notifications', 'vss' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="notifications_enabled" value="1" <?php checked( $notifications_enabled ); ?> />
                                <?php esc_html_e( 'Send Slack notifications for completed sales', 'vss' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Webhook URL', 'vss' ); ?></th>
                        <td>
                            <input type="url" 
                                   name="webhook_url" 
                                   value="<?php echo esc_attr( $webhook_url ); ?>" 
                                   class="regular-text" 
                                   placeholder="https://hooks.slack.com/services/..." />
                            <p class="description">
                                <?php esc_html_e( 'Your Slack webhook URL. Leave empty to use the default configured URL.', 'vss' ); ?>
                            </p>
                        </td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>

            <div class="vss-slack-actions" style="margin-top: 30px;">
                <h2><?php esc_html_e( 'Test & Troubleshooting', 'vss' ); ?></h2>
                <p>
                    <button type="button" id="vss-test-slack" class="button button-secondary">
                        <?php esc_html_e( 'Send Test Notification', 'vss' ); ?>
                    </button>
                    <span id="vss-test-result" style="margin-left: 10px;"></span>
                </p>
            </div>

            <?php if ( ! empty( $error_log ) ) : ?>
                <div class="vss-error-log" style="margin-top: 30px;">
                    <h3><?php esc_html_e( 'Recent Errors', 'vss' ); ?></h3>
                    <div style="background: #fff; border: 1px solid #ddd; padding: 10px; max-height: 200px; overflow-y: auto;">
                        <?php foreach ( array_reverse( array_slice( $error_log, -10 ) ) as $error ) : ?>
                            <div style="margin-bottom: 5px; font-family: monospace; font-size: 12px;">
                                <strong><?php echo esc_html( $error['timestamp'] ); ?>:</strong>
                                <?php echo esc_html( $error['message'] ); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $notification_logs ) ) : ?>
                <div class="vss-notification-log" style="margin-top: 30px;">
                    <h3><?php esc_html_e( 'Recent Notifications', 'vss' ); ?></h3>
                    <div style="background: #fff; border: 1px solid #ddd; padding: 10px; max-height: 200px; overflow-y: auto;">
                        <?php foreach ( array_reverse( array_slice( $notification_logs, -10 ) ) as $log ) : ?>
                            <div style="margin-bottom: 5px; font-family: monospace; font-size: 12px;">
                                <strong><?php echo esc_html( $log['timestamp'] ); ?>:</strong>
                                Order #<?php echo esc_html( $log['order_id'] ); ?> - 
                                <?php echo esc_html( $log['type'] ); ?> - 
                                <span style="color: green;"><?php echo esc_html( $log['status'] ); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('#vss-test-slack').on('click', function() {
                var $button = $(this);
                var $result = $('#vss-test-result');
                
                $button.prop('disabled', true).text('<?php esc_js_e( 'Sending...', 'vss' ); ?>');
                $result.text('');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'vss_test_slack_notification',
                        nonce: '<?php echo wp_create_nonce( 'vss_test_slack' ); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $result.html('<span style="color: green;">✓ Test notification sent successfully!</span>');
                        } else {
                            $result.html('<span style="color: red;">✗ Test failed: ' + response.data.message + '</span>');
                        }
                    },
                    error: function() {
                        $result.html('<span style="color: red;">✗ AJAX error occurred</span>');
                    },
                    complete: function() {
                        $button.prop('disabled', false).text('<?php esc_js_e( 'Send Test Notification', 'vss' ); ?>');
                    }
                });
            });
        });
        </script>
        <?php
    }

    /**
     * Save Slack settings
     */
    public static function save_slack_settings() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'You do not have permission to access this page.', 'vss' ) );
        }

        if ( ! wp_verify_nonce( $_POST['vss_slack_nonce'], 'vss_slack_settings' ) ) {
            wp_die( __( 'Security verification failed.', 'vss' ) );
        }

        $notifications_enabled = isset( $_POST['notifications_enabled'] ) ? true : false;
        $webhook_url = isset( $_POST['webhook_url'] ) ? sanitize_url( $_POST['webhook_url'] ) : '';

        update_option( 'vss_slack_notifications_enabled', $notifications_enabled );
        update_option( 'vss_slack_webhook_url', $webhook_url );

        wp_redirect( add_query_arg( 'settings-updated', 'true', wp_get_referer() ) );
        exit;
    }

    /**
     * AJAX test notification
     */
    public static function ajax_test_notification() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => __( 'Insufficient permissions', 'vss' ) ] );
        }

        if ( ! wp_verify_nonce( $_POST['nonce'], 'vss_test_slack' ) ) {
            wp_send_json_error( [ 'message' => __( 'Security verification failed', 'vss' ) ] );
        }

        $test_data = [
            'text' => '🧪 Test Notification from VSS Vendor Order Manager',
            'blocks' => [
                [
                    'type' => 'header',
                    'text' => [
                        'type' => 'plain_text',
                        'text' => '🧪 Test Notification',
                        'emoji' => true
                    ]
                ],
                [
                    'type' => 'section',
                    'text' => [
                        'type' => 'mrkdwn',
                        'text' => '*This is a test notification* from your VSS Vendor Order Manager plugin.\n\nIf you see this message, your Slack integration is working correctly! 🎉'
                    ]
                ],
                [
                    'type' => 'context',
                    'elements' => [
                        [
                            'type' => 'mrkdwn',
                            'text' => '🌐 Site: ' . get_site_url()
                        ],
                        [
                            'type' => 'mrkdwn',
                            'text' => '⏰ ' . current_time( 'M j, Y g:i A' )
                        ]
                    ]
                ]
            ]
        ];

        $success = self::send_slack_notification( $test_data );

        if ( $success ) {
            wp_send_json_success( [ 'message' => __( 'Test notification sent successfully!', 'vss' ) ] );
        } else {
            wp_send_json_error( [ 'message' => __( 'Failed to send test notification. Check error logs.', 'vss' ) ] );
        }
    }
}