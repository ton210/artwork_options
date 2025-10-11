<!-- Enhanced Designer Lightbox Template for SequinSwag -->
<div id="designer-lightbox" class="designer-lightbox" style="display: none;">
    <div class="designer-modal">

        <!-- Modern Header with Logo -->
        <div class="designer-header">
            <div class="header-left">
                <div class="header-logo">
                    <img src="https://sequinswag.s3.amazonaws.com/20250404132616/Untitled-3.png" alt="SequinSwag">
                </div>
                <h2>Customize Your Design</h2>
            </div>
            <button class="designer-close" type="button" aria-label="Close designer">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Sleek Loading Screen -->
        <div class="designer-loading">
            <div class="loading-spinner"></div>
            <p>Loading designer...</p>
        </div>

        <!-- Main Designer Body -->
        <div class="designer-body">

            <!-- Desktop Sidebar -->
            <div class="designer-sidebar desktop-only">
                <!-- Add Elements -->
                <div class="tool-section">
                    <h3>Add Elements</h3>
                    <div class="tool-buttons">
                        <label class="tool-btn upload-image-btn">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12"/>
                            </svg>
                            <span>Upload Image</span>
                            <input type="file" class="image-upload-input" id="main-file-input" accept="image/*" multiple style="display: none;">
                        </label>

                        <button class="tool-btn add-text-btn" type="button">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 7V4h16v3M9 20h6M12 4v16"/>
                            </svg>
                            <span>Add Text</span>
                        </button>
                    </div>
                </div>

                <!-- History -->
                <div class="tool-section">
                    <h3>History</h3>
                    <div class="history-controls">
                        <button id="undo-btn" class="history-btn" disabled>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 7v6h6M21 17a9 9 0 00-9-9 9 9 0 00-9 9"/>
                            </svg>
                            Undo
                        </button>
                        <button id="redo-btn" class="history-btn" disabled>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 7v6h-6M3 17a9 9 0 019-9 9 9 0 019 9"/>
                            </svg>
                            Redo
                        </button>
                    </div>
                </div>

                <!-- Save/Load -->
                <div class="tool-section">
                    <h3>Designs</h3>
                    <div class="design-controls">
                        <button class="tool-btn save-design-btn">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/>
                                <path d="M17 21v-8H7v8M7 3v5h8"/>
                            </svg>
                            <span>Save Design</span>
                        </button>
                        <button class="tool-btn load-design-btn">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z"/>
                                <path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/>
                            </svg>
                            <span>Load Design</span>
                        </button>
                    </div>
                </div>

                <!-- Hidden Crop Controls -->
                <div id="crop-controls" style="display: none;">
                    <h3>Crop Image</h3>
                    <div class="crop-buttons">
                        <button class="tool-btn apply-crop-btn">Apply</button>
                        <button class="tool-btn cancel-crop-btn">Cancel</button>
                    </div>
                </div>
            </div>

            <!-- Canvas Area -->
            <div class="designer-canvas-area">
                <div class="canvas-container">
                    <canvas id="designer-canvas"></canvas>
                </div>
            </div>

            <!-- Properties Panel (Hidden) -->
            <div class="properties-panel" style="display: none;">
                <div id="edit-tools" style="display: none;">
                    <button id="crop-btn" class="tool-btn" style="display: none;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M22 12h-4M2 12h4M12 2v4M12 18v4"/>
                        </svg>
                        <span>Crop</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="designer-footer">
            <div class="designer-pricing-info"></div>
            <div class="footer-right">
                <button class="btn btn-secondary cancel-design">Cancel</button>
                <button class="btn btn-primary apply-design">Apply Design</button>
                <button class="btn btn-primary add-to-cart-design">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="9" cy="21" r="1"/>
                        <circle cx="20" cy="21" r="1"/>
                        <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>
                    </svg>
                    Add to Cart
                </button>
            </div>
        </div>

        <!-- Mobile Quick Actions -->
        <div class="mobile-quick-actions mobile-only">
            <div class="mobile-quick-actions-inner">
                <button class="mobile-action-btn upload" id="mobile-upload-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12"/>
                    </svg>
                    <span>Upload</span>
                    <input type="file" accept="image/*" multiple style="display: none;">
                </button>

                <button class="mobile-action-btn text" id="mobile-text-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 7V4h16v3M9 20h6M12 4v16"/>
                    </svg>
                    <span>Text</span>
                </button>

                <button class="mobile-action-btn templates" id="mobile-templates-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                        <path d="M3 9h18M9 21V9"/>
                    </svg>
                    <span>Templates</span>
                </button>

                <button class="mobile-action-btn save" id="mobile-save-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/>
                        <path d="M17 21v-8H7v8M7 3v5h8"/>
                    </svg>
                    <span>Save</span>
                </button>
            </div>
        </div>

        <!-- Mobile Tools Drawer -->
        <div class="mobile-tools-drawer mobile-only">
            <div class="mobile-tools-header">
                <h3 class="mobile-tools-title">Tools</h3>
                <button class="mobile-tools-close">×</button>
            </div>
            <div class="mobile-tools-content">
                <!-- Tools will be cloned here for mobile -->
            </div>
        </div>
    </div>
