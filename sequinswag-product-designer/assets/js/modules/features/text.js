/**
 * Text editing functionality
 * Part of Enhanced Product Designer
 * 
 * @module textMethods
 */

export const textMethods = {


  showTextEditor() {
    const modal = document.getElementById('text-editor-modal');
    if (modal) {
      // Add mobile class if on mobile device
      if (window.innerWidth <= 768) {
        modal.classList.add('mobile-modal');
      }

      modal.style.display = 'flex';

      // Animate in
      setTimeout(() => {
        modal.classList.add('active');
        const input = document.getElementById('text-input');
        if (input) {
          input.focus();

          // Clear any existing text
          input.value = '';

          // Reset text size value display
          const sizeSlider = document.getElementById('text-size');
          const sizeValue = document.getElementById('text-size-value');
          if (sizeSlider && sizeValue) {
            sizeValue.textContent = sizeSlider.value + 'px';
          }

          // Initialize preview state (this will disable the button initially)
          this.updateTextPreview();

          // Add event listeners if they don't exist
          this.setupTextInputListeners();
        }
      }, 10);

      // Ensure modal appears within lightbox
      const lightbox = document.getElementById('designer-lightbox');
      if (lightbox && modal.parentElement !== lightbox.querySelector('.designer-modal')) {
        // Move modal inside lightbox if it's not already there
        lightbox.querySelector('.designer-modal').appendChild(modal);
      }
    }
  }

  // NEW: Setup text input listeners
  setupTextInputListeners() {
    const textInput = document.getElementById('text-input');
    if (textInput && !textInput.hasAttribute('data-listeners-added')) {
      console.log('Setting up text input listeners');

      textInput.addEventListener('input', () => {
        console.log('Text input changed:', textInput.value);
        this.updateTextPreview();
      });

      textInput.addEventListener('paste', () => {
        setTimeout(() => this.updateTextPreview(), 10);
      });

      // Mark as having listeners to avoid duplicates
      textInput.setAttribute('data-listeners-added', 'true');
    }
  }

  hideTextEditor() {
    const modal = document.getElementById('text-editor-modal');
    const input = document.getElementById('text-input');

    if (modal) {
      modal.classList.remove('active');
      modal.classList.remove('mobile-modal');

      // Animate out and then hide
      setTimeout(() => {
        modal.style.display = 'none';
      }, 300); // Wait for animation to complete
    }

    // Clear form values
    if (input) input.value = '';
    const colorInput = document.getElementById('text-color');
    const sizeInput = document.getElementById('text-size');
    const fontSelect = document.getElementById('font-select');

    if (colorInput) colorInput.value = '#000000';
    if (sizeInput) sizeInput.value = '30';
    if (fontSelect) fontSelect.value = 'Arial';

    // Update size display
    const sizeValue = document.getElementById('text-size-value');
    if (sizeValue) sizeValue.textContent = '30px';
  }

  addText() {
    console.log('=== ADD TEXT CALLED ===');

    if (!this.canvas) {
      console.error('Canvas not available');
      this.showNotification('Designer not ready. Please try again.', 'error');
      return;
    }

    const text = document.getElementById('text-input')?.value;
    console.log('Text to add:', text);

    if (!text || text.trim().length === 0) {
      this.showNotification('Please enter some text', 'error');
      return;
    }

    const font = document.getElementById('font-select')?.value || 'Arial';
    const color = document.getElementById('text-color')?.value || '#000000';
    const size = parseInt(document.getElementById('text-size')?.value || '30');

    console.log('Text properties:', { text: text.trim(), font, color, size });

    const textPathType = document.querySelector('.text-path-type')?.value || 'none';
    if (textPathType && textPathType !== 'none') {
        this.showNotification('Curved text is a planned feature. Adding regular text for now.');
    }

    try {
      const fabricText = new fabric.Text(text.trim(), {
        fontFamily: font,
        fontSize: size,
        fill: color,
        left: this.clipBounds ? this.clipBounds.left + this.clipBounds.width / 2 : this.canvas.width / 2,
        top: this.clipBounds ? this.clipBounds.top + this.clipBounds.height / 2 : this.canvas.height / 2,
        originX: 'center',
        originY: 'center',
        angle: 0
      });

      console.log('Fabric text object created:', fabricText);

      this.canvas.add(fabricText);
      this.canvas.setActiveObject(fabricText);
      this.ensureProperLayering();
      this.canvas.renderAll();

      console.log('Text added to canvas successfully');

      this.hideTextEditor();
      this.showNotification('Text added successfully!');
      this.analytics.track('add_text', { length: text.length, font, size });

    } catch (error) {
      console.error('Error creating text:', error);
      this.showNotification('Error adding text. Please try again.', 'error');
    }
  }

  // Setup product variant selector
  setupProductVariantSelector() {
    const variants = this.config.variants || [];

    if (variants.length === 0) {
      console.log('No variants available for selector');
      return;
    }

    const selector = document.createElement('div');
    selector.className = 'product-variant-selector';
    selector.innerHTML = `
      <label>Preview on:</label>
      <select class="variant-preview-select">
        ${variants.map(v => `
          <option value="${v.id}" ${v.id == this.currentVariantId ? 'selected' : ''}>
            ${v.title}
          </option>
        `).join('')}
      </select>
    `;

    const header = document.querySelector('.designer-header');
    if (header) {
      const closeBtn = header.querySelector('.designer-close');
      if (closeBtn) {
        // Only insert if close button exists and is a child of header
        try {
          header.insertBefore(selector, closeBtn);
        } catch (error) {
          console.warn('Could not insert variant selector before close button, appending instead:', error);
          header.appendChild(selector);
        }
      } else {
        // If no close button found, just append to header
        header.appendChild(selector);
      }
    } else {
      console.warn('Designer header not found, cannot add variant selector');
      return;
    }

    const selectElement = selector.querySelector('.variant-preview-select');
    if (selectElement) {
      selectElement.addEventListener('change', (e) => {
        const variantId = e.target.value;
        this.saveSessionDesign();
        this.loadVariantData(variantId);
        this.showNotification('Switching product preview...');
        this.analytics.track('switch_variant', { variantId });
      });
    }
  }

  // FIXED: Load quantity and pricing info
  loadQuantityInfo() {
    console.log('=== LOADING QUANTITY AND PRICING INFO ===');

    // Get quantity from the form
    const quantityInput = document.querySelector('input[name="quantity"]');
    let quantity = 1;
    if (quantityInput && !isNaN(parseInt(quantityInput.value))) {
      quantity = parseInt(quantityInput.value);
    }
    console.log('Quantity found:', quantity);

    // Get price from the product page - multiple selectors to cover different themes
    let unitPrice = 0;
    let priceElement = null;

    // Try different price selectors
    const priceSelectors = [
      '.price ins .woocommerce-Price-amount bdi',
      '.price > .woocommerce-Price-amount bdi',
      '.price .amount bdi',
      '.summary .price bdi',
      '.product-info .price bdi',
      '.price-wrapper .price bdi',
      '.woocommerce-Price-amount bdi'
    ];

    for (const selector of priceSelectors) {
      priceElement = document.querySelector(selector);
      if (priceElement) {
        console.log('Price element found with selector:', selector);
        console.log('Price element HTML:', priceElement.innerHTML);
        break;
      }
    }

    if (priceElement) {
      // Clone the element to manipulate without affecting the DOM
      const clonedElement = priceElement.cloneNode(true);

      // Remove currency symbol if it's a separate element
      const currencySymbol = clonedElement.querySelector('.woocommerce-Price-currencySymbol');
      if (currencySymbol) {
        currencySymbol.remove();
      }

      // Get the text content and extract the number
      const priceText = clonedElement.textContent.trim();
      console.log('Extracted price text:', priceText);

      // Remove all non-numeric characters except dots and commas
      const cleanedPrice = priceText.replace(/[^\d.,]/g, '');
      console.log('Cleaned price:', cleanedPrice);

      // Handle different decimal separators (some locales use comma)
      const normalizedPrice = cleanedPrice.replace(',', '.');
      unitPrice = parseFloat(normalizedPrice) || 0;

      console.log('Final unit price:', unitPrice);
    } else {
      console.warn('No price element found on page');
    }

    // Fallback: try to get price from product meta
    if (unitPrice === 0) {
      const productMeta = document.querySelector('meta[property="product:price:amount"]');
      if (productMeta) {
        unitPrice = parseFloat(productMeta.getAttribute('content')) || 0;
        console.log('Price from meta tag:', unitPrice);
      }
    }

    this.quantityInfo = {
      quantity: quantity,
      unitPrice: unitPrice
    };

    console.log('Final quantity info:', this.quantityInfo);
    this.updatePricingDisplay();
  }

  // FIXED: Update pricing display
  updatePricingDisplay() {
    if (!this.quantityInfo) return;

    let pricingDisplay = document.querySelector('.designer-pricing-info');
    if (!pricingDisplay) {
      pricingDisplay = document.createElement('div');
      pricingDisplay.className = 'designer-pricing-info';
      const footer = document.querySelector('.designer-footer');
      if (footer) {
        footer.insertBefore(pricingDisplay, footer.firstChild);
      }
    }

    // Get the currency symbol
    const currencySymbol = document.querySelector('.woocommerce-Price-currencySymbol')?.textContent || '$';

    // Format price with proper decimal places
    const formatPrice = (price) => {
      return price.toFixed(2).replace(/\.00$/, '');
    };

    pricingDisplay.innerHTML = `
      <div class="pricing-details">
        <span class="quantity-info">${this.quantityInfo.quantity} ${this.quantityInfo.quantity === 1 ? 'unit' : 'units'}</span>
        <span class="price-info">${currencySymbol}${formatPrice(this.quantityInfo.unitPrice * this.quantityInfo.quantity)} total</span>
        ${this.quantityInfo.quantity > 1 ? `<span class="unit-price">(${currencySymbol}${formatPrice(this.quantityInfo.unitPrice)}/unit)</span>` : ''}
      </div>
    `;
  }

  // Check image resolution
  checkImageResolution(imgElement, fabricImg) {
    const DPI_THRESHOLD = 72;
    const PIXEL_PER_INCH = 72;

    const productWidthPx = this.clipBounds ? this.clipBounds.width : this.canvas.width;
    const productHeightPx = this.clipBounds ? this.clipBounds.height : this.canvas.height;

    const currentImgScaleX = fabricImg.scaleX || 1;
    const currentImgScaleY = fabricImg.scaleY || 1;

    const effectiveWidthPx = fabricImg.width * currentImgScaleX;
    const effectiveHeightPx = fabricImg.height * currentImgScaleY;

    const approximatePrintWidthInches = effectiveWidthPx / PIXEL_PER_INCH;
    const approximatePrintHeightInches = effectiveHeightPx / PIXEL_PER_INCH;

    const actualDPI_X = imgElement.naturalWidth / approximatePrintWidthInches;
    const actualDPI_Y = imgElement.naturalHeight / approximatePrintHeightInches;
    const effectiveDPI = Math.min(actualDPI_X, actualDPI_Y);

    if (effectiveDPI < DPI_THRESHOLD && effectiveWidthPx > 0 && effectiveHeightPx > 0) {
      const warning = document.createElement('div');
      warning.className = 'low-res-warning';
      warning.innerHTML = `
        <div class="warning-content">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="#ff9800">
            <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
          </svg>
          <span>Low resolution image detected (${Math.round(effectiveDPI)} DPI). May appear blurry when printed.</span>
        </div>
      `;

      const canvas = document.querySelector('.canvas-container');
      if (canvas && !canvas.querySelector('.low-res-warning')) {
        canvas.appendChild(warning);

        setTimeout(() => {
          warning.remove();
        }, 5000);
      }

      fabricImg.set('strokeWidth', 3);
      fabricImg.set('stroke', '#ff9800');
    } else {
      fabricImg.set('strokeWidth', 0);
      fabricImg.set('stroke', '');
      const existingWarning = document.querySelector('.low-res-warning');
      if(existingWarning) existingWarning.remove();
    }
  }

  // Group/Ungroup functionality
  groupSelectedObjects() {
    const activeObjects = this.canvas.getActiveObjects().filter(obj => obj.selectable !== false);
    if (activeObjects.length < 2) return;

    const group = new fabric.Group(activeObjects, {
      originX: 'center',
      originY: 'center'
    });

    activeObjects.forEach(obj => {
      this.canvas.remove(obj);
    });

    this.canvas.add(group);
    this.canvas.setActiveObject(group);
    this.canvas.renderAll();
    this.saveHistory();
    this.analytics.track('group_objects', { count: activeObjects.length });
  }

  ungroupSelectedObject() {
    const activeObject = this.canvas.getActiveObject();
    if (!activeObject || activeObject.type !== 'group') return;

    const items = activeObject._objects;
    this.canvas.remove(activeObject);

    items.forEach(item => {
      this.canvas.add(item);
      item.setCoords();
    });

    this.canvas.renderAll();
    this.saveHistory();
    this.analytics.track('ungroup_objects', { count: items.length });
  }

  // Enhanced keyboard shortcuts
  setupKeyboardShortcuts() {
    document.addEventListener('keydown', (e) => {
      if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT') return;

      // Existing shortcuts
      if ((e.ctrlKey || e.metaKey) && e.key === 'z' && !e.shiftKey) {
        e.preventDefault();
        this.undo();
      }
      if ((e.ctrlKey || e.metaKey) && (e.key === 'y' || (e.key === 'z' && e.shiftKey))) {
        e.preventDefault();
        this.redo();
      }
      if (e.key === 'Delete' || e.key === 'Backspace') {
        const activeObject = this.canvas?.getActiveObject();
        if (activeObject && activeObject !== this.backgroundImage && activeObject !== this.maskImage && activeObject !== this.unclippedMaskImage) {
          this.canvas.remove(activeObject);
          this.ensureProperLayering();
          this.saveHistory();
          this.showNotification('Item deleted');
        }
      }

      // New shortcuts
      if ((e.ctrlKey || e.metaKey) && e.key === 'g') {
        e.preventDefault();
        this.groupSelectedObjects();
      }
      if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'G') {
        e.preventDefault();
        this.ungroupSelectedObject();
      }
      if ((e.ctrlKey || e.metaKey) && e.key === 'a') {
        e.preventDefault();
        this.selectAll();
      }
      if ((e.ctrlKey || e.metaKey) && e.key === 'd') {
        e.preventDefault();
        this.duplicateSelection();
      }

      // Arrow key nudging
      const activeObject = this.canvas?.getActiveObject();
      if (activeObject && activeObject.selectable) {
        const nudgeAmount = e.shiftKey ? 10 : 1;

        switch(e.key) {
          case 'ArrowLeft':
            e.preventDefault();
            activeObject.left -= nudgeAmount;
            break;
          case 'ArrowRight':
            e.preventDefault();
            activeObject.left += nudgeAmount;
            break;
          case 'ArrowUp':
            e.preventDefault();
            activeObject.top -= nudgeAmount;
            break;
          case 'ArrowDown':
            e.preventDefault();
            activeObject.top += nudgeAmount;
            break;
        }

        if (['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown'].includes(e.key)) {
          activeObject.setCoords();
          this.canvas.renderAll();
          this.saveHistory();
        }
      }
    });
  }

  selectAll() {
    const objects = this.canvas.getObjects().filter(obj => obj.selectable !== false);
    if (objects.length > 0) {
      this.canvas.discardActiveObject();
      if (objects.length === 1) {
        this.canvas.setActiveObject(objects[0]);
      } else {
        const selection = new fabric.ActiveSelection(objects, { canvas: this.canvas });
        this.canvas.setActiveObject(selection);
      }
      this.canvas.renderAll();
      this.analytics.track('select_all', { count: objects.length });
    }
  }

  duplicateSelection() {
    const activeObject = this.canvas.getActiveObject();
    if (!activeObject || activeObject === this.backgroundImage || activeObject === this.maskImage || activeObject === this.unclippedMaskImage) return;

    activeObject.clone((cloned) => {
      cloned.set({
        left: cloned.left + 20,
        top: cloned.top + 20
      });

      if (cloned.type === 'activeSelection') {
        cloned.canvas = this.canvas;
        cloned.forEachObject((obj) => {
          this.canvas.add(obj);
        });
        cloned.setCoords();
      } else {
        this.canvas.add(cloned);
      }

      this.canvas.setActiveObject(cloned);
      this.canvas.renderAll();
      this.saveHistory();
      this.analytics.track('duplicate_object', { type: cloned.type });
    });
  }

  // Setup event listeners
  setupEventListeners() {
    const setupListeners = () => {
      const closeBtn = document.querySelector('.designer-close');
      if (!closeBtn) {
        setTimeout(setupListeners, 100);
        return;
      }

      closeBtn.addEventListener('click', () => this.closeLightbox());
      document.querySelector('.cancel-design')?.addEventListener('click', () => this.closeLightbox());
      document.querySelector('.apply-design')?.addEventListener('click', () => this.applyDesign());
      document.querySelector('.add-to-cart-design')?.addEventListener('click', () => this.addToCart());

      document.getElementById('undo-btn')?.addEventListener('click', () => this.undo());
      document.getElementById('redo-btn')?.addEventListener('click', () => this.redo());

      document.querySelectorAll('.image-upload-input').forEach(input => {
        input.addEventListener('change', (e) => this.handleImageUpload(e));
      });

      // Simplified upload button setup
      document.querySelectorAll('.image-upload-input').forEach((input, index) => {
        console.log(`Setting up file input ${index + 1} with ID:`, input.id);
        input.addEventListener('change', (e) => {
          console.log(`File input ${index + 1} changed, files:`, e.target.files.length);
          this.handleImageUpload(e);
        });
      });

      // Additional setup for main file input
      const mainFileInput = document.getElementById('main-file-input');
      if (mainFileInput) {
        console.log('Main file input found and set up');
        mainFileInput.addEventListener('change', (e) => {
          console.log('Main file input changed, files:', e.target.files.length);
          this.handleImageUpload(e);
        });
      } else {
        console.warn('Main file input not found');
      }

      // Debug upload button
      const uploadBtn = document.querySelector('.upload-image-btn');
      if (uploadBtn) {
        console.log('Upload button found');
        uploadBtn.addEventListener('click', function() {
          console.log('Upload button clicked');
        });
      } else {
        console.warn('Upload button not found');
      }

      document.querySelector('.add-text-btn')?.addEventListener('click', () => this.showTextEditor());
      document.getElementById('add-text-confirm')?.addEventListener('click', () => this.addText());
      document.getElementById('cancel-text')?.addEventListener('click', () => this.hideTextEditor());
      document.querySelector('.modal-close')?.addEventListener('click', () => this.hideTextEditor());

      document.getElementById('text-size')?.addEventListener('input', (e) => {
        const sizeValue = document.getElementById('text-size-value');
        if (sizeValue) sizeValue.textContent = e.target.value + 'px';
      });

      // Add event listener for text input to enable/disable the add button
      document.getElementById('text-input')?.addEventListener('input', () => {
        this.updateTextPreview();
      });

      // Also listen for paste events
      document.getElementById('text-input')?.addEventListener('paste', () => {
        setTimeout(() => this.updateTextPreview(), 10);
      });

      document.getElementById('crop-btn')?.addEventListener('click', () => this.startCrop());
      document.querySelector('.apply-crop-btn')?.addEventListener('click', () => this.applyCrop());
      document.querySelector('.cancel-crop-btn')?.addEventListener('click', () => this.cancelCrop());

      document.querySelector('.save-design-btn')?.addEventListener('click', () => this.saveDesign());
      document.querySelector('.load-design-btn')?.addEventListener('click', () => this.showSavedDesigns());

      // Add help button listener
      document.querySelector('.help-btn')?.addEventListener('click', () => this.showHelp());

      const setupCanvasListeners = () => {
        if (this.canvas) {
          this.canvas.on('selection:created', (e) => {
            this.showProperties(e.selected[0]);
            const cropBtn = document.getElementById('crop-btn');
            const editTools = document.getElementById('edit-tools');
            if (cropBtn && e.selected[0].type === 'image' && e.selected[0].selectable !== false) {
              cropBtn.style.display = 'flex';
              if (editTools) editTools.style.display = 'block';
            }
          });
          this.canvas.on('selection:updated', (e) => {
            this.showProperties(e.selected[0]);
            const cropBtn = document.getElementById('crop-btn');
            const editTools = document.getElementById('edit-tools');
            if (cropBtn) {
              cropBtn.style.display = (e.selected[0].type === 'image' && e.selected[0].selectable !== false) ? 'flex' : 'none';
              if (editTools) editTools.style.display = (e.selected[0].type === 'image' && e.selected[0].selectable !== false) ? 'block' : 'none';
            }
          });
          this.canvas.on('selection:cleared', () => {
            this.hideProperties();
            const cropBtn = document.getElementById('crop-btn');
            const editTools = document.getElementById('edit-tools');
            if (cropBtn) cropBtn.style.display = 'none';
            if (editTools) editTools.style.display = 'none';
            if (this.isCropping) {
              this.cancelCrop();
            }
            const propsPanel = document.querySelector('.properties-panel');
            if (propsPanel && window.innerWidth <= 768) {
              propsPanel.classList.remove('mobile-visible');
            }
          });
          this.canvas.on('object:scaling', (e) => {
            if (e.target.type === 'image' && e.target.selectable !== false) {
              this.checkImageResolution(e.target._element, e.target);
            }
          });
          this.canvas.on('object:moved', (e) => {
            if (e.target.type === 'image' && e.target.selectable !== false) {
              this.checkImageResolution(e.target._element, e.target);
            }
          });
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
        } else {
          setTimeout(setupCanvasListeners, 100);
        }
      };

      setupCanvasListeners();

      document.getElementById('designer-lightbox')?.addEventListener('click', (e) => {
        if (e.target.id === 'designer-lightbox') {
          this.closeLightbox();
        }
      });
    };
    setupListeners();
  }

  // FIXED: Handle image upload with enhanced debugging
  async handleImageUpload(event) {
    console.log('=== IMAGE UPLOAD HANDLER CALLED ===');
    console.log('Event:', event);
    console.log('Canvas available:', !!this.canvas);

    if (!this.canvas) {
      console.error('Canvas not available');
      this.showNotification('Designer not ready. Please wait a moment and try again.', 'error');
      return;
    }

    const files = event.target.files;
    console.log('Files selected:', files.length);

    if (!files || files.length === 0) {
      console.log('No files selected');
      return;
    }

    console.log('=== IMAGE UPLOAD DEBUG ===');
    console.log('Number of files:', files.length);
    console.log('WordPress AJAX URL:', this.wpAjaxConfig.url);
    console.log('Nonce:', this.wpAjaxConfig.nonce);
    console.log('Canvas state:', {
      exists: !!this.canvas,
      width: this.canvas?.width,
      height: this.canvas?.height,
      objects: this.canvas?.getObjects()?.length
    });

    for (const file of files) {
      try {
        console.log('\n--- Processing file ---');
        console.log('File name:', file.name);
        console.log('File type:', file.type);
        console.log('File size:', file.size, 'bytes');

        // Validate file type
        if (!file.type.match(/image\/(jpeg|jpg|png|gif|webp)/i)) {
          this.showNotification('Please upload a valid image file (JPEG, PNG, GIF, or WebP)', 'error');
          continue;
        }

        // Check file size (5MB limit)
        const maxSize = 5 * 1024 * 1024; // 5MB in bytes
        if (file.size > maxSize) {
          this.showNotification('Image file size must be less than 5MB', 'error');
          continue;
        }

        // Show processing notification
        this.showNotification('Processing image...', 'info');

        // Convert to base64 first
        console.log('Converting to base64...');
        const base64 = await this.fileToBase64(file);
        console.log('Base64 length:', base64.length);

        // Create fabric image immediately for preview
        const img = new Image();
        img.onload = async () => {
          console.log('Image loaded, dimensions:', img.width, 'x', img.height);

          fabric.Image.fromURL(base64, async (fabricImg) => {
            console.log('Fabric image created successfully');

            // Position image in the design area
            if (this.clipBounds) {
              console.log('Using clip bounds for positioning:', this.clipBounds);
              const scale = Math.min(
                (this.clipBounds.width * 0.8) / fabricImg.width,
                (this.clipBounds.height * 0.8) / fabricImg.height,
                1
              );
              fabricImg.scale(scale);
              fabricImg.set({
                left: this.clipBounds.left + this.clipBounds.width / 2,
                top: this.clipBounds.top + this.clipBounds.height / 2,
                originX: 'center',
                originY: 'center'
              });
            } else {
              console.log('No clip bounds, centering in canvas');
              // Center in canvas if no clip bounds
              fabricImg.set({
                left: this.canvas.width / 2,
                top: this.canvas.height / 2,
                originX: 'center',
                originY: 'center'
              });
              fabricImg.scaleToWidth(Math.min(200, this.canvas.width * 0.5));
            }

            console.log('Adding image to canvas...');
            this.canvas.add(fabricImg);
            this.canvas.setActiveObject(fabricImg);
            this.ensureProperLayering();
            this.canvas.renderAll();
            console.log('Image added to canvas successfully');

            // Try to upload to WordPress/Cloudinary in background
            console.log('\n--- Attempting upload ---');
            try {
              const wpResult = await this.uploadToWordPress(base64, file.name);
              console.log('Upload result:', wpResult);

              if (wpResult && wpResult.url && !wpResult.error) {
                console.log('✓ Upload successful!');
                console.log('Uploaded URL:', wpResult.url);

                // Update image source to use uploaded URL
                fabricImg.setSrc(wpResult.url, () => {
                  this.canvas.renderAll();
                }, { crossOrigin: 'anonymous' });
              } else {
                console.log('✗ Upload failed, keeping base64');
                console.log('Error details:', wpResult);
              }
            } catch (error) {
              console.error('Upload error:', error);
              console.log('Continuing with base64 image...');
            }

            this.saveHistory();
            this.showNotification('Image added to design!');
            this.analytics.track('add_image', {
              size: file.size,
              type: file.type,
              dimensions: `${img.width}x${img.height}`
            });

            // Check resolution after adding
            this.checkImageResolution(img, fabricImg);
          }, { crossOrigin: 'anonymous' });
        };

        img.onerror = (error) => {
          console.error('Image failed to load:', error);
          this.showNotification('Failed to load image', 'error');
        };

        img.src = base64;
      } catch (error) {
        console.error('Error handling image:', error);
        console.error('Stack trace:', error.stack);
        this.showNotification('Failed to process image', 'error');
      }
    }

    console.log('=== END IMAGE UPLOAD DEBUG ===');

    // Reset the file input so the same file can be selected again
    event.target.value = '';
  }

  // Helper function to convert file to base64
  fileToBase64(file) {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.onload = (e) => resolve(e.target.result);
      reader.onerror = reject;
      reader.readAsDataURL(file);
    });
  }

  // Add showHelp method
  showHelp() {
    const modal = document.createElement('div');
    modal.className = 'help-modal';
    modal.innerHTML = `
      <div class="help-content">
        <h3>Keyboard Shortcuts</h3>
        <div class="help-sections">
          <div class="help-section">
            <div class="shortcuts-list">
              <div class="shortcut-item">
                <span class="shortcut-keys">Ctrl/Cmd + Z</span>
                <span class="shortcut-desc">Undo</span>
              </div>
              <div class="shortcut-item">
                <span class="shortcut-keys">Ctrl/Cmd + Y</span>
                <span class="shortcut-desc">Redo</span>
              </div>
              <div class="shortcut-item">
                <span class="shortcut-keys">Delete</span>
                <span class="shortcut-desc">Delete selected</span>
              </div>
              <div class="shortcut-item">
                <span class="shortcut-keys">Ctrl/Cmd + A</span>
                <span class="shortcut-desc">Select all</span>
              </div>
              <div class="shortcut-item">
                <span class="shortcut-keys">Ctrl/Cmd + D</span>
                <span class="shortcut-desc">Duplicate</span>
              </div>
              <div class="shortcut-item">
                <span class="shortcut-keys">Ctrl/Cmd + G</span>
                <span class="shortcut-desc">Group</span>
              </div>
              <div class="shortcut-item">
                <span class="shortcut-keys">Arrow Keys</span>
                <span class="shortcut-desc">Nudge 1px</span>
              </div>
              <div class="shortcut-item">
                <span class="shortcut-keys">Shift + Arrow</span>
                <span class="shortcut-desc">Nudge 10px</span>
              </div>
            </div>
          </div>
          <div class="help-section">
            <h4>Tips:</h4>
            <ul>
              <li>Click on the canvas background to deselect all items</li>
              <li>Hold Shift while resizing to maintain aspect ratio</li>
              <li>Use the layers panel to reorder elements</li>
              <li>Double-click text to edit it inline</li>
              <li>Scroll to zoom in/out when hovering over the canvas</li>
            </ul>
          </div>
        </div>
        <div class="modal-actions">
          <button class="btn btn-primary close-help">Got it!</button>
        </div>
      </div>
    `;

    document.body.appendChild(modal);
    modal.style.display = 'flex';

    modal.querySelector('.close-help').addEventListener('click', () => {
      modal.style.display = 'none';
      setTimeout(() => document.body.removeChild(modal), 300);
    });

    modal.addEventListener('click', (e) => {
      if (e.target === modal) {
        modal.style.display = 'none';
        setTimeout(() => document.body.removeChild(modal), 300);
      }
    });
  }

  /**
   * FIXED: Check for edit mode by reading from sessionStorage.
   * This method is called during initialization.
   */
  checkForEditMode() {
    console.log('=== ENHANCED CHECK FOR EDIT MODE ===');
    const urlParams = new URLSearchParams(window.location.search);
    const editMode = urlParams.get('edit_design');

    if (editMode === '1') {
        const sessionData = sessionStorage.getItem('edit_design_data');
        const sessionVariant = sessionStorage.getItem('edit_design_variant');

        console.log('Session storage check:', {
            hasSessionData: !!sessionData,
            hasSessionVariant: !!sessionVariant
        });

        if (sessionData && sessionVariant) {
            try {
                // The data from sessionStorage is a string, it will be parsed in loadPreservedDesign
                this.preservedCanvasData = sessionData;
                console.log('Edit mode: Design data loaded from session');
            } catch (e) {
                console.error('Failed to handle design data from session:', e);
            }
        }
    } else {
        console.log('Not in edit mode');
    }
  }

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
  }

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
  }

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
  }

  handleResize() {
    if (!this.canvas) return;

    const container = document.querySelector('.canvas-container');
    if (!container) return;

    const isMobile = window.innerWidth <= 768;
    // Better mobile calculation: consider padding/margins
    const padding = isMobile ? 20 : 40;
    const containerWidth = container.offsetWidth - padding;
    const containerHeight = container.offsetHeight - padding;
    let canvasSize = Math.min(containerWidth, containerHeight);

    if (isMobile) {
      canvasSize = Math.max(canvasSize, 280);
    } else {
      canvasSize = Math.max(canvasSize, 500);
      canvasSize = Math.min(canvasSize, 800); // Max size on desktop
    }

    const currentSize = this.canvas.width;
    const scaleFactor = canvasSize / currentSize;

    this.canvas.setDimensions({
      width: canvasSize,
      height: canvasSize
    });

    this.canvas.setZoom(this.canvas.getZoom() * scaleFactor);

    if (this.backgroundImage) {
        // Recenter background and mask based on new size
        this.backgroundImage.center();
        if (this.maskImage) {
            this.maskImage.center();
        }
        this.setupMaskClipping(); // Recalculate clipping bounds
    }

    this.canvas.renderAll();
}

  setupAnimations() {
    const modal = document.querySelector('.designer-modal');
    if (modal) {
      modal.style.transition = 'transform 0.3s ease, opacity 0.3s ease';
    }
  }

  saveHistory() {
    const currentObjects = this.canvas.getObjects().filter(obj => obj.selectable !== false);
    const currentState = JSON.stringify({objects: currentObjects.map(obj => obj.toObject(['selectable', 'evented', 'crossOrigin']))});

    if (this.isRedoing || this.isReordering || this.isLoadingDesign) return;

    if (this.historyStep >= 0 && this.history[this.historyStep] === currentState) {
      return;
    }

    this.history = this.history.slice(0, this.historyStep + 1);
    this.history.push(currentState);
    this.historyStep++;
    if (this.history.length > 50) {
      this.history.shift();
      this.historyStep--;
    }
    this.updateHistoryButtons();
    this.saveSessionDesign();
  }

  saveSessionDesign() {
    if (!this.canvas || !this.currentVariantId) return;
    const allObjects = this.canvas.getObjects();
    const userContent = allObjects.filter(obj => obj.selectable !== false);

    const sessionData = {
      variantId: this.currentVariantId,
      userObjects: userContent.map(obj => {
        const data = obj.toObject(['selectable', 'evented', 'crossOrigin']);
        if (obj.type === 'image' && obj._element && obj._element.src) {
          data.src = obj._element.src;
        }
        return data;
      }),
      timestamp: Date.now()
    };

    try {
      sessionStorage.setItem('designer_session_' + this.currentVariantId, JSON.stringify(sessionData));
    } catch (e) {
      console.warn('Failed to save session, storage might be full or inaccessible:', e);
      this.clearOldSessions();
    }
  }

  clearOldSessions() {
    Object.keys(sessionStorage).forEach(key => {
      if (key.startsWith('designer_session_')) {
        try {
          const data = JSON.parse(sessionStorage.getItem(key));
          // Clear sessions older than 2 hours
          if (Date.now() - data.timestamp > 2 * 60 * 60 * 1000) {
            sessionStorage.removeItem(key);
          }
        } catch (e) {
          sessionStorage.removeItem(key);
        }
      }
    });
  }

  loadSessionDesign() {
    if (!this.currentVariantId) return false;
    const sessionData = sessionStorage.getItem('designer_session_' + this.currentVariantId);
    if (!sessionData) return false;
    try {
      const data = JSON.parse(sessionData);
      if (Date.now() - data.timestamp > 2 * 60 * 60 * 1000) {
        sessionStorage.removeItem('designer_session_' + this.currentVariantId);
        return false;
      }
      if (!data.userObjects || data.userObjects.length === 0) return false;

      this.isLoadingDesign = true;
      this.canvas.discardActiveObject();
      this.canvas.remove(...this.canvas.getObjects().filter(obj => obj.selectable !== false));

      const loadPromises = [];
      data.userObjects.forEach(objData => {
        if (objData.type === 'text' || objData.type === 'i-text') {
          const text = new fabric.Text(objData.text || '', objData);
          this.canvas.add(text);
        } else if (objData.type === 'image' && objData.src) {
          const promise = new Promise((resolve) => {
            fabric.Image.fromURL(objData.src, (img) => {
              if (img) {
                img.set(objData);
                this.canvas.add(img);
              } else {
                console.warn('Failed to load image from session:', objData.src);
              }
              resolve();
            }, { crossOrigin: 'anonymous' });
          });
          loadPromises.push(promise);
        }
      });

      Promise.all(loadPromises).then(() => {
        this.ensureProperLayering();
        this.canvas.renderAll();

        setTimeout(() => {
          this.isLoadingDesign = false;
          this.history = [JSON.stringify({objects: this.canvas.getObjects().filter(obj => obj.selectable !== false).map(obj => obj.toObject(['selectable', 'evented', 'crossOrigin']))})];
          this.historyStep = 0;
          this.updateHistoryButtons();
          this.showNotification('Previous design session restored');
        }, 100);
      }).catch(error => {
        console.error('Error loading session design images:', error);
        this.isLoadingDesign = false;
        this.showNotification('Error restoring design. Some elements might be missing.', 'error');
      });

      return true;
    } catch (e) {
      console.error('Failed to load session design:', e);
      sessionStorage.removeItem('designer_session_' + this.currentVariantId);
      return false;
    }
  }

  undo() {
    if (this.historyStep > 0 && this.canvas) {
      this.historyStep--;
      this.isRedoing = true;
      this.canvas.clear();
      this.addFixedLayers();

      const stateToLoad = JSON.parse(this.history[this.historyStep]);

      this.canvas.loadFromJSON(stateToLoad, () => {
        this.canvas.renderAll();
        this.isRedoing = false;
        this.updateHistoryButtons();
        this.ensureProperLayering();
      }, (o, object) => {
        // Custom revive logic if needed
      });
    }
  }

  redo() {
    if (this.historyStep < this.history.length - 1 && this.canvas) {
      this.historyStep++;
      this.isRedoing = true;
      this.canvas.clear();
      this.addFixedLayers();

      const stateToLoad = JSON.parse(this.history[this.historyStep]);

      this.canvas.loadFromJSON(stateToLoad, () => {
        this.canvas.renderAll();
        this.isRedoing = false;
        this.updateHistoryButtons();
        this.ensureProperLayering();
      }, (o, object) => {
        // Custom revive logic if needed
      });
    }
  }

  updateHistoryButtons() {
    const undoBtn = document.getElementById('undo-btn');
    const redoBtn = document.getElementById('redo-btn');

    const hasUserContentInPrevSteps = this.history.slice(0, this.historyStep).some(stateStr => {
      const state = JSON.parse(stateStr);
      return state.objects.some(obj => obj.selectable !== false);
    });

    if (undoBtn) undoBtn.disabled = !hasUserContentInPrevSteps;
    if (redoBtn) redoBtn.disabled = this.historyStep >= this.history.length - 1;
  }

  addFixedLayers() {
    if (this.backgroundImage && !this.canvas.contains(this.backgroundImage)) {
      this.canvas.add(this.backgroundImage);
      this.backgroundImage.sendToBack();
    }
    if (this.maskImage && !this.canvas.contains(this.maskImage)) {
      this.canvas.add(this.maskImage);
    }
  }

  ensureProperLayering() {
    if (!this.canvas || this.isReordering) return;

    if (!this.backgroundImage) {
        console.warn('Cannot ensure proper layering: missing background image');
        return;
    }

    this.isReordering = true;
    const layers = { background: null, userContent: [], mask: null };

    this.canvas.getObjects().forEach(obj => {
        if (obj === this.backgroundImage) layers.background = obj;
        else if (obj === this.maskImage) layers.mask = obj;
        else if (obj.selectable !== false) layers.userContent.push(obj);
    });

    this.canvas._objects = [];

    // Correct layer order: background, user content, mask
    if (layers.background) this.canvas._objects.push(layers.background);
    layers.userContent.forEach(obj => this.canvas._objects.push(obj));
    if (layers.mask) this.canvas._objects.push(layers.mask);

    this.canvas.requestRenderAll();
    this.isReordering = false;
}

  startCrop() {
    if (!this.canvas) return;
    const activeObject = this.canvas.getActiveObject();
    if (!activeObject || activeObject.type !== 'image' || activeObject.selectable === false) {
      this.showNotification('Please select an image to crop');
      return;
    }
    this.isCropping = true;
    this.croppingObject = activeObject;
    activeObject.visible = false;
    this.canvas.renderAll();

    const cropContainer = document.createElement('div');
    cropContainer.id = 'crop-container';
    const canvasOffset = this.canvas.getElement().getBoundingClientRect();
    cropContainer.style.position = 'absolute';
    cropContainer.style.top = `${canvasOffset.top}px`;
    cropContainer.style.left = `${canvasOffset.left}px`;
    cropContainer.style.width = `${canvasOffset.width}px`;
    cropContainer.style.height = `${canvasOffset.height}px`;
    cropContainer.style.overflow = 'hidden';
    cropContainer.style.zIndex = '999';

    const img = document.createElement('img');
    img.style.width = '100%';
    img.style.height = '100%';
    img.style.objectFit = 'contain';
    img.src = activeObject.getSrc();
    cropContainer.appendChild(img);
    document.body.appendChild(cropContainer);

    this.cropper = new Cropper(img, {
      aspectRatio: NaN,
      viewMode: 1,
      background: false,
      autoCropArea: 0.8,
      responsive: true,
      ready: () => {
        const objRect = activeObject.getBoundingRect(true);
        const scaleFactorX = img.naturalWidth / this.canvas.width;
        const scaleFactorY = img.naturalHeight / this.canvas.height;

        this.cropper.setCropBoxData({
          left: objRect.left * scaleFactorX,
          top: objRect.top * scaleFactorY,
          width: objRect.width * scaleFactorX,
          height: objRect.height * scaleFactorY,
        });
      }
    });

    document.getElementById('crop-btn').style.display = 'none';
    document.getElementById('crop-controls').style.display = 'block';
  }

  applyCrop() {
    if (!this.cropper || !this.croppingObject || !this.canvas) return;

    const croppedDataURL = this.cropper.getCroppedCanvas().toDataURL();

    fabric.Image.fromURL(croppedDataURL, (img) => {
      img.set({
        left: this.croppingObject.left,
        top: this.croppingObject.top,
        angle: this.croppingObject.angle,
        opacity: this.croppingObject.opacity,
        scaleX: this.croppingObject.scaleX,
        scaleY: this.croppingObject.scaleY,
        originX: this.croppingObject.originX,
        originY: this.croppingObject.originY,
        filters: this.croppingObject.filters,
      });
      img.applyFilters();

      this.canvas.remove(this.croppingObject);
      this.canvas.add(img);
      this.canvas.setActiveObject(img);
      this.ensureProperLayering();
      this.canvas.renderAll();
      this.saveHistory();
      this.cleanupCrop();
    }, { crossOrigin: 'anonymous' });
  }

  cancelCrop() {
    if (this.croppingObject && this.canvas) {
      this.croppingObject.visible = true;
      this.canvas.renderAll();
    }
    this.cleanupCrop();
  }

  cleanupCrop() {
    if (this.cropper) {
      this.cropper.destroy();
      this.cropper = null;
    }
    document.getElementById('crop-container')?.remove();
    this.isCropping = false;
    this.croppingObject = null;
    document.getElementById('crop-btn').style.display = 'flex';
    document.getElementById('crop-controls').style.display = 'none';
  }

  openLightbox(variantId, preservedData = null) {
    console.log('=== OPENING LIGHTBOX DEBUG ===');
    console.log('Variant ID:', variantId);

    const lightbox = document.getElementById('designer-lightbox');
    console.log('Lightbox element found:', !!lightbox);

    if (!lightbox) {
      console.error('ERROR: Lightbox element with ID "designer-lightbox" not found!');
      console.log('Available elements with "designer" in ID:',
        Array.from(document.querySelectorAll('[id*="designer"]')).map(el => el.id)
      );

      // Try to create a basic lightbox structure as fallback
      console.log('Attempting to create fallback lightbox...');
      this.createFallbackLightbox();

      // Try again to find the lightbox
      const fallbackLightbox = document.getElementById('designer-lightbox');
      if (!fallbackLightbox) {
        this.showNotification('Designer interface could not be loaded. The plugin may not be properly configured.', 'error');
        console.error('CRITICAL: Could not create fallback lightbox. This suggests the plugin PHP code is not running.');
        return;
      }
    }

    const finalLightbox = document.getElementById('designer-lightbox');
    console.log('Setting lightbox display to flex...');
    finalLightbox.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    setTimeout(() => {
      console.log('Adding active class to lightbox...');
      finalLightbox.classList.add('active');
    }, 10);

    if (!preservedData && this.preservedCanvasData) {
      preservedData = this.preservedCanvasData;
    }

    if (preservedData) {
      this.preservedCanvasData = preservedData;
    }

    const currentVariantId = variantId || swpdDesignerConfig.product_id;
    const variants = swpdDesignerConfig.variants;
    if (variants) this.config.variants = variants;

    console.log('Current variant ID:', currentVariantId);
    console.log('Variants available:', variants ? variants.length : 0);

    // Check if we can load variant data
    if (currentVariantId) {
      console.log('Loading variant data...');
      this.loadVariantData(currentVariantId);
    } else {
      console.log('No variant ID, hiding loading...');
      this.hideLoading();
    }

    this.showLoading();
    this.history = [];
    this.historyStep = -1;
    this.updateHistoryButtons();

    this.loadQuantityInfo();

    console.log('Lightbox should now be visible. Checking final state...');
    setTimeout(() => {
      const lightboxAfter = document.getElementById('designer-lightbox');
      if (lightboxAfter) {
        console.log('Lightbox display style:', lightboxAfter.style.display);
        console.log('Lightbox classes:', lightboxAfter.className);
        console.log('Lightbox visible:', lightboxAfter.offsetWidth > 0 && lightboxAfter.offsetHeight > 0);
      }
    }, 100);
    console.log('=== END OPENING LIGHTBOX DEBUG ===');
  }

  openLightboxWithVariant(variantId, variants) {
    this.openLightbox(variantId);
  }

  closeLightbox() {
    const lightbox = document.getElementById('designer-lightbox');
    if (!lightbox) return;
    this.saveSessionDesign();
    this.stopAutoSave();
    lightbox.classList.remove('active');
    setTimeout(() => {
      lightbox.style.display = 'none';
      document.body.style.overflow = '';
      this.canvas.clear();
      this.backgroundImage = null;
      this.maskImage = null;
      this.unclippedMaskImage = null;
      this.history = [];
      this.historyStep = -1;
      this.preservedCanvasData = null;
    }, 300);
    if (this.isCropping) this.cancelCrop();
  }

  showLoading() {
    const loadingEl = document.querySelector('.designer-loading');
    if (loadingEl) {
      loadingEl.style.display = 'flex';
      console.log('Showing loading spinner...');
    }
    const designerBody = document.querySelector('.designer-body');
    if (designerBody) {
      designerBody.style.opacity = '0';
    }
  }

  hideLoading() {
    const loadingEl = document.querySelector('.designer-loading');
    if (loadingEl) {
      loadingEl.style.display = 'none';
      console.log('Hiding loading spinner...');
    }
    const designerBody = document.querySelector('.designer-body');
    if (designerBody) {
      designerBody.style.opacity = '1';
    }
  }

  loadVariantData(variantId) {
    this.currentVariantId = variantId;
    const variants = swpdDesignerConfig.variants;
    if (!variants || !Array.isArray(variants)) {
      console.error('No variants data available in swpdDesignerConfig.');
      this.hideLoading();
      return;
    }

    const variant = variants.find(v => v && v.id == variantId);
    if (!variant) {
      console.error(`Variant with ID ${variantId} not found in available variants.`);
      this.showNotification('This product variant is not configured for customization.');
      this.hideLoading();
      return;
    }

    let designerData = variant.designer_data;
    if (!designerData) {
      console.error('No designer data found for variant. Please ensure the variant has _design_tool_layer metafield configured.');
      this.showNotification('This product variant is not configured for customization.');
      this.hideLoading();
      return;
    }

    if (typeof designerData === 'string') {
      try {
        designerData = JSON.parse(designerData);
      } catch(e) {
        console.error('Failed to parse designer data string:', e);
        this.showNotification('Error loading designer configuration. Please contact support.');
        this.hideLoading();
        return;
      }
    }

    if (designerData.baseImage && designerData.alphaMask) {
      this.loadDesign(designerData);
    } else {
      console.error('Invalid designer data structure. Expected baseImage and alphaMask properties:', designerData);
      this.showNotification('Designer configuration is incomplete. Please contact support.');
      this.hideLoading();
      return;
    }
  }

  // FIXED: Load design with proper JSON structure
  loadDesign(data) {
    console.log('Loading design with data:', data);

    // Store URLs for later use
    this.backgroundUrl = data.baseImage;
    this.maskUrl = data.alphaMask;

    const loadImages = () => {
        if (!this.canvas) {
            setTimeout(loadImages, 100);
            return;
        }

        this.canvas.clear();
        this.canvas.backgroundColor = '#f5f5f5';
        this.isLoadingDesign = true;

        // Load only base image and alpha mask
        Promise.all([
            this.loadImagePromise(data.baseImage),
            this.loadImagePromise(data.alphaMask)
        ]).then(([bgImg, alphaMaskImg]) => {
            console.log('Images loaded successfully');

            this.backgroundImage = bgImg;
            this.maskImage = alphaMaskImg;

            const canvasWidth = this.canvas.width;
            const canvasHeight = this.canvas.height;

            // Scale and position images
            const fitScale = (img) => {
                const scaleX = canvasWidth / img.width;
                const scaleY = canvasHeight / img.height;
                return Math.min(scaleX, scaleY);
            }

            const bgScale = fitScale(bgImg);
            bgImg.set({
                scaleX: bgScale,
                scaleY: bgScale,
                left: canvasWidth / 2,
                top: canvasHeight / 2,
                originX: 'center',
                originY: 'center',
                selectable: false,
                evented: false
            });

            const maskScale = fitScale(alphaMaskImg);
            alphaMaskImg.set({
              scaleX: maskScale,
              scaleY: maskScale,
              left: canvasWidth / 2,
              top: canvasHeight / 2,
              originX: 'center',
              originY: 'center',
              selectable: false,
              evented: false,
              opacity: 1 // Full opacity
            });

            this.canvas.add(bgImg);
            this.canvas.add(alphaMaskImg);

            // Setup mask clipping (just for bounds calculation)
            this.setupMaskClipping();

            // Check for preserved data or session
            if (this.preservedCanvasData) {
              this.loadPreservedDesign();
            } else {
              const sessionLoaded = this.loadSessionDesign();
              if (!sessionLoaded) {
                setTimeout(() => {
                  this.isLoadingDesign = false;
                  this.saveHistory();
                }, 100);
              }
            }

            this.hideLoading();
        }).catch(error => {
            console.error('Error loading images:', error);
            this.isLoadingDesign = false;
            this.hideLoading();
            this.showNotification('Error loading product images. Please try again.', 'error');
        });
    };

    loadImages();
  }

  /**
   * FIXED: Load a preserved design (from cart edit).
   * This correctly handles both string and object data types to prevent errors.
   */
  loadPreservedDesign() {
      console.log('=== FIXED LOAD PRESERVED DESIGN START ===');

      if (!this.preservedCanvasData) {
          console.log('No preserved canvas data found');
          this.isLoadingDesign = false;
          return;
      }

      this.isLoadingDesign = true;

      try {
          console.log('Preserved canvas data type:', typeof this.preservedCanvasData);

          // Handle the preview logging correctly based on data type
          if (typeof this.preservedCanvasData === 'string') {
              console.log('Preserved canvas data preview:', this.preservedCanvasData.substring(0, 200) + '...');
          } else if (typeof this.preservedCanvasData === 'object') {
              console.log('Preserved canvas data preview:', JSON.stringify(this.preservedCanvasData).substring(0, 200) + '...');
          }

          // Ensure we have the correct data format (should be an object)
          let data = this.preservedCanvasData;
          if (typeof data === 'string') {
              try {
                  data = JSON.parse(data);
              } catch (e) {
                  console.error('Failed to parse preserved canvas data:', e);
                  this.isLoadingDesign = false;
                  this.showNotification('Error loading design', 'error');
                  return;
              }
          }

          console.log('Parsed data:', data);
          console.log('Number of objects:', data.objects ? data.objects.length : 0);

          this.canvas.remove(...this.canvas.getObjects().filter(obj => obj.selectable !== false));

          if (data.objects) {
              fabric.util.enlivenObjects(data.objects, (objects) => {
                  objects.forEach(obj => {
                      if (obj.selectable !== false) {
                          this.canvas.add(obj);
                      }
                  });

                  this.ensureProperLayering();
                  this.canvas.renderAll();

                  setTimeout(() => {
                      this.isLoadingDesign = false;
                      this.history = [JSON.stringify({
                          objects: this.canvas.getObjects().filter(obj => obj.selectable !== false).map(obj => obj.toObject(['selectable', 'evented', 'crossOrigin']))
                      })];
                      this.historyStep = 0;
                      this.updateHistoryButtons();
                      this.showNotification('Design loaded for editing');
                      console.log('=== FIXED LOAD PRESERVED DESIGN COMPLETE ===');
                  }, 300);

              }, '');

          } else {
              console.warn('No objects found in preserved data');
              this.isLoadingDesign = false;
              setTimeout(() => {
                  this.history = [JSON.stringify({objects: []})];
                  this.historyStep = 0;
                  this.updateHistoryButtons();
              }, 100);
          }
      } catch (e) {
          console.error('Failed to load preserved design:', e);
          console.error('Error stack:', e.stack);
          this.isLoadingDesign = false;
          this.showNotification('Error loading design', 'error');
      }

      // Clear the preserved data after attempting to load it
      this.preservedCanvasData = null;
      // Also clear from sessionStorage just in case
      sessionStorage.removeItem('edit_design_data');
      sessionStorage.removeItem('edit_design_variant');
  }

  setupMaskClipping() {
    // Simplified version - no clip guide, just calculate bounds for positioning
    const maskForClipping = this.maskImage;

    if (!this.canvas || !maskForClipping || !this.backgroundImage) {
      console.warn('Cannot setup mask clipping: missing required images');
      return;
    }

    console.log('Setting up mask clipping bounds...');

    const tempCanvas = document.createElement('canvas');
    tempCanvas.width = maskForClipping.width;
    tempCanvas.height = maskForClipping.height;
    const ctx = tempCanvas.getContext('2d');

    if (!ctx) {
      console.error('Failed to get 2D context for mask processing');
      return;
    }

    ctx.drawImage(maskForClipping._element, 0, 0);
    const imageData = ctx.getImageData(0, 0, tempCanvas.width, tempCanvas.height);
    const data = imageData.data;
    let minX = tempCanvas.width, minY = tempCanvas.height, maxX = 0, maxY = 0;
    let foundNonTransparent = false;

    for (let y = 0; y < tempCanvas.height; y++) {
      for (let x = 0; x < tempCanvas.width; x++) {
        const alpha = data[(y * tempCanvas.width + x) * 4 + 3];
        if (alpha > 10 || data[(y * tempCanvas.width + x) * 4] > 10) {
          foundNonTransparent = true;
          minX = Math.min(minX, x);
          minY = Math.min(minY, y);
          maxX = Math.max(maxX, x);
          maxY = Math.max(maxY, y);
        }
      }
    }

    if (foundNonTransparent) {
      const maskElement = maskForClipping._element;
      const originalMaskWidth = maskElement.naturalWidth;
      const originalMaskHeight = maskElement.naturalHeight;

      const currentScaledMaskWidth = maskForClipping.getScaledWidth();
      const currentScaledMaskHeight = maskForClipping.getScaledHeight();

      const widthRatio = currentScaledMaskWidth / originalMaskWidth;
      const heightRatio = currentScaledMaskHeight / originalMaskHeight;
      const effectiveScale = Math.min(widthRatio, heightRatio);

      const canvasWidth = this.canvas.width;
      const canvasHeight = this.canvas.height;
      const offsetX = (canvasWidth - currentScaledMaskWidth) / 2;
      const offsetY = (canvasHeight - currentScaledMaskHeight) / 2;

      this.clipBounds = {
        left: offsetX + minX * effectiveScale,
        top: offsetY + minY * effectiveScale,
        width: (maxX - minX + 1) * effectiveScale,
        height: (maxY - minY + 1) * effectiveScale
      };

      console.log('Clip bounds calculated:', this.clipBounds);
    } else {
      console.warn('No non-transparent area found in mask.');
      this.clipBounds = null;
    }
  }

  showProperties(object) {
    if (!object || object === this.backgroundImage || object === this.maskImage || object === this.unclippedMaskImage) return;

    const cropBtn = document.getElementById('crop-btn');
    const editTools = document.getElementById('edit-tools');
    if (cropBtn && object.type === 'image') {
      cropBtn.style.display = 'flex';
      if (editTools) editTools.style.display = 'block';
    }

    // Only show properties panel on mobile devices
    if (window.innerWidth <= 768) {
      const propsPanel = document.querySelector('.properties-panel');
      if (propsPanel) {
        propsPanel.style.display = 'block';
        propsPanel.classList.add('mobile-visible');
      }
    }
  }

  hideProperties() {
    const cropBtn = document.getElementById('crop-btn');
    const editTools = document.getElementById('edit-tools');
    if (cropBtn) cropBtn.style.display = 'none';
    if (editTools) editTools.style.display = 'none';

    // Hide properties panel on mobile
    if (window.innerWidth <= 768) {
      const propsPanel = document.querySelector('.properties-panel');
      if (propsPanel) {
        propsPanel.classList.remove('mobile-visible');
      }
    }
  }

  saveDesign() {
    if (!this.canvas) return;
    const designNameModal = document.createElement('div');
    designNameModal.className = 'text-editor-modal';
    designNameModal.innerHTML = `
      <div class="modal-content">
        <h3>Save Design</h3>
        <input type="text" id="design-name-input" placeholder="Enter a name for your design" />
        <div class="modal-actions">
          <button class="btn btn-secondary" id="cancel-save-design">Cancel</button>
          <button class="btn btn-primary" id="confirm-save-design">Save</button>
        </div>
      </div>
    `;
    document.body.appendChild(designNameModal);
    designNameModal.style.display = 'flex';

    const nameInput = document.getElementById('design-name-input');
    const confirmBtn = document.getElementById('confirm-save-design');
    const cancelBtn = document.getElementById('cancel-save-design');

    nameInput?.focus();

    const handleSave = () => {
      const designName = nameInput?.value.trim();
      if (!designName) {
        this.showNotification('Please enter a name for your design.', 'error');
        return;
      }

      const userObjects = this.canvas.getObjects().filter(obj => obj.selectable !== false);
      const designData = {
        name: designName,
        date: new Date().toISOString(),
        canvasData: { objects: userObjects.map(obj => obj.toObject(['selectable', 'evented', 'crossOrigin'])) },
        variantId: this.currentVariantId
      };

      this.savedDesigns.push(designData);
      localStorage.setItem('customDesigns', JSON.stringify(this.savedDesigns));
      this.showNotification('Design saved successfully!');
      document.body.removeChild(designNameModal);
    };

    confirmBtn?.addEventListener('click', handleSave);
    nameInput?.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') handleSave();
    });
    cancelBtn?.addEventListener('click', () => {
      document.body.removeChild(designNameModal);
    });
  }

  loadSavedDesigns() {
    const saved = localStorage.getItem('customDesigns');
    return saved ? JSON.parse(saved) : [];
  }

  showSavedDesigns() {
    const designs = this.savedDesigns.filter(d => d.variantId == this.currentVariantId);
    if (designs.length === 0) {
      this.showNotification('No saved designs for this product variant.');
      return;
    }
    const modal = document.createElement('div');
    modal.className = 'saved-designs-modal';
    modal.innerHTML = `
      <div class="saved-designs-content">
        <h3>Load Saved Design</h3>
        <div class="designs-grid">
          ${designs.map((design, index) => `
            <div class="design-item" data-index="${index}">
              <h4>${design.name}</h4>
              <p>${new Date(design.date).toLocaleDateString()}</p>
              <button class="btn load-this-design">Load</button>
              <button class="btn btn-danger delete-this-design">Delete</button>
            </div>
          `).join('')}
        </div>
        <button class="btn btn-secondary close-saved-designs">Close</button>
      </div>
    `;
    document.body.appendChild(modal);
    modal.style.display = 'flex';

    modal.querySelectorAll('.load-this-design').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const index = e.target.closest('.design-item').dataset.index;
        this.loadSavedDesign(designs[index]);
        document.body.removeChild(modal);
      });
    });
    modal.querySelectorAll('.delete-this-design').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const index = e.target.closest('.design-item').dataset.index;
        const designToDelete = designs[index];

        this.showConfirmationModal(`Delete design "${designToDelete.name}"?`, () => {
          this.savedDesigns = this.savedDesigns.filter((d, i) =>
            !(d.name === designToDelete.name && d.variantId == designToDelete.variantId && d.date === designToDelete.date)
          );
          localStorage.setItem('customDesigns', JSON.stringify(this.savedDesigns));
          document.body.removeChild(modal);
          this.showSavedDesigns();
          this.showNotification('Design deleted.');
        });
      });
    });
    modal.querySelector('.close-saved-designs')?.addEventListener('click', () => {
      document.body.removeChild(modal);
    });
  }

  showConfirmationModal(message, onConfirm) {
    const confirmModal = document.createElement('div');
    confirmModal.className = 'text-editor-modal';
    confirmModal.innerHTML = `
      <div class="modal-content">
        <h3>Confirmation</h3>
        <p>${message}</p>
        <div class="modal-actions">
          <button class="btn btn-secondary" id="cancel-confirm">Cancel</button>
          <button class="btn btn-primary" id="confirm-action">Confirm</button>
        </div>
      </div>
    `;
    document.body.appendChild(confirmModal);
    confirmModal.style.display = 'flex';

    document.getElementById('confirm-action')?.addEventListener('click', () => {
      onConfirm();
      document.body.removeChild(confirmModal);
    });
    document.getElementById('cancel-confirm')?.addEventListener('click', () => {
      document.body.removeChild(confirmModal);
    });
  }

  loadSavedDesign(design) {
    if (!this.canvas) return;
    this.isLoadingDesign = true;
    this.canvas.discardActiveObject();
    this.canvas.remove(...this.canvas.getObjects().filter(obj => obj.selectable !== false));

    const loadPromises = [];
    if (design.canvasData && design.canvasData.objects) {
      design.canvasData.objects.forEach(objData => {
        if (objData.type === 'text' || objData.type === 'i-text') {
          const text = new fabric.Text(objData.text || '', objData);
          this.canvas.add(text);
        } else if (objData.type === 'image' && objData.src) {
          const promise = new Promise((resolve) => {
            fabric.Image.fromURL(objData.src, (img) => {
              if (img) {
                img.set(objData);
                this.canvas.add(img);
              } else {
                console.warn('Failed to load image from saved design:', objData.src);
              }
              resolve();
            }, { crossOrigin: 'anonymous' });
          });
          loadPromises.push(promise);
        }
      });
    }

    Promise.all(loadPromises).then(() => {
      this.ensureProperLayering();
      this.canvas.renderAll();
      this.showNotification('Design loaded successfully!');

      setTimeout(() => {
        this.isLoadingDesign = false;
        this.history = [JSON.stringify({objects: this.canvas.getObjects().filter(obj => obj.selectable !== false).map(obj => obj.toObject(['selectable', 'evented', 'crossOrigin']))})];
        this.historyStep = 0;
        this.updateHistoryButtons();
      }, 100);
    }).catch(error => {
      console.error('Error loading images from saved design:', error);
      this.isLoadingDesign = false;
      this.showNotification('Error loading design. Some elements might be missing.', 'error');
    });
  }

  showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = 'designer-notification';
    if (type === 'error') {
      notification.classList.add('error');
    } else if (type === 'info') {
      notification.classList.add('info');
    } else if (type === 'warning') {
      notification.classList.add('warning');
    }

    const icon = type === 'error' ? '❌' : type === 'warning' ? '⚠️' : type === 'info' ? 'ℹ️' : '✅';
    notification.innerHTML = `<span>${icon}</span> ${message}`;

    document.body.appendChild(notification);
    setTimeout(() => notification.classList.add('show'), 10);
    setTimeout(() => {
      notification.classList.remove('show');
      setTimeout(() => {
        if (document.body.contains(notification)) {
          document.body.removeChild(notification);
        }
      }, 300);
    }, 3000);
  }

  updateUIAfterDesign() {
    // Add body class to show the add to cart button
    document.body.classList.add('swpd-design-applied');

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
            addToCartBtn.style.display = 'inline-block'; // Show the original button
            addToCartBtn.textContent = (typeof swpdTranslations !== 'undefined' && swpdTranslations.addToCart) ? swpdTranslations.addToCart : 'Add to Cart';
        }
    }
}

  // FIXED: Upload image to WordPress with enhanced debugging
  async uploadToWordPress(base64Image, filename) {
    console.log('\n=== WORDPRESS UPLOAD DEBUG ===');
    console.log('Upload URL:', this.wpAjaxConfig.url);
    console.log('Nonce:', this.wpAjaxConfig.nonce);
    console.log('Filename:', filename);
    console.log('Base64 length:', base64Image.length);

    try {
      // If image is already a WordPress URL, skip upload
      if (base64Image.includes(window.location.origin + '/wp-content/uploads/')) {
        console.log('✓ Image already uploaded to WordPress');
        return { url: base64Image, message: 'Already uploaded.' };
      }

      // If it's a base image from product, keep it
      if (this.backgroundUrl && base64Image === this.backgroundUrl ||
          this.maskUrl && base64Image === this.maskUrl ||
          this.unclippedMaskUrl && base64Image === this.unclippedMaskUrl) {
        console.log('✓ Skipping upload for product base/mask image');
        return { url: base64Image, message: 'Product base image.' };
      }

      const formData = new FormData();
      formData.append('action', 'swpd_upload_user_image');
      formData.append('nonce', this.wpAjaxConfig.nonce || swpdDesignerConfig?.nonce || '');
      formData.append('image', base64Image);
      formData.append('filename', filename);

      console.log('Sending AJAX request...');
      console.log('FormData entries:');
      for (let [key, value] of formData.entries()) {
        if (key === 'image') {
          console.log(`  ${key}: [base64 data, length: ${value.length}]`);
        } else {
          console.log(`  ${key}: ${value}`);
        }
      }

      const response = await fetch(this.wpAjaxConfig.url, {
        method: 'POST',
        credentials: 'same-origin',
        body: formData,
      });

      console.log('Response status:', response.status);
      console.log('Response headers:', Object.fromEntries(response.headers.entries()));

      const responseText = await response.text();
      console.log('Raw response:', responseText);

      // WordPress returns "0" when action handler is not found
      if (responseText === '0') {
        console.error('✗ WordPress returned "0" - possible causes:');
        console.error('  1. Action handler "swpd_upload_user_image" not registered');
        console.error('  2. Nonce verification failed');
        console.error('  3. User not logged in (if required)');
        console.error('  4. Handler died before sending response');
        console.error('  5. Plugin might not be active');

        console.log('Debug info:');
        console.log('  - Current nonce:', this.wpAjaxConfig.nonce);
        console.log('  - AJAX URL:', this.wpAjaxConfig.url);
        console.log('  - Is user logged in:', document.body.classList.contains('logged-in'));

        // Try Cloudinary as fallback
        console.log('Attempting Cloudinary upload as fallback...');
        return await this.uploadToCloudinary(base64Image, filename);
      }

      if (response.ok) {
        try {
          const data = JSON.parse(responseText);
          console.log('Parsed response:', data);

          if (data.success) {
            console.log('✓ Upload successful!');
            console.log('Uploaded URL:', data.data.url);
            return data.data;
          } else {
            console.error('✗ WordPress upload failed:', data.data ? data.data.message : 'Unknown error');
            console.error('Full error data:', data);

            // Try Cloudinary as fallback
            console.log('Attempting Cloudinary upload as fallback...');
            return await this.uploadToCloudinary(base64Image, filename);
          }
        } catch (e) {
          console.error('✗ Failed to parse JSON response:', e);
          console.error('Response was:', responseText);

          // Check if it's a WordPress error page
          if (responseText.includes('<!DOCTYPE') || responseText.includes('<html')) {
            console.error('Received HTML instead of JSON - possible WordPress error page');
          }

          // Try Cloudinary as fallback
          console.log('Attempting Cloudinary upload as fallback...');
          return await this.uploadToCloudinary(base64Image, filename);
        }
      } else {
        console.error('✗ HTTP error:', response.status, response.statusText);

        // Try to parse error response
        try {
          const errorData = JSON.parse(responseText);
          console.error('Error response data:', errorData);
        } catch (e) {
          console.error('Could not parse error response');
        }

        // Try Cloudinary as fallback
        console.log('Attempting Cloudinary upload as fallback...');
        return await this.uploadToCloudinary(base64Image, filename);
      }
    } catch (error) {
      console.error('✗ Network/JavaScript error:', error);
      console.error('Error type:', error.constructor.name);
      console.error('Error stack:', error.stack);

      // Try Cloudinary as fallback
      console.log('Attempting Cloudinary upload as fallback...');
      return await this.uploadToCloudinary(base64Image, filename);
    } finally {
      console.log('=== END WORDPRESS UPLOAD DEBUG ===\n');
    }
  }

  // NEW: Upload to Cloudinary
  async uploadToCloudinary(base64Image, filename) {
    console.log('\n=== CLOUDINARY UPLOAD DEBUG ===');

    try {
      // Check if Cloudinary is configured
      const cloudinaryConfig = swpdDesignerConfig?.cloudinary || {};

      console.log('Full swpdDesignerConfig:', swpdDesignerConfig);
      console.log('Cloudinary config object:', cloudinaryConfig);

      // Check different possible config locations (including from wp-config.php constants)
      const enabled = cloudinaryConfig.enabled ||
                     swpdDesignerConfig?.cloudinary_enabled ||
                     (swpdDesignerConfig?.cloudinary_cloud_name && true);
      const cloudName = cloudinaryConfig.cloud_name ||
                       swpdDesignerConfig?.cloudinary_cloud_name;
      const uploadPreset = cloudinaryConfig.upload_preset ||
                          swpdDesignerConfig?.cloudinary_upload_preset ||
                          ''; // Empty string is OK for signed uploads
      const folder = cloudinaryConfig.folder ||
                    swpdDesignerConfig?.cloudinary_folder ||
                    'swpd-designs';

      console.log('Resolved config:', {
        enabled: enabled,
        cloudName: cloudName,
        uploadPreset: uploadPreset,
        folder: folder,
        hasApiKey: !!(swpdDesignerConfig?.cloudinary_api_key),
        hasApiSecret: !!(swpdDesignerConfig?.cloudinary_api_secret)
      });

      if (!enabled || !cloudName) {
        console.warn('Cloudinary not configured properly');
        console.warn('Missing:', {
          enabled: !enabled ? 'enabled flag' : null,
          cloudName: !cloudName ? 'cloud name' : null
        });
        return { error: true, message: 'Cloudinary not configured' };
      }

      console.log('Using Cloudinary config:', {
        cloud_name: cloudName,
        upload_preset: uploadPreset || '(none - will use signed upload)',
        folder: folder
      });

      // Convert base64 to blob
      const base64Data = base64Image.split(',')[1];
      const byteCharacters = atob(base64Data);
      const byteNumbers = new Array(byteCharacters.length);
      for (let i = 0; i < byteCharacters.length; i++) {
        byteNumbers[i] = byteCharacters.charCodeAt(i);
      }
      const byteArray = new Uint8Array(byteNumbers);
      const blob = new Blob([byteArray], { type: 'image/png' });

      // Create form data for Cloudinary
      const formData = new FormData();
      formData.append('file', blob, filename);
      formData.append('folder', folder);
      formData.append('public_id', `design_${Date.now()}_${filename.replace(/\.[^/.]+$/, '')}`);

      // If we have an upload preset, use it (unsigned upload)
      if (uploadPreset) {
        formData.append('upload_preset', uploadPreset);
      }

      const cloudinaryUrl = `https://api.cloudinary.com/v1_1/${cloudName}/image/upload`;
      console.log('Uploading to:', cloudinaryUrl);
      console.log('Upload type:', uploadPreset ? 'Unsigned (with preset)' : 'Unsigned (no preset - may fail)');

      const response = await fetch(cloudinaryUrl, {
        method: 'POST',
        body: formData
      });

      console.log('Cloudinary response status:', response.status);
      const responseText = await response.text();
      console.log('Cloudinary response:', responseText);

      if (response.ok) {
        const data = JSON.parse(responseText);
        console.log('✓ Cloudinary upload successful!');
        console.log('Uploaded URL:', data.secure_url);
        console.log('Full response data:', data);

        // Also try to save the URL to WordPress
        this.saveCloudinaryUrlToWordPress(data.secure_url, filename);

        return {
          url: data.secure_url,
          public_id: data.public_id,
          format: data.format,
          width: data.width,
          height: data.height
        };
      } else {
        console.error('✗ Cloudinary upload failed:', responseText);
        try {
          const errorData = JSON.parse(responseText);
          console.error('Error details:', errorData);
          if (errorData.error?.message?.includes('Upload preset')) {
            console.error('TIP: You need to create an unsigned upload preset in Cloudinary dashboard');
            console.error('Go to: Settings > Upload > Upload presets > Add upload preset');
            console.error('Set it to "Unsigned" mode');
          }
        } catch (e) {
          console.error('Could not parse error response');
        }
        return { error: true, message: 'Cloudinary upload failed' };
      }
    } catch (error) {
      console.error('✗ Cloudinary error:', error);
      console.error('Error stack:', error.stack);
      return { error: true, message: error.message };
    } finally {
      console.log('=== END CLOUDINARY UPLOAD DEBUG ===\n');
    }
  }

  // Save Cloudinary URL to WordPress for reference
  async saveCloudinaryUrlToWordPress(cloudinaryUrl, filename) {
    try {
      const formData = new FormData();
      formData.append('action', 'swpd_save_cloudinary_url');
      formData.append('nonce', this.wpAjaxConfig.nonce || swpdDesignerConfig?.nonce || '');
      formData.append('url', cloudinaryUrl);
      formData.append('filename', filename);

      await fetch(this.wpAjaxConfig.url, {
        method: 'POST',
        credentials: 'same-origin',
        body: formData,
      });
    } catch (error) {
      console.warn('Could not save Cloudinary URL to WordPress:', error);
    }
  }

  // Upload preview to WordPress
  async uploadPreviewToWordPress(previewDataUrl) {
    console.log('=== PREVIEW UPLOAD DEBUG ===');
    console.log('Preview data URL length:', previewDataUrl.length);
    console.log('Estimated size:', Math.round(previewDataUrl.length * 0.75 / 1024), 'KB');
    console.log('AJAX URL:', this.wpAjaxConfig.url);
    console.log('Nonce:', this.wpAjaxConfig.nonce);

    try {
      const formData = new FormData();
      formData.append('action', 'swpd_upload_design_preview');
      formData.append('nonce', this.wpAjaxConfig.nonce || swpdDesignerConfig?.nonce || '');
      formData.append('image', previewDataUrl);
      formData.append('filename', `design-preview-${this.currentVariantId}-${Date.now()}.jpg`);

      console.log('Sending preview upload request...');

      const response = await fetch(this.wpAjaxConfig.url, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
      });

      console.log('Preview upload response status:', response.status);
      const responseText = await response.text();
      console.log('Raw response:', responseText);

      // Try to parse as JSON
      let data;
      try {
        data = JSON.parse(responseText);
      } catch (e) {
        console.error('Failed to parse response as JSON:', e);
        console.error('Response was:', responseText);

        // Check for common WordPress errors
        if (responseText === '0') {
          console.error('WordPress returned "0" - AJAX handler not found or nonce failed');
          this.showNotification('Preview upload failed. Please try again.', 'warning');
        }

        return previewDataUrl;
      }

      if (data.success) {
        console.log('✓ Design preview uploaded successfully');
        console.log('Preview URL:', data.data);
        // Return just the URL string, not the object
        if (typeof data.data === 'object' && data.data.url) {
          return data.data.url;
        } else if (typeof data.data === 'string') {
          return data.data;
        } else {
          console.error('Unexpected data format:', data.data);
          return previewDataUrl; // Fallback to base64
        }
      } else {
        console.error('✗ WordPress preview upload failed:', data.data?.message || 'Unknown error');
        this.showNotification('Using local preview due to upload error', 'warning');
        return previewDataUrl;
      }
    } catch (error) {
      console.error('✗ Error uploading preview to WordPress:', error);
      console.error('Stack:', error.stack);
      this.showNotification('Using local preview due to network error', 'warning');
      return previewDataUrl;
    } finally {
      console.log('=== END PREVIEW UPLOAD DEBUG ===');
    }
  }

  /**
   * FIXED: Apply design and redirect correctly.
   */
  async applyDesign() {
      if (!this.canvas) return;

      this.showNotification('Processing design...', 'info');
      const preview = await this.generatePreview();
      const uploadResult = await this.uploadPreviewToWordPress(preview);

      let wpPreviewUrl = typeof uploadResult === 'string' ? uploadResult : (uploadResult.url || preview);

      const canvasData = JSON.stringify({
          objects: this.canvas.getObjects().filter(obj => obj.selectable !== false).map(obj => obj.toObject(['selectable', 'evented', 'crossOrigin']))
      });

      document.getElementById('custom-design-preview').value = wpPreviewUrl;
      document.getElementById('custom-design-data').value = JSON.stringify({
          hasCustomDesign: true,
          elementCount: this.canvas.getObjects().filter(obj => obj.selectable !== false).length,
          timestamp: Date.now()
      });
      document.getElementById('custom-canvas-data').value = canvasData;

      // Update product image on the page
      const mainImage = document.querySelector('.woocommerce-product-gallery__image img');
      if (mainImage) {
        mainImage.src = wpPreviewUrl;
        mainImage.srcset = '';
      }

      this.updateUIAfterDesign();
      this.showNotification('Design applied!');

      // Close lightbox but also add a parameter to the URL to signify a design was just applied.
      this.closeLightbox();
      setTimeout(() => {
          const currentUrl = new URL(window.location.href);
          currentUrl.searchParams.set('design_applied', '1');
          // A soft reload is better here to ensure all WooCommerce scripts update correctly
          window.location.href = currentUrl.toString();
      }, 100);
  }

  // Add to cart
  async addToCart() {
    console.log('=== ADD TO CART DEBUG ===');

    if (!this.canvas) {
      console.error('Canvas not available');
      return;
    }

    const addToCartBtn = document.querySelector('.add-to-cart-design');
    let originalButtonHTML = '';
    if (addToCartBtn) {
      originalButtonHTML = addToCartBtn.innerHTML;
    }

    this.showNotification('Preparing your design...', 'info');

    // Generate and upload preview
    const preview = await this.generatePreview();
    console.log('Generated preview, size:', Math.round(preview.length * 0.75 / 1024), 'KB');

    const uploadResult = await this.uploadPreviewToWordPress(preview);
    console.log('Upload result type:', typeof uploadResult);

    // Extract the URL from the result object
    let wpPreviewUrl;
    if (typeof uploadResult === 'object' && uploadResult.url) {
      wpPreviewUrl = uploadResult.url;
    } else if (typeof uploadResult === 'string') {
      wpPreviewUrl = uploadResult;
    } else {
      console.error('Invalid preview upload result:', uploadResult);
      this.showNotification('Error processing preview image', 'error');
      return;
    }

    console.log('Final preview URL:', wpPreviewUrl);

    const canvasData = JSON.parse(JSON.stringify({objects: this.canvas.getObjects().filter(obj => obj.selectable !== false).map(obj => obj.toObject(['selectable', 'evented', 'crossOrigin']))}));

    // Set form data
    const previewInput = document.getElementById('custom-design-preview');
    const dataInput = document.getElementById('custom-design-data');
    const canvasInput = document.getElementById('custom-canvas-data');

    if (previewInput) {
      previewInput.value = wpPreviewUrl;
      console.log('Set preview input value:', wpPreviewUrl);
    }

    if (dataInput) {
      const designData = JSON.stringify({
        hasCustomDesign: true,
        designCount: this.canvas.getObjects().filter(obj => obj.selectable !== false).length,
        timestamp: Date.now(),
        previewUrl: wpPreviewUrl // Add preview URL to design data
      });
      dataInput.value = designData;
      console.log('Set design data:', designData);
    }

    if (canvasInput) {
      canvasInput.value = JSON.stringify(canvasData);
      console.log('Set canvas data, objects:', canvasData.objects.length);
    }

    // Update the main product image to show the design
    const productImages = document.querySelectorAll('.product-gallery__image img, .product__media-image, .product-single__photo img, .woocommerce-product-gallery__image img');
    console.log('Found product images to update:', productImages.length);

    productImages.forEach((img, index) => {
      if (img) {
        if (!img.dataset.originalSrc) {
          img.dataset.originalSrc = img.src;
          img.dataset.originalSrcset = img.srcset || '';
        }
        img.src = wpPreviewUrl;
        img.removeAttribute('srcset');
        console.log(`Updated product image ${index + 1}`);
      }
    });

    // Add the swpd-design-applied class
    document.body.classList.add('swpd-design-applied');

    this.saveSessionDesign();
    sessionStorage.removeItem('designer_session_' + this.currentVariantId);

    // Store preview URL for cart processing
    sessionStorage.setItem('swpd_cart_preview_url', wpPreviewUrl);
    sessionStorage.setItem('swpd_cart_canvas_data', JSON.stringify(canvasData));

    // Close the lightbox
    this.closeLightbox();

    // Wait a moment for the DOM to update, then submit the form
    setTimeout(() => {
      const productForm = document.querySelector('form.cart') || document.querySelector('.product-form form');
      if (!productForm) {
        console.error('Product form not found for add to cart action.');
        this.showNotification('Error: Could not find product form. Please click Add to Cart manually.', 'error');
        return;
      }

      console.log('Submitting product form...');
      console.log('Form action:', productForm.action);
      console.log('Form method:', productForm.method);

      // Find and click the actual add to cart button
      const originalAddToCartButton = productForm.querySelector('button[name="add-to-cart"]:not(#swpd-customize-design-button), button[type="submit"]:not(#swpd-customize-design-button)');
      if (originalAddToCartButton) {
        this.showNotification('Adding to cart...');
        console.log('Clicking add to cart button');
        originalAddToCartButton.click();
      } else {
        // Fallback: submit the form directly
        console.log('No add to cart button found, submitting form directly');
        productForm.submit();
      }
    }, 500);

    console.log('=== END ADD TO CART DEBUG ===');
  }

  generatePreview() {
    if (!this.canvas) return '';

    console.log('=== GENERATING PREVIEW ===');

    try {
      // First, ensure no objects are selected (to hide selection controls)
      this.canvas.discardActiveObject();
      this.canvas.renderAll();

      // Fixed size for preview (smaller for faster upload)
      const maxPreviewSize = 600;

      // Get the current canvas size
      const canvasWidth = this.canvas.width;
      const canvasHeight = this.canvas.height;

      // Calculate scale to fit within max size
      const scale = Math.min(maxPreviewSize / canvasWidth, maxPreviewSize / canvasHeight, 1);
      const finalWidth = Math.round(canvasWidth * scale);
      const finalHeight = Math.round(canvasHeight * scale);

      console.log('Canvas size:', canvasWidth, 'x', canvasHeight);
      console.log('Preview size:', finalWidth, 'x', finalHeight);
      console.log('Scale factor:', scale);

      // Use fabric's built-in toDataURL which excludes controls
      const previewDataUrl = this.canvas.toDataURL({
        format: 'jpeg',
        quality: 0.85,
        multiplier: scale,
        enableRetinaScaling: false
      });

      const sizeInKB = Math.round(previewDataUrl.length * 0.75 / 1024);
      console.log('Preview generated, size:', sizeInKB, 'KB');

      // If still too large, reduce quality
      if (sizeInKB > 150) {
        console.log('Preview too large, reducing quality...');
        const smallerPreview = this.canvas.toDataURL({
          format: 'jpeg',
          quality: 0.7,
          multiplier: scale * 0.8,
          enableRetinaScaling: false
        });
        const smallerSizeKB = Math.round(smallerPreview.length * 0.75 / 1024);
        console.log('Reduced preview size:', smallerSizeKB, 'KB');
        return smallerPreview;
      }

      return previewDataUrl;

    } catch (error) {
      console.error('Error in generatePreview:', error);
      console.error('Stack:', error.stack);

      // Create a simple fallback preview
      const fallbackCanvas = document.createElement('canvas');
      fallbackCanvas.width = 400;
      fallbackCanvas.height = 400;
      const ctx = fallbackCanvas.getContext('2d');

      // White background
      ctx.fillStyle = '#ffffff';
      ctx.fillRect(0, 0, 400, 400);

      // Add text
      ctx.fillStyle = '#333333';
      ctx.font = '24px Arial';
      ctx.textAlign = 'center';
      ctx.textBaseline = 'middle';
      ctx.fillText('Custom Design', 200, 200);

      const fallbackDataUrl = fallbackCanvas.toDataURL('image/jpeg', 0.8);
      console.log('Using fallback preview');

      return fallbackDataUrl;
    } finally {
      console.log('=== END PREVIEW GENERATION ===');
    }
  }

  // Load design from cart data
  loadDesignFromCartData(productUrl, variantId, canvasData) {
    sessionStorage.setItem('edit_design_data', canvasData);
    sessionStorage.setItem('edit_design_variant', variantId);
    window.location.href = `${productUrl}?edit_design=1&variant=${variantId}`;
  }

  regeneratePreviewForVariant(variantId, savedCanvasData, callback) {
    if (!this.canvas) return;

    this.regenerateCallback = callback;
    const variants = swpdDesignerConfig.variants;
    const variant = variants?.find(v => v && v.id == variantId);

    if (!variant) {
      console.error('Variant not found:', variantId);
      return;
    }

    let designerData = variant.designer_data;
    if (!designerData) {
      console.error('No designer data for variant:', variantId);
      return;
    }

    if (typeof designerData === 'string') {
      try {
        designerData = JSON.parse(designerData);
      } catch (e) {
        console.error('Failed to parse designer data:', e);
        return;
      }
    }

    this.canvas.clear();
    const tempBgUrl = designerData.baseImage;
    const tempAlphaMaskUrl = designerData.alphaMask;

    Promise.all([
      this.loadImagePromise(tempBgUrl),
      this.loadImagePromise(tempAlphaMaskUrl)
    ]).then(([tempBgImg, tempAlphaMaskImg]) => {
      const tempCanvas = new fabric.StaticCanvas(null, {
        width: this.canvas.width,
        height: this.canvas.height,
        backgroundColor: 'transparent'
      });

      const fitScale = (img) => {
        const scaleX = tempCanvas.width / img.width;
        const scaleY = tempCanvas.height / img.height;
        return Math.min(scaleX, scaleY);
      }

      const bgScale = fitScale(tempBgImg);
      tempBgImg.set({
        scaleX: bgScale,
        scaleY: bgScale,
        left: tempCanvas.width / 2,
        top: tempCanvas.height / 2,
        originX: 'center',
        originY: 'center'
      });

      const alphaMaskScale = fitScale(tempAlphaMaskImg);
      tempAlphaMaskImg.set({
        scaleX: alphaMaskScale,
        scaleY: alphaMaskScale,
        left: tempCanvas.width / 2,
        top: tempCanvas.height / 2,
        originX: 'center',
        originY: 'center',
        opacity: 0.5
      });
      tempCanvas.add(tempBgImg);
      tempCanvas.add(tempAlphaMaskImg);
      const designToLoad = JSON.parse(JSON.stringify(savedCanvasData));
      if (designToLoad.objects) {
        designToLoad.objects = designToLoad.objects.filter(obj => obj.selectable !== false);
      } else {
        designToLoad.objects = [];
      }
      tempCanvas.loadFromJSON(designToLoad, () => {
        tempCanvas.renderAll();
        if (this.regenerateCallback) {
          this.regenerateCallback(this.generatePreviewFromTempCanvas(tempCanvas));
          this.regenerateCallback = null;
        }
        tempCanvas.dispose();
      }, { crossOrigin: 'anonymous' });
    }).catch(error => {
      console.error('Error loading variant images for regeneration:', error);
      if (this.regenerateCallback) {
        this.regenerateCallback('');
        this.regenerateCallback = null;
      }
    });
  }

  generatePreviewFromTempCanvas(canvas) {
    if (!canvas) return '';
    const maxSize = 1200;
    const scale = Math.min(maxSize / canvas.width, maxSize / canvas.height, 1.5);
    return canvas.toDataURL({
      format: 'png',
      quality: 1,
      multiplier: scale
    });
  }

  loadImagePromise(url) {
    return new Promise((resolve, reject) => {
      if (!url) {
        reject(new Error('No URL provided'));
        return;
      }
      fabric.Image.fromURL(url, (img) => {
        if (img && img.getElement()) {
          resolve(img);
        } else {
          reject(new Error('Failed to load image: ' + url));
        }
      }, { crossOrigin: 'anonymous' });
    });
  }
}

