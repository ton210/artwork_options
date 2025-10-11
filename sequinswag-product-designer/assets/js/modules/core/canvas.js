/**
 * Canvas operations and management
 * Part of Enhanced Product Designer
 * 
 * @module canvasMethods
 */

export const canvasMethods = {


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
},



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
},



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
},



addFixedLayers() {
  if (this.backgroundImage && !this.canvas.contains(this.backgroundImage)) {
    this.canvas.add(this.backgroundImage);
    this.backgroundImage.sendToBack();
  }
  if (this.maskImage && !this.canvas.contains(this.maskImage)) {
    this.canvas.add(this.maskImage);
  }
}
};
