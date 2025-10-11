/**
 * SWPD Size Selector - Optimized JavaScript
 * Lightweight and fast size selection with multi-size support
 */

(function($) {
    'use strict';

    class SWPDSizeSelector {
        constructor() {
            this.config = window.swpdSizeSelector || {};
            this.selectedSize = null;
            this.multiSizeData = {};
            this.cache = new Map();
            this.isProcessing = false;
            
            this.init();
        }

        init() {
            this.bindEvents();
            this.loadInitialData();
        }

        bindEvents() {
            // Size selection
            $(document).on('click', '.size-option:not(:disabled)', (e) => {
                this.selectSize(e.currentTarget);
            });

            // Size chart
            $(document).on('click', '#show-size-chart', () => {
                this.showSizeChart();
            });

            // Multi-size selector
            $(document).on('click', '#multi-size-selector', () => {
                this.showMultiSizeModal();
            });

            // Modal close
            $(document).on('click', '.modal-close, .swpd-modal', (e) => {
                if (e.target === e.currentTarget) {
                    this.closeModals();
                }
            });

            // Quantity controls in multi-size modal
            $(document).on('click', '.quantity-btn', (e) => {
                this.handleQuantityChange(e);
            });

            // Quantity input changes
            $(document).on('input change', '.quantity-input', (e) => {
                this.updateQuantity(e.currentTarget);
            });

            // Add all to cart
            $(document).on('click', '#add-all-to-cart', () => {
                this.addAllToCart();
            });

            // Keyboard navigation
            $(document).on('keydown', '.size-option', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.selectSize(e.currentTarget);
                }
            });

            // ESC to close modals
            $(document).on('keydown', (e) => {
                if (e.key === 'Escape') {
                    this.closeModals();
                }
            });
        }

        selectSize(sizeElement) {
            if (this.isProcessing) return;

            const $size = $(sizeElement);
            const variationId = $size.data('variation-id');
            const sizeName = $size.data('size');
            const price = $size.data('price');
            const inStock = $size.data('stock') === 'true';

            if (!inStock) {
                this.showMessage('This size is currently out of stock', 'error');
                return;
            }

            // Update visual selection
            $('.size-option').removeClass('selected');
            $size.addClass('selected');

            // Store selection
            this.selectedSize = {
                variationId: variationId,
                size: sizeName,
                price: price,
                element: sizeElement
            };

            // Update size info
            this.updateSizeInfo();

            // Update main product form if it exists
            this.updateMainProductForm();

            // Trigger custom event
            $(document).trigger('swpd_size_selected', [this.selectedSize]);

            // Add subtle success feedback
            this.addFeedback(sizeElement, 'selected');
        }

        updateSizeInfo() {
            const info = $('#size-info');
            if (!this.selectedSize || !info.length) return;

            const $sizeElement = $(this.selectedSize.element);
            const stockText = $sizeElement.find('.low-stock').text() || 
                            ($sizeElement.hasClass('in-stock') ? 'In Stock' : 'Limited Stock');

            info.find('.size-label').text(`Size: ${this.selectedSize.size.toUpperCase()}`);
            info.find('.size-price').html($sizeElement.find('.price').html());
            info.find('.size-stock').text(stockText);

            info.slideDown(200);
        }

        updateMainProductForm() {
            // Update WooCommerce variation form if present
            const $form = $('form.variations_form');
            if (!$form.length) return;

            const $variationSelect = $form.find('select[name*="size"]');
            if ($variationSelect.length) {
                $variationSelect.val(this.selectedSize.size.toLowerCase()).trigger('change');
            }

            // Update variation ID
            $form.find('input[name="variation_id"]').val(this.selectedSize.variationId);
        }

        showSizeChart() {
            const modal = $('#size-chart-modal');
            const content = $('#size-chart-content');

            modal.show();
            content.html('<div class="size-selector-loading">Loading size chart...</div>');

            // Check cache first
            const cacheKey = `size_chart_${this.config.product_id}`;
            if (this.cache.has(cacheKey)) {
                this.renderSizeChart(this.cache.get(cacheKey));
                return;
            }

            // Load via AJAX
            $.post(this.config.ajax_url, {
                action: 'swpd_get_size_chart',
                nonce: this.config.nonce,
                product_id: this.config.product_id
            })
            .done((response) => {
                if (response.success) {
                    this.cache.set(cacheKey, response.data.chart);
                    this.renderSizeChart(response.data.chart);
                } else {
                    content.html('<div class="size-selector-message error">Failed to load size chart</div>');
                }
            })
            .fail(() => {
                content.html('<div class="size-selector-message error">Error loading size chart</div>');
            });
        }

        renderSizeChart(chartData) {
            const content = $('#size-chart-content');
            let html = '';

            if (chartData.measurements && chartData.measurements.length) {
                html += '<table class="size-chart-table"><thead><tr>';
                html += '<th>Size</th><th>Chest</th><th>Length</th><th>Sleeve</th>';
                html += '</tr></thead><tbody>';

                chartData.measurements.forEach(measurement => {
                    html += `<tr>
                        <td><strong>${measurement.size}</strong></td>
                        <td>${measurement.chest}</td>
                        <td>${measurement.length}</td>
                        <td>${measurement.sleeve}</td>
                    </tr>`;
                });

                html += '</tbody></table>';
            }

            if (chartData.notes && chartData.notes.length) {
                html += '<div class="size-chart-notes">';
                html += '<h4>Important Notes:</h4><ul>';
                chartData.notes.forEach(note => {
                    html += `<li>${note}</li>`;
                });
                html += '</ul></div>';
            }

            content.html(html || '<div class="size-selector-message">No size chart available</div>');
        }

        showMultiSizeModal() {
            const modal = $('#multi-size-modal');
            const grid = $('#multi-size-grid');

            modal.show();
            grid.html('<div class="size-selector-loading">Loading size options...</div>');

            this.loadMultiSizeOptions();
        }

        loadMultiSizeOptions() {
            const grid = $('#multi-size-grid');
            const sizes = this.getSizeOptionsData();

            if (!sizes.length) {
                grid.html('<div class="size-selector-message error">No size options available</div>');
                return;
            }

            let html = '';
            sizes.forEach(size => {
                const isOutOfStock = !size.inStock;
                const stockClass = isOutOfStock ? 'out-of-stock' : '';
                const stockText = isOutOfStock ? 'Out of Stock' : 
                    (size.stockQuantity && size.stockQuantity <= 10 ? `${size.stockQuantity} left` : 'In Stock');

                html += `
                    <div class="multi-size-item ${stockClass}" data-variation-id="${size.variationId}" data-size="${size.size}">
                        <div class="multi-size-info">
                            <div class="multi-size-label">${size.size.toUpperCase()}</div>
                            <div class="multi-size-details">
                                <span class="multi-size-price">${this.formatPrice(size.price)}</span>
                                <span class="multi-size-stock ${stockClass}">${stockText}</span>
                            </div>
                        </div>
                        <div class="multi-size-quantity">
                            <div class="quantity-control">
                                <button type="button" class="quantity-btn minus" data-action="minus" ${isOutOfStock ? 'disabled' : ''}>−</button>
                                <input type="number" class="quantity-input" value="0" min="0" max="${size.stockQuantity || 999}" ${isOutOfStock ? 'disabled' : ''}>
                                <button type="button" class="quantity-btn plus" data-action="plus" ${isOutOfStock ? 'disabled' : ''}>+</button>
                            </div>
                        </div>
                    </div>
                `;
            });

            grid.html(html);
            this.updateMultiSizeSummary();
        }

        getSizeOptionsData() {
            const sizes = [];
            $('.size-option').each((index, element) => {
                const $el = $(element);
                sizes.push({
                    variationId: $el.data('variation-id'),
                    size: $el.data('size'),
                    price: parseFloat($el.data('price')) || 0,
                    inStock: $el.data('stock') === 'true',
                    stockQuantity: parseInt($el.data('stock-quantity')) || null
                });
            });
            return sizes;
        }

        handleQuantityChange(e) {
            const $btn = $(e.currentTarget);
            const $input = $btn.siblings('.quantity-input');
            const action = $btn.data('action');
            const currentValue = parseInt($input.val()) || 0;
            const max = parseInt($input.attr('max')) || 999;
            
            let newValue = currentValue;
            
            if (action === 'plus' && currentValue < max) {
                newValue = currentValue + 1;
            } else if (action === 'minus' && currentValue > 0) {
                newValue = currentValue - 1;
            }
            
            $input.val(newValue);
            this.updateQuantity($input[0]);
        }

        updateQuantity(input) {
            const $input = $(input);
            const $item = $input.closest('.multi-size-item');
            const variationId = $item.data('variation-id');
            const size = $item.data('size');
            const quantity = parseInt($input.val()) || 0;
            const max = parseInt($input.attr('max')) || 999;

            // Validate quantity
            if (quantity < 0) {
                $input.val(0);
                return;
            }
            if (quantity > max) {
                $input.val(max);
                return;
            }

            // Update data
            if (quantity > 0) {
                this.multiSizeData[variationId] = {
                    size: size,
                    quantity: quantity,
                    variationId: variationId
                };
            } else {
                delete this.multiSizeData[variationId];
            }

            this.updateMultiSizeSummary();
        }

        updateMultiSizeSummary() {
            const totalQuantity = Object.values(this.multiSizeData)
                .reduce((sum, item) => sum + item.quantity, 0);

            $('#total-quantity').text(totalQuantity);

            const $addBtn = $('#add-all-to-cart');
            if (totalQuantity > 0) {
                $addBtn.prop('disabled', false).text(`Add ${totalQuantity} Items to Cart`);
            } else {
                $addBtn.prop('disabled', true).text('Add All to Cart');
            }
        }

        addAllToCart() {
            if (this.isProcessing || Object.keys(this.multiSizeData).length === 0) {
                return;
            }

            this.isProcessing = true;
            const $btn = $('#add-all-to-cart');
            const originalText = $btn.text();
            
            $btn.prop('disabled', true).text('Adding to Cart...');

            // Prepare data
            const sizesData = Object.values(this.multiSizeData).map(item => ({
                variation_id: item.variationId,
                quantity: item.quantity,
                design_data: this.getDesignDataForSize(item.size)
            }));

            $.post(this.config.ajax_url, {
                action: 'swpd_quick_add_sizes',
                nonce: this.config.nonce,
                product_id: this.config.product_id,
                sizes: sizesData
            })
            .done((response) => {
                if (response.success) {
                    this.showMessage(response.data.message, 'success');
                    this.closeModals();
                    
                    // Reset multi-size data
                    this.multiSizeData = {};
                    
                    // Update cart if cart widget exists
                    $(document.body).trigger('wc_fragment_refresh');
                } else {
                    this.showMessage(response.data || 'Failed to add items to cart', 'error');
                }
            })
            .fail(() => {
                this.showMessage('Error adding items to cart', 'error');
            })
            .always(() => {
                this.isProcessing = false;
                $btn.prop('disabled', false).text(originalText);
            });
        }

        getDesignDataForSize(size) {
            // Get design data from the main designer if available
            if (window.swpdDesigner && typeof window.swpdDesigner.getDesignData === 'function') {
                return window.swpdDesigner.getDesignData();
            }
            return '';
        }

        closeModals() {
            $('.swpd-modal').hide();
        }

        showMessage(message, type = 'info') {
            const $container = $('.swpd-size-selector-container');
            const $existing = $container.find('.size-selector-message');
            
            $existing.remove();
            
            const $message = $(`<div class="size-selector-message ${type}">${message}</div>`);
            $container.prepend($message);
            
            setTimeout(() => {
                $message.fadeOut(300, () => $message.remove());
            }, 3000);
        }

        addFeedback(element, type) {
            const $el = $(element);
            $el.addClass(`feedback-${type}`);
            
            setTimeout(() => {
                $el.removeClass(`feedback-${type}`);
            }, 300);
        }

        formatPrice(price) {
            // Basic price formatting - can be enhanced based on WooCommerce settings
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD'
            }).format(price);
        }

        loadInitialData() {
            // Pre-select size if coming from URL params
            const urlParams = new URLSearchParams(window.location.search);
            const selectedSize = urlParams.get('size');
            
            if (selectedSize) {
                const $sizeOption = $(`.size-option[data-size="${selectedSize}"]`);
                if ($sizeOption.length && !$sizeOption.is(':disabled')) {
                    this.selectSize($sizeOption[0]);
                }
            }
        }

        // Public API
        getSelectedSize() {
            return this.selectedSize;
        }

        selectSizeByName(sizeName) {
            const $sizeOption = $(`.size-option[data-size="${sizeName}"]`);
            if ($sizeOption.length) {
                this.selectSize($sizeOption[0]);
                return true;
            }
            return false;
        }

        reset() {
            this.selectedSize = null;
            this.multiSizeData = {};
            $('.size-option').removeClass('selected');
            $('#size-info').hide();
            this.closeModals();
        }
    }

    // Initialize when DOM is ready
    $(document).ready(() => {
        if ($('#swpd-smart-size-selector').length) {
            window.swpdSizeSelector = new SWPDSizeSelector();
        }
    });

    // Make available globally
    window.SWPDSizeSelector = SWPDSizeSelector;

})(jQuery);