/**
 * Initialization and setup methods
 * Part of Enhanced Product Designer
 * 
 * @module initializationMethods
 */

export const initializationMethods = {


init() {
  // Debug configuration
  console.log('=== DESIGNER INITIALIZATION ===');
  console.log('Config:', this.config);
  console.log('swpdDesignerConfig:', typeof swpdDesignerConfig !== 'undefined' ? swpdDesignerConfig : 'not defined');
  if (typeof swpdDesignerConfig !== 'undefined') {
    console.log('Cloudinary config:', swpdDesignerConfig.cloudinary);
    console.log('AJAX URL:', swpdDesignerConfig.ajax_url);
    console.log('Nonce:', swpdDesignerConfig.nonce);
  }

  // Load required libraries
  Promise.all([
    this.loadFabricJS(),
    this.loadCropperJS(),
  ]).then(() => {
    this.setupCanvas();
    setTimeout(() => {
      this.setupEventListeners();
      this.setupKeyboardShortcuts();
      this.setupAnimations();
      this.checkForEditMode(); // This will now correctly use sessionStorage
      this.initializeNewFeatures();
      this.startAutoSave();
      this.setupMobileUI(); // Updated call
    }, 100);
  }).catch(error => {
    console.error('Failed to load required libraries:', error);
    this.showNotification('Failed to load designer. Please refresh the page.', 'error');
  });
},


initializeNewFeatures() {
  try {
    this.setupLayersPanel();
  } catch (error) {
    console.warn('Error setting up layers panel:', error);
  }

  try {
    this.setupAlignmentTools();
  } catch (error) {
    console.warn('Error setting up alignment tools:', error);
  }

  try {
    this.setupAdvancedTextControls();
  } catch (error) {
    console.warn('Error setting up advanced text controls:', error);
  }

  try {
    this.setupProductVariantSelector();
  } catch (error) {
    console.warn('Error setting up product variant selector:', error);
  }

  try {
    this.loadQuantityInfo();
  } catch (error) {
    console.warn('Error loading quantity info:', error);
  }

  try {
    this.setupCanvasControls();
  } catch (error) {
    console.warn('Error setting up canvas controls:', error);
  }

  try {
    this.setupZoomControls();
  } catch (error) {
    console.warn('Error setting up zoom controls:', error);
  }

  try {
    this.setupGridToggle();
  } catch (error) {
    console.warn('Error setting up grid toggle:', error);
  }

  try {
    this.adjustLayoutForDesktop();
  } catch (error) {
    console.warn('Error adjusting layout for desktop:', error);
  }
},



setupCanvas() {
  const checkCanvas = () => {
    const canvasElement = document.getElementById('designer-canvas');
    if (canvasElement) {
      const container = document.querySelector('.canvas-container');
      const isMobile = window.innerWidth <= 768;
      let canvasSize = 800;

      if (container) {
        const containerWidth = container.offsetWidth - 40;
        const containerHeight = container.offsetHeight - 40;
        canvasSize = Math.min(containerWidth, containerHeight, 800);
        if (isMobile) {
          canvasSize = Math.max(canvasSize, 280);
        } else {
          canvasSize = Math.max(canvasSize, 500);
        }
      }

      this.canvas = new fabric.Canvas('designer-canvas', {
        backgroundColor: '#f5f5f5',
        preserveObjectStacking: true,
        width: canvasSize,
        height: canvasSize,
        isDrawingMode: false,
        selection: true,
        allowTouchScrolling: isMobile, // Mobile-optimized setting
      });

      const canvasWrapper = this.canvas.wrapperEl;
      if (canvasWrapper) {
        canvasWrapper.style.margin = '0 auto';
        canvasWrapper.style.display = 'block';
        canvasWrapper.style.position = 'relative';
      }

      const canvasContainerDiv = canvasWrapper.parentElement;
      if (canvasContainerDiv) {
        canvasContainerDiv.style.display = 'flex';
        canvasContainerDiv.style.alignItems = 'center';
        canvasContainerDiv.style.justifyContent = 'center';
        canvasContainerDiv.style.width = '100%';
        canvasContainerDiv.style.height = '100%';
      }

      this.canvas.isDrawingMode = false;
      this.canvas.freeDrawingBrush = null;

      fabric.Image.prototype.crossOrigin = 'anonymous';
      fabric.Object.prototype.transparentCorners = false;
      fabric.Object.prototype.cornerColor = '#0073aa';
      fabric.Object.prototype.cornerSize = isMobile ? 14 : 12; // Mobile-optimized setting
      fabric.Object.prototype.padding = isMobile ? 8 : 10; // Mobile-optimized setting
      fabric.Object.prototype.borderColor = '#0073aa';
      fabric.Object.prototype.borderDashArray = [5, 5];

      this.canvas.on('object:modified', () => {
        if (!this.isReordering && !this.isLoadingDesign) {
          this.saveHistory();
        }
      });

      this.canvas.on('object:added', (e) => {
        if (!this.isRedoing && !this.isReordering && !this.isLoadingDesign && e.target && e.target.selectable !== false) {
          this.saveHistory();
          this.ensureProperLayering();
        }
      });

      this.canvas.on('object:removed', (e) => {
        if (!this.isReordering && !this.isLoadingDesign && e.target && e.target.selectable !== false) {
          this.saveHistory();
        }
      });

      this.canvas.on('path:created', (e) => {
        if (e.path) {
          this.canvas.remove(e.path);
        }
      });

      this.canvas.on('mouse:down', () => {
        if (this.canvas.isDrawingMode) {
          this.canvas.isDrawingMode = false;
        }
      });

      this.handleResize();
      window.addEventListener('resize', () => this.handleResize());
    } else {
      setTimeout(checkCanvas, 100);
    }
  };
  checkCanvas();
},



loadFabricJS() {
  return new Promise((resolve) => {
    if (window.fabric) {
      resolve();
      return;
    }

    const script = document.createElement('script');
    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js';
    script.onload = resolve;
    script.onerror = () => console.error('Failed to load Fabric.js');
    document.head.appendChild(script);
  });
},



loadCropperJS() {
  return new Promise((resolve) => {
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css';
    link.onerror = () => console.error('Failed to load Cropper.js CSS');
    document.head.appendChild(link);

    const script = document.createElement('script');
    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js';
    script.onload = resolve;
    script.onerror = () => console.error('Failed to load Cropper.js');
    document.head.appendChild(script);
  });
},



setupAnimations() {
  const modal = document.querySelector('.designer-modal');
  if (modal) {
    modal.style.transition = 'transform 0.3s ease, opacity 0.3s ease';
  }
}
};
