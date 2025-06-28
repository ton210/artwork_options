<?php
/**
 * Modal Styles
 *
 * @package MunchMakers_Product_Customizer
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Return the CSS as a string instead of echoing it
return '
/* ==========================================================================
   MunchMakers Modal - Core Structure
   ========================================================================== */

/* Modal Container */
#munchmakers-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    box-sizing: border-box;
}

/* Modal Backdrop */
.modal-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    -webkit-backdrop-filter: blur(3px);
    backdrop-filter: blur(3px);
}

/* Modal Content Container */
.modal-content {
    position: relative;
    background: #fff;
    border-radius: 12px;
    width: 100%;
    max-width: 800px;
    max-height: 90vh;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    animation: modalSlideIn 0.3s ease-out;
    display: flex;
    flex-direction: column;
}

/* Modal Animation */
@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-50px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* ==========================================================================
   Modal Header
   ========================================================================== */

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 30px;
    border-bottom: 1px solid #eee;
    background: #f8f9fa;
}

.modal-header h2 {
    margin: 0;
    font-size: 24px;
    color: #333;
}

.modal-close {
    background: transparent;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #666;
    padding: 5px;
    border-radius: 50%;
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.modal-close:hover {
    background: #e9ecef;
    color: #333;
}

/* ==========================================================================
   Progress Bar
   ========================================================================== */

.progress-container {
    padding: 20px 30px;
    background: #fff;
    border-bottom: 1px solid #eee;
}

.progress-bar {
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

.progress-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    z-index: 2;
}

.step-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e9ecef;
    border: 3px solid #dee2e6;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 8px;
    transition: all 0.3s ease;
    position: relative;
}

.step-number {
    font-weight: bold;
    font-size: 16px;
    color: #6c757d;
    transition: opacity 0.3s ease;
}

.step-check {
    width: 20px;
    height: 20px;
    color: #fff;
    position: absolute;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.step-label {
    font-size: 12px;
    color: #6c757d;
    text-align: center;
    font-weight: 500;
    max-width: 80px;
}

.progress-line {
    flex: 1;
    height: 3px;
    background: #dee2e6;
    margin: 0 10px;
    transition: background-color 0.3s ease;
    margin-top: -16px;
    z-index: 1;
}

/* Progress States */
.progress-step.active .step-circle {
    background: #007cba;
    border-color: #007cba;
    transform: scale(1.1);
}

.progress-step.active .step-number {
    color: #fff;
}

.progress-step.active .step-label {
    color: #007cba;
    font-weight: 600;
}

.progress-step.completed .step-circle {
    background: #28a745;
    border-color: #28a745;
}

.progress-step.completed .step-number {
    opacity: 0;
}

.progress-step.completed .step-check {
    opacity: 1;
}

.progress-step.completed .step-label {
    color: #28a745;
}

.progress-step.completed + .progress-line {
    background: #28a745;
}

/* ==========================================================================
   Modal Body
   ========================================================================== */

.modal-body {
    padding: 30px;
    overflow-y: auto;
    flex: 1;
    position: relative;
}

.modal-step {
    display: none !important;
}

.modal-step.active {
    display: block !important;
}

.modal-step h3 {
    margin-top: 0;
    margin-bottom: 25px;
    font-size: 20px;
    color: #333;
    border-bottom: 2px solid #007cba;
    padding-bottom: 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

/* ==========================================================================
   Step Footer
   ========================================================================== */

.step-footer {
    margin-top: 30px;
    display: flex;
    justify-content: space-between;
    gap: 15px;
    padding: 20px 0;
    position: relative;
    background: #fff;
    z-index: 1;
}

.step-footer-sticky {
    position: sticky;
    bottom: 0;
    border-top: 1px solid #eee;
    box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.1);
    margin-top: auto;
}

#step-artwork .step-footer {
    position: sticky;
    bottom: 0;
    border-top: 1px solid #eee;
    box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.1);
    margin-top: auto;
}

/* ==========================================================================
   Buttons
   ========================================================================== */

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
    display: inline-block;
    position: relative;
}

.btn-primary {
    background: #007cba;
    color: #fff;
}

.btn-primary:hover:not(:disabled) {
    background: #005a87;
}

.btn-primary:disabled {
    background: #ccc;
    cursor: not-allowed;
}

.btn-secondary {
    background: #6c757d;
    color: #fff;
}

.btn-secondary:hover {
    background: #545b62;
}

.btn-success {
    background: #28a745;
    color: #fff;
}

.btn-success:hover {
    background: #1e7e34;
}

/* ==========================================================================
   Quantity Controls
   ========================================================================== */

.quantity-controls {
    margin-bottom: 20px;
}

.quantity-slider {
    margin: 20px 0;
    height: 8px;
}

.quantity-fields {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin: 20px 0;
}

.field-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
}

