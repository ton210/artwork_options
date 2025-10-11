<?php
/**
 * VSS Product Uploads Module
 * 
 * Product upload and management functionality for vendors
 * 
 * @package VendorOrderManager
 * @subpackage Modules
 * @since 7.0.2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Trait for Product Upload functionality
 */
trait VSS_Product_Uploads {

    /**
     * Initialize product uploads functionality
     */
    public static function init_product_uploads() {
        // Create database tables
        self::create_product_tables();
        
        // Register AJAX handlers
        add_action( 'wp_ajax_vss_upload_product', [ self::class, 'handle_product_upload' ] );
        add_action( 'wp_ajax_vss_upload_product_csv', [ self::class, 'handle_csv_upload' ] );
        add_action( 'wp_ajax_vss_download_sample_csv', [ self::class, 'download_sample_csv' ] );
        add_action( 'wp_ajax_vss_translate_product', [ self::class, 'translate_product_data' ] );
        add_action( 'wp_ajax_vss_save_product_draft', [ self::class, 'save_product_draft' ] );
        
        // Admin AJAX handlers for product approval
        add_action( 'wp_ajax_vss_approve_product', [ self::class, 'approve_product' ] );
        add_action( 'wp_ajax_vss_reject_product', [ self::class, 'reject_product' ] );
        add_action( 'wp_ajax_vss_update_product_costs', [ self::class, 'update_product_costs' ] );
        
        // Slack notification hooks
        add_action( 'vss_product_submitted', [ self::class, 'send_slack_notification' ] );
        add_action( 'vss_product_approved', [ self::class, 'send_slack_approval_notification' ] );
        add_action( 'vss_product_rejected', [ self::class, 'send_slack_rejection_notification' ] );
    }

