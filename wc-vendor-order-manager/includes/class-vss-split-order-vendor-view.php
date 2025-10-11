<?php
/**
 * VSS Split Order Vendor View Module
 * 
 * Enhanced vendor interface for managing split orders
 * 
 * @package VendorOrderManager
 * @subpackage Modules
 * @since 7.0.2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Trait for Split Order Vendor View functionality
 */
trait VSS_Split_Order_Vendor_View {

    /**
     * Display split order information in vendor portal
     */
    public static function display_vendor_split_info( $order ) {
        $vendor_id = get_current_user_id();
        $parent_order_id = get_post_meta( $order->get_id(), '_vss_parent_order_id', true );
        
        if ( $parent_order_id ) {
            $parent_order = wc_get_order( $parent_order_id );
            if ( $parent_order ) {
                ?>
                <div class="vss-split-order-notice">
                    <div class="vss-notice-icon">
                        <span class="dashicons dashicons-networking"></span>
                    </div>
                    <div class="vss-notice-content">
                        <h4><?php esc_html_e( 'Split Order Information', 'vss' ); ?></h4>
                        <p>
                            <?php 
                            printf( 
                                __( 'This is a partial order split from master order #%s', 'vss' ),
                                $parent_order->get_order_number()
                            );
                            ?>
                        </p>
                        
                        <?php
                        // Get sibling orders
                        $all_child_orders = get_post_meta( $parent_order_id, '_vss_child_order_ids', true );
                        $sibling_count = is_array( $all_child_orders ) ? count( $all_child_orders ) - 1 : 0;
                        
                        if ( $sibling_count > 0 ) {
                            printf(
                                '<p>' . _n(
                                    'There is %d other vendor handling parts of this order.',
                                    'There are %d other vendors handling parts of this order.',
                                    $sibling_count,
                                    'vss'
                                ) . '</p>',
                                $sibling_count
                            );
                        }
                        ?>
                        
                        <div class="vss-split-details">
                            <h5><?php esc_html_e( 'Your Assigned Items:', 'vss' ); ?></h5>
                            <ul class="vss-assigned-items">
                                <?php
                                foreach ( $order->get_items() as $item ) {
                                    echo '<li>';
                                    echo '<strong>' . esc_html( $item->get_name() ) . '</strong>';
                                    echo ' × ' . $item->get_quantity();
                                    echo ' - ' . wc_price( $item->get_total() );
                                    echo '</li>';
                                }
                                ?>
                            </ul>
                        </div>
                        
                        <div class="vss-split-coordination">
                            <h5><?php esc_html_e( 'Coordination Status:', 'vss' ); ?></h5>
                            <?php self::display_coordination_status( $order, $parent_order_id ); ?>
                        </div>
                    </div>
                </div>
                <?php
            }
        }
    }

