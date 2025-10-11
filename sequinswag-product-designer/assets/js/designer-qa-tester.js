/**
 * Comprehensive QA Tester for SequinDesigner v2.0
 * Tests all endpoints, functionality, and edge cases
 */

class DesignerQATester {
    constructor() {
        this.results = [];
        this.testCount = 0;
        this.passCount = 0;
        this.failCount = 0;
        this.startTime = Date.now();
        this.designer = null;
        
        console.log('🧪 DesignerQATester initialized');
    }
    
    log(message, type = 'info') {
        const timestamp = new Date().toLocaleTimeString();
        console.log(`[QA ${timestamp}] ${message}`);
        
        if (typeof addLogEntry === 'function') {
            addLogEntry(message, type);
        }
    }
    
    async test(name, testFunc) {
        this.testCount++;
        const startTime = performance.now();
        
        try {
            this.log(`🔍 Testing: ${name}`, 'info');
            const result = await testFunc();
            const duration = Math.round(performance.now() - startTime);
            
            this.passCount++;
            this.results.push({
                name,
                status: 'PASS',
                duration,
                result
            });
            
            this.log(`✅ PASS: ${name} (${duration}ms)`, 'success');
            return { success: true, result };
        } catch (error) {
            const duration = Math.round(performance.now() - startTime);
            
            this.failCount++;
            this.results.push({
                name,
                status: 'FAIL',
                duration,
                error: error.message
            });
            
            this.log(`❌ FAIL: ${name} - ${error.message} (${duration}ms)`, 'error');
            return { success: false, error };
        }
    }
    
    // Dependency Tests
    async testDependencies() {
        this.log('📦 Testing Dependencies...', 'info');
        
        await this.test('Fabric.js Available', () => {
            if (!window.fabric) throw new Error('Fabric.js not loaded');
            return `v${fabric.version || 'unknown'}`;
        });
        
        await this.test('SequinDesignerV2 Class Available', () => {
            if (!window.SequinDesignerV2) throw new Error('SequinDesignerV2 class not found');
            return 'Class loaded';
        });
        
        await this.test('Canvas Element Present', () => {
            const canvas = document.getElementById('designer-canvas');
            if (!canvas) throw new Error('Canvas element not found');
            return `${canvas.width}x${canvas.height}`;
        });
        
        await this.test('WordPress Config Present', () => {
            if (!window.swpdDesignerConfig) throw new Error('swpdDesignerConfig not found');
            return `Product ID: ${window.swpdDesignerConfig.product_id}`;
        });
    }
    
    // Initialization Tests
    async testInitialization() {
        this.log('🚀 Testing Initialization...', 'info');
        
        await this.test('Designer Instance Creation', () => {
            if (window.sequinDesigner) {
                this.designer = window.sequinDesigner;
                return 'Existing instance found';
            } else {
                this.designer = new SequinDesignerV2();
                return 'New instance created';
            }
        });
        
        await this.test('Canvas Initialization', () => {
            if (!this.designer.canvas) throw new Error('Canvas not initialized');
            return `${this.designer.canvas.width}x${this.designer.canvas.height}`;
        });
        
        await this.test('State Manager Initialization', () => {
            if (!this.designer.stateManager) throw new Error('StateManager not initialized');
            return 'StateManager ready';
        });
        
        await this.test('Event Manager Initialization', () => {
            if (!this.designer.events) throw new Error('EventManager not initialized');
            return 'EventManager ready';
        });
        
        await this.test('UI Manager Initialization', () => {
            if (!this.designer.ui) throw new Error('UIManager not initialized');
            return 'UIManager ready';
        });
    }
    
