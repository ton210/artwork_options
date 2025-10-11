<?php
/**
 * VSS Slack Notifications Demo
 *
 * Provides demo functionality for testing Slack notifications
 *
 * @package VendorOrderManager
 * @since 8.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class VSS_Slack_Demo {

    /**
     * Initialize demo functionality
     */
    public static function init() {
        // Add demo menu (admin only)
        if ( is_admin() ) {
            add_action( 'admin_menu', [ self::class, 'add_demo_menu' ] );
            add_action( 'wp_ajax_vss_create_demo_sale', [ self::class, 'ajax_create_demo_sale' ] );
        }
    }

    /**
     * Add demo menu
     */
    public static function add_demo_menu() {
        add_submenu_page(
            'vss-slack-notifications',
            __( 'Demo Sale', 'vss' ),
            __( 'Demo Sale', 'vss' ),
            'manage_options',
            'vss-slack-demo',
            [ self::class, 'render_demo_page' ]
        );
    }

    /**
     * Render demo page
     */
    public static function render_demo_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Slack Notifications Demo', 'vss' ); ?></h1>
            
            <div class="notice notice-info">
                <p><?php esc_html_e( 'Use this demo to test your Slack notifications without creating real orders.', 'vss' ); ?></p>
            </div>

            <div class="demo-controls" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
                <h2><?php esc_html_e( 'Simulate a Sale', 'vss' ); ?></h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Order Number', 'vss' ); ?></th>
                        <td>
                            <input type="text" id="demo-order-number" value="DEMO-<?php echo time(); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Vendor Name', 'vss' ); ?></th>
                        <td>
                            <input type="text" id="demo-vendor-name" value="Demo Vendor Store" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Customer Name', 'vss' ); ?></th>
                        <td>
                            <input type="text" id="demo-customer-name" value="John Demo Customer" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Order Total', 'vss' ); ?></th>
                        <td>
                            <input type="number" id="demo-order-total" value="99.99" step="0.01" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Product Name', 'vss' ); ?></th>
                        <td>
                            <input type="text" id="demo-product-name" value="Demo Product" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Quantity', 'vss' ); ?></th>
                        <td>
                            <input type="number" id="demo-quantity" value="2" min="1" class="regular-text" />
                        </td>
                    </tr>
                </table>

                <p>
                    <button type="button" id="create-demo-sale" class="button button-primary button-large">
                        <?php esc_html_e( 'Create Demo Sale & Send Notification', 'vss' ); ?>
                    </button>
                </p>

                <div id="demo-result" style="margin-top: 20px;"></div>
            </div>

            <div class="demo-info" style="margin-top: 30px;">
                <h2><?php esc_html_e( 'What This Demo Does', 'vss' ); ?></h2>
                <ul>
                    <li><?php esc_html_e( 'Creates a simulated sale notification with your custom data', 'vss' ); ?></li>
                    <li><?php esc_html_e( 'Sends the notification to your configured Slack channel', 'vss' ); ?></li>
                    <li><?php esc_html_e( 'Shows you exactly how real sale notifications will appear', 'vss' ); ?></li>
                    <li><?php esc_html_e( 'Tests all notification components: formatting, webhooks, and error handling', 'vss' ); ?></li>
                </ul>

                <h3><?php esc_html_e( 'Before Running Demo', 'vss' ); ?></h3>
                <p><?php esc_html_e( 'Make sure you have:', 'vss' ); ?></p>
                <ul>
                    <li><?php esc_html_e( 'Configured your Slack webhook URL in the settings', 'vss' ); ?></li>
                    <li><?php esc_html_e( 'Enabled Slack notifications', 'vss' ); ?></li>
                    <li><?php esc_html_e( 'Permission to post in your Slack channel', 'vss' ); ?></li>
                </ul>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('#create-demo-sale').on('click', function() {
                var $button = $(this);
                var $result = $('#demo-result');
                
                // Get form data
                var demoData = {
                    order_number: $('#demo-order-number').val(),
                    vendor_name: $('#demo-vendor-name').val(),
                    customer_name: $('#demo-customer-name').val(),
                    order_total: $('#demo-order-total').val(),
                    product_name: $('#demo-product-name').val(),
                    quantity: $('#demo-quantity').val()
                };

                // Validate required fields
                if (!demoData.order_number || !demoData.vendor_name || !demoData.customer_name || !demoData.order_total) {
                    $result.html('<div class="notice notice-error"><p><?php esc_js_e( 'Please fill in all required fields.', 'vss' ); ?></p></div>');
                    return;
                }
                
                $button.prop('disabled', true).text('<?php esc_js_e( 'Creating Demo Sale...', 'vss' ); ?>');
                $result.html('<div class="notice notice-info"><p><?php esc_js_e( 'Sending demo notification to Slack...', 'vss' ); ?></p></div>');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'vss_create_demo_sale',
                        demo_data: demoData,
                        nonce: '<?php echo wp_create_nonce( 'vss_demo_sale' ); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $result.html('<div class="notice notice-success"><p><strong><?php esc_js_e( 'Success!', 'vss' ); ?></strong> ' + response.data.message + '</p></div>');
                            
                            // Auto-generate new order number for next test
                            $('#demo-order-number').val('DEMO-' + Math.floor(Date.now() / 1000));
                        } else {
                            $result.html('<div class="notice notice-error"><p><strong><?php esc_js_e( 'Failed:', 'vss' ); ?></strong> ' + response.data.message + '</p></div>');
                        }
                    },
                    error: function() {
                        $result.html('<div class="notice notice-error"><p><?php esc_js_e( 'AJAX error occurred while creating demo sale.', 'vss' ); ?></p></div>');
                    },
                    complete: function() {
                        $button.prop('disabled', false).text('<?php esc_js_e( 'Create Demo Sale & Send Notification', 'vss' ); ?>');
                    }
                });
            });
        });
        </script>
        <?php
    }

    /**
     * AJAX handler for creating demo sale
     */
    public static function ajax_create_demo_sale() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => __( 'Insufficient permissions', 'vss' ) ] );
        }

        if ( ! wp_verify_nonce( $_POST['nonce'], 'vss_demo_sale' ) ) {
            wp_send_json_error( [ 'message' => __( 'Security verification failed', 'vss' ) ] );
        }

        $demo_data = $_POST['demo_data'];
        
        // Sanitize the demo data
        $sanitized_data = [
            'order_number' => sanitize_text_field( $demo_data['order_number'] ),
            'vendor_name' => sanitize_text_field( $demo_data['vendor_name'] ),
            'customer_name' => sanitize_text_field( $demo_data['customer_name'] ),
            'order_total' => floatval( $demo_data['order_total'] ),
            'product_name' => sanitize_text_field( $demo_data['product_name'] ),
            'quantity' => intval( $demo_data['quantity'] )
        ];

        // Create demo notification data
        $notification_data = self::create_demo_notification_data( $sanitized_data );

        // Send the notification
        $success = VSS_Slack_Notifications::send_slack_notification( $notification_data );

        if ( $success ) {
            wp_send_json_success( [
                'message' => sprintf( 
                    __( 'Demo sale notification sent successfully! Check your Slack channel for order #%s', 'vss' ),
                    $sanitized_data['order_number']
                )
            ] );
        } else {
            wp_send_json_error( [
                'message' => __( 'Failed to send demo notification. Check the error logs for details.', 'vss' )
            ] );
        }
    }

    /**
     * Create demo notification data
     */
    private static function create_demo_notification_data( $data ) {
        return [
            'text' => sprintf( '🎉 Demo Sale! Order #%s', $data['order_number'] ),
            'blocks' => [
                [
                    'type' => 'header',
                    'text' => [
                        'type' => 'plain_text',
                        'text' => '🧪 DEMO Sale Notification',
                        'emoji' => true
                    ]
                ],
                [
                    'type' => 'section',
                    'fields' => [
                        [
                            'type' => 'mrkdwn',
                            'text' => '*Order Number:*\n#' . $data['order_number']
                        ],
                        [
                            'type' => 'mrkdwn',
                            'text' => '*Vendor:*\n' . esc_html( $data['vendor_name'] )
                        ],
                        [
                            'type' => 'mrkdwn',
                            'text' => '*Customer:*\n' . esc_html( $data['customer_name'] )
                        ],
                        [
                            'type' => 'mrkdwn',
                            'text' => '*Total Amount:*\n$' . number_format( $data['order_total'], 2 )
                        ]
                    ]
                ],
                [
                    'type' => 'section',
                    'text' => [
                        'type' => 'mrkdwn',
                        'text' => '*Items:*\n' . esc_html( $data['product_name'] ) . ' (x' . $data['quantity'] . ')'
                    ]
                ],
                [
                    'type' => 'context',
                    'elements' => [
                        [
                            'type' => 'mrkdwn',
                            'text' => '🧪 This is a DEMO notification'
                        ],
                        [
                            'type' => 'mrkdwn',
                            'text' => '📅 Generated: ' . current_time( 'M j, Y g:i A' )
                        ],
                        [
                            'type' => 'mrkdwn',
                            'text' => '🌐 Site: ' . get_site_url()
                        ]
                    ]
                ],
                [
                    'type' => 'divider'
                ],
                [
                    'type' => 'section',
                    'text' => [
                        'type' => 'mrkdwn',
                        'text' => '_This demo shows how real sale notifications will appear. When actual orders are completed, you\'ll see similar notifications but without the "DEMO" label._'
                    ]
                ]
            ]
        ];
    }
}