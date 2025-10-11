
jQuery(document).ready(function($) {
    // Tab switching
    $(".aspm-tab-nav a").on("click", function(e) {
        e.preventDefault();
        var target = $(this).attr("href");
        
        $(".aspm-tab-nav a").removeClass("active");
        $(this).addClass("active");
        
        $(".aspm-tab-content").removeClass("active");
        $(target).addClass("active");
    });
    
    // Load stats on page load
    loadStats();
    setInterval(loadStats, 30000); // Refresh every 30 seconds
    
    function loadStats() {
        $.post(aspm_ajax.ajax_url, {
            action: "aspm_get_stats",
            nonce: aspm_ajax.nonce
        }, function(response) {
            if (response.success) {
                $("#current-load").text(response.data.current_load);
                $("#queue-size").text(response.data.queue_size);
                $("#failed-actions").text(response.data.failed_actions);
                $("#avg-processing").text(response.data.avg_processing);
                
                // Show warnings
                if (response.data.warnings.length > 0) {
                    var warningsHtml = "";
                    response.data.warnings.forEach(function(warning) {
                        warningsHtml += '<div class="notice notice-' + warning.level + '"><p>' + warning.message + '</p></div>';
                    });
                    $("#performance-warnings").html(warningsHtml);
                }
            }
        });
    }
    
    // Settings form
    $("#aspm-settings-form").on("submit", function(e) {
        e.preventDefault();
        var $form = $(this);
        var $spinner = $form.find(".spinner");
        
        $spinner.addClass("is-active");
        
        $.post(aspm_ajax.ajax_url, {
            action: "aspm_update_settings",
            nonce: aspm_ajax.nonce,
            disable_async_on_frontend: $("#disable_async_on_frontend").is(":checked") ? 1 : 0,
            batch_size: $("#batch_size").val(),
            time_limit: $("#time_limit").val(),
            cleanup_age: $("#cleanup_age").val(),
            enable_logging: $("#enable_logging").is(":checked") ? 1 : 0
        }, function(response) {
            $spinner.removeClass("is-active");
            if (response.success) {
                alert(response.data);
            } else {
                alert("Error saving settings");
            }
        });
    });
    
    // Tools
    $("#clean-completed").on("click", function() {
        if (!confirm("Clean all completed actions older than 30 days?")) return;
        
        $.post(aspm_ajax.ajax_url, {
            action: "aspm_clear_completed",
            nonce: aspm_ajax.nonce,
            days: 30
        }, function(response) {
            if (response.success) {
                $("#clean-result").html('<span class="success">' + response.data.message + '</span>');
                loadStats();
            }
        });
    });
    
    $("#process-manually").on("click", function() {
        var $button = $(this);
        $button.prop("disabled", true);
        
        $.post(aspm_ajax.ajax_url, {
            action: "aspm_process_queue",
            nonce: aspm_ajax.nonce
        }, function(response) {
            $button.prop("disabled", false);
            if (response.success) {
                $("#process-result").html('<span class="success">' + response.data.message + '</span>');
                loadStats();
            }
        });
    });
});
    