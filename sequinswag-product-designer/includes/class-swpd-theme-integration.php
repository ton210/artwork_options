<?php
/**
 * SWPD Theme Integration Class
 * Enhanced integration for WoodMart and other themes
 *
 * @package SWPD
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class SWPD_Theme_Integration
 *
 * Handles theme-specific enhancements and integrations
 */
class SWPD_Theme_Integration {

    /**
     * Logger instance
     *
     * @var SWPD_Logger
     */
    private $logger;

    /**
     * Current theme name
     *
     * @var string
     */
    private $theme_name;

    /**
     * Constructor
     *
     * @param SWPD_Logger $logger
     */
    public function __construct( $logger ) {
        $this->logger = $logger;
        $this->theme_name = get_template();
    }

    /**
     * Initialize theme integration
     */
    public function init() {
        // General enhancements
        add_filter( 'woocommerce_cart_item_class', array( $this, 'enhance_cart_item_classes' ), 10, 3 );
        add_filter( 'woocommerce_mini_cart_item_class', array( $this, 'enhance_mini_cart_item_classes' ), 10, 3 );
        
        // Theme-specific enhancements
        switch ( $this->theme_name ) {
            case 'woodmart':
                $this->init_woodmart_enhancements();
                break;
            
            default:
                $this->init_default_theme_enhancements();
                break;
        }
        
        // Add CSS variables for theming
        add_action( 'wp_head', array( $this, 'add_css_variables' ) );
        
        // Enhance cart and checkout pages
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_theme_integration_assets' ) );
        
        // Add structured data for custom designs
        add_action( 'wp_footer', array( $this, 'add_structured_data' ) );
    }

    /**
     * Initialize WoodMart specific enhancements
     */
    private function init_woodmart_enhancements() {
        // Enhanced cart item display
        add_filter( 'woocommerce_cart_item_thumbnail', array( $this, 'woodmart_enhance_cart_thumbnail' ), 15, 3 );
        
        // Enhanced checkout display
        add_action( 'woocommerce_checkout_before_order_review', array( $this, 'woodmart_checkout_custom_design_summary' ) );
        
        // Enhanced mini-cart
        add_action( 'woocommerce_widget_shopping_cart_before_buttons', array( $this, 'woodmart_mini_cart_design_summary' ) );
        
        // Add WoodMart specific body classes
        add_filter( 'body_class', array( $this, 'woodmart_body_classes' ) );
        
        // Enhance WoodMart's quick view for custom designs
        add_filter( 'woodmart_product_quick_view_content', array( $this, 'woodmart_quick_view_custom_design' ), 10, 2 );
    }

    /**
     * Initialize default theme enhancements
     */
    private function init_default_theme_enhancements() {
        // Basic enhancements for all themes
        add_filter( 'woocommerce_cart_item_thumbnail', array( $this, 'enhance_cart_thumbnail' ), 15, 3 );
        add_action( 'woocommerce_checkout_before_order_review', array( $this, 'checkout_custom_design_summary' ) );
    }

    /**
     * Enhance cart item classes
     */
    public function enhance_cart_item_classes( $class, $cart_item, $cart_item_key ) {
        if ( isset( $cart_item['custom_design_preview'] ) && ! empty( $cart_item['custom_design_preview'] ) ) {
            $class .= ' swpd-has-custom-design sequin-custom-item';
            
            // Add design complexity class
            if ( isset( $cart_item['custom_design_complexity'] ) ) {
                $class .= ' design-complexity-' . sanitize_html_class( $cart_item['custom_design_complexity'] );
            }
        }
        
        return $class;
    }

    /**
     * Enhance mini cart item classes
     */
    public function enhance_mini_cart_item_classes( $class, $cart_item, $cart_item_key ) {
        if ( isset( $cart_item['custom_design_preview'] ) && ! empty( $cart_item['custom_design_preview'] ) ) {
            $class .= ' swpd-mini-cart-custom-design';
        }
        
        return $class;
    }

    /**
     * WoodMart enhanced cart thumbnail
     */
    public function woodmart_enhance_cart_thumbnail( $thumbnail, $cart_item, $cart_item_key ) {
        if ( isset( $cart_item['custom_design_preview'] ) && ! empty( $cart_item['custom_design_preview'] ) ) {
            $preview_url = $cart_item['custom_design_preview'];
            $product_name = $cart_item['data']->get_name();
            
            // Enhanced thumbnail with WoodMart styling
            $thumbnail = sprintf(
                '<div class="swpd-woodmart-thumbnail-container">
                    <img src="%s" alt="%s" class="attachment-shop_thumbnail size-shop_thumbnail swpd-custom-design-thumbnail" loading="lazy" />
                    <div class="swpd-design-overlay">
                        <div class="swpd-design-badge">
                            <svg class="design-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                            <span>%s</span>
                        </div>
                    </div>
                </div>',
                esc_url( $preview_url ),
                esc_attr( $product_name . ' - Custom Design' ),
                esc_html__( 'Custom', 'swpd' )
            );
        }
        
        return $thumbnail;
    }

