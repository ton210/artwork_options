<?php
/**
 * Main Plugin Class
 *
 * @package MunchMakers_Product_Customizer
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'MunchMakers_Product_Customizer' ) ) {

    class MunchMakers_Product_Customizer {

        private static $instance;
        
        public $admin;
        public $frontend;
        public $ajax;
        public $pricing;
        public $cart;

        public static function get_instance() {
            if ( null === self::$instance ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        private function __construct() {
            $this->init_hooks();
            $this->init_classes();
        }

        private function init_hooks() {
            add_action( 'init', array( $this, 'load_textdomain' ) );
        }

        private function init_classes() {
            $this->admin    = new MunchMakers_Admin();
            $this->pricing  = new MunchMakers_Pricing();
            $this->cart     = new MunchMakers_Cart( $this->pricing );
            $this->ajax     = new MunchMakers_Ajax( $this->pricing );
            
            // Only initialize frontend if the customizer is active
            if ( get_option( 'munchmakers_artwork_options_active' ) ) {
                $this->frontend = new MunchMakers_Frontend( $this->pricing );
            }
        }

        public function load_textdomain() {
            load_plugin_textdomain(
                'munchmakers-product-customizer',
                false,
                dirname( plugin_basename( MUNCHMAKERS_PLUGIN_FILE ) ) . '/languages'
            );
        }

        public static function activate() {
            // Activation tasks if needed
            flush_rewrite_rules();
        }

        public static function deactivate() {
            // Deactivation tasks if needed
            flush_rewrite_rules();
        }
    }
}