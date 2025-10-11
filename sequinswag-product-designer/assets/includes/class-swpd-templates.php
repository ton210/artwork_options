<?php
/**
 * SWPD Templates Class
 *
 * @package SWPD
 * @since 2.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SWPD_Templates
 * 
 * Handles design templates
 */
class SWPD_Templates {
	
	/**
	 * Logger instance
	 *
	 * @var SWPD_Logger
	 */
	private $logger;
	
	/**
	 * Cache instance
	 *
	 * @var SWPD_Cache
	 */
	private $cache;
	
	/**
	 * Template categories
	 *
	 * @var array
	 */
	private $categories = array(
		'birthday' => 'Birthday',
		'wedding' => 'Wedding',
		'holiday' => 'Holiday',
		'sports' => 'Sports',
		'business' => 'Business',
		'fun' => 'Fun & Humor',
		'seasonal' => 'Seasonal',
		'custom' => 'Custom Text'
	);
	
	/**
	 * Constructor
	 *
	 * @param SWPD_Logger $logger
	 * @param SWPD_Cache $cache
	 */
	public function __construct( $logger, $cache ) {
		$this->logger = $logger;
		$this->cache = $cache;
	}
	
	/**
	 * Initialize templates
	 */
	public function init() {
		// Admin hooks
		add_action( 'admin_menu', array( $this, 'add_templates_menu' ), 20 );
		add_action( 'admin_post_swpd_save_template', array( $this, 'save_template' ) );
		add_action( 'admin_post_swpd_delete_template', array( $this, 'delete_template' ) );
		
		// AJAX handlers
		add_action( 'wp_ajax_swpd_import_template', array( $this, 'ajax_import_template' ) );
		add_action( 'wp_ajax_swpd_export_template', array( $this, 'ajax_export_template' ) );
		
		// Install default templates on activation
		add_action( 'swpd_install_default_templates', array( $this, 'install_default_templates' ) );
	}
	
	/**
	 * Get all templates
	 *
	 * @param string $category Filter by category
	 * @param int $product_id Filter by product compatibility
	 * @return array
	 */
	public function get_templates( $category = '', $product_id = 0 ) {
		// Try cache first
		$cache_key = 'templates_' . $category . '_' . $product_id;
		$cached = $this->cache->get( $cache_key, 'templates' );
		
		if ( false !== $cached ) {
			return $cached;
		}
		
		// Get from files and database
		$templates = array();
		
		// Load built-in templates
		$templates = array_merge( $templates, $this->get_builtin_templates() );
		
		// Load custom templates from database
		$templates = array_merge( $templates, $this->get_custom_templates() );
		
		// Filter by category
		if ( $category ) {
			$templates = array_filter( $templates, function( $template ) use ( $category ) {
				return $template['category'] === $category;
			});
		}
		
		// Filter by product compatibility
		if ( $product_id ) {
			$templates = array_filter( $templates, function( $template ) use ( $product_id ) {
				// Check if template is compatible with product
				if ( empty( $template['products'] ) ) {
					return true; // Universal template
				}
				return in_array( $product_id, $template['products'] );
			});
		}
		
		// Sort by priority and name
		usort( $templates, function( $a, $b ) {
			if ( $a['priority'] == $b['priority'] ) {
				return strcmp( $a['name'], $b['name'] );
			}
			return $b['priority'] - $a['priority'];
		});
		
		// Cache results
		$this->cache->set( $cache_key, $templates, 'templates' );
		
		return $templates;
	}
	
