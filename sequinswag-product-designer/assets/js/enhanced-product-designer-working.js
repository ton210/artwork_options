/**
 * Enhanced Product Designer - Working Version
 * Simplified for immediate functionality
 */

// Prevent duplicate loading
if (typeof window.EnhancedProductDesigner === 'undefined' && !window.swpdScriptLoaded) {
  window.swpdScriptLoaded = true;

  // Create default configurations if they don't exist
  if (typeof window.swpdDesignerConfig === 'undefined') {
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
    window.swpdTranslations = {
      editDesign: 'Edit Design',
      addToCart: 'Add to Cart',
      startDesigning: 'Start Designing',
      customizeDesign: 'Customize Design',
      uploadImage: 'Upload Image',
      addText: 'Add Text',
      cancel: 'Cancel',
      apply: 'Apply Design'
    };
  }

  class EnhancedProductDesigner {
    constructor(config = {}) {
      this.config = config;
      this.canvas = null;
      this.isInitialized = false;
      this.currentVariantId = null;
      this.backgroundImage = null;
      this.maskImage = null;
      this.isUploadingImage = false;
      this.history = [];
      this.historyStep = -1;

      this.init();
    }

    async init() {
      try {
        // Load Fabric.js if not loaded
        if (!window.fabric) {
          await this.loadScript('https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js');
        }

        this.isInitialized = true;
      } catch (error) {
        console.error('Failed to initialize designer:', error);
      }
    }

    loadScript(src) {
      return new Promise((resolve, reject) => {
        if (document.querySelector(`script[src="${src}"]`)) {
          resolve();
          return;
        }

        const script = document.createElement('script');
        script.src = src;
        script.onload = resolve;
        script.onerror = reject;
        document.head.appendChild(script);
      });
    }

    async openLightbox(variantId) {
      try {
        console.log('🔓 Opening lightbox for variant:', variantId);

        // Create lightbox HTML
        this.createLightboxHTML();

        const lightbox = document.getElementById('designer-lightbox');
        if (!lightbox) {
          console.error('❌ Failed to create lightbox');
          return;
        }

        // Show lightbox with animation
        lightbox.style.display = 'flex';

        // Force a reflow to ensure display:flex is applied before opacity change
        lightbox.offsetHeight;

        // Animate in
        setTimeout(() => {
          lightbox.style.opacity = '1';
        }, 10);

        // Prevent background scrolling
        if (document.body) {
          document.body.style.overflow = 'hidden';
          console.log('🔒 Background scrolling disabled');
        }

        // Setup canvas
        console.log('🎨 Setting up canvas...');
        await this.setupCanvas();

        // Load variant data
        if (variantId) {
          console.log('📊 Loading variant data...');
          await this.loadVariantData(variantId);
        }

        console.log('✅ Lightbox opened successfully');

      } catch (error) {
        console.error('❌ Error opening lightbox:', error);
        // Ensure scrolling is restored on error
        if (document.body) {
          document.body.style.overflow = '';
        }
      }
    }

    createLightboxHTML() {
      if (document.getElementById('designer-lightbox')) return;

      const html = `
        <div id="designer-lightbox" style="
          position: fixed !important;
          top: 0 !important;
          left: 0 !important;
          width: 100% !important;
          height: 100% !important;
          background: rgba(0,0,0,0.9) !important;
          z-index: 2147483647 !important;
          display: flex !important;
          justify-content: center !important;
          align-items: center !important;
          opacity: 0;
          transition: opacity 0.3s ease;
          pointer-events: auto !important;">
          <div style="
            width: 95% !important;
            height: 95% !important;
            max-width: 1400px !important;
            background: white !important;
            border-radius: 12px !important;
            display: flex !important;
            flex-direction: column !important;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4) !important;
            overflow: hidden !important;">
            <div style="
              padding: 20px !important;
              border-bottom: 2px solid #eee !important;
              display: flex !important;
              justify-content: space-between !important;
              align-items: center !important;
              background: #f8f9fa !important;">
              <h2 style="margin: 0 !important; color: #333 !important; font-size: 24px !important;">Customize Your Design</h2>
              <button id="close-designer" style="
                background: #dc3545 !important;
                color: white !important;
                border: none !important;
                width: 40px !important;
                height: 40px !important;
                border-radius: 50% !important;
                cursor: pointer !important;
                font-size: 24px !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;">×</button>
            </div>
            <div style="
              flex: 1 !important;
              display: flex !important;
              padding: 20px !important;
              gap: 20px !important;
              min-height: 0 !important;">
              <div style="
                width: 250px !important;
                display: flex !important;
                flex-direction: column !important;
                gap: 15px !important;
                background: #f8f9fa !important;
                padding: 20px !important;
                border-radius: 8px !important;">
                <button id="upload-btn" style="
                  padding: 15px !important;
                  background: #007cba !important;
                  color: white !important;
                  border: none !important;
                  border-radius: 6px !important;
                  cursor: pointer !important;
                  font-weight: 600 !important;
                  display: flex !important;
                  align-items: center !important;
                  justify-content: center !important;">📸 Upload Image</button>
                <input type="file" id="file-input" accept="image/*" style="display: none !important;">
                <button id="add-text-btn" style="
                  padding: 15px !important;
                  background: #6c757d !important;
                  color: white !important;
                  border: none !important;
                  border-radius: 6px !important;
                  cursor: pointer !important;
                  font-weight: 600 !important;
                  display: flex !important;
                  align-items: center !important;
                  justify-content: center !important;">📝 Add Text</button>
              </div>
              <div style="
                flex: 1 !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                background: #f0f0f0 !important;
                border-radius: 8px !important;
                min-height: 500px !important;
                position: relative !important;">
                <canvas id="designer-canvas" style="border: 2px solid #ddd !important; border-radius: 4px !important;"></canvas>
              </div>
            </div>
            <div style="
              padding: 20px !important;
              border-top: 2px solid #eee !important;
              display: flex !important;
              justify-content: flex-end !important;
              gap: 15px !important;
              background: #f8f9fa !important;">
              <button id="cancel-btn" style="
                padding: 12px 24px !important;
                background: #6c757d !important;
                color: white !important;
                border: none !important;
                border-radius: 6px !important;
                cursor: pointer !important;
                font-weight: 600 !important;">Cancel</button>
              <button id="apply-btn" style="
                padding: 12px 24px !important;
                background: #28a745 !important;
                color: white !important;
                border: none !important;
                border-radius: 6px !important;
                cursor: pointer !important;
                font-weight: 600 !important;">Apply Design</button>
            </div>
          </div>
        </div>
      `;

      document.body.insertAdjacentHTML('beforeend', html);
      this.setupEventListeners();
    }

    async setupCanvas() {
      const canvasElement = document.getElementById('designer-canvas');
      if (!canvasElement) return;

      this.canvas = new fabric.Canvas('designer-canvas', {
        width: 500,
        height: 500,
        backgroundColor: '#ffffff'
      });
    }

    async loadVariantData(variantId) {
      try {
        console.log('🔍 Loading variant data for ID:', variantId);
        console.log('📋 Available variants:', this.config.variants);

        const variant = this.config.variants?.find(v => v.id == variantId);
        if (!variant) {
          console.warn('⚠️ Variant not found, using empty canvas');
          return;
        }

        console.log('✅ Found variant:', variant);

        let designData = null;

        // Try multiple possible field names for design data
        const possibleFields = ['_design_tool_layer', 'designer_data', 'design_data'];
        let rawData = null;

        for (const field of possibleFields) {
          if (variant[field]) {
            rawData = variant[field];
            console.log(`📝 Found design data in field: ${field}`);
            break;
          }
        }

        if (rawData) {
          try {
            designData = typeof rawData === 'string' ? JSON.parse(rawData) : rawData;
          } catch (error) {
            console.error('Failed to parse design data:', error);
            designData = null;
          }
        }

        console.log('🎨 Design data:', designData);

        // If no design data, show empty canvas
        if (!designData) {
          console.warn('⚠️ No design data found for variant, showing empty canvas');
          return;
        }

        // Load background image
        if (designData.baseImage) {
          console.log('📸 Loading background image:', designData.baseImage);
          this.backgroundImage = await this.loadImage(designData.baseImage);
          this.backgroundImage.selectable = false;
          this.backgroundImage.evented = false;
          this.canvas.add(this.backgroundImage);
          this.backgroundImage.center();
          console.log('✅ Background image loaded');
        }

        // Load mask
        if (designData && designData.alphaMask) {
          console.log('🎭 Loading mask image:', designData.alphaMask);
          this.maskImage = await this.loadImage(designData.alphaMask);
          this.maskImage.selectable = false;
          this.maskImage.evented = false;
          this.canvas.add(this.maskImage);
          this.maskImage.center();
          console.log('✅ Mask image loaded');
        }

        this.ensureProperLayering();
        this.canvas.renderAll();

        console.log('🎯 Variant loading complete');

      } catch (error) {
        console.error('Error loading variant:', error);
      }
    }

    loadImage(url) {
      return new Promise((resolve, reject) => {
        fabric.Image.fromURL(url, resolve, { crossOrigin: 'anonymous' });
      });
    }

    setupEventListeners() {
      document.getElementById('close-designer')?.addEventListener('click', () => this.closeLightbox());
      document.getElementById('cancel-btn')?.addEventListener('click', () => this.closeLightbox());
      document.getElementById('apply-btn')?.addEventListener('click', () => this.applyDesign());

      const uploadBtn = document.getElementById('upload-btn');
      const fileInput = document.getElementById('file-input');

      uploadBtn?.addEventListener('click', () => fileInput.click());
      fileInput?.addEventListener('change', (e) => this.handleImageUpload(e));
    }

    async handleImageUpload(event) {
      const files = event.target.files;
      if (!files || files.length === 0) return;

      for (const file of files) {
        if (!file.type.startsWith('image/')) continue;

        try {
          const base64 = await this.fileToBase64(file);

          fabric.Image.fromURL(base64, (img) => {
            const scale = Math.min(300 / img.width, 300 / img.height);
            img.scale(scale);
            img.set({
              left: this.canvas.width / 2,
              top: this.canvas.height / 2,
              originX: 'center',
              originY: 'center'
            });

            this.canvas.add(img);
            this.canvas.setActiveObject(img);
            this.ensureProperLayering();
            this.canvas.renderAll();
          });
        } catch (error) {
          console.error('Error uploading image:', error);
        }
      }

      event.target.value = '';
    }

    fileToBase64(file) {
      return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(reader.result);
        reader.onerror = reject;
        reader.readAsDataURL(file);
      });
    }

    ensureProperLayering() {
      if (!this.canvas) return;

      // Background at bottom
      if (this.backgroundImage) {
        this.canvas.sendToBack(this.backgroundImage);
      }

      // Mask at top
      if (this.maskImage) {
        this.canvas.bringToFront(this.maskImage);
      }
    }

    closeLightbox() {
      console.log('🔓 Closing lightbox...');

      const lightbox = document.getElementById('designer-lightbox');
      if (lightbox) {
        lightbox.style.opacity = '0';
        setTimeout(() => {
          lightbox.style.display = 'none';
        }, 300);
      }

      // Always restore scrolling
      if (document.body) {
        document.body.style.overflow = '';
        console.log('🔓 Background scrolling restored');
      }

      console.log('✅ Lightbox closed');
    }

    applyDesign() {
      // Simple apply - just close for now
      this.closeLightbox();
    }
  }

  // Add immediate console log to verify script is loading
  console.log('🚀 SWPD Working Script Loading...');

  // Initialize when DOM is ready
  document.addEventListener('DOMContentLoaded', () => {
    console.log('🔧 DOM Ready - Initializing SWPD Designer');
    console.log('📋 swpdDesignerConfig available:', typeof swpdDesignerConfig !== 'undefined');

    if (typeof swpdDesignerConfig !== 'undefined') {
      console.log('✅ Creating designer instance');
      window.customDesigner = new EnhancedProductDesigner(swpdDesignerConfig);
      console.log('✅ Designer instance created:', !!window.customDesigner);
    } else {
      console.warn('❌ swpdDesignerConfig not found');
    }

    // Setup customize button
    console.log('🔘 Setting up customize button listener');
    console.log('📍 document.body exists:', !!document.body);

    if (document.body) {
      document.body.addEventListener('click', function(e) {
        console.log('👆 Body click detected, target ID:', e.target?.id);
        console.log('👆 Target element:', e.target);
        console.log('👆 Target classList:', e.target?.classList?.toString());
        console.log('👆 Target tagName:', e.target?.tagName);

        // Check if target or parent has the customize button ID
        let targetElement = e.target;
        let foundButton = false;

        // Check up to 3 levels of parents for the button
        for (let i = 0; i < 3 && targetElement; i++) {
          console.log(`👆 Level ${i} element:`, targetElement, 'ID:', targetElement.id);

          if (targetElement.id === 'swpd-customize-design-button' ||
              targetElement.classList?.contains('swpd-designer-button')) {
            foundButton = true;
            break;
          }
          targetElement = targetElement.parentElement;
        }

        if (foundButton) {
          console.log('🎯 Customize button clicked!');
          e.preventDefault();

          if (window.customDesigner) {
            console.log('✅ Designer found, opening lightbox');
            const variantInput = document.querySelector('input[name="variation_id"]');
            const variantId = variantInput?.value || swpdDesignerConfig?.product_id;
            console.log('🔍 Using variant ID:', variantId);
            window.customDesigner.openLightbox(variantId);
          } else {
            console.error('❌ Designer not found');
            alert('Designer not initialized. Please refresh and try again.');
          }
        }
      });
      console.log('✅ Click listener attached to body');
    } else {
      console.error('❌ document.body not found');
    }

    // Also check if customize button exists and add direct listener
    const customizeBtn = document.getElementById('swpd-customize-design-button');
    console.log('🔘 Customize button found:', !!customizeBtn);
    if (customizeBtn) {
      console.log('📍 Button element:', customizeBtn);

      // Add direct click listener as backup
      customizeBtn.addEventListener('click', function(e) {
        console.log('🎯 DIRECT: Customize button clicked!');
        e.preventDefault();
        e.stopPropagation();

        if (window.customDesigner) {
          console.log('✅ DIRECT: Designer found, opening lightbox');
          const variantInput = document.querySelector('input[name="variation_id"]');
          const variantId = variantInput?.value || swpdDesignerConfig?.product_id;
          console.log('🔍 DIRECT: Using variant ID:', variantId);
          window.customDesigner.openLightbox(variantId);
        } else {
          console.error('❌ DIRECT: Designer not found');
          alert('Designer not initialized. Please refresh and try again.');
        }
      });

      console.log('✅ Direct click listener added to button');
    }
  });

  // Make available globally
  window.EnhancedProductDesigner = EnhancedProductDesigner;

} // End of duplicate prevention