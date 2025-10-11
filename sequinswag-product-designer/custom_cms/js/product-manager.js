/**
 * Product Manager - Handles product data and UI updates
 */
class ProductManager {
    constructor() {
        this.currentProduct = null;
        this.currentVariant = null;
        this.productData = {};
        this.init();
    }

    async init() {
        this.setupEventListeners();
        await this.loadSampleProduct();
    }

    setupEventListeners() {
        // Size selector
        const sizeSelector = document.getElementById('size-selector');
        if (sizeSelector) {
            sizeSelector.addEventListener('change', (e) => {
                this.onVariantChange('size', e.target.value);
            });
        }

        // Color options
        const colorOptions = document.getElementById('color-options');
        if (colorOptions) {
            colorOptions.addEventListener('click', (e) => {
                if (e.target.classList.contains('color-swatch')) {
                    this.onColorChange(e.target);
                }
            });
        }

        // Quantity controls
        document.getElementById('qty-minus')?.addEventListener('click', () => {
            this.changeQuantity(-1);
        });

        document.getElementById('qty-plus')?.addEventListener('click', () => {
            this.changeQuantity(1);
        });

        // Thumbnail images
        document.addEventListener('click', (e) => {
            if (e.target.closest('.thumbnail')) {
                this.changeMainImage(e.target.closest('.thumbnail'));
            }
        });

        // Design actions
        document.getElementById('edit-design-btn')?.addEventListener('click', () => {
            if (window.designTool) {
                window.designTool.openDesigner();
            }
        });

        document.getElementById('remove-design-btn')?.addEventListener('click', () => {
            this.removeDesign();
        });
    }

    async loadSampleProduct() {
        // Load sample product data - in real implementation this would come from your data fetcher
        this.currentProduct = {
            id: 1,
            name: "Premium Sequin Pillow Cover",
            description: "Create stunning custom designs on our premium sequin pillow covers. Perfect for home decor, gifts, or personal style. High-quality sequins that flip to reveal your design.",
            price: 29.99,
            originalPrice: 39.99,
            images: [
                {
                    id: 1,
                    src: "https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=500&h=500&fit=crop",
                    alt: "Sequin Pillow - Main View"
                },
                {
                    id: 2,
                    src: "https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=500&h=500&fit=crop",
                    alt: "Sequin Pillow - Side View"
                },
                {
                    id: 3,
                    src: "https://images.unsplash.com/photo-1567538096630-e0c55bd6374c?w=500&h=500&fit=crop",
                    alt: "Sequin Pillow - Detail View"
                }
            ],
            variants: [
                {
                    id: 101,
                    attributes: { size: '16x16', color: 'gold' },
                    price: 29.99,
                    sku: 'SP-GOLD-16',
                    image: "https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=500&h=500&fit=crop",
                    design_tool_layer: {
                        baseImage: "https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=400&h=400&fit=crop",
                        alphaMask: "https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=400&h=400&fit=crop&blend=overlay",
                        designArea: { x: 50, y: 50, width: 300, height: 300 }
                    }
                },
                {
                    id: 102,
                    attributes: { size: '16x16', color: 'silver' },
                    price: 29.99,
                    sku: 'SP-SILVER-16',
                    image: "https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=500&h=500&fit=crop",
                    design_tool_layer: {
                        baseImage: "https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=400&h=400&fit=crop",
                        alphaMask: "https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=400&h=400&fit=crop&blend=overlay",
                        designArea: { x: 50, y: 50, width: 300, height: 300 }
                    }
                },
                {
                    id: 103,
                    attributes: { size: '20x20', color: 'gold' },
                    price: 39.99,
                    sku: 'SP-GOLD-20',
                    image: "https://images.unsplash.com/photo-1567538096630-e0c55bd6374c?w=500&h=500&fit=crop",
                    design_tool_layer: {
                        baseImage: "https://images.unsplash.com/photo-1567538096630-e0c55bd6374c?w=400&h=400&fit=crop",
                        alphaMask: "https://images.unsplash.com/photo-1567538096630-e0c55bd6374c?w=400&h=400&fit=crop&blend=overlay",
                        designArea: { x: 60, y: 60, width: 280, height: 280 }
                    }
                }
            ],
            attributes: {
                size: {
                    name: 'Size',
                    options: ['16x16', '20x20']
                },
                color: {
                    name: 'Color',
                    options: [
                        { name: 'Gold', value: 'gold', color: '#FFD700' },
                        { name: 'Silver', value: 'silver', color: '#C0C0C0' }
                    ]
                }
            }
        };

        this.updateProductUI();
        this.loadVariantOptions();
    }

    updateProductUI() {
        const product = this.currentProduct;

        // Update basic info
        document.getElementById('product-title').textContent = product.name;
        document.getElementById('product-price').textContent = `$${product.price.toFixed(2)}`;
        document.getElementById('original-price').textContent = `$${product.originalPrice.toFixed(2)}`;
        document.getElementById('product-description').innerHTML = `<p>${product.description}</p>`;
        document.getElementById('total-price').textContent = product.price.toFixed(2);

        // Update main image
        if (product.images && product.images.length > 0) {
            document.getElementById('main-product-image').src = product.images[0].src;
            document.getElementById('main-product-image').alt = product.images[0].alt;
        }

        // Update thumbnail images
        this.updateThumbnails();
    }

