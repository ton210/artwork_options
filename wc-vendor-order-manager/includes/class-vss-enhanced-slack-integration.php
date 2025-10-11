<?php
/**
 * VSS Enhanced Slack Integration Module
 * 
 * Advanced Slack webhook integration for product uploads with rich notifications
 * 
 * @package VendorOrderManager
 * @subpackage Modules
 * @since 8.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Enhanced Slack Integration Class
 */
class VSS_Enhanced_Slack_Integration {

    /**
     * Slack webhook URLs for different channels
     */
    private static $webhook_urls = [];
    
    /**
     * Initialize Slack integration
     */
    public static function init() {
        // Load webhook URLs from settings
        self::$webhook_urls = [
            'product_submissions' => get_option( 'vss_slack_webhook_submissions', '' ),
            'product_approvals' => get_option( 'vss_slack_webhook_approvals', '' ),
            'admin_alerts' => get_option( 'vss_slack_webhook_admin', '' ),
            'vendor_feedback' => get_option( 'vss_slack_webhook_feedback', '' )
        ];

        // Hook into product events
        add_action( 'vss_product_submitted', [ __CLASS__, 'notify_product_submission' ], 10, 2 );
        add_action( 'vss_product_approved', [ __CLASS__, 'notify_product_approved' ], 10, 3 );
        add_action( 'vss_product_rejected', [ __CLASS__, 'notify_product_rejected' ], 10, 3 );
        add_action( 'vss_product_updated', [ __CLASS__, 'notify_product_updated' ], 10, 2 );
        add_action( 'vss_vendor_message', [ __CLASS__, 'notify_vendor_message' ], 10, 3 );
        
        // Add AJAX handlers for Slack actions
        add_action( 'wp_ajax_vss_slack_quick_approve', [ __CLASS__, 'handle_slack_quick_approve' ] );
        add_action( 'wp_ajax_vss_slack_quick_reject', [ __CLASS__, 'handle_slack_quick_reject' ] );
        add_action( 'wp_ajax_nopriv_vss_slack_webhook', [ __CLASS__, 'handle_slack_webhook' ] );
        
        // Admin settings
        add_action( 'admin_init', [ __CLASS__, 'register_slack_settings' ] );
    }

    /**
     * Register Slack settings
     */
    public static function register_slack_settings() {
        add_settings_section(
            'vss_slack_settings',
            'Enhanced Slack Integration',
            [ __CLASS__, 'slack_settings_section_callback' ],
            'vss_settings'
        );

        $webhook_fields = [
            'vss_slack_webhook_submissions' => 'Product Submissions Webhook',
            'vss_slack_webhook_approvals' => 'Product Approvals Webhook',
            'vss_slack_webhook_admin' => 'Admin Alerts Webhook',
            'vss_slack_webhook_feedback' => 'Vendor Feedback Webhook'
        ];

        foreach ( $webhook_fields as $field => $label ) {
            add_settings_field(
                $field,
                $label,
                [ __CLASS__, 'webhook_field_callback' ],
                'vss_settings',
                'vss_slack_settings',
                [ 'field' => $field, 'label' => $label ]
            );
            register_setting( 'vss_settings', $field );
        }

        // Additional settings
        $additional_fields = [
            'vss_slack_enable_buttons' => 'Enable Quick Action Buttons',
            'vss_slack_include_images' => 'Include Product Images in Notifications',
            'vss_slack_mention_admin' => 'Mention Admin User ID',
            'vss_slack_enable_threads' => 'Use Threaded Conversations'
        ];

        foreach ( $additional_fields as $field => $label ) {
            add_settings_field(
                $field,
                $label,
                [ __CLASS__, 'setting_field_callback' ],
                'vss_settings',
                'vss_slack_settings',
                [ 'field' => $field, 'label' => $label, 'type' => $field === 'vss_slack_mention_admin' ? 'text' : 'checkbox' ]
            );
            register_setting( 'vss_settings', $field );
        }
    }

    /**
     * Notify about product submission
     */
    public static function notify_product_submission( $product_id, $vendor_id ) {
        if ( empty( self::$webhook_urls['product_submissions'] ) ) {
            return;
        }

        $product = self::get_product_data( $product_id );
        $vendor = get_userdata( $vendor_id );
        
        $message = self::build_submission_message( $product, $vendor );
        self::send_slack_message( self::$webhook_urls['product_submissions'], $message );
        
        // Log notification
        self::log_notification( 'submission', $product_id, $vendor_id );
    }

