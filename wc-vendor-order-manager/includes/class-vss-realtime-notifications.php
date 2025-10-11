<?php
/**
 * VSS Real-time Notifications and Status Tracking Module
 * 
 * WebSocket-like notifications, progress tracking, and status updates
 * 
 * @package VendorOrderManager
 * @subpackage Modules
 * @since 8.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Real-time Notifications Class
 */
class VSS_Realtime_Notifications {

    /**
     * Initialize real-time notifications
     */
    public static function init() {
        // AJAX handlers for notifications
        add_action( 'wp_ajax_vss_get_notifications', [ __CLASS__, 'get_user_notifications' ] );
        add_action( 'wp_ajax_vss_mark_notification_read', [ __CLASS__, 'mark_notification_read' ] );
        add_action( 'wp_ajax_vss_clear_notifications', [ __CLASS__, 'clear_all_notifications' ] );
        add_action( 'wp_ajax_vss_get_status_updates', [ __CLASS__, 'get_status_updates' ] );
        add_action( 'wp_ajax_vss_heartbeat_check', [ __CLASS__, 'heartbeat_check' ] );
        add_action( 'wp_ajax_vss_get_progress', [ __CLASS__, 'get_upload_progress' ] );
        
        // Admin AJAX handlers
        add_action( 'wp_ajax_vss_broadcast_notification', [ __CLASS__, 'broadcast_notification' ] );
        add_action( 'wp_ajax_vss_update_product_status', [ __CLASS__, 'update_product_status' ] );
        
        // Hook into product events
        add_action( 'vss_product_submitted', [ __CLASS__, 'on_product_submitted' ], 10, 2 );
        add_action( 'vss_product_approved', [ __CLASS__, 'on_product_approved' ], 10, 3 );
        add_action( 'vss_product_rejected', [ __CLASS__, 'on_product_rejected' ], 10, 3 );
        add_action( 'vss_product_updated', [ __CLASS__, 'on_product_updated' ], 10, 2 );
        add_action( 'vss_bulk_upload_started', [ __CLASS__, 'on_bulk_upload_started' ], 10, 2 );
        add_action( 'vss_bulk_upload_completed', [ __CLASS__, 'on_bulk_upload_completed' ], 10, 2 );
        
        // WordPress heartbeat integration
        add_filter( 'heartbeat_received', [ __CLASS__, 'heartbeat_received' ], 10, 2 );
        
        // Create notification tables
        self::create_notification_tables();
        
        // Enqueue scripts for real-time features
        add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_notification_scripts' ] );
        
        // Cleanup old notifications
        if ( ! wp_next_scheduled( 'vss_cleanup_old_notifications' ) ) {
            wp_schedule_event( time(), 'daily', 'vss_cleanup_old_notifications' );
        }
        add_action( 'vss_cleanup_old_notifications', [ __CLASS__, 'cleanup_old_notifications' ] );
    }

