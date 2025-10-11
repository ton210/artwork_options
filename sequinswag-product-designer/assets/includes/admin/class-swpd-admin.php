<?php
/**
 * SWPD Admin Class
 *
 * @package SWPD
 * @since 2.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SWPD_Admin
 * * Handles admin functionality
 */
class SWPD_Admin {
	
	/**
	 * Logger instance
	 *
	 * @var SWPD_Logger
	 */
	private $logger;
	
	/**
	 * Constructor
	 *
	 * @param SWPD_Logger $logger
	 */
	public function __construct( $logger ) {
		$this->logger = $logger;
	}
	
	/**
	 * Initialize admin hooks
	 */
	public function init() {
		// Product meta boxes
		add_action( 'woocommerce_product_options_general_product_data', array( $this, 'add_design_tool_layer_field_to_simple_products' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_design_tool_layer_field_for_simple_products' ) );
		add_action( 'woocommerce_product_after_variable_attributes', array( $this, 'add_design_tool_layer_field_to_variations' ), 10, 3 );
		add_action( 'woocommerce_save_product_variation', array( $this, 'save_design_tool_layer_field_for_variations' ), 10, 2 );
		
		// Admin menu
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		
		// Admin scripts and styles
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		// Register settings
		add_action( 'admin_init', array( $this, 'register_swpd_settings' ) );
		
		// Order admin columns
		add_filter( 'manage_edit-shop_order_columns', array( $this, 'add_custom_design_column' ) );
		add_action( 'manage_shop_order_posts_custom_column', array( $this, 'display_custom_design_column' ), 10, 2 );
		
		// AJAX handlers for admin
		add_action( 'wp_ajax_swpd_get_design_preview', array( $this, 'ajax_get_design_preview' ) );
		add_action( 'wp_ajax_swpd_validate_design_json', array( $this, 'ajax_validate_design_json' ) );
		
		// Bulk actions
		add_filter( 'bulk_actions-edit-shop_order', array( $this, 'add_bulk_export_designs' ) );
		add_filter( 'handle_bulk_actions-edit-shop_order', array( $this, 'handle_bulk_export_designs' ), 10, 3 );
	}
	
	/**
	 * Add admin menu items
	 */
	public function add_admin_menu() {
		// Main menu
		add_menu_page(
			__( 'Product Designer', 'swpd' ),
			__( 'Product Designer', 'swpd' ),
			'manage_woocommerce',
			'swpd-dashboard',
			array( $this, 'render_dashboard_page' ),
			'dashicons-admin-customizer',
			55
		);
		
		// Submenus
		add_submenu_page(
			'swpd-dashboard',
			__( 'Dashboard', 'swpd' ),
			__( 'Dashboard', 'swpd' ),
			'manage_woocommerce',
			'swpd-dashboard',
			array( $this, 'render_dashboard_page' )
		);
		
		add_submenu_page(
			'swpd-dashboard',
			__( 'Customer Designs', 'swpd' ),
			__( 'Designs', 'swpd' ),
			'manage_woocommerce',
			'swpd-designs',
			array( $this, 'render_designs_page' )
		);
		
		add_submenu_page(
			'swpd-dashboard',
			__( 'Templates', 'swpd' ),
			__( 'Templates', 'swpd' ),
			'manage_woocommerce',
			'swpd-templates',
			array( $this, 'render_templates_page' )
		);
		
		add_submenu_page(
			'swpd-dashboard',
			__( 'Analytics', 'swpd' ),
			__( 'Analytics', 'swpd' ),
			'manage_woocommerce',
			'swpd-analytics',
			array( $this, 'render_analytics_page' )
		);
		
		add_submenu_page(
			'swpd-dashboard',
			__( 'Settings', 'swpd' ),
			__( 'Settings', 'swpd' ),
			'manage_woocommerce',
			'swpd-settings',
			array( $this, 'render_settings_page' )
		);
		
		add_submenu_page(
			'swpd-dashboard',
			__( 'System Status', 'swpd' ),
			__( 'System Status', 'swpd' ),
			'manage_woocommerce',
			'swpd-status',
			array( $this, 'render_status_page' )
		);

		// Add debug submenu (only for admins in debug mode)
		if ( current_user_can( 'manage_options' ) && ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ) {
			add_submenu_page(
				'swpd-dashboard',
				__( 'Debug Test', 'swpd' ),
				__( 'Debug Test', 'swpd' ),
				'manage_options',
				'swpd-debug-test',
				array( $this, 'render_debug_test_page' )
			);
		}
	}

	/**
	 * Enqueue admin assets
	 *
	 * @param string $hook
	 */
	public function enqueue_admin_assets( $hook ) {
		// Only load on our admin pages and product edit pages
		if ( strpos( $hook, 'swpd' ) === false && ! in_array( $hook, array( 'post.php', 'post-new.php' ) ) ) {
			return;
		}

		// Admin styles
		wp_enqueue_style(
			'swpd-admin-styles',
			SWPD_PLUGIN_URL . 'assets/css/admin-styles.css',
			array(),
			SWPD_VERSION
		);

		// Admin scripts
		wp_enqueue_script(
			'swpd-admin-scripts',
			SWPD_PLUGIN_URL . 'assets/js/admin-scripts.js',
			array( 'jquery', 'wp-color-picker' ),
			SWPD_VERSION,
			true
		);

		// Chart.js for analytics
		if ( $hook === 'toplevel_page_swpd-dashboard' ) { // Adjusted hook name
			wp_enqueue_script(
				'chartjs',
				'https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js',
				array(),
				'3.9.1'
			);
		}

		// Localize script
		wp_localize_script( 'swpd-admin-scripts', 'swpdAdmin', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'swpd_admin_nonce' ),
			'strings' => array(
				'confirmDelete' => __( 'Are you sure you want to delete this?', 'swpd' ),
				'loading' => __( 'Loading...', 'swpd' ),
				'error' => __( 'An error occurred. Please try again.', 'swpd' )
			)
		));

		// Color picker
		wp_enqueue_style( 'wp-color-picker' );

		// Media uploader
		if ( $hook === 'post.php' || $hook === 'post-new.php' ) {
			wp_enqueue_media();
		}
	}

