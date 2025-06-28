<?php
/**
 * Modal Scripts
 *
 * @package MunchMakers_Product_Customizer
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// This file should only contain the modal-specific JavaScript
// All button positioning and styling should be handled separately
?>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('munchmakers-modal');
    if (!modal) return;
    
    const isVariable = <?php echo $is_variable_product ? 'true' : 'false'; ?>;
    const availableVariations = <?php echo json_encode( $available_variations ); ?>;
    const attributeNamesMap = <?php echo $attribute_map_json; ?>;
    const defaultVariation = <?php echo $default_variation_json; ?>;
    const variationInputs = modal.querySelectorAll('#modal-variations-container select, #modal-variations-container input[type="hidden"]');
    const productId = <?php echo $product_id; ?>;
    let currentVariation = null;
    let currentPricingData = <?php echo $pricing_json; ?>;
    let uploadedFileUrls = [];

    function closeModal() {
        modal.style.display = 'none';
        document.body.style.overflow = '';
        const form = document.querySelector('form.cart.munchmakers-active');
        if (form) form.classList.remove('munchmakers-active');
    }

    function showStep(stepName) {
        // Hide ALL steps first
        modal.querySelectorAll('.modal-step').forEach(step => {
            step.classList.remove('active');
            step.style.display = 'none';
        });
        
        // Show only the target step
        const targetStep = modal.querySelector('#step-' + stepName);
        if (targetStep) {
            targetStep.classList.add('active');
            targetStep.style.display = 'block';
            
            // Scroll to top of modal body when changing steps
            const modalBody = modal.querySelector('.modal-body');
            if (modalBody) {
                modalBody.scrollTop = 0;
            }
        }
        
        updateProgressBar(stepName);
    }
    
    function updateProgressBar(currentStepName) {
        const stepMapping = {
            'variations': 1,
            'quantity': isVariable ? 2 : 1,
            'artwork': isVariable ? 3 : 2
        };
        const currentStepNumber = stepMapping[currentStepName];
        const maxSteps = isVariable ? 3 : 2;
        
        modal.querySelectorAll('.progress-step').forEach(step => step.classList.remove('active', 'completed'));
        
        for (let i = 1; i <= maxSteps; i++) {
            const stepElement = modal.querySelector(`[data-step="${i}"]`);
            if (stepElement) {
                if (i < currentStepNumber) stepElement.classList.add('completed');
                else if (i === currentStepNumber) stepElement.classList.add('active');
            }
        }
        
        if (!isVariable) {
            const step3 = modal.querySelector('[data-step="3"]');
            const line2 = step3?.previousElementSibling;
            if (step3) step3.style.display = 'none';
            if (line2 && line2.classList.contains('progress-line')) line2.style.display = 'none';
        }
    }

    function initColorSwatches() {
        modal.querySelectorAll('.color-swatches').forEach(swatchContainer => {
            const hiddenInput = modal.querySelector(`#${swatchContainer.dataset.attribute}`);
            swatchContainer.querySelectorAll('.color-swatch').forEach(swatch => {
                swatch.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    swatchContainer.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('selected'));
                    this.classList.add('selected');
                    if (hiddenInput) hiddenInput.value = this.dataset.value;
                    hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                });
            });
        });
    }

    function findMatchingVariation() {
        let selectedOptions = {};
        let allOptionsSelected = true;
        variationInputs.forEach(input => {
            if (!input.value) allOptionsSelected = false;
            selectedOptions[input.name] = input.value;
        });
        if (!allOptionsSelected) return null;
        return availableVariations.find(v => Object.keys(v.attributes).every(k => v.attributes[k] === '' || v.attributes[k] === selectedOptions[k])) || null;
    }

    function fetchVariationPricing(variationId) {
        const data = new FormData();
        data.append('action', 'munchmakers_get_variation_pricing');
        data.append('product_id', productId);
        data.append('variation_id', variationId);
        data.append('nonce', munchmakers_ajax.pricing_nonce);
        
        fetch(munchmakers_ajax.ajax_url, { method: 'POST', body: data })
        .then(response => response.json())
        .then(result => {
            if (result.success && result.data) {
                currentPricingData = result.data;
                initQuantitySlider();
                updateAllPricingTooltips();
            }
        });
    }

    function updateVariationState() {
        currentVariation = findMatchingVariation();
        const nextBtn = modal.querySelector('#next-to-quantity');
        
        if (currentVariation) {
            nextBtn.disabled = false;
            fetchVariationPricing(currentVariation.variation_id);
            
            let selectionParts = [];
            for(const attr_key in currentVariation.attributes) {
                const slug = currentVariation.attributes[attr_key];
                if(slug) {
                    const taxonomyName = attr_key.replace('attribute_', '');
                    if (attributeNamesMap[taxonomyName] && attributeNamesMap[taxonomyName][slug]) {
                       selectionParts.push(attributeNamesMap[taxonomyName][slug]);
                    } else {
                       selectionParts.push(slug.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase()));
                    }
                }
            }
            
            let selectionText = 'Current Selection: ' + selectionParts.join(', ');
            modal.querySelector('#variation-step-selection').textContent = selectionText;
            modal.querySelector('#quantity-step-selection').textContent = selectionText;
            document.querySelector('#munchmakers_selected_options').value = selectionText;
        } else {
            nextBtn.disabled = true;
            modal.querySelector('#variation-step-selection').textContent = 'Please select all options.';
            modal.querySelector('#quantity-step-selection').textContent = '';
            currentPricingData = <?php echo $pricing_json; ?>;
            updateAllPricingTooltips();
        }
    }

    function initQuantitySlider() {
        const slider = modal.querySelector('#modal-slider');
        const quantityInput = modal.querySelector('#modal-quantity');
        if (typeof window.noUiSlider === 'undefined' || !slider || !quantityInput) return;

        if (slider.noUiSlider) slider.noUiSlider.destroy();
        const minQty = currentPricingData.moq || 1;
        const interval = currentPricingData.interval || 1;
        const maxQty = Math.max(1000, currentPricingData.max_qty || 1000);
        quantityInput.min = minQty;
        quantityInput.step = interval;
        
        if (parseInt(quantityInput.value) < minQty) quantityInput.value = minQty;
        
        window.noUiSlider.create(slider, {
            start: [parseInt(quantityInput.value)],
            connect: 'lower', 
            tooltips: true, 
            step: interval,
            range: { 'min': minQty, 'max': maxQty },
            format: { to: v => Math.round(v), from: v => Number(v) }
        });
        
        slider.noUiSlider.on('update', values => {
            const qty = parseInt(values[0], 10);
            quantityInput.value = qty;
            updateModalPricing(qty);
        });
        
        quantityInput.addEventListener('change', () => {
            let newValue = Math.max(minQty, parseInt(quantityInput.value) || minQty);
            if(newValue % interval !== 0) {
                newValue = Math.round(newValue / interval) * interval;
            }
            quantityInput.value = newValue;
            slider.noUiSlider.set(newValue);
        });
        
        updateModalPricing(parseInt(quantityInput.value));
    }

    function updateModalPricing(qty) {
        const basePrice = currentPricingData.base_price || 0;
        const rules = currentPricingData.rules || {};
        let unitPrice = basePrice;
        
        if (rules && Object.keys(rules).length > 0) {
            let matchedPrice = basePrice;
            Object.keys(rules).sort((a,b) => a-b).forEach(ruleQty => {
                 if (qty >= parseInt(ruleQty)) matchedPrice = parseFloat(rules[ruleQty]);
            });
            unitPrice = matchedPrice;
        }
        
        const subtotal = unitPrice * qty;
        const savings = (basePrice * qty) - subtotal;
        modal.querySelector('#modal-unit-price').value = formatPrice(unitPrice);
        modal.querySelector('#modal-subtotal').textContent = formatPrice(subtotal);
        const savingsEl = modal.querySelector('.savings');
        
        if (savings > 0.01) {
            modal.querySelector('#modal-savings').textContent = formatPrice(savings);
            savingsEl.style.display = 'block';
        } else {
            savingsEl.style.display = 'none';
        }
    }
    
    function setDefaultVariation() {
        if (!isVariable || !defaultVariation) return;
        variationInputs.forEach(input => {
            const attributeName = input.name;
            const attributeValue = defaultVariation.attributes[attributeName];
            if (attributeValue) {
                input.value = attributeValue;
                if (input.type === 'hidden') {
                    const swatchContainer = modal.querySelector(`[data-attribute="${attributeName}"]`);
                    if (swatchContainer) {
                        swatchContainer.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('selected'));
                        const targetSwatch = swatchContainer.querySelector(`[data-value="${attributeValue}"]`);
                        if (targetSwatch) targetSwatch.classList.add('selected');
                    }
                }
            }
        });
        updateVariationState();
    }

    function updateArtworkOption() {
        const checkedRadio = modal.querySelector('input[name="modal_artwork_choice"]:checked');
        if (checkedRadio && checkedRadio.value === 'send_later') {
            document.querySelector('#munchmakers_artwork_option').value = 'Will Send After Checkout';
        } else {
            const activeTab = modal.querySelector('.tab-btn.active');
            if (activeTab) {
                let tabText = "Upload Image";
                const tabType = activeTab.dataset.tab;
                if(tabType === 'designer') tabText = 'Using Design Tool';
                if(tabType === 'contact') tabText = 'Contact Us for Design';
                document.querySelector('#munchmakers_artwork_option').value = tabText;
            }
        }
    }

    // File Upload Logic
    const dropZone = document.getElementById('munch-drop-zone');
    const fileInput = document.getElementById('artwork-file-input');
    const fileListContainer = document.getElementById('file-preview-list');

    function handleFiles(files) {
        [...files].forEach(file => {
            if (!file.type.startsWith('image/')) {
                alert(`File "${file.name}" is not a valid image type.`);
                return;
            }
            if (file.size > 10 * 1024 * 1024) {
               alert(`File "${file.name}" is too large (max 10MB).`);
               return;
            }
            uploadFile(file);
        });
    }

    function uploadFile(file) {
        const fileId = 'file-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
        const fileItemHTML = `
            <div class="file-item" id="${fileId}">
                <span class="file-icon">📄</span>
                <span class="file-name">${file.name}</span>
                <div class="file-progress-bar"><div class="file-progress"></div></div>
                <span class="file-status">0%</span>
            </div>`;
        fileListContainer.insertAdjacentHTML('beforeend', fileItemHTML);

        const fileItem = document.getElementById(fileId);
        const progressBar = fileItem.querySelector('.file-progress');
        const statusEl = fileItem.querySelector('.file-status');
        
        const xhr = new XMLHttpRequest();
        const formData = new FormData();
        formData.append('action', 'munchmakers_upload_file');
        formData.append('nonce', munchmakers_ajax.upload_nonce);
        formData.append('artwork_file', file);

        xhr.upload.addEventListener('progress', e => {
            if (e.lengthComputable) {
                const percent = Math.round((e.loaded / e.total) * 100);
                progressBar.style.width = percent + '%';
                statusEl.textContent = percent + '%';
            }
        });

        xhr.addEventListener('load', () => {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        statusEl.textContent = '✓';
                        statusEl.classList.add('success');
                        uploadedFileUrls.push(response.data.url);
                        document.getElementById('munchmakers_artwork_file_urls').value = JSON.stringify(uploadedFileUrls);
                    } else {
                        throw new Error(response.data.message || 'Server error');
                    }
                } catch (e) {
                    statusEl.textContent = 'Error';
                    statusEl.classList.add('error');
                    console.error('Upload failed:', e.message);
                }
            } else {
                 statusEl.textContent = 'Error';
                 statusEl.classList.add('error');
            }
        });

        xhr.addEventListener('error', () => {
            statusEl.textContent = 'Error';
            statusEl.classList.add('error');
        });

        xhr.open('POST', munchmakers_ajax.ajax_url, true);
        xhr.send(formData);
    }

    // File input handling
    if (dropZone && fileInput) {
        dropZone.addEventListener('click', function(e) {
            if (e.target === dropZone || e.target.classList.contains('munch-drop-zone-text')) {
                e.preventDefault();
                fileInput.click();
            }
        });
        
        const fileLabel = dropZone.querySelector('.file-upload');
        if (fileLabel) {
            fileLabel.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                fileInput.click();
            });
        }
        
        dropZone.addEventListener('dragover', e => {
            e.preventDefault();
            dropZone.classList.add('dragover');
        });
        
        dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
        
        dropZone.addEventListener('drop', e => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            handleFiles(e.dataTransfer.files);
        });
        
        fileInput.addEventListener('change', function() {
            if (this.files && this.files.length > 0) {
                handleFiles(this.files);
            }
        });
    }

    // Pricing Tooltip Logic
    function formatPrice(price) {
        return munchmakers_ajax.currency + Number(price).toFixed(2);
    }
    
    function createPricingTooltip(trigger) {
        let tooltip = document.querySelector('.munch-tooltip');
        if (!tooltip) {
            tooltip = document.createElement('div');
            tooltip.className = 'munch-tooltip';
            document.body.appendChild(tooltip);
        }

        let tooltipTimer = null;
        let isTooltipHovered = false;
        let isTriggerHovered = false;

        function showTooltip() {
            let tableHTML = '<h4>Wholesale Pricing Tiers</h4>';
            tableHTML += '<button class="munch-tooltip-close" onclick="this.parentElement.classList.remove(\'visible\', \'stay-open\')">&times;</button>';
            tableHTML += '<table class="munch-tooltip-table"><thead><tr><th>Quantity</th><th>Price Per Unit</th></tr></thead><tbody>';
            
            const rules = currentPricingData.rules;
            const basePrice = currentPricingData.base_price;

            if (!rules || Object.keys(rules).length === 0) {
                tableHTML += `<tr><td colspan="2" style="text-align: center; font-style: italic;">Standard Price: ${formatPrice(basePrice)}</td></tr>`;
            } else {
                const tiers = Object.keys(rules).map(Number).sort((a, b) => a - b);
                if (tiers[0] > 1 && basePrice) {
                    tableHTML += `<tr><td>1 - ${tiers[0] - 1}</td><td>${formatPrice(basePrice)}</td></tr>`;
                }
                tiers.forEach((qty, index) => {
                    const nextQty = tiers[index + 1];
                    const rangeEnd = nextQty ? nextQty - 1 : '+';
                    tableHTML += `<tr><td>${qty}${rangeEnd === '+' ? '+' : ' - ' + rangeEnd}</td><td>${formatPrice(rules[qty])}</td></tr>`;
                });
            }
            tableHTML += '</tbody></table>';
            tooltip.innerHTML = tableHTML;
            
            const rect = trigger.getBoundingClientRect();
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            tooltip.style.left = Math.max(10, rect.left - 50) + 'px';
            tooltip.style.top = (rect.bottom + scrollTop + 10) + 'px';
            
            setTimeout(() => {
                const tooltipRect = tooltip.getBoundingClientRect();
                if (tooltipRect.right > window.innerWidth - 10) {
                    tooltip.style.left = (window.innerWidth - tooltipRect.width - 10) + 'px';
                }
            }, 10);
            
            tooltip.classList.add('visible');
        }

        function hideTooltip() {
            if (!isTooltipHovered && !isTriggerHovered) {
                tooltip.classList.remove('visible', 'stay-open');
            }
        }

        trigger.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            showTooltip();
            tooltip.classList.add('stay-open');
            
            if (tooltipTimer) {
                clearTimeout(tooltipTimer);
            }
            
            tooltipTimer = setTimeout(() => {
                if (!isTooltipHovered) {
                    tooltip.classList.remove('visible', 'stay-open');
                }
            }, 8000);
        });

        trigger.addEventListener('mouseenter', () => {
            isTriggerHovered = true;
            showTooltip();
        });

        trigger.addEventListener('mouseleave', () => {
            isTriggerHovered = false;
            setTimeout(hideTooltip, 300);
        });

        tooltip.addEventListener('mouseenter', () => {
            isTooltipHovered = true;
        });

        tooltip.addEventListener('mouseleave', () => {
            isTooltipHovered = false;
            setTimeout(hideTooltip, 300);
        });

        document.addEventListener('click', (e) => {
            if (!tooltip.contains(e.target) && !trigger.contains(e.target)) {
                tooltip.classList.remove('visible', 'stay-open');
                if (tooltipTimer) {
                    clearTimeout(tooltipTimer);
                }
            }
        });
    }

    function updateAllPricingTooltips() {
        document.querySelectorAll('.pricing-tooltip-trigger').forEach(trigger => {
            createPricingTooltip(trigger);
        });
    }

    // Event listeners
    modal.querySelector('.modal-close').addEventListener('click', closeModal);
    modal.querySelector('.modal-backdrop').addEventListener('click', closeModal);
    document.addEventListener('keydown', e => { 
        if (e.key === 'Escape' && modal.style.display === 'flex') closeModal(); 
    });

    document.addEventListener('munchmakersModalOpen', () => {
        updateAllPricingTooltips();
        if (isVariable) {
            setTimeout(() => {
                initColorSwatches();
                setDefaultVariation();
            }, 50);
        } else {
            showStep('quantity');
            initQuantitySlider();
        }
    });

    if (isVariable) {
        variationInputs.forEach(input => input.addEventListener('change', updateVariationState));
    }
    
    modal.querySelector('#next-to-quantity')?.addEventListener('click', () => {
        initQuantitySlider();
        showStep('quantity');
    });
    
    modal.querySelector('#back-to-variations')?.addEventListener('click', () => showStep('variations'));
    modal.querySelector('#next-to-artwork')?.addEventListener('click', () => showStep('artwork'));
    modal.querySelector('#back-to-quantity')?.addEventListener('click', () => showStep('quantity'));
    
    const artworkRadios = modal.querySelectorAll('input[name="modal_artwork_choice"]');
    const artworkNowOptions = modal.querySelector('#artwork-now-options');
    const artworkLaterOptions = modal.querySelector('#artwork-later-options');
    
    artworkRadios.forEach(radio => radio.addEventListener('change', function() {
        artworkNowOptions.style.display = this.value === 'add_now' ? 'block' : 'none';
        artworkLaterOptions.style.display = this.value === 'add_now' ? 'none' : 'block';
        updateArtworkOption();
        updateAddToCartVisibility();
    }));
    
    const tabBtns = modal.querySelectorAll('.tab-btn');
    tabBtns.forEach(btn => btn.addEventListener('click', function() {
        tabBtns.forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        modal.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        modal.querySelector('#tab-' + this.dataset.tab).classList.add('active');
        updateArtworkOption();
        updateAddToCartVisibility();
    }));

    function updateAddToCartVisibility() {
        const addToCartBtn = modal.querySelector('#add-to-cart');
        const checkedRadio = modal.querySelector('input[name="modal_artwork_choice"]:checked');
        const activeTab = modal.querySelector('.tab-btn.active');
        
        if (checkedRadio && checkedRadio.value === 'send_later') {
            addToCartBtn.style.display = 'block';
        } else if (activeTab && activeTab.dataset.tab === 'designer') {
            addToCartBtn.style.display = 'none';
        } else {
            addToCartBtn.style.display = 'block';
        }
    }

    const designButton = modal.querySelector('#open-designer');
    if (designButton) {
        designButton.addEventListener('click', function(e) {
            e.preventDefault();
            const btnText = this.querySelector('.btn-text');
            const btnSpinner = this.querySelector('.btn-spinner');
            
            if (btnText && btnSpinner) {
                btnText.style.display = 'none';
                btnSpinner.style.display = 'flex';
                this.disabled = true;
            }

            const resetLoadingState = () => {
                if (btnText && btnSpinner) {
                    btnText.style.display = 'flex';
                    btnSpinner.style.display = 'none';
                    this.disabled = false;
                }
            };
            
            setTimeout(resetLoadingState, 10000);

            try {
                const originalForm = document.querySelector('form.cart');
                if (!originalForm) throw new Error("Could not find product form.");

                const modalQuantity = modal.querySelector('#modal-quantity').value;
                let formQuantityInput = originalForm.querySelector('input[name="quantity"]');
                if (!formQuantityInput) {
                    formQuantityInput = document.createElement('input');
                    formQuantityInput.type = 'hidden';
                    formQuantityInput.name = 'quantity';
                    originalForm.appendChild(formQuantityInput);
                }
                formQuantityInput.value = modalQuantity;
                
                if (isVariable && currentVariation) {
                    let formVariationIdInput = originalForm.querySelector('input.variation_id');
                    if (!formVariationIdInput) {
                        formVariationIdInput = document.createElement('input');
                        formVariationIdInput.type = 'hidden';
                        formVariationIdInput.name = 'variation_id';
                        formVariationIdInput.className = 'variation_id';
                        originalForm.appendChild(formVariationIdInput);
                    }
                    formVariationIdInput.value = currentVariation.variation_id;

                    Object.keys(currentVariation.attributes).forEach(key => {
                        const value = currentVariation.attributes[key];
                        if(value) {
                            let formAttributeSelect = originalForm.querySelector(`select[name="${key}"]`);
                            if (formAttributeSelect) {
                                formAttributeSelect.value = value;
                                formAttributeSelect.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                        }
                    });
                }
                
                const defaultAddToCartButton = originalForm.querySelector('.single_add_to_cart_button:not(.munchmakers-start-order-btn)');
                if (!defaultAddToCartButton) throw new Error("Could not find default Add to Cart button.");

                const wasHidden = defaultAddToCartButton.style.display === 'none';
                if (wasHidden) {
                    defaultAddToCartButton.style.display = 'block';
                    defaultAddToCartButton.style.visibility = 'hidden';
                    defaultAddToCartButton.style.position = 'absolute';
                    defaultAddToCartButton.style.left = '-9999px';
                }

                setTimeout(() => {
                    defaultAddToCartButton.click();
                    setTimeout(() => {
                        if (wasHidden) defaultAddToCartButton.style.display = 'none';
                        resetLoadingState();
                        closeModal();
                    }, 2000);
                }, 300);

            } catch (error) {
                alert('Design tool integration not found. Please upload a file or contact support.');
                resetLoadingState();
            }
        });
    }
    
    modal.querySelector('#add-to-cart')?.addEventListener('click', function(e) {
        e.preventDefault();
        const button = this;
        if(button.classList.contains('loading')) return;
        
        button.classList.add('loading');
        button.disabled = true;
        
        const formData = new FormData();
        formData.append('action', 'munchmakers_add_to_cart');
        formData.append('nonce', munchmakers_ajax.add_to_cart_nonce);
        formData.append('product_id', productId);
        formData.append('quantity', modal.querySelector('#modal-quantity').value);
        
        if (isVariable && currentVariation) {
            formData.append('variation_id', currentVariation.variation_id);
            Object.keys(currentVariation.attributes).forEach(key => {
                if (currentVariation.attributes[key]) formData.append(key, currentVariation.attributes[key]);
            });
        }
        
        updateArtworkOption();
        formData.append('munchmakers_artwork_option', document.querySelector('#munchmakers_artwork_option').value);
        formData.append('munchmakers_selected_options', document.querySelector('#munchmakers_selected_options').value);
        formData.append('munchmakers_artwork_file_urls', document.querySelector('#munchmakers_artwork_file_urls').value);
        
        fetch(munchmakers_ajax.ajax_url, { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            button.classList.remove('loading');
            button.disabled = false;
            if (data.success) {
                button.textContent = 'Added! Redirecting...';
                button.style.background = '#28a745';
                setTimeout(() => { window.location.href = munchmakers_ajax.cart_url; }, 500);
            } else {
                alert('Error: ' + (data.data.message || 'Could not add to cart.'));
            }
        })
        .catch(error => {
            button.classList.remove('loading');
            button.disabled = false;
            alert('A network error occurred. Please try again.');
        });
    });

    // Initialize button visibility
    updateAddToCartVisibility();
});