    /**
     * Notify about product approval
     */
    public static function notify_product_approved( $product_id, $vendor_id, $admin_id ) {
        if ( empty( self::$webhook_urls['product_approvals'] ) ) {
            return;
        }

        $product = self::get_product_data( $product_id );
        $vendor = get_userdata( $vendor_id );
        $admin = get_userdata( $admin_id );
        
        $message = self::build_approval_message( $product, $vendor, $admin );
        self::send_slack_message( self::$webhook_urls['product_approvals'], $message );
        
        // Also notify vendor
        if ( !empty( self::$webhook_urls['vendor_feedback'] ) ) {
            $vendor_message = self::build_vendor_approval_message( $product, $admin );
            self::send_slack_message( self::$webhook_urls['vendor_feedback'], $vendor_message );
        }
        
        self::log_notification( 'approval', $product_id, $vendor_id, $admin_id );
    }

    /**
     * Notify about product rejection
     */
    public static function notify_product_rejected( $product_id, $vendor_id, $admin_id ) {
        if ( empty( self::$webhook_urls['product_approvals'] ) ) {
            return;
        }

        $product = self::get_product_data( $product_id );
        $vendor = get_userdata( $vendor_id );
        $admin = get_userdata( $admin_id );
        
        $message = self::build_rejection_message( $product, $vendor, $admin );
        self::send_slack_message( self::$webhook_urls['product_approvals'], $message );
        
        // Also notify vendor
        if ( !empty( self::$webhook_urls['vendor_feedback'] ) ) {
            $vendor_message = self::build_vendor_rejection_message( $product, $admin );
            self::send_slack_message( self::$webhook_urls['vendor_feedback'], $vendor_message );
        }
        
        self::log_notification( 'rejection', $product_id, $vendor_id, $admin_id );
    }

    /**
     * Build submission message for Slack
     */
    private static function build_submission_message( $product, $vendor ) {
        $admin_mention = get_option( 'vss_slack_mention_admin', '' );
        $mention_text = !empty( $admin_mention ) ? "<@{$admin_mention}> " : '';
        
        $message = [
            'text' => "{$mention_text}New Product Submission Received!",
            'attachments' => [
                [
                    'color' => '#36a64f',
                    'title' => $product['product_name_en'] ?: $product['product_name_zh'],
                    'title_link' => admin_url( "admin.php?page=vss_product_uploads&action=view&product_id={$product['id']}" ),
                    'fields' => [
                        [
                            'title' => 'Vendor',
                            'value' => $vendor->display_name . ' (' . $vendor->user_email . ')',
                            'short' => true
                        ],
                        [
                            'title' => 'Category',
                            'value' => ucfirst( str_replace( '_', ' ', $product['category'] ) ),
                            'short' => true
                        ],
                        [
                            'title' => 'Submission Time',
                            'value' => date( 'M j, Y \a\t g:i A', strtotime( $product['submission_date'] ) ),
                            'short' => true
                        ],
                        [
                            'title' => 'Status',
                            'value' => ucfirst( $product['status'] ),
                            'short' => true
                        ]
                    ],
                    'footer' => 'VSS Product Upload System',
                    'ts' => time()
                ]
            ]
        ];

        // Add product image if enabled
        if ( get_option( 'vss_slack_include_images', false ) && !empty( $product['main_images'] ) ) {
            $images = json_decode( $product['main_images'], true );
            if ( !empty( $images[0] ) ) {
                $message['attachments'][0]['image_url'] = wp_get_attachment_url( $images[0] );
            }
        }

        // Add quick action buttons if enabled
        if ( get_option( 'vss_slack_enable_buttons', false ) ) {
            $message['attachments'][0]['actions'] = [
                [
                    'type' => 'button',
                    'text' => 'Quick Approve',
                    'style' => 'primary',
                    'url' => add_query_arg( [
                        'action' => 'vss_slack_quick_approve',
                        'product_id' => $product['id'],
                        'nonce' => wp_create_nonce( 'vss_slack_action' )
                    ], admin_url( 'admin-ajax.php' ) )
                ],
                [
                    'type' => 'button',
                    'text' => 'Quick Reject',
                    'style' => 'danger',
                    'url' => add_query_arg( [
                        'action' => 'vss_slack_quick_reject',
                        'product_id' => $product['id'],
                        'nonce' => wp_create_nonce( 'vss_slack_action' )
                    ], admin_url( 'admin-ajax.php' ) )
                ],
                [
                    'type' => 'button',
                    'text' => 'View Details',
                    'url' => admin_url( "admin.php?page=vss_product_uploads&action=view&product_id={$product['id']}" )
                ]
            ];
        }

        return $message;
    }

