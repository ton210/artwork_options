<?php
/**
 * VSS Vendor Reports Module
 * 
 * Comprehensive reporting system for vendors
 * 
 * @package VendorOrderManager
 * @subpackage Modules
 * @since 7.0.2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Trait for Reports functionality
 */
trait VSS_Vendor_Reports {

    /**
     * Render reports page
     */
    private static function render_reports_page() {
        $vendor_id = get_current_user_id();
        $report_type = isset( $_GET['report_type'] ) ? sanitize_key( $_GET['report_type'] ) : 'overview';
        $date_range = isset( $_GET['date_range'] ) ? sanitize_key( $_GET['date_range'] ) : 'last_30_days';
        $custom_start = isset( $_GET['start_date'] ) ? sanitize_text_field( $_GET['start_date'] ) : '';
        $custom_end = isset( $_GET['end_date'] ) ? sanitize_text_field( $_GET['end_date'] ) : '';
        
        // Get analytics data
        $analytics = self::get_vendor_analytics( $vendor_id, $date_range );
        ?>
        
        <div class="vss-reports-page">
            <!-- Reports Header -->
            <div class="vss-reports-header">
                <h1><?php esc_html_e( 'Business Reports', 'vss' ); ?></h1>
                <div class="vss-reports-controls">
                    <select id="vss-report-type" class="vss-select">
                        <option value="overview" <?php selected( $report_type, 'overview' ); ?>><?php esc_html_e( 'Overview Report', 'vss' ); ?></option>
                        <option value="sales" <?php selected( $report_type, 'sales' ); ?>><?php esc_html_e( 'Sales Report', 'vss' ); ?></option>
                        <option value="products" <?php selected( $report_type, 'products' ); ?>><?php esc_html_e( 'Product Performance', 'vss' ); ?></option>
                        <option value="customers" <?php selected( $report_type, 'customers' ); ?>><?php esc_html_e( 'Customer Analysis', 'vss' ); ?></option>
                        <option value="financial" <?php selected( $report_type, 'financial' ); ?>><?php esc_html_e( 'Financial Summary', 'vss' ); ?></option>
                        <option value="shipping" <?php selected( $report_type, 'shipping' ); ?>><?php esc_html_e( 'Shipping Performance', 'vss' ); ?></option>
                    </select>
                    
                    <select id="vss-date-range" class="vss-select">
                        <option value="today" <?php selected( $date_range, 'today' ); ?>><?php esc_html_e( 'Today', 'vss' ); ?></option>
                        <option value="yesterday" <?php selected( $date_range, 'yesterday' ); ?>><?php esc_html_e( 'Yesterday', 'vss' ); ?></option>
                        <option value="last_7_days" <?php selected( $date_range, 'last_7_days' ); ?>><?php esc_html_e( 'Last 7 Days', 'vss' ); ?></option>
                        <option value="last_30_days" <?php selected( $date_range, 'last_30_days' ); ?>><?php esc_html_e( 'Last 30 Days', 'vss' ); ?></option>
                        <option value="last_90_days" <?php selected( $date_range, 'last_90_days' ); ?>><?php esc_html_e( 'Last 90 Days', 'vss' ); ?></option>
                        <option value="this_month" <?php selected( $date_range, 'this_month' ); ?>><?php esc_html_e( 'This Month', 'vss' ); ?></option>
                        <option value="last_month" <?php selected( $date_range, 'last_month' ); ?>><?php esc_html_e( 'Last Month', 'vss' ); ?></option>
                        <option value="this_year" <?php selected( $date_range, 'this_year' ); ?>><?php esc_html_e( 'This Year', 'vss' ); ?></option>
                        <option value="custom" <?php selected( $date_range, 'custom' ); ?>><?php esc_html_e( 'Custom Range', 'vss' ); ?></option>
                    </select>
                    
                    <div class="vss-custom-dates" style="<?php echo $date_range === 'custom' ? '' : 'display:none;'; ?>">
                        <input type="date" id="vss-start-date" value="<?php echo esc_attr( $custom_start ); ?>" placeholder="<?php esc_attr_e( 'Start Date', 'vss' ); ?>">
                        <span>—</span>
                        <input type="date" id="vss-end-date" value="<?php echo esc_attr( $custom_end ); ?>" placeholder="<?php esc_attr_e( 'End Date', 'vss' ); ?>">
                    </div>
                    
                    <button id="vss-generate-report" class="button button-primary">
                        <span class="dashicons dashicons-update"></span>
                        <?php esc_html_e( 'Generate Report', 'vss' ); ?>
                    </button>
                    
                    <div class="vss-export-buttons">
                        <button class="button vss-export" data-format="csv">
                            <span class="dashicons dashicons-media-spreadsheet"></span>
                            <?php esc_html_e( 'CSV', 'vss' ); ?>
                        </button>
                        <button class="button vss-export" data-format="pdf">
                            <span class="dashicons dashicons-pdf"></span>
                            <?php esc_html_e( 'PDF', 'vss' ); ?>
                        </button>
                        <button class="button vss-export" data-format="excel">
                            <span class="dashicons dashicons-media-document"></span>
                            <?php esc_html_e( 'Excel', 'vss' ); ?>
                        </button>
                        <button class="button vss-print">
                            <span class="dashicons dashicons-printer"></span>
                            <?php esc_html_e( 'Print', 'vss' ); ?>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Report Content -->
            <div class="vss-report-content" id="vss-report-content">
                <?php
                switch ( $report_type ) {
                    case 'overview':
                        self::render_overview_report( $analytics, $vendor_id );
                        break;
                    case 'sales':
                        self::render_sales_report( $analytics, $vendor_id );
                        break;
                    case 'products':
                        self::render_products_report( $analytics, $vendor_id );
                        break;
                    case 'customers':
                        self::render_customers_report( $analytics, $vendor_id );
                        break;
                    case 'financial':
                        self::render_financial_report( $analytics, $vendor_id );
                        break;
                    case 'shipping':
                        self::render_shipping_report( $analytics, $vendor_id );
                        break;
                }
                ?>
            </div>
            
            <!-- Report Footer -->
            <div class="vss-report-footer">
                <p class="report-generated">
                    <?php printf( 
                        __( 'Report generated on %s at %s', 'vss' ), 
                        date_i18n( get_option( 'date_format' ) ),
                        date_i18n( get_option( 'time_format' ) )
                    ); ?>
                </p>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Report type and date range change
            $('#vss-generate-report').on('click', function() {
                var reportType = $('#vss-report-type').val();
                var dateRange = $('#vss-date-range').val();
                var url = '<?php echo esc_url( add_query_arg( 'vss_action', 'reports', get_permalink() ) ); ?>';
                url += '&report_type=' + reportType + '&date_range=' + dateRange;
                
                if (dateRange === 'custom') {
                    url += '&start_date=' + $('#vss-start-date').val();
                    url += '&end_date=' + $('#vss-end-date').val();
                }
                
                window.location.href = url;
            });
            
            // Custom date range toggle
            $('#vss-date-range').on('change', function() {
                if ($(this).val() === 'custom') {
                    $('.vss-custom-dates').show();
                } else {
                    $('.vss-custom-dates').hide();
                }
            });
            
            // Export functionality
            $('.vss-export').on('click', function() {
                var format = $(this).data('format');
                var reportType = $('#vss-report-type').val();
                var dateRange = $('#vss-date-range').val();
                
                var exportUrl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
                exportUrl += '?action=vss_export_report';
                exportUrl += '&format=' + format;
                exportUrl += '&report_type=' + reportType;
                exportUrl += '&date_range=' + dateRange;
                exportUrl += '&vendor_id=<?php echo $vendor_id; ?>';
                exportUrl += '&nonce=<?php echo wp_create_nonce( 'vss_export_report' ); ?>';
                
                window.location.href = exportUrl;
            });
            
            // Print functionality
            $('.vss-print').on('click', function() {
                window.print();
            });
            
            // Initialize charts if needed
            if (typeof Chart !== 'undefined') {
                // Initialize any charts in the report
                $('.vss-chart').each(function() {
                    var chartId = $(this).attr('id');
                    var chartType = $(this).data('type');
                    var chartData = $(this).data('chart');
                    
                    if (chartData) {
                        new Chart(document.getElementById(chartId), {
                            type: chartType,
                            data: chartData,
                            options: {
                                responsive: true,
                                maintainAspectRatio: false
                            }
                        });
                    }
                });
            }
        });
        </script>
        <?php
    }
    
    /**
     * Render overview report
     */
    private static function render_overview_report( $analytics, $vendor_id ) {
        ?>
        <div class="vss-report-overview">
            <h2><?php esc_html_e( 'Business Overview Report', 'vss' ); ?></h2>
            
            <!-- Key Metrics -->
            <div class="vss-report-metrics">
                <div class="metric-box">
                    <h4><?php esc_html_e( 'Total Revenue', 'vss' ); ?></h4>
                    <p class="metric-value"><?php echo wc_price( $analytics['overview']['total_revenue'] ); ?></p>
                    <span class="metric-change <?php echo $analytics['overview']['revenue_growth'] >= 0 ? 'positive' : 'negative'; ?>">
                        <?php echo $analytics['overview']['revenue_growth'] >= 0 ? '↑' : '↓'; ?> 
                        <?php echo number_format( abs( $analytics['overview']['revenue_growth'] ), 1 ); ?>%
                    </span>
                </div>
                
                <div class="metric-box">
                    <h4><?php esc_html_e( 'Total Orders', 'vss' ); ?></h4>
                    <p class="metric-value"><?php echo number_format( $analytics['overview']['total_orders'] ); ?></p>
                    <span class="metric-change <?php echo $analytics['overview']['orders_growth'] >= 0 ? 'positive' : 'negative'; ?>">
                        <?php echo $analytics['overview']['orders_growth'] >= 0 ? '↑' : '↓'; ?> 
                        <?php echo number_format( abs( $analytics['overview']['orders_growth'] ), 1 ); ?>%
                    </span>
                </div>
                
                <div class="metric-box">
                    <h4><?php esc_html_e( 'Net Profit', 'vss' ); ?></h4>
                    <p class="metric-value"><?php echo wc_price( $analytics['overview']['total_profit'] ); ?></p>
                    <span class="metric-subtitle">
                        <?php 
                        $margin = $analytics['overview']['total_revenue'] > 0 ? 
                            ( $analytics['overview']['total_profit'] / $analytics['overview']['total_revenue'] ) * 100 : 0;
                        echo number_format( $margin, 1 ) . '% ' . __( 'margin', 'vss' );
                        ?>
                    </span>
                </div>
                
                <div class="metric-box">
                    <h4><?php esc_html_e( 'Avg Order Value', 'vss' ); ?></h4>
                    <p class="metric-value"><?php echo wc_price( $analytics['overview']['average_order_value'] ); ?></p>
                </div>
            </div>
            
            <!-- Performance Indicators -->
            <div class="vss-report-section">
                <h3><?php esc_html_e( 'Performance Indicators', 'vss' ); ?></h3>
                <table class="vss-report-table">
                    <tbody>
                        <tr>
                            <td><?php esc_html_e( 'Completion Rate', 'vss' ); ?></td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $analytics['overview']['completion_rate']; ?>%"></div>
                                </div>
                            </td>
                            <td><strong><?php echo number_format( $analytics['overview']['completion_rate'], 1 ); ?>%</strong></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e( 'On-Time Delivery', 'vss' ); ?></td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $analytics['overview']['on_time_delivery_rate']; ?>%"></div>
                                </div>
                            </td>
                            <td><strong><?php echo number_format( $analytics['overview']['on_time_delivery_rate'], 1 ); ?>%</strong></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e( 'Quality Score', 'vss' ); ?></td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $analytics['performance']['quality_score']; ?>%"></div>
                                </div>
                            </td>
                            <td><strong><?php echo number_format( $analytics['performance']['quality_score'], 1 ); ?>%</strong></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e( 'Customer Satisfaction', 'vss' ); ?></td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $analytics['performance']['customer_satisfaction']; ?>%"></div>
                                </div>
                            </td>
                            <td><strong><?php echo number_format( $analytics['performance']['customer_satisfaction'], 1 ); ?>%</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Revenue Trend Chart -->
            <div class="vss-report-section">
                <h3><?php esc_html_e( 'Revenue Trend', 'vss' ); ?></h3>
                <canvas id="overview-revenue-chart" class="vss-chart" data-type="line" 
                        data-chart='<?php echo json_encode([
                            'labels' => array_keys( $analytics['trends']['revenue'] ),
                            'datasets' => [[
                                'label' => __( 'Revenue', 'vss' ),
                                'data' => array_values( $analytics['trends']['revenue'] ),
                                'borderColor' => '#2271b1',
                                'backgroundColor' => 'rgba(34, 113, 177, 0.1)',
                            ]]
                        ]); ?>'></canvas>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render sales report
     */
    private static function render_sales_report( $analytics, $vendor_id ) {
        ?>
        <div class="vss-report-sales">
            <h2><?php esc_html_e( 'Sales Report', 'vss' ); ?></h2>
            
            <!-- Sales by Status -->
            <div class="vss-report-section">
                <h3><?php esc_html_e( 'Orders by Status', 'vss' ); ?></h3>
                <table class="vss-report-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Status', 'vss' ); ?></th>
                            <th><?php esc_html_e( 'Orders', 'vss' ); ?></th>
                            <th><?php esc_html_e( 'Revenue', 'vss' ); ?></th>
                            <th><?php esc_html_e( 'Percentage', 'vss' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total_orders = array_sum( array_column( $analytics['sales']['by_status'], 'count' ) );
                        foreach ( $analytics['sales']['by_status'] as $status => $data ) : 
                            $percentage = $total_orders > 0 ? ( $data['count'] / $total_orders ) * 100 : 0;
                        ?>
                        <tr>
                            <td>
                                <span class="status-badge status-<?php echo esc_attr( $status ); ?>">
                                    <?php echo esc_html( ucfirst( $status ) ); ?>
                                </span>
                            </td>
                            <td><?php echo number_format( $data['count'] ); ?></td>
                            <td><?php echo wc_price( $data['revenue'] ); ?></td>
                            <td><?php echo number_format( $percentage, 1 ); ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Daily Sales Chart -->
            <div class="vss-report-section">
                <h3><?php esc_html_e( 'Daily Sales Trend', 'vss' ); ?></h3>
                <canvas id="daily-sales-chart" class="vss-chart" data-type="bar" 
                        data-chart='<?php echo json_encode([
                            'labels' => array_keys( $analytics['sales']['by_day'] ),
                            'datasets' => [[
                                'label' => __( 'Revenue', 'vss' ),
                                'data' => array_column( $analytics['sales']['by_day'], 'revenue' ),
                                'backgroundColor' => '#2271b1',
                            ]]
                        ]); ?>'></canvas>
            </div>
            
            <!-- Peak Hours -->
            <div class="vss-report-section">
                <h3><?php esc_html_e( 'Order Distribution by Hour', 'vss' ); ?></h3>
                <div class="hour-distribution">
                    <?php 
                    $max_orders = max( $analytics['sales']['peak_hours'] );
                    for ( $hour = 0; $hour < 24; $hour++ ) :
                        $hour_key = sprintf( '%02d:00', $hour );
                        $orders = isset( $analytics['sales']['peak_hours'][$hour_key] ) ? 
                                 $analytics['sales']['peak_hours'][$hour_key] : 0;
                        $height = $max_orders > 0 ? ( $orders / $max_orders ) * 100 : 0;
                    ?>
                    <div class="hour-bar" title="<?php echo esc_attr( $hour_key . ': ' . $orders . ' orders' ); ?>">
                        <div class="bar-fill" style="height: <?php echo $height; ?>%"></div>
                        <span class="hour-label"><?php echo $hour; ?></span>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render products report
     */
    private static function render_products_report( $analytics, $vendor_id ) {
        ?>
        <div class="vss-report-products">
            <h2><?php esc_html_e( 'Product Performance Report', 'vss' ); ?></h2>
            
            <!-- Product Stats -->
            <div class="vss-report-metrics">
                <div class="metric-box">
                    <h4><?php esc_html_e( 'Total Items Sold', 'vss' ); ?></h4>
                    <p class="metric-value"><?php echo number_format( $analytics['products']['total_items_sold'] ); ?></p>
                </div>
                <div class="metric-box">
                    <h4><?php esc_html_e( 'Unique Products', 'vss' ); ?></h4>
                    <p class="metric-value"><?php echo number_format( $analytics['products']['unique_products'] ); ?></p>
                </div>
            </div>
            
            <!-- Top Products Table -->
            <div class="vss-report-section">
                <h3><?php esc_html_e( 'Top Performing Products', 'vss' ); ?></h3>
                <table class="vss-report-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Product', 'vss' ); ?></th>
                            <th><?php esc_html_e( 'Quantity Sold', 'vss' ); ?></th>
                            <th><?php esc_html_e( 'Revenue', 'vss' ); ?></th>
                            <th><?php esc_html_e( 'Avg Price', 'vss' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $analytics['products']['top_products'] as $product_id => $product ) : 
                            $avg_price = $product['quantity'] > 0 ? $product['revenue'] / $product['quantity'] : 0;
                        ?>
                        <tr>
                            <td><?php echo esc_html( $product['name'] ); ?></td>
                            <td><?php echo number_format( $product['quantity'] ); ?></td>
                            <td><?php echo wc_price( $product['revenue'] ); ?></td>
                            <td><?php echo wc_price( $avg_price ); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Category Performance -->
            <?php if ( ! empty( $analytics['products']['product_categories'] ) ) : ?>
            <div class="vss-report-section">
                <h3><?php esc_html_e( 'Sales by Category', 'vss' ); ?></h3>
                <canvas id="category-chart" class="vss-chart" data-type="pie" 
                        data-chart='<?php echo json_encode([
                            'labels' => array_keys( $analytics['products']['product_categories'] ),
                            'datasets' => [[
                                'data' => array_values( $analytics['products']['product_categories'] ),
                                'backgroundColor' => [
                                    '#2271b1', '#10b981', '#f59e0b', '#ef4444', 
                                    '#8b5cf6', '#ec4899', '#14b8a6', '#f97316'
                                ]
                            ]]
                        ]); ?>'></canvas>
            </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Render customers report
     */
    private static function render_customers_report( $analytics, $vendor_id ) {
        ?>
        <div class="vss-report-customers">
            <h2><?php esc_html_e( 'Customer Analysis Report', 'vss' ); ?></h2>
            
            <!-- Customer Stats -->
            <div class="vss-report-metrics">
                <div class="metric-box">
                    <h4><?php esc_html_e( 'Total Customers', 'vss' ); ?></h4>
                    <p class="metric-value"><?php echo number_format( $analytics['customers']['total_customers'] ); ?></p>
                </div>
                <div class="metric-box">
                    <h4><?php esc_html_e( 'New Customers', 'vss' ); ?></h4>
                    <p class="metric-value"><?php echo number_format( $analytics['customers']['new_customers'] ); ?></p>
                </div>
                <div class="metric-box">
                    <h4><?php esc_html_e( 'Returning', 'vss' ); ?></h4>
                    <p class="metric-value"><?php echo number_format( $analytics['customers']['returning_customers'] ); ?></p>
                </div>
                <div class="metric-box">
                    <h4><?php esc_html_e( 'Avg Lifetime Value', 'vss' ); ?></h4>
                    <p class="metric-value"><?php echo wc_price( $analytics['customers']['customer_lifetime_value'] ); ?></p>
                </div>
            </div>
            
            <!-- Top Customers -->
            <div class="vss-report-section">
                <h3><?php esc_html_e( 'Top Customers', 'vss' ); ?></h3>
                <table class="vss-report-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Customer', 'vss' ); ?></th>
                            <th><?php esc_html_e( 'Email', 'vss' ); ?></th>
                            <th><?php esc_html_e( 'Orders', 'vss' ); ?></th>
                            <th><?php esc_html_e( 'Total Spent', 'vss' ); ?></th>
                            <th><?php esc_html_e( 'Avg Order', 'vss' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $analytics['customers']['top_customers'] as $email => $customer ) : 
                            $avg_order = $customer['orders'] > 0 ? $customer['total_spent'] / $customer['orders'] : 0;
                        ?>
                        <tr>
                            <td><?php echo esc_html( $customer['name'] ); ?></td>
                            <td><?php echo esc_html( $email ); ?></td>
                            <td><?php echo number_format( $customer['orders'] ); ?></td>
                            <td><?php echo wc_price( $customer['total_spent'] ); ?></td>
                            <td><?php echo wc_price( $avg_order ); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Geographic Distribution -->
            <?php if ( ! empty( $analytics['customers']['customer_locations'] ) ) : ?>
            <div class="vss-report-section">
                <h3><?php esc_html_e( 'Geographic Distribution', 'vss' ); ?></h3>
                <table class="vss-report-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Country', 'vss' ); ?></th>
                            <th><?php esc_html_e( 'Orders', 'vss' ); ?></th>
                            <th><?php esc_html_e( 'Percentage', 'vss' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total_location_orders = array_sum( $analytics['customers']['customer_locations'] );
                        foreach ( $analytics['customers']['customer_locations'] as $country => $count ) : 
                            $percentage = $total_location_orders > 0 ? ( $count / $total_location_orders ) * 100 : 0;
                            $country_name = WC()->countries->get_countries()[$country] ?? $country;
                        ?>
                        <tr>
                            <td><?php echo esc_html( $country_name ); ?></td>
                            <td><?php echo number_format( $count ); ?></td>
                            <td><?php echo number_format( $percentage, 1 ); ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Render financial report
     */
    private static function render_financial_report( $analytics, $vendor_id ) {
        ?>
        <div class="vss-report-financial">
            <h2><?php esc_html_e( 'Financial Summary Report', 'vss' ); ?></h2>
            
            <!-- Financial Overview -->
            <div class="vss-report-section">
                <h3><?php esc_html_e( 'Financial Overview', 'vss' ); ?></h3>
                <table class="vss-report-table financial-summary">
                    <tbody>
                        <tr>
                            <td><?php esc_html_e( 'Gross Revenue', 'vss' ); ?></td>
                            <td class="amount"><?php echo wc_price( $analytics['overview']['total_revenue'] ); ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e( 'Total Costs', 'vss' ); ?></td>
                            <td class="amount">-<?php echo wc_price( $analytics['overview']['total_costs'] ); ?></td>
                        </tr>
                        <tr class="separator">
                            <td colspan="2"><hr></td>
                        </tr>
                        <tr class="total">
                            <td><strong><?php esc_html_e( 'Net Profit', 'vss' ); ?></strong></td>
                            <td class="amount"><strong><?php echo wc_price( $analytics['overview']['total_profit'] ); ?></strong></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e( 'Profit Margin', 'vss' ); ?></td>
                            <td class="amount">
                                <?php 
                                $margin = $analytics['overview']['total_revenue'] > 0 ? 
                                    ( $analytics['overview']['total_profit'] / $analytics['overview']['total_revenue'] ) * 100 : 0;
                                echo number_format( $margin, 2 ) . '%';
                                ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Profit Trend Chart -->
            <div class="vss-report-section">
                <h3><?php esc_html_e( 'Profit Trend', 'vss' ); ?></h3>
                <canvas id="profit-chart" class="vss-chart" data-type="line" 
                        data-chart='<?php echo json_encode([
                            'labels' => array_keys( $analytics['trends']['profit'] ),
                            'datasets' => [
                                [
                                    'label' => __( 'Revenue', 'vss' ),
                                    'data' => array_values( $analytics['trends']['revenue'] ),
                                    'borderColor' => '#2271b1',
                                    'backgroundColor' => 'rgba(34, 113, 177, 0.1)',
                                ],
                                [
                                    'label' => __( 'Costs', 'vss' ),
                                    'data' => array_values( $analytics['trends']['costs'] ),
                                    'borderColor' => '#ef4444',
                                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                                ],
                                [
                                    'label' => __( 'Profit', 'vss' ),
                                    'data' => array_values( $analytics['trends']['profit'] ),
                                    'borderColor' => '#10b981',
                                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                                ]
                            ]
                        ]); ?>'></canvas>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render shipping report
     */
    private static function render_shipping_report( $analytics, $vendor_id ) {
        ?>
        <div class="vss-report-shipping">
            <h2><?php esc_html_e( 'Shipping Performance Report', 'vss' ); ?></h2>
            
            <!-- Shipping Metrics -->
            <div class="vss-report-metrics">
                <div class="metric-box">
                    <h4><?php esc_html_e( 'On-Time Delivery', 'vss' ); ?></h4>
                    <p class="metric-value"><?php echo number_format( $analytics['overview']['on_time_delivery_rate'], 1 ); ?>%</p>
                </div>
                <div class="metric-box">
                    <h4><?php esc_html_e( 'Avg Processing Time', 'vss' ); ?></h4>
                    <p class="metric-value"><?php echo number_format( $analytics['performance']['average_processing_time'], 1 ); ?></p>
                    <span class="metric-unit"><?php esc_html_e( 'days', 'vss' ); ?></span>
                </div>
                <div class="metric-box">
                    <h4><?php esc_html_e( 'Late Orders', 'vss' ); ?></h4>
                    <p class="metric-value"><?php echo number_format( $analytics['performance']['late_orders_percentage'], 1 ); ?>%</p>
                </div>
            </div>
            
            <!-- Delivery Performance by Week -->
            <div class="vss-report-section">
                <h3><?php esc_html_e( 'Weekly Shipping Performance', 'vss' ); ?></h3>
                <p><?php esc_html_e( 'Track your shipping performance trends over time.', 'vss' ); ?></p>
                <!-- Additional shipping analytics can be added here -->
            </div>
        </div>
        <?php
    }
}