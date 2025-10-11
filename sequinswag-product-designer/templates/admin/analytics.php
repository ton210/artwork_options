<?php
/**
 * Admin Analytics Page
 *
 * @package SWPD
 * @since 2.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
$analytics_table = $wpdb->prefix . 'swpd_analytics';

// Date range
$date_from = isset( $_GET['date_from'] ) ? sanitize_text_field( $_GET['date_from'] ) : date( 'Y-m-d', strtotime( '-30 days' ) );
$date_to = isset( $_GET['date_to'] ) ? sanitize_text_field( $_GET['date_to'] ) : date( 'Y-m-d' );

// Get summary stats
$total_designs = $wpdb->get_var( $wpdb->prepare(
	"SELECT COUNT(*) FROM {$analytics_table} 
	WHERE event_type = 'design_added_to_cart' 
	AND created_at BETWEEN %s AND %s",
	$date_from . ' 00:00:00',
	$date_to . ' 23:59:59'
));

$total_orders = $wpdb->get_var( $wpdb->prepare(
	"SELECT COUNT(*) FROM {$analytics_table} 
	WHERE event_type = 'design_ordered' 
	AND created_at BETWEEN %s AND %s",
	$date_from . ' 00:00:00',
	$date_to . ' 23:59:59'
));

$unique_users = $wpdb->get_var( $wpdb->prepare(
	"SELECT COUNT(DISTINCT user_id) FROM {$analytics_table} 
	WHERE created_at BETWEEN %s AND %s 
	AND user_id > 0",
	$date_from . ' 00:00:00',
	$date_to . ' 23:59:59'
));

// Top products
$top_products = $wpdb->get_results( $wpdb->prepare(
	"SELECT product_id, COUNT(*) as count 
	FROM {$analytics_table} 
	WHERE event_type = 'design_added_to_cart'
	AND created_at BETWEEN %s AND %s
	AND product_id IS NOT NULL
	GROUP BY product_id 
	ORDER BY count DESC 
	LIMIT 10",
	$date_from . ' 00:00:00',
	$date_to . ' 23:59:59'
));
?>

<div class="wrap swpd-analytics">
	<h1><?php esc_html_e( 'Design Analytics', 'swpd' ); ?></h1>
	
	<div class="swpd-date-filter">
		<form method="get" action="">
			<input type="hidden" name="page" value="swpd-analytics" />
			<label><?php esc_html_e( 'From:', 'swpd' ); ?></label>
			<input type="date" name="date_from" value="<?php echo esc_attr( $date_from ); ?>" />
			<label><?php esc_html_e( 'To:', 'swpd' ); ?></label>
			<input type="date" name="date_to" value="<?php echo esc_attr( $date_to ); ?>" />
			<input type="submit" class="button" value="<?php esc_attr_e( 'Filter', 'swpd' ); ?>" />
		</form>
	</div>
	
	<div class="swpd-analytics-summary">
		<div class="stat-box">
			<h3><?php echo esc_html( number_format( $total_designs ) ); ?></h3>
			<p><?php esc_html_e( 'Designs Created', 'swpd' ); ?></p>
		</div>
		<div class="stat-box">
			<h3><?php echo esc_html( number_format( $total_orders ) ); ?></h3>
			<p><?php esc_html_e( 'Orders with Designs', 'swpd' ); ?></p>
		</div>
		<div class="stat-box">
			<h3><?php echo esc_html( number_format( $unique_users ) ); ?></h3>
			<p><?php esc_html_e( 'Unique Designers', 'swpd' ); ?></p>
		</div>
		<div class="stat-box">
			<h3><?php echo $total_designs > 0 ? esc_html( number_format( ( $total_orders / $total_designs ) * 100, 1 ) . '%' ) : '0%'; ?></h3>
			<p><?php esc_html_e( 'Conversion Rate', 'swpd' ); ?></p>
		</div>
	</div>
	
	<div class="swpd-analytics-charts">
		<div class="chart-container">
			<h2><?php esc_html_e( 'Design Activity Over Time', 'swpd' ); ?></h2>
			<canvas id="activity-chart" height="100"></canvas>
		</div>
		
		<div class="chart-container half">
			<h2><?php esc_html_e( 'Top Products', 'swpd' ); ?></h2>
			<table class="wp-list-table widefat">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Product', 'swpd' ); ?></th>
						<th><?php esc_html_e( 'Designs', 'swpd' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $top_products as $item ) : ?>
						<?php $product = wc_get_product( $item->product_id ); ?>
						<?php if ( $product ) : ?>
						<tr>
							<td>
								<a href="<?php echo esc_url( get_edit_post_link( $item->product_id ) ); ?>">
									<?php echo esc_html( $product->get_name() ); ?>
								</a>
							</td>
							<td><?php echo esc_html( $item->count ); ?></td>
						</tr>
						<?php endif; ?>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		
		<div class="chart-container half">
			<h2><?php esc_html_e( 'Event Distribution', 'swpd' ); ?></h2>
			<canvas id="event-chart" height="200"></canvas>
		</div>
	</div>
</div>

<style>
.swpd-date-filter {
	background: #fff;
	padding: 20px;
	margin: 20px 0;
	border: 1px solid #ddd;
}

.swpd-date-filter label {
	margin: 0 10px;
}

.swpd-analytics-summary {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
	gap: 20px;
	margin: 20px 0;
}

.stat-box {
	background: #fff;
	padding: 20px;
	text-align: center;
	border: 1px solid #ddd;
	border-radius: 4px;
}

.stat-box h3 {
	margin: 0 0 10px;
	font-size: 32px;
	color: #0073aa;
}

.stat-box p {
	margin: 0;
	color: #666;
}

.swpd-analytics-charts {
	display: grid;
	grid-template-columns: 1fr;
	gap: 20px;
}

.chart-container {
	background: #fff;
	padding: 20px;
	border: 1px solid #ddd;
	border-radius: 4px;
}

.chart-container.half {
	grid-column: span 1;
}

@media (min-width: 1200px) {
	.swpd-analytics-charts {
		grid-template-columns: 1fr 1fr;
	}
	
	.chart-container:first-child {
		grid-column: span 2;
	}
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
jQuery(document).ready(function($) {
	// Activity Chart
	<?php
	$chart_data = $wpdb->get_results( $wpdb->prepare(
		"SELECT DATE(created_at) as date, event_type, COUNT(*) as count 
		FROM {$analytics_table} 
		WHERE created_at BETWEEN %s AND %s 
		GROUP BY DATE(created_at), event_type 
		ORDER BY date ASC",
		$date_from . ' 00:00:00',
		$date_to . ' 23:59:59'
	));
	
	$dates = array();
	$designs_data = array();
	$orders_data = array();
	
	// Initialize date range
	$current = strtotime( $date_from );
	$end = strtotime( $date_to );
	
	while ( $current <= $end ) {
		$date = date( 'Y-m-d', $current );
		$dates[] = date( 'M j', $current );
		$designs_data[$date] = 0;
		$orders_data[$date] = 0;
		$current = strtotime( '+1 day', $current );
	}
	
	foreach ( $chart_data as $row ) {
		if ( $row->event_type === 'design_added_to_cart' ) {
			$designs_data[$row->date] = intval( $row->count );
		} elseif ( $row->event_type === 'design_ordered' ) {
			$orders_data[$row->date] = intval( $row->count );
		}
	}
	?>
	
	const activityCtx = document.getElementById('activity-chart').getContext('2d');
	new Chart(activityCtx, {
		type: 'line',
		data: {
			labels: <?php echo wp_json_encode( $dates ); ?>,
			datasets: [{
				label: '<?php esc_html_e( 'Designs Created', 'swpd' ); ?>',
				data: <?php echo wp_json_encode( array_values( $designs_data ) ); ?>,
				borderColor: '#0073aa',
				backgroundColor: 'rgba(0, 115, 170, 0.1)',
				tension: 0.4
			}, {
				label: '<?php esc_html_e( 'Orders', 'swpd' ); ?>',
				data: <?php echo wp_json_encode( array_values( $orders_data ) ); ?>,
				borderColor: '#46b450',
				backgroundColor: 'rgba(70, 180, 80, 0.1)',
				tension: 0.4
			}]
		},
		options: {
			responsive: true,
			maintainAspectRatio: false,
			plugins: {
				legend: {
					position: 'bottom'
				}
			},
			scales: {
				y: {
					beginAtZero: true,
					ticks: {
						stepSize: 1
					}
				}
			}
		}
	});
	
	// Event Distribution Chart
	<?php
	$event_data = $wpdb->get_results( $wpdb->prepare(
		"SELECT event_type, COUNT(*) as count 
		FROM {$analytics_table} 
		WHERE created_at BETWEEN %s AND %s 
		GROUP BY event_type",
		$date_from . ' 00:00:00',
		$date_to . ' 23:59:59'
	));
	
	$event_labels = array();
	$event_counts = array();
	
	foreach ( $event_data as $event ) {
		$labels = array(
			'designer_button_displayed' => __( 'Designer Opened', 'swpd' ),
			'design_added_to_cart' => __( 'Added to Cart', 'swpd' ),
			'design_ordered' => __( 'Ordered', 'swpd' ),
		);
		$event_labels[] = $labels[$event->event_type] ?? $event->event_type;
		$event_counts[] = intval( $event->count );
	}
	?>
	
	const eventCtx = document.getElementById('event-chart').getContext('2d');
	new Chart(eventCtx, {
		type: 'doughnut',
		data: {
			labels: <?php echo wp_json_encode( $event_labels ); ?>,
			datasets: [{
				data: <?php echo wp_json_encode( $event_counts ); ?>,
				backgroundColor: ['#0073aa', '#f0ad4e', '#46b450']
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
});
</script>