    /**
     * Build approval message for Slack
     */
    private static function build_approval_message( $product, $vendor, $admin ) {
        return [
            'text' => '✅ Product Approved!',
            'attachments' => [
                [
                    'color' => '#36a64f',
                    'title' => $product['product_name_en'] ?: $product['product_name_zh'],
                    'fields' => [
                        [
                            'title' => 'Vendor',
                            'value' => $vendor->display_name,
                            'short' => true
                        ],
                        [
                            'title' => 'Approved By',
                            'value' => $admin->display_name,
                            'short' => true
                        ],
                        [
                            'title' => 'Approval Time',
                            'value' => date( 'M j, Y \a\t g:i A' ),
                            'short' => true
                        ],
                        [
                            'title' => 'Selling Price',
                            'value' => !empty( $product['selling_price'] ) ? '$' . number_format( $product['selling_price'], 2 ) : 'Not set',
                            'short' => true
                        ]
                    ],
                    'footer' => 'Product is now live and available for purchase',
                    'ts' => time()
                ]
            ]
        ];
    }

    /**
     * Build rejection message for Slack
     */
    private static function build_rejection_message( $product, $vendor, $admin ) {
        return [
            'text' => '❌ Product Rejected',
            'attachments' => [
                [
                    'color' => '#ff4444',
                    'title' => $product['product_name_en'] ?: $product['product_name_zh'],
                    'fields' => [
                        [
                            'title' => 'Vendor',
                            'value' => $vendor->display_name,
                            'short' => true
                        ],
                        [
                            'title' => 'Rejected By',
                            'value' => $admin->display_name,
                            'short' => true
                        ],
                        [
                            'title' => 'Reason',
                            'value' => $product['rejection_reason'] ?: 'No reason provided',
                            'short' => false
                        ]
                    ],
                    'footer' => 'Vendor has been notified to make corrections',
                    'ts' => time()
                ]
            ]
        ];
    }

