/**
 * Main application entry point
 */
class SequinSwagApp {
    constructor() {
        this.productManager = null;
        this.designTool = null;
        this.isInitialized = false;

        this.init();
    }

    async init() {
        try {
            // Wait for DOM to be fully loaded
            if (document.readyState === 'loading') {
                await new Promise(resolve => {
                    document.addEventListener('DOMContentLoaded', resolve);
                });
            }

            // Wait for required dependencies
            await this.waitForDependencies();

            // Initialize managers
            this.initializeManagers();

            // Setup global event listeners
            this.setupGlobalEvents();

            // Setup cart functionality
            this.setupCart();

            this.isInitialized = true;
            console.log('SequinSwag App initialized successfully');

        } catch (error) {
            console.error('Failed to initialize SequinSwag App:', error);
            this.showError('Application failed to initialize. Please refresh the page.');
        }
    }

    async waitForDependencies() {
        // Wait for Fabric.js
        let attempts = 0;
        while (typeof fabric === 'undefined' && attempts < 50) {
            await new Promise(resolve => setTimeout(resolve, 100));
            attempts++;
        }

        if (typeof fabric === 'undefined') {
            throw new Error('Fabric.js failed to load');
        }

        console.log('Dependencies loaded successfully');
    }

    initializeManagers() {
        // Product manager should already be initialized
        this.productManager = window.productManager;
        this.designTool = window.designTool;

        if (!this.productManager) {
            console.error('ProductManager not initialized');
        }

        if (!this.designTool) {
            console.error('DesignTool not initialized');
        }
    }

