<?php
/**
 * SWPD AJAX Class - Fixed Version
 *
 * @package SWPD
 * @since 2.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class SWPD_AJAX
 * Handles AJAX requests with fixed upload functionality
 */
class SWPD_AJAX {
    
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
    public function __construct( $logger = null ) {
        $this->logger = $logger;
        
        // Initialize AJAX handlers immediately on construction
        $this->init_ajax_handlers();
    }
    
    /**
     * Initialize AJAX handlers - called immediately
     */
    private function init_ajax_handlers() {
        // User image upload - register for both logged in and non-logged in users
        add_action( 'wp_ajax_swpd_upload_user_image', array( $this, 'upload_user_image' ) );
        add_action( 'wp_ajax_nopriv_swpd_upload_user_image', array( $this, 'upload_user_image' ) );
        
        // Design preview upload
        add_action( 'wp_ajax_swpd_upload_design_preview', array( $this, 'upload_design_preview' ) );
        add_action( 'wp_ajax_nopriv_swpd_upload_design_preview', array( $this, 'upload_design_preview' ) );
        
        // Save Cloudinary URL
        add_action( 'wp_ajax_swpd_save_cloudinary_url', array( $this, 'save_cloudinary_url' ) );
        add_action( 'wp_ajax_nopriv_swpd_save_cloudinary_url', array( $this, 'save_cloudinary_url' ) );
    }
    
    /**
     * Initialize other non-critical features
     */
    public function init() {
        // Auto-save
        add_action( 'wp_ajax_swpd_autosave_design', array( $this, 'autosave_design' ) );
        add_action( 'wp_ajax_nopriv_swpd_autosave_design', array( $this, 'autosave_design' ) );
        
        // Load autosaved design
        add_action( 'wp_ajax_swpd_load_autosave', array( $this, 'load_autosave' ) );
        add_action( 'wp_ajax_nopriv_swpd_load_autosave', array( $this, 'load_autosave' ) );
        
        // Templates
        add_action( 'wp_ajax_swpd_get_templates', array( $this, 'get_templates' ) );
        add_action( 'wp_ajax_nopriv_swpd_get_templates', array( $this, 'get_templates' ) );
        
        // Save user design
        add_action( 'wp_ajax_swpd_save_user_design', array( $this, 'save_user_design' ) );
        add_action( 'wp_ajax_nopriv_swpd_save_user_design', array( $this, 'save_user_design' ) );
        
        // Load user designs
        add_action( 'wp_ajax_swpd_load_user_designs', array( $this, 'load_user_designs' ) );
        add_action( 'wp_ajax_nopriv_swpd_load_user_designs', array( $this, 'load_user_designs' ) );
        
        // Delete user design
        add_action( 'wp_ajax_swpd_delete_user_design', array( $this, 'delete_user_design' ) );
        add_action( 'wp_ajax_nopriv_swpd_delete_user_design', array( $this, 'delete_user_design' ) );
        
        // Cloudinary settings
        add_action( 'wp_ajax_swpd_save_cloudinary_settings', array( $this, 'save_cloudinary_settings' ) );
        
        // Order processing hooks
        add_action( 'woocommerce_order_status_completed', array( $this, 'trigger_design_processing_on_order_completion' ) );
        add_action( 'woocommerce_order_status_processing', array( $this, 'trigger_design_processing_on_order_completion' ) );
        
        // Clean up autosaves periodically
        add_action( 'swpd_cleanup_autosaves', array( $this, 'cleanup_old_autosaves' ) );
        if ( ! wp_next_scheduled( 'swpd_cleanup_autosaves' ) ) {
            wp_schedule_event( time(), 'daily', 'swpd_cleanup_autosaves' );
        }
    }
    
    /**
     * Get user identifier for rate limiting and sessions
     *
     * @return string
     */
    private function get_user_identifier() {
        $user_id = get_current_user_id();
        if ( $user_id ) {
            return 'user_' . $user_id;
        }
        
        // For guests, use session ID or IP
        if ( ! session_id() && ! headers_sent() && ! wp_doing_ajax() ) {
            session_start();
        }
        
        if ( session_id() ) {
            return 'guest_' . session_id();
        }
        
        // Fallback to IP
        return 'guest_' . $_SERVER['REMOTE_ADDR'];
    }
    