    /**
     * Create database tables for product uploads
     */
    public static function create_product_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Main product uploads table
        $table_name = $wpdb->prefix . 'vss_product_uploads';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            vendor_id bigint(20) NOT NULL,
            product_name_zh text NOT NULL,
            product_name_en text DEFAULT NULL,
            description_zh longtext DEFAULT NULL,
            description_en longtext DEFAULT NULL,
            category varchar(255) DEFAULT NULL,
            subcategory varchar(255) DEFAULT NULL,
            sku varchar(100) DEFAULT NULL,
            product_weight decimal(10,2) DEFAULT NULL,
            production_time int(11) DEFAULT NULL,
            customization_options longtext DEFAULT NULL,
            target_stores longtext DEFAULT NULL,
            design_tool_files longtext DEFAULT NULL,
            design_tool_template longtext DEFAULT NULL,
            finished_production_pictures longtext DEFAULT NULL,
            status enum('draft','pending','approved','rejected') DEFAULT 'draft',
            submission_date datetime DEFAULT CURRENT_TIMESTAMP,
            last_modified datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            approval_date datetime DEFAULT NULL,
            approved_by bigint(20) DEFAULT NULL,
            rejection_reason text DEFAULT NULL,
            admin_notes text DEFAULT NULL,
            our_cost decimal(10,2) DEFAULT NULL,
            markup_percentage decimal(5,2) DEFAULT NULL,
            selling_price decimal(10,2) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY vendor_id (vendor_id),
            KEY status (status),
            KEY submission_date (submission_date)
        ) $charset_collate;";
        
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
        
        // Product variants table
        $table_name = $wpdb->prefix . 'vss_product_variants';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            product_id bigint(20) NOT NULL,
            variant_name_zh varchar(255) NOT NULL,
            variant_name_en varchar(255) DEFAULT NULL,
            variant_value_zh varchar(255) NOT NULL,
            variant_value_en varchar(255) DEFAULT NULL,
            price_adjustment decimal(10,2) DEFAULT 0.00,
            sku_suffix varchar(50) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY product_id (product_id),
            FOREIGN KEY (product_id) REFERENCES {$wpdb->prefix}vss_product_uploads(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        dbDelta( $sql );
        
        // Product images table
        $table_name = $wpdb->prefix . 'vss_product_images';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            product_id bigint(20) NOT NULL,
            image_type enum('product','finished','customization','reference') NOT NULL,
            image_url varchar(500) NOT NULL,
            image_caption_zh text DEFAULT NULL,
            image_caption_en text DEFAULT NULL,
            sort_order int(11) DEFAULT 0,
            is_primary tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY product_id (product_id),
            KEY image_type (image_type),
            FOREIGN KEY (product_id) REFERENCES {$wpdb->prefix}vss_product_uploads(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        dbDelta( $sql );
        
        // Tiered pricing table
        $table_name = $wpdb->prefix . 'vss_product_pricing';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            product_id bigint(20) NOT NULL,
            min_quantity int(11) NOT NULL,
            max_quantity int(11) DEFAULT NULL,
            unit_price decimal(10,2) NOT NULL,
            currency varchar(10) DEFAULT 'USD',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY product_id (product_id),
            KEY quantity_range (min_quantity, max_quantity),
            FOREIGN KEY (product_id) REFERENCES {$wpdb->prefix}vss_product_uploads(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        dbDelta( $sql );
        
        // Product approval history
        $table_name = $wpdb->prefix . 'vss_product_approval_history';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            product_id bigint(20) NOT NULL,
            action enum('submitted','approved','rejected','updated') NOT NULL,
            user_id bigint(20) NOT NULL,
            notes text DEFAULT NULL,
            previous_status varchar(50) DEFAULT NULL,
            new_status varchar(50) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY product_id (product_id),
            KEY user_id (user_id),
            KEY action (action),
            FOREIGN KEY (product_id) REFERENCES {$wpdb->prefix}vss_product_uploads(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        dbDelta( $sql );
    }
    
    /**
     * Render product upload interface
     */
    public static function render_product_uploads() {
        if ( ! self::is_current_user_vendor() ) {
            return;
        }
        
        $vendor_id = get_current_user_id();
        $action = isset( $_GET['product_action'] ) ? sanitize_key( $_GET['product_action'] ) : 'list';
        $product_id = isset( $_GET['product_id'] ) ? intval( $_GET['product_id'] ) : 0;
        
        ?>
        <div class="vss-product-uploads-wrapper">
            <?php
            switch ( $action ) {
                case 'new':
                    self::render_product_upload_form();
                    break;
                    
                case 'edit':
                    if ( $product_id ) {
                        self::render_product_edit_form( $product_id );
                    } else {
                        self::render_products_list();
                    }
                    break;
                    
                case 'view':
                    if ( $product_id ) {
                        self::render_product_details( $product_id );
                    } else {
                        self::render_products_list();
                    }
                    break;
                    
                case 'list':
                default:
                    self::render_products_list();
                    break;
            }
            ?>
        </div>
        <?php
    }
    
    /**
     * Render products list
     */
    private static function render_products_list() {
        $vendor_id = get_current_user_id();
        
        // Get vendor's products
        global $wpdb;
        $table_name = $wpdb->prefix . 'vss_product_uploads';
        
        $products = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM $table_name WHERE vendor_id = %d ORDER BY created_at DESC",
            $vendor_id
        ) );
        
        ?>
        <div class="vss-products-header">
            <div class="vss-header-left">
                <h1><?php esc_html_e( 'Upload Products', 'vss' ); ?></h1>
                <p><?php esc_html_e( 'Manage your product catalog and submit products for approval', 'vss' ); ?></p>
            </div>
            <div class="vss-header-right">
                <a href="<?php echo esc_url( add_query_arg( 'product_action', 'new', get_permalink() ) ); ?>" 
                   class="button button-primary vss-add-product-btn">
                    <span class="dashicons dashicons-plus-alt"></span>
                    <?php esc_html_e( 'Add New Product', 'vss' ); ?>
                </a>
            </div>
        </div>
        
        <div class="vss-products-toolbar">
            <div class="vss-toolbar-left">
                <div class="vss-bulk-actions">
                    <select name="bulk_action" id="bulk-action-selector-top">
                        <option value="-1"><?php esc_html_e( 'Bulk Actions', 'vss' ); ?></option>
                        <option value="submit"><?php esc_html_e( 'Submit for Approval', 'vss' ); ?></option>
                        <option value="delete"><?php esc_html_e( 'Delete', 'vss' ); ?></option>
                    </select>
                    <button type="button" class="button vss-apply-bulk-action">
                        <?php esc_html_e( 'Apply', 'vss' ); ?>
                    </button>
                </div>
            </div>
            <div class="vss-toolbar-right">
                <div class="vss-csv-actions">
                    <button type="button" class="button vss-download-sample">
                        <span class="dashicons dashicons-download"></span>
                        <?php esc_html_e( 'Download Sample CSV', 'vss' ); ?>
                    </button>
                    <button type="button" class="button vss-upload-csv">
                        <span class="dashicons dashicons-upload"></span>
                        <?php esc_html_e( 'Upload CSV', 'vss' ); ?>
                    </button>
                </div>
            </div>
        </div>
        
        <?php if ( ! empty( $products ) ) : ?>
            <div class="vss-products-table-wrapper">
                <table class="vss-products-table">
                    <thead>
                        <tr>
                            <th class="check-column">
                                <input type="checkbox" id="cb-select-all">
                            </th>
                            <th><?php esc_html_e( 'Image', 'vss' ); ?></th>
                            <th><?php esc_html_e( 'Product Name', 'vss' ); ?></th>
                            <th><?php esc_html_e( 'Category', 'vss' ); ?></th>
                            <th><?php esc_html_e( 'Status', 'vss' ); ?></th>
                            <th><?php esc_html_e( 'Submitted', 'vss' ); ?></th>
                            <th><?php esc_html_e( 'Actions', 'vss' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $products as $product ) : 
                            $primary_image = self::get_primary_product_image( $product->id );
                        ?>
                            <tr data-product-id="<?php echo esc_attr( $product->id ); ?>">
                                <td class="check-column">
                                    <input type="checkbox" name="product[]" value="<?php echo esc_attr( $product->id ); ?>">
                                </td>
                                <td class="product-image">
                                    <?php if ( $primary_image ) : ?>
                                        <img src="<?php echo esc_url( $primary_image ); ?>" alt="" class="product-thumb">
                                    <?php else : ?>
                                        <div class="product-no-image">
                                            <span class="dashicons dashicons-format-image"></span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="product-name">
                                    <strong><?php echo esc_html( $product->product_name_zh ); ?></strong>
                                    <?php if ( $product->product_name_en ) : ?>
                                        <br><small><?php echo esc_html( $product->product_name_en ); ?></small>
                                    <?php endif; ?>
                                    <?php if ( $product->sku ) : ?>
                                        <br><code><?php echo esc_html( $product->sku ); ?></code>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html( $product->category ?: '—' ); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo esc_attr( $product->status ); ?>">
                                        <?php echo esc_html( ucfirst( $product->status ) ); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ( $product->submission_date && $product->submission_date !== '0000-00-00 00:00:00' ) : ?>
                                        <?php echo esc_html( date_i18n( 'M j, Y', strtotime( $product->submission_date ) ) ); ?>
                                    <?php else : ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td class="actions">
                                    <div class="action-buttons">
                                        <a href="<?php echo esc_url( add_query_arg( [ 'product_action' => 'view', 'product_id' => $product->id ], get_permalink() ) ); ?>" 
                                           class="button button-small" title="<?php esc_attr_e( 'View Details', 'vss' ); ?>">
                                            <span class="dashicons dashicons-visibility"></span>
                                        </a>
                                        <?php if ( in_array( $product->status, [ 'draft', 'rejected' ] ) ) : ?>
                                            <a href="<?php echo esc_url( add_query_arg( [ 'product_action' => 'edit', 'product_id' => $product->id ], get_permalink() ) ); ?>" 
                                               class="button button-small" title="<?php esc_attr_e( 'Edit', 'vss' ); ?>">
                                                <span class="dashicons dashicons-edit"></span>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ( $product->status === 'draft' ) : ?>
                                            <button class="button button-small vss-submit-product" 
                                                    data-product-id="<?php echo esc_attr( $product->id ); ?>"
                                                    title="<?php esc_attr_e( 'Submit for Approval', 'vss' ); ?>">
                                                <span class="dashicons dashicons-yes"></span>
                                            </button>
                                        <?php endif; ?>
                                        <button class="button button-small button-link-delete vss-delete-product" 
                                                data-product-id="<?php echo esc_attr( $product->id ); ?>"
                                                title="<?php esc_attr_e( 'Delete', 'vss' ); ?>">
                                            <span class="dashicons dashicons-trash"></span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else : ?>
            <div class="vss-empty-state">
                <div class="vss-empty-state-icon">
                    <span class="dashicons dashicons-products"></span>
                </div>
                <h3><?php esc_html_e( 'No products yet', 'vss' ); ?></h3>
                <p><?php esc_html_e( 'Start building your product catalog by adding your first product.', 'vss' ); ?></p>
                <a href="<?php echo esc_url( add_query_arg( 'product_action', 'new', get_permalink() ) ); ?>" 
                   class="button button-primary">
                    <?php esc_html_e( 'Add Your First Product', 'vss' ); ?>
                </a>
            </div>
        <?php endif; ?>
        
        <!-- CSV Upload Modal -->
        <div id="vss-csv-upload-modal" class="vss-modal" style="display: none;">
            <div class="vss-modal-content">
                <div class="vss-modal-header">
                    <h3><?php esc_html_e( 'Upload Products CSV', 'vss' ); ?></h3>
                    <button type="button" class="vss-modal-close">&times;</button>
                </div>
                <div class="vss-modal-body">
                    <form id="vss-csv-upload-form" enctype="multipart/form-data">
                        <p><?php esc_html_e( 'Upload a CSV file containing your product data. Make sure to follow the sample format.', 'vss' ); ?></p>
                        <div class="vss-file-upload">
                            <input type="file" id="csv-file" name="csv_file" accept=".csv" required>
                            <label for="csv-file">
                                <span class="dashicons dashicons-upload"></span>
                                <?php esc_html_e( 'Choose CSV File', 'vss' ); ?>
                            </label>
                        </div>
                        <div class="vss-upload-options">
                            <label>
                                <input type="checkbox" name="skip_duplicates" value="1" checked>
                                <?php esc_html_e( 'Skip duplicate SKUs', 'vss' ); ?>
                            </label>
                        </div>
                    </form>
                </div>
                <div class="vss-modal-footer">
                    <button type="button" class="button" id="vss-cancel-upload">
                        <?php esc_html_e( 'Cancel', 'vss' ); ?>
                    </button>
                    <button type="button" class="button button-primary" id="vss-start-upload">
                        <?php esc_html_e( 'Upload CSV', 'vss' ); ?>
                    </button>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Download sample CSV
            $('.vss-download-sample').on('click', function(e) {
                e.preventDefault();
                window.location.href = '<?php echo admin_url( 'admin-ajax.php' ); ?>?action=vss_download_sample_csv&nonce=<?php echo wp_create_nonce( 'vss_download_csv' ); ?>';
            });
            
            // CSV Upload Modal
            $('.vss-upload-csv').on('click', function() {
                $('#vss-csv-upload-modal').show();
            });
            
            $('.vss-modal-close, #vss-cancel-upload').on('click', function() {
                $('#vss-csv-upload-modal').hide();
            });
            
            // Handle CSV upload
            $('#vss-start-upload').on('click', function() {
                var formData = new FormData();
                var fileInput = document.getElementById('csv-file');
                
                if (!fileInput.files[0]) {
                    alert('<?php esc_js( __( 'Please select a CSV file.', 'vss' ) ); ?>');
                    return;
                }
                
                formData.append('action', 'vss_upload_product_csv');
                formData.append('csv_file', fileInput.files[0]);
                formData.append('skip_duplicates', $('input[name="skip_duplicates"]').is(':checked') ? 1 : 0);
                formData.append('nonce', '<?php echo wp_create_nonce( 'vss_upload_csv' ); ?>');
                
                $.ajax({
                    url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            alert('<?php esc_js( __( 'Products uploaded successfully!', 'vss' ) ); ?>');
                            location.reload();
                        } else {
                            alert('<?php esc_js( __( 'Upload failed:', 'vss' ) ); ?> ' + response.data);
                        }
                    },
                    error: function() {
                        alert('<?php esc_js( __( 'Upload failed. Please try again.', 'vss' ) ); ?>');
                    }
                });
            });
            
            // Select all checkbox
            $('#cb-select-all').on('change', function() {
                $('input[name="product[]"]').prop('checked', $(this).is(':checked'));
            });
            
            // Bulk actions
            $('.vss-apply-bulk-action').on('click', function() {
                var action = $('#bulk-action-selector-top').val();
                var selected = $('input[name="product[]"]:checked').map(function() {
                    return $(this).val();
                }).get();
                
                if (action === '-1' || selected.length === 0) {
                    alert('<?php esc_js( __( 'Please select an action and at least one product.', 'vss' ) ); ?>');
                    return;
                }
                
                if (action === 'delete' && !confirm('<?php esc_js( __( 'Are you sure you want to delete the selected products?', 'vss' ) ); ?>')) {
                    return;
                }
                
                // Handle bulk actions via AJAX
                $.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', {
                    action: 'vss_bulk_product_action',
                    bulk_action: action,
                    products: selected,
                    nonce: '<?php echo wp_create_nonce( 'vss_bulk_products' ); ?>'
                }, function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('<?php esc_js( __( 'Action failed:', 'vss' ) ); ?> ' + response.data);
                    }
                });
            });
            
            // Individual product actions
            $('.vss-submit-product').on('click', function() {
                var productId = $(this).data('product-id');
                if (confirm('<?php esc_js( __( 'Submit this product for approval?', 'vss' ) ); ?>')) {
                    // Submit product
                    $.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', {
                        action: 'vss_submit_product',
                        product_id: productId,
                        nonce: '<?php echo wp_create_nonce( 'vss_submit_product' ); ?>'
                    }, function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert('<?php esc_js( __( 'Submission failed:', 'vss' ) ); ?> ' + response.data);
                        }
                    });
                }
            });
            
            $('.vss-delete-product').on('click', function() {
                var productId = $(this).data('product-id');
                if (confirm('<?php esc_js( __( 'Are you sure you want to delete this product?', 'vss' ) ); ?>')) {
                    // Delete product
                    $.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', {
                        action: 'vss_delete_product',
                        product_id: productId,
                        nonce: '<?php echo wp_create_nonce( 'vss_delete_product' ); ?>'
                    }, function(response) {
                        if (response.success) {
                            $('tr[data-product-id="' + productId + '"]').fadeOut();
                        } else {
                            alert('<?php esc_js( __( 'Deletion failed:', 'vss' ) ); ?> ' + response.data);
                        }
                    });
                }
            });
        });
        </script>
        <?php
    }
    
    /**
     * Get primary product image
     */
    private static function get_primary_product_image( $product_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'vss_product_images';
        
        $image_url = $wpdb->get_var( $wpdb->prepare(
            "SELECT image_url FROM $table_name WHERE product_id = %d AND is_primary = 1 LIMIT 1",
            $product_id
        ) );
        
        if ( ! $image_url ) {
            // Get first available image
            $image_url = $wpdb->get_var( $wpdb->prepare(
                "SELECT image_url FROM $table_name WHERE product_id = %d ORDER BY sort_order ASC LIMIT 1",
                $product_id
            ) );
        }
        
        return $image_url;
    }
    
    /**
     * Render product upload form
     */
    private static function render_product_upload_form( $product_id = 0 ) {
        $product = null;
        $variants = [];
        $images = [];
        $pricing_tiers = [];
        
        if ( $product_id ) {
            global $wpdb;
            $product = $wpdb->get_row( $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}vss_product_uploads WHERE id = %d AND vendor_id = %d",
                $product_id,
                get_current_user_id()
            ) );
            
            if ( $product ) {
                // Get variants
                $variants = $wpdb->get_results( $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}vss_product_variants WHERE product_id = %d ORDER BY id",
                    $product_id
                ) );
                
                // Get images
                $images = $wpdb->get_results( $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}vss_product_images WHERE product_id = %d ORDER BY sort_order",
                    $product_id
                ) );
                
                // Get pricing tiers
                $pricing_tiers = $wpdb->get_results( $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}vss_product_pricing WHERE product_id = %d ORDER BY min_quantity",
                    $product_id
                ) );
            }
        }
        
        $is_edit = $product ? true : false;
        ?>
        <div class="vss-product-form-container">
            <div class="vss-form-header">
                <h1>
                    <?php echo $is_edit ? esc_html__( 'Edit Product', 'vss' ) : esc_html__( 'Add New Product', 'vss' ); ?>
                </h1>
                <div class="vss-form-actions">
                    <button type="button" class="button vss-save-draft" data-product-id="<?php echo $product ? $product->id : 0; ?>">
                        <span class="dashicons dashicons-saved"></span>
                        <?php esc_html_e( 'Save Draft', 'vss' ); ?>
                    </button>
                    <button type="button" class="button button-primary vss-submit-product-form">
                        <span class="dashicons dashicons-yes"></span>
                        <?php echo $is_edit ? esc_html__( 'Update Product', 'vss' ) : esc_html__( 'Submit for Approval', 'vss' ); ?>
                    </button>
                </div>
            </div>
            
            <form id="vss-product-form" class="vss-product-form">
                <?php wp_nonce_field( 'vss_product_form', 'vss_product_nonce' ); ?>
                <input type="hidden" name="product_id" value="<?php echo $product ? $product->id : 0; ?>">
                
                <!-- Basic Information Section -->
                <div class="vss-form-section">
                    <h3>
                        <span class="dashicons dashicons-info"></span>
                        <?php esc_html_e( 'Basic Information', 'vss' ); ?>
                        <span class="vss-language-toggle">
                            <button type="button" class="vss-lang-btn active" data-lang="zh">中文</button>
                            <button type="button" class="vss-lang-btn" data-lang="en">English</button>
                            <button type="button" class="vss-translate-btn" title="<?php esc_attr_e( 'Auto-translate to English', 'vss' ); ?>">
                                <span class="dashicons dashicons-translation"></span>
                            </button>
                        </span>
                    </h3>
                    
                    <div class="vss-form-row">
                        <div class="vss-form-col-6">
                            <label for="product_name_zh"><?php esc_html_e( 'Product Name (Chinese)', 'vss' ); ?> <span class="required">*</span></label>
                            <input type="text" id="product_name_zh" name="product_name_zh" 
                                   value="<?php echo $product ? esc_attr( $product->product_name_zh ) : ''; ?>" 
                                   placeholder="请输入产品名称" required>
                        </div>
                        <div class="vss-form-col-6">
                            <label for="product_name_en"><?php esc_html_e( 'Product Name (English)', 'vss' ); ?></label>
                            <input type="text" id="product_name_en" name="product_name_en" 
                                   value="<?php echo $product ? esc_attr( $product->product_name_en ) : ''; ?>" 
                                   placeholder="Product name will appear here after translation">
                        </div>
                    </div>
                    
                    <div class="vss-form-row">
                        <div class="vss-form-col-12">
                            <label for="description_zh"><?php esc_html_e( 'Product Description (Chinese)', 'vss' ); ?> <span class="required">*</span></label>
                            <textarea id="description_zh" name="description_zh" rows="4" 
                                      placeholder="请详细描述您的产品特点、用途、材料等信息"><?php echo $product ? esc_textarea( $product->description_zh ) : ''; ?></textarea>
                        </div>
                    </div>
                    
                    <div class="vss-form-row">
                        <div class="vss-form-col-12">
                            <label for="description_en"><?php esc_html_e( 'Product Description (English)', 'vss' ); ?></label>
                            <textarea id="description_en" name="description_en" rows="4" 
                                      placeholder="English description will appear here after translation"><?php echo $product ? esc_textarea( $product->description_en ) : ''; ?></textarea>
                        </div>
                    </div>
                    
                    <div class="vss-form-row">
                        <div class="vss-form-col-12">
                            <label for="target_store"><?php esc_html_e( 'Target Store (目标商店)', 'vss' ); ?> <span class="required">*</span></label>
                            <select id="target_store" name="target_store" required>
                                <option value=""><?php esc_html_e( 'Please select a store / 请选择商店', 'vss' ); ?></option>
                                <?php 
                                $vendor_id = get_current_user_id();
                                $available_stores = VSS_Store_Integration::get_vendor_stores( $vendor_id );
                                foreach ( $available_stores as $store_key => $store_name ) :
                                ?>
                                    <option value="<?php echo esc_attr( $store_key ); ?>"><?php echo esc_html( $store_name ); ?></option>
                                <?php endforeach; ?>
                                <option value="unknown"><?php esc_html_e( '我不知道 (I don\'t know)', 'vss' ); ?></option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="vss-form-row vss-categories-row" style="display: none;">
                        <div class="vss-form-col-6">
                            <label for="category"><?php esc_html_e( 'Category (类别)', 'vss' ); ?> <span class="required">*</span></label>
                            <select id="category" name="category">
                                <option value=""><?php esc_html_e( 'Select Category / 选择类别', 'vss' ); ?></option>
                            </select>
                            <button type="button" class="button vss-refresh-categories" style="margin-top: 5px;">
                                <span class="dashicons dashicons-update"></span>
                                <?php esc_html_e( 'Refresh Categories', 'vss' ); ?>
                            </button>
                        </div>
                        <div class="vss-form-col-6">
                            <label for="subcategory"><?php esc_html_e( 'Subcategory (子类别)', 'vss' ); ?></label>
                            <select id="subcategory" name="subcategory">
                                <option value=""><?php esc_html_e( 'Select Subcategory / 选择子类别', 'vss' ); ?></option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="vss-form-row">
                        <div class="vss-form-col-4">
                            <label for="sku"><?php esc_html_e( 'SKU', 'vss' ); ?></label>
                            <input type="text" id="sku" name="sku" 
                                   value="<?php echo $product ? esc_attr( $product->sku ) : ''; ?>" 
                                   placeholder="<?php esc_attr_e( 'Product SKU', 'vss' ); ?>">
                        </div>
                        <div class="vss-form-col-4">
                            <label for="product_weight"><?php esc_html_e( 'Weight (kg) / 重量', 'vss' ); ?></label>
                            <input type="number" id="product_weight" name="product_weight" step="0.01" min="0"
                                   value="<?php echo $product ? $product->product_weight : ''; ?>" 
                                   placeholder="0.00">
                        </div>
                        <div class="vss-form-col-4">
                            <label for="production_time"><?php esc_html_e( 'Production Time (days) / 生产时间(天)', 'vss' ); ?> <span class="required">*</span></label>
                            <input type="number" id="production_time" name="production_time" min="1"
                                   value="<?php echo $product ? $product->production_time : ''; ?>" 
                                   placeholder="<?php esc_attr_e( 'e.g., 7', 'vss' ); ?>" required>
                        </div>
                    </div>
                </div>
                
                <!-- Product Images Section -->
                <div class="vss-form-section">
                    <h3>
                        <span class="dashicons dashicons-format-gallery"></span>
                        <?php esc_html_e( 'Product Images', 'vss' ); ?>
                    </h3>
                    
                    <div class="vss-image-upload-area">
                        <div class="vss-image-types">
                            <div class="vss-image-type" data-type="product">
                                <h4><?php esc_html_e( 'Product Images', 'vss' ); ?></h4>
                                <p><?php esc_html_e( 'Upload high-quality photos of your product from different angles', 'vss' ); ?></p>
                                <div class="vss-image-upload" data-type="product">
                                    <input type="file" id="product-images" name="product_images[]" multiple accept="image/*">
                                    <label for="product-images" class="vss-upload-btn">
                                        <span class="dashicons dashicons-upload"></span>
                                        <?php esc_html_e( 'Upload Product Images', 'vss' ); ?>
                                    </label>
                                </div>
                                <div class="vss-image-preview" data-type="product">
                                    <?php foreach ( $images as $image ) : if ( $image->image_type === 'product' ) : ?>
                                        <div class="vss-image-item" data-image-id="<?php echo $image->id; ?>">
                                            <img src="<?php echo esc_url( $image->image_url ); ?>" alt="">
                                            <div class="vss-image-actions">
                                                <button type="button" class="vss-set-primary <?php echo $image->is_primary ? 'active' : ''; ?>" title="<?php esc_attr_e( 'Set as primary', 'vss' ); ?>">
                                                    <span class="dashicons dashicons-star-filled"></span>
                                                </button>
                                                <button type="button" class="vss-remove-image" title="<?php esc_attr_e( 'Remove', 'vss' ); ?>">
                                                    <span class="dashicons dashicons-no"></span>
                                                </button>
                                            </div>
                                        </div>
                                    <?php endif; endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="vss-image-type" data-type="finished">
                                <h4><?php esc_html_e( 'Finished Production Pictures / 成品图片', 'vss' ); ?></h4>
                                <p><?php esc_html_e( 'Show examples of the finished product with customizations / 展示带有定制的成品示例', 'vss' ); ?></p>
                                <div class="vss-image-upload" data-type="finished">
                                    <input type="file" id="finished-images" name="finished_images[]" multiple accept="image/*">
                                    <label for="finished-images" class="vss-upload-btn">
                                        <span class="dashicons dashicons-upload"></span>
                                        <?php esc_html_e( 'Upload Finished Examples / 上传成品示例', 'vss' ); ?>
                                    </label>
                                </div>
                                <div class="vss-image-preview" data-type="finished">
                                    <?php foreach ( $images as $image ) : if ( $image->image_type === 'finished' ) : ?>
                                        <div class="vss-image-item" data-image-id="<?php echo $image->id; ?>">
                                            <img src="<?php echo esc_url( $image->image_url ); ?>" alt="">
                                            <div class="vss-image-actions">
                                                <button type="button" class="vss-remove-image" title="<?php esc_attr_e( 'Remove', 'vss' ); ?>">
                                                    <span class="dashicons dashicons-no"></span>
                                                </button>
                                            </div>
                                        </div>
                                    <?php endif; endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="vss-image-type" data-type="design_tool">
                                <h4><?php esc_html_e( 'Design Tool Files / 设计工具文件', 'vss' ); ?></h4>
                                <p><?php esc_html_e( 'Upload design templates or tool files for customization / 上传用于定制的设计模板或工具文件', 'vss' ); ?></p>
                                <div class="vss-image-upload" data-type="design_tool">
                                    <input type="file" id="design-tool-files" name="design_tool_files[]" multiple accept="image/*,.psd,.ai,.svg,.pdf">
                                    <label for="design-tool-files" class="vss-upload-btn">
                                        <span class="dashicons dashicons-admin-tools"></span>
                                        <?php esc_html_e( 'Upload Design Files / 上传设计文件', 'vss' ); ?>
                                    </label>
                                </div>
                                <div class="vss-image-preview" data-type="design_tool">
                                    <!-- Design tool files will be displayed here -->
                                </div>
                            </div>
                        </div>
                        
                        <div class="vss-image-urls-section">
                            <h4><?php esc_html_e( 'Or Add Image URLs', 'vss' ); ?></h4>
                            <p><?php esc_html_e( 'You can also provide direct links to your product images', 'vss' ); ?></p>
                            <div class="vss-url-inputs">
                                <div class="vss-url-input">
                                    <input type="url" name="image_urls[]" placeholder="https://example.com/image1.jpg">
                                    <select name="image_types[]">
                                        <option value="product"><?php esc_html_e( 'Product Image', 'vss' ); ?></option>
                                        <option value="finished"><?php esc_html_e( 'Finished Example', 'vss' ); ?></option>
                                    </select>
                                    <button type="button" class="vss-remove-url">×</button>
                                </div>
                            </div>
                            <button type="button" class="button vss-add-url-input">
                                <span class="dashicons dashicons-plus"></span>
                                <?php esc_html_e( 'Add Another URL', 'vss' ); ?>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Variants Section -->
                <div class="vss-form-section">
                    <h3>
                        <span class="dashicons dashicons-admin-settings"></span>
                        <?php esc_html_e( 'Product Variants', 'vss' ); ?>
                        <button type="button" class="button vss-add-variant">
                            <span class="dashicons dashicons-plus"></span>
                            <?php esc_html_e( 'Add Variant', 'vss' ); ?>
                        </button>
                    </h3>
                    
                    <div class="vss-variants-container">
                        <?php if ( ! empty( $variants ) ) : ?>
                            <?php foreach ( $variants as $index => $variant ) : ?>
                                <div class="vss-variant-row" data-index="<?php echo $index; ?>">
                                    <div class="vss-form-col-3">
                                        <input type="text" name="variants[<?php echo $index; ?>][name_zh]" 
                                               value="<?php echo esc_attr( $variant->variant_name_zh ); ?>"
                                               placeholder="<?php esc_attr_e( 'Variant name (Chinese)', 'vss' ); ?>">
                                    </div>
                                    <div class="vss-form-col-3">
                                        <input type="text" name="variants[<?php echo $index; ?>][name_en]" 
                                               value="<?php echo esc_attr( $variant->variant_name_en ); ?>"
                                               placeholder="<?php esc_attr_e( 'Variant name (English)', 'vss' ); ?>">
                                    </div>
                                    <div class="vss-form-col-3">
                                        <input type="text" name="variants[<?php echo $index; ?>][value_zh]" 
                                               value="<?php echo esc_attr( $variant->variant_value_zh ); ?>"
                                               placeholder="<?php esc_attr_e( 'Value (Chinese)', 'vss' ); ?>">
                                    </div>
                                    <div class="vss-form-col-2">
                                        <input type="number" name="variants[<?php echo $index; ?>][price_adjustment]" 
                                               value="<?php echo $variant->price_adjustment; ?>" step="0.01"
                                               placeholder="<?php esc_attr_e( 'Price +/-', 'vss' ); ?>">
                                    </div>
                                    <div class="vss-form-col-1">
                                        <button type="button" class="button vss-remove-variant">×</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="vss-variant-template" style="display: none;">
                        <div class="vss-variant-row" data-index="__INDEX__">
                            <div class="vss-form-col-3">
                                <input type="text" name="variants[__INDEX__][name_zh]" 
                                       placeholder="<?php esc_attr_e( 'Variant name (Chinese)', 'vss' ); ?>">
                            </div>
                            <div class="vss-form-col-3">
                                <input type="text" name="variants[__INDEX__][name_en]" 
                                       placeholder="<?php esc_attr_e( 'Variant name (English)', 'vss' ); ?>">
                            </div>
                            <div class="vss-form-col-3">
                                <input type="text" name="variants[__INDEX__][value_zh]" 
                                       placeholder="<?php esc_attr_e( 'Value (Chinese)', 'vss' ); ?>">
                            </div>
                            <div class="vss-form-col-2">
                                <input type="number" name="variants[__INDEX__][price_adjustment]" 
                                       step="0.01" placeholder="<?php esc_attr_e( 'Price +/-', 'vss' ); ?>">
                            </div>
                            <div class="vss-form-col-1">
                                <button type="button" class="button vss-remove-variant">×</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Pricing & Production Section -->
                <div class="vss-form-section">
                    <h3>
                        <span class="dashicons dashicons-money-alt"></span>
                        <?php esc_html_e( 'Pricing & Production', 'vss' ); ?>
                    </h3>
                    
                    <div class="vss-form-row">
                        <div class="vss-form-col-12">
                            <label for="customization_options"><?php esc_html_e( 'Customization Options / 定制选项', 'vss' ); ?></label>
                            <textarea id="customization_options" name="customization_options" rows="3"
                                      placeholder="<?php esc_attr_e( 'Describe available customization options / 描述可用的定制选项', 'vss' ); ?>"><?php echo $product ? esc_textarea( $product->customization_options ) : ''; ?></textarea>
                        </div>
                    </div>
                    
                    <div class="vss-pricing-section">
                        <h4><?php esc_html_e( 'Tiered Pricing / 阶梯定价', 'vss' ); ?></h4>
                        <p><?php esc_html_e( 'Set different prices based on order quantity (MOQ = Minimum Order Quantity) / 根据订单数量设置不同价格（MOQ = 最小订单量）', 'vss' ); ?></p>
                        
                        <div class="vss-pricing-tiers">
                            <?php if ( ! empty( $pricing_tiers ) ) : ?>
                                <?php foreach ( $pricing_tiers as $index => $tier ) : ?>
                                    <div class="vss-pricing-tier" data-index="<?php echo $index; ?>">
                                        <div class="vss-form-col-3">
                                            <label><?php esc_html_e( 'Min Quantity', 'vss' ); ?></label>
                                            <input type="number" name="pricing[<?php echo $index; ?>][min_quantity]" 
                                                   value="<?php echo $tier->min_quantity; ?>" min="1">
                                        </div>
                                        <div class="vss-form-col-3">
                                            <label><?php esc_html_e( 'Max Quantity', 'vss' ); ?></label>
                                            <input type="number" name="pricing[<?php echo $index; ?>][max_quantity]" 
                                                   value="<?php echo $tier->max_quantity; ?>">
                                        </div>
                                        <div class="vss-form-col-3">
                                            <label><?php esc_html_e( 'Unit Price (USD)', 'vss' ); ?></label>
                                            <input type="number" name="pricing[<?php echo $index; ?>][unit_price]" 
                                                   value="<?php echo $tier->unit_price; ?>" step="0.01" min="0">
                                        </div>
                                        <div class="vss-form-col-3">
                                            <button type="button" class="button vss-remove-pricing-tier">
                                                <span class="dashicons dashicons-trash"></span>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <div class="vss-pricing-tier" data-index="0">
                                    <div class="vss-form-col-3">
                                        <label><?php esc_html_e( 'Min Quantity', 'vss' ); ?></label>
                                        <input type="number" name="pricing[0][min_quantity]" value="1" min="1">
                                    </div>
                                    <div class="vss-form-col-3">
                                        <label><?php esc_html_e( 'Max Quantity', 'vss' ); ?></label>
                                        <input type="number" name="pricing[0][max_quantity]" placeholder="<?php esc_attr_e( 'Leave empty for unlimited', 'vss' ); ?>">
                                    </div>
                                    <div class="vss-form-col-3">
                                        <label><?php esc_html_e( 'Unit Price (USD)', 'vss' ); ?></label>
                                        <input type="number" name="pricing[0][unit_price]" step="0.01" min="0" placeholder="0.00">
                                    </div>
                                    <div class="vss-form-col-3">
                                        <button type="button" class="button vss-remove-pricing-tier">
                                            <span class="dashicons dashicons-trash"></span>
                                        </button>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <button type="button" class="button vss-add-pricing-tier">
                            <span class="dashicons dashicons-plus"></span>
                            <?php esc_html_e( 'Add Price Tier', 'vss' ); ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Form JavaScript -->
        <script>
        jQuery(document).ready(function($) {
            var variantIndex = <?php echo count( $variants ); ?>;
            var pricingIndex = <?php echo count( $pricing_tiers ) ?: 1; ?>;
            
            // Language toggle
            $('.vss-lang-btn').on('click', function() {
                var lang = $(this).data('lang');
                $('.vss-lang-btn').removeClass('active');
                $(this).addClass('active');
                
                // Show/hide appropriate language fields
                if (lang === 'zh') {
                    $('[id$="_zh"]').closest('.vss-form-col-6, .vss-form-col-12').show();
                    $('[id$="_en"]').closest('.vss-form-col-6, .vss-form-col-12').hide();
                } else {
                    $('[id$="_zh"]').closest('.vss-form-col-6, .vss-form-col-12').hide();
                    $('[id$="_en"]').closest('.vss-form-col-6, .vss-form-col-12').show();
                }
            });
            
            // Auto-translate functionality
            $('.vss-translate-btn').on('click', function() {
                var button = $(this);
                button.prop('disabled', true).addClass('loading');
                
                var productName = $('#product_name_zh').val();
                var description = $('#description_zh').val();
                
                if (!productName && !description) {
                    alert('<?php esc_js( __( 'Please enter Chinese text first', 'vss' ) ); ?>');
                    button.prop('disabled', false).removeClass('loading');
                    return;
                }
                
                $.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', {
                    action: 'vss_translate_product',
                    product_name: productName,
                    description: description,
                    nonce: '<?php echo wp_create_nonce( 'vss_translate' ); ?>'
                }, function(response) {
                    if (response.success) {
                        if (response.data.product_name) {
                            $('#product_name_en').val(response.data.product_name);
                        }
                        if (response.data.description) {
                            $('#description_en').val(response.data.description);
                        }
                    } else {
                        alert('<?php esc_js( __( 'Translation failed:', 'vss' ) ); ?> ' + response.data);
                    }
                }).always(function() {
                    button.prop('disabled', false).removeClass('loading');
                });
            });
            
            // Add variant
            $('.vss-add-variant').on('click', function() {
                var template = $('.vss-variant-template .vss-variant-row').clone();
                template.find('input').each(function() {
                    $(this).attr('name', $(this).attr('name').replace('__INDEX__', variantIndex));
                });
                template.attr('data-index', variantIndex);
                $('.vss-variants-container').append(template);
                variantIndex++;
            });
            
            // Remove variant
            $(document).on('click', '.vss-remove-variant', function() {
                $(this).closest('.vss-variant-row').remove();
            });
            
            // Add pricing tier
            $('.vss-add-pricing-tier').on('click', function() {
                var template = `
                    <div class="vss-pricing-tier" data-index="${pricingIndex}">
                        <div class="vss-form-col-3">
                            <label><?php esc_js( __( 'Min Quantity', 'vss' ) ); ?></label>
                            <input type="number" name="pricing[${pricingIndex}][min_quantity]" min="1">
                        </div>
                        <div class="vss-form-col-3">
                            <label><?php esc_js( __( 'Max Quantity', 'vss' ) ); ?></label>
                            <input type="number" name="pricing[${pricingIndex}][max_quantity]">
                        </div>
                        <div class="vss-form-col-3">
                            <label><?php esc_js( __( 'Unit Price (USD)', 'vss' ) ); ?></label>
                            <input type="number" name="pricing[${pricingIndex}][unit_price]" step="0.01" min="0">
                        </div>
                        <div class="vss-form-col-3">
                            <button type="button" class="button vss-remove-pricing-tier">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </div>
                    </div>
                `;
                $('.vss-pricing-tiers').append(template);
                pricingIndex++;
            });
            
            // Remove pricing tier
            $(document).on('click', '.vss-remove-pricing-tier', function() {
                if ($('.vss-pricing-tier').length > 1) {
                    $(this).closest('.vss-pricing-tier').remove();
                }
            });
            
            // Add URL input
            $('.vss-add-url-input').on('click', function() {
                var template = `
                    <div class="vss-url-input">
                        <input type="url" name="image_urls[]" placeholder="https://example.com/image.jpg">
                        <select name="image_types[]">
                            <option value="product"><?php esc_js( __( 'Product Image', 'vss' ) ); ?></option>
                            <option value="finished"><?php esc_js( __( 'Finished Example', 'vss' ) ); ?></option>
                        </select>
                        <button type="button" class="vss-remove-url">×</button>
                    </div>
                `;
                $('.vss-url-inputs').append(template);
            });
            
            // Remove URL input
            $(document).on('click', '.vss-remove-url', function() {
                if ($('.vss-url-input').length > 1) {
                    $(this).closest('.vss-url-input').remove();
                }
            });
            
            // Save draft
            $('.vss-save-draft').on('click', function() {
                var formData = $('#vss-product-form').serialize();
                formData += '&action=vss_save_product_draft';
                
                $.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', formData, function(response) {
                    if (response.success) {
                        alert('<?php esc_js( __( 'Draft saved successfully!', 'vss' ) ); ?>');
                        if (response.data.product_id) {
                            $('input[name="product_id"]').val(response.data.product_id);
                        }
                    } else {
                        alert('<?php esc_js( __( 'Failed to save draft:', 'vss' ) ); ?> ' + response.data);
                    }
                });
            });
            
            // Submit product
            $('.vss-submit-product-form').on('click', function() {
                var formData = $('#vss-product-form').serialize();
                formData += '&action=vss_upload_product';
                
                if (!confirm('<?php esc_js( __( 'Submit this product for approval?', 'vss' ) ); ?>')) {
                    return;
                }
                
                $.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', formData, function(response) {
                    if (response.success) {
                        alert('<?php esc_js( __( 'Product submitted successfully!', 'vss' ) ); ?>');
                        window.location.href = '<?php echo esc_url( add_query_arg( 'vss_action', 'upload_products', get_permalink() ) ); ?>';
                    } else {
                        alert('<?php esc_js( __( 'Submission failed:', 'vss' ) ); ?> ' + response.data);
                    }
                });
            });
            
            // Image upload handling
            $('input[type="file"]').on('change', function() {
                var input = this;
                var files = input.files;
                var imageType = $(input).closest('.vss-image-upload').data('type');
                var preview = $(input).closest('.vss-image-type').find('.vss-image-preview');
                
                for (var i = 0; i < files.length; i++) {
                    var file = files[i];
                    var reader = new FileReader();
                    
                    reader.onload = function(e) {
                        var imageHtml = `
                            <div class="vss-image-item">
                                <img src="${e.target.result}" alt="">
                                <div class="vss-image-actions">
                                    ${imageType === 'product' ? '<button type="button" class="vss-set-primary" title="Set as primary"><span class="dashicons dashicons-star-filled"></span></button>' : ''}
                                    <button type="button" class="vss-remove-image" title="Remove"><span class="dashicons dashicons-no"></span></button>
                                </div>
                                <input type="hidden" name="uploaded_images[]" value="${e.target.result}">
                                <input type="hidden" name="uploaded_image_types[]" value="${imageType}">
                            </div>
                        `;
                        preview.append(imageHtml);
                    };
                    
                    reader.readAsDataURL(file);
                }
            });
            
            // Set primary image
            $(document).on('click', '.vss-set-primary', function() {
                $('.vss-set-primary').removeClass('active');
                $(this).addClass('active');
            });
            
            // Remove image
            $(document).on('click', '.vss-remove-image', function() {
                $(this).closest('.vss-image-item').remove();
            });
            
            // Target store change handler
            $('#target_store').on('change', function() {
                var store = $(this).val();
                if (store && store !== 'unknown') {
                    $('.vss-categories-row').show();
                    loadStoreCategories(store);
                } else {
                    $('.vss-categories-row').hide();
                    $('#category, #subcategory').empty().append('<option value="">Select Category</option>');
                }
            });
            
            // Category change handler (load subcategories)
            $('#category').on('change', function() {
                var categoryId = $(this).val();
                var store = $('#target_store').val();
                
                $('#subcategory').empty().append('<option value="">Select Subcategory / 选择子类别</option>');
                
                if (categoryId && store && store !== 'unknown') {
                    loadStoreCategories(store, categoryId, true);
                }
            });
            
            // Refresh categories manually
            $('.vss-refresh-categories').on('click', function() {
                var store = $('#target_store').val();
                if (store && store !== 'unknown') {
                    $(this).prop('disabled', true).addClass('loading');
                    
                    $.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', {
                        action: 'vss_refresh_store_categories',
                        store_key: store,
                        nonce: '<?php echo wp_create_nonce( 'vss_frontend_nonce' ); ?>'
                    }, function(response) {
                        if (response.success) {
                            loadStoreCategories(store);
                            alert('<?php esc_js( __( 'Categories refreshed!', 'vss' ) ); ?>');
                        } else {
                            alert('<?php esc_js( __( 'Failed to refresh categories', 'vss' ) ); ?>');
                        }
                    }).always(function() {
                        $('.vss-refresh-categories').prop('disabled', false).removeClass('loading');
                    });
                }
            });
            
            // Function to load store categories
            function loadStoreCategories(store, parentId = null, isSubcategory = false) {
                $.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', {
                    action: 'vss_get_store_categories',
                    store_key: store,
                    parent_id: parentId,
                    nonce: '<?php echo wp_create_nonce( 'vss_frontend_nonce' ); ?>'
                }, function(response) {
                    if (response.success) {
                        var select = isSubcategory ? $('#subcategory') : $('#category');
                        var defaultText = isSubcategory ? 'Select Subcategory / 选择子类别' : 'Select Category / 选择类别';
                        
                        select.empty().append('<option value="">' + defaultText + '</option>');
                        
                        if (response.data.categories && response.data.categories.length > 0) {
                            response.data.categories.forEach(function(category) {
                                select.append('<option value="' + category.category_id + '">' + category.category_name + '</option>');
                            });
                        }
                    }
                });
            }
        });
        </script>
        <?php
    }
    
    /**
     * Render product edit form
     */
    private static function render_product_edit_form( $product_id ) {
        self::render_product_upload_form( $product_id );
    }
    
    /**
     * Handle Chinese to English translation
     */
    public static function translate_product_data() {
        check_ajax_referer( 'vss_translate', 'nonce' );
        
        if ( ! self::is_current_user_vendor() ) {
            wp_send_json_error( __( 'Unauthorized', 'vss' ) );
        }
        
        $product_name = sanitize_text_field( $_POST['product_name'] ?? '' );
        $description = sanitize_textarea_field( $_POST['description'] ?? '' );
        
        $translated = [];
        
        // Simple translation using Google Translate API or similar service
        // For now, we'll use a placeholder function
        if ( $product_name ) {
            $translated['product_name'] = self::translate_text( $product_name );
        }
        
        if ( $description ) {
            $translated['description'] = self::translate_text( $description );
        }
        
        wp_send_json_success( $translated );
    }
    
    /**
     * Simple translation function (placeholder)
     * In production, integrate with Google Translate API or similar
     */
    private static function translate_text( $text ) {
        // This is a placeholder function
        // In production, you would integrate with Google Translate API, DeepL, or similar service
        
        // For now, return a simple translated version
        $translations = [
            // Common product terms
            '手机壳' => 'Phone Case',
            '保护套' => 'Protective Case',
            '硅胶' => 'Silicone',
            '皮革' => 'Leather',
            '塑料' => 'Plastic',
            '金属' => 'Metal',
            '透明' => 'Transparent',
            '黑色' => 'Black',
            '白色' => 'White',
            '红色' => 'Red',
            '蓝色' => 'Blue',
            '绿色' => 'Green',
            '定制' => 'Custom',
            '批发' => 'Wholesale',
            '零售' => 'Retail',
            '尺寸' => 'Size',
            '颜色' => 'Color',
            '材质' => 'Material',
            '重量' => 'Weight',
            '厚度' => 'Thickness',
            '宽度' => 'Width',
            '长度' => 'Length',
            '高度' => 'Height',
            '包装' => 'Packaging',
            '运输' => 'Shipping',
            '质量' => 'Quality',
            '耐用' => 'Durable',
            '防水' => 'Waterproof',
            '防震' => 'Shockproof',
            '品牌' => 'Brand',
            '型号' => 'Model',
            '版本' => 'Version',
            '系列' => 'Series',
        ];
        
        // Simple word replacement
        foreach ( $translations as $chinese => $english ) {
            $text = str_replace( $chinese, $english, $text );
        }
        
        // If no translation found, use a simple prefix
        if ( preg_match( '/[\x{4e00}-\x{9fff}]/u', $text ) ) {
            // Still contains Chinese characters, might need actual translation service
            return '[Auto-translated] ' . $text;
        }
        
        return $text;
    }
    
    /**
     * Download sample CSV
     */
    public static function download_sample_csv() {
        check_ajax_referer( 'vss_download_csv', 'nonce' );
        
        if ( ! self::is_current_user_vendor() ) {
            wp_die( __( 'Unauthorized', 'vss' ) );
        }
        
        $filename = 'product_upload_sample_' . date( 'Y-m-d' ) . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        
        $output = fopen('php://output', 'w');
        
        // Add BOM for proper Chinese character display in Excel
        fputs($output, "\xEF\xBB\xBF");
        
        // CSV Headers (in Chinese for vendors)
        fputcsv($output, [
            '产品名称(中文)',
            '产品名称(英文)',
            '产品描述(中文)',
            '产品描述(英文)',
            '类别',
            'SKU',
            '重量(kg)',
            '生产时间(天数)',
            '定制选项',
            '最小订购量',
            '最大订购量',
            '单价(美元)',
            '产品图片链接1',
            '产品图片链接2',
            '产品图片链接3',
            '成品图片链接1',
            '成品图片链接2'
        ]);
        
        // Sample data rows
        $sample_data = [
            [
                '硅胶手机壳',
                'Silicone Phone Case',
                '高品质硅胶材质，柔软舒适，全面保护手机，多种颜色可选',
                'High-quality silicone material, soft and comfortable, comprehensive phone protection, multiple colors available',
                'electronics',
                'CASE-001',
                '0.05',
                '5',
                '颜色定制，印刷LOGO',
                '100',
                '1000',
                '2.50',
                'https://example.com/phone-case-1.jpg',
                'https://example.com/phone-case-2.jpg',
                'https://example.com/phone-case-3.jpg',
                'https://example.com/finished-case-1.jpg',
                'https://example.com/finished-case-2.jpg'
            ],
            [
                '定制帆布袋',
                'Custom Canvas Bag',
                '环保帆布材质，结实耐用，可印刷客户LOGO和图案',
                'Eco-friendly canvas material, durable and strong, customizable with customer logos and patterns',
                'bags',
                'BAG-002',
                '0.15',
                '7',
                '颜色、尺寸、印刷内容定制',
                '50',
                '500',
                '3.80',
                'https://example.com/canvas-bag-1.jpg',
                'https://example.com/canvas-bag-2.jpg',
                '',
                'https://example.com/finished-bag-1.jpg',
                ''
            ],
            [
                '不锈钢保温杯',
                'Stainless Steel Thermos',
                '304不锈钢材质，保温6小时，保冷12小时，安全健康',
                '304 stainless steel material, keeps warm for 6 hours, keeps cold for 12 hours, safe and healthy',
                'home',
                'THERMO-003',
                '0.35',
                '10',
                '颜色、容量、激光雕刻LOGO',
                '30',
                '200',
                '8.50',
                'https://example.com/thermos-1.jpg',
                'https://example.com/thermos-2.jpg',
                'https://example.com/thermos-3.jpg',
                'https://example.com/finished-thermos-1.jpg',
                'https://example.com/finished-thermos-2.jpg'
            ]
        ];
        
        foreach ( $sample_data as $row ) {
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Handle CSV upload
     */
    public static function handle_csv_upload() {
        check_ajax_referer( 'vss_upload_csv', 'nonce' );
        
        if ( ! self::is_current_user_vendor() ) {
            wp_send_json_error( __( 'Unauthorized', 'vss' ) );
        }
        
        if ( ! isset( $_FILES['csv_file'] ) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK ) {
            wp_send_json_error( __( 'No file uploaded or upload error', 'vss' ) );
        }
        
        $file = $_FILES['csv_file'];
        $skip_duplicates = isset( $_POST['skip_duplicates'] ) && $_POST['skip_duplicates'] === '1';
        
        // Validate file type
        $file_ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
        if ( $file_ext !== 'csv' ) {
            wp_send_json_error( __( 'Please upload a CSV file', 'vss' ) );
        }
        
        // Read CSV file
        $csv_data = [];
        if ( ( $handle = fopen( $file['tmp_name'], 'r' ) ) !== FALSE ) {
            // Skip BOM if present
            $bom = fread( $handle, 3 );
            if ( $bom !== "\xEF\xBB\xBF" ) {
                rewind( $handle );
            }
            
            // Read header row
            $headers = fgetcsv( $handle );
            if ( ! $headers ) {
                wp_send_json_error( __( 'Invalid CSV format - no headers found', 'vss' ) );
            }
            
            // Read data rows
            while ( ( $row = fgetcsv( $handle ) ) !== FALSE ) {
                if ( count( $row ) === count( $headers ) ) {
                    $csv_data[] = array_combine( $headers, $row );
                }
            }
            fclose( $handle );
        } else {
            wp_send_json_error( __( 'Could not read CSV file', 'vss' ) );
        }
        
        if ( empty( $csv_data ) ) {
            wp_send_json_error( __( 'No data found in CSV file', 'vss' ) );
        }
        
        $vendor_id = get_current_user_id();
        $imported_count = 0;
        $skipped_count = 0;
        $errors = [];
        
        global $wpdb;
        $products_table = $wpdb->prefix . 'vss_product_uploads';
        $images_table = $wpdb->prefix . 'vss_product_images';
        $pricing_table = $wpdb->prefix . 'vss_product_pricing';
        
        foreach ( $csv_data as $index => $row ) {
            $row_num = $index + 2; // +2 because of header row and 0-based index
            
            // Extract data
            $product_name_zh = trim( $row['产品名称(中文)'] ?? $row[array_keys($row)[0]] ?? '' );
            $product_name_en = trim( $row['产品名称(英文)'] ?? $row[array_keys($row)[1]] ?? '' );
            $description_zh = trim( $row['产品描述(中文)'] ?? $row[array_keys($row)[2]] ?? '' );
            $description_en = trim( $row['产品描述(英文)'] ?? $row[array_keys($row)[3]] ?? '' );
            $category = trim( $row['类别'] ?? $row[array_keys($row)[4]] ?? '' );
            $sku = trim( $row['SKU'] ?? $row[array_keys($row)[5]] ?? '' );
            $weight = floatval( $row['重量(kg)'] ?? $row[array_keys($row)[6]] ?? 0 );
            $production_time = intval( $row['生产时间(天数)'] ?? $row[array_keys($row)[7]] ?? 0 );
            $customization = trim( $row['定制选项'] ?? $row[array_keys($row)[8]] ?? '' );
            $min_qty = intval( $row['最小订购量'] ?? $row[array_keys($row)[9]] ?? 1 );
            $max_qty = intval( $row['最大订购量'] ?? $row[array_keys($row)[10]] ?? 0 );
            $unit_price = floatval( $row['单价(美元)'] ?? $row[array_keys($row)[11]] ?? 0 );
            
            // Validate required fields
            if ( empty( $product_name_zh ) || empty( $description_zh ) || $production_time <= 0 ) {
                $errors[] = sprintf( __( 'Row %d: Missing required fields (Product Name, Description, or Production Time)', 'vss' ), $row_num );
                continue;
            }
            
            // Check for duplicate SKU if skip_duplicates is enabled
            if ( $skip_duplicates && ! empty( $sku ) ) {
                $existing = $wpdb->get_var( $wpdb->prepare(
                    "SELECT id FROM $products_table WHERE vendor_id = %d AND sku = %s",
                    $vendor_id, $sku
                ) );
                if ( $existing ) {
                    $skipped_count++;
                    continue;
                }
            }
            
            // Auto-translate if English fields are empty
            if ( empty( $product_name_en ) && ! empty( $product_name_zh ) ) {
                $product_name_en = self::translate_text( $product_name_zh );
            }
            if ( empty( $description_en ) && ! empty( $description_zh ) ) {
                $description_en = self::translate_text( $description_zh );
            }
            
            // Insert product
            $result = $wpdb->insert(
                $products_table,
                [
                    'vendor_id' => $vendor_id,
                    'product_name_zh' => $product_name_zh,
                    'product_name_en' => $product_name_en,
                    'description_zh' => $description_zh,
                    'description_en' => $description_en,
                    'category' => $category,
                    'sku' => $sku,
                    'product_weight' => $weight,
                    'production_time' => $production_time,
                    'customization_options' => $customization,
                    'status' => 'draft',
                    'created_at' => current_time( 'mysql' ),
                    'updated_at' => current_time( 'mysql' )
                ],
                [
                    '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%d', '%s', '%s', '%s', '%s'
                ]
            );
            
            if ( $result === false ) {
                $errors[] = sprintf( __( 'Row %d: Database error - %s', 'vss' ), $row_num, $wpdb->last_error );
                continue;
            }
            
            $product_id = $wpdb->insert_id;
            
            // Add pricing tier
            if ( $unit_price > 0 ) {
                $wpdb->insert(
                    $pricing_table,
                    [
                        'product_id' => $product_id,
                        'min_quantity' => $min_qty,
                        'max_quantity' => $max_qty > 0 ? $max_qty : null,
                        'unit_price' => $unit_price,
                        'currency' => 'USD',
                        'created_at' => current_time( 'mysql' )
                    ],
                    [ '%d', '%d', '%d', '%f', '%s', '%s' ]
                );
            }
            
            // Add product images
            $image_columns = [
                '产品图片链接1', '产品图片链接2', '产品图片链接3',
                '成品图片链接1', '成品图片链接2'
            ];
            
            $sort_order = 0;
            foreach ( $image_columns as $col_index => $column ) {
                $image_url = trim( $row[$column] ?? $row[array_keys($row)[$col_index + 12]] ?? '' );
                if ( ! empty( $image_url ) && filter_var( $image_url, FILTER_VALIDATE_URL ) ) {
                    $image_type = strpos( $column, '成品' ) !== false ? 'finished' : 'product';
                    $is_primary = ( $image_type === 'product' && $sort_order === 0 ) ? 1 : 0;
                    
                    $wpdb->insert(
                        $images_table,
                        [
                            'product_id' => $product_id,
                            'image_type' => $image_type,
                            'image_url' => $image_url,
                            'sort_order' => $sort_order,
                            'is_primary' => $is_primary,
                            'created_at' => current_time( 'mysql' )
                        ],
                        [ '%d', '%s', '%s', '%d', '%d', '%s' ]
                    );
                    
                    $sort_order++;
                }
            }
            
            $imported_count++;
        }
        
        $message = sprintf( 
            __( 'Import completed: %d products imported, %d skipped', 'vss' ), 
            $imported_count, 
            $skipped_count 
        );
        
        if ( ! empty( $errors ) ) {
            $message .= '<br><strong>' . __( 'Errors:', 'vss' ) . '</strong><br>' . implode( '<br>', array_slice( $errors, 0, 10 ) );
            if ( count( $errors ) > 10 ) {
                $message .= '<br>' . sprintf( __( '... and %d more errors', 'vss' ), count( $errors ) - 10 );
            }
        }
        
        wp_send_json_success( [
            'message' => $message,
            'imported' => $imported_count,
            'skipped' => $skipped_count,
            'errors' => count( $errors )
        ] );
    }
    
    /**
     * Handle product upload form submission
     */
    public static function handle_product_upload() {
        check_ajax_referer( 'vss_product_form', 'vss_product_nonce' );
        
        if ( ! self::is_current_user_vendor() ) {
            wp_send_json_error( __( 'Unauthorized', 'vss' ) );
        }
        
        $vendor_id = get_current_user_id();
        $product_id = intval( $_POST['product_id'] ?? 0 );
        
        // Validate required fields
        $product_name_zh = sanitize_text_field( $_POST['product_name_zh'] ?? '' );
        $description_zh = sanitize_textarea_field( $_POST['description_zh'] ?? '' );
        $production_time = intval( $_POST['production_time'] ?? 0 );
        
        if ( empty( $product_name_zh ) || empty( $description_zh ) || $production_time <= 0 ) {
            wp_send_json_error( __( 'Please fill in all required fields', 'vss' ) );
        }
        
        global $wpdb;
        $products_table = $wpdb->prefix . 'vss_product_uploads';
        
        $target_store = sanitize_key( $_POST['target_store'] ?? '' );
        $category_id = sanitize_text_field( $_POST['category'] ?? '' );
        $subcategory_id = sanitize_text_field( $_POST['subcategory'] ?? '' );
        
        $target_stores_data = json_encode( [
            [
                'store_key' => $target_store,
                'category_id' => $category_id,
                'subcategory_id' => $subcategory_id
            ]
        ] );
        
        $product_data = [
            'vendor_id' => $vendor_id,
            'product_name_zh' => $product_name_zh,
            'product_name_en' => sanitize_text_field( $_POST['product_name_en'] ?? '' ),
            'description_zh' => $description_zh,
            'description_en' => sanitize_textarea_field( $_POST['description_en'] ?? '' ),
            'category' => sanitize_text_field( $_POST['category'] ?? '' ),
            'subcategory' => sanitize_text_field( $_POST['subcategory'] ?? '' ),
            'sku' => sanitize_text_field( $_POST['sku'] ?? '' ),
            'product_weight' => floatval( $_POST['product_weight'] ?? 0 ),
            'production_time' => $production_time,
            'customization_options' => sanitize_textarea_field( $_POST['customization_options'] ?? '' ),
            'target_stores' => $target_stores_data,
            'status' => 'pending',
            'submission_date' => current_time( 'mysql' ),
            'updated_at' => current_time( 'mysql' )
        ];
        
        if ( $product_id > 0 ) {
            // Update existing product
            $existing = $wpdb->get_row( $wpdb->prepare(
                "SELECT * FROM $products_table WHERE id = %d AND vendor_id = %d",
                $product_id, $vendor_id
            ) );
            
            if ( ! $existing ) {
                wp_send_json_error( __( 'Product not found', 'vss' ) );
            }
            
            $result = $wpdb->update(
                $products_table,
                $product_data,
                [ 'id' => $product_id, 'vendor_id' => $vendor_id ],
                [ '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%d', '%s', '%s', '%s', '%s' ],
                [ '%d', '%d' ]
            );
        } else {
            // Insert new product
            $product_data['created_at'] = current_time( 'mysql' );
            $result = $wpdb->insert(
                $products_table,
                $product_data,
                [ '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%d', '%s', '%s', '%s', '%s', '%s' ]
            );
            $product_id = $wpdb->insert_id;
        }
        
        if ( $result === false ) {
            wp_send_json_error( __( 'Database error: ', 'vss' ) . $wpdb->last_error );
        }
        
        // Handle variants, pricing, and images here...
        // (This would be a longer implementation)
        
        // Trigger Slack notification
        do_action( 'vss_product_submitted', $product_id, $vendor_id );
        
        // Log the submission
        self::log_product_activity( $product_id, 'submitted', $vendor_id );
        
        wp_send_json_success( [
            'message' => __( 'Product submitted for approval successfully!', 'vss' ),
            'product_id' => $product_id
        ] );
    }
    
    /**
     * Save product draft
     */
    public static function save_product_draft() {
        check_ajax_referer( 'vss_product_form', 'vss_product_nonce' );
        
        if ( ! self::is_current_user_vendor() ) {
            wp_send_json_error( __( 'Unauthorized', 'vss' ) );
        }
        
        $vendor_id = get_current_user_id();
        $product_id = intval( $_POST['product_id'] ?? 0 );
        
        global $wpdb;
        $products_table = $wpdb->prefix . 'vss_product_uploads';
        
        $product_data = [
            'vendor_id' => $vendor_id,
            'product_name_zh' => sanitize_text_field( $_POST['product_name_zh'] ?? '' ),
            'product_name_en' => sanitize_text_field( $_POST['product_name_en'] ?? '' ),
            'description_zh' => sanitize_textarea_field( $_POST['description_zh'] ?? '' ),
            'description_en' => sanitize_textarea_field( $_POST['description_en'] ?? '' ),
            'category' => sanitize_text_field( $_POST['category'] ?? '' ),
            'sku' => sanitize_text_field( $_POST['sku'] ?? '' ),
            'product_weight' => floatval( $_POST['product_weight'] ?? 0 ),
            'production_time' => intval( $_POST['production_time'] ?? 0 ),
            'customization_options' => sanitize_textarea_field( $_POST['customization_options'] ?? '' ),
            'status' => 'draft',
            'updated_at' => current_time( 'mysql' )
        ];
        
        if ( $product_id > 0 ) {
            $result = $wpdb->update(
                $products_table,
                $product_data,
                [ 'id' => $product_id, 'vendor_id' => $vendor_id ]
            );
        } else {
            $product_data['created_at'] = current_time( 'mysql' );
            $result = $wpdb->insert( $products_table, $product_data );
            $product_id = $wpdb->insert_id;
        }
        
        if ( $result === false ) {
            wp_send_json_error( __( 'Failed to save draft: ', 'vss' ) . $wpdb->last_error );
        }
        
        wp_send_json_success( [
            'message' => __( 'Draft saved successfully!', 'vss' ),
            'product_id' => $product_id
        ] );
    }
    
    /**
     * Log product activity
     */
    private static function log_product_activity( $product_id, $action, $user_id, $notes = '' ) {
        global $wpdb;
        
        $wpdb->insert(
            $wpdb->prefix . 'vss_product_approval_history',
            [
                'product_id' => $product_id,
                'action' => $action,
                'user_id' => $user_id,
                'notes' => $notes,
                'created_at' => current_time( 'mysql' )
            ],
            [ '%d', '%s', '%d', '%s', '%s' ]
        );
    }
    
    /**
     * Send Slack notification for new product submission
     */
    public static function send_slack_notification( $product_id, $vendor_id ) {
        // Get Slack webhook URL from settings
        $slack_webhook = get_option( 'vss_slack_webhook_url' );
        if ( empty( $slack_webhook ) ) {
            return; // No Slack webhook configured
        }
        
        // Get product and vendor details
        global $wpdb;
        $product = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}vss_product_uploads WHERE id = %d",
            $product_id
        ) );
        
        if ( ! $product ) {
            return;
        }
        
        $vendor = get_user_by( 'id', $vendor_id );
        if ( ! $vendor ) {
            return;
        }
        
        // Prepare Slack message
        $message = [
            'text' => '🎉 New Product Submission',
            'attachments' => [
                [
                    'color' => '#36a64f',
                    'fields' => [
                        [
                            'title' => 'Product Name',
                            'value' => $product->product_name_zh . ( $product->product_name_en ? ' / ' . $product->product_name_en : '' ),
                            'short' => true
                        ],
                        [
                            'title' => 'Vendor',
                            'value' => $vendor->display_name,
                            'short' => true
                        ],
                        [
                            'title' => 'Category',
                            'value' => $product->category ?: 'Not specified',
                            'short' => true
                        ],
                        [
                            'title' => 'SKU',
                            'value' => $product->sku ?: 'Not specified',
                            'short' => true
                        ],
                        [
                            'title' => 'Production Time',
                            'value' => $product->production_time . ' days',
                            'short' => true
                        ],
                        [
                            'title' => 'Submitted',
                            'value' => date( 'Y-m-d H:i:s' ),
                            'short' => true
                        ]
                    ],
                    'footer' => 'VSS Product Management',
                    'ts' => time()
                ]
            ]
        ];
        
        // Send to Slack
        wp_remote_post( $slack_webhook, [
            'body' => json_encode( $message ),
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'timeout' => 30
        ] );
    }
}