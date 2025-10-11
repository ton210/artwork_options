<?php
/**
 * VSS Vendor Analytics Module
 * 
 * Advanced analytics and statistics for vendors
 * 
 * @package VendorOrderManager
 * @subpackage Modules
 * @since 7.0.2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Trait for Analytics functionality
 */
trait VSS_Vendor_Analytics {

    /**
     * Get comprehensive vendor analytics
     */
    public static function get_vendor_analytics( $vendor_id, $date_range = 'last_30_days' ) {
        $analytics = [
            'overview' => self::get_overview_stats( $vendor_id, $date_range ),
            'sales' => self::get_sales_stats( $vendor_id, $date_range ),
            'performance' => self::get_performance_stats( $vendor_id, $date_range ),
            'customers' => self::get_customer_stats( $vendor_id, $date_range ),
            'products' => self::get_product_stats( $vendor_id, $date_range ),
            'trends' => self::get_trend_data( $vendor_id, $date_range ),
        ];

        return apply_filters( 'vss_vendor_analytics', $analytics, $vendor_id, $date_range );
    }

    /**
     * Get overview statistics
     */
    private static function get_overview_stats( $vendor_id, $date_range ) {
        $dates = self::get_date_range( $date_range );
        
        $stats = [
            'total_orders' => 0,
            'total_revenue' => 0,
            'total_costs' => 0,
            'total_profit' => 0,
            'average_order_value' => 0,
            'orders_growth' => 0,
            'revenue_growth' => 0,
            'completion_rate' => 0,
            'on_time_delivery_rate' => 0,
        ];

        // Get orders for current period
        $current_orders = wc_get_orders([
            'meta_key' => '_vss_vendor_user_id',
            'meta_value' => $vendor_id,
            'date_created' => $dates['start'] . '...' . $dates['end'],
            'limit' => -1,
            'return' => 'objects',
        ]);

        // Calculate current period stats
        foreach ( $current_orders as $order ) {
            $stats['total_orders']++;
            $order_total = $order->get_total();
            $stats['total_revenue'] += $order_total;
            
            $costs = get_post_meta( $order->get_id(), '_vss_order_costs', true );
            if ( isset( $costs['total_cost'] ) ) {
                $stats['total_costs'] += floatval( $costs['total_cost'] );
            }

            // Check if delivered on time
            if ( $order->has_status( ['completed', 'shipped'] ) ) {
                $ship_date = get_post_meta( $order->get_id(), '_vss_estimated_ship_date', true );
                $actual_ship = get_post_meta( $order->get_id(), '_vss_actual_ship_date', true );
                
                if ( $ship_date && $actual_ship ) {
                    if ( strtotime( $actual_ship ) <= strtotime( $ship_date ) ) {
                        $stats['on_time_delivery_rate']++;
                    }
                }
            }
        }

        // Calculate profit and averages
        $stats['total_profit'] = $stats['total_revenue'] - $stats['total_costs'];
        if ( $stats['total_orders'] > 0 ) {
            $stats['average_order_value'] = $stats['total_revenue'] / $stats['total_orders'];
            $stats['completion_rate'] = ( count( array_filter( $current_orders, function($o) { 
                return $o->has_status(['completed', 'shipped']); 
            })) / $stats['total_orders'] ) * 100;
            
            if ( $stats['on_time_delivery_rate'] > 0 ) {
                $shipped_orders = count( array_filter( $current_orders, function($o) { 
                    return $o->has_status(['completed', 'shipped']); 
                }));
                if ( $shipped_orders > 0 ) {
                    $stats['on_time_delivery_rate'] = ( $stats['on_time_delivery_rate'] / $shipped_orders ) * 100;
                }
            }
        }

        // Get previous period for comparison
        $prev_dates = self::get_previous_period( $date_range );
        $prev_orders = wc_get_orders([
            'meta_key' => '_vss_vendor_user_id',
            'meta_value' => $vendor_id,
            'date_created' => $prev_dates['start'] . '...' . $prev_dates['end'],
            'limit' => -1,
            'return' => 'ids',
        ]);

        $prev_count = count( $prev_orders );
        $prev_revenue = 0;
        
        foreach ( $prev_orders as $order_id ) {
            $order = wc_get_order( $order_id );
            if ( $order ) {
                $prev_revenue += $order->get_total();
            }
        }

        // Calculate growth rates
        if ( $prev_count > 0 ) {
            $stats['orders_growth'] = (( $stats['total_orders'] - $prev_count ) / $prev_count ) * 100;
        }
        if ( $prev_revenue > 0 ) {
            $stats['revenue_growth'] = (( $stats['total_revenue'] - $prev_revenue ) / $prev_revenue ) * 100;
        }

        return $stats;
    }

