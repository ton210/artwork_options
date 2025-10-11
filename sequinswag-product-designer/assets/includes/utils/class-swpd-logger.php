<?php
/**
 * SWPD Logger Class
 *
 * @package SWPD
 * @since 2.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SWPD_Logger
 * 
 * Handles comprehensive logging for the plugin
 */
class SWPD_Logger {
	
	/**
	 * Log file path
	 *
	 * @var string
	 */
	private $log_file;
	
	/**
	 * Log levels
	 *
	 * @var array
	 */
	private $log_levels = array(
		'debug' => 0,
		'info'  => 1,
		'warning' => 2,
		'error' => 3,
		'critical' => 4
	);
	
	/**
	 * Current log level threshold
	 *
	 * @var int
	 */
	private $log_level_threshold;
	
	/**
	 * Maximum log file size (5MB)
	 *
	 * @var int
	 */
	private $max_file_size = 5242880;
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$upload_dir = wp_upload_dir();
		$log_dir = $upload_dir['basedir'] . '/swpd-logs';
		
		// Ensure log directory exists
		if ( ! file_exists( $log_dir ) ) {
			wp_mkdir_p( $log_dir );
			file_put_contents( $log_dir . '/index.php', '<?php // Silence is golden' );
		}
		
		$this->log_file = $log_dir . '/swpd-' . date( 'Y-m' ) . '.log';
		
		// Set log level based on debug mode
		$debug_mode = get_option( 'swpd_debug_mode', false );
		$this->log_level_threshold = $debug_mode ? 0 : 1; // Debug or Info
		