	/**
	 * Get built-in templates
	 *
	 * @return array
	 */
	private function get_builtin_templates() {
		$templates = array();
		
		// Birthday templates
		$templates[] = array(
			'id' => 'birthday-balloons',
			'name' => __( 'Birthday Balloons', 'swpd' ),
			'category' => 'birthday',
			'thumbnail' => SWPD_PLUGIN_URL . 'assets/templates/birthday-balloons-thumb.jpg',
			'priority' => 10,
			'products' => array(), // Empty means all products
			'data' => array(
				'objects' => array(
					array(
						'type' => 'text',
						'text' => 'Happy Birthday!',
						'fontSize' => 48,
						'fontFamily' => 'Impact',
						'fill' => '#ff6b6b',
						'left' => 200,
						'top' => 100,
						'angle' => -5,
						'originX' => 'center',
						'originY' => 'center'
					),
					array(
						'type' => 'text',
						'text' => '🎈🎈🎈',
						'fontSize' => 60,
						'left' => 200,
						'top' => 200,
						'originX' => 'center',
						'originY' => 'center'
					)
				)
			)
		);
		
		$templates[] = array(
			'id' => 'birthday-cake',
			'name' => __( 'Birthday Cake', 'swpd' ),
			'category' => 'birthday',
			'thumbnail' => SWPD_PLUGIN_URL . 'assets/templates/birthday-cake-thumb.jpg',
			'priority' => 9,
			'products' => array(),
			'data' => array(
				'objects' => array(
					array(
						'type' => 'text',
						'text' => 'Make a Wish!',
						'fontSize' => 36,
						'fontFamily' => 'Georgia',
						'fill' => '#4ecdc4',
						'left' => 200,
						'top' => 80,
						'originX' => 'center',
						'originY' => 'center'
					),
					array(
						'type' => 'text',
						'text' => '🎂',
						'fontSize' => 100,
						'left' => 200,
						'top' => 180,
						'originX' => 'center',
						'originY' => 'center'
					),
					array(
						'type' => 'text',
						'text' => '[Age Here]',
						'fontSize' => 24,
						'fontFamily' => 'Arial',
						'fill' => '#333333',
						'left' => 200,
						'top' => 280,
						'originX' => 'center',
						'originY' => 'center'
					)
				)
			)
		);
		
		// Wedding templates
		$templates[] = array(
			'id' => 'wedding-elegant',
			'name' => __( 'Elegant Wedding', 'swpd' ),
			'category' => 'wedding',
			'thumbnail' => SWPD_PLUGIN_URL . 'assets/templates/wedding-elegant-thumb.jpg',
			'priority' => 10,
			'products' => array(),
			'data' => array(
				'objects' => array(
					array(
						'type' => 'text',
						'text' => 'Mr & Mrs',
						'fontSize' => 42,
						'fontFamily' => 'Times New Roman',
						'fill' => '#d4af37',
						'left' => 200,
						'top' => 100,
						'originX' => 'center',
						'originY' => 'center'
					),
					array(
						'type' => 'text',
						'text' => '[Names]',
						'fontSize' => 32,
						'fontFamily' => 'Georgia',
						'fill' => '#333333',
						'left' => 200,
						'top' => 160,
						'originX' => 'center',
						'originY' => 'center'
					),
					array(
						'type' => 'text',
						'text' => '♥',
						'fontSize' => 40,
						'fill' => '#ff6b6b',
						'left' => 200,
						'top' => 220,
						'originX' => 'center',
						'originY' => 'center'
					),
					array(
						'type' => 'text',
						'text' => '[Date]',
						'fontSize' => 24,
						'fontFamily' => 'Arial',
						'fill' => '#666666',
						'left' => 200,
						'top' => 280,
						'originX' => 'center',
						'originY' => 'center'
					)
				)
			)
		);
		
		// Sports templates
		$templates[] = array(
			'id' => 'sports-team',
			'name' => __( 'Team Spirit', 'swpd' ),
			'category' => 'sports',
			'thumbnail' => SWPD_PLUGIN_URL . 'assets/templates/sports-team-thumb.jpg',
			'priority' => 10,
			'products' => array(),
			'data' => array(
				'objects' => array(
					array(
						'type' => 'text',
						'text' => 'TEAM',
						'fontSize' => 60,
						'fontFamily' => 'Impact',
						'fill' => '#ff0000',
						'left' => 200,
						'top' => 100,
						'originX' => 'center',
						'originY' => 'center',
						'strokeWidth' => 3,
						'stroke' => '#000000'
					),
					array(
						'type' => 'text',
						'text' => '[Name]',
						'fontSize' => 48,
						'fontFamily' => 'Arial Black',
						'fill' => '#000000',
						'left' => 200,
						'top' => 180,
						'originX' => 'center',
						'originY' => 'center'
					),
					array(
						'type' => 'text',
						'text' => '#[Number]',
						'fontSize' => 72,
						'fontFamily' => 'Impact',
						'fill' => '#ffffff',
						'left' => 200,
						'top' => 280,
						'originX' => 'center',
						'originY' => 'center',
						'strokeWidth' => 4,
						'stroke' => '#000000'
					)
				)
			)
		);
		
		// Business templates
		$templates[] = array(
			'id' => 'business-professional',
			'name' => __( 'Professional', 'swpd' ),
			'category' => 'business',
			'thumbnail' => SWPD_PLUGIN_URL . 'assets/templates/business-professional-thumb.jpg',
			'priority' => 10,
			'products' => array(),
			'data' => array(
				'objects' => array(
					array(
						'type' => 'text',
						'text' => '[Company Name]',
						'fontSize' => 32,
						'fontFamily' => 'Helvetica',
						'fill' => '#1a1a1a',
						'left' => 200,
						'top' => 120,
						'originX' => 'center',
						'originY' => 'center'
					),
					array(
						'type' => 'rect',
						'left' => 200,
						'top' => 160,
						'width' => 200,
						'height' => 2,
						'fill' => '#0073aa',
						'originX' => 'center',
						'originY' => 'center'
					),
					array(
						'type' => 'text',
						'text' => '[Your Title]',
						'fontSize' => 24,
						'fontFamily' => 'Arial',
						'fill' => '#666666',
						'left' => 200,
						'top' => 200,
						'originX' => 'center',
						'originY' => 'center'
					)
				)
			)
		);
		
		// Holiday templates
		$templates[] = array(
			'id' => 'holiday-christmas',
			'name' => __( 'Merry Christmas', 'swpd' ),
			'category' => 'holiday',
			'thumbnail' => SWPD_PLUGIN_URL . 'assets/templates/holiday-christmas-thumb.jpg',
			'priority' => 10,
			'products' => array(),
			'data' => array(
				'objects' => array(
					array(
						'type' => 'text',
						'text' => 'Merry',
						'fontSize' => 48,
						'fontFamily' => 'Georgia',
						'fill' => '#c41e3a',
						'left' => 200,
						'top' => 100,
						'originX' => 'center',
						'originY' => 'center'
					),
					array(
						'type' => 'text',
						'text' => 'Christmas',
						'fontSize' => 56,
						'fontFamily' => 'Georgia',
						'fill' => '#165b33',
						'left' => 200,
						'top' => 160,
						'originX' => 'center',
						'originY' => 'center'
					),
					array(
						'type' => 'text',
						'text' => '🎄❄️🎅',
						'fontSize' => 40,
						'left' => 200,
						'top' => 240,
						'originX' => 'center',
						'originY' => 'center'
					)
				)
			)
		);
		
		// Fun templates
		$templates[] = array(
			'id' => 'fun-emoji',
			'name' => __( 'Emoji Fun', 'swpd' ),
			'category' => 'fun',
			'thumbnail' => SWPD_PLUGIN_URL . 'assets/templates/fun-emoji-thumb.jpg',
			'priority' => 8,
			'products' => array(),
			'data' => array(
				'objects' => array(
					array(
						'type' => 'text',
						'text' => '😎',
						'fontSize' => 100,
						'left' => 200,
						'top' => 150,
						'originX' => 'center',
						'originY' => 'center',
						'angle' => -15
					),
					array(
						'type' => 'text',
						'text' => 'Cool Kid',
						'fontSize' => 36,
						'fontFamily' => 'Comic Sans MS',
						'fill' => '#ff6b6b',
						'left' => 200,
						'top' => 250,
						'originX' => 'center',
						'originY' => 'center'
					)
				)
			)
		);
		
		return $templates;
	}
	