// Initialize the designer and set up cart event handlers when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Initialize the designer if config is available
    if (typeof swpdDesignerConfig !== 'undefined') {
        try {
            window.customDesigner = new EnhancedProductDesigner(swpdDesignerConfig);
        } catch (error) {
            console.error('Error initializing custom designer:', error);
        }
    } else {
        console.warn('swpdDesignerConfig not found. Custom designer not initialized.');
    }

    // FIXED: Add event listener for the product page's "Customize Design" button
    document.body.addEventListener('click', function(e) {
        if (e.target && e.target.id === 'swpd-customize-design-button') {
            e.preventDefault();
            console.log('=== CUSTOMIZE BUTTON CLICK DEBUG ===');
            console.log('Customize Design button clicked!');
            console.log('Designer available:', !!window.customDesigner);
            console.log('Designer methods:', window.customDesigner ? Object.getOwnPropertyNames(Object.getPrototypeOf(window.customDesigner)) : 'N/A');

            if (window.customDesigner) {
                console.log('swpdDesignerConfig:', swpdDesignerConfig);
                var productId = swpdDesignerConfig.product_id;
                var variantId = productId; // Default to product ID for simple products

                // For variable products, find the currently selected variation ID
                var variationInput = document.querySelector('input[name="variation_id"]');
                console.log('Variation input found:', !!variationInput);
                if (variationInput && variationInput.value > 0) {
                    variantId = variationInput.value;
                    console.log('Using variation ID from input:', variantId);
                } else {
                    console.log('Using product ID as variant ID:', variantId);
                }

                console.log('Final variant ID to use:', variantId);
                console.log('About to call openLightbox...');

                if (variantId) {
                    try {
                        window.customDesigner.openLightbox(variantId);
                        console.log('openLightbox called successfully');
                    } catch (error) {
                        console.error('Error calling openLightbox:', error);
                        alert('Error opening designer: ' + error.message);
                    }
                } else if (swpdDesignerConfig.product_type === 'variable') {
                    // Alert the user if they haven't selected product options for a variable product
                    alert('Please select product options before customizing.');
                } else {
                    // Fallback for simple products
                    console.log('Fallback: using product ID for simple product');
                    try {
                        window.customDesigner.openLightbox(productId);
                        console.log('Fallback openLightbox called successfully');
                    } catch (error) {
                        console.error('Error in fallback openLightbox:', error);
                        alert('Error opening designer: ' + error.message);
                    }
                }
            } else {
                console.error('Designer not initialized. Cannot open lightbox.');
                console.log('Window object keys:', Object.keys(window).filter(key => key.includes('custom') || key.includes('designer')));
                alert('Sorry, the designer could not be opened. Please refresh the page and try again.');
            }
            console.log('=== END CUSTOMIZE BUTTON CLICK DEBUG ===');
        },



hideTextEditor() {
  const modal = document.getElementById('text-editor-modal');
  const input = document.getElementById('text-input');

  if (modal) {
    modal.classList.remove('active');
    modal.classList.remove('mobile-modal');

    // Animate out and then hide
    setTimeout(() => {
      modal.style.display = 'none';
    }, 300); // Wait for animation to complete
  }

  // Clear form values
  if (input) input.value = '';
  const colorInput = document.getElementById('text-color');
  const sizeInput = document.getElementById('text-size');
  const fontSelect = document.getElementById('font-select');

  if (colorInput) colorInput.value = '#000000';
  if (sizeInput) sizeInput.value = '30';
  if (fontSelect) fontSelect.value = 'Arial';

  // Update size display
  const sizeValue = document.getElementById('text-size-value');
  if (sizeValue) sizeValue.textContent = '30px';
},



addText() {
  console.log('=== ADD TEXT CALLED ===');

  if (!this.canvas) {
    console.error('Canvas not available');
    this.showNotification('Designer not ready. Please try again.', 'error');
    return;
  }

  const text = document.getElementById('text-input')?.value;
  console.log('Text to add:', text);

  if (!text || text.trim().length === 0) {
    this.showNotification('Please enter some text', 'error');
    return;
  }

  const font = document.getElementById('font-select')?.value || 'Arial';
  const color = document.getElementById('text-color')?.value || '#000000';
  const size = parseInt(document.getElementById('text-size')?.value || '30');

  console.log('Text properties:', { text: text.trim(), font, color, size });

  const textPathType = document.querySelector('.text-path-type')?.value || 'none';
  if (textPathType && textPathType !== 'none') {
      this.showNotification('Curved text is a planned feature. Adding regular text for now.');
  }

  try {
    const fabricText = new fabric.Text(text.trim(), {
      fontFamily: font,
      fontSize: size,
      fill: color,
      left: this.clipBounds ? this.clipBounds.left + this.clipBounds.width / 2 : this.canvas.width / 2,
      top: this.clipBounds ? this.clipBounds.top + this.clipBounds.height / 2 : this.canvas.height / 2,
      originX: 'center',
      originY: 'center',
      angle: 0
    });

    console.log('Fabric text object created:', fabricText);

    this.canvas.add(fabricText);
    this.canvas.setActiveObject(fabricText);
    this.ensureProperLayering();
    this.canvas.renderAll();

    console.log('Text added to canvas successfully');

    this.hideTextEditor();
    this.showNotification('Text added successfully!');
    this.analytics.track('add_text', { length: text.length, font, size });

  } catch (error) {
    console.error('Error creating text:', error);
    this.showNotification('Error adding text. Please try again.', 'error');
  }
},


updateTextPreview() {
  // This could show a live preview of the text on the canvas
  // For now, just validate the input
  const textInput = document.getElementById('text-input');
  const addButton = document.getElementById('add-text-confirm');

  if (textInput && addButton) {
    const hasText = textInput.value.trim().length > 0;
    console.log('Updating text preview - hasText:', hasText, 'text:', textInput.value);

    addButton.disabled = !hasText;
    addButton.style.opacity = hasText ? '1' : '0.5';
    addButton.style.cursor = hasText ? 'pointer' : 'not-allowed';

    console.log('Button state - disabled:', addButton.disabled);
  } else {
    console.warn('Text input or add button not found');
  }
},


setupTextInputListeners() {
  const textInput = document.getElementById('text-input');
  if (textInput && !textInput.hasAttribute('data-listeners-added')) {
    console.log('Setting up text input listeners');

    textInput.addEventListener('input', () => {
      console.log('Text input changed:', textInput.value);
      this.updateTextPreview();
    });

    textInput.addEventListener('paste', () => {
      setTimeout(() => this.updateTextPreview(), 10);
    });

    // Mark as having listeners to avoid duplicates
    textInput.setAttribute('data-listeners-added', 'true');
  }
},


setupAdvancedTextControls() {
  const textPathSelect = document.querySelector('.text-path-type');
  if (textPathSelect) {
    textPathSelect.addEventListener('change', (e) => {
      const curveControls = document.querySelector('.text-curve-controls');
      if (curveControls) {
        curveControls.style.display = e.target.value !== 'none' ? 'block' : 'none';
      }
    });
  }
}
};
