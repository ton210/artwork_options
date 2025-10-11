/**
 * UI Manager Module
 * Handles lightbox, mobile UI, loading states, and user interactions
 */

import { Utilities } from './utilities.js';

export class UIManager {
  constructor(canvasManager, notificationManager) {
    this.canvasManager = canvasManager;
    this.notifications = notificationManager;
    this.isLoading = false;
    this.resizeHandler = null;
  }

  createFallbackLightbox() {
    if (document.getElementById('designer-lightbox')) return;

    const lightboxHTML = `
      <div id="designer-lightbox" class="designer-lightbox">
        <div class="designer-container">
          <div class="designer-header">
            <div class="designer-logo">
              <h2>Customize Your Design</h2>
            </div>
            <div class="designer-controls">
              <select id="variant-selector" class="variant-selector" style="display: none;">
                <option value="">Select Variant</option>
              </select>
              <button class="btn btn-close" id="close-designer">×</button>
            </div>
          </div>
          <div class="designer-body">
            <div class="designer-sidebar">
              <div class="sidebar-section">
                <h4>ADD ELEMENTS</h4>
                <button class="btn btn-primary upload-image-btn">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12"/>
                  </svg>
                  Upload Image
                </button>
                <input type="file" id="main-file-input" class="image-upload-input" accept="image/*" style="display: none;" multiple>
                <button class="btn btn-secondary add-text-btn">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                    <polyline points="14,2 14,8 20,8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                    <polyline points="10,9 9,9 8,9"/>
                  </svg>
                  ADD TEXT
                </button>
              </div>
              <div class="sidebar-section">
                <h4>HISTORY</h4>
                <div class="history-controls">
                  <button class="btn btn-secondary" id="undo-btn" disabled>UNDO</button>
                  <button class="btn btn-secondary" id="redo-btn" disabled>REDO</button>
                </div>
              </div>
              <div class="sidebar-section">
                <h4>DESIGNS</h4>
                <button class="btn btn-secondary" id="save-design-btn">SAVE DESIGN</button>
                <button class="btn btn-secondary" id="load-design-btn">LOAD DESIGN</button>
              </div>
            </div>
            <div class="canvas-container">
              <div id="loading-spinner" class="loading-spinner" style="display: none;">
                <div class="spinner"></div>
                <p>Loading...</p>
              </div>
              <canvas id="designer-canvas"></canvas>
            </div>
            <div class="properties-panel">
              <h4>Properties</h4>
              <div class="property-group">
                <label>Opacity:</label>
                <input type="range" id="opacity-slider" min="0" max="100" value="100">
                <span id="opacity-value">100%</span>
              </div>
              <div class="property-group">
                <button class="btn btn-danger" id="delete-object-btn">DELETE</button>
              </div>
            </div>
          </div>
          <div class="designer-footer">
            <button class="btn btn-secondary" id="cancel-design">CANCEL</button>
            <button class="btn btn-primary" id="apply-design">APPLY DESIGN</button>
            <button class="btn btn-success" id="add-to-cart-btn" style="display: none;">ADD TO CART</button>
          </div>
        </div>
      </div>

      <div id="text-editor-modal" class="text-modal" style="display: none;">
        <div class="text-modal-content">
          <div class="text-modal-header">
            <h3>Add Text</h3>
            <span class="modal-close">&times;</span>
          </div>
          <div class="text-modal-body">
            <div class="form-group">
              <label for="text-input">Text:</label>
              <textarea id="text-input" placeholder="Enter your text here..."></textarea>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label for="font-family">Font:</label>
                <select id="font-family">
                  <option value="Arial">Arial</option>
                  <option value="Helvetica">Helvetica</option>
                  <option value="Times New Roman">Times New Roman</option>
                  <option value="Georgia">Georgia</option>
                  <option value="Verdana">Verdana</option>
                </select>
              </div>
              <div class="form-group">
                <label for="font-size">Size:</label>
                <input type="number" id="font-size" value="24" min="8" max="200">
              </div>
              <div class="form-group">
                <label for="text-color">Color:</label>
                <input type="color" id="text-color" value="#000000">
              </div>
            </div>
          </div>
          <div class="text-modal-footer">
            <button class="btn btn-secondary" id="cancel-text">Cancel</button>
            <button class="btn btn-primary" id="add-text-confirm">Add Text</button>
          </div>
        </div>
      </div>
    `;

    document.body.insertAdjacentHTML('beforeend', lightboxHTML);
  }

