/**
 * UI controls and panels
 * Part of Enhanced Product Designer
 * 
 * @module controlMethods
 */

export const controlMethods = {

setupLayersPanel() {
  const layersPanel = document.createElement('div');
  layersPanel.className = 'layers-panel';
  layersPanel.innerHTML = `
    <h3>Layers</h3>
    <div class="layers-list" id="layers-list"></div>
  `;

  const propertiesPanel = document.querySelector('.properties-panel');
  if (propertiesPanel) {
    propertiesPanel.appendChild(layersPanel);
  }

  if (this.canvas) {
    this.canvas.on('object:added', () => this.updateLayersPanel());
    this.canvas.on('object:removed', () => this.updateLayersPanel());
    this.canvas.on('object:modified', () => this.updateLayersPanel());
  }
},



updateLayersPanel() {
  const layersList = document.getElementById('layers-list');
  if (!layersList || !this.canvas) return;

  layersList.innerHTML = '';
  const objects = this.canvas.getObjects().filter(obj => obj.selectable !== false);

  objects.reverse().forEach((obj, index) => {
    const layerItem = document.createElement('div');
    layerItem.className = 'layer-item';
    layerItem.dataset.index = objects.length - 1 - index;

    const isSelected = this.canvas.getActiveObjects().includes(obj);
    if (isSelected) layerItem.classList.add('selected');

    layerItem.innerHTML = `
      <div class="layer-controls">
        <button class="layer-visibility" title="Toggle visibility">
          <svg width="16" height="16" viewBox="0 0 24 24">
            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
          </svg>
        </button>
        <button class="layer-lock" title="Toggle lock">
          <svg width="16" height="16" viewBox="0 0 24 24">
            <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>
          </svg>
        </button>
      </div>
      <span class="layer-name">${obj.type === 'text' ? obj.text : obj.type}</span>
      <div class="layer-reorder">
        <button class="layer-up" title="Move up">↑</button>
        <button class="layer-down" title="Move down">↓</button>
      </div>
    `;

    layerItem.addEventListener('click', (e) => {
      if (!e.target.closest('button')) {
        this.canvas.setActiveObject(obj);
        this.canvas.renderAll();
        this.updateLayersPanel();
      }
    });

    layerItem.querySelector('.layer-visibility').addEventListener('click', () => {
      obj.visible = !obj.visible;
      this.canvas.renderAll();
      layerItem.classList.toggle('hidden');
    });

    layerItem.querySelector('.layer-lock').addEventListener('click', () => {
      obj.selectable = !obj.selectable;
      obj.evented = !obj.evented;
      layerItem.classList.toggle('locked');
    });

    layerItem.querySelector('.layer-up').addEventListener('click', () => {
      const currentIndex = objects.length - 1 - index;
      if (currentIndex < objects.length - 1) {
        obj.bringForward();
        this.updateLayersPanel();
      }
    });

    layerItem.querySelector('.layer-down').addEventListener('click', () => {
      const currentIndex = objects.length - 1 - index;
      if (currentIndex > 0) {
        obj.sendBackwards();
        this.updateLayersPanel();
      }
    });

    layersList.appendChild(layerItem);
  });
},


setupAlignmentTools() {
  const alignmentPanel = document.createElement('div');
  alignmentPanel.className = 'alignment-tools';
  alignmentPanel.style.display = 'none';
  alignmentPanel.innerHTML = `
    <h4>Alignment</h4>
    <div class="alignment-buttons">
      <button class="align-btn" data-align="left" title="Align left">
        <svg width="20" height="20" viewBox="0 0 24 24"><path d="M4 4v16h2V4H4zm4 12h12v-2H8v2zm0-6h12V8H8v2z"/></svg>
      </button>
      <button class="align-btn" data-align="center-h" title="Align center horizontally">
        <svg width="20" height="20" viewBox="0 0 24 24"><path d="M11 4v5H6v2h5v2H8v2h3v5h2v-5h3v-2h-3v-2h5V9h-5V4h-2z"/></svg>
      </button>
      <button class="align-btn" data-align="right" title="Align right">
        <svg width="20" height="20" viewBox="0 0 24 24"><path d="M18 4v16h2V4h-2zM4 16h12v-2H4v2zm0-6h12V8H4v2z"/></svg>
      </button>
      <button class="align-btn" data-align="top" title="Align top">
        <svg width="20" height="20" viewBox="0 0 24 24"><path d="M4 4h16v2H4V4zm12 4v12h-2V8h2zm-6 0v12H8V8h2z"/></svg>
      </button>
      <button class="align-btn" data-align="center-v" title="Align center vertically">
        <svg width="20" height="20" viewBox="0 0 24 24"><path d="M4 11h5V6h2v5h2V8h2v3h5v2h-5v3h-2v-3h-2v5H9v-5H4v-2z"/></svg>
      </button>
      <button class="align-btn" data-align="bottom" title="Align bottom">
        <svg width="20" height="20" viewBox="0 0 24 24"><path d="M4 18h16v2H4v-2zm12-14v12h-2V4h2zm-6 0v12H8V4h2z"/></svg>
      </button>
    </div>
    <h4>Distribution</h4>
    <div class="distribution-buttons">
      <button class="distribute-btn" data-distribute="h" title="Distribute horizontally">
        <svg width="20" height="20" viewBox="0 0 24 24"><path d="M4 4v16h2V4H4zm14 0v16h2V4h-2zm-6 4v8h2V8h-2z"/></svg>
      </button>
      <button class="distribute-btn" data-distribute="v" title="Distribute vertically">
        <svg width="20" height="20" viewBox="0 0 24 24"><path d="M4 4h16v2H4V4zm0 14h16v2H4v-2zm4-6h8v2H8v-2z"/></svg>
      </button>
    </div>
  `;

  const propertiesPanel = document.querySelector('.properties-panel');
  if (propertiesPanel) {
    propertiesPanel.appendChild(alignmentPanel);
  }

  alignmentPanel.querySelectorAll('.align-btn').forEach(btn => {
    btn.addEventListener('click', () => this.alignObjects(btn.dataset.align));
  });

  alignmentPanel.querySelectorAll('.distribute-btn').forEach(btn => {
    btn.addEventListener('click', () => this.distributeObjects(btn.dataset.distribute));
  });

  if (this.canvas) {
    this.canvas.on('selection:created', () => this.updateAlignmentTools());
    this.canvas.on('selection:updated', () => this.updateAlignmentTools());
    this.canvas.on('selection:cleared', () => this.updateAlignmentTools());
  }
},



updateAlignmentTools() {
  const alignmentTools = document.querySelector('.alignment-tools');
  if (!alignmentTools) return;

  const activeObjects = this.canvas.getActiveObjects();
  alignmentTools.style.display = activeObjects.length > 1 && !activeObjects.some(obj => obj === this.backgroundImage || obj === this.maskImage || obj === this.unclippedMaskImage) ? 'block' : 'none';
},



alignObjects(alignment) {
  const activeObjects = this.canvas.getActiveObjects().filter(obj => obj.selectable !== false);
  if (activeObjects.length < 2) return;

  const group = new fabric.ActiveSelection(activeObjects, { canvas: this.canvas });
  const groupBounds = group.getBoundingRect();
  this.canvas.discardActiveObject();

  activeObjects.forEach(obj => {
    switch(alignment) {
      case 'left':
        obj.set('left', groupBounds.left + (obj.width * obj.scaleX / 2));
        break;
      case 'center-h':
        obj.set('left', groupBounds.left + (groupBounds.width / 2));
        break;
      case 'right':
        obj.set('left', groupBounds.left + groupBounds.width - (obj.width * obj.scaleX / 2));
        break;
      case 'top':
        obj.set('top', groupBounds.top + (obj.height * obj.scaleY / 2));
        break;
      case 'center-v':
        obj.set('top', groupBounds.top + (groupBounds.height / 2));
        break;
      case 'bottom':
        obj.set('top', groupBounds.top + groupBounds.height - (obj.height * obj.scaleY / 2));
        break;
    }
    obj.setCoords();
  });

  this.canvas.setActiveObject(new fabric.ActiveSelection(activeObjects, { canvas: this.canvas }));
  this.canvas.renderAll();
  this.saveHistory();
  this.analytics.track('align_objects', { alignment, count: activeObjects.length });
},



distributeObjects(direction) {
  const activeObjects = this.canvas.getActiveObjects().filter(obj => obj.selectable !== false);
  if (activeObjects.length < 3) return;

  if (direction === 'h') {
    activeObjects.sort((a, b) => a.left - b.left);
    const minX = activeObjects[0].left;
    const maxX = activeObjects[activeObjects.length - 1].left;
    const totalSpace = maxX - minX;
    const spacing = totalSpace / (activeObjects.length - 1);

    activeObjects.forEach((obj, index) => {
      obj.set('left', minX + spacing * index);
      obj.setCoords();
    });
  } else {
    activeObjects.sort((a, b) => a.top - b.top);
    const minY = activeObjects[0].top;
    const maxY = activeObjects[activeObjects.length - 1].top;
    const totalSpace = maxY - minY;
    const spacing = totalSpace / (activeObjects.length - 1);

    activeObjects.forEach((obj, index) => {
      obj.set('top', minY + spacing * index);
      obj.setCoords();
    });
  }

  this.canvas.setActiveObject(new fabric.ActiveSelection(activeObjects, { canvas: this.canvas }));
  this.canvas.renderAll();
  this.saveHistory();
  this.analytics.track('distribute_objects', { direction, count: activeObjects.length });
},


setupCanvasControls() {
  if (!this.canvas) return;

  // Custom controls for fabric objects
  fabric.Object.prototype.set({
    transparentCorners: false,
    cornerColor: '#ff4444',
    cornerStrokeColor: '#fff',
    borderColor: '#0073aa',
    cornerSize: window.innerWidth <= 768 ? 16 : 12,
    cornerStyle: 'circle',
    borderScaleFactor: 2,
    borderDashArray: [5, 5],
    rotatingPointOffset: 40
  });

  // Delete control icon
  const deleteIcon = "data:image/svg+xml,%3Csvg height='20' width='20' viewBox='0 0 512 512' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M128 405.429C128 428.846 147.198 448 170.667 448h170.667C364.802 448 384 428.846 384 405.429V160H128v245.429zM416 96h-80l-26.785-32H202.786L176 96H96v32h320V96z' fill='%23ffffff'/%3E%3C/svg%3E";

  const deleteImg = document.createElement('img');
  deleteImg.src = deleteIcon;

  // Custom delete control
  fabric.Object.prototype.controls.deleteControl = new fabric.Control({
    x: 0.5,
    y: -0.5,
    offsetY: -20,
    offsetX: 20,
    cursorStyle: 'pointer',
    mouseUpHandler: (eventData, transform) => {
      const target = transform.target;
      const canvas = target.canvas;

      // Show confirmation on mobile
      if (window.innerWidth <= 768) {
        if (confirm('Delete this item?')) {
          canvas.remove(target);
          canvas.requestRenderAll();
        }
      } else {
        canvas.remove(target);
        canvas.requestRenderAll();
      }

      this.saveHistory();
      this.showNotification('Item deleted');
      return true;
    },
    render: function(ctx, left, top, styleOverride, fabricObject) {
      const size = 24;
      ctx.save();
      ctx.fillStyle = '#ff4444';
      ctx.beginPath();
      ctx.arc(left, top, size/2, 0, 2 * Math.PI);
      ctx.fill();

      ctx.strokeStyle = '#ffffff';
      ctx.lineWidth = 2;
      ctx.stroke();

      if (deleteImg.complete) {
        ctx.drawImage(deleteImg, left - size/2 + 2, top - size/2 + 2, size - 4, size - 4);
      }

      ctx.restore();
    },
    cornerSize: 24
  });

  // Enhanced rotation control
  const rotateIcon = "data:image/svg+xml,%3Csvg width='20' height='20' viewBox='0 0 24 24' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z' fill='%23ffffff'/%3E%3C/svg%3E";

  fabric.Object.prototype.controls.mtr = new fabric.Control({
    x: 0,
    y: -0.5,
    offsetY: -40,
    cursorStyle: 'crosshair',
    actionHandler: fabric.controlsUtils.rotationWithSnapping,
    actionName: 'rotate',
    render: function(ctx, left, top, styleOverride, fabricObject) {
      const size = 24;
      ctx.save();
      ctx.fillStyle = '#0073aa';
      ctx.beginPath();
      ctx.arc(left, top, size/2, 0, 2 * Math.PI);
      ctx.fill();

      ctx.strokeStyle = '#ffffff';
      ctx.lineWidth = 2;
      ctx.stroke();

      // Draw rotation icon
      const rotateImg = new Image();
      rotateImg.src = rotateIcon;
      if (rotateImg.complete) {
        ctx.drawImage(rotateImg, left - size/2 + 2, top - size/2 + 2, size - 4, size - 4);
      }

      ctx.restore();
    },
    cornerSize: 24,
    withConnection: true
  });

  // Add opacity slider on selection
  this.canvas.on('selection:created', (e) => this.showInlineControls(e.selected[0]));
  this.canvas.on('selection:updated', (e) => this.showInlineControls(e.selected[0]));
  this.canvas.on('selection:cleared', () => this.hideInlineControls());
},



showInlineControls(object) {
  if (!object || object === this.backgroundImage || object === this.maskImage || object === this.unclippedMaskImage) return;

  this.hideInlineControls();

  const controlsDiv = document.createElement('div');
  controlsDiv.id = 'inline-controls';
  controlsDiv.className = 'inline-controls';
  controlsDiv.innerHTML = `
    <div class="inline-control-item">
      <label>Opacity:</label>
      <input type="range" class="inline-opacity" min="0" max="100" value="${object.opacity * 100}">
      <span class="inline-value">${Math.round(object.opacity * 100)}%</span>
    </div>
    ${object.type === 'image' ? `
      <div class="inline-control-item">
        <button class="btn btn-sm btn-danger" id="inline-delete">
          <svg width="16" height="16" viewBox="0 0 24 24">
            <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z" fill="currentColor"/>
          </svg>
          Delete
        </button>
      </div>
    ` : ''}
  `;

  const canvasContainer = document.querySelector('.canvas-container');
  if (canvasContainer) {
    canvasContainer.appendChild(controlsDiv);

    const opacitySlider = controlsDiv.querySelector('.inline-opacity');
    const opacityValue = controlsDiv.querySelector('.inline-value');

    opacitySlider.addEventListener('input', (e) => {
      object.set('opacity', e.target.value / 100);
      opacityValue.textContent = e.target.value + '%';
      this.canvas.renderAll();
    });

    // Add delete button functionality
    const deleteBtn = controlsDiv.querySelector('#inline-delete');
    if (deleteBtn) {
      deleteBtn.addEventListener('click', () => {
        if (window.innerWidth <= 768 && !confirm('Delete this image?')) {
          return;
        }
        this.canvas.remove(object);
        this.canvas.renderAll();
        this.hideInlineControls();
        this.saveHistory();
        this.showNotification('Image deleted');
      });
    }
  }
},



hideInlineControls() {
  const controls = document.getElementById('inline-controls');
  if (controls) controls.remove();
},


adjustLayoutForDesktop() {
  if (window.innerWidth > 768) {
    const propsPanel = document.querySelector('.properties-panel');
    const canvasContainer = document.querySelector('.canvas-container');

    if (propsPanel) {
      propsPanel.style.display = 'none';
    }

    // Adjust canvas container to take full width without properties panel
    if (canvasContainer) {
      canvasContainer.style.marginRight = '20px';
    }
  }
}
};