    /**
     * Create notification tables
     */
    public static function create_notification_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        // User notifications table
        $table_name = $wpdb->prefix . 'vss_user_notifications';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            type varchar(50) NOT NULL,
            title varchar(255) NOT NULL,
            message text NOT NULL,
            action_url varchar(500) DEFAULT NULL,
            action_text varchar(100) DEFAULT NULL,
            icon varchar(50) DEFAULT NULL,
            priority enum('low','normal','high','urgent') DEFAULT 'normal',
            is_read tinyint(1) DEFAULT 0,
            is_dismissed tinyint(1) DEFAULT 0,
            expires_at datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            read_at datetime DEFAULT NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY type (type),
            KEY is_read (is_read),
            KEY created_at (created_at),
            KEY expires_at (expires_at)
        ) $charset_collate;";
        
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
        
        // Status updates table
        $table_name = $wpdb->prefix . 'vss_status_updates';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            entity_type varchar(50) NOT NULL,
            entity_id bigint(20) NOT NULL,
            user_id bigint(20) NOT NULL,
            status varchar(50) NOT NULL,
            previous_status varchar(50) DEFAULT NULL,
            message text DEFAULT NULL,
            metadata longtext DEFAULT NULL,
            created_by bigint(20) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY entity_type_id (entity_type, entity_id),
            KEY user_id (user_id),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        dbDelta( $sql );
        
        // Progress tracking table
        $table_name = $wpdb->prefix . 'vss_progress_tracking';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            session_id varchar(255) NOT NULL,
            user_id bigint(20) NOT NULL,
            process_type varchar(50) NOT NULL,
            total_steps int(11) DEFAULT 0,
            completed_steps int(11) DEFAULT 0,
            current_step_name varchar(255) DEFAULT NULL,
            progress_percentage decimal(5,2) DEFAULT 0,
            status enum('pending','in_progress','completed','failed','cancelled') DEFAULT 'pending',
            error_message text DEFAULT NULL,
            estimated_completion datetime DEFAULT NULL,
            started_at datetime DEFAULT NULL,
            completed_at datetime DEFAULT NULL,
            last_updated datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY session_id (session_id),
            KEY user_id (user_id),
            KEY process_type (process_type),
            KEY status (status)
        ) $charset_collate;";
        
        dbDelta( $sql );
        
        // Notification preferences table
        $table_name = $wpdb->prefix . 'vss_notification_preferences';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            notification_type varchar(50) NOT NULL,
            enabled tinyint(1) DEFAULT 1,
            delivery_method enum('browser','email','sms','slack') DEFAULT 'browser',
            frequency enum('instant','hourly','daily','weekly') DEFAULT 'instant',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY user_type (user_id, notification_type)
        ) $charset_collate;";
        
        dbDelta( $sql );
    }

    /**
     * Enqueue notification scripts
     */
    public static function enqueue_notification_scripts() {
        if ( ! self::should_load_notifications() ) return;

        wp_enqueue_script( 
            'vss-notifications', 
            plugins_url( 'assets/js/vss-notifications.js', dirname( __DIR__ ) ), 
            [ 'jquery', 'heartbeat' ], 
            '8.0.0', 
            true 
        );

        wp_localize_script( 'vss-notifications', 'vssNotifications', [
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'vss_notifications' ),
            'userId' => get_current_user_id(),
            'heartbeatInterval' => 15000, // 15 seconds
            'strings' => [
                'newNotification' => 'You have a new notification',
                'markAllRead' => 'Mark all as read',
                'clearAll' => 'Clear all',
                'noNotifications' => 'No notifications',
                'connectionLost' => 'Connection lost. Retrying...',
                'connectionRestored' => 'Connection restored'
            ]
        ] );

        wp_enqueue_style( 
            'vss-notifications', 
            plugins_url( 'assets/css/vss-notifications.css', dirname( __DIR__ ) ), 
            [], 
            '8.0.0' 
        );
    }

    /**
     * Check if notifications should be loaded
     */
    private static function should_load_notifications() {
        return is_user_logged_in() && ( 
            self::is_current_user_vendor() || 
            current_user_can( 'manage_options' )
        );
    }

    /**
     * Product event handlers
     */
    public static function on_product_submitted( $product_id, $vendor_id ) {
        // Notify vendor
        self::create_notification( $vendor_id, [
            'type' => 'product_submitted',
            'title' => 'Product Submitted Successfully',
            'message' => 'Your product has been submitted for review and will be processed shortly.',
            'action_url' => self::get_product_url( $product_id ),
            'action_text' => 'View Product',
            'icon' => 'upload',
            'priority' => 'normal'
        ] );

        // Notify admins
        $admin_users = get_users( [ 'capability' => 'manage_options' ] );
        foreach ( $admin_users as $admin ) {
            self::create_notification( $admin->ID, [
                'type' => 'product_needs_review',
                'title' => 'New Product Awaiting Review',
                'message' => 'A vendor has submitted a new product that requires your review.',
                'action_url' => admin_url( "admin.php?page=vss_product_uploads&action=review&product_id={$product_id}" ),
                'action_text' => 'Review Now',
                'icon' => 'visibility',
                'priority' => 'high'
            ] );
        }

        // Track status
        self::track_status_change( 'product', $product_id, $vendor_id, 'pending_review', 'draft', 'Product submitted for review' );

        // Update progress if part of bulk upload
        self::update_bulk_upload_progress( $vendor_id, 1 );
    }

    public static function on_product_approved( $product_id, $vendor_id, $admin_id ) {
        $admin_name = get_userdata( $admin_id )->display_name;
        
        // Notify vendor
        self::create_notification( $vendor_id, [
            'type' => 'product_approved',
            'title' => 'Product Approved! 🎉',
            'message' => "Great news! Your product has been approved by {$admin_name} and is now live.",
            'action_url' => self::get_product_url( $product_id ),
            'action_text' => 'View Product',
            'icon' => 'check_circle',
            'priority' => 'high'
        ] );

        // Track status
        self::track_status_change( 
            'product', 
            $product_id, 
            $vendor_id, 
            'approved', 
            'pending_review', 
            "Product approved by {$admin_name}",
            [ 'approved_by' => $admin_id ]
        );
    }

    public static function on_product_rejected( $product_id, $vendor_id, $admin_id ) {
        $admin_name = get_userdata( $admin_id )->display_name;
        
        // Get rejection reason
        global $wpdb;
        $rejection_reason = $wpdb->get_var( $wpdb->prepare(
            "SELECT rejection_reason FROM {$wpdb->prefix}vss_pro_product_uploads WHERE id = %d",
            $product_id
        ) );

        // Notify vendor
        self::create_notification( $vendor_id, [
            'type' => 'product_rejected',
            'title' => 'Product Needs Updates',
            'message' => $rejection_reason ? "Your product was rejected: {$rejection_reason}" : 'Your product needs some updates before it can be approved.',
            'action_url' => self::get_product_url( $product_id, 'edit' ),
            'action_text' => 'Update Product',
            'icon' => 'edit',
            'priority' => 'high'
        ] );

        // Track status
        self::track_status_change( 
            'product', 
            $product_id, 
            $vendor_id, 
            'rejected', 
            'pending_review', 
            "Product rejected by {$admin_name}: {$rejection_reason}",
            [ 'rejected_by' => $admin_id, 'rejection_reason' => $rejection_reason ]
        );
    }

    public static function on_product_updated( $product_id, $vendor_id ) {
        // Check if product was previously rejected
        global $wpdb;
        $product_status = $wpdb->get_var( $wpdb->prepare(
            "SELECT status FROM {$wpdb->prefix}vss_pro_product_uploads WHERE id = %d",
            $product_id
        ) );

        if ( $product_status === 'rejected' ) {
            // Notify admins that rejected product has been updated
            $admin_users = get_users( [ 'capability' => 'manage_options' ] );
            foreach ( $admin_users as $admin ) {
                self::create_notification( $admin->ID, [
                    'type' => 'product_resubmitted',
                    'title' => 'Rejected Product Updated',
                    'message' => 'A previously rejected product has been updated and needs re-review.',
                    'action_url' => admin_url( "admin.php?page=vss_product_uploads&action=review&product_id={$product_id}" ),
                    'action_text' => 'Review Changes',
                    'icon' => 'refresh',
                    'priority' => 'normal'
                ] );
            }
        }

        // Track status
        self::track_status_change( 'product', $product_id, $vendor_id, 'updated', $product_status, 'Product updated by vendor' );
    }

    public static function on_bulk_upload_started( $session_id, $vendor_id ) {
        self::create_notification( $vendor_id, [
            'type' => 'bulk_upload_started',
            'title' => 'Bulk Upload Started',
            'message' => 'Your bulk product upload has started processing. You\'ll be notified when it\'s complete.',
            'icon' => 'cloud_upload',
            'priority' => 'normal'
        ] );

        // Initialize progress tracking
        global $wpdb;
        $session = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}vss_bulk_upload_sessions WHERE session_id = %s",
            $session_id
        ) );

        if ( $session ) {
            self::start_progress_tracking( $session_id, $vendor_id, 'bulk_upload', $session->total_products );
        }
    }

    public static function on_bulk_upload_completed( $session_id, $vendor_id ) {
        global $wpdb;
        $session = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}vss_bulk_upload_sessions WHERE session_id = %s",
            $session_id
        ) );

        if ( $session ) {
            $message = sprintf(
                'Bulk upload completed! %d products processed successfully, %d failed.',
                $session->successful_products,
                $session->failed_products
            );

            self::create_notification( $vendor_id, [
                'type' => 'bulk_upload_completed',
                'title' => 'Bulk Upload Complete',
                'message' => $message,
                'action_url' => admin_url( 'admin.php?page=vss_product_uploads' ),
                'action_text' => 'View Products',
                'icon' => 'check_circle',
                'priority' => 'high'
            ] );

            // Complete progress tracking
            self::complete_progress_tracking( $session_id );
        }
    }

    /**
     * Create notification
     */
    public static function create_notification( $user_id, $args ) {
        global $wpdb;

        $defaults = [
            'type' => 'general',
            'title' => '',
            'message' => '',
            'action_url' => null,
            'action_text' => null,
            'icon' => 'info',
            'priority' => 'normal',
            'expires_at' => null
        ];

        $notification = wp_parse_args( $args, $defaults );
        $notification['user_id'] = $user_id;

        // Check user preferences
        if ( ! self::should_send_notification( $user_id, $notification['type'] ) ) {
            return false;
        }

        $result = $wpdb->insert( $wpdb->prefix . 'vss_user_notifications', $notification );

        if ( $result ) {
            // Trigger real-time update
            self::trigger_realtime_update( $user_id, 'new_notification', $notification );
            
            // Send additional delivery methods if configured
            self::send_additional_notifications( $user_id, $notification );
            
            return $wpdb->insert_id;
        }

        return false;
    }

    /**
     * Get user notifications
     */
    public static function get_user_notifications() {
        check_ajax_referer( 'vss_notifications', 'nonce' );

        $user_id = get_current_user_id();
        $limit = intval( $_POST['limit'] ?? 20 );
        $offset = intval( $_POST['offset'] ?? 0 );
        $unread_only = isset( $_POST['unread_only'] ) ? (bool) $_POST['unread_only'] : false;

        global $wpdb;
        
        $where_clause = "WHERE user_id = %d AND (expires_at IS NULL OR expires_at > NOW())";
        $params = [ $user_id ];
        
        if ( $unread_only ) {
            $where_clause .= " AND is_read = 0";
        }

        $notifications = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}vss_user_notifications 
             {$where_clause}
             ORDER BY priority DESC, created_at DESC 
             LIMIT %d OFFSET %d",
            array_merge( $params, [ $limit, $offset ] )
        ) );

        // Get unread count
        $unread_count = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}vss_user_notifications 
             WHERE user_id = %d AND is_read = 0 AND (expires_at IS NULL OR expires_at > NOW())",
            $user_id
        ) );

        // Format notifications
        $formatted_notifications = array_map( [ __CLASS__, 'format_notification' ], $notifications );

        wp_send_json_success( [
            'notifications' => $formatted_notifications,
            'unread_count' => intval( $unread_count ),
            'has_more' => count( $notifications ) === $limit
        ] );
    }

    /**
     * Mark notification as read
     */
    public static function mark_notification_read() {
        check_ajax_referer( 'vss_notifications', 'nonce' );

        $notification_id = intval( $_POST['notification_id'] );
        $user_id = get_current_user_id();

        global $wpdb;
        $result = $wpdb->update(
            $wpdb->prefix . 'vss_user_notifications',
            [
                'is_read' => 1,
                'read_at' => current_time( 'mysql' )
            ],
            [
                'id' => $notification_id,
                'user_id' => $user_id
            ]
        );

        if ( $result !== false ) {
            wp_send_json_success( 'Notification marked as read' );
        } else {
            wp_send_json_error( 'Failed to mark notification as read' );
        }
    }

    /**
     * Clear all notifications
     */
    public static function clear_all_notifications() {
        check_ajax_referer( 'vss_notifications', 'nonce' );

        $user_id = get_current_user_id();

        global $wpdb;
        $result = $wpdb->update(
            $wpdb->prefix . 'vss_user_notifications',
            [ 'is_dismissed' => 1 ],
            [ 'user_id' => $user_id ]
        );

        wp_send_json_success( "Cleared {$result} notifications" );
    }

    /**
     * Get status updates
     */
    public static function get_status_updates() {
        check_ajax_referer( 'vss_notifications', 'nonce' );

        $entity_type = sanitize_text_field( $_POST['entity_type'] );
        $entity_id = intval( $_POST['entity_id'] );
        $user_id = get_current_user_id();

        global $wpdb;
        $updates = $wpdb->get_results( $wpdb->prepare(
            "SELECT su.*, u.display_name as created_by_name 
             FROM {$wpdb->prefix}vss_status_updates su 
             LEFT JOIN {$wpdb->users} u ON su.created_by = u.ID 
             WHERE su.entity_type = %s AND su.entity_id = %d AND su.user_id = %d 
             ORDER BY su.created_at DESC 
             LIMIT 50",
            $entity_type, $entity_id, $user_id
        ) );

        $formatted_updates = array_map( [ __CLASS__, 'format_status_update' ], $updates );

        wp_send_json_success( $formatted_updates );
    }

    /**
     * Heartbeat check for real-time updates
     */
    public static function heartbeat_check() {
        check_ajax_referer( 'vss_notifications', 'nonce' );

        $user_id = get_current_user_id();
        $last_check = sanitize_text_field( $_POST['last_check'] ?? '' );

        // Get new notifications since last check
        global $wpdb;
        $where_clause = "WHERE user_id = %d AND is_read = 0";
        $params = [ $user_id ];

        if ( $last_check ) {
            $where_clause .= " AND created_at > %s";
            $params[] = $last_check;
        }

        $new_notifications = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}vss_user_notifications 
             {$where_clause}
             ORDER BY created_at DESC 
             LIMIT 10",
            $params
        ) );

        // Get progress updates
        $progress_updates = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}vss_progress_tracking 
             WHERE user_id = %d AND status IN ('in_progress', 'completed') 
             AND last_updated > %s 
             ORDER BY last_updated DESC",
            $user_id, $last_check ?: '1970-01-01 00:00:00'
        ) );

        wp_send_json_success( [
            'notifications' => array_map( [ __CLASS__, 'format_notification' ], $new_notifications ),
            'progress_updates' => array_map( [ __CLASS__, 'format_progress_update' ], $progress_updates ),
            'timestamp' => current_time( 'mysql' )
        ] );
    }

    /**
     * Get upload progress
     */
    public static function get_upload_progress() {
        check_ajax_referer( 'vss_notifications', 'nonce' );

        $session_id = sanitize_text_field( $_POST['session_id'] );
        $user_id = get_current_user_id();

        global $wpdb;
        $progress = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}vss_progress_tracking 
             WHERE session_id = %s AND user_id = %d",
            $session_id, $user_id
        ) );

        if ( $progress ) {
            wp_send_json_success( self::format_progress_update( $progress ) );
        } else {
            wp_send_json_error( 'Progress not found' );
        }
    }

    /**
     * WordPress heartbeat integration
     */
    public static function heartbeat_received( $response, $data ) {
        if ( ! isset( $data['vss_notifications_check'] ) ) {
            return $response;
        }

        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            return $response;
        }

        // Get unread notification count
        global $wpdb;
        $unread_count = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}vss_user_notifications 
             WHERE user_id = %d AND is_read = 0 AND (expires_at IS NULL OR expires_at > NOW())",
            $user_id
        ) );

        $response['vss_notifications'] = [
            'unread_count' => intval( $unread_count ),
            'timestamp' => current_time( 'mysql' )
        ];

        return $response;
    }

    /**
     * Progress tracking methods
     */
    public static function start_progress_tracking( $session_id, $user_id, $process_type, $total_steps ) {
        global $wpdb;

        $wpdb->replace(
            $wpdb->prefix . 'vss_progress_tracking',
            [
                'session_id' => $session_id,
                'user_id' => $user_id,
                'process_type' => $process_type,
                'total_steps' => $total_steps,
                'completed_steps' => 0,
                'progress_percentage' => 0,
                'status' => 'in_progress',
                'started_at' => current_time( 'mysql' )
            ]
        );
    }

    public static function update_progress( $session_id, $completed_steps, $current_step_name = null ) {
        global $wpdb;

        $progress = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}vss_progress_tracking WHERE session_id = %s",
            $session_id
        ) );

        if ( ! $progress ) return false;

        $progress_percentage = $progress->total_steps > 0 ? ( $completed_steps / $progress->total_steps ) * 100 : 0;

        $update_data = [
            'completed_steps' => $completed_steps,
            'progress_percentage' => min( 100, $progress_percentage ),
            'last_updated' => current_time( 'mysql' )
        ];

        if ( $current_step_name ) {
            $update_data['current_step_name'] = $current_step_name;
        }

        $wpdb->update(
            $wpdb->prefix . 'vss_progress_tracking',
            $update_data,
            [ 'session_id' => $session_id ]
        );

        // Trigger real-time update
        self::trigger_realtime_update( $progress->user_id, 'progress_update', [
            'session_id' => $session_id,
            'progress_percentage' => $progress_percentage,
            'completed_steps' => $completed_steps,
            'current_step_name' => $current_step_name
        ] );
    }

    public static function complete_progress_tracking( $session_id, $error_message = null ) {
        global $wpdb;

        $update_data = [
            'status' => $error_message ? 'failed' : 'completed',
            'progress_percentage' => $error_message ? null : 100,
            'completed_at' => current_time( 'mysql' )
        ];

        if ( $error_message ) {
            $update_data['error_message'] = $error_message;
        }

        $wpdb->update(
            $wpdb->prefix . 'vss_progress_tracking',
            $update_data,
            [ 'session_id' => $session_id ]
        );
    }

    public static function update_bulk_upload_progress( $vendor_id, $completed_products ) {
        global $wpdb;
        
        // Find active bulk upload session
        $session = $wpdb->get_row( $wpdb->prepare(
            "SELECT session_id FROM {$wpdb->prefix}vss_bulk_upload_sessions 
             WHERE vendor_id = %d AND status = 'processing' 
             ORDER BY created_at DESC LIMIT 1",
            $vendor_id
        ) );

        if ( $session ) {
            self::update_progress( $session->session_id, $completed_products, 'Processing products...' );
        }
    }

    /**
     * Status tracking methods
     */
    public static function track_status_change( $entity_type, $entity_id, $user_id, $new_status, $previous_status = null, $message = null, $metadata = null ) {
        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . 'vss_status_updates',
            [
                'entity_type' => $entity_type,
                'entity_id' => $entity_id,
                'user_id' => $user_id,
                'status' => $new_status,
                'previous_status' => $previous_status,
                'message' => $message,
                'metadata' => $metadata ? wp_json_encode( $metadata ) : null,
                'created_by' => get_current_user_id()
            ]
        );
    }

    /**
     * Notification preference methods
     */
    private static function should_send_notification( $user_id, $notification_type ) {
        global $wpdb;
        
        $preference = $wpdb->get_row( $wpdb->prepare(
            "SELECT enabled FROM {$wpdb->prefix}vss_notification_preferences 
             WHERE user_id = %d AND notification_type = %s",
            $user_id, $notification_type
        ) );

        // Default to enabled if no preference set
        return $preference ? (bool) $preference->enabled : true;
    }

    /**
     * Trigger real-time update (simulated WebSocket)
     */
    private static function trigger_realtime_update( $user_id, $event_type, $data ) {
        // This would typically use WebSockets or Server-Sent Events
        // For now, we'll use WordPress transients for simple polling
        
        $transient_key = "vss_realtime_update_{$user_id}";
        $existing_updates = get_transient( $transient_key ) ?: [];
        
        $existing_updates[] = [
            'event' => $event_type,
            'data' => $data,
            'timestamp' => time()
        ];
        
        // Keep only last 10 updates
        $existing_updates = array_slice( $existing_updates, -10 );
        
        set_transient( $transient_key, $existing_updates, 300 ); // 5 minutes
    }

    /**
     * Formatting methods
     */
    private static function format_notification( $notification ) {
        return [
            'id' => intval( $notification->id ),
            'type' => $notification->type,
            'title' => $notification->title,
            'message' => $notification->message,
            'action_url' => $notification->action_url,
            'action_text' => $notification->action_text,
            'icon' => $notification->icon,
            'priority' => $notification->priority,
            'is_read' => (bool) $notification->is_read,
            'created_at' => $notification->created_at,
            'time_ago' => self::time_elapsed_string( $notification->created_at )
        ];
    }

    private static function format_status_update( $update ) {
        return [
            'id' => intval( $update->id ),
            'status' => $update->status,
            'previous_status' => $update->previous_status,
            'message' => $update->message,
            'created_by_name' => $update->created_by_name,
            'created_at' => $update->created_at,
            'time_ago' => self::time_elapsed_string( $update->created_at ),
            'metadata' => $update->metadata ? json_decode( $update->metadata, true ) : null
        ];
    }

    private static function format_progress_update( $progress ) {
        return [
            'session_id' => $progress->session_id,
            'process_type' => $progress->process_type,
            'total_steps' => intval( $progress->total_steps ),
            'completed_steps' => intval( $progress->completed_steps ),
            'current_step_name' => $progress->current_step_name,
            'progress_percentage' => floatval( $progress->progress_percentage ),
            'status' => $progress->status,
            'error_message' => $progress->error_message,
            'estimated_completion' => $progress->estimated_completion,
            'last_updated' => $progress->last_updated
        ];
    }

    /**
     * Helper methods
     */
    private static function get_product_url( $product_id, $action = 'view' ) {
        return home_url( "?vss_action=upload_products&product_action={$action}&product_id={$product_id}" );
    }

    private static function time_elapsed_string( $datetime ) {
        $time = time() - strtotime( $datetime );
        
        if ( $time < 60 ) {
            return 'Just now';
        } elseif ( $time < 3600 ) {
            return sprintf( '%d min ago', $time / 60 );
        } elseif ( $time < 86400 ) {
            return sprintf( '%d hr ago', $time / 3600 );
        } else {
            return sprintf( '%d days ago', $time / 86400 );
        }
    }

    private static function is_current_user_vendor() {
        return current_user_can( 'vss_vendor' ) || current_user_can( 'manage_woocommerce' );
    }

    private static function send_additional_notifications( $user_id, $notification ) {
        // Email, SMS, Slack notifications would be implemented here
        // Based on user preferences
    }

    /**
     * Cleanup old notifications
     */
    public static function cleanup_old_notifications() {
        global $wpdb;
        
        // Delete read notifications older than 30 days
        $wpdb->query(
            "DELETE FROM {$wpdb->prefix}vss_user_notifications 
             WHERE is_read = 1 AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)"
        );
        
        // Delete expired notifications
        $wpdb->query(
            "DELETE FROM {$wpdb->prefix}vss_user_notifications 
             WHERE expires_at IS NOT NULL AND expires_at < NOW()"
        );
        
        // Delete old status updates (keep 90 days)
        $wpdb->query(
            "DELETE FROM {$wpdb->prefix}vss_status_updates 
             WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)"
        );
        
        // Delete completed progress tracking (keep 7 days)
        $wpdb->query(
            "DELETE FROM {$wpdb->prefix}vss_progress_tracking 
             WHERE status IN ('completed', 'failed', 'cancelled') 
             AND completed_at < DATE_SUB(NOW(), INTERVAL 7 DAY)"
        );
    }
}

// Initialize the class
add_action( 'plugins_loaded', [ 'VSS_Realtime_Notifications', 'init' ] );