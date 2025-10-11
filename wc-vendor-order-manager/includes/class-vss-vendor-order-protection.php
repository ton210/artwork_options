<?php
/**
 * VSS Vendor Order Protection Module
 * 
 * Ensures vendor orders are never deleted and always accessible
 * 
 * @package VendorOrderManager
 * @subpackage Modules
 * @since 7.0.2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Trait for Order Protection functionality
 */
trait VSS_Vendor_Order_Protection {

    /**
     * Initialize order protection hooks
     */
    public static function init_order_protection() {
        // Prevent deletion of orders assigned to vendors
        add_action( 'before_delete_post', [ self::class, 'prevent_vendor_order_deletion' ], 10, 1 );
        add_action( 'wp_trash_post', [ self::class, 'prevent_vendor_order_trash' ], 10, 1 );
        
        // Ensure vendor meta is never removed
        add_filter( 'delete_post_metadata', [ self::class, 'protect_vendor_metadata' ], 10, 5 );
        add_filter( 'update_post_metadata', [ self::class, 'protect_vendor_assignment' ], 10, 5 );
        
        // Add admin notices for protection
        add_action( 'admin_notices', [ self::class, 'show_order_protection_notice' ] );
        
        // Log all vendor order access for audit trail
        add_action( 'vss_vendor_accessed_order', [ self::class, 'log_order_access' ], 10, 2 );
        
        // Ensure orders remain visible to vendors even if status changes
        add_filter( 'woocommerce_order_is_visible', [ self::class, 'ensure_vendor_order_visibility' ], 10, 2 );
    }

    /**
     * Prevent deletion of vendor-assigned orders
     */
    public static function prevent_vendor_order_deletion( $post_id ) {
        // Check if this is an order
        if ( get_post_type( $post_id ) !== 'shop_order' ) {
            return;
        }
        
        // Check if order is assigned to a vendor
        $vendor_id = get_post_meta( $post_id, '_vss_vendor_user_id', true );
        
        if ( ! empty( $vendor_id ) ) {
            // Log the attempt
            self::log_deletion_attempt( $post_id, $vendor_id );
            
            // Prevent deletion
            wp_die( 
                __( 'This order cannot be deleted because it is assigned to a vendor. Vendor orders must be preserved for record-keeping purposes.', 'vss' ),
                __( 'Order Protection', 'vss' ),
                [ 'back_link' => true ]
            );
        }
    }

    /**
     * Prevent trashing of vendor-assigned orders
     */
    public static function prevent_vendor_order_trash( $post_id ) {
        // Check if this is an order
        if ( get_post_type( $post_id ) !== 'shop_order' ) {
            return $post_id;
        }
        
        // Check if order is assigned to a vendor
        $vendor_id = get_post_meta( $post_id, '_vss_vendor_user_id', true );
        
        if ( ! empty( $vendor_id ) ) {
            // Add admin notice
            set_transient( 'vss_order_trash_prevented_' . get_current_user_id(), $post_id, 30 );
            
            // Prevent trashing
            return false;
        }
        
        return $post_id;
    }

    /**
     * Protect vendor metadata from deletion
     */
    public static function protect_vendor_metadata( $check, $object_id, $meta_key, $meta_value, $delete_all ) {
        // Protect vendor assignment metadata
        if ( $meta_key === '_vss_vendor_user_id' ) {
            // Log the attempt
            self::log_metadata_deletion_attempt( $object_id, $meta_key );
            
            // Prevent deletion
            return false;
        }
        
        return $check;
    }

    /**
     * Protect vendor assignment from being removed via update
     */
    public static function protect_vendor_assignment( $check, $object_id, $meta_key, $meta_value, $prev_value ) {
        // If trying to update vendor assignment to empty/null
        if ( $meta_key === '_vss_vendor_user_id' && empty( $meta_value ) ) {
            $current_vendor = get_post_meta( $object_id, '_vss_vendor_user_id', true );
            
            if ( ! empty( $current_vendor ) ) {
                // Log the attempt
                self::log_assignment_removal_attempt( $object_id, $current_vendor );
                
                // Prevent removal (allow only reassignment to another vendor)
                return false;
            }
        }
        
        return $check;
    }

