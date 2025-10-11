/**
 * Main Entry Point for Modular Product Designer
 * Initializes the designer and sets up global access
 */

import { EnhancedProductDesigner } from './product-designer.js';
import { StyleInjector } from './style-injector.js';

// Prevent duplicate script execution
if (!window.swpdScriptLoaded) {
  window.swpdScriptLoaded = true;

  // Create default configurations if missing
  if (typeof window.swpdDesignerConfig === 'undefined') {
    console.warn('swpdDesignerConfig not found, using defaults');
    window.swpdDesignerConfig = {
      ajax_url: '/wp-admin/admin-ajax.php',
      nonce: '',
      product_id: null,
      variants: [],
      cloudinary: { enabled: false, cloudName: '', uploadPreset: '' },
      debug: true
    };
  }

  if (typeof window.swpdTranslations === 'undefined') {
    console.warn('swpdTranslations not found, using defaults');
    window.swpdTranslations = {
      editDesign: 'Edit Design',
      addToCart: 'Add to Cart',
      startDesigning: 'Start Designing',
      tapToUpload: 'Tap the upload button below to add your first image',
      customizeDesign: 'Customize Design',
      uploadImage: 'Upload Image',
      addText: 'Add Text',
      save: 'Save',
      cancel: 'Cancel',
      apply: 'Apply Design',
      undo: 'Undo',
      redo: 'Redo',
      delete: 'Delete',
      loading: 'Loading...',
      error: 'Error',
      success: 'Success'
    };
  }

  // Initialize when DOM is ready
  document.addEventListener('DOMContentLoaded', async () => {
    // Inject essential styles
    StyleInjector.injectStyles();

    // Wait for DOM to be fully ready
    await new Promise(resolve => setTimeout(resolve, 100));

    // Prevent multiple instances
    if (window.customDesigner) {
      return;
    }

    // Initialize designer if on product page
    if (typeof swpdDesignerConfig !== 'undefined' || window.location.pathname.includes('product')) {
      try {
        window.customDesigner = new EnhancedProductDesigner(swpdDesignerConfig);
      } catch (error) {
        console.error('Error initializing custom designer:', error);
      }
    }

    // Setup global event listeners
    setupGlobalEventListeners();
  });

  function setupGlobalEventListeners() {
    // Customize design button handler
    if (document.body) {
      document.body.addEventListener('click', async function(e) {
        if (e.target && e.target.id === 'swpd-customize-design-button') {
          e.preventDefault();

          if (window.customDesigner) {
            const productId = typeof swpdDesignerConfig !== 'undefined' ? swpdDesignerConfig.product_id : null;
            let variantId = productId;

            const variationInput = document.querySelector('input[name="variation_id"]');
            if (variationInput && variationInput.value > 0) {
              variantId = variationInput.value;
            }

            // Open lightbox - it will handle initialization internally
            await window.customDesigner.openLightbox(variantId);
          } else {
            alert('Designer could not be opened. Please refresh the page and try again.');
          }
        }
      });
    }

    // Edit design button handler
    if (document.body) {
      document.body.addEventListener('click', function(e) {
        if (e.target && e.target.matches('.swpd-edit-design-button')) {
          e.preventDefault();

          const button = e.target;
          const productUrl = button.dataset.productUrl;
          const variantId = button.dataset.variantId;
          const cartKey = button.id ? button.id.replace('swpd-edit-', '') : null;

          if (cartKey && window.swpdCanvasData && window.swpdCanvasData[cartKey]) {
            let canvasData = window.swpdCanvasData[cartKey];
            if (typeof canvasData !== 'string') {
              canvasData = JSON.stringify(canvasData);
            }

            sessionStorage.setItem('edit_design_data', canvasData);
            sessionStorage.setItem('edit_design_variant', variantId.toString());
            sessionStorage.setItem('edit_cart_key', cartKey);

            window.location.href = productUrl + '?edit_design=1&variant=' + variantId;
          } else {
            alert('Unable to load design data. Please refresh the page and try again.');
          }
        }
      });
    }

    // Auto-open designer for edit mode
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('edit_design') === '1') {
      let attempts = 0;
      const initInterval = setInterval(async () => {
        if (window.customDesigner && typeof window.customDesigner.openLightbox === 'function') {
          clearInterval(initInterval);

          await window.customDesigner.waitForInitialization();

          const variantId = urlParams.get('variant');
          window.customDesigner.openLightbox(variantId);

          // Clean URL
          const cleanUrl = window.location.pathname;
          window.history.replaceState({}, document.title, cleanUrl);
        } else if (++attempts > 50) {
          clearInterval(initInterval);
          console.error("Designer didn't initialize for auto-open.");
        }
      }, 100);
    }
  }

  // Cleanup on page unload
  window.addEventListener('beforeunload', () => {
    if (window.customDesigner && typeof window.customDesigner.destroy === 'function') {
      window.customDesigner.destroy();
    }
  });

  // Make constructor available globally
  window.EnhancedProductDesigner = EnhancedProductDesigner;
}