    // Canvas Basic Functionality
    async testCanvasBasics() {
        this.log('🎨 Testing Canvas Basics...', 'info');
        
        await this.test('Canvas Render', () => {
            this.designer.canvas.renderAll();
            return 'Render successful';
        });
        
        await this.test('Canvas Clear', () => {
            const initialCount = this.designer.canvas.getObjects().length;
            this.designer.clear();
            const afterCount = this.designer.canvas.getObjects().length;
            return `Objects: ${initialCount} → ${afterCount}`;
        });
        
        await this.test('Canvas Object Addition', () => {
            const rect = new fabric.Rect({
                left: 100,
                top: 100,
                width: 50,
                height: 50,
                fill: 'red'
            });
            
            this.designer.canvas.add(rect);
            this.designer.canvas.renderAll();
            
            const count = this.designer.canvas.getObjects().length;
            if (count === 0) throw new Error('Object not added');
            return `${count} objects on canvas`;
        });
        
        await this.test('Canvas Object Selection', () => {
            const objects = this.designer.canvas.getObjects();
            if (objects.length === 0) throw new Error('No objects to select');
            
            this.designer.canvas.setActiveObject(objects[0]);
            const activeObj = this.designer.canvas.getActiveObject();
            if (!activeObj) throw new Error('Object not selected');
            return 'Object selected successfully';
        });
        
        await this.test('Canvas Object Removal', () => {
            const initialCount = this.designer.canvas.getObjects().length;
            this.designer.deleteSelected();
            const afterCount = this.designer.canvas.getObjects().length;
            
            if (afterCount >= initialCount) throw new Error('Object not removed');
            return `Objects: ${initialCount} → ${afterCount}`;
        });
    }
    
    // Text Functionality
    async testTextFunctionality() {
        this.log('📝 Testing Text Functionality...', 'info');
        
        await this.test('Add Text - Default', () => {
            const textObj = this.designer.addText();
            if (!textObj) throw new Error('Failed to add text');
            return `Text added: "${textObj.text}"`;
        });
        
        await this.test('Add Text - Custom Options', () => {
            const textObj = this.designer.addText('Custom Text', {
                fontSize: 32,
                fill: '#ff0000',
                fontFamily: 'Arial'
            });
            
            if (!textObj) throw new Error('Failed to add custom text');
            if (textObj.fontSize !== 32) throw new Error('Font size not applied');
            if (textObj.fill !== '#ff0000') throw new Error('Color not applied');
            
            return `Custom text: ${textObj.fontSize}px, ${textObj.fill}`;
        });
        
        await this.test('Text Selection and Modification', () => {
            const objects = this.designer.canvas.getObjects();
            const textObj = objects.find(obj => obj.type === 'text');
            
            if (!textObj) throw new Error('No text object found');
            
            this.designer.canvas.setActiveObject(textObj);
            textObj.set('fontSize', 40);
            this.designer.canvas.renderAll();
            
            return `Text modified to ${textObj.fontSize}px`;
        });
    }
    
    // Image Processing Tests
    async testImageProcessing() {
        this.log('📷 Testing Image Processing...', 'info');
        
        await this.test('Image Validation - Valid Format', () => {
            // Create mock file
            const mockFile = new File([''], 'test.png', { type: 'image/png', lastModified: Date.now() });
            
            const result = this.designer.imageManager.validateFile(mockFile);
            if (!result) throw new Error('Valid file failed validation');
            return 'PNG validation passed';
        });
        
        await this.test('Image Validation - Invalid Format', () => {
            const mockFile = new File([''], 'test.txt', { type: 'text/plain', lastModified: Date.now() });
            
            try {
                this.designer.imageManager.validateFile(mockFile);
                throw new Error('Invalid file passed validation');
            } catch (error) {
                if (error.message.includes('Unsupported format')) {
                    return 'Invalid format correctly rejected';
                }
                throw error;
            }
        });
        
        await this.test('Create Fabric Image from URL', async () => {
            const testUrl = 'https://via.placeholder.com/100x100/ff6b6b/ffffff?text=Test';
            const fabricImg = await this.designer.imageManager.createFabricImage(testUrl, {
                left: 200,
                top: 200
            });
            
            if (!fabricImg) throw new Error('Failed to create fabric image');
            
            this.designer.canvas.add(fabricImg);
            this.designer.canvas.renderAll();
            
            return `Image added at (${fabricImg.left}, ${fabricImg.top})`;
        });
    }
    