    /**
     * Get sales statistics
     */
    private static function get_sales_stats( $vendor_id, $date_range ) {
        $dates = self::get_date_range( $date_range );
        
        $stats = [
            'by_status' => [],
            'by_day' => [],
            'by_week' => [],
            'by_month' => [],
            'top_days' => [],
            'peak_hours' => [],
        ];

        // Get all orders
        $orders = wc_get_orders([
            'meta_key' => '_vss_vendor_user_id',
            'meta_value' => $vendor_id,
            'date_created' => $dates['start'] . '...' . $dates['end'],
            'limit' => -1,
            'return' => 'objects',
        ]);

        // Group by status
        $status_counts = [];
        $daily_sales = [];
        $hourly_sales = [];

        foreach ( $orders as $order ) {
            $status = $order->get_status();
            if ( ! isset( $status_counts[$status] ) ) {
                $status_counts[$status] = ['count' => 0, 'revenue' => 0];
            }
            $status_counts[$status]['count']++;
            $status_counts[$status]['revenue'] += $order->get_total();

            // Daily sales
            $date = $order->get_date_created()->date('Y-m-d');
            if ( ! isset( $daily_sales[$date] ) ) {
                $daily_sales[$date] = ['orders' => 0, 'revenue' => 0];
            }
            $daily_sales[$date]['orders']++;
            $daily_sales[$date]['revenue'] += $order->get_total();

            // Hourly distribution
            $hour = $order->get_date_created()->date('H');
            if ( ! isset( $hourly_sales[$hour] ) ) {
                $hourly_sales[$hour] = 0;
            }
            $hourly_sales[$hour]++;
        }

        $stats['by_status'] = $status_counts;
        $stats['by_day'] = $daily_sales;
        
        // Find top performing days
        arsort( $daily_sales );
        $stats['top_days'] = array_slice( $daily_sales, 0, 5, true );

        // Find peak hours
        ksort( $hourly_sales );
        $stats['peak_hours'] = $hourly_sales;

        return $stats;
    }

    /**
     * Get performance statistics
     */
    private static function get_performance_stats( $vendor_id, $date_range ) {
        $dates = self::get_date_range( $date_range );
        
        return [
            'average_processing_time' => self::calculate_avg_processing_time( $vendor_id, $dates ),
            'mockup_approval_rate' => self::calculate_approval_rate( $vendor_id, 'mockup', $dates ),
            'production_approval_rate' => self::calculate_approval_rate( $vendor_id, 'production_file', $dates ),
            'customer_satisfaction' => self::calculate_satisfaction_score( $vendor_id, $dates ),
            'late_orders_percentage' => self::calculate_late_orders_percentage( $vendor_id, $dates ),
            'quality_score' => self::calculate_quality_score( $vendor_id, $dates ),
        ];
    }

