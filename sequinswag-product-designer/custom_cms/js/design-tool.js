/**
 * Design Tool - Comprehensive Fabric.js based design tool
 * Adapted from SequinSwag WordPress plugin
 */
class DesignTool {
    constructor() {
        this.canvas = null;
        this.isInitialized = false;
        this.currentDesign = null;
        this.history = [];
        this.historyStep = -1;
        this.backgroundImage = null;
        this.maskImage = null;
        this.designArea = null;

        this.init();
    }

    async init() {
        this.setupEventListeners();
        await this.initializeCanvas();
    }

    setupEventListeners() {
        // Design tool buttons
        document.getElementById('customize-design-btn')?.addEventListener('click', () => {
            this.openDesigner();
        });

        document.getElementById('close-designer')?.addEventListener('click', () => {
            this.closeDesigner();
        });

        document.getElementById('cancel-design')?.addEventListener('click', () => {
            this.closeDesigner();
        });

        document.getElementById('apply-design')?.addEventListener('click', () => {
            this.applyDesign();
        });

        // Tool buttons
        document.getElementById('upload-image-btn')?.addEventListener('click', () => {
            document.getElementById('main-file-input')?.click();
        });

        document.getElementById('add-text-btn')?.addEventListener('click', () => {
            this.addText();
        });

        document.getElementById('undo-btn')?.addEventListener('click', () => {
            this.undo();
        });

        document.getElementById('redo-btn')?.addEventListener('click', () => {
            this.redo();
        });

        document.getElementById('delete-object-btn')?.addEventListener('click', () => {
            this.deleteSelected();
        });

        // File input
        document.getElementById('main-file-input')?.addEventListener('change', (e) => {
            this.handleImageUpload(e);
        });

        // Properties panel
        this.setupPropertiesPanel();
    }

    setupPropertiesPanel() {
        const opacitySlider = document.getElementById('opacity-slider');
        const fontSizeSlider = document.getElementById('font-size-slider');
        const textColorInput = document.getElementById('text-color');
        const textInput = document.getElementById('text-input');

        if (opacitySlider) {
            opacitySlider.addEventListener('input', (e) => {
                this.updateObjectOpacity(e.target.value / 100);
            });
        }

        if (fontSizeSlider) {
            fontSizeSlider.addEventListener('input', (e) => {
                this.updateTextFontSize(parseInt(e.target.value));
                document.getElementById('font-size-value').textContent = e.target.value + 'px';
            });
        }

        if (textColorInput) {
            textColorInput.addEventListener('change', (e) => {
                this.updateTextColor(e.target.value);
            });
        }

        if (textInput) {
            textInput.addEventListener('input', (e) => {
                this.updateTextContent(e.target.value);
            });
        }
    }

    async initializeCanvas() {
        if (this.isInitialized) return;

        try {
            // Wait for Fabric.js to be available
            if (typeof fabric === 'undefined') {
                console.error('Fabric.js not loaded');
                return;
            }

            // Initialize canvas
            this.canvas = new fabric.Canvas('design-canvas', {
                width: 400,
                height: 400,
                backgroundColor: '#ffffff',
                selection: true,
                preserveObjectStacking: true
            });

            // Setup canvas event listeners
            this.setupCanvasEvents();

            this.isInitialized = true;
            this.hideLoading();

        } catch (error) {
            console.error('Failed to initialize canvas:', error);
            this.showNotification('Failed to initialize design tool', 'error');
        }
    }

    setupCanvasEvents() {
        if (!this.canvas) return;

        // Selection events
        this.canvas.on('selection:created', (e) => {
            this.onObjectSelected(e.selected[0]);
        });

        this.canvas.on('selection:updated', (e) => {
            this.onObjectSelected(e.selected[0]);
        });

        this.canvas.on('selection:cleared', () => {
            this.onObjectDeselected();
        });

        // History events
        this.canvas.on('object:modified', () => {
            this.saveHistory();
        });

        this.canvas.on('object:added', (e) => {
            if (e.target && e.target.selectable !== false) {
                this.saveHistory();
            }
        });

        this.canvas.on('object:removed', (e) => {
            if (e.target && e.target.selectable !== false) {
                this.saveHistory();
            }
        });
    }

    async openDesigner() {
        const lightbox = document.getElementById('designer-lightbox');
        if (!lightbox) return;

        lightbox.classList.add('active');
        this.showLoading();

        // Initialize canvas if needed
        if (!this.isInitialized) {
            await this.initializeCanvas();
        }

        // Load variant design data if available
        const variantData = window.productManager?.getCurrentVariantDesignData();
        if (variantData) {
            await this.loadDesignData(variantData);
        } else {
            this.hideLoading();
        }
    }

    closeDesigner() {
        const lightbox = document.getElementById('designer-lightbox');
        if (lightbox) {
            lightbox.classList.remove('active');
        }

        this.onObjectDeselected();
    }