    // State Management Tests
    async testStateManagement() {
        this.log('💾 Testing State Management...', 'info');
        
        // Clear canvas first
        this.designer.clear();
        
        await this.test('Initial State Save', () => {
            this.designer.stateManager.saveState();
            if (this.designer.stateManager.history.length === 0) {
                throw new Error('State not saved');
            }
            return `History length: ${this.designer.stateManager.history.length}`;
        });
        
        await this.test('Add Object and Auto-Save', async () => {
            const initialHistoryLength = this.designer.stateManager.history.length;
            
            const circle = new fabric.Circle({
                left: 150,
                top: 150,
                radius: 30,
                fill: 'blue'
            });
            
            this.designer.canvas.add(circle);
            this.designer.canvas.renderAll();
            
            // Wait for auto-save
            await new Promise(resolve => setTimeout(resolve, 100));
            
            const newHistoryLength = this.designer.stateManager.history.length;
            if (newHistoryLength <= initialHistoryLength) {
                throw new Error('State not auto-saved');
            }
            
            return `History: ${initialHistoryLength} → ${newHistoryLength}`;
        });
        
        await this.test('Undo Functionality', () => {
            const canUndo = this.designer.stateManager.canUndo();
            if (!canUndo) throw new Error('Cannot undo');
            
            const objectsBefore = this.designer.canvas.getObjects().length;
            this.designer.undo();
            
            // Wait for undo to complete
            return new Promise((resolve, reject) => {
                setTimeout(() => {
                    const objectsAfter = this.designer.canvas.getObjects().length;
                    if (objectsAfter >= objectsBefore) {
                        reject(new Error('Undo did not reduce objects'));
                    } else {
                        resolve(`Objects: ${objectsBefore} → ${objectsAfter}`);
                    }
                }, 200);
            });
        });
        
        await this.test('Redo Functionality', () => {
            const canRedo = this.designer.stateManager.canRedo();
            if (!canRedo) throw new Error('Cannot redo');
            
            const objectsBefore = this.designer.canvas.getObjects().length;
            this.designer.redo();
            
            return new Promise((resolve, reject) => {
                setTimeout(() => {
                    const objectsAfter = this.designer.canvas.getObjects().length;
                    if (objectsAfter <= objectsBefore) {
                        reject(new Error('Redo did not restore objects'));
                    } else {
                        resolve(`Objects: ${objectsBefore} → ${objectsAfter}`);
                    }
                }, 200);
            });
        });
        
        await this.test('Design Data Export', () => {
            const designData = this.designer.getDesignData();
            if (!designData) throw new Error('No design data returned');
            if (!designData.canvas) throw new Error('Canvas data missing');
            if (!designData.metadata) throw new Error('Metadata missing');
            
            return `Objects: ${designData.metadata.objectCount}, Version: ${designData.metadata.version}`;
        });
        
        await this.test('Design Data Import', () => {
            const designData = this.designer.getDesignData();
            
            // Clear canvas
            this.designer.clear();
            const objectsAfterClear = this.designer.canvas.getObjects().length;
            
            // Load design
            this.designer.loadDesignData(designData);
            
            return new Promise((resolve, reject) => {
                setTimeout(() => {
                    const objectsAfterLoad = this.designer.canvas.getObjects().length;
                    if (objectsAfterLoad <= objectsAfterClear) {
                        reject(new Error('Design data not loaded properly'));
                    } else {
                        resolve(`Restored ${objectsAfterLoad} objects`);
                    }
                }, 300);
            });
        });
    }
    