.field-group input {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.pricing-summary {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid #007cba;
}

.pricing-summary .subtotal {
    font-size: 18px;
    font-weight: 600;
    margin: 0 0 10px;
}

.savings {
    color: #28a745;
    font-weight: 600;
}

/* ==========================================================================
   Artwork Section
   ========================================================================== */

.artwork-choice {
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.artwork-choice label {
    display: inline-block;
    margin-right: 25px;
    cursor: pointer;
    font-weight: 400;
}

.artwork-choice input[type="radio"] {
    margin-right: 8px;
}

.artwork-tabs {
    display: flex;
    border-bottom: 1px solid #ddd;
    margin-bottom: 0;
}

.tab-btn {
    flex: 1;
    padding: 15px;
    border: none;
    background: #f8f9fa;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    transition: all 0.2s ease;
    font-size: 14px;
}

.tab-btn:hover {
    background: #e9ecef;
}

.tab-btn.active {
    background: #fff;
    border-bottom-color: #007cba;
    font-weight: 600;
}

.tab-btn .icon {
    margin-right: 5px;
}

.tab-content {
    background: #fff;
}

.tab-panel {
    display: none;
    padding: 25px;
}

.tab-panel.active {
    display: block;
}

.send-later-info {
    padding: 25px;
    background: #f8f9fa;
    border-radius: 8px;
}

.send-later-info h4 {
    margin-top: 0;
    color: #333;
}

.proof-notice {
    margin-top: 20px;
    padding: 15px;
    background: #e7f3ff;
    border-left: 4px solid #007cba;
    border-radius: 4px;
    font-size: 14px;
    color: #666;
}

/* ==========================================================================
   Variations
   ========================================================================== */

.modal-variation-row {
    display: flex;
    align-items: flex-start;
    margin-bottom: 15px;
    flex-direction: column;
}

.modal-variation-row label {
    font-weight: 600;
    margin-bottom: 8px;
    width: 100%;
}

.modal-variation-row select {
    width: 100%;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
    background-color: #fff;
}

/* Color Swatches */
.color-swatches {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    width: 100%;
}

.color-swatch {
    display: flex;
    flex-direction: column;
    align-items: center;
    cursor: pointer;
    padding: 8px;
    border: 2px solid transparent;
    border-radius: 8px;
    transition: all 0.2s ease;
    min-width: 80px;
}

.color-swatch:hover {
    border-color: #007cba;
    background-color: #f8f9fa;
}

.color-swatch.selected {
    border-color: #007cba;
    background-color: #e7f3ff;
    box-shadow: 0 2px 8px rgba(0, 124, 186, 0.3);
}

.color-swatch img {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 4px;
    border: 1px solid #ddd;
}

.color-placeholder {
    width: 50px;
    height: 50px;
    border-radius: 4px;
    border: 1px solid #ddd;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 12px;
    color: #666;
}

.swatch-label {
    margin-top: 6px;
    font-size: 12px;
    text-align: center;
    color: #333;
    font-weight: 500;
}

.current-selection-display {
    margin-top: 20px;
    padding: 10px;
    background-color: #f8f9fa;
    border-left: 3px solid #007cba;
    font-style: italic;
    color: #555;
    min-height: 20px;
}

/* ==========================================================================
   Loading States
   ========================================================================== */

.btn-spinner {
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-spinner svg {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

#add-to-cart.loading {
    position: relative;
    color: transparent !important;
}

#add-to-cart.loading::after {
    content: "";
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    margin-top: -10px;
    margin-left: -10px;
    border: 2px solid rgba(255, 255, 255, 0.5);
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

/* ==========================================================================
   File Upload
   ========================================================================== */

.munch-drop-zone {
    border: 2px dashed #ccc;
    border-radius: 8px;
    padding: 25px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    background-color: #f8f9fa;
    margin-bottom: 15px;
}

.munch-drop-zone.dragover {
    border-color: #007cba;
    background-color: #e7f3ff;
}

.munch-drop-zone .file-upload {
    display: inline-block;
    padding: 8px 16px;
    background: #007cba;
    color: #fff;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.2s ease;
    font-weight: 600;
}

.munch-drop-zone .file-upload:hover {
    background: #005a87;
}

.file-preview-list .file-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px;
    background-color: #f1f1f1;
    border-radius: 4px;
    margin-bottom: 8px;
    font-size: 14px;
}

.file-preview-list .file-icon {
    font-size: 20px;
}

.file-preview-list .file-name {
    flex: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.file-preview-list .file-progress-bar {
    width: 100px;
    height: 8px;
    background-color: #e0e0e0;
    border-radius: 4px;
    overflow: hidden;
}

.file-preview-list .file-progress {
    width: 0;
    height: 100%;
    background-color: #28a745;
    transition: width 0.3s ease;
}

.file-preview-list .file-status {
    font-weight: bold;
}

.file-preview-list .file-status.success {
    color: #28a745;
}

.file-preview-list .file-status.error {
    color: #d9534f;
}

/* ==========================================================================
   Designer Tab
   ========================================================================== */

.designer-content-simple {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 20px 0;
    min-height: 200px;
}

.designer-description {
    margin: 0 0 30px 0;
    color: #666;
    line-height: 1.5;
    font-size: 16px;
    max-width: 400px;
}

.designer-action-center {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 15px;
}

.btn-design-now {
    display: inline-block;
    padding: 18px 40px;
    background: linear-gradient(135deg, #28a745, #20c997);
    color: #fff;
    border: none;
    border-radius: 12px;
    font-size: 18px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    box-shadow: 0 6px 20px rgba(40, 167, 69, 0.3);
    transform: translateY(0);
    position: relative;
    overflow: hidden;
}

.btn-design-now:hover {
    background: linear-gradient(135deg, #218838, #1a9b7a);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
}

.btn-design-now:active {
    transform: translateY(0);
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
}

.btn-design-now .design-icon {
    font-size: 20px;
    margin-right: 8px;
}

.designer-note-simple {
    margin: 0;
    font-size: 13px;
    color: #888;
    font-style: italic;
    line-height: 1.3;
}

/* ==========================================================================
   Pricing Tooltip
   ========================================================================== */

.pricing-tooltip-trigger {
    font-size: 14px;
    color: #007cba;
    cursor: pointer;
    background: #f0f8ff;
    border: 2px solid #007cba;
    border-radius: 20px;
    padding: 6px 12px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-weight: 600;
    margin-left: auto;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0, 124, 186, 0.2);
}

.pricing-tooltip-trigger:hover {
    background: #007cba;
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 124, 186, 0.3);
}

.munch-tooltip {
    position: absolute;
    z-index: 10001;
    background: #fff;
    border: 2px solid #007cba;
    border-radius: 8px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.25);
    padding: 20px;
    width: 350px;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s, transform 0.3s, visibility 0.3s;
    transform: translateY(15px);
}

.munch-tooltip.visible {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.munch-tooltip.stay-open {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.munch-tooltip::before {
    content: "";
    position: absolute;
    top: -8px;
    left: 20px;
    width: 0;
    height: 0;
    border-left: 8px solid transparent;
    border-right: 8px solid transparent;
    border-bottom: 8px solid #007cba;
}

.munch-tooltip h4 {
    margin: 0 0 15px 0;
    color: #007cba;
    font-size: 16px;
    font-weight: bold;
    border-bottom: 2px solid #f0f8ff;
    padding-bottom: 8px;
}

.munch-tooltip-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

.munch-tooltip-table th,
.munch-tooltip-table td {
    padding: 10px 12px;
    border-bottom: 1px solid #f0f8ff;
    text-align: left;
}

.munch-tooltip-table th {
    font-weight: 600;
    font-size: 0.95em;
    background: #f8f9fa;
    color: #007cba;
}

.munch-tooltip-table td:last-child {
    text-align: right;
    font-weight: bold;
    color: #28a745;
}

.munch-tooltip-table tr:hover {
    background: #f8f9fa;
}

.munch-tooltip-close {
    position: absolute;
    top: 8px;
    right: 12px;
    background: none;
    border: none;
    font-size: 18px;
    color: #666;
    cursor: pointer;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.2s ease;
}

.munch-tooltip-close:hover {
    background: #f0f0f0;
    color: #333;
}

/* ==========================================================================
   Mobile Responsive
   ========================================================================== */

@media (max-width: 768px) {
    #munchmakers-modal {
        padding: 10px;
    }
    
    .modal-content {
        max-height: 95vh;
    }
    
    .modal-header,
    .modal-body,
    .progress-container {
        padding: 15px 20px;
    }
    
    .quantity-fields {
        grid-template-columns: 1fr;
    }
    
    .step-footer {
        flex-direction: column;
        gap: 10px;
    }
    
    .artwork-tabs {
        flex-direction: column;
    }
    
    .tab-btn {
        border-bottom: 1px solid #ddd;
        border-right: none;
    }
    
    .tab-btn.active {
        border-bottom: 1px solid #ddd;
        border-left: 3px solid #007cba;
    }
    
    .color-swatches {
        justify-content: center;
    }
    
    .color-swatch {
        min-width: 70px;
    }
    
    .modal-variation-row {
        align-items: center;
    }
    
    .progress-bar {
        flex-direction: column;
        gap: 15px;
    }
    
    .progress-step {
        flex-direction: row;
        align-items: center;
        width: 100%;
    }
    
    .step-circle {
        margin-bottom: 0;
        margin-right: 10px;
    }
    
    .progress-line {
        display: none;
    }
    
    .step-label {
        max-width: none;
        text-align: left;
    }
    
    .designer-content {
        gap: 15px;
    }
    
    .btn-design-now {
        padding: 15px 30px;
        font-size: 16px;
    }
    
    .designer-features li {
        font-size: 14px;
    }
}

/* ==========================================================================
   Utility Classes
   ========================================================================== */

.file-upload input[type="file"] {
    display: none;
}

/* End of MunchMakers Modal Styles */
';
?>