	/**
	 * Get custom templates from database
	 *
	 * @return array
	 */
	private function get_custom_templates() {
		$templates = array();
		
		// Get custom templates from options
		$custom_templates = get_option( 'swpd_custom_templates', array() );
		
		foreach ( $custom_templates as $template ) {
			// Ensure required fields
			if ( ! isset( $template['id'] ) || ! isset( $template['name'] ) || ! isset( $template['data'] ) ) {
				continue;
			}
			
			// Add defaults
			$template['priority'] = isset( $template['priority'] ) ? $template['priority'] : 5;
			$template['products'] = isset( $template['products'] ) ? $template['products'] : array();
			
			$templates[] = $template;
		}
		
		return $templates;
	}
	
	/**
	 * Save a custom template
	 *
	 * @param array $template_data
	 * @return bool|WP_Error
	 */
	public function save_custom_template( $template_data ) {
		// Validate required fields
		if ( empty( $template_data['name'] ) || empty( $template_data['data'] ) ) {
			return new WP_Error( 'missing_fields', __( 'Template name and data are required.', 'swpd' ) );
		}
		
		// Generate ID if not provided
		if ( empty( $template_data['id'] ) ) {
			$template_data['id'] = 'custom-' . sanitize_title( $template_data['name'] ) . '-' . time();
		}
		
		// Sanitize data
		$template = array(
			'id' => sanitize_key( $template_data['id'] ),
			'name' => sanitize_text_field( $template_data['name'] ),
			'category' => isset( $template_data['category'] ) ? sanitize_key( $template_data['category'] ) : 'custom',
			'thumbnail' => isset( $template_data['thumbnail'] ) ? esc_url_raw( $template_data['thumbnail'] ) : '',
			'priority' => isset( $template_data['priority'] ) ? absint( $template_data['priority'] ) : 5,
			'products' => isset( $template_data['products'] ) ? array_map( 'absint', $template_data['products'] ) : array(),
			'data' => $template_data['data'] // This should be validated JSON
		);
		
		// Validate data is proper JSON structure
		if ( ! is_array( $template['data'] ) || ! isset( $template['data']['objects'] ) ) {
			return new WP_Error( 'invalid_data', __( 'Template data must contain objects array.', 'swpd' ) );
		}
		
		// Get existing templates
		$custom_templates = get_option( 'swpd_custom_templates', array() );
		
		// Update or add template
		$found = false;
		foreach ( $custom_templates as $key => $existing ) {
			if ( $existing['id'] === $template['id'] ) {
				$custom_templates[$key] = $template;
				$found = true;
				break;
			}
		}
		
		if ( ! $found ) {
			$custom_templates[] = $template;
		}
		
		// Save to database
		update_option( 'swpd_custom_templates', $custom_templates );
		
		// Clear cache
		$this->cache->flush( 'templates' );
		
		// Log action
		$this->logger->info( 'Custom template saved', array(
			'template_id' => $template['id'],
			'template_name' => $template['name']
		));
		
		return true;
	}
	
