<?php
/**
 * VSS Analytics Dashboard Module
 * 
 * Comprehensive analytics and statistics for vendor marketplace
 * 
 * @package VendorOrderManager
 * @subpackage Modules
 * @since 7.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Trait for Analytics Dashboard functionality
 */
trait VSS_Analytics_Dashboard {

    /**
     * Initialize analytics dashboard
     */
    public static function init_analytics_dashboard() {
        // AJAX handlers for analytics
        add_action( 'wp_ajax_vss_get_analytics_data', [ self::class, 'get_analytics_data' ] );
        add_action( 'wp_ajax_vss_export_analytics', [ self::class, 'export_analytics' ] );
        add_action( 'wp_ajax_vss_get_product_performance', [ self::class, 'get_product_performance' ] );
        add_action( 'wp_ajax_vss_track_product_view', [ self::class, 'track_product_view' ] );
        add_action( 'wp_ajax_nopriv_vss_track_product_view', [ self::class, 'track_product_view' ] );
        
        // Analytics tracking hooks
        add_action( 'woocommerce_add_to_cart', [ self::class, 'track_cart_addition' ], 10, 6 );
        add_action( 'woocommerce_order_status_completed', [ self::class, 'track_purchase' ] );
        add_action( 'vss_product_question_submitted', [ self::class, 'track_product_question' ] );
        
        // Create analytics tables
        self::create_analytics_tables();
        
        // Schedule analytics aggregation
        if ( ! wp_next_scheduled( 'vss_aggregate_analytics' ) ) {
            wp_schedule_event( time(), 'daily', 'vss_aggregate_analytics' );
        }
        add_action( 'vss_aggregate_analytics', [ self::class, 'aggregate_daily_analytics' ] );
    }

