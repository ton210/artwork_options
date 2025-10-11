/**
 * VSS Product Admin JavaScript
 * Comprehensive admin interface functionality
 */

(function($) {
    'use strict';

    // Global variables
    var currentProductId = null;
    var isLoading = false;

    // Initialize when document is ready
    $(document).ready(function() {
        initializeAdmin();
        bindEvents();
        initializeModals();
    });

    /**
     * Initialize admin functionality
     */
    function initializeAdmin() {
        // Initialize view toggles
        initializeViewToggle();
        
        // Initialize tooltips if available
        if ($.fn.tooltip) {
            $('[title]').tooltip();
        }
        
        // Auto-calculate selling price
        initializeCostCalculation();
        
        // Initialize image gallery in modals
        initializeImageGallery();
        
        console.log('VSS Product Admin initialized');
    }

    /**
     * Bind all event handlers
     */
    function bindEvents() {
        // View toggle
        $(document).on('click', '.vss-view-toggle', handleViewToggle);
        
        // Product actions
        $(document).on('click', '.vss-view-details', handleViewDetails);
        $(document).on('click', '.vss-manage-costs', handleManageCosts);
        $(document).on('click', '.vss-approve-product', handleApproveProduct);
        $(document).on('click', '.vss-reject-product', handleRejectProduct);
        $(document).on('click', '.vss-send-feedback', handleSendFeedback);
        
        // Bulk actions
        $(document).on('click', '#apply-bulk-action', handleBulkAction);
        $(document).on('change', '#cb-select-all', handleSelectAll);
        $(document).on('change', 'input[name="product[]"]', updateBulkActionVisibility);
        
        // Modal events
        $(document).on('click', '.vss-modal-close, .vss-modal', handleCloseModal);
        $(document).on('click', '.vss-modal-content', function(e) { e.stopPropagation(); });
        
        // Form submissions
        $(document).on('submit', '#vss-cost-form', handleCostFormSubmit);
        $(document).on('submit', '#vss-feedback-form', handleFeedbackFormSubmit);
        $(document).on('click', '#cancel-cost-update, #cancel-feedback', handleCloseModal);
        
        // Cost calculation
        $(document).on('input', '#our-cost, #markup-percentage', calculateSellingPrice);
        
        // ESC key to close modals
        $(document).on('keyup', function(e) {
            if (e.keyCode === 27) { // ESC
                $('.vss-modal:visible').hide();
            }
        });
    }

    /**
     * Initialize modals
     */
    function initializeModals() {
        // Ensure modals are hidden on load
        $('.vss-modal').hide();
    }

    /**
     * Initialize view toggle functionality
     */
    function initializeViewToggle() {
        var activeView = localStorage.getItem('vss_product_view') || 'cards';
        switchView(activeView);
    }

    /**
     * Handle view toggle
     */
    function handleViewToggle(e) {
        e.preventDefault();
        var view = $(this).data('view');
        switchView(view);
        localStorage.setItem('vss_product_view', view);
    }

    /**
     * Switch between card and table view
     */
    function switchView(view) {
        $('.vss-view-toggle').removeClass('active');
        $('.vss-view-toggle[data-view="' + view + '"]').addClass('active');
        
        $('.vss-view-cards, .vss-view-table').removeClass('active');
        $('.vss-view-' + view).addClass('active');
    }

    /**
     * Handle select all checkbox
     */
    function handleSelectAll() {
        var checked = $(this).is(':checked');
        $('input[name="product[]"]').prop('checked', checked);
        updateBulkActionVisibility();
    }

    /**
     * Update bulk action visibility
     */
    function updateBulkActionVisibility() {
        var selectedCount = $('input[name="product[]"]:checked').length;
        var bulkActions = $('.vss-bulk-actions');
        
        if (selectedCount > 0) {
            bulkActions.addClass('has-selection');
        } else {
            bulkActions.removeClass('has-selection');
        }
    }

    /**
     * Handle bulk actions
     */
    function handleBulkAction() {
        var action = $('#bulk-action-selector').val();
        var selected = getSelectedProducts();
        
        if (!action) {
            showNotice('Please select an action', 'error');
            return;
        }
        
        if (selected.length === 0) {
            showNotice('Please select at least one product', 'error');
            return;
        }
        
        if (!confirm(vss_product_admin.i18n.confirm_bulk_action)) {
            return;
        }
        
        performBulkAction(action, selected);
    }

    /**
     * Get selected product IDs
     */
    function getSelectedProducts() {
        return $('input[name="product[]"]:checked').map(function() {
            return $(this).val();
        }).get();
    }

    /**
     * Perform bulk action
     */
    function performBulkAction(action, productIds) {
        if (isLoading) return;
        
        setLoading(true);
        showNotice('Processing...', 'info');
        
        $.ajax({
            url: vss_product_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'vss_bulk_product_action',
                nonce: vss_product_admin.nonce,
                bulk_action: action,
                product_ids: productIds
            },
            success: function(response) {
                if (response.success) {
                    showNotice(response.data.message || vss_product_admin.i18n.success, 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showNotice(response.data || vss_product_admin.i18n.error, 'error');
                }
            },
            error: function() {
                showNotice(vss_product_admin.i18n.error, 'error');
            },
            complete: function() {
                setLoading(false);
            }
        });
    }

    /**
     * Handle view details
     */
    function handleViewDetails() {
        var productId = $(this).data('product-id');
        loadProductDetails(productId);
    }

    /**
     * Load product details
     */
    function loadProductDetails(productId) {
        currentProductId = productId;
        
        $('#vss-product-modal-content').html('<div class="loading-placeholder">' + vss_product_admin.i18n.loading + '</div>');
        $('#vss-product-modal').show();
        
        $.ajax({
            url: vss_product_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'vss_get_product_details',
                nonce: vss_product_admin.nonce,
                product_id: productId
            },
            success: function(response) {
                if (response.success) {
                    $('#vss-product-modal-content').html(response.data.html);
                    initializeImageGallery();
                } else {
                    $('#vss-product-modal-content').html('<p>Error loading product details.</p>');
                }
            },
            error: function() {
                $('#vss-product-modal-content').html('<p>Error loading product details.</p>');
            }
        });
    }

    /**
     * Handle manage costs
     */
    function handleManageCosts() {
        var productId = $(this).data('product-id');
        openCostModal(productId);
    }

    /**
     * Open cost management modal
     */
    function openCostModal(productId) {
        currentProductId = productId;
        
        // Load existing cost data
        $.ajax({
            url: vss_product_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'vss_get_product_costs',
                nonce: vss_product_admin.nonce,
                product_id: productId
            },
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    $('#cost-product-id').val(productId);
                    $('#our-cost').val(data.our_cost || '');
                    $('#markup-percentage').val(data.markup_percentage || '');
                    $('#selling-price').val(data.selling_price || '');
                    $('#admin-notes').val(data.admin_notes || '');
                    
                    calculateSellingPrice();
                    $('#vss-cost-modal').show();
                }
            },
            error: function() {
                showNotice('Error loading cost data', 'error');
            }
        });
    }

    /**
     * Initialize cost calculation
     */
    function initializeCostCalculation() {
        calculateSellingPrice();
    }

    /**
     * Calculate selling price automatically
     */
    function calculateSellingPrice() {
        var cost = parseFloat($('#our-cost').val()) || 0;
        var markup = parseFloat($('#markup-percentage').val()) || 0;
        
        var sellingPrice = cost * (1 + (markup / 100));
        $('#selling-price').val(sellingPrice.toFixed(2));
    }

    /**
     * Handle cost form submission
     */
    function handleCostFormSubmit(e) {
        e.preventDefault();
        
        if (isLoading) return;
        
        var formData = $(this).serialize();
        formData += '&action=vss_update_product_costs&nonce=' + vss_product_admin.nonce;
        
        setLoading(true);
        
        $.ajax({
            url: vss_product_admin.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    showNotice(response.data.message || 'Costs updated successfully', 'success');
                    $('#vss-cost-modal').hide();
                    
                    // Update the card display if needed
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    showNotice(response.data || 'Error updating costs', 'error');
                }
            },
            error: function() {
                showNotice('Error updating costs', 'error');
            },
            complete: function() {
                setLoading(false);
            }
        });
    }

    /**
     * Handle approve product
     */
    function handleApproveProduct() {
        var productId = $(this).data('product-id');
        
        if (!confirm(vss_product_admin.i18n.confirm_approve)) {
            return;
        }
        
        performProductAction('approve', productId);
    }

    /**
     * Handle reject product
     */
    function handleRejectProduct() {
        var productId = $(this).data('product-id');
        
        if (!confirm(vss_product_admin.i18n.confirm_reject)) {
            return;
        }
        
        performProductAction('reject', productId);
    }

    /**
     * Perform product action (approve/reject)
     */
    function performProductAction(action, productId) {
        if (isLoading) return;
        
        setLoading(true);
        
        $.ajax({
            url: vss_product_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'vss_' + action + '_product',
                nonce: vss_product_admin.nonce,
                product_id: productId
            },
            success: function(response) {
                if (response.success) {
                    showNotice(response.data.message || 'Action completed successfully', 'success');
                    
                    // Update the product card status
                    var card = $('.vss-product-card[data-product-id="' + productId + '"]');
                    var newStatus = action === 'approve' ? 'approved' : 'rejected';
                    
                    card.find('.vss-card-status')
                        .removeClass('status-pending')
                        .addClass('status-' + newStatus)
                        .text(newStatus.charAt(0).toUpperCase() + newStatus.slice(1));
                    
                    card.find('.vss-approval-actions').remove();
                    
                    // Update statistics
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showNotice(response.data || 'Action failed', 'error');
                }
            },
            error: function() {
                showNotice('Action failed', 'error');
            },
            complete: function() {
                setLoading(false);
            }
        });
    }

    /**
     * Handle send feedback
     */
    function handleSendFeedback() {
        var productId = $(this).data('product-id');
        openFeedbackModal(productId);
    }

    /**
     * Open feedback modal
     */
    function openFeedbackModal(productId) {
        currentProductId = productId;
        $('#feedback-product-id').val(productId);
        $('#feedback-message').val('');
        $('input[name="request_changes"]').prop('checked', false);
        $('input[name="notify_vendor"]').prop('checked', true);
        $('#vss-feedback-modal').show();
        $('#feedback-message').focus();
    }

    /**
     * Handle feedback form submission
     */
    function handleFeedbackFormSubmit(e) {
        e.preventDefault();
        
        if (isLoading) return;
        
        var message = $('#feedback-message').val().trim();
        if (!message) {
            showNotice('Please enter a feedback message', 'error');
            return;
        }
        
        var formData = $(this).serialize();
        formData += '&action=vss_send_vendor_feedback&nonce=' + vss_product_admin.nonce;
        
        setLoading(true);
        
        $.ajax({
            url: vss_product_admin.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    showNotice(response.data.message || 'Feedback sent successfully', 'success');
                    $('#vss-feedback-modal').hide();
                } else {
                    showNotice(response.data || 'Error sending feedback', 'error');
                }
            },
            error: function() {
                showNotice('Error sending feedback', 'error');
            },
            complete: function() {
                setLoading(false);
            }
        });
    }

    /**
     * Handle close modal
     */
    function handleCloseModal(e) {
        if (e.target === this) {
            $(this).hide();
        }
    }

    /**
     * Initialize image gallery
     */
    function initializeImageGallery() {
        $(document).on('click', '.vss-product-images img', function() {
            var newSrc = $(this).attr('src');
            $(this).siblings().removeClass('active');
            $(this).addClass('active');
            $('.vss-main-product-image').attr('src', newSrc);
        });
        
        // Set first image as active by default
        $('.vss-product-images img:first').addClass('active');
    }

    /**
     * Show notification
     */
    function showNotice(message, type) {
        // Remove existing notices
        $('.vss-notice').remove();
        
        // Create new notice
        var notice = $('<div class="vss-notice ' + type + '">' + message + '</div>');
        $('.vss-product-admin').prepend(notice);
        
        // Auto-hide after 5 seconds for success messages
        if (type === 'success') {
            setTimeout(function() {
                notice.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        }
        
        // Scroll to top to show notice
        $('html, body').animate({
            scrollTop: $('.vss-product-admin').offset().top - 50
        }, 500);
    }

    /**
     * Set loading state
     */
    function setLoading(loading) {
        isLoading = loading;
        
        if (loading) {
            $('.vss-product-admin').addClass('vss-loading');
            $('button').prop('disabled', true);
        } else {
            $('.vss-product-admin').removeClass('vss-loading');
            $('button').prop('disabled', false);
        }
    }

    /**
     * Utility: Format currency
     */
    function formatCurrency(amount) {
        return '$' + parseFloat(amount).toFixed(2);
    }

    /**
     * Utility: Format date
     */
    function formatDate(dateString) {
        var date = new Date(dateString);
        return date.toLocaleDateString();
    }

    /**
     * Handle search form auto-submit
     */
    $(document).on('change', '.vss-vendor-filter', function() {
        $(this).closest('form').submit();
    });

    /**
     * Handle keyboard shortcuts
     */
    $(document).on('keydown', function(e) {
        // Ctrl/Cmd + A to select all
        if ((e.ctrlKey || e.metaKey) && e.keyCode === 65) {
            if ($('input[name="product[]"]').length > 0) {
                e.preventDefault();
                $('#cb-select-all').click();
            }
        }
        
        // Enter key in search input
        if (e.keyCode === 13 && e.target.classList.contains('vss-search-input')) {
            $(e.target).closest('form').submit();
        }
    });

    /**
     * Auto-save search state
     */
    var searchTimeout;
    $(document).on('input', '.vss-search-input', function() {
        clearTimeout(searchTimeout);
        var form = $(this).closest('form');
        
        searchTimeout = setTimeout(function() {
            if ($(this).val().length > 2 || $(this).val().length === 0) {
                form.submit();
            }
        }.bind(this), 1000);
    });

    /**
     * Initialize drag and drop for image reordering (future enhancement)
     */
    function initializeDragDrop() {
        // This could be implemented for reordering product images
        // Using jQuery UI sortable or a similar library
    }

    /**
     * Export functions for external access
     */
    window.VSSProductAdmin = {
        loadProductDetails: loadProductDetails,
        openCostModal: openCostModal,
        openFeedbackModal: openFeedbackModal,
        showNotice: showNotice,
        formatCurrency: formatCurrency,
        formatDate: formatDate
    };

})(jQuery);