    // Layer Management Tests
    async testLayerManagement() {
        this.log('📚 Testing Layer Management...', 'info');
        
        // Setup test objects
        this.designer.clear();
        
        const rect1 = new fabric.Rect({ left: 100, top: 100, width: 50, height: 50, fill: 'red' });
        const rect2 = new fabric.Rect({ left: 120, top: 120, width: 50, height: 50, fill: 'blue' });
        const rect3 = new fabric.Rect({ left: 140, top: 140, width: 50, height: 50, fill: 'green' });
        
        this.designer.canvas.add(rect1);
        this.designer.canvas.add(rect2);
        this.designer.canvas.add(rect3);
        this.designer.canvas.setActiveObject(rect2);
        this.designer.canvas.renderAll();
        
        await this.test('Bring to Front', () => {
            const initialIndex = this.designer.canvas.getObjects().indexOf(rect2);
            this.designer.bringToFront();
            const newIndex = this.designer.canvas.getObjects().indexOf(rect2);
            
            if (newIndex <= initialIndex) throw new Error('Object not moved to front');
            return `Index: ${initialIndex} → ${newIndex}`;
        });
        
        await this.test('Send to Back', () => {
            this.designer.canvas.setActiveObject(rect2);
            const initialIndex = this.designer.canvas.getObjects().indexOf(rect2);
            this.designer.sendToBack();
            const newIndex = this.designer.canvas.getObjects().indexOf(rect2);
            
            if (newIndex >= initialIndex) throw new Error('Object not moved to back');
            return `Index: ${initialIndex} → ${newIndex}`;
        });
        
        await this.test('Bring Forward', () => {
            this.designer.canvas.setActiveObject(rect1);
            const initialIndex = this.designer.canvas.getObjects().indexOf(rect1);
            this.designer.bringForward();
            const newIndex = this.designer.canvas.getObjects().indexOf(rect1);
            
            return `Index: ${initialIndex} → ${newIndex}`;
        });
        
        await this.test('Send Backward', () => {
            this.designer.canvas.setActiveObject(rect3);
            const initialIndex = this.designer.canvas.getObjects().indexOf(rect3);
            this.designer.sendBackward();
            const newIndex = this.designer.canvas.getObjects().indexOf(rect3);
            
            return `Index: ${initialIndex} → ${newIndex}`;
        });
    }
    
    // Selection and Manipulation Tests
    async testSelectionManipulation() {
        this.log('🎯 Testing Selection & Manipulation...', 'info');
        
        await this.test('Select All Objects', () => {
            this.designer.selectAll();
            const activeObjects = this.designer.canvas.getActiveObjects();
            const totalObjects = this.designer.canvas.getObjects().length;
            
            if (activeObjects.length === 0) throw new Error('No objects selected');
            return `${activeObjects.length}/${totalObjects} objects selected`;
        });
        
        await this.test('Delete Multiple Selected', () => {
            const objectsBefore = this.designer.canvas.getObjects().length;
            this.designer.deleteSelected();
            const objectsAfter = this.designer.canvas.getObjects().length;
            
            if (objectsAfter >= objectsBefore) throw new Error('Objects not deleted');
            return `Objects: ${objectsBefore} → ${objectsAfter}`;
        });
        
        await this.test('Object Property Modification', () => {
            // Add test object
            const testRect = new fabric.Rect({
                left: 200,
                top: 200,
                width: 100,
                height: 100,
                fill: 'purple'
            });
            
            this.designer.canvas.add(testRect);
            this.designer.canvas.setActiveObject(testRect);
            
            // Modify properties
            testRect.set({
                fill: 'orange',
                width: 150,
                angle: 45
            });
            
            this.designer.canvas.renderAll();
            
            if (testRect.fill !== 'orange') throw new Error('Color not changed');
            if (testRect.width !== 150) throw new Error('Width not changed');
            if (testRect.angle !== 45) throw new Error('Rotation not applied');
            
            return `Modified: color=${testRect.fill}, width=${testRect.width}, angle=${testRect.angle}°`;
        });
    }
    
    // Export and Data Tests
    async testExportFunctionality() {
        this.log('📤 Testing Export Functionality...', 'info');
        
        await this.test('Export as PNG', () => {
            const dataURL = this.designer.exportAsImage('png', 0.8);
            if (!dataURL) throw new Error('PNG export failed');
            if (!dataURL.startsWith('data:image/png')) throw new Error('Invalid PNG data URL');
            
            const sizeKB = Math.round(dataURL.length * 0.75 / 1024);
            return `PNG exported (${sizeKB}KB)`;
        });
        
        await this.test('Canvas to JSON', () => {
            const json = this.designer.canvas.toJSON();
            if (!json) throw new Error('JSON export failed');
            if (!json.objects) throw new Error('Objects not in JSON');
            
            return `JSON with ${json.objects.length} objects`;
        });
        
        await this.test('Stats Collection', () => {
            const stats = this.designer.getStats();
            if (!stats) throw new Error('Stats not available');
            
            const requiredFields = ['totalObjects', 'images', 'texts', 'canUndo', 'canRedo', 'isInitialized'];
            for (const field of requiredFields) {
                if (stats[field] === undefined) throw new Error(`Missing stats field: ${field}`);
            }
            
            return `Stats: ${stats.totalObjects} objects, undo:${stats.canUndo}, redo:${stats.canRedo}`;
        });
    }
    
