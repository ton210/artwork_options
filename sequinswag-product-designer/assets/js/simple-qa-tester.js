/**
 * Simple QA Tester for SequinDesigner v2.0
 * Comprehensive but straightforward testing
 */

class SimpleQATester {
    constructor() {
        this.results = [];
        this.testCount = 0;
        this.passCount = 0;
        this.failCount = 0;
        
        this.log('🧪 Simple QA Tester initialized', 'info');
    }
    
    log(message, type = 'info') {
        const timestamp = new Date().toLocaleTimeString();
        console.log(`[QA ${timestamp}] ${message}`);
        
        if (typeof addLogEntry === 'function') {
            addLogEntry(message, type);
        }
    }
    
    test(name, testFunc) {
        this.testCount++;
        this.log(`🔍 Testing: ${name}`, 'info');
        
        try {
            const result = testFunc();
            this.passCount++;
            this.results.push({ name, status: 'PASS', result });
            this.log(`✅ PASS: ${name}`, 'success');
            return { success: true, result };
        } catch (error) {
            this.failCount++;
            this.results.push({ name, status: 'FAIL', error: error.message });
            this.log(`❌ FAIL: ${name} - ${error.message}`, 'error');
            return { success: false, error };
        }
    }
    
    // Test 1: Dependencies and Setup
    testDependencies() {
        this.log('📦 Testing Dependencies...', 'info');
        
        this.test('Fabric.js Available', () => {
            if (!window.fabric) throw new Error('Fabric.js not loaded');
            return `Version: ${fabric.version || 'unknown'}`;
        });
        
        this.test('SequinDesignerV2 Class', () => {
            if (!window.SequinDesignerV2) throw new Error('SequinDesignerV2 class not found');
            return 'Class available';
        });
        
        this.test('Canvas Element', () => {
            const canvas = document.getElementById('designer-canvas');
            if (!canvas) throw new Error('Canvas element not found');
            return `Canvas found: ${canvas.width}x${canvas.height}`;
        });
        
        this.test('WordPress Config', () => {
            if (!window.swpdDesignerConfig) throw new Error('swpdDesignerConfig missing');
            return `Product ID: ${window.swpdDesignerConfig.product_id}`;
        });
    }
    
    // Test 2: Designer Instance
    testDesignerInstance() {
        this.log('🚀 Testing Designer Instance...', 'info');
        
        this.test('Designer Instance Creation', () => {
            if (!window.sequinDesigner) {
                throw new Error('Designer instance not found');
            }
            return 'Instance exists';
        });
        
        this.test('Canvas Initialization', () => {
            if (!window.sequinDesigner.canvas) throw new Error('Canvas not initialized');
            return `Canvas ready: ${window.sequinDesigner.canvas.width}x${window.sequinDesigner.canvas.height}`;
        });
        
        this.test('Component Initialization', () => {
            const designer = window.sequinDesigner;
            if (!designer.stateManager) throw new Error('StateManager missing');
            if (!designer.events) throw new Error('EventManager missing');
            if (!designer.ui) throw new Error('UIManager missing');
            if (!designer.imageManager) throw new Error('ImageManager missing');
            return 'All components initialized';
        });
    }
    
    // Test 3: Basic Canvas Operations
    testCanvasBasics() {
        this.log('🎨 Testing Canvas Operations...', 'info');
        
        this.test('Canvas Render', () => {
            window.sequinDesigner.canvas.renderAll();
            return 'Render successful';
        });
        
        this.test('Add Rectangle', () => {
            const rect = new fabric.Rect({
                left: 100,
                top: 100,
                width: 50,
                height: 50,
                fill: 'red'
            });
            
            window.sequinDesigner.canvas.add(rect);
            const objects = window.sequinDesigner.canvas.getObjects();
            if (!objects.find(obj => obj === rect)) {
                throw new Error('Rectangle not added');
            }
            return `Objects count: ${objects.length}`;
        });
        
        this.test('Object Selection', () => {
            const objects = window.sequinDesigner.canvas.getObjects();
            if (objects.length === 0) throw new Error('No objects to select');
            
            window.sequinDesigner.canvas.setActiveObject(objects[objects.length - 1]);
            const activeObj = window.sequinDesigner.canvas.getActiveObject();
            if (!activeObj) throw new Error('Selection failed');
            return 'Object selected';
        });
        
        this.test('Object Deletion', () => {
            const before = window.sequinDesigner.canvas.getObjects().length;
            window.sequinDesigner.deleteSelected();
            const after = window.sequinDesigner.canvas.getObjects().length;
            
            if (after >= before) throw new Error('Object not deleted');
            return `Objects: ${before} → ${after}`;
        });
    }
    