    updateThumbnails() {
        const thumbnailContainer = document.querySelector('.thumbnail-images');
        if (!thumbnailContainer || !this.currentProduct.images) return;

        thumbnailContainer.innerHTML = '';

        this.currentProduct.images.forEach((image, index) => {
            const thumbnail = document.createElement('div');
            thumbnail.className = `thumbnail ${index === 0 ? 'active' : ''}`;
            thumbnail.innerHTML = `<img src="${image.src}" alt="${image.alt}">`;
            thumbnail.dataset.imageIndex = index;
            thumbnailContainer.appendChild(thumbnail);
        });
    }

    loadVariantOptions() {
        const product = this.currentProduct;

        // Load size options
        const sizeSelector = document.getElementById('size-selector');
        if (sizeSelector && product.attributes?.size) {
            sizeSelector.innerHTML = '<option value="">Select Size</option>';
            product.attributes.size.options.forEach(size => {
                const option = document.createElement('option');
                option.value = size;
                option.textContent = size;
                sizeSelector.appendChild(option);
            });
        }

        // Load color options
        const colorOptions = document.getElementById('color-options');
        if (colorOptions && product.attributes?.color) {
            colorOptions.innerHTML = '';
            product.attributes.color.options.forEach(color => {
                const swatch = document.createElement('div');
                swatch.className = 'color-swatch';
                swatch.style.backgroundColor = color.color;
                swatch.dataset.color = color.value;
                swatch.title = color.name;
                colorOptions.appendChild(swatch);
            });
        }
    }

    onVariantChange(attribute, value) {
        this.findAndSetVariant();
        this.updatePrice();
    }

    onColorChange(colorElement) {
        // Remove active class from all color swatches
        document.querySelectorAll('.color-swatch').forEach(swatch => {
            swatch.classList.remove('active');
        });

        // Add active class to clicked swatch
        colorElement.classList.add('active');

        this.findAndSetVariant();
        this.updatePrice();
    }

    findAndSetVariant() {
        const sizeValue = document.getElementById('size-selector')?.value;
        const colorValue = document.querySelector('.color-swatch.active')?.dataset.color;

        if (sizeValue && colorValue) {
            const variant = this.currentProduct.variants.find(v =>
                v.attributes.size === sizeValue && v.attributes.color === colorValue
            );

            if (variant) {
                this.currentVariant = variant;
                this.updateVariantUI();
                this.enableAddToCart();
            }
        } else {
            this.currentVariant = null;
            this.disableAddToCart();
        }
    }

    updateVariantUI() {
        if (!this.currentVariant) return;

        // Update main image if variant has specific image
        if (this.currentVariant.image) {
            document.getElementById('main-product-image').src = this.currentVariant.image;
        }

        // Update price
        this.updatePrice();
    }

    updatePrice() {
        const price = this.currentVariant ? this.currentVariant.price : this.currentProduct.price;
        const quantity = parseInt(document.getElementById('quantity')?.value || 1);
        const total = price * quantity;

        document.getElementById('product-price').textContent = `$${price.toFixed(2)}`;
        document.getElementById('total-price').textContent = total.toFixed(2);
    }

    changeQuantity(delta) {
        const quantityInput = document.getElementById('quantity');
        if (!quantityInput) return;

        const currentQty = parseInt(quantityInput.value);
        const newQty = Math.max(1, Math.min(99, currentQty + delta));

        quantityInput.value = newQty;
        this.updatePrice();
    }

    changeMainImage(thumbnail) {
        const imageIndex = parseInt(thumbnail.dataset.imageIndex);
        const image = this.currentProduct.images[imageIndex];

        if (image) {
            document.getElementById('main-product-image').src = image.src;
            document.getElementById('main-product-image').alt = image.alt;

            // Update thumbnail active state
            document.querySelectorAll('.thumbnail').forEach(thumb => {
                thumb.classList.remove('active');
            });
            thumbnail.classList.add('active');
        }
    }

    enableAddToCart() {
        const addToCartBtn = document.getElementById('add-to-cart-btn');
        if (addToCartBtn) {
            addToCartBtn.disabled = false;
            addToCartBtn.textContent = `Add to Cart - $${document.getElementById('total-price').textContent}`;
        }
    }

    disableAddToCart() {
        const addToCartBtn = document.getElementById('add-to-cart-btn');
        if (addToCartBtn) {
            addToCartBtn.disabled = true;
            addToCartBtn.textContent = 'Select options first';
        }
    }

    showDesignPreview(designDataUrl) {
        const designPreview = document.getElementById('design-preview');
        const previewImage = document.getElementById('design-preview-image');

        if (designPreview && previewImage && designDataUrl) {
            previewImage.src = designDataUrl;
            designPreview.style.display = 'block';

            // Enable add to cart if variant is selected
            if (this.currentVariant) {
                this.enableAddToCart();
            }
        }
    }

    removeDesign() {
        const designPreview = document.getElementById('design-preview');
        if (designPreview) {
            designPreview.style.display = 'none';
        }

        // Clear design data
        if (window.designTool) {
            window.designTool.clearDesign();
        }
    }

    getCurrentVariantDesignData() {
        return this.currentVariant?.design_tool_layer || null;
    }

    getCurrentVariant() {
        return this.currentVariant;
    }

    getCurrentProduct() {
        return this.currentProduct;
    }
}

// Initialize when DOM is loaded
if (typeof window !== 'undefined') {
    window.productManager = new ProductManager();
}