    /**
     * Enhanced cart thumbnail for default themes
     */
    public function enhance_cart_thumbnail( $thumbnail, $cart_item, $cart_item_key ) {
        if ( isset( $cart_item['custom_design_preview'] ) && ! empty( $cart_item['custom_design_preview'] ) ) {
            $preview_url = $cart_item['custom_design_preview'];
            $product_name = $cart_item['data']->get_name();
            
            $thumbnail = sprintf(
                '<div class="swpd-custom-thumbnail-wrapper">
                    <img src="%s" alt="%s" class="swpd-custom-design-thumbnail" loading="lazy" />
                    <span class="swpd-custom-design-badge">%s</span>
                </div>',
                esc_url( $preview_url ),
                esc_attr( $product_name . ' - Custom Design' ),
                esc_html__( 'Custom Design', 'swpd' )
            );
        }
        
        return $thumbnail;
    }

    /**
     * WoodMart checkout custom design summary
     */
    public function woodmart_checkout_custom_design_summary() {
        if ( ! WC()->cart || WC()->cart->is_empty() ) {
            return;
        }
        
        $custom_items = array();
        foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
            if ( isset( $cart_item['custom_design_preview'] ) && ! empty( $cart_item['custom_design_preview'] ) ) {
                $custom_items[] = array(
                    'name' => $cart_item['data']->get_name(),
                    'preview' => $cart_item['custom_design_preview'],
                    'quantity' => $cart_item['quantity']
                );
            }
        }
        