    // Test 4: Text Functionality
    testTextTools() {
        this.log('📝 Testing Text Tools...', 'info');
        
        this.test('Add Default Text', () => {
            const textObj = window.sequinDesigner.addText();
            if (!textObj) throw new Error('Text not added');
            return `Text: "${textObj.text}"`;
        });
        
        this.test('Add Custom Text', () => {
            const textObj = window.sequinDesigner.addText('Custom Test', {
                fontSize: 32,
                fill: '#ff0000'
            });
            
            if (!textObj) throw new Error('Custom text failed');
            if (textObj.fontSize !== 32) throw new Error('Font size not set');
            return `Custom text: ${textObj.fontSize}px`;
        });
        
        this.test('Text Modification', () => {
            const textObjects = window.sequinDesigner.canvas.getObjects().filter(obj => obj.type === 'text');
            if (textObjects.length === 0) throw new Error('No text objects found');
            
            const textObj = textObjects[0];
            textObj.set('text', 'Modified Text');
            window.sequinDesigner.canvas.renderAll();
            
            if (textObj.text !== 'Modified Text') throw new Error('Text not modified');
            return 'Text modified successfully';
        });
    }
    
    // Test 5: State Management
    testStateManagement() {
        this.log('💾 Testing State Management...', 'info');
        
        this.test('State Save', () => {
            window.sequinDesigner.stateManager.saveState();
            if (window.sequinDesigner.stateManager.history.length === 0) {
                throw new Error('State not saved');
            }
            return `History: ${window.sequinDesigner.stateManager.history.length} entries`;
        });
        
        this.test('Undo Capability', () => {
            const canUndo = window.sequinDesigner.stateManager.canUndo();
            return `Can undo: ${canUndo}`;
        });
        
        this.test('Redo Capability', () => {
            const canRedo = window.sequinDesigner.stateManager.canRedo();
            return `Can redo: ${canRedo}`;
        });
        
        this.test('Design Data Export', () => {
            const data = window.sequinDesigner.getDesignData();
            if (!data) throw new Error('No design data');
            if (!data.canvas) throw new Error('Canvas data missing');
            if (!data.metadata) throw new Error('Metadata missing');
            return `Data exported: ${data.metadata.objectCount} objects`;
        });
    }
    
    // Test 6: Layer Management
    testLayerManagement() {
        this.log('📚 Testing Layer Management...', 'info');
        
        // Setup test objects
        const rect1 = new fabric.Rect({ left: 50, top: 50, width: 40, height: 40, fill: 'red' });
        const rect2 = new fabric.Rect({ left: 60, top: 60, width: 40, height: 40, fill: 'blue' });
        
        window.sequinDesigner.canvas.add(rect1);
        window.sequinDesigner.canvas.add(rect2);
        
        this.test('Bring to Front', () => {
            window.sequinDesigner.canvas.setActiveObject(rect1);
            const initialIndex = window.sequinDesigner.canvas.getObjects().indexOf(rect1);
            window.sequinDesigner.bringToFront();
            const newIndex = window.sequinDesigner.canvas.getObjects().indexOf(rect1);
            return `Index: ${initialIndex} → ${newIndex}`;
        });
        
        this.test('Send to Back', () => {
            window.sequinDesigner.canvas.setActiveObject(rect2);
            const initialIndex = window.sequinDesigner.canvas.getObjects().indexOf(rect2);
            window.sequinDesigner.sendToBack();
            const newIndex = window.sequinDesigner.canvas.getObjects().indexOf(rect2);
            return `Index: ${initialIndex} → ${newIndex}`;
        });
        
        this.test('Select All', () => {
            window.sequinDesigner.selectAll();
            const selected = window.sequinDesigner.canvas.getActiveObjects();
            return `Selected: ${selected.length} objects`;
        });
    }
    
