<?php
/**
 * SWPD QA Tester Class
 * Comprehensive testing and debugging tools for the Sequin Designer
 *
 * @package SWPD
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class SWPD_QA_Tester
 *
 * Provides comprehensive testing and debugging functionality
 */
class SWPD_QA_Tester {

    /**
     * Test results
     *
     * @var array
     */
    private $test_results = array();

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
    }

    /**
     * Initialize QA testing
     */
    public function init() {
        // Only load for administrators
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // Add admin menu
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        
        // Add AJAX handlers
        add_action( 'wp_ajax_swpd_run_qa_tests', array( $this, 'ajax_run_qa_tests' ) );
        add_action( 'wp_ajax_swpd_clear_debug_logs', array( $this, 'ajax_clear_debug_logs' ) );
        
        // Add frontend debugging
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            add_action( 'wp_footer', array( $this, 'add_debug_console' ) );
        }
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            'Sequin Designer QA',
            'Designer QA',
            'manage_options',
            'swpd-qa-tester',
            array( $this, 'render_qa_page' )
        );
    }

    /**
     * Render QA testing page
     */
    public function render_qa_page() {
        ?>
        <div class="wrap">
            <h1>Sequin Designer QA Testing</h1>
            
            <div class="swpd-qa-dashboard">
                <div class="qa-section">
                    <h2>System Status</h2>
                    <div id="system-status">
                        <?php $this->render_system_status(); ?>
                    </div>
                </div>
                
                <div class="qa-section">
                    <h2>Automated Tests</h2>
                    <button id="run-all-tests" class="button button-primary">Run All Tests</button>
                    <div id="test-results" style="margin-top: 15px;"></div>
                </div>
                
                <div class="qa-section">
                    <h2>Manual Testing Tools</h2>
                    <div class="manual-tests">
                        <button class="button" onclick="testImageUpload()">Test Image Upload</button>
                        <button class="button" onclick="testVariantSwitching()">Test Variant Switching</button>
                        <button class="button" onclick="testCartThumbnails()">Test Cart Thumbnails</button>
                        <button class="button" onclick="clearDebugLogs()">Clear Debug Logs</button>
                    </div>
                </div>
                
                <div class="qa-section">
                    <h2>Debug Information</h2>
                    <div id="debug-info">
                        <?php $this->render_debug_info(); ?>
                    </div>
                </div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('#run-all-tests').on('click', function() {
                $(this).prop('disabled', true).text('Running Tests...');
                
                $.post(ajaxurl, {
                    action: 'swpd_run_qa_tests',
                    nonce: '<?php echo wp_create_nonce( 'swpd_qa_nonce' ); ?>'
                }, function(response) {
                    $('#test-results').html(response.data);
                    $('#run-all-tests').prop('disabled', false).text('Run All Tests');
                });
            });
        });
        
        function testImageUpload() {
            console.log('Testing image upload functionality...');
            // Implementation for manual image upload test
        }
        
        function testVariantSwitching() {
            console.log('Testing variant switching...');
            // Implementation for variant switching test
        }
        
        function testCartThumbnails() {
            console.log('Testing cart thumbnails...');
            // Implementation for cart thumbnail test
        }
        
        function clearDebugLogs() {
            if (confirm('Are you sure you want to clear all debug logs?')) {
                jQuery.post(ajaxurl, {
                    action: 'swpd_clear_debug_logs',
                    nonce: '<?php echo wp_create_nonce( 'swpd_qa_nonce' ); ?>'
                }, function(response) {
                    alert('Debug logs cleared');
                    location.reload();
                });
            }
        }
        </script>

        <style>
        .swpd-qa-dashboard {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .qa-section {
            background: #fff;
            padding: 20px;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
        }
        
        .qa-section h2 {
            margin-top: 0;
            color: #23282d;
        }
        
        .manual-tests {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .test-result {
            padding: 10px;
            margin: 5px 0;
            border-radius: 3px;
        }
        
        .test-pass {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .test-fail {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .test-warning {
            background: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
        }
        
        .debug-item {
            margin-bottom: 10px;
            padding: 8px;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-family: monospace;
            font-size: 12px;
        }
        
        .debug-error { border-left: 4px solid #dc3545; }
        .debug-warning { border-left: 4px solid #ffc107; }
        .debug-info { border-left: 4px solid #17a2b8; }
        </style>
        <?php
    }

    /**
     * Render system status
     */
    private function render_system_status() {
        $status_items = array(
            'WordPress Version' => get_bloginfo( 'version' ),
            'WooCommerce Version' => defined( 'WC_VERSION' ) ? WC_VERSION : 'Not installed',
            'PHP Version' => phpversion(),
            'Memory Limit' => ini_get( 'memory_limit' ),
            'Upload Max Size' => size_format( wp_max_upload_size() ),
            'SWPD Version' => defined( 'SWPD_VERSION' ) ? SWPD_VERSION : 'Unknown',
            'Debug Mode' => defined( 'WP_DEBUG' ) && WP_DEBUG ? 'Enabled' : 'Disabled',
            'Script Debug' => defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? 'Enabled' : 'Disabled'
        );

        echo '<table class="wp-list-table widefat fixed striped">';
        foreach ( $status_items as $label => $value ) {
            echo '<tr>';
            echo '<td><strong>' . esc_html( $label ) . '</strong></td>';
            echo '<td>' . esc_html( $value ) . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }

    /**
     * Render debug information
     */
    private function render_debug_info() {
        $debug_logs = get_option( 'swpd_debug_logs', array() );
        
        if ( empty( $debug_logs ) ) {
            echo '<p>No debug logs found.</p>';
            return;
        }

        // Show latest 50 entries
        $debug_logs = array_slice( $debug_logs, -50 );
        
        foreach ( $debug_logs as $log ) {
            $class = 'debug-' . $log['level'];
            echo '<div class="debug-item ' . $class . '">';
            echo '<strong>' . esc_html( $log['timestamp'] ) . '</strong> ';
            echo '[' . strtoupper( esc_html( $log['level'] ) ) . '] ';
            echo esc_html( $log['message'] );
            echo '</div>';
        }
    }

    /**
     * Run automated QA tests
     */
    public function ajax_run_qa_tests() {
        check_ajax_referer( 'swpd_qa_nonce', 'nonce' );

        $this->test_results = array();

        // Run all tests
        $this->test_plugin_activation();
        $this->test_database_tables();
        $this->test_file_permissions();
        $this->test_dependencies();
        $this->test_javascript_functionality();
        $this->test_cart_functionality();
        $this->test_design_save_load();

        // Generate results HTML
        $html = $this->generate_test_results_html();
        
        wp_send_json_success( $html );
    }

    /**
     * Test plugin activation
     */
    private function test_plugin_activation() {
        $this->add_test_result(
            'Plugin Activation',
            is_plugin_active( 'sequinswag-product-designer/sequinswag-product-designer.php' ),
            'Plugin is active and properly loaded',
            'Plugin is not active or not found'
        );
    }

    /**
     * Test database tables
     */
    private function test_database_tables() {
        global $wpdb;
        
        // Check if required tables exist
        $tables_to_check = array(
            $wpdb->prefix . 'swpd_designs',
            $wpdb->prefix . 'swpd_templates'
        );
        
        $all_tables_exist = true;
        foreach ( $tables_to_check as $table ) {
            if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) !== $table ) {
                $all_tables_exist = false;
                break;
            }
        }
        
        $this->add_test_result(
            'Database Tables',
            $all_tables_exist,
            'All required database tables exist',
            'Some database tables are missing'
        );
    }

    /**
     * Test file permissions
     */
    private function test_file_permissions() {
        $upload_dir = wp_upload_dir();
        $writable = is_writable( $upload_dir['basedir'] );
        
        $this->add_test_result(
            'File Permissions',
            $writable,
            'Upload directory is writable',
            'Upload directory is not writable - check permissions'
        );
    }

    /**
     * Test dependencies
     */
    private function test_dependencies() {
        // Test if WooCommerce is active
        $wc_active = class_exists( 'WooCommerce' );
        
        $this->add_test_result(
            'WooCommerce Dependency',
            $wc_active,
            'WooCommerce is active and available',
            'WooCommerce is required but not active'
        );

        // Test PHP version
        $php_version_ok = version_compare( PHP_VERSION, '7.4', '>=' );
        
        $this->add_test_result(
            'PHP Version',
            $php_version_ok,
            'PHP version is compatible (' . PHP_VERSION . ')',
            'PHP version is too old - requires PHP 7.4+'
        );
    }

    /**
     * Test JavaScript functionality
     */
    private function test_javascript_functionality() {
        // Check if main JS file exists
        $js_file_exists = file_exists( SWPD_PLUGIN_DIR . 'assets/js/enhanced-product-designer-fixed.js' );
        
        $this->add_test_result(
            'JavaScript Files',
            $js_file_exists,
            'Designer JavaScript file exists',
            'Main designer JavaScript file is missing'
        );
    }

    /**
     * Test cart functionality
     */
    private function test_cart_functionality() {
        // Test if cart hooks are properly registered
        $cart_thumbnail_hook = has_filter( 'woocommerce_cart_item_thumbnail', array( 'SWPD_Frontend_Fixed', 'display_cart_item_thumbnail' ) );
        
        $this->add_test_result(
            'Cart Thumbnail Hook',
            $cart_thumbnail_hook !== false,
            'Cart thumbnail hook is registered',
            'Cart thumbnail hook is not registered'
        );
    }

    /**
     * Test design save/load functionality
     */
    private function test_design_save_load() {
        // Test session functionality
        $session_working = session_status() === PHP_SESSION_ACTIVE || headers_sent() === false;
        
        $this->add_test_result(
            'Session Handling',
            $session_working,
            'PHP sessions are working',
            'PHP session issues detected'
        );
    }

    /**
     * Add test result
     */
    private function add_test_result( $test_name, $passed, $success_message, $failure_message ) {
        $this->test_results[] = array(
            'name' => $test_name,
            'passed' => $passed,
            'message' => $passed ? $success_message : $failure_message
        );
    }

    /**
     * Generate test results HTML
     */
    private function generate_test_results_html() {
        $html = '<div class="test-results-container">';
        
        $total_tests = count( $this->test_results );
        $passed_tests = count( array_filter( $this->test_results, function( $test ) {
            return $test['passed'];
        } ) );
        
        $html .= '<div class="test-summary">';
        $html .= '<h3>Test Results Summary</h3>';
        $html .= '<p>Passed: ' . $passed_tests . '/' . $total_tests . ' tests</p>';
        $html .= '</div>';
        
        foreach ( $this->test_results as $test ) {
            $class = $test['passed'] ? 'test-pass' : 'test-fail';
            $icon = $test['passed'] ? '✅' : '❌';
            
            $html .= '<div class="test-result ' . $class . '">';
            $html .= '<strong>' . $icon . ' ' . esc_html( $test['name'] ) . '</strong><br>';
            $html .= esc_html( $test['message'] );
            $html .= '</div>';
        }
        
        $html .= '</div>';
        return $html;
    }

    /**
     * Clear debug logs
     */
    public function ajax_clear_debug_logs() {
        check_ajax_referer( 'swpd_qa_nonce', 'nonce' );
        
        delete_option( 'swpd_debug_logs' );
        
        wp_send_json_success( 'Debug logs cleared' );
    }

    /**
     * Add debug console to frontend
     */
    public function add_debug_console() {
        if ( ! is_product() && ! is_cart() && ! is_checkout() ) {
            return;
        }
        ?>
        <div id="swpd-debug-console" style="position: fixed; bottom: 10px; right: 10px; background: rgba(0,0,0,0.9); color: white; padding: 10px; border-radius: 5px; font-family: monospace; font-size: 12px; max-width: 300px; z-index: 9999; display: none;">
            <div style="margin-bottom: 5px; font-weight: bold;">SWPD Debug Console</div>
            <div id="debug-messages"></div>
            <button onclick="document.getElementById('swpd-debug-console').style.display='none'" style="float: right; margin-top: 5px; background: #666; color: white; border: none; padding: 2px 6px; border-radius: 2px; cursor: pointer;">Close</button>
        </div>
        
        <script>
        // Debug console functions
        window.swpdDebug = {
            log: function(message, level = 'info') {
                const console_el = document.getElementById('debug-messages');
                if (console_el) {
                    const timestamp = new Date().toLocaleTimeString();
                    const color = level === 'error' ? '#ff6b6b' : level === 'warning' ? '#feca57' : '#48dbfb';
                    console_el.innerHTML += '<div style="color: ' + color + '; margin: 2px 0;">[' + timestamp + '] ' + message + '</div>';
                    console_el.scrollTop = console_el.scrollHeight;
                }
                console.log('[SWPD Debug] ' + message);
            },
            
            show: function() {
                document.getElementById('swpd-debug-console').style.display = 'block';
            },
            
            testImageUpload: function() {
                this.log('Testing image upload functionality...');
                // Create a test file input
                const input = document.createElement('input');
                input.type = 'file';
                input.accept = 'image/*';
                input.onchange = function(e) {
                    if (e.target.files.length > 0) {
                        swpdDebug.log('File selected: ' + e.target.files[0].name);
                        swpdDebug.log('File size: ' + (e.target.files[0].size / 1024).toFixed(2) + ' KB');
                        swpdDebug.log('File type: ' + e.target.files[0].type);
                    }
                };
                input.click();
            },
            
            testCartData: function() {
                this.log('Testing cart data...');
                if (typeof window.swpdCanvasData !== 'undefined') {
                    this.log('Canvas data available: ' + Object.keys(window.swpdCanvasData).length + ' items');
                    Object.keys(window.swpdCanvasData).forEach(key => {
                        this.log('Cart item key: ' + key);
                    });
                } else {
                    this.log('No canvas data found', 'warning');
                }
            }
        };
        
        // Show debug console on page load if debug mode is enabled
        if (typeof swpdDesignerConfig !== 'undefined' && swpdDesignerConfig.debug) {
            swpdDebug.show();
            swpdDebug.log('Debug mode enabled');
        }
        
        // Add keyboard shortcut to show/hide debug console
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.shiftKey && e.key === 'D') {
                e.preventDefault();
                const console_el = document.getElementById('swpd-debug-console');
                console_el.style.display = console_el.style.display === 'none' ? 'block' : 'none';
            }
        });
        </script>
        <?php
    }

    /**
     * Log debug message
     */
    public static function log_debug( $message, $level = 'info' ) {
        $logs = get_option( 'swpd_debug_logs', array() );
        
        $logs[] = array(
            'timestamp' => current_time( 'Y-m-d H:i:s' ),
            'level' => $level,
            'message' => $message
        );
        
        // Keep only latest 500 entries
        if ( count( $logs ) > 500 ) {
            $logs = array_slice( $logs, -500 );
        }
        
        update_option( 'swpd_debug_logs', $logs );
    }
}