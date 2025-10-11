/**
 * Style Injector Module
 * Injects essential CSS for the designer when using modular mode
 */

export class StyleInjector {
  static injectStyles() {
    if (document.getElementById('swpd-modular-styles')) {
      return; // Already injected
    }

    const styles = `
      .designer-lightbox {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        z-index: 999999;
        opacity: 0;
        transition: opacity 0.3s ease;
      }

      .designer-lightbox.active {
        opacity: 1;
      }

      .designer-container {
        width: 95%;
        height: 95%;
        max-width: 1400px;
        background: white;
        border-radius: 8px;
        margin: 2.5% auto;
        display: flex;
        flex-direction: column;
        overflow: hidden;
      }

      .designer-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        border-bottom: 1px solid #ddd;
        background: #f8f9fa;
      }

      .designer-header h2 {
        margin: 0;
        color: #333;
      }

      .designer-controls {
        display: flex;
        gap: 10px;
        align-items: center;
      }

      .variant-selector {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        background: white;
      }

      .btn-close {
        background: #dc3545;
        color: white;
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        font-size: 24px;
        line-height: 1;
        cursor: pointer;
      }

      .loading-spinner {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        z-index: 10;
        background: rgba(255, 255, 255, 0.9);
        padding: 20px;
        border-radius: 8px;
      }

      .spinner {
        border: 4px solid #f3f3f3;
        border-top: 4px solid #007cba;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
        margin: 0 auto 10px;
      }

      @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
      }

      .designer-body {
        flex: 1;
        display: flex;
        padding: 20px;
        gap: 20px;
      }

      .designer-sidebar {
        width: 250px;
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
      }

      .sidebar-section {
        margin-bottom: 30px;
      }

      .sidebar-section h4 {
        margin: 0 0 15px 0;
        color: #333;
        font-size: 14px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
      }

      .canvas-container {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f0f0f0;
        border-radius: 8px;
        position: relative;
        min-height: 500px;
      }

      .properties-panel {
        width: 250px;
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        display: none;
      }

      .properties-panel.show {
        display: block;
      }

      .designer-footer {
        padding: 20px;
        border-top: 1px solid #ddd;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        background: #f8f9fa;
      }

      .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
      }

      .btn-primary {
        background: #007cba;
        color: white;
      }

      .btn-primary:hover {
        background: #005a87;
      }

      .btn-secondary {
        background: #6c757d;
        color: white;
      }

      .btn-secondary:hover {
        background: #545b62;
      }

      .btn-success {
        background: #28a745;
        color: white;
      }

      .btn-success:hover {
        background: #1e7e34;
      }

      .btn-danger {
        background: #dc3545;
        color: white;
      }

      .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
      }

      .text-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000000;
      }

      .text-modal-content {
        background: white;
        border-radius: 8px;
        padding: 0;
        max-width: 500px;
        width: 90%;
      }

      .text-modal-header {
        padding: 20px;
        border-bottom: 1px solid #ddd;
        display: flex;
        justify-content: space-between;
        align-items: center;
      }

      .text-modal-body {
        padding: 20px;
      }

      .text-modal-footer {
        padding: 20px;
        border-top: 1px solid #ddd;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
      }

      .form-group {
        margin-bottom: 15px;
      }

      .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
      }

      .form-group input,
      .form-group select,
      .form-group textarea {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
      }

      .form-row {
        display: flex;
        gap: 15px;
      }

      .form-row .form-group {
        flex: 1;
      }

      .modal-close {
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

      .history-controls {
        display: flex;
        gap: 10px;
      }

      .history-controls .btn {
        flex: 1;
        justify-content: center;
      }

      .property-group {
        margin-bottom: 20px;
      }

      .property-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
        font-size: 12px;
        text-transform: uppercase;
      }

      #opacity-slider {
        width: 100%;
        margin-bottom: 5px;
      }

      #opacity-value {
        font-size: 12px;
        color: #666;
      }

      @media (max-width: 768px) {
        .designer-container {
          width: 100%;
          height: 100%;
          margin: 0;
          border-radius: 0;
        }

        .designer-body {
          flex-direction: column;
          padding: 10px;
        }

        .designer-sidebar {
          width: 100%;
          order: 2;
        }

        .canvas-container {
          order: 1;
          min-height: 300px;
        }

        .properties-panel {
          width: 100%;
          order: 3;
        }
      }
    `;

    const styleElement = document.createElement('style');
    styleElement.id = 'swpd-modular-styles';
    styleElement.textContent = styles;
    document.head.appendChild(styleElement);
  }
}