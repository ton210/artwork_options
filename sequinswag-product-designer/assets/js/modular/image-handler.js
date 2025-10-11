/**
 * Image Upload and Handling Module
 * Handles image uploads, processing, and canvas integration
 */

import { Utilities } from './utilities.js';

export class ImageHandler {
  constructor(canvasManager, layerManager, notificationManager, analytics) {
    this.canvasManager = canvasManager;
    this.layerManager = layerManager;
    this.notifications = notificationManager;
    this.analytics = analytics;

    // Upload state management
    this.isUploadingImage = false;
    this.currentUploadId = null;
  }

  async handleImageUpload(event) {
    const files = event.target.files;
    if (!files || files.length === 0) {
      this.isUploadingImage = false;
      return;
    }

    // Prevent duplicate uploads
    if (this.isUploadingImage) {
      console.warn('⚠️ Upload flag was stuck, forcing reset');
      this.isUploadingImage = false;
      this.currentUploadId = null;
    }

    // Generate unique upload ID
    const uploadId = Utilities.generateId();
    this.currentUploadId = uploadId;
    this.isUploadingImage = true;

    console.log('🚀 Starting image upload process', {
      uploadId: uploadId.substring(0, 10) + '...',
      fileCount: files.length,
      fileName: files[0].name
    });

    // Set safety timeout
    const uploadTimeout = setTimeout(() => {
      if (this.currentUploadId === uploadId) {
        console.warn('⏱️ Upload timeout reached, resetting flags');
        this.isUploadingImage = false;
        this.currentUploadId = null;
        this.notifications.show('Upload timed out. Please try again.', 'error');
      }
    }, 10000);

    for (const file of files) {
      try {
        // Validate file
        Utilities.validateImageFile(file);

        // Convert to base64
        const base64 = await Utilities.fileToBase64(file);

        // Create fabric image
        await this.createFabricImage(base64, uploadId, uploadTimeout, file);

      } catch (error) {
        console.error('Error uploading image:', error);
        this.notifications.show(error.message || 'Error uploading image. Please try again.', 'error');
        clearTimeout(uploadTimeout);
        this.isUploadingImage = false;
      }
    }

    // Clear file input
    event.target.value = '';
  }

  async createFabricImage(base64, uploadId, uploadTimeout, file) {
    return new Promise((resolve, reject) => {
      fabric.Image.fromURL(base64, (img) => {
        console.log('🖼️ Fabric.js image creation callback fired');

        // Check if image creation failed
        if (!img || !img.width || !img.height) {
          console.error('❌ Fabric.js failed to create image');
          clearTimeout(uploadTimeout);
          if (this.currentUploadId === uploadId) {
            this.isUploadingImage = false;
            this.currentUploadId = null;
          }
          this.notifications.show('Failed to process image. Please try again.', 'error');
          reject(new Error('Image creation failed'));
          return;
        }

        // Double-check upload flag
        if (!this.isUploadingImage) {
          console.warn('⚠️ Upload flag was false in callback, resetting');
          clearTimeout(uploadTimeout);
          reject(new Error('Upload flag reset'));
          return;
        }

        // Configure image
        const canvas = this.canvasManager.getCanvas();
        const maxSize = Math.min(canvas.width, canvas.height) * 0.8;
        const scale = Math.min(maxSize / img.width, maxSize / img.height);

        img.scale(scale);
        img.set({
          left: this.canvasManager.clipBounds ?
                this.canvasManager.clipBounds.left + this.canvasManager.clipBounds.width / 2 :
                canvas.width / 2,
          top: this.canvasManager.clipBounds ?
               this.canvasManager.clipBounds.top + this.canvasManager.clipBounds.height / 2 :
               canvas.height / 2,
          originX: 'center',
          originY: 'center',
          uploadId: uploadId
        });

        // Check for duplicates
        const existingImages = canvas.getObjects('image').filter(img => img.selectable !== false);
        const isDuplicate = existingImages.some(existingImg => {
          try {
            return existingImg.getSrc && img.getSrc && existingImg.getSrc() === img.getSrc();
          } catch (error) {
            return false;
          }
        });

        if (isDuplicate) {
          clearTimeout(uploadTimeout);
          this.isUploadingImage = false;
          reject(new Error('Duplicate image'));
          return;
        }

        // Add to canvas
        canvas.add(img);
        canvas.setActiveObject(img);
        canvas.renderAll();

        console.log('✅ Image uploaded successfully');
        console.log('📊 Canvas objects before layering:', canvas.getObjects().length);

        // Ensure proper layering
        setTimeout(() => {
          this.layerManager.ensureProperLayering();
          canvas.renderAll();

          // Log final position
          const finalObjects = canvas.getObjects();
          const imageIndex = finalObjects.indexOf(img);
          console.log('🎯 Image placed at layer position:', imageIndex + 1, 'of', finalObjects.length);

          // Show layer structure
          finalObjects.forEach((obj, index) => {
            let type = 'user-content';
            if (obj === this.canvasManager.backgroundImage) type = 'background';
            else if (obj === this.canvasManager.maskImage) type = 'mask';
            else if (obj.selectable === false) type = 'system';

            const isNewImage = obj === img ? ' ← NEW IMAGE' : '';
            console.log(`  Layer ${index + 1}: ${type}${isNewImage}`);
          });
        }, 50);

        // Track analytics
        this.analytics.track('upload_image', {
          fileSize: file.size,
          fileType: file.type
        });

        // Clear timeout and reset flags
        clearTimeout(uploadTimeout);
        if (this.currentUploadId === uploadId) {
          this.isUploadingImage = false;
          this.currentUploadId = null;
        }

        resolve(img);
      });
    });
  }

  setupUploadListeners() {
    // Remove existing listeners to prevent duplicates
    document.querySelectorAll('.image-upload-input').forEach(input => {
      const newInput = input.cloneNode(true);
      input.parentNode.replaceChild(newInput, input);
      newInput.addEventListener('change', (e) => this.handleImageUpload(e));
    });

    // Main file input
    const mainFileInput = document.getElementById('main-file-input');
    if (mainFileInput && !mainFileInput.hasAttribute('data-listener-added')) {
      mainFileInput.addEventListener('change', (e) => this.handleImageUpload(e));
      mainFileInput.setAttribute('data-listener-added', 'true');
    }

    // Mobile file input
    const mobileFileInput = document.getElementById('mobile-file-input');
    if (mobileFileInput && !mobileFileInput.hasAttribute('data-listener-added')) {
      mobileFileInput.addEventListener('change', (e) => this.handleImageUpload(e));
      mobileFileInput.setAttribute('data-listener-added', 'true');
    }
  }
}