/**
 * Main Product Designer Module
 * Orchestrates all other modules and provides the main API
 */

import { LibraryLoader } from './library-loader.js';
import { Analytics, Utilities, NotificationManager } from './utilities.js';
import { CanvasManager } from './canvas-manager.js';
import { LayerManager } from './layer-manager.js';
import { HistoryManager } from './history-manager.js';
import { ImageHandler } from './image-handler.js';
import { TextEditor } from './text-editor.js';
import { UIManager } from './ui-manager.js';

export class EnhancedProductDesigner {
  constructor(config = {}) {
    // Set default configuration
    this.config = {
      ajax_url: '/wp-admin/admin-ajax.php',
      nonce: '',
      product_id: null,
      variants: [],
      cloudinary: { enabled: false, cloudName: '', uploadPreset: '' },
      debug: false,
      ...config
    };

    // Initialize state
    this.isInitialized = false;
    this.isInitializing = false;
    this.currentVariantId = null;

    // Initialize modules
    this.initializeModules();

    // Start initialization
    this.init();
  }

  initializeModules() {
    this.libraryLoader = new LibraryLoader();
    this.analytics = new Analytics(this.config);
    this.notifications = new NotificationManager();
    this.canvasManager = new CanvasManager();
    this.layerManager = new LayerManager(this.canvasManager);
    this.historyManager = new HistoryManager(this.canvasManager, this.layerManager);
    this.imageHandler = new ImageHandler(this.canvasManager, this.layerManager, this.notifications, this.analytics);
    this.textEditor = new TextEditor(this.canvasManager, this.layerManager, this.notifications, this.analytics);
    this.uiManager = new UIManager(this.canvasManager, this.notifications);

    // Set up cross-module references
    this.uiManager.setImageHandler(this.imageHandler);
    this.uiManager.setTextEditor(this.textEditor);
    this.uiManager.setProductDesigner(this);
  }

  async init() {
    if (this.isInitializing || this.isInitialized) return;
    this.isInitializing = true;

    try {
      await this._performInit();
    } catch (error) {
      console.error('Failed to initialize designer:', error);
      this.notifications.show('Failed to load designer. Please refresh the page.', 'error');
      this.isInitializing = false;
      throw error;
    }
  }

  async _performInit() {
    try {
      // Load required libraries
      await this.libraryLoader.loadAllDependencies();

      // Verify libraries loaded
      if (!window.fabric || !window.Cropper) {
        throw new Error('Required libraries failed to load');
      }

      // Setup canvas
      await this.canvasManager.setupCanvas();

      // Setup modules
      this.setupCanvasEventListeners();
      this.setupUIEventListeners();
      this.uiManager.setupResizeHandler();
      this.uiManager.setupMobileUI();

      // Start auto-save
      this.startAutoSave();

      this.isInitialized = true;
      this.isInitializing = false;

    } catch (error) {
      this.isInitializing = false;
      throw error;
    }
  }

  setupCanvasEventListeners() {
    const canvas = this.canvasManager.getCanvas();
    if (!canvas) return;

    // History management
    canvas.on('object:modified', () => {
      if (!this.historyManager.isReordering && !this.historyManager.isLoadingDesign) {
        this.historyManager.saveHistory();
      }
    });

    canvas.on('object:added', (e) => {
      if (!this.historyManager.isRedoing &&
          !this.historyManager.isReordering &&
          !this.historyManager.isLoadingDesign &&
          e.target && e.target.selectable !== false) {
        this.historyManager.saveHistory();
        // Don't call layering if uploading - will be handled by upload
        if (!this.imageHandler.isUploadingImage) {
          this.layerManager.ensureProperLayering();
        }
      }
    });

    canvas.on('object:removed', (e) => {
      if (!this.historyManager.isReordering &&
          !this.historyManager.isLoadingDesign &&
          e.target && e.target.selectable !== false) {
        this.historyManager.saveHistory();
      }
    });

    // Selection handling
    canvas.on('selection:created', (e) => this.updatePropertiesPanel(e.selected[0]));
    canvas.on('selection:updated', (e) => this.updatePropertiesPanel(e.selected[0]));
    canvas.on('selection:cleared', () => this.updatePropertiesPanel(null));
  }