    // Error Handling Tests
    async testErrorHandling() {
        this.log('⚠️ Testing Error Handling...', 'info');
        
        await this.test('Invalid Text Parameters', () => {
            try {
                const textObj = this.designer.addText(null, { fontSize: 'invalid' });
                // Should handle gracefully
                return 'Invalid params handled gracefully';
            } catch (error) {
                throw new Error('Should handle invalid params gracefully');
            }
        });
        
        await this.test('Canvas Operation with No Selection', () => {
            this.designer.canvas.discardActiveObject();
            this.designer.deleteSelected(); // Should not error
            return 'No selection handled gracefully';
        });
        
        await this.test('Undo/Redo at Limits', () => {
            // Try to undo beyond limit
            this.designer.stateManager.currentIndex = -1;
            const undoResult = this.designer.stateManager.undo();
            
            // Try to redo beyond limit  
            this.designer.stateManager.currentIndex = this.designer.stateManager.history.length;
            const redoResult = this.designer.stateManager.redo();
            
            return `Undo at limit: ${!undoResult}, Redo at limit: ${!redoResult}`;
        });
    }
    
    // Memory and Performance Tests
    async testMemoryPerformance() {
        this.log('⚡ Testing Memory & Performance...', 'info');
        
        await this.test('Large Object Count Performance', () => {
            const startTime = performance.now();
            const startMemory = performance.memory?.usedJSHeapSize || 0;
            
            // Add 50 objects rapidly
            for (let i = 0; i < 50; i++) {
                const shape = new fabric.Circle({
                    left: Math.random() * 400,
                    top: Math.random() * 400,
                    radius: 10,
                    fill: `hsl(${Math.random() * 360}, 70%, 50%)`
                });
                this.designer.canvas.add(shape);
            }
            
            this.designer.canvas.renderAll();
            
            const endTime = performance.now();
            const endMemory = performance.memory?.usedJSHeapSize || 0;
            const duration = Math.round(endTime - startTime);
            const memoryDiff = Math.round((endMemory - startMemory) / 1024);
            
            if (duration > 5000) throw new Error('Performance too slow');
            
            return `50 objects in ${duration}ms, +${memoryDiff}KB`;
        });
        
        await this.test('Memory Cleanup Test', () => {
            const beforeObjects = this.designer.canvas.getObjects().length;
            const beforeMemory = performance.memory?.usedJSHeapSize || 0;
            
            this.designer.clear();
            
            const afterObjects = this.designer.canvas.getObjects().length;
            const afterMemory = performance.memory?.usedJSHeapSize || 0;
            const memoryFreed = Math.round((beforeMemory - afterMemory) / 1024);
            
            return `Objects: ${beforeObjects} → ${afterObjects}, Memory freed: ${memoryFreed}KB`;
        });
        
        await this.test('Event Listener Cleanup', () => {
            const initialListeners = this.designer.events.listeners.size;
            const initialCanvasListeners = this.designer.events.canvasListeners.size;
            
            // Add some test listeners
            this.designer.events.addEventListener(document, 'click', () => {});
            this.designer.events.addCanvasListener(this.designer.canvas, 'object:added', () => {});
            
            const withListeners = this.designer.events.listeners.size;
            
            // Cleanup
            this.designer.events.cleanup();
            
            const afterCleanup = this.designer.events.listeners.size;
            
            return `Listeners: ${initialListeners} → ${withListeners} → ${afterCleanup}`;
        });
    }
    
