<?php
/**
 * SWPD Cache Class
 *
 * @package SWPD
 * @since 2.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SWPD_Cache
 * 
 * Handles caching for the plugin
 */
class SWPD_Cache {
	
	/**
	 * Cache prefix
	 *
	 * @var string
	 */
	const CACHE_PREFIX = 'swpd_';
	
	/**
	 * Cache groups
	 *
	 * @var array
	 */
	private static $cache_groups = array(
		'designs' => 3600,          // 1 hour
		'templates' => 86400,       // 24 hours
		'product_data' => 1800,     // 30 minutes
		'user_data' => 600,         // 10 minutes
		'api_responses' => 300      // 5 minutes
	);
	
	/**
	 * Get cached value
	 *
	 * @param string $key Cache key
	 * @param string $group Cache group
	 * @return mixed|false Cached value or false if not found
	 */
	public static function get( $key, $group = 'default' ) {
		$cache_key = self::build_cache_key( $key, $group );
		
		// Try object cache first if available
		if ( wp_using_ext_object_cache() ) {
			$value = wp_cache_get( $cache_key, 'swpd' );
			if ( false !== $value ) {
				return $value;
			}
		}
		
		// Fall back to transient
		return get_transient( $cache_key );
	}
	
	/**
	 * Set cache value
	 *
	 * @param string $key Cache key
	 * @param mixed $value Value to cache
	 * @param string $group Cache group
	 * @param int $expiration Optional custom expiration time
	 * @return bool Success
	 */
	public static function set( $key, $value, $group = 'default', $expiration = null ) {
		$cache_key = self::build_cache_key( $key, $group );
		
		// Determine expiration time
		if ( null === $expiration ) {
			$expiration = isset( self::$cache_groups[$group] ) ? self::$cache_groups[$group] : 3600;
		}
		
		// Use object cache if available
		if ( wp_using_ext_object_cache() ) {
			wp_cache_set( $cache_key, $value, 'swpd', $expiration );
		}
		
		// Always set transient as fallback
		return set_transient( $cache_key, $value, $expiration );
	}
	
	/**
	 * Delete cached value
	 *
	 * @param string $key Cache key
	 * @param string $group Cache group
	 * @return bool Success
	 */
	public static function delete( $key, $group = 'default' ) {
		$cache_key = self::build_cache_key( $key, $group );
		
		// Delete from object cache
		if ( wp_using_ext_object_cache() ) {
			wp_cache_delete( $cache_key, 'swpd' );
		}
		
		// Delete transient
		return delete_transient( $cache_key );
	}
	
	/**
	 * Flush entire cache or specific group
	 *
	 * @param string $group Optional group to flush
	 * @return bool Success
	 */
	public static function flush( $group = null ) {
		global $wpdb;
		
		if ( null === $group ) {
			// Flush all SWPD cache
			if ( wp_using_ext_object_cache() ) {
				wp_cache_flush();
			}
			
			// Delete all SWPD transients
			$wpdb->query(
				$wpdb->prepare(
					"DELETE FROM {$wpdb->options} 
					WHERE option_name LIKE %s 
					OR option_name LIKE %s",
					'_transient_' . self::CACHE_PREFIX . '%',
					'_transient_timeout_' . self::CACHE_PREFIX . '%'
				)
			);
		} else {
			// Flush specific group
			$prefix = self::CACHE_PREFIX . $group . '_';
			
			$wpdb->query(
				$wpdb->prepare(
					"DELETE FROM {$wpdb->options} 
					WHERE option_name LIKE %s 
					OR option_name LIKE %s",
					'_transient_' . $prefix . '%',
					'_transient_timeout_' . $prefix . '%'
				)
			);
		}
		
		return true;
	}
	
	/**
	 * Build cache key
	 *
	 * @param string $key
	 * @param string $group
	 * @return string
	 */
	private static function build_cache_key( $key, $group ) {
		// Sanitize key
		$key = preg_replace( '/[^a-zA-Z0-9_\-]/', '_', $key );
		
		// Build full key
		if ( 'default' === $group ) {
			return self::CACHE_PREFIX . $key;
		}
		
		return self::CACHE_PREFIX . $group . '_' . $key;
	}
	
