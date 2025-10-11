/**
 * Product variant handling
 * Part of Enhanced Product Designer
 * 
 * @module variantMethods
 */

export const variantMethods = {


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
},


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
},


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
},


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
},



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
},



generatePreviewFromTempCanvas(canvas) {
  if (!canvas) return '';
  const maxSize = 1200;
  const scale = Math.min(maxSize / canvas.width, maxSize / canvas.height, 1.5);
  return canvas.toDataURL({
    format: 'png',
    quality: 1,
    multiplier: scale
  });
},



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
};