</div>

<!-- Text Editor Modal -->
<div id="text-editor-modal" class="text-editor-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add Text</h3>
            <button class="modal-close">×</button>
        </div>
        <div class="text-controls">
            <div class="control-group">
                <label>Text</label>
                <input type="text" id="text-input" placeholder="Enter your text here...">
            </div>
            <div class="control-group">
                <label>Font</label>
                <select id="font-select">
                    <option value="Arial">Arial</option>
                    <option value="Helvetica">Helvetica</option>
                    <option value="Times New Roman">Times New Roman</option>
                    <option value="Georgia">Georgia</option>
                    <option value="Verdana">Verdana</option>
                    <option value="Impact">Impact</option>
                </select>
            </div>
            <div class="control-group">
                <label>Color</label>
                <input type="color" id="text-color" value="#000000">
            </div>
            <div class="control-group">
                <label>Size: <span id="text-size-value">30px</span></label>
                <input type="range" id="text-size" min="12" max="120" value="30">
            </div>
        </div>
        <div class="modal-actions">
            <button class="btn btn-secondary" id="cancel-text">Cancel</button>
            <button class="btn btn-primary" id="add-text-confirm" disabled>Add Text</button>
        </div>
    </div>
</div>

<!-- Hidden Inputs -->
<input type="hidden" id="custom-design-preview" name="custom_design_preview" value="">
<input type="hidden" id="custom-design-data" name="custom_design_data" value="">
<input type="hidden" id="custom-canvas-data" name="custom_canvas_data" value="">

<style>
/* Enhanced Designer Styles */
.designer-lightbox {
    --primary: #9b59ff;
    --primary-hover: #8847ee;
    --primary-light: rgba(155, 89, 255, 0.1);
    --secondary: #64d8cb;
    --secondary-light: rgba(100, 216, 203, 0.1);
    --success: #52c41a;
    --danger: #ff4d4f;
    --dark: #1a1a2e;
    --gray: #6c757d;
    --light-gray: #f8f9fa;
    --border: #e9ecef;
    --shadow: 0 2px 8px rgba(0,0,0,0.08);
    --shadow-lg: 0 8px 24px rgba(0,0,0,0.12);
    --shadow-xl: 0 20px 40px rgba(0,0,0,0.15);
    --radius: 16px;
    --radius-sm: 8px;
    --radius-lg: 24px;
}

/* Prevent SVG rendering issues */
.designer-lightbox svg {
    display: block;
    flex-shrink: 0;
}

/* Beautiful modern modal */
.designer-modal {
    background: white;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-xl);
    width: 95vw;
    height: 95vh;
    max-width: 1600px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transform: scale(0.9) translateY(20px);
    opacity: 0;
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.designer-lightbox.active .designer-modal {
    transform: scale(1) translateY(0);
    opacity: 1;
}

/* Clean header */
.designer-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem 2rem;
    border-bottom: 1px solid var(--border);
    background: linear-gradient(to bottom, white, rgba(248, 249, 250, 0.5));
}

.header-left {
    display: flex;
    align-items: center;
    gap: 1.25rem;
}

.header-logo {
    display: flex;
    align-items: center;
    padding-right: 1.25rem;
    border-right: 1px solid var(--border);
}

.header-logo img {
    height: 40px;
    width: auto;
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
}

.designer-header h2 {
    margin: 0;
    font-size: 1.375rem;
    font-weight: 600;
    color: var(--dark);
    letter-spacing: -0.02em;
}