	/**
	 * Add "Design Tool Layer" custom field to simple product general tab
	 */
	public function add_design_tool_layer_field_to_simple_products() {
		echo '<div class="options_group show_if_simple">';

		woocommerce_wp_textarea_input(
			array(
				'id'          => 'design_tool_layer',
				'label'       => __( 'Design Tool Layer Data', 'swpd' ),
				'placeholder' => __( 'Enter JSON data or use the helper below', 'swpd' ),
				'description' => __( 'JSON configuration for the design tool. Use the helper below or enter manually.', 'swpd' ),
				'desc_tip'    => true,
				'custom_attributes' => array(
					'rows' => 5,
					'style' => 'width:100%; font-family: monospace;'
				)
			)
		);

		// Add helper UI
		$this->render_design_layer_helper();

		echo '</div>';
	}

	/**
	 * Save "Design Tool Layer" custom field for simple products
	 *
	 * @param int $post_id The product ID
	 */
	public function save_design_tool_layer_field_for_simple_products( $post_id ) {
		if ( isset( $_POST['design_tool_layer'] ) ) {
			$design_data = wp_unslash( $_POST['design_tool_layer'] );

			// Validate JSON
			$validated = $this->validate_design_json( $design_data );

			if ( is_wp_error( $validated ) ) {
				// Add admin notice
				add_action( 'admin_notices', function() use ( $validated ) {
					?>
					<div class="notice notice-error is-dismissible">
						<p><?php echo esc_html( $validated->get_error_message() ); ?></p>
					</div>
					<?php
				});
			} else {
				update_post_meta( $post_id, 'design_tool_layer', $validated );
				$this->logger->info( 'Design tool layer saved for product', array( 'product_id' => $post_id ) );
			}
		}
	}

