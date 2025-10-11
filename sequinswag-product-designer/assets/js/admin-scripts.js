(function($) {
    'use strict';

    var SWPD_Admin = {
        init: function() {
            this.bindEvents();
            this.initColorPickers();
            this.initTabs();
            this.initMediaUploader();
            this.initCloudinaryTest();
        },

        bindEvents: function() {
            // Tab switching
            $('.nav-tab').on('click', function(e) {
                e.preventDefault();
                var tab = $(this).data('tab');
                SWPD_Admin.switchTab(tab);
            });

            // Design helper buttons
            $('.swpd-generate-json').on('click', this.generateDesignJSON);
            $('.swpd-preview-design').on('click', this.previewDesign);
            $('.swpd-validate-json-btn').on('click', this.validateJSON);
            $('.swpd-design-helper-btn').on('click', this.openDesignHelper);
        },

        switchTab: function(tab) {
            $('.swpd-tab-content').hide();
            $('.nav-tab').removeClass('nav-tab-active');
            $('#' + tab + '-tab').show();
            $('.nav-tab[data-tab="' + tab + '"]').addClass('nav-tab-active');

            // Store active tab
            if (typeof(Storage) !== "undefined") {
                localStorage.setItem('swpd_active_tab', tab);
            }
        },

        initTabs: function() {
            // Check for stored active tab
            var activeTab = localStorage.getItem('swpd_active_tab');
            if (activeTab && $('.nav-tab[data-tab="' + activeTab + '"]').length) {
                this.switchTab(activeTab);
            } else {
                // Show first tab by default
                var firstTab = $('.nav-tab').first().data('tab');
                if (firstTab) {
                    this.switchTab(firstTab);
                }
            }
        },

        initColorPickers: function() {
            if ($.fn.wpColorPicker) {
                $('.swpd-color-picker').wpColorPicker();
            }
        },

        initMediaUploader: function() {
            var mediaUploader;

            $('.swpd-media-upload').on('click', function(e) {
                e.preventDefault();

                var button = $(this);
                var field = button.data('field');
                var input = button.prev('input');

                // If the media uploader already exists, reopen it
                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }

                // Create the media uploader
                mediaUploader = wp.media({
                    title: 'Select Image',
                    button: {
                        text: 'Use this image'
                    },
                    multiple: false
                });

                // When an image is selected, update the input field
                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    input.val(attachment.url);

                    // Trigger change event
                    input.trigger('change');
                });

                // Open the media uploader
                mediaUploader.open();
            });
        },

        generateDesignJSON: function() {
            var baseImage = $('.swpd-base-image-url').val();
            var alphaMask = $('.swpd-alpha-mask-url').val();
            var unclippedMask = $('.swpd-unclipped-mask-url').val();

            if (!baseImage || !alphaMask) {
                alert('Please provide at least Base Image and Alpha Mask URLs');
                return;
            }

            var jsonData = {
                baseImage: baseImage,
                alphaMask: alphaMask
            };

            if (unclippedMask) {
                jsonData.unclippedMask = unclippedMask;
            }

            $('#design_tool_layer').val(JSON.stringify(jsonData, null, 2));

            // Auto-validate after generation
            SWPD_Admin.validateJSON();
        },

        previewDesign: function() {
            var jsonStr = $('#design_tool_layer').val();

            if (!jsonStr) {
                alert('Please generate or enter JSON data first');
                return;
            }

            try {
                var data = JSON.parse(jsonStr);

                var previewHtml = '<div style="position: relative; display: inline-block;">';
                previewHtml += '<img src="' + data.baseImage + '" style="max-width: 100%; height: auto;" />';

                if (data.unclippedMask || data.alphaMask) {
                    previewHtml += '<img src="' + (data.unclippedMask || data.alphaMask) + '" style="position: absolute; top: 0; left: 0; max-width: 100%; height: auto; opacity: 0.5;" />';
                }

                previewHtml += '</div>';

                $('.swpd-preview-container').html(previewHtml);
                $('.swpd-preview-area').show();

            } catch (e) {
                alert('Invalid JSON: ' + e.message);
            }
        },

        validateJSON: function() {
            var button = $(this);
            var loop = button.data('loop');
            var textarea = loop ? $('#variable_design_tool_layer\\[' + loop + '\\]') : $('#design_tool_layer');
            var jsonStr = textarea.val();

            if (!jsonStr) {
                alert('Please enter JSON data to validate');
                return;
            }

            // Disable button and show loading
            button.prop('disabled', true).text('Validating...');

            $.post(swpdAdmin.ajaxUrl, {
                action: 'swpd_validate_design_json',
                nonce: swpdAdmin.nonce,
                json: jsonStr
            }, function(response) {
                button.prop('disabled', false).text('Validate JSON');

                if (response.success) {
                    // Show success message
                    var successMsg = $('<span class="swpd-validation-success" style="color: green; margin-left: 10px;">✓ ' + response.data.message + '</span>');
                    button.after(successMsg);

                    // Update textarea with formatted JSON
                    if (response.data.formatted) {
                        textarea.val(JSON.stringify(response.data.formatted, null, 2));
                    }

                    // Remove success message after 3 seconds
                    setTimeout(function() {
                        successMsg.fadeOut(function() {
                            $(this).remove();
                        });
                    }, 3000);
                } else {
                    alert('Validation Error: ' + response.data);
                }
            }).fail(function() {
                button.prop('disabled', false).text('Validate JSON');
                alert('AJAX request failed. Please try again.');
            });
        },

        openDesignHelper: function() {
            var loop = $(this).data('loop');
            // TODO: Implement design helper modal
            alert('Design helper coming soon for variation ' + loop);
        },

        initCloudinaryTest: function() {
            $('#swpd-test-cloudinary').on('click', function() {
                var button = $(this);
                var spinner = button.next('.spinner');
                var resultDiv = $('#swpd-cloudinary-test-result');
                var debugDiv = $('#swpd-cloudinary-debug-info');

                // Disable button and show spinner
                button.prop('disabled', true);
                spinner.addClass('is-active');
                resultDiv.html('');
                debugDiv.hide();

                // Gather form values
                var data = {
                    action: 'swpd_test_cloudinary',
                    nonce: swpdAdmin.nonce,
                    cloud_name: $('#swpd_cloudinary_cloud_name').val(),
                    api_key: $('#swpd_cloudinary_api_key').val(),
                    api_secret: $('#swpd_cloudinary_api_secret').val(),
                    upload_preset: $('#swpd_cloudinary_upload_preset').val()
                };

                $.post(swpdAdmin.ajaxUrl, data, function(response) {
                    button.prop('disabled', false);
                    spinner.removeClass('is-active');

                    if (response.success) {
                        resultDiv.html('<div class="notice notice-success inline"><p>' + response.data.message + '</p></div>');
                    } else {
                        resultDiv.html('<div class="notice notice-error inline"><p>' + response.data.message + '</p></div>');
                    }

                    // Show debug info if available
                    if (response.data && response.data.debug) {
                        debugDiv.find('pre').text(JSON.stringify(response.data.debug, null, 2));
                        debugDiv.show();
                    }
                }).fail(function(xhr, status, error) {
                    button.prop('disabled', false);
                    spinner.removeClass('is-active');
                    resultDiv.html('<div class="notice notice-error inline"><p>AJAX Error: ' + error + '</p></div>');

                    console.error('Cloudinary test failed:', {
                        status: status,
                        error: error,
                        response: xhr.responseText
                    });
                });
            });
        }
    };

    $(document).ready(function() {
        SWPD_Admin.init();
    });

})(jQuery);