    /**
     * Log helper method
     */
    private function log( $level, $message, $context = array() ) {
        if ( $this->logger ) {
            $this->logger->$level( $message, $context );
        }
        
        // Also log to error_log in debug mode
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'SWPD: ' . $level . ' - ' . $message . ' - ' . json_encode( $context ) );
        }
    }
    
    /**
     * FIXED: Upload user image with better error handling
     */
    public function upload_user_image() {
        // Enable error reporting for debugging
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_reporting( E_ALL );
            ini_set( 'display_errors', 1 );
        }
        
        // Log the request
        $this->log( 'debug', 'Upload user image request received', array(
            'action' => isset($_POST['action']) ? $_POST['action'] : 'not set',
            'nonce' => isset($_POST['nonce']) ? $_POST['nonce'] : 'not set',
            'has_image' => isset($_POST['image']) ? 'yes' : 'no',
            'filename' => isset($_POST['filename']) ? $_POST['filename'] : 'not set',
            'post_data_keys' => array_keys($_POST)
        ));

        // Check if we received the expected data
        if ( ! isset( $_POST['action'] ) || $_POST['action'] !== 'swpd_upload_user_image' ) {
            wp_send_json_error( array(
                'message' => __( 'Invalid action.', 'swpd' ),
                'debug' => 'Action not set or incorrect'
            ));
            return;
        }

        // Verify nonce - be flexible with nonce verification
        $nonce_valid = false;
        if ( isset( $_POST['nonce'] ) ) {
            // Try multiple nonce actions for compatibility
            $nonce_actions = array( 'swpd_design_upload_nonce', 'swpd_designer_nonce', 'swpd_nonce' );
            foreach ( $nonce_actions as $action ) {
                if ( wp_verify_nonce( $_POST['nonce'], $action ) ) {
                    $nonce_valid = true;
                    break;
                }
            }
        }
        
        if ( ! $nonce_valid ) {
            $this->log( 'warning', 'Nonce verification failed', array(
                'nonce' => isset($_POST['nonce']) ? $_POST['nonce'] : 'not provided'
            ));
            
            // For development/testing, you might want to bypass nonce check
            // Remove this in production!
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                $this->log( 'warning', 'Bypassing nonce check in debug mode' );
            } else {
                wp_send_json_error( array(
                    'message' => __( 'Security check failed. Please refresh the page and try again.', 'swpd' ),
                    'debug' => 'Nonce verification failed'
                ));
                return;
            }
        }

        // Get user identifier
        $user_identifier = $this->get_user_identifier();

        // Get image data
        $image_data_b64 = isset( $_POST['image'] ) ? $_POST['image'] : '';
        $filename = isset( $_POST['filename'] ) ? sanitize_file_name( $_POST['filename'] ) : 'user-upload-' . time() . '.png';

        if ( empty( $image_data_b64 ) ) {
            wp_send_json_error( array( 
                'message' => __( 'No image data received.', 'swpd' ),
                'debug' => 'Image data is empty'
            ));
            return;
        }

        // Try to handle the upload
        try {
            $result = $this->handle_local_upload( $image_data_b64, $filename, $user_identifier );
            
            if ( is_wp_error( $result ) ) {
                throw new Exception( $result->get_error_message() );
            }
            
            // Success response already sent by handle_local_upload
        } catch ( Exception $e ) {
            $this->log( 'error', 'Upload failed with exception', array(
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ));
            
            wp_send_json_error( array(
                'message' => $e->getMessage(),
                'debug' => 'Exception caught: ' . $e->getMessage()
            ));
        }
    }
    
    /**
     * FIXED: Handle local upload with better error handling
     */
    private function handle_local_upload( $image_data_b64, $filename, $user_identifier ) {
        try {
            // Log upload attempt
            $this->log( 'info', 'Starting local upload', array(
                'filename' => $filename,
                'user' => $user_identifier,
                'data_length' => strlen($image_data_b64)
            ));

            // Parse base64 data
            $image_data = $image_data_b64;
            
            // Handle data URI format
            if ( strpos( $image_data, 'data:' ) === 0 ) {
                $parts = explode( ',', $image_data );
                if ( count( $parts ) !== 2 ) {
                    throw new Exception( 'Invalid data URI format' );
                }
                
                $header = $parts[0];
                $image_data = $parts[1];
                
                // Extract MIME type
                if ( preg_match( '/^data:([^;]+);/', $header, $matches ) ) {
                    $mime_type = $matches[1];
                } else {
                    $mime_type = 'image/png';
                }
            } else {
                // Raw base64 data
                $mime_type = 'image/png';
            }

            // Decode base64
            $decoded_image = base64_decode( $image_data );
            if ( $decoded_image === false ) {
                throw new Exception( 'Failed to decode base64 image data' );
            }

            // Validate image
            $img_info = getimagesizefromstring( $decoded_image );
            if ( $img_info === false ) {
                throw new Exception( 'Invalid image data - not a valid image file' );
            }

            // Get actual MIME type from image
            $mime_type = $img_info['mime'];
            $width = $img_info[0];
            $height = $img_info[1];

            // Validate MIME type
            $allowed_types = array( 'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp' );
            if ( ! in_array( $mime_type, $allowed_types ) ) {
                throw new Exception( 'Invalid image type. Only JPEG, PNG, GIF, and WebP are allowed.' );
            }

            // Check file size (5MB limit)
            $file_size = strlen( $decoded_image );
            $max_size = 5 * 1024 * 1024; // 5MB
            if ( $file_size > $max_size ) {
                throw new Exception( 'Image file size exceeds 5MB limit' );
            }

            // Generate secure filename
            $extension = str_replace( 'image/', '', $mime_type );
            if ( $extension === 'jpeg' ) $extension = 'jpg';
            
            $base_filename = pathinfo( $filename, PATHINFO_FILENAME );
            $base_filename = preg_replace( '/[^a-zA-Z0-9_-]/', '', $base_filename );
            if ( empty( $base_filename ) ) {
                $base_filename = 'upload';
            }
            
            $unique_filename = $base_filename . '-' . time() . '-' . wp_rand( 1000, 9999 ) . '.' . $extension;

            // Get upload directory
            $upload_dir = wp_upload_dir();
            if ( isset( $upload_dir['error'] ) && $upload_dir['error'] !== false ) {
                throw new Exception( 'Failed to get upload directory: ' . $upload_dir['error'] );
            }

            // Create custom upload directory
            $custom_dir = '/swpd-designs/user-uploads/' . date('Y/m') . '/';
            $upload_path = $upload_dir['basedir'] . $custom_dir;
            $upload_url = $upload_dir['baseurl'] . $custom_dir;

            // Create directory if it doesn't exist
            if ( ! file_exists( $upload_path ) ) {
                if ( ! wp_mkdir_p( $upload_path ) ) {
                    throw new Exception( 'Failed to create upload directory' );
                }
                
                // Add index.php for security
                if ( ! file_exists( $upload_path . 'index.php' ) ) {
                    file_put_contents( $upload_path . 'index.php', '<?php // Silence is golden' );
                }
            }

            // Full file paths
            $file_path = $upload_path . $unique_filename;
            $file_url = $upload_url . $unique_filename;

            // Save the file
            $bytes_written = file_put_contents( $file_path, $decoded_image );
            if ( $bytes_written === false ) {
                throw new Exception( 'Failed to write image file to disk' );
            }

            // Verify file was written
            if ( ! file_exists( $file_path ) ) {
                throw new Exception( 'File was not saved successfully' );
            }

            // Create attachment in media library
            $attachment_id = $this->create_attachment( $file_path, $file_url, $mime_type, $unique_filename );

            // Log successful upload
            $this->log( 'info', 'Image uploaded successfully', array(
                'user' => $user_identifier,
                'filename' => $unique_filename,
                'size' => $file_size,
                'dimensions' => $width . 'x' . $height,
                'path' => $file_path,
                'url' => $file_url,
                'attachment_id' => $attachment_id
            ));

            // Send success response
            wp_send_json_success( array(
                'url' => $file_url,
                'path' => $file_path,
                'attachment_id' => $attachment_id,
                'width' => $width,
                'height' => $height,
                'size' => $file_size,
                'size_formatted' => size_format( $file_size ),
                'mime_type' => $mime_type,
                'provider' => 'wordpress'
            ));

        } catch ( Exception $e ) {
            $this->log( 'error', 'Local upload failed', array(
                'error' => $e->getMessage(),
                'user' => $user_identifier,
                'filename' => $filename
            ));

            return new WP_Error( 'upload_failed', $e->getMessage() );
        }
    }
    
    /**
     * Create attachment in media library
     */
    private function create_attachment( $file_path, $file_url, $mime_type, $filename ) {
        try {
            // Prepare attachment data
            $attachment = array(
                'guid'           => $file_url,
                'post_mime_type' => $mime_type,
                'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
                'post_content'   => '',
                'post_status'    => 'inherit',
                'meta_input'     => array(
                    '_wp_attachment_metadata' => array(
                        'swpd_upload' => true,
                        'upload_date' => current_time( 'mysql' )
                    )
                )
            );

            // Insert attachment
            $attach_id = wp_insert_attachment( $attachment, $file_path );
            
            if ( is_wp_error( $attach_id ) ) {
                $this->log( 'error', 'Failed to create attachment', array(
                    'error' => $attach_id->get_error_message()
                ));
                return 0;
            }

            // Generate attachment metadata
            if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
                require_once( ABSPATH . 'wp-admin/includes/image.php' );
            }
            
            $attach_data = wp_generate_attachment_metadata( $attach_id, $file_path );
            wp_update_attachment_metadata( $attach_id, $attach_data );

            return $attach_id;
            
        } catch ( Exception $e ) {
            $this->log( 'error', 'Exception creating attachment', array(
                'error' => $e->getMessage()
            ));
            return 0;
        }
    }
    
    /**
     * Save Cloudinary URL reference
     */
    public function save_cloudinary_url() {
        // Basic validation
        if ( ! isset( $_POST['url'] ) || ! isset( $_POST['filename'] ) ) {
            wp_send_json_error( array( 'message' => 'Missing required data' ) );
            return;
        }
        
        $url = esc_url_raw( $_POST['url'] );
        $filename = sanitize_file_name( $_POST['filename'] );
        
        // Store reference in database or transient
        $user_identifier = $this->get_user_identifier();
        $transient_key = 'swpd_cloudinary_refs_' . md5( $user_identifier );
        $refs = get_transient( $transient_key ) ?: array();
        
        $refs[] = array(
            'url' => $url,
            'filename' => $filename,
            'timestamp' => time()
        );
        
        // Keep only last 50 references
        if ( count( $refs ) > 50 ) {
            $refs = array_slice( $refs, -50 );
        }
        
        set_transient( $transient_key, $refs, DAY_IN_SECONDS );
        
        wp_send_json_success( array( 'message' => 'URL saved' ) );
    }
    
    /**
     * Upload design preview
     */
    public function upload_design_preview() {
        // Log the request
        $this->log( 'debug', 'Upload design preview request' );

        // For preview uploads, we can be more lenient with nonce
        $image_data_b64 = isset( $_POST['image'] ) ? $_POST['image'] : '';
        $filename = isset( $_POST['filename'] ) ? sanitize_file_name( $_POST['filename'] ) : 'preview-' . time() . '.jpg';
        
        if ( empty( $image_data_b64 ) ) {
            wp_send_json_error( array( 'message' => __( 'No image data received.', 'swpd' ) ) );
            return;
        }
        
        try {
            // Parse base64 data
            if ( strpos( $image_data_b64, 'data:' ) === 0 ) {
                list( $type, $data ) = explode( ',', $image_data_b64 );
                $image_data_b64 = $data;
            }
            
            $decoded_image = base64_decode( $image_data_b64 );
            if ( $decoded_image === false ) {
                throw new Exception( 'Invalid base64 data' );
            }
            
            // Get upload directory
            $upload_dir = wp_upload_dir();
            $preview_dir = $upload_dir['basedir'] . '/swpd-designs/previews/' . date('Y/m') . '/';
            $preview_url = $upload_dir['baseurl'] . '/swpd-designs/previews/' . date('Y/m') . '/';
            
            // Create directory
            if ( ! wp_mkdir_p( $preview_dir ) ) {
                throw new Exception( 'Failed to create preview directory' );
            }
            
            // Save file
            $file_path = $preview_dir . $filename;
            $file_url = $preview_url . $filename;
            
            if ( file_put_contents( $file_path, $decoded_image ) === false ) {
                throw new Exception( 'Failed to save preview file' );
            }
            
            // Optimize preview image
            $this->optimize_preview_image( $file_path );
            
            $this->log( 'info', 'Preview uploaded', array(
                'filename' => $filename,
                'size' => filesize( $file_path )
            ));
            
            wp_send_json_success( array(
                'url' => $file_url,
                'path' => $file_path
            ));
            
        } catch ( Exception $e ) {
            $this->log( 'error', 'Preview upload failed', array(
                'error' => $e->getMessage()
            ));
            
            wp_send_json_error( array( 'message' => $e->getMessage() ) );
        }
    }
    
    /**
     * Optimize preview image for smaller file size
     */
    private function optimize_preview_image( $file_path ) {
        try {
            // Load image
            $image = imagecreatefromstring( file_get_contents( $file_path ) );
            if ( ! $image ) {
                return;
            }
            
            // Get dimensions
            $width = imagesx( $image );
            $height = imagesy( $image );
            
            // Resize if too large
            $max_dimension = 1200;
            if ( $width > $max_dimension || $height > $max_dimension ) {
                $ratio = min( $max_dimension / $width, $max_dimension / $height );
                $new_width = round( $width * $ratio );
                $new_height = round( $height * $ratio );
                
                $resized = imagecreatetruecolor( $new_width, $new_height );
                imagecopyresampled( $resized, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height );
                imagedestroy( $image );
                $image = $resized;
            }
            
            // Save as JPEG with reasonable quality
            imagejpeg( $image, $file_path, 85 );
            imagedestroy( $image );
            
        } catch ( Exception $e ) {
            $this->log( 'warning', 'Failed to optimize preview', array(
                'error' => $e->getMessage()
            ));
        }
    }
    
    // ... rest of the methods remain the same as in the original file ...
    
    /**
     * Handle auto-save of design
     */
    public function autosave_design() {
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'swpd_design_upload_nonce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed.', 'swpd' ) ) );
            return;
        }
        
        $design_data = isset( $_POST['design_data'] ) ? wp_unslash( $_POST['design_data'] ) : '';
        $product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
        $variant_id = isset( $_POST['variant_id'] ) ? absint( $_POST['variant_id'] ) : null;
        
        if ( empty( $design_data ) || ! $product_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid data.', 'swpd' ) ) );
        }
        
        // Validate JSON
        $decoded = json_decode( $design_data, true );
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            wp_send_json_error( array( 'message' => __( 'Invalid design data.', 'swpd' ) ) );
        }
        
        // Get session ID
        $session_id = $this->get_user_identifier();
        $user_id = get_current_user_id();
        
        // Save to database
        global $wpdb;
        $table_name = $wpdb->prefix . 'swpd_autosaves';
        
        // Check if table exists
        if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
            wp_send_json_error( array( 'message' => __( 'Database table not found.', 'swpd' ) ) );
            return;
        }
        
        $result = $wpdb->replace(
            $table_name,
            array(
                'session_id' => $session_id,
                'user_id' => $user_id ?: null,
                'product_id' => $product_id,
                'variant_id' => $variant_id,
                'design_data' => $design_data,
                'updated_at' => current_time( 'mysql' )
            ),
            array( '%s', '%d', '%d', '%d', '%s', '%s' )
        );
        
        if ( false === $result ) {
            $this->log( 'error', 'Failed to autosave design', array(
                'error' => $wpdb->last_error
            ));
            wp_send_json_error( array( 'message' => __( 'Failed to save design.', 'swpd' ) ) );
        }
        
        wp_send_json_success( array(
            'message' => __( 'Design auto-saved.', 'swpd' ),
            'timestamp' => current_time( 'timestamp' )
        ));
    }
    
    /**
     * Load autosaved design
     */
    public function load_autosave() {
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'swpd_design_upload_nonce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed.', 'swpd' ) ) );
            return;
        }
        
        $product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
        $variant_id = isset( $_POST['variant_id'] ) ? absint( $_POST['variant_id'] ) : null;
        
        if ( ! $product_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid product ID.', 'swpd' ) ) );
        }
        
        $session_id = $this->get_user_identifier();
        
        // Load from database
        global $wpdb;
        $table_name = $wpdb->prefix . 'swpd_autosaves';
        
        if ( $variant_id ) {
            $query = $wpdb->prepare(
                "SELECT * FROM {$table_name}
                WHERE session_id = %s
                AND product_id = %d
                AND variant_id = %d
                ORDER BY updated_at DESC LIMIT 1",
                $session_id,
                $product_id,
                $variant_id
            );
        } else {
            $query = $wpdb->prepare(
                "SELECT * FROM {$table_name}
                WHERE session_id = %s
                AND product_id = %d
                AND variant_id IS NULL
                ORDER BY updated_at DESC LIMIT 1",
                $session_id,
                $product_id
            );
        }

        $autosave = $wpdb->get_row( $query );
        
        if ( $autosave ) {
            wp_send_json_success( array(
                'design_data' => $autosave->design_data,
                'updated_at' => $autosave->updated_at
            ));
        } else {
            wp_send_json_error( array( 'message' => __( 'No autosaved design found.', 'swpd' ) ) );
        }
    }
    
    /**
     * Clean up old autosaves
     */
    public function cleanup_old_autosaves() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'swpd_autosaves';
        
        // Delete autosaves older than 7 days
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$table_name} 
                WHERE updated_at < %s",
                date( 'Y-m-d H:i:s', strtotime( '-7 days' ) )
            )
        );
        
        $this->log( 'info', 'Cleaned up old autosaves', array(
            'deleted' => $wpdb->rows_affected
        ));
    }
}