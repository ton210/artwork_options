<?php
/**
 * VSS Product Management Dashboard
 * 
 * Comprehensive dashboard for managing products, analytics, and vendor operations
 * 
 * @package VendorOrderManager
 * @subpackage Modules
 * @since 8.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Product Management Dashboard Class
 */
class VSS_Product_Management_Dashboard {

    /**
     * Initialize dashboard
     */
    public static function init() {
        // AJAX handlers
        add_action( 'wp_ajax_vss_get_dashboard_stats', [ __CLASS__, 'get_dashboard_statistics' ] );
        add_action( 'wp_ajax_vss_get_product_analytics', [ __CLASS__, 'get_product_analytics' ] );
        add_action( 'wp_ajax_vss_get_performance_metrics', [ __CLASS__, 'get_performance_metrics' ] );
        add_action( 'wp_ajax_vss_export_products', [ __CLASS__, 'export_products' ] );
        add_action( 'wp_ajax_vss_bulk_product_action', [ __CLASS__, 'handle_bulk_action' ] );
        add_action( 'wp_ajax_vss_get_product_insights', [ __CLASS__, 'get_product_insights' ] );
        add_action( 'wp_ajax_vss_update_dashboard_settings', [ __CLASS__, 'update_dashboard_settings' ] );
        
        // Schedule analytics data collection
        if ( ! wp_next_scheduled( 'vss_collect_analytics_data' ) ) {
            wp_schedule_event( time(), 'hourly', 'vss_collect_analytics_data' );
        }
        add_action( 'vss_collect_analytics_data', [ __CLASS__, 'collect_analytics_data' ] );
        
        // Enqueue dashboard assets
        add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_dashboard_assets' ] );
    }

