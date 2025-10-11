(function($) {
    'use strict';

    $(document).ready(function() {

        // Handle the "Send Proof & Update Status" button click
        $('#mcr-send-proof-button').on('click', function(e) {
            e.preventDefault();

            var $button = $(this);
            var $spinner = $button.next('.spinner');
            var $feedbackDiv = $('#mcr-send-proof-feedback');
            var postId = $button.data('postid');

            var fileInput = $('#mcr_proof_file')[0];
            if (fileInput.files.length === 0) {
                $feedbackDiv.text('Please select a proof file to send.').css('color', 'red').show();
                return;
            }
            var file = fileInput.files[0];
            var message = $('#mcr_proof_message').val();

            $button.prop('disabled', true);
            $spinner.addClass('is-active');
            $feedbackDiv.hide().empty();

            var formData = new FormData();
            formData.append('action', 'mcr_send_proof_email');
            formData.append('nonce', mcr_admin_ajax.send_proof_nonce);
            formData.append('post_id', postId);
            formData.append('message', message);
            formData.append('mcr_proof_file', file);

            $.ajax({
                url: mcr_admin_ajax.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $feedbackDiv.text(response.data.message).css('color', 'green').show();
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        $feedbackDiv.text(response.data.message).css('color', 'red').show();
                         $button.prop('disabled', false);
                    }
                },
                error: function() {
                    $feedbackDiv.text(mcr_admin_ajax.error_text).css('color', 'red').show();
                     $button.prop('disabled', false);
                },
                complete: function() {
                    $spinner.removeClass('is-active');
                }
            });
        });

        // Handle the "Add Note" button click
        $('#mcr-add-note-button').on('click', function(e) {
            e.preventDefault();

            var $button = $(this);
            var $spinner = $button.next('.spinner');
            var $feedbackDiv = $('#mcr-add-note-feedback');
            var postId = $button.data('postid');
            var noteContent = $('#mcr_new_internal_note').val();

            if (!noteContent.trim()) {
                $feedbackDiv.text('Note cannot be empty.').css('color', 'red').show();
                return;
            }

            $button.prop('disabled', true);
            $spinner.addClass('is-active');
            $feedbackDiv.hide().empty();

            $.ajax({
                url: mcr_admin_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'mcr_add_internal_note',
                    nonce: mcr_admin_ajax.add_note_nonce,
                    post_id: postId,
                    note: noteContent
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $feedbackDiv.text(response.data.message).css('color', 'green').show();
                        if($('.mcr-internal-notes-log').length > 0) {
                            $('.mcr-internal-notes-log').prepend(response.data.note_html);
                        } else {
                             $('#mcr-internal-notes-log-wrapper').html('<ul class="mcr-internal-notes-log">' + response.data.note_html + '</ul>');
                        }
                        $('#mcr_new_internal_note').val('');
                        $('#mcr-internal-notes-log-wrapper p').remove();
                    } else {
                        $feedbackDiv.text(response.data.message).css('color', 'red').show();
                    }
                },
                error: function() {
                    $feedbackDiv.text(mcr_admin_ajax.error_text).css('color', 'red').show();
                },
                complete: function() {
                    $button.prop('disabled', false);
                    $spinner.removeClass('is-active');
                }
            });
        });
    });

})(jQuery);