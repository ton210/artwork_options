/**
 * Image handling and uploads
 * Part of Enhanced Product Designer
 * 
 * @module imageMethods
 */

export const imageMethods = {

fileToBase64(file) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = (e) => resolve(e.target.result);
    reader.onerror = reject;
    reader.readAsDataURL(file);
  });
},


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
},

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
},

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
  },

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
},

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
},

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
};