		// Rotate log if too large
		$this->rotate_log_if_needed();
	}
	
	/**
	 * Log a message
	 *
	 * @param string $message The message to log
	 * @param string $level The log level
	 * @param array $context Additional context data
	 */
	public function log( $message, $level = 'info', $context = array() ) {
		// Check if we should log this level
		$level = strtolower( $level );
		if ( ! isset( $this->log_levels[$level] ) || $this->log_levels[$level] < $this->log_level_threshold ) {
			return;
		}
		
		// Format the log entry
		$entry = $this->format_log_entry( $message, $level, $context );
		
		// Write to file
		$this->write_to_file( $entry );
		
		// Also log to WordPress debug.log if WP_DEBUG is true
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			error_log( 'SWPD: ' . $entry );
		}
		
		// Send critical errors to admin email
		if ( $level === 'critical' ) {
			$this->notify_admin( $message, $context );
		}
	}
	
	/**
	 * Log debug message
	 *
	 * @param string $message
	 * @param array $context
	 */
	public function debug( $message, $context = array() ) {
		$this->log( $message, 'debug', $context );
	}
	
	/**
	 * Log info message
	 *
	 * @param string $message
	 * @param array $context
	 */
	public function info( $message, $context = array() ) {
		$this->log( $message, 'info', $context );
	}
	
	/**
	 * Log warning message
	 *
	 * @param string $message
	 * @param array $context
	 */
	public function warning( $message, $context = array() ) {
		$this->log( $message, 'warning', $context );
	}
	
	/**
	 * Log error message
	 *
	 * @param string $message
	 * @param array $context
	 */
	public function error( $message, $context = array() ) {
		$this->log( $message, 'error', $context );
	}
	
	/**
	 * Log critical message
	 *
	 * @param string $message
	 * @param array $context
	 */
	public function critical( $message, $context = array() ) {
		$this->log( $message, 'critical', $context );
	}
	
	/**
	 * Format log entry
	 *
	 * @param string $message
	 * @param string $level
	 * @param array $context
	 * @return string
	 */
	private function format_log_entry( $message, $level, $context ) {
		$timestamp = date( 'Y-m-d H:i:s' );
		$level_upper = strtoupper( $level );
		$user_id = get_current_user_id();
		$ip = $this->get_client_ip();
		
		// Base entry
		$entry = "[{$timestamp}] [{$level_upper}] ";
		
		// Add user info if available
		if ( $user_id ) {
			$entry .= "[User: {$user_id}] ";
		}
		
		// Add IP for certain events
		if ( in_array( $level, array( 'warning', 'error', 'critical' ) ) ) {
			$entry .= "[IP: {$ip}] ";
		}
		
		// Add message
		$entry .= $message;
		
		// Add context if available
		if ( ! empty( $context ) ) {
			$entry .= ' | Context: ' . wp_json_encode( $context );
		}
		
		// Add backtrace for errors
		if ( in_array( $level, array( 'error', 'critical' ) ) ) {
			$backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 5 );
			$trace_info = array();
			foreach ( $backtrace as $trace ) {
				if ( isset( $trace['file'] ) && strpos( $trace['file'], 'swpd' ) !== false ) {
					$trace_info[] = basename( $trace['file'] ) . ':' . $trace['line'];
				}
			}
			if ( ! empty( $trace_info ) ) {
				$entry .= ' | Trace: ' . implode( ' -> ', $trace_info );
			}
		}
		
		return $entry;
	}
	
	/**
	 * Write log entry to file
	 *
	 * @param string $entry
	 */
	private function write_to_file( $entry ) {
		// Add newline
		$entry .= "\n";
		
		// Write to file with lock
		$fp = fopen( $this->log_file, 'a' );
		if ( $fp ) {
			if ( flock( $fp, LOCK_EX ) ) {
				fwrite( $fp, $entry );
				flock( $fp, LOCK_UN );
			}
			fclose( $fp );
		}
	}
	
	/**
	 * Rotate log if needed
	 */
	private function rotate_log_if_needed() {
		if ( file_exists( $this->log_file ) && filesize( $this->log_file ) > $this->max_file_size ) {
			$backup_file = $this->log_file . '.' . time() . '.bak';
			rename( $this->log_file, $backup_file );
			
			// Keep only last 5 backup files
			$this->cleanup_old_logs();
		}
	}
	
	/**
	 * Clean up old log files
	 */
	private function cleanup_old_logs() {
		$log_dir = dirname( $this->log_file );
		$files = glob( $log_dir . '/*.log.*.bak' );
		
		if ( count( $files ) > 5 ) {
			// Sort by modification time
			usort( $files, function( $a, $b ) {
				return filemtime( $a ) - filemtime( $b );
			});
			
			// Remove oldest files
			$files_to_remove = array_slice( $files, 0, count( $files ) - 5 );
			foreach ( $files_to_remove as $file ) {
				unlink( $file );
			}
		}
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
				$ip = $_SERVER[$key];
				if ( strpos( $ip, ',' ) !== false ) {
					$ip = explode( ',', $ip )[0];
				}
				$ip = trim( $ip );
				if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
					return $ip;
				}
			}
		}
		
		return 'Unknown';
	}
	
	/**
	 * Notify admin of critical errors
	 *
	 * @param string $message
	 * @param array $context
	 */
	private function notify_admin( $message, $context ) {
		// Check if notifications are enabled
		if ( ! get_option( 'swpd_email_notifications', true ) ) {
			return;
		}
		
		// Rate limit notifications (max 1 per hour)
		$last_notification = get_transient( 'swpd_last_critical_notification' );
		if ( $last_notification ) {
			return;
		}
		
		set_transient( 'swpd_last_critical_notification', time(), HOUR_IN_SECONDS );
		
		// Send email to admin
		$admin_email = get_option( 'admin_email' );
		$subject = sprintf( '[%s] Critical Error in SequinSwag Product Designer', get_bloginfo( 'name' ) );
		$body = "A critical error has occurred in the SequinSwag Product Designer plugin.\n\n";
		$body .= "Error: {$message}\n\n";
		if ( ! empty( $context ) ) {
			$body .= "Context:\n" . print_r( $context, true ) . "\n\n";
		}
		$body .= "Time: " . date( 'Y-m-d H:i:s' ) . "\n";
		$body .= "Site: " . home_url() . "\n";
		
		wp_mail( $admin_email, $subject, $body );
	}
	
	/**
	 * Get recent log entries
	 *
	 * @param int $lines Number of lines to retrieve
	 * @param string $level Filter by log level
	 * @return array
	 */
	public function get_recent_entries( $lines = 100, $level = null ) {
		if ( ! file_exists( $this->log_file ) ) {
			return array();
		}
		
		$entries = array();
		$file = new SplFileObject( $this->log_file );
		$file->seek( PHP_INT_MAX );
		$total_lines = $file->key();
		
		$start = max( 0, $total_lines - $lines );
		$file->seek( $start );
		
		while ( ! $file->eof() ) {
			$line = $file->current();
			if ( ! empty( $line ) ) {
				// Parse log entry
				if ( preg_match( '/\[([\d\-\s:]+)\]\s\[(\w+)\]\s(.*)/', $line, $matches ) ) {
					$entry = array(
						'timestamp' => $matches[1],
						'level' => strtolower( $matches[2] ),
						'message' => $matches[3]
					);
					
					// Filter by level if specified
					if ( $level === null || $entry['level'] === $level ) {
						$entries[] = $entry;
					}
				}
			}
			$file->next();
		}
		
		return array_reverse( $entries );
	}
	
	/**
	 * Clear all logs
	 */
	public function clear_logs() {
		$log_dir = dirname( $this->log_file );
		$files = glob( $log_dir . '/*.log*' );
		
		foreach ( $files as $file ) {
			unlink( $file );
		}
		
		$this->info( 'Logs cleared' );
	}
}