/**
 * ImageLoader Helper Class
 */
class ImageLoader {
  constructor() {
    this.queue = [];
    this.loading = false;
    this.maxConcurrent = 3;
    this.activeLoads = 0;
  }

  // NEW: Create fallback lightbox if the PHP didn't load it
  createFallbackLightbox() {
    console.log('Creating fallback lightbox HTML structure...');

    const lightboxHTML = `
      <div id="designer-lightbox" class="designer-lightbox">
        <div class="designer-modal">
          <div class="designer-header">
            <h2>Customize Design</h2>
            <button class="designer-close">&times;</button>
          </div>
          <div class="designer-loading" style="display: flex;">
            <div class="loading-spinner"></div>
            <p>Loading designer...</p>
          </div>
          <div class="designer-body" style="opacity: 0;">
            <div class="designer-sidebar">
              <div class="tool-section">
                <h3>Add Elements</h3>
                <div class="tool-buttons">
                  <div class="upload-image-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24"><path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z" /></svg>
                    <span>Upload Image</span>
                    <input type="file" class="image-upload-input" accept="image/*" multiple>
                  </div>
                  <button class="add-text-btn tool-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24"><path d="M18.5,4L19.66,8.35L18.7,8.61C18.25,7.74 17.79,6.87 17.26,6.43C16.73,6 16.11,6 15.5,6H13V16.5C13,17 13,17.5 13.5,17.5H14V19H10V17.5H10.5C11,17.5 11,17 11,16.5V6H8.5C7.89,6 7.27,6 6.74,6.43C6.21,6.87 5.75,7.74 5.3,8.61L4.34,8.35L5.5,4H18.5Z" /></svg>
                    <span>Add Text</span>
                  </button>
                </div>
              </div>
              <div class="tool-section">
                <h3>Actions</h3>
                <div class="history-controls">
                  <button id="undo-btn" class="history-btn" disabled>
                    <svg width="20" height="20" viewBox="0 0 24 24"><path d="M12.5,8C9.85,8 7.45,9 5.6,10.6L2,7V16H11L7.38,12.38C8.77,11.22 10.54,10.5 12.5,10.5C16.04,10.5 19.05,12.81 20.1,16L22.47,15.22C21.08,11.03 17.15,8 12.5,8Z" /></svg>
                    Undo
                  </button>
                  <button id="redo-btn" class="history-btn" disabled>
                    <svg width="20" height="20" viewBox="0 0 24 24"><path d="M18.4,10.6C16.55,9 14.15,8 11.5,8C6.85,8 2.92,11.03 1.53,15.22L3.9,16C4.95,12.81 7.96,10.5 11.5,10.5C13.46,10.5 15.23,11.22 16.62,12.38L13,16H22V7L18.4,10.6Z" /></svg>
                    Redo
                  </button>
                </div>
              </div>
            </div>
            <div class="canvas-container">
              <canvas id="designer-canvas"></canvas>
            </div>
            <div class="properties-panel">
              <h3>Properties</h3>
            </div>
          </div>
          <div class="designer-footer">
            <button class="cancel-design btn btn-secondary">Cancel</button>
            <button class="apply-design btn btn-primary">Apply Design</button>
          </div>
        </div>
      </div>
      
      <!-- Text Editor Modal -->
      <div id="text-editor-modal" class="text-editor-modal">
        <div class="modal-content">
          <h3>Add Text</h3>
          <div class="text-controls">
            <input type="text" id="text-input" placeholder="Enter your text">
            <div class="text-options">
              <select id="font-select">
                <option value="Arial">Arial</option>
                <option value="Times New Roman">Times New Roman</option>
                <option value="Helvetica">Helvetica</option>
              </select>
              <input type="color" id="text-color" value="#000000">
              <input type="range" id="text-size" min="10" max="100" value="30">
              <span id="text-size-value">30px</span>
            </div>
          </div>
          <div class="modal-actions">
            <button id="cancel-text" class="btn btn-secondary">Cancel</button>
            <button id="add-text-confirm" class="btn btn-primary">Add Text</button>
          </div>
        </div>
      </div>
      
      <!-- Hidden inputs for form data -->
      <input type="hidden" id="custom-design-preview" name="custom_design_preview" value="">
      <input type="hidden" id="custom-design-data" name="custom_design_data" value="">
      <input type="hidden" id="custom-canvas-data" name="custom_canvas_data" value="">
    `;

    // Add basic CSS for the fallback lightbox
    const fallbackCSS = `
      <style id="fallback-designer-css">
        .designer-lightbox {
          position: fixed;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          background: rgba(0,0,0,0.8);
          display: none;
          z-index: 999999;
          align-items: center;
          justify-content: center;
        }
        .designer-modal {
          background: white;
          border-radius: 8px;
          width: 90vw;
          height: 90vh;
          max-width: 1200px;
          display: flex;
          flex-direction: column;
          position: relative;
        }
        .designer-header {
          padding: 20px;
          border-bottom: 1px solid #ddd;
          display: flex;
          justify-content: space-between;
          align-items: center;
        }
        .designer-close {
          background: none;
          border: none;
          font-size: 24px;
          cursor: pointer;
        }
        .designer-loading {
          position: absolute;
          top: 50%;
          left: 50%;
          transform: translate(-50%, -50%);
          text-align: center;
          flex-direction: column;
          align-items: center;
        }
        .loading-spinner {
          width: 40px;
          height: 40px;
          border: 4px solid #f3f3f3;
          border-top: 4px solid #0073aa;
          border-radius: 50%;
          animation: spin 1s linear infinite;
          margin-bottom: 10px;
        }
        @keyframes spin {
          0% { transform: rotate(0deg); }
          100% { transform: rotate(360deg); }
        }
        .designer-body {
          flex: 1;
          display: flex;
          padding: 20px;
        }
        .designer-sidebar {
          width: 250px;
          padding-right: 20px;
          border-right: 1px solid #ddd;
        }
        .canvas-container {
          flex: 1;
          display: flex;
          align-items: center;
          justify-content: center;
          margin: 0 20px;
          position: relative;
        }
        .properties-panel {
          width: 200px;
          padding-left: 20px;
          border-left: 1px solid #ddd;
          display: none; /* Hidden by default on desktop */
        }
        @media (max-width: 768px) {
          .properties-panel.mobile-visible {
            display: block;
            position: fixed;
            top: 0;
            right: 0;
            width: 300px;
            height: 100vh;
            background: white;
            z-index: 1000001;
            box-shadow: -2px 0 10px rgba(0,0,0,0.3);
          }
        }
        .designer-footer {
          padding: 20px;
          border-top: 1px solid #ddd;
          display: flex;
          justify-content: flex-end;
          gap: 10px;
        }
        .btn {
          padding: 10px 20px;
          border: none;
          border-radius: 4px;
          cursor: pointer;
        }
        .btn-primary {
          background: #0073aa;
          color: white;
        }
        .btn-secondary {
          background: #666;
          color: white;
        }
        .tool-btn {
          display: flex;
          align-items: center;
          gap: 8px;
          width: 100%;
          padding: 12px;
          margin-bottom: 8px;
          border: 1px solid #ddd;
          background: white;
          cursor: pointer;
          border-radius: 4px;
        }
        .upload-image-btn {
          position: relative;
          display: flex;
          align-items: center;
          gap: 8px;
          width: 100%;
          padding: 12px;
          margin-bottom: 8px;
          border: 1px solid #ddd;
          background: white;
          cursor: pointer;
          border-radius: 4px;
          overflow: hidden;
        }
        .upload-image-btn input {
          position: absolute;
          opacity: 0;
          width: 100%;
          height: 100%;
          cursor: pointer;
          left: 0;
          top: 0;
        }
        .text-editor-modal {
          position: absolute;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          background: rgba(0,0,0,0.7);
          display: none;
          z-index: 1000002;
          align-items: center;
          justify-content: center;
        }
        .text-editor-modal .modal-content {
          background: white;
          padding: 20px;
          border-radius: 8px;
          min-width: 400px;
          max-width: 500px;
          max-height: 80vh;
          overflow-y: auto;
        }
        .text-editor-modal .modal-header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          margin-bottom: 20px;
          padding-bottom: 10px;
          border-bottom: 1px solid #ddd;
        }
        .text-editor-modal .modal-close {
          background: none;
          border: none;
          font-size: 24px;
          cursor: pointer;
          padding: 0;
          width: 30px;
          height: 30px;
          display: flex;
          align-items: center;
          justify-content: center;
        }
        .text-controls {
          margin: 20px 0;
        }
        .text-controls input,
        .text-controls select {
          margin: 5px;
          padding: 8px;
        }
        .modal-actions {
          display: flex;
          justify-content: flex-end;
          gap: 10px;
          margin-top: 20px;
        }
        .history-controls {
          display: flex;
          gap: 5px;
        }
        .history-btn {
          padding: 8px;
          border: 1px solid #ddd;
          background: white;
          cursor: pointer;
          border-radius: 4px;
          font-size: 12px;
        }
        .history-btn:disabled {
          opacity: 0.5;
          cursor: not-allowed;
        }
      </style>
    `;

    // Add the CSS first
    document.head.insertAdjacentHTML('beforeend', fallbackCSS);

    // Add the HTML to the body
    document.body.insertAdjacentHTML('beforeend', lightboxHTML);

    console.log('Fallback lightbox created successfully');

    // Verify it was created
    const createdLightbox = document.getElementById('designer-lightbox');
    console.log('Fallback lightbox verification:', !!createdLightbox);

    return !!createdLightbox;
  }

  async loadImage(url, options = {}) {
    return new Promise((resolve, reject) => {
      this.queue.push({ url, options, resolve, reject });
      this.processQueue();
    });
  }

  async processQueue() {
    if (this.activeLoads >= this.maxConcurrent || this.queue.length === 0) {
      return;
    }

    const item = this.queue.shift();
    this.activeLoads++;

    try {
      const img = await this.loadImageElement(item.url);
      item.resolve(img);
    } catch (error) {
      item.reject(error);
    } finally {
      this.activeLoads--;
      this.processQueue();
    }
  }

  loadImageElement(url) {
    return new Promise((resolve, reject) => {
      const img = new Image();
      img.crossOrigin = 'anonymous';
      img.onload = () => resolve(img);
      img.onerror = reject;
      img.src = url;
    });
  }
}

export { ImageLoader };
