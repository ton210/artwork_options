/**
 * SequinSwag Product Designer v2.0
 * Built from the ground up with modern JavaScript and clean architecture
 * Author: SequinSwag Team
 * Version: 2.0.0
 * Date: 2025-09-04
 */

'use strict';

// Ensure single instance
if (window.SequinDesignerV2) {
  console.log('SequinDesigner v2 already loaded');
} else {

/**
 * Configuration Manager
 * Handles all configuration and environment setup
 */
class DesignerConfig {
  constructor(config = {}) {
    this.config = {
      canvasId: 'designer-canvas',
      containerId: 'designer-container',
      canvasWidth: 600,
      canvasHeight: 600,
      maxImageSize: 10 * 1024 * 1024, // 10MB
      supportedFormats: ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
      debug: true,
      ...config
    };
    
    this.wordpress = this.setupWordPress();
    this.validateConfig();
  }
  
  setupWordPress() {
    return {
      ajaxUrl: window.swpdDesignerConfig?.ajax_url || '/wp-admin/admin-ajax.php',
      nonce: window.swpdDesignerConfig?.nonce || '',
      productId: window.swpdDesignerConfig?.product_id || null,
      variants: window.swpdDesignerConfig?.variants || [],
      cloudinary: window.swpdDesignerConfig?.cloudinary || { enabled: false }
    };
  }
  
  validateConfig() {
    const required = ['canvasId', 'canvasWidth', 'canvasHeight'];
    for (const key of required) {
      if (!this.config[key]) {
        throw new Error(`Required config missing: ${key}`);
      }
    }
  }
  
  get(key) {
    return this.config[key];
  }
  
  getWordPress(key) {
    return this.wordpress[key];
  }
}

/**
 * Event Manager
 * Centralized event handling with automatic cleanup
 */
class EventManager {
  constructor() {
    this.listeners = new Map();
    this.canvasListeners = new Map();
  }
  
  addEventListener(element, event, handler, options = {}) {
    if (!element || typeof handler !== 'function') return;
    
    const key = `${element.constructor.name}-${event}`;
    if (!this.listeners.has(key)) {
      this.listeners.set(key, []);
    }
    
    this.listeners.get(key).push({ element, event, handler, options });
    element.addEventListener(event, handler, options);
  }
  
  addCanvasListener(canvas, event, handler) {
    if (!canvas || typeof handler !== 'function') return;
    
    if (!this.canvasListeners.has(event)) {
      this.canvasListeners.set(event, []);
    }
    
    this.canvasListeners.get(event).push(handler);
    canvas.on(event, handler);
  }
  
  cleanup() {
    // Remove DOM event listeners
    this.listeners.forEach(eventList => {
      eventList.forEach(({ element, event, handler, options }) => {
        if (element && element.removeEventListener) {
          element.removeEventListener(event, handler, options);
        }
      });
    });
    
    this.listeners.clear();
    this.canvasListeners.clear();
  }
  
  cleanupCanvas(canvas) {
    if (canvas && canvas.off) {
      canvas.off();
    }
    this.canvasListeners.clear();
  }
}

/**
 * State Manager
 * Handles undo/redo and state persistence
 */
class StateManager {
  constructor(canvas, maxHistory = 20) {
    this.canvas = canvas;
    this.history = [];
    this.currentIndex = -1;
    this.maxHistory = maxHistory;
    this.isRestoring = false;
  }
  
  saveState() {
    if (this.isRestoring || !this.canvas) return;
    
    // Remove any history after current index
    this.history = this.history.slice(0, this.currentIndex + 1);
    
    // Add new state
    const state = this.canvas.toJSON(['customId', 'selectable', 'evented']);
    this.history.push(JSON.stringify(state));
    this.currentIndex++;
    
    // Keep history within limits
    if (this.history.length > this.maxHistory) {
      this.history.shift();
      this.currentIndex--;
    }
    
    console.log(`State saved (${this.currentIndex + 1}/${this.history.length})`);
  }
  
  undo() {
    if (this.currentIndex > 0) {
      this.currentIndex--;
      this.restoreState();
      return true;
    }
    return false;
  }
  
  redo() {
    if (this.currentIndex < this.history.length - 1) {
      this.currentIndex++;
      this.restoreState();
      return true;
    }
    return false;
  }
  
  restoreState() {
    if (this.currentIndex < 0 || !this.history[this.currentIndex]) return;
    
    this.isRestoring = true;
    const state = this.history[this.currentIndex];
    
    this.canvas.loadFromJSON(state, () => {
      this.canvas.renderAll();
      this.isRestoring = false;
      console.log(`State restored to ${this.currentIndex + 1}/${this.history.length}`);
    });
  }
  
  canUndo() {
    return this.currentIndex > 0;
  }
  
  canRedo() {
    return this.currentIndex < this.history.length - 1;
  }
  
  clear() {
    this.history = [];
    this.currentIndex = -1;
  }
}

/**
 * Image Manager
 * Handles image loading, validation, and processing
 */
class ImageManager {
  constructor(config) {
    this.config = config;
    this.loadQueue = [];
    this.isProcessing = false;
  }
  
  validateFile(file) {
    if (!file) {
      throw new Error('No file provided');
    }
    
    if (!this.config.get('supportedFormats').includes(file.type)) {
      throw new Error(`Unsupported format: ${file.type}`);
    }
    
    if (file.size > this.config.get('maxImageSize')) {
      throw new Error(`File too large: ${(file.size / 1024 / 1024).toFixed(1)}MB (max: ${this.config.get('maxImageSize') / 1024 / 1024}MB)`);
    }
    
    return true;
  }
  
  async loadFile(file) {
    this.validateFile(file);
    
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.onload = (e) => resolve(e.target.result);
      reader.onerror = () => reject(new Error('Failed to read file'));
      reader.readAsDataURL(file);
    });
  }
  
  async createFabricImage(imageData, options = {}) {
    return new Promise((resolve, reject) => {
      fabric.Image.fromURL(imageData, (img) => {
        if (!img) {
          reject(new Error('Failed to create fabric image'));
          return;
        }
        
        // Apply default settings
        img.set({
          left: options.left || 100,
          top: options.top || 100,
          scaleX: options.scaleX || 1,
          scaleY: options.scaleY || 1,
          selectable: options.selectable !== false,
          evented: options.evented !== false,
          customId: options.customId || `img_${Date.now()}`
        });
        
        resolve(img);
      }, {
        crossOrigin: 'anonymous'
      });
    });
  }
  
  async processFiles(files, canvas) {
    const results = [];
    
    for (const file of Array.from(files)) {
      try {
        const imageData = await this.loadFile(file);
        const fabricImage = await this.createFabricImage(imageData, {
          left: Math.random() * (canvas.width - 200) + 100,
          top: Math.random() * (canvas.height - 200) + 100
        });
        
        canvas.add(fabricImage);
        results.push({ success: true, image: fabricImage, filename: file.name });
      } catch (error) {
        results.push({ success: false, error: error.message, filename: file.name });
        console.error(`Failed to process ${file.name}:`, error);
      }
    }
    
    return results;
  }
}