.designer-close {
    width: 44px;
    height: 44px;
    border: none;
    background: var(--light-gray);
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.designer-close::before {
    content: '';
    position: absolute;
    inset: 0;
    background: var(--danger);
    transform: scale(0);
    transition: transform 0.3s ease;
    border-radius: inherit;
}

.designer-close:hover {
    color: white;
}

.designer-close:hover::before {
    transform: scale(1);
}

.designer-close svg {
    position: relative;
    z-index: 1;
    transition: transform 0.3s ease;
}

.designer-close:hover svg {
    transform: rotate(90deg);
}

/* Loading screen */
.designer-loading {
    position: absolute;
    inset: 0;
    background: rgba(255,255,255,0.98);
    backdrop-filter: blur(10px);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 100;
    gap: 1.5rem;
}

.loading-spinner {
    width: 48px;
    height: 48px;
    position: relative;
}

.loading-spinner::before,
.loading-spinner::after {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 50%;
    border: 3px solid transparent;
}

.loading-spinner::before {
    border-top-color: var(--primary);
    animation: spin 1s cubic-bezier(0.68, -0.55, 0.265, 1.55) infinite;
}

.loading-spinner::after {
    border-bottom-color: var(--secondary);
    animation: spin 1s cubic-bezier(0.68, -0.55, 0.265, 1.55) infinite reverse;
}

.designer-loading p {
    color: var(--gray);
    font-size: 0.875rem;
    font-weight: 500;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Main body layout */
.designer-body {
    display: grid;
    grid-template-columns: 280px 1fr;
    flex: 1;
    overflow: hidden;
    opacity: 0;
    transition: opacity 0.3s ease 0.2s;
}

.designer-body.active {
    opacity: 1;
}

/* Sidebar styling */
.designer-sidebar {
    background: linear-gradient(to bottom, var(--light-gray), rgba(248, 249, 250, 0.8));
    padding: 1.75rem;
    overflow-y: auto;
    border-right: 1px solid var(--border);
}

.tool-section {
    margin-bottom: 2.5rem;
    position: relative;
}

.tool-section::after {
    content: '';
    position: absolute;
    bottom: -1.25rem;
    left: 50%;
    transform: translateX(-50%);
    width: 40px;
    height: 1px;
    background: var(--border);
}

.tool-section:last-child::after {
    display: none;
}

.tool-section h3 {
    margin: 0 0 1.25rem;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--gray);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.tool-section h3::before {
    content: '';
    width: 4px;
    height: 4px;
    background: var(--primary);
    border-radius: 50%;
}

.tool-buttons,
.history-controls,
.design-controls {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.history-controls {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.5rem;
}

.tool-btn,
.history-btn {
    display: flex;
    align-items: center;
    gap: 0.875rem;
    padding: 0.875rem 1.125rem;
    background: white;
    border: 2px solid transparent;
    border-radius: var(--radius-sm);
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--dark);
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.tool-btn::before,
.history-btn::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, var(--primary-light), var(--secondary-light));
    opacity: 0;
    transition: opacity 0.3s ease;
}

.tool-btn:hover:not(:disabled),
.history-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
    border-color: var(--primary);
}

.tool-btn:hover:not(:disabled)::before,
.history-btn:hover:not(:disabled)::before {
    opacity: 1;
}

.tool-btn:hover:not(:disabled) svg,
.history-btn:hover:not(:disabled) svg {
    stroke: var(--primary);
}

.tool-btn:active:not(:disabled),
.history-btn:active:not(:disabled) {
    transform: translateY(0);
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.tool-btn svg,
.history-btn svg {
    position: relative;
    z-index: 1;
    transition: all 0.3s ease;
}

.tool-btn span,
.history-btn span {
    position: relative;
    z-index: 1;
}

.tool-btn:disabled,
.history-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.upload-image-btn {
    background: linear-gradient(135deg, var(--primary), var(--primary-hover));
    color: white;
    border-color: transparent;
}

.upload-image-btn svg {
    stroke: white;
}

.upload-image-btn:hover {
    background: linear-gradient(135deg, var(--primary-hover), #7339db);
    transform: translateY(-2px) scale(1.02);
}

/* Canvas area */
.designer-canvas-area {
    background: linear-gradient(135deg, #f5f6fa 0%, #e9ecf5 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    position: relative;
    overflow: auto;
}

.designer-canvas-area::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image:
        radial-gradient(circle at 2px 2px, rgba(155, 89, 255, 0.05) 1px, transparent 1px);
    background-size: 32px 32px;
    pointer-events: none;
}

.canvas-container {
    background: white;
    border-radius: var(--radius);
    box-shadow:
        0 2px 4px rgba(0,0,0,0.04),
        0 8px 16px rgba(0,0,0,0.08),
        0 24px 48px rgba(0,0,0,0.12);
    padding: 2rem;
    position: relative;
    transition: all 0.3s ease;
}

.canvas-container:hover {
    box-shadow:
        0 2px 4px rgba(0,0,0,0.04),
        0 8px 16px rgba(0,0,0,0.08),
        0 32px 64px rgba(0,0,0,0.16);
}

/* Footer */
.designer-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem 2rem;
    background: linear-gradient(to top, white, rgba(248, 249, 250, 0.5));
    border-top: 1px solid var(--border);
    gap: 1rem;
}

.footer-right {
    display: flex;
    gap: 0.875rem;
}

.btn {
    padding: 0.875rem 1.75rem;
    border: none;
    border-radius: var(--radius-sm);
    font-weight: 600;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: inline-flex;
    align-items: center;
    gap: 0.625rem;
    position: relative;
    overflow: hidden;
}

.btn::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(to right, transparent, rgba(255,255,255,0.2), transparent);
    transform: translateX(-100%);
    transition: transform 0.6s ease;
}

