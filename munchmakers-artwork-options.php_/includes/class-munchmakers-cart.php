<?php
/**
 * Cart Class
 *
 * @package MunchMakers_Product_Customizer
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MunchMakers_Cart {

    private $pricing;

    public function __construct( $pricing ) {
        $this->pricing = $pricing;
        
        // Always add these hooks, not just when customizer is active
        add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_custom_data_to_cart' ), 10, 3 );
        add_filter( 'woocommerce_get_item_data', array( $this, 'display_custom_data_in_cart' ), 10, 2 );
        add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'save_custom_data_to_order_meta' ), 10, 4 );
        add_action( 'woocommerce_before_calculate_totals', array( $this, 'apply_custom_price_to_cart' ), 20, 1 );
        add_filter( 'woocommerce_cart_item_price', array( $this, 'display_correct_cart_item_price' ), 10, 3 );
        
        // Add hooks for better cart display
        add_filter( 'woocommerce_cart_item_name', array( $this, 'modify_cart_item_name' ), 10, 3 );
    }

    public function add_custom_data_to_cart( $cart_item_data, $product_id, $variation_id ) {
        // Debug: Log what we're receiving
        error_log('MunchMakers POST Data: ' . print_r($_POST, true));

        // Add artwork option
        if ( ! empty( $_POST['munchmakers_artwork_option'] ) ) {
            $cart_item_data['munchmakers_artwork_option'] = sanitize_text_field( $_POST['munchmakers_artwork_option'] );
            error_log('Added artwork option: ' . $cart_item_data['munchmakers_artwork_option']);
        }

        // Add selected variation options text
        if ( ! empty( $_POST['munchmakers_selected_options'] ) ) {
            $cart_item_data['munchmakers_selected_options'] = sanitize_text_field( $_POST['munchmakers_selected_options'] );
            error_log('Added selected options: ' . $cart_item_data['munchmakers_selected_options']);
        }

        // Add uploaded artwork files
        if ( isset( $_POST['munchmakers_artwork_file_urls'] ) && ! empty( $_POST['munchmakers_artwork_file_urls'] ) ) {
            $urls = json_decode( stripslashes( $_POST['munchmakers_artwork_file_urls'] ), true );
            if ( is_array( $urls ) ) {
                $cart_item_data['munchmakers_artwork_file_urls'] = array_map( 'esc_url_raw', $urls );
                error_log('Added file URLs: ' . print_r($cart_item_data['munchmakers_artwork_file_urls'], true));
            }
        }

        // Force cart item to be unique if it has custom data
        if ( ! empty( $cart_item_data ) ) {
            $cart_item_data['munchmakers_unique_key'] = md5( microtime() . rand() );
        }

        error_log('Final cart item data: ' . print_r($cart_item_data, true));
        return $cart_item_data;
    }

    public function display_custom_data_in_cart( $item_data, $cart_item ) {
        // Debug: Log what we're receiving
        error_log('MunchMakers Cart Item Data: ' . print_r($cart_item, true));

        // Display selected variation options
        if ( ! empty( $cart_item['munchmakers_selected_options'] ) ) {
            $selection_text = str_replace( 'Current Selection: ', '', $cart_item['munchmakers_selected_options'] );
            $item_data[] = array(
                'key'     => __( 'Configuration', 'munchmakers-product-customizer' ),
                'value'   => esc_html( $selection_text ),
                'display' => '<strong>' . esc_html( $selection_text ) . '</strong>',
            );
        }

        // Display artwork choice
        if ( ! empty( $cart_item['munchmakers_artwork_option'] ) ) {
            $artwork_label = '';
            switch ( $cart_item['munchmakers_artwork_option'] ) {
                case 'Upload Image':
                    $artwork_label = __( 'Custom Artwork Uploaded', 'munchmakers-product-customizer' );
                    break;
                case 'Contact Us':
                    $artwork_label = __( 'Design Service Required', 'munchmakers-product-customizer' );
                    break;
                case 'After Order':
                    $artwork_label = __( 'Artwork to be provided after order', 'munchmakers-product-customizer' );
                    break;
                default:
                    $artwork_label = esc_html( $cart_item['munchmakers_artwork_option'] );
            }
            
            $item_data[] = array(
                'key'     => __( 'Customization Method', 'munchmakers-product-customizer' ),
                'value'   => $artwork_label,
                'display' => '<span class="munchmakers-artwork-method">' . $artwork_label . '</span>',
            );
        }

        // Display uploaded files with download links
        if ( ! empty( $cart_item['munchmakers_artwork_file_urls'] ) && is_array( $cart_item['munchmakers_artwork_file_urls'] ) ) {
            $links = array();
            foreach ( $cart_item['munchmakers_artwork_file_urls'] as $index => $url ) {
                $filename = basename( $url );
                // Create a more user-friendly filename if it's a WordPress upload
                if ( strpos( $filename, '-' ) !== false ) {
                    $filename_parts = explode( '-', $filename );
                    if ( count( $filename_parts ) > 1 ) {
                        // Remove timestamp-like parts and keep meaningful name
                        $clean_name = end( $filename_parts );
                        if ( strlen( $clean_name ) > 3 ) {
                            $filename = $clean_name;
                        }
                    }
                }
                
                $links[] = sprintf(
                    '<a href="%s" target="_blank" rel="noopener" class="munchmakers-file-link" style="display: inline-flex; align-items: center; gap: 5px; padding: 5px 10px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; text-decoration: none; color: #495057; margin: 2px 5px 2px 0; font-size: 12px;">
                        <span style="font-size: 14px;">📎</span>
                        <span>%s</span>
                        <span style="font-size: 10px; color: #6c757d;">↗</span>
                    </a>',
                    esc_url( $url ),
                    esc_html( $filename )
                );
            }
            
            $item_data[] = array(
                'key'     => __( 'Artwork Files', 'munchmakers-product-customizer' ),
                'value'   => implode( '', $links ),
                'display' => '<div class="munchmakers-artwork-files">' . implode( '', $links ) . '</div>',
            );
        }

        return $item_data;
    }

    /**
     * Modify cart item name to include variation attributes
     */
    public function modify_cart_item_name( $name, $cart_item, $cart_item_key ) {
        $product = $cart_item['data'];
        
        // For variable products, add variation attributes to the name
        if ( $product && $product->is_type( 'variation' ) ) {
            $variation_attributes = $product->get_variation_attributes();
            if ( ! empty( $variation_attributes ) ) {
                $attribute_summary = array();
                foreach ( $variation_attributes as $attribute_name => $value ) {
                    if ( ! empty( $value ) ) {
                        // Clean up attribute name
                        $clean_name = str_replace( array( 'attribute_', 'pa_' ), '', $attribute_name );
                        $clean_name = ucwords( str_replace( array( '-', '_' ), ' ', $clean_name ) );
                        
                        // Clean up value
                        $clean_value = ucwords( str_replace( array( '-', '_' ), ' ', $value ) );
                        
                        $attribute_summary[] = $clean_name . ': ' . $clean_value;
                    }
                }
                
                if ( ! empty( $attribute_summary ) ) {
                    $name .= '<br><small class="variation-summary">' . implode( ', ', $attribute_summary ) . '</small>';
                }
            }
        }
        
        return $name;
    }

    public function save_custom_data_to_order_meta( $item, $cart_item_key, $values, $order ) {
        // Save selected variation options
        if ( ! empty( $values['munchmakers_selected_options'] ) ) {
            $selection_text = str_replace( 'Current Selection: ', '', $values['munchmakers_selected_options'] );
            $item->add_meta_data( __( 'Configuration', 'munchmakers-product-customizer' ), $selection_text );
        }

        // Save artwork option
        if ( ! empty( $values['munchmakers_artwork_option'] ) ) {
            $artwork_label = '';
            switch ( $values['munchmakers_artwork_option'] ) {
                case 'Upload Image':
                    $artwork_label = __( 'Custom Artwork Uploaded', 'munchmakers-product-customizer' );
                    break;
                case 'Contact Us':
                    $artwork_label = __( 'Design Service Required', 'munchmakers-product-customizer' );
                    break;
                case 'After Order':
                    $artwork_label = __( 'Artwork to be provided after order', 'munchmakers-product-customizer' );
                    break;
                default:
                    $artwork_label = esc_html( $values['munchmakers_artwork_option'] );
            }
            
            $item->add_meta_data( __( 'Customization Method', 'munchmakers-product-customizer' ), $artwork_label );
        }

        // Save uploaded files with better formatting
        if ( ! empty( $values['munchmakers_artwork_file_urls'] ) && is_array( $values['munchmakers_artwork_file_urls'] ) ) {
            $links = array();
            foreach ( $values['munchmakers_artwork_file_urls'] as $index => $url ) {
                $filename = basename( $url );
                // Create a more user-friendly filename
                if ( strpos( $filename, '-' ) !== false ) {
                    $filename_parts = explode( '-', $filename );
                    if ( count( $filename_parts ) > 1 ) {
                        $clean_name = end( $filename_parts );
                        if ( strlen( $clean_name ) > 3 ) {
                            $filename = $clean_name;
                        }
                    }
                }
                
                $links[] = sprintf(
                    '<a href="%s" target="_blank" rel="noopener" style="display: inline-block; margin: 2px 5px 2px 0; padding: 4px 8px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 3px; text-decoration: none; color: #495057; font-size: 12px;">📎 %s</a>',
                    esc_url( $url ),
                    esc_html( $filename )
                );
            }
            
            $item->add_meta_data( __( 'Artwork Files', 'munchmakers-product-customizer' ), implode( ' ', $links ) );
        }
    }

    public function apply_custom_price_to_cart( $cart ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            return;
        }

        if ( did_action( 'woocommerce_before_calculate_totals' ) > 1 ) {
            return;
        }

        foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
            if ( empty( $cart_item['data'] ) || ! $cart_item['data'] instanceof WC_Product ) {
                continue;
            }

            $product_obj = $cart_item['data'];
            $parent_product = wc_get_product( $cart_item['product_id'] );
            if ( ! $parent_product ) {
                continue;
            }

            $price = $this->pricing->calculate_price_for_quantity(
                $cart_item['quantity'],
                $cart_item['product_id'],
                $cart_item['variation_id'] ?? 0
            );

            if ( is_numeric( $price ) && $price > 0 ) {
                $product_obj->set_price( $price );
                $cart->cart_contents[ $cart_item_key ]['munchmakers_matched_unit_price'] = (float) $price;
            }
        }
    }

    public function display_correct_cart_item_price( $price_html, $cart_item, $cart_item_key ) {
        if ( isset( $cart_item['munchmakers_matched_unit_price'] ) ) {
            return wc_price( $cart_item['munchmakers_matched_unit_price'] );
        }
        return $price_html;
    }
}