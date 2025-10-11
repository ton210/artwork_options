<?php
/**
 * Plugin Name: SequinSwag Product Designer
 * Plugin URI:  https://sequinswag.com/
 * Description: A powerful product design tool for WooCommerce with local image processing and Cloudinary integration.
 * Version:     12.20.7
 * Author:      SequinSwag Team
 * Author URI:  https://sequinswag.com/
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: swpd
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'SWPD_VERSION', '12.20.7' ); // Incremented version
define( 'SWPD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SWPD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SWPD_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Check if WooCommerce is active
 */
function swpd_is_woocommerce_active() {
	return in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
}

/**
 * Show admin notice if WooCommerce is not active
 */
function swpd_woocommerce_not_active_notice() {
	?>
	<div class="error">
		<p><?php esc_html_e( 'SequinSwag Product Designer requires WooCommerce to be installed and active.', 'swpd' ); ?></p>
	</div>
	<?php
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing hooks.
 */
require_once SWPD_PLUGIN_DIR . 'includes/class-swpd-autoloader.php';

// Initialize autoloader
SWPD_Autoloader::init();

/**
 * Initialize AJAX handlers very early to ensure they're registered
 */
function swpd_init_ajax_handlers_early() {
    // Only initialize if WooCommerce is active
    if ( ! swpd_is_woocommerce_active() ) {
        return;
    }
    
    // Initialize logger
    if ( class_exists( 'SWPD_Logger' ) ) {
        $logger = new SWPD_Logger();
    } else {
        $logger = null;
    }
    
    // Initialize AJAX handler immediately
    if ( class_exists( 'SWPD_AJAX' ) ) {
        $ajax = new SWPD_AJAX( $logger );
        // The constructor now registers the handlers immediately
        
        // Store instance for later use
        $GLOBALS['swpd_ajax_instance'] = $ajax;
    }
}

// Hook AJAX initialization very early
add_action( 'init', 'swpd_init_ajax_handlers_early', 1 );

/**
 * Initialize all the classes - IMPROVED VERSION
 */
function swpd_run_designer() {
    // Check if WooCommerce is active
    if ( ! swpd_is_woocommerce_active() ) {
        add_action( 'admin_notices', 'swpd_woocommerce_not_active_notice' );
        return;
    }

    // Initialize logger first
    $logger = new SWPD_Logger();
    
    // Log initialization
    $logger->info( 'SWPD Plugin initializing', array(
        'version' => SWPD_VERSION,
        'php_version' => PHP_VERSION,
        'wp_version' => get_bloginfo( 'version' ),
        'wc_version' => defined( 'WC_VERSION' ) ? WC_VERSION : 'unknown',
        'gd_enabled' => extension_loaded( 'gd' ),
        'imagick_enabled' => extension_loaded( 'imagick' ),
        'memory_limit' => ini_get( 'memory_limit' ),
        'max_execution_time' => ini_get( 'max_execution_time' ),
        'upload_max_filesize' => ini_get( 'upload_max_filesize' )
    ));

    // Initialize cache
    $cache = new SWPD_Cache();

    // Core classes
    $swpd_admin = new SWPD_Admin( $logger );
    $swpd_frontend = new SWPD_Frontend( $logger, $cache );
    
    // Get or create AJAX instance
    if ( isset( $GLOBALS['swpd_ajax_instance'] ) ) {
        $swpd_ajax = $GLOBALS['swpd_ajax_instance'];
    } else {
        $swpd_ajax = new SWPD_AJAX( $logger );
    }

    // New feature classes
    $swpd_templates = new SWPD_Templates( $logger, $cache );
    $swpd_rest_api = new SWPD_REST_API( $logger );
    $swpd_security = new SWPD_Security( $logger );
    $swpd_cloudinary = new SWPD_Cloudinary( $logger );

    // Register hooks
    add_action( 'plugins_loaded', array( $swpd_admin, 'init' ) );
    add_action( 'plugins_loaded', array( $swpd_ajax, 'init' ) );
    add_action( 'plugins_loaded', array( $swpd_frontend, 'init' ) );
    add_action( 'plugins_loaded', array( $swpd_templates, 'init' ) );
    add_action( 'plugins_loaded', array( $swpd_rest_api, 'init' ) );
    add_action( 'plugins_loaded', array( $swpd_security, 'init' ) );
    add_action( 'plugins_loaded', array( $swpd_cloudinary, 'init' ) );

    // Make logger available globally for debugging
    $GLOBALS['swpd_logger'] = $logger;

    // Log successful initialization
    $logger->info( 'SWPD Plugin initialized successfully' );
}

// Initialize the plugin
add_action( 'init', 'swpd_run_designer' );

/**
 * Frontend nonce generation fix
 * Add this to ensure nonces are available on the frontend
 */
add_action( 'wp_enqueue_scripts', function() {
    if ( is_product() || is_shop() ) {
        // Add inline script with proper nonce
        wp_add_inline_script( 'swpd-enhanced-product-designer', 
            'window.swpd_ajax_nonce = "' . wp_create_nonce( 'swpd_design_upload_nonce' ) . '";',
            'before'
        );
    }
}, 20 );

/**
 * Add AJAX URL to frontend
 */
add_action( 'wp_head', function() {
    if ( is_product() ) {
        ?>
        <script type="text/javascript">
        /* SWPD AJAX Configuration */
        window.swpd_ajax = {
            url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
            nonce: '<?php echo wp_create_nonce( 'swpd_design_upload_nonce' ); ?>'
        };
        </script>
        <?php
    }
});

/**
 * Debug mode for AJAX requests
 */
add_action( 'wp_ajax_swpd_debug_test', function() {
    // Simple test endpoint
    wp_send_json_success( array(
        'message' => 'AJAX is working!',
        'timestamp' => current_time( 'mysql' ),
        'user_logged_in' => is_user_logged_in(),
        'nonce_action' => 'swpd_design_upload_nonce',
        'test_nonce' => wp_create_nonce( 'swpd_design_upload_nonce' )
    ));
});
add_action( 'wp_ajax_nopriv_swpd_debug_test', function() {
    wp_send_json_success( array(
        'message' => 'AJAX is working for non-logged-in users!',
        'timestamp' => current_time( 'mysql' )
    ));
});


// Activation and Deactivation hooks
register_activation_hook( __FILE__, 'swpd_activate_plugin' );
register_deactivation_hook( __FILE__, 'swpd_deactivate_plugin' );
register_uninstall_hook( __FILE__, 'swpd_uninstall_plugin' );

/**
 * Plugin activation hook.
 */
function swpd_activate_plugin() {
	// Ensure GD library is available.
	if ( ! extension_loaded( 'gd' ) && ! function_exists( 'gd_info' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die(
			esc_html__( 'The SequinSwag Product Designer plugin requires the GD library extension to be installed and enabled on your server. Please contact your hosting provider for assistance.', 'swpd' ),
			'Plugin Activation Error',
			array( 'back_link' => true )
		);
	}

	// Create database tables
	swpd_create_database_tables();

	// Create upload directories
	swpd_create_upload_directories();

	// Set default options
	swpd_set_default_options();

	// Clear any existing cache
	SWPD_Cache::flush();

	// Log activation
	if ( class_exists( 'SWPD_Logger' ) ) {
		$logger = new SWPD_Logger();
		$logger->info( 'Plugin activated', array( 'version' => SWPD_VERSION ) );
	}

	// Flush rewrite rules for REST API endpoints
	flush_rewrite_rules();
}

/**
 * Create database tables
 */
function swpd_create_database_tables() {
	global $wpdb;

	$charset_collate = $wpdb->get_charset_collate();

	// Designs table for better performance than post meta
	$designs_table = $wpdb->prefix . 'swpd_designs';
	$sql_designs = "CREATE TABLE IF NOT EXISTS $designs_table (
		id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		user_id bigint(20) UNSIGNED NOT NULL,
		product_id bigint(20) UNSIGNED NOT NULL,
		variant_id bigint(20) UNSIGNED DEFAULT NULL,
		design_name varchar(255),
		design_data longtext NOT NULL,
		preview_url varchar(500),
		status varchar(20) DEFAULT 'draft',
		created_at datetime DEFAULT CURRENT_TIMESTAMP,
		updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		PRIMARY KEY (id),
		KEY user_id (user_id),
		KEY product_id (product_id),
		KEY variant_id (variant_id),
		KEY status (status),
		KEY created_at (created_at)
	) $charset_collate;";

	// Auto-save table
	$autosave_table = $wpdb->prefix . 'swpd_autosaves';
	$sql_autosave = "CREATE TABLE IF NOT EXISTS $autosave_table (
		id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		session_id varchar(255) NOT NULL,
		user_id bigint(20) UNSIGNED DEFAULT NULL,
		product_id bigint(20) UNSIGNED NOT NULL,
		variant_id bigint(20) UNSIGNED DEFAULT NULL,
		design_data longtext NOT NULL,
		created_at datetime DEFAULT CURRENT_TIMESTAMP,
		updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		PRIMARY KEY (id),
		UNIQUE KEY session_product (session_id, product_id, variant_id),
		KEY user_id (user_id),
		KEY updated_at (updated_at)
	) $charset_collate;";

	// Analytics table
	$analytics_table = $wpdb->prefix . 'swpd_analytics';
	$sql_analytics = "CREATE TABLE IF NOT EXISTS $analytics_table (
		id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		event_type varchar(50) NOT NULL,
		user_id bigint(20) UNSIGNED DEFAULT NULL,
		product_id bigint(20) UNSIGNED DEFAULT NULL,
		event_data text,
		created_at datetime DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (id),
		KEY event_type (event_type),
		KEY user_id (user_id),
		KEY product_id (product_id),
		KEY created_at (created_at)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql_designs );
	dbDelta( $sql_autosave );
	dbDelta( $sql_analytics );

	// Store database version for future updates
	update_option( 'swpd_db_version', SWPD_VERSION );

	// Log table creation
	if ( class_exists( 'SWPD_Logger' ) ) {
		$logger = new SWPD_Logger();
		$logger->info( 'Database tables created/updated', array(
			'version' => SWPD_VERSION,
			'tables' => array( 'designs', 'autosaves', 'analytics' )
		));
	}
}