    // Test 7: Error Handling
    testErrorHandling() {
        this.log('⚠️ Testing Error Handling...', 'info');
        
        this.test('Invalid File Validation', () => {
            const mockFile = { type: 'text/plain', size: 1000 };
            try {
                window.sequinDesigner.imageManager.validateFile(mockFile);
                throw new Error('Should have failed validation');
            } catch (error) {
                if (error.message.includes('Unsupported format')) {
                    return 'Correctly rejected invalid file';
                }
                throw error;
            }
        });
        
        this.test('Delete with No Selection', () => {
            window.sequinDesigner.canvas.discardActiveObject();
            window.sequinDesigner.deleteSelected(); // Should not throw
            return 'Handled gracefully';
        });
        
        this.test('Operations with Destroyed Canvas', () => {
            // This should not crash the page
            try {
                const stats = window.sequinDesigner.getStats();
                return `Stats available: ${!!stats}`;
            } catch (error) {
                return 'Error handled gracefully';
            }
        });
    }
    
    // Test 8: Performance
    testPerformance() {
        this.log('⚡ Testing Performance...', 'info');
        
        this.test('Multiple Object Addition', () => {
            const startTime = performance.now();
            
            for (let i = 0; i < 20; i++) {
                const circle = new fabric.Circle({
                    left: Math.random() * 300,
                    top: Math.random() * 300,
                    radius: 15,
                    fill: `hsl(${Math.random() * 360}, 70%, 50%)`
                });
                window.sequinDesigner.canvas.add(circle);
            }
            
            window.sequinDesigner.canvas.renderAll();
            const duration = Math.round(performance.now() - startTime);
            
            if (duration > 2000) throw new Error('Too slow');
            return `20 objects added in ${duration}ms`;
        });
        
        this.test('Canvas Clear Performance', () => {
            const startTime = performance.now();
            const objectsBefore = window.sequinDesigner.canvas.getObjects().length;
            
            window.sequinDesigner.clear();
            
            const duration = Math.round(performance.now() - startTime);
            const objectsAfter = window.sequinDesigner.canvas.getObjects().length;
            
            return `Cleared ${objectsBefore} objects in ${duration}ms`;
        });
    }
    
    // Main test runner
    runFullSuite() {
        this.log('🎬 Starting Simple QA Test Suite...', 'info');
        
        // Reset counters
        this.testCount = 0;
        this.passCount = 0;
        this.failCount = 0;
        this.results = [];
        
        // Run all test groups
        this.testDependencies();
        this.testDesignerInstance();
        this.testCanvasBasics();
        this.testTextTools();
        this.testStateManagement();
        this.testLayerManagement();
        this.testErrorHandling();
        this.testPerformance();
        
        // Generate report
        this.generateReport();
        
        return {
            total: this.testCount,
            passed: this.passCount,
            failed: this.failCount,
            results: this.results
        };
    }
    
    generateReport() {
        const successRate = Math.round((this.passCount / this.testCount) * 100);
        
        this.log('📋 QA REPORT', 'info');
        this.log(`Tests: ${this.testCount} | Passed: ${this.passCount} | Failed: ${this.failCount}`, 'info');
        this.log(`Success Rate: ${successRate}%`, successRate >= 90 ? 'success' : 'warning');
        
        if (this.failCount === 0) {
            this.log('🎉 ALL TESTS PASSED!', 'success');
        } else {
            this.log(`⚠️ ${this.failCount} tests failed`, 'warning');
        }
        
        // Log failed tests
        const failed = this.results.filter(r => r.status === 'FAIL');
        if (failed.length > 0) {
            this.log('❌ Failed Tests:', 'error');
            failed.forEach(test => {
                this.log(`   - ${test.name}: ${test.error}`, 'error');
            });
        }
    }
    
    // Quick individual tests
    testBasics() {
        this.testDependencies();
        this.testDesignerInstance();
        this.generateReport();
    }
    
    testFunctionality() {
        this.testCanvasBasics();
        this.testTextTools();
        this.testLayerManagement();
        this.generateReport();
    }
    
    testStability() {
        this.testErrorHandling();
        this.testPerformance();
        this.generateReport();
    }
}

// Make globally available
window.SimpleQATester = SimpleQATester;

console.log('🧪 Simple QA Tester loaded');