/**
 * UI Manager
 * Handles all UI interactions and updates
 */
class UIManager {
  constructor(designer) {
    this.designer = designer;
    this.notifications = [];
  }
  
  showNotification(message, type = 'info', duration = 3000) {
    const notification = document.createElement('div');
    notification.className = `designer-notification ${type}`;
    notification.style.cssText = `
      position: fixed;
      top: 20px;
      right: 20px;
      background: ${type === 'error' ? '#f44336' : type === 'success' ? '#4caf50' : '#2196f3'};
      color: white;
      padding: 12px 20px;
      border-radius: 6px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.3);
      z-index: 999999;
      font-family: -apple-system, BlinkMacSystemFont, sans-serif;
      font-size: 14px;
      max-width: 300px;
      word-wrap: break-word;
      animation: slideInRight 0.3s ease-out;
    `;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    this.notifications.push(notification);
    
    // Auto remove
    setTimeout(() => {
      this.removeNotification(notification);
    }, duration);
    
    return notification;
  }
  
  removeNotification(notification) {
    if (notification && notification.parentNode) {
      notification.style.animation = 'slideOutRight 0.3s ease-in';
      setTimeout(() => {
        if (notification.parentNode) {
          notification.parentNode.removeChild(notification);
        }
      }, 300);
    }
    
    const index = this.notifications.indexOf(notification);
    if (index > -1) {
      this.notifications.splice(index, 1);
    }
  }
  
