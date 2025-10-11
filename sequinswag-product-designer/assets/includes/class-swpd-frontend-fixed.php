<?php
/**
 * SWPD Frontend Class - Fixed Version
 * Resolves cart thumbnail issues and improves reliability
 *
 * @package SWPD
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class SWPD_Frontend_Fixed
 *
 * Handles all frontend functionality with bug fixes
 */
class SWPD_Frontend_Fixed {

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

        // Handle cart item data - IMPROVED
        add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_cart_item_data' ), 10, 3 );
        add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'get_cart_item_from_session' ), 10, 3 );

        // Display custom design in cart - FIXED
        add_filter( 'woocommerce_cart_item_thumbnail', array( $this, 'display_cart_item_thumbnail' ), 20, 3 );
        add_filter( 'woocommerce_get_item_data', array( $this, 'display_cart_item_data' ), 10, 2 );

        // Mini cart thumbnail - ADDED
        add_filter( 'woocommerce_widget_cart_item_thumbnail', array( $this, 'display_cart_item_thumbnail' ), 20, 3 );

        // Add edit design button in cart
        add_action( 'woocommerce_cart_item_name', array( $this, 'add_edit_design_button' ), 10, 3 );

        // Save design data to order
        add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'save_design_to_order_item' ), 10, 4 );

        // Display design in order emails and thank you page - IMPROVED
        add_filter( 'woocommerce_order_item_thumbnail', array( $this, 'display_order_item_thumbnail' ), 20, 2 );
        add_filter( 'woocommerce_display_item_meta', array( $this, 'display_order_item_meta' ), 10, 3 );

        // AJAX handlers for frontend
        add_action( 'wp_ajax_swpd_get_product_variants', array( $this, 'ajax_get_product_variants' ) );
        add_action( 'wp_ajax_nopriv_swpd_get_product_variants', array( $this, 'ajax_get_product_variants' ) );

        // Add body class on product pages with designer
        add_filter( 'body_class', array( $this, 'add_body_class' ) );

        // Handle variable product changes
        add_action( 'woocommerce_before_single_product', array( $this, 'setup_variable_product_handler' ) );

        // Debug hooks - ADDED
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            add_action( 'wp_footer', array( $this, 'debug_cart_data' ) );
        }
    }

    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        // Only load on product pages or pages with shortcode
        if ( ! is_product() && ! has_shortcode( get_the_content(), 'swpd_designer' ) && ! is_cart() && ! is_checkout() ) {
            return;
        }

        // Get current product
        global $product;
        if ( is_product() && $product && get_post_meta( $product->get_id(), '_swpd_design_enabled', true ) ) {
            
            // Enqueue the fixed designer script
            wp_enqueue_script(
                'swpd-enhanced-designer-fixed',
                SWPD_PLUGIN_URL . 'assets/js/enhanced-product-designer-fixed.js',
                array( 'jquery' ),
                SWPD_VERSION,
                true
            );

            // Localize script with comprehensive config
            wp_localize_script( 'swpd-enhanced-designer-fixed', 'swpdDesignerConfig', array(
                'ajax_url'          => admin_url( 'admin-ajax.php' ),
                'nonce'             => wp_create_nonce( 'swpd_designer_nonce' ),
                'product_id'        => $product->get_id(),
                'is_variable'       => $product->is_type( 'variable' ),
                'variants'          => $this->get_product_variants( $product ),
                'canvas_width'      => get_post_meta( $product->get_id(), '_swpd_canvas_width', true ) ?: 800,
                'canvas_height'     => get_post_meta( $product->get_id(), '_swpd_canvas_height', true ) ?: 600,
                'max_file_size'     => wp_max_upload_size(),
                'allowed_types'     => array( 'image/jpeg', 'image/png', 'image/gif', 'image/webp' ),
                'debug'             => defined( 'WP_DEBUG' ) && WP_DEBUG,
                'cloudinary'        => array(
                    'enabled'       => get_option( 'swpd_cloudinary_enabled', false ),
                    'cloud_name'    => get_option( 'swpd_cloudinary_cloud_name', '' ),
                    'upload_preset' => get_option( 'swpd_cloudinary_upload_preset', '' )
                )
            ) );

            wp_localize_script( 'swpd-enhanced-designer-fixed', 'swpdTranslations', array(
                'editDesign'        => __( 'Edit Design', 'swpd' ),
                'addToCart'         => __( 'Add to Cart', 'swpd' ),
                'startDesigning'    => __( 'Start Designing', 'swpd' ),
                'uploadImage'       => __( 'Upload Image', 'swpd' ),
                'addText'           => __( 'Add Text', 'swpd' ),
                'save'              => __( 'Save', 'swpd' ),
                'cancel'            => __( 'Cancel', 'swpd' ),
                'apply'             => __( 'Apply Design', 'swpd' ),
                'delete'            => __( 'Delete', 'swpd' ),
                'loading'           => __( 'Loading...', 'swpd' ),
                'error'             => __( 'Error', 'swpd' ),
                'success'           => __( 'Success', 'swpd' ),
                'selectVariant'     => __( 'Please select a variant first', 'swpd' ),
                'designSaved'       => __( 'Design saved successfully', 'swpd' ),
                'uploadFailed'      => __( 'Upload failed. Please try again.', 'swpd' ),
                'fileTooLarge'      => __( 'File too large. Please select smaller images.', 'swpd' ),
                'invalidFileType'   => __( 'Invalid file type. Please select only images.', 'swpd' )
            ) );
        }

        // Always load cart/checkout styles
        if ( is_cart() || is_checkout() || is_account_page() ) {
            wp_enqueue_style(
                'swpd-cart-styles',
                SWPD_PLUGIN_URL . 'assets/css/designer-styles.css',
                array(),
                SWPD_VERSION
            );
        }
    }

    /**
     * Get product variants for variable products
     */
    private function get_product_variants( $product ) {
        if ( ! $product || ! $product->is_type( 'variable' ) ) {
            return array();
        }

        $variants = array();
        $available_variations = $product->get_available_variations();

        foreach ( $available_variations as $variation ) {
            $variants[] = array(
                'variation_id'  => $variation['variation_id'],
                'attributes'    => $variation['attributes'],
                'display_name'  => wc_get_formatted_variation( $variation, true ),
                'price'         => $variation['display_price'],
                'image'         => $variation['image']
            );
        }

        return $variants;
    }

    /**
     * Add designer button to product pages
     */
    public function add_designer_button() {
        global $product;
        
        if ( ! $product || ! get_post_meta( $product->get_id(), '_swpd_design_enabled', true ) ) {
            return;
        }

        $button_text = get_post_meta( $product->get_id(), '_swpd_button_text', true );
        if ( empty( $button_text ) ) {
            $button_text = __( 'Customize Design', 'swpd' );
        }

        echo '<div class="swpd-designer-button-container">';
        echo '<button type="button" id="swpd-open-designer" class="button alt swpd-designer-btn" data-product-id="' . esc_attr( $product->get_id() ) . '">';
        echo '<span class="designer-icon">🎨</span> ' . esc_html( $button_text );
        echo '</button>';
        echo '</div>';
    }

    /**
     * Add hidden fields for design data
     */
    public function add_hidden_fields() {
        ?>
        <input type="hidden" id="custom-design-preview" name="custom_design_preview" value="">
        <input type="hidden" id="custom-design-data" name="custom_design_data" value="">
        <input type="hidden" id="custom-canvas-data" name="custom_canvas_data" value="">
        <input type="hidden" id="custom-variant-id" name="custom_variant_id" value="">
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
        if ( ! $product || ! get_post_meta( $product->get_id(), '_swpd_design_enabled', true ) ) {
            return;
        }

        ?>
        <div id="swpd-designer-modal" class="swpd-modal" style="display: none;">
            <div class="swpd-modal-content">
                <div class="swpd-modal-header">
                    <h3><?php esc_html_e( 'Product Designer', 'swpd' ); ?></h3>
                    <button class="swpd-modal-close">&times;</button>
                </div>
                <div class="swpd-modal-body">
                    <div id="swpd-designer-container" data-swpd-designer data-swpd-options='<?php echo wp_json_encode( array(
                        'width' => get_post_meta( $product->get_id(), '_swpd_canvas_width', true ) ?: 800,
                        'height' => get_post_meta( $product->get_id(), '_swpd_canvas_height', true ) ?: 600,
                        'backgroundColor' => get_post_meta( $product->get_id(), '_swpd_canvas_bg', true ) ?: '#ffffff'
                    ) ); ?>'></div>
                </div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            // Open designer modal
            $('#swpd-open-designer').on('click', function() {
                $('#swpd-designer-modal').fadeIn();
            });
            
            // Close designer modal
            $('.swpd-modal-close, .swpd-modal').on('click', function(e) {
                if (e.target === this) {
                    $('#swpd-designer-modal').fadeOut();
                }
            });
        });
        </script>
        <?php
    }

    /**
     * Add cart item data - IMPROVED with validation
     */
    public function add_cart_item_data( $cart_item_data, $product_id, $variation_id ) {
        // Validate and sanitize design data
        if ( isset( $_POST['custom_design_preview'] ) && ! empty( $_POST['custom_design_preview'] ) ) {
            $preview_data = sanitize_text_field( $_POST['custom_design_preview'] );
            
            // Validate base64 image data
            if ( $this->is_valid_base64_image( $preview_data ) ) {
                $cart_item_data['custom_design_preview'] = $preview_data;
                
                // Store additional metadata
                $cart_item_data['custom_design_timestamp'] = time();
                $cart_item_data['custom_design_product_id'] = $product_id;
                
                if ( $variation_id ) {
                    $cart_item_data['custom_design_variation_id'] = $variation_id;
                }
            } else {
                $this->logger->log( 'Invalid design preview data provided', 'warning' );
            }
        }

        if ( isset( $_POST['custom_design_data'] ) && ! empty( $_POST['custom_design_data'] ) ) {
            $cart_item_data['custom_design_data'] = sanitize_text_field( $_POST['custom_design_data'] );
        }

        if ( isset( $_POST['custom_canvas_data'] ) && ! empty( $_POST['custom_canvas_data'] ) ) {
            // Validate JSON data
            $canvas_data = wp_kses_post( $_POST['custom_canvas_data'] );
            if ( $this->is_valid_json( $canvas_data ) ) {
                $cart_item_data['custom_canvas_data'] = $canvas_data;
            }
        }

        if ( isset( $_POST['custom_variant_id'] ) && ! empty( $_POST['custom_variant_id'] ) ) {
            $cart_item_data['custom_variant_id'] = intval( $_POST['custom_variant_id'] );
        }

        return $cart_item_data;
    }

    /**
     * Validate base64 image data
     */
    private function is_valid_base64_image( $data ) {
        // Check if it starts with data:image/
        if ( strpos( $data, 'data:image/' ) !== 0 ) {
            return false;
        }

        // Extract the actual base64 data
        $base64_data = substr( $data, strpos( $data, ',' ) + 1 );
        
        // Validate base64
        if ( ! base64_decode( $base64_data, true ) ) {
            return false;
        }

        return true;
    }

    /**
     * Validate JSON data
     */
    private function is_valid_json( $string ) {
        json_decode( $string );
        return json_last_error() === JSON_ERROR_NONE;
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

        if ( isset( $values['custom_design_timestamp'] ) ) {
            $item['custom_design_timestamp'] = $values['custom_design_timestamp'];
        }

        if ( isset( $values['custom_design_product_id'] ) ) {
            $item['custom_design_product_id'] = $values['custom_design_product_id'];
        }

        if ( isset( $values['custom_design_variation_id'] ) ) {
            $item['custom_design_variation_id'] = $values['custom_design_variation_id'];
        }

        if ( isset( $values['custom_variant_id'] ) ) {
            $item['custom_variant_id'] = $values['custom_variant_id'];
        }

        return $item;
    }

    /**
     * Display custom design thumbnail in cart - FIXED VERSION
     */
    public function display_cart_item_thumbnail( $thumbnail, $cart_item, $cart_item_key ) {
        // Check if this cart item has custom design data
        if ( ! isset( $cart_item['custom_design_preview'] ) || empty( $cart_item['custom_design_preview'] ) ) {
            return $thumbnail;
        }

        $preview_url = $cart_item['custom_design_preview'];
        $product_name = $cart_item['data']->get_name();

        // Validate the preview URL
        if ( ! $this->is_valid_base64_image( $preview_url ) ) {
            $this->logger->log( 'Invalid cart thumbnail preview data for item: ' . $cart_item_key, 'warning' );
            return $thumbnail;
        }

        // Create enhanced thumbnail with proper fallback
        try {
            $custom_thumbnail = sprintf(
                '<div class="swpd-custom-thumbnail-wrapper" data-cart-key="%s">
                    <img src="%s" alt="%s" class="attachment-shop_thumbnail size-shop_thumbnail swpd-custom-design-thumbnail custom-design-preview" loading="lazy" onerror="this.style.display=\'none\'; this.nextSibling.style.display=\'block\';" />
                    <div class="thumbnail-fallback" style="display: none;">%s</div>
                    <div class="swpd-design-badge" title="%s">
                        <span class="swpd-badge-icon">🎨</span>
                    </div>
                    <div class="design-timestamp" data-timestamp="%s" style="display: none;"></div>
                </div>',
                esc_attr( $cart_item_key ),
                esc_url( $preview_url ),
                esc_attr( $product_name . ' - Custom Design' ),
                esc_html( $thumbnail ), // Fallback to original thumbnail
                esc_attr__( 'Custom Design', 'swpd' ),
                isset( $cart_item['custom_design_timestamp'] ) ? esc_attr( $cart_item['custom_design_timestamp'] ) : ''
            );

            return $custom_thumbnail;

        } catch ( Exception $e ) {
            $this->logger->log( 'Error creating custom thumbnail: ' . $e->getMessage(), 'error' );
            return $thumbnail; // Return original on error
        }
    }

    /**
     * Display custom design data in cart
     */
    public function display_cart_item_data( $item_data, $cart_item ) {
        if ( isset( $cart_item['custom_design_data'] ) && ! empty( $cart_item['custom_design_data'] ) ) {
            $item_data[] = array(
                'name'    => __( 'Custom Design', 'swpd' ),
                'value'   => __( 'Yes', 'swpd' ),
                'display' => '<span class="custom-design-indicator">🎨 ' . __( 'Custom Design', 'swpd' ) . '</span>'
            );
        }

        // Add variant info if available
        if ( isset( $cart_item['custom_variant_id'] ) && ! empty( $cart_item['custom_variant_id'] ) ) {
            $variant_product = wc_get_product( $cart_item['custom_variant_id'] );
            if ( $variant_product ) {
                $item_data[] = array(
                    'name'    => __( 'Design Variant', 'swpd' ),
                    'value'   => $variant_product->get_formatted_name(),
                    'display' => $variant_product->get_formatted_name()
                );
            }
        }

        return $item_data;
    }

    /**
     * Add edit design button in cart - IMPROVED
     */
    public function add_edit_design_button( $product_name, $cart_item, $cart_item_key ) {
        if ( ! isset( $cart_item['custom_design_preview'] ) || empty( $cart_item['custom_design_preview'] ) ) {
            return $product_name;
        }

        $product_id = $cart_item['product_id'];
        $variation_id = isset( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : 0;
        $product_url = get_permalink( $product_id );

        // Make canvas data available to JavaScript
        wp_add_inline_script( 'jquery', '
            if (typeof window.swpdCanvasData === "undefined") { 
                window.swpdCanvasData = {}; 
            }
            window.swpdCanvasData["' . esc_js( $cart_item_key ) . '"] = ' . wp_json_encode( 
                isset( $cart_item['custom_canvas_data'] ) ? $cart_item['custom_canvas_data'] : '' 
            ) . ';
        ' );

        // Add edit button after product name
        $edit_button = '<div class="swpd-cart-design-actions" style="margin-top: 8px;">';
        $edit_button .= '<a href="' . esc_url( $product_url ) . '" class="button swpd-edit-design-button btn-small" ';
        $edit_button .= 'data-cart-key="' . esc_attr( $cart_item_key ) . '" ';
        $edit_button .= 'data-product-id="' . esc_attr( $product_id ) . '" ';
        $edit_button .= 'data-variant-id="' . esc_attr( $variation_id ?: $product_id ) . '">';
        $edit_button .= '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 5px;">';
        $edit_button .= '<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>';
        $edit_button .= '<path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>';
        $edit_button .= '</svg>';
        $edit_button .= __( 'Edit Design', 'swpd' );
        $edit_button .= '</a>';
        $edit_button .= '</div>';

        return $product_name . $edit_button;
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

        if ( isset( $values['custom_design_timestamp'] ) ) {
            $item->add_meta_data( '_swpd_design_timestamp', $values['custom_design_timestamp'] );
        }

        if ( isset( $values['custom_variant_id'] ) ) {
            $item->add_meta_data( '_swpd_variant_id', $values['custom_variant_id'] );
        }
    }

    /**
     * Display custom design thumbnail in order - IMPROVED
     */
    public function display_order_item_thumbnail( $image, $item ) {
        $preview = $item->get_meta( '_swpd_design_preview' );
        
        if ( ! empty( $preview ) && $this->is_valid_base64_image( $preview ) ) {
            $product_name = $item->get_name();
            
            $custom_thumbnail = sprintf(
                '<div class="swpd-order-thumbnail-wrapper">
                    <img src="%s" alt="%s" class="swpd-order-design-thumbnail" style="max-width: 64px; height: auto; border: 2px solid #007cba; border-radius: 4px;" />
                    <div class="swpd-design-badge-small" style="position: absolute; top: -5px; right: -5px; background: #007cba; color: white; border-radius: 50%; width: 16px; height: 16px; display: flex; align-items: center; justify-content: center; font-size: 10px;">🎨</div>
                </div>',
                esc_url( $preview ),
                esc_attr( $product_name . ' - Custom Design' )
            );

            return $custom_thumbnail;
        }

        return $image;
    }

    /**
     * Display custom design meta in order
     */
    public function display_order_item_meta( $html, $item, $args ) {
        $design_data = $item->get_meta( '_swpd_design_data' );
        $design_timestamp = $item->get_meta( '_swpd_design_timestamp' );
        
        if ( ! empty( $design_data ) ) {
            $html .= '<div class="swpd-order-design-meta">';
            $html .= '<strong>' . __( 'Custom Design:', 'swpd' ) . '</strong> ';
            $html .= '<span class="design-indicator">🎨 ' . __( 'Yes', 'swpd' ) . '</span>';
            
            if ( ! empty( $design_timestamp ) ) {
                $html .= '<br><small>' . __( 'Designed on:', 'swpd' ) . ' ' . wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $design_timestamp ) . '</small>';
            }
            
            $html .= '</div>';
        }

        return $html;
    }

    /**
     * AJAX handler for getting product variants
     */
    public function ajax_get_product_variants() {
        check_ajax_referer( 'swpd_designer_nonce', 'nonce' );

        $product_id = intval( $_POST['product_id'] );
        $product = wc_get_product( $product_id );

        if ( ! $product ) {
            wp_send_json_error( 'Product not found' );
        }

        $variants = $this->get_product_variants( $product );
        wp_send_json_success( $variants );
    }

    /**
     * Add body class on relevant pages
     */
    public function add_body_class( $classes ) {
        global $product;
        
        if ( is_product() && $product && get_post_meta( $product->get_id(), '_swpd_design_enabled', true ) ) {
            $classes[] = 'swpd-designer-enabled';
        }

        if ( is_cart() || is_checkout() ) {
            // Check if cart has custom designs
            if ( WC()->cart && ! WC()->cart->is_empty() ) {
                foreach ( WC()->cart->get_cart() as $cart_item ) {
                    if ( isset( $cart_item['custom_design_preview'] ) ) {
                        $classes[] = 'swpd-has-custom-designs';
                        break;
                    }
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

        // Add variation handling script
        wp_add_inline_script( 'swpd-enhanced-designer-fixed', '
            jQuery(document).ready(function($) {
                $(".variations_form").on("show_variation", function(event, variation) {
                    // Trigger custom event for designer
                    $(document).trigger("swpd:variant:changed", variation);
                });
                
                $(".variations_form").on("hide_variation", function() {
                    $(document).trigger("swpd:variant:changed", null);
                });
            });
        ' );
    }

    /**
     * Debug cart data - only in debug mode
     */
    public function debug_cart_data() {
        if ( ! is_cart() || ! current_user_can( 'manage_options' ) ) {
            return;
        }

        if ( WC()->cart && ! WC()->cart->is_empty() ) {
            echo '<div style="display: none;" id="swpd-debug-data">';
            echo '<h4>SWPD Debug - Cart Data</h4>';
            
            foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                if ( isset( $cart_item['custom_design_preview'] ) ) {
                    echo '<div>';
                    echo '<strong>Item:</strong> ' . $cart_item['data']->get_name() . '<br>';
                    echo '<strong>Key:</strong> ' . $cart_item_key . '<br>';
                    echo '<strong>Has Preview:</strong> ' . ( ! empty( $cart_item['custom_design_preview'] ) ? 'Yes' : 'No' ) . '<br>';
                    echo '<strong>Preview Length:</strong> ' . strlen( $cart_item['custom_design_preview'] ) . '<br>';
                    echo '<strong>Valid Base64:</strong> ' . ( $this->is_valid_base64_image( $cart_item['custom_design_preview'] ) ? 'Yes' : 'No' ) . '<br>';
                    echo '<hr>';
                    echo '</div>';
                }
            }
            
            echo '</div>';
        }
    }
}