    /**
     * Get customer statistics
     */
    private static function get_customer_stats( $vendor_id, $date_range ) {
        $dates = self::get_date_range( $date_range );
        
        $stats = [
            'total_customers' => 0,
            'new_customers' => 0,
            'returning_customers' => 0,
            'top_customers' => [],
            'customer_locations' => [],
            'customer_lifetime_value' => 0,
        ];

        $orders = wc_get_orders([
            'meta_key' => '_vss_vendor_user_id',
            'meta_value' => $vendor_id,
            'date_created' => $dates['start'] . '...' . $dates['end'],
            'limit' => -1,
            'return' => 'objects',
        ]);

        $customers = [];
        $customer_orders = [];
        $locations = [];

        foreach ( $orders as $order ) {
            $customer_email = $order->get_billing_email();
            $customer_name = $order->get_formatted_billing_full_name();
            
            if ( ! isset( $customers[$customer_email] ) ) {
                $customers[$customer_email] = [
                    'name' => $customer_name,
                    'orders' => 0,
                    'total_spent' => 0,
                    'first_order' => $order->get_date_created(),
                ];
                $stats['total_customers']++;
            }
            
            $customers[$customer_email]['orders']++;
            $customers[$customer_email]['total_spent'] += $order->get_total();

            // Track locations
            $country = $order->get_billing_country();
            if ( ! isset( $locations[$country] ) ) {
                $locations[$country] = 0;
            }
            $locations[$country]++;
        }

        // Analyze customer behavior
        foreach ( $customers as $email => $data ) {
            if ( $data['orders'] == 1 ) {
                $stats['new_customers']++;
            } else {
                $stats['returning_customers']++;
            }
            $stats['customer_lifetime_value'] += $data['total_spent'];
        }

        // Get top customers
        uasort( $customers, function($a, $b) {
            return $b['total_spent'] - $a['total_spent'];
        });
        $stats['top_customers'] = array_slice( $customers, 0, 10, true );

        // Top locations
        arsort( $locations );
        $stats['customer_locations'] = array_slice( $locations, 0, 10, true );

        if ( $stats['total_customers'] > 0 ) {
            $stats['customer_lifetime_value'] = $stats['customer_lifetime_value'] / $stats['total_customers'];
        }

        return $stats;
    }

    /**
     * Get product statistics
     */
    private static function get_product_stats( $vendor_id, $date_range ) {
        $dates = self::get_date_range( $date_range );
        
        $stats = [
            'top_products' => [],
            'product_categories' => [],
            'total_items_sold' => 0,
            'unique_products' => 0,
        ];

        $orders = wc_get_orders([
            'meta_key' => '_vss_vendor_user_id',
            'meta_value' => $vendor_id,
            'date_created' => $dates['start'] . '...' . $dates['end'],
            'limit' => -1,
            'return' => 'objects',
        ]);

        $products = [];
        $categories = [];

        foreach ( $orders as $order ) {
            foreach ( $order->get_items() as $item ) {
                $product_id = $item->get_product_id();
                $product_name = $item->get_name();
                $quantity = $item->get_quantity();
                
                if ( ! isset( $products[$product_id] ) ) {
                    $products[$product_id] = [
                        'name' => $product_name,
                        'quantity' => 0,
                        'revenue' => 0,
                    ];
                    $stats['unique_products']++;
                }
                
                $products[$product_id]['quantity'] += $quantity;
                $products[$product_id]['revenue'] += $item->get_total();
                $stats['total_items_sold'] += $quantity;

                // Get product categories
                $product = $item->get_product();
                if ( $product ) {
                    $terms = get_the_terms( $product_id, 'product_cat' );
                    if ( $terms && ! is_wp_error( $terms ) ) {
                        foreach ( $terms as $term ) {
                            if ( ! isset( $categories[$term->name] ) ) {
                                $categories[$term->name] = 0;
                            }
                            $categories[$term->name] += $quantity;
                        }
                    }
                }
            }
        }

        // Sort and get top products
        uasort( $products, function($a, $b) {
            return $b['revenue'] - $a['revenue'];
        });
        $stats['top_products'] = array_slice( $products, 0, 10, true );

        // Sort categories
        arsort( $categories );
        $stats['product_categories'] = $categories;

        return $stats;
    }