  updateCanvasInfo(canvas) {
    const info = {
      objects: canvas.getObjects().length,
      images: canvas.getObjects().filter(obj => obj.type === 'image').length,
      texts: canvas.getObjects().filter(obj => obj.type === 'text' || obj.type === 'textbox').length
    };
    
    // Update debug panel if it exists
    const objectCountEl = document.getElementById('object-count');
    if (objectCountEl) {
      objectCountEl.textContent = info.objects;
    }
    
    return info;
  }
  
  cleanup() {
    this.notifications.forEach(notification => {
      this.removeNotification(notification);
    });
  }
}

/**
 * Main SequinSwag Designer Class
 * Modern, clean implementation with proper error handling
 */
class SequinDesignerV2 {
  constructor(config = {}) {
    console.log('🎨 SequinDesigner v2.0 initializing...');
    
    try {
      this.config = new DesignerConfig(config);
      this.events = new EventManager();
      this.ui = new UIManager(this);
      this.imageManager = new ImageManager(this.config);
      
      this.canvas = null;
      this.stateManager = null;
      this.isInitialized = false;
      this.backgroundImage = null;
      this.maskImage = null;
      
      this.init();
    } catch (error) {
      console.error('Failed to initialize SequinDesigner v2:', error);
      throw error;
    }
  }
  
  async init() {
    try {
      await this.loadDependencies();
      await this.setupCanvas();
      this.setupEventListeners();
      this.setupKeyboardShortcuts();
      
      this.isInitialized = true;
      this.ui.showNotification('Designer ready!', 'success');
      console.log('✅ SequinDesigner v2 initialized successfully');
      
      // Make globally available
      window.sequinDesigner = this;
      
    } catch (error) {
      console.error('Designer initialization failed:', error);
      this.ui.showNotification('Failed to initialize designer', 'error');
      throw error;
    }
  }
  
  async loadDependencies() {
    const dependencies = [
      {
        url: 'https://cdnjs.cloudflare.com/ajax/libs/fabric.js/6.0.0-beta21/fabric.min.js',
        check: () => window.fabric,
        name: 'Fabric.js'
      }
    ];
    
    for (const dep of dependencies) {
      if (!dep.check()) {
        await this.loadScript(dep.url, dep.name);
      } else {
        console.log(`${dep.name} already loaded`);
      }
    }
  }
  
  loadScript(url, name) {
    return new Promise((resolve, reject) => {
      if (document.querySelector(`script[src="${url}"]`)) {
        resolve();
        return;
      }
      
      const script = document.createElement('script');
      script.src = url;
      script.onload = () => {
        console.log(`✅ ${name} loaded`);
        resolve();
      };
      script.onerror = () => {
        reject(new Error(`Failed to load ${name}`));
      };
      
      document.head.appendChild(script);
    });
  }
  
