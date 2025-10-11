<?php
/**
 * VSS Store Integration Management
 *
 * Manages integration with Qstomize (Shopify), MunchMakers (BigCommerce), and Sequinswag (WordPress)
 *
 * @package VendorOrderManager
 * @since 7.0.2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class VSS_Store_Integration {

    /**
     * Store configurations
     */
    private static $stores = [
        'qstomize' => [
            'name' => 'Qstomize',
            'type' => 'shopify',
            'enabled' => true,
            'api_endpoint' => '',
            'api_key' => '',
            'api_password' => '',
            'store_url' => ''
        ],
        'munchmakers' => [
            'name' => 'MunchMakers', 
            'type' => 'bigcommerce',
            'enabled' => true,
            'api_endpoint' => '',
            'api_token' => '',
            'client_id' => '',
            'store_hash' => ''
        ],
        'sequinswag' => [
            'name' => 'Sequinswag',
            'type' => 'wordpress',
            'enabled' => true,
            'api_endpoint' => '',
            'consumer_key' => '',
            'consumer_secret' => '',
            'site_url' => ''
        ]
    ];

    /**
     * Initialize store integration
     */
    public static function init() {
        // Create database tables
        self::create_integration_tables();
        
        // AJAX handlers
        add_action( 'wp_ajax_vss_get_store_categories', [ self::class, 'ajax_get_store_categories' ] );
        add_action( 'wp_ajax_vss_refresh_store_categories', [ self::class, 'ajax_refresh_store_categories' ] );
        add_action( 'wp_ajax_vss_create_store_product', [ self::class, 'ajax_create_store_product' ] );
        add_action( 'wp_ajax_vss_update_vendor_stores', [ self::class, 'ajax_update_vendor_stores' ] );
        
        // Scheduled category refresh
        add_action( 'vss_daily_category_refresh', [ self::class, 'refresh_all_store_categories' ] );
        if ( ! wp_next_scheduled( 'vss_daily_category_refresh' ) ) {
            wp_schedule_event( time(), 'daily', 'vss_daily_category_refresh' );
        }
        
        // Hook into product submission
        add_action( 'vss_product_approved', [ self::class, 'create_products_in_stores' ], 10, 2 );
    }

    /**
     * Create integration database tables
     */
    public static function create_integration_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Store categories table
        $table_name = $wpdb->prefix . 'vss_store_categories';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            store_key varchar(50) NOT NULL,
            category_id varchar(100) NOT NULL,
            parent_id varchar(100) DEFAULT NULL,
            category_name varchar(255) NOT NULL,
            category_path text DEFAULT NULL,
            is_active tinyint(1) DEFAULT 1,
            last_updated datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY store_key (store_key),
            KEY category_id (category_id),
            KEY parent_id (parent_id),
            UNIQUE KEY unique_store_category (store_key, category_id)
        ) $charset_collate;";
        
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
        
        // Vendor store assignments table
        $table_name = $wpdb->prefix . 'vss_vendor_stores';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            vendor_id bigint(20) NOT NULL,
            store_key varchar(50) NOT NULL,
            is_allowed tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY vendor_id (vendor_id),
            KEY store_key (store_key),
            UNIQUE KEY unique_vendor_store (vendor_id, store_key)
        ) $charset_collate;";
        
        dbDelta( $sql );
        
        // Store product mappings table
        $table_name = $wpdb->prefix . 'vss_store_product_mappings';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            vss_product_id bigint(20) NOT NULL,
            store_key varchar(50) NOT NULL,
            store_product_id varchar(100) NOT NULL,
            store_product_status varchar(50) DEFAULT 'draft',
            store_product_url text DEFAULT NULL,
            sync_status varchar(50) DEFAULT 'pending',
            last_synced datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY vss_product_id (vss_product_id),
            KEY store_key (store_key),
            KEY store_product_id (store_product_id),
            UNIQUE KEY unique_store_product (vss_product_id, store_key)
        ) $charset_collate;";
        
        dbDelta( $sql );
    }

    /**
     * Get available stores for a vendor
     */
    public static function get_vendor_stores( $vendor_id ) {
        global $wpdb;
        
        $assigned_stores = $wpdb->get_results( $wpdb->prepare(
            "SELECT store_key FROM {$wpdb->prefix}vss_vendor_stores 
             WHERE vendor_id = %d AND is_allowed = 1",
            $vendor_id
        ) );
        
        $available_stores = [];
        $assigned_keys = array_column( $assigned_stores, 'store_key' );
        
        foreach ( self::$stores as $key => $store ) {
            if ( in_array( $key, $assigned_keys ) && $store['enabled'] ) {
                $available_stores[$key] = $store['name'];
            }
        }
        
        return $available_stores;
    }

    /**
     * Get store categories
     */
    public static function get_store_categories( $store_key, $parent_id = null ) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'vss_store_categories';
        
        if ( $parent_id ) {
            $categories = $wpdb->get_results( $wpdb->prepare(
                "SELECT * FROM $table_name 
                 WHERE store_key = %s AND parent_id = %s AND is_active = 1 
                 ORDER BY category_name",
                $store_key, $parent_id
            ) );
        } else {
            $categories = $wpdb->get_results( $wpdb->prepare(
                "SELECT * FROM $table_name 
                 WHERE store_key = %s AND (parent_id IS NULL OR parent_id = '') AND is_active = 1 
                 ORDER BY category_name",
                $store_key
            ) );
        }
        
        return $categories;
    }

    /**
     * AJAX: Get store categories
     */
    public static function ajax_get_store_categories() {
        check_ajax_referer( 'vss_frontend_nonce', 'nonce' );
        
        if ( ! self::is_current_user_vendor() ) {
            wp_send_json_error( __( 'Unauthorized', 'vss' ) );
        }
        
        $store_key = sanitize_key( $_POST['store_key'] ?? '' );
        $parent_id = sanitize_text_field( $_POST['parent_id'] ?? '' );
        
        if ( ! $store_key ) {
            wp_send_json_error( __( 'Invalid store', 'vss' ) );
        }
        
        // Check if vendor has access to this store
        $vendor_id = get_current_user_id();
        $available_stores = self::get_vendor_stores( $vendor_id );
        
        if ( ! array_key_exists( $store_key, $available_stores ) ) {
            wp_send_json_error( __( 'Store not available', 'vss' ) );
        }
        
        $categories = self::get_store_categories( $store_key, $parent_id ?: null );
        
        wp_send_json_success( [
            'categories' => $categories,
            'store_name' => $available_stores[$store_key]
        ] );
    }

    /**
     * Refresh store categories from APIs
     */
    public static function refresh_store_categories( $store_key ) {
        $store_config = self::$stores[$store_key] ?? null;
        if ( ! $store_config || ! $store_config['enabled'] ) {
            return false;
        }
        
        switch ( $store_config['type'] ) {
            case 'shopify':
                return self::refresh_shopify_categories( $store_key );
            case 'bigcommerce':
                return self::refresh_bigcommerce_categories( $store_key );
            case 'wordpress':
                return self::refresh_wordpress_categories( $store_key );
        }
        
        return false;
    }

    /**
     * Refresh Shopify categories
     */
    private static function refresh_shopify_categories( $store_key ) {
        $config = self::$stores[$store_key];
        
        // Shopify uses collections as categories
        $url = "https://{$config['store_url']}/admin/api/2023-10/collections.json";
        
        $response = wp_remote_get( $url, [
            'headers' => [
                'X-Shopify-Access-Token' => $config['api_password'],
                'Content-Type' => 'application/json'
            ],
            'timeout' => 30
        ] );
        
        if ( is_wp_error( $response ) ) {
            return false;
        }
        
        $data = json_decode( wp_remote_retrieve_body( $response ), true );
        
        if ( ! isset( $data['collections'] ) ) {
            return false;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'vss_store_categories';
        
        // Clear existing categories for this store
        $wpdb->delete( $table_name, [ 'store_key' => $store_key ] );
        
        foreach ( $data['collections'] as $collection ) {
            $wpdb->insert( $table_name, [
                'store_key' => $store_key,
                'category_id' => $collection['id'],
                'category_name' => $collection['title'],
                'category_path' => $collection['handle'],
                'is_active' => 1
            ] );
        }
        
        return true;
    }

    /**
     * Refresh BigCommerce categories
     */
    private static function refresh_bigcommerce_categories( $store_key ) {
        $config = self::$stores[$store_key];
        
        $url = "https://api.bigcommerce.com/stores/{$config['store_hash']}/v3/catalog/categories";
        
        $response = wp_remote_get( $url, [
            'headers' => [
                'X-Auth-Token' => $config['api_token'],
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ],
            'timeout' => 30
        ] );
        
        if ( is_wp_error( $response ) ) {
            return false;
        }
        
        $data = json_decode( wp_remote_retrieve_body( $response ), true );
        
        if ( ! isset( $data['data'] ) ) {
            return false;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'vss_store_categories';
        
        // Clear existing categories for this store
        $wpdb->delete( $table_name, [ 'store_key' => $store_key ] );
        
        foreach ( $data['data'] as $category ) {
            $wpdb->insert( $table_name, [
                'store_key' => $store_key,
                'category_id' => $category['id'],
                'parent_id' => $category['parent_id'] > 0 ? $category['parent_id'] : null,
                'category_name' => $category['name'],
                'category_path' => $category['name'],
                'is_active' => 1
            ] );
        }
        
        return true;
    }

    /**
     * Refresh WordPress/WooCommerce categories
     */
    private static function refresh_wordpress_categories( $store_key ) {
        $config = self::$stores[$store_key];
        
        $url = "{$config['site_url']}/wp-json/wc/v3/products/categories";
        
        $auth = base64_encode( $config['consumer_key'] . ':' . $config['consumer_secret'] );
        
        $response = wp_remote_get( $url, [
            'headers' => [
                'Authorization' => 'Basic ' . $auth,
                'Content-Type' => 'application/json'
            ],
            'timeout' => 30
        ] );
        
        if ( is_wp_error( $response ) ) {
            return false;
        }
        
        $categories = json_decode( wp_remote_retrieve_body( $response ), true );
        
        if ( ! is_array( $categories ) ) {
            return false;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'vss_store_categories';
        
        // Clear existing categories for this store
        $wpdb->delete( $table_name, [ 'store_key' => $store_key ] );
        
        foreach ( $categories as $category ) {
            $wpdb->insert( $table_name, [
                'store_key' => $store_key,
                'category_id' => $category['id'],
                'parent_id' => $category['parent'] > 0 ? $category['parent'] : null,
                'category_name' => $category['name'],
                'category_path' => $category['name'],
                'is_active' => 1
            ] );
        }
        
        return true;
    }

    /**
     * AJAX: Refresh store categories
     */
    public static function ajax_refresh_store_categories() {
        check_ajax_referer( 'vss_frontend_nonce', 'nonce' );
        
        if ( ! self::is_current_user_vendor() ) {
            wp_send_json_error( __( 'Unauthorized', 'vss' ) );
        }
        
        $store_key = sanitize_key( $_POST['store_key'] ?? '' );
        
        if ( ! $store_key ) {
            wp_send_json_error( __( 'Invalid store', 'vss' ) );
        }
        
        $success = self::refresh_store_categories( $store_key );
        
        if ( $success ) {
            wp_send_json_success( [
                'message' => __( 'Categories refreshed successfully', 'vss' )
            ] );
        } else {
            wp_send_json_error( __( 'Failed to refresh categories', 'vss' ) );
        }
    }

    /**
     * Create products in assigned stores when approved
     */
    public static function create_products_in_stores( $product_id, $admin_user_id ) {
        global $wpdb;
        
        // Get product details
        $product = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}vss_product_uploads WHERE id = %d",
            $product_id
        ) );
        
        if ( ! $product || ! $product->target_stores ) {
            return;
        }
        
        $target_stores = json_decode( $product->target_stores, true );
        if ( ! is_array( $target_stores ) ) {
            return;
        }
        
        foreach ( $target_stores as $store_data ) {
            if ( isset( $store_data['store_key'] ) && $store_data['store_key'] !== 'unknown' ) {
                self::create_product_in_store( $product, $store_data );
            }
        }
    }

    /**
     * Create product in specific store
     */
    private static function create_product_in_store( $product, $store_data ) {
        $store_key = $store_data['store_key'];
        $store_config = self::$stores[$store_key] ?? null;
        
        if ( ! $store_config || ! $store_config['enabled'] ) {
            return false;
        }
        
        switch ( $store_config['type'] ) {
            case 'shopify':
                return self::create_shopify_product( $product, $store_data );
            case 'bigcommerce':
                return self::create_bigcommerce_product( $product, $store_data );
            case 'wordpress':
                return self::create_wordpress_product( $product, $store_data );
        }
        
        return false;
    }

    /**
     * Create Shopify product
     */
    private static function create_shopify_product( $product, $store_data ) {
        $config = self::$stores['qstomize'];
        
        // Get product images
        global $wpdb;
        $images = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}vss_product_images WHERE product_id = %d ORDER BY sort_order",
            $product->id
        ) );
        
        $product_images = [];
        foreach ( $images as $image ) {
            $product_images[] = [
                'src' => $image->image_url,
                'alt' => $image->image_caption_en ?: $product->product_name_en
            ];
        }
        
        // Get pricing
        $pricing = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}vss_product_pricing WHERE product_id = %d ORDER BY min_quantity",
            $product->id
        ) );
        
        $price = $product->selling_price ?: ( $pricing[0]->unit_price ?? 0 );
        
        $shopify_product = [
            'product' => [
                'title' => $product->product_name_en ?: $product->product_name_zh,
                'body_html' => nl2br( $product->description_en ?: $product->description_zh ),
                'vendor' => get_user_by( 'id', $product->vendor_id )->display_name ?? 'Unknown',
                'product_type' => ucfirst( $product->category ),
                'status' => 'draft',
                'published' => false,
                'images' => $product_images,
                'variants' => [
                    [
                        'title' => 'Default',
                        'price' => number_format( $price, 2, '.', '' ),
                        'sku' => $product->sku,
                        'inventory_management' => 'shopify',
                        'inventory_quantity' => 0,
                        'weight' => $product->product_weight,
                        'weight_unit' => 'kg'
                    ]
                ],
                'metafields' => [
                    [
                        'namespace' => 'vss',
                        'key' => 'product_id',
                        'value' => (string) $product->id,
                        'type' => 'single_line_text_field'
                    ],
                    [
                        'namespace' => 'vss',
                        'key' => 'production_time',
                        'value' => (string) $product->production_time,
                        'type' => 'number_integer'
                    ],
                    [
                        'namespace' => 'vss',
                        'key' => 'customization_options',
                        'value' => $product->customization_options,
                        'type' => 'multi_line_text_field'
                    ]
                ]
            ]
        ];
        
        $url = "https://{$config['store_url']}/admin/api/2023-10/products.json";
        
        $response = wp_remote_post( $url, [
            'headers' => [
                'X-Shopify-Access-Token' => $config['api_password'],
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode( $shopify_product ),
            'timeout' => 30
        ] );
        
        if ( is_wp_error( $response ) ) {
            return false;
        }
        
        $result = json_decode( wp_remote_retrieve_body( $response ), true );
        
        if ( isset( $result['product']['id'] ) ) {
            // Save mapping
            $wpdb->insert( $wpdb->prefix . 'vss_store_product_mappings', [
                'vss_product_id' => $product->id,
                'store_key' => 'qstomize',
                'store_product_id' => $result['product']['id'],
                'store_product_status' => 'draft',
                'store_product_url' => "https://{$config['store_url']}/admin/products/{$result['product']['id']}",
                'sync_status' => 'completed',
                'last_synced' => current_time( 'mysql' )
            ] );
            
            return true;
        }
        
        return false;
    }

    /**
     * Create BigCommerce product
     */
    private static function create_bigcommerce_product( $product, $store_data ) {
        $config = self::$stores['munchmakers'];
        
        global $wpdb;
        $images = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}vss_product_images WHERE product_id = %d ORDER BY sort_order",
            $product->id
        ) );
        
        $pricing = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}vss_product_pricing WHERE product_id = %d ORDER BY min_quantity",
            $product->id
        ) );
        
        $price = $product->selling_price ?: ( $pricing[0]->unit_price ?? 0 );
        
        $bc_product = [
            'name' => $product->product_name_en ?: $product->product_name_zh,
            'description' => $product->description_en ?: $product->description_zh,
            'type' => 'physical',
            'price' => $price,
            'sku' => $product->sku,
            'weight' => $product->product_weight,
            'is_visible' => false,
            'availability' => 'available',
            'custom_fields' => [
                [
                    'name' => 'VSS Product ID',
                    'value' => (string) $product->id
                ],
                [
                    'name' => 'Production Time',
                    'value' => $product->production_time . ' days'
                ],
                [
                    'name' => 'Customization Options',
                    'value' => $product->customization_options
                ]
            ]
        ];
        
        if ( isset( $store_data['category_id'] ) && $store_data['category_id'] ) {
            $bc_product['categories'] = [ (int) $store_data['category_id'] ];
        }
        
        $url = "https://api.bigcommerce.com/stores/{$config['store_hash']}/v3/catalog/products";
        
        $response = wp_remote_post( $url, [
            'headers' => [
                'X-Auth-Token' => $config['api_token'],
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ],
            'body' => json_encode( $bc_product ),
            'timeout' => 30
        ] );
        
        if ( is_wp_error( $response ) ) {
            return false;
        }
        
        $result = json_decode( wp_remote_retrieve_body( $response ), true );
        
        if ( isset( $result['data']['id'] ) ) {
            // Save mapping
            $wpdb->insert( $wpdb->prefix . 'vss_store_product_mappings', [
                'vss_product_id' => $product->id,
                'store_key' => 'munchmakers',
                'store_product_id' => $result['data']['id'],
                'store_product_status' => 'draft',
                'store_product_url' => "https://store-{$config['store_hash']}.mybigcommerce.com/manage/products/edit/" . $result['data']['id'],
                'sync_status' => 'completed',
                'last_synced' => current_time( 'mysql' )
            ] );
            
            // Add images if any
            if ( ! empty( $images ) ) {
                self::add_bigcommerce_product_images( $result['data']['id'], $images, $config );
            }
            
            return true;
        }
        
        return false;
    }

    /**
     * Create WordPress/WooCommerce product
     */
    private static function create_wordpress_product( $product, $store_data ) {
        $config = self::$stores['sequinswag'];
        
        global $wpdb;
        $pricing = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}vss_product_pricing WHERE product_id = %d ORDER BY min_quantity",
            $product->id
        ) );
        
        $price = $product->selling_price ?: ( $pricing[0]->unit_price ?? 0 );
        
        $wc_product = [
            'name' => $product->product_name_en ?: $product->product_name_zh,
            'description' => $product->description_en ?: $product->description_zh,
            'short_description' => wp_trim_words( $product->description_en ?: $product->description_zh, 20 ),
            'sku' => $product->sku,
            'regular_price' => (string) $price,
            'status' => 'draft',
            'catalog_visibility' => 'hidden',
            'manage_stock' => true,
            'stock_quantity' => 0,
            'weight' => (string) $product->product_weight,
            'meta_data' => [
                [
                    'key' => '_vss_product_id',
                    'value' => (string) $product->id
                ],
                [
                    'key' => '_vss_production_time',
                    'value' => (string) $product->production_time
                ],
                [
                    'key' => '_vss_customization_options',
                    'value' => $product->customization_options
                ]
            ]
        ];
        
        if ( isset( $store_data['category_id'] ) && $store_data['category_id'] ) {
            $wc_product['categories'] = [
                [ 'id' => (int) $store_data['category_id'] ]
            ];
        }
        
        $url = "{$config['site_url']}/wp-json/wc/v3/products";
        $auth = base64_encode( $config['consumer_key'] . ':' . $config['consumer_secret'] );
        
        $response = wp_remote_post( $url, [
            'headers' => [
                'Authorization' => 'Basic ' . $auth,
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode( $wc_product ),
            'timeout' => 30
        ] );
        
        if ( is_wp_error( $response ) ) {
            return false;
        }
        
        $result = json_decode( wp_remote_retrieve_body( $response ), true );
        
        if ( isset( $result['id'] ) ) {
            // Save mapping
            $wpdb->insert( $wpdb->prefix . 'vss_store_product_mappings', [
                'vss_product_id' => $product->id,
                'store_key' => 'sequinswag',
                'store_product_id' => $result['id'],
                'store_product_status' => 'draft',
                'store_product_url' => $config['site_url'] . '/wp-admin/post.php?post=' . $result['id'] . '&action=edit',
                'sync_status' => 'completed',
                'last_synced' => current_time( 'mysql' )
            ] );
            
            return true;
        }
        
        return false;
    }

    /**
     * Add images to BigCommerce product
     */
    private static function add_bigcommerce_product_images( $product_id, $images, $config ) {
        foreach ( $images as $image ) {
            $image_data = [
                'image_url' => $image->image_url,
                'description' => $image->image_caption_en ?: '',
                'sort_order' => $image->sort_order
            ];
            
            $url = "https://api.bigcommerce.com/stores/{$config['store_hash']}/v3/catalog/products/{$product_id}/images";
            
            wp_remote_post( $url, [
                'headers' => [
                    'X-Auth-Token' => $config['api_token'],
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'body' => json_encode( $image_data ),
                'timeout' => 30
            ] );
        }
    }

    /**
     * Refresh all store categories (scheduled task)
     */
    public static function refresh_all_store_categories() {
        foreach ( array_keys( self::$stores ) as $store_key ) {
            self::refresh_store_categories( $store_key );
        }
    }

    /**
     * Check if current user is vendor
     */
    private static function is_current_user_vendor() {
        $user = wp_get_current_user();
        return in_array( 'vendor-mm', (array) $user->roles, true ) || current_user_can( 'vendor-mm' );
    }

    /**
     * Get store configuration for admin
     */
    public static function get_store_config() {
        return self::$stores;
    }

    /**
     * Update store configuration
     */
    public static function update_store_config( $store_configs ) {
        foreach ( $store_configs as $store_key => $config ) {
            if ( isset( self::$stores[$store_key] ) ) {
                self::$stores[$store_key] = array_merge( self::$stores[$store_key], $config );
                update_option( "vss_store_config_{$store_key}", $config );
            }
        }
    }

    /**
     * Load store configurations from options
     */
    public static function load_store_configs() {
        foreach ( array_keys( self::$stores ) as $store_key ) {
            $saved_config = get_option( "vss_store_config_{$store_key}", [] );
            if ( ! empty( $saved_config ) ) {
                self::$stores[$store_key] = array_merge( self::$stores[$store_key], $saved_config );
            }
        }
    }
}