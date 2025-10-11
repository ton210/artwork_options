/**
 * Image cropping functionality
 * Part of Enhanced Product Designer
 * 
 * @module croppingMethods
 */

export const croppingMethods = {


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
},



cancelCrop() {
  if (this.croppingObject && this.canvas) {
    this.croppingObject.visible = true;
    this.canvas.renderAll();
  }
  this.cleanupCrop();
},



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
},



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
};