    // Stress Tests
    async testStressCases() {
        this.log('🔥 Testing Stress Cases...', 'info');
        
        await this.test('Rapid Operations Stress Test', () => {
            const startTime = performance.now();
            
            // Rapid add/remove operations
            for (let i = 0; i < 20; i++) {
                const obj = new fabric.Rect({
                    left: Math.random() * 300,
                    top: Math.random() * 300,
                    width: 20,
                    height: 20,
                    fill: 'red'
                });
                
                this.designer.canvas.add(obj);
                if (i % 3 === 0) {
                    this.designer.canvas.remove(obj);
                }
            }
            
            this.designer.canvas.renderAll();
            
            const duration = Math.round(performance.now() - startTime);
            const finalCount = this.designer.canvas.getObjects().length;
            
            if (duration > 3000) throw new Error('Operations too slow');
            
            return `${finalCount} objects, ${duration}ms`;
        });
        
        await this.test('Large Image Simulation', () => {
            // Create large canvas data URL
            const tempCanvas = document.createElement('canvas');
            tempCanvas.width = 1000;
            tempCanvas.height = 1000;
            const ctx = tempCanvas.getContext('2d');
            ctx.fillStyle = 'linear-gradient(45deg, #ff6b6b, #4ecdc4)';
            ctx.fillRect(0, 0, 1000, 1000);
            
            const largeImageData = tempCanvas.toDataURL();
            
            return new Promise((resolve, reject) => {
                fabric.Image.fromURL(largeImageData, (img) => {
                    if (!img) {
                        reject(new Error('Failed to create large image'));
                        return;
                    }
                    
                    img.set({ scaleX: 0.3, scaleY: 0.3 });
                    this.designer.canvas.add(img);
                    this.designer.canvas.renderAll();
                    
                    const sizeKB = Math.round(largeImageData.length * 0.75 / 1024);
                    resolve(`Large image loaded (${sizeKB}KB)`);
                });
            });
        });
    }
    
    // Main test runner
    async runAllTests() {
        this.log('🎬 Starting Comprehensive QA Test Suite...', 'info');
        
        try {
            await this.testDependencies();
            await this.testInitialization();
            await this.testCanvasBasics();
            await this.testTextFunctionality();
            await this.testImageProcessing();
            await this.testStateManagement();
            await this.testLayerManagement();
            await this.testSelectionManipulation();
            await this.testExportFunctionality();
            await this.testErrorHandling();
            await this.testMemoryPerformance();
            await this.testStressCases();
            
            this.generateReport();
        } catch (error) {
            this.log(`🚨 Test suite failed: ${error.message}`, 'error');
        }
    }
    
    generateReport() {
        const duration = Math.round((Date.now() - this.startTime) / 1000);
        const successRate = Math.round((this.passCount / this.testCount) * 100);
        
        this.log('📋 QA TEST REPORT', 'info');
        this.log(`Total Tests: ${this.testCount}`, 'info');
        this.log(`Passed: ${this.passCount}`, 'success');
        this.log(`Failed: ${this.failCount}`, this.failCount > 0 ? 'error' : 'info');
        this.log(`Success Rate: ${successRate}%`, successRate >= 90 ? 'success' : 'warning');
        this.log(`Duration: ${duration}s`, 'info');
        
        if (this.failCount === 0) {
            this.log('🎉 ALL TESTS PASSED! Designer is ready for production.', 'success');
        } else {
            this.log('⚠️ Some tests failed. Review errors above.', 'warning');
        }
        
        // Return summary for external use
        return {
            total: this.testCount,
            passed: this.passCount,
            failed: this.failCount,
            successRate,
            duration,
            results: this.results
        };
    }
    
    // Quick test for specific functionality
    async quickTest(functionality) {
        switch (functionality) {
            case 'canvas':
                return this.testCanvasBasics();
            case 'text':
                return this.testTextFunctionality();
            case 'images':
                return this.testImageProcessing();
            case 'state':
                return this.testStateManagement();
            case 'layers':
                return this.testLayerManagement();
            case 'export':
                return this.testExportFunctionality();
            default:
                throw new Error(`Unknown functionality: ${functionality}`);
        }
    }
}

// Make globally available
window.DesignerQATester = DesignerQATester;

console.log('🧪 DesignerQATester loaded and ready');