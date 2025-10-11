<?php
/**
 * VSS Vendor Export Module
 * 
 * Export functionality for CSV, PDF, and Excel formats
 * 
 * @package VendorOrderManager
 * @subpackage Modules
 * @since 7.0.2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Trait for Export functionality
 */
trait VSS_Vendor_Export {

    /**
     * Initialize export handlers
     */
    public static function init_export_handlers() {
        add_action( 'wp_ajax_vss_export_orders', [ self::class, 'handle_export_orders' ] );
        add_action( 'wp_ajax_vss_export_report', [ self::class, 'handle_export_report' ] );
        add_action( 'wp_ajax_vss_export_dashboard', [ self::class, 'handle_export_dashboard' ] );
    }

    /**
     * Handle orders export
     */
    public static function handle_export_orders() {
        // Verify nonce and permissions
        if ( ! wp_verify_nonce( $_GET['nonce'] ?? '', 'vss_export_orders' ) ) {
            wp_die( 'Security check failed' );
        }

        $vendor_id = get_current_user_id();
        if ( ! self::is_current_user_vendor() ) {
            wp_die( 'Unauthorized access' );
        }

        $format = sanitize_key( $_GET['format'] ?? 'csv' );
        $filters = [
            'status' => sanitize_key( $_GET['status'] ?? 'all' ),
            'date_from' => sanitize_text_field( $_GET['date_from'] ?? '' ),
            'date_to' => sanitize_text_field( $_GET['date_to'] ?? '' ),
            'search' => sanitize_text_field( $_GET['search'] ?? '' ),
        ];

        // Get orders
        $orders = self::get_filtered_orders( $vendor_id, $filters );

        switch ( $format ) {
            case 'csv':
                self::export_orders_csv( $orders );
                break;
            case 'excel':
                self::export_orders_excel( $orders );
                break;
            case 'pdf':
                self::export_orders_pdf( $orders );
                break;
        }

        exit;
    }

    /**
     * Export orders as CSV
     */
    private static function export_orders_csv( $orders ) {
        $filename = 'orders-' . date( 'Y-m-d-H-i-s' ) . '.csv';
        
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=' . $filename );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );
        
        // Add BOM for UTF-8 compatibility in Excel
        echo "\xEF\xBB\xBF";
        
        $output = fopen( 'php://output', 'w' );
        
        // Headers
        fputcsv( $output, [
            __( 'Order ID', 'vss' ),
            __( 'Order Number', 'vss' ),
            __( 'Date', 'vss' ),
            __( 'Status', 'vss' ),
            __( 'Customer Name', 'vss' ),
            __( 'Customer Email', 'vss' ),
            __( 'Customer Phone', 'vss' ),
            __( 'Billing Address', 'vss' ),
            __( 'Shipping Address', 'vss' ),
            __( 'Products', 'vss' ),
            __( 'Quantity', 'vss' ),
            __( 'Subtotal', 'vss' ),
            __( 'Shipping', 'vss' ),
            __( 'Tax', 'vss' ),
            __( 'Total', 'vss' ),
            __( 'Payment Method', 'vss' ),
            __( 'Ship Date', 'vss' ),
            __( 'Tracking Number', 'vss' ),
            __( 'Mockup Status', 'vss' ),
            __( 'Production Status', 'vss' ),
            __( 'Order Notes', 'vss' ),
            __( 'Vendor Costs', 'vss' ),
            __( 'Vendor Profit', 'vss' ),
        ]);
        
        // Data rows
        foreach ( $orders as $order ) {
            $order_id = $order->get_id();
            
            // Get products
            $products = [];
            $total_quantity = 0;
            foreach ( $order->get_items() as $item ) {
                $products[] = $item->get_name() . ' (x' . $item->get_quantity() . ')';
                $total_quantity += $item->get_quantity();
            }
            
            // Get meta data
            $ship_date = get_post_meta( $order_id, '_vss_estimated_ship_date', true );
            $tracking = get_post_meta( $order_id, '_vss_tracking_number', true );
            $mockup_status = get_post_meta( $order_id, '_vss_mockup_status', true );
            $production_status = get_post_meta( $order_id, '_vss_production_file_status', true );
            $costs = get_post_meta( $order_id, '_vss_order_costs', true );
            
            // Calculate profit
            $vendor_cost = isset( $costs['total_cost'] ) ? floatval( $costs['total_cost'] ) : 0;
            $vendor_profit = $order->get_total() - $vendor_cost;
            
            // Get notes
            $notes = wc_get_order_notes([
                'order_id' => $order_id,
                'type' => 'customer',
            ]);
            $note_text = ! empty( $notes ) ? $notes[0]->content : '';
            
            fputcsv( $output, [
                $order_id,
                $order->get_order_number(),
                $order->get_date_created()->date( 'Y-m-d H:i:s' ),
                wc_get_order_status_name( $order->get_status() ),
                $order->get_formatted_billing_full_name(),
                $order->get_billing_email(),
                $order->get_billing_phone(),
                $order->get_formatted_billing_address(),
                $order->get_formatted_shipping_address(),
                implode( ', ', $products ),
                $total_quantity,
                $order->get_subtotal(),
                $order->get_shipping_total(),
                $order->get_total_tax(),
                $order->get_total(),
                $order->get_payment_method_title(),
                $ship_date,
                $tracking,
                $mockup_status,
                $production_status,
                $note_text,
                $vendor_cost,
                $vendor_profit,
            ]);
        }
        
        fclose( $output );
    }