	/**
	 * Add "Design Tool Layer" custom field to product variations
	 *
	 * @param int     $loop           The loop counter
	 * @param array   $variation_data The variation data
	 * @param WP_Post $variation      The WP_Post object for the variation
	 */
	public function add_design_tool_layer_field_to_variations( $loop, $variation_data, $variation ) {
		$design_tool_layer = get_post_meta( $variation->ID, 'design_tool_layer', true );
		?>
		<div class="variable_custom_field">
			<p class="form-row form-row-full">
				<label for="variable_design_tool_layer[<?php echo esc_attr( $loop ); ?>]">
					<?php esc_html_e( 'Design Tool Layer Data (for this variation)', 'swpd' ); ?>
					<?php echo wc_help_tip( __( 'JSON configuration specific to this variation. Leave empty to use parent product settings.', 'swpd' ) ); ?>
				</label>
				<textarea name="variable_design_tool_layer[<?php echo esc_attr( $loop ); ?>]"
						  id="variable_design_tool_layer[<?php echo esc_attr( $loop ); ?>]"
						  rows="3" cols="20"
						  style="width:100%; font-family: monospace;"
						  class="swpd-variation-design-data"><?php echo esc_textarea( is_string($design_tool_layer) ? $design_tool_layer : wp_json_encode($design_tool_layer) ); ?></textarea>
			</p>
			<p class="form-row form-row-full">
				<button type="button" class="button swpd-design-helper-btn" data-loop="<?php echo esc_attr( $loop ); ?>">
					<?php esc_html_e( 'Open Design Helper', 'swpd' ); ?>
				</button>
				<button type="button" class="button swpd-validate-json-btn" data-loop="<?php echo esc_attr( $loop ); ?>">
					<?php esc_html_e( 'Validate JSON', 'swpd' ); ?>
				</button>
			</p>
		</div>
		<?php
	}

	/**
	 * Save "Design Tool Layer" custom field for product variations
	 *
	 * @param int $variation_id The ID of the variation
	 * @param int $i            The loop counter
	 */
	public function save_design_tool_layer_field_for_variations( $variation_id, $i ) {
		if ( isset( $_POST['variable_design_tool_layer'][ $i ] ) ) {
			$design_data = wp_unslash( $_POST['variable_design_tool_layer'][ $i ] );

			if ( ! empty( $design_data ) ) {
				// Validate JSON
				$validated = $this->validate_design_json( $design_data );

				if ( ! is_wp_error( $validated ) ) {
					update_post_meta( $variation_id, 'design_tool_layer', $validated );
					$this->logger->info( 'Design tool layer saved for variation', array( 'variation_id' => $variation_id ) );
				}
			} else {
				delete_post_meta( $variation_id, 'design_tool_layer' );
			}
		}
	}

