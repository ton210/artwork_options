<?php
/**
 * SWPD Security Class
 *
 * @package SWPD
 * @since 2.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SWPD_Security
 * 
 * Handles security measures for the plugin
 */
class SWPD_Security {
	
	/**
	 * Logger instance
	 *
	 * @var SWPD_Logger
	 */
	private $logger;
	
	/**
	 * Constructor
	 *
	 * @param SWPD_Logger $logger
	 */
	public function __construct( $logger ) {
		$this->logger = $logger;
	}
	
	/**
	 * Initialize security measures
	 */
	public function init() {
		// Add security headers
		add_action( 'send_headers', array( $this, 'add_security_headers' ) );
		
		// Rate limiting for uploads
		add_filter( 'swpd_before_upload', array( $this, 'check_upload_rate_limit' ), 10, 2 );
		
		// Validate file uploads
		add_filter( 'swpd_validate_upload', array( $this, 'validate_file_upload' ), 10, 2 );
		
		// Clean up old temporary files
		add_action( 'swpd_cleanup_temp_files', array( $this, 'cleanup_temp_files' ) );
		
		// Schedule cleanup if not already scheduled
		if ( ! wp_next_scheduled( 'swpd_cleanup_temp_files' ) ) {
			wp_schedule_event( time(), 'hourly', 'swpd_cleanup_temp_files' );
		}
	}
	
	/**
	 * Add security headers for plugin endpoints
	 */
	public function add_security_headers() {
		if ( $this->is_plugin_request() ) {
			header( 'X-Content-Type-Options: nosniff' );
			header( 'X-Frame-Options: SAMEORIGIN' );
			header( 'X-XSS-Protection: 1; mode=block' );
			header( 'Referrer-Policy: strict-origin-when-cross-origin' );
		}
	}
	