/**
 * Create upload directories
 */
function swpd_create_upload_directories() {
	$upload_dir = wp_upload_dir();
	$directories = array(
		$upload_dir['basedir'] . '/swpd-designs',
		$upload_dir['basedir'] . '/swpd-designs/user-uploads',
		$upload_dir['basedir'] . '/swpd-designs/previews',
		$upload_dir['basedir'] . '/swpd-designs/production',
		$upload_dir['basedir'] . '/swpd-designs/templates',
		$upload_dir['basedir'] . '/swpd-designs/temp',
		$upload_dir['basedir'] . '/swpd-logs',
		$upload_dir['basedir'] . '/swpd-exports'
	);

	$created_dirs = array();

	foreach ( $directories as $dir ) {
		if ( ! file_exists( $dir ) ) {
			if ( wp_mkdir_p( $dir ) ) {
				// Add index.php to prevent directory listing
				file_put_contents( $dir . '/index.php', '<?php // Silence is golden' );
				$created_dirs[] = basename( $dir );
			}
		}
	}

	// Add .htaccess for extra security in logs directory
	$htaccess_content = "Order Allow,Deny\nDeny from all";
	$logs_htaccess = $upload_dir['basedir'] . '/swpd-logs/.htaccess';
	if ( ! file_exists( $logs_htaccess ) ) {
		file_put_contents( $logs_htaccess, $htaccess_content );
	}

	// Log directory creation
	if ( ! empty( $created_dirs ) && class_exists( 'SWPD_Logger' ) ) {
		$logger = new SWPD_Logger();
		$logger->info( 'Upload directories created', array( 'directories' => $created_dirs ) );
	}
}