    /**
     * Get trend data for charts
     */
    private static function get_trend_data( $vendor_id, $date_range ) {
        $dates = self::get_date_range( $date_range );
        $interval = self::get_chart_interval( $date_range );
        
        $trends = [
            'revenue' => [],
            'orders' => [],
            'costs' => [],
            'profit' => [],
        ];

        // Generate date labels based on interval
        $current = new DateTime( $dates['start'] );
        $end = new DateTime( $dates['end'] );
        
        while ( $current <= $end ) {
            $key = $current->format( $interval['format'] );
            $trends['revenue'][$key] = 0;
            $trends['orders'][$key] = 0;
            $trends['costs'][$key] = 0;
            $trends['profit'][$key] = 0;
            $current->modify( $interval['step'] );
        }

        // Get orders and populate trends
        $orders = wc_get_orders([
            'meta_key' => '_vss_vendor_user_id',
            'meta_value' => $vendor_id,
            'date_created' => $dates['start'] . '...' . $dates['end'],
            'limit' => -1,
            'return' => 'objects',
        ]);

        foreach ( $orders as $order ) {
            $date_key = $order->get_date_created()->date( $interval['format'] );
            
            if ( isset( $trends['orders'][$date_key] ) ) {
                $trends['orders'][$date_key]++;
                $trends['revenue'][$date_key] += $order->get_total();
                
                $costs = get_post_meta( $order->get_id(), '_vss_order_costs', true );
                if ( isset( $costs['total_cost'] ) ) {
                    $trends['costs'][$date_key] += floatval( $costs['total_cost'] );
                }
            }
        }

        // Calculate profit
        foreach ( $trends['revenue'] as $key => $revenue ) {
            $trends['profit'][$key] = $revenue - $trends['costs'][$key];
        }

        return $trends;
    }

