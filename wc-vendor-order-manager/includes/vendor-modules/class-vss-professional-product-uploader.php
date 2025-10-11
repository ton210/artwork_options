<?php
/**
 * VSS Professional Product Uploader Module
 * 
 * Modern, intuitive product upload interface with advanced features
 * 
 * @package VendorOrderManager
 * @subpackage Modules
 * @since 8.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Professional Product Uploader Class
 */
class VSS_Professional_Product_Uploader {

    /**
     * Initialize the professional uploader
     */
    public static function init() {
        // AJAX handlers
        add_action( 'wp_ajax_vss_pro_upload_product', [ __CLASS__, 'handle_product_upload' ] );
        add_action( 'wp_ajax_vss_pro_save_draft', [ __CLASS__, 'save_product_draft' ] );
        add_action( 'wp_ajax_vss_pro_upload_images', [ __CLASS__, 'handle_image_upload' ] );
        add_action( 'wp_ajax_vss_pro_delete_image', [ __CLASS__, 'delete_product_image' ] );
        add_action( 'wp_ajax_vss_pro_optimize_images', [ __CLASS__, 'optimize_product_images' ] );
        add_action( 'wp_ajax_vss_pro_validate_product', [ __CLASS__, 'validate_product_data' ] );
        add_action( 'wp_ajax_vss_pro_get_suggestions', [ __CLASS__, 'get_ai_suggestions' ] );
        add_action( 'wp_ajax_vss_pro_bulk_upload', [ __CLASS__, 'handle_bulk_upload' ] );
        add_action( 'wp_ajax_vss_pro_generate_variants', [ __CLASS__, 'generate_product_variants' ] );
        
        // Create enhanced tables
        self::create_professional_tables();
        
        // Enqueue scripts and styles
        add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
    }

    /**
     * Create professional uploader tables
     */
    public static function create_professional_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        // Enhanced product uploads table
        $table_name = $wpdb->prefix . 'vss_pro_product_uploads';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            vendor_id bigint(20) NOT NULL,
            product_name varchar(255) NOT NULL,
            product_slug varchar(255) NOT NULL,
            short_description text DEFAULT NULL,
            full_description longtext DEFAULT NULL,
            product_type enum('simple','variable','grouped','external') DEFAULT 'simple',
            category_id bigint(20) DEFAULT NULL,
            subcategory_id bigint(20) DEFAULT NULL,
            tags text DEFAULT NULL,
            sku varchar(100) DEFAULT NULL,
            barcode varchar(100) DEFAULT NULL,
            brand varchar(255) DEFAULT NULL,
            manufacturer varchar(255) DEFAULT NULL,
            model_number varchar(100) DEFAULT NULL,
            dimensions_length decimal(10,2) DEFAULT NULL,
            dimensions_width decimal(10,2) DEFAULT NULL,
            dimensions_height decimal(10,2) DEFAULT NULL,
            weight decimal(10,2) DEFAULT NULL,
            material varchar(255) DEFAULT NULL,
            color_options text DEFAULT NULL,
            size_options text DEFAULT NULL,
            production_cost decimal(10,2) DEFAULT NULL,
            suggested_price decimal(10,2) DEFAULT NULL,
            minimum_order_quantity int(11) DEFAULT 1,
            maximum_order_quantity int(11) DEFAULT NULL,
            production_time_days int(11) DEFAULT NULL,
            shipping_class varchar(100) DEFAULT NULL,
            tax_class varchar(100) DEFAULT 'standard',
            stock_status enum('in_stock','out_of_stock','on_backorder') DEFAULT 'in_stock',
            manage_stock tinyint(1) DEFAULT 0,
            stock_quantity int(11) DEFAULT NULL,
            low_stock_threshold int(11) DEFAULT NULL,
            backorders enum('no','notify','yes') DEFAULT 'no',
            sold_individually tinyint(1) DEFAULT 0,
            purchase_note text DEFAULT NULL,
            menu_order int(11) DEFAULT 0,
            reviews_allowed tinyint(1) DEFAULT 1,
            featured tinyint(1) DEFAULT 0,
            downloadable tinyint(1) DEFAULT 0,
            virtual tinyint(1) DEFAULT 0,
            external_url varchar(255) DEFAULT NULL,
            button_text varchar(100) DEFAULT NULL,
            meta_title varchar(255) DEFAULT NULL,
            meta_description text DEFAULT NULL,
            meta_keywords text DEFAULT NULL,
            social_media_description text DEFAULT NULL,
            status enum('draft','pending_review','approved','rejected','needs_revision') DEFAULT 'draft',
            visibility enum('visible','catalog','search','hidden') DEFAULT 'visible',
            submission_notes text DEFAULT NULL,
            admin_notes text DEFAULT NULL,
            rejection_reason text DEFAULT NULL,
            ai_suggestions longtext DEFAULT NULL,
            quality_score decimal(3,2) DEFAULT NULL,
            seo_score decimal(3,2) DEFAULT NULL,
            completion_percentage int(3) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            submitted_at datetime DEFAULT NULL,
            approved_at datetime DEFAULT NULL,
            approved_by bigint(20) DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY product_slug (product_slug),
            KEY vendor_id (vendor_id),
            KEY status (status),
            KEY category_id (category_id),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
        