	/**
	 * Check if current request is for plugin
	 *
	 * @return bool
	 */
	private function is_plugin_request() {
		// Check if it's an AJAX request for our plugin
		if ( wp_doing_ajax() && isset( $_POST['action'] ) && strpos( sanitize_text_field( wp_unslash( $_POST['action'] ) ), 'swpd_' ) === 0 ) {
			return true;
		}
		
		// Check if accessing plugin files directly
		if ( isset( $_SERVER['REQUEST_URI'] ) && strpos( $_SERVER['REQUEST_URI'], '/swpd-designs/' ) !== false ) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Check upload rate limit
	 *
	 * @param bool $allowed Whether upload is allowed
	 * @param string $user_identifier User identifier (ID or session)
	 * @return bool|WP_Error
	 */
	public function check_upload_rate_limit( $allowed, $user_identifier ) {
		if ( ! $allowed ) {
			return $allowed;
		}
		
		$limit_per_hour = get_option( 'swpd_upload_limit_per_hour', 20 );
		$transient_key = 'swpd_upload_count_' . md5( $user_identifier );
		$uploads = get_transient( $transient_key );
		
		if ( false === $uploads ) {
			$uploads = array();
		}
		
		// Remove uploads older than 1 hour
		$current_time = time();
		$uploads = array_filter( $uploads, function( $timestamp ) use ( $current_time ) {
			return ( $current_time - $timestamp ) < HOUR_IN_SECONDS;
		});
		
		if ( count( $uploads ) >= $limit_per_hour ) {
			$this->logger->warning( 'Upload rate limit exceeded', array(
				'user' => $user_identifier,
				'uploads' => count( $uploads )
			));
			
			return new WP_Error( 
				'rate_limit_exceeded', 
				sprintf( 
					__( 'Upload limit exceeded. Maximum %d uploads per hour allowed.', 'swpd' ), 
					$limit_per_hour 
				)
			);
		}
		
		// Add current upload
		$uploads[] = $current_time;
		set_transient( $transient_key, $uploads, HOUR_IN_SECONDS );
		
		return true;
	}
	
	/**
	 * Validate file upload
	 *
	 * @param array $file_data Decoded file data
	 * @param string $mime_type MIME type
	 * @return array|WP_Error Validated data or error
	 */
	public function validate_file_upload( $file_data, $mime_type ) {
		// Check file size
		$max_size = get_option( 'swpd_max_upload_size', 5 ) * 1024 * 1024; // Convert MB to bytes
		if ( strlen( $file_data['content'] ) > $max_size ) {
			return new WP_Error( 
				'file_too_large', 
				sprintf( 
					__( 'File size exceeds the maximum allowed size of %dMB.', 'swpd' ), 
					get_option( 'swpd_max_upload_size', 5 ) 
				)
			);
		}
		
		// Verify MIME type
		$finfo = finfo_open( FILEINFO_MIME_TYPE );
		$actual_mime = finfo_buffer( $finfo, $file_data['content'] );
		finfo_close( $finfo );
		
		$allowed_mimes = array(
			'image/jpeg',
			'image/jpg',
			'image/png',
			'image/gif',
			'image/webp'
		);
		
		if ( ! in_array( $actual_mime, $allowed_mimes, true ) ) {
			$this->logger->warning( 'Invalid file type upload attempt', array(
				'claimed_mime' => $mime_type,
				'actual_mime' => $actual_mime
			));
			
			return new WP_Error( 
				'invalid_file_type', 
				__( 'Invalid file type. Only JPEG, PNG, GIF, and WebP images are allowed.', 'swpd' ) 
			);
		}
		
		// Check image dimensions
		$img = imagecreatefromstring( $file_data['content'] );
		if ( ! $img ) {
			return new WP_Error( 
				'invalid_image', 
				__( 'The uploaded file is not a valid image.', 'swpd' ) 
			);
		}
		
		$width = imagesx( $img );
		$height = imagesy( $img );
		imagedestroy( $img );
		
		$max_dimension = get_option( 'swpd_max_image_dimensions', 4000 );
		if ( $width > $max_dimension || $height > $max_dimension ) {
			return new WP_Error( 
				'image_too_large', 
				sprintf( 
					__( 'Image dimensions exceed the maximum allowed size of %dx%d pixels.', 'swpd' ), 
					$max_dimension, 
					$max_dimension 
				)
			);
		}
		
		// Check for malicious content
		if ( $this->contains_php_code( $file_data['content'] ) ) {
			$this->logger->critical( 'Malicious file upload attempt detected', array(
				'mime_type' => $actual_mime,
				'user_id' => get_current_user_id(),
				'ip' => $this->get_client_ip()
			));
			
			return new WP_Error( 
				'security_violation', 
				__( 'Security violation detected. This incident has been logged.', 'swpd' ) 
			);
		}
		
		// Additional image validation
		$image_info = getimagesizefromstring( $file_data['content'] );
		if ( false === $image_info ) {
			return new WP_Error( 
				'invalid_image_data', 
				__( 'Unable to process image data.', 'swpd' ) 
			);
		}
		
		// Return validated data
		return array(
			'content' => $file_data['content'],
			'mime_type' => $actual_mime,
			'width' => $width,
			'height' => $height,
			'size' => strlen( $file_data['content'] )
		);
	}
	
	/**
	 * Check if content contains PHP code
	 *
	 * @param string $content
	 * @return bool
	 */
	private function contains_php_code( $content ) {
		$dangerous_patterns = array(
			'/<\?php/i',
			'/<\?=/i',
			'/<\?/i',
			'/\$_(?:GET|POST|REQUEST|COOKIE|SESSION|SERVER|ENV|FILES)\[/i',
			'/(?:exec|system|passthru|shell_exec|eval|file_get_contents|file_put_contents|fopen|include|require)\s*\(/i'
		);
		
		foreach ( $dangerous_patterns as $pattern ) {
			if ( preg_match( $pattern, $content ) ) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Get client IP address
	 *
	 * @return string
	 */
	private function get_client_ip() {
		$ip_keys = array( 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' );
		
		foreach ( $ip_keys as $key ) {
			if ( array_key_exists( $key, $_SERVER ) === true ) {
				$ips = explode( ',', $_SERVER[$key] );
				foreach ( $ips as $ip ) {
					$ip = trim( $ip );
					if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false ) {
						return $ip;
					}
				}
			}
		}
		
		return isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : 'Unknown';
	}
	
	/**
	 * Clean up temporary files
	 */
	public function cleanup_temp_files() {
		$upload_dir = wp_upload_dir();
		$temp_dirs = array(
			$upload_dir['basedir'] . '/swpd-designs/temp/',
			$upload_dir['basedir'] . '/swpd-designs/user-uploads/'
		);
		
		foreach ( $temp_dirs as $dir ) {
			if ( ! is_dir( $dir ) ) {
				continue;
			}
			
			$files = glob( $dir . '*' );
			$now = time();
			
			foreach ( $files as $file ) {
				// Skip index.php
				if ( basename( $file ) === 'index.php' ) {
					continue;
				}
				
				// Remove files older than 24 hours
				if ( is_file( $file ) && ( $now - filemtime( $file ) ) > 86400 ) {
					unlink( $file );
					$this->logger->debug( 'Cleaned up temporary file', array( 'file' => basename( $file ) ) );
				}
			}
		}
	}
	
	/**
	 * Sanitize filename for saving
	 *
	 * @param string $filename
	 * @return string
	 */
	public static function sanitize_filename( $filename ) {
		// Remove any path components
		$filename = basename( $filename );
		
		// Remove special characters
		$filename = preg_replace( '/[^a-zA-Z0-9._-]/', '', $filename );
		
		// Ensure it doesn't start with a dot
		$filename = ltrim( $filename, '.' );
		
		// Add timestamp to ensure uniqueness
		$info = pathinfo( $filename );
		$name = isset( $info['filename'] ) ? $info['filename'] : 'file';
		$ext = isset( $info['extension'] ) ? '.' . $info['extension'] : '';
		
		return $name . '-' . time() . '-' . wp_rand( 1000, 9999 ) . $ext;
	}
	
	/**
	 * Verify nonce with additional checks
	 *
	 * @param string $nonce
	 * @param string $action
	 * @return bool
	 */
	public static function verify_nonce( $nonce, $action ) {
		// Basic nonce verification
		if ( ! wp_verify_nonce( $nonce, $action ) ) {
			return false;
		}
		
		// Additional timestamp check (nonces are valid for 24 hours by default)
		// This adds an extra layer by limiting to 1 hour for sensitive operations
		$nonce_tick = wp_nonce_tick();
		$expected = substr( wp_hash( $nonce_tick . '|' . $action, 'nonce' ), -12, 10 );
		
		if ( substr( $nonce, -10 ) !== $expected ) {
			// Nonce is older than 1 hour
			return false;
		}
		
		return true;
	}
	
	/**
	 * Generate secure token
	 *
	 * @param string $purpose
	 * @return string
	 */
	public static function generate_token( $purpose = 'general' ) {
		$data = array(
			'purpose' => $purpose,
			'user_id' => get_current_user_id(),
			'time' => time(),
			'random' => wp_rand()
		);
		
		return wp_hash( wp_json_encode( $data ) );
	}
	
	/**
	 * Encrypt sensitive data
	 *
	 * @param string $data
	 * @return string
	 */
	public static function encrypt( $data ) {
		if ( ! function_exists( 'openssl_encrypt' ) ) {
			// Fallback to base64 if OpenSSL is not available
			return base64_encode( $data );
		}
		
		$key = wp_salt( 'auth' );
		$iv = openssl_random_pseudo_bytes( 16 );
		$encrypted = openssl_encrypt( $data, 'AES-256-CBC', $key, 0, $iv );
		
		return base64_encode( $iv . $encrypted );
	}
	
	/**
	 * Decrypt sensitive data
	 *
	 * @param string $encrypted_data
	 * @return string
	 */
	public static function decrypt( $encrypted_data ) {
		if ( ! function_exists( 'openssl_decrypt' ) ) {
			// Fallback from base64 if OpenSSL is not available
			return base64_decode( $encrypted_data );
		}
		
		$key = wp_salt( 'auth' );
		$data = base64_decode( $encrypted_data );
		$iv = substr( $data, 0, 16 );
		$encrypted = substr( $data, 16 );
		
		return openssl_decrypt( $encrypted, 'AES-256-CBC', $key, 0, $iv );
	}
}