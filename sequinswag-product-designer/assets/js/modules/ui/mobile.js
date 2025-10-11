/**
 * Mobile UI and interactions
 * Part of Enhanced Product Designer
 * 
 * @module mobileMethods
 */

export const mobileMethods = {

setupMobileUI() {
      if (window.innerWidth <= 768) {
          // Setup mobile quick actions
          this.setupMobileQuickActions();

          // Setup mobile tools drawer
          this.setupMobileToolsDrawer();

          // Add mobile upload zone when no items
          this.setupMobileUploadZone();

          // Setup touch gestures
          this.setupTouchGestures();

          // Add mobile-specific event handlers
          this.setupMobileEventHandlers();
      }
  },


setupMobileQuickActions() {
      // Upload button
      const mobileUploadBtn = document.getElementById('mobile-upload-btn');
      if (mobileUploadBtn) {
          const fileInput = mobileUploadBtn.querySelector('input[type="file"]');
          mobileUploadBtn.addEventListener('click', (e) => {
              if (e.target.tagName !== 'INPUT') {
                  fileInput.click();
              }
          });

          fileInput.addEventListener('change', (e) => this.handleImageUpload(e));
      }

      // Text button
      const mobileTextBtn = document.getElementById('mobile-text-btn');
      if (mobileTextBtn) {
          mobileTextBtn.addEventListener('click', () => this.showTextEditor());
      }

      // Templates button
      const mobileTemplatesBtn = document.getElementById('mobile-templates-btn');
      if (mobileTemplatesBtn) {
          mobileTemplatesBtn.addEventListener('click', () => this.showTemplatesModal());
      }

      // Save button
      const mobileSaveBtn = document.getElementById('mobile-save-btn');
      if (mobileSaveBtn) {
          mobileSaveBtn.addEventListener('click', () => this.saveDesign());
      }

      // Apply/Done button
      const mobileApplyBtn = document.getElementById('mobile-apply-btn');
      if (mobileApplyBtn) {
          mobileApplyBtn.addEventListener('click', () => {
              if (this.canvas && this.canvas.getObjects().filter(obj => obj.selectable !== false).length > 0) {
                  this.applyDesign();
              } else {
                  this.showNotification('Please add at least one element to your design', 'warning');
              }
          });
      }
  },


setupMobileToolsDrawer() {
      const drawer = document.getElementById('mobile-tools-drawer');
      const closeBtn = document.getElementById('mobile-tools-close');

      if (drawer && closeBtn) {
          closeBtn.addEventListener('click', () => {
              drawer.classList.remove('active');
          });

          // Close on outside click
          drawer.addEventListener('click', (e) => {
              if (e.target === drawer) {
                  drawer.classList.remove('active');
              }
          });
      }
  },


setupMobileUploadZone() {
      if (!this.canvas) return;

      const checkAndShowUploadZone = () => {
          const objects = this.canvas.getObjects().filter(obj => obj.selectable !== false);
          if (objects.length === 0) {
              const uploadZone = document.createElement('div');
              uploadZone.className = 'mobile-upload-zone';
              uploadZone.innerHTML = `
                  <h3>${this.translations?.startDesigning || 'Start Designing'}</h3>
                  <p>${this.translations?.tapToUpload || 'Tap the upload button below to add your first image'}</p>
                  <svg width="60" height="60" viewBox="0 0 24 24" fill="var(--swpd-primary)" opacity="0.5">
                      <path d="M9 16h6v-6h4l-7-7-7 7h4zm-4 2h14v2H5z"/>
                  </svg>
              `;

              const canvasContainer = document.querySelector('.canvas-container');
              if (canvasContainer && !canvasContainer.querySelector('.mobile-upload-zone')) {
                  canvasContainer.appendChild(uploadZone);

                  // Animate in
                  setTimeout(() => {
                      uploadZone.style.opacity = '1';
                  }, 100);
              }
          } else {
              // Remove upload zone if items exist
              const existingZone = document.querySelector('.mobile-upload-zone');
              if (existingZone) {
                  existingZone.remove();
              }
          }
      };

      // Check on canvas changes
      this.canvas.on('object:added', checkAndShowUploadZone);
      this.canvas.on('object:removed', checkAndShowUploadZone);

      // Initial check
      checkAndShowUploadZone();
  },


setupTouchGestures() {
      if (!this.canvas) return;

      // Enable touch scrolling
      this.canvas.allowTouchScrolling = true;

      // Pinch to zoom
      let lastScale = 1;
      let lastDistance = 0;

      this.canvas.on('touch:gesture', (e) => {
          if (e.e.touches && e.e.touches.length === 2) {
              const touch1 = e.e.touches[0];
              const touch2 = e.e.touches[1];
              const distance = Math.sqrt(
                  Math.pow(touch2.pageX - touch1.pageX, 2) +
                  Math.pow(touch2.pageY - touch1.pageY, 2)
              );

              if (lastDistance > 0) {
                  const scale = distance / lastDistance;
                  const newZoom = this.canvas.getZoom() * scale;
                  this.canvas.setZoom(Math.max(0.5, Math.min(3, newZoom)));
                  this.canvas.renderAll();
              }

              lastDistance = distance;
          }
      });

      this.canvas.on('touch:drag', (e) => {
          // Show hint for first time users
          if (!localStorage.getItem('swpd_gesture_hint_shown')) {
              const hint = document.createElement('div');
              hint.className = 'mobile-gesture-hint';
              hint.textContent = 'Pinch to zoom • Drag to move';
              document.querySelector('.canvas-container')?.appendChild(hint);
              localStorage.setItem('swpd_gesture_hint_shown', 'true');

              setTimeout(() => hint.remove(), 3000);
          }
      });

      // Reset on touch end
      this.canvas.on('touch:end', () => {
          lastDistance = 0;
      });

      // Long press to delete
      let longPressTimer;
      this.canvas.on('mouse:down', (e) => {
          if (e.target && e.target.selectable) {
              longPressTimer = setTimeout(() => {
                  if (confirm(this.translations?.deleteItem || 'Delete this item?')) {
                      this.canvas.remove(e.target);
                      this.canvas.renderAll();
                      this.saveHistory();

                      // Haptic feedback if available
                      if (window.navigator && window.navigator.vibrate) {
                          window.navigator.vibrate(50);
                      }
                  }
              }, 1000);
          }
      });

      this.canvas.on('mouse:up', () => {
          if (longPressTimer) {
              clearTimeout(longPressTimer);
              longPressTimer = null;
          }
      });
  },


setupMobileEventHandlers() {
      // Prevent zoom on double tap
      let lastTouchEnd = 0;
      document.addEventListener('touchend', (e) => {
          const now = Date.now();
          if (now - lastTouchEnd <= 300) {
              e.preventDefault();
          }
          lastTouchEnd = now;
      }, false);

      // Handle orientation change
      window.addEventListener('orientationchange', () => {
          setTimeout(() => {
              this.handleResize();
              this.canvas.renderAll();
          }, 300);
      });

      // Show/hide UI elements based on canvas selection
      if (this.canvas) {
          this.canvas.on('selection:created', () => {
              this.showMobileEditControls();
          });

          this.canvas.on('selection:cleared', () => {
              this.hideMobileEditControls();
          });
      }
  },


showMobileEditControls() {
      // Add a floating delete button for selected items
      const deleteBtn = document.createElement('button');
      deleteBtn.className = 'mobile-fab delete-fab';
      deleteBtn.innerHTML = `
          <svg viewBox="0 0 24 24" fill="currentColor">
              <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
          </svg>
      `;
      deleteBtn.style.cssText = `
          position: fixed;
          bottom: 170px;
          right: 20px;
          width: 48px;
          height: 48px;
          background: var(--swpd-danger);
          border-radius: 50%;
          box-shadow: 0 4px 12px rgba(255, 77, 79, 0.3);
          display: flex;
          align-items: center;
          justify-content: center;
          cursor: pointer;
          transition: all 0.3s ease;
          z-index: 91;
          border: none;
      `;

      deleteBtn.addEventListener('click', () => {
          const activeObject = this.canvas.getActiveObject();
          if (activeObject && activeObject !== this.backgroundImage && activeObject !== this.maskImage) {
              this.canvas.remove(activeObject);
              this.canvas.renderAll();
              this.saveHistory();
              this.showNotification('Item deleted');
              deleteBtn.remove();
          }
      });

      document.body.appendChild(deleteBtn);

      // Store reference for cleanup
      this.mobileDeleteBtn = deleteBtn;
  },


hideMobileEditControls() {
      if (this.mobileDeleteBtn) {
          this.mobileDeleteBtn.remove();
          this.mobileDeleteBtn = null;
      }
  },


showTemplatesModal() {
      const modal = document.createElement('div');
      modal.className = 'templates-modal mobile-optimized';
      modal.innerHTML = `
          <div class="templates-content">
              <div class="templates-header">
                  <h3>${this.translations?.chooseTemplate || 'Choose a Template'}</h3>
                  <button class="close-modal">&times;</button>
              </div>
              <div class="template-categories">
                  <button class="category-btn active" data-category="all">All</button>
                  <button class="category-btn" data-category="birthday">Birthday</button>
                  <button class="category-btn" data-category="wedding">Wedding</button>
                  <button class="category-btn" data-category="sports">Sports</button>
              </div>
              <div class="templates-grid mobile-grid">
                                  </div>
          </div>
      `;

      document.body.appendChild(modal);
      modal.style.display = 'flex';

      // Add mobile-specific styles
      const style = document.createElement('style');
      style.textContent = `
          .templates-modal.mobile-optimized .templates-content {
              width: 100%;
              height: 100vh;
              max-height: 100vh;
              margin: 0;
              border-radius: 0;
              display: flex;
              flex-direction: column;
          }
          .templates-modal.mobile-optimized .templates-header {
              display: flex;
              justify-content: space-between;
              align-items: center;
              padding: 16px;
              border-bottom: 1px solid var(--swpd-border);
              background: #fff;
              position: sticky;
              top: 0;
              z-index: 10;
          }
          .templates-modal.mobile-optimized .templates-header h3 {
              margin: 0;
              font-size: 1.1rem;
          }
          .templates-modal.mobile-optimized .close-modal {
              background: none;
              border: none;
              font-size: 1.5rem;
              cursor: pointer;
              padding: 4px;
          }
          .templates-modal.mobile-optimized .template-categories {
              padding: 12px;
              overflow-x: auto;
              white-space: nowrap;
              -webkit-overflow-scrolling: touch;
              background: #f5f5f5;
          }
          .templates-modal.mobile-optimized .templates-grid.mobile-grid {
              flex: 1;
              overflow-y: auto;
              padding: 12px;
              grid-template-columns: repeat(2, 1fr);
              gap: 12px;
          }
          .templates-modal.mobile-optimized .template-item {
              padding: 8px;
          }
          .templates-modal.mobile-optimized .template-item img {
              height: 120px;
          }
      `;
      document.head.appendChild(style);

      // Load templates (mock data for now)
      const templatesGrid = modal.querySelector('.templates-grid');
      const mockTemplates = [
          { name: 'Birthday Balloons', category: 'birthday', preview: 'balloon-icon' },
          { name: 'Wedding Elegant', category: 'wedding', preview: 'heart-icon' },
          { name: 'Team Spirit', category: 'sports', preview: 'star-icon' }
      ];

      mockTemplates.forEach(template => {
          const item = document.createElement('div');
          item.className = 'template-item';
          item.innerHTML = `
              <div style="background: #f0f0f0; height: 120px; display: flex; align-items: center; justify-content: center; border-radius: 8px;">
                  <svg width="60" height="60" fill="var(--swpd-primary)" opacity="0.5">
                      <circle cx="30" cy="30" r="25"/>
                  </svg>
              </div>
              <p style="margin: 8px 0 0; font-size: 0.9rem;">${template.name}</p>
          `;
          item.addEventListener('click', () => {
              // Apply template logic here
              this.showNotification('Template applied!');
              modal.remove();
              style.remove();
          });
          templatesGrid.appendChild(item);
      });

      // Category filter
      modal.querySelectorAll('.category-btn').forEach(btn => {
          btn.addEventListener('click', () => {
              modal.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
              btn.classList.add('active');
              // Filter logic here
          });
      });

      // Close button
      modal.querySelector('.close-modal').addEventListener('click', () => {
          modal.remove();
          style.remove();
      });
  }
};
