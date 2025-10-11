<?php
/**
 * VSS Advanced Features Module
 * 
 * AI suggestions, bulk upload, image optimization, and other advanced features
 * 
 * @package VendorOrderManager
 * @subpackage Modules
 * @since 8.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Advanced Features Class
 */
class VSS_Advanced_Features {

    /**
     * Initialize advanced features
     */
    public static function init() {
        // AJAX handlers
        add_action( 'wp_ajax_vss_pro_get_suggestions', [ __CLASS__, 'generate_ai_suggestions' ] );
        add_action( 'wp_ajax_vss_pro_optimize_images', [ __CLASS__, 'optimize_product_images' ] );
        add_action( 'wp_ajax_vss_pro_bulk_upload', [ __CLASS__, 'handle_bulk_upload' ] );
        add_action( 'wp_ajax_vss_generate_csv_template', [ __CLASS__, 'generate_csv_template' ] );
        add_action( 'wp_ajax_vss_pro_duplicate_product', [ __CLASS__, 'duplicate_product' ] );
        add_action( 'wp_ajax_vss_pro_delete_product', [ __CLASS__, 'delete_product' ] );
        add_action( 'wp_ajax_vss_get_subcategories', [ __CLASS__, 'get_subcategories' ] );
        add_action( 'wp_ajax_vss_get_tag_suggestions', [ __CLASS__, 'get_tag_suggestions' ] );
        add_action( 'wp_ajax_vss_pro_load_images', [ __CLASS__, 'load_product_images' ] );
        add_action( 'wp_ajax_vss_pro_set_primary_image', [ __CLASS__, 'set_primary_image' ] );
        add_action( 'wp_ajax_vss_pro_update_image_order', [ __CLASS__, 'update_image_order' ] );
        add_action( 'wp_ajax_vss_market_analysis', [ __CLASS__, 'get_market_analysis' ] );
        add_action( 'wp_ajax_vss_seo_analysis', [ __CLASS__, 'analyze_seo' ] );
        
        // Create advanced features tables
        self::create_advanced_tables();
        
        // Schedule cleanup tasks
        if ( ! wp_next_scheduled( 'vss_cleanup_temp_files' ) ) {
            wp_schedule_event( time(), 'daily', 'vss_cleanup_temp_files' );
        }
        
        add_action( 'vss_cleanup_temp_files', [ __CLASS__, 'cleanup_temp_files' ] );
    }