    /**
     * Export orders as Excel
     */
    private static function export_orders_excel( $orders ) {
        // For Excel export, we'll use a library like PhpSpreadsheet if available
        // For now, we'll output as CSV with Excel-compatible formatting
        
        $filename = 'orders-' . date( 'Y-m-d-H-i-s' ) . '.xls';
        
        header( 'Content-Type: application/vnd.ms-excel' );
        header( 'Content-Disposition: attachment; filename=' . $filename );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );
        
        echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
        echo '<head>';
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
        echo '<!--[if gte mso 9]><xml>';
        echo '<x:ExcelWorkbook>';
        echo '<x:ExcelWorksheets>';
        echo '<x:ExcelWorksheet>';
        echo '<x:Name>Orders</x:Name>';
        echo '<x:WorksheetOptions><x:Print><x:ValidPrinterInfo/></x:Print></x:WorksheetOptions>';
        echo '</x:ExcelWorksheet>';
        echo '</x:ExcelWorksheets>';
        echo '</x:ExcelWorkbook>';
        echo '</xml><![endif]-->';
        echo '</head>';
        echo '<body>';
        echo '<table border="1">';
        
        // Headers
        echo '<tr style="background-color: #2271b1; color: white; font-weight: bold;">';
        echo '<th>' . __( 'Order ID', 'vss' ) . '</th>';
        echo '<th>' . __( 'Order Number', 'vss' ) . '</th>';
        echo '<th>' . __( 'Date', 'vss' ) . '</th>';
        echo '<th>' . __( 'Status', 'vss' ) . '</th>';
        echo '<th>' . __( 'Customer', 'vss' ) . '</th>';
        echo '<th>' . __( 'Email', 'vss' ) . '</th>';
        echo '<th>' . __( 'Products', 'vss' ) . '</th>';
        echo '<th>' . __( 'Quantity', 'vss' ) . '</th>';
        echo '<th>' . __( 'Total', 'vss' ) . '</th>';
        echo '<th>' . __( 'Ship Date', 'vss' ) . '</th>';
        echo '<th>' . __( 'Tracking', 'vss' ) . '</th>';
        echo '</tr>';
        
