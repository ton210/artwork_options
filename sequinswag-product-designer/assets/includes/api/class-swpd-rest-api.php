<?php
/**
 * SWPD REST API Class
 *
 * @package SWPD
 * @since 2.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SWPD_REST_API
 * * Handles REST API endpoints
 */
class SWPD_REST_API {
	
	/**
	 * Logger instance
	 *
	 * @var SWPD_Logger
	 */
	private $logger;
	
	/**
	 * API namespace
	 *
	 * @var string
	 */
	private $namespace = 'swpd/v1';
	
	/**
	 * Constructor
	 *
	 * @param SWPD_Logger $logger
	 */
	public function __construct( $logger ) {
		$this->logger = $logger;
	}
	
	/**
	 * Initialize REST API
	 */
	public function init() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}
	
	/**
	 * Register REST API routes
	 */
	public function register_routes() {
		// Designs endpoints
		register_rest_route( $this->namespace, '/designs', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_designs' ),
				'permission_callback' => array( $this, 'check_read_permission' ),
				'args'                => $this->get_collection_params()
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_design' ),
				'permission_callback' => array( $this, 'check_write_permission' ),
				'args'                => $this->get_design_params()
			)
		));
		
		register_rest_route( $this->namespace, '/designs/(?P<id>\d+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_design' ),
				'permission_callback' => array( $this, 'check_read_permission' ),
				'args'                => array(
					'id' => array(
						'description' => __( 'Unique identifier for the design.', 'swpd' ),
						'type'        => 'integer',
						'required'    => true
					)
				)
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_design' ),
				'permission_callback' => array( $this, 'check_write_permission' ),
				'args'                => $this->get_design_params()
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_design' ),
				'permission_callback' => array( $this, 'check_write_permission' )
			)
		));
		
		// Templates endpoints
		register_rest_route( $this->namespace, '/templates', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_templates' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'category' => array(
						'description' => __( 'Filter templates by category.', 'swpd' ),
						'type'        => 'string',
						'required'    => false
					),
					'product_id' => array(
						'description' => __( 'Filter templates by product compatibility.', 'swpd' ),
						'type'        => 'integer',
						'required'    => false
					)
				)
			)
		));
		
		// Products with designer endpoints
		register_rest_route( $this->namespace, '/products', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_designer_products' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'per_page' => array(
						'description' => __( 'Maximum number of items to return.', 'swpd' ),
						'type'        => 'integer',
						'default'     => 10,
						'minimum'     => 1,
						'maximum'     => 100,
					),
					'page' => array(
						'description' => __( 'Current page of the collection.', 'swpd' ),
						'type'        => 'integer',
						'default'     => 1,
						'minimum'     => 1,
					)
				)
			)
		));
		
		// Analytics endpoints
		register_rest_route( $this->namespace, '/analytics', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_analytics' ),
				'permission_callback' => array( $this, 'check_analytics_permission' ),
				'args'                => array(
					'date_from' => array(
						'description' => __( 'Start date for analytics data.', 'swpd' ),
						'type'        => 'string',
						'format'      => 'date',
						'required'    => false
					),
					'date_to' => array(
						'description' => __( 'End date for analytics data.', 'swpd' ),
						'type'        => 'string',
						'format'      => 'date',
						'required'    => false
					),
					'event_type' => array(
						'description' => __( 'Filter by event type.', 'swpd' ),
						'type'        => 'string',
						'required'    => false
					)
				)
			)
		));
		
		// Process design for production
		register_rest_route( $this->namespace, '/process-design', array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'process_design' ),
				'permission_callback' => array( $this, 'check_process_permission' ),
				'args'                => array(
					'order_id' => array(
						'description' => __( 'Order ID to process designs for.', 'swpd' ),
						'type'        => 'integer',
						'required'    => true
					)
				)
			)
		));
		
		// System status endpoint
		register_rest_route( $this->namespace, '/status', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_status' ),
				'permission_callback' => array( $this, 'check_status_permission' )
			)
		));
	}
	
	/**
	 * Get designs
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_designs( $request ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'swpd_designs';
		
		$page = $request->get_param( 'page' ) ?: 1;
		$per_page = $request->get_param( 'per_page' ) ?: 10;
		$offset = ( $page - 1 ) * $per_page;
		
		// Build query
		$where = array( '1=1' );
		$values = array();
		
		// Filter by user
		if ( $request->get_param( 'user_id' ) ) {
			$where[] = 'user_id = %d';
			$values[] = $request->get_param( 'user_id' );
		}
		
		// Filter by product
		if ( $request->get_param( 'product_id' ) ) {
			$where[] = 'product_id = %d';
			$values[] = $request->get_param( 'product_id' );
		}
		
		// Filter by status
		if ( $request->get_param( 'status' ) ) {
			$where[] = 'status = %s';
			$values[] = $request->get_param( 'status' );
		}
		
		$where_clause = implode( ' AND ', $where );
		
		// Get total count
		$count_query = "SELECT COUNT(*) FROM {$table_name} WHERE {$where_clause}";
		if ( ! empty( $values ) ) {
			$count_query = $wpdb->prepare( $count_query, $values );
		}
		$total_items = $wpdb->get_var( $count_query );
		
		// Get designs
		$query = "SELECT * FROM {$table_name} WHERE {$where_clause} ORDER BY created_at DESC LIMIT %d OFFSET %d";
		$values[] = $per_page;
		$values[] = $offset;
		
		$designs = $wpdb->get_results( $wpdb->prepare( $query, $values ) );
		
		// Format response
		$data = array();
		foreach ( $designs as $design ) {
			$data[] = $this->prepare_design_for_response( $design );
		}
		
		$response = rest_ensure_response( $data );
		$response->header( 'X-WP-Total', $total_items );
		$response->header( 'X-WP-TotalPages', ceil( $total_items / $per_page ) );
		
		return $response;
	}
	
	/**
	 * Get single design
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_design( $request ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'swpd_designs';
		
		$design_id = $request->get_param( 'id' );
		
		$design = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$table_name} WHERE id = %d",
			$design_id
		));
		
		if ( ! $design ) {
			return new WP_Error( 'design_not_found', __( 'Design not found.', 'swpd' ), array( 'status' => 404 ) );
		}
		
		// Check permissions
		if ( ! $this->can_access_design( $design ) ) {
			return new WP_Error( 'forbidden', __( 'You do not have permission to view this design.', 'swpd' ), array( 'status' => 403 ) );
		}
		
		return rest_ensure_response( $this->prepare_design_for_response( $design ) );
	}
	
	/**
	 * Create design
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_design( $request ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'swpd_designs';
		
		// Validate required fields
		$required = array( 'product_id', 'design_name', 'design_data' );
		foreach ( $required as $field ) {
			if ( empty( $request->get_param( $field ) ) ) {
				return new WP_Error( 'missing_field', sprintf( __( 'Missing required field: %s', 'swpd' ), $field ), array( 'status' => 400 ) );
			}
		}
		
		// Validate design data
		$design_data = $request->get_param( 'design_data' );
		if ( is_string( $design_data ) ) {
			$design_data = json_decode( $design_data, true );
			if ( json_last_error() !== JSON_ERROR_NONE ) {
				return new WP_Error( 'invalid_json', __( 'Invalid design data JSON.', 'swpd' ), array( 'status' => 400 ) );
			}
		}
		
		// Insert design
		$result = $wpdb->insert(
			$table_name,
			array(
				'user_id' => get_current_user_id(),
				'product_id' => $request->get_param( 'product_id' ),
				'variant_id' => $request->get_param( 'variant_id' ),
				'design_name' => sanitize_text_field( $request->get_param( 'design_name' ) ),
				'design_data' => wp_json_encode( $design_data ),
				'preview_url' => esc_url_raw( $request->get_param( 'preview_url' ) ),
				'status' => $request->get_param( 'status' ) ?: 'draft',
				'created_at' => current_time( 'mysql' )
			),
			array( '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s' )
		);
		
		if ( false === $result ) {
			$this->logger->error( 'Failed to create design via API', array(
				'error' => $wpdb->last_error
			));
			return new WP_Error( 'database_error', __( 'Failed to create design.', 'swpd' ), array( 'status' => 500 ) );
		}
		
		$design = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$table_name} WHERE id = %d",
			$wpdb->insert_id
		));
		
		$this->logger->info( 'Design created via API', array(
			'design_id' => $design->id,
			'user_id' => get_current_user_id()
		));
		
		return rest_ensure_response( $this->prepare_design_for_response( $design ) );
	}
	
	/**
	 * Update design
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_design( $request ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'swpd_designs';
		
		$design_id = $request->get_param( 'id' );
		
		// Get existing design
		$design = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$table_name} WHERE id = %d",
			$design_id
		));
		
		if ( ! $design ) {
			return new WP_Error( 'design_not_found', __( 'Design not found.', 'swpd' ), array( 'status' => 404 ) );
		}
		
		// Check permissions
		if ( ! $this->can_edit_design( $design ) ) {
			return new WP_Error( 'forbidden', __( 'You do not have permission to edit this design.', 'swpd' ), array( 'status' => 403 ) );
		}
		
		// Prepare update data
		$update_data = array();
		$update_format = array();
		
		if ( $request->has_param( 'design_name' ) ) {
			$update_data['design_name'] = sanitize_text_field( $request->get_param( 'design_name' ) );
			$update_format[] = '%s';
		}
		
		if ( $request->has_param( 'design_data' ) ) {
			$design_data = $request->get_param( 'design_data' );
			if ( is_string( $design_data ) ) {
				$design_data = json_decode( $design_data, true );
			}
			$update_data['design_data'] = wp_json_encode( $design_data );
			$update_format[] = '%s';
		}
		
		if ( $request->has_param( 'preview_url' ) ) {
			$update_data['preview_url'] = esc_url_raw( $request->get_param( 'preview_url' ) );
			$update_format[] = '%s';
		}
		
		if ( $request->has_param( 'status' ) ) {
			$update_data['status'] = $request->get_param( 'status' );
			$update_format[] = '%s';
		}
		
		if ( empty( $update_data ) ) {
			return new WP_Error( 'nothing_to_update', __( 'No valid fields to update.', 'swpd' ), array( 'status' => 400 ) );
		}
		
		// Update
		$result = $wpdb->update(
			$table_name,
			$update_data,
			array( 'id' => $design_id ),
			$update_format,
			array( '%d' )
		);
		
		if ( false === $result ) {
			return new WP_Error( 'database_error', __( 'Failed to update design.', 'swpd' ), array( 'status' => 500 ) );
		}
		
		// Get updated design
		$design = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$table_name} WHERE id = %d",
			$design_id
		));
		
		$this->logger->info( 'Design updated via API', array(
			'design_id' => $design_id
		));
		
		return rest_ensure_response( $this->prepare_design_for_response( $design ) );
	}
	
	/**
	 * Delete design
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_design( $request ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'swpd_designs';
		
		$design_id = $request->get_param( 'id' );
		
		// Get design
		$design = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$table_name} WHERE id = %d",
			$design_id
		));
		
		if ( ! $design ) {
			return new WP_Error( 'design_not_found', __( 'Design not found.', 'swpd' ), array( 'status' => 404 ) );
		}
		
		// Check permissions
		if ( ! $this->can_edit_design( $design ) ) {
			return new WP_Error( 'forbidden', __( 'You do not have permission to delete this design.', 'swpd' ), array( 'status' => 403 ) );
		}
		
		// Delete
		$result = $wpdb->delete(
			$table_name,
			array( 'id' => $design_id ),
			array( '%d' )
		);
		
		if ( false === $result ) {
			return new WP_Error( 'database_error', __( 'Failed to delete design.', 'swpd' ), array( 'status' => 500 ) );
		}
		
		$this->logger->info( 'Design deleted via API', array(
			'design_id' => $design_id
		));
		
		return rest_ensure_response( array(
			'deleted' => true,
			'previous' => $this->prepare_design_for_response( $design )
		));
	}
	
	/**
	 * Get templates
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function get_templates( $request ) {
		$templates_class = new SWPD_Templates( $this->logger, new SWPD_Cache() );
		
		$category = $request->get_param( 'category' );
		$product_id = $request->get_param( 'product_id' );
		
		$templates = $templates_class->get_templates( $category, $product_id );
		
		return rest_ensure_response( $templates );
	}
	
	/**
	 * Get products with designer enabled
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function get_designer_products( $request ) {
		$page = $request->get_param( 'page' ) ?: 1;
		$per_page = $request->get_param( 'per_page' ) ?: 10;
		
		$args = array(
			'post_type' => 'product',
			'posts_per_page' => $per_page,
			'paged' => $page,
			'meta_query' => array(
				array(
					'key' => 'design_tool_layer',
					'compare' => 'EXISTS'
				)
			)
		);
		
		$query = new WP_Query( $args );
		
		$products = array();
		foreach ( $query->posts as $post ) {
			$product = wc_get_product( $post->ID );
			
			$products[] = array(
				'id' => $product->get_id(),
				'name' => $product->get_name(),
				'permalink' => $product->get_permalink(),
				'price' => $product->get_price(),
				'image' => wp_get_attachment_url( $product->get_image_id() ),
				'type' => $product->get_type(),
				'design_enabled' => true
			);
		}
		
		$response = rest_ensure_response( $products );
		$response->header( 'X-WP-Total', $query->found_posts );
		$response->header( 'X-WP-TotalPages', $query->max_num_pages );
		
		return $response;
	}
	
	/**
	 * Get analytics data
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_analytics( $request ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'swpd_analytics';
		
		$date_from = $request->get_param( 'date_from' ) ?: date( 'Y-m-d', strtotime( '-30 days' ) );
		$date_to = $request->get_param( 'date_to' ) ?: date( 'Y-m-d' );
		$event_type = $request->get_param( 'event_type' );
		
		// Build query
		$where = array(
			'created_at >= %s',
			'created_at <= %s'
		);
		$values = array(
			$date_from . ' 00:00:00',
			$date_to . ' 23:59:59'
		);
		
		if ( $event_type ) {
			$where[] = 'event_type = %s';
			$values[] = $event_type;
		}
		
		$where_clause = implode( ' AND ', $where );
		
		// Get analytics data
		$results = $wpdb->get_results( $wpdb->prepare(
			"SELECT event_type, DATE(created_at) as date, COUNT(*) as count, event_data 
			FROM {$table_name} 
			WHERE {$where_clause} 
			GROUP BY event_type, DATE(created_at) 
			ORDER BY date ASC",
			$values
		));
		
		// Format data
		$analytics = array(
			'summary' => array(),
			'timeline' => array(),
			'events' => array()
		);
		
		foreach ( $results as $row ) {
			// Summary
			if ( ! isset( $analytics['summary'][$row->event_type] ) ) {
				$analytics['summary'][$row->event_type] = 0;
			}
			$analytics['summary'][$row->event_type] += $row->count;
			
			// Timeline
			if ( ! isset( $analytics['timeline'][$row->date] ) ) {
				$analytics['timeline'][$row->date] = array();
			}
			$analytics['timeline'][$row->date][$row->event_type] = $row->count;
		}
		
		// Get recent events
		$recent_events = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$table_name} 
			WHERE created_at >= %s AND created_at <= %s 
			ORDER BY created_at DESC 
			LIMIT 100",
			$date_from . ' 00:00:00',
			$date_to . ' 23:59:59'
		));
		
		foreach ( $recent_events as $event ) {
			$analytics['events'][] = array(
				'type' => $event->event_type,
				'user_id' => $event->user_id,
				'product_id' => $event->product_id,
				'data' => json_decode( $event->event_data, true ),
				'timestamp' => $event->created_at
			);
		}
		
		return rest_ensure_response( $analytics );
	}
	
	/**
	 * Process design for production
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function process_design( $request ) {
		$order_id = $request->get_param( 'order_id' );
		
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return new WP_Error( 'order_not_found', __( 'Order not found.', 'swpd' ), array( 'status' => 404 ) );
		}
		
		// Process designs
		$ajax = new SWPD_AJAX( $this->logger );
		$ajax->trigger_design_processing_on_order_completion( $order_id );
		
		// Get production images
		$production_images = array();
		foreach ( $order->get_items() as $item ) {
			$images = $item->get_meta( '_swpd_production_images' );
			if ( $images ) {
				$production_images[$item->get_id()] = $images;
			}
		}
		
		return rest_ensure_response( array(
			'order_id' => $order_id,
			'production_images' => $production_images
		));
	}
	
	/**
	 * Get system status
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function get_status( $request ) {
		$status = array(
			'version' => SWPD_VERSION,
			'php_version' => PHP_VERSION,
			'wp_version' => get_bloginfo( 'version' ),
			'wc_version' => WC()->version,
			'gd_enabled' => extension_loaded( 'gd' ),
			'imagick_enabled' => extension_loaded( 'imagick' ),
			'memory_limit' => ini_get( 'memory_limit' ),
			'max_upload_size' => size_format( wp_max_upload_size() ),
			'settings' => array(
				'max_upload_size' => get_option( 'swpd_max_upload_size', 5 ) . 'MB',
				'max_image_dimensions' => get_option( 'swpd_max_image_dimensions', 4000 ) . 'px',
				'autosave_enabled' => get_option( 'swpd_enable_autosave', true ),
				'templates_enabled' => get_option( 'swpd_enable_templates', true ),
				'analytics_enabled' => get_option( 'swpd_enable_analytics', true )
			),
			'cache_stats' => SWPD_Cache::get_stats(),
			'database' => $this->get_database_stats()
		);
		
		return rest_ensure_response( $status );
	}
	
	/**
	 * Get database statistics
	 *
	 * @return array
	 */
	private function get_database_stats() {
		global $wpdb;
		
		$stats = array();
		$tables = array(
			'designs' => $wpdb->prefix . 'swpd_designs',
			'autosaves' => $wpdb->prefix . 'swpd_autosaves',
			'analytics' => $wpdb->prefix . 'swpd_analytics'
		);
		
		foreach ( $tables as $key => $table ) {
			$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
			$size = $wpdb->get_var( "SELECT ROUND(((data_length + index_length) / 1024 / 1024), 2) FROM information_schema.TABLES WHERE table_schema = DATABASE() AND table_name = '{$table}'" );
			
			$stats[$key] = array(
				'count' => intval( $count ),
				'size' => $size . 'MB'
			);
		}
		
		return $stats;
	}
	
	/**
	 * Prepare design for response
	 *
	 * @param object $design
	 * @return array
	 */
	private function prepare_design_for_response( $design ) {
		return array(
			'id' => intval( $design->id ),
			'user_id' => intval( $design->user_id ),
			'product_id' => intval( $design->product_id ),
			'variant_id' => $design->variant_id ? intval( $design->variant_id ) : null,
			'name' => $design->design_name,
			'data' => json_decode( $design->design_data, true ),
			'preview_url' => $design->preview_url,
			'status' => $design->status,
			'created_at' => mysql_to_rfc3339( $design->created_at ),
			'updated_at' => mysql_to_rfc3339( $design->updated_at ),
			'_links' => $this->prepare_links( $design )
		);
	}
	
	/**
	 * Prepare links for design
	 *
	 * @param object $design
	 * @return array
	 */
	private function prepare_links( $design ) {
		$base = rest_url( $this->namespace . '/designs' );
		
		return array(
			'self' => array(
				array( 'href' => $base . '/' . $design->id )
			),
			'collection' => array(
				array( 'href' => $base )
			),
			'product' => array(
				array( 'href' => get_permalink( $design->product_id ) )
			)
		);
	}
	
	/**
	 * Get collection parameters
	 *
	 * @return array
	 */
	private function get_collection_params() {
		return array(
			'page' => array(
				'description' => __( 'Current page of the collection.', 'swpd' ),
				'type'        => 'integer',
				'default'     => 1,
				'minimum'     => 1
			),
			'per_page' => array(
				'description' => __( 'Maximum number of items to return.', 'swpd' ),
				'type'        => 'integer',
				'default'     => 10,
				'minimum'     => 1,
				'maximum'     => 100
			),
			'user_id' => array(
				'description' => __( 'Filter by user ID.', 'swpd' ),
				'type'        => 'integer',
				'required'    => false
			),
			'product_id' => array(
				'description' => __( 'Filter by product ID.', 'swpd' ),
				'type'        => 'integer',
				'required'    => false
			),
			'status' => array(
				'description' => __( 'Filter by status.', 'swpd' ),
				'type'        => 'string',
				'enum'        => array( 'draft', 'published' ),
				'required'    => false
			)
		);
	}
	
	/**
	 * Get design parameters
	 *
	 * @return array
	 */
	private function get_design_params() {
		return array(
			'product_id' => array(
				'description' => __( 'Product ID for the design.', 'swpd' ),
				'type'        => 'integer',
				'required'    => true
			),
			'variant_id' => array(
				'description' => __( 'Product variant ID.', 'swpd' ),
				'type'        => 'integer',
				'required'    => false
			),
			'design_name' => array(
				'description' => __( 'Name of the design.', 'swpd' ),
				'type'        => 'string',
				'required'    => true
			),
			'design_data' => array(
				'description' => __( 'Design canvas data as JSON.', 'swpd' ),
				'type'        => array( 'object', 'string' ),
				'required'    => true
			),
			'preview_url' => array(
				'description' => __( 'URL of the design preview image.', 'swpd' ),
				'type'        => 'string',
				'format'      => 'uri',
				'required'    => false
			),
			'status' => array(
				'description' => __( 'Design status.', 'swpd' ),
				'type'        => 'string',
				'enum'        => array( 'draft', 'published' ),
				'default'     => 'draft',
				'required'    => false
			)
		);
	}
	
	/**
	 * Check read permission
	 *
	 * @param WP_REST_Request $request
	 * @return bool|WP_Error
	 */
	public function check_read_permission( $request ) {
		// Allow reading own designs
		if ( is_user_logged_in() ) {
			return true;
		}
		
		// API key authentication
		$api_key = $request->get_header( 'X-SWPD-API-Key' );
		if ( $api_key && $this->validate_api_key( $api_key, 'read' ) ) {
			return true;
		}
		
		return new WP_Error( 'rest_forbidden', __( 'You do not have permission to view designs.', 'swpd' ), array( 'status' => 401 ) );
	}
	
	/**
	 * Check write permission
	 *
	 * @param WP_REST_Request $request
	 * @return bool|WP_Error
	 */
	public function check_write_permission( $request ) {
		// Must be logged in to write
		if ( ! is_user_logged_in() ) {
			return new WP_Error( 'rest_forbidden', __( 'You must be logged in to create or modify designs.', 'swpd' ), array( 'status' => 401 ) );
		}
		
		// Check capability
		if ( ! current_user_can( 'edit_posts' ) ) {
			// API key with write permission
			$api_key = $request->get_header( 'X-SWPD-API-Key' );
			if ( ! $api_key || ! $this->validate_api_key( $api_key, 'write' ) ) {
				return new WP_Error( 'rest_forbidden', __( 'You do not have permission to create or modify designs.', 'swpd' ), array( 'status' => 403 ) );
			}
		}
		
		return true;
	}
	
	/**
	 * Check analytics permission
	 *
	 * @param WP_REST_Request $request
	 * @return bool|WP_Error
	 */
	public function check_analytics_permission( $request ) {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			$api_key = $request->get_header( 'X-SWPD-API-Key' );
			if ( ! $api_key || ! $this->validate_api_key( $api_key, 'analytics' ) ) {
				return new WP_Error( 'rest_forbidden', __( 'You do not have permission to view analytics.', 'swpd' ), array( 'status' => 403 ) );
			}
		}
		
		return true;
	}
	
	/**
	 * Check process permission
	 *
	 * @param WP_REST_Request $request
	 * @return bool|WP_Error
	 */
	public function check_process_permission( $request ) {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			$api_key = $request->get_header( 'X-SWPD-API-Key' );
			if ( ! $api_key || ! $this->validate_api_key( $api_key, 'process' ) ) {
				return new WP_Error( 'rest_forbidden', __( 'You do not have permission to process designs.', 'swpd' ), array( 'status' => 403 ) );
			}
		}
		
		return true;
	}
	
	/**
	 * Check status permission
	 *
	 * @param WP_REST_Request $request
	 * @return bool|WP_Error
	 */
	public function check_status_permission( $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'rest_forbidden', __( 'You do not have permission to view system status.', 'swpd' ), array( 'status' => 403 ) );
		}
		
		return true;
	}
	
	/**
	 * Check if user can access design
	 *
	 * @param object $design
	 * @return bool
	 */
	private function can_access_design( $design ) {
		// Admin can access all
		if ( current_user_can( 'manage_woocommerce' ) ) {
			return true;
		}
		
		// User can access own designs
		if ( is_user_logged_in() && intval( $design->user_id ) === get_current_user_id() ) {
			return true;
		}
		
		// Published designs are public
		if ( $design->status === 'published' ) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Check if user can edit design
	 *
	 * @param object $design
	 * @return bool
	 */
	private function can_edit_design( $design ) {
		// Admin can edit all
		if ( current_user_can( 'manage_woocommerce' ) ) {
			return true;
		}
		
		// User can edit own designs
		if ( is_user_logged_in() && intval( $design->user_id ) === get_current_user_id() ) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Validate API key
	 *
	 * @param string $api_key
	 * @param string $permission
	 * @return bool
	 */
	private function validate_api_key( $api_key, $permission = 'read' ) {
		// Get API keys from options
		$api_keys = get_option( 'swpd_api_keys', array() );
		
		foreach ( $api_keys as $key_data ) {
			if ( hash_equals( $key_data['key'], $api_key ) ) {
				// Check if key is active
				if ( ! $key_data['active'] ) {
					return false;
				}
				
				// Check permissions
				if ( ! in_array( $permission, $key_data['permissions'] ) ) {
					return false;
				}
				
				// Log API usage
				$this->logger->info( 'API key used', array(
					'key_id' => $key_data['id'],
					'permission' => $permission
				));
				
				return true;
			}
		}
		
		return false;
	}
}