<?php
/**
 * Plugin Name:         MunchMakers Product Page Customizer
 * Description:         Integrates a multi-step variation, quantity, and artwork selection module on WooCommerce product pages, compatible with Zakeke.
 * Version:             4.5
 * Author:              Gemini for MunchMakers
 * Author URI:          https://munchmakers.com
 * Text Domain:         munchmakers-product-customizer
 * Domain Path:         /languages
 * Requires at least:   5.5
 * Tested up to:        6.5
 * WC requires at least: 5.0
 * WC tested up to:     8.9
 * Requires PHP:        7.4
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
define( 'MUNCHMAKERS_PLUGIN_VERSION', '4.5' );
define( 'MUNCHMAKERS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MUNCHMAKERS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'MUNCHMAKERS_PLUGIN_FILE', __FILE__ );

// Function to check if required files exist
function munchmakers_check_files() {
    $required_files = array(
        MUNCHMAKERS_PLUGIN_DIR . 'includes/class-munchmakers-product-customizer.php',
        MUNCHMAKERS_PLUGIN_DIR . 'includes/class-munchmakers-admin.php',
        MUNCHMAKERS_PLUGIN_DIR . 'includes/class-munchmakers-frontend.php',
        MUNCHMAKERS_PLUGIN_DIR . 'includes/class-munchmakers-ajax.php',
        MUNCHMAKERS_PLUGIN_DIR . 'includes/class-munchmakers-pricing.php',
        MUNCHMAKERS_PLUGIN_DIR . 'includes/class-munchmakers-cart.php'
    );
    
    foreach ( $required_files as $file ) {
        if ( ! file_exists( $file ) ) {
            add_action( 'admin_notices', function() use ( $file ) {
                echo '<div class="error"><p><strong>MunchMakers Error:</strong> Required file missing: ' . esc_html( basename( $file ) ) . '</p></div>';
            });
            return false;
        }
    }
    return true;
}

// Only load if files exist
if ( ! munchmakers_check_files() ) {
    return;
}

// Include required files
require_once MUNCHMAKERS_PLUGIN_DIR . 'includes/class-munchmakers-product-customizer.php';
require_once MUNCHMAKERS_PLUGIN_DIR . 'includes/class-munchmakers-admin.php';
require_once MUNCHMAKERS_PLUGIN_DIR . 'includes/class-munchmakers-frontend.php';
require_once MUNCHMAKERS_PLUGIN_DIR . 'includes/class-munchmakers-ajax.php';
require_once MUNCHMAKERS_PLUGIN_DIR . 'includes/class-munchmakers-pricing.php';
require_once MUNCHMAKERS_PLUGIN_DIR . 'includes/class-munchmakers-cart.php';

// Check if WooCommerce is active
function munchmakers_is_woocommerce_active() {
    return class_exists( 'WooCommerce' ) || function_exists( 'WC' );
}

// Admin notice for missing WooCommerce
function munchmakers_woocommerce_missing_notice() {
    echo '<div class="error"><p><strong>MunchMakers Product Customizer</strong> requires WooCommerce to be installed and active.</p></div>';
}

// Initialize the plugin
function munchmakers_product_customizer_init() {
    // Check if WooCommerce is available
    if ( ! munchmakers_is_woocommerce_active() ) {
        add_action( 'admin_notices', 'munchmakers_woocommerce_missing_notice' );
        return;
    }
    
    // Initialize the main class
    if ( class_exists( 'MunchMakers_Product_Customizer' ) ) {
        MunchMakers_Product_Customizer::get_instance();
    } else {
        add_action( 'admin_notices', function() {
            echo '<div class="error"><p><strong>MunchMakers Error:</strong> Main class not found. Please check file structure.</p></div>';
        });
    }
}

// Hook into WordPress
add_action( 'plugins_loaded', 'munchmakers_product_customizer_init', 10 );