<?php
/**
 * MunchMakers Custom Request Core Helper Functions
 *
 * This file contains core helper functions used across the plugin,
 * such as logging and status management.
 *
 * @package    MunchMakers_Custom_Request
 * @subpackage MunchMakers_Custom_Request/includes
 * @since      1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Logs a message to a dedicated log file if logging is enabled in settings.
 *
 * The log file will be created in wp-content/uploads/munchmakers_logs/
 *
 * @since 1.0.0
 * @updated 1.2.1 (Added context parameter, conceptual log level support)
 * @param string $message The message to log.
 * @param string $level   Log level (e.g., INFO, ERROR, DEBUG, WARNING). Defaults to 'INFO'.
 * @param string $context Optional. A string indicating the context (e.g., function name, process).
 */
function mcr_log( $message, $level = 'INFO', $context = '' ) {
    $options = get_option('mcr_options'); // Rebranded option name
    
    // Check if logging is enabled in the plugin settings
    if ( !isset($options['enable_logging']) || !$options['enable_logging'] ) {
        return; // Logging is disabled, so do nothing.
    }

    // -- Conceptual: Add support for configurable log level from $options['log_level'] --
    // Example:
    // $configured_log_level = isset($options['log_level']) ? strtoupper($options['log_level']) : 'INFO';
    // $log_levels_hierarchy = ['DEBUG' => 4, 'INFO' => 3, 'WARNING' => 2, 'ERROR' => 1];
    // // Check if current message's level is severe enough to be logged based on configuration
    // if ( ($log_levels_hierarchy[strtoupper($level)] ?? 0) < ($log_levels_hierarchy[$configured_log_level] ?? 3) ) {
    //     return; 
    // }
    // -- End Conceptual --

    $upload_dir = wp_upload_dir();
    $log_dir = $upload_dir['basedir'] . '/munchmakers_logs'; // Rebranded log directory

    // Ensure the log directory exists, try to create it if not.
    if ( !is_dir($log_dir) ) {
        if ( !wp_mkdir_p($log_dir) ) {
            // Fallback to WordPress standard error log if directory creation fails
            error_log("MunchMakers Plugin Log Error: Could not create log directory at {$log_dir}. Logging to standard PHP error log instead.");
            $context_str = $context ? " [{$context}]" : "";
            error_log("[MunchMakers Request - {$level}]{$context_str} {$message}");
            return;
        }
    }

    $log_file = $log_dir . '/munchmakers-requests-' . date('Y-m-d') . '.log'; // Rebranded log file name
    $timestamp = date('Y-m-d H:i:s T'); // Standard timestamp format
    $context_str = $context ? " [{$context}]" : "";
    $formatted_message = "[{$timestamp}] [" . strtoupper($level) . "]{$context_str} {$message}\n";

    // Append message to the log file.
    // Using error_log with type 3 is a safe way to append to a file.
    if (false === @error_log( $formatted_message, 3, $log_file )) {
        // Fallback if writing to custom log file fails
         error_log("MunchMakers Plugin Log Error: Could not write to custom log file {$log_file}. Logging to standard PHP error log instead.");
         error_log("[MunchMakers Request - {$level}]{$context_str} {$message}");
    }
}

/**
 * Handles file uploads for the plugin, creating a unique subdirectory.
 *
 * @since 1.2.1 (New helper function)
 * @param array  $file_data         The $_FILES['input_name'] array for the uploaded file.
 * @param string $base_upload_subdir The base subdirectory within wp-content/uploads (e.g., 'munchmakers_proofs', 'munchmakers_requests').
 * @param array  $allowed_mime_types Optional. Array of allowed mime types. If empty, WordPress defaults will be used. Ex: array('jpg|jpeg|jpe' => 'image/jpeg', 'png' => 'image/png')
 * @return array An array containing 'url', 'file' (path), 'type' on success, or an array with 'error' key on failure.
 */
