<?php
/**
 * AJAX Class
 *
 * @package MunchMakers_Product_Customizer
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MunchMakers_Ajax {

    private $pricing;

    public function __construct( $pricing ) {
        $this->pricing = $pricing;
        
        if ( get_option( 'munchmakers_artwork_options_active' ) ) {
            add_action( 'wp_ajax_munchmakers_get_variation_pricing', array( $this, 'ajax_get_variation_pricing' ) );
            add_action( 'wp_ajax_nopriv_munchmakers_get_variation_pricing', array( $this, 'ajax_get_variation_pricing' ) );
            add_action( 'wp_ajax_munchmakers_add_to_cart', array( $this, 'ajax_add_to_cart' ) );
            add_action( 'wp_ajax_nopriv_munchmakers_add_to_cart', array( $this, 'ajax_add_to_cart' ) );
            add_action( 'wp_ajax_munchmakers_upload_file', array( $this, 'ajax_upload_file' ) );
            add_action( 'wp_ajax_nopriv_munchmakers_upload_file', array( $this, 'ajax_upload_file' ) );
        }
    }

    public function ajax_get_variation_pricing() {
        try {
            check_ajax_referer( 'munchmakers_pricing_nonce', 'nonce' );
            
            $product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
            $variation_id = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;
            
            if ( ! $product_id ) {
                throw new Exception( __( 'Invalid product ID.', 'munchmakers-product-customizer' ) );
            }
            
            // Validate variation belongs to product
            if ( $variation_id > 0 ) {
                $variation = wc_get_product( $variation_id );
                if ( ! $variation || ! $variation instanceof WC_Product_Variation || $variation->get_parent_id() !== $product_id ) {
                    throw new Exception( __( 'Invalid variation.', 'munchmakers-product-customizer' ) );
                }
            }
            
            $pricing_data = $this->pricing->get_initial_pricing_data( $product_id, $variation_id );
            wp_send_json_success( $pricing_data );
            
        } catch ( Exception $e ) {
            wp_send_json_error( array( 'message' => $e->getMessage() ) );
        }
    }

    public function ajax_add_to_cart() {
        try {
            if ( ! check_ajax_referer( 'munchmakers_add_to_cart_nonce', 'nonce', false ) ) {
                throw new Exception( __( 'Nonce verification failed. Please refresh and try again.', 'munchmakers-product-customizer' ) );
            }

            $product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
            $variation_id = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;
            $quantity = isset( $_POST['quantity'] ) ? absint( $_POST['quantity'] ) : 0;

            if ( ! $product_id || ! $quantity ) {
                throw new Exception( __( 'Missing required product data.', 'munchmakers-product-customizer' ) );
            }

            // Debug logging
            error_log('MunchMakers AJAX Add to Cart - POST Data: ' . print_r($_POST, true));

            if ( function_exists( 'WC' ) && WC()->session && ! WC()->session->has_session() ) {
                WC()->session->set_customer_session_cookie( true );
            }

            $product = wc_get_product( $variation_id ?: $product_id );
            if ( ! $product || ! $product->is_purchasable() ) {
                throw new Exception( __( 'This product cannot be purchased.', 'munchmakers-product-customizer' ) );
            }

            // Prepare cart item data with custom meta
            $cart_item_data = array();
            
            // Add artwork option
            if ( ! empty( $_POST['munchmakers_artwork_option'] ) ) {
                $cart_item_data['munchmakers_artwork_option'] = sanitize_text_field( $_POST['munchmakers_artwork_option'] );
                error_log('Adding artwork option: ' . $cart_item_data['munchmakers_artwork_option']);
            }
            
            // Add selected options text
            if ( ! empty( $_POST['munchmakers_selected_options'] ) ) {
                $cart_item_data['munchmakers_selected_options'] = sanitize_text_field( $_POST['munchmakers_selected_options'] );
                error_log('Adding selected options: ' . $cart_item_data['munchmakers_selected_options']);
            }
            
            // Add uploaded files
            if ( isset( $_POST['munchmakers_artwork_file_urls'] ) && ! empty( $_POST['munchmakers_artwork_file_urls'] ) ) {
                $urls_json = stripslashes( $_POST['munchmakers_artwork_file_urls'] );
                $urls = json_decode( $urls_json, true );
                if ( is_array( $urls ) && ! empty( $urls ) ) {
                    $cart_item_data['munchmakers_artwork_file_urls'] = array_map( 'esc_url_raw', $urls );
                    error_log('Adding file URLs: ' . print_r($cart_item_data['munchmakers_artwork_file_urls'], true));
                }
            }

            // Prepare variation data for variable products
            $variation_data = array();
            if ( $variation_id && $product->is_type( 'variation' ) ) {
                foreach ( $_POST as $key => $value ) {
                    if ( strpos( $key, 'attribute_' ) === 0 && ! empty( $value ) ) {
                        $variation_data[ $key ] = sanitize_text_field( $value );
                    }
                }
                error_log('Variation data: ' . print_r($variation_data, true));
            }

            // Force unique cart item if we have custom data
            if ( ! empty( $cart_item_data ) ) {
                $cart_item_data['munchmakers_unique_key'] = md5( microtime() . rand() );
            }

            error_log('Final cart item data before adding to cart: ' . print_r($cart_item_data, true));

            // Add to cart
            $cart_item_key = WC()->cart->add_to_cart( 
                $product_id, 
                $quantity, 
                $variation_id, 
                $variation_data, 
                $cart_item_data 
            );

            if ( $cart_item_key ) {
                // Verify the item was added with our custom data
                $cart_item = WC()->cart->get_cart_item( $cart_item_key );
                error_log('Cart item after adding: ' . print_r($cart_item, true));
                
                wp_send_json_success( array( 
                    'message' => __( 'Product added successfully.', 'munchmakers-product-customizer' ),
                    'cart_item_key' => $cart_item_key,
                    'has_custom_data' => ! empty( $cart_item_data )
                ) );
            } else {
                $notices = wc_get_notices( 'error', true );
                $error_message = ! empty( $notices ) ? wp_strip_all_tags( implode( ' | ', array_column( $notices, 'notice' ) ) ) : __( 'Failed to add product to cart.', 'munchmakers-product-customizer' );
                throw new Exception( $error_message );
            }

        } catch ( Exception $e ) {
            error_log('MunchMakers AJAX Error: ' . $e->getMessage());
            wp_send_json_error( array( 'message' => $e->getMessage() ) );
        }
    }

    public function ajax_upload_file() {
        try {
            check_ajax_referer( 'munchmakers_upload_nonce', 'nonce' );

            if ( ! isset( $_FILES['artwork_file'] ) || $_FILES['artwork_file']['error'] !== UPLOAD_ERR_OK ) {
                throw new Exception( __( 'Upload error or no file provided.', 'munchmakers-product-customizer' ) );
            }

            $file = $_FILES['artwork_file'];

            if ( ! function_exists( 'wp_handle_upload' ) ) {
                require_once( ABSPATH . 'wp-admin/includes/file.php' );
            }
            
            if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
                require_once( ABSPATH . 'wp-admin/includes/image.php' );
            }

            $upload_overrides = array(
                'test_form' => false,
                'mimes'     => array(
                    'jpg|jpeg|jpe' => 'image/jpeg',
                    'png'          => 'image/png',
                    'gif'          => 'image/gif',
                    'webp'         => 'image/webp',
                ),
            );

            $uploaded_file = wp_handle_upload( $file, $upload_overrides );

            if ( isset( $uploaded_file['error'] ) ) {
                throw new Exception( __( 'Upload failed: ', 'munchmakers-product-customizer' ) . $uploaded_file['error'] );
            }

            $attachment = array(
                'guid'           => $uploaded_file['url'],
                'post_mime_type' => $uploaded_file['type'],
                'post_title'     => sanitize_file_name( pathinfo( $uploaded_file['file'], PATHINFO_FILENAME ) ),
                'post_content'   => '',
                'post_status'    => 'inherit',
            );

            $attach_id = wp_insert_attachment( $attachment, $uploaded_file['file'] );

            if ( is_wp_error( $attach_id ) ) {
                throw new Exception( __( 'Failed to create media library attachment.', 'munchmakers-product-customizer' ) );
            }

            wp_update_attachment_metadata( $attach_id, wp_generate_attachment_metadata( $attach_id, $uploaded_file['file'] ) );

            wp_send_json_success( array( 
                'url' => $uploaded_file['url'],
                'attachment_id' => $attach_id 
            ) );

        } catch ( Exception $e ) {
            wp_send_json_error( array( 'message' => $e->getMessage() ) );
        }
    }
}