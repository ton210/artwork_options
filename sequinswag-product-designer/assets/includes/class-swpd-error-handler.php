<?php
/**
 * SWPD Error Handler Class
 * Comprehensive error handling and validation system
 *
 * @package SWPD
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class SWPD_Error_Handler
 *
 * Handles errors, validation, and provides robust error recovery
 */
class SWPD_Error_Handler {

    /**
     * Error log
     *
     * @var array
     */
    private $error_log = array();

    /**
     * Logger instance
     *
     * @var SWPD_Logger
     */
    private $logger;

    /**
     * Constructor
     */
    public function __construct( $logger = null ) {
        $this->logger = $logger;
        $this->init();
    }

    /**
     * Initialize error handling
     */
    public function init() {
        // Set up error handlers
        add_action( 'wp_ajax_swpd_report_js_error', array( $this, 'handle_js_error' ) );
        add_action( 'wp_ajax_nopriv_swpd_report_js_error', array( $this, 'handle_js_error' ) );
        
        // Add error handling scripts
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_error_handling_scripts' ) );
        
        // Load existing error log
        $this->error_log = get_option( 'swpd_error_log', array() );
    }

    /**
     * Sanitize and validate base64 image
     */
    public function sanitize_base64_image( $data ) {
        // Remove any whitespace
        $data = trim( $data );
        
        // Check if it starts with data:image/
        if ( strpos( $data, 'data:image/' ) !== 0 ) {
            return false;
        }
        
        // Extract the MIME type and base64 data
        $parts = explode( ',', $data, 2 );
        if ( count( $parts ) !== 2 ) {
            return false;
        }
        
        $header = $parts[0];
        $base64_data = $parts[1];
        
        // Validate MIME type
        if ( ! preg_match( '/^data:image\/(jpeg|png|gif|webp);base64$/', $header ) ) {
            return false;
        }
        
        // Validate base64 data
        if ( ! base64_decode( $base64_data, true ) ) {
            return false;
        }
        
        // Check size (5MB limit)
        $size = strlen( base64_decode( $base64_data ) );
        if ( $size > 5 * 1024 * 1024 ) {
            return false;
        }
        
        return $data;
    }

    /**
     * Handle JavaScript errors
     */
    public function handle_js_error() {
        if ( ! check_ajax_referer( 'swpd_error_nonce', 'nonce', false ) ) {
            wp_die( 'Security check failed' );
        }
        
        $error_data = array(
            'message' => sanitize_text_field( $_POST['message'] ?? '' ),
            'source' => sanitize_text_field( $_POST['source'] ?? '' ),
            'line' => intval( $_POST['line'] ?? 0 ),
            'column' => intval( $_POST['column'] ?? 0 ),
            'stack' => sanitize_textarea_field( $_POST['stack'] ?? '' ),
            'user_agent' => sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ?? '' ),
            'url' => sanitize_url( $_POST['url'] ?? '' ),
            'timestamp' => current_time( 'mysql' )
        );
        
        $this->log_error( 'JavaScript Error', $error_data, 'js_error' );
        
        wp_send_json_success( array(
            'logged' => true
        ) );
    }

    /**
     * Log error
     */
    public function log_error( $title, $data, $type = 'general' ) {
        $error_entry = array(
            'id' => uniqid(),
            'title' => $title,
            'type' => $type,
            'data' => $data,
            'timestamp' => current_time( 'mysql' ),
            'user_id' => get_current_user_id(),
            'resolved' => false
        );
        
        $this->error_log[] = $error_entry;
        
        // Keep only last 1000 entries
        if ( count( $this->error_log ) > 1000 ) {
            $this->error_log = array_slice( $this->error_log, -1000 );
        }
        
        // Save to database
        update_option( 'swpd_error_log', $this->error_log );
    }

    /**
     * Enqueue error handling scripts
     */
    public function enqueue_error_handling_scripts() {
        if ( ! is_product() && ! is_cart() && ! is_checkout() ) {
            return;
        }
        
        wp_add_inline_script( 'jquery', '
            // Global error handler for JavaScript errors
            window.addEventListener("error", function(e) {
                if (typeof swpdDesignerConfig !== "undefined" && swpdDesignerConfig.debug) {
                    jQuery.post("' . admin_url( 'admin-ajax.php' ) . '", {
                        action: "swpd_report_js_error",
                        nonce: "' . wp_create_nonce( 'swpd_error_nonce' ) . '",
                        message: e.message,
                        source: e.filename,
                        line: e.lineno,
                        column: e.colno,
                        stack: e.error ? e.error.stack : "",
                        url: window.location.href
                    });
                }
            });
        ' );
    }
}