	/**
	 * Delete a custom template
	 *
	 * @param string $template_id
	 * @return bool
	 */
	public function delete_custom_template( $template_id ) {
		$custom_templates = get_option( 'swpd_custom_templates', array() );
		
		$updated = array();
		$deleted = false;
		
		foreach ( $custom_templates as $template ) {
			if ( $template['id'] !== $template_id ) {
				$updated[] = $template;
			} else {
				$deleted = true;
			}
		}
		
		if ( $deleted ) {
			update_option( 'swpd_custom_templates', $updated );
			$this->cache->flush( 'templates' );
			
			$this->logger->info( 'Custom template deleted', array(
				'template_id' => $template_id
			));
		}
		
		return $deleted;
	}
	
	/**
	 * Install default templates
	 */
	public function install_default_templates() {
		// Create template thumbnails directory
		$upload_dir = wp_upload_dir();
		$template_dir = $upload_dir['basedir'] . '/swpd-designs/templates/';
		
		if ( ! file_exists( $template_dir ) ) {
			wp_mkdir_p( $template_dir );
		}
		
		// Copy default template thumbnails from plugin to uploads
		$source_dir = SWPD_PLUGIN_DIR . 'assets/templates/';
		if ( is_dir( $source_dir ) ) {
			$files = glob( $source_dir . '*.jpg' );
			foreach ( $files as $file ) {
				$filename = basename( $file );
				$destination = $template_dir . $filename;
				if ( ! file_exists( $destination ) ) {
					copy( $file, $destination );
				}
			}
		}
		
		$this->logger->info( 'Default templates installed' );
	}
	
	/**
	 * Add templates menu
	 */
	public function add_templates_menu() {
		// This is added by SWPD_Admin class
	}
	