        // Product images table
        $table_name = $wpdb->prefix . 'vss_pro_product_images';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            product_id bigint(20) NOT NULL,
            attachment_id bigint(20) NOT NULL,
            image_type enum('gallery','thumbnail','lifestyle','technical','packaging','size_guide','color_swatch') DEFAULT 'gallery',
            sort_order int(11) DEFAULT 0,
            alt_text varchar(255) DEFAULT NULL,
            caption text DEFAULT NULL,
            is_primary tinyint(1) DEFAULT 0,
            optimization_data text DEFAULT NULL,
            uploaded_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY product_id (product_id),
            KEY attachment_id (attachment_id),
            KEY image_type (image_type)
        ) $charset_collate;";
        
        dbDelta( $sql );
        
        // Product variants table
        $table_name = $wpdb->prefix . 'vss_pro_product_variants';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            product_id bigint(20) NOT NULL,
            variant_name varchar(255) NOT NULL,
            attributes text NOT NULL,
            sku varchar(100) DEFAULT NULL,
            price decimal(10,2) DEFAULT NULL,
            sale_price decimal(10,2) DEFAULT NULL,
            stock_quantity int(11) DEFAULT NULL,
            weight decimal(10,2) DEFAULT NULL,
            dimensions text DEFAULT NULL,
            image_id bigint(20) DEFAULT NULL,
            is_default tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY product_id (product_id),
            KEY variant_name (variant_name)
        ) $charset_collate;";
        
        dbDelta( $sql );
        
        // Product analytics table
        $table_name = $wpdb->prefix . 'vss_pro_product_analytics';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            product_id bigint(20) NOT NULL,
            views_count int(11) DEFAULT 0,
            conversion_rate decimal(5,4) DEFAULT 0,
            avg_rating decimal(3,2) DEFAULT 0,
            total_reviews int(11) DEFAULT 0,
            total_sales int(11) DEFAULT 0,
            total_revenue decimal(10,2) DEFAULT 0,
            bounce_rate decimal(5,4) DEFAULT 0,
            time_on_page int(11) DEFAULT 0,
            last_updated datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY product_id (product_id)
        ) $charset_collate;";
        
        dbDelta( $sql );
    }

    /**
     * Enqueue professional uploader assets
     */
    public static function enqueue_assets() {
        if ( self::is_product_upload_page() ) {
            // Core libraries
            wp_enqueue_media();
            wp_enqueue_script( 'jquery-ui-sortable' );
            wp_enqueue_script( 'jquery-ui-autocomplete' );
            
            // Professional uploader styles
            wp_enqueue_style( 
                'vss-pro-uploader', 
                plugins_url( 'assets/css/vss-professional-uploader.css', dirname( __DIR__ ) ), 
                [], 
                '8.0.0' 
            );
            
            // Professional uploader scripts
            wp_enqueue_script( 
                'vss-pro-uploader', 
                plugins_url( 'assets/js/vss-professional-uploader.js', dirname( __DIR__ ) ), 
                [ 'jquery', 'jquery-ui-sortable', 'jquery-ui-autocomplete' ], 
                '8.0.0', 
                true 
            );
            
            // Localize script
            wp_localize_script( 'vss-pro-uploader', 'vssProUploader', [
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'vss_pro_uploader' ),
                'maxFileSize' => wp_max_upload_size(),
                'allowedTypes' => [ 'image/jpeg', 'image/png', 'image/gif', 'image/webp' ],
                'strings' => [
                    'saving' => 'Saving...',
                    'saved' => 'Saved!',
                    'uploading' => 'Uploading...',
                    'uploaded' => 'Upload complete!',
                    'error' => 'An error occurred',
                    'validating' => 'Validating...',
                    'optimizing' => 'Optimizing images...',
                    'generating' => 'Generating suggestions...',
                    'confirm_delete' => 'Are you sure you want to delete this image?',
                    'unsaved_changes' => 'You have unsaved changes. Are you sure you want to leave?'
                ]
            ] );
        }
    }

    /**
     * Render professional product uploader
     */
    public static function render_professional_uploader( $product_id = null ) {
        if ( ! self::is_current_user_vendor() ) {
            return;
        }

        $vendor_id = get_current_user_id();
        $product = null;
        
        if ( $product_id ) {
            $product = self::get_product_data( $product_id, $vendor_id );
            if ( ! $product ) {
                wp_die( 'Product not found or access denied.' );
            }
        }

        $is_editing = ! empty( $product );
        
        ?>
        <div class="vss-pro-uploader-container">
            <!-- Header -->
            <div class="vss-pro-header">
                <div class="vss-pro-header-content">
                    <h1 class="vss-pro-title">
                        <?php echo $is_editing ? 'Edit Product' : 'Add New Product'; ?>
                    </h1>
                    <p class="vss-pro-subtitle">
                        <?php echo $is_editing ? 'Update your product details and images' : 'Create a professional product listing with our advanced tools'; ?>
                    </p>
                </div>
                <div class="vss-pro-header-actions">
                    <button type="button" class="vss-btn vss-btn-outline" id="vss-save-draft">
                        <span class="dashicons dashicons-cloud"></span>
                        Save Draft
                    </button>
                    <button type="button" class="vss-btn vss-btn-outline" id="vss-preview-product">
                        <span class="dashicons dashicons-visibility"></span>
                        Preview
                    </button>
                    <button type="button" class="vss-btn vss-btn-secondary" id="vss-ai-assist">
                        <span class="dashicons dashicons-superhero"></span>
                        AI Assist
                    </button>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="vss-pro-progress-container">
                <div class="vss-pro-progress-bar">
                    <div class="vss-pro-progress-fill" style="width: <?php echo $product['completion_percentage'] ?? 0; ?>%"></div>
                </div>
                <span class="vss-pro-progress-text"><?php echo $product['completion_percentage'] ?? 0; ?>% Complete</span>
            </div>

            <!-- Main Form -->
            <form id="vss-pro-upload-form" class="vss-pro-form" enctype="multipart/form-data">
                <?php wp_nonce_field( 'vss_pro_upload_product', 'vss_pro_nonce' ); ?>
                <input type="hidden" name="product_id" value="<?php echo esc_attr( $product_id ); ?>">
                <input type="hidden" name="vendor_id" value="<?php echo esc_attr( $vendor_id ); ?>">

                <div class="vss-pro-form-grid">
                    <!-- Left Column - Main Content -->
                    <div class="vss-pro-main-content">
                        
                        <!-- Basic Information Card -->
                        <div class="vss-pro-card">
                            <div class="vss-pro-card-header">
                                <h3 class="vss-pro-card-title">
                                    <span class="dashicons dashicons-info-outline"></span>
                                    Basic Information
                                </h3>
                                <div class="vss-pro-card-status">
                                    <span class="vss-status-indicator" data-section="basic"></span>
                                </div>
                            </div>
                            <div class="vss-pro-card-body">
                                <div class="vss-form-group">
                                    <label for="product_name" class="vss-label">
                                        Product Name *
                                        <span class="vss-tooltip" data-tip="Enter a clear, descriptive name for your product">?</span>
                                    </label>
                                    <input type="text" 
                                           id="product_name" 
                                           name="product_name" 
                                           class="vss-input vss-input-lg" 
                                           value="<?php echo esc_attr( $product['product_name'] ?? '' ); ?>"
                                           placeholder="e.g., Premium Wireless Headphones"
                                           required>
                                    <div class="vss-field-feedback"></div>
                                </div>

                                <div class="vss-form-group">
                                    <label for="product_slug" class="vss-label">
                                        Product URL Slug
                                        <span class="vss-tooltip" data-tip="This will be used in the product URL. Leave empty to auto-generate">?</span>
                                    </label>
                                    <div class="vss-input-group">
                                        <span class="vss-input-addon"><?php echo home_url( '/product/' ); ?></span>
                                        <input type="text" 
                                               id="product_slug" 
                                               name="product_slug" 
                                               class="vss-input"
                                               value="<?php echo esc_attr( $product['product_slug'] ?? '' ); ?>"
                                               placeholder="premium-wireless-headphones">
                                    </div>
                                </div>

                                <div class="vss-form-row">
                                    <div class="vss-form-group">
                                        <label for="category_id" class="vss-label">Category *</label>
                                        <select id="category_id" name="category_id" class="vss-select" required>
                                            <option value="">Select Category</option>
                                            <?php self::render_category_options( $product['category_id'] ?? '' ); ?>
                                        </select>
                                    </div>
                                    <div class="vss-form-group">
                                        <label for="subcategory_id" class="vss-label">Subcategory</label>
                                        <select id="subcategory_id" name="subcategory_id" class="vss-select">
                                            <option value="">Select Subcategory</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="vss-form-group">
                                    <label for="short_description" class="vss-label">
                                        Short Description *
                                        <span class="vss-tooltip" data-tip="Brief summary that appears in search results and product listings">?</span>
                                    </label>
                                    <textarea id="short_description" 
                                              name="short_description" 
                                              class="vss-textarea" 
                                              rows="3"
                                              maxlength="160"
                                              placeholder="Brief description of your product features and benefits..."
                                              required><?php echo esc_textarea( $product['short_description'] ?? '' ); ?></textarea>
                                    <div class="vss-char-counter">
                                        <span class="current">0</span>/<span class="max">160</span> characters
                                    </div>
                                </div>

                                <div class="vss-form-group">
                                    <label for="tags" class="vss-label">
                                        Product Tags
                                        <span class="vss-tooltip" data-tip="Comma-separated tags that help customers find your product">?</span>
                                    </label>
                                    <input type="text" 
                                           id="tags" 
                                           name="tags" 
                                           class="vss-input vss-tag-input"
                                           value="<?php echo esc_attr( $product['tags'] ?? '' ); ?>"
                                           placeholder="wireless, bluetooth, headphones, premium"
                                           data-suggestions="true">
                                    <div class="vss-tag-suggestions"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Product Details Card -->
                        <div class="vss-pro-card">
                            <div class="vss-pro-card-header">
                                <h3 class="vss-pro-card-title">
                                    <span class="dashicons dashicons-edit-large"></span>
                                    Product Details
                                </h3>
                                <div class="vss-pro-card-status">
                                    <span class="vss-status-indicator" data-section="details"></span>
                                </div>
                            </div>
                            <div class="vss-pro-card-body">
                                <div class="vss-form-group">
                                    <label for="full_description" class="vss-label">
                                        Full Description *
                                    </label>
                                    <div class="vss-editor-container">
                                        <?php
                                        wp_editor( 
                                            $product['full_description'] ?? '',
                                            'full_description',
                                            [
                                                'textarea_name' => 'full_description',
                                                'media_buttons' => true,
                                                'textarea_rows' => 8,
                                                'teeny' => false,
                                                'tinymce' => [
                                                    'toolbar1' => 'bold,italic,underline,strikethrough,|,bullist,numlist,|,link,unlink,|,undo,redo',
                                                    'toolbar2' => 'formatselect,|,forecolor,backcolor,|,alignleft,aligncenter,alignright,alignjustify'
                                                ]
                                            ]
                                        );
                                        ?>
                                    </div>
                                </div>

                                <div class="vss-form-row">
                                    <div class="vss-form-group">
                                        <label for="brand" class="vss-label">Brand</label>
                                        <input type="text" 
                                               id="brand" 
                                               name="brand" 
                                               class="vss-input"
                                               value="<?php echo esc_attr( $product['brand'] ?? '' ); ?>"
                                               placeholder="e.g., Sony, Apple, Nike">
                                    </div>
                                    <div class="vss-form-group">
                                        <label for="manufacturer" class="vss-label">Manufacturer</label>
                                        <input type="text" 
                                               id="manufacturer" 
                                               name="manufacturer" 
                                               class="vss-input"
                                               value="<?php echo esc_attr( $product['manufacturer'] ?? '' ); ?>">
                                    </div>
                                </div>

                                <div class="vss-form-row">
                                    <div class="vss-form-group">
                                        <label for="model_number" class="vss-label">Model Number</label>
                                        <input type="text" 
                                               id="model_number" 
                                               name="model_number" 
                                               class="vss-input"
                                               value="<?php echo esc_attr( $product['model_number'] ?? '' ); ?>">
                                    </div>
                                    <div class="vss-form-group">
                                        <label for="material" class="vss-label">Material</label>
                                        <input type="text" 
                                               id="material" 
                                               name="material" 
                                               class="vss-input"
                                               value="<?php echo esc_attr( $product['material'] ?? '' ); ?>"
                                               placeholder="e.g., Aluminum, Cotton, Leather">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Images & Media Card -->
                        <div class="vss-pro-card">
                            <div class="vss-pro-card-header">
                                <h3 class="vss-pro-card-title">
                                    <span class="dashicons dashicons-format-gallery"></span>
                                    Product Images
                                </h3>
                                <div class="vss-pro-card-actions">
                                    <button type="button" class="vss-btn vss-btn-sm vss-btn-secondary" id="vss-optimize-images">
                                        <span class="dashicons dashicons-performance"></span>
                                        Optimize All
                                    </button>
                                </div>
                            </div>
                            <div class="vss-pro-card-body">
                                <!-- Image Upload Areas -->
                                <div class="vss-image-upload-sections">
                                    
                                    <!-- Primary Image -->
                                    <div class="vss-image-section">
                                        <h4 class="vss-section-title">Primary Product Image *</h4>
                                        <p class="vss-section-description">This will be the main image customers see first</p>
                                        <div class="vss-dropzone vss-dropzone-primary" data-type="thumbnail" data-max="1">
                                            <div class="vss-dropzone-content">
                                                <span class="dashicons dashicons-cloud-upload"></span>
                                                <p>Drop your primary image here or <span class="vss-upload-link">click to browse</span></p>
                                                <small>Recommended: 1200x1200px, JPG or PNG, max 5MB</small>
                                            </div>
                                            <input type="file" class="vss-file-input" accept="image/*" multiple>
                                        </div>
                                        <div class="vss-uploaded-images" data-type="thumbnail"></div>
                                    </div>

                                    <!-- Gallery Images -->
                                    <div class="vss-image-section">
                                        <h4 class="vss-section-title">Product Gallery</h4>
                                        <p class="vss-section-description">Additional images showing different angles and details</p>
                                        <div class="vss-dropzone vss-dropzone-gallery" data-type="gallery" data-max="10">
                                            <div class="vss-dropzone-content">
                                                <span class="dashicons dashicons-images-alt2"></span>
                                                <p>Drop gallery images here or <span class="vss-upload-link">click to browse</span></p>
                                                <small>Up to 10 images, 1200x1200px recommended</small>
                                            </div>
                                            <input type="file" class="vss-file-input" accept="image/*" multiple>
                                        </div>
                                        <div class="vss-uploaded-images vss-sortable" data-type="gallery"></div>
                                    </div>

                                    <!-- Lifestyle Images -->
                                    <div class="vss-image-section">
                                        <h4 class="vss-section-title">Lifestyle Images</h4>
                                        <p class="vss-section-description">Show your product in use or in context</p>
                                        <div class="vss-dropzone" data-type="lifestyle" data-max="5">
                                            <div class="vss-dropzone-content">
                                                <span class="dashicons dashicons-camera-alt"></span>
                                                <p>Drop lifestyle images here or <span class="vss-upload-link">click to browse</span></p>
                                                <small>Optional: Show product in real-world settings</small>
                                            </div>
                                            <input type="file" class="vss-file-input" accept="image/*" multiple>
                                        </div>
                                        <div class="vss-uploaded-images vss-sortable" data-type="lifestyle"></div>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Right Column - Sidebar -->
                    <div class="vss-pro-sidebar">
                        
                        <!-- Quick Actions -->
                        <div class="vss-pro-card vss-pro-card-compact">
                            <div class="vss-pro-card-header">
                                <h3 class="vss-pro-card-title">Quick Actions</h3>
                            </div>
                            <div class="vss-pro-card-body">
                                <button type="button" class="vss-btn vss-btn-block vss-btn-primary" id="vss-submit-product">
                                    <span class="dashicons dashicons-yes-alt"></span>
                                    Submit for Review
                                </button>
                                <button type="button" class="vss-btn vss-btn-block vss-btn-outline" id="vss-duplicate-product">
                                    <span class="dashicons dashicons-admin-page"></span>
                                    Duplicate Product
                                </button>
                                <?php if ( $is_editing ): ?>
                                <button type="button" class="vss-btn vss-btn-block vss-btn-outline vss-btn-danger" id="vss-delete-product">
                                    <span class="dashicons dashicons-trash"></span>
                                    Delete Product
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Product Information -->
                        <div class="vss-pro-card vss-pro-card-compact">
                            <div class="vss-pro-card-header">
                                <h3 class="vss-pro-card-title">
                                    <span class="dashicons dashicons-archive"></span>
                                    Inventory & Shipping
                                </h3>
                            </div>
                            <div class="vss-pro-card-body">
                                <div class="vss-form-group">
                                    <label for="sku" class="vss-label">SKU</label>
                                    <div class="vss-input-group">
                                        <input type="text" 
                                               id="sku" 
                                               name="sku" 
                                               class="vss-input"
                                               value="<?php echo esc_attr( $product['sku'] ?? '' ); ?>">
                                        <button type="button" class="vss-btn vss-btn-sm" id="vss-generate-sku">Generate</button>
                                    </div>
                                </div>

                                <div class="vss-form-group">
                                    <label for="barcode" class="vss-label">Barcode/UPC</label>
                                    <input type="text" 
                                           id="barcode" 
                                           name="barcode" 
                                           class="vss-input"
                                           value="<?php echo esc_attr( $product['barcode'] ?? '' ); ?>">
                                </div>

                                <div class="vss-form-group">
                                    <label class="vss-label">Dimensions (cm)</label>
                                    <div class="vss-dimensions-grid">
                                        <input type="number" 
                                               name="dimensions_length" 
                                               placeholder="Length"
                                               class="vss-input vss-input-sm"
                                               value="<?php echo esc_attr( $product['dimensions_length'] ?? '' ); ?>"
                                               step="0.1">
                                        <input type="number" 
                                               name="dimensions_width" 
                                               placeholder="Width"
                                               class="vss-input vss-input-sm"
                                               value="<?php echo esc_attr( $product['dimensions_width'] ?? '' ); ?>"
                                               step="0.1">
                                        <input type="number" 
                                               name="dimensions_height" 
                                               placeholder="Height"
                                               class="vss-input vss-input-sm"
                                               value="<?php echo esc_attr( $product['dimensions_height'] ?? '' ); ?>"
                                               step="0.1">
                                    </div>
                                </div>

                                <div class="vss-form-group">
                                    <label for="weight" class="vss-label">Weight (kg)</label>
                                    <input type="number" 
                                           id="weight" 
                                           name="weight" 
                                           class="vss-input"
                                           value="<?php echo esc_attr( $product['weight'] ?? '' ); ?>"
                                           step="0.01">
                                </div>

                                <div class="vss-form-group">
                                    <label for="production_time_days" class="vss-label">Production Time (days)</label>
                                    <select id="production_time_days" name="production_time_days" class="vss-select">
                                        <option value="">Select timeframe</option>
                                        <option value="1" <?php selected( $product['production_time_days'] ?? '', '1' ); ?>>1-2 days</option>
                                        <option value="3" <?php selected( $product['production_time_days'] ?? '', '3' ); ?>>3-5 days</option>
                                        <option value="7" <?php selected( $product['production_time_days'] ?? '', '7' ); ?>>1 week</option>
                                        <option value="14" <?php selected( $product['production_time_days'] ?? '', '14' ); ?>>2 weeks</option>
                                        <option value="30" <?php selected( $product['production_time_days'] ?? '', '30' ); ?>>1 month</option>
                                        <option value="60" <?php selected( $product['production_time_days'] ?? '', '60' ); ?>>2 months</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Pricing -->
                        <div class="vss-pro-card vss-pro-card-compact">
                            <div class="vss-pro-card-header">
                                <h3 class="vss-pro-card-title">
                                    <span class="dashicons dashicons-money-alt"></span>
                                    Pricing
                                </h3>
                            </div>
                            <div class="vss-pro-card-body">
                                <div class="vss-form-group">
                                    <label for="production_cost" class="vss-label">Production Cost ($)</label>
                                    <input type="number" 
                                           id="production_cost" 
                                           name="production_cost" 
                                           class="vss-input vss-price-input"
                                           value="<?php echo esc_attr( $product['production_cost'] ?? '' ); ?>"
                                           step="0.01"
                                           min="0">
                                </div>

                                <div class="vss-form-group">
                                    <label for="suggested_price" class="vss-label">Suggested Selling Price ($)</label>
                                    <input type="number" 
                                           id="suggested_price" 
                                           name="suggested_price" 
                                           class="vss-input vss-price-input"
                                           value="<?php echo esc_attr( $product['suggested_price'] ?? '' ); ?>"
                                           step="0.01"
                                           min="0">
                                </div>

                                <div class="vss-pricing-calculator">
                                    <div class="vss-calc-row">
                                        <span>Production Cost:</span>
                                        <span class="vss-cost-display">$0.00</span>
                                    </div>
                                    <div class="vss-calc-row">
                                        <span>Platform Fee (15%):</span>
                                        <span class="vss-fee-display">$0.00</span>
                                    </div>
                                    <div class="vss-calc-row">
                                        <span>Profit Margin:</span>
                                        <span class="vss-profit-display">$0.00</span>
                                    </div>
                                    <div class="vss-calc-row vss-calc-total">
                                        <span>Recommended Price:</span>
                                        <span class="vss-recommended-display">$0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quality Score -->
                        <div class="vss-pro-card vss-pro-card-compact">
                            <div class="vss-pro-card-header">
                                <h3 class="vss-pro-card-title">
                                    <span class="dashicons dashicons-star-filled"></span>
                                    Quality Score
                                </h3>
                            </div>
                            <div class="vss-pro-card-body">
                                <div class="vss-quality-score">
                                    <div class="vss-score-circle">
                                        <svg class="vss-progress-ring" width="80" height="80">
                                            <circle class="vss-progress-ring-circle" cx="40" cy="40" r="35"></circle>
                                        </svg>
                                        <div class="vss-score-text">
                                            <span class="vss-score-number"><?php echo number_format( $product['quality_score'] ?? 0, 1 ); ?></span>
                                            <span class="vss-score-label">/ 10</span>
                                        </div>
                                    </div>
                                    <div class="vss-score-breakdown">
                                        <div class="vss-score-item">
                                            <span class="vss-score-category">Images</span>
                                            <div class="vss-score-bar">
                                                <div class="vss-score-fill" style="width: 80%"></div>
                                            </div>
                                        </div>
                                        <div class="vss-score-item">
                                            <span class="vss-score-category">Description</span>
                                            <div class="vss-score-bar">
                                                <div class="vss-score-fill" style="width: 65%"></div>
                                            </div>
                                        </div>
                                        <div class="vss-score-item">
                                            <span class="vss-score-category">Pricing</span>
                                            <div class="vss-score-bar">
                                                <div class="vss-score-fill" style="width: 90%"></div>
                                            </div>
                                        </div>
                                        <div class="vss-score-item">
                                            <span class="vss-score-category">SEO</span>
                                            <div class="vss-score-bar">
                                                <div class="vss-score-fill" style="width: 45%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </form>

            <!-- AI Assistant Modal -->
            <div id="vss-ai-modal" class="vss-modal">
                <div class="vss-modal-content">
                    <div class="vss-modal-header">
                        <h3>AI Product Assistant</h3>
                        <button class="vss-modal-close">&times;</button>
                    </div>
                    <div class="vss-modal-body">
                        <div class="vss-ai-suggestions">
                            <div class="vss-loading">Generating intelligent suggestions...</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Image Editor Modal -->
            <div id="vss-image-editor-modal" class="vss-modal">
                <div class="vss-modal-content vss-modal-large">
                    <div class="vss-modal-header">
                        <h3>Image Editor</h3>
                        <button class="vss-modal-close">&times;</button>
                    </div>
                    <div class="vss-modal-body">
                        <div class="vss-image-editor">
                            <canvas id="vss-image-canvas"></canvas>
                            <div class="vss-editor-controls">
                                <button class="vss-btn" data-action="crop">Crop</button>
                                <button class="vss-btn" data-action="resize">Resize</button>
                                <button class="vss-btn" data-action="enhance">Enhance</button>
                                <button class="vss-btn" data-action="watermark">Watermark</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <script>
        jQuery(document).ready(function($) {
            window.vssProUploader = new VSSProfessionalUploader();
            window.vssProUploader.init();
        });
        </script>
        <?php
    }

    /**
     * Check if current page is product upload page
     */
    private static function is_product_upload_page() {
        return isset( $_GET['vss_action'] ) && $_GET['vss_action'] === 'upload_products';
    }

    /**
     * Check if current user is vendor
     */
    private static function is_current_user_vendor() {
        return current_user_can( 'vss_vendor' ) || current_user_can( 'manage_woocommerce' );
    }

    /**
     * Get product data
     */
    private static function get_product_data( $product_id, $vendor_id ) {
        global $wpdb;
        
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}vss_pro_product_uploads WHERE id = %d AND vendor_id = %d",
            $product_id, $vendor_id
        ), ARRAY_A );
    }

    /**
     * Render category options
     */
    private static function render_category_options( $selected = '' ) {
        $categories = get_terms( [
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
        ] );

        foreach ( $categories as $category ) {
            printf( 
                '<option value="%d" %s>%s</option>',
                $category->term_id,
                selected( $selected, $category->term_id, false ),
                esc_html( $category->name )
            );
        }
    }

    /**
     * Handle product upload
     */
    public static function handle_product_upload() {
        check_ajax_referer( 'vss_pro_uploader', 'vss_pro_nonce' );
        
        if ( ! self::is_current_user_vendor() ) {
            wp_send_json_error( 'Insufficient permissions' );
        }

        $product_data = self::sanitize_product_data( $_POST );
        $product_id = self::save_product( $product_data );

        if ( $product_id ) {
            // Trigger submission hook
            do_action( 'vss_product_submitted', $product_id, get_current_user_id() );
            
            wp_send_json_success( [
                'message' => 'Product submitted successfully!',
                'product_id' => $product_id,
                'redirect_url' => home_url( '?vss_action=upload_products&product_action=view&product_id=' . $product_id )
            ] );
        } else {
            wp_send_json_error( 'Failed to save product' );
        }
    }

    /**
     * Save product draft
     */
    public static function save_product_draft() {
        check_ajax_referer( 'vss_pro_uploader', 'vss_pro_nonce' );
        
        if ( ! self::is_current_user_vendor() ) {
            wp_send_json_error( 'Insufficient permissions' );
        }

        $product_data = self::sanitize_product_data( $_POST );
        $product_data['status'] = 'draft';
        
        $product_id = self::save_product( $product_data );

        if ( $product_id ) {
            wp_send_json_success( [
                'message' => 'Draft saved successfully!',
                'product_id' => $product_id
            ] );
        } else {
            wp_send_json_error( 'Failed to save draft' );
        }
    }

    /**
     * Sanitize product data
     */
    private static function sanitize_product_data( $data ) {
        return [
            'id' => intval( $data['product_id'] ?? 0 ),
            'vendor_id' => intval( $data['vendor_id'] ),
            'product_name' => sanitize_text_field( $data['product_name'] ),
            'product_slug' => sanitize_title( $data['product_slug'] ?: $data['product_name'] ),
            'short_description' => sanitize_textarea_field( $data['short_description'] ),
            'full_description' => wp_kses_post( $data['full_description'] ),
            'category_id' => intval( $data['category_id'] ),
            'subcategory_id' => intval( $data['subcategory_id'] ) ?: null,
            'tags' => sanitize_text_field( $data['tags'] ),
            'sku' => sanitize_text_field( $data['sku'] ),
            'barcode' => sanitize_text_field( $data['barcode'] ),
            'brand' => sanitize_text_field( $data['brand'] ),
            'manufacturer' => sanitize_text_field( $data['manufacturer'] ),
            'model_number' => sanitize_text_field( $data['model_number'] ),
            'dimensions_length' => floatval( $data['dimensions_length'] ) ?: null,
            'dimensions_width' => floatval( $data['dimensions_width'] ) ?: null,
            'dimensions_height' => floatval( $data['dimensions_height'] ) ?: null,
            'weight' => floatval( $data['weight'] ) ?: null,
            'material' => sanitize_text_field( $data['material'] ),
            'production_cost' => floatval( $data['production_cost'] ) ?: null,
            'suggested_price' => floatval( $data['suggested_price'] ) ?: null,
            'production_time_days' => intval( $data['production_time_days'] ) ?: null,
            'status' => sanitize_key( $data['status'] ?? 'pending_review' )
        ];
    }

    /**
     * Save product to database
     */
    private static function save_product( $data ) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'vss_pro_product_uploads';
        
        if ( $data['id'] ) {
            // Update existing product
            $data['updated_at'] = current_time( 'mysql' );
            if ( $data['status'] === 'pending_review' ) {
                $data['submitted_at'] = current_time( 'mysql' );
            }
            
            $updated = $wpdb->update( $table, $data, [ 'id' => $data['id'] ] );
            return $updated !== false ? $data['id'] : false;
        } else {
            // Create new product
            unset( $data['id'] );
            $data['created_at'] = current_time( 'mysql' );
            if ( $data['status'] === 'pending_review' ) {
                $data['submitted_at'] = current_time( 'mysql' );
            }
            
            $inserted = $wpdb->insert( $table, $data );
            return $inserted ? $wpdb->insert_id : false;
        }
    }
}

// Initialize the class
add_action( 'plugins_loaded', [ 'VSS_Professional_Product_Uploader', 'init' ] );