    /**
     * Show admin notice when order protection is triggered
     */
    public static function show_order_protection_notice() {
        $user_id = get_current_user_id();
        $prevented_order = get_transient( 'vss_order_trash_prevented_' . $user_id );
        
        if ( $prevented_order ) {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p>
                    <?php 
                    printf( 
                        __( 'Order #%s cannot be deleted or trashed because it is assigned to a vendor. Vendor orders must be preserved for record-keeping and accountability.', 'vss' ),
                        $prevented_order
                    ); 
                    ?>
                </p>
            </div>
            <?php
            delete_transient( 'vss_order_trash_prevented_' . $user_id );
        }
    }

    /**
     * Ensure vendor can always see their assigned orders
     */
    public static function ensure_vendor_order_visibility( $visible, $order ) {
        // If user is a vendor, check if order is assigned to them
        if ( self::is_current_user_vendor() ) {
            $vendor_id = get_current_user_id();
            $assigned_vendor = get_post_meta( $order->get_id(), '_vss_vendor_user_id', true );
            
            if ( $assigned_vendor == $vendor_id ) {
                // Always make order visible to assigned vendor
                return true;
            }
        }
        
        return $visible;
    }

    /**
     * Get all lifetime orders for a vendor
     */
    public static function get_vendor_lifetime_orders( $vendor_id, $args = [] ) {
        $defaults = [
            'meta_key' => '_vss_vendor_user_id',
            'meta_value' => $vendor_id,
            'limit' => -1, // Get all orders
            'orderby' => 'date',
            'order' => 'DESC',
            'post_status' => 'any', // Include ALL statuses
            'type' => 'shop_order',
            'return' => 'objects',
        ];
        
        $args = wp_parse_args( $args, $defaults );
        
        // Never allow removal of vendor filter
        $args['meta_key'] = '_vss_vendor_user_id';
        $args['meta_value'] = $vendor_id;
        
        return wc_get_orders( $args );
    }

    /**
     * Get vendor lifetime statistics
     */
    public static function get_vendor_lifetime_stats( $vendor_id ) {
        $stats = [
            'total_orders' => 0,
            'total_revenue' => 0,
            'total_items' => 0,
            'first_order_date' => null,
            'last_order_date' => null,
            'order_statuses' => [],
        ];
        
        // Get ALL orders for this vendor
        $orders = self::get_vendor_lifetime_orders( $vendor_id );
        
        foreach ( $orders as $order ) {
            $stats['total_orders']++;
            $stats['total_revenue'] += $order->get_total();
            $stats['total_items'] += $order->get_item_count();
            
            // Track order statuses
            $status = $order->get_status();
            if ( ! isset( $stats['order_statuses'][$status] ) ) {
                $stats['order_statuses'][$status] = 0;
            }
            $stats['order_statuses'][$status]++;
            
            // Track date range
            $order_date = $order->get_date_created();
            if ( $order_date ) {
                if ( ! $stats['first_order_date'] || $order_date < $stats['first_order_date'] ) {
                    $stats['first_order_date'] = $order_date;
                }
                if ( ! $stats['last_order_date'] || $order_date > $stats['last_order_date'] ) {
                    $stats['last_order_date'] = $order_date;
                }
            }
        }
        
        return $stats;
    }

    /**
     * Log deletion attempt for audit trail
     */
    private static function log_deletion_attempt( $order_id, $vendor_id ) {
        $log_entry = sprintf(
            '[%s] Deletion attempt prevented for Order #%d assigned to Vendor ID %d by User ID %d',
            current_time( 'mysql' ),
            $order_id,
            $vendor_id,
            get_current_user_id()
        );
        
        error_log( $log_entry );
        
        // Store in database for audit trail
        add_post_meta( $order_id, '_vss_deletion_attempts', [
            'timestamp' => current_time( 'mysql' ),
            'user_id' => get_current_user_id(),
            'action' => 'delete',
        ]);
    }