  openLightbox(variantId = null, preservedData = null) {
    let lightbox = document.getElementById('designer-lightbox');

    if (!lightbox) {
      this.createFallbackLightbox();
      lightbox = document.getElementById('designer-lightbox');
    }

    if (!lightbox) {
      this.notifications.show('Designer interface could not be loaded. The plugin may not be properly configured.', 'error');
      console.error('CRITICAL: Could not create fallback lightbox');
      return;
    }

    // Show lightbox
    lightbox.style.display = 'flex';
    if (document.body) {
      document.body.style.overflow = 'hidden';
    }

    setTimeout(() => {
      if (lightbox && lightbox.classList) {
        lightbox.classList.add('active');
      }
    }, 10);

    // Setup variant selector if variants exist
    this.setupVariantSelector();

    this.showLoading();

    // The product designer will handle initialization and loading
    // Don't try to initialize here, just setup the UI
  }

  setupVariantSelector() {
    const selector = document.getElementById('variant-selector');
    if (!selector) return;

    const variants = window.swpdDesignerConfig?.variants || [];
    if (variants.length > 1) {
      selector.style.display = 'block';
      selector.innerHTML = '<option value="">Select Variant</option>';

      variants.forEach(variant => {
        const option = document.createElement('option');
        option.value = variant.id;
        option.textContent = variant.name || `Variant ${variant.id}`;
        selector.appendChild(option);
      });

      // Set current variant
      if (this.currentVariantId) {
        selector.value = this.currentVariantId;
      }

      // Add change listener
      selector.addEventListener('change', (e) => {
        if (e.target.value && this.productDesigner) {
          this.productDesigner.loadVariantData(e.target.value);
        }
      });
    }
  }

  closeLightbox() {
    const lightbox = document.getElementById('designer-lightbox');
    if (lightbox) {
      lightbox.classList.remove('active');
      setTimeout(() => {
        lightbox.style.display = 'none';
        document.body.style.overflow = '';
      }, 300);
    }
  }

  showLoading() {
    const spinner = document.getElementById('loading-spinner');
    if (spinner) {
      spinner.style.display = 'flex';
      this.isLoading = true;
    }
  }

  hideLoading() {
    const spinner = document.getElementById('loading-spinner');
    if (spinner) {
      spinner.style.display = 'none';
      this.isLoading = false;
    }
  }

  setupMobileUI() {
    const isMobile = window.innerWidth <= 768;
    if (!isMobile) return;

    // Add mobile-specific event listeners
    const mobileFileInput = document.getElementById('mobile-file-input');
    if (mobileFileInput && !mobileFileInput.hasAttribute('data-listener-added')) {
      mobileFileInput.addEventListener('change', (e) => {
        if (this.imageHandler) {
          this.imageHandler.handleImageUpload(e);
        }
      });
      mobileFileInput.setAttribute('data-listener-added', 'true');
    }

    // Mobile action buttons
    this.setupMobileButtons();
  }

  setupMobileButtons() {
    const buttons = [
      { id: 'mobile-text-btn', action: () => this.textEditor?.showTextEditor() },
      { id: 'mobile-templates-btn', action: () => this.showTemplatesModal() },
      { id: 'mobile-save-btn', action: () => this.saveDesign() },
      { id: 'mobile-apply-btn', action: () => this.applyDesign() }
    ];

    buttons.forEach(({ id, action }) => {
      const btn = document.getElementById(id);
      if (btn && !btn.hasAttribute('data-listener-added')) {
        btn.addEventListener('click', action);
        btn.setAttribute('data-listener-added', 'true');
      }
    });
  }

  setupResizeHandler() {
    this.resizeHandler = Utilities.debounce(() => {
      this.canvasManager.handleResize();
    }, 250);

    window.addEventListener('resize', this.resizeHandler);
  }

  removeResizeHandler() {
    if (this.resizeHandler) {
      window.removeEventListener('resize', this.resizeHandler);
      this.resizeHandler = null;
    }
  }

  setImageHandler(imageHandler) {
    this.imageHandler = imageHandler;
  }

  setTextEditor(textEditor) {
    this.textEditor = textEditor;
  }

  setProductDesigner(productDesigner) {
    this.productDesigner = productDesigner;
    this.currentVariantId = productDesigner.currentVariantId;
  }
}