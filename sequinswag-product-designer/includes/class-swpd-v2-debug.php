<?php
/**
 * SequinSwag Designer v2.0 Debug Page
 */

if (!defined('ABSPATH')) {
    exit;
}

class SWPD_V2_Debug {
    
    public function __construct() {
        add_action('init', array($this, 'handle_v2_debug_request'));
    }
    
    public function handle_v2_debug_request() {
        if (isset($_GET['swpd_v2_debug']) && $_GET['swpd_v2_debug'] === '1') {
            $this->render_v2_debug_page();
            exit;
        }
    }
    
    public function render_v2_debug_page() {
        // Get product data
        $product_id = 1215;
        $product = wc_get_product($product_id);
        
        header('Content-Type: text/html; charset=UTF-8');
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>SequinSwag Designer v2.0 - Test Page</title>
            
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
                
                .container {
                    max-width: 1200px;
                    margin: 0 auto;
                }
                
                .header {
                    background: rgba(255,255,255,0.1);
                    padding: 20px;
                    border-radius: 15px;
                    margin-bottom: 20px;
                    backdrop-filter: blur(10px);
                    text-align: center;
                }
                
                .header h1 {
                    color: white;
                    margin-bottom: 10px;
                    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
                }
                
                .header .version {
                    color: rgba(255,255,255,0.8);
                    font-size: 14px;
                }
                
                .main-content {
                    background: white;
                    border-radius: 20px;
                    padding: 30px;
                    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
                }
                
                .layout {
                    display: grid;
                    grid-template-columns: 250px 1fr 300px;
                    gap: 20px;
                    min-height: 600px;
                }
                
                .controls {
                    background: #f8f9fa;
                    border-radius: 12px;
                    padding: 20px;
                }
                
                .control-group {
                    margin-bottom: 20px;
                    padding-bottom: 15px;
                    border-bottom: 1px solid #dee2e6;
                }
                
                .control-group:last-child {
                    border-bottom: none;
                }
                
                .control-group h3 {
                    font-size: 14px;
                    color: #495057;
                    margin-bottom: 10px;
                    font-weight: 600;
                }
                
                .btn {
                    display: block;
                    width: 100%;
                    padding: 8px 12px;
                    border: none;
                    border-radius: 6px;
                    font-size: 13px;
                    cursor: pointer;
                    margin-bottom: 8px;
                    text-align: center;
                    transition: all 0.2s ease;
                }
                
                .btn-primary { background: #007bff; color: white; }
                .btn-success { background: #28a745; color: white; }
                .btn-danger { background: #dc3545; color: white; }
                .btn-warning { background: #ffc107; color: #212529; }
                .btn-info { background: #17a2b8; color: white; }
                
                .btn:hover {
                    transform: translateY(-1px);
                    opacity: 0.9;
                }
                
                .input-group {
                    margin-bottom: 10px;
                }
                
                .input-group label {
                    display: block;
                    font-size: 12px;
                    color: #495057;
                    margin-bottom: 4px;
                }
                
                .input-group input {
                    width: 100%;
                    padding: 6px 8px;
                    border: 1px solid #ced4da;
                    border-radius: 4px;
                    font-size: 13px;
                }
                
                .canvas-area {
                    background: #f8f9fa;
                    border-radius: 12px;
                    padding: 20px;
                    text-align: center;
                }
                
                .canvas-wrapper {
                    background: white;
                    border-radius: 8px;
                    padding: 15px;
                    display: inline-block;
                    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                }
                
                #designer-canvas {
                    border: 2px solid #dee2e6;
                    border-radius: 6px;
                }
                
                .debug-panel {
                    background: #212529;
                    color: #fff;
                    border-radius: 12px;
                    padding: 15px;
                    font-family: 'Monaco', monospace;
                    font-size: 12px;
                }
                
                .debug-section {
                    margin-bottom: 15px;
                    padding-bottom: 10px;
                    border-bottom: 1px solid #495057;
                }
                
                .debug-section:last-child {
                    border-bottom: none;
                }
                
                .debug-section h4 {
                    color: #28a745;
                    margin-bottom: 8px;
                    font-size: 13px;
                }
                
                .status-grid {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 8px;
                    margin-bottom: 10px;
                }
                
                .status-item {
                    background: #2d3748;
                    padding: 8px;
                    border-radius: 4px;
                    text-align: center;
                    font-size: 11px;
                }
                
                .status-value {
                    font-weight: bold;
                    color: #90cdf4;
                }
                
                .debug-log {
                    background: #000;
                    border: 1px solid #495057;
                    border-radius: 4px;
                    padding: 8px;
                    height: 200px;
                    overflow-y: auto;
                    font-size: 10px;
                    line-height: 1.3;
                }
                
                .log-entry {
                    margin: 2px 0;
                }
                
                .log-entry.error { color: #ff6b6b; }
                .log-entry.success { color: #4ecdc4; }
                .log-entry.warning { color: #feca57; }
                .log-entry.info { color: #74b9ff; }
                
                .file-input {
                    position: absolute;
                    left: -9999px;
                }
                
                @media (max-width: 768px) {
                    .layout {
                        grid-template-columns: 1fr;
                        gap: 15px;
                    }
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🎨 SequinSwag Designer v2.0</h1>
                    <div class="version">Clean Architecture • Modern JavaScript • Product ID: <?php echo $product_id; ?></div>
                </div>
                
                <div class="main-content">
                    <div class="layout">
                        <!-- Controls -->
                        <div class="controls">
                            <div class="control-group">
                                <h3>🎨 Designer</h3>
                                <button id="btn-open" class="btn btn-primary">Open Designer</button>
                                <button id="btn-close" class="btn btn-primary">Close Designer</button>
                                <button id="btn-reinit" class="btn btn-warning">Reinitialize</button>
                            </div>
                            
                            <div class="control-group">
                                <h3>📷 Images</h3>
                                <label class="btn btn-info">
                                    Upload Images
                                    <input type="file" class="file-input" id="image-upload" accept="image/*" multiple>
                                </label>
                                <button id="btn-add-sample-img" class="btn btn-success">Add Sample Image</button>
                            </div>
                            
                            <div class="control-group">
                                <h3>📝 Text</h3>
                                <div class="input-group">
                                    <label>Text:</label>
                                    <input type="text" id="text-input" value="Sample Text" placeholder="Enter text...">
                                </div>
                                <div class="input-group">
                                    <label>Size:</label>
                                    <input type="number" id="font-size" value="24" min="8" max="100">
                                </div>
                                <div class="input-group">
                                    <label>Color:</label>
                                    <input type="color" id="text-color" value="#000000">
                                </div>
                                <button id="btn-add-text" class="btn btn-success">Add Text</button>
                            </div>
                            
                            <div class="control-group">
                                <h3>🎯 Actions</h3>
                                <button id="btn-undo" class="btn btn-info">Undo</button>
                                <button id="btn-redo" class="btn btn-info">Redo</button>
                                <button id="btn-delete" class="btn btn-danger">Delete Selected</button>
                                <button id="btn-clear" class="btn btn-danger">Clear All</button>
                            </div>
                            
                            <div class="control-group">
                                <h3>📚 Layers</h3>
                                <button id="btn-bring-front" class="btn btn-primary">Bring to Front</button>
                                <button id="btn-send-back" class="btn btn-primary">Send to Back</button>
                                <button id="btn-bring-forward" class="btn btn-primary">Bring Forward</button>
                                <button id="btn-send-backward" class="btn btn-primary">Send Backward</button>
                            </div>
                            
                            <div class="control-group">
                                <h3>💾 Export</h3>
                                <button id="btn-export-png" class="btn btn-success">Export PNG</button>
                                <button id="btn-export-json" class="btn btn-info">Export JSON</button>
                            </div>
                        </div>
                        
                        <!-- Canvas -->
                        <div class="canvas-area">
                            <div class="canvas-wrapper">
                                <canvas id="designer-canvas"></canvas>
                            </div>
                            <div style="margin-top: 10px; font-size: 12px; color: #666;">
                                Objects: <span id="object-count">0</span> | 
                                Size: <span id="canvas-size">600x600</span>
                            </div>
                        </div>
                        
                        <!-- Debug Panel -->
                        <div class="debug-panel">
                            <div class="debug-section">
                                <h4>📊 Status</h4>
                                <div class="status-grid">
                                    <div class="status-item">
                                        <div class="status-value" id="status-designer">❌</div>
                                        <div>Designer</div>
                                    </div>
                                    <div class="status-item">
                                        <div class="status-value" id="status-fabric">❌</div>
                                        <div>Fabric.js</div>
                                    </div>
                                    <div class="status-item">
                                        <div class="status-value" id="status-canvas">❌</div>
                                        <div>Canvas</div>
                                    </div>
                                    <div class="status-item">
                                        <div class="status-value" id="status-objects">0</div>
                                        <div>Objects</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="debug-section">
                                <h4>🔧 QA Tools</h4>
                                <button id="btn-run-basic-tests" class="btn btn-success" style="font-size: 11px; padding: 6px;">Basic Tests</button>
                                <button id="btn-run-full-qa" class="btn btn-info" style="font-size: 11px; padding: 6px;">Full QA Suite</button>
                                <button id="btn-test-canvas" class="btn btn-primary" style="font-size: 11px; padding: 6px;">Test Canvas</button>
                                <button id="btn-test-text" class="btn btn-primary" style="font-size: 11px; padding: 6px;">Test Text</button>
                                <button id="btn-test-layers" class="btn btn-primary" style="font-size: 11px; padding: 6px;">Test Layers</button>
                                <button id="btn-stress-test" class="btn btn-warning" style="font-size: 11px; padding: 6px;">Stress Test</button>
                            </div>
                            
                            <div class="debug-section">
                                <h4>📝 Log</h4>
                                <div id="debug-log" class="debug-log"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- WordPress Config -->
            <script>
                window.swpdDesignerConfig = <?php echo json_encode(array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('swpd_nonce'),
                    'product_id' => $product_id,
                    'debug' => true
                )); ?>;
                
                // Enhanced logging
                const logEntries = [];
                const originalLog = console.log;
                const originalError = console.error;
                const originalWarn = console.warn;
                
                function addLogEntry(message, type = 'info') {
                    const timestamp = new Date().toLocaleTimeString();
                    const entry = { timestamp, message, type };
                    logEntries.push(entry);
                    
                    const logElement = document.getElementById('debug-log');
                    if (logElement) {
                        const logDiv = document.createElement('div');
                        logDiv.className = `log-entry ${type}`;
                        logDiv.textContent = `[${timestamp}] ${message}`;
                        logElement.appendChild(logDiv);
                        logElement.scrollTop = logElement.scrollHeight;
                        
                        // Keep only last 100 entries
                        if (logElement.children.length > 100) {
                            logElement.removeChild(logElement.firstChild);
                        }
                    }
                }
                
                console.log = function(...args) {
                    originalLog.apply(console, args);
                    addLogEntry(args.join(' '), 'info');
                };
                
                console.error = function(...args) {
                    originalError.apply(console, args);
                    addLogEntry('ERROR: ' + args.join(' '), 'error');
                };
                
                console.warn = function(...args) {
                    originalWarn.apply(console, args);
                    addLogEntry('WARN: ' + args.join(' '), 'warning');
                };
                
                // Status updater
                function updateStatus() {
                    document.getElementById('status-fabric').textContent = window.fabric ? '✅' : '❌';
                    document.getElementById('status-designer').textContent = window.SequinDesignerV2 ? '✅' : '❌';
                    document.getElementById('status-canvas').textContent = document.getElementById('designer-canvas') ? '✅' : '❌';
                    
                    if (window.sequinDesigner) {
                        const stats = window.sequinDesigner.getStats();
                        if (stats) {
                            document.getElementById('status-objects').textContent = stats.totalObjects;
                            document.getElementById('object-count').textContent = stats.totalObjects;
                        }
                    }
                }
                
                setInterval(updateStatus, 1000);
            </script>
            
            <!-- Load the new designer and QA tester -->
            <script src="<?php echo plugin_dir_url(dirname(__FILE__)); ?>assets/js/sequin-designer-v2.js"></script>
            <script src="<?php echo plugin_dir_url(dirname(__FILE__)); ?>assets/js/simple-qa-tester.js"></script>
            
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    addLogEntry('🚀 v2.0 Debug page loaded', 'success');
                    
                    // Test suite
                    function runTests() {
                        const tests = [
                            { name: 'Fabric.js', test: () => window.fabric !== undefined },
                            { name: 'Designer Class', test: () => window.SequinDesignerV2 !== undefined },
                            { name: 'Canvas Element', test: () => document.getElementById('designer-canvas') !== null },
                            { name: 'Designer Instance', test: () => window.sequinDesigner !== undefined },
                            { name: 'Canvas Ready', test: () => window.sequinDesigner?.canvas !== undefined }
                        ];
                        
                        let passed = 0;
                        tests.forEach(({ name, test }) => {
                            try {
                                if (test()) {
                                    addLogEntry(`✅ ${name} test passed`, 'success');
                                    passed++;
                                } else {
                                    addLogEntry(`❌ ${name} test failed`, 'error');
                                }
                            } catch (e) {
                                addLogEntry(`❌ ${name} test error: ${e.message}`, 'error');
                            }
                        });
                        
                        addLogEntry(`Test results: ${passed}/${tests.length} passed`, passed === tests.length ? 'success' : 'warning');
                    }
                    
                    // Initialize QA tester
                    let qaTester;
                    setTimeout(() => {
                        if (window.SimpleQATester) {
                            qaTester = new SimpleQATester();
                            addLogEntry('Simple QA Tester ready', 'success');
                        }
                    }, 2000);
                    
                    // Button handlers
                    document.getElementById('btn-run-basic-tests')?.addEventListener('click', runTests);
                    
                    document.getElementById('btn-run-full-qa')?.addEventListener('click', () => {
                        if (qaTester) {
                            addLogEntry('🎬 Starting Full QA Suite...', 'info');
                            qaTester.runFullSuite();
                        } else {
                            addLogEntry('QA Tester not ready', 'error');
                        }
                    });
                    
                    document.getElementById('btn-test-canvas')?.addEventListener('click', () => {
                        if (qaTester) {
                            qaTester.testCanvasBasics();
                            qaTester.generateReport();
                        }
                    });
                    
                    document.getElementById('btn-test-text')?.addEventListener('click', () => {
                        if (qaTester) {
                            qaTester.testTextTools();
                            qaTester.generateReport();
                        }
                    });
                    
                    document.getElementById('btn-test-layers')?.addEventListener('click', () => {
                        if (qaTester) {
                            qaTester.testLayerManagement();
                            qaTester.generateReport();
                        }
                    });
                    
                    document.getElementById('btn-open')?.addEventListener('click', () => {
                        if (window.sequinDesigner) {
                            window.sequinDesigner.openDesigner();
                        }
                    });
                    
                    document.getElementById('btn-close')?.addEventListener('click', () => {
                        if (window.sequinDesigner) {
                            window.sequinDesigner.closeDesigner();
                        }
                    });
                    
                    document.getElementById('btn-reinit')?.addEventListener('click', () => {
                        if (window.sequinDesigner) {
                            window.sequinDesigner.destroy();
                        }
                        setTimeout(() => {
                            window.sequinDesigner = new SequinDesignerV2();
                            addLogEntry('Designer reinitialized', 'info');
                        }, 500);
                    });
                    
                    document.getElementById('image-upload')?.addEventListener('change', async (e) => {
                        if (e.target.files.length > 0 && window.sequinDesigner) {
                            addLogEntry(`Uploading ${e.target.files.length} files...`, 'info');
                            const results = await window.sequinDesigner.uploadImages(e.target.files);
                            addLogEntry(`Upload complete: ${results.filter(r => r.success).length} successful`, 'success');
                        }
                    });
                    
                    document.getElementById('btn-add-sample-img')?.addEventListener('click', () => {
                        if (window.sequinDesigner && window.sequinDesigner.canvas) {
                            const sampleUrl = 'https://via.placeholder.com/200x200/4ecdc4/ffffff?text=Sample';
                            fabric.Image.fromURL(sampleUrl, (img) => {
                                img.set({
                                    left: Math.random() * 300 + 50,
                                    top: Math.random() * 300 + 50,
                                    scaleX: 0.5,
                                    scaleY: 0.5
                                });
                                window.sequinDesigner.canvas.add(img);
                                window.sequinDesigner.canvas.renderAll();
                                addLogEntry('Sample image added', 'success');
                            });
                        }
                    });
                    
                    document.getElementById('btn-add-text')?.addEventListener('click', () => {
                        const text = document.getElementById('text-input').value;
                        const fontSize = parseInt(document.getElementById('font-size').value);
                        const color = document.getElementById('text-color').value;
                        
                        if (window.sequinDesigner) {
                            window.sequinDesigner.addText(text, { fontSize, fill: color });
                        }
                    });
                    
                    document.getElementById('btn-undo')?.addEventListener('click', () => {
                        if (window.sequinDesigner) {
                            window.sequinDesigner.undo();
                        }
                    });
                    
                    document.getElementById('btn-redo')?.addEventListener('click', () => {
                        if (window.sequinDesigner) {
                            window.sequinDesigner.redo();
                        }
                    });
                    
                    document.getElementById('btn-delete')?.addEventListener('click', () => {
                        if (window.sequinDesigner) {
                            window.sequinDesigner.deleteSelected();
                        }
                    });
                    
                    document.getElementById('btn-clear')?.addEventListener('click', () => {
                        if (window.sequinDesigner) {
                            window.sequinDesigner.clear();
                        }
                    });
                    
                    document.getElementById('btn-bring-front')?.addEventListener('click', () => {
                        if (window.sequinDesigner) {
                            window.sequinDesigner.bringToFront();
                        }
                    });
                    
                    document.getElementById('btn-send-back')?.addEventListener('click', () => {
                        if (window.sequinDesigner) {
                            window.sequinDesigner.sendToBack();
                        }
                    });
                    
                    document.getElementById('btn-bring-forward')?.addEventListener('click', () => {
                        if (window.sequinDesigner) {
                            window.sequinDesigner.bringForward();
                        }
                    });
                    
                    document.getElementById('btn-send-backward')?.addEventListener('click', () => {
                        if (window.sequinDesigner) {
                            window.sequinDesigner.sendBackward();
                        }
                    });
                    
                    document.getElementById('btn-export-png')?.addEventListener('click', () => {
                        if (window.sequinDesigner) {
                            window.sequinDesigner.exportAsImage('png');
                        }
                    });
                    
                    document.getElementById('btn-export-json')?.addEventListener('click', () => {
                        if (window.sequinDesigner) {
                            const data = window.sequinDesigner.getDesignData();
                            console.log('Design Data:', data);
                            addLogEntry('JSON exported to console', 'info');
                        }
                    });
                    
                    document.getElementById('btn-stress-test')?.addEventListener('click', () => {
                        if (window.sequinDesigner && window.sequinDesigner.canvas) {
                            addLogEntry('Starting stress test...', 'warning');
                            
                            for (let i = 0; i < 20; i++) {
                                const rect = new fabric.Rect({
                                    left: Math.random() * 400,
                                    top: Math.random() * 400,
                                    width: 30,
                                    height: 30,
                                    fill: `hsl(${Math.random() * 360}, 70%, 50%)`
                                });
                                window.sequinDesigner.canvas.add(rect);
                            }
                            
                            window.sequinDesigner.canvas.renderAll();
                            addLogEntry('Stress test complete: 20 objects added', 'warning');
                        }
                    });
                    
                    // Auto-run tests after 2 seconds
                    setTimeout(runTests, 2000);
                });
                
                // Global error handling
                window.addEventListener('error', (e) => {
                    addLogEntry(`Global Error: ${e.message} at ${e.filename}:${e.lineno}`, 'error');
                });
                
                window.addEventListener('unhandledrejection', (e) => {
                    addLogEntry(`Promise Error: ${e.reason}`, 'error');
                });
                
                addLogEntry('🎯 Debug environment ready', 'info');
            </script>
        </body>
        </html>
        <?php
    }
}

new SWPD_V2_Debug();
?>