/**
 * Set default plugin options
 */
function swpd_set_default_options() {
	$defaults = array(
		// General options
		'swpd_max_upload_size' => 5, // MB
		'swpd_max_image_dimensions' => 4000, // pixels
		'swpd_allowed_file_types' => array( 'jpg', 'jpeg', 'png', 'gif', 'webp' ),
		'swpd_enable_autosave' => true,
		'swpd_autosave_interval' => 30, // seconds
		'swpd_enable_templates' => true,
		'swpd_enable_analytics' => true,
		'swpd_upload_limit_per_hour' => 20,
		'swpd_enable_imagick' => extension_loaded( 'imagick' ),
		'swpd_debug_mode' => false,
		'swpd_primary_color' => '#0073aa',
		'swpd_secondary_color' => '#f0ad4e',
		'swpd_email_notifications' => true,
		'swpd_remove_data_on_uninstall' => false,
		
		// Cloudinary options
        'swpd_cloudinary_enabled' => false,
        'swpd_cloudinary_cloud_name' => '',
        'swpd_cloudinary_api_key' => '',
        'swpd_cloudinary_api_secret' => '',
        'swpd_cloudinary_upload_preset' => '',
        'swpd_cloudinary_folder' => 'swpd-designs',
        'swpd_cloudinary_debug' => false,
        'swpd_cloudinary_fallback_local' => true,
	);

	$updated_options = array();

	foreach ( $defaults as $option => $value ) {
		if ( get_option( $option ) === false ) {
			update_option( $option, $value );
			$updated_options[$option] = $value;
		}
	}

	// Log options update
	if ( ! empty( $updated_options ) && class_exists( 'SWPD_Logger' ) ) {
		$logger = new SWPD_Logger();
		$logger->info( 'Default options set', array( 'options' => array_keys( $updated_options ) ) );
	}
}