    /**
     * Display coordination status between vendors
     */
    private static function display_coordination_status( $order, $parent_order_id ) {
        $all_child_orders = get_post_meta( $parent_order_id, '_vss_child_order_ids', true );
        
        if ( ! is_array( $all_child_orders ) ) {
            return;
        }

        $statuses = [];
        foreach ( $all_child_orders as $child_id ) {
            $child_order = wc_get_order( $child_id );
            if ( $child_order ) {
                $vendor_id = get_post_meta( $child_id, '_vss_vendor_user_id', true );
                $vendor = get_userdata( $vendor_id );
                
                $is_current = ( $child_id == $order->get_id() );
                
                $statuses[] = [
                    'vendor' => $vendor ? $vendor->display_name : __( 'Unknown Vendor', 'vss' ),
                    'status' => $child_order->get_status(),
                    'is_current' => $is_current,
                    'ship_date' => get_post_meta( $child_id, '_vss_estimated_ship_date', true ),
                    'tracking' => get_post_meta( $child_id, '_vss_tracking_number', true ),
                ];
            }
        }
        ?>
        
        <div class="vss-coordination-grid">
            <?php foreach ( $statuses as $status_info ) : ?>
                <div class="vss-vendor-status <?php echo $status_info['is_current'] ? 'current-vendor' : ''; ?>">
                    <div class="vendor-name">
                        <?php if ( $status_info['is_current'] ) : ?>
                            <span class="you-badge"><?php esc_html_e( 'YOU', 'vss' ); ?></span>
                        <?php endif; ?>
                        <?php echo esc_html( $status_info['vendor'] ); ?>
                    </div>
                    <div class="vendor-status-badge status-<?php echo esc_attr( $status_info['status'] ); ?>">
                        <?php echo esc_html( wc_get_order_status_name( $status_info['status'] ) ); ?>
                    </div>
                    <?php if ( $status_info['ship_date'] ) : ?>
                        <div class="vendor-ship-date">
                            <span class="dashicons dashicons-calendar-alt"></span>
                            <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $status_info['ship_date'] ) ) ); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ( $status_info['tracking'] ) : ?>
                        <div class="vendor-tracking">
                            <span class="dashicons dashicons-airplane"></span>
                            <?php esc_html_e( 'Shipped', 'vss' ); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php
        // Check if all vendors have completed their parts
        $all_completed = true;
        $all_shipped = true;
        foreach ( $statuses as $status_info ) {
            if ( ! in_array( $status_info['status'], [ 'completed', 'shipped' ] ) ) {
                $all_completed = false;
            }
            if ( $status_info['status'] !== 'shipped' && ! $status_info['tracking'] ) {
                $all_shipped = false;
            }
        }
        
        if ( $all_completed ) {
            ?>
            <div class="vss-coordination-success">
                <span class="dashicons dashicons-yes-alt"></span>
                <?php esc_html_e( 'All vendors have completed their parts of this order!', 'vss' ); ?>
            </div>
            <?php
        } elseif ( $all_shipped ) {
            ?>
            <div class="vss-coordination-info">
                <span class="dashicons dashicons-info"></span>
                <?php esc_html_e( 'All parts of this order have been shipped.', 'vss' ); ?>
            </div>
            <?php
        }
    }

    /**
     * Display split order summary in vendor dashboard
     */
    public static function display_split_orders_summary( $vendor_id ) {
        // Get all split orders for this vendor
        $split_orders = wc_get_orders( [
            'meta_query' => [
                [
                    'key' => '_vss_vendor_user_id',
                    'value' => $vendor_id,
                ],
                [
                    'key' => '_vss_parent_order_id',
                    'compare' => 'EXISTS',
                ],
            ],
            'limit' => -1,
            'return' => 'objects',
        ] );

        if ( empty( $split_orders ) ) {
            return;
        }

        $active_splits = [];
        $completed_splits = [];

        foreach ( $split_orders as $split_order ) {
            if ( $split_order->has_status( [ 'completed', 'refunded', 'cancelled' ] ) ) {
                $completed_splits[] = $split_order;
            } else {
                $active_splits[] = $split_order;
            }
        }

        if ( ! empty( $active_splits ) ) {
            ?>
            <div class="vss-split-orders-widget">
                <h3>
                    <span class="dashicons dashicons-networking"></span>
                    <?php esc_html_e( 'Active Split Orders', 'vss' ); ?>
                </h3>
                <p class="description">
                    <?php esc_html_e( 'Orders that are split between multiple vendors', 'vss' ); ?>
                </p>
                
                <div class="vss-split-orders-list">
                    <?php foreach ( $active_splits as $split_order ) : 
                        $parent_id = get_post_meta( $split_order->get_id(), '_vss_parent_order_id', true );
                        $parent_order = wc_get_order( $parent_id );
                    ?>
                        <div class="vss-split-order-item">
                            <div class="split-order-header">
                                <a href="<?php echo esc_url( add_query_arg( [ 
                                    'vss_action' => 'view_order', 
                                    'order_id' => $split_order->get_id() 
                                ], get_permalink() ) ); ?>">
                                    #<?php echo esc_html( $split_order->get_order_number() ); ?>
                                </a>
                                <span class="split-badge"><?php esc_html_e( 'SPLIT', 'vss' ); ?></span>
                            </div>
                            <div class="split-order-meta">
                                <?php if ( $parent_order ) : ?>
                                    <span class="parent-info">
                                        <?php 
                                        printf( 
                                            __( 'From order #%s', 'vss' ),
                                            $parent_order->get_order_number()
                                        );
                                        ?>
                                    </span>
                                <?php endif; ?>
                                <span class="split-status status-<?php echo esc_attr( $split_order->get_status() ); ?>">
                                    <?php echo esc_html( wc_get_order_status_name( $split_order->get_status() ) ); ?>
                                </span>
                            </div>
                            <div class="split-order-items">
                                <?php
                                $item_count = $split_order->get_item_count();
                                printf(
                                    _n( '%d item', '%d items', $item_count, 'vss' ),
                                    $item_count
                                );
                                ?>
                                - <?php echo wp_kses_post( $split_order->get_formatted_order_total() ); ?>
                            </div>
                            
                            <?php
                            // Show other vendors working on this order
                            $all_child_orders = get_post_meta( $parent_id, '_vss_child_order_ids', true );
                            if ( is_array( $all_child_orders ) && count( $all_child_orders ) > 1 ) {
                                ?>
                                <div class="split-order-vendors">
                                    <span class="dashicons dashicons-groups"></span>
                                    <?php
                                    printf(
                                        _n(
                                            '%d vendor total',
                                            '%d vendors total',
                                            count( $all_child_orders ),
                                            'vss'
                                        ),
                                        count( $all_child_orders )
                                    );
                                    ?>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <a href="<?php echo esc_url( add_query_arg( [ 
                    'vss_action' => 'orders',
                    'filter' => 'split_orders'
                ], get_permalink() ) ); ?>" class="view-all-link">
                    <?php esc_html_e( 'View all split orders →', 'vss' ); ?>
                </a>
            </div>
            <?php
        }
    }

    /**
     * Add split order filter to vendor orders page
     */
    public static function add_split_order_filter( $filters ) {
        $filters['split_orders'] = __( 'Split Orders', 'vss' );
        return $filters;
    }

    /**
     * Apply split order filter to query
     */
    public static function apply_split_order_filter( $args, $filter ) {
        if ( $filter === 'split_orders' ) {
            if ( ! isset( $args['meta_query'] ) ) {
                $args['meta_query'] = [];
            }
            $args['meta_query'][] = [
                'key' => '_vss_parent_order_id',
                'compare' => 'EXISTS',
            ];
        }
        return $args;
    }

    /**
     * Display communication panel for split orders
     */
    public static function display_split_order_communication( $order ) {
        $parent_order_id = get_post_meta( $order->get_id(), '_vss_parent_order_id', true );
        
        if ( ! $parent_order_id ) {
            return;
        }

        $vendor_id = get_current_user_id();
        $messages = get_post_meta( $parent_order_id, '_vss_vendor_messages', true );
        
        if ( ! is_array( $messages ) ) {
            $messages = [];
        }
        ?>
        
        <div class="vss-split-communication">
            <h3><?php esc_html_e( 'Vendor Coordination', 'vss' ); ?></h3>
            
            <div class="vss-messages-container">
                <?php if ( ! empty( $messages ) ) : ?>
                    <?php foreach ( $messages as $message ) : 
                        $is_own_message = ( $message['vendor_id'] == $vendor_id );
                        $vendor = get_userdata( $message['vendor_id'] );
                    ?>
                        <div class="vss-message <?php echo $is_own_message ? 'own-message' : ''; ?>">
                            <div class="message-header">
                                <strong><?php echo $vendor ? esc_html( $vendor->display_name ) : __( 'Unknown', 'vss' ); ?></strong>
                                <span class="message-time">
                                    <?php echo esc_html( human_time_diff( strtotime( $message['timestamp'] ), current_time( 'timestamp' ) ) ); ?> 
                                    <?php esc_html_e( 'ago', 'vss' ); ?>
                                </span>
                            </div>
                            <div class="message-content">
                                <?php echo esc_html( $message['message'] ); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <p class="no-messages"><?php esc_html_e( 'No coordination messages yet.', 'vss' ); ?></p>
                <?php endif; ?>
            </div>
            
            <form class="vss-send-message" data-parent-order="<?php echo esc_attr( $parent_order_id ); ?>">
                <textarea name="message" placeholder="<?php esc_attr_e( 'Message other vendors handling this order...', 'vss' ); ?>" rows="3"></textarea>
                <button type="submit" class="button button-primary">
                    <?php esc_html_e( 'Send Message', 'vss' ); ?>
                </button>
            </form>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('.vss-send-message').on('submit', function(e) {
                e.preventDefault();
                
                var form = $(this);
                var message = form.find('textarea').val();
                var parentOrder = form.data('parent-order');
                
                if (!message.trim()) {
                    return;
                }
                
                $.ajax({
                    url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
                    type: 'POST',
                    data: {
                        action: 'vss_send_vendor_message',
                        parent_order: parentOrder,
                        message: message,
                        nonce: '<?php echo wp_create_nonce( 'vss_vendor_message' ); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        }
                    }
                });
            });
        });
        </script>
        <?php
    }
}