  setupUIEventListeners() {
    // Upload listeners
    this.imageHandler.setupUploadListeners();

    // Text editor listeners
    this.textEditor.setupTextEditorListeners();

    // History buttons
    document.getElementById('undo-btn')?.addEventListener('click', () => this.historyManager.undo());
    document.getElementById('redo-btn')?.addEventListener('click', () => this.historyManager.redo());

    // Main UI buttons
    document.getElementById('close-designer')?.addEventListener('click', () => this.closeLightbox());
    document.getElementById('cancel-design')?.addEventListener('click', () => this.closeLightbox());
    document.getElementById('apply-design')?.addEventListener('click', () => this.applyDesign());

    // Upload button
    const uploadBtn = document.querySelector('.upload-image-btn');
    if (uploadBtn) {
      uploadBtn.addEventListener('click', () => {
        document.getElementById('main-file-input')?.click();
      });
    }

    // Properties panel
    this.setupPropertiesPanel();
  }

  setupPropertiesPanel() {
    const opacitySlider = document.getElementById('opacity-slider');
    const deleteBtn = document.getElementById('delete-object-btn');

    if (opacitySlider) {
      opacitySlider.addEventListener('input', (e) => {
        const canvas = this.canvasManager.getCanvas();
        const activeObject = canvas?.getActiveObject();
        if (activeObject) {
          activeObject.set('opacity', e.target.value / 100);
          canvas.renderAll();
          document.getElementById('opacity-value').textContent = e.target.value + '%';
        }
      });
    }

    if (deleteBtn) {
      deleteBtn.addEventListener('click', () => {
        const canvas = this.canvasManager.getCanvas();
        const activeObject = canvas?.getActiveObject();
        if (activeObject && activeObject.selectable !== false) {
          canvas.remove(activeObject);
          canvas.renderAll();
        }
      });
    }
  }

  updatePropertiesPanel(obj) {
    const panel = document.querySelector('.properties-panel');
    const opacitySlider = document.getElementById('opacity-slider');
    const opacityValue = document.getElementById('opacity-value');
    const deleteBtn = document.getElementById('delete-object-btn');

    if (!panel) return;

    if (obj && obj.selectable !== false) {
      panel.style.display = 'block';

      if (opacitySlider && opacityValue) {
        const opacity = Math.round((obj.opacity || 1) * 100);
        opacitySlider.value = opacity;
        opacityValue.textContent = opacity + '%';
      }

      if (deleteBtn) {
        deleteBtn.style.display = 'block';
      }
    } else {
      if (window.innerWidth > 768) {
        panel.style.display = 'none';
      }
    }
  }

  async waitForInitialization(timeout = 5000) {
    const startTime = Date.now();
    while (!this.isInitialized && (Date.now() - startTime) < timeout) {
      await new Promise(resolve => setTimeout(resolve, 100));
    }
    return this.isInitialized;
  }

  // API Methods
  async openLightbox(variantId, preservedData) {
    this.uiManager.openLightbox(variantId, preservedData);

    // Ensure designer is initialized first
    if (!this.isInitialized) {
      await this.waitForInitialization();
    }

    // Load variant data if provided
    if (variantId) {
      await this.loadVariantData(variantId);
    } else {
      // Hide loading if no variant to load
      this.uiManager.hideLoading();
    }
  }

  closeLightbox() {
    this.uiManager.closeLightbox();
  }

  async loadVariantData(variantId) {
    try {
      this.currentVariantId = variantId;

      // Get variant configuration from global config
      const variant = this.config.variants?.find(v => v.id == variantId);
      if (!variant) {
        console.error('Variant not found:', variantId);
        return;
      }

      // Parse design data
      let designData;
      try {
        designData = typeof variant._design_tool_layer === 'string'
          ? JSON.parse(variant._design_tool_layer)
          : variant._design_tool_layer;
      } catch (error) {
        console.error('Failed to parse design data:', error);
        this.uiManager.hideLoading();
        return;
      }

      // Load images
      await this.loadDesignImages(designData);

      this.uiManager.hideLoading();
    } catch (error) {
      console.error('Error loading variant data:', error);
      this.uiManager.hideLoading();
    }
  }

  async loadDesignImages(data) {
    const canvas = this.canvasManager.getCanvas();
    if (!canvas || !data) return;

    try {
      // Load background image
      if (data.baseImage) {
        this.canvasManager.backgroundImage = await this.loadImagePromise(data.baseImage);
        this.canvasManager.backgroundImage.selectable = false;
        this.canvasManager.backgroundImage.evented = false;
        canvas.add(this.canvasManager.backgroundImage);
        this.canvasManager.backgroundImage.center();
      }

      // Load mask image
      if (data.alphaMask) {
        this.canvasManager.maskImage = await this.loadImagePromise(data.alphaMask);
        this.canvasManager.maskImage.selectable = false;
        this.canvasManager.maskImage.evented = false;
        canvas.add(this.canvasManager.maskImage);
        this.canvasManager.maskImage.center();
      }

      // Setup clipping bounds
      this.canvasManager.setupClipBounds();

      // Ensure proper layering
      this.layerManager.ensureProperLayering();
      canvas.renderAll();

    } catch (error) {
      console.error('Error loading design images:', error);
      throw error;
    }
  }