/**
 * Plugin deactivation hook.
 */
function swpd_deactivate_plugin() {
	// Clean up scheduled events
	wp_clear_scheduled_hook( 'swpd_cleanup_autosaves' );
	wp_clear_scheduled_hook( 'swpd_cleanup_temp_files' );
	wp_clear_scheduled_hook( 'swpd_cache_garbage_collection' );

	// Log deactivation
	if ( class_exists( 'SWPD_Logger' ) ) {
		$logger = new SWPD_Logger();
		$logger->info( 'Plugin deactivated', array( 'version' => SWPD_VERSION ) );
	}

	// Clear cache
	SWPD_Cache::flush();

	// Flush rewrite rules
	flush_rewrite_rules();
}

/**
 * Plugin uninstall hook.
 */
function swpd_uninstall_plugin() {
	// Only run if explicitly uninstalling
	if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
		return;
	}

	// Get uninstall option
	$remove_data = get_option( 'swpd_remove_data_on_uninstall', false );

	if ( $remove_data ) {
		global $wpdb;

		// Drop custom tables
		$tables = array(
			$wpdb->prefix . 'swpd_designs',
			$wpdb->prefix . 'swpd_autosaves',
			$wpdb->prefix . 'swpd_analytics'
		);

		foreach ( $tables as $table ) {
			$wpdb->query( "DROP TABLE IF EXISTS $table" );
		}

		// Remove options
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'swpd_%'" );

		// Remove post meta
		$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '_swpd_%' OR meta_key = 'design_tool_layer'" );

		// Remove transients
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_swpd_%' OR option_name LIKE '_transient_timeout_swpd_%'" );

		// Remove uploaded files
		$upload_dir = wp_upload_dir();
		$swpd_dir = $upload_dir['basedir'] . '/swpd-designs';
		if ( file_exists( $swpd_dir ) ) {
			swpd_recursive_rmdir( $swpd_dir );
		}

		$logs_dir = $upload_dir['basedir'] . '/swpd-logs';
		if ( file_exists( $logs_dir ) ) {
			swpd_recursive_rmdir( $logs_dir );
		}

		$exports_dir = $upload_dir['basedir'] . '/swpd-exports';
		if ( file_exists( $exports_dir ) ) {
			swpd_recursive_rmdir( $exports_dir );
		}
	}
}

/**
 * Recursively remove directory
 */
function swpd_recursive_rmdir( $dir ) {
	if ( is_dir( $dir ) ) {
		$objects = scandir( $dir );
		foreach ( $objects as $object ) {
			if ( $object != "." && $object != ".." ) {
				if ( is_dir( $dir . "/" . $object ) ) {
					swpd_recursive_rmdir( $dir . "/" . $object );
				} else {
					unlink( $dir . "/" . $object );
				}
			}
		}
		rmdir( $dir );
	}
}

/**
 * Migrate design_tool_layer meta keys to use underscore prefix
 */
function swpd_migrate_meta_keys() {
    global $wpdb;

    // Check if migration from the incorrect key is needed
    $needs_migration = $wpdb->get_var(
        "SELECT COUNT(*) FROM {$wpdb->postmeta}
         WHERE meta_key = '_design_tool_layer'"
    );

    if ( $needs_migration > 0 ) {
        // Update all _design_tool_layer to design_tool_layer
        $updated = $wpdb->query(
            "UPDATE {$wpdb->postmeta}
             SET meta_key = 'design_tool_layer'
             WHERE meta_key = '_design_tool_layer'"
        );

        if ( class_exists( 'SWPD_Logger' ) ) {
            $logger = new SWPD_Logger();
            $logger->info( 'Migrated meta keys from _design_tool_layer to design_tool_layer', array(
                'count' => $updated
            ));
        }

        // Clear cache
        wp_cache_flush();
    }
}