	/**
	 * Render design layer helper UI
	 */
	private function render_design_layer_helper() {
		?>
		<div class="swpd-design-helper" style="margin-top: 10px;">
			<h4><?php esc_html_e( 'Design Configuration Helper', 'swpd' ); ?></h4>
			<div class="swpd-helper-fields">
				<p>
					<label><?php esc_html_e( 'Base Image URL:', 'swpd' ); ?></label>
					<input type="text" class="swpd-base-image-url" style="width: 70%;" />
					<button type="button" class="button swpd-media-upload" data-field="base-image">
						<?php esc_html_e( 'Upload', 'swpd' ); ?>
					</button>
				</p>
				<p>
					<label><?php esc_html_e( 'Alpha Mask URL:', 'swpd' ); ?></label>
					<input type="text" class="swpd-alpha-mask-url" style="width: 70%;" />
					<button type="button" class="button swpd-media-upload" data-field="alpha-mask">
						<?php esc_html_e( 'Upload', 'swpd' ); ?>
					</button>
				</p>
				<p>
					<label><?php esc_html_e( 'Unclipped Mask URL:', 'swpd' ); ?></label>
					<input type="text" class="swpd-unclipped-mask-url" style="width: 70%;" />
					<button type="button" class="button swpd-media-upload" data-field="unclipped-mask">
						<?php esc_html_e( 'Upload', 'swpd' ); ?>
					</button>
				</p>
				<p>
					<button type="button" class="button button-primary swpd-generate-json">
						<?php esc_html_e( 'Generate JSON', 'swpd' ); ?>
					</button>
					<button type="button" class="button swpd-preview-design">
						<?php esc_html_e( 'Preview', 'swpd' ); ?>
					</button>
				</p>
			</div>
			<div class="swpd-preview-area" style="display:none; margin-top: 20px;">
				<h4><?php esc_html_e( 'Preview', 'swpd' ); ?></h4>
				<div class="swpd-preview-container" style="max-width: 400px; margin: 0 auto; position: relative;">
					</div>
			</div>
		</div>

		<script type="text/javascript">
		jQuery(document).ready(function($) {
			// Media upload
			$('.swpd-media-upload').on('click', function(e) {
				e.preventDefault();
				var field = $(this).data('field');
				var input = $(this).prev('input');

				var mediaUploader = wp.media({
					title: 'Select Image',
					button: { text: 'Use this image' },
					multiple: false
				});

				mediaUploader.on('select', function() {
					var attachment = mediaUploader.state().get('selection').first().toJSON();
					input.val(attachment.url);
				});

				mediaUploader.open();
			});

			// Generate JSON
			$('.swpd-generate-json').on('click', function() {
				var baseImage = $('.swpd-base-image-url').val();
				var alphaMask = $('.swpd-alpha-mask-url').val();
				var unclippedMask = $('.swpd-unclipped-mask-url').val();

				if (!baseImage || !alphaMask) {
					alert('Please provide at least Base Image and Alpha Mask URLs');
					return;
				}

				var jsonData = {
					baseImage: baseImage,
					alphaMask: alphaMask
				};

				if (unclippedMask) {
					jsonData.unclippedMask = unclippedMask;
				}

				$('#design_tool_layer').val(JSON.stringify(jsonData, null, 2));
			});

			// Preview design
			$('.swpd-preview-design').on('click', function() {
				var jsonStr = $('#design_tool_layer').val();

				if (!jsonStr) {
					alert('Please generate or enter JSON data first');
					return;
				}

				try {
					var data = JSON.parse(jsonStr);

					var previewHtml = '<div style="position: relative; display: inline-block;">';
					previewHtml += '<img src="' + data.baseImage + '" style="max-width: 100%; height: auto;" />';
					if (data.unclippedMask || data.alphaMask) {
						previewHtml += '<img src="' + (data.unclippedMask || data.alphaMask) + '" style="position: absolute; top: 0; left: 0; max-width: 100%; height: auto; opacity: 0.5;" />';
					}
					previewHtml += '</div>';

					$('.swpd-preview-container').html(previewHtml);
					$('.swpd-preview-area').show();

				} catch (e) {
					alert('Invalid JSON: ' + e.message);
				}
			});
		});
		</script>
		<?php
	}

	/**
	 * Validate design JSON
	 *
	 * @param string $json
	 * @return string|WP_Error
	 */
	private function validate_design_json( $json ) {
		// If it's not a string, it might be an array already, encode it.
		if ( !is_string($json) ) {
			$json = wp_json_encode($json);
		}
		
		$data = json_decode( $json, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return new WP_Error( 'invalid_json', __( 'Invalid JSON format in design tool layer data.', 'swpd' ) );
		}

		// Required fields
		$required_fields = array( 'baseImage', 'alphaMask' );
		foreach ( $required_fields as $field ) {
			if ( empty( $data[ $field ] ) ) {
				return new WP_Error(
					'missing_field',
					sprintf( __( 'Required field "%s" is missing in design tool layer data.', 'swpd' ), $field )
				);
			}
		}

		// Validate URLs
		$url_fields = array( 'baseImage', 'alphaMask', 'unclippedMask', 'clippedMasked' );
		foreach ( $url_fields as $field ) {
			if ( ! empty( $data[ $field ] ) && ! filter_var( $data[ $field ], FILTER_VALIDATE_URL ) ) {
				return new WP_Error(
					'invalid_url',
					sprintf( __( 'Invalid URL in field "%s".', 'swpd' ), $field )
				);
			}
		}

		// Return cleaned JSON
		return wp_json_encode( $data );
	}

