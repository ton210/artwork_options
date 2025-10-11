(function($) {
    'use strict';

    // Helper function for conditional logging based on mcr_public_ajax.debug_mode
    function mcr_js_log(message, data) {
        if (typeof mcr_public_ajax !== 'undefined' && mcr_public_ajax.debug_mode) {
            if (typeof data !== 'undefined') {
                console.log('MCR_JS_DEBUG: ' + message, data);
            } else {
                console.log('MCR_JS_DEBUG: ' + message);
            }
        }
    }

    mcr_js_log('MunchMakers Custom Request JS - File Loaded & Initializing (v1.2.1)...');

    $(document).ready(function() {
        mcr_js_log('MunchMakers Custom Request JS - Document Ready.');

        const $popupOverlay = $('#mcr-popup-overlay'); 
        const $body = $('body');
        
        // Event delegation for ANY button that should open the popup.
        $(document).on('click', '.mcr-popup-trigger', function(e) {
            e.preventDefault(); 
            const buttonId = $(this).attr('id') || 'unknown_trigger_button';
            mcr_js_log('Popup trigger clicked. Button ID: ' + buttonId);
            
            if ($popupOverlay.length) {
                $popupOverlay.addClass('active').attr('aria-hidden', 'false');
                $body.addClass('mcr-popup-open'); // For potential body scroll lock

                // Debugging checks
                if ($popupOverlay.hasClass('active')) {
                    mcr_js_log('.active class confirmed on #mcr-popup-overlay.');
                    mcr_js_log('Computed display style after adding class:', window.getComputedStyle($popupOverlay[0]).display);
                } else {
                    console.error('MCR_JS_ERROR: FAILED to add ".active" class to #mcr-popup-overlay.');
                }

                const $formInPopup = $('#mcr-custom-request-form'); 
                if ($formInPopup.length) {
                    $formInPopup[0].reset(); 
                    mcr_js_log('Popup form (#mcr-custom-request-form) reset.');
                } else {
                    mcr_js_log('Popup Form #mcr-custom-request-form not found (this might be okay if only inline form is present).', 'warn');
                }
                const $messagesDivInPopup = $('#mcr-form-messages'); 
                if ($messagesDivInPopup.length) {
                    $messagesDivInPopup.hide().removeClass('success error').empty();
                }
                
                // Focus on the first focusable element in the popup for accessibility
                const $firstFocusable = $popupOverlay.find('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])').filter(':visible:first');
                if ($firstFocusable.length) {
                    $firstFocusable.focus();
                    mcr_js_log('Focused on first visible element in popup.');
                } else {
                    mcr_js_log('No visible focusable element found in popup.', 'warn');
                }
            } else {
                console.error('MCR_JS_ERROR: Popup overlay #mcr-popup-overlay HTML element not found in the DOM. Cannot open popup.');
            }
        });

        function closeMcrPopup() {
            if ($popupOverlay.hasClass('active')) {
                mcr_js_log('Closing popup.');
                $popupOverlay.removeClass('active').attr('aria-hidden', 'true');
                $body.removeClass('mcr-popup-open');
                 // Return focus to the trigger button if possible and sensible
                // This needs more sophisticated tracking of which button triggered it if multiple exist.
                // For now, a generic approach or focus the first visible trigger.
                $('.mcr-popup-trigger:visible:first').focus();
            }
        }

        // Bind close events only if the popup overlay exists
        if ($popupOverlay.length) {
            const $closePopupButton = $('#mcr-popup-close-btn');

            if ($closePopupButton.length) {
                $closePopupButton.on('click', function() {
                    mcr_js_log('Close button clicked.');
                    closeMcrPopup();
                });
            } else {
                mcr_js_log('Close button #mcr-popup-close-btn not found.', 'warn');
            }

            // Click on overlay to close
            $popupOverlay.on('click', function(e) {
                if (e.target === this) { 
                    mcr_js_log('Overlay background clicked.');
                    closeMcrPopup();
                }
            });

            // Escape key to close
            $(document).on('keydown', function(e) {
                if (e.key === "Escape" && $popupOverlay.hasClass('active')) {
                    mcr_js_log('Escape key pressed, closing popup.');
                    closeMcrPopup();
                }
            });
        }

        // Handle form submission for BOTH popup and inline forms
        $(document).on('submit', 'form.mcr-custom-request-form', function(e) {
            const $currentForm = $(this);
            const formId = $currentForm.attr('id') || 'unknown_form';
            mcr_js_log('Submit event triggered for form: #' + formId);
            
            e.preventDefault(); 
            mcr_js_log('e.preventDefault() CALLED for form: #' + formId);

            if (typeof mcr_public_ajax === 'undefined' || !mcr_public_ajax.ajax_url || !mcr_public_ajax.nonce) {
                console.error('MCR_JS_ERROR: mcr_public_ajax object not defined or incomplete. AJAX submission cannot proceed.');
                alert('A configuration error occurred. Please contact support (Ref: MCR_AJAX_OBJ_MISSING).');
                return;
            }

            // Try to find the message div within the current form first, then fallback to specific IDs if needed.
            let $messagesDiv = $currentForm.find('.mcr-form-messages'); 
            if (!$messagesDiv.length) { // Fallback for older structure or if class isn't on the div
                $messagesDiv = (formId === 'mcr-custom-request-form-inline') ? $('#mcr-form-messages-inline') : $('#mcr-form-messages');
            }
            
            if($messagesDiv.length) {
                $messagesDiv.hide().removeClass('success error').empty();
            } else { 
                mcr_js_log('Messages DIV (e.g., .mcr-form-messages or specific ID) not found for form #' + formId, 'warn'); 
            }
            
            const $submitButton = $currentForm.find('.mcr-submit-button'); 
            const $spinner = $submitButton.length ? $submitButton.find('.mcr-spinner') : $();

            if($submitButton.length) { $submitButton.prop('disabled', true); }
            if($spinner.length) { $spinner.show(); }

            // Client-side validation
            const emailInput = $currentForm.find('input[name="mcr_email"]');
            const email = emailInput.val();
            const requestDetailsInput = $currentForm.find('textarea[name="mcr_request_details"]');
            const requestDetails = requestDetailsInput.val();

            if (!email || email.trim() === '' || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.trim())) {
                showErrorInForm(mcr_public_ajax.fill_required_fields_message || 'Please enter a valid email address.', $messagesDiv, $submitButton, $spinner);
                if (emailInput.length) emailInput.focus();
                return;
            }
            if (!requestDetails || requestDetails.trim() === '') {
                showErrorInForm(mcr_public_ajax.fill_required_fields_message || 'Please describe your request.', $messagesDiv, $submitButton, $spinner);
                if (requestDetailsInput.length) requestDetailsInput.focus();
                return;
            }

            let formData = new FormData(this); 
            
            // Ensure the nonce from mcr_public_ajax is sent as 'nonce' for PHP's check_ajax_referer
            if (typeof mcr_public_ajax !== 'undefined' && mcr_public_ajax.nonce) {
                formData.append('nonce', mcr_public_ajax.nonce);
                 mcr_js_log('Appended nonce to FormData: ' + mcr_public_ajax.nonce);
            } else {
                console.error('MCR_JS_ERROR: mcr_public_ajax.nonce is not defined. Nonce cannot be appended.');
                // Optionally, stop submission if nonce is critical and missing
                showErrorInForm('A security token is missing. Please refresh the page and try again.', $messagesDiv, $submitButton, $spinner);
                return;
            }
            // The hidden input 'action' with value 'mcr_handle_custom_request' is automatically included by FormData(this)
            // The hidden nonce field generated by wp_nonce_field() is also automatically included.
            // PHP's check_ajax_referer('mcr_handle_custom_request_nonce', 'nonce') will use the 'nonce' field we just appended.


            mcr_js_log('FormData prepared for submission. Contents:', formData); // FormData itself isn't easily logged directly this way
            // For debugging FormData contents (iterate if needed, shown in mcr_js_log example before):
            // for (var pair of formData.entries()) {
            //     mcr_js_log(pair[0]+ ': ' + (pair[1] instanceof File ? pair[1].name + " (File Size: " + pair[1].size + " bytes)" : pair[1]));
            // }


            $.ajax({
                url: mcr_public_ajax.ajax_url,
                type: 'POST',
                data: formData,
                processData: false, // Important for FormData
                contentType: false, // Important for FormData
                dataType: 'json', // Expect JSON response from server
                success: function(response) {
                    mcr_js_log('AJAX success for form #' + formId, response);
                    if (response && response.success && response.data && response.data.redirect_url) {
                        mcr_js_log('Redirecting to: ' + response.data.redirect_url);
                        window.location.href = response.data.redirect_url;
                    } else {
                        // Handle cases where response or response.data or response.data.message might be undefined
                        const message = (response && response.data && response.data.message) 
                                        ? response.data.message 
                                        : mcr_public_ajax.error_message;
                        showErrorInForm(message, $messagesDiv, $submitButton, $spinner);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    // Log more detailed error information
                    console.error("MCR_JS_ERROR: AJAX error for form #" + formId, {
                        status: jqXHR.status, // e.g., 403, 500
                        textStatus: textStatus, // e.g., "error", "timeout"
                        errorThrown: errorThrown, // e.g., "Forbidden", "Internal Server Error"
                        responseText: jqXHR.responseText // Server's raw response, very useful for debugging
                    });
                    let userMessage = mcr_public_ajax.error_message;
                    if (jqXHR.status) {
                        userMessage += ' (Server Error: ' + jqXHR.status + ' ' + textStatus + ')';
                    } else {
                        userMessage += ' (Server Error: ' + textStatus + ')';
                    }
                    showErrorInForm(userMessage, $messagesDiv, $submitButton, $spinner);
                },
                complete: function(jqXHR) { // jqXHR will contain responseJSON if dataType was json and parse succeeded
                     mcr_js_log('AJAX Complete for form #' + formId);
                     try {
                        // Re-enable button only if not redirecting
                        // Check responseJSON for success and redirect_url
                        const responseData = jqXHR.responseJSON;
                        if (!(responseData && responseData.success && responseData.data && responseData.data.redirect_url)) {
                           if($submitButton.length) $submitButton.prop('disabled', false);
                           if($spinner.length) $spinner.hide();
                        }
                    } catch (e) {
                        // Fallback if responseJSON is not as expected or error during parsing
                        mcr_js_log('Error in AJAX complete handler while checking responseJSON: ' + e.message, 'warn');
                        // Ensure button is re-enabled in case of any error here
                        if($submitButton.length) $submitButton.prop('disabled', false);
                        if($spinner.length) $spinner.hide();
                    }
                }
            });
        });

        function showErrorInForm(message, $msgDiv, $submitBtn, $spinElement) {
            mcr_js_log('Displaying error in form: "' + message + '"', 'warn');
            if ($msgDiv.length) {
                $msgDiv.html(message).removeClass('success').addClass('error').show(); // Use .html() if message might contain HTML
            } else {
                // This fallback is if the designated message div isn't found at all
                console.error('MCR_JS_ERROR: Message DIV not found, using alert for error: ' + message);
                alert('Error: ' + message); 
            }
            if ($submitBtn.length) $submitBtn.prop('disabled', false);
            if ($spinElement.length) $spinElement.hide();
        }

    }); // end document.ready
})(jQuery);