// Run migration on admin init
add_action( 'admin_init', 'swpd_migrate_meta_keys' );

/**
 * Load plugin textdomain
 */
add_action( 'plugins_loaded', function() {
	load_plugin_textdomain( 'swpd', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
});

/**
 * Add plugin action links
 */
add_filter( 'plugin_action_links_' . SWPD_PLUGIN_BASENAME, function( $links ) {
	$settings_link = '<a href="' . admin_url( 'admin.php?page=swpd-settings' ) . '">' . esc_html__( 'Settings', 'swpd' ) . '</a>';
	$dashboard_link = '<a href="' . admin_url( 'admin.php?page=swpd-dashboard' ) . '">' . esc_html__( 'Dashboard', 'swpd' ) . '</a>';

	array_unshift( $links, $settings_link );
	array_unshift( $links, $dashboard_link );

	// Add debug link if in debug mode
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG && current_user_can( 'manage_options' ) ) {
		$debug_link = '<a href="' . admin_url( 'admin.php?page=swpd-debug-test' ) . '" style="color: #ff6b6b;">' . esc_html__( 'Debug', 'swpd' ) . '</a>';
		$links[] = $debug_link;
	}

	return $links;
});

/**
 * Add plugin row meta
 */
add_filter( 'plugin_row_meta', function( $links, $file ) {
	if ( SWPD_PLUGIN_BASENAME === $file ) {
		$row_meta = array(
			'docs' => '<a href="https://sequinswag.com/docs/product-designer/" target="_blank">' . esc_html__( 'Documentation', 'swpd' ) . '</a>',
			'support' => '<a href="https://sequinswag.com/support/" target="_blank">' . esc_html__( 'Support', 'swpd' ) . '</a>',
		);

		return array_merge( $links, $row_meta );
	}

	return $links;
}, 10, 2 );

/**
 * Check for plugin updates
 */
add_action( 'admin_init', function() {
	$current_version = get_option( 'swpd_version' );

	if ( $current_version !== SWPD_VERSION ) {
		// Run any update routines here
		swpd_plugin_update_routine( $current_version, SWPD_VERSION );

		// Update version
		update_option( 'swpd_version', SWPD_VERSION );
	}
});

/**
 * Plugin update routine
 */
function swpd_plugin_update_routine( $old_version, $new_version ) {
	// Log update
	if ( class_exists( 'SWPD_Logger' ) ) {
		$logger = new SWPD_Logger();
		$logger->info( 'Plugin updated', array(
			'old_version' => $old_version,
			'new_version' => $new_version
		));
	}

	// Run database updates
	swpd_create_database_tables();

	// Clear cache
	if ( class_exists( 'SWPD_Cache' ) ) {
		SWPD_Cache::flush();
	}

	// Add any version-specific updates here
	// Example:
	// if ( version_compare( $old_version, '2.0.0', '<' ) ) {
	//       // Run 2.0.0 specific updates
	// }
}

/**
 * Add admin bar debug menu
 */
add_action( 'admin_bar_menu', function( $wp_admin_bar ) {
	if ( ! current_user_can( 'manage_options' ) || ! is_admin_bar_showing() ) {
		return;
	}

	// Only show on product pages or if debug mode is on
	if ( ! is_product() && ! ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ) {
		return;
	}

	$args = array(
		'id'    => 'swpd-debug',
		'title' => 'SWPD Debug',
		'href'  => '#',
		'meta'  => array(
			'class' => 'swpd-debug-menu'
		)
	);
	$wp_admin_bar->add_node( $args );

	// Add submenu items
	if ( is_product() ) {
		$product_id = get_the_ID();

		$wp_admin_bar->add_node( array(
			'parent' => 'swpd-debug',
			'id'     => 'swpd-debug-mode',
			'title'  => 'Enable Debug Mode',
			'href'   => add_query_arg( 'swpd_debug', '1' )
		));

		$wp_admin_bar->add_node( array(
			'parent' => 'swpd-debug',
			'id'     => 'swpd-view-logs',
			'title'  => 'View Logs',
			'href'   => admin_url( 'admin.php?page=swpd-status#logs' )
		));
	}

	$wp_admin_bar->add_node( array(
		'parent' => 'swpd-debug',
		'id'     => 'swpd-debug-test',
		'title'  => 'Debug Test Page',
		'href'   => admin_url( 'admin.php?page=swpd-debug-test' )
	));
}, 999 );