	/**
	 * Add custom design column to orders list
	 *
	 * @param array $columns
	 * @return array
	 */
	public function add_custom_design_column( $columns ) {
		$new_columns = array();

		foreach ( $columns as $key => $column ) {
			$new_columns[ $key ] = $column;

			// Add after order status
			if ( 'order_status' === $key ) {
				$new_columns['custom_design'] = __( 'Design', 'swpd' );
			}
		}

		return $new_columns;
	}

	/**
	 * Display custom design column content
	 *
	 * @param string $column
	 * @param int $post_id
	 */
	public function display_custom_design_column( $column, $post_id ) {
		if ( 'custom_design' === $column ) {
			$order = wc_get_order( $post_id );
			$has_design = false;

			foreach ( $order->get_items() as $item ) {
				if ( $item->get_meta( '_swpd_design_preview' ) ) {
					$has_design = true;
					break;
				}
			}

			if ( $has_design ) {
				echo '<span class="dashicons dashicons-admin-customizer" title="' . esc_attr__( 'Has custom design', 'swpd' ) . '"></span>';
			}
		}
	}

	/**
	 * AJAX handler for design preview
	 */
	public function ajax_get_design_preview() {
		check_ajax_referer( 'swpd_admin_nonce', 'nonce' );

		$order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
		$item_id = isset( $_POST['item_id'] ) ? absint( $_POST['item_id'] ) : 0;

		if ( ! $order_id || ! $item_id ) {
			wp_send_json_error( __( 'Invalid request', 'swpd' ) );
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			wp_send_json_error( __( 'Order not found', 'swpd' ) );
		}

		$item = $order->get_item( $item_id );
		if ( ! $item ) {
			wp_send_json_error( __( 'Order item not found', 'swpd' ) );
		}

		$preview_url = $item->get_meta( '_swpd_design_preview' );
		$canvas_data = $item->get_meta( '_swpd_canvas_data' );
		$production_images = $item->get_meta( '_swpd_production_images' );

		wp_send_json_success( array(
			'preview_url' => $preview_url,
			'canvas_data' => $canvas_data,
			'production_images' => $production_images
		));
	}

	/**
	 * AJAX handler for JSON validation
	 */
	public function ajax_validate_design_json() {
		check_ajax_referer( 'swpd_admin_nonce', 'nonce' );

		$json = isset( $_POST['json'] ) ? wp_unslash( $_POST['json'] ) : '';

		$validated = $this->validate_design_json( $json );

		if ( is_wp_error( $validated ) ) {
			wp_send_json_error( $validated->get_error_message() );
		} else {
			wp_send_json_success( array(
				'message' => __( 'JSON is valid!', 'swpd' ),
				'formatted' => json_decode($validated) // send back formatted object
			));
		}
	}

	/**
	 * Add bulk export designs action
	 *
	 * @param array $actions
	 * @return array
	 */
	public function add_bulk_export_designs( $actions ) {
		$actions['export_designs'] = __( 'Export Custom Designs', 'swpd' );
		return $actions;
	}