  loadImagePromise(url) {
    return new Promise((resolve, reject) => {
      fabric.Image.fromURL(url, (img) => {
        if (img) {
          resolve(img);
        } else {
          reject(new Error('Failed to load image'));
        }
      }, { crossOrigin: 'anonymous' });
    });
  }

  async applyDesign() {
    const canvas = this.canvasManager.getCanvas();
    if (!canvas) return;

    const userObjects = canvas.getObjects().filter(obj => obj.selectable !== false);
    if (userObjects.length === 0) {
      this.notifications.show('Please add at least one element to your design', 'error');
      return;
    }

    try {
      // Generate preview
      const dataURL = this.canvasManager.getCanvasDataURL();

      // Save design data
      const designData = JSON.stringify(canvas.toJSON(['selectable', 'uploadId']));

      // Update product form
      this.updateProductForm(designData, dataURL);

      this.notifications.show('Design applied successfully!', 'success');
      this.closeLightbox();

    } catch (error) {
      console.error('Error applying design:', error);
      this.notifications.show('Error applying design. Please try again.', 'error');
    }
  }

  updateProductForm(designData, previewUrl) {
    // Add design data to product form
    let designInput = document.querySelector('input[name="design_tool_layer"]');
    if (!designInput) {
      designInput = document.createElement('input');
      designInput.type = 'hidden';
      designInput.name = 'design_tool_layer';

      const form = document.querySelector('form.cart');
      if (form) {
        form.appendChild(designInput);
      }
    }

    if (designInput) {
      designInput.value = designData;
    }

    // Update preview image if exists
    const previewImg = document.querySelector('.swpd-design-preview');
    if (previewImg && previewUrl) {
      previewImg.src = previewUrl;
      previewImg.style.display = 'block';
    }

    // Update UI after design
    this.updateUIAfterDesign();
  }

  updateUIAfterDesign() {
    if (document.body) {
      document.body.classList.add('swpd-design-applied');
    }

    const customizeBtn = document.getElementById('swpd-customize-design-button');
    if (customizeBtn) {
      customizeBtn.style.display = 'inline-flex';
      customizeBtn.innerHTML = `
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path d="M12 20h9M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/>
        </svg>
        ${(typeof swpdTranslations !== 'undefined' && swpdTranslations.editDesign) ? swpdTranslations.editDesign : 'Edit Design'}
      `;
    }

    const productForm = document.querySelector('form.cart');
    if (productForm) {
      const addToCartBtn = productForm.querySelector('button[name="add-to-cart"], button[type="submit"]:not(#swpd-customize-design-button)');
      if (addToCartBtn) {
        addToCartBtn.style.display = 'inline-block';
        addToCartBtn.textContent = (typeof swpdTranslations !== 'undefined' && swpdTranslations.addToCart) ? swpdTranslations.addToCart : 'Add to Cart';
      }
    }
  }

  startAutoSave() {
    if (this.autoSaveInterval) {
      clearInterval(this.autoSaveInterval);
    }

    this.autoSaveInterval = setInterval(() => {
      this.performAutoSave();
    }, 30000); // Auto-save every 30 seconds
  }

  stopAutoSave() {
    if (this.autoSaveInterval) {
      clearInterval(this.autoSaveInterval);
      this.autoSaveInterval = null;
    }
  }

  async performAutoSave() {
    const canvas = this.canvasManager.getCanvas();
    if (!canvas) return;

    const userObjects = canvas.getObjects().filter(obj => obj.selectable !== false);
    if (userObjects.length === 0) return;

    try {
      const designData = JSON.stringify(canvas.toJSON(['selectable', 'uploadId']));

      // Send to server for auto-save
      const response = await fetch(this.config.ajax_url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
          action: 'swpd_autosave_design',
          nonce: this.config.nonce,
          design_data: designData,
          product_id: this.config.product_id,
          variant_id: this.currentVariantId
        })
      });

      if (response.ok) {
        const result = await response.json();
        if (result.success) {
          console.log('Design auto-saved successfully');
        }
      }
    } catch (error) {
      console.error('Auto-save failed:', error);
    }
  }

  // Cleanup
  destroy() {
    this.stopAutoSave();
    this.uiManager.removeResizeHandler();
    this.canvasManager.dispose();
  }
}