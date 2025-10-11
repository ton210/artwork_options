<?php
/**
 * MunchMakers Custom Request Slack Notification Functions
 *
 * This file contains functions dedicated to preparing and sending
 * notifications to a configured Slack channel using Incoming Webhooks.
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
 * Sends a notification payload to the configured Slack webhook URL.
 *
 * This is a generic function that takes a Slack message payload (Block Kit).
 * It checks if a webhook URL is set in the plugin's options before attempting to send.
 *
 * @since 1.0.0
 * @param array $payload The Slack message payload (formatted for Slack's Block Kit API).
 * @return bool True on success, false on failure or if webhook is not configured.
 */
function mcr_send_slack_notification( $payload ) {
    $options = get_option('mcr_options'); // Rebranded option name
    $webhook_url = isset($options['slack_webhook_url']) ? trim($options['slack_webhook_url']) : '';

    if ( empty($webhook_url) || !filter_var($webhook_url, FILTER_VALIDATE_URL) ) {
        if (function_exists('mcr_log')) { // Check if logging function exists
            mcr_log('Slack Notification: Webhook URL is empty or invalid. Notification not sent.', 'WARNING');
        }
        return false;
    }

    $args = array(
        'body'        => json_encode($payload),
        'headers'     => array('Content-Type' => 'application/json'),
        'timeout'     => 15, // seconds
        'data_format' => 'body', // wp_remote_post expects data in 'body'
    );

    // Send the POST request to Slack
    $response = wp_remote_post( $webhook_url, $args );

    if ( is_wp_error( $response ) ) {
        if (function_exists('mcr_log')) {
            mcr_log('Slack Notification Error: ' . $response->get_error_message(), 'ERROR');
        }
        return false;
    }

    $response_code = wp_remote_retrieve_response_code( $response );
    $response_body = wp_remote_retrieve_body( $response );

    // Slack usually returns 'ok' on success for incoming webhooks.
    if ( $response_code !== 200 || strtolower(trim($response_body)) !== 'ok' ) {
        if (function_exists('mcr_log')) {
            mcr_log('Slack Notification HTTP Error or Unexpected Response: Code ' . $response_code . ' - Body: ' . $response_body, 'ERROR');
        }
        return false;
    }

    if (function_exists('mcr_log')) {
        $message_type = 'Unknown Slack Message Type';
        if (isset($payload['blocks'][0]['text']['text'])) {
            $message_type = $payload['blocks'][0]['text']['text'];
        } elseif (isset($payload['text'])) { // Fallback for simple text payloads
            $message_type = $payload['text'];
        }
        mcr_log('Slack notification sent successfully. Type: ' . $message_type, 'INFO');
    }
    return true;
}

/**
 * Prepares and sends a Slack notification when a new custom request is created.
 *
 * @since 1.0.0
 * @param int $request_id The ID of the newly created 'mcr_request' CPT post.
 */
function mcr_notify_slack_new_request( $request_id ) {
    $customer_name = get_post_meta( $request_id, '_customer_name', true ) ?: __('N/A', 'munchmakers-custom-request');
    $customer_email = get_post_meta( $request_id, '_customer_email', true ) ?: __('N/A', 'munchmakers-custom-request');
    $product_id = get_post_meta( $request_id, '_product_id', true );
    $product_name = $product_id ? get_the_title( $product_id ) : __('N/A', 'munchmakers-custom-request');
    $product_url = $product_id ? get_permalink( $product_id ) : '#';
    $request_admin_url = admin_url( 'post.php?post=' . $request_id . '&action=edit' );
    $submitted_time = get_the_date( get_option('date_format') . ' ' . get_option('time_format'), $request_id ); // Uses WordPress date/time settings
    $request_details_preview = wp_trim_words( get_post_field('post_content', $request_id), 25, '...' ); // Slightly longer preview

    $message_text = sprintf(
        __("A new custom design request has been submitted by *%s*.", 'munchmakers-custom-request'),
        esc_html($customer_name)
    );

    $payload = array(
        'blocks' => array(
            array(
                'type' => 'header',
                'text' => array(
                    'type' => 'plain_text',
                    'text' => '🎉 New MunchMakers Custom Request!', // Rebranded
                    'emoji' => true,
                ),
            ),
            array(
                'type' => 'section',
                'text' => array(
                    'type' => 'mrkdwn',
                    'text' => $message_text,
                ),
            ),
            array(
                'type' => 'section',
                'fields' => array(
                    array(
                        'type' => 'mrkdwn',
                        'text' => "*Request ID:*\n<{$request_admin_url}|#" . esc_html($request_id) . ">",
                    ),
                    array(
                        'type' => 'mrkdwn',
                        'text' => "*Customer Email:*\n" . esc_html($customer_email),
                    ),
                    array(
                        'type' => 'mrkdwn',
                        'text' => "*Product:*\n" . ($product_id ? "<" . esc_url($product_url) . "|" . esc_html($product_name) . ">" : esc_html($product_name)),
                    ),
                    array(
                        'type' => 'mrkdwn',
                        'text' => "*Submitted:*\n" . esc_html($submitted_time),
                    ),
                ),
            ),
            array(
                'type' => 'section',
                'text' => array(
                    'type' => 'mrkdwn',
                    'text' => "*Request Preview:*\n>_" . esc_html($request_details_preview) . "_",
                ),
            ),
            array(
                'type' => 'actions',
                'elements' => array(
                    array(
                        'type' => 'button',
                        'text' => array(
                            'type' => 'plain_text',
                            'text' => 'View Full Request Details',
                            'emoji' => true,
                        ),
                        'style' => 'primary',
                        'url' => $request_admin_url,
                        'action_id' => 'view_request_button_' . $request_id, // Unique action_id
                    ),
                ),
            ),
             array(
                'type' => 'divider'
            ),
            array(
                'type' => 'context',
                'elements' => array(
                    array(
                        'type' => 'mrkdwn',
                        'text' => 'MunchMakers Request System Notification' // Rebranded
                    )
                )
            )
        ),
    );
    mcr_send_slack_notification( $payload );
}

