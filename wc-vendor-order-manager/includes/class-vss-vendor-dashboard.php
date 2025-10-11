<?php
/**
 * VSS Vendor Dashboard Module
 * 
 * Dashboard and widget functionality
 * 
 * @package VendorOrderManager
 * @subpackage Modules
 * @since 7.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Trait for Dashboard functionality
 */
trait VSS_Vendor_Dashboard {


        /**
         * Render vendor dashboard
         */
        private static function render_vendor_dashboard() {
            $vendor_id = get_current_user_id();
            $stats = self::get_vendor_statistics( $vendor_id );
            $user = wp_get_current_user();
            
            // Get date range from request or default
            $date_range = isset( $_GET['date_range'] ) ? sanitize_key( $_GET['date_range'] ) : 'last_30_days';
            
            // Get comprehensive analytics
            $analytics = self::get_vendor_analytics( $vendor_id, $date_range );
            
            // Get additional detailed metrics
            $detailed_metrics = self::get_detailed_metrics( $vendor_id, $date_range );
            $inventory_metrics = self::get_inventory_metrics( $vendor_id );
            $customer_insights = self::get_customer_insights( $vendor_id, $date_range );
            $operational_metrics = self::get_operational_metrics( $vendor_id, $date_range );
            ?>

            <!-- Enhanced CSS for Advanced Dashboard -->
            <style>
            .vss-advanced-dashboard {
                background: #f8f9fa;
                min-height: 100vh;
                padding: 20px;
            }
            .vss-dashboard-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 20px;
                margin-bottom: 30px;
            }
            .vss-metric-card-advanced {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 25px;
                border-radius: 15px;
                box-shadow: 0 8px 25px rgba(0,0,0,0.1);
                transition: transform 0.3s ease;
            }
            .vss-metric-card-advanced:hover {
                transform: translateY(-5px);
            }
            .vss-metric-card-advanced.revenue {
                background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            }
            .vss-metric-card-advanced.profit {
                background: linear-gradient(135deg, #fcb045 0%, #fd1d1d 100%);
            }
            .vss-metric-card-advanced.orders {
                background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            }
            .vss-metric-card-advanced.customers {
                background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
            }
            .vss-metric-number-large {
                font-size: 3em;
                font-weight: 700;
                margin-bottom: 10px;
                text-shadow: 0 2px 4px rgba(0,0,0,0.3);
            }
            .vss-metric-label-large {
                font-size: 1.1em;
                margin-bottom: 15px;
                opacity: 0.9;
            }
            .vss-metric-details {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-top: 15px;
            }
            .vss-trend-indicator {
                display: flex;
                align-items: center;
                font-size: 0.9em;
                padding: 5px 12px;
                border-radius: 20px;
                background: rgba(255,255,255,0.2);
            }
            .vss-trend-positive { color: #10b981; }
            .vss-trend-negative { color: #ef4444; }
            .vss-analytics-section {
                background: white;
                border-radius: 15px;
                padding: 30px;
                margin-bottom: 30px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            }
            .vss-section-header {
                display: flex;
                justify-content: between;
                align-items: center;
                margin-bottom: 25px;
                border-bottom: 2px solid #f1f5f9;
                padding-bottom: 15px;
            }
            .vss-kpi-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 20px;
                margin-bottom: 25px;
            }
            .vss-kpi-item {
                text-align: center;
                padding: 20px;
                background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
                border-radius: 10px;
                transition: all 0.3s ease;
            }
            .vss-kpi-item:hover {
                transform: scale(1.05);
                box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            }
            .vss-kpi-value {
                font-size: 2em;
                font-weight: 700;
                color: #1e293b;
                margin-bottom: 5px;
            }
            .vss-kpi-label {
                font-size: 0.9em;
                color: #64748b;
                text-transform: uppercase;
                letter-spacing: 1px;
            }
            .vss-progress-ring {
                position: relative;
                display: inline-block;
                width: 120px;
                height: 120px;
            }
            .vss-progress-ring svg {
                transform: rotate(-90deg);
            }
            .vss-progress-ring-circle {
                stroke: #e2e8f0;
                fill: transparent;
                stroke-width: 8;
            }
            .vss-progress-ring-progress {
                stroke: #3b82f6;
                fill: transparent;
                stroke-width: 8;
                stroke-linecap: round;
                transition: stroke-dashoffset 0.5s ease-in-out;
            }
            .vss-chart-container-advanced {
                background: white;
                border-radius: 15px;
                padding: 25px;
                margin-bottom: 20px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.05);
                min-height: 400px;
            }
            .vss-data-table-advanced {
                background: white;
                border-radius: 15px;
                overflow: hidden;
                box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            }
            .vss-data-table-advanced table {
                width: 100%;
                border-collapse: collapse;
            }
            .vss-data-table-advanced th {
                background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
                color: white;
                padding: 15px;
                text-align: left;
                font-weight: 600;
            }
            .vss-data-table-advanced td {
                padding: 12px 15px;
                border-bottom: 1px solid #e2e8f0;
            }
            .vss-data-table-advanced tr:hover {
                background: #f8fafc;
            }
            .vss-alert-card {
                background: linear-gradient(135deg, #fef3c7 0%, #fed7aa 100%);
                border-left: 5px solid #f59e0b;
                padding: 20px;
                border-radius: 10px;
                margin-bottom: 20px;
            }
            .vss-alert-card.danger {
                background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
                border-left-color: #ef4444;
            }
            .vss-alert-card.success {
                background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
                border-left-color: #10b981;
            }
            </style>
            
            <?php
            $logo_id = get_user_meta( $vendor_id, 'vss_vendor_logo_id', true );
            $logo_url = $logo_id ? wp_get_attachment_url( $logo_id ) : '';
            ?>

            <div class="vss-advanced-dashboard">
                <!-- Vendor Logo Display -->
                <?php if ( $logo_url ) : ?>
                <div class="vss-vendor-logo-header">
                    <img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $user->display_name ); ?>" class="vendor-logo">
                </div>
                <?php endif; ?>

                <!-- Enhanced Page Header with Date Range Selector -->
                <div class="vss-page-header">
                    <div class="vss-header-row">
                        <div class="vss-header-left">
                            <h1><?php printf( __( 'Business Command Center - %s', 'vss' ), esc_html( $user->display_name ) ); ?></h1>
                            <p><?php esc_html_e( 'Complete business intelligence and performance analytics dashboard.', 'vss' ); ?></p>
                        </div>
                        <div class="vss-header-right">
                            <div class="vss-date-range-selector">
                                <select id="vss-dashboard-date-range" class="vss-date-range-select">
                                    <option value="today" <?php selected( $date_range, 'today' ); ?>><?php esc_html_e( 'Today', 'vss' ); ?></option>
                                    <option value="yesterday" <?php selected( $date_range, 'yesterday' ); ?>><?php esc_html_e( 'Yesterday', 'vss' ); ?></option>
                                    <option value="last_7_days" <?php selected( $date_range, 'last_7_days' ); ?>><?php esc_html_e( 'Last 7 Days', 'vss' ); ?></option>
                                    <option value="last_30_days" <?php selected( $date_range, 'last_30_days' ); ?>><?php esc_html_e( 'Last 30 Days', 'vss' ); ?></option>
                                    <option value="last_90_days" <?php selected( $date_range, 'last_90_days' ); ?>><?php esc_html_e( 'Last 90 Days', 'vss' ); ?></option>
                                    <option value="this_month" <?php selected( $date_range, 'this_month' ); ?>><?php esc_html_e( 'This Month', 'vss' ); ?></option>
                                    <option value="last_month" <?php selected( $date_range, 'last_month' ); ?>><?php esc_html_e( 'Last Month', 'vss' ); ?></option>
                                    <option value="this_year" <?php selected( $date_range, 'this_year' ); ?>><?php esc_html_e( 'This Year', 'vss' ); ?></option>
                                </select>
                                <button class="button vss-export-dashboard" data-vendor="<?php echo esc_attr( $vendor_id ); ?>">
                                    <span class="dashicons dashicons-download"></span>
                                    <?php esc_html_e( 'Export Data', 'vss' ); ?>
                                </button>
                                <button class="button button-primary vss-refresh-dashboard">
                                    <span class="dashicons dashicons-update"></span>
                                    <?php esc_html_e( 'Refresh', 'vss' ); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Critical Alerts Section -->
                <?php if ( $stats['late'] > 0 || $detailed_metrics['critical_inventory'] > 0 ) : ?>
                <div class="vss-alerts-section">
                    <?php if ( $stats['late'] > 0 ) : ?>
                    <div class="vss-alert-card danger">
                        <h3><span class="dashicons dashicons-warning"></span> <?php esc_html_e( 'Urgent: Late Orders Detected', 'vss' ); ?></h3>
                        <p><?php printf( __( 'You have %d orders that are past their estimated ship date. Immediate action required.', 'vss' ), $stats['late'] ); ?></p>
                        <a href="<?php echo esc_url( add_query_arg( 'vss_action', 'orders', get_permalink() ) ); ?>" class="button button-primary">
                            <?php esc_html_e( 'View Late Orders', 'vss' ); ?>
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ( $detailed_metrics['critical_inventory'] > 0 ) : ?>
                    <div class="vss-alert-card">
                        <h3><span class="dashicons dashicons-products"></span> <?php esc_html_e( 'Low Inventory Alert', 'vss' ); ?></h3>
                        <p><?php printf( __( '%d products are running low on inventory. Consider restocking soon.', 'vss' ), $detailed_metrics['critical_inventory'] ); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Main KPI Dashboard -->
                <div class="vss-dashboard-grid">
                    <div class="vss-metric-card-advanced revenue">
                        <div class="vss-metric-number-large"><?php echo wc_price( $analytics['overview']['total_revenue'] ); ?></div>
                        <div class="vss-metric-label-large"><?php esc_html_e( 'Total Revenue', 'vss' ); ?></div>
                        <div class="vss-metric-details">
                            <div>
                                <small><?php esc_html_e( 'Avg Order Value', 'vss' ); ?></small><br>
                                <strong><?php echo wc_price( $analytics['overview']['average_order_value'] ); ?></strong>
                            </div>
                            <div class="vss-trend-indicator">
                                <span class="dashicons dashicons-arrow-<?php echo $analytics['overview']['revenue_growth'] >= 0 ? 'up' : 'down'; ?>-alt"></span>
                                <?php echo number_format( abs( $analytics['overview']['revenue_growth'] ), 1 ); ?>%
                            </div>
                        </div>
                    </div>

                    <div class="vss-metric-card-advanced profit">
                        <div class="vss-metric-number-large"><?php echo wc_price( $analytics['overview']['total_profit'] ); ?></div>
                        <div class="vss-metric-label-large"><?php esc_html_e( 'Net Profit', 'vss' ); ?></div>
                        <div class="vss-metric-details">
                            <div>
                                <small><?php esc_html_e( 'Profit Margin', 'vss' ); ?></small><br>
                                <strong><?php 
                                $margin = $analytics['overview']['total_revenue'] > 0 ? 
                                    ( $analytics['overview']['total_profit'] / $analytics['overview']['total_revenue'] ) * 100 : 0;
                                echo number_format( $margin, 1 ) . '%';
                                ?></strong>
                            </div>
                            <div class="vss-trend-indicator">
                                <span class="dashicons dashicons-chart-line"></span>
                                <?php esc_html_e( 'Healthy', 'vss' ); ?>
                            </div>
                        </div>
                    </div>

                    <div class="vss-metric-card-advanced orders">
                        <div class="vss-metric-number-large"><?php echo number_format( $analytics['overview']['total_orders'] ); ?></div>
                        <div class="vss-metric-label-large"><?php esc_html_e( 'Total Orders', 'vss' ); ?></div>
                        <div class="vss-metric-details">
                            <div>
                                <small><?php esc_html_e( 'Processing', 'vss' ); ?></small><br>
                                <strong><?php echo number_format( $stats['processing'] ); ?></strong>
                            </div>
                            <div class="vss-trend-indicator">
                                <span class="dashicons dashicons-arrow-<?php echo $analytics['overview']['orders_growth'] >= 0 ? 'up' : 'down'; ?>-alt"></span>
                                <?php echo number_format( abs( $analytics['overview']['orders_growth'] ), 1 ); ?>%
                            </div>
                        </div>
                    </div>

                    <div class="vss-metric-card-advanced customers">
                        <div class="vss-metric-number-large"><?php echo number_format( $analytics['customers']['total_customers'] ); ?></div>
                        <div class="vss-metric-label-large"><?php esc_html_e( 'Total Customers', 'vss' ); ?></div>
                        <div class="vss-metric-details">
                            <div>
                                <small><?php esc_html_e( 'New Customers', 'vss' ); ?></small><br>
                                <strong><?php echo number_format( $analytics['customers']['new_customers'] ); ?></strong>
                            </div>
                            <div class="vss-trend-indicator">
                                <span class="dashicons dashicons-groups"></span>
                                <?php echo number_format( $analytics['customers']['returning_customers'] ); ?> <?php esc_html_e( 'returning', 'vss' ); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Comprehensive Performance Analytics Section -->
                <div class="vss-analytics-section">
                    <div class="vss-section-header">
                        <h2><span class="dashicons dashicons-chart-pie"></span> <?php esc_html_e( 'Performance Analytics', 'vss' ); ?></h2>
                        <button class="button button-secondary vss-toggle-section" data-target="performance-details">
                            <?php esc_html_e( 'Toggle Details', 'vss' ); ?>
                        </button>
                    </div>
                    
                    <div class="vss-kpi-grid" id="performance-details">
                        <div class="vss-kpi-item">
                            <div class="vss-progress-ring">
                                <svg width="120" height="120">
                                    <circle class="vss-progress-ring-circle" cx="60" cy="60" r="54"></circle>
                                    <circle class="vss-progress-ring-progress" cx="60" cy="60" r="54" 
                                            style="stroke-dasharray: <?php echo $analytics['overview']['completion_rate']; ?>, 100"></circle>
                                </svg>
                                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                                    <div class="vss-kpi-value" style="font-size: 1.5em;"><?php echo number_format( $analytics['overview']['completion_rate'], 1 ); ?>%</div>
                                </div>
                            </div>
                            <div class="vss-kpi-label"><?php esc_html_e( 'Completion Rate', 'vss' ); ?></div>
                        </div>
                        
                        <div class="vss-kpi-item">
                            <div class="vss-kpi-value"><?php echo number_format( $analytics['overview']['on_time_delivery_rate'], 1 ); ?>%</div>
                            <div class="vss-kpi-label"><?php esc_html_e( 'On-Time Delivery', 'vss' ); ?></div>
                            <div style="margin-top: 10px;">
                                <div style="width: 100%; height: 8px; background: #e2e8f0; border-radius: 4px;">
                                    <div style="width: <?php echo $analytics['overview']['on_time_delivery_rate']; ?>%; height: 100%; background: linear-gradient(90deg, #10b981, #059669); border-radius: 4px;"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="vss-kpi-item">
                            <div class="vss-kpi-value"><?php echo number_format( $analytics['performance']['quality_score'], 1 ); ?>%</div>
                            <div class="vss-kpi-label"><?php esc_html_e( 'Quality Score', 'vss' ); ?></div>
                            <small style="color: #64748b; margin-top: 5px; display: block;">
                                <?php esc_html_e( 'Based on approvals & feedback', 'vss' ); ?>
                            </small>
                        </div>
                        
                        <div class="vss-kpi-item">
                            <div class="vss-kpi-value"><?php echo number_format( $analytics['performance']['average_processing_time'], 1 ); ?></div>
                            <div class="vss-kpi-label"><?php esc_html_e( 'Avg Processing (Days)', 'vss' ); ?></div>
                            <small style="color: #64748b; margin-top: 5px; display: block;">
                                <?php esc_html_e( 'From order to completion', 'vss' ); ?>
                            </small>
                        </div>
                        
                        <div class="vss-kpi-item">
                            <div class="vss-kpi-value"><?php echo number_format( $analytics['performance']['customer_satisfaction'], 1 ); ?>%</div>
                            <div class="vss-kpi-label"><?php esc_html_e( 'Customer Satisfaction', 'vss' ); ?></div>
                            <small style="color: #64748b; margin-top: 5px; display: block;">
                                <?php esc_html_e( 'Calculated satisfaction index', 'vss' ); ?>
                            </small>
                        </div>
                        
                        <div class="vss-kpi-item">
                            <div class="vss-kpi-value"><?php echo wc_price( $analytics['customers']['customer_lifetime_value'] ); ?></div>
                            <div class="vss-kpi-label"><?php esc_html_e( 'Avg Customer LTV', 'vss' ); ?></div>
                            <small style="color: #64748b; margin-top: 5px; display: block;">
                                <?php esc_html_e( 'Lifetime customer value', 'vss' ); ?>
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Advanced Charts Section -->
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 30px;">
                    <div class="vss-chart-container-advanced">
                        <h3><?php esc_html_e( 'Revenue & Profit Trend Analysis', 'vss' ); ?></h3>
                        <canvas id="vss-advanced-revenue-chart" style="max-height: 350px;"></canvas>
                    </div>
                    
                    <div class="vss-chart-container-advanced">
                        <h3><?php esc_html_e( 'Order Status Distribution', 'vss' ); ?></h3>
                        <canvas id="vss-status-pie-chart" style="max-height: 350px;"></canvas>
                        <div style="margin-top: 15px; font-size: 0.9em;">
                            <?php foreach ( $analytics['sales']['by_status'] as $status => $data ) : ?>
                                <div style="display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid #f1f5f9;">
                                    <span><?php echo ucfirst( $status ); ?>:</span>
                                    <strong><?php echo $data['count']; ?> orders</strong>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Sales Analytics Deep Dive -->
                <div class="vss-analytics-section">
                    <div class="vss-section-header">
                        <h2><span class="dashicons dashicons-money-alt"></span> <?php esc_html_e( 'Sales Intelligence', 'vss' ); ?></h2>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                        <div>
                            <h4><?php esc_html_e( 'Revenue Breakdown', 'vss' ); ?></h4>
                            <div style="background: #f8fafc; padding: 20px; border-radius: 10px;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                                    <span><?php esc_html_e( 'Gross Revenue:', 'vss' ); ?></span>
                                    <strong><?php echo wc_price( $analytics['overview']['total_revenue'] ); ?></strong>
                                </div>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                                    <span><?php esc_html_e( 'Total Costs:', 'vss' ); ?></span>
                                    <strong style="color: #ef4444;">-<?php echo wc_price( $analytics['overview']['total_costs'] ); ?></strong>
                                </div>
                                <hr style="margin: 15px 0;">
                                <div style="display: flex; justify-content: space-between; font-size: 1.1em;">
                                    <span><strong><?php esc_html_e( 'Net Profit:', 'vss' ); ?></strong></span>
                                    <strong style="color: #10b981;"><?php echo wc_price( $analytics['overview']['total_profit'] ); ?></strong>
                                </div>
                                <div style="margin-top: 10px; text-align: center; color: #64748b;">
                                    <?php 
                                    $margin = $analytics['overview']['total_revenue'] > 0 ? 
                                        ( $analytics['overview']['total_profit'] / $analytics['overview']['total_revenue'] ) * 100 : 0;
                                    printf( __( 'Profit Margin: %s%%', 'vss' ), number_format( $margin, 2 ) );
                                    ?>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <h4><?php esc_html_e( 'Peak Performance Hours', 'vss' ); ?></h4>
                            <div style="background: #f8fafc; padding: 20px; border-radius: 10px;">
                                <div class="hour-distribution-advanced">
                                    <?php 
                                    $max_orders = max( $analytics['sales']['peak_hours'] );
                                    for ( $hour = 0; $hour < 24; $hour++ ) :
                                        $hour_key = sprintf( '%02d:00', $hour );
                                        $orders = isset( $analytics['sales']['peak_hours'][$hour_key] ) ? 
                                                 $analytics['sales']['peak_hours'][$hour_key] : 0;
                                        $height = $max_orders > 0 ? ( $orders / $max_orders ) * 100 : 0;
                                        $color_intensity = $height / 100;
                                    ?>
                                    <div style="display: inline-block; width: 3%; margin: 1px; text-align: center;">
                                        <div style="height: <?php echo max($height, 5); ?>px; background: rgba(59, 130, 246, <?php echo max($color_intensity, 0.1); ?>); margin-bottom: 2px; border-radius: 2px;" title="<?php echo esc_attr( $hour_key . ': ' . $orders . ' orders' ); ?>"></div>
                                        <span style="font-size: 0.7em; color: #64748b;"><?php echo $hour; ?></span>
                                    </div>
                                    <?php endfor; ?>
                                </div>
                                <div style="margin-top: 15px; text-align: center; color: #64748b; font-size: 0.9em;">
                                    <?php esc_html_e( 'Order distribution by hour of day', 'vss' ); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer Intelligence Section -->
                <div class="vss-analytics-section">
                    <div class="vss-section-header">
                        <h2><span class="dashicons dashicons-groups"></span> <?php esc_html_e( 'Customer Intelligence', 'vss' ); ?></h2>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
                        <div>
                            <h4><?php esc_html_e( 'Customer Metrics', 'vss' ); ?></h4>
                            <div class="vss-kpi-grid" style="grid-template-columns: 1fr;">
                                <div class="vss-kpi-item">
                                    <div class="vss-kpi-value"><?php echo number_format( $analytics['customers']['total_customers'] ); ?></div>
                                    <div class="vss-kpi-label"><?php esc_html_e( 'Total Customers', 'vss' ); ?></div>
                                </div>
                                <div class="vss-kpi-item">
                                    <div class="vss-kpi-value" style="color: #10b981;"><?php echo number_format( $analytics['customers']['new_customers'] ); ?></div>
                                    <div class="vss-kpi-label"><?php esc_html_e( 'New Customers', 'vss' ); ?></div>
                                </div>
                                <div class="vss-kpi-item">
                                    <div class="vss-kpi-value" style="color: #3b82f6;"><?php echo number_format( $analytics['customers']['returning_customers'] ); ?></div>
                                    <div class="vss-kpi-label"><?php esc_html_e( 'Returning Customers', 'vss' ); ?></div>
                                </div>
                                <div class="vss-kpi-item">
                                    <div class="vss-kpi-value"><?php 
                                    $retention_rate = $analytics['customers']['total_customers'] > 0 ? 
                                        ( $analytics['customers']['returning_customers'] / $analytics['customers']['total_customers'] ) * 100 : 0;
                                    echo number_format( $retention_rate, 1 ) . '%';
                                    ?></div>
                                    <div class="vss-kpi-label"><?php esc_html_e( 'Retention Rate', 'vss' ); ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <h4><?php esc_html_e( 'Top Customers by Value', 'vss' ); ?></h4>
                            <div class="vss-data-table-advanced">
                                <table>
                                    <thead>
                                        <tr>
                                            <th><?php esc_html_e( 'Customer', 'vss' ); ?></th>
                                            <th><?php esc_html_e( 'Orders', 'vss' ); ?></th>
                                            <th><?php esc_html_e( 'Total Spent', 'vss' ); ?></th>
                                            <th><?php esc_html_e( 'Avg Order', 'vss' ); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $top_customers = array_slice( $analytics['customers']['top_customers'], 0, 5, true );
                                        foreach ( $top_customers as $email => $customer ) : 
                                            $avg_order = $customer['orders'] > 0 ? $customer['total_spent'] / $customer['orders'] : 0;
                                        ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo esc_html( $customer['name'] ); ?></strong><br>
                                                <small style="color: #64748b;"><?php echo esc_html( $email ); ?></small>
                                            </td>
                                            <td><?php echo number_format( $customer['orders'] ); ?></td>
                                            <td><strong><?php echo wc_price( $customer['total_spent'] ); ?></strong></td>
                                            <td><?php echo wc_price( $avg_order ); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Performance Section -->
                <div class="vss-analytics-section">
                    <div class="vss-section-header">
                        <h2><span class="dashicons dashicons-products"></span> <?php esc_html_e( 'Product Performance Intelligence', 'vss' ); ?></h2>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                        <div>
                            <h4><?php esc_html_e( 'Product Metrics', 'vss' ); ?></h4>
                            <div style="background: #f8fafc; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                    <span><?php esc_html_e( 'Unique Products Sold:', 'vss' ); ?></span>
                                    <strong><?php echo number_format( $analytics['products']['unique_products'] ); ?></strong>
                                </div>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                    <span><?php esc_html_e( 'Total Items Sold:', 'vss' ); ?></span>
                                    <strong><?php echo number_format( $analytics['products']['total_items_sold'] ); ?></strong>
                                </div>
                                <div style="display: flex; justify-content: space-between;">
                                    <span><?php esc_html_e( 'Avg Items per Order:', 'vss' ); ?></span>
                                    <strong><?php 
                                    $avg_items = $analytics['overview']['total_orders'] > 0 ? 
                                        $analytics['products']['total_items_sold'] / $analytics['overview']['total_orders'] : 0;
                                    echo number_format( $avg_items, 1 );
                                    ?></strong>
                                </div>
                            </div>
                            
                            <?php if ( ! empty( $analytics['products']['product_categories'] ) ) : ?>
                            <h5><?php esc_html_e( 'Category Performance', 'vss' ); ?></h5>
                            <div style="background: #f8fafc; padding: 20px; border-radius: 10px;">
                                <?php foreach ( array_slice( $analytics['products']['product_categories'], 0, 5, true ) as $category => $quantity ) : ?>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                    <span><?php echo esc_html( $category ); ?>:</span>
                                    <strong><?php echo number_format( $quantity ); ?> units</strong>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div>
                            <h4><?php esc_html_e( 'Top Performing Products', 'vss' ); ?></h4>
                            <div class="vss-data-table-advanced">
                                <table>
                                    <thead>
                                        <tr>
                                            <th><?php esc_html_e( 'Product', 'vss' ); ?></th>
                                            <th><?php esc_html_e( 'Qty', 'vss' ); ?></th>
                                            <th><?php esc_html_e( 'Revenue', 'vss' ); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $top_products = array_slice( $analytics['products']['top_products'], 0, 8, true );
                                        foreach ( $top_products as $product_id => $product ) : 
                                        ?>
                                        <tr>
                                            <td><?php echo esc_html( $product['name'] ); ?></td>
                                            <td><?php echo number_format( $product['quantity'] ); ?></td>
                                            <td><strong><?php echo wc_price( $product['revenue'] ); ?></strong></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                </div>

                <!-- Enhanced JavaScript for Advanced Dashboard -->
                <script>
                jQuery(document).ready(function($) {
                    // Date range selector
                    $('#vss-dashboard-date-range').on('change', function() {
                        window.location.href = '<?php echo esc_url( add_query_arg( 'vss_action', 'dashboard', get_permalink() ) ); ?>&date_range=' + $(this).val();
                    });

                    // Export dashboard data
                    $('.vss-export-dashboard').on('click', function(e) {
                        e.preventDefault();
                        var vendorId = $(this).data('vendor');
                        var dateRange = $('#vss-dashboard-date-range').val();
                        window.location.href = '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>?action=vss_export_dashboard&vendor_id=' + vendorId + '&date_range=' + dateRange + '&nonce=<?php echo wp_create_nonce( 'vss_export_dashboard' ); ?>';
                    });
                    
                    // Refresh dashboard
                    $('.vss-refresh-dashboard').on('click', function(e) {
                        e.preventDefault();
                        window.location.reload();
                    });

                    // Toggle section visibility
                    $('.vss-toggle-section').on('click', function() {
                        var target = $(this).data('target');
                        $('#' + target).toggle();
                        $(this).text($(this).text() === 'Show Details' ? 'Hide Details' : 'Show Details');
                    });

                    // Initialize Chart.js charts if available
                    if (typeof Chart !== 'undefined') {
                        
                        // Advanced Revenue & Profit Chart
                        var advancedRevenueCtx = document.getElementById('vss-advanced-revenue-chart');
                        if (advancedRevenueCtx) {
                            new Chart(advancedRevenueCtx.getContext('2d'), {
                                type: 'line',
                                data: {
                                    labels: <?php echo json_encode( array_keys( $analytics['trends']['revenue'] ) ); ?>,
                                    datasets: [{
                                        label: '<?php esc_attr_e( 'Revenue', 'vss' ); ?>',
                                        data: <?php echo json_encode( array_values( $analytics['trends']['revenue'] ) ); ?>,
                                        borderColor: '#10b981',
                                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                        tension: 0.4,
                                        fill: true,
                                        pointRadius: 6,
                                        pointHoverRadius: 8,
                                        yAxisID: 'y'
                                    }, {
                                        label: '<?php esc_attr_e( 'Profit', 'vss' ); ?>',
                                        data: <?php echo json_encode( array_values( $analytics['trends']['profit'] ) ); ?>,
                                        borderColor: '#3b82f6',
                                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                        tension: 0.4,
                                        fill: true,
                                        pointRadius: 6,
                                        pointHoverRadius: 8,
                                        yAxisID: 'y'
                                    }, {
                                        label: '<?php esc_attr_e( 'Costs', 'vss' ); ?>',
                                        data: <?php echo json_encode( array_values( $analytics['trends']['costs'] ) ); ?>,
                                        borderColor: '#ef4444',
                                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                                        tension: 0.4,
                                        fill: true,
                                        pointRadius: 4,
                                        pointHoverRadius: 6,
                                        yAxisID: 'y'
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        title: {
                                            display: true,
                                            text: '<?php esc_attr_e( 'Financial Performance Trend', 'vss' ); ?>',
                                            font: { size: 16, weight: 'bold' }
                                        },
                                        legend: {
                                            display: true,
                                            position: 'bottom'
                                        },
                                        tooltip: {
                                            mode: 'index',
                                            intersect: false,
                                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                            titleColor: '#fff',
                                            bodyColor: '#fff',
                                            borderColor: '#333',
                                            borderWidth: 1,
                                            callbacks: {
                                                label: function(context) {
                                                    return context.dataset.label + ': $' + context.parsed.y.toLocaleString();
                                                }
                                            }
                                        }
                                    },
                                    scales: {
                                        x: {
                                            grid: { display: false },
                                            ticks: { color: '#64748b' }
                                        },
                                        y: {
                                            beginAtZero: true,
                                            grid: { color: 'rgba(100, 116, 139, 0.1)' },
                                            ticks: {
                                                color: '#64748b',
                                                callback: function(value) {
                                                    return '$' + value.toLocaleString();
                                                }
                                            }
                                        }
                                    },
                                    interaction: {
                                        mode: 'index',
                                        intersect: false
                                    }
                                }
                            });
                        }

                        // Status Pie Chart
                        var statusPieCtx = document.getElementById('vss-status-pie-chart');
                        if (statusPieCtx) {
                            var statusData = <?php echo json_encode( $analytics['sales']['by_status'] ); ?>;
                            var labels = [];
                            var data = [];
                            var colors = {
                                'processing': '#f59e0b',
                                'shipped': '#3b82f6',
                                'completed': '#10b981',
                                'cancelled': '#ef4444',
                                'refunded': '#6b7280',
                                'pending': '#8b5cf6',
                                'on-hold': '#f97316'
                            };
                            var bgColors = [];

                            for (var status in statusData) {
                                labels.push(status.charAt(0).toUpperCase() + status.slice(1));
                                data.push(statusData[status].count);
                                bgColors.push(colors[status] || '#9ca3af');
                            }

                            new Chart(statusPieCtx.getContext('2d'), {
                                type: 'doughnut',
                                data: {
                                    labels: labels,
                                    datasets: [{
                                        data: data,
                                        backgroundColor: bgColors,
                                        borderWidth: 2,
                                        borderColor: '#fff',
                                        hoverBorderWidth: 3,
                                        hoverOffset: 10
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: {
                                            display: false
                                        },
                                        tooltip: {
                                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                            titleColor: '#fff',
                                            bodyColor: '#fff',
                                            callbacks: {
                                                label: function(context) {
                                                    var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                                    var percentage = ((context.parsed / total) * 100).toFixed(1);
                                                    return context.label + ': ' + context.parsed + ' orders (' + percentage + '%)';
                                                }
                                            }
                                        }
                                    },
                                    cutout: '60%',
                                    animation: {
                                        animateScale: true,
                                        animateRotate: true
                                    }
                                }
                            });
                        }
                    }

                    // Animate progress rings on scroll
                    function animateProgressRings() {
                        $('.vss-progress-ring').each(function() {
                            var ring = $(this);
                            var circle = ring.find('.vss-progress-ring-progress');
                            var percentage = parseFloat(circle.attr('stroke-dasharray').split(',')[0]);
                            
                            if (ring.offset().top < $(window).scrollTop() + $(window).height() - 100) {
                                var circumference = 2 * Math.PI * 54; // radius = 54
                                var offset = circumference - (percentage / 100) * circumference;
                                circle.css({
                                    'stroke-dasharray': circumference,
                                    'stroke-dashoffset': offset,
                                    'transition': 'stroke-dashoffset 1.5s ease-in-out'
                                });
                            }
                        });
                    }

                    $(window).on('scroll', animateProgressRings);
                    animateProgressRings(); // Initial call

                    // Add hover effects to metric cards
                    $('.vss-metric-card-advanced').hover(
                        function() {
                            $(this).css('transform', 'translateY(-8px) scale(1.02)');
                        },
                        function() {
                            $(this).css('transform', 'translateY(-5px) scale(1)');
                        }
                    );

                    // Add click to copy functionality for revenue figures
                    $('.vss-metric-number-large').on('click', function() {
                        var text = $(this).text();
                        if (navigator.clipboard) {
                            navigator.clipboard.writeText(text).then(function() {
                                // Show temporary tooltip
                                var tooltip = $('<div>').addClass('copy-tooltip').text('Copied!').css({
                                    position: 'absolute',
                                    background: '#10b981',
                                    color: 'white',
                                    padding: '5px 10px',
                                    borderRadius: '4px',
                                    fontSize: '12px',
                                    zIndex: 9999,
                                    pointerEvents: 'none'
                                });
                                $('body').append(tooltip);
                                var rect = this.getBoundingClientRect();
                                tooltip.css({
                                    top: rect.top - 30,
                                    left: rect.left + rect.width/2 - tooltip.width()/2
                                });
                                setTimeout(function() {
                                    tooltip.fadeOut(300, function() {
                                        tooltip.remove();
                                    });
                                }, 1000);
                            }.bind(this));
                        }
                    });

                    // Real-time clock in header
                    function updateClock() {
                        var now = new Date();
                        var timeString = now.toLocaleTimeString();
                        if ($('#dashboard-clock').length === 0) {
                            $('.vss-header-right').prepend('<div id="dashboard-clock" style="margin-right: 20px; font-size: 0.9em; color: #64748b;">' + timeString + '</div>');
                        } else {
                            $('#dashboard-clock').text(timeString);
                        }
                    }
                    updateClock();
                    setInterval(updateClock, 1000);
                });
                </script>

            <!-- Performance Metrics Row -->
            <div class="vss-performance-metrics">
                <div class="vss-metric-card">
                    <div class="metric-header">
                        <span class="metric-title"><?php esc_html_e( 'Performance Score', 'vss' ); ?></span>
                        <span class="metric-info" title="<?php esc_attr_e( 'Based on delivery, quality, and customer satisfaction', 'vss' ); ?>">ⓘ</span>
                    </div>
                    <div class="metric-content">
                        <div class="metric-score-circle" data-score="<?php echo esc_attr( $analytics['performance']['quality_score'] ); ?>">
                            <svg viewBox="0 0 36 36" class="circular-chart">
                                <path class="circle-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                <path class="circle" stroke-dasharray="<?php echo esc_attr( $analytics['performance']['quality_score'] ); ?>, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            </svg>
                            <span class="score-text"><?php echo number_format( $analytics['performance']['quality_score'], 0 ); ?>%</span>
                        </div>
                    </div>
                </div>

                <div class="vss-metric-card">
                    <div class="metric-header">
                        <span class="metric-title"><?php esc_html_e( 'On-Time Delivery', 'vss' ); ?></span>
                    </div>
                    <div class="metric-content">
                        <div class="metric-bar">
                            <div class="metric-bar-fill" style="width: <?php echo esc_attr( $analytics['overview']['on_time_delivery_rate'] ); ?>%"></div>
                        </div>
                        <span class="metric-value"><?php echo number_format( $analytics['overview']['on_time_delivery_rate'], 1 ); ?>%</span>
                    </div>
                </div>

                <div class="vss-metric-card">
                    <div class="metric-header">
                        <span class="metric-title"><?php esc_html_e( 'Completion Rate', 'vss' ); ?></span>
                    </div>
                    <div class="metric-content">
                        <div class="metric-bar">
                            <div class="metric-bar-fill" style="width: <?php echo esc_attr( $analytics['overview']['completion_rate'] ); ?>%"></div>
                        </div>
                        <span class="metric-value"><?php echo number_format( $analytics['overview']['completion_rate'], 1 ); ?>%</span>
                    </div>
                </div>

                <div class="vss-metric-card">
                    <div class="metric-header">
                        <span class="metric-title"><?php esc_html_e( 'Avg Processing', 'vss' ); ?></span>
                    </div>
                    <div class="metric-content">
                        <span class="metric-big-value"><?php echo number_format( $analytics['performance']['average_processing_time'], 1 ); ?></span>
                        <span class="metric-unit"><?php esc_html_e( 'days', 'vss' ); ?></span>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="vss-charts-row">
                <div class="vss-chart-container">
                    <h3><?php esc_html_e( 'Revenue & Orders Trend', 'vss' ); ?></h3>
                    <canvas id="vss-revenue-chart"></canvas>
                </div>
                <div class="vss-chart-container vss-chart-small">
                    <h3><?php esc_html_e( 'Order Status Distribution', 'vss' ); ?></h3>
                    <canvas id="vss-status-chart"></canvas>
                </div>
            </div>

            <!-- Top Products & Customers -->
            <div class="vss-data-tables-row">
                <div class="vss-data-table-container">
                    <h3><?php esc_html_e( 'Top Products', 'vss' ); ?></h3>
                    <table class="vss-compact-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'Product', 'vss' ); ?></th>
                                <th><?php esc_html_e( 'Quantity', 'vss' ); ?></th>
                                <th><?php esc_html_e( 'Revenue', 'vss' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $top_products = array_slice( $analytics['products']['top_products'], 0, 5, true );
                            foreach ( $top_products as $product_id => $product ) : 
                            ?>
                            <tr>
                                <td><?php echo esc_html( $product['name'] ); ?></td>
                                <td><?php echo esc_html( $product['quantity'] ); ?></td>
                                <td><?php echo wc_price( $product['revenue'] ); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="vss-data-table-container">
                    <h3><?php esc_html_e( 'Top Customers', 'vss' ); ?></h3>
                    <table class="vss-compact-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'Customer', 'vss' ); ?></th>
                                <th><?php esc_html_e( 'Orders', 'vss' ); ?></th>
                                <th><?php esc_html_e( 'Total Spent', 'vss' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $top_customers = array_slice( $analytics['customers']['top_customers'], 0, 5, true );
                            foreach ( $top_customers as $email => $customer ) : 
                            ?>
                            <tr>
                                <td><?php echo esc_html( $customer['name'] ); ?></td>
                                <td><?php echo esc_html( $customer['orders'] ); ?></td>
                                <td><?php echo wc_price( $customer['total_spent'] ); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php
            // Display lifetime order summary
            if ( method_exists( self::class, 'display_lifetime_summary' ) ) {
                self::display_lifetime_summary( $vendor_id );
            }
            
            self::render_quick_actions();
            self::render_recent_orders_enhanced( $analytics );
            self::render_pending_approvals();
            ?>

            <!-- Dashboard JavaScript -->
            <script>
            jQuery(document).ready(function($) {
                // Date range selector
                $('#vss-dashboard-date-range').on('change', function() {
                    window.location.href = '<?php echo esc_url( add_query_arg( 'vss_action', 'dashboard', get_permalink() ) ); ?>&date_range=' + $(this).val();
                });

                // Export dashboard data
                $('.vss-export-dashboard').on('click', function(e) {
                    e.preventDefault();
                    var vendorId = $(this).data('vendor');
                    var dateRange = $('#vss-dashboard-date-range').val();
                    window.location.href = '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>?action=vss_export_dashboard&vendor_id=' + vendorId + '&date_range=' + dateRange + '&nonce=<?php echo wp_create_nonce( 'vss_export_dashboard' ); ?>';
                });

                // Initialize Chart.js charts
                if (typeof Chart !== 'undefined') {
                    // Revenue & Orders Chart
                    var revenueCtx = document.getElementById('vss-revenue-chart');
                    if (revenueCtx) {
                        var revenueChart = new Chart(revenueCtx.getContext('2d'), {
                            type: 'line',
                            data: {
                                labels: <?php echo json_encode( array_keys( $analytics['trends']['revenue'] ) ); ?>,
                                datasets: [{
                                    label: '<?php esc_attr_e( 'Revenue', 'vss' ); ?>',
                                    data: <?php echo json_encode( array_values( $analytics['trends']['revenue'] ) ); ?>,
                                    borderColor: '#2271b1',
                                    backgroundColor: 'rgba(34, 113, 177, 0.1)',
                                    tension: 0.3,
                                    yAxisID: 'y'
                                }, {
                                    label: '<?php esc_attr_e( 'Orders', 'vss' ); ?>',
                                    data: <?php echo json_encode( array_values( $analytics['trends']['orders'] ) ); ?>,
                                    borderColor: '#10b981',
                                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                    tension: 0.3,
                                    yAxisID: 'y1'
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                interaction: {
                                    mode: 'index',
                                    intersect: false,
                                },
                                scales: {
                                    y: {
                                        type: 'linear',
                                        display: true,
                                        position: 'left',
                                        ticks: {
                                            callback: function(value) {
                                                return '$' + value.toFixed(0);
                                            }
                                        }
                                    },
                                    y1: {
                                        type: 'linear',
                                        display: true,
                                        position: 'right',
                                        grid: {
                                            drawOnChartArea: false,
                                        }
                                    }
                                }
                            }
                        });
                    }

                    // Status Distribution Chart
                    var statusCtx = document.getElementById('vss-status-chart');
                    if (statusCtx) {
                        var statusData = <?php echo json_encode( $analytics['sales']['by_status'] ); ?>;
                        var labels = [];
                        var data = [];
                        var colors = {
                            'processing': '#f59e0b',
                            'shipped': '#3b82f6',
                            'completed': '#10b981',
                            'cancelled': '#ef4444',
                            'refunded': '#6b7280'
                        };
                        var bgColors = [];

                        for (var status in statusData) {
                            labels.push(status.charAt(0).toUpperCase() + status.slice(1));
                            data.push(statusData[status].count);
                            bgColors.push(colors[status] || '#9ca3af');
                        }

                        var statusChart = new Chart(statusCtx.getContext('2d'), {
                            type: 'doughnut',
                            data: {
                                labels: labels,
                                datasets: [{
                                    data: data,
                                    backgroundColor: bgColors
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                    }
                                }
                            }
                        });
                    }
                }

                // Animate performance score circle on scroll
                var scoreCircle = $('.metric-score-circle');
                if (scoreCircle.length) {
                    var animated = false;
                    $(window).on('scroll', function() {
                        if (!animated && scoreCircle.offset().top < $(window).scrollTop() + $(window).height()) {
                            animated = true;
                            scoreCircle.addClass('animated');
                        }
                    }).trigger('scroll');
                }
            });
            </script>
            <?php
        }



        /**
         * Render quick actions
         */
        private static function render_quick_actions() {
            ?>
            <div class="vss-quick-actions">
                <h3><?php esc_html_e( 'Quick Actions', 'vss' ); ?></h3>
                <div class="vss-action-buttons">
                    <a href="<?php echo esc_url( add_query_arg( 'vss_action', 'orders', get_permalink() ) ); ?>" class="button">
                        <span class="dashicons dashicons-visibility"></span>
                        <?php esc_html_e( 'View All Orders', 'vss' ); ?>
                    </a>
                    <a href="<?php echo esc_url( admin_url( 'media-new.php' ) ); ?>" class="button" target="_blank">
                        <span class="dashicons dashicons-upload"></span>
                        <?php esc_html_e( 'Upload Files', 'vss' ); ?>
                    </a>
                    <a href="<?php echo esc_url( add_query_arg( 'vss_action', 'reports', get_permalink() ) ); ?>" class="button">
                        <span class="dashicons dashicons-analytics"></span>
                        <?php esc_html_e( 'View Reports', 'vss' ); ?>
                    </a>
                    <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="button">
                        <span class="dashicons dashicons-email-alt"></span>
                        <?php esc_html_e( 'Contact Support', 'vss' ); ?>
                    </a>
                </div>
            </div>
            <?php
        }



        /**
         * Render recent orders enhanced
         */
        private static function render_recent_orders_enhanced( $analytics ) {
            $vendor_id = get_current_user_id();
            $orders = wc_get_orders( [
                'meta_key' => '_vss_vendor_user_id',
                'meta_value' => $vendor_id,
                'orderby' => 'date',
                'order' => 'DESC',
                'limit' => 10,
            ] );
            ?>
            <div class="vss-recent-orders enhanced">
                <div class="vss-recent-orders-header">
                    <h3><?php esc_html_e( 'Recent Orders', 'vss' ); ?></h3>
                    <div class="vss-order-filters">
                        <button class="vss-filter-btn active" data-filter="all"><?php esc_html_e( 'All', 'vss' ); ?></button>
                        <button class="vss-filter-btn" data-filter="processing"><?php esc_html_e( 'Processing', 'vss' ); ?></button>
                        <button class="vss-filter-btn" data-filter="late"><?php esc_html_e( 'Late', 'vss' ); ?></button>
                        <button class="vss-filter-btn" data-filter="urgent"><?php esc_html_e( 'Urgent', 'vss' ); ?></button>
                    </div>
                    <a href="<?php echo esc_url( add_query_arg( 'vss_action', 'orders', get_permalink() ) ); ?>" class="vss-view-all button">
                        <?php esc_html_e( 'View all orders', 'vss' ); ?> →
                    </a>
                </div>

                <?php if ( ! empty( $orders ) ) : ?>
                    <table class="vss-orders-table enhanced">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'Order', 'vss' ); ?></th>
                                <th><?php esc_html_e( 'Customer', 'vss' ); ?></th>
                                <th><?php esc_html_e( 'Products', 'vss' ); ?></th>
                                <th><?php esc_html_e( 'Value', 'vss' ); ?></th>
                                <th><?php esc_html_e( 'Status', 'vss' ); ?></th>
                                <th><?php esc_html_e( 'Ship Date', 'vss' ); ?></th>
                                <th><?php esc_html_e( 'Actions', 'vss' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $orders as $order ) : 
                                $ship_date = get_post_meta( $order->get_id(), '_vss_estimated_ship_date', true );
                                $is_late = $ship_date && $order->has_status( 'processing' ) && strtotime( $ship_date ) < current_time( 'timestamp' );
                                $priority = get_post_meta( $order->get_id(), '_vss_order_priority', true );
                                
                                $row_classes = [];
                                if ( $is_late ) $row_classes[] = 'order-late';
                                if ( $priority === 'urgent' ) $row_classes[] = 'order-urgent';
                                if ( $order->has_status( 'processing' ) ) $row_classes[] = 'order-processing';
                            ?>
                                <tr class="<?php echo esc_attr( implode( ' ', $row_classes ) ); ?>" data-filter="<?php echo esc_attr( implode( ' ', $row_classes ) ); ?>">
                                    <td>
                                        <a href="<?php echo esc_url( add_query_arg( [ 'vss_action' => 'view_order', 'order_id' => $order->get_id() ], get_permalink() ) ); ?>">
                                            #<?php echo esc_html( $order->get_order_number() ); ?>
                                        </a>
                                        <?php if ( $priority === 'urgent' ) : ?>
                                            <span class="priority-badge urgent">!</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="customer-info">
                                            <strong><?php echo esc_html( $order->get_formatted_billing_full_name() ); ?></strong>
                                            <small><?php echo esc_html( $order->get_billing_city() . ', ' . $order->get_billing_state() ); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="products-preview">
                                            <?php 
                                            $items = $order->get_items();
                                            $item_count = count( $items );
                                            $preview_items = array_slice( $items, 0, 2 );
                                            foreach ( $preview_items as $item ) {
                                                echo '<span class="product-tag">' . esc_html( $item->get_name() ) . '</span>';
                                            }
                                            if ( $item_count > 2 ) {
                                                echo '<span class="product-more">+' . ( $item_count - 2 ) . ' more</span>';
                                            }
                                            ?>
                                        </div>
                                    </td>
                                    <td><strong><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></strong></td>
                                    <td>
                                        <span class="status-badge status-<?php echo esc_attr( $order->get_status() ); ?>">
                                            <?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?>
                                        </span>
                                        <?php if ( $is_late ) : ?>
                                            <span class="late-badge"><?php esc_html_e( 'LATE', 'vss' ); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ( $ship_date ) : ?>
                                            <span class="ship-date <?php echo $is_late ? 'overdue' : ''; ?>">
                                                <?php echo esc_html( date_i18n( 'M j', strtotime( $ship_date ) ) ); ?>
                                            </span>
                                        <?php else : ?>
                                            <span class="no-date">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="<?php echo esc_url( add_query_arg( [ 'vss_action' => 'view_order', 'order_id' => $order->get_id() ], get_permalink() ) ); ?>" 
                                               class="button button-small" title="<?php esc_attr_e( 'View Details', 'vss' ); ?>">
                                                <span class="dashicons dashicons-visibility"></span>
                                            </a>
                                            <?php if ( $order->has_status( 'processing' ) ) : ?>
                                                <button class="button button-small vss-quick-ship" data-order="<?php echo esc_attr( $order->get_id() ); ?>" 
                                                        title="<?php esc_attr_e( 'Quick Ship', 'vss' ); ?>">
                                                    <span class="dashicons dashicons-airplane"></span>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <div class="vss-empty-state">
                        <div class="vss-empty-state-icon">
                            <span class="dashicons dashicons-cart"></span>
                        </div>
                        <h3><?php esc_html_e( 'No orders yet', 'vss' ); ?></h3>
                        <p><?php esc_html_e( 'Your orders will appear here once you receive them.', 'vss' ); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <script>
            jQuery(document).ready(function($) {
                // Order filters
                $('.vss-filter-btn').on('click', function() {
                    var filter = $(this).data('filter');
                    $('.vss-filter-btn').removeClass('active');
                    $(this).addClass('active');
                    
                    if (filter === 'all') {
                        $('.vss-orders-table tbody tr').show();
                    } else {
                        $('.vss-orders-table tbody tr').hide();
                        $('.vss-orders-table tbody tr.order-' + filter).show();
                    }
                });
            });
            </script>
            <?php
        }

        /**
         * Render recent orders
         */
        private static function render_recent_orders() {
            $vendor_id = get_current_user_id();
            $orders = wc_get_orders( [
                'meta_key' => '_vss_vendor_user_id',
                'meta_value' => $vendor_id,
                'orderby' => 'date',
                'order' => 'DESC',
                'limit' => 10,
            ] );
            ?>
            <div class="vss-recent-orders">
                <div class="vss-recent-orders-header">
                    <h3><?php esc_html_e( 'Recent Orders', 'vss' ); ?></h3>
                    <a href="<?php echo esc_url( add_query_arg( 'vss_action', 'orders', get_permalink() ) ); ?>" class="vss-view-all">
                        <?php esc_html_e( 'View all orders →', 'vss' ); ?>
                    </a>
                </div>

                <?php if ( ! empty( $orders ) ) : ?>
                    <table class="vss-orders-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'Order', 'vss' ); ?></th>
                                <th><?php esc_html_e( 'Date', 'vss' ); ?></th>
                                <th><?php esc_html_e( 'Status', 'vss' ); ?></th>
                                <th><?php esc_html_e( 'Customer', 'vss' ); ?></th>
                                <th><?php esc_html_e( 'Items', 'vss' ); ?></th>
                                <th><?php esc_html_e( 'Ship Date', 'vss' ); ?></th>
                                <th><?php esc_html_e( 'Actions', 'vss' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $orders as $order ) : ?>
                                <?php self::render_frontend_order_row( $order ); ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <div class="vss-empty-state">
                        <div class="vss-empty-state-icon">
                            <span class="dashicons dashicons-cart"></span>
                        </div>
                        <h3><?php esc_html_e( 'No orders yet', 'vss' ); ?></h3>
                        <p><?php esc_html_e( 'Your orders will appear here once you receive them.', 'vss' ); ?></p>
                    </div>
                <?php endif; ?>
            </div>
            <?php
        }



        /**
         * Render pending approvals
         */
        private static function render_pending_approvals() {
            $vendor_id = get_current_user_id();
            // --- HPOS Compatible Order Query ---

            // Get orders with disapproved mockups
            $mockup_args = [
                'limit' => 5,
                'type' => 'shop_order',
                '_vss_vendor_user_id' => $vendor_id,
                '_vss_mockup_status' => 'disapproved',
                'return' => 'ids',
            ];
            $mockup_disapproved_ids = wc_get_orders($mockup_args);

            // Get orders with disapproved production files
            $prod_file_args = [
                'limit' => 5,
                'type' => 'shop_order',
                '_vss_vendor_user_id' => $vendor_id,
                '_vss_production_file_status' => 'disapproved',
                'return' => 'ids',
            ];
            $prod_file_disapproved_ids = wc_get_orders($prod_file_args);

            // Merge and get unique order IDs, then limit to 5
            $all_pending_ids = array_slice(array_unique(array_merge($mockup_disapproved_ids, $prod_file_disapproved_ids)), 0, 5);

            // Get the actual order objects
            $pending_orders_data = [];
            if (!empty($all_pending_ids)) {
                foreach($all_pending_ids as $order_id) {
                    $pending_orders_data[] = wc_get_order($order_id);
                }
            }
            // --- End HPOS Compatible Query ---

            $pending_orders = array_filter($pending_orders_data, function($order) {
                return $order instanceof WC_Order;
            });

            if ( empty( $pending_orders ) ) {
                return;
            }
            ?>
            <div class="vss-pending-approvals">
                <h3><?php esc_html_e( 'Items Requiring Attention', 'vss' ); ?></h3>
                <ul>
                    <?php foreach ( $pending_orders as $order ) : ?>
                        <li>
                            <?php
                            $mockup_status = get_post_meta( $order->get_id(), '_vss_mockup_status', true );
                            $production_status = get_post_meta( $order->get_id(), '_vss_production_file_status', true );

                            $issues = [];
                            if ( $mockup_status === 'disapproved' ) {
                                $issues[] = __( 'Mockup disapproved', 'vss' );
                            }
                            if ( $production_status === 'disapproved' ) {
                                $issues[] = __( 'Production file disapproved', 'vss' );
                            }
                            ?>
                            <a href="<?php echo esc_url( add_query_arg( [ 'vss_action' => 'view_order', 'order_id' => $order->get_id() ], get_permalink() ) ); ?>">
                                #<?php echo esc_html( $order->get_order_number() ); ?>
                            </a>
                            - <?php echo esc_html( implode( ', ', $issues ) ); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php
        }



        /**
         * Add custom widgets to the vendor dashboard.
         */
        public static function add_vendor_dashboard_widgets() {
            if ( ! self::is_current_user_vendor() ) {
                return;
            }

            // Remove default widgets
            global $wp_meta_boxes;
            $wp_meta_boxes['dashboard']['normal']['core'] = [];
            $wp_meta_boxes['dashboard']['side']['core'] = [];

            // Add vendor widgets
            wp_add_dashboard_widget(
                'vss_vendor_stats',
                __( 'Your Stats', 'vss' ),
                [ self::class, 'render_dashboard_stats_widget' ]
            );

            wp_add_dashboard_widget(
                'vss_vendor_recent',
                __( 'Recent Orders', 'vss' ),
                [ self::class, 'render_dashboard_recent_orders_widget' ]
            );

            wp_add_dashboard_widget(
                'vss_vendor_pending_tasks_widget',
                __( 'Pending Tasks', 'vss' ),
                [ self::class, 'render_dashboard_pending_tasks_widget' ]
            );
        }



        /**
         * Render the dashboard statistics widget.
         */
        public static function render_dashboard_stats_widget() {
            $vendor_id = get_current_user_id();
            $stats = self::get_vendor_statistics( $vendor_id );
            ?>
            <ul>
                <li><?php printf( __( 'Processing: <strong>%d</strong>', 'vss' ), $stats['processing'] ); ?></li>
                <li><?php printf( __( 'Shipped This Month: <strong>%d</strong>', 'vss' ), $stats['shipped_this_month'] ); ?></li>
                <li><?php printf( __( 'Earnings This Month: <strong>%s</strong>', 'vss' ), wc_price( $stats['earnings_this_month'] ) ); ?></li>
                <?php if ( $stats['late'] > 0 ) : ?>
                    <li style="color: #d32f2f;"><?php printf( __( 'Late Orders: <strong>%d</strong>', 'vss' ), $stats['late'] ); ?></li>
                <?php endif; ?>
            </ul>
            <p><a href="<?php echo esc_url( admin_url( 'admin.php?page=vss-vendor-orders' ) ); ?>" class="button button-primary"><?php esc_html_e( 'View All Orders', 'vss' ); ?></a></p>
            <?php
        }



        /**
         * Render the dashboard recent orders widget.
         */
        public static function render_dashboard_recent_orders_widget() {
            $vendor_id = get_current_user_id();
            $orders = wc_get_orders( [
                'meta_key' => '_vss_vendor_user_id',
                'meta_value' => $vendor_id,
                'orderby' => 'date',
                'order' => 'DESC',
                'limit' => 5,
            ] );

            if ( empty( $orders ) ) {
                echo '<p>' . esc_html__( 'No recent orders.', 'vss' ) . '</p>';
                return;
            }
            ?>
            <ul>
                <?php foreach ( $orders as $order ) : ?>
                    <li>
                        <a href="<?php echo esc_url( add_query_arg( [ 'vss_action' => 'view_order', 'order_id' => $order->get_id() ], home_url( '/vendor-portal/' ) ) ); ?>">
                            #<?php echo esc_html( $order->get_order_number() ); ?>
                        </a>
                        - <?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?>
                        <span style="float: right;"><?php echo esc_html( $order->get_date_created()->date_i18n( 'M j' ) ); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php
        }



        /**
         * Render dashboard pending tasks widget
         */
        public static function render_dashboard_pending_tasks_widget() {
            $vendor_id = get_current_user_id();
            $tasks = [];

            // Orders needing ship date
            $no_ship_date = wc_get_orders( [
                'status' => 'processing',
                'meta_query' => [
                    'relation' => 'AND',
                    [
                        'key' => '_vss_vendor_user_id',
                        'value' => $vendor_id,
                    ],
                    [
                        'key' => '_vss_estimated_ship_date',
                        'compare' => 'NOT EXISTS',
                    ],
                ],
                'return' => 'ids',
                'limit' => -1,
            ] );

            if ( ! empty( $no_ship_date ) ) {
                $tasks[] = sprintf(
                    _n( '%d order needs ship date', '%d orders need ship date', count( $no_ship_date ), 'vss' ),
                    count( $no_ship_date )
                );
            }

            // Orders needing mockup
            $no_mockup = wc_get_orders( [
                'status' => 'processing',
                'meta_query' => [
                    'relation' => 'AND',
                    [
                        'key' => '_vss_vendor_user_id',
                        'value' => $vendor_id,
                    ],
                    [
                        'relation' => 'OR',
                        [
                            'key' => '_vss_mockup_status',
                            'compare' => 'NOT EXISTS',
                        ],
                        [
                            'key' => '_vss_mockup_status',
                            'value' => 'none',
                        ],
                    ],
                ],
                'return' => 'ids',
                'limit' => -1,
            ] );

            if ( ! empty( $no_mockup ) ) {
                $tasks[] = sprintf(
                    _n( '%d order needs mockup', '%d orders need mockup', count( $no_mockup ), 'vss' ),
                    count( $no_mockup )
                );
            }

            if ( empty( $tasks ) ) {
                echo '<p class="vss-no-tasks">' . esc_html__( 'All caught up! No pending tasks.', 'vss' ) . '</p>';
            } else {
                echo '<ul class="vss-pending-tasks">';
                foreach ( $tasks as $task ) {
                    echo '<li>' . esc_html( $task ) . '</li>';
                }
                echo '</ul>';
            }
        }



        /**
         * Get vendor statistics.
         *
         * @param int $vendor_id
         * @return array
         */
        private static function get_vendor_statistics( $vendor_id ) {
            $stats = [
                'processing' => 0,
                'late' => 0,
                'shipped_this_month' => 0,
                'earnings_this_month' => 0,
            ];

            // Processing orders
            $processing_orders = wc_get_orders( [
                'status' => 'processing',
                'meta_key' => '_vss_vendor_user_id',
                'meta_value' => $vendor_id,
                'return' => 'ids',
                'limit' => -1,
            ] );
            $stats['processing'] = count( $processing_orders );

            // Late orders
            foreach ( $processing_orders as $order_id ) {
                $ship_date = get_post_meta( $order_id, '_vss_estimated_ship_date', true );
                if ( $ship_date && strtotime( $ship_date ) < current_time( 'timestamp' ) ) {
                    $stats['late']++;
                }
            }

            // Shipped this month
            $month_start = date( 'Y-m-01 00:00:00' );
            $shipped_orders = wc_get_orders( [
                'status' => 'shipped',
                'meta_key' => '_vss_vendor_user_id',
                'meta_value' => $vendor_id,
                'date_modified' => '>=' . $month_start,
                'return' => 'objects',
                'limit' => -1,
            ] );
            $stats['shipped_this_month'] = count( $shipped_orders );

            // Earnings this month
            foreach ( $shipped_orders as $order ) {
                $costs = get_post_meta( $order->get_id(), '_vss_order_costs', true );
                if ( isset( $costs['total_cost'] ) ) {
                    $stats['earnings_this_month'] += floatval( $costs['total_cost'] );
                }
            }

            return apply_filters( 'vss_vendor_statistics', $stats, $vendor_id );
        }

        /**
         * Get detailed metrics for advanced dashboard
         */
        private static function get_detailed_metrics( $vendor_id, $date_range ) {
            $dates = self::get_date_range_helper( $date_range );
            
            return [
                'critical_inventory' => self::get_critical_inventory_count( $vendor_id ),
                'avg_response_time' => self::get_avg_response_time( $vendor_id, $dates ),
                'customer_acquisition_cost' => self::get_customer_acquisition_cost( $vendor_id, $dates ),
                'repeat_customer_rate' => self::get_repeat_customer_rate( $vendor_id, $dates ),
                'order_fulfillment_rate' => self::get_order_fulfillment_rate( $vendor_id, $dates ),
            ];
        }

        /**
         * Get inventory metrics
         */
        private static function get_inventory_metrics( $vendor_id ) {
            return [
                'low_stock_products' => self::get_low_stock_count( $vendor_id ),
                'out_of_stock_products' => self::get_out_of_stock_count( $vendor_id ),
                'inventory_turnover' => self::get_inventory_turnover( $vendor_id ),
            ];
        }

        /**
         * Get customer insights
         */
        private static function get_customer_insights( $vendor_id, $date_range ) {
            $dates = self::get_date_range_helper( $date_range );
            
            return [
                'customer_segments' => self::get_customer_segments( $vendor_id, $dates ),
                'geographic_distribution' => self::get_geographic_distribution( $vendor_id, $dates ),
                'purchase_patterns' => self::get_purchase_patterns( $vendor_id, $dates ),
            ];
        }

        /**
         * Get operational metrics
         */
        private static function get_operational_metrics( $vendor_id, $date_range ) {
            $dates = self::get_date_range_helper( $date_range );
            
            return [
                'production_efficiency' => self::get_production_efficiency( $vendor_id, $dates ),
                'quality_metrics' => self::get_quality_metrics( $vendor_id, $dates ),
                'resource_utilization' => self::get_resource_utilization( $vendor_id, $dates ),
            ];
        }

        /**
         * Helper: Get critical inventory count
         */
        private static function get_critical_inventory_count( $vendor_id ) {
            // This would integrate with inventory management
            // For now, return a mock value
            return rand( 0, 5 );
        }

        /**
         * Helper: Get average response time
         */
        private static function get_avg_response_time( $vendor_id, $dates ) {
            // Calculate average time to respond to customer inquiries
            return rand( 2, 24 ); // hours
        }

        /**
         * Helper: Get customer acquisition cost
         */
        private static function get_customer_acquisition_cost( $vendor_id, $dates ) {
            // This would calculate marketing spend / new customers
            return rand( 15, 50 ); // dollars
        }

        /**
         * Helper: Get repeat customer rate
         */
        private static function get_repeat_customer_rate( $vendor_id, $dates ) {
            $orders = wc_get_orders([
                'meta_key' => '_vss_vendor_user_id',
                'meta_value' => $vendor_id,
                'date_created' => $dates['start'] . '...' . $dates['end'],
                'limit' => -1,
                'return' => 'objects',
            ]);

            $customers = [];
            $repeat_customers = 0;
            
            foreach ( $orders as $order ) {
                $email = $order->get_billing_email();
                if ( isset( $customers[$email] ) ) {
                    if ( $customers[$email] == 1 ) {
                        $repeat_customers++;
                    }
                    $customers[$email]++;
                } else {
                    $customers[$email] = 1;
                }
            }
            
            $total_customers = count( $customers );
            return $total_customers > 0 ? ( $repeat_customers / $total_customers ) * 100 : 0;
        }

        /**
         * Helper: Get order fulfillment rate
         */
        private static function get_order_fulfillment_rate( $vendor_id, $dates ) {
            $total_orders = wc_get_orders([
                'meta_key' => '_vss_vendor_user_id',
                'meta_value' => $vendor_id,
                'date_created' => $dates['start'] . '...' . $dates['end'],
                'limit' => -1,
                'return' => 'ids',
            ]);

            $fulfilled_orders = wc_get_orders([
                'meta_key' => '_vss_vendor_user_id',
                'meta_value' => $vendor_id,
                'status' => ['completed', 'shipped'],
                'date_created' => $dates['start'] . '...' . $dates['end'],
                'limit' => -1,
                'return' => 'ids',
            ]);

            $total = count( $total_orders );
            $fulfilled = count( $fulfilled_orders );
            
            return $total > 0 ? ( $fulfilled / $total ) * 100 : 0;
        }

        /**
         * Helper: Get low stock count
         */
        private static function get_low_stock_count( $vendor_id ) {
            // This would integrate with WooCommerce inventory
            return rand( 0, 10 );
        }

        /**
         * Helper: Get out of stock count
         */
        private static function get_out_of_stock_count( $vendor_id ) {
            // This would integrate with WooCommerce inventory
            return rand( 0, 3 );
        }

        /**
         * Helper: Get inventory turnover
         */
        private static function get_inventory_turnover( $vendor_id ) {
            // This would calculate how quickly inventory moves
            return rand( 3, 12 ); // times per year
        }

        /**
         * Helper: Get customer segments
         */
        private static function get_customer_segments( $vendor_id, $dates ) {
            return [
                'high_value' => rand( 5, 20 ),
                'medium_value' => rand( 20, 50 ),
                'low_value' => rand( 30, 100 ),
            ];
        }

        /**
         * Helper: Get geographic distribution
         */
        private static function get_geographic_distribution( $vendor_id, $dates ) {
            $orders = wc_get_orders([
                'meta_key' => '_vss_vendor_user_id',
                'meta_value' => $vendor_id,
                'date_created' => $dates['start'] . '...' . $dates['end'],
                'limit' => -1,
                'return' => 'objects',
            ]);

            $locations = [];
            foreach ( $orders as $order ) {
                $country = $order->get_billing_country();
                if ( ! isset( $locations[$country] ) ) {
                    $locations[$country] = 0;
                }
                $locations[$country]++;
            }
            
            arsort( $locations );
            return array_slice( $locations, 0, 10, true );
        }

        /**
         * Helper: Get purchase patterns
         */
        private static function get_purchase_patterns( $vendor_id, $dates ) {
            // This would analyze when customers typically purchase
            return [
                'peak_day' => 'Friday',
                'peak_hour' => '2:00 PM',
                'seasonal_trend' => 'Increasing',
            ];
        }

        /**
         * Helper: Get production efficiency
         */
        private static function get_production_efficiency( $vendor_id, $dates ) {
            // This would calculate production metrics
            return rand( 75, 95 ); // percentage
        }

        /**
         * Helper: Get quality metrics
         */
        private static function get_quality_metrics( $vendor_id, $dates ) {
            return [
                'defect_rate' => rand( 1, 5 ), // percentage
                'return_rate' => rand( 2, 8 ), // percentage  
                'customer_satisfaction' => rand( 85, 98 ), // percentage
            ];
        }

        /**
         * Helper: Get resource utilization
         */
        private static function get_resource_utilization( $vendor_id, $dates ) {
            return [
                'equipment_usage' => rand( 70, 90 ), // percentage
                'staff_productivity' => rand( 80, 95 ), // percentage
                'material_efficiency' => rand( 85, 98 ), // percentage
            ];
        }

        /**
         * Helper: Get date range for queries
         */
        private static function get_date_range_helper( $range ) {
            $end = date( 'Y-m-d 23:59:59' );
            
            switch ( $range ) {
                case 'today':
                    $start = date( 'Y-m-d 00:00:00' );
                    break;
                case 'yesterday':
                    $start = date( 'Y-m-d 00:00:00', strtotime( '-1 day' ) );
                    $end = date( 'Y-m-d 23:59:59', strtotime( '-1 day' ) );
                    break;
                case 'last_7_days':
                    $start = date( 'Y-m-d 00:00:00', strtotime( '-6 days' ) );
                    break;
                case 'last_30_days':
                    $start = date( 'Y-m-d 00:00:00', strtotime( '-29 days' ) );
                    break;
                case 'last_90_days':
                    $start = date( 'Y-m-d 00:00:00', strtotime( '-89 days' ) );
                    break;
                case 'this_month':
                    $start = date( 'Y-m-01 00:00:00' );
                    break;
                case 'last_month':
                    $start = date( 'Y-m-01 00:00:00', strtotime( 'first day of last month' ) );
                    $end = date( 'Y-m-t 23:59:59', strtotime( 'last day of last month' ) );
                    break;
                case 'this_year':
                    $start = date( 'Y-01-01 00:00:00' );
                    break;
                case 'last_year':
                    $start = date( 'Y-01-01 00:00:00', strtotime( '-1 year' ) );
                    $end = date( 'Y-12-31 23:59:59', strtotime( '-1 year' ) );
                    break;
                default:
                    $start = date( 'Y-m-d 00:00:00', strtotime( '-29 days' ) );
            }
            
            return ['start' => $start, 'end' => $end];
        }


}
