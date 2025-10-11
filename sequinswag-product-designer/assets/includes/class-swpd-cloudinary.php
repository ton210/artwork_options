<?php
/**
 * SWPD Cloudinary Integration Class
 *
 * @package SWPD
 * @since 2.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class SWPD_Cloudinary
 * 
 * Handles Cloudinary integration for the plugin
 */
class SWPD_Cloudinary {
    
    /**
     * Logger instance
     *
     * @var SWPD_Logger
     */
    private $logger;
    
    /**
     * Cloudinary configuration
     *
     * @var array
     */
    private $config;
    
    /**
     * Debug mode
     *
     * @var bool
     */
    private $debug_mode = false;
    
    /**
     * Constructor
     *
     * @param SWPD_Logger $logger
     */
    public function __construct( $logger ) {
        $this->logger = $logger;
        $this->load_config();
        $this->debug_mode = get_option( 'swpd_cloudinary_debug', false );
    }
    
    /**
     * Initialize Cloudinary integration
     */
    public function init() {
        // Admin AJAX handlers
        add_action( 'wp_ajax_swpd_test_cloudinary', array( $this, 'ajax_test_cloudinary' ) );
        add_action( 'wp_ajax_swpd_upload_to_cloudinary', array( $this, 'ajax_upload_to_cloudinary' ) );
        add_action( 'wp_ajax_nopriv_swpd_upload_to_cloudinary', array( $this, 'ajax_upload_to_cloudinary' ) );
        
        // Add settings fields
        add_action( 'swpd_settings_cloudinary', array( $this, 'render_settings' ) );
        
        // Filter to use Cloudinary for uploads
        add_filter( 'swpd_upload_handler', array( $this, 'maybe_use_cloudinary' ), 10, 2 );
    }
    
    /**
     * Load Cloudinary configuration
     */
    private function load_config() {
        $this->config = array(
            'cloud_name' => get_option( 'swpd_cloudinary_cloud_name', '' ),
            'api_key' => get_option( 'swpd_cloudinary_api_key', '' ),
            'api_secret' => get_option( 'swpd_cloudinary_api_secret', '' ),
            'upload_preset' => get_option( 'swpd_cloudinary_upload_preset', '' ),
            'folder' => get_option( 'swpd_cloudinary_folder', 'swpd-designs' ),
            'enabled' => get_option( 'swpd_cloudinary_enabled', false ),
            'secure' => true
        );
        
        // Debug output
        if ( $this->debug_mode ) {
            $this->debug_log( 'Configuration loaded', array(
                'cloud_name' => $this->config['cloud_name'],
                'api_key' => substr( $this->config['api_key'], 0, 4 ) . '***',
                'has_secret' => ! empty( $this->config['api_secret'] ),
                'upload_preset' => $this->config['upload_preset'],
                'folder' => $this->config['folder'],
                'enabled' => $this->config['enabled']
            ));
        }
    }
    