    /**
     * Create advanced features tables
     */
    public static function create_advanced_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        // AI suggestions cache table
        $table_name = $wpdb->prefix . 'vss_ai_suggestions_cache';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            cache_key varchar(255) NOT NULL,
            suggestions longtext NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            expires_at datetime NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY cache_key (cache_key),
            KEY expires_at (expires_at)
        ) $charset_collate;";
        
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
        
        // Bulk upload sessions table
        $table_name = $wpdb->prefix . 'vss_bulk_upload_sessions';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            session_id varchar(255) NOT NULL,
            vendor_id bigint(20) NOT NULL,
            file_path varchar(500) NOT NULL,
            total_products int(11) DEFAULT 0,
            processed_products int(11) DEFAULT 0,
            successful_products int(11) DEFAULT 0,
            failed_products int(11) DEFAULT 0,
            status enum('pending','processing','completed','failed') DEFAULT 'pending',
            error_log longtext DEFAULT NULL,
            started_at datetime DEFAULT NULL,
            completed_at datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY session_id (session_id),
            KEY vendor_id (vendor_id),
            KEY status (status)
        ) $charset_collate;";
        
        dbDelta( $sql );
        
        // Market analysis cache table
        $table_name = $wpdb->prefix . 'vss_market_analysis_cache';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            category_id bigint(20) NOT NULL,
            keywords varchar(500) NOT NULL,
            analysis_data longtext NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            expires_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY category_id (category_id),
            KEY expires_at (expires_at)
        ) $charset_collate;";
        
        dbDelta( $sql );
    }

    /**
     * Generate AI-powered product suggestions
     */
    public static function generate_ai_suggestions() {
        check_ajax_referer( 'vss_pro_uploader', 'nonce' );
        
        if ( ! self::is_current_user_vendor() ) {
            wp_send_json_error( 'Insufficient permissions' );
        }

        $product_name = sanitize_text_field( $_POST['product_name'] ?? '' );
        $category_id = intval( $_POST['category_id'] ?? 0 );
        $short_description = sanitize_textarea_field( $_POST['short_description'] ?? '' );
        
        // Create cache key
        $cache_key = md5( $product_name . $category_id . $short_description );
        
        // Check cache first
        $cached = self::get_cached_suggestions( $cache_key );
        if ( $cached ) {
            wp_send_json_success( [ 'suggestions' => $cached ] );
        }

        $suggestions = [];
        
        // Title optimization suggestions
        if ( $product_name ) {
            $title_suggestions = self::generate_title_suggestions( $product_name, $category_id );
            $suggestions = array_merge( $suggestions, $title_suggestions );
        }
        
        // Description suggestions
        if ( $short_description ) {
            $description_suggestions = self::generate_description_suggestions( $short_description, $product_name );
            $suggestions = array_merge( $suggestions, $description_suggestions );
        }
        
        // Tag suggestions
        if ( $product_name || $short_description ) {
            $tag_suggestions = self::generate_smart_tag_suggestions( $product_name, $short_description, $category_id );
            if ( $tag_suggestions ) {
                $suggestions[] = $tag_suggestions;
            }
        }
        
        // SEO suggestions
        $seo_suggestions = self::generate_seo_suggestions( $product_name, $short_description );
        $suggestions = array_merge( $suggestions, $seo_suggestions );
        
        // Pricing suggestions
        if ( $category_id ) {
            $pricing_suggestions = self::generate_pricing_suggestions( $category_id, $product_name );
            if ( $pricing_suggestions ) {
                $suggestions[] = $pricing_suggestions;
            }
        }

        // Cache the results for 1 hour
        self::cache_suggestions( $cache_key, $suggestions, 3600 );
        
        wp_send_json_success( [ 'suggestions' => $suggestions ] );
    }

    /**
     * Generate title optimization suggestions
     */
    private static function generate_title_suggestions( $title, $category_id ) {
        $suggestions = [];
        $category = get_term( $category_id );
        
        // Length optimization
        $title_length = strlen( $title );
        if ( $title_length < 20 ) {
            $suggestions[] = [
                'title' => 'Expand Product Title',
                'description' => 'Your title is quite short. Consider adding descriptive words like "Premium", "Professional", or specific features.',
                'field' => 'product_name',
                'value' => $title . ' - Premium Quality',
                'priority' => 'high'
            ];
        } elseif ( $title_length > 70 ) {
            $suggestions[] = [
                'title' => 'Shorten Product Title',
                'description' => 'Your title is quite long. Consider making it more concise for better readability.',
                'field' => 'product_name',
                'value' => self::truncate_title( $title, 60 ),
                'priority' => 'medium'
            ];
        }

        // Category-specific suggestions
        if ( $category ) {
            $category_keywords = self::get_category_keywords( $category->slug );
            if ( $category_keywords ) {
                $missing_keywords = array_diff( $category_keywords, explode( ' ', strtolower( $title ) ) );
                if ( ! empty( $missing_keywords ) ) {
                    $suggested_keyword = $missing_keywords[0];
                    $suggestions[] = [
                        'title' => 'Add Category-Relevant Keywords',
                        'description' => "Consider adding '$suggested_keyword' to improve discoverability in the {$category->name} category.",
                        'field' => 'product_name',
                        'value' => $title . ' ' . ucfirst( $suggested_keyword ),
                        'priority' => 'medium'
                    ];
                }
            }
        }

        return $suggestions;
    }

    /**
     * Generate description enhancement suggestions
     */
    private static function generate_description_suggestions( $description, $title ) {
        $suggestions = [];
        $word_count = str_word_count( $description );
        
        if ( $word_count < 10 ) {
            $suggestions[] = [
                'title' => 'Expand Product Description',
                'description' => 'Add more details about features, benefits, and use cases to help customers make informed decisions.',
                'field' => 'short_description',
                'value' => $description . ' This high-quality product offers exceptional value and performance for all your needs.',
                'priority' => 'high'
            ];
        }

        // Check for benefit-focused language
        $benefit_words = ['benefit', 'advantage', 'perfect', 'ideal', 'solution', 'helps', 'improves'];
        $has_benefits = false;
        foreach ( $benefit_words as $word ) {
            if ( stripos( $description, $word ) !== false ) {
                $has_benefits = true;
                break;
            }
        }

        if ( ! $has_benefits ) {
            $suggestions[] = [
                'title' => 'Add Customer Benefits',
                'description' => 'Focus on benefits rather than just features. How does this product improve the customer\'s life?',
                'field' => 'short_description',
                'value' => $description . ' Perfect for customers looking for reliable, high-performance solutions.',
                'priority' => 'medium'
            ];
        }

        return $suggestions;
    }

    /**
     * Generate smart tag suggestions
     */
    private static function generate_smart_tag_suggestions( $title, $description, $category_id ) {
        $text = strtolower( $title . ' ' . $description );
        $suggested_tags = [];
        
        // Extract potential tags from text
        $common_descriptors = [
            'premium', 'professional', 'deluxe', 'standard', 'basic', 'advanced',
            'wireless', 'bluetooth', 'usb', 'rechargeable', 'portable', 'compact',
            'durable', 'lightweight', 'heavy-duty', 'waterproof', 'indoor', 'outdoor'
        ];

        foreach ( $common_descriptors as $descriptor ) {
            if ( stripos( $text, $descriptor ) !== false ) {
                $suggested_tags[] = $descriptor;
            }
        }

        // Add category-specific tags
        if ( $category_id ) {
            $category_tags = self::get_category_tags( $category_id );
            $suggested_tags = array_merge( $suggested_tags, array_slice( $category_tags, 0, 3 ) );
        }

        if ( ! empty( $suggested_tags ) ) {
            return [
                'title' => 'Suggested Tags',
                'description' => 'These tags can help customers find your product more easily.',
                'field' => 'tags',
                'value' => implode( ', ', array_unique( $suggested_tags ) ),
                'priority' => 'medium'
            ];
        }

        return null;
    }

    /**
     * Generate SEO suggestions
     */
    private static function generate_seo_suggestions( $title, $description ) {
        $suggestions = [];
        
        // Meta title suggestion
        if ( strlen( $title ) > 60 ) {
            $suggestions[] = [
                'title' => 'Optimize Meta Title',
                'description' => 'Your product title is long for search engines. Consider a shorter version for better SEO.',
                'field' => 'meta_title',
                'value' => self::truncate_title( $title, 55 ),
                'priority' => 'medium'
            ];
        }

        // Meta description suggestion
        if ( strlen( $description ) < 120 ) {
            $enhanced_description = $description . ' Shop now for fast shipping and great customer service.';
            $suggestions[] = [
                'title' => 'Enhance Meta Description',
                'description' => 'A longer meta description can improve your search engine visibility.',
                'field' => 'meta_description',
                'value' => $enhanced_description,
                'priority' => 'low'
            ];
        }

        return $suggestions;
    }

    /**
     * Generate pricing suggestions based on market analysis
     */
    private static function generate_pricing_suggestions( $category_id, $product_name ) {
        // Get market data for category
        $market_data = self::get_market_analysis( $category_id );
        
        if ( empty( $market_data['avg_price'] ) ) {
            return null;
        }

        $avg_price = $market_data['avg_price'];
        $suggested_price = $avg_price;

        // Adjust based on product name indicators
        if ( stripos( $product_name, 'premium' ) !== false || 
             stripos( $product_name, 'professional' ) !== false ||
             stripos( $product_name, 'deluxe' ) !== false ) {
            $suggested_price = $avg_price * 1.3;
        } elseif ( stripos( $product_name, 'basic' ) !== false ||
                   stripos( $product_name, 'standard' ) !== false ) {
            $suggested_price = $avg_price * 0.8;
        }

        return [
            'title' => 'Market-Based Pricing',
            'description' => "Based on similar products in this category, the average price is $" . number_format( $avg_price, 2 ) . ".",
            'field' => 'suggested_price',
            'value' => number_format( $suggested_price, 2 ),
            'priority' => 'high'
        ];
    }

    /**
     * Optimize product images
     */
    public static function optimize_product_images() {
        check_ajax_referer( 'vss_pro_uploader', 'nonce' );
        
        if ( ! self::is_current_user_vendor() ) {
            wp_send_json_error( 'Insufficient permissions' );
        }

        $product_id = intval( $_POST['product_id'] );
        $vendor_id = get_current_user_id();
        
        // Get product images
        global $wpdb;
        $images = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}vss_pro_product_images WHERE product_id = %d",
            $product_id
        ) );

        if ( empty( $images ) ) {
            wp_send_json_error( 'No images found to optimize' );
        }

        $optimized_count = 0;
        $errors = [];

        foreach ( $images as $image ) {
            $result = self::optimize_single_image( $image->attachment_id );
            if ( $result['success'] ) {
                $optimized_count++;
                
                // Update optimization data
                $wpdb->update(
                    $wpdb->prefix . 'vss_pro_product_images',
                    [
                        'optimization_data' => wp_json_encode( $result['data'] )
                    ],
                    [ 'id' => $image->id ]
                );
            } else {
                $errors[] = $result['error'];
            }
        }

        if ( $optimized_count > 0 ) {
            wp_send_json_success( [
                'message' => sprintf( 'Optimized %d images successfully', $optimized_count ),
                'optimized_count' => $optimized_count,
                'errors' => $errors
            ] );
        } else {
            wp_send_json_error( [
                'message' => 'Failed to optimize images',
                'errors' => $errors
            ] );
        }
    }

    /**
     * Optimize a single image
     */
    private static function optimize_single_image( $attachment_id ) {
        $file_path = get_attached_file( $attachment_id );
        if ( ! $file_path || ! file_exists( $file_path ) ) {
            return [ 'success' => false, 'error' => 'File not found' ];
        }

        $original_size = filesize( $file_path );
        $image_info = getimagesize( $file_path );
        
        if ( ! $image_info ) {
            return [ 'success' => false, 'error' => 'Invalid image file' ];
        }

        $image_type = $image_info[2];
        $optimized = false;
        
        try {
            switch ( $image_type ) {
                case IMAGETYPE_JPEG:
                    $image = imagecreatefromjpeg( $file_path );
                    if ( $image ) {
                        imagejpeg( $image, $file_path, 85 ); // 85% quality
                        imagedestroy( $image );
                        $optimized = true;
                    }
                    break;
                    
                case IMAGETYPE_PNG:
                    $image = imagecreatefrompng( $file_path );
                    if ( $image ) {
                        // Enable compression
                        imagesavealpha( $image, true );
                        imagepng( $image, $file_path, 6 ); // Compression level 6
                        imagedestroy( $image );
                        $optimized = true;
                    }
                    break;
            }

            if ( $optimized ) {
                $new_size = filesize( $file_path );
                $savings = $original_size - $new_size;
                $savings_percent = ( $savings / $original_size ) * 100;

                // Generate WebP version if supported
                $webp_path = self::generate_webp_version( $file_path );

                return [
                    'success' => true,
                    'data' => [
                        'original_size' => $original_size,
                        'optimized_size' => $new_size,
                        'savings' => $savings,
                        'savings_percent' => round( $savings_percent, 2 ),
                        'webp_generated' => ! empty( $webp_path ),
                        'optimized_at' => current_time( 'mysql' )
                    ]
                ];
            }

        } catch ( Exception $e ) {
            return [ 'success' => false, 'error' => $e->getMessage() ];
        }

        return [ 'success' => false, 'error' => 'Optimization failed' ];
    }

    /**
     * Generate WebP version of image
     */
    private static function generate_webp_version( $file_path ) {
        if ( ! function_exists( 'imagewebp' ) ) {
            return false;
        }

        $path_info = pathinfo( $file_path );
        $webp_path = $path_info['dirname'] . '/' . $path_info['filename'] . '.webp';

        $image_info = getimagesize( $file_path );
        if ( ! $image_info ) return false;

        $image = null;
        switch ( $image_info[2] ) {
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg( $file_path );
                break;
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng( $file_path );
                break;
        }

        if ( $image ) {
            $result = imagewebp( $image, $webp_path, 80 );
            imagedestroy( $image );
            return $result ? $webp_path : false;
        }

        return false;
    }

    /**
     * Handle bulk product upload
     */
    public static function handle_bulk_upload() {
        check_ajax_referer( 'vss_pro_uploader', 'nonce' );
        
        if ( ! self::is_current_user_vendor() ) {
            wp_send_json_error( 'Insufficient permissions' );
        }

        if ( empty( $_FILES['bulk_file'] ) ) {
            wp_send_json_error( 'No file uploaded' );
        }

        $file = $_FILES['bulk_file'];
        
        // Validate file type
        $allowed_types = [ 'text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' ];
        if ( ! in_array( $file['type'], $allowed_types ) ) {
            wp_send_json_error( 'Invalid file type. Please upload CSV or Excel file.' );
        }

        // Move uploaded file
        $upload_dir = wp_upload_dir();
        $temp_dir = $upload_dir['basedir'] . '/vss-temp/';
        if ( ! file_exists( $temp_dir ) ) {
            wp_mkdir_p( $temp_dir );
        }

        $session_id = uniqid( 'bulk_', true );
        $file_path = $temp_dir . $session_id . '_' . sanitize_file_name( $file['name'] );
        
        if ( ! move_uploaded_file( $file['tmp_name'], $file_path ) ) {
            wp_send_json_error( 'Failed to save uploaded file' );
        }

        // Parse file and count products
        $products = self::parse_bulk_upload_file( $file_path );
        if ( is_wp_error( $products ) ) {
            unlink( $file_path );
            wp_send_json_error( $products->get_error_message() );
        }

        // Create bulk upload session
        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . 'vss_bulk_upload_sessions',
            [
                'session_id' => $session_id,
                'vendor_id' => get_current_user_id(),
                'file_path' => $file_path,
                'total_products' => count( $products ),
                'status' => 'pending'
            ]
        );

        wp_send_json_success( [
            'session_id' => $session_id,
            'total_products' => count( $products ),
            'preview' => array_slice( $products, 0, 5 ) // Show first 5 for preview
        ] );
    }

    /**
     * Parse bulk upload file
     */
    private static function parse_bulk_upload_file( $file_path ) {
        $extension = pathinfo( $file_path, PATHINFO_EXTENSION );
        
        if ( strtolower( $extension ) === 'csv' ) {
            return self::parse_csv_file( $file_path );
        } else {
            return self::parse_excel_file( $file_path );
        }
    }

    /**
     * Parse CSV file
     */
    private static function parse_csv_file( $file_path ) {
        $products = [];
        $required_columns = [ 'product_name', 'short_description', 'category_id' ];
        
        if ( ( $handle = fopen( $file_path, 'r' ) ) !== false ) {
            $headers = fgetcsv( $handle );
            if ( ! $headers ) {
                return new WP_Error( 'invalid_file', 'Could not read file headers' );
            }

            // Validate required columns
            foreach ( $required_columns as $column ) {
                if ( ! in_array( $column, $headers ) ) {
                    return new WP_Error( 'missing_column', "Missing required column: $column" );
                }
            }

            $row_number = 1;
            while ( ( $row = fgetcsv( $handle ) ) !== false ) {
                $row_number++;
                if ( count( $row ) !== count( $headers ) ) {
                    continue; // Skip malformed rows
                }

                $product = array_combine( $headers, $row );
                $product['row_number'] = $row_number;
                $products[] = $product;
            }
            fclose( $handle );
        } else {
            return new WP_Error( 'file_error', 'Could not open file' );
        }

        return $products;
    }

    /**
     * Parse Excel file (simplified - would need library like PhpSpreadsheet for full support)
     */
    private static function parse_excel_file( $file_path ) {
        // This is a simplified version. In production, you'd use PhpSpreadsheet
        return new WP_Error( 'not_supported', 'Excel files not yet supported. Please use CSV format.' );
    }

    /**
     * Process bulk upload session
     */
    public static function process_bulk_upload_session( $session_id ) {
        global $wpdb;
        
        $session = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}vss_bulk_upload_sessions WHERE session_id = %s",
            $session_id
        ) );

        if ( ! $session || $session->status !== 'pending' ) {
            return false;
        }

        // Update status to processing
        $wpdb->update(
            $wpdb->prefix . 'vss_bulk_upload_sessions',
            [
                'status' => 'processing',
                'started_at' => current_time( 'mysql' )
            ],
            [ 'session_id' => $session_id ]
        );

        // Parse and process products
        $products = self::parse_bulk_upload_file( $session->file_path );
        if ( is_wp_error( $products ) ) {
            $wpdb->update(
                $wpdb->prefix . 'vss_bulk_upload_sessions',
                [
                    'status' => 'failed',
                    'error_log' => $products->get_error_message(),
                    'completed_at' => current_time( 'mysql' )
                ],
                [ 'session_id' => $session_id ]
            );
            return false;
        }

        $successful = 0;
        $failed = 0;
        $errors = [];

        foreach ( $products as $product_data ) {
            $result = self::create_product_from_bulk_data( $product_data, $session->vendor_id );
            if ( $result['success'] ) {
                $successful++;
            } else {
                $failed++;
                $errors[] = "Row {$product_data['row_number']}: {$result['error']}";
            }

            // Update progress
            $wpdb->update(
                $wpdb->prefix . 'vss_bulk_upload_sessions',
                [
                    'processed_products' => $successful + $failed,
                    'successful_products' => $successful,
                    'failed_products' => $failed
                ],
                [ 'session_id' => $session_id ]
            );
        }

        // Complete session
        $wpdb->update(
            $wpdb->prefix . 'vss_bulk_upload_sessions',
            [
                'status' => 'completed',
                'error_log' => ! empty( $errors ) ? implode( "\n", $errors ) : null,
                'completed_at' => current_time( 'mysql' )
            ],
            [ 'session_id' => $session_id ]
        );

        // Cleanup file
        if ( file_exists( $session->file_path ) ) {
            unlink( $session->file_path );
        }

        return true;
    }

    /**
     * Create product from bulk upload data
     */
    private static function create_product_from_bulk_data( $data, $vendor_id ) {
        global $wpdb;
        
        // Validate required fields
        if ( empty( $data['product_name'] ) ) {
            return [ 'success' => false, 'error' => 'Product name is required' ];
        }

        // Sanitize data
        $product_data = [
            'vendor_id' => $vendor_id,
            'product_name' => sanitize_text_field( $data['product_name'] ),
            'product_slug' => sanitize_title( $data['product_slug'] ?? $data['product_name'] ),
            'short_description' => sanitize_textarea_field( $data['short_description'] ?? '' ),
            'full_description' => wp_kses_post( $data['full_description'] ?? '' ),
            'category_id' => intval( $data['category_id'] ?? 0 ),
            'subcategory_id' => intval( $data['subcategory_id'] ?? 0 ) ?: null,
            'tags' => sanitize_text_field( $data['tags'] ?? '' ),
            'sku' => sanitize_text_field( $data['sku'] ?? '' ),
            'brand' => sanitize_text_field( $data['brand'] ?? '' ),
            'production_cost' => floatval( $data['production_cost'] ?? 0 ) ?: null,
            'suggested_price' => floatval( $data['suggested_price'] ?? 0 ) ?: null,
            'status' => 'draft',
            'created_at' => current_time( 'mysql' )
        ];

        // Insert product
        $result = $wpdb->insert( $wpdb->prefix . 'vss_pro_product_uploads', $product_data );
        
        if ( $result === false ) {
            return [ 'success' => false, 'error' => 'Database error: ' . $wpdb->last_error ];
        }

        return [ 'success' => true, 'product_id' => $wpdb->insert_id ];
    }

    /**
     * Generate CSV template for bulk upload
     */
    public static function generate_csv_template() {
        check_ajax_referer( 'vss_bulk_upload', 'nonce' );
        
        $columns = [
            'product_name',
            'product_slug',
            'short_description',
            'full_description',
            'category_id',
            'subcategory_id',
            'tags',
            'sku',
            'brand',
            'manufacturer',
            'production_cost',
            'suggested_price',
            'weight',
            'dimensions_length',
            'dimensions_width',
            'dimensions_height',
            'production_time_days'
        ];

        header( 'Content-Type: text/csv' );
        header( 'Content-Disposition: attachment; filename="product-upload-template.csv"' );

        $output = fopen( 'php://output', 'w' );
        fputcsv( $output, $columns );
        
        // Add sample row
        $sample_data = [
            'Premium Wireless Headphones',
            'premium-wireless-headphones',
            'High-quality wireless headphones with noise cancellation',
            'Experience superior sound quality with our premium wireless headphones featuring active noise cancellation, 30-hour battery life, and crystal-clear audio.',
            '1', // Electronics category ID
            '',
            'wireless, bluetooth, headphones, audio, premium',
            'PWH001',
            'AudioTech',
            'AudioTech Manufacturing',
            '45.00',
            '129.99',
            '0.3',
            '20',
            '18',
            '8',
            '3'
        ];
        
        fputcsv( $output, $sample_data );
        fclose( $output );
        exit;
    }

    /**
     * Get market analysis data
     */
    public static function get_market_analysis() {
        check_ajax_referer( 'vss_pro_uploader', 'nonce' );
        
        $category_id = intval( $_POST['category_id'] );
        $keywords = sanitize_text_field( $_POST['keywords'] ?? '' );
        
        $analysis = self::get_cached_market_analysis( $category_id, $keywords );
        
        if ( ! $analysis ) {
            $analysis = self::perform_market_analysis( $category_id, $keywords );
            self::cache_market_analysis( $category_id, $keywords, $analysis );
        }
        
        wp_send_json_success( $analysis );
    }

    /**
     * Perform market analysis
     */
    private static function perform_market_analysis( $category_id, $keywords ) {
        global $wpdb;
        
        // Get similar products in category
        $products = $wpdb->get_results( $wpdb->prepare(
            "SELECT suggested_price, production_cost FROM {$wpdb->prefix}vss_pro_product_uploads 
             WHERE category_id = %d AND status = 'approved' AND suggested_price > 0",
            $category_id
        ) );

        if ( empty( $products ) ) {
            return [
                'avg_price' => 0,
                'price_range' => [ 'min' => 0, 'max' => 0 ],
                'competition_level' => 'unknown',
                'recommendations' => [ 'Insufficient data for analysis' ]
            ];
        }

        $prices = array_map( function( $p ) { return floatval( $p->suggested_price ); }, $products );
        $costs = array_filter( array_map( function( $p ) { return floatval( $p->production_cost ); }, $products ) );

        $analysis = [
            'avg_price' => array_sum( $prices ) / count( $prices ),
            'price_range' => [
                'min' => min( $prices ),
                'max' => max( $prices )
            ],
            'competition_level' => count( $products ) > 50 ? 'high' : ( count( $products ) > 20 ? 'medium' : 'low' ),
            'avg_margin' => ! empty( $costs ) ? ( array_sum( $prices ) / count( $prices ) ) - ( array_sum( $costs ) / count( $costs ) ) : 0,
            'recommendations' => self::generate_market_recommendations( $prices, $costs, count( $products ) )
        ];

        return $analysis;
    }

    /**
     * Generate market-based recommendations
     */
    private static function generate_market_recommendations( $prices, $costs, $product_count ) {
        $recommendations = [];
        
        $avg_price = array_sum( $prices ) / count( $prices );
        $price_std = self::calculate_standard_deviation( $prices );
        
        if ( $price_std / $avg_price > 0.5 ) {
            $recommendations[] = 'High price variation in this category suggests opportunity for different price points';
        }
        
        if ( $product_count > 100 ) {
            $recommendations[] = 'Highly competitive category - focus on unique features and quality';
        } elseif ( $product_count < 10 ) {
            $recommendations[] = 'Low competition category - opportunity for market leadership';
        }
        
        if ( ! empty( $costs ) ) {
            $avg_cost = array_sum( $costs ) / count( $costs );
            $avg_margin = $avg_price - $avg_cost;
            $margin_percent = ( $avg_margin / $avg_price ) * 100;
            
            if ( $margin_percent > 60 ) {
                $recommendations[] = 'High margin category - consider premium positioning';
            } elseif ( $margin_percent < 30 ) {
                $recommendations[] = 'Low margin category - focus on volume and efficiency';
            }
        }
        
        return $recommendations;
    }

    /**
     * Calculate standard deviation
     */
    private static function calculate_standard_deviation( $array ) {
        $mean = array_sum( $array ) / count( $array );
        $variance = array_sum( array_map( function( $x ) use ( $mean ) { return pow( $x - $mean, 2 ); }, $array ) ) / count( $array );
        return sqrt( $variance );
    }

    // Helper methods for caching and data retrieval

    private static function get_cached_suggestions( $cache_key ) {
        global $wpdb;
        
        $cached = $wpdb->get_row( $wpdb->prepare(
            "SELECT suggestions FROM {$wpdb->prefix}vss_ai_suggestions_cache 
             WHERE cache_key = %s AND expires_at > NOW()",
            $cache_key
        ) );

        return $cached ? json_decode( $cached->suggestions, true ) : null;
    }

    private static function cache_suggestions( $cache_key, $suggestions, $duration ) {
        global $wpdb;
        
        $expires_at = date( 'Y-m-d H:i:s', time() + $duration );
        
        $wpdb->replace(
            $wpdb->prefix . 'vss_ai_suggestions_cache',
            [
                'cache_key' => $cache_key,
                'suggestions' => wp_json_encode( $suggestions ),
                'expires_at' => $expires_at
            ]
        );
    }

    private static function get_category_keywords( $category_slug ) {
        $keyword_map = [
            'electronics' => [ 'digital', 'tech', 'smart', 'wireless', 'bluetooth' ],
            'clothing' => [ 'fashion', 'style', 'comfortable', 'trendy', 'quality' ],
            'home-garden' => [ 'decorative', 'functional', 'durable', 'design', 'practical' ]
        ];

        return $keyword_map[ $category_slug ] ?? [];
    }

    private static function get_category_tags( $category_id ) {
        // This would typically query a tags database or API
        return [ 'popular', 'trending', 'bestseller', 'featured', 'recommended' ];
    }

    private static function truncate_title( $title, $max_length ) {
        if ( strlen( $title ) <= $max_length ) return $title;
        
        $truncated = substr( $title, 0, $max_length );
        $last_space = strrpos( $truncated, ' ' );
        
        return $last_space !== false ? substr( $truncated, 0, $last_space ) : $truncated;
    }

    private static function is_current_user_vendor() {
        return current_user_can( 'vss_vendor' ) || current_user_can( 'manage_woocommerce' );
    }

    /**
     * Cleanup temporary files
     */
    public static function cleanup_temp_files() {
        $upload_dir = wp_upload_dir();
        $temp_dir = $upload_dir['basedir'] . '/vss-temp/';
        
        if ( ! is_dir( $temp_dir ) ) return;
        
        $files = scandir( $temp_dir );
        foreach ( $files as $file ) {
            if ( $file === '.' || $file === '..' ) continue;
            
            $file_path = $temp_dir . $file;
            if ( is_file( $file_path ) && time() - filemtime( $file_path ) > 86400 ) { // 24 hours
                unlink( $file_path );
            }
        }
    }

    // Additional AJAX handlers for completeness

    public static function get_subcategories() {
        check_ajax_referer( 'vss_pro_uploader', 'nonce' );
        
        $category_id = intval( $_POST['category_id'] );
        
        $subcategories = get_terms( [
            'taxonomy' => 'product_cat',
            'parent' => $category_id,
            'hide_empty' => false
        ] );

        $result = array_map( function( $term ) {
            return [ 'id' => $term->term_id, 'name' => $term->name ];
        }, $subcategories );

        wp_send_json_success( $result );
    }

    public static function get_tag_suggestions() {
        check_ajax_referer( 'vss_pro_uploader', 'nonce' );
        
        $category = sanitize_text_field( $_POST['category'] );
        
        // This would typically fetch from a database of popular tags
        $suggestions = [
            'premium', 'quality', 'durable', 'lightweight', 'professional',
            'modern', 'stylish', 'efficient', 'reliable', 'affordable'
        ];

        wp_send_json_success( $suggestions );
    }

    public static function load_product_images() {
        check_ajax_referer( 'vss_pro_uploader', 'nonce' );
        
        $product_id = intval( $_POST['product_id'] );
        $vendor_id = get_current_user_id();
        
        global $wpdb;
        $images = $wpdb->get_results( $wpdb->prepare(
            "SELECT pi.*, pm.meta_value as url 
             FROM {$wpdb->prefix}vss_pro_product_images pi
             JOIN {$wpdb->prefix}postmeta pm ON pi.attachment_id = pm.post_id AND pm.meta_key = '_wp_attached_file'
             WHERE pi.product_id = %d
             ORDER BY pi.image_type, pi.sort_order",
            $product_id
        ) );

        $grouped_images = [];
        foreach ( $images as $image ) {
            $image->url = wp_get_attachment_url( $image->attachment_id );
            $grouped_images[ $image->image_type ][] = $image;
        }

        wp_send_json_success( $grouped_images );
    }

    public static function set_primary_image() {
        check_ajax_referer( 'vss_pro_uploader', 'nonce' );
        
        $image_id = intval( $_POST['image_id'] );
        
        global $wpdb;
        
        // Get the product_id and image_type for this image
        $image = $wpdb->get_row( $wpdb->prepare(
            "SELECT product_id, image_type FROM {$wpdb->prefix}vss_pro_product_images WHERE id = %d",
            $image_id
        ) );

        if ( ! $image ) {
            wp_send_json_error( 'Image not found' );
        }

        // Remove primary flag from other images of the same type
        $wpdb->update(
            $wpdb->prefix . 'vss_pro_product_images',
            [ 'is_primary' => 0 ],
            [ 
                'product_id' => $image->product_id,
                'image_type' => $image->image_type
            ]
        );

        // Set this image as primary
        $wpdb->update(
            $wpdb->prefix . 'vss_pro_product_images',
            [ 'is_primary' => 1 ],
            [ 'id' => $image_id ]
        );

        wp_send_json_success( 'Primary image updated' );
    }

    public static function update_image_order() {
        check_ajax_referer( 'vss_pro_uploader', 'nonce' );
        
        $orders = $_POST['orders'] ?? [];
        
        global $wpdb;
        
        foreach ( $orders as $image_type => $type_orders ) {
            foreach ( $type_orders as $order_data ) {
                $wpdb->update(
                    $wpdb->prefix . 'vss_pro_product_images',
                    [ 'sort_order' => intval( $order_data['order'] ) ],
                    [ 'id' => intval( $order_data['id'] ) ]
                );
            }
        }

        wp_send_json_success( 'Image order updated' );
    }

    public static function duplicate_product() {
        check_ajax_referer( 'vss_pro_uploader', 'nonce' );
        
        $product_id = intval( $_POST['product_id'] );
        $vendor_id = get_current_user_id();
        
        global $wpdb;
        
        // Get original product
        $original = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}vss_pro_product_uploads WHERE id = %d AND vendor_id = %d",
            $product_id, $vendor_id
        ), ARRAY_A );

        if ( ! $original ) {
            wp_send_json_error( 'Product not found' );
        }

        // Create duplicate
        unset( $original['id'] );
        $original['product_name'] = $original['product_name'] . ' (Copy)';
        $original['product_slug'] = $original['product_slug'] . '-copy';
        $original['status'] = 'draft';
        $original['created_at'] = current_time( 'mysql' );
        $original['updated_at'] = current_time( 'mysql' );

        $result = $wpdb->insert( $wpdb->prefix . 'vss_pro_product_uploads', $original );
        
        if ( $result ) {
            $new_product_id = $wpdb->insert_id;
            
            wp_send_json_success( [
                'message' => 'Product duplicated successfully',
                'redirect_url' => admin_url( "admin.php?page=vss_product_uploads&action=edit&product_id={$new_product_id}" )
            ] );
        } else {
            wp_send_json_error( 'Duplication failed' );
        }
    }

    public static function delete_product() {
        check_ajax_referer( 'vss_pro_uploader', 'nonce' );
        
        $product_id = intval( $_POST['product_id'] );
        $vendor_id = get_current_user_id();
        
        global $wpdb;
        
        // Delete product and related data
        $deleted = $wpdb->delete(
            $wpdb->prefix . 'vss_pro_product_uploads',
            [ 'id' => $product_id, 'vendor_id' => $vendor_id ]
        );

        if ( $deleted ) {
            // Delete related images
            $wpdb->delete(
                $wpdb->prefix . 'vss_pro_product_images',
                [ 'product_id' => $product_id ]
            );

            // Delete variants
            $wpdb->delete(
                $wpdb->prefix . 'vss_pro_product_variants',
                [ 'product_id' => $product_id ]
            );

            wp_send_json_success( [
                'message' => 'Product deleted successfully',
                'redirect_url' => admin_url( 'admin.php?page=vss_product_uploads' )
            ] );
        } else {
            wp_send_json_error( 'Deletion failed' );
        }
    }

    private static function get_cached_market_analysis( $category_id, $keywords ) {
        global $wpdb;
        
        $cached = $wpdb->get_row( $wpdb->prepare(
            "SELECT analysis_data FROM {$wpdb->prefix}vss_market_analysis_cache 
             WHERE category_id = %d AND keywords = %s AND expires_at > NOW()",
            $category_id, $keywords
        ) );

        return $cached ? json_decode( $cached->analysis_data, true ) : null;
    }

    private static function cache_market_analysis( $category_id, $keywords, $analysis ) {
        global $wpdb;
        
        $expires_at = date( 'Y-m-d H:i:s', time() + 3600 ); // 1 hour
        
        $wpdb->replace(
            $wpdb->prefix . 'vss_market_analysis_cache',
            [
                'category_id' => $category_id,
                'keywords' => $keywords,
                'analysis_data' => wp_json_encode( $analysis ),
                'expires_at' => $expires_at
            ]
        );
    }
}

// Initialize the class
add_action( 'plugins_loaded', [ 'VSS_Advanced_Features', 'init' ] );