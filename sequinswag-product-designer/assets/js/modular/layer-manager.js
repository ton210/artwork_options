/**
 * Layer Management Module
 * Handles proper layering and z-index management of canvas objects
 */

export class LayerManager {
  constructor(canvasManager) {
    this.canvasManager = canvasManager;
  }

  ensureProperLayering() {
    const canvas = this.canvasManager.getCanvas();
    if (!canvas) return;

    const allObjects = canvas.getObjects();
    const userObjects = allObjects.filter(obj => obj.selectable !== false);

    console.log('🔄 Arranging layers...');
    console.log('   Total objects:', allObjects.length, '| User objects:', userObjects.length);

    // Layer order: Background (bottom) → User content (middle) → Mask (top)

    // Step 1: Ensure background is at the bottom
    if (this.canvasManager.backgroundImage && allObjects.includes(this.canvasManager.backgroundImage)) {
      canvas.sendToBack(this.canvasManager.backgroundImage);
      console.log('   📍 Background moved to bottom');
    }

    // Step 2: Position all user objects above background
    userObjects.forEach((obj, index) => {
      if (this.canvasManager.backgroundImage && allObjects.includes(this.canvasManager.backgroundImage)) {
        const targetPosition = allObjects.indexOf(this.canvasManager.backgroundImage) + 1 + index;
        canvas.moveTo(obj, targetPosition);
        console.log(`   📍 User object ${index + 1} moved to position ${targetPosition + 1}`);
      }
    });

    // Step 3: Ensure mask is at the top
    if (this.canvasManager.maskImage && allObjects.includes(this.canvasManager.maskImage)) {
      canvas.bringToFront(this.canvasManager.maskImage);
      console.log('   📍 Mask moved to top');
    }

    console.log('✅ Layer arrangement complete');
  }

  addFixedLayers() {
    const canvas = this.canvasManager.getCanvas();
    if (!canvas) return;

    if (this.canvasManager.backgroundImage) {
      canvas.add(this.canvasManager.backgroundImage);
    }
    if (this.canvasManager.maskImage) {
      canvas.add(this.canvasManager.maskImage);
    }

    this.ensureProperLayering();
  }

  bringObjectToFront(obj) {
    const canvas = this.canvasManager.getCanvas();
    if (!canvas || !obj) return;

    // Bring to front but keep below mask
    if (this.canvasManager.maskImage) {
      const maskIndex = canvas.getObjects().indexOf(this.canvasManager.maskImage);
      canvas.moveTo(obj, maskIndex - 1);
    } else {
      canvas.bringToFront(obj);
    }
  }

  sendObjectToBack(obj) {
    const canvas = this.canvasManager.getCanvas();
    if (!canvas || !obj) return;

    // Send to back but keep above background
    if (this.canvasManager.backgroundImage) {
      const bgIndex = canvas.getObjects().indexOf(this.canvasManager.backgroundImage);
      canvas.moveTo(obj, bgIndex + 1);
    } else {
      canvas.sendToBack(obj);
    }
  }

  moveObjectUp(obj) {
    const canvas = this.canvasManager.getCanvas();
    if (!canvas || !obj) return;

    canvas.bringForward(obj);
    this.ensureProperLayering();
  }

  moveObjectDown(obj) {
    const canvas = this.canvasManager.getCanvas();
    if (!canvas || !obj) return;

    canvas.sendBackwards(obj);
    this.ensureProperLayering();
  }
}