	/**
	 * Handle bulk export designs
	 *
	 * @param string $redirect_to
	 * @param string $action
	 * @param array $post_ids
	 * @return string
	 */
	public function handle_bulk_export_designs( $redirect_to, $action, $post_ids ) {
		if ( $action !== 'export_designs' ) {
			return $redirect_to;
		}

		// Create export file
		$export_data = array();

		foreach ( $post_ids as $order_id ) {
			$order = wc_get_order( $order_id );
			if ( ! $order ) {
				continue;
			}

			foreach ( $order->get_items() as $item_id => $item ) {
				$design_data = $item->get_meta( '_swpd_canvas_data' );
				if ( $design_data ) {
					$export_data[] = array(
						'order_id' => $order_id,
						'item_id' => $item_id,
						'order_date' => $order->get_date_created()->format( 'Y-m-d H:i:s' ),
						'customer_email' => $order->get_billing_email(),
						'product_name' => $item->get_name(),
						'design_preview' => $item->get_meta( '_swpd_design_preview' ),
						'production_images' => $item->get_meta( '_swpd_production_images' ),
						'design_data' => $design_data
					);
				}
			}
		}

		if ( empty( $export_data ) ) {
			add_settings_error('swpd_notices', 'swpd_export_no_designs', __('No orders with custom designs were selected.', 'swpd'), 'warning');
			return $redirect_to;
		}

		// Generate CSV
		$upload_dir = wp_upload_dir();
		$export_dir = $upload_dir['basedir'] . '/swpd-exports/';
		wp_mkdir_p( $export_dir );
		$export_file = $export_dir . 'designs-' . date( 'Y-m-d-His' ) . '.csv';
		
		$fp = fopen( $export_file, 'w' );

		// Headers
		fputcsv( $fp, array( 'Order ID', 'Item ID', 'Date', 'Customer', 'Product', 'Preview URL', 'Production URLs', 'Design JSON' ) );

		// Data
		foreach ( $export_data as $row ) {
			fputcsv( $fp, array(
				$row['order_id'],
				$row['item_id'],
				$row['order_date'],
				$row['customer_email'],
				$row['product_name'],
				$row['design_preview'],
				is_array( $row['production_images'] ) ? implode( ', ', $row['production_images'] ) : '',
				$row['design_data']
			));
		}

		fclose( $fp );

		// Force download
		header('Content-Description: File Transfer');
		header('Content-Type: application/csv');
		header('Content-Disposition: attachment; filename="'.basename($export_file).'"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($export_file));
		readfile($export_file);
		unlink($export_file); // clean up file
		exit;
	}

	// Admin page rendering methods

	/**
	 * Render dashboard page
	 */
	public function render_dashboard_page() {
		include SWPD_PLUGIN_DIR . 'templates/admin/dashboard.php';
	}

	/**
	 * Render designs page
	 */
	public function render_designs_page() {
		// This template was not provided, so we create a placeholder.
		echo '<div class="wrap"><h1>Customer Designs</h1><p>This page is under construction.</p></div>';
	}

	/**
	 * Render templates page
	 */
	public function render_templates_page() {
	    // This template was not provided, but we can assume it's handled by the SWPD_Templates class.
		include SWPD_PLUGIN_DIR . 'includes/admin/views/templates-page.php';
	}

	/**
	 * Render analytics page
	 */
	public function render_analytics_page() {
		// This template was not provided, so we create a placeholder.
		echo '<div class="wrap"><h1>Analytics</h1><p>This page is under construction.</p></div>';
	}

	/**
	 * Render settings page
	 */
	public function render_settings_page() {
		// This template was not provided, so we create a basic structure.
		include SWPD_PLUGIN_DIR . 'includes/admin/views/settings-page.php';
	}

	/**
	 * Render system status page
	 */
	public function render_status_page() {
		// This template was not provided, so we create a placeholder.
		echo '<div class="wrap"><h1>System Status</h1><p>This page is under construction.</p></div>';
	}

	/**
	 * Render debug test page
	 */
	public function render_debug_test_page() {
		include SWPD_PLUGIN_DIR . 'templates/debug-test.php';
	}

	/**
	 * Register plugin settings
	 */
	public function register_swpd_settings() {
		// The first parameter is the "option group" name.
		// This MUST match the string used in the settings_fields() function in your settings page form.
		$option_group = 'swpd-settings';

		register_setting( $option_group, 'swpd_cloudinary_cloud_name' );
		register_setting( $option_group, 'swpd_cloudinary_api_key' );
		register_setting( $option_group, 'swpd_cloudinary_api_secret' );
		register_setting( $option_group, 'swpd_max_upload_size' );
		register_setting( $option_group, 'swpd_max_image_dimensions' );
		register_setting( $option_group, 'swpd_enable_autosave' );
		register_setting( $option_group, 'swpd_autosave_interval' );
		register_setting( $option_group, 'swpd_enable_templates' );
		register_setting( $option_group, 'swpd_enable_analytics' );
		register_setting( $option_group, 'swpd_upload_limit_per_hour' );
		register_setting( $option_group, 'swpd_enable_imagick' );
		register_setting( $option_group, 'swpd_debug_mode' );
		register_setting( $option_group, 'swpd_primary_color' );
		register_setting( $option_group, 'swpd_secondary_color' );
		register_setting( $option_group, 'swpd_email_notifications' );
		register_setting( $option_group, 'swpd_remove_data_on_uninstall' );
	}
}