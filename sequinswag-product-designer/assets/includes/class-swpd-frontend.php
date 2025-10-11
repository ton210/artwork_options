<?php
/**
 * SWPD Frontend Class
 *
 * @package SWPD
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class SWPD_Frontend
 *
 * Handles all frontend functionality
 */
class SWPD_Frontend {

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
     * Initialize frontend
     */
    public function init() {
        // Enqueue scripts and styles
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        // Add designer button to product pages
        add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'add_designer_button' ), 20 );

        // Add hidden fields for design data
        add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'add_hidden_fields' ), 30 );

        // Add designer lightbox HTML
        add_action( 'wp_footer', array( $this, 'add_designer_lightbox' ) );

        // Handle cart item data
        add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_cart_item_data' ), 10, 3 );
        add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'get_cart_item_from_session' ), 10, 3 );

        // Display custom design in cart
        add_filter( 'woocommerce_cart_item_thumbnail', array( $this, 'display_cart_item_thumbnail' ), 10, 3 );
        add_filter( 'woocommerce_get_item_data', array( $this, 'display_cart_item_data' ), 10, 2 );

        // Add edit design button in cart
        add_action( 'woocommerce_cart_item_name', array( $this, 'add_edit_design_button' ), 10, 3 );

        // Save design data to order
        add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'save_design_to_order_item' ), 10, 4 );

        // Display design in order emails and thank you page
        add_filter( 'woocommerce_order_item_thumbnail', array( $this, 'display_order_item_thumbnail' ), 10, 2 );
        add_filter( 'woocommerce_display_item_meta', array( $this, 'display_order_item_meta' ), 10, 3 );

        // AJAX handlers for frontend
        add_action( 'wp_ajax_swpd_get_product_variants', array( $this, 'ajax_get_product_variants' ) );
        add_action( 'wp_ajax_nopriv_swpd_get_product_variants', array( $this, 'ajax_get_product_variants' ) );

        // Add body class on product pages with designer
        add_filter( 'body_class', array( $this, 'add_body_class' ) );

        // Handle variable product changes
        add_action( 'woocommerce_before_single_product', array( $this, 'setup_variable_product_handler' ) );
    }

    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        // Only load on product pages or pages with shortcode
        if ( ! is_product() && ! has_shortcode( get_the_content(), 'swpd_designer' ) ) {
            return;
        }

        // Get current product
        global $product;

        // Ensure we have a valid product object
        if ( ! $product || ! is_object( $product ) ) {
            // Try to get the product from the global post
            global $post;
            if ( $post && $post->post_type === 'product' ) {
                $product = wc_get_product( $post->ID );
            }
        }

        // Verify we have a WC_Product object
        if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
            return;
        }

        // Check if product has designer enabled
        if ( ! $this->product_has_designer( $product ) ) {
            return;
        }

        // Enqueue styles
        wp_enqueue_style(
            'swpd-designer-styles',
            SWPD_PLUGIN_URL . 'assets/css/designer-styles.css',
            array(),
            SWPD_VERSION
        );

        // Enqueue mobile-specific styles
        wp_enqueue_style(
            'swpd-designer-mobile',
            SWPD_PLUGIN_URL . 'assets/css/designer-mobile.css',
            array('swpd-designer-styles'),
            SWPD_VERSION
        );


        // Enqueue Fabric.js if not already loaded
        wp_enqueue_script(
            'fabric-js',
            'https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js',
            array(),
            '5.3.0',
            true
        );

        // Enqueue Cropper.js
        wp_enqueue_style(
            'cropper-css',
            'https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css',
            array(),
            '1.5.13'
        );

        wp_enqueue_script(
            'cropper-js',
            'https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js',
            array(),
            '1.5.13',
            true
        );

        // Enqueue main designer script (non-modular version)
        wp_enqueue_script(
            'swpd-enhanced-product-designer',
            ( defined( 'SWPD_SCRIPT_DEBUG' ) && SWPD_SCRIPT_DEBUG )
                ? SWPD_PLUGIN_URL . 'assets/js/enhanced-product-designer.js'
                : SWPD_PLUGIN_URL . 'assets/js/enhanced-product-designer.js',
            array( 'jquery', 'fabric-js', 'cropper-js' ),
            SWPD_VERSION,
            true
        );

        // Add module type attribute if in debug mode
        if ( defined( 'SWPD_SCRIPT_DEBUG' ) && SWPD_SCRIPT_DEBUG ) {
            add_filter( 'script_loader_tag', function( $tag, $handle, $src ) {
                if ( 'swpd-enhanced-product-designer' === $handle ) {
                    // Ensure the src is for the modular file before adding the type attribute
                    if ( strpos( $src, 'enhanced-product-designer.js' ) !== false ) {
                        return '<script type="module" src="' . esc_url( $src ) . '" id="' . esc_attr( $handle ) . '-js"></script>';
                    }
                }
                return $tag;
            }, 10, 3 );
        }

        // Prepare designer configuration
        $designer_config = $this->get_designer_config( $product );

        // Localize script
        wp_localize_script( 'swpd-enhanced-product-designer', 'swpdDesignerConfig', $designer_config );

        // Add translations
        wp_localize_script( 'swpd-enhanced-product-designer', 'swpdTranslations', array(
            'addToCart' => __( 'Add to Cart', 'swpd' ),
            'applyDesign' => __( 'Apply Design', 'swpd' ),
            'cancel' => __( 'Cancel', 'swpd' ),
            'customizeDesign' => __( 'Customize Design', 'swpd' ),
            'editDesign' => __( 'Edit Design', 'swpd' ),
            'loading' => __( 'Loading...', 'swpd' ),
            'savingDesign' => __( 'Saving design...', 'swpd' ),
            'designSaved' => __( 'Design saved!', 'swpd' ),
            'error' => __( 'An error occurred. Please try again.', 'swpd' ),
            'uploadImage' => __( 'Upload Image', 'swpd' ),
            'addText' => __( 'Add Text', 'swpd' ),
            'deleteItem' => __( 'Delete this item?', 'swpd' ),
            'lowResWarning' => __( 'This image may appear blurry when printed.', 'swpd' ),
            'maxFileSizeError' => __( 'File size must be less than 5MB.', 'swpd' ),
            'invalidFileTypeError' => __( 'Please upload a valid image file (JPEG, PNG, GIF, or WebP).', 'swpd' ),
            'startDesigning' => __( 'Start Designing', 'swpd' ),
            'tapToUpload' => __( 'Tap the upload button below to add your first image', 'swpd' ),
            'chooseTemplate' => __( 'Choose a Template', 'swpd' ),
        ) );

        // Add inline CSS for dynamic colors
        $primary_color = get_option( 'swpd_primary_color', '#0073aa' );
        $secondary_color = get_option( 'swpd_secondary_color', '#f0ad4e' );

        $inline_css = "
            :root {
                --swpd-primary: {$primary_color};
                --swpd-secondary: {$secondary_color};
                --swpd-primary-dark: " . $this->darken_color( $primary_color, 20 ) . ";
                --swpd-primary-light: " . $this->lighten_color( $primary_color, 20 ) . ";
            }
        ";

        wp_add_inline_style( 'swpd-frontend', $inline_css );
    }

    /**
     * Get designer configuration for product
     */
    private function get_designer_config( $product ) {
        $product_id = $product->get_id();

        // Get variants data
        $variants = array();

        if ( $product->is_type( 'variable' ) ) {
            $available_variations = $product->get_available_variations();

            foreach ( $available_variations as $variation_data ) {
                $variation = wc_get_product( $variation_data['variation_id'] );
                if ( ! $variation ) {
                    continue;
                }

                $designer_data = get_post_meta( $variation->get_id(), 'design_tool_layer', true );

                if ( $designer_data ) {
                    $variants[] = array(
                        'id' => $variation->get_id(),
                        'title' => $variation->get_name(),
                        'price' => $variation->get_price(),
                        'designer_data' => $designer_data,
                        'attributes' => $variation->get_variation_attributes()
                    );
                }
            }
        } else {
            // Simple product
            $designer_data = get_post_meta( $product_id, 'design_tool_layer', true );

            if ( $designer_data ) {
                $variants[] = array(
                    'id' => $product_id,
                    'title' => $product->get_name(),
                    'price' => $product->get_price(),
                    'designer_data' => $designer_data
                );
            }
        }

        // Get Cloudinary settings
        $cloudinary_config = array(
            'enabled' => get_option( 'swpd_cloudinary_enabled', false ),
            'cloud_name' => get_option( 'swpd_cloudinary_cloud_name', '' ),
            'api_key' => get_option( 'swpd_cloudinary_api_key', '' ),
            'upload_preset' => get_option( 'swpd_cloudinary_upload_preset', '' ),
            'folder' => get_option( 'swpd_cloudinary_folder', 'swpd-designs' ),
        );

        // Don't expose API secret to frontend
        unset( $cloudinary_config['api_secret'] );

        return array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'swpd_designer_nonce' ),
            'product_id' => $product_id,
            'product_type' => $product->get_type(),
            'product_name' => $product->get_name(),
            'currency' => get_woocommerce_currency(),
            'currency_symbol' => get_woocommerce_currency_symbol(),
            'variants' => $variants,
            'max_file_size' => get_option( 'swpd_max_upload_size', 5 ) * 1024 * 1024, // Convert MB to bytes
            'allowed_file_types' => get_option( 'swpd_allowed_file_types', array( 'jpg', 'jpeg', 'png', 'gif', 'webp' ) ),
            'enable_templates' => get_option( 'swpd_enable_templates', true ),
            'enable_text' => true,
            'enable_upload' => true,
            'enable_layers' => true,
            'cloudinary' => $cloudinary_config,
            'debug_mode' => get_option( 'swpd_debug_mode', false ),
        );
    }

    /**
     * Check if product has designer enabled
     */
    private function product_has_designer( $product ) {
        // Validate product object
        if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
            return false;
        }

        if ( $product->is_type( 'variable' ) ) {
            // Check if any variation has designer data
            $variations = $product->get_children();
            foreach ( $variations as $variation_id ) {
                if ( get_post_meta( $variation_id, 'design_tool_layer', true ) ) {
                    return true;
                }
            }
            return false;
        } else {
            // Simple product
            return (bool) get_post_meta( $product->get_id(), 'design_tool_layer', true );
        }
    }

    /**
     * Add designer button to product page
     */
    public function add_designer_button() {
        global $product;

        // Ensure we have a valid product object
        if ( ! $product || ! is_object( $product ) ) {
            // Try to get the product from the global post
            global $post;
            if ( $post && $post->post_type === 'product' ) {
                $product = wc_get_product( $post->ID );
            }
        }

        // Verify we have a WC_Product object
        if ( ! $product || ! is_a( $product, 'WC_Product' ) || ! $this->product_has_designer( $product ) ) {
            return;
        }

        // Check if design has been applied
        $design_applied = isset( $_GET['design_applied'] ) && $_GET['design_applied'] === '1';

        ?>
        <button type="button" id="swpd-customize-design-button" class="button alt swpd-designer-button" <?php echo $design_applied ? 'style="display:inline-flex;"' : ''; ?>>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
            <span><?php echo $design_applied ? esc_html__( 'Edit Design', 'swpd' ) : esc_html__( 'Customize Design', 'swpd' ); ?></span>
        </button>

        <?php if ( $product->is_type( 'variable' ) ) : ?>
        <script type="text/javascript">
        jQuery(function($) {
            // Handle variation changes
            $('.single_variation_wrap').on('show_variation', function(event, variation) {
                var hasDesigner = false;

                // Check if this variation has designer data
                if (window.swpdDesignerConfig && window.swpdDesignerConfig.variants) {
                    for (var i = 0; i < window.swpdDesignerConfig.variants.length; i++) {
                        if (window.swpdDesignerConfig.variants[i].id == variation.variation_id) {
                            hasDesigner = true;
                            break;
                        }
                    }
                }

                // Show/hide designer button based on variation
                if (hasDesigner) {
                    $('#swpd-customize-design-button').show();
                } else {
                    $('#swpd-customize-design-button').hide();
                }
            });

            // Hide button initially if no variation selected
            $('.single_variation_wrap').on('hide_variation', function() {
                $('#swpd-customize-design-button').hide();
            });
        });
        </script>
        <?php endif; ?>
        <?php
    }

    /**
     * Add hidden fields for design data
     */
    public function add_hidden_fields() {
        ?>
        <input type="hidden" id="custom-design-preview" name="custom_design_preview" value="">
        <input type="hidden" id="custom-design-data" name="custom_design_data" value="">
        <input type="hidden" id="custom-canvas-data" name="custom_canvas_data" value="">
        <?php
    }

    /**
     * Add designer lightbox HTML
     */
    public function add_designer_lightbox() {
        if ( ! is_product() ) {
            return;
        }

        global $product;

        // Ensure we have a valid product object
        if ( ! $product || ! is_object( $product ) ) {
            // Try to get the product from the global post
            global $post;
            if ( $post && $post->post_type === 'product' ) {
                $product = wc_get_product( $post->ID );
            }
        }

        // Verify we have a WC_Product object
        if ( ! $product || ! is_a( $product, 'WC_Product' ) || ! $this->product_has_designer( $product ) ) {
            return;
        }

        // Include the lightbox template
        $template_path = SWPD_PLUGIN_DIR . 'templates/designer-lightbox.php';

        if ( file_exists( $template_path ) ) {
            include $template_path;
        } else {
            // Fallback inline template
            ?>
            <div id="designer-lightbox" class="designer-lightbox" style="display: none;">
                <div class="designer-modal">
                    <div class="designer-header">
                        <h2><?php esc_html_e( 'Customize Your Design', 'swpd' ); ?></h2>
                        <button class="designer-close">&times;</button>
                    </div>

                    <div class="designer-loading">
                        <div class="loading-spinner"></div>
                        <p><?php esc_html_e( 'Loading designer...', 'swpd' ); ?></p>
                    </div>

                    <div class="designer-body" style="opacity: 0;">
                        <div class="designer-sidebar">
                            <div class="tool-section">
                                <h3><?php esc_html_e( 'Add Elements', 'swpd' ); ?></h3>
                                <div class="tool-buttons">
                                    <div class="upload-image-btn tool-btn">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20M12,12L16,16H13.5V19H10.5V16H8L12,12Z" />
                                        </svg>
                                        <span><?php esc_html_e( 'Upload Image', 'swpd' ); ?></span>
                                        <input type="file" id="main-file-input" class="image-upload-input" accept="image/*" multiple style="display: none;">
                                    </div>

                                    <button class="add-text-btn tool-btn">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M18.5,4L19.66,8.35L18.7,8.61C18.25,7.74 17.79,6.87 17.26,6.43C16.73,6 16.11,6 15.5,6H13V16.5C13,17 13,17.5 13.5,17.5H14V19H10V17.5H10.5C11,17.5 11,17 11,16.5V6H8.5C7.89,6 7.27,6 6.74,6.43C6.21,6.87 5.75,7.74 5.3,8.61L4.34,8.35L5.5,4H18.5Z" />
                                        </svg>
                                        <span><?php esc_html_e( 'Add Text', 'swpd' ); ?></span>
                                    </button>
                                </div>
                            </div>

                            <div class="tool-section">
                                <h3><?php esc_html_e( 'Actions', 'swpd' ); ?></h3>
                                <div class="history-controls">
                                    <button id="undo-btn" class="history-btn" disabled>
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12.5,8C9.85,8 7.45,9 5.6,10.6L2,7V16H11L7.38,12.38C8.77,11.22 10.54,10.5 12.5,10.5C16.04,10.5 19.05,12.81 20.1,16L22.47,15.22C21.08,11.03 17.15,8 12.5,8Z" />
                                        </svg>
                                        <?php esc_html_e( 'Undo', 'swpd' ); ?>
                                    </button>
                                    <button id="redo-btn" class="history-btn" disabled>
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M18.4,10.6C16.55,9 14.15,8 11.5,8C6.85,8 2.92,11.03 1.53,15.22L3.9,16C4.95,12.81 7.96,10.5 11.5,10.5C13.46,10.5 15.23,11.22 16.62,12.38L13,16H22V7L18.4,10.6Z" />
                                        </svg>
                                        <?php esc_html_e( 'Redo', 'swpd' ); ?>
                                    </button>
                                </div>

                                <div class="additional-tools">
                                    <button class="save-design-btn tool-btn">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M15,9H5V5H15M12,19A3,3 0 0,1 9,16A3,3 0 0,1 12,13A3,3 0 0,1 15,16A3,3 0 0,1 12,19M17,3H5C3.89,3 3,3.9 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V7L17,3Z" />
                                        </svg>
                                        <span><?php esc_html_e( 'Save Design', 'swpd' ); ?></span>
                                    </button>

                                    <button class="load-design-btn tool-btn">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20M12,10L8,14H10.5V17H13.5V14H16L12,10Z" />
                                        </svg>
                                        <span><?php esc_html_e( 'Load Design', 'swpd' ); ?></span>
                                    </button>
                                </div>
                            </div>

                            <div id="edit-tools" class="tool-section" style="display: none;">
                                <h3><?php esc_html_e( 'Edit', 'swpd' ); ?></h3>
                                <button id="crop-btn" class="tool-btn" style="display: none;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M7,17V1H5V5H1V7H5V17A2,2 0 0,0 7,19H17V23H19V19H23V17M17,15H19V7C19,5.89 18.1,5 17,5H9V7H17V15Z" />
                                    </svg>
                                    <span><?php esc_html_e( 'Crop', 'swpd' ); ?></span>
                                </button>

                                <div id="crop-controls" style="display: none;">
                                    <button class="apply-crop-btn tool-btn">
                                        <?php esc_html_e( 'Apply Crop', 'swpd' ); ?>
                                    </button>
                                    <button class="cancel-crop-btn tool-btn">
                                        <?php esc_html_e( 'Cancel', 'swpd' ); ?>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="canvas-container">
                            <canvas id="designer-canvas"></canvas>
                        </div>

                        <div class="properties-panel">
                            <h3><?php esc_html_e( 'Properties', 'swpd' ); ?></h3>
                            <div class="properties-content">
                                <!-- Properties will be populated dynamically -->
                            </div>
                        </div>
                    </div>

                    <div class="designer-footer">
                        <button class="cancel-design btn btn-secondary"><?php esc_html_e( 'Cancel', 'swpd' ); ?></button>
                        <button class="apply-design btn btn-primary"><?php esc_html_e( 'Apply Design', 'swpd' ); ?></button>
                        <button class="add-to-cart-design btn btn-primary" style="display: none;"><?php esc_html_e( 'Add to Cart', 'swpd' ); ?></button>
                    </div>
                </div>
            </div>

            <!-- Text Editor Modal -->
            <div id="text-editor-modal" class="text-editor-modal" style="display: none;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3><?php esc_html_e( 'Add Text', 'swpd' ); ?></h3>
                        <button class="modal-close">&times;</button>
                    </div>

                    <div class="text-controls">
                        <div class="control-group">
                            <label><?php esc_html_e( 'Text', 'swpd' ); ?></label>
                            <input type="text" id="text-input" placeholder="<?php esc_attr_e( 'Enter your text', 'swpd' ); ?>">
                        </div>

                        <div class="control-group">
                            <label><?php esc_html_e( 'Font', 'swpd' ); ?></label>
                            <select id="font-select">
                                <option value="Arial">Arial</option>
                                <option value="Times New Roman">Times New Roman</option>
                                <option value="Georgia">Georgia</option>
                                <option value="Helvetica">Helvetica</option>
                                <option value="Comic Sans MS">Comic Sans MS</option>
                                <option value="Impact">Impact</option>
                                <option value="Courier New">Courier New</option>
                            </select>
                        </div>

                        <div class="control-group">
                            <label><?php esc_html_e( 'Color', 'swpd' ); ?></label>
                            <input type="color" id="text-color" value="#000000">
                        </div>

                        <div class="control-group">
                            <label><?php esc_html_e( 'Size', 'swpd' ); ?></label>
                            <input type="range" id="text-size" min="10" max="100" value="30">
                            <span id="text-size-value">30px</span>
                        </div>
                    </div>

                    <div class="modal-actions">
                        <button id="cancel-text" class="btn btn-secondary"><?php esc_html_e( 'Cancel', 'swpd' ); ?></button>
                        <button id="add-text-confirm" class="btn btn-primary" disabled><?php esc_html_e( 'Add Text', 'swpd' ); ?></button>
                    </div>
                </div>
            </div>

            <!-- Mobile UI Elements -->
            <div id="mobile-tools-drawer" class="mobile-tools-drawer">
                <div class="drawer-header">
                    <h3><?php esc_html_e( 'Tools', 'swpd' ); ?></h3>
                    <button id="mobile-tools-close">&times;</button>
                </div>
                <div class="drawer-content">
                    <!-- Mobile tools will be populated here -->
                </div>
            </div>

            <div class="mobile-quick-actions">
                <button id="mobile-upload-btn" class="mobile-action-btn">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20M12,12L16,16H13.5V19H10.5V16H8L12,12Z" />
                    </svg>
                    <input type="file" accept="image/*" multiple style="display: none;">
                </button>

                <button id="mobile-text-btn" class="mobile-action-btn">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M18.5,4L19.66,8.35L18.7,8.61C18.25,7.74 17.79,6.87 17.26,6.43C16.73,6 16.11,6 15.5,6H13V16.5C13,17 13,17.5 13.5,17.5H14V19H10V17.5H10.5C11,17.5 11,17 11,16.5V6H8.5C7.89,6 7.27,6 6.74,6.43C6.21,6.87 5.75,7.74 5.3,8.61L4.34,8.35L5.5,4H18.5Z" />
                    </svg>
                </button>

                <button id="mobile-templates-btn" class="mobile-action-btn">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4M12,6A6,6 0 0,0 6,12A6,6 0 0,0 12,18A6,6 0 0,0 18,12A6,6 0 0,0 12,6M12,8A4,4 0 0,1 16,12A4,4 0 0,1 12,16A4,4 0 0,1 8,12A4,4 0 0,1 12,8Z" />
                    </svg>
                </button>

                <button id="mobile-save-btn" class="mobile-action-btn">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M15,9H5V5H15M12,19A3,3 0 0,1 9,16A3,3 0 0,1 12,13A3,3 0 0,1 15,16A3,3 0 0,1 12,19M17,3H5C3.89,3 3,3.9 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V7L17,3Z" />
                    </svg>
                </button>

                <button id="mobile-apply-btn" class="mobile-action-btn primary">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M21,7L9,19L3.5,13.5L4.91,12.09L9,16.17L19.59,5.59L21,7Z" />
                    </svg>
                </button>
            </div>
            <?php
        }
    }

    /**
     * Add cart item data
     */
    public function add_cart_item_data( $cart_item_data, $product_id, $variation_id ) {
        if ( isset( $_POST['custom_design_preview'] ) && ! empty( $_POST['custom_design_preview'] ) ) {
            $cart_item_data['custom_design_preview'] = sanitize_text_field( $_POST['custom_design_preview'] );
        }

        if ( isset( $_POST['custom_design_data'] ) && ! empty( $_POST['custom_design_data'] ) ) {
            $cart_item_data['custom_design_data'] = sanitize_text_field( $_POST['custom_design_data'] );
        }

        if ( isset( $_POST['custom_canvas_data'] ) && ! empty( $_POST['custom_canvas_data'] ) ) {
            $cart_item_data['custom_canvas_data'] = wp_kses_post( $_POST['custom_canvas_data'] );
        }

        return $cart_item_data;
    }

    /**
     * Get cart item from session
     */
    public function get_cart_item_from_session( $item, $values, $key ) {
        if ( isset( $values['custom_design_preview'] ) ) {
            $item['custom_design_preview'] = $values['custom_design_preview'];
        }

        if ( isset( $values['custom_design_data'] ) ) {
            $item['custom_design_data'] = $values['custom_design_data'];
        }

        if ( isset( $values['custom_canvas_data'] ) ) {
            $item['custom_canvas_data'] = $values['custom_canvas_data'];
        }

        return $item;
    }

    /**
     * Display custom design thumbnail in cart
     */
    public function display_cart_item_thumbnail( $thumbnail, $cart_item, $cart_item_key ) {
        if ( isset( $cart_item['custom_design_preview'] ) && ! empty( $cart_item['custom_design_preview'] ) ) {
            $preview_url = $cart_item['custom_design_preview'];
            $product_name = $cart_item['data']->get_name();

            // Enhanced thumbnail with better integration for WoodMart theme
            $thumbnail = sprintf(
                '<div class="swpd-custom-thumbnail-wrapper">
                    <img src="%s" alt="%s" class="attachment-shop_thumbnail size-shop_thumbnail swpd-custom-design-thumbnail custom-design-preview" loading="lazy" />
                    <div class="swpd-design-badge" title="%s">
                        <span class="swpd-badge-icon">🎨</span>
                    </div>
                </div>',
                esc_url( $preview_url ),
                esc_attr( $product_name . ' - Custom Design' ),
                esc_attr__( 'Custom Design', 'swpd' )
            );
        }

        return $thumbnail;
    }

    /**
     * Display custom design data in cart
     */
    public function display_cart_item_data( $item_data, $cart_item ) {
        if ( isset( $cart_item['custom_design_data'] ) && ! empty( $cart_item['custom_design_data'] ) ) {
            $item_data[] = array(
                'name' => __( 'Custom Design', 'swpd' ),
                'value' => __( 'Yes', 'swpd' ),
                'display' => __( 'Yes', 'swpd' )
            );

            // Parse design data
            $design_data = json_decode( $cart_item['custom_design_data'], true );

            if ( isset( $design_data['elementCount'] ) && $design_data['elementCount'] > 0 ) {
                $item_data[] = array(
                    'name' => __( 'Design Elements', 'swpd' ),
                    'value' => $design_data['elementCount'],
                    'display' => $design_data['elementCount']
                );
            }
        }

        return $item_data;
    }

    /**
     * Add edit design button in cart
     */
    public function add_edit_design_button( $product_name, $cart_item, $cart_item_key ) {
        if ( isset( $cart_item['custom_design_preview'] ) && ! empty( $cart_item['custom_design_preview'] ) ) {
            $product = $cart_item['data'];
            $product_id = $cart_item['product_id'];
            $variation_id = isset( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : 0;
            $product_url = get_permalink( $product_id );

            // Make canvas data available to JavaScript
            echo '<script type="text/javascript">';
            echo 'if (typeof window.swpdCanvasData === "undefined") { window.swpdCanvasData = {}; }';
            echo 'window.swpdCanvasData["' . esc_js( $cart_item_key ) . '"] = ' . wp_json_encode( $cart_item['custom_canvas_data'] ) . ';';
            echo '</script>';

            echo '<div class="swpd-cart-design-actions">';
            echo '<button type="button" class="button swpd-edit-design-button" ';
            echo 'id="swpd-edit-' . esc_attr( $cart_item_key ) . '" ';
            echo 'data-product-url="' . esc_url( $product_url ) . '" ';
            echo 'data-variant-id="' . esc_attr( $variation_id ?: $product_id ) . '">';
            echo '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">';
            echo '<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>';
            echo '<path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>';
            echo '</svg> ';
            echo esc_html__( 'Edit Design', 'swpd' );
            echo '</button>';
            echo '</div>';
        }

        return $product_name;
    }

    /**
     * Save design data to order item
     */
    public function save_design_to_order_item( $item, $cart_item_key, $values, $order ) {
        if ( isset( $values['custom_design_preview'] ) ) {
            $item->add_meta_data( '_swpd_design_preview', $values['custom_design_preview'] );
        }

        if ( isset( $values['custom_design_data'] ) ) {
            $item->add_meta_data( '_swpd_design_data', $values['custom_design_data'] );
        }

        if ( isset( $values['custom_canvas_data'] ) ) {
            $item->add_meta_data( '_swpd_canvas_data', $values['custom_canvas_data'] );
        }
    }

    /**
     * Display custom design thumbnail in order
     */
    public function display_order_item_thumbnail( $image, $item ) {
        $preview_url = $item->get_meta( '_swpd_design_preview', true );

        if ( $preview_url ) {
            $product_name = $item->get_name();
            $image = sprintf(
                '<img src="%s" alt="%s" class="swpd-order-design-thumbnail" style="width: 80px; height: auto;" />',
                esc_url( $preview_url ),
                esc_attr( $product_name . ' - Custom Design' )
            );
        }

        return $image;
    }

    /**
     * Display custom design data in order
     */
    public function display_order_item_meta( $html, $item, $args ) {
        $design_data_json = $item->get_meta( '_swpd_design_data', true );

        if ( $design_data_json ) {
            $design_data = json_decode( $design_data_json, true );

            $html .= '<dl class="swpd-design-meta">';
            $html .= '<dt class="swpd-design-meta-key">' . esc_html__( 'Custom Design', 'swpd' ) . ':</dt>';
            $html .= '<dd class="swpd-design-meta-value">' . esc_html__( 'Yes', 'swpd' ) . '</dd>';

            if ( isset( $design_data['elementCount'] ) && $design_data['elementCount'] > 0 ) {
                $html .= '<dt class="swpd-design-meta-key">' . esc_html__( 'Design Elements', 'swpd' ) . ':</dt>';
                $html .= '<dd class="swpd-design-meta-value">' . esc_html( $design_data['elementCount'] ) . '</dd>';
            }

            // Add download link for admin
            if ( current_user_can( 'manage_woocommerce' ) ) {
                $preview_url = $item->get_meta( '_swpd_design_preview', true );
                if ( $preview_url ) {
                    $html .= '<dt class="swpd-design-meta-key">' . esc_html__( 'Design File', 'swpd' ) . ':</dt>';
                    $html .= '<dd class="swpd-design-meta-value">';
                    $html .= '<a href="' . esc_url( $preview_url ) . '" target="_blank" class="button button-small">';
                    $html .= esc_html__( 'View Design', 'swpd' ) . '</a>';
                    $html .= '</dd>';
                }
            }

            $html .= '</dl>';
        }

        return $html;
    }

    /**
     * AJAX handler to get product variants
     */
    public function ajax_get_product_variants() {
        check_ajax_referer( 'swpd_designer_nonce', 'nonce' );

        $product_id = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;

        if ( ! $product_id ) {
            wp_send_json_error( 'Invalid product ID' );
        }

        $product = wc_get_product( $product_id );

        if ( ! $product ) {
            wp_send_json_error( 'Product not found' );
        }

        $variants = array();

        if ( $product->is_type( 'variable' ) ) {
            $available_variations = $product->get_available_variations();

            foreach ( $available_variations as $variation_data ) {
                $variation = wc_get_product( $variation_data['variation_id'] );
                if ( ! $variation ) {
                    continue;
                }

                $designer_data = get_post_meta( $variation->get_id(), 'design_tool_layer', true );

                if ( $designer_data ) {
                    $variants[] = array(
                        'id' => $variation->get_id(),
                        'title' => $variation->get_name(),
                        'price' => $variation->get_price(),
                        'designer_data' => $designer_data,
                        'attributes' => $variation->get_variation_attributes()
                    );
                }
            }
        }

        wp_send_json_success( $variants );
    }

    /**
     * Add body class on product pages with designer
     */
    public function add_body_class( $classes ) {
        if ( is_product() ) {
            global $product;
            if ( $product && $this->product_has_designer( $product ) ) {
                $classes[] = 'swpd-designer-enabled';

                // Add class if design has been applied
                if ( isset( $_GET['design_applied'] ) && $_GET['design_applied'] === '1' ) {
                    $classes[] = 'swpd-design-applied';
                }
            }
        }

        return $classes;
    }

    /**
     * Setup variable product handler
     */
    public function setup_variable_product_handler() {
        global $product;

        if ( ! $product || ! $product->is_type( 'variable' ) ) {
            return;
        }

        ?>
        <script type="text/javascript">
        jQuery(function($) {
            // Store original add to cart button text
            var originalButtonText = $('.single_add_to_cart_button').text();

            // Handle when a design is applied
            $(document).on('swpd:design_applied', function() {
                $('body').addClass('swpd-design-applied');
                $('.single_add_to_cart_button').show();
            });

            // Handle variation changes
            $('.variations_form').on('found_variation', function(event, variation) {
                // Check if this variation has designer
                var hasDesigner = false;
                if (window.swpdDesignerConfig && window.swpdDesignerConfig.variants) {
                    for (var i = 0; i < window.swpdDesignerConfig.variants.length; i++) {
                        if (window.swpdDesignerConfig.variants[i].id == variation.variation_id) {
                            hasDesigner = true;
                            break;
                        }
                    }
                }

                if (hasDesigner && !$('body').hasClass('swpd-design-applied')) {
                    $('.single_add_to_cart_button').hide();
                } else {
                    $('.single_add_to_cart_button').show();
                }
            });
        });
        </script>
        <?php
    }

    /**
     * Helper function to darken color
     */
    private function darken_color( $hex, $percent ) {
        $hex = str_replace( '#', '', $hex );
        $r = hexdec( substr( $hex, 0, 2 ) );
        $g = hexdec( substr( $hex, 2, 2 ) );
        $b = hexdec( substr( $hex, 4, 2 ) );

        $r = max( 0, min( 255, $r - ( $r * $percent / 100 ) ) );
        $g = max( 0, min( 255, $g - ( $g * $percent / 100 ) ) );
        $b = max( 0, min( 255, $b - ( $b * $percent / 100 ) ) );

        return '#' . str_pad( dechex( $r ), 2, '0', STR_PAD_LEFT )
                   . str_pad( dechex( $g ), 2, '0', STR_PAD_LEFT )
                   . str_pad( dechex( $b ), 2, '0', STR_PAD_LEFT );
    }

    /**
     * Helper function to lighten color
     */
    private function lighten_color( $hex, $percent ) {
        $hex = str_replace( '#', '', $hex );
        $r = hexdec( substr( $hex, 0, 2 ) );
        $g = hexdec( substr( $hex, 2, 2 ) );
        $b = hexdec( substr( $hex, 4, 2 ) );

        $r = max( 0, min( 255, $r + ( ( 255 - $r ) * $percent / 100 ) ) );
        $g = max( 0, min( 255, $g + ( ( 255 - $g ) * $percent / 100 ) ) );
        $b = max( 0, min( 255, $b + ( ( 255 - $b ) * $percent / 100 ) ) );

        return '#' . str_pad( dechex( $r ), 2, '0', STR_PAD_LEFT )
                   . str_pad( dechex( $g ), 2, '0', STR_PAD_LEFT )
                   . str_pad( dechex( $b ), 2, '0', STR_PAD_LEFT );
    }
}