  async setupCanvas() {
    const canvasElement = document.getElementById(this.config.get('canvasId'));
    if (!canvasElement) {
      throw new Error(`Canvas element not found: ${this.config.get('canvasId')}`);
    }
    
    // Initialize Fabric canvas
    this.canvas = new fabric.Canvas(canvasElement, {
      width: this.config.get('canvasWidth'),
      height: this.config.get('canvasHeight'),
      backgroundColor: '#ffffff',
      preserveObjectStacking: true,
      selection: true,
      allowTouchScrolling: false
    });
    
    // Initialize state manager
    this.stateManager = new StateManager(this.canvas);
    
    console.log('✅ Canvas initialized');
  }
  
  setupEventListeners() {
    if (!this.canvas) return;
    
    // Canvas events with proper cleanup tracking
    this.events.addCanvasListener(this.canvas, 'object:added', (e) => {
      if (!this.stateManager.isRestoring && e.target) {
        this.stateManager.saveState();
        this.ui.updateCanvasInfo(this.canvas);
      }
    });
    
    this.events.addCanvasListener(this.canvas, 'object:removed', (e) => {
      if (!this.stateManager.isRestoring) {
        this.stateManager.saveState();
        this.ui.updateCanvasInfo(this.canvas);
      }
    });
    
    this.events.addCanvasListener(this.canvas, 'object:modified', (e) => {
      this.stateManager.saveState();
    });
    
    this.events.addCanvasListener(this.canvas, 'selection:created', (e) => {
      this.updateSelectionUI(e.selected);
    });
    
    this.events.addCanvasListener(this.canvas, 'selection:updated', (e) => {
      this.updateSelectionUI(e.selected);
    });
    
    this.events.addCanvasListener(this.canvas, 'selection:cleared', () => {
      this.updateSelectionUI([]);
    });
    
    // Window resize with cleanup tracking
    this.events.addEventListener(window, 'resize', this.debounce(() => {
      this.handleResize();
    }, 250));
    
    console.log('✅ Event listeners setup complete');
  }
  
  setupKeyboardShortcuts() {
    this.events.addEventListener(document, 'keydown', (e) => {
      if (!this.isInitialized) return;
      
      // Undo: Ctrl+Z
      if (e.ctrlKey && e.key === 'z' && !e.shiftKey) {
        e.preventDefault();
        this.undo();
      }
      
      // Redo: Ctrl+Shift+Z
      if (e.ctrlKey && e.shiftKey && e.key === 'Z') {
        e.preventDefault();
        this.redo();
      }
      
      // Delete: Delete key
      if (e.key === 'Delete') {
        this.deleteSelected();
      }
      
      // Select All: Ctrl+A
      if (e.ctrlKey && e.key === 'a') {
        e.preventDefault();
        this.selectAll();
      }
    });
    
    console.log('✅ Keyboard shortcuts setup');
  }
  
  // Public API Methods
  
  async uploadImages(files) {
    try {
      this.ui.showNotification('Processing images...', 'info');
      const results = await this.imageManager.processFiles(files, this.canvas);
      
      const successful = results.filter(r => r.success).length;
      const failed = results.filter(r => !r.success).length;
      
      if (successful > 0) {
        this.canvas.renderAll();
        this.ui.showNotification(`${successful} image(s) added successfully`, 'success');
      }
      
      if (failed > 0) {
        this.ui.showNotification(`${failed} image(s) failed to upload`, 'error');
      }
      
      return results;
    } catch (error) {
      console.error('Upload failed:', error);
      this.ui.showNotification('Upload failed: ' + error.message, 'error');
      return [];
    }
  }
  
  addText(text = 'Sample Text', options = {}) {
    try {
      const fabricText = new fabric.Text(text, {
        left: options.left || this.canvas.width / 2,
        top: options.top || this.canvas.height / 2,
        fontSize: options.fontSize || 24,
        fill: options.fill || '#000000',
        fontFamily: options.fontFamily || 'Arial',
        textAlign: options.textAlign || 'left',
        originX: 'center',
        originY: 'center',
        customId: `text_${Date.now()}`
      });
      
      this.canvas.add(fabricText);
      this.canvas.setActiveObject(fabricText);
      this.canvas.renderAll();
      
      this.ui.showNotification('Text added', 'success');
      return fabricText;
    } catch (error) {
      console.error('Failed to add text:', error);
      this.ui.showNotification('Failed to add text', 'error');
      return null;
    }
  }
  
