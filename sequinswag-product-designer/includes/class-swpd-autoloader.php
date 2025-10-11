<?php
/**
 * SWPD Autoloader
 *
 * @package SWPD
 * @since 2.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SWPD_Autoloader
 * 
 * Handles autoloading of plugin classes
 */
class SWPD_Autoloader {
	
	/**
	 * Initialize the autoloader
	 */
	public static function init() {
		spl_autoload_register( array( __CLASS__, 'autoload' ) );
	}
	
	/**
	 * Autoload SWPD classes
	 *
	 * @param string $class Class name
	 */
	public static function autoload( $class ) {
		// Check if it's our plugin's class
		if ( 0 !== strpos( $class, 'SWPD_' ) ) {
			return;
		}
		
		// Convert class name to file name
		$class_file = 'class-' . str_replace( '_', '-', strtolower( $class ) ) . '.php';
		
		// Define possible paths
		$paths = array(
			SWPD_PLUGIN_DIR . 'includes/',
			SWPD_PLUGIN_DIR . 'includes/admin/',
			SWPD_PLUGIN_DIR . 'includes/frontend/',
			SWPD_PLUGIN_DIR . 'includes/api/',
			SWPD_PLUGIN_DIR . 'includes/utils/',
			SWPD_PLUGIN_DIR . 'includes/integrations/',
		);
		
		// Try to find and include the class file
		foreach ( $paths as $path ) {
			$file = $path . $class_file;
			if ( file_exists( $file ) ) {
				require_once $file;
				return;
			}
		}
	}
}