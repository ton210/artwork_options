<?php
/**
 * Admin Dashboard Template
 *
 * @package SWPD
 * @since 2.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get statistics
global $wpdb;
$designs_table = $wpdb->prefix . 'swpd_designs';
$analytics_table = $wpdb->prefix . 'swpd_analytics';

// Total designs
$total_designs = $wpdb->get_var( "SELECT COUNT(*) FROM {$designs_table}" );

// Designs this month
$designs_this_month = $wpdb->get_var( 
	$wpdb->prepare(
		"SELECT COUNT(*) FROM {$designs_table} WHERE created_at >= %s",
		date( 'Y-m-01' )
	)
);

// Active users
$active_users = $wpdb->get_var( 
	"SELECT COUNT(DISTINCT user_id) FROM {$designs_table} WHERE user_id > 0"
);

// Popular products
$popular_products = $wpdb->get_results(
	"SELECT product_id, COUNT(*) as count 
	FROM {$designs_table} 
	GROUP BY product_id 
	ORDER BY count DESC 
	LIMIT 5"
);

// Recent activity
$recent_activity = $wpdb->get_results(
	"SELECT * FROM {$analytics_table} 
	ORDER BY created_at DESC 
	LIMIT 10"
);

// Get cache stats
$cache_stats = SWPD_Cache::get_stats();
?>

<div class="wrap swpd-dashboard">
	<h1><?php esc_html_e( 'Product Designer Dashboard', 'swpd' ); ?></h1>
	
	<!-- Statistics Cards -->
	<div class="swpd-stats-grid">
		<div class="swpd-stat-card">
			<div class="stat-icon">
				<span class="dashicons dashicons-admin-customizer"></span>
			</div>
			<div class="stat-content">
				<h3><?php echo esc_html( number_format( $total_designs ) ); ?></h3>
				<p><?php esc_html_e( 'Total Designs', 'swpd' ); ?></p>
			</div>
		</div>
		
		<div class="swpd-stat-card">
			<div class="stat-icon">
				<span class="dashicons dashicons-calendar-alt"></span>
			</div>
			<div class="stat-content">
				<h3><?php echo esc_html( number_format( $designs_this_month ) ); ?></h3>
				<p><?php esc_html_e( 'Designs This Month', 'swpd' ); ?></p>
			</div>
		</div>
		
		<div class="swpd-stat-card">
			<div class="stat-icon">
				<span class="dashicons dashicons-groups"></span>
			</div>
			<div class="stat-content">
				<h3><?php echo esc_html( number_format( $active_users ) ); ?></h3>
				<p><?php esc_html_e( 'Active Designers', 'swpd' ); ?></p>
			</div>
		</div>
		
		<div class="swpd-stat-card">
			<div class="stat-icon">
				<span class="dashicons dashicons-database"></span>
			</div>
			<div class="stat-content">
				<h3><?php echo esc_html( $cache_stats['total_entries'] ); ?></h3>
				<p><?php esc_html_e( 'Cached Items', 'swpd' ); ?></p>
			</div>
		</div>
	</div>
	
	<div class="swpd-dashboard-content">
		<div class="swpd-dashboard-main">
			<!-- Chart Area -->
			<div class="swpd-card">
				<h2><?php esc_html_e( 'Design Activity (Last 30 Days)', 'swpd' ); ?></h2>
				<canvas id="swpd-activity-chart" height="300"></canvas>
			</div>
			
			<!-- Recent Activity -->
			<div class="swpd-card">
				<h2><?php esc_html_e( 'Recent Activity', 'swpd' ); ?></h2>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Event', 'swpd' ); ?></th>
							<th><?php esc_html_e( 'User', 'swpd' ); ?></th>
							<th><?php esc_html_e( 'Product', 'swpd' ); ?></th>
							<th><?php esc_html_e( 'Time', 'swpd' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( ! empty( $recent_activity ) ) : ?>
							<?php foreach ( $recent_activity as $event ) : ?>
								<tr>
									<td>
										<?php
										$event_labels = array(
											'designer_button_displayed' => __( 'Designer Opened', 'swpd' ),
											'design_added_to_cart' => __( 'Design Added to Cart', 'swpd' ),
											'design_ordered' => __( 'Design Ordered', 'swpd' ),
										);
										echo esc_html( $event_labels[ $event->event_type ] ?? $event->event_type );
										?>
									</td>
									<td>
										<?php
										if ( $event->user_id ) {
											$user = get_user_by( 'id', $event->user_id );
											echo esc_html( $user ? $user->display_name : 'User #' . $event->user_id );
										} else {
											esc_html_e( 'Guest', 'swpd' );
										}
										?>
									</td>
									<td>
										<?php
										if ( $event->product_id ) {
											$product = wc_get_product( $event->product_id );
											if ( $product ) {
												echo '<a href="' . esc_url( get_edit_post_link( $event->product_id ) ) . '">';
												echo esc_html( $product->get_name() );
												echo '</a>';
											} else {
												echo esc_html( 'Product #' . $event->product_id );
											}
										} else {
											echo '—';
										}
										?>
									</td>
									<td><?php echo esc_html( human_time_diff( strtotime( $event->created_at ) ) . ' ' . __( 'ago', 'swpd' ) ); ?></td>
								</tr>
							<?php endforeach; ?>
						<?php else : ?>
							<tr>
								<td colspan="4"><?php esc_html_e( 'No recent activity.', 'swpd' ); ?></td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
		
		<div class="swpd-dashboard-sidebar">
			<!-- Popular Products -->
			<div class="swpd-card">
				<h3><?php esc_html_e( 'Popular Products', 'swpd' ); ?></h3>
				<?php if ( ! empty( $popular_products ) ) : ?>
					<ul class="swpd-popular-list">
						<?php foreach ( $popular_products as $item ) : ?>
							<?php $product = wc_get_product( $item->product_id ); ?>
							<?php if ( $product ) : ?>
								<li>
									<a href="<?php echo esc_url( get_edit_post_link( $item->product_id ) ); ?>">
										<?php echo esc_html( $product->get_name() ); ?>
									</a>
									<span class="count"><?php echo esc_html( $item->count ); ?> <?php esc_html_e( 'designs', 'swpd' ); ?></span>
								</li>
							<?php endif; ?>
						<?php endforeach; ?>
					</ul>
				<?php else : ?>
					<p><?php esc_html_e( 'No design data yet.', 'swpd' ); ?></p>
				<?php endif; ?>
			</div>
			
			<!-- Quick Actions -->
			<div class="swpd-card">
				<h3><?php esc_html_e( 'Quick Actions', 'swpd' ); ?></h3>
				<div class="swpd-quick-actions">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=swpd-templates' ) ); ?>" class="button button-primary">
						<?php esc_html_e( 'Manage Templates', 'swpd' ); ?>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=swpd-designs' ) ); ?>" class="button">
						<?php esc_html_e( 'View All Designs', 'swpd' ); ?>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=swpd-settings' ) ); ?>" class="button">
						<?php esc_html_e( 'Settings', 'swpd' ); ?>
					</a>
				</div>
			</div>
			
			<!-- System Status -->
			<div class="swpd-card">
				<h3><?php esc_html_e( 'System Status', 'swpd' ); ?></h3>
				<ul class="swpd-status-list">
					<li>
						<span><?php esc_html_e( 'PHP Version', 'swpd' ); ?></span>
						<span class="status-value"><?php echo esc_html( PHP_VERSION ); ?></span>
					</li>
					<li>
						<span><?php esc_html_e( 'GD Library', 'swpd' ); ?></span>
						<span class="status-value <?php echo extension_loaded( 'gd' ) ? 'status-good' : 'status-bad'; ?>">
							<?php echo extension_loaded( 'gd' ) ? __( 'Enabled', 'swpd' ) : __( 'Disabled', 'swpd' ); ?>
						</span>
					</li>
					<li>
						<span><?php esc_html_e( 'ImageMagick', 'swpd' ); ?></span>
						<span class="status-value <?php echo extension_loaded( 'imagick' ) ? 'status-good' : 'status-warning'; ?>">
							<?php echo extension_loaded( 'imagick' ) ? __( 'Enabled', 'swpd' ) : __( 'Not Available', 'swpd' ); ?>
						</span>
					</li>
					<li>
						<span><?php esc_html_e( 'Memory Limit', 'swpd' ); ?></span>
						<span class="status-value"><?php echo esc_html( ini_get( 'memory_limit' ) ); ?></span>
					</li>
				</ul>
				<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=swpd-status' ) ); ?>"><?php esc_html_e( 'View Full Status', 'swpd' ); ?></a></p>
			</div>
		</div>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
	// Activity Chart
	<?php
	// Get chart data
	$chart_data = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT DATE(created_at) as date, COUNT(*) as count 
			FROM {$analytics_table} 
			WHERE created_at >= %s 
			AND event_type = 'design_added_to_cart'
			GROUP BY DATE(created_at) 
			ORDER BY date ASC",
			date( 'Y-m-d', strtotime( '-30 days' ) )
		)
	);
	
	$labels = array();
	$data = array();
	
	// Fill in missing dates
	for ( $i = 29; $i >= 0; $i-- ) {
		$date = date( 'Y-m-d', strtotime( "-{$i} days" ) );
		$labels[] = date( 'M j', strtotime( $date ) );
		$data[ $date ] = 0;
	}
	
	foreach ( $chart_data as $row ) {
		$data[ $row->date ] = intval( $row->count );
	}
	?>
	
	const ctx = document.getElementById('swpd-activity-chart').getContext('2d');
	const chart = new Chart(ctx, {
		type: 'line',
		data: {
			labels: <?php echo wp_json_encode( $labels ); ?>,
			datasets: [{
				label: '<?php esc_html_e( 'Designs Added to Cart', 'swpd' ); ?>',
				data: <?php echo wp_json_encode( array_values( $data ) ); ?>,
				borderColor: '<?php echo esc_js( get_option( 'swpd_primary_color', '#0073aa' ) ); ?>',
				backgroundColor: 'rgba(0, 115, 170, 0.1)',
				tension: 0.4
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
						stepSize: 1
					}
				}
			}
		}
	});
});
</script>

<style>
.swpd-dashboard {
	max-width: 1400px;
}

.swpd-stats-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
	gap: 20px;
	margin: 20px 0;
}

.swpd-stat-card {
	background: #fff;
	border: 1px solid #e0e0e0;
	border-radius: 8px;
	padding: 20px;
	display: flex;
	align-items: center;
	gap: 20px;
	transition: transform 0.2s, box-shadow 0.2s;
}

.swpd-stat-card:hover {
	transform: translateY(-2px);
	box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.stat-icon {
	background: #f0f0f0;
	border-radius: 50%;
	width: 60px;
	height: 60px;
	display: flex;
	align-items: center;
	justify-content: center;
}

.stat-icon .dashicons {
	font-size: 30px;
	width: 30px;
	height: 30px;
	color: var(--swpd-primary);
}

.stat-content h3 {
	margin: 0 0 5px;
	font-size: 28px;
	font-weight: 600;
}

.stat-content p {
	margin: 0;
	color: #666;
}

.swpd-dashboard-content {
	display: grid;
	grid-template-columns: 1fr 350px;
	gap: 20px;
	margin-top: 30px;
}

.swpd-card {
	background: #fff;
	border: 1px solid #e0e0e0;
	border-radius: 8px;
	padding: 20px;
	margin-bottom: 20px;
}

.swpd-card h2,
.swpd-card h3 {
	margin-top: 0;
	margin-bottom: 20px;
	font-weight: 600;
}

.swpd-popular-list {
	list-style: none;
	margin: 0;
	padding: 0;
}

.swpd-popular-list li {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 10px 0;
	border-bottom: 1px solid #f0f0f0;
}

.swpd-popular-list li:last-child {
	border-bottom: none;
}

.swpd-popular-list .count {
	color: #666;
	font-size: 0.9em;
}

.swpd-quick-actions {
	display: flex;
	flex-direction: column;
	gap: 10px;
}

.swpd-quick-actions .button {
	width: 100%;
	text-align: center;
}

.swpd-status-list {
	list-style: none;
	margin: 0;
	padding: 0;
}

.swpd-status-list li {
	display: flex;
	justify-content: space-between;
	padding: 8px 0;
	border-bottom: 1px solid #f0f0f0;
}

.swpd-status-list li:last-child {
	border-bottom: none;
}

.status-value {
	font-weight: 600;
}

.status-good {
	color: #46b450;
}

.status-warning {
	color: #ffb900;
}

.status-bad {
	color: #dc3232;
}

@media (max-width: 1200px) {
	.swpd-dashboard-content {
		grid-template-columns: 1fr;
	}
	
	.swpd-dashboard-sidebar {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
		gap: 20px;
	}
}
</style>