/**
 * Prepares and sends a Slack notification when a design proof is sent to a customer.
 *
 * @since 1.0.0
 * @param int $request_id The ID of the 'mcr_request' CPT post.
 */
function mcr_notify_slack_proof_sent( $request_id ) {
    $customer_name = get_post_meta( $request_id, '_customer_name', true ) ?: __('N/A', 'munchmakers-custom-request');
    $request_admin_url = admin_url( 'post.php?post=' . $request_id . '&action=edit' );

    $message_text = sprintf(
        __("Design proof for request <{$request_admin_url}|#%s> has been sent to *%s*.", 'munchmakers-custom-request'),
        esc_html($request_id),
        esc_html($customer_name)
    );

    $payload = array(
        'blocks' => array(
            array(
                'type' => 'header',
                'text' => array(
                    'type' => 'plain_text',
                    'text' => '🎨 MunchMakers Design Proof Sent!', // Rebranded
                    'emoji' => true,
                ),
            ),
            array(
                'type' => 'section',
                'text' => array(
                    'type' => 'mrkdwn',
                    'text' => $message_text,
                ),
            ),
            array(
                'type' => 'section',
                'fields' => array(
                    array(
                        'type' => 'mrkdwn',
                        'text' => "*Status Updated To:*\n" . mcr_get_status_label('awaiting_customer'), // Use helper
                    ),
                ),
            ),
            array(
                'type' => 'actions',
                'elements' => array(
                    array(
                        'type' => 'button',
                        'text' => array(
                            'type' => 'plain_text',
                            'text' => 'View Request',
                            'emoji' => true,
                        ),
                        'url' => $request_admin_url,
                        'action_id' => 'view_request_proof_sent_button_' . $request_id, // Unique action_id
                    ),
                ),
            ),
        ),
    );
    mcr_send_slack_notification( $payload );
}

/**
 * Prepares and sends a Slack notification when a request status is updated by an admin.
 *
 * @since 1.0.0
 * @param int    $request_id The ID of the 'mcr_request' CPT post.
 * @param string $new_status_key The new status key (e.g., 'design_in_progress').
 * @param string $old_status_key The old status key (optional).
 */
function mcr_notify_slack_status_update( $request_id, $new_status_key, $old_status_key = '' ) {
    if ($new_status_key === $old_status_key) {
        return; // Avoid notification if status hasn't actually changed
    }
    // Avoid double notification if proof_sent also updates status to awaiting_customer
    if ($new_status_key === 'awaiting_customer' && $old_status_key !== 'awaiting_customer' && $old_status_key !== '') {
        // This specific transition is usually handled by mcr_notify_slack_proof_sent if a proof was just sent.
        // However, if an admin *manually* changes to 'awaiting_customer' without using the "Send Proof" button, this will still fire.
        // This is generally okay, as it ensures all manual status changes are logged to Slack.
    }

    $request_admin_url = admin_url( 'post.php?post=' . $request_id . '&action=edit' );
    $status_label = mcr_get_status_label($new_status_key); // Use helper for translatable label

    $message_text = sprintf(
        __("Status for request <{$request_admin_url}|#%s> has been updated to: *%s*.", 'munchmakers-custom-request'),
        esc_html($request_id),
        esc_html($status_label)
    );

    $payload = array(
        'blocks' => array(
            array(
                'type' => 'header',
                'text' => array(
                    'type' => 'plain_text',
                    'text' => '🔔 MunchMakers Request Status Update', // Rebranded
                    'emoji' => true,
                ),
            ),
            array(
                'type' => 'section',
                'text' => array(
                    'type' => 'mrkdwn',
                    'text' => $message_text,
                ),
            ),
            array(
                'type' => 'actions',
                'elements' => array(
                    array(
                        'type' => 'button',
                        'text' => array(
                            'type' => 'plain_text',
                            'text' => 'View Request',
                            'emoji' => true,
                        ),
                        'url' => $request_admin_url,
                        'action_id' => 'view_request_status_update_button_' . $request_id, // Unique action_id
                    ),
                ),
            ),
        ),
    );
    mcr_send_slack_notification( $payload );
}
