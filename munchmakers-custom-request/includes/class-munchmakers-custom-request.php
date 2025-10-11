<?php // munchmakers-custom-request/includes/class-munchmakers-custom-request.php
/**
 * The file that defines the core plugin class.
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @package    MunchMakers_Custom_Request
 * @subpackage MunchMakers_Custom_Request/includes
 * @since      1.0.0
 */
class MunchMakers_Custom_Request { // Ensure this class name matches EXACTLY what's in munchmakers-custom-request.php

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      MunchMakers_Custom_Request_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {
        if ( defined( 'MCR_VERSION' ) ) {
            $this->version = MCR_VERSION;
        } else {
            $this->version = '1.0.1'; // Default version, matches main file
        }
        $this->plugin_name = 'munchmakers-custom-request';

        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_woocommerce_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - MunchMakers_Custom_Request_Loader. Orchestrates the hooks of the plugin.
     * - MunchMakers_Custom_Request_Admin. Defines all hooks for the admin area.
     * - MunchMakers_Custom_Request_Public. Defines all hooks for the public side.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
        require_once MCR_PLUGIN_DIR . 'includes/class-munchmakers-custom-request-loader.php';
        require_once MCR_PLUGIN_DIR . 'admin/class-munchmakers-custom-request-admin.php';
        require_once MCR_PLUGIN_DIR . 'public/class-munchmakers-custom-request-public.php';
        $this->loader = new MunchMakers_Custom_Request_Loader();
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $plugin_admin = new MunchMakers_Custom_Request_Admin( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'init', $plugin_admin, 'register_custom_post_type' );
        $this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'add_meta_boxes' );
        $this->loader->add_action( 'save_post_' . MCR_POST_TYPE, $plugin_admin, 'save_meta_box_data', 10, 2 );
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_settings_page' );
        $this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_admin_assets' );

        $this->loader->add_filter( 'manage_' . MCR_POST_TYPE . '_posts_columns', $plugin_admin, 'set_custom_edit_columns' );
        $this->loader->add_action( 'manage_' . MCR_POST_TYPE . '_posts_custom_column', $plugin_admin, 'custom_column_content', 10, 2 );
        $this->loader->add_filter( 'manage_edit-' . MCR_POST_TYPE . '_sortable_columns', $plugin_admin, 'set_custom_sortable_columns' );

        $this->loader->add_action( 'wp_ajax_mcr_send_proof_email', $plugin_admin, 'ajax_send_proof_email' );
        $this->loader->add_action( 'wp_ajax_mcr_add_internal_note', $plugin_admin, 'ajax_add_internal_note' );
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        $plugin_public = new MunchMakers_Custom_Request_Public( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles_and_scripts' );
        $this->loader->add_action( 'init', $plugin_public, 'register_shortcodes' );
        $this->loader->add_action( 'wp_footer', $plugin_public, 'add_popup_html_if_needed' );

        $this->loader->add_action( 'wp_ajax_mcr_handle_custom_request', $plugin_public, 'handle_ajax_form_submission' );
        $this->loader->add_action( 'wp_ajax_nopriv_mcr_handle_custom_request', $plugin_public, 'handle_ajax_form_submission' );
    }

    /**
     * Register all of the hooks related to WooCommerce integration.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_woocommerce_hooks() {
        if ( class_exists( 'WooCommerce' ) && class_exists('MCR_WooCommerce_Integration') ) {
            $wc_integration = new MCR_WooCommerce_Integration();
            
            // Corrected: Use the instantiated object and its instance method for the loader
            $this->loader->add_action( 'init', $wc_integration, 'add_endpoints' );

            // Corrected: woocommerce_account_menu_items is a filter
            $this->loader->add_filter( 'woocommerce_account_menu_items', $wc_integration, 'add_my_requests_link' );
            
            $this->loader->add_action( 'woocommerce_account_my-custom-requests_endpoint', $wc_integration, 'my_requests_endpoint_content' );
            $this->loader->add_filter( 'query_vars', $wc_integration, 'add_query_vars', 0 );
        }
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
}
