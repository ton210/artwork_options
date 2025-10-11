<?php
/**
 * VSS Product Admin Management
 *
 * Admin interface for product review, approval, and cost management
 *
 * @package VendorOrderManager
 * @since 7.0.2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class VSS_Product_Admin {

    /**
     * Initialize admin functionality
     */
    public static function init() {
        // Add admin menu
        add_action( 'admin_menu', [ self::class, 'add_admin_menu' ] );
        
        // AJAX handlers for product management
        add_action( 'wp_ajax_vss_approve_product', [ self::class, 'ajax_approve_product' ] );
        add_action( 'wp_ajax_vss_reject_product', [ self::class, 'ajax_reject_product' ] );
        add_action( 'wp_ajax_vss_update_product_costs', [ self::class, 'ajax_update_product_costs' ] );
        add_action( 'wp_ajax_vss_get_product_details', [ self::class, 'ajax_get_product_details' ] );
        add_action( 'wp_ajax_vss_bulk_product_action', [ self::class, 'ajax_bulk_product_action' ] );
        add_action( 'wp_ajax_vss_send_vendor_feedback', [ self::class, 'ajax_send_vendor_feedback' ] );
        
        // Enqueue admin assets
        add_action( 'admin_enqueue_scripts', [ self::class, 'enqueue_admin_assets' ] );
        
        // Add dashboard widget
        add_action( 'wp_dashboard_setup', [ self::class, 'add_dashboard_widgets' ] );
        
        // Vendor store assignment
        add_action( 'wp_ajax_vss_assign_vendor_stores', [ self::class, 'ajax_assign_vendor_stores' ] );
    }

    /**
     * Add admin menu
     */
    public static function add_admin_menu() {
        add_menu_page(
            __( 'Product Management', 'vss' ),
            __( 'Product Management', 'vss' ),
            'manage_options',
            'vss-product-management',
            [ self::class, 'render_admin_page' ],
            'dashicons-products',
            30
        );
        
        add_submenu_page(
            'vss-product-management',
            __( 'Product Review', 'vss' ),
            __( 'Product Review', 'vss' ),
            'manage_options',
            'vss-product-management',
            [ self::class, 'render_admin_page' ]
        );
        
        add_submenu_page(
            'vss-product-management',
            __( 'Product Analytics', 'vss' ),
            __( 'Product Analytics', 'vss' ),
            'manage_options',
            'vss-product-analytics',
            [ self::class, 'render_analytics_page' ]
        );
        
        add_submenu_page(
            'vss-product-management',
            __( 'Vendor Stores', 'vss' ),
            __( 'Vendor Stores', 'vss' ),
            'manage_options',
            'vss-vendor-stores',
            [ self::class, 'render_vendor_stores_page' ]
        );
        
        add_submenu_page(
            'vss-product-management',
            __( 'Settings', 'vss' ),
            __( 'Settings', 'vss' ),
            'manage_options',
            'vss-product-settings',
            [ self::class, 'render_settings_page' ]
        );
    }

    /**
     * Enqueue admin assets
     */
    public static function enqueue_admin_assets( $hook ) {
        // Only load on our admin pages
        if ( strpos( $hook, 'vss-product-' ) === false ) {
            return;
        }
        
        // Admin CSS
        wp_enqueue_style(
            'vss-product-admin',
            VSS_PLUGIN_URL . 'assets/css/vss-product-admin.css',
            [],
            VSS_VERSION
        );
        
        // Admin JS
        wp_enqueue_script(
            'vss-product-admin',
            VSS_PLUGIN_URL . 'assets/js/vss-product-admin.js',
            [ 'jquery', 'wp-media-utils' ],
            VSS_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script( 'vss-product-admin', 'vss_product_admin', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'vss_product_admin' ),
            'i18n' => [
                'confirm_approve' => __( 'Are you sure you want to approve this product?', 'vss' ),
                'confirm_reject' => __( 'Are you sure you want to reject this product?', 'vss' ),
                'confirm_bulk_action' => __( 'Apply this action to selected products?', 'vss' ),
                'loading' => __( 'Loading...', 'vss' ),
                'error' => __( 'An error occurred. Please try again.', 'vss' ),
                'success' => __( 'Action completed successfully.', 'vss' ),
            ]
        ] );
        
        // Chart.js for analytics
        if ( $hook === 'product-management_page_vss-product-analytics' ) {
            wp_enqueue_script( 'chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', [], '3.9.1', true );
        }
    }

    /**
     * Add dashboard widgets
     */
    public static function add_dashboard_widgets() {
        wp_add_dashboard_widget(
            'vss_product_overview',
            __( 'Product Submissions Overview', 'vss' ),
            [ self::class, 'render_dashboard_widget' ]
        );
    }

    /**
     * Render dashboard widget
     */
    public static function render_dashboard_widget() {
        global $wpdb;
        
        $stats = self::get_product_statistics();
        ?>
        <div class="vss-dashboard-widget">
            <div class="vss-stats-grid">
                <div class="vss-stat-item">
                    <div class="stat-number"><?php echo $stats['pending']; ?></div>
                    <div class="stat-label"><?php esc_html_e( 'Pending Review', 'vss' ); ?></div>
                </div>
                <div class="vss-stat-item">
                    <div class="stat-number"><?php echo $stats['approved_today']; ?></div>
                    <div class="stat-label"><?php esc_html_e( 'Approved Today', 'vss' ); ?></div>
                </div>
                <div class="vss-stat-item">
                    <div class="stat-number"><?php echo $stats['total_this_week']; ?></div>
                    <div class="stat-label"><?php esc_html_e( 'This Week', 'vss' ); ?></div>
                </div>
            </div>
            
            <?php if ( $stats['pending'] > 0 ) : ?>
                <div class="vss-widget-actions">
                    <a href="<?php echo admin_url( 'admin.php?page=vss-product-management&status=pending' ); ?>" 
                       class="button button-primary">
                        <?php esc_html_e( 'Review Pending Products', 'vss' ); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render main admin page
     */
    public static function render_admin_page() {
        $status = isset( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : 'all';
        $vendor_id = isset( $_GET['vendor'] ) ? intval( $_GET['vendor'] ) : 0;
        $search = isset( $_GET['search'] ) ? sanitize_text_field( $_GET['search'] ) : '';
        
        // Get products with filters
        $products = self::get_products( $status, $vendor_id, $search );
        $vendors = self::get_vendors_with_products();
        $stats = self::get_product_statistics();
        ?>
        <div class="wrap vss-product-admin">
            <h1 class="wp-heading-inline">
                <?php esc_html_e( 'Product Management', 'vss' ); ?>
                <span class="title-count">(<?php echo count( $products ); ?>)</span>
            </h1>
            
            <!-- Statistics Cards -->
            <div class="vss-admin-stats">
                <div class="vss-stat-card pending">
                    <div class="stat-icon">📋</div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['pending']; ?></div>
                        <div class="stat-label"><?php esc_html_e( 'Pending Review', 'vss' ); ?></div>
                    </div>
                </div>
                <div class="vss-stat-card approved">
                    <div class="stat-icon">✅</div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['approved']; ?></div>
                        <div class="stat-label"><?php esc_html_e( 'Approved', 'vss' ); ?></div>
                    </div>
                </div>
                <div class="vss-stat-card rejected">
                    <div class="stat-icon">❌</div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['rejected']; ?></div>
                        <div class="stat-label"><?php esc_html_e( 'Rejected', 'vss' ); ?></div>
                    </div>
                </div>
                <div class="vss-stat-card total">
                    <div class="stat-icon">📊</div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['total']; ?></div>
                        <div class="stat-label"><?php esc_html_e( 'Total Products', 'vss' ); ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="vss-admin-filters">
                <div class="vss-filters-left">
                    <div class="vss-status-filters">
                        <a href="<?php echo admin_url( 'admin.php?page=vss-product-management&status=all' ); ?>" 
                           class="<?php echo $status === 'all' ? 'active' : ''; ?>">
                            <?php esc_html_e( 'All', 'vss' ); ?> (<?php echo $stats['total']; ?>)
                        </a>
                        <a href="<?php echo admin_url( 'admin.php?page=vss-product-management&status=pending' ); ?>" 
                           class="<?php echo $status === 'pending' ? 'active' : ''; ?>">
                            <?php esc_html_e( 'Pending', 'vss' ); ?> (<?php echo $stats['pending']; ?>)
                        </a>
                        <a href="<?php echo admin_url( 'admin.php?page=vss-product-management&status=approved' ); ?>" 
                           class="<?php echo $status === 'approved' ? 'active' : ''; ?>">
                            <?php esc_html_e( 'Approved', 'vss' ); ?> (<?php echo $stats['approved']; ?>)
                        </a>
                        <a href="<?php echo admin_url( 'admin.php?page=vss-product-management&status=rejected' ); ?>" 
                           class="<?php echo $status === 'rejected' ? 'active' : ''; ?>">
                            <?php esc_html_e( 'Rejected', 'vss' ); ?> (<?php echo $stats['rejected']; ?>)
                        </a>
                    </div>
                </div>
                
                <div class="vss-filters-right">
                    <form method="get" class="vss-search-form">
                        <input type="hidden" name="page" value="vss-product-management">
                        <input type="hidden" name="status" value="<?php echo esc_attr( $status ); ?>">
                        
                        <select name="vendor" class="vss-vendor-filter">
                            <option value="0"><?php esc_html_e( 'All Vendors', 'vss' ); ?></option>
                            <?php foreach ( $vendors as $vendor ) : ?>
                                <option value="<?php echo $vendor->ID; ?>" <?php selected( $vendor_id, $vendor->ID ); ?>>
                                    <?php echo esc_html( $vendor->display_name ); ?> (<?php echo $vendor->product_count; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <input type="text" name="search" value="<?php echo esc_attr( $search ); ?>" 
                               placeholder="<?php esc_attr_e( 'Search products...', 'vss' ); ?>" class="vss-search-input">
                        
                        <button type="submit" class="button">
                            <span class="dashicons dashicons-search"></span>
                            <?php esc_html_e( 'Search', 'vss' ); ?>
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Bulk Actions -->
            <div class="vss-bulk-actions-bar">
                <div class="vss-bulk-actions">
                    <select id="bulk-action-selector">
                        <option value=""><?php esc_html_e( 'Bulk Actions', 'vss' ); ?></option>
                        <option value="approve"><?php esc_html_e( 'Approve Selected', 'vss' ); ?></option>
                        <option value="reject"><?php esc_html_e( 'Reject Selected', 'vss' ); ?></option>
                        <option value="delete"><?php esc_html_e( 'Delete Selected', 'vss' ); ?></option>
                    </select>
                    <button type="button" class="button" id="apply-bulk-action">
                        <?php esc_html_e( 'Apply', 'vss' ); ?>
                    </button>
                </div>
                
                <div class="vss-view-options">
                    <button type="button" class="button vss-view-toggle active" data-view="cards">
                        <span class="dashicons dashicons-grid-view"></span>
                        <?php esc_html_e( 'Cards', 'vss' ); ?>
                    </button>
                    <button type="button" class="button vss-view-toggle" data-view="table">
                        <span class="dashicons dashicons-list-view"></span>
                        <?php esc_html_e( 'Table', 'vss' ); ?>
                    </button>
                </div>
            </div>
            
            <!-- Products Display -->
            <div class="vss-products-container">
                <?php if ( ! empty( $products ) ) : ?>
                    <!-- Card View -->
                    <div class="vss-products-grid vss-view-cards active">
                        <?php foreach ( $products as $product ) : ?>
                            <?php self::render_product_card( $product ); ?>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Table View -->
                    <div class="vss-products-table vss-view-table">
                        <?php self::render_products_table( $products ); ?>
                    </div>
                <?php else : ?>
                    <div class="vss-empty-state">
                        <div class="vss-empty-icon">📦</div>
                        <h3><?php esc_html_e( 'No products found', 'vss' ); ?></h3>
                        <p><?php esc_html_e( 'No products match your current filters.', 'vss' ); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Product Details Modal -->
        <div id="vss-product-modal" class="vss-modal" style="display: none;">
            <div class="vss-modal-content">
                <div class="vss-modal-header">
                    <h2><?php esc_html_e( 'Product Details', 'vss' ); ?></h2>
                    <button type="button" class="vss-modal-close">&times;</button>
                </div>
                <div class="vss-modal-body" id="vss-product-modal-content">
                    <!-- Product details loaded via AJAX -->
                </div>
            </div>
        </div>
        
        <!-- Cost Management Modal -->
        <div id="vss-cost-modal" class="vss-modal" style="display: none;">
            <div class="vss-modal-content">
                <div class="vss-modal-header">
                    <h2><?php esc_html_e( 'Cost Management', 'vss' ); ?></h2>
                    <button type="button" class="vss-modal-close">&times;</button>
                </div>
                <div class="vss-modal-body">
                    <form id="vss-cost-form">
                        <input type="hidden" id="cost-product-id" name="product_id">
                        
                        <div class="vss-form-row">
                            <div class="vss-form-col-6">
                                <label for="our-cost"><?php esc_html_e( 'Our Cost (USD)', 'vss' ); ?></label>
                                <input type="number" id="our-cost" name="our_cost" step="0.01" min="0" required>
                                <small><?php esc_html_e( 'Your actual cost for this product', 'vss' ); ?></small>
                            </div>
                            <div class="vss-form-col-6">
                                <label for="markup-percentage"><?php esc_html_e( 'Markup %', 'vss' ); ?></label>
                                <input type="number" id="markup-percentage" name="markup_percentage" step="0.1" min="0">
                                <small><?php esc_html_e( 'Markup percentage for profit', 'vss' ); ?></small>
                            </div>
                        </div>
                        
                        <div class="vss-form-row">
                            <div class="vss-form-col-12">
                                <label for="selling-price"><?php esc_html_e( 'Final Selling Price (USD)', 'vss' ); ?></label>
                                <input type="number" id="selling-price" name="selling_price" step="0.01" min="0" readonly>
                                <small><?php esc_html_e( 'Calculated automatically based on cost and markup', 'vss' ); ?></small>
                            </div>
                        </div>
                        
                        <div class="vss-form-row">
                            <div class="vss-form-col-12">
                                <label for="admin-notes"><?php esc_html_e( 'Internal Notes', 'vss' ); ?></label>
                                <textarea id="admin-notes" name="admin_notes" rows="3" 
                                          placeholder="<?php esc_attr_e( 'Internal notes about pricing, sourcing, etc.', 'vss' ); ?>"></textarea>
                            </div>
                        </div>
                        
                        <div class="vss-modal-footer">
                            <button type="button" class="button" id="cancel-cost-update">
                                <?php esc_html_e( 'Cancel', 'vss' ); ?>
                            </button>
                            <button type="submit" class="button button-primary">
                                <?php esc_html_e( 'Update Costs', 'vss' ); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Feedback Modal -->
        <div id="vss-feedback-modal" class="vss-modal" style="display: none;">
            <div class="vss-modal-content">
                <div class="vss-modal-header">
                    <h2><?php esc_html_e( 'Send Feedback to Vendor', 'vss' ); ?></h2>
                    <button type="button" class="vss-modal-close">&times;</button>
                </div>
                <div class="vss-modal-body">
                    <form id="vss-feedback-form">
                        <input type="hidden" id="feedback-product-id" name="product_id">
                        
                        <div class="vss-form-row">
                            <div class="vss-form-col-12">
                                <label for="feedback-message"><?php esc_html_e( 'Feedback Message', 'vss' ); ?></label>
                                <textarea id="feedback-message" name="feedback_message" rows="6" required
                                          placeholder="<?php esc_attr_e( 'Provide detailed feedback to help the vendor improve their product submission...', 'vss' ); ?>"></textarea>
                            </div>
                        </div>
                        
                        <div class="vss-form-row">
                            <div class="vss-form-col-6">
                                <label>
                                    <input type="checkbox" name="request_changes" value="1">
                                    <?php esc_html_e( 'Request changes before approval', 'vss' ); ?>
                                </label>
                            </div>
                            <div class="vss-form-col-6">
                                <label>
                                    <input type="checkbox" name="notify_vendor" value="1" checked>
                                    <?php esc_html_e( 'Send email notification to vendor', 'vss' ); ?>
                                </label>
                            </div>
                        </div>
                        
                        <div class="vss-modal-footer">
                            <button type="button" class="button" id="cancel-feedback">
                                <?php esc_html_e( 'Cancel', 'vss' ); ?>
                            </button>
                            <button type="submit" class="button button-primary">
                                <?php esc_html_e( 'Send Feedback', 'vss' ); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render product card
     */
    private static function render_product_card( $product ) {
        $vendor = get_user_by( 'id', $product->vendor_id );
        $primary_image = self::get_primary_product_image( $product->id );
        $pricing = self::get_product_pricing( $product->id );
        ?>
        <div class="vss-product-card" data-product-id="<?php echo $product->id; ?>">
            <div class="vss-product-card-header">
                <div class="vss-card-checkbox">
                    <input type="checkbox" name="product[]" value="<?php echo $product->id; ?>">
                </div>
                <div class="vss-card-status status-<?php echo esc_attr( $product->status ); ?>">
                    <?php echo esc_html( ucfirst( $product->status ) ); ?>
                </div>
            </div>
            
            <div class="vss-product-image">
                <?php if ( $primary_image ) : ?>
                    <img src="<?php echo esc_url( $primary_image ); ?>" alt="<?php echo esc_attr( $product->product_name_en ); ?>">
                <?php else : ?>
                    <div class="vss-no-image">
                        <span class="dashicons dashicons-format-image"></span>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="vss-product-content">
                <h3 class="vss-product-title">
                    <?php echo esc_html( $product->product_name_zh ); ?>
                    <?php if ( $product->product_name_en ) : ?>
                        <br><small><?php echo esc_html( $product->product_name_en ); ?></small>
                    <?php endif; ?>
                </h3>
                
                <div class="vss-product-meta">
                    <div class="vss-meta-item">
                        <strong><?php esc_html_e( 'Vendor:', 'vss' ); ?></strong>
                        <?php echo $vendor ? esc_html( $vendor->display_name ) : __( 'Unknown', 'vss' ); ?>
                    </div>
                    
                    <?php if ( $product->category ) : ?>
                    <div class="vss-meta-item">
                        <strong><?php esc_html_e( 'Category:', 'vss' ); ?></strong>
                        <?php echo esc_html( ucfirst( $product->category ) ); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ( $product->sku ) : ?>
                    <div class="vss-meta-item">
                        <strong><?php esc_html_e( 'SKU:', 'vss' ); ?></strong>
                        <code><?php echo esc_html( $product->sku ); ?></code>
                    </div>
                    <?php endif; ?>
                    
                    <div class="vss-meta-item">
                        <strong><?php esc_html_e( 'Production Time:', 'vss' ); ?></strong>
                        <?php echo $product->production_time; ?> <?php esc_html_e( 'days', 'vss' ); ?>
                    </div>
                    
                    <?php if ( ! empty( $pricing ) ) : ?>
                    <div class="vss-meta-item">
                        <strong><?php esc_html_e( 'Price Range:', 'vss' ); ?></strong>
                        $<?php echo number_format( min( array_column( $pricing, 'unit_price' ) ), 2 ); ?> - 
                        $<?php echo number_format( max( array_column( $pricing, 'unit_price' ) ), 2 ); ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="vss-meta-item">
                        <strong><?php esc_html_e( 'Submitted:', 'vss' ); ?></strong>
                        <?php echo date_i18n( 'M j, Y', strtotime( $product->submission_date ) ); ?>
                    </div>
                </div>
                
                <?php if ( $product->our_cost || $product->selling_price ) : ?>
                <div class="vss-pricing-info">
                    <?php if ( $product->our_cost ) : ?>
                        <div class="vss-cost-item">
                            <span class="vss-cost-label"><?php esc_html_e( 'Our Cost:', 'vss' ); ?></span>
                            <span class="vss-cost-value">$<?php echo number_format( $product->our_cost, 2 ); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ( $product->selling_price ) : ?>
                        <div class="vss-cost-item">
                            <span class="vss-cost-label"><?php esc_html_e( 'Selling Price:', 'vss' ); ?></span>
                            <span class="vss-cost-value">$<?php echo number_format( $product->selling_price, 2 ); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ( $product->our_cost && $product->selling_price ) : ?>
                        <div class="vss-cost-item profit">
                            <span class="vss-cost-label"><?php esc_html_e( 'Profit:', 'vss' ); ?></span>
                            <span class="vss-cost-value">$<?php echo number_format( $product->selling_price - $product->our_cost, 2 ); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="vss-product-actions">
                <button type="button" class="button vss-view-details" data-product-id="<?php echo $product->id; ?>">
                    <span class="dashicons dashicons-visibility"></span>
                    <?php esc_html_e( 'View Details', 'vss' ); ?>
                </button>
                
                <button type="button" class="button vss-manage-costs" data-product-id="<?php echo $product->id; ?>">
                    <span class="dashicons dashicons-money-alt"></span>
                    <?php esc_html_e( 'Manage Costs', 'vss' ); ?>
                </button>
                
                <?php if ( $product->status === 'pending' ) : ?>
                    <div class="vss-approval-actions">
                        <button type="button" class="button button-primary vss-approve-product" data-product-id="<?php echo $product->id; ?>">
                            <span class="dashicons dashicons-yes"></span>
                            <?php esc_html_e( 'Approve', 'vss' ); ?>
                        </button>
                        <button type="button" class="button vss-reject-product" data-product-id="<?php echo $product->id; ?>">
                            <span class="dashicons dashicons-no"></span>
                            <?php esc_html_e( 'Reject', 'vss' ); ?>
                        </button>
                    </div>
                <?php endif; ?>
                
                <button type="button" class="button vss-send-feedback" data-product-id="<?php echo $product->id; ?>">
                    <span class="dashicons dashicons-email-alt"></span>
                    <?php esc_html_e( 'Send Feedback', 'vss' ); ?>
                </button>
            </div>
        </div>
        <?php
    }

    /**
     * Get products with filters
     */
    private static function get_products( $status = 'all', $vendor_id = 0, $search = '' ) {
        global $wpdb;
        
        $where_clauses = [ '1=1' ];
        $params = [];
        
        if ( $status !== 'all' ) {
            $where_clauses[] = 'status = %s';
            $params[] = $status;
        }
        
        if ( $vendor_id > 0 ) {
            $where_clauses[] = 'vendor_id = %d';
            $params[] = $vendor_id;
        }
        
        if ( ! empty( $search ) ) {
            $where_clauses[] = '(product_name_zh LIKE %s OR product_name_en LIKE %s OR sku LIKE %s)';
            $params[] = '%' . $wpdb->esc_like( $search ) . '%';
            $params[] = '%' . $wpdb->esc_like( $search ) . '%';
            $params[] = '%' . $wpdb->esc_like( $search ) . '%';
        }
        
        $where_sql = implode( ' AND ', $where_clauses );
        
        $query = "SELECT * FROM {$wpdb->prefix}vss_product_uploads WHERE {$where_sql} ORDER BY submission_date DESC";
        
        if ( ! empty( $params ) ) {
            $query = $wpdb->prepare( $query, $params );
        }
        
        return $wpdb->get_results( $query );
    }

    /**
     * Get vendors with product counts
     */
    private static function get_vendors_with_products() {
        global $wpdb;
        
        $query = "
            SELECT u.ID, u.display_name, COUNT(p.id) as product_count
            FROM {$wpdb->users} u
            INNER JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
            LEFT JOIN {$wpdb->prefix}vss_product_uploads p ON u.ID = p.vendor_id
            WHERE um.meta_key = '{$wpdb->prefix}capabilities'
            AND um.meta_value LIKE '%vendor-mm%'
            GROUP BY u.ID, u.display_name
            HAVING product_count > 0
            ORDER BY u.display_name
        ";
        
        return $wpdb->get_results( $query );
    }

    /**
     * Get product statistics
     */
    private static function get_product_statistics() {
        global $wpdb;
        
        $table = $wpdb->prefix . 'vss_product_uploads';
        
        $stats = [
            'total' => $wpdb->get_var( "SELECT COUNT(*) FROM $table" ),
            'pending' => $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE status = 'pending'" ),
            'approved' => $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE status = 'approved'" ),
            'rejected' => $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE status = 'rejected'" ),
            'draft' => $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE status = 'draft'" ),
        ];
        
        // Today's approved
        $today = date( 'Y-m-d' );
        $stats['approved_today'] = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE status = 'approved' AND DATE(approval_date) = %s",
            $today
        ) );
        
        // This week's submissions
        $week_start = date( 'Y-m-d', strtotime( 'monday this week' ) );
        $stats['total_this_week'] = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE DATE(submission_date) >= %s",
            $week_start
        ) );
        
        return $stats;
    }

    /**
     * Get primary product image
     */
    private static function get_primary_product_image( $product_id ) {
        global $wpdb;
        
        $image_url = $wpdb->get_var( $wpdb->prepare(
            "SELECT image_url FROM {$wpdb->prefix}vss_product_images 
             WHERE product_id = %d AND is_primary = 1 LIMIT 1",
            $product_id
        ) );
        
        if ( ! $image_url ) {
            $image_url = $wpdb->get_var( $wpdb->prepare(
                "SELECT image_url FROM {$wpdb->prefix}vss_product_images 
                 WHERE product_id = %d ORDER BY sort_order ASC LIMIT 1",
                $product_id
            ) );
        }
        
        return $image_url;
    }

    /**
     * Get product pricing
     */
    private static function get_product_pricing( $product_id ) {
        global $wpdb;
        
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}vss_product_pricing 
             WHERE product_id = %d ORDER BY min_quantity ASC",
            $product_id
        ) );
    }

    /**
     * Render products table
     */
    private static function render_products_table( $products ) {
        ?>
        <div class="vss-products-table-container">
            <table class="vss-products-table">
                <thead>
                    <tr>
                        <th width="40">
                            <input type="checkbox" id="cb-select-all-table">
                        </th>
                        <th width="80"><?php esc_html_e( 'Image', 'vss' ); ?></th>
                        <th><?php esc_html_e( 'Product Name', 'vss' ); ?></th>
                        <th><?php esc_html_e( 'Vendor', 'vss' ); ?></th>
                        <th><?php esc_html_e( 'Category', 'vss' ); ?></th>
                        <th><?php esc_html_e( 'Status', 'vss' ); ?></th>
                        <th><?php esc_html_e( 'Submitted', 'vss' ); ?></th>
                        <th><?php esc_html_e( 'Actions', 'vss' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $products as $product ) : 
                        $vendor = get_user_by( 'id', $product->vendor_id );
                        $primary_image = self::get_primary_product_image( $product->id );
                    ?>
                        <tr data-product-id="<?php echo $product->id; ?>">
                            <td>
                                <input type="checkbox" name="product[]" value="<?php echo $product->id; ?>">
                            </td>
                            <td>
                                <?php if ( $primary_image ) : ?>
                                    <img src="<?php echo esc_url( $primary_image ); ?>" alt="" class="vss-table-product-image">
                                <?php else : ?>
                                    <div class="vss-table-no-image">
                                        <span class="dashicons dashicons-format-image"></span>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo esc_html( $product->product_name_zh ); ?></strong>
                                <?php if ( $product->product_name_en ) : ?>
                                    <br><small><?php echo esc_html( $product->product_name_en ); ?></small>
                                <?php endif; ?>
                                <?php if ( $product->sku ) : ?>
                                    <br><code><?php echo esc_html( $product->sku ); ?></code>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $vendor ? esc_html( $vendor->display_name ) : __( 'Unknown', 'vss' ); ?></td>
                            <td><?php echo $product->category ? esc_html( ucfirst( $product->category ) ) : '—'; ?></td>
                            <td>
                                <span class="vss-card-status status-<?php echo esc_attr( $product->status ); ?>">
                                    <?php echo esc_html( ucfirst( $product->status ) ); ?>
                                </span>
                            </td>
                            <td><?php echo date_i18n( 'M j, Y', strtotime( $product->submission_date ) ); ?></td>
                            <td>
                                <div class="vss-table-actions">
                                    <button type="button" class="button button-small vss-view-details" 
                                            data-product-id="<?php echo $product->id; ?>" title="<?php esc_attr_e( 'View Details', 'vss' ); ?>">
                                        <span class="dashicons dashicons-visibility"></span>
                                    </button>
                                    
                                    <button type="button" class="button button-small vss-manage-costs" 
                                            data-product-id="<?php echo $product->id; ?>" title="<?php esc_attr_e( 'Manage Costs', 'vss' ); ?>">
                                        <span class="dashicons dashicons-money-alt"></span>
                                    </button>
                                    
                                    <?php if ( $product->status === 'pending' ) : ?>
                                        <button type="button" class="button button-small button-primary vss-approve-product" 
                                                data-product-id="<?php echo $product->id; ?>" title="<?php esc_attr_e( 'Approve', 'vss' ); ?>">
                                            <span class="dashicons dashicons-yes"></span>
                                        </button>
                                        <button type="button" class="button button-small vss-reject-product" 
                                                data-product-id="<?php echo $product->id; ?>" title="<?php esc_attr_e( 'Reject', 'vss' ); ?>">
                                            <span class="dashicons dashicons-no"></span>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * AJAX: Get product details
     */
    public static function ajax_get_product_details() {
        check_ajax_referer( 'vss_product_admin', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Insufficient permissions', 'vss' ) );
        }
        
        $product_id = intval( $_POST['product_id'] ?? 0 );
        if ( ! $product_id ) {
            wp_send_json_error( __( 'Invalid product ID', 'vss' ) );
        }
        
        global $wpdb;
        $product = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}vss_product_uploads WHERE id = %d",
            $product_id
        ) );
        
        if ( ! $product ) {
            wp_send_json_error( __( 'Product not found', 'vss' ) );
        }
        
        // Get additional data
        $vendor = get_user_by( 'id', $product->vendor_id );
        $images = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}vss_product_images WHERE product_id = %d ORDER BY sort_order",
            $product_id
        ) );
        $variants = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}vss_product_variants WHERE product_id = %d ORDER BY id",
            $product_id
        ) );
        $pricing = self::get_product_pricing( $product_id );
        $history = $wpdb->get_results( $wpdb->prepare(
            "SELECT h.*, u.display_name as user_name 
             FROM {$wpdb->prefix}vss_product_approval_history h
             LEFT JOIN {$wpdb->users} u ON h.user_id = u.ID
             WHERE h.product_id = %d ORDER BY h.created_at DESC",
            $product_id
        ) );
        
        ob_start();
        self::render_product_details_modal( $product, $vendor, $images, $variants, $pricing, $history );
        $html = ob_get_clean();
        
        wp_send_json_success( [ 'html' => $html ] );
    }
    
    /**
     * Render product details modal content
     */
    private static function render_product_details_modal( $product, $vendor, $images, $variants, $pricing, $history ) {
        $product_images = array_filter( $images, function( $img ) { return $img->image_type === 'product'; } );
        $finished_images = array_filter( $images, function( $img ) { return $img->image_type === 'finished'; } );
        ?>
        <div class="vss-product-details-grid">
            <div class="vss-product-images-section">
                <?php if ( ! empty( $product_images ) ) : ?>
                    <img src="<?php echo esc_url( $product_images[0]->image_url ); ?>" alt="" class="vss-main-product-image">
                    
                    <?php if ( count( $product_images ) > 1 ) : ?>
                        <div class="vss-product-images">
                            <?php foreach ( $product_images as $image ) : ?>
                                <img src="<?php echo esc_url( $image->image_url ); ?>" alt="">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php else : ?>
                    <div class="vss-no-image-placeholder">
                        <span class="dashicons dashicons-format-image"></span>
                        <p><?php esc_html_e( 'No product images', 'vss' ); ?></p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="vss-product-info">
                <h3><?php echo esc_html( $product->product_name_zh ); ?></h3>
                <?php if ( $product->product_name_en ) : ?>
                    <h4 style="color: #6b7280; margin: -10px 0 20px 0;"><?php echo esc_html( $product->product_name_en ); ?></h4>
                <?php endif; ?>
                
                <div class="vss-info-section">
                    <h4><?php esc_html_e( 'Basic Information', 'vss' ); ?></h4>
                    <div class="vss-info-grid">
                        <div class="vss-info-item">
                            <div class="vss-info-label"><?php esc_html_e( 'Vendor', 'vss' ); ?></div>
                            <div class="vss-info-value"><?php echo $vendor ? esc_html( $vendor->display_name ) : __( 'Unknown', 'vss' ); ?></div>
                        </div>
                        
                        <?php if ( $product->category ) : ?>
                        <div class="vss-info-item">
                            <div class="vss-info-label"><?php esc_html_e( 'Category', 'vss' ); ?></div>
                            <div class="vss-info-value"><?php echo esc_html( ucfirst( $product->category ) ); ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ( $product->sku ) : ?>
                        <div class="vss-info-item">
                            <div class="vss-info-label"><?php esc_html_e( 'SKU', 'vss' ); ?></div>
                            <div class="vss-info-value"><code><?php echo esc_html( $product->sku ); ?></code></div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="vss-info-item">
                            <div class="vss-info-label"><?php esc_html_e( 'Status', 'vss' ); ?></div>
                            <div class="vss-info-value">
                                <span class="vss-card-status status-<?php echo esc_attr( $product->status ); ?>">
                                    <?php echo esc_html( ucfirst( $product->status ) ); ?>
                                </span>
                            </div>
                        </div>
                        
                        <?php if ( $product->product_weight ) : ?>
                        <div class="vss-info-item">
                            <div class="vss-info-label"><?php esc_html_e( 'Weight', 'vss' ); ?></div>
                            <div class="vss-info-value"><?php echo $product->product_weight; ?> kg</div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="vss-info-item">
                            <div class="vss-info-label"><?php esc_html_e( 'Production Time', 'vss' ); ?></div>
                            <div class="vss-info-value"><?php echo $product->production_time; ?> <?php esc_html_e( 'days', 'vss' ); ?></div>
                        </div>
                        
                        <div class="vss-info-item">
                            <div class="vss-info-label"><?php esc_html_e( 'Submitted', 'vss' ); ?></div>
                            <div class="vss-info-value"><?php echo date_i18n( 'M j, Y \a\t H:i', strtotime( $product->submission_date ) ); ?></div>
                        </div>
                        
                        <?php if ( $product->approval_date ) : ?>
                        <div class="vss-info-item">
                            <div class="vss-info-label"><?php esc_html_e( 'Approved/Rejected', 'vss' ); ?></div>
                            <div class="vss-info-value"><?php echo date_i18n( 'M j, Y \a\t H:i', strtotime( $product->approval_date ) ); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if ( $product->description_zh ) : ?>
                <div class="vss-info-section">
                    <h4><?php esc_html_e( 'Description (Chinese)', 'vss' ); ?></h4>
                    <p><?php echo esc_html( $product->description_zh ); ?></p>
                </div>
                <?php endif; ?>
                
                <?php if ( $product->description_en ) : ?>
                <div class="vss-info-section">
                    <h4><?php esc_html_e( 'Description (English)', 'vss' ); ?></h4>
                    <p><?php echo esc_html( $product->description_en ); ?></p>
                </div>
                <?php endif; ?>
                
                <?php if ( $product->customization_options ) : ?>
                <div class="vss-info-section">
                    <h4><?php esc_html_e( 'Customization Options', 'vss' ); ?></h4>
                    <p><?php echo esc_html( $product->customization_options ); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ( ! empty( $pricing ) ) : ?>
        <div class="vss-info-section">
            <h4><?php esc_html_e( 'Pricing Tiers', 'vss' ); ?></h4>
            <div class="vss-pricing-tiers">
                <table>
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Min Quantity', 'vss' ); ?></th>
                            <th><?php esc_html_e( 'Max Quantity', 'vss' ); ?></th>
                            <th><?php esc_html_e( 'Unit Price', 'vss' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $pricing as $tier ) : ?>
                            <tr>
                                <td><?php echo number_format( $tier->min_quantity ); ?></td>
                                <td><?php echo $tier->max_quantity ? number_format( $tier->max_quantity ) : '∞'; ?></td>
                                <td>$<?php echo number_format( $tier->unit_price, 2 ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ( ! empty( $variants ) ) : ?>
        <div class="vss-info-section">
            <h4><?php esc_html_e( 'Product Variants', 'vss' ); ?></h4>
            <div class="vss-variants-list">
                <?php foreach ( $variants as $variant ) : ?>
                    <div class="vss-variant-item">
                        <strong><?php echo esc_html( $variant->variant_name_zh ); ?></strong>
                        <?php if ( $variant->variant_name_en ) : ?>
                            / <?php echo esc_html( $variant->variant_name_en ); ?>
                        <?php endif; ?>
                        : <?php echo esc_html( $variant->variant_value_zh ); ?>
                        <?php if ( $variant->price_adjustment != 0 ) : ?>
                            <span class="price-adjustment">
                                (<?php echo $variant->price_adjustment > 0 ? '+' : ''; ?>$<?php echo number_format( $variant->price_adjustment, 2 ); ?>)
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ( ! empty( $finished_images ) ) : ?>
        <div class="vss-info-section">
            <h4><?php esc_html_e( 'Finished Product Examples', 'vss' ); ?></h4>
            <div class="vss-finished-images">
                <?php foreach ( $finished_images as $image ) : ?>
                    <img src="<?php echo esc_url( $image->image_url ); ?>" alt="" style="width: 150px; height: 150px; object-fit: cover; border-radius: 8px; margin-right: 10px;">
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ( $product->our_cost || $product->selling_price || $product->admin_notes ) : ?>
        <div class="vss-info-section">
            <h4><?php esc_html_e( 'Internal Information', 'vss' ); ?></h4>
            <div class="vss-info-grid">
                <?php if ( $product->our_cost ) : ?>
                <div class="vss-info-item">
                    <div class="vss-info-label"><?php esc_html_e( 'Our Cost', 'vss' ); ?></div>
                    <div class="vss-info-value">$<?php echo number_format( $product->our_cost, 2 ); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ( $product->markup_percentage ) : ?>
                <div class="vss-info-item">
                    <div class="vss-info-label"><?php esc_html_e( 'Markup', 'vss' ); ?></div>
                    <div class="vss-info-value"><?php echo number_format( $product->markup_percentage, 1 ); ?>%</div>
                </div>
                <?php endif; ?>
                
                <?php if ( $product->selling_price ) : ?>
                <div class="vss-info-item">
                    <div class="vss-info-label"><?php esc_html_e( 'Selling Price', 'vss' ); ?></div>
                    <div class="vss-info-value">$<?php echo number_format( $product->selling_price, 2 ); ?></div>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if ( $product->admin_notes ) : ?>
            <p><strong><?php esc_html_e( 'Admin Notes:', 'vss' ); ?></strong><br><?php echo esc_html( $product->admin_notes ); ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php if ( ! empty( $history ) ) : ?>
        <div class="vss-info-section">
            <h4><?php esc_html_e( 'Activity History', 'vss' ); ?></h4>
            <div class="vss-history-timeline">
                <?php foreach ( $history as $entry ) : ?>
                    <div class="vss-history-item">
                        <div class="vss-history-date"><?php echo date_i18n( 'M j, Y H:i', strtotime( $entry->created_at ) ); ?></div>
                        <div class="vss-history-action">
                            <strong><?php echo esc_html( ucfirst( $entry->action ) ); ?></strong>
                            <?php if ( $entry->user_name ) : ?>
                                by <?php echo esc_html( $entry->user_name ); ?>
                            <?php endif; ?>
                        </div>
                        <?php if ( $entry->notes ) : ?>
                            <div class="vss-history-notes"><?php echo esc_html( $entry->notes ); ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        <?php
    }
    
    /**
     * AJAX: Update product costs
     */
    public static function ajax_update_product_costs() {
        check_ajax_referer( 'vss_product_admin', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Insufficient permissions', 'vss' ) );
        }
        
        $product_id = intval( $_POST['product_id'] ?? 0 );
        $our_cost = floatval( $_POST['our_cost'] ?? 0 );
        $markup_percentage = floatval( $_POST['markup_percentage'] ?? 0 );
        $selling_price = floatval( $_POST['selling_price'] ?? 0 );
        $admin_notes = sanitize_textarea_field( $_POST['admin_notes'] ?? '' );
        
        if ( ! $product_id ) {
            wp_send_json_error( __( 'Invalid product ID', 'vss' ) );
        }
        
        global $wpdb;
        $result = $wpdb->update(
            $wpdb->prefix . 'vss_product_uploads',
            [
                'our_cost' => $our_cost,
                'markup_percentage' => $markup_percentage,
                'selling_price' => $selling_price,
                'admin_notes' => $admin_notes,
                'updated_at' => current_time( 'mysql' )
            ],
            [ 'id' => $product_id ],
            [ '%f', '%f', '%f', '%s', '%s' ],
            [ '%d' ]
        );
        
        if ( $result === false ) {
            wp_send_json_error( __( 'Database error', 'vss' ) );
        }
        
        // Log the activity
        self::log_product_activity( $product_id, 'cost_updated', get_current_user_id(), 
            sprintf( 'Cost: $%s, Markup: %s%%, Selling: $%s', $our_cost, $markup_percentage, $selling_price ) );
        
        wp_send_json_success( [
            'message' => __( 'Product costs updated successfully', 'vss' )
        ] );
    }
    
    /**
     * AJAX: Approve product
     */
    public static function ajax_approve_product() {
        check_ajax_referer( 'vss_product_admin', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Insufficient permissions', 'vss' ) );
        }
        
        $product_id = intval( $_POST['product_id'] ?? 0 );
        if ( ! $product_id ) {
            wp_send_json_error( __( 'Invalid product ID', 'vss' ) );
        }
        
        global $wpdb;
        $result = $wpdb->update(
            $wpdb->prefix . 'vss_product_uploads',
            [
                'status' => 'approved',
                'approval_date' => current_time( 'mysql' ),
                'approved_by' => get_current_user_id(),
                'updated_at' => current_time( 'mysql' )
            ],
            [ 'id' => $product_id ],
            [ '%s', '%s', '%d', '%s' ],
            [ '%d' ]
        );
        
        if ( $result === false ) {
            wp_send_json_error( __( 'Database error', 'vss' ) );
        }
        
        // Log the activity
        self::log_product_activity( $product_id, 'approved', get_current_user_id() );
        
        // Trigger approval action
        do_action( 'vss_product_approved', $product_id, get_current_user_id() );
        
        wp_send_json_success( [
            'message' => __( 'Product approved successfully', 'vss' )
        ] );
    }
    
    /**
     * AJAX: Reject product
     */
    public static function ajax_reject_product() {
        check_ajax_referer( 'vss_product_admin', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Insufficient permissions', 'vss' ) );
        }
        
        $product_id = intval( $_POST['product_id'] ?? 0 );
        $rejection_reason = sanitize_textarea_field( $_POST['rejection_reason'] ?? '' );
        
        if ( ! $product_id ) {
            wp_send_json_error( __( 'Invalid product ID', 'vss' ) );
        }
        
        global $wpdb;
        $result = $wpdb->update(
            $wpdb->prefix . 'vss_product_uploads',
            [
                'status' => 'rejected',
                'approval_date' => current_time( 'mysql' ),
                'approved_by' => get_current_user_id(),
                'rejection_reason' => $rejection_reason,
                'updated_at' => current_time( 'mysql' )
            ],
            [ 'id' => $product_id ],
            [ '%s', '%s', '%d', '%s', '%s' ],
            [ '%d' ]
        );
        
        if ( $result === false ) {
            wp_send_json_error( __( 'Database error', 'vss' ) );
        }
        
        // Log the activity
        self::log_product_activity( $product_id, 'rejected', get_current_user_id(), $rejection_reason );
        
        // Trigger rejection action
        do_action( 'vss_product_rejected', $product_id, get_current_user_id() );
        
        wp_send_json_success( [
            'message' => __( 'Product rejected', 'vss' )
        ] );
    }
    
    /**
     * Log product activity
     */
    private static function log_product_activity( $product_id, $action, $user_id, $notes = '' ) {
        global $wpdb;
        
        $wpdb->insert(
            $wpdb->prefix . 'vss_product_approval_history',
            [
                'product_id' => $product_id,
                'action' => $action,
                'user_id' => $user_id,
                'notes' => $notes,
                'created_at' => current_time( 'mysql' )
            ],
            [ '%d', '%s', '%d', '%s', '%s' ]
        );
    }
    
    /**
     * Render analytics page
     */
    public static function render_analytics_page() {
        ?>
        <div class="wrap vss-product-analytics">
            <h1><?php esc_html_e( 'Product Analytics', 'vss' ); ?></h1>
            <p><?php esc_html_e( 'Analytics functionality coming soon...', 'vss' ); ?></p>
        </div>
        <?php
    }
    
    /**
     * Render settings page
     */
    public static function render_settings_page() {
        if ( isset( $_POST['submit'] ) ) {
            check_admin_referer( 'vss_product_settings' );
            
            // Save settings
            update_option( 'vss_slack_webhook_url', sanitize_url( $_POST['slack_webhook'] ?? '' ) );
            update_option( 'vss_auto_translate', isset( $_POST['auto_translate'] ) ? 1 : 0 );
            update_option( 'vss_require_approval', isset( $_POST['require_approval'] ) ? 1 : 0 );
            
            echo '<div class="notice notice-success"><p>' . __( 'Settings saved successfully', 'vss' ) . '</p></div>';
        }
        
        $slack_webhook = get_option( 'vss_slack_webhook_url', '' );
        $auto_translate = get_option( 'vss_auto_translate', 1 );
        $require_approval = get_option( 'vss_require_approval', 1 );
        ?>
        <div class="wrap vss-product-settings">
            <h1><?php esc_html_e( 'Product Management Settings', 'vss' ); ?></h1>
            
            <form method="post" action="">
                <?php wp_nonce_field( 'vss_product_settings' ); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="slack_webhook"><?php esc_html_e( 'Slack Webhook URL', 'vss' ); ?></label>
                        </th>
                        <td>
                            <input type="url" id="slack_webhook" name="slack_webhook" value="<?php echo esc_attr( $slack_webhook ); ?>" class="regular-text">
                            <p class="description"><?php esc_html_e( 'Enter your Slack webhook URL to receive notifications for new product submissions', 'vss' ); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Auto-translate', 'vss' ); ?></th>
                        <td>
                            <label for="auto_translate">
                                <input type="checkbox" id="auto_translate" name="auto_translate" value="1" <?php checked( $auto_translate ); ?>>
                                <?php esc_html_e( 'Automatically translate Chinese text to English', 'vss' ); ?>
                            </label>
                            <p class="description"><?php esc_html_e( 'When enabled, Chinese product names and descriptions will be automatically translated', 'vss' ); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Require Approval', 'vss' ); ?></th>
                        <td>
                            <label for="require_approval">
                                <input type="checkbox" id="require_approval" name="require_approval" value="1" <?php checked( $require_approval ); ?>>
                                <?php esc_html_e( 'All products must be approved before being visible', 'vss' ); ?>
                            </label>
                            <p class="description"><?php esc_html_e( 'When disabled, products will be automatically approved upon submission', 'vss' ); ?></p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Render vendor stores assignment page
     */
    public static function render_vendor_stores_page() {
        // Handle form submission
        if ( isset( $_POST['submit_vendor_stores'] ) ) {
            check_admin_referer( 'vss_vendor_stores' );
            
            $vendor_id = intval( $_POST['vendor_id'] ?? 0 );
            $assigned_stores = $_POST['assigned_stores'] ?? [];
            
            if ( $vendor_id ) {
                self::update_vendor_store_assignments( $vendor_id, $assigned_stores );
                echo '<div class="notice notice-success"><p>' . __( 'Vendor store assignments updated successfully', 'vss' ) . '</p></div>';
            }
        }
        
        // Get all vendors
        $vendors = get_users( [ 'role' => 'vendor-mm' ] );
        $selected_vendor = isset( $_GET['vendor'] ) ? intval( $_GET['vendor'] ) : 0;
        ?>
        <div class="wrap vss-vendor-stores">
            <h1><?php esc_html_e( 'Vendor Store Assignments', 'vss' ); ?></h1>
            <p><?php esc_html_e( 'Assign which stores each vendor can upload products to.', 'vss' ); ?></p>
            
            <!-- Vendor Selection -->
            <div class="vss-vendor-selection">
                <form method="get" action="">
                    <input type="hidden" name="page" value="vss-vendor-stores">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="vendor-select"><?php esc_html_e( 'Select Vendor', 'vss' ); ?></label>
                            </th>
                            <td>
                                <select id="vendor-select" name="vendor" onchange="this.form.submit()">
                                    <option value="0"><?php esc_html_e( 'Choose a vendor...', 'vss' ); ?></option>
                                    <?php foreach ( $vendors as $vendor ) : ?>
                                        <option value="<?php echo $vendor->ID; ?>" <?php selected( $selected_vendor, $vendor->ID ); ?>>
                                            <?php echo esc_html( $vendor->display_name . ' (' . $vendor->user_email . ')' ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
            
            <?php if ( $selected_vendor ) : 
                $vendor = get_user_by( 'id', $selected_vendor );
                $current_assignments = self::get_vendor_store_assignments( $selected_vendor );
                $store_configs = VSS_Store_Integration::get_store_config();
            ?>
                <!-- Store Assignment Form -->
                <div class="vss-store-assignments">
                    <h2><?php printf( __( 'Store Access for %s', 'vss' ), $vendor->display_name ); ?></h2>
                    
                    <form method="post" action="">
                        <?php wp_nonce_field( 'vss_vendor_stores' ); ?>
                        <input type="hidden" name="vendor_id" value="<?php echo $selected_vendor; ?>">
                        
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th width="40"><?php esc_html_e( 'Access', 'vss' ); ?></th>
                                    <th><?php esc_html_e( 'Store Name', 'vss' ); ?></th>
                                    <th><?php esc_html_e( 'Platform', 'vss' ); ?></th>
                                    <th><?php esc_html_e( 'Status', 'vss' ); ?></th>
                                    <th><?php esc_html_e( 'Categories', 'vss' ); ?></th>
                                    <th><?php esc_html_e( 'Actions', 'vss' ); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( $store_configs as $store_key => $store_config ) : 
                                    $is_assigned = in_array( $store_key, $current_assignments );
                                    $category_count = self::get_store_category_count( $store_key );
                                ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="assigned_stores[]" value="<?php echo esc_attr( $store_key ); ?>" 
                                                   <?php checked( $is_assigned ); ?>>
                                        </td>
                                        <td>
                                            <strong><?php echo esc_html( $store_config['name'] ); ?></strong>
                                        </td>
                                        <td>
                                            <span class="platform-badge platform-<?php echo esc_attr( $store_config['type'] ); ?>">
                                                <?php echo esc_html( ucfirst( $store_config['type'] ) ); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ( $store_config['enabled'] ) : ?>
                                                <span class="status-enabled">✓ <?php esc_html_e( 'Enabled', 'vss' ); ?></span>
                                            <?php else : ?>
                                                <span class="status-disabled">✗ <?php esc_html_e( 'Disabled', 'vss' ); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo $category_count; ?> <?php esc_html_e( 'categories', 'vss' ); ?>
                                            <small>(<?php esc_html_e( 'Last updated:', 'vss' ); ?> <?php echo self::get_categories_last_updated( $store_key ); ?>)</small>
                                        </td>
                                        <td>
                                            <button type="button" class="button button-small vss-refresh-store-categories" 
                                                    data-store="<?php echo esc_attr( $store_key ); ?>">
                                                <span class="dashicons dashicons-update"></span>
                                                <?php esc_html_e( 'Refresh', 'vss' ); ?>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <?php submit_button( __( 'Update Store Assignments', 'vss' ), 'primary', 'submit_vendor_stores' ); ?>
                    </form>
                </div>
                
                <!-- Store Configuration (if needed) -->
                <div class="vss-store-config">
                    <h3><?php esc_html_e( 'Store Configuration', 'vss' ); ?></h3>
                    <p><?php esc_html_e( 'Configure API credentials and settings for each store integration.', 'vss' ); ?></p>
                    
                    <div class="vss-config-cards">
                        <?php foreach ( $store_configs as $store_key => $store_config ) : ?>
                            <div class="vss-config-card">
                                <h4><?php echo esc_html( $store_config['name'] ); ?></h4>
                                <p><strong><?php esc_html_e( 'Platform:', 'vss' ); ?></strong> <?php echo esc_html( ucfirst( $store_config['type'] ) ); ?></p>
                                <p><strong><?php esc_html_e( 'Status:', 'vss' ); ?></strong> 
                                    <?php echo $store_config['enabled'] ? __( 'Enabled', 'vss' ) : __( 'Disabled', 'vss' ); ?>
                                </p>
                                <a href="<?php echo admin_url( 'admin.php?page=vss-product-settings&store=' . $store_key ); ?>" 
                                   class="button">
                                    <?php esc_html_e( 'Configure', 'vss' ); ?>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <style>
        .vss-config-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .vss-config-card {
            background: white;
            border: 1px solid #ccd0d4;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .platform-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .platform-shopify { background: #95bf47; color: white; }
        .platform-bigcommerce { background: #121118; color: white; }
        .platform-wordpress { background: #21759b; color: white; }
        
        .status-enabled { color: #46b450; font-weight: 600; }
        .status-disabled { color: #dc3232; font-weight: 600; }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            $('.vss-refresh-store-categories').on('click', function() {
                var button = $(this);
                var store = button.data('store');
                
                button.prop('disabled', true).addClass('loading');
                
                $.post(ajaxurl, {
                    action: 'vss_refresh_store_categories',
                    store_key: store,
                    nonce: '<?php echo wp_create_nonce( 'vss_product_admin' ); ?>'
                }, function(response) {
                    if (response.success) {
                        alert('Categories refreshed successfully!');
                        location.reload();
                    } else {
                        alert('Failed to refresh categories: ' + response.data);
                    }
                }).always(function() {
                    button.prop('disabled', false).removeClass('loading');
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Update vendor store assignments
     */
    private static function update_vendor_store_assignments( $vendor_id, $assigned_stores ) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'vss_vendor_stores';
        
        // Remove all existing assignments for this vendor
        $wpdb->delete( $table_name, [ 'vendor_id' => $vendor_id ] );
        
        // Add new assignments
        foreach ( $assigned_stores as $store_key ) {
            $wpdb->insert( $table_name, [
                'vendor_id' => $vendor_id,
                'store_key' => $store_key,
                'is_allowed' => 1,
                'created_at' => current_time( 'mysql' ),
                'updated_at' => current_time( 'mysql' )
            ] );
        }
    }
    
    /**
     * Get vendor store assignments
     */
    private static function get_vendor_store_assignments( $vendor_id ) {
        global $wpdb;
        
        $assignments = $wpdb->get_col( $wpdb->prepare(
            "SELECT store_key FROM {$wpdb->prefix}vss_vendor_stores 
             WHERE vendor_id = %d AND is_allowed = 1",
            $vendor_id
        ) );
        
        return $assignments ?: [];
    }
    
    /**
     * Get store category count
     */
    private static function get_store_category_count( $store_key ) {
        global $wpdb;
        
        return $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}vss_store_categories WHERE store_key = %s",
            $store_key
        ) );
    }
    
    /**
     * Get categories last updated time
     */
    private static function get_categories_last_updated( $store_key ) {
        global $wpdb;
        
        $last_updated = $wpdb->get_var( $wpdb->prepare(
            "SELECT MAX(last_updated) FROM {$wpdb->prefix}vss_store_categories WHERE store_key = %s",
            $store_key
        ) );
        
        return $last_updated ? date_i18n( 'M j, Y', strtotime( $last_updated ) ) : __( 'Never', 'vss' );
    }
}