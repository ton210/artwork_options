<?php
/**
 * Plugin Name:       MunchMakers Custom Request
 * Plugin URI:        https://munchmakers.com
 * Description:       Adds "Request a Custom Design" buttons via shortcodes, an inline request form on the tracker page, customer accounts, tracking, admin dashboard, Slack, and logging.
 * Version:           1.2.0
 * Author:            Your Name / MunchMakers
 * Author URI:        https://munchmakers.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       munchmakers-custom-request
 * Domain Path:       /languages
 * Requires at least: 5.5
 * Requires PHP:      7.4
 * WC requires at least: 3.0
 * WC tested up to:   latest
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Define plugin constants
define( 'MCR_VERSION', '1.2.0' );
define( 'MCR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MCR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'MCR_POST_TYPE', 'mcr_request' );

/**
 * WP Rocket: Exclude our CSS/JS from optimization on all pages
 */
function mcr_exclude_wp_rocket_assets() {
    // Exclude CSS file from minify/combine
    add_filter( 'rocket_exclude_css', function( $excluded ) {
        $excluded[] = MCR_PLUGIN_URL . 'public/assets/css/munchmakers-custom-request-public.css';
        return $excluded;
    }, 1 );

    // Exclude JS file from minify/combine
    add_filter( 'rocket_exclude_js', function( $excluded ) {
        $excluded[] = MCR_PLUGIN_URL . 'public/assets/js/munchmakers-custom-request-public.js';
        return $excluded;
    }, 1 );

    // Exclude our script handle from Delay JS execution
    add_filter( 'rocket_delay_js_exclusions', function( $exclusions ) {
        $exclusions[] = 'munchmakers-custom-request';
        return $exclusions;
    }, 1 );

    // Exclude our script handle from Deferred JS (Defer JS) if using Defer feature
    add_filter( 'rocket_defer_js_excluded_handles', function( $handles ) {
        $handles[] = 'munchmakers-custom-request';
        return $handles;
    }, 1 );
}
add_action( 'init', 'mcr_exclude_wp_rocket_assets', 1 );

// Helper functions
require_once MCR_PLUGIN_DIR . 'includes/munchmakers-cr-core-functions.php';
require_once MCR_PLUGIN_DIR . 'includes/munchmakers-cr-slack-functions.php';
require_once MCR_PLUGIN_DIR . 'includes/munchmakers-cr-email-functions.php';
require_once MCR_PLUGIN_DIR . 'includes/munchmakers-cr-user-functions.php';
require_once MCR_PLUGIN_DIR . 'includes/munchmakers-cr-woocommerce-integration.php';

// Main plugin class
require_once MCR_PLUGIN_DIR . 'includes/class-munchmakers-custom-request.php';

/**
 * Begins execution of the plugin.
 */
function run_munchmakers_custom_request() {
    $plugin = new MunchMakers_Custom_Request();
    $plugin->run();
}
run_munchmakers_custom_request();

/**
 * Runs on plugin activation.
 * Sets a transient to flush rewrite rules on the next admin load.
 * Creates necessary directories.
 */
function mcr_activate() {
    set_transient( 'mcr_activated_flush_rewrite', true, 30 ); // Schedule rewrite flush

    if ( class_exists( 'MCR_WooCommerce_Integration' ) ) {
        if ( method_exists( 'MCR_WooCommerce_Integration', 'add_endpoints_static' ) ) {
            MCR_WooCommerce_Integration::add_endpoints_static();
        }
    }

    $upload_dir = wp_upload_dir();
    $dirs_to_create = array(
        $upload_dir['basedir'] . '/munchmakers_requests',
        $upload_dir['basedir'] . '/munchmakers_proofs',
        $upload_dir['basedir'] . '/munchmakers_logs'
    );

    foreach ( $dirs_to_create as $dir ) {
        if ( ! is_dir( $dir ) ) {
            wp_mkdir_p( $dir );
        }
    }
    if ( function_exists( 'mcr_log' ) ) {
        mcr_log( 'Plugin activated (v' . MCR_VERSION . '). Rewrite flush scheduled. Directories checked/created.' );
    }
}
register_activation_hook( __FILE__, 'mcr_activate' );

/**
 * Runs on plugin deactivation.
 * Flushes rewrite rules to remove CPT/endpoint rules.
 */
function mcr_deactivate() {
    flush_rewrite_rules();
    delete_transient( 'mcr_activated_flush_rewrite' );
    if ( function_exists( 'mcr_log' ) ) {
        mcr_log( 'Plugin deactivated (v' . MCR_VERSION . '). Rewrite rules flushed.' );
    }
}
register_deactivation_hook( __FILE__, 'mcr_deactivate' );