    /**
     * Create analytics tracking tables
     */
    public static function create_analytics_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        // Product views tracking
        $table_name = $wpdb->prefix . 'vss_product_views';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            product_id bigint(20) NOT NULL,
            user_id bigint(20) DEFAULT NULL,
            session_id varchar(255) NOT NULL,
            ip_address varchar(45) NOT NULL,
            user_agent text DEFAULT NULL,
            referrer text DEFAULT NULL,
            viewed_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY product_id (product_id),
            KEY user_id (user_id),
            KEY session_id (session_id),
            KEY viewed_at (viewed_at)
        ) $charset_collate;";
        
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
        
        // Cart additions tracking
        $table_name = $wpdb->prefix . 'vss_cart_additions';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            product_id bigint(20) NOT NULL,
            user_id bigint(20) DEFAULT NULL,
            session_id varchar(255) NOT NULL,
            quantity int(11) DEFAULT 1,
            added_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY product_id (product_id),
            KEY user_id (user_id),
            KEY added_at (added_at)
        ) $charset_collate;";
        
        dbDelta( $sql );
        
        // Purchase tracking
        $table_name = $wpdb->prefix . 'vss_purchase_tracking';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            product_id bigint(20) NOT NULL,
            order_id bigint(20) NOT NULL,
            user_id bigint(20) DEFAULT NULL,
            quantity int(11) DEFAULT 1,
            unit_price decimal(10,2) DEFAULT 0,
            total_price decimal(10,2) DEFAULT 0,
            purchased_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY product_id (product_id),
            KEY order_id (order_id),
            KEY purchased_at (purchased_at)
        ) $charset_collate;";
        
        dbDelta( $sql );
        
        // Vendor performance metrics
        $table_name = $wpdb->prefix . 'vss_vendor_performance';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            vendor_id bigint(20) NOT NULL,
            date_recorded date NOT NULL,
            total_views int(11) DEFAULT 0,
            unique_views int(11) DEFAULT 0,
            cart_additions int(11) DEFAULT 0,
            purchases int(11) DEFAULT 0,
            revenue decimal(10,2) DEFAULT 0,
            questions_received int(11) DEFAULT 0,
            questions_answered int(11) DEFAULT 0,
            response_time_avg int(11) DEFAULT 0,
            conversion_rate decimal(5,4) DEFAULT 0,
            PRIMARY KEY (id),
            UNIQUE KEY unique_vendor_date (vendor_id, date_recorded),
            KEY vendor_id (vendor_id),
            KEY date_recorded (date_recorded)
        ) $charset_collate;";
        
        dbDelta( $sql );
    }

    /**
     * Render analytics dashboard
     */
    public static function render_analytics_dashboard() {
        $vendor_id = get_current_user_id();
        $preferred_lang = get_user_meta( $vendor_id, 'vss_preferred_language', true );
        $is_chinese = ( $preferred_lang === 'zh_CN' );
        
        // Get date range from request
        $date_range = sanitize_key( $_GET['range'] ?? 'last_30_days' );
        $start_date = self::get_date_range_start( $date_range );
        $end_date = date( 'Y-m-d' );
        
        // Get analytics data
        $analytics = self::get_vendor_analytics_data( $vendor_id, $start_date, $end_date );
        ?>
        
        <div class="vss-analytics-dashboard">
            <div class="vss-analytics-header">
                <h2><?php echo $is_chinese ? '数据分析' : 'Analytics Dashboard'; ?></h2>
                
                <div class="vss-analytics-controls">
                    <div class="date-range-selector">
                        <select id="analytics-date-range" class="vss-select">
                            <option value="today" <?php selected( $date_range, 'today' ); ?>><?php echo $is_chinese ? '今天' : 'Today'; ?></option>
                            <option value="yesterday" <?php selected( $date_range, 'yesterday' ); ?>><?php echo $is_chinese ? '昨天' : 'Yesterday'; ?></option>
                            <option value="last_7_days" <?php selected( $date_range, 'last_7_days' ); ?>><?php echo $is_chinese ? '最近7天' : 'Last 7 Days'; ?></option>
                            <option value="last_30_days" <?php selected( $date_range, 'last_30_days' ); ?>><?php echo $is_chinese ? '最近30天' : 'Last 30 Days'; ?></option>
                            <option value="last_90_days" <?php selected( $date_range, 'last_90_days' ); ?>><?php echo $is_chinese ? '最近90天' : 'Last 90 Days'; ?></option>
                            <option value="this_month" <?php selected( $date_range, 'this_month' ); ?>><?php echo $is_chinese ? '本月' : 'This Month'; ?></option>
                            <option value="last_month" <?php selected( $date_range, 'last_month' ); ?>><?php echo $is_chinese ? '上月' : 'Last Month'; ?></option>
                            <option value="this_year" <?php selected( $date_range, 'this_year' ); ?>><?php echo $is_chinese ? '今年' : 'This Year'; ?></option>
                        </select>
                    </div>
                    
                    <button class="vss-btn secondary" id="export-analytics">
                        <span class="dashicons dashicons-download"></span>
                        <?php echo $is_chinese ? '导出数据' : 'Export Data'; ?>
                    </button>
                    
                    <button class="vss-btn secondary" id="refresh-analytics">
                        <span class="dashicons dashicons-update"></span>
                        <?php echo $is_chinese ? '刷新' : 'Refresh'; ?>
                    </button>
                </div>
            </div>

            <!-- Key Metrics Overview -->
            <div class="vss-metrics-overview">
                <div class="metric-card primary">
                    <div class="metric-icon">
                        <span class="dashicons dashicons-visibility"></span>
                    </div>
                    <div class="metric-content">
                        <div class="metric-value"><?php echo number_format( $analytics['overview']['total_views'] ); ?></div>
                        <div class="metric-label"><?php echo $is_chinese ? '总浏览量' : 'Total Views'; ?></div>
                        <div class="metric-change <?php echo $analytics['overview']['views_change'] >= 0 ? 'positive' : 'negative'; ?>">
                            <?php echo $analytics['overview']['views_change'] >= 0 ? '+' : ''; ?><?php echo number_format( $analytics['overview']['views_change'], 1 ); ?>%
                        </div>
                    </div>
                </div>
                
                <div class="metric-card success">
                    <div class="metric-icon">
                        <span class="dashicons dashicons-cart"></span>
                    </div>
                    <div class="metric-content">
                        <div class="metric-value"><?php echo number_format( $analytics['overview']['cart_additions'] ); ?></div>
                        <div class="metric-label"><?php echo $is_chinese ? '加入购物车' : 'Cart Additions'; ?></div>
                        <div class="metric-change <?php echo $analytics['overview']['cart_change'] >= 0 ? 'positive' : 'negative'; ?>">
                            <?php echo $analytics['overview']['cart_change'] >= 0 ? '+' : ''; ?><?php echo number_format( $analytics['overview']['cart_change'], 1 ); ?>%
                        </div>
                    </div>
                </div>
                
                <div class="metric-card warning">
                    <div class="metric-icon">
                        <span class="dashicons dashicons-products"></span>
                    </div>
                    <div class="metric-content">
                        <div class="metric-value"><?php echo number_format( $analytics['overview']['purchases'] ); ?></div>
                        <div class="metric-label"><?php echo $is_chinese ? '销售数量' : 'Sales'; ?></div>
                        <div class="metric-change <?php echo $analytics['overview']['sales_change'] >= 0 ? 'positive' : 'negative'; ?>">
                            <?php echo $analytics['overview']['sales_change'] >= 0 ? '+' : ''; ?><?php echo number_format( $analytics['overview']['sales_change'], 1 ); ?>%
                        </div>
                    </div>
                </div>
                
                <div class="metric-card info">
                    <div class="metric-icon">
                        <span class="dashicons dashicons-chart-line"></span>
                    </div>
                    <div class="metric-content">
                        <div class="metric-value"><?php echo number_format( $analytics['overview']['conversion_rate'], 2 ); ?>%</div>
                        <div class="metric-label"><?php echo $is_chinese ? '转化率' : 'Conversion Rate'; ?></div>
                        <div class="metric-change <?php echo $analytics['overview']['conversion_change'] >= 0 ? 'positive' : 'negative'; ?>">
                            <?php echo $analytics['overview']['conversion_change'] >= 0 ? '+' : ''; ?><?php echo number_format( $analytics['overview']['conversion_change'], 2 ); ?>%
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="vss-charts-section">
                <div class="chart-container large">
                    <div class="chart-header">
                        <h3><?php echo $is_chinese ? '流量趋势' : 'Traffic Trends'; ?></h3>
                        <div class="chart-controls">
                            <button class="chart-toggle active" data-metric="views"><?php echo $is_chinese ? '浏览量' : 'Views'; ?></button>
                            <button class="chart-toggle" data-metric="cart"><?php echo $is_chinese ? '购物车' : 'Cart'; ?></button>
                            <button class="chart-toggle" data-metric="sales"><?php echo $is_chinese ? '销量' : 'Sales'; ?></button>
                        </div>
                    </div>
                    <div class="chart-body">
                        <canvas id="traffic-trends-chart"></canvas>
                    </div>
                </div>
                
                <div class="chart-container medium">
                    <div class="chart-header">
                        <h3><?php echo $is_chinese ? '产品表现' : 'Product Performance'; ?></h3>
                    </div>
                    <div class="chart-body">
                        <canvas id="product-performance-chart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Detailed Analytics Tables -->
            <div class="vss-analytics-tables">
                <!-- Top Products Table -->
                <div class="analytics-table-container">
                    <div class="table-header">
                        <h3><?php echo $is_chinese ? '热门产品' : 'Top Products'; ?></h3>
                        <select class="table-filter" data-table="top-products">
                            <option value="views"><?php echo $is_chinese ? '按浏览量' : 'By Views'; ?></option>
                            <option value="cart"><?php echo $is_chinese ? '按购物车' : 'By Cart Adds'; ?></option>
                            <option value="sales"><?php echo $is_chinese ? '按销量' : 'By Sales'; ?></option>
                            <option value="revenue"><?php echo $is_chinese ? '按收入' : 'By Revenue'; ?></option>
                        </select>
                    </div>
                    <div class="table-body">
                        <table class="vss-analytics-table" id="top-products-table">
                            <thead>
                                <tr>
                                    <th><?php echo $is_chinese ? '产品' : 'Product'; ?></th>
                                    <th><?php echo $is_chinese ? '浏览量' : 'Views'; ?></th>
                                    <th><?php echo $is_chinese ? '购物车' : 'Cart Adds'; ?></th>
                                    <th><?php echo $is_chinese ? '销量' : 'Sales'; ?></th>
                                    <th><?php echo $is_chinese ? '转化率' : 'Conversion'; ?></th>
                                    <th><?php echo $is_chinese ? '收入' : 'Revenue'; ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( $analytics['top_products'] as $product ) : ?>
                                <tr>
                                    <td>
                                        <div class="product-info">
                                            <img src="<?php echo esc_url( $product['image'] ); ?>" alt="" class="product-thumb">
                                            <span class="product-name"><?php echo esc_html( $product['name'] ); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo number_format( $product['views'] ); ?></td>
                                    <td><?php echo number_format( $product['cart_additions'] ); ?></td>
                                    <td><?php echo number_format( $product['sales'] ); ?></td>
                                    <td><?php echo number_format( $product['conversion_rate'], 2 ); ?>%</td>
                                    <td><?php echo wc_price( $product['revenue'] ); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Customer Insights Table -->
                <div class="analytics-table-container">
                    <div class="table-header">
                        <h3><?php echo $is_chinese ? '客户洞察' : 'Customer Insights'; ?></h3>
                    </div>
                    <div class="table-body">
                        <table class="vss-analytics-table">
                            <thead>
                                <tr>
                                    <th><?php echo $is_chinese ? '指标' : 'Metric'; ?></th>
                                    <th><?php echo $is_chinese ? '当期' : 'Current'; ?></th>
                                    <th><?php echo $is_chinese ? '上期' : 'Previous'; ?></th>
                                    <th><?php echo $is_chinese ? '变化' : 'Change'; ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><?php echo $is_chinese ? '访客数' : 'Unique Visitors'; ?></td>
                                    <td><?php echo number_format( $analytics['customers']['unique_visitors'] ); ?></td>
                                    <td><?php echo number_format( $analytics['customers']['unique_visitors_prev'] ); ?></td>
                                    <td class="<?php echo $analytics['customers']['visitors_change'] >= 0 ? 'positive' : 'negative'; ?>">
                                        <?php echo $analytics['customers']['visitors_change'] >= 0 ? '+' : ''; ?><?php echo number_format( $analytics['customers']['visitors_change'], 1 ); ?>%
                                    </td>
                                </tr>
                                <tr>
                                    <td><?php echo $is_chinese ? '回访客户' : 'Returning Customers'; ?></td>
                                    <td><?php echo number_format( $analytics['customers']['returning_customers'] ); ?></td>
                                    <td><?php echo number_format( $analytics['customers']['returning_customers_prev'] ); ?></td>
                                    <td class="<?php echo $analytics['customers']['returning_change'] >= 0 ? 'positive' : 'negative'; ?>">
                                        <?php echo $analytics['customers']['returning_change'] >= 0 ? '+' : ''; ?><?php echo number_format( $analytics['customers']['returning_change'], 1 ); ?>%
                                    </td>
                                </tr>
                                <tr>
                                    <td><?php echo $is_chinese ? '平均页面停留时间' : 'Avg. Session Duration'; ?></td>
                                    <td><?php echo gmdate( 'i:s', $analytics['customers']['avg_session_duration'] ); ?></td>
                                    <td><?php echo gmdate( 'i:s', $analytics['customers']['avg_session_duration_prev'] ); ?></td>
                                    <td class="<?php echo $analytics['customers']['duration_change'] >= 0 ? 'positive' : 'negative'; ?>">
                                        <?php echo $analytics['customers']['duration_change'] >= 0 ? '+' : ''; ?><?php echo number_format( $analytics['customers']['duration_change'], 1 ); ?>%
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Questions & Support Table -->
                <div class="analytics-table-container">
                    <div class="table-header">
                        <h3><?php echo $is_chinese ? '客户咨询' : 'Customer Questions'; ?></h3>
                        <a href="#questions" class="view-all-link"><?php echo $is_chinese ? '查看全部' : 'View All'; ?></a>
                    </div>
                    <div class="table-body">
                        <table class="vss-analytics-table">
                            <thead>
                                <tr>
                                    <th><?php echo $is_chinese ? '产品' : 'Product'; ?></th>
                                    <th><?php echo $is_chinese ? '问题' : 'Question'; ?></th>
                                    <th><?php echo $is_chinese ? '状态' : 'Status'; ?></th>
                                    <th><?php echo $is_chinese ? '时间' : 'Time'; ?></th>
                                    <th><?php echo $is_chinese ? '操作' : 'Actions'; ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( $analytics['recent_questions'] as $question ) : ?>
                                <tr>
                                    <td><?php echo esc_html( $question['product_name'] ); ?></td>
                                    <td>
                                        <div class="question-preview">
                                            <?php echo esc_html( wp_trim_words( $question['question'], 10 ) ); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo esc_attr( $question['status'] ); ?>">
                                            <?php echo esc_html( $question['status'] ); ?>
                                        </span>
                                    </td>
                                    <td><?php echo esc_html( human_time_diff( strtotime( $question['created_at'] ) ) ); ?> <?php echo $is_chinese ? '前' : 'ago'; ?></td>
                                    <td>
                                        <button class="vss-btn small answer-question" data-question-id="<?php echo esc_attr( $question['id'] ); ?>">
                                            <?php echo $is_chinese ? '回答' : 'Answer'; ?>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart.js Integration -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        jQuery(document).ready(function($) {
            const analyticsData = <?php echo json_encode( $analytics ); ?>;
            const isChirouonese = <?php echo $is_chinese ? 'true' : 'false'; ?>;
            
            // Initialize charts
            initTrafficTrendsChart(analyticsData.trends);
            initProductPerformanceChart(analyticsData.top_products);
            
            // Date range change handler
            $('#analytics-date-range').on('change', function() {
                const range = $(this).val();
                window.location.href = '<?php echo esc_url( add_query_arg( 'vss_action', 'analytics', get_permalink() ) ); ?>&range=' + range;
            });
            
            // Export analytics
            $('#export-analytics').on('click', function() {
                const range = $('#analytics-date-range').val();
                window.location.href = '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>?action=vss_export_analytics&range=' + range + '&nonce=<?php echo wp_create_nonce( 'vss_export_analytics' ); ?>';
            });
            
            // Chart toggles
            $('.chart-toggle').on('click', function() {
                $('.chart-toggle').removeClass('active');
                $(this).addClass('active');
                const metric = $(this).data('metric');
                updateTrafficChart(metric);
            });
            
            // Table filters
            $('.table-filter').on('change', function() {
                const table = $(this).data('table');
                const sortBy = $(this).val();
                updateTable(table, sortBy);
            });
            
            function initTrafficTrendsChart(trendsData) {
                const ctx = document.getElementById('traffic-trends-chart').getContext('2d');
                window.trafficChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: trendsData.labels,
                        datasets: [{
                            label: isChirouonese ? '浏览量' : 'Views',
                            data: trendsData.views,
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return value.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });
            }
            
            function initProductPerformanceChart(productsData) {
                const ctx = document.getElementById('product-performance-chart').getContext('2d');
                const labels = productsData.slice(0, 5).map(p => p.name.substring(0, 20) + '...');
                const data = productsData.slice(0, 5).map(p => p.views);
                
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data,
                            backgroundColor: [
                                '#3b82f6',
                                '#10b981',
                                '#f59e0b',
                                '#ef4444',
                                '#8b5cf6'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }
            
            function updateTrafficChart(metric) {
                const dataset = window.trafficChart.data.datasets[0];
                const trendsData = analyticsData.trends;
                
                switch(metric) {
                    case 'cart':
                        dataset.label = isChirouonese ? '购物车' : 'Cart Additions';
                        dataset.data = trendsData.cart_additions;
                        dataset.borderColor = '#10b981';
                        dataset.backgroundColor = 'rgba(16, 185, 129, 0.1)';
                        break;
                    case 'sales':
                        dataset.label = isChirouonese ? '销量' : 'Sales';
                        dataset.data = trendsData.sales;
                        dataset.borderColor = '#f59e0b';
                        dataset.backgroundColor = 'rgba(245, 158, 11, 0.1)';
                        break;
                    default:
                        dataset.label = isChirouonese ? '浏览量' : 'Views';
                        dataset.data = trendsData.views;
                        dataset.borderColor = '#3b82f6';
                        dataset.backgroundColor = 'rgba(59, 130, 246, 0.1)';
                }
                
                window.trafficChart.update();
            }
        });
        </script>
        <?php
    }

    /**
     * Get vendor analytics data
     */
    public static function get_vendor_analytics_data( $vendor_id, $start_date, $end_date ) {
        global $wpdb;
        
        $previous_start = date( 'Y-m-d', strtotime( $start_date . ' - ' . self::get_date_diff( $start_date, $end_date ) . ' days' ) );
        $previous_end = date( 'Y-m-d', strtotime( $start_date . ' - 1 day' ) );
        
        // Get vendor products
        $vendor_products = $wpdb->get_col( $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}vss_product_uploads WHERE vendor_id = %d AND status = 'approved'",
            $vendor_id
        ) );
        
        if ( empty( $vendor_products ) ) {
            return self::get_empty_analytics_data();
        }
        
        $product_ids = implode( ',', array_map( 'intval', $vendor_products ) );
        
        // Current period stats
        $current_views = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}vss_product_views 
             WHERE product_id IN ($product_ids) 
             AND DATE(viewed_at) BETWEEN '$start_date' AND '$end_date'"
        );
        
        $current_cart = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}vss_cart_additions 
             WHERE product_id IN ($product_ids) 
             AND DATE(added_at) BETWEEN '$start_date' AND '$end_date'"
        );
        
        $current_sales = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}vss_purchase_tracking 
             WHERE product_id IN ($product_ids) 
             AND DATE(purchased_at) BETWEEN '$start_date' AND '$end_date'"
        );
        
        // Previous period stats
        $previous_views = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}vss_product_views 
             WHERE product_id IN ($product_ids) 
             AND DATE(viewed_at) BETWEEN '$previous_start' AND '$previous_end'"
        );
        
        $previous_cart = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}vss_cart_additions 
             WHERE product_id IN ($product_ids) 
             AND DATE(added_at) BETWEEN '$previous_start' AND '$previous_end'"
        );
        
        $previous_sales = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}vss_purchase_tracking 
             WHERE product_id IN ($product_ids) 
             AND DATE(purchased_at) BETWEEN '$previous_start' AND '$previous_end'"
        );
        
        // Calculate changes
        $views_change = $previous_views > 0 ? ( ( $current_views - $previous_views ) / $previous_views ) * 100 : 100;
        $cart_change = $previous_cart > 0 ? ( ( $current_cart - $previous_cart ) / $previous_cart ) * 100 : 100;
        $sales_change = $previous_sales > 0 ? ( ( $current_sales - $previous_sales ) / $previous_sales ) * 100 : 100;
        
        $conversion_rate = $current_views > 0 ? ( $current_sales / $current_views ) * 100 : 0;
        $previous_conversion_rate = $previous_views > 0 ? ( $previous_sales / $previous_views ) * 100 : 0;
        $conversion_change = $previous_conversion_rate > 0 ? ( ( $conversion_rate - $previous_conversion_rate ) / $previous_conversion_rate ) * 100 : 100;
        
        // Get trends data
        $trends = self::get_trends_data( $vendor_products, $start_date, $end_date );
        
        // Get top products
        $top_products = self::get_top_products( $vendor_products, $start_date, $end_date );
        
        // Get recent questions
        $recent_questions = self::get_recent_questions( $vendor_products );
        
        // Get customer insights
        $customer_insights = self::get_customer_insights( $vendor_products, $start_date, $end_date, $previous_start, $previous_end );
        
        return [
            'overview' => [
                'total_views' => (int) $current_views,
                'views_change' => round( $views_change, 1 ),
                'cart_additions' => (int) $current_cart,
                'cart_change' => round( $cart_change, 1 ),
                'purchases' => (int) $current_sales,
                'sales_change' => round( $sales_change, 1 ),
                'conversion_rate' => round( $conversion_rate, 2 ),
                'conversion_change' => round( $conversion_change, 2 )
            ],
            'trends' => $trends,
            'top_products' => $top_products,
            'recent_questions' => $recent_questions,
            'customers' => $customer_insights
        ];
    }

    /**
     * Track product view
     */
    public static function track_product_view() {
        if ( ! isset( $_POST['product_id'] ) ) {
            wp_send_json_error( 'Missing product ID' );
        }
        
        $product_id = intval( $_POST['product_id'] );
        $user_id = get_current_user_id();
        $session_id = session_id() ?: wp_generate_password( 32, false );
        $ip_address = self::get_client_ip();
        $user_agent = sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ?? '' );
        $referrer = sanitize_url( $_SERVER['HTTP_REFERER'] ?? '' );
        
        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . 'vss_product_views',
            [
                'product_id' => $product_id,
                'user_id' => $user_id ?: null,
                'session_id' => $session_id,
                'ip_address' => $ip_address,
                'user_agent' => $user_agent,
                'referrer' => $referrer,
                'viewed_at' => current_time( 'mysql' )
            ]
        );
        
        wp_send_json_success( 'View tracked' );
    }

    /**
     * Track cart addition
     */
    public static function track_cart_addition( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
        $user_id = get_current_user_id();
        $session_id = session_id() ?: wp_generate_password( 32, false );
        
        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . 'vss_cart_additions',
            [
                'product_id' => $product_id,
                'user_id' => $user_id ?: null,
                'session_id' => $session_id,
                'quantity' => $quantity,
                'added_at' => current_time( 'mysql' )
            ]
        );
    }

    /**
     * Track purchase
     */
    public static function track_purchase( $order_id ) {
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }
        
        $user_id = $order->get_user_id();
        
        foreach ( $order->get_items() as $item ) {
            $product_id = $item->get_product_id();
            
            global $wpdb;
            $wpdb->insert(
                $wpdb->prefix . 'vss_purchase_tracking',
                [
                    'product_id' => $product_id,
                    'order_id' => $order_id,
                    'user_id' => $user_id ?: null,
                    'quantity' => $item->get_quantity(),
                    'unit_price' => $item->get_subtotal() / $item->get_quantity(),
                    'total_price' => $item->get_subtotal(),
                    'purchased_at' => $order->get_date_created()->date( 'Y-m-d H:i:s' )
                ]
            );
        }
    }

    /**
     * Get client IP address
     */
    private static function get_client_ip() {
        $ip_keys = [ 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR' ];
        
        foreach ( $ip_keys as $key ) {
            if ( ! empty( $_SERVER[ $key ] ) ) {
                $ip = sanitize_text_field( $_SERVER[ $key ] );
                if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
                    return $ip;
                }
            }
        }
        
        return '127.0.0.1';
    }

    /**
     * Get date range start
     */
    private static function get_date_range_start( $range ) {
        switch ( $range ) {
            case 'today':
                return date( 'Y-m-d' );
            case 'yesterday':
                return date( 'Y-m-d', strtotime( '-1 day' ) );
            case 'last_7_days':
                return date( 'Y-m-d', strtotime( '-7 days' ) );
            case 'last_30_days':
                return date( 'Y-m-d', strtotime( '-30 days' ) );
            case 'last_90_days':
                return date( 'Y-m-d', strtotime( '-90 days' ) );
            case 'this_month':
                return date( 'Y-m-01' );
            case 'last_month':
                return date( 'Y-m-01', strtotime( '-1 month' ) );
            case 'this_year':
                return date( 'Y-01-01' );
            default:
                return date( 'Y-m-d', strtotime( '-30 days' ) );
        }
    }

    /**
     * Get date difference in days
     */
    private static function get_date_diff( $start_date, $end_date ) {
        $start = new DateTime( $start_date );
        $end = new DateTime( $end_date );
        return $start->diff( $end )->days;
    }

    /**
     * Get empty analytics data structure
     */
    private static function get_empty_analytics_data() {
        return [
            'overview' => [
                'total_views' => 0,
                'views_change' => 0,
                'cart_additions' => 0,
                'cart_change' => 0,
                'purchases' => 0,
                'sales_change' => 0,
                'conversion_rate' => 0,
                'conversion_change' => 0
            ],
            'trends' => [
                'labels' => [],
                'views' => [],
                'cart_additions' => [],
                'sales' => []
            ],
            'top_products' => [],
            'recent_questions' => [],
            'customers' => [
                'unique_visitors' => 0,
                'unique_visitors_prev' => 0,
                'visitors_change' => 0,
                'returning_customers' => 0,
                'returning_customers_prev' => 0,
                'returning_change' => 0,
                'avg_session_duration' => 0,
                'avg_session_duration_prev' => 0,
                'duration_change' => 0
            ]
        ];
    }

    /**
     * Aggregate daily analytics
     */
    public static function aggregate_daily_analytics() {
        global $wpdb;
        
        $yesterday = date( 'Y-m-d', strtotime( '-1 day' ) );
        
        // Get all vendors with products
        $vendors = $wpdb->get_col(
            "SELECT DISTINCT vendor_id FROM {$wpdb->prefix}vss_product_uploads WHERE status = 'approved'"
        );
        
        foreach ( $vendors as $vendor_id ) {
            $vendor_products = $wpdb->get_col( $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}vss_product_uploads WHERE vendor_id = %d AND status = 'approved'",
                $vendor_id
            ) );
            
            if ( empty( $vendor_products ) ) {
                continue;
            }
            
            $product_ids = implode( ',', array_map( 'intval', $vendor_products ) );
            
            // Calculate daily metrics
            $total_views = $wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->prefix}vss_product_views 
                 WHERE product_id IN ($product_ids) AND DATE(viewed_at) = '$yesterday'"
            );
            
            $unique_views = $wpdb->get_var(
                "SELECT COUNT(DISTINCT session_id) FROM {$wpdb->prefix}vss_product_views 
                 WHERE product_id IN ($product_ids) AND DATE(viewed_at) = '$yesterday'"
            );
            
            $cart_additions = $wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->prefix}vss_cart_additions 
                 WHERE product_id IN ($product_ids) AND DATE(added_at) = '$yesterday'"
            );
            
            $purchases = $wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->prefix}vss_purchase_tracking 
                 WHERE product_id IN ($product_ids) AND DATE(purchased_at) = '$yesterday'"
            );
            
            $revenue = $wpdb->get_var(
                "SELECT SUM(total_price) FROM {$wpdb->prefix}vss_purchase_tracking 
                 WHERE product_id IN ($product_ids) AND DATE(purchased_at) = '$yesterday'"
            );
            
            $questions_received = $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}vss_product_questions pq
                 JOIN {$wpdb->prefix}vss_product_uploads pu ON pq.product_id = pu.id
                 WHERE pu.vendor_id = %d AND DATE(pq.created_at) = %s",
                $vendor_id, $yesterday
            ) );
            
            $questions_answered = $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}vss_product_questions pq
                 JOIN {$wpdb->prefix}vss_product_uploads pu ON pq.product_id = pu.id
                 WHERE pu.vendor_id = %d AND DATE(pq.answered_at) = %s",
                $vendor_id, $yesterday
            ) );
            
            $conversion_rate = $total_views > 0 ? ( $purchases / $total_views ) : 0;
            
            // Insert/update performance record
            $wpdb->replace(
                $wpdb->prefix . 'vss_vendor_performance',
                [
                    'vendor_id' => $vendor_id,
                    'date_recorded' => $yesterday,
                    'total_views' => $total_views,
                    'unique_views' => $unique_views,
                    'cart_additions' => $cart_additions,
                    'purchases' => $purchases,
                    'revenue' => $revenue ?: 0,
                    'questions_received' => $questions_received,
                    'questions_answered' => $questions_answered,
                    'conversion_rate' => $conversion_rate
                ]
            );
        }
    }
}