        // Data rows
        foreach ( $orders as $order ) {
            $order_id = $order->get_id();
            
            // Get products
            $products = [];
            $total_quantity = 0;
            foreach ( $order->get_items() as $item ) {
                $products[] = $item->get_name() . ' (x' . $item->get_quantity() . ')';
                $total_quantity += $item->get_quantity();
            }
            
            $ship_date = get_post_meta( $order_id, '_vss_estimated_ship_date', true );
            $tracking = get_post_meta( $order_id, '_vss_tracking_number', true );
            
            $row_style = '';
            if ( $order->has_status( 'processing' ) && $ship_date && strtotime( $ship_date ) < current_time( 'timestamp' ) ) {
                $row_style = 'background-color: #fee2e2;'; // Light red for late orders
            }
            
            echo '<tr style="' . $row_style . '">';
            echo '<td>' . $order_id . '</td>';
            echo '<td>' . $order->get_order_number() . '</td>';
            echo '<td>' . $order->get_date_created()->date( 'Y-m-d H:i:s' ) . '</td>';
            echo '<td>' . wc_get_order_status_name( $order->get_status() ) . '</td>';
            echo '<td>' . $order->get_formatted_billing_full_name() . '</td>';
            echo '<td>' . $order->get_billing_email() . '</td>';
            echo '<td>' . implode( ', ', $products ) . '</td>';
            echo '<td>' . $total_quantity . '</td>';
            echo '<td>' . strip_tags( $order->get_formatted_order_total() ) . '</td>';
            echo '<td>' . $ship_date . '</td>';
            echo '<td>' . $tracking . '</td>';
            echo '</tr>';
        }
        