    setupGlobalEvents() {
        // Handle window resize
        window.addEventListener('resize', this.debounce(() => {
            this.handleResize();
        }, 250));

        // Handle orientation change on mobile
        window.addEventListener('orientationchange', () => {
            setTimeout(() => this.handleResize(), 100);
        });

        // Add to cart functionality
        document.getElementById('add-to-cart-btn')?.addEventListener('click', (e) => {
            e.preventDefault();
            this.handleAddToCart();
        });

        // Handle escape key to close designer
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const lightbox = document.getElementById('designer-lightbox');
                if (lightbox && lightbox.classList.contains('active')) {
                    this.designTool?.closeDesigner();
                }
            }
        });

        // Prevent accidental page refresh when designer is open
        window.addEventListener('beforeunload', (e) => {
            const lightbox = document.getElementById('designer-lightbox');
            if (lightbox && lightbox.classList.contains('active')) {
                const currentDesign = this.designTool?.getCurrentDesign();
                if (currentDesign) {
                    e.preventDefault();
                    e.returnValue = '';
                    return '';
                }
            }
        });
    }

    setupCart() {
        // Initialize cart if not exists
        if (!this.getCartData()) {
            this.saveCartData({
                items: [],
                total: 0,
                count: 0
            });
        }

        this.updateCartUI();
    }

    handleResize() {
        // Handle canvas resize if designer is open
        if (this.designTool && this.designTool.canvas) {
            const container = document.querySelector('.canvas-container');
            if (container) {
                const rect = container.getBoundingClientRect();
                const maxWidth = rect.width - 40; // Account for padding
                const maxHeight = rect.height - 40;

                if (maxWidth > 0 && maxHeight > 0) {
                    const canvas = this.designTool.canvas;
                    const scale = Math.min(maxWidth / 400, maxHeight / 400, 1);

                    canvas.setDimensions({
                        width: 400 * scale,
                        height: 400 * scale
                    });

                    canvas.setZoom(scale);
                    canvas.renderAll();
                }
            }
        }
    }

    async handleAddToCart() {
        if (!this.productManager || !this.productManager.getCurrentVariant()) {
            this.showNotification('Please select product options first', 'error');
            return;
        }

        const variant = this.productManager.getCurrentVariant();
        const product = this.productManager.getCurrentProduct();
        const quantity = parseInt(document.getElementById('quantity')?.value || 1);
        const design = this.designTool?.getCurrentDesign();

        // Validate design requirement (you can make this optional)
        if (!design) {
            const proceed = confirm('You haven\'t added a custom design. Do you want to continue without a design?');
            if (!proceed) return;
        }

        try {
            const cartItem = {
                id: Date.now(), // Simple ID generation
                productId: product.id,
                variantId: variant.id,
                name: product.name,
                sku: variant.sku,
                price: variant.price,
                quantity: quantity,
                attributes: variant.attributes,
                image: variant.image || product.images[0]?.src,
                design: design ? {
                    preview: design.preview,
                    data: design.data,
                    timestamp: design.timestamp
                } : null
            };

            this.addToCart(cartItem);
            this.showNotification('Product added to cart successfully!', 'success');

            // Optional: Reset form
            this.resetProductForm();

        } catch (error) {
            console.error('Error adding to cart:', error);
            this.showNotification('Error adding product to cart', 'error');
        }
    }

    addToCart(item) {
        const cart = this.getCartData();

        // Check if same item already exists
        const existingIndex = cart.items.findIndex(cartItem =>
            cartItem.productId === item.productId &&
            cartItem.variantId === item.variantId &&
            JSON.stringify(cartItem.design?.data) === JSON.stringify(item.design?.data)
        );

        if (existingIndex >= 0) {
            // Update quantity
            cart.items[existingIndex].quantity += item.quantity;
        } else {
            // Add new item
            cart.items.push(item);
        }

        // Update totals
        this.updateCartTotals(cart);
        this.saveCartData(cart);
        this.updateCartUI();
    }

    updateCartTotals(cart) {
        cart.total = cart.items.reduce((total, item) => total + (item.price * item.quantity), 0);
        cart.count = cart.items.reduce((count, item) => count + item.quantity, 0);
    }

    getCartData() {
        try {
            return JSON.parse(localStorage.getItem('sequinswag_cart') || '{}');
        } catch {
            return { items: [], total: 0, count: 0 };
        }
    }

    saveCartData(cart) {
        localStorage.setItem('sequinswag_cart', JSON.stringify(cart));
    }

    updateCartUI() {
        const cart = this.getCartData();

        // Update cart count in nav (if exists)
        const cartCount = document.querySelector('.cart-count');
        if (cartCount) {
            cartCount.textContent = cart.count || 0;
            cartCount.style.display = cart.count > 0 ? 'inline' : 'none';
        }
    }

    resetProductForm() {
        // Reset quantity
        const quantityInput = document.getElementById('quantity');
        if (quantityInput) {
            quantityInput.value = 1;
        }

        // Clear design preview
        this.productManager?.removeDesign();

        // Reset variant selection (optional)
        // You might want to keep the selection for user convenience
    }

    showNotification(message, type = 'info') {
        // Remove existing notifications
        const existing = document.querySelectorAll('.notification');
        existing.forEach(notif => notif.remove());

        // Create notification
        const notification = document.createElement('div');
        notification.className = `notification ${type} fade-in`;
        notification.textContent = message;

        document.body.appendChild(notification);

        // Auto-remove after 4 seconds
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease forwards';
            setTimeout(() => notification.remove(), 300);
        }, 4000);
    }

    showError(message) {
        this.showNotification(message, 'error');
    }

    // Utility function for debouncing
    debounce(func, wait, immediate) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                timeout = null;
                if (!immediate) func(...args);
            };
            const callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func(...args);
        };
    }

    // Development helpers
    getCart() {
        return this.getCartData();
    }

    clearCart() {
        this.saveCartData({ items: [], total: 0, count: 0 });
        this.updateCartUI();
        this.showNotification('Cart cleared', 'info');
    }

    exportDesign() {
        if (this.designTool && this.designTool.canvas) {
            const design = this.designTool.getCurrentDesign();
            if (design) {
                // Create download link
                const link = document.createElement('a');
                link.download = `sequinswag-design-${Date.now()}.png`;
                link.href = design.preview;
                link.click();
            } else {
                this.showNotification('No design to export', 'error');
            }
        }
    }
}

// Add CSS for slideOut animation
const additionalStyles = `
@keyframes slideOut {
    from { transform: translateX(0); opacity: 1; }
    to { transform: translateX(100%); opacity: 0; }
}
`;

const styleSheet = document.createElement('style');
styleSheet.textContent = additionalStyles;
document.head.appendChild(styleSheet);

// Initialize app when DOM is ready
const sequinSwagApp = new SequinSwagApp();

// Make app available globally for debugging
window.sequinSwagApp = sequinSwagApp;