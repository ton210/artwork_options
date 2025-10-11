<?php
/**
 * Admin System Status Page
 *
 * @package SWPD
 * @since 2.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap swpd-status">
	<h1><?php esc_html_e( 'System Status', 'swpd' ); ?></h1>
	
	<div class="swpd-status-sections">
		<!-- System Information -->
		<div class="status-section">
			<h2><?php esc_html_e( 'System Information', 'swpd' ); ?></h2>
			<table class="wp-list-table widefat">
				<tbody>
					<tr>
						<td><?php esc_html_e( 'Plugin Version', 'swpd' ); ?></td>
						<td><?php echo esc_html( SWPD_VERSION ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'WordPress Version', 'swpd' ); ?></td>
						<td><?php echo esc_html( get_bloginfo( 'version' ) ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'WooCommerce Version', 'swpd' ); ?></td>
						<td><?php echo esc_html( WC()->version ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'PHP Version', 'swpd' ); ?></td>
						<td><?php echo esc_html( PHP_VERSION ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'MySQL Version', 'swpd' ); ?></td>
						<td><?php global $wpdb; echo esc_html( $wpdb->db_version() ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		
		<!-- Server Environment -->
		<div class="status-section">
			<h2><?php esc_html_e( 'Server Environment', 'swpd' ); ?></h2>
			<table class="wp-list-table widefat">
				<tbody>
					<tr>
						<td><?php esc_html_e( 'Server Software', 'swpd' ); ?></td>
						<td><?php echo esc_html( $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'PHP Memory Limit', 'swpd' ); ?></td>
						<td><?php echo esc_html( ini_get( 'memory_limit' ) ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'PHP Max Execution Time', 'swpd' ); ?></td>
						<td><?php echo esc_html( ini_get( 'max_execution_time' ) ); ?> seconds</td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'PHP Max Upload Size', 'swpd' ); ?></td>
						<td><?php echo esc_html( ini_get( 'upload_max_filesize' ) ); ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'PHP Max Post Size', 'swpd' ); ?></td>
						<td><?php echo esc_html( ini_get( 'post_max_size' ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		
		<!-- Image Processing -->
		<div class="status-section">
			<h2><?php esc_html_e( 'Image Processing', 'swpd' ); ?></h2>
			<table class="wp-list-table widefat">
				<tbody>
					<tr>
						<td><?php esc_html_e( 'GD Library', 'swpd' ); ?></td>
						<td class="<?php echo extension_loaded( 'gd' ) ? 'status-good' : 'status-bad'; ?>">
							<?php
							if ( extension_loaded( 'gd' ) ) {
								$gd_info = gd_info();
								echo esc_html__( 'Enabled', 'swpd' ) . ' (Version: ' . esc_html( $gd_info['GD Version'] ) . ')';
							} else {
								echo esc_html__( 'Not Installed', 'swpd' );
							}
							?>
						</td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'ImageMagick', 'swpd' ); ?></td>
						<td class="<?php echo extension_loaded( 'imagick' ) ? 'status-good' : 'status-warning'; ?>">
							<?php
							if ( extension_loaded( 'imagick' ) ) {
								$imagick = new Imagick();
								$version = $imagick->getVersion();
								echo esc_html__( 'Enabled', 'swpd' ) . ' (Version: ' . esc_html( $version['versionString'] ) . ')';
							} else {
								echo esc_html__( 'Not Installed', 'swpd' ) . ' (' . esc_html__( 'Optional', 'swpd' ) . ')';
							}
							?>
						</td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'CURL', 'swpd' ); ?></td>
						<td class="<?php echo function_exists( 'curl_version' ) ? 'status-good' : 'status-bad'; ?>">
							<?php
							if ( function_exists( 'curl_version' ) ) {
								$curl = curl_version();
								echo esc_html__( 'Enabled', 'swpd' ) . ' (Version: ' . esc_html( $curl['version'] ) . ')';
							} else {
								echo esc_html__( 'Not Installed', 'swpd' );
							}
							?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		
		<!-- Database Status -->
		<div class="status-section">
			<h2><?php esc_html_e( 'Database Tables', 'swpd' ); ?></h2>
			<table class="wp-list-table widefat">
				<tbody>
					<?php
					global $wpdb;
					$tables = array(
						'swpd_designs' => $wpdb->prefix . 'swpd_designs',
						'swpd_autosaves' => $wpdb->prefix . 'swpd_autosaves',
						'swpd_analytics' => $wpdb->prefix . 'swpd_analytics'
					);
					
					foreach ( $tables as $name => $table ) :
						$exists = $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" ) === $table;
						$count = $exists ? $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ) : 0;
					?>
					<tr>
						<td><?php echo esc_html( $table ); ?></td>
						<td class="<?php echo $exists ? 'status-good' : 'status-bad'; ?>">
							<?php
							if ( $exists ) {
								echo esc_html__( 'Exists', 'swpd' ) . ' (' . esc_html( number_format( $count ) ) . ' ' . esc_html__( 'records', 'swpd' ) . ')';
							} else {
								echo esc_html__( 'Missing', 'swpd' );
							}
							?>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		
		<!-- Logs -->
		<div class="status-section" id="logs">
			<h2><?php esc_html_e( 'Recent Logs', 'swpd' ); ?></h2>
			<div class="log-controls">
				<select id="log-level-filter">
					<option value=""><?php esc_html_e( 'All Levels', 'swpd' ); ?></option>
					<option value="debug"><?php esc_html_e( 'Debug', 'swpd' ); ?></option>
					<option value="info"><?php esc_html_e( 'Info', 'swpd' ); ?></option>
					<option value="warning"><?php esc_html_e( 'Warning', 'swpd' ); ?></option>
					<option value="error"><?php esc_html_e( 'Error', 'swpd' ); ?></option>
					<option value="critical"><?php esc_html_e( 'Critical', 'swpd' ); ?></option>
				</select>
				<button class="button" id="refresh-logs"><?php esc_html_e( 'Refresh', 'swpd' ); ?></button>
				<button class="button" id="clear-logs"><?php esc_html_e( 'Clear Logs', 'swpd' ); ?></button>
			</div>
			<div id="log-viewer">
				<?php
				$logger = new SWPD_Logger();
				$logs = $logger->get_recent_entries( 100 );
				
				if ( ! empty( $logs ) ) :
				?>
				<table class="wp-list-table widefat">
					<thead>
						<tr>
							<th style="width: 150px;"><?php esc_html_e( 'Time', 'swpd' ); ?></th>
							<th style="width: 80px;"><?php esc_html_e( 'Level', 'swpd' ); ?></th>
							<th><?php esc_html_e( 'Message', 'swpd' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $logs as $log ) : ?>
						<tr class="log-<?php echo esc_attr( $log['level'] ); ?>">
							<td><?php echo esc_html( $log['timestamp'] ); ?></td>
							<td><span class="log-level log-level-<?php echo esc_attr( $log['level'] ); ?>"><?php echo esc_html( strtoupper( $log['level'] ) ); ?></span></td>
							<td><?php echo esc_html( $log['message'] ); ?></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<?php else : ?>
				<p><?php esc_html_e( 'No log entries found.', 'swpd' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<style>
.swpd-status-sections {
	margin-top: 20px;
}

.status-section {
	background: #fff;
	border: 1px solid #ccd0d4;
	padding: 20px;
	margin-bottom: 20px;
	box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.status-section h2 {
	margin-top: 0;
}

.status-good {
	color: #46b450;
	font-weight: 600;
}

.status-warning {
	color: #ffb900;
	font-weight: 600;
}

.status-bad {
	color: #dc3232;
	font-weight: 600;
}

.log-controls {
	margin-bottom: 15px;
}

.log-controls select,
.log-controls button {
	margin-right: 10px;
}

#log-viewer {
	max-height: 400px;
	overflow-y: auto;
	border: 1px solid #ddd;
	background: #f9f9f9;
}

.log-level {
	display: inline-block;
	padding: 2px 6px;
	border-radius: 3px;
	font-size: 11px;
	font-weight: 600;
}

.log-level-debug {
	background: #e0e0e0;
	color: #666;
}

.log-level-info {
	background: #d4edda;
	color: #155724;
}

.log-level-warning {
	background: #fff3cd;
	color: #856404;
}

.log-level-error {
	background: #f8d7da;
	color: #721c24;
}

.log-level-critical {
	background: #721c24;
	color: #fff;
}

.log-debug {
	opacity: 0.7;
}

.log-error,
.log-critical {
	background-color: #fff5f5;
}
</style>

<script>
jQuery(document).ready(function($) {
	// Log filtering
	$('#log-level-filter').on('change', function() {
		var level = $(this).val();
		if (level) {
			$('#log-viewer tbody tr').hide();
			$('#log-viewer tbody tr.log-' + level).show();
		} else {
			$('#log-viewer tbody tr').show();
		}
	});
	
	// Refresh logs
	$('#refresh-logs').on('click', function() {
		location.reload();
	});
	
	// Clear logs
	$('#clear-logs').on('click', function() {
		if (!confirm('<?php esc_html_e( 'Are you sure you want to clear all logs?', 'swpd' ); ?>')) {
			return;
		}
		
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'swpd_clear_logs',
				nonce: '<?php echo wp_create_nonce( 'swpd_clear_logs' ); ?>'
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