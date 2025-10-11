/**
 * Lightbox and modal functionality
 * Part of Enhanced Product Designer
 * 
 * @module lightboxMethods
 */

export const lightboxMethods = {


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
},



openLightboxWithVariant(variantId, variants) {
  this.openLightbox(variantId);
},



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
},



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
},



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
};