	/**
	 * Save template from admin
	 */
	public function save_template() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( __( 'Unauthorized', 'swpd' ) );
		}
		
		check_admin_referer( 'swpd_save_template' );
		
		$template_data = array(
			'id' => isset( $_POST['template_id'] ) ? sanitize_key( $_POST['template_id'] ) : '',
			'name' => isset( $_POST['template_name'] ) ? sanitize_text_field( $_POST['template_name'] ) : '',
			'category' => isset( $_POST['template_category'] ) ? sanitize_key( $_POST['template_category'] ) : 'custom',
			'priority' => isset( $_POST['template_priority'] ) ? absint( $_POST['template_priority'] ) : 5,
			'products' => isset( $_POST['template_products'] ) ? array_map( 'absint', $_POST['template_products'] ) : array(),
			'data' => isset( $_POST['template_data'] ) ? json_decode( wp_unslash( $_POST['template_data'] ), true ) : array()
		);
		
		// Handle thumbnail upload
		if ( ! empty( $_FILES['template_thumbnail']['name'] ) ) {
			$upload = wp_handle_upload( $_FILES['template_thumbnail'], array( 'test_form' => false ) );
			if ( ! isset( $upload['error'] ) ) {
				$template_data['thumbnail'] = $upload['url'];
			}
		} elseif ( isset( $_POST['existing_thumbnail'] ) ) {
			$template_data['thumbnail'] = esc_url_raw( $_POST['existing_thumbnail'] );
		}
		
		$result = $this->save_custom_template( $template_data );
		
		if ( is_wp_error( $result ) ) {
			wp_redirect( add_query_arg( array(
				'page' => 'swpd-templates',
				'error' => urlencode( $result->get_error_message() )
			), admin_url( 'admin.php' ) ) );
		} else {
			wp_redirect( add_query_arg( array(
				'page' => 'swpd-templates',
				'success' => '1'
			), admin_url( 'admin.php' ) ) );
		}
		exit;
	}
	
	/**
	 * Delete template from admin
	 */
	public function delete_template() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( __( 'Unauthorized', 'swpd' ) );
		}
		
		check_admin_referer( 'swpd_delete_template' );
		
		$template_id = isset( $_GET['template_id'] ) ? sanitize_key( $_GET['template_id'] ) : '';
		
		if ( $template_id ) {
			$this->delete_custom_template( $template_id );
		}
		
		wp_redirect( add_query_arg( array(
			'page' => 'swpd-templates',
			'deleted' => '1'
		), admin_url( 'admin.php' ) ) );
		exit;
	}
	
	/**
	 * AJAX handler for importing template
	 */
	public function ajax_import_template() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( __( 'Unauthorized', 'swpd' ) );
		}
		
		check_ajax_referer( 'swpd_admin_nonce', 'nonce' );
		
		$json_data = isset( $_POST['template_json'] ) ? wp_unslash( $_POST['template_json'] ) : '';
		
		if ( empty( $json_data ) ) {
			wp_send_json_error( __( 'No template data provided.', 'swpd' ) );
		}
		
		$template_data = json_decode( $json_data, true );
		
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			wp_send_json_error( __( 'Invalid JSON format.', 'swpd' ) );
		}
		
		// Ensure it's a valid template
		if ( ! isset( $template_data['name'] ) || ! isset( $template_data['data'] ) ) {
			wp_send_json_error( __( 'Invalid template format. Missing name or data.', 'swpd' ) );
		}
		
		// Generate new ID to avoid conflicts
		$template_data['id'] = 'imported-' . sanitize_title( $template_data['name'] ) . '-' . time();
		
		$result = $this->save_custom_template( $template_data );
		
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}
		
		wp_send_json_success( array(
			'message' => __( 'Template imported successfully!', 'swpd' ),
			'template_id' => $template_data['id']
		));
	}
	
	/**
	 * AJAX handler for exporting template
	 */
	public function ajax_export_template() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( __( 'Unauthorized', 'swpd' ) );
		}
		
		check_ajax_referer( 'swpd_admin_nonce', 'nonce' );
		
		$template_id = isset( $_POST['template_id'] ) ? sanitize_key( $_POST['template_id'] ) : '';
		
		if ( empty( $template_id ) ) {
			wp_send_json_error( __( 'No template ID provided.', 'swpd' ) );
		}
		
		// Find template
		$all_templates = $this->get_templates();
		$found_template = null;
		
		foreach ( $all_templates as $template ) {
			if ( $template['id'] === $template_id ) {
				$found_template = $template;
				break;
			}
		}
		
		if ( ! $found_template ) {
			wp_send_json_error( __( 'Template not found.', 'swpd' ) );
		}
		
		// Remove internal fields
		unset( $found_template['products'] );
		unset( $found_template['priority'] );
		
		wp_send_json_success( array(
			'template' => $found_template,
			'json' => wp_json_encode( $found_template, JSON_PRETTY_PRINT )
		));
	}
	
	/**
	 * Get template by ID
	 *
	 * @param string $template_id
	 * @return array|null
	 */
	public function get_template( $template_id ) {
		$templates = $this->get_templates();
		
		foreach ( $templates as $template ) {
			if ( $template['id'] === $template_id ) {
				return $template;
			}
		}
		
		return null;
	}
	
	/**
	 * Get template categories
	 *
	 * @return array
	 */
	public function get_categories() {
		return apply_filters( 'swpd_template_categories', $this->categories );
	}
	
	/**
	 * Create template from design data
	 *
	 * @param array $design_data
	 * @param array $meta
	 * @return array
	 */
	public function create_from_design( $design_data, $meta = array() ) {
		$template = array(
			'id' => 'user-' . uniqid(),
			'name' => isset( $meta['name'] ) ? $meta['name'] : __( 'User Template', 'swpd' ),
			'category' => isset( $meta['category'] ) ? $meta['category'] : 'custom',
			'thumbnail' => isset( $meta['thumbnail'] ) ? $meta['thumbnail'] : '',
			'priority' => 1,
			'products' => isset( $meta['products'] ) ? $meta['products'] : array(),
			'data' => $design_data
		);
		
		return $template;
	}
	
	/**
	 * Apply template to canvas
	 *
	 * @param string $template_id
	 * @param array $options
	 * @return array|WP_Error
	 */
	public function apply_template( $template_id, $options = array() ) {
		$template = $this->get_template( $template_id );
		
		if ( ! $template ) {
			return new WP_Error( 'template_not_found', __( 'Template not found.', 'swpd' ) );
		}
		
		// Apply any transformations or options
		$template_data = $template['data'];
		
		// Scale template to fit canvas if needed
		if ( isset( $options['canvas_width'] ) && isset( $options['canvas_height'] ) ) {
			$template_data = $this->scale_template_to_canvas( $template_data, $options['canvas_width'], $options['canvas_height'] );
		}
		
		// Replace placeholders
		if ( isset( $options['replacements'] ) ) {
			$template_data = $this->replace_template_placeholders( $template_data, $options['replacements'] );
		}
		
		return $template_data;
	}
	
	/**
	 * Scale template to fit canvas
	 *
	 * @param array $template_data
	 * @param int $canvas_width
	 * @param int $canvas_height
	 * @return array
	 */
	private function scale_template_to_canvas( $template_data, $canvas_width, $canvas_height ) {
		// Assuming original template was designed for 400x400 canvas
		$original_width = 400;
		$original_height = 400;
		
		$scale_x = $canvas_width / $original_width;
		$scale_y = $canvas_height / $original_height;
		
		if ( isset( $template_data['objects'] ) ) {
			foreach ( $template_data['objects'] as &$object ) {
				// Scale positions
				if ( isset( $object['left'] ) ) {
					$object['left'] *= $scale_x;
				}
				if ( isset( $object['top'] ) ) {
					$object['top'] *= $scale_y;
				}
				
				// Scale dimensions
				if ( isset( $object['width'] ) ) {
					$object['width'] *= $scale_x;
				}
				if ( isset( $object['height'] ) ) {
					$object['height'] *= $scale_y;
				}
				
				// Scale font size
				if ( isset( $object['fontSize'] ) ) {
					$object['fontSize'] *= min( $scale_x, $scale_y );
				}
				
				// Scale scale values
				if ( isset( $object['scaleX'] ) ) {
					$object['scaleX'] *= $scale_x;
				}
				if ( isset( $object['scaleY'] ) ) {
					$object['scaleY'] *= $scale_y;
				}
			}
		}
		
		return $template_data;
	}
	
	/**
	 * Replace template placeholders
	 *
	 * @param array $template_data
	 * @param array $replacements
	 * @return array
	 */
	private function replace_template_placeholders( $template_data, $replacements ) {
		if ( isset( $template_data['objects'] ) ) {
			foreach ( $template_data['objects'] as &$object ) {
				if ( $object['type'] === 'text' && isset( $object['text'] ) ) {
					foreach ( $replacements as $placeholder => $value ) {
						$object['text'] = str_replace( '[' . $placeholder . ']', $value, $object['text'] );
					}
				}
			}
		}
		
		return $template_data;
	}
}