  deleteSelected() {
    const activeObjects = this.canvas.getActiveObjects();
    if (activeObjects.length === 0) return;
    
    this.canvas.discardActiveObject();
    activeObjects.forEach(obj => {
      // Don't delete background or mask images
      if (obj !== this.backgroundImage && obj !== this.maskImage) {
        this.canvas.remove(obj);
      }
    });
    
    this.canvas.renderAll();
    this.ui.showNotification(`${activeObjects.length} object(s) deleted`, 'info');
  }
  
  selectAll() {
    const allObjects = this.canvas.getObjects().filter(obj => 
      obj.selectable && obj !== this.backgroundImage && obj !== this.maskImage
    );
    
    if (allObjects.length > 0) {
      const selection = new fabric.ActiveSelection(allObjects, {
        canvas: this.canvas
      });
      this.canvas.setActiveObject(selection);
      this.canvas.renderAll();
    }
  }
  
  undo() {
    if (this.stateManager.undo()) {
      this.ui.showNotification('Undo', 'info', 1000);
    }
  }
  
  redo() {
    if (this.stateManager.redo()) {
      this.ui.showNotification('Redo', 'info', 1000);
    }
  }
  
  clear() {
    // Remove all objects except background and mask
    const objectsToRemove = this.canvas.getObjects().filter(obj => 
      obj !== this.backgroundImage && obj !== this.maskImage
    );
    
    objectsToRemove.forEach(obj => this.canvas.remove(obj));
    this.canvas.renderAll();
    this.stateManager.clear();
    
    this.ui.showNotification('Canvas cleared', 'info');
  }
  
  exportAsImage(format = 'png', quality = 1) {
    try {
      const dataURL = this.canvas.toDataURL({
        format: format,
        quality: quality,
        multiplier: 2 // High resolution export
      });
      
      // Create download link
      const link = document.createElement('a');
      link.download = `sequin-design-${Date.now()}.${format}`;
      link.href = dataURL;
      link.click();
      
      this.ui.showNotification('Image exported', 'success');
      return dataURL;
    } catch (error) {
      console.error('Export failed:', error);
      this.ui.showNotification('Export failed', 'error');
      return null;
    }
  }
  
  getDesignData() {
    if (!this.canvas) return null;
    
    return {
      canvas: this.canvas.toJSON(['customId']),
      metadata: {
        version: '2.0.0',
        timestamp: Date.now(),
        objectCount: this.canvas.getObjects().length,
        productId: this.config.getWordPress('productId')
      }
    };
  }
  
  loadDesignData(data) {
    try {
      if (!data || !data.canvas) {
        throw new Error('Invalid design data');
      }
      
      this.stateManager.isRestoring = true;
      this.canvas.loadFromJSON(data.canvas, () => {
        this.canvas.renderAll();
        this.stateManager.isRestoring = false;
        this.stateManager.clear();
        this.stateManager.saveState();
        this.ui.showNotification('Design loaded', 'success');
      });
    } catch (error) {
      console.error('Failed to load design:', error);
      this.ui.showNotification('Failed to load design', 'error');
    }
  }
  
  // Layer management
  bringToFront() {
    const activeObject = this.canvas.getActiveObject();
    if (activeObject && activeObject !== this.backgroundImage && activeObject !== this.maskImage) {
      this.canvas.bringToFront(activeObject);
      this.canvas.renderAll();
    }
  }
  