        echo '</table>';
        echo '</body>';
        echo '</html>';
    }

    /**
     * Export orders as PDF
     */
    private static function export_orders_pdf( $orders ) {
        // For PDF export, we would typically use a library like TCPDF or DomPDF
        // For now, we'll output an HTML version that can be printed to PDF
        
        $filename = 'orders-' . date( 'Y-m-d-H-i-s' ) . '.html';
        
        header( 'Content-Type: text/html; charset=utf-8' );
        header( 'Content-Disposition: inline; filename=' . $filename );
        
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title><?php esc_html_e( 'Orders Export', 'vss' ); ?></title>
            <style>
                @page { size: landscape; margin: 1cm; }
                body { font-family: Arial, sans-serif; font-size: 12px; }
                h1 { color: #2271b1; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th { background: #2271b1; color: white; padding: 8px; text-align: left; }
                td { padding: 6px; border-bottom: 1px solid #ddd; }
                tr:nth-child(even) { background: #f9f9f9; }
                .late { background: #fee2e2 !important; }
                .header-info { margin-bottom: 20px; }
                .footer { margin-top: 30px; text-align: center; color: #666; font-size: 10px; }
                @media print {
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body>
            <div class="header-info">
                <h1><?php esc_html_e( 'Orders Export', 'vss' ); ?></h1>
                <p><?php printf( __( 'Generated on: %s', 'vss' ), date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ); ?></p>
                <p><?php printf( __( 'Total Orders: %d', 'vss' ), count( $orders ) ); ?></p>
            </div>
            
            <button onclick="window.print();" class="no-print" style="padding: 10px 20px; background: #2271b1; color: white; border: none; cursor: pointer;">
                <?php esc_html_e( 'Print / Save as PDF', 'vss' ); ?>
            </button>
            
            <table>
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Order', 'vss' ); ?></th>
                        <th><?php esc_html_e( 'Date', 'vss' ); ?></th>
                        <th><?php esc_html_e( 'Customer', 'vss' ); ?></th>
                        <th><?php esc_html_e( 'Products', 'vss' ); ?></th>
                        <th><?php esc_html_e( 'Status', 'vss' ); ?></th>
                        <th><?php esc_html_e( 'Total', 'vss' ); ?></th>
                        <th><?php esc_html_e( 'Ship Date', 'vss' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $orders as $order ) : 
                        $order_id = $order->get_id();
                        $ship_date = get_post_meta( $order_id, '_vss_estimated_ship_date', true );
                        $is_late = $order->has_status( 'processing' ) && $ship_date && strtotime( $ship_date ) < current_time( 'timestamp' );
                        
                        $products = [];
                        foreach ( $order->get_items() as $item ) {
                            $products[] = $item->get_name() . ' (x' . $item->get_quantity() . ')';
                        }
                    ?>
                    <tr class="<?php echo $is_late ? 'late' : ''; ?>">
                        <td>#<?php echo esc_html( $order->get_order_number() ); ?></td>
                        <td><?php echo esc_html( $order->get_date_created()->date( 'Y-m-d' ) ); ?></td>
                        <td><?php echo esc_html( $order->get_formatted_billing_full_name() ); ?></td>
                        <td><?php echo esc_html( implode( ', ', $products ) ); ?></td>
                        <td><?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?></td>
                        <td><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></td>
                        <td><?php echo $ship_date ? esc_html( date_i18n( 'Y-m-d', strtotime( $ship_date ) ) ) : '-'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="footer">
                <p><?php esc_html_e( 'This document is confidential and for internal use only.', 'vss' ); ?></p>
            </div>
        </body>
        </html>
        <?php
    }

    /**
     * Handle report export
     */
    public static function handle_export_report() {
        // Verify nonce and permissions
        if ( ! wp_verify_nonce( $_GET['nonce'] ?? '', 'vss_export_report' ) ) {
            wp_die( 'Security check failed' );
        }

        $vendor_id = intval( $_GET['vendor_id'] ?? get_current_user_id() );
        if ( ! self::is_current_user_vendor() ) {
            wp_die( 'Unauthorized access' );
        }

        $format = sanitize_key( $_GET['format'] ?? 'csv' );
        $report_type = sanitize_key( $_GET['report_type'] ?? 'overview' );
        $date_range = sanitize_key( $_GET['date_range'] ?? 'last_30_days' );

        // Get analytics data
        $analytics = self::get_vendor_analytics( $vendor_id, $date_range );

        switch ( $format ) {
            case 'csv':
                self::export_report_csv( $analytics, $report_type );
                break;
            case 'excel':
                self::export_report_excel( $analytics, $report_type );
                break;
            case 'pdf':
                self::export_report_pdf( $analytics, $report_type );
                break;
        }

        exit;
    }

    /**
     * Export report as CSV
     */
    private static function export_report_csv( $analytics, $report_type ) {
        $filename = $report_type . '-report-' . date( 'Y-m-d-H-i-s' ) . '.csv';
        
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=' . $filename );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );
        
        // Add BOM for UTF-8 compatibility
        echo "\xEF\xBB\xBF";
        
        $output = fopen( 'php://output', 'w' );
        
        switch ( $report_type ) {
            case 'overview':
                self::export_overview_csv( $output, $analytics );
                break;
            case 'sales':
                self::export_sales_csv( $output, $analytics );
                break;
            case 'products':
                self::export_products_csv( $output, $analytics );
                break;
            case 'customers':
                self::export_customers_csv( $output, $analytics );
                break;
            case 'financial':
                self::export_financial_csv( $output, $analytics );
                break;
        }
        
        fclose( $output );
    }

    /**
     * Export overview data to CSV
     */
    private static function export_overview_csv( $output, $analytics ) {
        fputcsv( $output, [ __( 'Business Overview Report', 'vss' ) ] );
        fputcsv( $output, [] );
        
        fputcsv( $output, [ __( 'Metric', 'vss' ), __( 'Value', 'vss' ) ] );
        fputcsv( $output, [ __( 'Total Orders', 'vss' ), $analytics['overview']['total_orders'] ] );
        fputcsv( $output, [ __( 'Total Revenue', 'vss' ), $analytics['overview']['total_revenue'] ] );
        fputcsv( $output, [ __( 'Total Costs', 'vss' ), $analytics['overview']['total_costs'] ] );
        fputcsv( $output, [ __( 'Net Profit', 'vss' ), $analytics['overview']['total_profit'] ] );
        fputcsv( $output, [ __( 'Average Order Value', 'vss' ), $analytics['overview']['average_order_value'] ] );
        fputcsv( $output, [ __( 'Orders Growth', 'vss' ), $analytics['overview']['orders_growth'] . '%' ] );
        fputcsv( $output, [ __( 'Revenue Growth', 'vss' ), $analytics['overview']['revenue_growth'] . '%' ] );
        fputcsv( $output, [ __( 'Completion Rate', 'vss' ), $analytics['overview']['completion_rate'] . '%' ] );
        fputcsv( $output, [ __( 'On-Time Delivery Rate', 'vss' ), $analytics['overview']['on_time_delivery_rate'] . '%' ] );
        fputcsv( $output, [] );
        
        // Performance metrics
        fputcsv( $output, [ __( 'Performance Metrics', 'vss' ) ] );
        fputcsv( $output, [ __( 'Average Processing Time (days)', 'vss' ), $analytics['performance']['average_processing_time'] ] );
        fputcsv( $output, [ __( 'Mockup Approval Rate', 'vss' ), $analytics['performance']['mockup_approval_rate'] . '%' ] );
        fputcsv( $output, [ __( 'Production Approval Rate', 'vss' ), $analytics['performance']['production_approval_rate'] . '%' ] );
        fputcsv( $output, [ __( 'Customer Satisfaction', 'vss' ), $analytics['performance']['customer_satisfaction'] . '%' ] );
        fputcsv( $output, [ __( 'Late Orders Percentage', 'vss' ), $analytics['performance']['late_orders_percentage'] . '%' ] );
        fputcsv( $output, [ __( 'Quality Score', 'vss' ), $analytics['performance']['quality_score'] . '%' ] );
    }

    /**
     * Export sales data to CSV
     */
    private static function export_sales_csv( $output, $analytics ) {
        fputcsv( $output, [ __( 'Sales Report', 'vss' ) ] );
        fputcsv( $output, [] );
        
        // Sales by status
        fputcsv( $output, [ __( 'Sales by Status', 'vss' ) ] );
        fputcsv( $output, [ __( 'Status', 'vss' ), __( 'Count', 'vss' ), __( 'Revenue', 'vss' ) ] );
        foreach ( $analytics['sales']['by_status'] as $status => $data ) {
            fputcsv( $output, [ ucfirst( $status ), $data['count'], $data['revenue'] ] );
        }
        fputcsv( $output, [] );
        
        // Daily sales
        fputcsv( $output, [ __( 'Daily Sales', 'vss' ) ] );
        fputcsv( $output, [ __( 'Date', 'vss' ), __( 'Orders', 'vss' ), __( 'Revenue', 'vss' ) ] );
        foreach ( $analytics['sales']['by_day'] as $date => $data ) {
            fputcsv( $output, [ $date, $data['orders'], $data['revenue'] ] );
        }
    }

    /**
     * Export products data to CSV
     */
    private static function export_products_csv( $output, $analytics ) {
        fputcsv( $output, [ __( 'Product Performance Report', 'vss' ) ] );
        fputcsv( $output, [] );
        
        fputcsv( $output, [ __( 'Summary', 'vss' ) ] );
        fputcsv( $output, [ __( 'Total Items Sold', 'vss' ), $analytics['products']['total_items_sold'] ] );
        fputcsv( $output, [ __( 'Unique Products', 'vss' ), $analytics['products']['unique_products'] ] );
        fputcsv( $output, [] );
        
        fputcsv( $output, [ __( 'Top Products', 'vss' ) ] );
        fputcsv( $output, [ __( 'Product Name', 'vss' ), __( 'Quantity', 'vss' ), __( 'Revenue', 'vss' ) ] );
        foreach ( $analytics['products']['top_products'] as $product_id => $product ) {
            fputcsv( $output, [ $product['name'], $product['quantity'], $product['revenue'] ] );
        }
    }

    /**
     * Export customers data to CSV
     */
    private static function export_customers_csv( $output, $analytics ) {
        fputcsv( $output, [ __( 'Customer Analysis Report', 'vss' ) ] );
        fputcsv( $output, [] );
        
        fputcsv( $output, [ __( 'Summary', 'vss' ) ] );
        fputcsv( $output, [ __( 'Total Customers', 'vss' ), $analytics['customers']['total_customers'] ] );
        fputcsv( $output, [ __( 'New Customers', 'vss' ), $analytics['customers']['new_customers'] ] );
        fputcsv( $output, [ __( 'Returning Customers', 'vss' ), $analytics['customers']['returning_customers'] ] );
        fputcsv( $output, [ __( 'Average Lifetime Value', 'vss' ), $analytics['customers']['customer_lifetime_value'] ] );
        fputcsv( $output, [] );
        
        fputcsv( $output, [ __( 'Top Customers', 'vss' ) ] );
        fputcsv( $output, [ __( 'Customer Name', 'vss' ), __( 'Email', 'vss' ), __( 'Orders', 'vss' ), __( 'Total Spent', 'vss' ) ] );
        foreach ( $analytics['customers']['top_customers'] as $email => $customer ) {
            fputcsv( $output, [ $customer['name'], $email, $customer['orders'], $customer['total_spent'] ] );
        }
    }

    /**
     * Export financial data to CSV
     */
    private static function export_financial_csv( $output, $analytics ) {
        fputcsv( $output, [ __( 'Financial Summary Report', 'vss' ) ] );
        fputcsv( $output, [] );
        
        fputcsv( $output, [ __( 'Financial Overview', 'vss' ) ] );
        fputcsv( $output, [ __( 'Gross Revenue', 'vss' ), $analytics['overview']['total_revenue'] ] );
        fputcsv( $output, [ __( 'Total Costs', 'vss' ), $analytics['overview']['total_costs'] ] );
        fputcsv( $output, [ __( 'Net Profit', 'vss' ), $analytics['overview']['total_profit'] ] );
        
        $margin = $analytics['overview']['total_revenue'] > 0 ? 
            ( $analytics['overview']['total_profit'] / $analytics['overview']['total_revenue'] ) * 100 : 0;
        fputcsv( $output, [ __( 'Profit Margin', 'vss' ), number_format( $margin, 2 ) . '%' ] );
    }

    /**
     * Get filtered orders
     */
    private static function get_filtered_orders( $vendor_id, $filters = [] ) {
        $args = [
            'meta_key' => '_vss_vendor_user_id',
            'meta_value' => $vendor_id,
            'orderby' => 'date',
            'order' => 'DESC',
            'limit' => -1,
            'return' => 'objects',
        ];

        // Apply filters
        if ( ! empty( $filters['status'] ) && $filters['status'] !== 'all' ) {
            $args['status'] = 'wc-' . $filters['status'];
        }

        if ( ! empty( $filters['date_from'] ) ) {
            $args['date_created'] = '>=' . $filters['date_from'];
        }

        if ( ! empty( $filters['date_to'] ) ) {
            if ( isset( $args['date_created'] ) ) {
                $args['date_created'] = [ $args['date_created'], '<=' . $filters['date_to'] ];
            } else {
                $args['date_created'] = '<=' . $filters['date_to'];
            }
        }

        if ( ! empty( $filters['search'] ) ) {
            $args['s'] = $filters['search'];
        }

        return wc_get_orders( $args );
    }

    /**
     * Handle dashboard export
     */
    public static function handle_export_dashboard() {
        // Verify nonce and permissions
        if ( ! wp_verify_nonce( $_GET['nonce'] ?? '', 'vss_export_dashboard' ) ) {
            wp_die( 'Security check failed' );
        }

        $vendor_id = intval( $_GET['vendor_id'] ?? get_current_user_id() );
        if ( ! self::is_current_user_vendor() ) {
            wp_die( 'Unauthorized access' );
        }

        $date_range = sanitize_key( $_GET['date_range'] ?? 'last_30_days' );
        $analytics = self::get_vendor_analytics( $vendor_id, $date_range );

        // Export complete dashboard data as comprehensive CSV
        $filename = 'dashboard-' . $date_range . '-' . date( 'Y-m-d-H-i-s' ) . '.csv';
        
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=' . $filename );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );
        
        echo "\xEF\xBB\xBF";
        $output = fopen( 'php://output', 'w' );
        
        // Export all sections
        self::export_overview_csv( $output, $analytics );
        fputcsv( $output, [] );
        fputcsv( $output, [] );
        self::export_sales_csv( $output, $analytics );
        fputcsv( $output, [] );
        fputcsv( $output, [] );
        self::export_products_csv( $output, $analytics );
        fputcsv( $output, [] );
        fputcsv( $output, [] );
        self::export_customers_csv( $output, $analytics );
        
        fclose( $output );
        exit;
    }
}