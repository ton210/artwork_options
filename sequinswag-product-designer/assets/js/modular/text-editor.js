/**
 * Text Editor Module
 * Handles text addition, editing, and formatting
 */

export class TextEditor {
  constructor(canvasManager, layerManager, notificationManager, analytics) {
    this.canvasManager = canvasManager;
    this.layerManager = layerManager;
    this.notifications = notificationManager;
    this.analytics = analytics;
    this.isTextEditorVisible = false;
  }

  showTextEditor() {
    const modal = document.getElementById('text-editor-modal');
    if (modal) {
      modal.style.display = 'flex';
      this.isTextEditorVisible = true;

      // Focus on text input
      const textInput = document.getElementById('text-input');
      if (textInput) {
        textInput.focus();
      }
    }
  }

  hideTextEditor() {
    const modal = document.getElementById('text-editor-modal');
    if (modal) {
      modal.style.display = 'none';
      this.isTextEditorVisible = false;

      // Clear form
      this.clearTextForm();
    }
  }

  clearTextForm() {
    const textInput = document.getElementById('text-input');
    const fontSelect = document.getElementById('font-family');
    const sizeInput = document.getElementById('font-size');
    const colorInput = document.getElementById('text-color');

    if (textInput) textInput.value = '';
    if (fontSelect) fontSelect.value = 'Arial';
    if (sizeInput) sizeInput.value = '24';
    if (colorInput) colorInput.value = '#000000';
  }

  addText() {
    const textInput = document.getElementById('text-input');
    const fontSelect = document.getElementById('font-family');
    const sizeInput = document.getElementById('font-size');
    const colorInput = document.getElementById('text-color');

    if (!textInput || !textInput.value.trim()) {
      this.notifications.show('Please enter some text', 'warning');
      return;
    }

    const canvas = this.canvasManager.getCanvas();
    if (!canvas) return;

    try {
      const text = textInput.value.trim();
      const font = fontSelect ? fontSelect.value : 'Arial';
      const size = sizeInput ? parseInt(sizeInput.value) : 24;
      const color = colorInput ? colorInput.value : '#000000';

      const fabricText = new fabric.Text(text, {
        left: this.canvasManager.clipBounds ?
              this.canvasManager.clipBounds.left + this.canvasManager.clipBounds.width / 2 :
              canvas.width / 2,
        top: this.canvasManager.clipBounds ?
             this.canvasManager.clipBounds.top + this.canvasManager.clipBounds.height / 2 :
             canvas.height / 2,
        fontFamily: font,
        fontSize: size,
        fill: color,
        originX: 'center',
        originY: 'center',
        textAlign: 'center'
      });

      canvas.add(fabricText);
      canvas.setActiveObject(fabricText);
      this.layerManager.ensureProperLayering();
      canvas.renderAll();

      this.hideTextEditor();
      this.notifications.show('Text added successfully!');
      this.analytics.track('add_text', { length: text.length, font, size });

    } catch (error) {
      console.error('Error creating text:', error);
      this.notifications.show('Error adding text. Please try again.', 'error');
    }
  }

  setupTextEditorListeners() {
    // Add text button
    document.querySelector('.add-text-btn')?.addEventListener('click', () => this.showTextEditor());

    // Text editor buttons
    document.getElementById('add-text-confirm')?.addEventListener('click', () => this.addText());
    document.getElementById('cancel-text')?.addEventListener('click', () => this.hideTextEditor());

    // Modal close
    document.querySelector('.modal-close')?.addEventListener('click', () => this.hideTextEditor());

    // Mobile text button
    document.getElementById('mobile-text-btn')?.addEventListener('click', () => this.showTextEditor());

    // Enter key support
    document.getElementById('text-input')?.addEventListener('keypress', (e) => {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        this.addText();
      }
    });
  }
}