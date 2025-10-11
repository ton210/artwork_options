/**
 * VSS Professional Product Uploader JavaScript
 * Modern, feature-rich product upload interface with real-time validation
 * 
 * @package VendorOrderManager
 * @since 8.0.0
 */

(function($) {
    'use strict';

    class VSSProfessionalUploader {
        constructor() {
            this.form = null;
            this.currentImages = {
                thumbnail: [],
                gallery: [],
                lifestyle: [],
                technical: [],
                packaging: []
            };
            this.isDirty = false;
            this.autoSaveTimer = null;
            this.validationTimer = null;
            this.qualityScore = 0;
            this.completionPercentage = 0;
            this.aiSuggestions = [];
        }

        init() {
            this.form = document.getElementById('vss-pro-upload-form');
            if (!this.form) return;

            this.bindEvents();
            this.initializeDropzones();
            this.initializeImageSorting();
            this.initializeValidation();
            this.initializeAutoSave();
            this.initializePricingCalculator();
            this.loadExistingData();
            this.updateProgressBar();
            
            // Prevent form submission on page unload if dirty
            this.setupUnloadProtection();
        }

        bindEvents() {
            // Header actions
            $('#vss-save-draft').on('click', () => this.saveDraft());
            $('#vss-preview-product').on('click', () => this.previewProduct());
            $('#vss-ai-assist').on('click', () => this.showAIAssistant());
            $('#vss-submit-product').on('click', () => this.submitProduct());
            $('#vss-duplicate-product').on('click', () => this.duplicateProduct());
            $('#vss-delete-product').on('click', () => this.deleteProduct());

            // Form interactions
            $('#product_name').on('input', (e) => {
                this.generateSlug(e.target.value);
                this.generateSKU();
                this.markDirty();
            });

            $('#category_id').on('change', (e) => {
                this.loadSubcategories(e.target.value);
                this.updateTagSuggestions();
                this.markDirty();
            });

            // Real-time validation
            this.form.querySelectorAll('input, textarea, select').forEach(field => {
                field.addEventListener('input', () => this.validateField(field));
                field.addEventListener('blur', () => this.validateField(field, true));
                field.addEventListener('change', () => this.markDirty());
            });

            // Character counter
            $('#short_description').on('input', this.updateCharacterCounter);

            // Tag input with suggestions
            $('#tags').on('input', this.handleTagInput.bind(this));
            $('#tags').on('keydown', this.handleTagKeydown.bind(this));

            // SKU generator
            $('#vss-generate-sku').on('click', () => this.generateSKU());

            // Image optimization
            $('#vss-optimize-images').on('click', () => this.optimizeAllImages());

            // Pricing calculator
            $('.vss-price-input').on('input', () => this.updatePricingCalculator());

            // Modal controls
            $('.vss-modal-close').on('click', this.closeModal);
            $('.vss-modal').on('click', (e) => {
                if (e.target === e.currentTarget) this.closeModal();
            });

            // Form submission
            this.form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleFormSubmission();
            });

            // Keyboard shortcuts
            $(document).on('keydown', this.handleKeyboardShortcuts.bind(this));
        }

        initializeDropzones() {
            $('.vss-dropzone').each((index, dropzone) => {
                const $dropzone = $(dropzone);
                const fileInput = $dropzone.find('.vss-file-input')[0];
                const imageType = $dropzone.data('type');
                const maxFiles = $dropzone.data('max') || 10;

                // Drag and drop events
                $dropzone.on('dragover dragenter', (e) => {
                    e.preventDefault();
                    $dropzone.addClass('dragover');
                });

                $dropzone.on('dragleave dragend', () => {
                    $dropzone.removeClass('dragover');
                });

                $dropzone.on('drop', (e) => {
                    e.preventDefault();
                    $dropzone.removeClass('dragover');
                    
                    const files = e.originalEvent.dataTransfer.files;
                    this.handleFileSelection(files, imageType, maxFiles);
                });

                // Click to browse
                $dropzone.on('click', () => fileInput.click());

                // File input change
                $(fileInput).on('change', (e) => {
                    this.handleFileSelection(e.target.files, imageType, maxFiles);
                });
            });
        }

        handleFileSelection(files, imageType, maxFiles) {
            const currentCount = this.currentImages[imageType].length;
            const availableSlots = maxFiles - currentCount;
            
            if (files.length > availableSlots) {
                this.showNotification('error', `You can only upload ${availableSlots} more images for this section.`);
                return;
            }

            Array.from(files).forEach(file => {
                if (this.validateImageFile(file)) {
                    this.uploadImage(file, imageType);
                }
            });
        }

        validateImageFile(file) {
            const allowedTypes = vssProUploader.allowedTypes;
            const maxSize = vssProUploader.maxFileSize;

            if (!allowedTypes.includes(file.type)) {
                this.showNotification('error', `Invalid file type: ${file.name}`);
                return false;
            }

            if (file.size > maxSize) {
                this.showNotification('error', `File too large: ${file.name}`);
                return false;
            }

            return true;
        }

        uploadImage(file, imageType) {
            const formData = new FormData();
            formData.append('action', 'vss_pro_upload_images');
            formData.append('nonce', vssProUploader.nonce);
            formData.append('image', file);
            formData.append('image_type', imageType);

            // Show upload progress
            const uploadId = this.generateUploadId();
            this.showUploadProgress(uploadId, file.name);

            $.ajax({
                url: vssProUploader.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: () => {
                    const xhr = new XMLHttpRequest();
                    xhr.upload.addEventListener('progress', (e) => {
                        if (e.lengthComputable) {
                            const percentComplete = (e.loaded / e.total) * 100;
                            this.updateUploadProgress(uploadId, percentComplete);
                        }
                    });
                    return xhr;
                },
                success: (response) => {
                    this.hideUploadProgress(uploadId);
                    
                    if (response.success) {
                        const imageData = response.data;
                        this.currentImages[imageType].push(imageData);
                        this.renderImagePreview(imageData, imageType);
                        this.updateProgressBar();
                        this.calculateQualityScore();
                        this.markDirty();
                        this.showNotification('success', `${file.name} uploaded successfully!`);
                    } else {
                        this.showNotification('error', response.data.message || 'Upload failed');
                    }
                },
                error: () => {
                    this.hideUploadProgress(uploadId);
                    this.showNotification('error', 'Upload failed. Please try again.');
                }
            });
        }

        renderImagePreview(imageData, imageType) {
            const container = $(`.vss-uploaded-images[data-type="${imageType}"]`);
            const isPrimary = imageType === 'thumbnail' || imageData.is_primary;
            
            const imageHtml = `
                <div class="vss-image-item" data-id="${imageData.id}" data-type="${imageType}">
                    <img src="${imageData.url}" alt="${imageData.alt || ''}" class="vss-image-preview">
                    ${isPrimary ? '<div class="vss-image-meta vss-primary-badge">Primary</div>' : ''}
                    <div class="vss-image-overlay">
                        <div class="vss-image-actions">
                            <button type="button" class="vss-image-action edit" title="Edit Image">
                                <span class="dashicons dashicons-edit"></span>
                            </button>
                            ${!isPrimary ? '<button type="button" class="vss-image-action primary" title="Set as Primary"><span class="dashicons dashicons-star-filled"></span></button>' : ''}
                            <button type="button" class="vss-image-action delete" title="Delete Image">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            container.append(imageHtml);
            
            // Bind image actions
            this.bindImageActions(container.find('.vss-image-item').last());
        }

        bindImageActions($imageItem) {
            const imageId = $imageItem.data('id');
            const imageType = $imageItem.data('type');

            $imageItem.find('.edit').on('click', () => {
                this.openImageEditor(imageId);
            });

            $imageItem.find('.primary').on('click', () => {
                this.setPrimaryImage(imageId, imageType);
            });

            $imageItem.find('.delete').on('click', () => {
                this.deleteImage(imageId, imageType, $imageItem);
            });
        }

        initializeImageSorting() {
            $('.vss-sortable').sortable({
                items: '.vss-image-item',
                cursor: 'grabbing',
                opacity: 0.8,
                tolerance: 'pointer',
                update: (event, ui) => {
                    this.updateImageOrder();
                    this.markDirty();
                }
            });
        }

        initializeValidation() {
            this.validationRules = {
                product_name: {
                    required: true,
                    minLength: 3,
                    maxLength: 255,
                    pattern: /^[a-zA-Z0-9\s\-_.,()&]+$/
                },
                short_description: {
                    required: true,
                    minLength: 10,
                    maxLength: 160
                },
                category_id: {
                    required: true
                },
                production_cost: {
                    min: 0,
                    max: 10000
                },
                suggested_price: {
                    min: 0,
                    max: 50000
                }
            };
        }

        validateField(field, showErrors = false) {
            const fieldName = field.name;
            const value = field.value.trim();
            const rules = this.validationRules[fieldName];
            
            if (!rules) return true;

            let isValid = true;
            let errorMessage = '';

            // Required validation
            if (rules.required && !value) {
                isValid = false;
                errorMessage = 'This field is required';
            }

            // Length validation
            if (value && rules.minLength && value.length < rules.minLength) {
                isValid = false;
                errorMessage = `Minimum ${rules.minLength} characters required`;
            }

            if (value && rules.maxLength && value.length > rules.maxLength) {
                isValid = false;
                errorMessage = `Maximum ${rules.maxLength} characters allowed`;
            }

            // Pattern validation
            if (value && rules.pattern && !rules.pattern.test(value)) {
                isValid = false;
                errorMessage = 'Invalid format';
            }

            // Numeric validation
            if (value && rules.min !== undefined && parseFloat(value) < rules.min) {
                isValid = false;
                errorMessage = `Minimum value is ${rules.min}`;
            }

            if (value && rules.max !== undefined && parseFloat(value) > rules.max) {
                isValid = false;
                errorMessage = `Maximum value is ${rules.max}`;
            }

            // Update field UI
            const $field = $(field);
            const $feedback = $field.siblings('.vss-field-feedback');

            if (isValid) {
                $field.removeClass('error').addClass('valid');
                $feedback.removeClass('error').addClass('success').text('✓');
            } else if (showErrors) {
                $field.removeClass('valid').addClass('error');
                $feedback.removeClass('success').addClass('error').text(errorMessage);
            } else {
                $field.removeClass('valid error');
                $feedback.removeClass('success error').text('');
            }

            // Update section status
            this.updateSectionStatus();
            
            return isValid;
        }

        updateSectionStatus() {
            const sections = {
                basic: ['product_name', 'category_id', 'short_description'],
                details: ['full_description'],
                images: () => Object.values(this.currentImages).some(imgs => imgs.length > 0),
                pricing: ['production_cost', 'suggested_price']
            };

            Object.keys(sections).forEach(sectionName => {
                const $indicator = $(`.vss-status-indicator[data-section="${sectionName}"]`);
                let isComplete = false;

                if (typeof sections[sectionName] === 'function') {
                    isComplete = sections[sectionName]();
                } else {
                    isComplete = sections[sectionName].every(fieldName => {
                        const field = this.form.querySelector(`[name="${fieldName}"]`);
                        return field && this.validateField(field);
                    });
                }

                $indicator.removeClass('complete incomplete error')
                         .addClass(isComplete ? 'complete' : 'incomplete');
            });
        }

        initializeAutoSave() {
            // Auto-save every 30 seconds if dirty
            setInterval(() => {
                if (this.isDirty && !this.isSubmitting) {
                    this.saveDraft(true); // Silent save
                }
            }, 30000);
        }

        initializePricingCalculator() {
            this.updatePricingCalculator();
        }

        updatePricingCalculator() {
            const cost = parseFloat($('#production_cost').val()) || 0;
            const price = parseFloat($('#suggested_price').val()) || 0;
            const platformFee = price * 0.15;
            const profit = price - cost - platformFee;
            const recommendedPrice = cost * 2.5; // 150% markup

            $('.vss-cost-display').text(`$${cost.toFixed(2)}`);
            $('.vss-fee-display').text(`$${platformFee.toFixed(2)}`);
            $('.vss-profit-display').text(`$${profit.toFixed(2)}`);
            $('.vss-recommended-display').text(`$${recommendedPrice.toFixed(2)}`);

            // Update suggested price if empty
            if (!price && cost > 0) {
                $('#suggested_price').val(recommendedPrice.toFixed(2));
            }
        }

        generateSlug(title) {
            if (!title) return;
            
            const slug = title.toLowerCase()
                             .replace(/[^a-z0-9\s-]/g, '')
                             .replace(/\s+/g, '-')
                             .replace(/-+/g, '-')
                             .trim('-');
            
            $('#product_slug').val(slug);
        }

        generateSKU() {
            const category = $('#category_id option:selected').text().substring(0, 3).toUpperCase();
            const timestamp = Date.now().toString().slice(-6);
            const random = Math.floor(Math.random() * 100).toString().padStart(2, '0');
            
            const sku = `${category}${timestamp}${random}`;
            $('#sku').val(sku);
            this.markDirty();
        }

        loadSubcategories(categoryId) {
            if (!categoryId) {
                $('#subcategory_id').html('<option value="">Select Subcategory</option>');
                return;
            }

            $.post(vssProUploader.ajaxUrl, {
                action: 'vss_get_subcategories',
                category_id: categoryId,
                nonce: vssProUploader.nonce
            }, (response) => {
                if (response.success) {
                    let options = '<option value="">Select Subcategory</option>';
                    response.data.forEach(sub => {
                        options += `<option value="${sub.id}">${sub.name}</option>`;
                    });
                    $('#subcategory_id').html(options);
                }
            });
        }

        updateTagSuggestions() {
            const category = $('#category_id option:selected').text();
            if (!category) return;

            $.post(vssProUploader.ajaxUrl, {
                action: 'vss_get_tag_suggestions',
                category: category,
                nonce: vssProUploader.nonce
            }, (response) => {
                if (response.success) {
                    this.tagSuggestions = response.data;
                }
            });
        }

        handleTagInput(e) {
            const value = e.target.value;
            const lastTag = value.split(',').pop().trim();
            
            if (lastTag.length >= 2 && this.tagSuggestions) {
                const suggestions = this.tagSuggestions.filter(tag => 
                    tag.toLowerCase().includes(lastTag.toLowerCase())
                );
                this.showTagSuggestions(suggestions, lastTag);
            } else {
                this.hideTagSuggestions();
            }
        }

        handleTagKeydown(e) {
            if (e.key === 'Tab' || e.key === 'Enter') {
                const $suggestions = $('.vss-tag-suggestions');
                if ($suggestions.is(':visible')) {
                    e.preventDefault();
                    const firstSuggestion = $suggestions.find('.vss-tag-suggestion').first();
                    if (firstSuggestion.length) {
                        this.selectTagSuggestion(firstSuggestion.text());
                    }
                }
            }
        }

        showTagSuggestions(suggestions, query) {
            if (!suggestions.length) {
                this.hideTagSuggestions();
                return;
            }

            let html = '';
            suggestions.slice(0, 5).forEach(tag => {
                const highlighted = tag.replace(new RegExp(query, 'gi'), `<strong>$&</strong>`);
                html += `<div class="vss-tag-suggestion">${highlighted}</div>`;
            });

            $('.vss-tag-suggestions').html(html).show();
            
            // Bind click events
            $('.vss-tag-suggestion').on('click', (e) => {
                this.selectTagSuggestion($(e.target).text());
            });
        }

        selectTagSuggestion(tag) {
            const $input = $('#tags');
            const value = $input.val();
            const tags = value.split(',').map(t => t.trim());
            tags.pop(); // Remove the last incomplete tag
            tags.push(tag);
            $input.val(tags.join(', ') + ', ');
            this.hideTagSuggestions();
            this.markDirty();
        }

        hideTagSuggestions() {
            $('.vss-tag-suggestions').hide();
        }

        updateCharacterCounter() {
            const $field = $(this);
            const current = $field.val().length;
            const max = $field.attr('maxlength') || 160;
            const $counter = $field.siblings('.vss-char-counter');
            
            $counter.find('.current').text(current);
            
            if (current > max * 0.9) {
                $counter.addClass('warning');
            } else {
                $counter.removeClass('warning');
            }
        }

        updateProgressBar() {
            const totalSections = 4;
            let completedSections = 0;

            // Basic info
            if ($('#product_name').val() && $('#category_id').val() && $('#short_description').val()) {
                completedSections++;
            }

            // Details
            if ($('#full_description').val()) {
                completedSections++;
            }

            // Images
            if (Object.values(this.currentImages).some(imgs => imgs.length > 0)) {
                completedSections++;
            }

            // Pricing
            if ($('#production_cost').val() && $('#suggested_price').val()) {
                completedSections++;
            }

            this.completionPercentage = Math.round((completedSections / totalSections) * 100);
            
            $('.vss-pro-progress-fill').css('width', `${this.completionPercentage}%`);
            $('.vss-pro-progress-text').text(`${this.completionPercentage}% Complete`);
        }

        calculateQualityScore() {
            let score = 0;
            const maxScore = 10;

            // Product name quality (0-2 points)
            const nameLength = $('#product_name').val().length;
            if (nameLength >= 10 && nameLength <= 60) score += 2;
            else if (nameLength >= 5) score += 1;

            // Description quality (0-3 points)
            const descLength = $('#full_description').val().length;
            if (descLength >= 300) score += 3;
            else if (descLength >= 150) score += 2;
            else if (descLength >= 50) score += 1;

            // Image quality (0-3 points)
            const imageCount = Object.values(this.currentImages).reduce((sum, imgs) => sum + imgs.length, 0);
            if (imageCount >= 5) score += 3;
            else if (imageCount >= 3) score += 2;
            else if (imageCount >= 1) score += 1;

            // Pricing (0-2 points)
            const cost = parseFloat($('#production_cost').val());
            const price = parseFloat($('#suggested_price').val());
            if (cost && price && price > cost * 1.5) score += 2;
            else if (cost && price) score += 1;

            this.qualityScore = score;
            this.updateQualityScoreUI();
        }

        updateQualityScoreUI() {
            $('.vss-score-number').text(this.qualityScore.toFixed(1));
            
            // Update progress ring
            const circle = $('.vss-progress-ring-circle')[0];
            if (circle) {
                const circumference = 2 * Math.PI * 35; // radius = 35
                const progress = (this.qualityScore / 10) * circumference;
                const offset = circumference - progress;
                
                circle.style.strokeDasharray = `${circumference} ${circumference}`;
                circle.style.strokeDashoffset = offset;
                
                if (this.qualityScore >= 7) {
                    $(circle).css('stroke', 'var(--vss-success)');
                } else if (this.qualityScore >= 4) {
                    $(circle).css('stroke', 'var(--vss-warning)');
                } else {
                    $(circle).css('stroke', 'var(--vss-error)');
                }
            }

            // Update score breakdown bars
            const scores = {
                images: Math.min(Object.values(this.currentImages).reduce((sum, imgs) => sum + imgs.length, 0) * 20, 100),
                description: Math.min($('#full_description').val().length / 3, 100),
                pricing: $('#production_cost').val() && $('#suggested_price').val() ? 90 : 0,
                seo: $('#product_name').val().length > 10 && $('#short_description').val().length > 20 ? 70 : 30
            };

            Object.keys(scores).forEach(category => {
                $(`.vss-score-item:contains("${category}") .vss-score-fill`).css('width', `${scores[category]}%`);
            });
        }

        saveDraft(silent = false) {
            if (this.isSaving) return;
            
            this.isSaving = true;
            const formData = new FormData(this.form);
            formData.append('action', 'vss_pro_save_draft');

            if (!silent) {
                this.showNotification('info', vssProUploader.strings.saving);
            }

            $.ajax({
                url: vssProUploader.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: (response) => {
                    this.isSaving = false;
                    
                    if (response.success) {
                        this.isDirty = false;
                        
                        if (!silent) {
                            this.showNotification('success', vssProUploader.strings.saved);
                        }

                        // Update product ID if new
                        if (response.data.product_id && !$('input[name="product_id"]').val()) {
                            $('input[name="product_id"]').val(response.data.product_id);
                            
                            // Update URL without refresh
                            const url = new URL(window.location);
                            url.searchParams.set('product_id', response.data.product_id);
                            window.history.replaceState({}, '', url);
                        }
                    } else {
                        if (!silent) {
                            this.showNotification('error', response.data.message || 'Save failed');
                        }
                    }
                },
                error: () => {
                    this.isSaving = false;
                    if (!silent) {
                        this.showNotification('error', 'Save failed. Please try again.');
                    }
                }
            });
        }

        submitProduct() {
            if (!this.validateForm()) {
                this.showNotification('error', 'Please fix all errors before submitting');
                return;
            }

            if (Object.values(this.currentImages).every(imgs => imgs.length === 0)) {
                this.showNotification('error', 'Please upload at least one product image');
                return;
            }

            this.isSubmitting = true;
            const formData = new FormData(this.form);
            formData.append('action', 'vss_pro_upload_product');

            // Show loading state
            const $submitBtn = $('#vss-submit-product');
            const originalText = $submitBtn.text();
            $submitBtn.prop('disabled', true).html('<span class="dashicons dashicons-update"></span> Submitting...');

            $.ajax({
                url: vssProUploader.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: (response) => {
                    this.isSubmitting = false;
                    $submitBtn.prop('disabled', false).text(originalText);
                    
                    if (response.success) {
                        this.isDirty = false;
                        this.showNotification('success', response.data.message);
                        
                        // Redirect after delay
                        setTimeout(() => {
                            window.location.href = response.data.redirect_url;
                        }, 2000);
                    } else {
                        this.showNotification('error', response.data.message || 'Submission failed');
                    }
                },
                error: () => {
                    this.isSubmitting = false;
                    $submitBtn.prop('disabled', false).text(originalText);
                    this.showNotification('error', 'Submission failed. Please try again.');
                }
            });
        }

        validateForm() {
            let isValid = true;
            
            this.form.querySelectorAll('input[required], textarea[required], select[required]').forEach(field => {
                if (!this.validateField(field, true)) {
                    isValid = false;
                }
            });

            return isValid;
        }

        showAIAssistant() {
            this.openModal('#vss-ai-modal');
            this.generateAISuggestions();
        }

        generateAISuggestions() {
            const formData = new FormData(this.form);
            formData.append('action', 'vss_pro_get_suggestions');

            $.ajax({
                url: vssProUploader.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: (response) => {
                    if (response.success) {
                        this.renderAISuggestions(response.data.suggestions);
                    } else {
                        $('.vss-ai-suggestions').html('<p>Unable to generate suggestions at this time.</p>');
                    }
                },
                error: () => {
                    $('.vss-ai-suggestions').html('<p>Unable to generate suggestions at this time.</p>');
                }
            });
        }

        renderAISuggestions(suggestions) {
            let html = '';
            
            suggestions.forEach(suggestion => {
                html += `
                    <div class="vss-suggestion-item">
                        <h4>${suggestion.title}</h4>
                        <p>${suggestion.description}</p>
                        <button class="vss-btn vss-btn-sm vss-btn-primary apply-suggestion" 
                                data-field="${suggestion.field}" 
                                data-value="${suggestion.value}">
                            Apply Suggestion
                        </button>
                    </div>
                `;
            });

            $('.vss-ai-suggestions').html(html);
            
            $('.apply-suggestion').on('click', (e) => {
                const field = $(e.target).data('field');
                const value = $(e.target).data('value');
                $(`[name="${field}"]`).val(value);
                this.markDirty();
                this.showNotification('success', 'Suggestion applied!');
            });
        }

        previewProduct() {
            // Generate preview HTML and open in modal or new window
            const formData = this.getFormData();
            const previewWindow = window.open('', '_blank', 'width=800,height=600');
            
            previewWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Product Preview: ${formData.product_name}</title>
                    <style>
                        body { font-family: Arial, sans-serif; padding: 20px; }
                        .preview-container { max-width: 600px; margin: 0 auto; }
                        .product-images { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 20px; }
                        .product-image { width: 100%; height: 200px; object-fit: cover; border-radius: 8px; }
                    </style>
                </head>
                <body>
                    <div class="preview-container">
                        <h1>${formData.product_name}</h1>
                        <div class="product-images">
                            ${Object.values(this.currentImages).flat().map(img => 
                                `<img src="${img.url}" alt="${img.alt || ''}" class="product-image">`
                            ).join('')}
                        </div>
                        <p><strong>Short Description:</strong> ${formData.short_description}</p>
                        <div><strong>Full Description:</strong> ${formData.full_description}</div>
                        <p><strong>Price:</strong> $${formData.suggested_price}</p>
                        <p><strong>SKU:</strong> ${formData.sku}</p>
                    </div>
                </body>
                </html>
            `);
        }

        duplicateProduct() {
            if (!confirm('Are you sure you want to duplicate this product?')) return;

            const formData = new FormData(this.form);
            formData.append('action', 'vss_pro_duplicate_product');

            $.ajax({
                url: vssProUploader.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: (response) => {
                    if (response.success) {
                        window.location.href = response.data.redirect_url;
                    } else {
                        this.showNotification('error', response.data.message || 'Duplication failed');
                    }
                },
                error: () => {
                    this.showNotification('error', 'Duplication failed. Please try again.');
                }
            });
        }

        deleteProduct() {
            if (!confirm('Are you sure you want to delete this product? This action cannot be undone.')) return;

            const productId = $('input[name="product_id"]').val();
            if (!productId) return;

            $.post(vssProUploader.ajaxUrl, {
                action: 'vss_pro_delete_product',
                product_id: productId,
                nonce: vssProUploader.nonce
            }, (response) => {
                if (response.success) {
                    this.showNotification('success', 'Product deleted successfully');
                    setTimeout(() => {
                        window.location.href = response.data.redirect_url;
                    }, 1500);
                } else {
                    this.showNotification('error', response.data.message || 'Deletion failed');
                }
            });
        }

        openModal(selector) {
            $(selector).addClass('active').addClass('vss-fade-in');
        }

        closeModal() {
            $('.vss-modal').removeClass('active');
        }

        showNotification(type, message) {
            // Create notification element
            const notification = $(`
                <div class="vss-notification vss-notification-${type} vss-slide-in">
                    <span class="vss-notification-message">${message}</span>
                    <button class="vss-notification-close">&times;</button>
                </div>
            `);

            // Add to page
            if (!$('.vss-notifications').length) {
                $('body').append('<div class="vss-notifications"></div>');
            }

            $('.vss-notifications').append(notification);

            // Auto remove after 5 seconds
            setTimeout(() => {
                notification.fadeOut(() => notification.remove());
            }, 5000);

            // Manual close
            notification.find('.vss-notification-close').on('click', () => {
                notification.fadeOut(() => notification.remove());
            });
        }

        setupUnloadProtection() {
            window.addEventListener('beforeunload', (e) => {
                if (this.isDirty && !this.isSubmitting) {
                    e.preventDefault();
                    e.returnValue = vssProUploader.strings.unsaved_changes;
                    return vssProUploader.strings.unsaved_changes;
                }
            });
        }

        handleKeyboardShortcuts(e) {
            // Ctrl/Cmd + S to save
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                this.saveDraft();
            }

            // Esc to close modals
            if (e.key === 'Escape') {
                this.closeModal();
            }
        }

        markDirty() {
            this.isDirty = true;
            
            // Debounced progress update
            clearTimeout(this.updateTimer);
            this.updateTimer = setTimeout(() => {
                this.updateProgressBar();
                this.calculateQualityScore();
            }, 300);
        }

        getFormData() {
            const formData = new FormData(this.form);
            const data = {};
            
            for (let [key, value] of formData.entries()) {
                data[key] = value;
            }
            
            return data;
        }

        loadExistingData() {
            // Load existing images if editing
            const productId = $('input[name="product_id"]').val();
            if (!productId) return;

            $.post(vssProUploader.ajaxUrl, {
                action: 'vss_pro_load_images',
                product_id: productId,
                nonce: vssProUploader.nonce
            }, (response) => {
                if (response.success) {
                    Object.keys(response.data).forEach(imageType => {
                        this.currentImages[imageType] = response.data[imageType];
                        response.data[imageType].forEach(imageData => {
                            this.renderImagePreview(imageData, imageType);
                        });
                    });
                }
            });
        }

        // Helper methods for image management
        generateUploadId() {
            return Date.now().toString(36) + Math.random().toString(36).substr(2);
        }

        showUploadProgress(uploadId, filename) {
            // Implementation for upload progress UI
        }

        updateUploadProgress(uploadId, percentage) {
            // Implementation for progress update
        }

        hideUploadProgress(uploadId) {
            // Implementation to hide progress
        }

        deleteImage(imageId, imageType, $element) {
            if (!confirm(vssProUploader.strings.confirm_delete)) return;

            $.post(vssProUploader.ajaxUrl, {
                action: 'vss_pro_delete_image',
                image_id: imageId,
                nonce: vssProUploader.nonce
            }, (response) => {
                if (response.success) {
                    $element.fadeOut(() => {
                        $element.remove();
                        this.currentImages[imageType] = this.currentImages[imageType].filter(img => img.id !== imageId);
                        this.updateProgressBar();
                        this.calculateQualityScore();
                        this.markDirty();
                    });
                    this.showNotification('success', 'Image deleted successfully');
                } else {
                    this.showNotification('error', response.data.message || 'Delete failed');
                }
            });
        }

        setPrimaryImage(imageId, imageType) {
            $.post(vssProUploader.ajaxUrl, {
                action: 'vss_pro_set_primary_image',
                image_id: imageId,
                nonce: vssProUploader.nonce
            }, (response) => {
                if (response.success) {
                    // Update UI to reflect new primary
                    $(`.vss-uploaded-images[data-type="${imageType}"] .vss-primary-badge`).remove();
                    $(`.vss-uploaded-images[data-type="${imageType}"] .primary`).show();
                    
                    const $newPrimary = $(`.vss-image-item[data-id="${imageId}"]`);
                    $newPrimary.prepend('<div class="vss-image-meta vss-primary-badge">Primary</div>');
                    $newPrimary.find('.primary').hide();
                    
                    this.showNotification('success', 'Primary image updated');
                    this.markDirty();
                } else {
                    this.showNotification('error', response.data.message || 'Update failed');
                }
            });
        }

        openImageEditor(imageId) {
            // Image editor functionality would be implemented here
            this.showNotification('info', 'Image editor coming soon!');
        }

        optimizeAllImages() {
            if (Object.values(this.currentImages).every(imgs => imgs.length === 0)) {
                this.showNotification('info', 'No images to optimize');
                return;
            }

            this.showNotification('info', vssProUploader.strings.optimizing);

            $.post(vssProUploader.ajaxUrl, {
                action: 'vss_pro_optimize_images',
                product_id: $('input[name="product_id"]').val(),
                nonce: vssProUploader.nonce
            }, (response) => {
                if (response.success) {
                    this.showNotification('success', 'Images optimized successfully!');
                    // Reload image previews with optimized versions
                    this.loadExistingData();
                } else {
                    this.showNotification('error', response.data.message || 'Optimization failed');
                }
            });
        }

        updateImageOrder() {
            const orders = {};
            
            $('.vss-sortable').each(function() {
                const imageType = $(this).data('type');
                orders[imageType] = [];
                
                $(this).find('.vss-image-item').each(function(index) {
                    orders[imageType].push({
                        id: $(this).data('id'),
                        order: index
                    });
                });
            });

            $.post(vssProUploader.ajaxUrl, {
                action: 'vss_pro_update_image_order',
                orders: orders,
                nonce: vssProUploader.nonce
            });
        }
    }

    // Make class globally available
    window.VSSProfessionalUploader = VSSProfessionalUploader;

})(jQuery);