    async loadDesignData(designData) {
        if (!this.canvas || !designData) return;

        try {
            this.showLoading();

            // Clear existing objects
            this.canvas.clear();

            // Load background image
            if (designData.baseImage) {
                await this.loadBackgroundImage(designData.baseImage);
            }

            // Load mask image
            if (designData.alphaMask) {
                await this.loadMaskImage(designData.alphaMask);
            }

            // Set design area
            if (designData.designArea) {
                this.designArea = designData.designArea;
            }

            this.hideLoading();

        } catch (error) {
            console.error('Error loading design data:', error);
            this.showNotification('Error loading design template', 'error');
            this.hideLoading();
        }
    }

    loadBackgroundImage(imageUrl) {
        return new Promise((resolve, reject) => {
            fabric.Image.fromURL(imageUrl, (img) => {
                if (img) {
                    img.set({
                        selectable: false,
                        evented: false,
                        left: 0,
                        top: 0
                    });

                    this.backgroundImage = img;
                    this.canvas.add(img);
                    this.canvas.sendToBack(img);
                    this.canvas.renderAll();
                    resolve(img);
                } else {
                    reject(new Error('Failed to load background image'));
                }
            }, { crossOrigin: 'anonymous' });
        });
    }

    loadMaskImage(imageUrl) {
        return new Promise((resolve, reject) => {
            fabric.Image.fromURL(imageUrl, (img) => {
                if (img) {
                    img.set({
                        selectable: false,
                        evented: false,
                        left: 0,
                        top: 0,
                        opacity: 0.5
                    });

                    this.maskImage = img;
                    this.canvas.add(img);
                    this.canvas.renderAll();
                    resolve(img);
                } else {
                    reject(new Error('Failed to load mask image'));
                }
            }, { crossOrigin: 'anonymous' });
        });
    }

    handleImageUpload(event) {
        const file = event.target.files[0];
        if (!file) return;

        // Validate file type
        if (!file.type.match(/image.*/)) {
            this.showNotification('Please select an image file', 'error');
            return;
        }

        // Validate file size (5MB limit)
        if (file.size > 5 * 1024 * 1024) {
            this.showNotification('Image file too large. Please select a file under 5MB', 'error');
            return;
        }

        const reader = new FileReader();
        reader.onload = (e) => {
            this.addImageToCanvas(e.target.result);
        };
        reader.readAsDataURL(file);

        // Reset file input
        event.target.value = '';
    }

    addImageToCanvas(imageSrc) {
        fabric.Image.fromURL(imageSrc, (img) => {
            if (!img) {
                this.showNotification('Failed to load image', 'error');
                return;
            }

            // Scale image to fit canvas
            const canvasWidth = this.canvas.width;
            const canvasHeight = this.canvas.height;
            const maxSize = Math.min(canvasWidth, canvasHeight) * 0.6;

            const scale = Math.min(maxSize / img.width, maxSize / img.height);

            img.set({
                scaleX: scale,
                scaleY: scale,
                left: canvasWidth / 2,
                top: canvasHeight / 2,
                originX: 'center',
                originY: 'center'
            });

            this.canvas.add(img);
            this.canvas.setActiveObject(img);
            this.canvas.renderAll();

            this.showNotification('Image added successfully', 'success');
        }, { crossOrigin: 'anonymous' });
    }

    addText() {
        const text = new fabric.Text('Your Text Here', {
            left: this.canvas.width / 2,
            top: this.canvas.height / 2,
            originX: 'center',
            originY: 'center',
            fontSize: 24,
            fill: '#000000',
            fontFamily: 'Arial'
        });

        this.canvas.add(text);
        this.canvas.setActiveObject(text);
        this.canvas.renderAll();

        this.showNotification('Text added successfully', 'success');
    }

    onObjectSelected(obj) {
        if (!obj) return;

        const propertiesPanel = document.querySelector('.properties-panel');
        const textProperties = document.getElementById('text-properties');
        const deleteBtn = document.getElementById('delete-object-btn');

        if (propertiesPanel) {
            propertiesPanel.style.display = 'block';
        }

        if (deleteBtn) {
            deleteBtn.style.display = 'block';
        }

        // Update opacity slider
        const opacitySlider = document.getElementById('opacity-slider');
        const opacityValue = document.getElementById('opacity-value');
        if (opacitySlider && opacityValue) {
            const opacity = Math.round((obj.opacity || 1) * 100);
            opacitySlider.value = opacity;
            opacityValue.textContent = opacity + '%';
        }

        // Show text properties if it's a text object
        if (obj.type === 'text' && textProperties) {
            textProperties.style.display = 'block';

            // Update text input
            const textInput = document.getElementById('text-input');
            if (textInput) {
                textInput.value = obj.text || '';
            }

            // Update font size
            const fontSizeSlider = document.getElementById('font-size-slider');
            const fontSizeValue = document.getElementById('font-size-value');
            if (fontSizeSlider && fontSizeValue) {
                fontSizeSlider.value = obj.fontSize || 24;
                fontSizeValue.textContent = (obj.fontSize || 24) + 'px';
            }

            // Update text color
            const textColor = document.getElementById('text-color');
            if (textColor) {
                textColor.value = obj.fill || '#000000';
            }
        } else if (textProperties) {
            textProperties.style.display = 'none';
        }
    }