    /**
     * AJAX handler for testing Cloudinary credentials
     */
    public function ajax_test_cloudinary() {
        check_ajax_referer( 'swpd_admin_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array(
                'message' => __( 'Insufficient permissions', 'swpd' ),
                'debug' => null
            ));
            return;
        }
        
        // Get credentials from POST if testing new ones
        $cloud_name = isset( $_POST['cloud_name'] ) ? sanitize_text_field( $_POST['cloud_name'] ) : $this->config['cloud_name'];
        $api_key = isset( $_POST['api_key'] ) ? sanitize_text_field( $_POST['api_key'] ) : $this->config['api_key'];
        $api_secret = isset( $_POST['api_secret'] ) ? sanitize_text_field( $_POST['api_secret'] ) : $this->config['api_secret'];
        $upload_preset = isset( $_POST['upload_preset'] ) ? sanitize_text_field( $_POST['upload_preset'] ) : $this->config['upload_preset'];
        
        $debug_info = array();
        
        // Step 1: Validate basic requirements
        if ( empty( $cloud_name ) ) {
            wp_send_json_error( array(
                'message' => __( 'Cloud Name is required', 'swpd' ),
                'debug' => array(
                    'step' => 'validation',
                    'error' => 'missing_cloud_name'
                )
            ));
            return;
        }
        
        $debug_info['validation'] = 'passed';
        $debug_info['cloud_name'] = $cloud_name;
        
        // Step 2: Determine authentication method
        $auth_method = 'unsigned';
        if ( ! empty( $api_key ) && ! empty( $api_secret ) ) {
            $auth_method = 'signed';
        } elseif ( ! empty( $upload_preset ) ) {
            $auth_method = 'unsigned_preset';
        }
        
        $debug_info['auth_method'] = $auth_method;
        
        // Step 3: Test API endpoint
        $test_results = $this->test_credentials( $cloud_name, $api_key, $api_secret, $upload_preset );
        
        if ( is_wp_error( $test_results ) ) {
            $error_data = $test_results->get_error_data();
            wp_send_json_error( array(
                'message' => $test_results->get_error_message(),
                'debug' => array_merge( $debug_info, array(
                    'error_code' => $test_results->get_error_code(),
                    'error_data' => $error_data,
                    'test_method' => $error_data['test_method'] ?? 'unknown',
                    'http_code' => $error_data['http_code'] ?? null,
                    'response_body' => $error_data['response_body'] ?? null,
                    'curl_error' => $error_data['curl_error'] ?? null
                ))
            ));
            return;
        }
        
        // Success - save credentials if they were provided
        if ( isset( $_POST['cloud_name'] ) ) {
            update_option( 'swpd_cloudinary_cloud_name', $cloud_name );
            update_option( 'swpd_cloudinary_api_key', $api_key );
            update_option( 'swpd_cloudinary_api_secret', $api_secret );
            update_option( 'swpd_cloudinary_upload_preset', $upload_preset );
            
            // Reload config
            $this->load_config();
        }
        
        wp_send_json_success( array(
            'message' => __( 'Cloudinary credentials are valid!', 'swpd' ),
            'debug' => array_merge( $debug_info, $test_results )
        ));
    }
    
    /**
     * Test Cloudinary credentials
     *
     * @param string $cloud_name
     * @param string $api_key
     * @param string $api_secret
     * @param string $upload_preset
     * @return array|WP_Error
     */
    private function test_credentials( $cloud_name, $api_key, $api_secret, $upload_preset ) {
        $debug_data = array();
        
        // Method 1: Try signed upload test (requires API key and secret)
        if ( ! empty( $api_key ) && ! empty( $api_secret ) ) {
            $debug_data['test_method'] = 'signed_ping';
            
            // Test using ping endpoint
            $timestamp = time();
            $auth_string = 'cloud_name=' . $cloud_name . '&timestamp=' . $timestamp . $api_secret;
            $signature = sha1( $auth_string );
            
            $ping_url = sprintf(
                'https://api.cloudinary.com/v1_1/%s/ping',
                $cloud_name
            );
            
            $response = wp_remote_get( $ping_url, array(
                'headers' => array(
                    'Authorization' => 'Basic ' . base64_encode( $api_key . ':' . $api_secret )
                ),
                'timeout' => 15,
                'sslverify' => true
            ));
            
            if ( is_wp_error( $response ) ) {
                return new WP_Error( 'connection_failed', 
                    sprintf( __( 'Failed to connect to Cloudinary: %s', 'swpd' ), $response->get_error_message() ),
                    array_merge( $debug_data, array(
                        'error' => $response->get_error_message(),
                        'curl_error' => $response->get_error_code()
                    ))
                );
            }
            
            $http_code = wp_remote_retrieve_response_code( $response );
            $body = wp_remote_retrieve_body( $response );
            
            $debug_data['ping_http_code'] = $http_code;
            $debug_data['ping_response'] = $body;
            
            if ( $http_code === 200 ) {
                return array_merge( array(
                    'status' => 'success',
                    'method' => 'signed',
                    'capabilities' => array( 'upload', 'transform', 'delete' )
                ), $debug_data );
            } elseif ( $http_code === 401 ) {
                return new WP_Error( 'invalid_credentials', 
                    __( 'Invalid API credentials. Please check your API Key and Secret.', 'swpd' ),
                    array_merge( $debug_data, array(
                        'http_code' => $http_code,
                        'response_body' => $body
                    ))
                );
            }
        }
        
        // Method 2: Try unsigned upload test (requires upload preset)
        if ( ! empty( $upload_preset ) ) {
            $debug_data['test_method'] = 'unsigned_upload_test';
            
            // Create a small test image
            $test_image = $this->create_test_image();
            
            $upload_url = sprintf(
                'https://api.cloudinary.com/v1_1/%s/image/upload',
                $cloud_name
            );
            
            $boundary = wp_generate_password( 24 );
            $body = $this->build_multipart_body( array(
                'upload_preset' => $upload_preset,
                'file' => 'data:image/png;base64,' . base64_encode( $test_image ),
                'folder' => $this->config['folder'] . '/test',
                'public_id' => 'test_' . time()
            ), $boundary );
            
            $response = wp_remote_post( $upload_url, array(
                'headers' => array(
                    'Content-Type' => 'multipart/form-data; boundary=' . $boundary
                ),
                'body' => $body,
                'timeout' => 30,
                'sslverify' => true
            ));
            
            if ( is_wp_error( $response ) ) {
                return new WP_Error( 'upload_test_failed', 
                    sprintf( __( 'Upload test failed: %s', 'swpd' ), $response->get_error_message() ),
                    array_merge( $debug_data, array(
                        'error' => $response->get_error_message()
                    ))
                );
            }
            
            $http_code = wp_remote_retrieve_response_code( $response );
            $body = wp_remote_retrieve_body( $response );
            $decoded = json_decode( $body, true );
            
            $debug_data['upload_http_code'] = $http_code;
            $debug_data['upload_response'] = $decoded;
            
            if ( $http_code === 200 && isset( $decoded['secure_url'] ) ) {
                // Delete test image
                if ( ! empty( $api_key ) && ! empty( $api_secret ) && isset( $decoded['public_id'] ) ) {
                    $this->delete_image( $decoded['public_id'], $api_key, $api_secret );
                }
                
                return array_merge( array(
                    'status' => 'success',
                    'method' => 'unsigned_preset',
                    'capabilities' => array( 'upload' ),
                    'test_upload_url' => $decoded['secure_url']
                ), $debug_data );
            } elseif ( isset( $decoded['error'] ) ) {
                $error_message = $decoded['error']['message'] ?? __( 'Unknown Cloudinary error', 'swpd' );
                return new WP_Error( 'cloudinary_error', 
                    sprintf( __( 'Cloudinary error: %s', 'swpd' ), $error_message ),
                    array_merge( $debug_data, array(
                        'http_code' => $http_code,
                        'response_body' => $decoded
                    ))
                );
            }
        }
        
        // Method 3: Basic connectivity test
        $debug_data['test_method'] = 'basic_connectivity';
        
        $base_url = sprintf( 'https://res.cloudinary.com/%s', $cloud_name );
        $response = wp_remote_head( $base_url, array(
            'timeout' => 10,
            'sslverify' => true
        ));
        
        if ( is_wp_error( $response ) ) {
            return new WP_Error( 'connection_failed', 
                __( 'Cannot connect to Cloudinary. Please check your internet connection.', 'swpd' ),
                array_merge( $debug_data, array(
                    'error' => $response->get_error_message()
                ))
            );
        }
        
        $http_code = wp_remote_retrieve_response_code( $response );
        if ( $http_code >= 200 && $http_code < 400 ) {
            return new WP_Error( 'incomplete_config', 
                __( 'Cloud name is valid but authentication is not configured. Please provide either API credentials or an upload preset.', 'swpd' ),
                array_merge( $debug_data, array(
                    'http_code' => $http_code,
                    'cloud_accessible' => true
                ))
            );
        } else {
            return new WP_Error( 'invalid_cloud_name', 
                __( 'Invalid cloud name or Cloudinary account not accessible.', 'swpd' ),
                array_merge( $debug_data, array(
                    'http_code' => $http_code
                ))
            );
        }
    }
    
    /**
     * Create a small test image
     *
     * @return string Binary image data
     */
    private function create_test_image() {
        // Create a 10x10 PNG
        $image = imagecreatetruecolor( 10, 10 );
        $color = imagecolorallocate( $image, 255, 0, 0 );
        imagefill( $image, 0, 0, $color );
        
        ob_start();
        imagepng( $image );
        $image_data = ob_get_clean();
        
        imagedestroy( $image );
        
        return $image_data;
    }
    
    /**
     * Build multipart form data body
     *
     * @param array $fields
     * @param string $boundary
     * @return string
     */
    private function build_multipart_body( $fields, $boundary ) {
        $body = '';
        
        foreach ( $fields as $name => $value ) {
            $body .= '--' . $boundary . "\r\n";
            $body .= 'Content-Disposition: form-data; name="' . $name . '"' . "\r\n\r\n";
            $body .= $value . "\r\n";
        }
        
        $body .= '--' . $boundary . '--' . "\r\n";
        
        return $body;
    }
    
    /**
     * Delete image from Cloudinary
     *
     * @param string $public_id
     * @param string $api_key
     * @param string $api_secret
     * @return bool
     */
    private function delete_image( $public_id, $api_key, $api_secret ) {
        $timestamp = time();
        $params = array(
            'public_id' => $public_id,
            'timestamp' => $timestamp
        );
        
        // Create signature
        $signature_string = http_build_query( $params ) . $api_secret;
        $signature = sha1( $signature_string );
        
        $params['signature'] = $signature;
        $params['api_key'] = $api_key;
        
        $delete_url = sprintf(
            'https://api.cloudinary.com/v1_1/%s/image/destroy',
            $this->config['cloud_name']
        );
        
        $response = wp_remote_post( $delete_url, array(
            'body' => $params,
            'timeout' => 10
        ));
        
        return ! is_wp_error( $response );
    }
    
    /**
     * AJAX handler for uploading to Cloudinary
     */
    public function ajax_upload_to_cloudinary() {
        check_ajax_referer( 'swpd_design_upload_nonce', 'nonce' );
        
        if ( ! $this->config['enabled'] ) {
            wp_send_json_error( array(
                'message' => __( 'Cloudinary is not enabled', 'swpd' )
            ));
            return;
        }
        
        $image_data = isset( $_POST['image'] ) ? $_POST['image'] : '';
        $filename = isset( $_POST['filename'] ) ? sanitize_file_name( $_POST['filename'] ) : 'upload';
        
        if ( empty( $image_data ) ) {
            wp_send_json_error( array(
                'message' => __( 'No image data provided', 'swpd' )
            ));
            return;
        }
        
        // Upload to Cloudinary
        $result = $this->upload_image( $image_data, $filename );
        
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array(
                'message' => $result->get_error_message(),
                'debug' => $this->debug_mode ? $result->get_error_data() : null
            ));
            return;
        }
        
        wp_send_json_success( $result );
    }
    
    /**
     * Upload image to Cloudinary
     *
     * @param string $image_data Base64 or binary image data
     * @param string $filename
     * @param array $options
     * @return array|WP_Error
     */
    public function upload_image( $image_data, $filename = '', $options = array() ) {
        if ( empty( $this->config['cloud_name'] ) ) {
            return new WP_Error( 'not_configured', __( 'Cloudinary is not configured', 'swpd' ) );
        }
        
        $upload_url = sprintf(
            'https://api.cloudinary.com/v1_1/%s/image/upload',
            $this->config['cloud_name']
        );
        
        // Prepare upload parameters
        $params = array(
            'folder' => $this->config['folder'],
            'public_id' => pathinfo( $filename, PATHINFO_FILENAME ) . '_' . time()
        );
        
        // Add custom options
        $params = array_merge( $params, $options );
        
        // Handle data URL
        if ( strpos( $image_data, 'data:image' ) === 0 ) {
            $params['file'] = $image_data;
        } else {
            // Assume it's base64
            $params['file'] = 'data:image/png;base64,' . base64_encode( $image_data );
        }
        
        // Signed upload
        if ( ! empty( $this->config['api_key'] ) && ! empty( $this->config['api_secret'] ) ) {
            $timestamp = time();
            $params['timestamp'] = $timestamp;
            $params['api_key'] = $this->config['api_key'];
            
            // Create signature
            $signature_params = $params;
            unset( $signature_params['file'], $signature_params['api_key'] );
            ksort( $signature_params );
            
            $signature_string = http_build_query( $signature_params ) . $this->config['api_secret'];
            $params['signature'] = sha1( $signature_string );
        } elseif ( ! empty( $this->config['upload_preset'] ) ) {
            // Unsigned upload with preset
            $params['upload_preset'] = $this->config['upload_preset'];
        } else {
            return new WP_Error( 'no_auth', __( 'No authentication method configured', 'swpd' ) );
        }
        
        // Debug log
        if ( $this->debug_mode ) {
            $this->debug_log( 'Upload attempt', array(
                'url' => $upload_url,
                'params' => array_diff_key( $params, array( 'file' => 1 ) ),
                'file_size' => strlen( $params['file'] )
            ));
        }
        
        // Perform upload
        $boundary = wp_generate_password( 24 );
        $body = $this->build_multipart_body( $params, $boundary );
        
        $response = wp_remote_post( $upload_url, array(
            'headers' => array(
                'Content-Type' => 'multipart/form-data; boundary=' . $boundary
            ),
            'body' => $body,
            'timeout' => 60,
            'sslverify' => true
        ));
        
        if ( is_wp_error( $response ) ) {
            $this->debug_log( 'Upload failed', array(
                'error' => $response->get_error_message()
            ));
            
            return new WP_Error( 'upload_failed', 
                sprintf( __( 'Upload failed: %s', 'swpd' ), $response->get_error_message() ),
                array( 'wp_error' => $response->get_error_message() )
            );
        }
        
        $http_code = wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );
        $result = json_decode( $body, true );
        
        if ( $this->debug_mode ) {
            $this->debug_log( 'Upload response', array(
                'http_code' => $http_code,
                'body' => $result
            ));
        }
        
        if ( $http_code !== 200 || ! isset( $result['secure_url'] ) ) {
            $error_message = isset( $result['error']['message'] ) 
                ? $result['error']['message'] 
                : __( 'Unknown upload error', 'swpd' );
                
            return new WP_Error( 'cloudinary_error', 
                $error_message,
                array(
                    'http_code' => $http_code,
                    'response' => $result
                )
            );
        }
        
        // Log successful upload
        $this->logger->info( 'Image uploaded to Cloudinary', array(
            'public_id' => $result['public_id'],
            'url' => $result['secure_url'],
            'size' => $result['bytes'] ?? 0
        ));
        
        return array(
            'url' => $result['secure_url'],
            'public_id' => $result['public_id'],
            'width' => $result['width'],
            'height' => $result['height'],
            'format' => $result['format'],
            'size' => size_format( $result['bytes'] ?? 0 ),
            'cloudinary_data' => $result
        );
    }
    
    /**
     * Maybe use Cloudinary for uploads
     *
     * @param mixed $handler
     * @param array $upload_data
     * @return mixed
     */
    public function maybe_use_cloudinary( $handler, $upload_data ) {
        if ( ! $this->config['enabled'] ) {
            return $handler;
        }
        
        // Check if this is an image upload
        if ( isset( $upload_data['type'] ) && strpos( $upload_data['type'], 'image' ) === 0 ) {
            return array( $this, 'handle_upload' );
        }
        
        return $handler;
    }
    
    /**
     * Render settings fields
     */
    public function render_settings() {
        ?>
        <h2><?php esc_html_e( 'Cloudinary Settings', 'swpd' ); ?></h2>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="swpd_cloudinary_enabled"><?php esc_html_e( 'Enable Cloudinary', 'swpd' ); ?></label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" name="swpd_cloudinary_enabled" id="swpd_cloudinary_enabled" value="1" <?php checked( $this->config['enabled'] ); ?> />
                        <?php esc_html_e( 'Use Cloudinary for image uploads', 'swpd' ); ?>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="swpd_cloudinary_cloud_name"><?php esc_html_e( 'Cloud Name', 'swpd' ); ?></label>
                </th>
                <td>
                    <input type="text" name="swpd_cloudinary_cloud_name" id="swpd_cloudinary_cloud_name" value="<?php echo esc_attr( $this->config['cloud_name'] ); ?>" class="regular-text" />
                    <p class="description"><?php esc_html_e( 'Your Cloudinary cloud name', 'swpd' ); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="swpd_cloudinary_api_key"><?php esc_html_e( 'API Key', 'swpd' ); ?></label>
                </th>
                <td>
                    <input type="text" name="swpd_cloudinary_api_key" id="swpd_cloudinary_api_key" value="<?php echo esc_attr( $this->config['api_key'] ); ?>" class="regular-text" />
                    <p class="description"><?php esc_html_e( 'Required for signed uploads (recommended)', 'swpd' ); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="swpd_cloudinary_api_secret"><?php esc_html_e( 'API Secret', 'swpd' ); ?></label>
                </th>
                <td>
                    <input type="password" name="swpd_cloudinary_api_secret" id="swpd_cloudinary_api_secret" value="<?php echo esc_attr( $this->config['api_secret'] ); ?>" class="regular-text" />
                    <p class="description"><?php esc_html_e( 'Required for signed uploads (recommended)', 'swpd' ); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="swpd_cloudinary_upload_preset"><?php esc_html_e( 'Upload Preset', 'swpd' ); ?></label>
                </th>
                <td>
                    <input type="text" name="swpd_cloudinary_upload_preset" id="swpd_cloudinary_upload_preset" value="<?php echo esc_attr( $this->config['upload_preset'] ); ?>" class="regular-text" />
                    <p class="description"><?php esc_html_e( 'For unsigned uploads (if not using API key/secret)', 'swpd' ); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="swpd_cloudinary_folder"><?php esc_html_e( 'Upload Folder', 'swpd' ); ?></label>
                </th>
                <td>
                    <input type="text" name="swpd_cloudinary_folder" id="swpd_cloudinary_folder" value="<?php echo esc_attr( $this->config['folder'] ); ?>" class="regular-text" />
                    <p class="description"><?php esc_html_e( 'Cloudinary folder for uploads', 'swpd' ); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="swpd_cloudinary_debug"><?php esc_html_e( 'Debug Mode', 'swpd' ); ?></label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" name="swpd_cloudinary_debug" id="swpd_cloudinary_debug" value="1" <?php checked( $this->debug_mode ); ?> />
                        <?php esc_html_e( 'Enable debug logging for Cloudinary operations', 'swpd' ); ?>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php esc_html_e( 'Test Connection', 'swpd' ); ?></th>
                <td>
                    <button type="button" class="button button-secondary" id="swpd-test-cloudinary">
                        <?php esc_html_e( 'Test Cloudinary Connection', 'swpd' ); ?>
                    </button>
                    <span class="spinner" style="float: none;"></span>
                    <div id="swpd-cloudinary-test-result" style="margin-top: 10px;"></div>
                    <div id="swpd-cloudinary-debug-info" style="margin-top: 10px; display: none;">
                        <h4><?php esc_html_e( 'Debug Information:', 'swpd' ); ?></h4>
                        <pre style="background: #f0f0f0; padding: 10px; overflow: auto; max-height: 400px;"></pre>
                    </div>
                </td>
            </tr>
        </table>
        
        <script>
        jQuery(document).ready(function($) {
            $('#swpd-test-cloudinary').on('click', function() {
                var $button = $(this);
                var $spinner = $button.next('.spinner');
                var $result = $('#swpd-cloudinary-test-result');
                var $debug = $('#swpd-cloudinary-debug-info');
                
                $button.prop('disabled', true);
                $spinner.addClass('is-active');
                $result.html('');
                $debug.hide();
                
                // Gather current form values
                var data = {
                    action: 'swpd_test_cloudinary',
                    nonce: swpdAdmin.nonce,
                    cloud_name: $('#swpd_cloudinary_cloud_name').val(),
                    api_key: $('#swpd_cloudinary_api_key').val(),
                    api_secret: $('#swpd_cloudinary_api_secret').val(),
                    upload_preset: $('#swpd_cloudinary_upload_preset').val()
                };
                
                $.post(swpdAdmin.ajaxUrl, data, function(response) {
                    $button.prop('disabled', false);
                    $spinner.removeClass('is-active');
                    
                    if (response.success) {
                        $result.html('<div class="notice notice-success inline"><p>' + response.data.message + '</p></div>');
                    } else {
                        $result.html('<div class="notice notice-error inline"><p>' + response.data.message + '</p></div>');
                    }
                    
                    // Show debug info if available
                    if (response.data && response.data.debug) {
                        $debug.find('pre').text(JSON.stringify(response.data.debug, null, 2));
                        $debug.show();
                        
                        console.log('Cloudinary Test Debug:', response.data.debug);
                    }
                }).fail(function(xhr, status, error) {
                    $button.prop('disabled', false);
                    $spinner.removeClass('is-active');
                    $result.html('<div class="notice notice-error inline"><p>AJAX Error: ' + error + '</p></div>');
                    
                    // Log to console
                    console.error('Cloudinary test failed:', {
                        status: status,
                        error: error,
                        response: xhr.responseText
                    });
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Debug log helper
     *
     * @param string $message
     * @param array $data
     */
    private function debug_log( $message, $data = array() ) {
        if ( $this->debug_mode ) {
            $this->logger->debug( '[Cloudinary] ' . $message, $data );
            
            // Also log to browser console if in admin
            if ( is_admin() ) {
                error_log( '[SWPD Cloudinary Debug] ' . $message . ' - ' . wp_json_encode( $data ) );
            }
        }
    }
    
    /**
     * Check if Cloudinary is properly configured
     *
     * @return bool
     */
    public function is_configured() {
        return ! empty( $this->config['cloud_name'] ) && 
               ( ( ! empty( $this->config['api_key'] ) && ! empty( $this->config['api_secret'] ) ) || 
                 ! empty( $this->config['upload_preset'] ) );
    }
    
    /**
     * Get configuration status
     *
     * @return array
     */
    public function get_status() {
        return array(
            'enabled' => $this->config['enabled'],
            'configured' => $this->is_configured(),
            'cloud_name' => ! empty( $this->config['cloud_name'] ),
            'auth_method' => ! empty( $this->config['api_key'] ) ? 'signed' : ( ! empty( $this->config['upload_preset'] ) ? 'unsigned' : 'none' ),
            'debug_mode' => $this->debug_mode
        );
    }
}