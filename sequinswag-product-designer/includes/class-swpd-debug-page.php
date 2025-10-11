<?php
/**
 * Debug Test Page for SequinSwag Product Designer
 */

if (!defined('ABSPATH')) {
    exit;
}

class SWPD_Debug_Page {
    
    public function __construct() {
        add_action('wp_ajax_swpd_debug_page', array($this, 'render_debug_page'));
        add_action('wp_ajax_nopriv_swpd_debug_page', array($this, 'render_debug_page'));
        add_action('init', array($this, 'handle_debug_page_request'));
    }
    
    public function handle_debug_page_request() {
        if (isset($_GET['swpd_debug']) && $_GET['swpd_debug'] === '1') {
            $this->render_debug_page();
            exit;
        }
    }
    
    public function render_debug_page() {
        // Set proper headers
        header('Content-Type: text/html; charset=UTF-8');
        
        // Get real product data for ID 1215
        $product_id = 1215;
        $product = wc_get_product($product_id);
        $variants = array();
        
        if ($product && $product->is_type('variable')) {
            $variations = $product->get_available_variations();
            foreach ($variations as $variation) {
                $variation_obj = wc_get_product($variation['variation_id']);
                $design_data = $variation_obj->get_meta('_design_tool_layer');
                
                $variants[] = array(
                    'id' => $variation['variation_id'],
                    'name' => implode(' - ', array_values($variation['attributes'])),
                    '_design_tool_layer' => $design_data ?: json_encode(array(
                        'baseImage' => '',
                        'alphaMask' => ''
                    ))
                );
            }
        } else if ($product) {
            // Simple product
            $design_data = $product->get_meta('_design_tool_layer');
            $variants[] = array(
                'id' => $product_id,
                'name' => $product->get_name(),
                '_design_tool_layer' => $design_data ?: json_encode(array(
                    'baseImage' => '',
                    'alphaMask' => ''
                ))
            );
        }
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>SequinSwag Designer - QA Debug Test</title>
            
            <!-- Latest Fabric.js -->
            <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/6.0.0-beta21/fabric.min.js"></script>
            
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                
                body {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh;
                    padding: 20px;
                }
                
                .debug-container {
                    max-width: 1400px;
                    margin: 0 auto;
                }
                
                .debug-header {
                    background: rgba(255,255,255,0.1);
                    padding: 20px;
                    border-radius: 15px;
                    margin-bottom: 20px;
                    backdrop-filter: blur(10px);
                }
                
                .debug-header h1 {
                    color: white;
                    text-align: center;
                    margin-bottom: 10px;
                    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
                }
                
                .debug-header .status {
                    text-align: center;
                    color: rgba(255,255,255,0.9);
                    font-size: 14px;
                }
                
                .main-wrapper {
                    background: white;
                    border-radius: 20px;
                    padding: 30px;
                    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
                }
                
                .debug-layout {
                    display: grid;
                    grid-template-columns: 300px 1fr 350px;
                    gap: 20px;
                    height: 70vh;
                }
                
                /* Controls Panel */
                .controls-panel {
                    background: #f8f9fa;
                    border-radius: 12px;
                    padding: 20px;
                    overflow-y: auto;
                }
                
                .control-section {
                    margin-bottom: 25px;
                    padding-bottom: 20px;
                    border-bottom: 1px solid #dee2e6;
                }
                
                .control-section h3 {
                    color: #495057;
                    font-size: 14px;
                    font-weight: 600;
                    margin-bottom: 15px;
                }
                
                .btn {
                    display: block;
                    width: 100%;
                    padding: 8px 12px;
                    border: none;
                    border-radius: 6px;
                    font-size: 12px;
                    cursor: pointer;
                    margin-bottom: 8px;
                    text-align: center;
                    text-decoration: none;
                    color: white;
                }
                
                .btn-primary { background: #007bff; }
                .btn-danger { background: #dc3545; }
                .btn-success { background: #28a745; }
                .btn-warning { background: #ffc107; color: #212529; }
                
                /* Canvas Area */
                .canvas-area {
                    background: #f8f9fa;
                    border-radius: 12px;
                    padding: 20px;
                    text-align: center;
                }
                
                .canvas-wrapper {
                    display: inline-block;
                    position: relative;
                    background: white;
                    border-radius: 8px;
                    padding: 10px;
                    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                }
                
                #design-canvas {
                    border: 2px solid #dee2e6;
                    border-radius: 6px;
                }
                
                /* Debug Panel */
                .debug-panel {
                    background: #212529;
                    color: #fff;
                    border-radius: 12px;
                    padding: 15px;
                    overflow-y: auto;
                    font-family: monospace;
                    font-size: 11px;
                }
                
                .debug-info {
                    background: #2d3748;
                    border-radius: 6px;
                    padding: 8px;
                    margin-bottom: 10px;
                }
                
                .debug-log {
                    background: #000;
                    border: 1px solid #495057;
                    border-radius: 6px;
                    padding: 8px;
                    height: 200px;
                    overflow-y: auto;
                    font-size: 10px;
                    line-height: 1.3;
                }
                
                .status-indicator {
                    display: inline-block;
                    width: 8px;
                    height: 8px;
                    border-radius: 50%;
                    margin-right: 5px;
                }
                
                .status-ok { background: #28a745; }
                .status-error { background: #dc3545; }
                
                .error { color: #ff6b6b; }
                .success { color: #48dbfb; }
                .warning { color: #feca57; }
            </style>
        </head>
        <body>
            <div class="debug-container">
                <div class="debug-header">
                    <h1>🔧 SequinSwag Designer - Debug Test</h1>
                    <div class="status">
                        <span id="status-indicator" class="status-indicator status-error"></span>
                        <span id="status-text">Initializing...</span>
                    </div>
                </div>
                
                <div class="main-wrapper">
                    <div class="debug-layout">
                        <div class="controls-panel">
                            <div class="control-section">
                                <h3>🎨 Designer Tests</h3>
                                <button id="btn-open-designer" class="btn btn-primary">Open Designer</button>
                                <button id="btn-test-layering" class="btn btn-warning">Test Layering Fix</button>
                                <button id="btn-add-sample" class="btn btn-success">Add Sample Objects</button>
                                <button id="btn-clear" class="btn btn-danger">Clear Canvas</button>
                            </div>
                            
                            <div class="control-section">
                                <h3>📊 System Check</h3>
                                <button id="btn-run-tests" class="btn btn-primary">Run All Tests</button>
                                <a href="<?php echo home_url('/?swpd_debug=1&refresh=1'); ?>" class="btn btn-warning">Refresh Page</a>
                            </div>
                        </div>
                        
                        <div class="canvas-area">
                            <div class="canvas-wrapper">
                                <div class="canvas-container">
                                    <canvas id="designer-canvas" width="400" height="400"></canvas>
                                </div>
                            </div>
                            <div style="margin-top: 10px; font-size: 12px; color: #666;">
                                Canvas: <span id="canvas-size">400x400</span> | 
                                Objects: <span id="object-count">0</span>
                            </div>
                        </div>
                        
                        <div class="debug-panel">
                            <div class="debug-info">
                                <strong>System Status:</strong><br>
                                Fabric.js: <span id="fabric-status">❌</span><br>
                                Designer: <span id="designer-status">❌</span><br>
                                Canvas: <span id="canvas-status">❌</span><br>
                                Product ID: <?php echo isset($product_id) ? $product_id : 'Error loading'; ?><br>
                                Variants: <?php echo isset($variants) ? count($variants) : '0'; ?> found
                            </div>
                            
                            <div class="debug-info">
                                <strong>Product Info:</strong><br>
                                <?php if ($product): ?>
                                    Name: <?php echo esc_html($product->get_name()); ?><br>
                                    Type: <?php echo esc_html($product->get_type()); ?><br>
                                    Status: <?php echo esc_html($product->get_status()); ?>
                                <?php else: ?>
                                    <span class="error">Product ID 1215 not found!</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="debug-info">
                                <strong>Test Results:</strong><br>
                                <span id="test-results">No tests run</span>
                            </div>
                            
                            <strong>Console Log:</strong>
                            <div id="debug-log" class="debug-log"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- WordPress globals -->
            <script>
                window.swpdDesignerConfig = <?php echo json_encode(array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('swpd_nonce'),
                    'product_id' => $product_id,
                    'debug' => true,
                    'variants' => $variants,
                    'cloudinary' => array(
                        'enabled' => get_option('swpd_cloudinary_enabled', false),
                        'cloudName' => get_option('swpd_cloudinary_cloud_name', ''),
                        'uploadPreset' => get_option('swpd_cloudinary_upload_preset', '')
                    )
                )); ?>;
                
                window.swpdTranslations = <?php echo json_encode(array(
                    'editDesign' => 'Edit Design',
                    'uploadImage' => 'Upload Image',
                    'addText' => 'Add Text',
                    'loading' => 'Loading...'
                )); ?>;
            </script>
            
            <!-- Include designer script -->
            <script src="<?php echo plugin_dir_url(dirname(__FILE__)); ?>assets/js/enhanced-product-designer.js"></script>
            
            <script>
                // Enhanced console logging
                const originalLog = console.log;
                const originalError = console.error;
                let logCount = 0;
                
                function addToDebugLog(message, type = 'info') {
                    const debugLog = document.getElementById('debug-log');
                    if (debugLog) {
                        const time = new Date().toLocaleTimeString();
                        const logEntry = document.createElement('div');
                        logEntry.className = type;
                        logEntry.innerHTML = `[${time}] ${message}`;
                        debugLog.appendChild(logEntry);
                        debugLog.scrollTop = debugLog.scrollHeight;
                    }
                }
                
                console.log = function(...args) {
                    originalLog.apply(console, args);
                    addToDebugLog(args.join(' '), 'info');
                };
                
                console.error = function(...args) {
                    originalError.apply(console, args);
                    addToDebugLog('ERROR: ' + args.join(' '), 'error');
                };
                
                // System status updates
                function updateStatus() {
                    const statusIndicator = document.getElementById('status-indicator');
                    const statusText = document.getElementById('status-text');
                    
                    // Check Fabric.js
                    document.getElementById('fabric-status').innerHTML = window.fabric ? '✅ Loaded' : '❌ Missing';
                    
                    // Check Designer
                    document.getElementById('designer-status').innerHTML = window.EnhancedProductDesigner ? '✅ Loaded' : '❌ Missing';
                    
                    // Check Canvas
                    document.getElementById('canvas-status').innerHTML = document.getElementById('designer-canvas') ? '✅ Found' : '❌ Missing';
                    
                    // Update object count
                    const designer = window.customDesigner || window.productDesigner;
                    if (designer && designer.canvas) {
                        document.getElementById('object-count').textContent = designer.canvas.getObjects().length;
                        statusIndicator.className = 'status-indicator status-ok';
                        statusText.textContent = 'Designer Ready';
                    } else {
                        statusIndicator.className = 'status-indicator status-error';
                        statusText.textContent = 'Designer Not Ready';
                    }
                }
                
                // Test functions
                function runAllTests() {
                    let passed = 0;
                    let total = 0;
                    
                    const tests = [
                        () => window.fabric !== undefined,
                        () => window.EnhancedProductDesigner !== undefined,
                        () => document.getElementById('designer-canvas') !== null,
                        () => (window.customDesigner || window.productDesigner) !== undefined,
                        () => {
                            const designer = window.customDesigner || window.productDesigner;
                            return designer && designer.canvas !== undefined;
                        }
                    ];
                    
                    tests.forEach((test, i) => {
                        total++;
                        try {
                            if (test()) {
                                passed++;
                                addToDebugLog(`✅ Test ${i+1} passed`, 'success');
                            } else {
                                addToDebugLog(`❌ Test ${i+1} failed`, 'error');
                            }
                        } catch (e) {
                            addToDebugLog(`❌ Test ${i+1} error: ${e.message}`, 'error');
                        }
                    });
                    
                    document.getElementById('test-results').innerHTML = `${passed}/${total} tests passed`;
                    addToDebugLog(`Test suite complete: ${passed}/${total}`, passed === total ? 'success' : 'warning');
                }
                
                // Event listeners
                document.addEventListener('DOMContentLoaded', function() {
                    addToDebugLog('Debug page loaded', 'info');
                    
                    setInterval(updateStatus, 1000);
                    
                    // Wait for designer to load then run tests
                    setTimeout(runAllTests, 3000);
                    
                    // Button handlers
                    document.getElementById('btn-open-designer')?.addEventListener('click', () => {
                        const designer = window.customDesigner || window.productDesigner;
                        if (designer) {
                            designer.openLightbox();
                            addToDebugLog('Designer opened', 'success');
                        } else {
                            addToDebugLog('Designer not available', 'error');
                        }
                    });
                    
                    document.getElementById('btn-test-layering')?.addEventListener('click', () => {
                        const designer = window.customDesigner || window.productDesigner;
                        if (designer && designer.ensureProperLayering) {
                            try {
                                designer.ensureProperLayering();
                                addToDebugLog('✅ Layering test PASSED', 'success');
                            } catch (error) {
                                addToDebugLog(`❌ Layering test FAILED: ${error.message}`, 'error');
                            }
                        } else {
                            addToDebugLog('ensureProperLayering not available', 'error');
                        }
                    });
                    
                    document.getElementById('btn-add-sample')?.addEventListener('click', () => {
                        const designer = window.customDesigner || window.productDesigner;
                        if (designer && designer.canvas) {
                            // Add sample rectangle
                            const rect = new fabric.Rect({
                                left: 100,
                                top: 100,
                                width: 100,
                                height: 100,
                                fill: 'red'
                            });
                            
                            // Add sample text
                            const text = new fabric.Text('Test Text', {
                                left: 150,
                                top: 150,
                                fontSize: 20,
                                fill: 'blue'
                            });
                            
                            designer.canvas.add(rect);
                            designer.canvas.add(text);
                            designer.canvas.renderAll();
                            addToDebugLog('Sample objects added', 'success');
                        }
                    });
                    
                    document.getElementById('btn-clear')?.addEventListener('click', () => {
                        const designer = window.customDesigner || window.productDesigner;
                        if (designer && designer.canvas) {
                            designer.canvas.clear();
                            designer.canvas.renderAll();
                            addToDebugLog('Canvas cleared', 'info');
                        }
                    });
                    
                    document.getElementById('btn-run-tests')?.addEventListener('click', runAllTests);
                });
                
                // Global error handler
                window.addEventListener('error', (e) => {
                    addToDebugLog(`Global Error: ${e.message} at ${e.filename}:${e.lineno}`, 'error');
                });
                
                addToDebugLog('Debug environment initialized', 'success');
            </script>
        </body>
        </html>
        <?php
        exit;
    }
}

new SWPD_Debug_Page();
?>