	/**
	 * Remember function - get from cache or execute callback
	 *
	 * @param string $key Cache key
	 * @param callable $callback Function to execute if not cached
	 * @param string $group Cache group
	 * @param int $expiration Optional expiration time
	 * @return mixed
	 */
	public static function remember( $key, $callback, $group = 'default', $expiration = null ) {
		$value = self::get( $key, $group );
		
		if ( false === $value ) {
			$value = call_user_func( $callback );
			self::set( $key, $value, $group, $expiration );
		}
		
		return $value;
	}
	
	/**
	 * Get cache statistics
	 *
	 * @return array
	 */
	public static function get_stats() {
		global $wpdb;
		
		$stats = array(
			'total_entries' => 0,
			'total_size' => 0,
			'groups' => array()
		);
		
		// Get all SWPD transients
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT option_name, option_value 
				FROM {$wpdb->options} 
				WHERE option_name LIKE %s 
				AND option_name NOT LIKE %s",
				'_transient_' . self::CACHE_PREFIX . '%',
				'_transient_timeout_%'
			)
		);
		
		foreach ( $results as $row ) {
			$stats['total_entries']++;
			$stats['total_size'] += strlen( $row->option_value );
			
			// Determine group
			$key = str_replace( '_transient_' . self::CACHE_PREFIX, '', $row->option_name );
			foreach ( array_keys( self::$cache_groups ) as $group ) {
				if ( strpos( $key, $group . '_' ) === 0 ) {
					if ( ! isset( $stats['groups'][$group] ) ) {
						$stats['groups'][$group] = array(
							'entries' => 0,
							'size' => 0
						);
					}
					$stats['groups'][$group]['entries']++;
					$stats['groups'][$group]['size'] += strlen( $row->option_value );
					break;
				}
			}
		}
		
		// Convert size to human readable
		$stats['total_size_formatted'] = size_format( $stats['total_size'] );
		foreach ( $stats['groups'] as $group => &$group_stats ) {
			$group_stats['size_formatted'] = size_format( $group_stats['size'] );
		}
		
		return $stats;
	}
	
	/**
	 * Garbage collection - remove expired transients
	 */
	public static function garbage_collection() {
		global $wpdb;
		
		// Delete expired transients
		$wpdb->query(
			$wpdb->prepare(
				"DELETE a, b FROM {$wpdb->options} a, {$wpdb->options} b
				WHERE a.option_name LIKE %s
				AND a.option_name NOT LIKE %s
				AND b.option_name = CONCAT('_transient_timeout_', SUBSTRING(a.option_name, 12))
				AND b.option_value < %d",
				'_transient_' . self::CACHE_PREFIX . '%',
				'_transient_timeout_%',
				time()
			)
		);
		
		// Delete orphaned timeout options
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options}
				WHERE option_name LIKE %s
				AND option_value < %d",
				'_transient_timeout_' . self::CACHE_PREFIX . '%',
				time()
			)
		);
	}
	
	/**
	 * Warm cache for specific items
	 *
	 * @param string $type Type of cache to warm
	 */
	public static function warm_cache( $type = 'templates' ) {
		switch ( $type ) {
			case 'templates':
				// Pre-cache all templates
				if ( class_exists( 'SWPD_Templates' ) ) {
					$templates = new SWPD_Templates();
					$all_templates = $templates->get_all_templates();
					foreach ( $all_templates as $template ) {
						self::set( 'template_' . $template['id'], $template, 'templates' );
					}
				}
				break;
				
			case 'products':
				// Pre-cache products with designer data
				$args = array(
					'post_type' => 'product',
					'posts_per_page' => -1,
					'meta_query' => array(
						array(
							'key' => '_design_tool_layer',
							'compare' => 'EXISTS'
						)
					)
				);
				$products = get_posts( $args );
				
				foreach ( $products as $product ) {
					$design_data = get_post_meta( $product->ID, '_design_tool_layer', true );
					if ( $design_data ) {
						self::set( 'product_design_' . $product->ID, $design_data, 'product_data' );
					}
				}
				break;
		}
	}
}

// Schedule garbage collection
add_action( 'init', function() {
	if ( ! wp_next_scheduled( 'swpd_cache_garbage_collection' ) ) {
		wp_schedule_event( time(), 'daily', 'swpd_cache_garbage_collection' );
	}
});

add_action( 'swpd_cache_garbage_collection', array( 'SWPD_Cache', 'garbage_collection' ) );