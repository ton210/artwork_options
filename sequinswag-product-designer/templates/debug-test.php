<?php
/**
 * Debug Test Page
 * Access via: /wp-admin/admin.php?page=swpd-debug-test
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Handle design data save
if ( isset( $_POST['save_design_data'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'swpd_save_design_data' ) ) {
    $product_id = absint( $_POST['product_id'] );
    $design_data = wp_unslash( $_POST['design_data'] );

    // Validate JSON
    $decoded = json_decode( $design_data, true );
    if ( json_last_error() === JSON_ERROR_NONE ) {
        update_post_meta( $product_id, 'design_tool_layer', $design_data );
        echo '<div class="notice notice-success"><p>Design data saved!</p></div>';
    } else {
        echo '<div class="notice notice-error"><p>Invalid JSON: ' . json_last_error_msg() . '</p></div>';
    }
}

// Get test product ID from URL or use the first product with design data
$test_product_id = isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : 0;

if ( ! $test_product_id ) {
    // Find a product with design data
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => 10,
        'meta_query' => array(
            array(
                'key' => 'design_tool_layer',
                'compare' => 'EXISTS'
            )
        )
    );
    $products = get_posts( $args );
    if ( ! empty( $products ) ) {
        $test_product_id = $products[0]->ID;
    }
}

$product = $test_product_id ? wc_get_product( $test_product_id ) : null;

// Load the designer scripts for testing
wp_enqueue_script( 'swpd-enhanced-product-designer', SWPD_PLUGIN_URL . 'assets/js/enhanced-product-designer-modular.js', array( 'jquery' ), SWPD_VERSION, true );

// Prepare test config
if ( $product ) {
    $variants_data = [];

    if ( $product->is_type( 'variable' ) ) {
        $variations = $product->get_available_variations();
        foreach ( $variations as $variation_array ) {
            $variation_id = $variation_array['variation_id'];
            $design_tool_layer = get_post_meta( $variation_id, 'design_tool_layer', true );
            $variants_data[] = [
                'id' => $variation_id,
                'title' => implode( ' - ', $variation_array['attributes'] ),
                'designer_data' => $design_tool_layer ? (is_string($design_tool_layer) ? json_decode( $design_tool_layer, true ) : $design_tool_layer) : null,
            ];
        }
    } else {
        $design_tool_layer = get_post_meta( $test_product_id, 'design_tool_layer', true );
        $variants_data[] = [
            'id' => $test_product_id,
            'title' => $product->get_name(),
            'designer_data' => $design_tool_layer ? (is_string($design_tool_layer) ? json_decode( $design_tool_layer, true ) : $design_tool_layer) : null,
        ];
    }

    wp_localize_script( 'swpd-enhanced-product-designer', 'swpdDesignerConfig', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'product_id' => $test_product_id,
        'product_type' => $product->get_type(),
        'variants' => $variants_data,
        'nonce' => wp_create_nonce( 'swpd_design_upload_nonce' ),
        'debug_mode' => true,
        'version' => SWPD_VERSION
    ));
}
?>

<div class="wrap">
    <h1>SWPD Debug Test</h1>

    <div class="card">
        <h2>Product Selection</h2>
        <form method="get" action="">
            <input type="hidden" name="page" value="swpd-debug-test">
            <label>Product ID: <input type="number" name="product_id" value="<?php echo esc_attr( $test_product_id ); ?>"></label>
            <button type="submit" class="button button-primary">Test Product</button>
        </form>
    </div>

    <?php if ( $product ) : ?>
    <div class="card">
        <h2>Product Information</h2>
        <table class="widefat">
            <tr>
                <th>Product Name</th>
                <td><?php echo esc_html( $product->get_name() ); ?></td>
            </tr>
            <tr>
                <th>Product Type</th>
                <td><?php echo esc_html( $product->get_type() ); ?></td>
            </tr>
            <tr>
                <th>Product URL</th>
                <td>
                    <a href="<?php echo esc_url( $product->get_permalink() ); ?>?swpd_debug=1" target="_blank">
                        View Product with Debug Mode
                    </a>
                </td>
            </tr>
        </table>
    </div>

    <div class="card">
        <h2>Design Data</h2>
        <?php
        if ( $product->is_type( 'variable' ) ) {
            $variations = $product->get_available_variations();
            ?>
            <h3>Variations (<?php echo count( $variations ); ?>)</h3>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>Variation ID</th>
                        <th>Attributes</th>
                        <th>Has Design Data</th>
                        <th>Design Data</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $variations as $variation ) :
                        $design_data = get_post_meta( $variation['variation_id'], 'design_tool_layer', true );
                        $decoded = $design_data ? (is_string($design_data) ? json_decode( $design_data, true ) : $design_data) : null;
                    ?>
                    <tr>
                        <td><?php echo esc_html( $variation['variation_id'] ); ?></td>
                        <td><?php echo esc_html( implode( ', ', $variation['attributes'] ) ); ?></td>
                        <td>
                            <?php if ( $design_data ) : ?>
                                <span style="color: green;">✓ Yes</span>
                            <?php else : ?>
                                <span style="color: red;">✗ No</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ( $decoded ) : ?>
                                <details>
                                    <summary>View Data</summary>
                                    <pre style="background: #f0f0f0; padding: 10px; overflow: auto;"><?php
                                        echo esc_html( json_encode( $decoded, JSON_PRETTY_PRINT ) );
                                    ?></pre>
                                </details>
                            <?php else : ?>
                                <em>No data</em>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="button add-design-data" data-variation-id="<?php echo esc_attr( $variation['variation_id'] ); ?>">
                                <?php echo $design_data ? 'Edit' : 'Add'; ?> Design Data
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php
        } else {
            $design_data = get_post_meta( $product->get_id(), 'design_tool_layer', true );
            $decoded = $design_data ? (is_string($design_data) ? json_decode( $design_data, true ) : $design_data) : null;
            ?>
            <table class="widefat">
                <tr>
                    <th>Has Design Data</th>
                    <td>
                        <?php if ( $design_data ) : ?>
                            <span style="color: green;">✓ Yes</span>
                        <?php else : ?>
                            <span style="color: red;">✗ No</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php if ( $decoded ) : ?>
                <tr>
                    <th>Design Data</th>
                    <td>
                        <pre style="background: #f0f0f0; padding: 10px; overflow: auto;"><?php
                            echo esc_html( json_encode( $decoded, JSON_PRETTY_PRINT ) );
                        ?></pre>
                    </td>
                </tr>
                <tr>
                    <th>Data Validation</th>
                    <td>
                        <?php
                        $validation_errors = array();

                        // Check required fields
                        if ( empty( $decoded['baseImage'] ) ) {
                            $validation_errors[] = 'Missing baseImage';
                        }
                        if ( empty( $decoded['alphaMask'] ) ) {
                            $validation_errors[] = 'Missing alphaMask';
                        }

                        // Check URLs
                        if ( ! empty( $decoded['baseImage'] ) && ! filter_var( $decoded['baseImage'], FILTER_VALIDATE_URL ) ) {
                            $validation_errors[] = 'Invalid baseImage URL';
                        }
                        if ( ! empty( $decoded['alphaMask'] ) && ! filter_var( $decoded['alphaMask'], FILTER_VALIDATE_URL ) ) {
                            $validation_errors[] = 'Invalid alphaMask URL';
                        }

                        if ( empty( $validation_errors ) ) {
                            echo '<span style="color: green;">✓ Valid</span>';
                        } else {
                            echo '<span style="color: red;">✗ Errors:</span><ul>';
                            foreach ( $validation_errors as $error ) {
                                echo '<li>' . esc_html( $error ) . '</li>';
                            }
                            echo '</ul>';
                        }
                        ?>
                    </td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th>Actions</th>
                    <td>
                        <button class="button add-design-data" data-variation-id="<?php echo esc_attr( $product->get_id() ); ?>">
                            <?php echo $design_data ? 'Edit' : 'Add'; ?> Design Data
                        </button>
                    </td>
                </tr>
            </table>
            <?php
        }
        ?>
    </div>

    <div class="card" id="design-data-form" style="display: none;">
        <h2>Add/Edit Design Data</h2>
        <form method="post" action="">
            <?php wp_nonce_field( 'swpd_save_design_data' ); ?>
            <input type="hidden" name="save_design_data" value="1">
            <input type="hidden" name="product_id" id="form-product-id" value="">

            <h3>Quick Template</h3>
            <button type="button" class="button" id="use-template">Use Sample Template</button>

            <h3>Design Data JSON</h3>
            <textarea name="design_data" id="design-data-textarea" rows="15" style="width: 100%; font-family: monospace;"></textarea>

            <p>
                <button type="submit" class="button button-primary">Save Design Data</button>
                <button type="button" class="button" id="cancel-form">Cancel</button>
            </p>
        </form>
    </div>

    <div class="card">
        <h2>JavaScript Test</h2>
        <button id="test-designer-init" class="button button-primary">Test Designer Initialization</button>
        <button id="test-designer-open" class="button">Test Open Designer</button>
        <div id="test-results" style="margin-top: 20px;"></div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        // Add design data button
        $('.add-design-data').on('click', function() {
            var variationId = $(this).data('variation-id');
            $('#form-product-id').val(variationId);
            $('#design-data-form').show();

            // Load existing data if any
            var existingData = $(this).closest('tr').find('pre').text();
            if (existingData && existingData !== 'No data') {
                $('#design-data-textarea').val(existingData);
            } else {
                $('#design-data-textarea').val('');
            }
        });

        // Use template button
        $('#use-template').on('click', function() {
            var sampleData = {
                "baseImage": "<?php echo esc_js( SWPD_PLUGIN_URL ); ?>assets/sample/base-pillow.jpg",
                "alphaMask": "<?php echo esc_js( SWPD_PLUGIN_URL ); ?>assets/sample/alpha-mask.png",
                "unclippedMask": "<?php echo esc_js( SWPD_PLUGIN_URL ); ?>assets/sample/unclipped-mask.png"
            };

            $('#design-data-textarea').val(JSON.stringify(sampleData, null, 2));
        });

        // Cancel form
        $('#cancel-form').on('click', function() {
            $('#design-data-form').hide();
        });

        // Test initialization
        $('#test-designer-init').on('click', function() {
            const results = $('#test-results');
            results.html('<h3>Test Results:</h3>');

            // Test 1: Check if config is loaded
            if (typeof swpdDesignerConfig !== 'undefined') {
                results.append('<p style="color: green;">✓ swpdDesignerConfig is loaded</p>');
                results.append('<pre>' + JSON.stringify(swpdDesignerConfig, null, 2) + '</pre>');
            } else {
                results.append('<p style="color: red;">✗ swpdDesignerConfig is NOT loaded</p>');
            }

            // Test 2: Check if designer class exists
            if (typeof EnhancedProductDesigner !== 'undefined') {
                results.append('<p style="color: green;">✓ EnhancedProductDesigner class exists</p>');
            } else {
                results.append('<p style="color: red;">✗ EnhancedProductDesigner class NOT found</p>');
            }

            // Test 3: Check if instance exists
            if (typeof window.customDesigner !== 'undefined') {
                results.append('<p style="color: green;">✓ window.customDesigner instance exists</p>');
            } else {
                results.append('<p style="color: red;">✗ window.customDesigner instance NOT found</p>');

                // Try to create instance
                try {
                    window.customDesigner = new EnhancedProductDesigner(swpdDesignerConfig);
                    results.append('<p style="color: green;">✓ Successfully created customDesigner instance</p>');
                } catch (error) {
                    results.append('<p style="color: red;">✗ Error creating instance: ' + error.message + '</p>');
                }
            }
        });

        // Test open designer
        $('#test-designer-open').on('click', function() {
            if (typeof window.customDesigner !== 'undefined' && window.customDesigner.openLightbox) {
                try {
                    var variantId = <?php
                        if ( $product && $product->is_type( 'variable' ) ) {
                            $variations = $product->get_available_variations();
                            echo ! empty( $variations ) ? $variations[0]['variation_id'] : $test_product_id;
                        } else {
                            echo $test_product_id;
                        }
                    ?>;
                    window.customDesigner.openLightbox(variantId);
                    $('#test-results').append('<p style="color: green;">✓ Designer opened successfully</p>');
                } catch (error) {
                    $('#test-results').append('<p style="color: red;">✗ Error opening designer: ' + error.message + '</p>');
                    console.error('Error:', error);
                }
            } else {
                alert('Please run "Test Designer Initialization" first');
            }
        });
    });
    </script>

    <?php endif; ?>

    <div class="card">
        <h2>Quick Instructions</h2>
        <ol>
            <li><strong>No Design Data?</strong> Click "Add Design Data" button to add configuration to your product/variation.</li>
            <li>Use the "Use Sample Template" button to get started with sample images.</li>
            <li>Replace the URLs with your actual product base image and mask images.</li>
            <li>Save the design data and then test the designer.</li>
            <li>Visit the product page to see the designer in action.</li>
        </ol>

        <h3>Required Image Files:</h3>
        <ul>
            <li><strong>baseImage:</strong> The base product image (e.g., blank pillow)</li>
            <li><strong>alphaMask:</strong> Black and white mask defining the customizable area</li>
            <li><strong>unclippedMask:</strong> (Optional) Visual guide showing the design area</li>
        </ul>
    </div>

    <div class="card">
        <h2>Sample Images</h2>
        <p>Create these sample images in your plugin directory:</p>
        <ul>
            <li><code><?php echo SWPD_PLUGIN_DIR; ?>assets/sample/base-pillow.jpg</code></li>
            <li><code><?php echo SWPD_PLUGIN_DIR; ?>assets/sample/alpha-mask.png</code></li>
            <li><code><?php echo SWPD_PLUGIN_DIR; ?>assets/sample/unclipped-mask.png</code></li>
        </ul>
        <p>Or upload your own images to the Media Library and use those URLs.</p>
    </div>

    <div class="card">
        <h2>System Status</h2>
        <table class="widefat">
            <tr>
                <th>PHP Version</th>
                <td><?php echo PHP_VERSION; ?></td>
            </tr>
            <tr>
                <th>WordPress Version</th>
                <td><?php echo get_bloginfo( 'version' ); ?></td>
            </tr>
            <tr>
                <th>WooCommerce Version</th>
                <td><?php echo defined( 'WC_VERSION' ) ? WC_VERSION : 'Not detected'; ?></td>
            </tr>
            <tr>
                <th>Plugin Version</th>
                <td><?php echo SWPD_VERSION; ?></td>
            </tr>
            <tr>
                <th>GD Library</th>
                <td><?php echo extension_loaded( 'gd' ) ? '✓ Enabled' : 'status-good'; ?></td>
            </tr>
            <tr>
                <th>ImageMagick</th>
                <td><?php echo extension_loaded( 'imagick' ) ? '✓ Enabled' : 'status-warning'; ?></td>
            </tr>
        </table>
    </div>
</div>

<?php include SWPD_PLUGIN_DIR . 'templates/designer-lightbox.php'; ?>

<style>
.card {
    background: #fff;
    border: 1px solid #ccd0d4;
    padding: 20px;
    margin: 20px 0;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}
details {
    margin-top: 10px;
}
pre {
    white-space: pre-wrap;
    word-wrap: break-word;
}
#design-data-form {
    background: #f9f9f9;
    border: 2px solid #0073aa;
}
</style>