        if ( ! empty( $custom_items ) ) {
            echo '<div class="swpd-checkout-design-summary woodmart-enhanced">';
            echo '<h4>' . esc_html__( 'Custom Designs in Your Order', 'swpd' ) . '</h4>';
            echo '<div class="swpd-design-summary-grid">';
            
            foreach ( $custom_items as $item ) {
                echo '<div class="swpd-design-summary-item">';
                echo '<img src="' . esc_url( $item['preview'] ) . '" alt="' . esc_attr( $item['name'] ) . '" class="design-preview-thumb" loading="lazy" />';
                echo '<div class="item-details">';
                echo '<span class="item-name">' . esc_html( $item['name'] ) . '</span>';
                echo '<span class="item-qty">Qty: ' . esc_html( $item['quantity'] ) . '</span>';
                echo '</div>';
                echo '</div>';
            }
            
            echo '</div>';
            echo '</div>';
        }
    }

    /**
     * Default theme checkout custom design summary
     */
    public function checkout_custom_design_summary() {
        // Simplified version for non-WoodMart themes
        $custom_count = 0;
        if ( WC()->cart && ! WC()->cart->is_empty() ) {
            foreach ( WC()->cart->get_cart() as $cart_item ) {
                if ( isset( $cart_item['custom_design_preview'] ) && ! empty( $cart_item['custom_design_preview'] ) ) {
                    $custom_count++;
                }
            }
        }
        
        if ( $custom_count > 0 ) {
            echo '<div class="swpd-checkout-notice">';
            echo '<p>' . sprintf( _n( 'Your order includes %d custom designed item.', 'Your order includes %d custom designed items.', $custom_count, 'swpd' ), $custom_count ) . '</p>';
            echo '</div>';
        }
    }

    /**
     * WoodMart mini cart design summary
     */
    public function woodmart_mini_cart_design_summary() {
        if ( ! WC()->cart || WC()->cart->is_empty() ) {
            return;
        }
        
        $custom_count = 0;
        foreach ( WC()->cart->get_cart() as $cart_item ) {
            if ( isset( $cart_item['custom_design_preview'] ) && ! empty( $cart_item['custom_design_preview'] ) ) {
                $custom_count++;
            }
        }
        
        if ( $custom_count > 0 ) {
            echo '<div class="swpd-mini-cart-design-notice">';
            echo '<span class="design-icon">🎨</span>';
            echo '<span>' . sprintf( _n( '%d custom design', '%d custom designs', $custom_count, 'swpd' ), $custom_count ) . '</span>';
            echo '</div>';
        }
    }

    /**
     * Add WoodMart specific body classes
     */
    public function woodmart_body_classes( $classes ) {
        $classes[] = 'swpd-woodmart-enhanced';
        
        if ( is_cart() || is_checkout() ) {
            $classes[] = 'swpd-has-enhanced-cart';
        }
        
        return $classes;
    }

    /**
     * Add CSS variables for theming
     */
    public function add_css_variables() {
        if ( ! is_cart() && ! is_checkout() && ! is_account_page() ) {
            return;
        }
        
        $primary_color = get_theme_mod( 'primary_color', '#007cba' );
        $accent_color = get_theme_mod( 'accent_color', '#f8f9fa' );
        
        echo '<style>:root {';
        echo '--swpd-primary-color: ' . esc_attr( $primary_color ) . ';';
        echo '--swpd-accent-color: ' . esc_attr( $accent_color ) . ';';
        echo '--swpd-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);';
        echo '--swpd-border-radius: 6px;';
        echo '}</style>';
    }

    /**
     * Enqueue theme integration assets
     */
    public function enqueue_theme_integration_assets() {
        if ( is_cart() || is_checkout() ) {
            wp_add_inline_style( 'woocommerce-general', $this->get_inline_css() );
            wp_add_inline_script( 'jquery', $this->get_inline_js() );
        }
    }

    /**
     * Get inline CSS for theme integration
     */
    private function get_inline_css() {
        $css = '
        .swpd-custom-thumbnail-wrapper {
            position: relative;
            display: inline-block;
        }
        
        .swpd-design-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--swpd-primary-color, #007cba);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: bold;
            border: 2px solid white;
            box-shadow: var(--swpd-shadow, 0 2px 8px rgba(0,0,0,0.1));
        }
        
        .swpd-checkout-design-summary {
            background: var(--swpd-accent-color, #f8f9fa);
            border: 1px solid #dee2e6;
            border-radius: var(--swpd-border-radius, 6px);
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .swpd-design-summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }
        
        .swpd-design-summary-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        
        .design-preview-thumb {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: var(--swpd-border-radius, 6px);
            border: 2px solid var(--swpd-primary-color, #007cba);
        }
        
        .swpd-mini-cart-design-notice {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 8px 15px;
            background: var(--swpd-accent-color, #f8f9fa);
            border-radius: var(--swpd-border-radius, 6px);
            margin-bottom: 10px;
            font-size: 12px;
            color: var(--swpd-primary-color, #007cba);
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .swpd-design-summary-grid {
                grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
            }
            
            .design-preview-thumb {
                width: 40px;
                height: 40px;
            }
        }
        ';
        
        return $css;
    }

    /**
     * Get inline JavaScript for theme integration
     */
    private function get_inline_js() {
        return '
        jQuery(document).ready(function($) {
            // Add enhanced hover effects
            $(".swpd-has-custom-design").hover(
                function() { $(this).addClass("swpd-hover-active"); },
                function() { $(this).removeClass("swpd-hover-active"); }
            );
            
            // Smooth animations for custom design elements
            $(".swpd-design-badge, .swpd-custom-design-badge").each(function() {
                $(this).css("animation", "swpdBadgePulse 2s ease-in-out infinite");
            });
            
            // Add loading states for design-related actions
            $(document).on("click", ".swpd-edit-design-button", function() {
                $(this).addClass("loading").append("<span class=\\"spinner\\"></span>");
            });
        });
        ';
    }

    /**
     * Add structured data for custom designs
     */
    public function add_structured_data() {
        if ( ! is_cart() && ! is_checkout() ) {
            return;
        }
        
        if ( ! WC()->cart || WC()->cart->is_empty() ) {
            return;
        }
        
        $custom_designs = array();
        foreach ( WC()->cart->get_cart() as $cart_item ) {
            if ( isset( $cart_item['custom_design_preview'] ) && ! empty( $cart_item['custom_design_preview'] ) ) {
                $custom_designs[] = array(
                    '@type' => 'Product',
                    'name' => $cart_item['data']->get_name(),
                    'image' => $cart_item['custom_design_preview'],
                    'additionalProperty' => array(
                        '@type' => 'PropertyValue',
                        'name' => 'customDesign',
                        'value' => 'true'
                    )
                );
            }
        }
        
        if ( ! empty( $custom_designs ) ) {
            echo '<script type="application/ld+json">';
            echo wp_json_encode( array(
                '@context' => 'https://schema.org',
                '@type' => 'ItemList',
                'name' => 'Custom Designed Products',
                'itemListElement' => $custom_designs
            ) );
            echo '</script>';
        }
    }

    /**
     * WoodMart quick view custom design enhancement
     */
    public function woodmart_quick_view_custom_design( $content, $product_id ) {
        // Add custom design indicator in quick view if product supports it
        $design_enabled = get_post_meta( $product_id, '_swpd_design_enabled', true );
        
        if ( $design_enabled ) {
            $design_notice = '<div class="swpd-quick-view-design-notice">';
            $design_notice .= '<span class="design-icon">🎨</span>';
            $design_notice .= '<span>' . esc_html__( 'Customizable Design Available', 'swpd' ) . '</span>';
            $design_notice .= '</div>';
            
            // Insert after product title
            $content = str_replace( '</h1>', '</h1>' . $design_notice, $content );
        }
        
        return $content;
    }
}