  sendToBack() {
    const activeObject = this.canvas.getActiveObject();
    if (activeObject && activeObject !== this.backgroundImage && activeObject !== this.maskImage) {
      this.canvas.sendToBack(activeObject);
      // Ensure background stays at bottom
      if (this.backgroundImage) {
        this.canvas.sendToBack(this.backgroundImage);
      }
      this.canvas.renderAll();
    }
  }
  
  bringForward() {
    const activeObject = this.canvas.getActiveObject();
    if (activeObject && activeObject !== this.backgroundImage && activeObject !== this.maskImage) {
      this.canvas.bringForward(activeObject);
      this.canvas.renderAll();
    }
  }
  
  sendBackward() {
    const activeObject = this.canvas.getActiveObject();
    if (activeObject && activeObject !== this.backgroundImage && activeObject !== this.maskImage) {
      this.canvas.sendBackwards(activeObject);
      this.canvas.renderAll();
    }
  }
  
  // Helper methods
  
  updateSelectionUI(selected) {
    // Override in subclass or through events
    console.log('Selection updated:', selected?.length || 0, 'objects');
  }
  
  handleResize() {
    if (!this.canvas) return;
    
    const container = this.canvas.getElement().parentElement;
    if (!container) return;
    
    const containerWidth = container.clientWidth;
    const containerHeight = container.clientHeight;
    
    // Maintain aspect ratio
    const aspectRatio = this.config.get('canvasWidth') / this.config.get('canvasHeight');
    let newWidth = containerWidth;
    let newHeight = containerWidth / aspectRatio;
    
    if (newHeight > containerHeight) {
      newHeight = containerHeight;
      newWidth = containerHeight * aspectRatio;
    }
    
    const scale = newWidth / this.config.get('canvasWidth');
    
    this.canvas.setDimensions({
      width: newWidth,
      height: newHeight
    });
    
    this.canvas.setZoom(scale);
    this.canvas.renderAll();
  }
  
  debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }
  
  // Public API for external integration
  
  openDesigner() {
    console.log('Designer opened');
    this.ui.showNotification('Designer opened', 'info');
  }
  
  closeDesigner() {
    console.log('Designer closed');
    this.ui.showNotification('Designer closed', 'info');
  }
  
  getStats() {
    if (!this.canvas) return null;
    
    const objects = this.canvas.getObjects();
    return {
      totalObjects: objects.length,
      images: objects.filter(obj => obj.type === 'image').length,
      texts: objects.filter(obj => obj.type === 'text' || obj.type === 'textbox').length,
      canUndo: this.stateManager?.canUndo() || false,
      canRedo: this.stateManager?.canRedo() || false,
      isInitialized: this.isInitialized
    };
  }
  
  // Cleanup and disposal
  
  destroy() {
    console.log('🧹 Cleaning up SequinDesigner v2...');
    
    this.isInitialized = false;
    
    if (this.events) {
      this.events.cleanup();
      if (this.canvas) {
        this.events.cleanupCanvas(this.canvas);
      }
    }
    
    if (this.ui) {
      this.ui.cleanup();
    }
    
    if (this.canvas) {
      this.canvas.dispose();
      this.canvas = null;
    }
    
    this.stateManager = null;
    this.backgroundImage = null;
    this.maskImage = null;
    
    // Remove from global
    if (window.sequinDesigner === this) {
      window.sequinDesigner = null;
    }
    
    console.log('✅ Cleanup complete');
  }
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  console.log('🚀 DOM ready, checking for designer initialization...');
  
  // Check if we should auto-initialize
  const canvasElement = document.getElementById('designer-canvas');
  if (canvasElement && !window.sequinDesigner) {
    try {
      window.sequinDesigner = new SequinDesignerV2();
    } catch (error) {
      console.error('Auto-initialization failed:', error);
    }
  }
});

// Export for global use
window.SequinDesignerV2 = SequinDesignerV2;

console.log('📦 SequinDesigner v2.0 module loaded');

} // End single instance check