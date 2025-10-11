<?php
/**
 * Cloudinary Debug Test Page
 * Save this as templates/admin/cloudinary-test.php
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Initialize Cloudinary
$cloudinary = new SWPD_Cloudinary( $GLOBALS['swpd_logger'] ?? new SWPD_Logger() );
$status = $cloudinary->get_status();
?>

<div class="wrap">
    <h1>Cloudinary Debug Test</h1>
    
    <div class="card">
        <h2>Current Configuration</h2>
        <table class="widefat">
            <tr>
                <th>Setting</th>
                <th>Value</th>
                <th>Status</th>
            </tr>
            <tr>
                <td>Enabled</td>
                <td><?php echo $status['enabled'] ? 'Yes' : 'No'; ?></td>
                <td><?php echo $status['enabled'] ? '<span style="color:green">✓</span>' : '<span style="color:red">✗</span>'; ?></td>
            </tr>
            <tr>
                <td>Cloud Name</td>
                <td><?php echo esc_html( get_option( 'swpd_cloudinary_cloud_name', '' ) ); ?></td>
                <td><?php echo $status['cloud_name'] ? '<span style="color:green">✓</span>' : '<span style="color:red">✗</span>'; ?></td>
            </tr>
            <tr>
                <td>API Key</td>
                <td><?php 
                    $api_key = get_option( 'swpd_cloudinary_api_key', '' );
                    echo $api_key ? substr( $api_key, 0, 4 ) . '***' : 'Not set';
                ?></td>
                <td><?php echo ! empty( $api_key ) ? '<span style="color:green">✓</span>' : '<span style="color:orange">-</span>'; ?></td>
            </tr>
            <tr>
                <td>API Secret</td>
                <td><?php echo get_option( 'swpd_cloudinary_api_secret', '' ) ? '***' : 'Not set'; ?></td>
                <td><?php echo get_option( 'swpd_cloudinary_api_secret', '' ) ? '<span style="color:green">✓</span>' : '<span style="color:orange">-</span>'; ?></td>
            </tr>
            <tr>
                <td>Upload Preset</td>
                <td><?php echo esc_html( get_option( 'swpd_cloudinary_upload_preset', '' ) ); ?></td>
                <td><?php echo get_option( 'swpd_cloudinary_upload_preset', '' ) ? '<span style="color:green">✓</span>' : '<span style="color:orange">-</span>'; ?></td>
            </tr>
            <tr>
                <td>Authentication Method</td>
                <td><?php echo esc_html( $status['auth_method'] ); ?></td>
                <td><?php echo $status['auth_method'] !== 'none' ? '<span style="color:green">✓</span>' : '<span style="color:red">✗</span>'; ?></td>
            </tr>
            <tr>
                <td>Debug Mode</td>
                <td><?php echo $status['debug_mode'] ? 'Enabled' : 'Disabled'; ?></td>
                <td>-</td>
            </tr>
        </table>
    </div>
    
    <div class="card">
        <h2>Connection Test</h2>
        <p>Click the button below to test your Cloudinary configuration. This will show detailed debug information.</p>
        
        <button id="test-cloudinary-detailed" class="button button-primary">Run Detailed Connection Test</button>
        <div id="test-progress" style="display:none; margin-top: 20px;">
            <div class="spinner is-active" style="float:none;"></div>
            <p>Testing connection...</p>
        </div>
        
        <div id="test-results" style="margin-top: 20px; display:none;">
            <h3>Test Results:</h3>
            <div id="test-output"></div>
        </div>
    </div>
    
    <div class="card">
        <h2>Manual Tests</h2>
        
        <h3>1. Direct API Test</h3>
        <p>Test the Cloudinary API directly using cURL command:</p>
        <pre style="background: #f0f0f0; padding: 10px; overflow: auto;">
<?php 
$cloud_name = get_option( 'swpd_cloudinary_cloud_name', '' );
$api_key = get_option( 'swpd_cloudinary_api_key', '' );
$api_secret = get_option( 'swpd_cloudinary_api_secret', '' );

if ( $cloud_name && $api_key && $api_secret ) {
    echo "curl https://api.cloudinary.com/v1_1/{$cloud_name}/ping \\\n";
    echo "  -u {$api_key}:{YOUR_API_SECRET}";
} else {
    echo "# Configure your credentials first";
}
?>
        </pre>
        
        <h3>2. Browser Console Test</h3>
        <p>Open your browser console and run:</p>
        <pre style="background: #f0f0f0; padding: 10px;">
fetch('https://res.cloudinary.com/<?php echo esc_js( $cloud_name ); ?>/image/upload/v1/sample.jpg')
  .then(r => console.log('Status:', r.status))
  .catch(e => console.error('Error:', e));
        </pre>
        
        <h3>3. Test Upload</h3>
        <input type="file" id="test-upload-file" accept="image/*">
        <button id="test-upload" class="button">Test Upload to Cloudinary</button>
        <div id="upload-result" style="margin-top: 10px;"></div>
    </div>
    
    <div class="card">
        <h2>Server Information</h2>
        <table class="widefat">
            <tr>
                <th>Check</th>
                <th>Status</th>
                <th>Details</th>
            </tr>
            <tr>
                <td>cURL Extension</td>
                <td><?php echo extension_loaded( 'curl' ) ? '<span style="color:green">✓</span>' : '<span style="color:red">✗</span>'; ?></td>
                <td><?php echo extension_loaded( 'curl' ) ? 'Enabled' : 'Required for API calls'; ?></td>
            </tr>
            <tr>
                <td>SSL/TLS Support</td>
                <td><?php 
                    $curl_version = curl_version();
                    echo isset( $curl_version['ssl_version'] ) ? '<span style="color:green">✓</span>' : '<span style="color:red">✗</span>'; 
                ?></td>
                <td><?php echo isset( $curl_version['ssl_version'] ) ? $curl_version['ssl_version'] : 'Not available'; ?></td>
            </tr>
            <tr>
                <td>allow_url_fopen</td>
                <td><?php echo ini_get( 'allow_url_fopen' ) ? '<span style="color:green">✓</span>' : '<span style="color:orange">!</span>'; ?></td>
                <td><?php echo ini_get( 'allow_url_fopen' ) ? 'Enabled' : 'May affect remote connections'; ?></td>
            </tr>
            <tr>
                <td>WordPress HTTP API</td>
                <td><span style="color:green">✓</span></td>
                <td>Using: <?php echo _wp_http_get_object()->_get_first_available_transport( array(), 'https://api.cloudinary.com' ); ?></td>
            </tr>
        </table>
    </div>
    
    <div class="card">
        <h2>Recent Logs</h2>
        <button id="refresh-logs" class="button">Refresh Logs</button>
        <button id="clear-logs" class="button">Clear Logs</button>
        <div id="log-output" style="margin-top: 10px; background: #f0f0f0; padding: 10px; max-height: 400px; overflow: auto; font-family: monospace; font-size: 12px;">
            <?php
            // Get recent Cloudinary-related logs
            $logger = new SWPD_Logger();
            $logs = $logger->get_recent_entries( 50 );
            $cloudinary_logs = array_filter( $logs, function( $log ) {
                return strpos( $log['message'], 'Cloudinary' ) !== false || 
                       strpos( $log['message'], 'cloudinary' ) !== false;
            });
            
            if ( empty( $cloudinary_logs ) ) {
                echo "No Cloudinary-related logs found.\n";
            } else {
                foreach ( $cloudinary_logs as $log ) {
                    $color = $log['level'] === 'error' ? 'red' : ( $log['level'] === 'warning' ? 'orange' : 'black' );
                    echo sprintf(
                        '<span style="color: %s">[%s] [%s] %s</span>' . "\n",
                        $color,
                        $log['timestamp'],
                        strtoupper( $log['level'] ),
                        esc_html( $log['message'] )
                    );
                }
            }
            ?>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Detailed connection test
    $('#test-cloudinary-detailed').on('click', function() {
        var $button = $(this);
        var $progress = $('#test-progress');
        var $results = $('#test-results');
        var $output = $('#test-output');
        
        $button.prop('disabled', true);
        $progress.show();
        $results.hide();
        $output.html('');
        
        // Enable debug mode temporarily
        $.post(ajaxurl, {
            action: 'swpd_test_cloudinary',
            nonce: '<?php echo wp_create_nonce( 'swpd_admin_nonce' ); ?>',
            debug: true
        }, function(response) {
            $button.prop('disabled', false);
            $progress.hide();
            $results.show();
            
            var html = '';
            
            if (response.success) {
                html += '<div class="notice notice-success"><p><strong>' + response.data.message + '</strong></p></div>';
            } else {
                html += '<div class="notice notice-error"><p><strong>' + response.data.message + '</strong></p></div>';
            }
            
            // Show debug information
            if (response.data && response.data.debug) {
                html += '<h4>Debug Information:</h4>';
                html += '<pre style="background: #f0f0f0; padding: 10px; overflow: auto; max-height: 400px;">';
                html += JSON.stringify(response.data.debug, null, 2);
                html += '</pre>';
                
                // Log to console as well
                console.log('Cloudinary Test Results:', response.data);
            }
            
            $output.html(html);
        }).fail(function(xhr, status, error) {
            $button.prop('disabled', false);
            $progress.hide();
            $results.show();
            
            var html = '<div class="notice notice-error"><p><strong>AJAX Request Failed</strong></p></div>';
            html += '<h4>Error Details:</h4>';
            html += '<pre style="background: #f0f0f0; padding: 10px;">';
            html += 'Status: ' + status + '\n';
            html += 'Error: ' + error + '\n';
            html += 'Response: ' + xhr.responseText;
            html += '</pre>';
            
            $output.html(html);
            
            console.error('Cloudinary test failed:', {
                status: status,
                error: error,
                response: xhr.responseText
            });
        });
    });
    
    // Test upload
    $('#test-upload').on('click', function() {
        var fileInput = $('#test-upload-file')[0];
        var $result = $('#upload-result');
        
        if (!fileInput.files || !fileInput.files[0]) {
            $result.html('<div class="notice notice-error inline"><p>Please select a file first</p></div>');
            return;
        }
        
        var file = fileInput.files[0];
        var reader = new FileReader();
        
        $result.html('<div class="spinner is-active" style="float:none;"></div> Uploading...');
        
        reader.onload = function(e) {
            $.post(ajaxurl, {
                action: 'swpd_upload_to_cloudinary',
                nonce: '<?php echo wp_create_nonce( 'swpd_design_upload_nonce' ); ?>',
                image: e.target.result,
                filename: file.name
            }, function(response) {
                if (response.success) {
                    var html = '<div class="notice notice-success inline"><p>Upload successful!</p></div>';
                    html += '<div style="margin-top: 10px;">';
                    html += '<img src="' + response.data.url + '" style="max-width: 300px; max-height: 300px;">';
                    html += '<pre style="background: #f0f0f0; padding: 10px; margin-top: 10px;">';
                    html += JSON.stringify(response.data, null, 2);
                    html += '</pre>';
                    html += '</div>';
                    $result.html(html);
                } else {
                    var html = '<div class="notice notice-error inline"><p>' + response.data.message + '</p></div>';
                    if (response.data.debug) {
                        html += '<pre style="background: #f0f0f0; padding: 10px; margin-top: 10px;">';
                        html += JSON.stringify(response.data.debug, null, 2);
                        html += '</pre>';
                    }
                    $result.html(html);
                }
            }).fail(function(xhr, status, error) {
                $result.html('<div class="notice notice-error inline"><p>Upload failed: ' + error + '</p></div>');
            });
        };
        
        reader.readAsDataURL(file);
    });
    
    // Refresh logs
    $('#refresh-logs').on('click', function() {
        location.reload();
    });
    
    // Clear logs
    $('#clear-logs').on('click', function() {
        if (confirm('Are you sure you want to clear all logs?')) {
            $.post(ajaxurl, {
                action: 'swpd_clear_logs',
                nonce: '<?php echo wp_create_nonce( 'swpd_admin_nonce' ); ?>'
            }, function(response) {
                location.reload();
            });
        }
    });
});
</script>

<style>
.card {
    background: #fff;
    border: 1px solid #ccd0d4;
    padding: 20px;
    margin: 20px 0;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.card h2 {
    margin-top: 0;
}

.card h3 {
    margin-top: 20px;
}

pre {
    white-space: pre-wrap;
    word-wrap: break-word;
}

.notice.inline {
    margin: 0;
}
</style>