    /**
     * Build vendor approval message
     */
    private static function build_vendor_approval_message( $product, $admin ) {
        return [
            'text' => '🎉 Great news! Your product has been approved!',
            'attachments' => [
                [
                    'color' => '#36a64f',
                    'title' => $product['product_name_en'] ?: $product['product_name_zh'],
                    'fields' => [
                        [
                            'title' => 'Approved By',
                            'value' => $admin->display_name,
                            'short' => true
                        ],
                        [
                            'title' => 'Status',
                            'value' => 'Live and available for purchase',
                            'short' => true
                        ]
                    ],
                    'actions' => [
                        [
                            'type' => 'button',
                            'text' => 'View Product',
                            'style' => 'primary',
                            'url' => home_url( "?vss_action=upload_products&product_action=view&product_id={$product['id']}" )
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Build vendor rejection message
     */
    private static function build_vendor_rejection_message( $product, $admin ) {
        return [
            'text' => '📝 Your product submission needs some updates',
            'attachments' => [
                [
                    'color' => '#ff9500',
                    'title' => $product['product_name_en'] ?: $product['product_name_zh'],
                    'fields' => [
                        [
                            'title' => 'Reviewed By',
                            'value' => $admin->display_name,
                            'short' => true
                        ],
                        [
                            'title' => 'Feedback',
                            'value' => $product['rejection_reason'] ?: 'Please contact admin for more details',
                            'short' => false
                        ]
                    ],
                    'actions' => [
                        [
                            'type' => 'button',
                            'text' => 'Edit Product',
                            'style' => 'primary',
                            'url' => home_url( "?vss_action=upload_products&product_action=edit&product_id={$product['id']}" )
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Send message to Slack
     */
    private static function send_slack_message( $webhook_url, $message ) {
        if ( empty( $webhook_url ) ) {
            return false;
        }

        $response = wp_remote_post( $webhook_url, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => wp_json_encode( $message ),
            'timeout' => 15
        ] );

        if ( is_wp_error( $response ) ) {
            error_log( 'VSS Slack Integration Error: ' . $response->get_error_message() );
            return false;
        }

        $response_code = wp_remote_retrieve_response_code( $response );
        if ( $response_code !== 200 ) {
            error_log( "VSS Slack Integration Error: HTTP {$response_code}" );
            return false;
        }

        return true;
    }

    /**
     * Get product data
     */
    private static function get_product_data( $product_id ) {
        global $wpdb;
        
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}vss_product_uploads WHERE id = %d",
            $product_id
        ), ARRAY_A );
    }

    /**
     * Log notification
     */
    private static function log_notification( $type, $product_id, $vendor_id, $admin_id = null ) {
        global $wpdb;
        
        $wpdb->insert(
            $wpdb->prefix . 'vss_notification_log',
            [
                'type' => $type,
                'product_id' => $product_id,
                'vendor_id' => $vendor_id,
                'admin_id' => $admin_id,
                'sent_at' => current_time( 'mysql' ),
                'channel' => 'slack'
            ]
        );
    }

    /**
     * Handle Slack quick approve action
     */
    public static function handle_slack_quick_approve() {
        if ( ! wp_verify_nonce( $_GET['nonce'], 'vss_slack_action' ) ) {
            wp_die( 'Security check failed' );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }

        $product_id = intval( $_GET['product_id'] );
        
        // Update product status
        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . 'vss_product_uploads',
            [
                'status' => 'approved',
                'approval_date' => current_time( 'mysql' ),
                'approved_by' => get_current_user_id()
            ],
            [ 'id' => $product_id ]
        );

        // Trigger approval hook
        $product = $wpdb->get_row( $wpdb->prepare(
            "SELECT vendor_id FROM {$wpdb->prefix}vss_product_uploads WHERE id = %d",
            $product_id
        ) );

        if ( $product ) {
            do_action( 'vss_product_approved', $product_id, $product->vendor_id, get_current_user_id() );
        }

        wp_redirect( admin_url( 'admin.php?page=vss_product_uploads&approved=1' ) );
        exit;
    }

    /**
     * Handle Slack quick reject action
     */
    public static function handle_slack_quick_reject() {
        if ( ! wp_verify_nonce( $_GET['nonce'], 'vss_slack_action' ) ) {
            wp_die( 'Security check failed' );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }

        $product_id = intval( $_GET['product_id'] );
        
        // Redirect to rejection form
        wp_redirect( admin_url( "admin.php?page=vss_product_uploads&action=reject&product_id={$product_id}" ) );
        exit;
    }

    /**
     * Settings callbacks
     */
    public static function slack_settings_section_callback() {
        echo '<p>Configure Slack webhook URLs for real-time notifications about product submissions and approvals.</p>';
    }

    public static function webhook_field_callback( $args ) {
        $value = get_option( $args['field'], '' );
        echo "<input type='url' name='{$args['field']}' value='" . esc_attr( $value ) . "' class='regular-text' placeholder='https://hooks.slack.com/services/...' />";
        echo "<p class='description'>Webhook URL for {$args['label']} notifications</p>";
    }

    public static function setting_field_callback( $args ) {
        $value = get_option( $args['field'], '' );
        
        if ( $args['type'] === 'checkbox' ) {
            echo "<input type='checkbox' name='{$args['field']}' value='1' " . checked( 1, $value, false ) . " />";
        } else {
            echo "<input type='text' name='{$args['field']}' value='" . esc_attr( $value ) . "' class='regular-text' />";
            if ( $args['field'] === 'vss_slack_mention_admin' ) {
                echo "<p class='description'>Slack user ID to mention (e.g., U1234567890)</p>";
            }
        }
    }

    /**
     * Create notification log table
     */
    public static function create_notification_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'vss_notification_log';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            type varchar(50) NOT NULL,
            product_id bigint(20) NOT NULL,
            vendor_id bigint(20) NOT NULL,
            admin_id bigint(20) DEFAULT NULL,
            channel varchar(50) NOT NULL,
            sent_at datetime NOT NULL,
            response_data text DEFAULT NULL,
            PRIMARY KEY (id),
            KEY product_id (product_id),
            KEY vendor_id (vendor_id),
            KEY sent_at (sent_at)
        ) $charset_collate;";
        
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }
}

// Initialize the class
add_action( 'plugins_loaded', [ 'VSS_Enhanced_Slack_Integration', 'init' ] );
add_action( 'after_setup_theme', [ 'VSS_Enhanced_Slack_Integration', 'create_notification_table' ] );