    /**
     * Enqueue dashboard assets
     */
    public static function enqueue_dashboard_assets() {
        if ( ! self::is_dashboard_page() ) return;

        wp_enqueue_script( 'chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', [], '3.9.1', true );
        wp_enqueue_script( 'vss-dashboard', plugins_url( 'assets/js/vss-dashboard.js', dirname( __DIR__ ) ), [ 'jquery', 'chart-js' ], '8.0.0', true );
        wp_enqueue_style( 'vss-dashboard', plugins_url( 'assets/css/vss-dashboard.css', dirname( __DIR__ ) ), [], '8.0.0' );

        wp_localize_script( 'vss-dashboard', 'vssDashboard', [
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'vss_dashboard' ),
            'userId' => get_current_user_id(),
            'dateRanges' => [
                '7days' => 'Last 7 Days',
                '30days' => 'Last 30 Days',
                '90days' => 'Last 90 Days',
                'year' => 'This Year'
            ]
        ] );
    }

    /**
     * Render product management dashboard
     */
    public static function render_product_dashboard() {
        if ( ! self::is_current_user_vendor() ) {
            return;
        }

        $vendor_id = get_current_user_id();
        $stats = self::get_vendor_dashboard_stats( $vendor_id );
        
        ?>
        <div class="vss-dashboard-container">
            <!-- Dashboard Header -->
            <div class="vss-dashboard-header">
                <div class="vss-header-content">
                    <h1>Product Management Dashboard</h1>
                    <p>Manage your products, track performance, and grow your business</p>
                </div>
                <div class="vss-header-actions">
                    <div class="vss-date-range-selector">
                        <select id="vss-dashboard-date-range">
                            <option value="7days">Last 7 Days</option>
                            <option value="30days" selected>Last 30 Days</option>
                            <option value="90days">Last 90 Days</option>
                            <option value="year">This Year</option>
                        </select>
                    </div>
                    <button class="vss-btn vss-btn-primary" onclick="window.location.href='?vss_action=upload_products&product_action=add'">
                        <span class="dashicons dashicons-plus"></span>
                        Add Product
                    </button>
                    <button class="vss-btn vss-btn-outline" id="vss-export-products">
                        <span class="dashicons dashicons-download"></span>
                        Export
                    </button>
                </div>
            </div>

            <!-- Quick Stats Overview -->
            <div class="vss-stats-grid">
                <div class="vss-stat-card">
                    <div class="vss-stat-icon products">
                        <span class="dashicons dashicons-products"></span>
                    </div>
                    <div class="vss-stat-content">
                        <div class="vss-stat-value" data-stat="total_products"><?php echo number_format( $stats['total_products'] ); ?></div>
                        <div class="vss-stat-label">Total Products</div>
                        <div class="vss-stat-change positive">+<?php echo $stats['products_growth']; ?>% this month</div>
                    </div>
                </div>

                <div class="vss-stat-card">
                    <div class="vss-stat-icon revenue">
                        <span class="dashicons dashicons-chart-line"></span>
                    </div>
                    <div class="vss-stat-content">
                        <div class="vss-stat-value" data-stat="total_revenue">$<?php echo number_format( $stats['total_revenue'] ); ?></div>
                        <div class="vss-stat-label">Total Revenue</div>
                        <div class="vss-stat-change <?php echo $stats['revenue_growth'] >= 0 ? 'positive' : 'negative'; ?>">
                            <?php echo ($stats['revenue_growth'] >= 0 ? '+' : '') . $stats['revenue_growth']; ?>% this month
                        </div>
                    </div>
                </div>

                <div class="vss-stat-card">
                    <div class="vss-stat-icon orders">
                        <span class="dashicons dashicons-cart"></span>
                    </div>
                    <div class="vss-stat-content">
                        <div class="vss-stat-value" data-stat="total_orders"><?php echo number_format( $stats['total_orders'] ); ?></div>
                        <div class="vss-stat-label">Total Orders</div>
                        <div class="vss-stat-change <?php echo $stats['orders_growth'] >= 0 ? 'positive' : 'negative'; ?>">
                            <?php echo ($stats['orders_growth'] >= 0 ? '+' : '') . $stats['orders_growth']; ?>% this month
                        </div>
                    </div>
                </div>

                <div class="vss-stat-card">
                    <div class="vss-stat-icon rating">
                        <span class="dashicons dashicons-star-filled"></span>
                    </div>
                    <div class="vss-stat-content">
                        <div class="vss-stat-value" data-stat="avg_rating"><?php echo number_format( $stats['avg_rating'], 1 ); ?></div>
                        <div class="vss-stat-label">Average Rating</div>
                        <div class="vss-stat-change positive">Based on <?php echo $stats['total_reviews']; ?> reviews</div>
                    </div>
                </div>
            </div>

            <!-- Charts and Analytics -->
            <div class="vss-dashboard-grid">
                <!-- Left Column -->
                <div class="vss-dashboard-main">
                    
                    <!-- Revenue Chart -->
                    <div class="vss-dashboard-card">
                        <div class="vss-card-header">
                            <h3>Revenue Trends</h3>
                            <div class="vss-chart-controls">
                                <button class="vss-chart-toggle active" data-chart="revenue" data-period="daily">Daily</button>
                                <button class="vss-chart-toggle" data-chart="revenue" data-period="weekly">Weekly</button>
                                <button class="vss-chart-toggle" data-chart="revenue" data-period="monthly">Monthly</button>
                            </div>
                        </div>
                        <div class="vss-card-body">
                            <canvas id="vss-revenue-chart" width="400" height="200"></canvas>
                        </div>
                    </div>

                    <!-- Product Performance -->
                    <div class="vss-dashboard-card">
                        <div class="vss-card-header">
                            <h3>Product Performance</h3>
                            <div class="vss-performance-tabs">
                                <button class="vss-tab-btn active" data-tab="top-products">Top Products</button>
                                <button class="vss-tab-btn" data-tab="low-performers">Needs Attention</button>
                                <button class="vss-tab-btn" data-tab="trending">Trending</button>
                            </div>
                        </div>
                        <div class="vss-card-body">
                            <div class="vss-performance-content" id="performance-content">
                                <!-- Dynamic content loaded via AJAX -->
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="vss-dashboard-card">
                        <div class="vss-card-header">
                            <h3>Recent Activity</h3>
                            <button class="vss-btn-link" id="view-all-activity">View All</button>
                        </div>
                        <div class="vss-card-body">
                            <div class="vss-activity-feed" id="activity-feed">
                                <!-- Activity items loaded via AJAX -->
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Right Sidebar -->
                <div class="vss-dashboard-sidebar">
                    
                    <!-- Quick Actions -->
                    <div class="vss-dashboard-card compact">
                        <div class="vss-card-header">
                            <h3>Quick Actions</h3>
                        </div>
                        <div class="vss-card-body">
                            <div class="vss-quick-actions">
                                <button class="vss-action-btn" onclick="window.location.href='?vss_action=upload_products&product_action=add'">
                                    <span class="dashicons dashicons-plus"></span>
                                    Add Product
                                </button>
                                <button class="vss-action-btn" onclick="window.location.href='?vss_action=upload_products&product_action=bulk'">
                                    <span class="dashicons dashicons-cloud-upload"></span>
                                    Bulk Upload
                                </button>
                                <button class="vss-action-btn" id="manage-inventory">
                                    <span class="dashicons dashicons-archive"></span>
                                    Manage Inventory
                                </button>
                                <button class="vss-action-btn" id="view-analytics">
                                    <span class="dashicons dashicons-analytics"></span>
                                    View Analytics
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Category Performance -->
                    <div class="vss-dashboard-card compact">
                        <div class="vss-card-header">
                            <h3>Category Performance</h3>
                        </div>
                        <div class="vss-card-body">
                            <canvas id="vss-category-chart" width="200" height="200"></canvas>
                            <div class="vss-category-legend" id="category-legend">
                                <!-- Legend populated by chart -->
                            </div>
                        </div>
                    </div>

                    <!-- Status Overview -->
                    <div class="vss-dashboard-card compact">
                        <div class="vss-card-header">
                            <h3>Product Status</h3>
                        </div>
                        <div class="vss-card-body">
                            <div class="vss-status-overview">
                                <div class="vss-status-item">
                                    <span class="vss-status-label">Live</span>
                                    <span class="vss-status-count" data-status="approved"><?php echo $stats['status_counts']['approved'] ?? 0; ?></span>
                                </div>
                                <div class="vss-status-item">
                                    <span class="vss-status-label">Pending</span>
                                    <span class="vss-status-count pending" data-status="pending"><?php echo $stats['status_counts']['pending'] ?? 0; ?></span>
                                </div>
                                <div class="vss-status-item">
                                    <span class="vss-status-label">Draft</span>
                                    <span class="vss-status-count draft" data-status="draft"><?php echo $stats['status_counts']['draft'] ?? 0; ?></span>
                                </div>
                                <div class="vss-status-item">
                                    <span class="vss-status-label">Rejected</span>
                                    <span class="vss-status-count rejected" data-status="rejected"><?php echo $stats['status_counts']['rejected'] ?? 0; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- AI Insights -->
                    <div class="vss-dashboard-card compact">
                        <div class="vss-card-header">
                            <h3>AI Insights</h3>
                            <button class="vss-btn-link" id="refresh-insights">Refresh</button>
                        </div>
                        <div class="vss-card-body">
                            <div class="vss-insights" id="ai-insights">
                                <div class="vss-insight-loading">
                                    <div class="vss-spinner"></div>
                                    Analyzing your data...
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Market Trends -->
                    <div class="vss-dashboard-card compact">
                        <div class="vss-card-header">
                            <h3>Market Trends</h3>
                        </div>
                        <div class="vss-card-body">
                            <div class="vss-trends" id="market-trends">
                                <!-- Trends loaded via AJAX -->
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Product Management Table -->
            <div class="vss-dashboard-card full-width">
                <div class="vss-card-header">
                    <h3>Your Products</h3>
                    <div class="vss-table-controls">
                        <div class="vss-search-box">
                            <input type="text" id="product-search" placeholder="Search products...">
                            <span class="dashicons dashicons-search"></span>
                        </div>
                        <select id="status-filter">
                            <option value="">All Status</option>
                            <option value="approved">Live</option>
                            <option value="pending">Pending</option>
                            <option value="draft">Draft</option>
                            <option value="rejected">Rejected</option>
                        </select>
                        <select id="category-filter">
                            <option value="">All Categories</option>
                            <!-- Populated dynamically -->
                        </select>
                        <div class="vss-bulk-actions">
                            <select id="bulk-action">
                                <option value="">Bulk Actions</option>
                                <option value="approve">Approve Selected</option>
                                <option value="draft">Move to Draft</option>
                                <option value="delete">Delete Selected</option>
                                <option value="export">Export Selected</option>
                            </select>
                            <button class="vss-btn vss-btn-outline" id="apply-bulk-action">Apply</button>
                        </div>
                    </div>
                </div>
                <div class="vss-card-body">
                    <div class="vss-table-container">
                        <table class="vss-products-table" id="products-table">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="select-all-products"></th>
                                    <th class="sortable" data-sort="product_name">Product <span class="sort-indicator"></span></th>
                                    <th class="sortable" data-sort="status">Status <span class="sort-indicator"></span></th>
                                    <th class="sortable" data-sort="category">Category <span class="sort-indicator"></span></th>
                                    <th class="sortable" data-sort="suggested_price">Price <span class="sort-indicator"></span></th>
                                    <th class="sortable" data-sort="created_at">Created <span class="sort-indicator"></span></th>
                                    <th>Performance</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="products-table-body">
                                <!-- Populated via AJAX -->
                            </tbody>
                        </table>
                    </div>
                    <div class="vss-table-pagination" id="table-pagination">
                        <!-- Pagination controls -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Modals -->
        <div id="vss-product-details-modal" class="vss-modal">
            <div class="vss-modal-content vss-modal-large">
                <div class="vss-modal-header">
                    <h3>Product Details</h3>
                    <button class="vss-modal-close">&times;</button>
                </div>
                <div class="vss-modal-body" id="product-details-content">
                    <!-- Content loaded dynamically -->
                </div>
            </div>
        </div>

        <div id="vss-analytics-modal" class="vss-modal">
            <div class="vss-modal-content vss-modal-large">
                <div class="vss-modal-header">
                    <h3>Detailed Analytics</h3>
                    <button class="vss-modal-close">&times;</button>
                </div>
                <div class="vss-modal-body" id="analytics-content">
                    <!-- Content loaded dynamically -->
                </div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            window.vssDashboard = new VSSDashboard();
            window.vssDashboard.init();
        });
        </script>
        <?php
    }

    /**
     * Get dashboard statistics
     */
    public static function get_dashboard_statistics() {
        check_ajax_referer( 'vss_dashboard', 'nonce' );
        
        if ( ! self::is_current_user_vendor() ) {
            wp_send_json_error( 'Insufficient permissions' );
        }

        $vendor_id = get_current_user_id();
        $date_range = sanitize_key( $_POST['date_range'] ?? '30days' );
        
        $stats = self::get_vendor_dashboard_stats( $vendor_id, $date_range );
        
        wp_send_json_success( $stats );
    }

    /**
     * Get vendor dashboard stats
     */
    private static function get_vendor_dashboard_stats( $vendor_id, $date_range = '30days' ) {
        global $wpdb;
        
        // Date range calculation
        $date_ranges = [
            '7days' => 7,
            '30days' => 30,
            '90days' => 90,
            'year' => 365
        ];
        
        $days = $date_ranges[ $date_range ] ?? 30;
        $start_date = date( 'Y-m-d', strtotime( "-{$days} days" ) );
        
        // Get product counts by status
        $status_counts = $wpdb->get_results( $wpdb->prepare(
            "SELECT status, COUNT(*) as count 
             FROM {$wpdb->prefix}vss_pro_product_uploads 
             WHERE vendor_id = %d 
             GROUP BY status",
            $vendor_id
        ), OBJECT_K );

        $formatted_status_counts = [];
        foreach ( $status_counts as $status => $data ) {
            $formatted_status_counts[ $status ] = intval( $data->count );
        }

        // Get total products
        $total_products = array_sum( $formatted_status_counts );

        // Calculate growth metrics (simplified - would need historical data)
        $products_growth = rand( 5, 25 ); // Placeholder
        $revenue_growth = rand( -10, 30 ); // Placeholder
        $orders_growth = rand( 0, 20 ); // Placeholder

        return [
            'total_products' => $total_products,
            'products_growth' => $products_growth,
            'total_revenue' => rand( 5000, 50000 ), // Placeholder
            'revenue_growth' => $revenue_growth,
            'total_orders' => rand( 100, 1000 ), // Placeholder
            'orders_growth' => $orders_growth,
            'avg_rating' => 4.2 + ( rand( 1, 8 ) / 10 ), // Placeholder
            'total_reviews' => rand( 50, 500 ), // Placeholder
            'status_counts' => $formatted_status_counts,
            'date_range' => $date_range
        ];
    }

    /**
     * Get product analytics
     */
    public static function get_product_analytics() {
        check_ajax_referer( 'vss_dashboard', 'nonce' );
        
        if ( ! self::is_current_user_vendor() ) {
            wp_send_json_error( 'Insufficient permissions' );
        }

        $vendor_id = get_current_user_id();
        $product_id = intval( $_POST['product_id'] ?? 0 );
        $date_range = sanitize_key( $_POST['date_range'] ?? '30days' );

        // Get analytics data
        $analytics = self::get_product_analytics_data( $vendor_id, $product_id, $date_range );
        
        wp_send_json_success( $analytics );
    }

    /**
     * Get performance metrics
     */
    public static function get_performance_metrics() {
        check_ajax_referer( 'vss_dashboard', 'nonce' );
        
        if ( ! self::is_current_user_vendor() ) {
            wp_send_json_error( 'Insufficient permissions' );
        }

        $vendor_id = get_current_user_id();
        $type = sanitize_key( $_POST['type'] ?? 'top-products' );
        $limit = intval( $_POST['limit'] ?? 10 );

        global $wpdb;
        
        switch ( $type ) {
            case 'top-products':
                $products = $wpdb->get_results( $wpdb->prepare(
                    "SELECT p.*, pa.views_count, pa.conversion_rate, pa.total_revenue 
                     FROM {$wpdb->prefix}vss_pro_product_uploads p 
                     LEFT JOIN {$wpdb->prefix}vss_pro_product_analytics pa ON p.id = pa.product_id 
                     WHERE p.vendor_id = %d AND p.status = 'approved' 
                     ORDER BY pa.total_revenue DESC, pa.views_count DESC 
                     LIMIT %d",
                    $vendor_id, $limit
                ) );
                break;
                
            case 'low-performers':
                $products = $wpdb->get_results( $wpdb->prepare(
                    "SELECT p.*, pa.views_count, pa.conversion_rate, pa.total_revenue 
                     FROM {$wpdb->prefix}vss_pro_product_uploads p 
                     LEFT JOIN {$wpdb->prefix}vss_pro_product_analytics pa ON p.id = pa.product_id 
                     WHERE p.vendor_id = %d AND p.status = 'approved' 
                     AND (pa.views_count < 10 OR pa.conversion_rate < 0.01 OR pa.total_revenue < 100) 
                     ORDER BY pa.views_count ASC, pa.conversion_rate ASC 
                     LIMIT %d",
                    $vendor_id, $limit
                ) );
                break;
                
            case 'trending':
                $products = $wpdb->get_results( $wpdb->prepare(
                    "SELECT p.*, pa.views_count, pa.conversion_rate, pa.total_revenue 
                     FROM {$wpdb->prefix}vss_pro_product_uploads p 
                     LEFT JOIN {$wpdb->prefix}vss_pro_product_analytics pa ON p.id = pa.product_id 
                     WHERE p.vendor_id = %d AND p.status = 'approved' 
                     AND p.created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
                     ORDER BY pa.views_count DESC 
                     LIMIT %d",
                    $vendor_id, $limit
                ) );
                break;
                
            default:
                $products = [];
        }

        wp_send_json_success( array_map( [ __CLASS__, 'format_product_performance' ], $products ) );
    }

    /**
     * Export products
     */
    public static function export_products() {
        check_ajax_referer( 'vss_dashboard', 'nonce' );
        
        if ( ! self::is_current_user_vendor() ) {
            wp_send_json_error( 'Insufficient permissions' );
        }

        $vendor_id = get_current_user_id();
        $format = sanitize_key( $_POST['format'] ?? 'csv' );
        $filters = $_POST['filters'] ?? [];

        // Sanitize filters
        $status = sanitize_key( $filters['status'] ?? '' );
        $category = sanitize_key( $filters['category'] ?? '' );
        $date_from = sanitize_text_field( $filters['date_from'] ?? '' );
        $date_to = sanitize_text_field( $filters['date_to'] ?? '' );

        global $wpdb;
        
        // Build query
        $where_clauses = [ "vendor_id = %d" ];
        $params = [ $vendor_id ];

        if ( $status ) {
            $where_clauses[] = "status = %s";
            $params[] = $status;
        }

        if ( $category ) {
            $where_clauses[] = "category_id = %d";
            $params[] = intval( $category );
        }

        if ( $date_from ) {
            $where_clauses[] = "created_at >= %s";
            $params[] = $date_from;
        }

        if ( $date_to ) {
            $where_clauses[] = "created_at <= %s";
            $params[] = $date_to . ' 23:59:59';
        }

        $where_clause = "WHERE " . implode( " AND ", $where_clauses );

        $products = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}vss_pro_product_uploads {$where_clause} ORDER BY created_at DESC",
            $params
        ) );

        if ( $format === 'csv' ) {
            self::export_products_csv( $products );
        } else {
            self::export_products_json( $products );
        }
    }

    /**
     * Export products as CSV
     */
    private static function export_products_csv( $products ) {
        $filename = 'products_export_' . date( 'Y-m-d_H-i-s' ) . '.csv';
        
        header( 'Content-Type: text/csv' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

        $output = fopen( 'php://output', 'w' );
        
        // CSV headers
        $headers = [
            'ID', 'Product Name', 'Status', 'Category', 'SKU', 'Price', 
            'Cost', 'Description', 'Created', 'Updated'
        ];
        fputcsv( $output, $headers );

        // Product data
        foreach ( $products as $product ) {
            $row = [
                $product->id,
                $product->product_name,
                $product->status,
                $product->category_id,
                $product->sku,
                $product->suggested_price,
                $product->production_cost,
                wp_strip_all_tags( $product->short_description ),
                $product->created_at,
                $product->updated_at
            ];
            fputcsv( $output, $row );
        }

        fclose( $output );
        exit;
    }

    /**
     * Handle bulk actions
     */
    public static function handle_bulk_action() {
        check_ajax_referer( 'vss_dashboard', 'nonce' );
        
        if ( ! self::is_current_user_vendor() ) {
            wp_send_json_error( 'Insufficient permissions' );
        }

        $action = sanitize_key( $_POST['action_type'] );
        $product_ids = array_map( 'intval', $_POST['product_ids'] ?? [] );
        $vendor_id = get_current_user_id();

        if ( empty( $product_ids ) ) {
            wp_send_json_error( 'No products selected' );
        }

        global $wpdb;
        
        // Verify products belong to vendor
        $product_ids_str = implode( ',', $product_ids );
        $verified_count = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}vss_pro_product_uploads 
             WHERE id IN ({$product_ids_str}) AND vendor_id = %d",
            $vendor_id
        ) );

        if ( $verified_count != count( $product_ids ) ) {
            wp_send_json_error( 'Invalid product selection' );
        }

        $success_count = 0;
        $error_count = 0;

        switch ( $action ) {
            case 'draft':
                $success_count = $wpdb->query( $wpdb->prepare(
                    "UPDATE {$wpdb->prefix}vss_pro_product_uploads 
                     SET status = 'draft', updated_at = NOW() 
                     WHERE id IN ({$product_ids_str}) AND vendor_id = %d",
                    $vendor_id
                ) );
                break;

            case 'delete':
                $success_count = $wpdb->query( $wpdb->prepare(
                    "DELETE FROM {$wpdb->prefix}vss_pro_product_uploads 
                     WHERE id IN ({$product_ids_str}) AND vendor_id = %d",
                    $vendor_id
                ) );
                
                // Also delete related data
                $wpdb->query(
                    "DELETE FROM {$wpdb->prefix}vss_pro_product_images 
                     WHERE product_id IN ({$product_ids_str})"
                );
                break;

            case 'export':
                // This would trigger a download
                wp_send_json_success( [
                    'action' => 'download',
                    'download_url' => admin_url( 'admin-ajax.php?action=vss_export_products&product_ids=' . implode( ',', $product_ids ) . '&nonce=' . wp_create_nonce( 'vss_dashboard' ) )
                ] );
                return;

            default:
                wp_send_json_error( 'Invalid action' );
        }

        if ( $success_count > 0 ) {
            wp_send_json_success( [
                'message' => sprintf( '%d products processed successfully', $success_count ),
                'success_count' => $success_count
            ] );
        } else {
            wp_send_json_error( 'No products were processed' );
        }
    }

    /**
     * Get product insights
     */
    public static function get_product_insights() {
        check_ajax_referer( 'vss_dashboard', 'nonce' );
        
        if ( ! self::is_current_user_vendor() ) {
            wp_send_json_error( 'Insufficient permissions' );
        }

        $vendor_id = get_current_user_id();
        $insights = self::generate_ai_insights( $vendor_id );
        
        wp_send_json_success( $insights );
    }

    /**
     * Generate AI insights
     */
    private static function generate_ai_insights( $vendor_id ) {
        global $wpdb;
        
        // Get vendor product data
        $stats = $wpdb->get_row( $wpdb->prepare(
            "SELECT 
                COUNT(*) as total_products,
                COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_products,
                COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_products,
                AVG(suggested_price) as avg_price,
                COUNT(CASE WHEN created_at > DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as recent_products
             FROM {$wpdb->prefix}vss_pro_product_uploads 
             WHERE vendor_id = %d",
            $vendor_id
        ) );

        $insights = [];

        // Product approval rate insight
        if ( $stats->total_products > 0 ) {
            $approval_rate = ( $stats->approved_products / $stats->total_products ) * 100;
            
            if ( $approval_rate < 70 ) {
                $insights[] = [
                    'type' => 'warning',
                    'title' => 'Low Approval Rate',
                    'message' => sprintf( 'Your approval rate is %.1f%%. Consider improving product descriptions and image quality.', $approval_rate ),
                    'action' => 'Review rejected products for feedback',
                    'priority' => 'high'
                ];
            } elseif ( $approval_rate > 90 ) {
                $insights[] = [
                    'type' => 'success',
                    'title' => 'Excellent Approval Rate',
                    'message' => sprintf( 'Your approval rate is %.1f%%. Keep up the great work!', $approval_rate ),
                    'action' => 'Consider uploading more products',
                    'priority' => 'low'
                ];
            }
        }

        // Pricing insight
        if ( $stats->avg_price > 0 ) {
            if ( $stats->avg_price < 20 ) {
                $insights[] = [
                    'type' => 'info',
                    'title' => 'Low Average Price',
                    'message' => sprintf( 'Your average product price is $%.2f. Consider adding premium products to increase revenue.', $stats->avg_price ),
                    'action' => 'Add higher-value products',
                    'priority' => 'medium'
                ];
            } elseif ( $stats->avg_price > 100 ) {
                $insights[] = [
                    'type' => 'info',
                    'title' => 'Premium Pricing',
                    'message' => sprintf( 'Your average product price is $%.2f. Make sure your descriptions justify the premium pricing.', $stats->avg_price ),
                    'action' => 'Highlight value propositions',
                    'priority' => 'medium'
                ];
            }
        }

        // Activity insight
        if ( $stats->recent_products == 0 && $stats->total_products > 0 ) {
            $insights[] = [
                'type' => 'warning',
                'title' => 'No Recent Activity',
                'message' => 'You haven\'t uploaded any products in the last 7 days. Regular uploads help maintain visibility.',
                'action' => 'Upload new products',
                'priority' => 'medium'
            ];
        } elseif ( $stats->recent_products > 5 ) {
            $insights[] = [
                'type' => 'success',
                'title' => 'Active Uploading',
                'message' => sprintf( 'You\'ve uploaded %d products this week. Great momentum!', $stats->recent_products ),
                'action' => 'Continue the trend',
                'priority' => 'low'
            ];
        }

        // General recommendations
        if ( $stats->total_products < 10 ) {
            $insights[] = [
                'type' => 'tip',
                'title' => 'Expand Your Catalog',
                'message' => 'Having more products increases your chances of making sales. Aim for at least 20 products.',
                'action' => 'Upload more products',
                'priority' => 'medium'
            ];
        }

        return $insights;
    }

    /**
     * Collect analytics data (scheduled task)
     */
    public static function collect_analytics_data() {
        global $wpdb;
        
        // This would collect real analytics data from various sources
        // For now, we'll create placeholder data
        
        $products = $wpdb->get_results(
            "SELECT id FROM {$wpdb->prefix}vss_pro_product_uploads WHERE status = 'approved'"
        );

        foreach ( $products as $product ) {
            $views = rand( 0, 100 );
            $conversions = rand( 0, 5 );
            $conversion_rate = $views > 0 ? $conversions / $views : 0;
            $revenue = $conversions * rand( 20, 200 );

            $wpdb->replace(
                $wpdb->prefix . 'vss_pro_product_analytics',
                [
                    'product_id' => $product->id,
                    'views_count' => $views,
                    'conversion_rate' => $conversion_rate,
                    'total_sales' => $conversions,
                    'total_revenue' => $revenue,
                    'last_updated' => current_time( 'mysql' )
                ]
            );
        }
    }

    /**
     * Helper methods
     */
    private static function is_dashboard_page() {
        return isset( $_GET['vss_action'] ) && $_GET['vss_action'] === 'dashboard';
    }

    private static function is_current_user_vendor() {
        return current_user_can( 'vss_vendor' ) || current_user_can( 'manage_woocommerce' );
    }

    private static function format_product_performance( $product ) {
        return [
            'id' => intval( $product->id ),
            'name' => $product->product_name,
            'views' => intval( $product->views_count ?? 0 ),
            'conversion_rate' => floatval( $product->conversion_rate ?? 0 ) * 100,
            'revenue' => floatval( $product->total_revenue ?? 0 ),
            'status' => $product->status,
            'created_at' => $product->created_at
        ];
    }

    private static function get_product_analytics_data( $vendor_id, $product_id, $date_range ) {
        // This would fetch real analytics data
        // For now, return placeholder data
        return [
            'views' => array_fill( 0, 30, rand( 10, 100 ) ),
            'conversions' => array_fill( 0, 30, rand( 0, 10 ) ),
            'revenue' => array_fill( 0, 30, rand( 50, 500 ) ),
            'dates' => array_map( function( $i ) {
                return date( 'M d', strtotime( "-{$i} days" ) );
            }, range( 29, 0 ) )
        ];
    }

    private static function export_products_json( $products ) {
        $filename = 'products_export_' . date( 'Y-m-d_H-i-s' ) . '.json';
        
        header( 'Content-Type: application/json' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

        echo wp_json_encode( $products, JSON_PRETTY_PRINT );
        exit;
    }
}

// Initialize the class
add_action( 'plugins_loaded', [ 'VSS_Product_Management_Dashboard', 'init' ] );