    /**
     * Helper: Get date range
     */
    private static function get_date_range( $range ) {
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

    /**
     * Helper: Get previous period for comparison
     */
    private static function get_previous_period( $range ) {
        $dates = self::get_date_range( $range );
        $start = new DateTime( $dates['start'] );
        $end = new DateTime( $dates['end'] );
        $diff = $start->diff( $end );
        
        $prev_end = clone $start;
        $prev_end->modify( '-1 day' );
        $prev_start = clone $prev_end;
        $prev_start->modify( '-' . $diff->days . ' days' );
        
        return [
            'start' => $prev_start->format( 'Y-m-d H:i:s' ),
            'end' => $prev_end->format( 'Y-m-d H:i:s' ),
        ];
    }

    /**
     * Helper: Get chart interval based on date range
     */
    private static function get_chart_interval( $range ) {
        switch ( $range ) {
            case 'today':
            case 'yesterday':
                return ['format' => 'H:00', 'step' => '+1 hour'];
            case 'last_7_days':
                return ['format' => 'Y-m-d', 'step' => '+1 day'];
            case 'last_30_days':
                return ['format' => 'Y-m-d', 'step' => '+1 day'];
            case 'last_90_days':
                return ['format' => 'Y-W', 'step' => '+1 week'];
            case 'this_month':
            case 'last_month':
                return ['format' => 'Y-m-d', 'step' => '+1 day'];
            case 'this_year':
            case 'last_year':
                return ['format' => 'Y-m', 'step' => '+1 month'];
            default:
                return ['format' => 'Y-m-d', 'step' => '+1 day'];
        }
    }

    /**
     * Calculate average processing time
     */
    private static function calculate_avg_processing_time( $vendor_id, $dates ) {
        $orders = wc_get_orders([
            'meta_key' => '_vss_vendor_user_id',
            'meta_value' => $vendor_id,
            'status' => ['completed', 'shipped'],
            'date_created' => $dates['start'] . '...' . $dates['end'],
            'limit' => -1,
            'return' => 'objects',
        ]);

        $total_time = 0;
        $count = 0;

        foreach ( $orders as $order ) {
            $created = $order->get_date_created();
            $modified = $order->get_date_modified();
            
            if ( $created && $modified ) {
                $diff = $modified->getTimestamp() - $created->getTimestamp();
                $total_time += $diff;
                $count++;
            }
        }

        if ( $count > 0 ) {
            $avg_seconds = $total_time / $count;
            return round( $avg_seconds / 86400, 1 ); // Convert to days
        }

        return 0;
    }

    /**
     * Calculate approval rate
     */
    private static function calculate_approval_rate( $vendor_id, $type, $dates ) {
        $meta_key = $type === 'mockup' ? '_vss_mockup_status' : '_vss_production_file_status';
        
        $orders = wc_get_orders([
            'meta_key' => '_vss_vendor_user_id',
            'meta_value' => $vendor_id,
            'date_created' => $dates['start'] . '...' . $dates['end'],
            'limit' => -1,
            'return' => 'ids',
        ]);

        $total = 0;
        $approved = 0;

        foreach ( $orders as $order_id ) {
            $status = get_post_meta( $order_id, $meta_key, true );
            if ( $status ) {
                $total++;
                if ( $status === 'approved' ) {
                    $approved++;
                }
            }
        }

        if ( $total > 0 ) {
            return round( ( $approved / $total ) * 100, 1 );
        }

        return 0;
    }

    /**
     * Calculate customer satisfaction score
     */
    private static function calculate_satisfaction_score( $vendor_id, $dates ) {
        // This could integrate with a review/rating system
        // For now, we'll base it on completion rate and on-time delivery
        
        $orders = wc_get_orders([
            'meta_key' => '_vss_vendor_user_id',
            'meta_value' => $vendor_id,
            'date_created' => $dates['start'] . '...' . $dates['end'],
            'limit' => -1,
            'return' => 'objects',
        ]);

        $total = count( $orders );
        $positive_factors = 0;

        foreach ( $orders as $order ) {
            // Check if completed
            if ( $order->has_status(['completed', 'shipped']) ) {
                $positive_factors += 0.5;
                
                // Check if on time
                $ship_date = get_post_meta( $order->get_id(), '_vss_estimated_ship_date', true );
                $actual_ship = get_post_meta( $order->get_id(), '_vss_actual_ship_date', true );
                
                if ( $ship_date && $actual_ship && strtotime( $actual_ship ) <= strtotime( $ship_date ) ) {
                    $positive_factors += 0.5;
                }
            }
        }

        if ( $total > 0 ) {
            return round( ( $positive_factors / $total ) * 100, 1 );
        }

        return 0;
    }

    /**
     * Calculate late orders percentage
     */
    private static function calculate_late_orders_percentage( $vendor_id, $dates ) {
        $orders = wc_get_orders([
            'meta_key' => '_vss_vendor_user_id',
            'meta_value' => $vendor_id,
            'status' => 'processing',
            'date_created' => $dates['start'] . '...' . $dates['end'],
            'limit' => -1,
            'return' => 'ids',
        ]);

        $total = count( $orders );
        $late = 0;

        foreach ( $orders as $order_id ) {
            $ship_date = get_post_meta( $order_id, '_vss_estimated_ship_date', true );
            if ( $ship_date && strtotime( $ship_date ) < current_time( 'timestamp' ) ) {
                $late++;
            }
        }

        if ( $total > 0 ) {
            return round( ( $late / $total ) * 100, 1 );
        }

        return 0;
    }

    /**
     * Calculate quality score
     */
    private static function calculate_quality_score( $vendor_id, $dates ) {
        // Composite score based on multiple factors
        $mockup_rate = self::calculate_approval_rate( $vendor_id, 'mockup', $dates );
        $production_rate = self::calculate_approval_rate( $vendor_id, 'production_file', $dates );
        $satisfaction = self::calculate_satisfaction_score( $vendor_id, $dates );
        
        // Weight the factors
        $score = ( $mockup_rate * 0.3 ) + ( $production_rate * 0.3 ) + ( $satisfaction * 0.4 );
        
        return round( $score, 1 );
    }

    /**
     * Get orders by product SKU
     */
    private static function get_orders_by_product_sku( $sku, $vendor_id ) {
        global $wpdb;
        
        // Find products with this SKU
        $product_ids = $wpdb->get_col( $wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} 
             WHERE meta_key = '_sku' AND meta_value LIKE %s",
            '%' . $wpdb->esc_like( $sku ) . '%'
        ));
        
        if ( empty( $product_ids ) ) {
            return [];
        }
        
        // Find orders containing these products
        $order_ids = $wpdb->get_col(
            "SELECT DISTINCT order_id 
             FROM {$wpdb->prefix}woocommerce_order_items oi
             INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim ON oi.order_item_id = oim.order_item_id
             WHERE oim.meta_key IN ('_product_id', '_variation_id')
             AND oim.meta_value IN (" . implode(',', array_map('intval', $product_ids)) . ")"
        );
        
        // Filter by vendor
        if ( ! empty( $order_ids ) ) {
            $vendor_order_ids = [];
            foreach ( $order_ids as $order_id ) {
                if ( get_post_meta( $order_id, '_vss_vendor_user_id', true ) == $vendor_id ) {
                    $vendor_order_ids[] = $order_id;
                }
            }
            return $vendor_order_ids;
        }
        
        return [];
    }
}