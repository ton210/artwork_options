/**
 * Cart and checkout integration
 * Part of Enhanced Product Designer
 * 
 * @module cartMethods
 */

export const cartMethods = {


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
},


loadDesignFromCartData(productUrl, variantId, canvasData) {
  sessionStorage.setItem('edit_design_data', canvasData);
  sessionStorage.setItem('edit_design_variant', variantId);
  window.location.href = `${productUrl}?edit_design=1&variant=${variantId}`;
},

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
},

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
};