.btn:hover::before {
    transform: translateX(100%);
}

.btn-primary {
    background: linear-gradient(135deg, var(--primary), var(--primary-hover));
    color: white;
    box-shadow: 0 4px 12px rgba(155, 89, 255, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(155, 89, 255, 0.4);
}

.btn-secondary {
    background: white;
    color: var(--dark);
    border: 2px solid var(--border);
}

.btn-secondary:hover {
    background: var(--light-gray);
    border-color: var(--primary);
    color: var(--primary);
}

.add-to-cart-design {
    background: linear-gradient(135deg, var(--success), #449d16);
    box-shadow: 0 4px 12px rgba(82, 196, 26, 0.3);
}

.add-to-cart-design:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(82, 196, 26, 0.4);
}

/* Mobile Quick Actions */
.mobile-quick-actions {
    display: none;
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: white;
    border-top: 1px solid var(--border);
    box-shadow: 0 -8px 32px rgba(0,0,0,0.12);
    z-index: 100;
    padding: 0.75rem;
}

.mobile-quick-actions-inner {
    display: flex;
    gap: 0.625rem;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    padding: 0.25rem;
}

.mobile-quick-actions-inner::-webkit-scrollbar {
    display: none;
}

.mobile-action-btn {
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.375rem;
    padding: 0.875rem 1.125rem;
    background: white;
    border: 2px solid var(--border);
    border-radius: var(--radius);
    color: var(--dark);
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    min-width: 72px;
}

.mobile-action-btn::before {
    content: '';
    position: absolute;
    inset: -2px;
    border-radius: var(--radius);
    padding: 2px;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.mobile-action-btn:active {
    transform: scale(0.95);
}

.mobile-action-btn:active::before {
    opacity: 1;
}

.mobile-action-btn.upload {
    background: linear-gradient(135deg, var(--secondary), #4fc3b6);
    color: white;
    border-color: transparent;
}

.mobile-action-btn.text {
    background: linear-gradient(135deg, var(--primary), var(--primary-hover));
    color: white;
    border-color: transparent;
}

.mobile-action-btn input[type="file"] {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
}

/* Mobile Tools Drawer */
.mobile-tools-drawer {
    display: none;
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: white;
    border-radius: var(--radius) var(--radius) 0 0;
    box-shadow: 0 -4px 20px rgba(0,0,0,0.15);
    max-height: 70vh;
    transform: translateY(100%);
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 101;
}

.mobile-tools-drawer.active {
    transform: translateY(0);
}

.mobile-tools-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--border);
}

.mobile-tools-title {
    margin: 0;
    font-size: 1.125rem;
    font-weight: 600;
}

.mobile-tools-close {
    width: 32px;
    height: 32px;
    border: none;
    background: var(--light-gray);
    border-radius: 50%;
    font-size: 1.5rem;
    cursor: pointer;
}

.mobile-tools-content {
    padding: 1rem;
    overflow-y: auto;
    max-height: calc(70vh - 60px);
}

/* Text Editor Modal */
.text-editor-modal {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 1rem;
}

.text-editor-modal.active {
    display: flex;
}

.modal-content {
    background: white;
    border-radius: var(--radius);
    padding: 2rem;
    max-width: 500px;
    width: 100%;
    box-shadow: var(--shadow-lg);
    animation: modalSlideIn 0.3s ease;
}

@keyframes modalSlideIn {
    from {
        transform: translateY(20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.modal-header h3 {
    margin: 0;
    font-size: 1.25rem;
    color: var(--dark);
}

.modal-close {
    width: 32px;
    height: 32px;
    border: none;
    background: var(--light-gray);
    border-radius: 50%;
    font-size: 1.5rem;
    cursor: pointer;
}

.text-controls {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.control-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.control-group label {
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--gray);
}

.control-group input,
.control-group select {
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    font-size: 0.875rem;
    transition: all 0.2s;
}

.control-group input:focus,
.control-group select:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(155, 89, 255, 0.1);
}

.control-group input[type="range"] {
    padding: 0;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
}

/* Pricing Info */
.designer-pricing-info {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.875rem 1.25rem;
    background: linear-gradient(135deg, var(--primary-light), var(--secondary-light));
    border: 1px solid var(--primary);
    border-radius: var(--radius-sm);
    font-size: 0.875rem;
}

.pricing-details {
    display: flex;
    gap: 0.75rem;
    align-items: center;
}

.pricing-details::before {
    content: '💎';
    font-size: 1.25rem;
}

.quantity-info {
    font-weight: 600;
    color: var(--dark);
}

.price-info {
    color: var(--primary);
    font-weight: 700;
    font-size: 1rem;
}

.unit-price {
    color: var(--gray);
    font-size: 0.75rem;
}

/* Notifications */
.designer-notification {
    position: fixed;
    bottom: 2rem;
    left: 50%;
    transform: translateX(-50%) translateY(100px);
    background: white;
    padding: 1rem 1.5rem;
    border-radius: var(--radius-sm);
    box-shadow: var(--shadow-lg);
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-weight: 500;
    opacity: 0;
    transition: all 0.3s;
    z-index: 1001;
}

.designer-notification.show {
    transform: translateX(-50%) translateY(0);
    opacity: 1;
}

.designer-notification.error {
    background: var(--danger);
    color: white;
}

.designer-notification.info {
    background: var(--primary);
    color: white;
}

.designer-notification.warning {
    background: #ff9800;
    color: white;
}

/* Responsive Design */
@media (max-width: 768px) {
    .desktop-only {
        display: none !important;
    }

    .mobile-only {
        display: block !important;
    }

    .designer-modal {
        width: 100%;
        height: 100%;
        max-width: 100%;
        border-radius: 0;
    }

    .designer-header {
        padding: 0.75rem 1rem;
    }

    .designer-header h2 {
        font-size: 1rem;
    }

    .header-logo {
        padding-right: 1rem;
    }

    .header-logo img {
        height: 32px;
    }

    .designer-body {
        grid-template-columns: 1fr;
    }

    .designer-canvas-area {
        padding: 1rem;
    }

    .canvas-container {
        padding: 1rem;
    }

    .designer-footer {
        flex-direction: column;
        padding: 0.75rem;
        gap: 0.75rem;
    }

    .designer-pricing-info {
        width: 100%;
        justify-content: center;
    }

    .footer-right {
        width: 100%;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem;
    }

    .footer-right .btn {
        font-size: 0.75rem;
        padding: 0.625rem 0.75rem;
    }

    .add-to-cart-design {
        grid-column: 1 / -1;
    }

    .mobile-quick-actions {
        display: block;
    }

    .mobile-tools-drawer {
        display: block;
    }

    .modal-content {
        margin: 1rem;
        padding: 1.5rem;
    }

    .designer-notification {
        bottom: 5rem;
        left: 1rem;
        right: 1rem;
        transform: translateY(100px);
    }

    .designer-notification.show {
        transform: translateY(0);
    }
}

/* Utility Classes */
.hidden {
    display: none !important;
}

/* Fix for proper layering */
.properties-panel {
    display: none;
}

/* Smooth scrollbars */
.designer-sidebar::-webkit-scrollbar,
.mobile-tools-content::-webkit-scrollbar {
    width: 6px;
}

.designer-sidebar::-webkit-scrollbar-track,
.mobile-tools-content::-webkit-scrollbar-track {
    background: transparent;
}

.designer-sidebar::-webkit-scrollbar-thumb,
.mobile-tools-content::-webkit-scrollbar-thumb {
    background: rgba(0,0,0,0.2);
    border-radius: 3px;
}

.designer-sidebar::-webkit-scrollbar-thumb:hover,
.mobile-tools-content::-webkit-scrollbar-thumb:hover {
    background: rgba(0,0,0,0.3);
}
</style>