    /**
     * Log metadata deletion attempt
     */
    private static function log_metadata_deletion_attempt( $order_id, $meta_key ) {
        $log_entry = sprintf(
            '[%s] Metadata deletion attempt prevented for Order #%d, meta_key: %s, by User ID %d',
            current_time( 'mysql' ),
            $order_id,
            $meta_key,
            get_current_user_id()
        );
        
        error_log( $log_entry );
    }

    /**
     * Log assignment removal attempt
     */
    private static function log_assignment_removal_attempt( $order_id, $vendor_id ) {
        $log_entry = sprintf(
            '[%s] Vendor assignment removal attempt prevented for Order #%d (Vendor ID %d) by User ID %d',
            current_time( 'mysql' ),
            $order_id,
            $vendor_id,
            get_current_user_id()
        );
        
        error_log( $log_entry );
    }

    /**
     * Log order access for audit trail
     */
    public static function log_order_access( $order_id, $vendor_id ) {
        // Store access log
        add_post_meta( $order_id, '_vss_access_log', [
            'vendor_id' => $vendor_id,
            'timestamp' => current_time( 'mysql' ),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
        ]);
    }

    /**
     * Display vendor lifetime summary
     */
    public static function display_lifetime_summary( $vendor_id ) {
        $stats = self::get_vendor_lifetime_stats( $vendor_id );
        ?>
        <div class="vss-lifetime-summary">
            <h3><?php esc_html_e( 'Lifetime Order Summary', 'vss' ); ?></h3>
            <div class="lifetime-stats">
                <div class="stat-item">
                    <span class="stat-label"><?php esc_html_e( 'Total Orders:', 'vss' ); ?></span>
                    <span class="stat-value"><?php echo number_format( $stats['total_orders'] ); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label"><?php esc_html_e( 'Total Revenue:', 'vss' ); ?></span>
                    <span class="stat-value"><?php echo wc_price( $stats['total_revenue'] ); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label"><?php esc_html_e( 'Total Items:', 'vss' ); ?></span>
                    <span class="stat-value"><?php echo number_format( $stats['total_items'] ); ?></span>
                </div>
                <?php if ( $stats['first_order_date'] ) : ?>
                <div class="stat-item">
                    <span class="stat-label"><?php esc_html_e( 'First Order:', 'vss' ); ?></span>
                    <span class="stat-value"><?php echo $stats['first_order_date']->date_i18n( get_option( 'date_format' ) ); ?></span>
                </div>
                <?php endif; ?>
                <?php if ( $stats['last_order_date'] ) : ?>
                <div class="stat-item">
                    <span class="stat-label"><?php esc_html_e( 'Last Order:', 'vss' ); ?></span>
                    <span class="stat-value"><?php echo $stats['last_order_date']->date_i18n( get_option( 'date_format' ) ); ?></span>
                </div>
                <?php endif; ?>
            </div>
            <div class="status-breakdown">
                <h4><?php esc_html_e( 'Orders by Status', 'vss' ); ?></h4>
                <?php foreach ( $stats['order_statuses'] as $status => $count ) : ?>
                    <div class="status-item">
                        <span class="status-badge status-<?php echo esc_attr( $status ); ?>">
                            <?php echo esc_html( wc_get_order_status_name( $status ) ); ?>
                        </span>
                        <span class="status-count"><?php echo number_format( $count ); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Create database backup of vendor orders (for extra safety)
     */
    public static function backup_vendor_orders( $vendor_id ) {
        global $wpdb;
        
        $orders = self::get_vendor_lifetime_orders( $vendor_id );
        $backup_data = [];
        
        foreach ( $orders as $order ) {
            $backup_data[] = [
                'order_id' => $order->get_id(),
                'order_number' => $order->get_order_number(),
                'vendor_id' => $vendor_id,
                'customer_email' => $order->get_billing_email(),
                'total' => $order->get_total(),
                'status' => $order->get_status(),
                'date_created' => $order->get_date_created()->date( 'Y-m-d H:i:s' ),
                'items' => $order->get_items(),
                'meta_data' => $order->get_meta_data(),
            ];
        }
        
        // Store backup in a custom table or as serialized option
        update_user_meta( $vendor_id, '_vss_orders_backup_' . date( 'Ymd' ), $backup_data );
        
        return count( $backup_data );
    }
}