function mcr_handle_file_upload( $file_data, $base_upload_subdir, $allowed_mime_types = array() ) {
    if ( ! function_exists( 'wp_handle_upload' ) ) {
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
    }

    $upload_overrides = array( 'test_form' => false );

    // Add filter for allowed mime types if provided
    // WordPress will use its own defaults if this is not set or is empty.
    // To strictly enforce only these types, you might need `wp_check_filetype_and_ext` and then use `upload_dir` filter before `wp_handle_upload`.
    // For simplicity here, we pass it to `wp_handle_upload` which respects it.
    if (!empty($allowed_mime_types)) {
        $upload_overrides['mimes'] = $allowed_mime_types;
    }

    $upload_dir_info   = wp_upload_dir(); // Get fresh upload dir info
    // Ensure the base_upload_subdir does not have leading/trailing slashes for clean path joining
    $base_upload_subdir = trim($base_upload_subdir, '/');
    $custom_subdir_path = $upload_dir_info['basedir'] . '/' . $base_upload_subdir . '/' . date( 'Y/m' );
    $custom_subdir_url_part = $base_upload_subdir . '/' . date( 'Y/m' );


    if ( ! is_dir( $custom_subdir_path ) ) {
        if ( ! wp_mkdir_p( $custom_subdir_path ) ) {
            $error_message = sprintf( __( 'Could not create directory for uploads: %s', 'munchmakers-custom-request' ), $custom_subdir_path );
            mcr_log( $error_message, 'ERROR', __FUNCTION__ );
            return array( 'error' => $error_message );
        }
    }

    // Temporarily filter upload directory
    $upload_dir_callback = function( $param ) use ( $custom_subdir_path, $upload_dir_info, $custom_subdir_url_part ) {
        $param['path']   = $custom_subdir_path; // Set the new path
        $param['url']    = $upload_dir_info['baseurl'] . '/' . $custom_subdir_url_part; // Set the new URL
        $param['subdir'] = ''; // We've built the full path, so empty this.
        // 'basedir' and 'baseurl' should remain WordPress's main upload paths
        // $param['basedir'] = $upload_dir_info['basedir'];
        // $param['baseurl'] = $upload_dir_info['baseurl'];
        $param['error']  = false; // No error
        return $param;
    };

    add_filter( 'upload_dir', $upload_dir_callback );
    $moved_file = wp_handle_upload( $file_data, $upload_overrides );
    remove_filter( 'upload_dir', $upload_dir_callback );

    if ( $moved_file && ! isset( $moved_file['error'] ) ) {
        // The URL might need reconstruction if the filter didn't perfectly align with wp_handle_upload's expectation.
        // $moved_file['url'] should be correct due to the filter.
        mcr_log( "File uploaded successfully: " . ($moved_file['url'] ?? 'URL not set') . " to path: " . ($moved_file['file'] ?? 'Path not set'), 'INFO', __FUNCTION__ );
        return $moved_file;
    } else {
        $upload_error_message = isset( $moved_file['error'] ) ? $moved_file['error'] : __( 'Unknown file upload error.', 'munchmakers-custom-request' );
        mcr_log( "File upload error: " . $upload_error_message, 'ERROR', __FUNCTION__ );
        return array( 'error' => $upload_error_message );
    }
}


/**
 * Get the defined request statuses with their labels.
 * These statuses are used in the admin dashboard and customer tracking page.
 *
 * @since 1.0.0
 * @return array Array of status keys and their translatable labels.
 */
function mcr_get_request_statuses() {
    return array(
        'new'                   => __( 'New', 'munchmakers-custom-request' ),
        'design_in_progress'    => __( 'Design in Progress', 'munchmakers-custom-request' ),
        'awaiting_customer'     => __( 'Proof Ready for Review', 'munchmakers-custom-request' ),
        'revisions_requested'   => __( 'Revisions Requested', 'munchmakers-custom-request' ),
        'in_production'         => __( 'In Production', 'munchmakers-custom-request' ),
        'completed'             => __( 'Completed', 'munchmakers-custom-request' ),
        'cancelled'             => __( 'Cancelled', 'munchmakers-custom-request' ),
    );
}

/**
 * Get the human-readable label for a given status key.
 *
 * @since 1.0.0
 * @param string $status_key The status key (e.g., 'design_in_progress').
 * @return string The translatable status label, or a formatted version of the key if not found.
 */
function mcr_get_status_label( $status_key ) {
    $statuses = mcr_get_request_statuses();
    if ( isset( $statuses[$status_key] ) ) {
        return $statuses[$status_key];
    }
    // Fallback for unknown status keys - format them nicely
    return ucwords( str_replace('_', ' ', $status_key ) );
}