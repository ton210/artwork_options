/**
 * Design save/load functionality
 * Part of Enhanced Product Designer
 * 
 * @module designMethods
 */

export const designMethods = {


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
},



loadSavedDesigns() {
  const saved = localStorage.getItem('customDesigns');
  return saved ? JSON.parse(saved) : [];
},



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
},



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
},



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
},



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
},



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
},


startAutoSave() {
  // Auto-save every 30 seconds
  this.autoSaveInterval = setInterval(() => {
    if (this.canvas && !this.isLoadingDesign) {
      this.saveSessionDesign();
    }
  }, 30000);
},



stopAutoSave() {
  if (this.autoSaveInterval) {
    clearInterval(this.autoSaveInterval);
    this.autoSaveInterval = null;
  }
}
};
