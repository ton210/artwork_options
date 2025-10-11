<?php
/**
 * SWPD Size Selector Class - Optimized for Performance
 *
 * @package SWPD
 * @since 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SWPD_Size_Selector {
    
    private $logger;
    
    public function __construct( $logger = null ) {
        $this->logger = $logger;
    }
    
    public function init() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_size_selector_assets' ) );
        add_action( 'woocommerce_single_product_summary', array( $this, 'render_smart_size_selector' ), 25 );
        add_filter( 'woocommerce_available_variation', array( $this, 'add_size_data_to_variations' ) );
        
        // AJAX handlers for size operations
        add_action( 'wp_ajax_swpd_get_size_chart', array( $this, 'ajax_get_size_chart' ) );
        add_action( 'wp_ajax_nopriv_swpd_get_size_chart', array( $this, 'ajax_get_size_chart' ) );
        add_action( 'wp_ajax_swpd_quick_add_sizes', array( $this, 'ajax_quick_add_sizes' ) );
        add_action( 'wp_ajax_nopriv_swpd_quick_add_sizes', array( $this, 'ajax_quick_add_sizes' ) );
    }
    
    public function enqueue_size_selector_assets() {
        if ( ! is_product() ) {
            return;
        }
        
        wp_enqueue_style( 
            'swpd-size-selector', 
            SWPD_PLUGIN_URL . 'assets/css/size-selector.css', 
            array(), 
            SWPD_VERSION 
        );
        
        wp_enqueue_script( 
            'swpd-size-selector', 
            SWPD_PLUGIN_URL . 'assets/js/size-selector.js', 
            array( 'jquery' ), 
            SWPD_VERSION, 
            true 
        );
        
        wp_localize_script( 'swpd-size-selector', 'swpdSizeSelector', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'swpd_size_nonce' ),
            'product_id' => get_the_ID(),
            'translations' => array(
                'selectSize' => __( 'Select Size', 'swpd' ),
                'sizeChart' => __( 'Size Chart', 'swpd' ),
                'addMultipleSizes' => __( 'Add Multiple Sizes', 'swpd' ),
                'quantityFor' => __( 'Quantity for', 'swpd' ),
                'totalItems' => __( 'Total Items', 'swpd' ),
                'addAllToCart' => __( 'Add All to Cart', 'swpd' ),
                'loading' => __( 'Loading...', 'swpd' ),
                'error' => __( 'Error loading size data', 'swpd' )
            )
        ));
    }
    
    public function render_smart_size_selector() {
        global $product;
        
        if ( ! $product || ! $product->is_type( 'variable' ) ) {
            return;
        }
        
        $size_attribute = $this->get_size_attribute( $product );
        if ( ! $size_attribute ) {
            return;
        }
        
        ?>
        <div id="swpd-smart-size-selector" class="swpd-size-selector-container">
            <div class="size-selector-header">
                <h4><?php esc_html_e( 'Select Your Size', 'swpd' ); ?></h4>
                <div class="size-actions">
                    <button type="button" class="size-chart-btn" id="show-size-chart">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M3 3h18v18H3V3zm2 2v14h14V5H5zm2 2h10v2H7V7zm0 4h7v2H7v-2zm0 4h10v2H7v-2z"/>
                        </svg>
                        <?php esc_html_e( 'Size Chart', 'swpd' ); ?>
                    </button>
                    <button type="button" class="multi-size-btn" id="multi-size-selector">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M3 6h2v2H3V6zm0 5h2v2H3v-2zm0 5h2v2H3v-2zm4-10h12v2H7V6zm0 5h12v2H7v-2zm0 5h12v2H7v-2z"/>
                        </svg>
                        <?php esc_html_e( 'Multiple Sizes', 'swpd' ); ?>
                    </button>
                </div>
            </div>
            
            <div class="size-selector-grid" id="size-grid">
                <?php $this->render_size_options( $product, $size_attribute ); ?>
            </div>
            
            <div class="size-info" id="size-info" style="display: none;">
                <div class="selected-size-preview">
                    <span class="size-label"></span>
                    <span class="size-price"></span>
                    <span class="size-stock"></span>
                </div>
            </div>
        </div>
        
        <!-- Multi-size modal -->
        <div id="multi-size-modal" class="swpd-modal" style="display: none;">
            <div class="modal-content">
                <div class="modal-header">
                    <h3><?php esc_html_e( 'Select Multiple Sizes', 'swpd' ); ?></h3>
                    <button type="button" class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="multi-size-grid" id="multi-size-grid">
                        <!-- Populated via AJAX -->
                    </div>
                    <div class="multi-size-summary">
                        <div class="total-items">
                            <strong><?php esc_html_e( 'Total Items:', 'swpd' ); ?> <span id="total-quantity">0</span></strong>
                        </div>
                        <button type="button" class="btn btn-primary" id="add-all-to-cart" disabled>
                            <?php esc_html_e( 'Add All to Cart', 'swpd' ); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Size chart modal -->
        <div id="size-chart-modal" class="swpd-modal" style="display: none;">
            <div class="modal-content size-chart-content">
                <div class="modal-header">
                    <h3><?php esc_html_e( 'Size Chart', 'swpd' ); ?></h3>
                    <button type="button" class="modal-close">&times;</button>
                </div>
                <div class="modal-body" id="size-chart-content">
                    <!-- Loaded via AJAX -->
                </div>
            </div>
        </div>
        <?php
    }
    
    private function get_size_attribute( $product ) {
        $attributes = $product->get_variation_attributes();
        
        // Common size attribute names
        $size_attrs = array( 'pa_size', 'size', 'pa_sizes', 'sizes' );
        
        foreach ( $size_attrs as $attr ) {
            if ( isset( $attributes[$attr] ) ) {
                return $attr;
            }
        }
        
        // Fallback: look for any attribute containing "size"
        foreach ( $attributes as $attr_name => $values ) {
            if ( stripos( $attr_name, 'size' ) !== false ) {
                return $attr_name;
            }
        }
        
        return false;
    }
    
    private function render_size_options( $product, $size_attribute ) {
        $available_variations = $product->get_available_variations();
        $size_options = array();
        
        foreach ( $available_variations as $variation ) {
            $size = $variation['attributes']['attribute_' . $size_attribute] ?? '';
            if ( empty( $size ) ) continue;
            
            $variation_obj = wc_get_product( $variation['variation_id'] );
            if ( ! $variation_obj ) continue;
            
            $size_options[$size] = array(
                'variation_id' => $variation['variation_id'],
                'price' => $variation_obj->get_price(),
                'regular_price' => $variation_obj->get_regular_price(),
                'sale_price' => $variation_obj->get_sale_price(),
                'stock_status' => $variation_obj->get_stock_status(),
                'stock_quantity' => $variation_obj->get_stock_quantity(),
                'image' => wp_get_attachment_image_url( $variation['image_id'], 'thumbnail' ),
                'in_stock' => $variation_obj->is_in_stock()
            );
        }
        
        // Sort sizes logically (S, M, L, XL, etc.)
        uksort( $size_options, array( $this, 'sort_sizes' ) );
        
        foreach ( $size_options as $size => $data ) {
            $stock_class = $data['in_stock'] ? 'in-stock' : 'out-of-stock';
            $disabled = $data['in_stock'] ? '' : 'disabled';
            
            $price_html = '';
            if ( $data['sale_price'] && $data['sale_price'] < $data['regular_price'] ) {
                $price_html = sprintf(
                    '<span class="price"><del>%s</del> <ins>%s</ins></span>',
                    wc_price( $data['regular_price'] ),
                    wc_price( $data['sale_price'] )
                );
            } else {
                $price_html = sprintf( '<span class="price">%s</span>', wc_price( $data['price'] ) );
            }
            
            $stock_text = '';
            if ( $data['stock_quantity'] && $data['stock_quantity'] > 0 && $data['stock_quantity'] <= 10 ) {
                $stock_text = sprintf( 
                    '<span class="low-stock">%s %s</span>',
                    $data['stock_quantity'],
                    __( 'left', 'swpd' )
                );
            } elseif ( ! $data['in_stock'] ) {
                $stock_text = '<span class="out-of-stock-text">' . __( 'Out of Stock', 'swpd' ) . '</span>';
            }
            
            ?>
            <button type="button" 
                    class="size-option <?php echo esc_attr( $stock_class ); ?>" 
                    data-variation-id="<?php echo esc_attr( $data['variation_id'] ); ?>"
                    data-size="<?php echo esc_attr( $size ); ?>"
                    data-price="<?php echo esc_attr( $data['price'] ); ?>"
                    data-stock="<?php echo esc_attr( $data['in_stock'] ? 'true' : 'false' ); ?>"
                    <?php echo $disabled; ?>>
                <span class="size-label"><?php echo esc_html( strtoupper( $size ) ); ?></span>
                <?php echo $price_html; ?>
                <?php echo $stock_text; ?>
            </button>
            <?php
        }
    }
    
    private function sort_sizes( $a, $b ) {
        // Define size order
        $size_order = array(
            'xs' => 1, 'extra-small' => 1,
            's' => 2, 'small' => 2,
            'm' => 3, 'medium' => 3,
            'l' => 4, 'large' => 4,
            'xl' => 5, 'extra-large' => 5, 'x-large' => 5,
            'xxl' => 6, '2xl' => 6, 'xx-large' => 6,
            'xxxl' => 7, '3xl' => 7, 'xxx-large' => 7,
            '4xl' => 8, '5xl' => 9, '6xl' => 10
        );
        
        $a_lower = strtolower( $a );
        $b_lower = strtolower( $b );
        
        $a_order = $size_order[$a_lower] ?? 999;
        $b_order = $size_order[$b_lower] ?? 999;
        
        if ( $a_order === $b_order ) {
            return strcmp( $a, $b );
        }
        
        return $a_order - $b_order;
    }
    
    public function add_size_data_to_variations( $variation_data ) {
        $variation_id = $variation_data['variation_id'];
        $variation = wc_get_product( $variation_id );
        
        if ( $variation ) {
            $variation_data['size_data'] = array(
                'stock_quantity' => $variation->get_stock_quantity(),
                'low_stock_threshold' => get_option( 'woocommerce_notify_low_stock_amount', 2 )
            );
        }
        
        return $variation_data;
    }
    
    public function ajax_get_size_chart() {
        check_ajax_referer( 'swpd_size_nonce', 'nonce' );
        
        $product_id = intval( $_POST['product_id'] ?? 0 );
        if ( ! $product_id ) {
            wp_send_json_error( 'Invalid product ID' );
        }
        
        // Get size chart data (can be customized per product or use global chart)
        $size_chart = get_post_meta( $product_id, '_swpd_size_chart', true );
        
        if ( empty( $size_chart ) ) {
            // Default size chart
            $size_chart = $this->get_default_size_chart();
        }
        
        wp_send_json_success( array( 'chart' => $size_chart ) );
    }
    
    public function ajax_quick_add_sizes() {
        check_ajax_referer( 'swpd_size_nonce', 'nonce' );
        
        $sizes_data = $_POST['sizes'] ?? array();
        $product_id = intval( $_POST['product_id'] ?? 0 );
        
        if ( ! $product_id || empty( $sizes_data ) ) {
            wp_send_json_error( 'Invalid data' );
        }
        
        $cart_items = array();
        $total_quantity = 0;
        
        foreach ( $sizes_data as $size_data ) {
            $variation_id = intval( $size_data['variation_id'] );
            $quantity = intval( $size_data['quantity'] );
            $design_data = $size_data['design_data'] ?? '';
            
            if ( $quantity > 0 ) {
                $cart_item_data = array();
                if ( ! empty( $design_data ) ) {
                    $cart_item_data['swpd_design_data'] = $design_data;
                }
                
                $cart_key = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, array(), $cart_item_data );
                
                if ( $cart_key ) {
                    $cart_items[] = $cart_key;
                    $total_quantity += $quantity;
                }
            }
        }
        
        if ( ! empty( $cart_items ) ) {
            wp_send_json_success( array( 
                'cart_items' => $cart_items,
                'total_quantity' => $total_quantity,
                'message' => sprintf( 
                    __( 'Added %d items to cart', 'swpd' ), 
                    $total_quantity 
                )
            ));
        } else {
            wp_send_json_error( 'Failed to add items to cart' );
        }
    }
    
    private function get_default_size_chart() {
        return array(
            'title' => __( 'Size Chart', 'swpd' ),
            'measurements' => array(
                array(
                    'size' => 'S',
                    'chest' => '36"',
                    'length' => '28"',
                    'sleeve' => '8.5"'
                ),
                array(
                    'size' => 'M',
                    'chest' => '40"',
                    'length' => '29"',
                    'sleeve' => '9"'
                ),
                array(
                    'size' => 'L',
                    'chest' => '44"',
                    'length' => '30"',
                    'sleeve' => '9.5"'
                ),
                array(
                    'size' => 'XL',
                    'chest' => '48"',
                    'length' => '31"',
                    'sleeve' => '10"'
                ),
                array(
                    'size' => 'XXL',
                    'chest' => '52"',
                    'length' => '32"',
                    'sleeve' => '10.5"'
                )
            ),
            'notes' => array(
                __( 'All measurements are in inches', 'swpd' ),
                __( 'Measurements are taken laying flat', 'swpd' ),
                __( 'For best fit, compare to a similar garment', 'swpd' )
            )
        );
    }
}