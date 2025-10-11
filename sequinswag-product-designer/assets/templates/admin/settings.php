<?php
/**
 * Settings Page Template
 *
 * @package SWPD
 * @since 2.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Initialize Cloudinary if needed
if ( ! isset( $cloudinary ) ) {
	$cloudinary = new SWPD_Cloudinary( $GLOBALS['swpd_logger'] ?? new SWPD_Logger() );
}

// Get the active tab
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'general';
?>

<div class="wrap swpd-settings">
	<h1><?php esc_html_e( 'Product Designer Settings', 'swpd' ); ?></h1>

	<?php
	// Show any settings errors/messages
	settings_errors( 'swpd_settings' );
	?>

	<h2 class="nav-tab-wrapper">
		<a href="?page=swpd-settings&tab=general" class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'General', 'swpd' ); ?>
		</a>
		<a href="?page=swpd-settings&tab=cloudinary" class="nav-tab <?php echo $active_tab === 'cloudinary' ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Cloudinary', 'swpd' ); ?>
		</a>
		<a href="?page=swpd-settings&tab=advanced" class="nav-tab <?php echo $active_tab === 'advanced' ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Advanced', 'swpd' ); ?>
		</a>
		<a href="?page=swpd-settings&tab=api" class="nav-tab <?php echo $active_tab === 'api' ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'API', 'swpd' ); ?>
		</a>
	</h2>

	<form method="post" action="options.php">
		<?php settings_fields( 'swpd-settings' ); ?>

		<!-- General Tab -->
		<div id="general-tab" class="tab-content" style="<?php echo $active_tab !== 'general' ? 'display:none;' : ''; ?>">
			<h2><?php esc_html_e( 'General Settings', 'swpd' ); ?></h2>

			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="swpd_max_upload_size"><?php esc_html_e( 'Max Upload Size', 'swpd' ); ?></label>
					</th>
					<td>
						<input type="number" name="swpd_max_upload_size" id="swpd_max_upload_size"
							value="<?php echo esc_attr( get_option( 'swpd_max_upload_size', 5 ) ); ?>"
							min="1" max="50" class="small-text" />
						<span><?php esc_html_e( 'MB', 'swpd' ); ?></span>
						<p class="description">
							<?php esc_html_e( 'Maximum file size for user uploads.', 'swpd' ); ?>
						</p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="swpd_max_image_dimensions"><?php esc_html_e( 'Max Image Dimensions', 'swpd' ); ?></label>
					</th>
					<td>
						<input type="number" name="swpd_max_image_dimensions" id="swpd_max_image_dimensions"
							value="<?php echo esc_attr( get_option( 'swpd_max_image_dimensions', 4000 ) ); ?>"
							min="500" max="10000" class="small-text" />
						<span><?php esc_html_e( 'pixels', 'swpd' ); ?></span>
						<p class="description">
							<?php esc_html_e( 'Maximum width or height for uploaded images.', 'swpd' ); ?>
						</p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="swpd_upload_limit_per_hour"><?php esc_html_e( 'Upload Rate Limit', 'swpd' ); ?></label>
					</th>
					<td>
						<input type="number" name="swpd_upload_limit_per_hour" id="swpd_upload_limit_per_hour"
							value="<?php echo esc_attr( get_option( 'swpd_upload_limit_per_hour', 20 ) ); ?>"
							min="1" max="100" class="small-text" />
						<span><?php esc_html_e( 'uploads per hour', 'swpd' ); ?></span>
						<p class="description">
							<?php esc_html_e( 'Maximum number of uploads allowed per user per hour.', 'swpd' ); ?>
						</p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Features', 'swpd' ); ?></th>
					<td>
						<fieldset>
							<label>
								<input type="checkbox" name="swpd_enable_autosave" value="1"
									<?php checked( get_option( 'swpd_enable_autosave', true ) ); ?> />
								<?php esc_html_e( 'Enable auto-save', 'swpd' ); ?>
							</label>
							<br>
							<label>
								<input type="checkbox" name="swpd_enable_templates" value="1"
									<?php checked( get_option( 'swpd_enable_templates', true ) ); ?> />
								<?php esc_html_e( 'Enable design templates', 'swpd' ); ?>
							</label>
							<br>
							<label>
								<input type="checkbox" name="swpd_enable_analytics" value="1"
									<?php checked( get_option( 'swpd_enable_analytics', true ) ); ?> />
								<?php esc_html_e( 'Enable analytics tracking', 'swpd' ); ?>
							</label>
						</fieldset>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="swpd_autosave_interval"><?php esc_html_e( 'Auto-save Interval', 'swpd' ); ?></label>
					</th>
					<td>
						<input type="number" name="swpd_autosave_interval" id="swpd_autosave_interval"
							value="<?php echo esc_attr( get_option( 'swpd_autosave_interval', 30 ) ); ?>"
							min="10" max="300" class="small-text" />
						<span><?php esc_html_e( 'seconds', 'swpd' ); ?></span>
						<p class="description">
							<?php esc_html_e( 'How often to auto-save user designs.', 'swpd' ); ?>
						</p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Colors', 'swpd' ); ?></th>
					<td>
						<div style="display: flex; gap: 20px;">
							<div>
								<label for="swpd_primary_color"><?php esc_html_e( 'Primary Color', 'swpd' ); ?></label><br>
								<input type="text" name="swpd_primary_color" id="swpd_primary_color"
									value="<?php echo esc_attr( get_option( 'swpd_primary_color', '#0073aa' ) ); ?>"
									class="swpd-color-picker" />
							</div>
							<div>
								<label for="swpd_secondary_color"><?php esc_html_e( 'Secondary Color', 'swpd' ); ?></label><br>
								<input type="text" name="swpd_secondary_color" id="swpd_secondary_color"
									value="<?php echo esc_attr( get_option( 'swpd_secondary_color', '#f0ad4e' ) ); ?>"
									class="swpd-color-picker" />
							</div>
						</div>
					</td>
				</tr>
			</table>
		</div>

		<!-- Cloudinary Tab -->
		<div id="cloudinary-tab" class="tab-content" style="<?php echo $active_tab !== 'cloudinary' ? 'display:none;' : ''; ?>">
			<?php $cloudinary->render_settings(); ?>
		</div>

		<!-- Advanced Tab -->
		<div id="advanced-tab" class="tab-content" style="<?php echo $active_tab !== 'advanced' ? 'display:none;' : ''; ?>">
			<h2><?php esc_html_e( 'Advanced Settings', 'swpd' ); ?></h2>

			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'Image Processing', 'swpd' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="swpd_enable_imagick" value="1"
								<?php checked( get_option( 'swpd_enable_imagick', extension_loaded( 'imagick' ) ) ); ?>
								<?php disabled( ! extension_loaded( 'imagick' ) ); ?> />
							<?php esc_html_e( 'Use ImageMagick for image processing', 'swpd' ); ?>
							<?php if ( ! extension_loaded( 'imagick' ) ) : ?>
								<span style="color: #999;"><?php esc_html_e( '(Not available)', 'swpd' ); ?></span>
							<?php endif; ?>
						</label>
						<p class="description">
							<?php esc_html_e( 'ImageMagick provides better image quality and performance.', 'swpd' ); ?>
						</p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Debug Mode', 'swpd' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="swpd_debug_mode" value="1"
								<?php checked( get_option( 'swpd_debug_mode', false ) ); ?> />
							<?php esc_html_e( 'Enable debug mode', 'swpd' ); ?>
						</label>
						<p class="description">
							<?php esc_html_e( 'Enables detailed logging for troubleshooting.', 'swpd' ); ?>
						</p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Email Notifications', 'swpd' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="swpd_email_notifications" value="1"
								<?php checked( get_option( 'swpd_email_notifications', true ) ); ?> />
							<?php esc_html_e( 'Send email notifications for critical errors', 'swpd' ); ?>
						</label>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Uninstall', 'swpd' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="swpd_remove_data_on_uninstall" value="1"
								<?php checked( get_option( 'swpd_remove_data_on_uninstall', false ) ); ?> />
							<?php esc_html_e( 'Remove all data when plugin is uninstalled', 'swpd' ); ?>
						</label>
						<p class="description" style="color: #d63638;">
							<?php esc_html_e( 'Warning: This will permanently delete all designs, templates, and settings when the plugin is uninstalled.', 'swpd' ); ?>
						</p>
					</td>
				</tr>
			</table>
		</div>

		<!-- API Tab -->
		<div id="api-tab" class="tab-content" style="<?php echo $active_tab !== 'api' ? 'display:none;' : ''; ?>">
			<h2><?php esc_html_e( 'API Settings', 'swpd' ); ?></h2>

			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'REST API', 'swpd' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="swpd_enable_rest_api" value="1"
								<?php checked( get_option( 'swpd_enable_rest_api', true ) ); ?> />
							<?php esc_html_e( 'Enable REST API endpoints', 'swpd' ); ?>
						</label>
						<p class="description">
							<?php
							printf(
								__( 'API Base URL: %s', 'swpd' ),
								'<code>' . rest_url( 'swpd/v1' ) . '</code>'
							);
							?>
						</p>
					</td>
				</tr>
			</table>

			<h3><?php esc_html_e( 'API Keys', 'swpd' ); ?></h3>
			<p><?php esc_html_e( 'Generate API keys for external applications to access the designer API.', 'swpd' ); ?></p>

			<div id="swpd-api-keys">
				<?php
				$api_keys = get_option( 'swpd_api_keys', array() );
				if ( empty( $api_keys ) ) {
					echo '<p>' . esc_html__( 'No API keys created yet.', 'swpd' ) . '</p>';
				} else {
					?>
					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Name', 'swpd' ); ?></th>
								<th><?php esc_html_e( 'Key', 'swpd' ); ?></th>
								<th><?php esc_html_e( 'Permissions', 'swpd' ); ?></th>
								<th><?php esc_html_e( 'Created', 'swpd' ); ?></th>
								<th><?php esc_html_e( 'Actions', 'swpd' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $api_keys as $key ) : ?>
								<tr>
									<td><?php echo esc_html( $key['name'] ); ?></td>
									<td><code><?php echo esc_html( substr( $key['key'], 0, 10 ) . '...' ); ?></code></td>
									<td><?php echo esc_html( implode( ', ', $key['permissions'] ) ); ?></td>
									<td><?php echo esc_html( date_i18n( get_option( 'date_format' ), $key['created'] ) ); ?></td>
									<td>
										<button class="button button-small swpd-revoke-api-key" data-key-id="<?php echo esc_attr( $key['id'] ); ?>">
											<?php esc_html_e( 'Revoke', 'swpd' ); ?>
										</button>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
					<?php
				}
				?>
			</div>

			<p>
				<button type="button" class="button button-secondary" id="swpd-generate-api-key">
					<?php esc_html_e( 'Generate New API Key', 'swpd' ); ?>
				</button>
			</p>
		</div>

		<?php submit_button(); ?>
	</form>
</div>

<script>
jQuery(document).ready(function($) {
	// Color pickers
	if ($.fn.wpColorPicker) {
		$('.swpd-color-picker').wpColorPicker();
	}

	// API key generation
	$('#swpd-generate-api-key').on('click', function() {
		// You would implement API key generation here
		alert('API key generation would be implemented here');
	});

	// API key revocation
	$('.swpd-revoke-api-key').on('click', function() {
		if (!confirm('Are you sure you want to revoke this API key?')) {
			return;
		}

		var keyId = $(this).data('key-id');
		// Implementation for revoking API key would go here
		alert('Revoke API key: ' + keyId);
	});
});
</script>

<style>
.swpd-settings .form-table th {
	width: 200px;
}
.tab-content {
	background: #fff;
	padding: 20px;
	border: 1px solid #ccd0d4;
	border-top: none;
	margin-top: -1px;
}
.nav-tab-wrapper {
	margin-bottom: 0;
}
.nav-tab {
	cursor: pointer;
}
</style>