// Add styles for split order display
add_action( 'wp_head', function() {
    if ( ! is_page() || get_query_var( 'vss_action' ) === '' ) {
        return;
    }
    ?>
    <style>
    .vss-split-order-notice {
        background: linear-gradient(135deg, #f0f7ff, #e8f3ff);
        border: 2px solid #2271b1;
        border-radius: 12px;
        padding: 20px;
        margin: 20px 0;
        display: flex;
        gap: 20px;
    }
    
    .vss-notice-icon {
        flex-shrink: 0;
    }
    
    .vss-notice-icon .dashicons {
        font-size: 48px;
        color: #2271b1;
    }
    
    .vss-notice-content {
        flex-grow: 1;
    }
    
    .vss-notice-content h4 {
        margin: 0 0 10px 0;
        color: #1f2937;
        font-size: 1.3em;
    }
    
    .vss-split-details {
        background: white;
        padding: 15px;
        border-radius: 8px;
        margin: 15px 0;
    }
    
    .vss-split-details h5 {
        margin: 0 0 10px 0;
        color: #374151;
    }
    
    .vss-assigned-items {
        list-style: none;
        margin: 0;
        padding: 0;
    }
    
    .vss-assigned-items li {
        padding: 8px 0;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .vss-assigned-items li:last-child {
        border-bottom: none;
    }
    
    .vss-coordination-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin: 15px 0;
    }
    
    .vss-vendor-status {
        background: white;
        padding: 15px;
        border-radius: 8px;
        border: 2px solid #e5e7eb;
        text-align: center;
        transition: all 0.3s ease;
    }
    
    .vss-vendor-status.current-vendor {
        border-color: #2271b1;
        background: #f0f7ff;
    }
    
    .vendor-name {
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 10px;
    }
    
    .you-badge {
        background: #2271b1;
        color: white;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 0.8em;
        margin-right: 5px;
    }
    
    .vendor-status-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 20px;
        font-size: 0.85em;
        font-weight: 600;
        margin: 5px 0;
    }
    
    .vendor-status-badge.status-processing {
        background: #fef3c7;
        color: #92400e;
    }
    
    .vendor-status-badge.status-shipped {
        background: #dbeafe;
        color: #1e40af;
    }
    
    .vendor-status-badge.status-completed {
        background: #d1fae5;
        color: #065f46;
    }
    
    .vendor-ship-date,
    .vendor-tracking {
        margin-top: 8px;
        font-size: 0.9em;
        color: #6b7280;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
    }
    
    .vendor-ship-date .dashicons,
    .vendor-tracking .dashicons {
        font-size: 16px;
    }
    
    .vss-coordination-success,
    .vss-coordination-info {
        padding: 12px;
        border-radius: 8px;
        margin-top: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .vss-coordination-success {
        background: #d1fae5;
        color: #065f46;
    }
    
    .vss-coordination-info {
        background: #dbeafe;
        color: #1e40af;
    }
    
    /* Split Orders Widget */
    .vss-split-orders-widget {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        margin-bottom: 30px;
    }
    
    .vss-split-orders-widget h3 {
        margin: 0 0 10px 0;
        display: flex;
        align-items: center;
        gap: 10px;
        color: #1f2937;
    }
    
    .vss-split-orders-list {
        margin: 20px 0;
    }
    
    .vss-split-order-item {
        padding: 15px;
        background: #f9fafb;
        border-radius: 8px;
        margin-bottom: 10px;
        transition: all 0.3s ease;
    }
    
    .vss-split-order-item:hover {
        background: #f3f4f6;
        transform: translateX(5px);
    }
    
    .split-order-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 8px;
    }
    
    .split-badge {
        background: #9333ea;
        color: white;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 0.75em;
        font-weight: 700;
    }
    
    .split-order-meta {
        display: flex;
        gap: 15px;
        align-items: center;
        font-size: 0.9em;
        color: #6b7280;
        margin-bottom: 5px;
    }
    
    .split-order-vendors {
        display: flex;
        align-items: center;
        gap: 5px;
        margin-top: 8px;
        color: #9333ea;
        font-size: 0.9em;
    }
    
    /* Communication Panel */
    .vss-split-communication {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin: 20px 0;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }
    
    .vss-messages-container {
        max-height: 400px;
        overflow-y: auto;
        padding: 15px;
        background: #f9fafb;
        border-radius: 8px;
        margin: 15px 0;
    }
    
    .vss-message {
        background: white;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 10px;
        border-left: 3px solid #e5e7eb;
    }
    
    .vss-message.own-message {
        border-left-color: #2271b1;
        background: #f0f7ff;
    }
    
    .message-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 5px;
    }
    
    .message-time {
        color: #9ca3af;
        font-size: 0.85em;
    }
    
    .vss-send-message textarea {
        width: 100%;
        padding: 10px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        resize: vertical;
    }
    
    .vss-send-message button {
        margin-top: 10px;
    }
    </style>
    <?php
});