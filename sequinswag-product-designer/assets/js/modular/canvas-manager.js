/**
 * Canvas Management Module
 * Handles canvas creation, setup, and basic operations
 */

export class CanvasManager {
  constructor() {
    this.canvas = null;
    this.backgroundImage = null;
    this.maskImage = null;
    this.unclippedMaskImage = null;
    this.clipBounds = null;
  }

  async setupCanvas() {
    return new Promise((resolve, reject) => {
      try {
        // Create canvas element if it doesn't exist
        let canvasElement = document.getElementById('designer-canvas');
        if (!canvasElement) {
          canvasElement = document.createElement('canvas');
          canvasElement.id = 'designer-canvas';

          const container = document.querySelector('.canvas-container');
          if (container) {
            container.appendChild(canvasElement);
          } else {
            reject(new Error('Canvas container not found'));
            return;
          }
        }

        // Calculate initial canvas size
        const container = document.querySelector('.canvas-container');
        let canvasSize = 500;

        if (container) {
          const containerSize = Math.min(container.offsetWidth, container.offsetHeight) - 40;
          canvasSize = Math.max(containerSize, 300);
        }

        // Initialize Fabric.js canvas
        this.canvas = new fabric.Canvas('designer-canvas', {
          width: canvasSize,
          height: canvasSize,
          backgroundColor: '#ffffff',
          selection: true,
          preserveObjectStacking: true,
          renderOnAddRemove: false,
          skipTargetFind: false,
          imageSmoothingEnabled: true,
          allowTouchScrolling: false
        });

        // Set canvas properties
        fabric.Object.prototype.cornerColor = '#007cba';
        fabric.Object.prototype.cornerStyle = 'circle';
        fabric.Object.prototype.borderColor = '#007cba';
        fabric.Object.prototype.cornerStrokeColor = '#007cba';
        fabric.Object.prototype.transparentCorners = false;
        fabric.Image.prototype.crossOrigin = 'anonymous';

        resolve();
      } catch (error) {
        reject(error);
      }
    });
  }

  handleResize() {
    if (!this.canvas) return;

    const container = document.querySelector('.canvas-container');
    if (!container) return;

    const isMobile = window.innerWidth <= 768;
    const padding = isMobile ? 20 : 40;
    const containerWidth = container.offsetWidth - padding;
    const containerHeight = container.offsetHeight - padding;
    let canvasSize = Math.min(containerWidth, containerHeight);

    if (isMobile) {
      canvasSize = Math.max(canvasSize, 280);
    } else {
      canvasSize = Math.max(canvasSize, 500);
      canvasSize = Math.min(canvasSize, 800);
    }

    const currentSize = this.canvas.width;
    const scaleFactor = canvasSize / currentSize;

    this.canvas.setDimensions({
      width: canvasSize,
      height: canvasSize
    });

    this.canvas.setZoom(this.canvas.getZoom() * scaleFactor);

    if (this.backgroundImage) {
      this.backgroundImage.center();
      if (this.maskImage) {
        this.maskImage.center();
      }
    }

    this.canvas.renderAll();
  }

  setupClipBounds() {
    if (!this.maskImage || !this.canvas) return;

    try {
      const maskBounds = this.maskImage.getBoundingRect();
      this.clipBounds = {
        left: maskBounds.left,
        top: maskBounds.top,
        width: maskBounds.width,
        height: maskBounds.height
      };

      console.log('🔧 Clip bounds set:', this.clipBounds);
    } catch (error) {
      console.error('Error setting up clip bounds:', error);
      this.clipBounds = null;
    }
  }

  clear() {
    if (this.canvas) {
      this.canvas.clear();
      this.backgroundImage = null;
      this.maskImage = null;
      this.unclippedMaskImage = null;
      this.clipBounds = null;
    }
  }

  dispose() {
    if (this.canvas) {
      this.canvas.dispose();
      this.canvas = null;
    }
  }

  getCanvas() {
    return this.canvas;
  }

  getUserObjects() {
    if (!this.canvas) return [];
    return this.canvas.getObjects().filter(obj => obj.selectable !== false);
  }

  getCanvasDataURL(options = {}) {
    if (!this.canvas) return null;

    const defaultOptions = {
      format: 'png',
      quality: 0.9,
      multiplier: 1
    };

    return this.canvas.toDataURL({
      ...defaultOptions,
      ...options
    });
  }
}