    onObjectDeselected() {
        const textProperties = document.getElementById('text-properties');
        const deleteBtn = document.getElementById('delete-object-btn');

        if (textProperties) {
            textProperties.style.display = 'none';
        }

        if (deleteBtn) {
            deleteBtn.style.display = 'none';
        }
    }

    updateObjectOpacity(opacity) {
        const activeObject = this.canvas?.getActiveObject();
        if (activeObject) {
            activeObject.set('opacity', opacity);
            this.canvas.renderAll();
            document.getElementById('opacity-value').textContent = Math.round(opacity * 100) + '%';
        }
    }

    updateTextContent(text) {
        const activeObject = this.canvas?.getActiveObject();
        if (activeObject && activeObject.type === 'text') {
            activeObject.set('text', text);
            this.canvas.renderAll();
        }
    }

    updateTextFontSize(fontSize) {
        const activeObject = this.canvas?.getActiveObject();
        if (activeObject && activeObject.type === 'text') {
            activeObject.set('fontSize', fontSize);
            this.canvas.renderAll();
        }
    }

    updateTextColor(color) {
        const activeObject = this.canvas?.getActiveObject();
        if (activeObject && activeObject.type === 'text') {
            activeObject.set('fill', color);
            this.canvas.renderAll();
        }
    }

    deleteSelected() {
        const activeObject = this.canvas?.getActiveObject();
        if (activeObject && activeObject.selectable !== false) {
            this.canvas.remove(activeObject);
            this.canvas.renderAll();
            this.showNotification('Object deleted', 'info');
        }
    }

    saveHistory() {
        if (!this.canvas) return;

        const state = JSON.stringify(this.canvas.toJSON(['selectable', 'evented']));

        // Remove any history after current step
        this.history = this.history.slice(0, this.historyStep + 1);

        // Add new state
        this.history.push(state);
        this.historyStep++;

        // Limit history size
        if (this.history.length > 50) {
            this.history.shift();
            this.historyStep--;
        }
    }

    undo() {
        if (this.historyStep > 0) {
            this.historyStep--;
            this.loadCanvasState(this.history[this.historyStep]);
            this.showNotification('Undone', 'info');
        }
    }

    redo() {
        if (this.historyStep < this.history.length - 1) {
            this.historyStep++;
            this.loadCanvasState(this.history[this.historyStep]);
            this.showNotification('Redone', 'info');
        }
    }

    loadCanvasState(state) {
        if (!this.canvas || !state) return;

        this.canvas.loadFromJSON(state, () => {
            this.canvas.renderAll();

            // Reapply non-selectable properties to background and mask
            const objects = this.canvas.getObjects();
            objects.forEach((obj, index) => {
                if (index === 0 && this.backgroundImage) {
                    obj.selectable = false;
                    obj.evented = false;
                } else if (index === 1 && this.maskImage) {
                    obj.selectable = false;
                    obj.evented = false;
                }
            });
        });
    }

    applyDesign() {
        if (!this.canvas) return;

        const userObjects = this.canvas.getObjects().filter(obj => obj.selectable !== false);

        if (userObjects.length === 0) {
            this.showNotification('Please add at least one element to your design', 'error');
            return;
        }

        try {
            // Generate preview image
            const previewDataUrl = this.canvas.toDataURL({
                format: 'png',
                quality: 0.8
            });

            // Save design data
            const designData = JSON.stringify(this.canvas.toJSON(['selectable', 'evented']));

            // Update product manager
            if (window.productManager) {
                window.productManager.showDesignPreview(previewDataUrl);
            }

            // Store design data for potential cart submission
            this.currentDesign = {
                data: designData,
                preview: previewDataUrl,
                timestamp: Date.now()
            };

            this.showNotification('Design applied successfully!', 'success');
            this.closeDesigner();

        } catch (error) {
            console.error('Error applying design:', error);
            this.showNotification('Error applying design. Please try again.', 'error');
        }
    }

    clearDesign() {
        if (this.canvas) {
            const userObjects = this.canvas.getObjects().filter(obj => obj.selectable !== false);
            userObjects.forEach(obj => this.canvas.remove(obj));
            this.canvas.renderAll();
        }

        this.currentDesign = null;
        this.history = [];
        this.historyStep = -1;
    }

    showLoading() {
        const loading = document.getElementById('loading-overlay');
        if (loading) {
            loading.style.display = 'flex';
        }
    }

    hideLoading() {
        const loading = document.getElementById('loading-overlay');
        if (loading) {
            loading.style.display = 'none';
        }
    }

    showNotification(message, type = 'info') {
        // Remove existing notifications
        const existing = document.querySelectorAll('.notification');
        existing.forEach(notif => notif.remove());

        // Create notification
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;

        document.body.appendChild(notification);

        // Auto-remove after 3 seconds
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    getCurrentDesign() {
        return this.currentDesign;
    }
}

// Initialize when DOM is loaded
if (typeof window !== 'undefined') {
    window.designTool = new DesignTool();
}