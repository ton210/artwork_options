<?php
/**
 * MunchMakers Custom Request Email Functions
 *
 * This file contains functions dedicated to preparing and sending
 * various email notifications to customers.
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
 * Sends the initial request confirmation email to the customer.
 * This is sent when a request is first submitted, regardless of whether a new user account was created.
 *
 * @since 1.0.0
 * @param int    $request_id     The ID of the 'mcr_request' CPT post.
 * @param string $customer_email The customer's email address.
 * @param string $customer_name  The customer's name.
 * @param string $product_name   The name of the product associated with the request.
 * @param string $access_key     The unique access key for the tracking page.
 */
function mcr_send_customer_confirmation_email( $request_id, $customer_email, $customer_name, $product_name, $access_key ) {
    $options = get_option('mcr_options'); // Rebranded option name
    $tracking_link = home_url('/track-your-order/?key=' . $access_key); // Ensure your tracking page slug is 'track-your-order'

    // Get subject and body from settings, with defaults
    $subject_template = isset($options['customer_confirmation_subject']) && !empty(trim($options['customer_confirmation_subject']))
                        ? $options['customer_confirmation_subject']
                        : "We've Received Your MunchMakers Request!";

    $default_body = "<h3>Thank You, {customer_name}!</h3>" .
                    "<p>We have received your custom design request for <strong>{product_name}</strong>.</p>" .
                    "<p>Our team will review your details and get back to you with a design proof or any questions, typically within 24 business hours.</p>" .
                    "<p>You can track the status of your request here: <a href='{tracking_link}' style='padding:10px 15px; background-color:#93BC48; color:#fff; text-decoration:none; border-radius:5px;'>Track Your Request</a></p>" . // Using MunchMakers Green
                    "<p>Thanks for choosing MunchMakers!</p>";
    $body_template = isset($options['customer_confirmation_body']) && !empty(trim($options['customer_confirmation_body']))
                     ? $options['customer_confirmation_body']
                     : $default_body;

    $placeholders = array(
        '{customer_name}' => esc_html( $customer_name ?: 'Valued Customer' ),
        '{product_name}'  => esc_html($product_name),
        '{tracking_link}' => esc_url($tracking_link),
        '{request_id}'    => esc_html($request_id),
    );

    $final_subject = str_replace(array_keys($placeholders), array_values($placeholders), $subject_template);
    $final_body    = str_replace(array_keys($placeholders), array_values($placeholders), $body_template);

    $headers = array('Content-Type: text/html; charset=UTF-8');
    if (wp_mail( $customer_email, $final_subject, $final_body, $headers )) {
        mcr_log("Customer confirmation email sent for request #{$request_id} to {$customer_email}.", 'INFO');
    } else {
        mcr_log("FAILED to send customer confirmation email for request #{$request_id} to {$customer_email}.", 'ERROR');
    }
}

/**
 * Sends the new user account creation email, including password set link and tracking info.
 *
 * @since 1.0.0
 * @param int    $user_id        The ID of the newly created WordPress user.
 * @param string $customer_email The customer's email address.
 * @param string $customer_name  The customer's name.
 * @param int    $request_id     The ID of the 'mcr_request' CPT post associated with this new account.
 * @param string $access_key     The unique access key for the tracking page for this request.
 */
function mcr_send_new_user_account_email( $user_id, $customer_email, $customer_name, $request_id, $access_key ) {
    $options = get_option('mcr_options');
    $user_info = get_userdata($user_id);
    if (!$user_info) {
        mcr_log("New User Email: User #{$user_id} not found. Email not sent.", 'ERROR');
        return;
    }
    $username = $user_info->user_login;

    // Generate a password reset key and link
    $reset_key = get_password_reset_key( $user_info );
    if (is_wp_error($reset_key)) {
        mcr_log("Password reset key generation FAILED for new user #{$user_id} ({$customer_email}): " . $reset_key->get_error_message(), 'ERROR');
        // Fallback: direct user to standard lost password page if key generation fails
        $password_set_url = class_exists('WooCommerce') ? wc_lostpassword_url() : wp_lostpassword_url();
    } else {
        $password_set_url = network_site_url( "wp-login.php?action=rp&key=$reset_key&login=" . rawurlencode( $username ), 'login' );
    }

    $tracking_link = home_url('/track-your-order/?key=' . $access_key);
    $my_account_url = class_exists('WooCommerce') ? wc_get_page_permalink('myaccount') : home_url('/wp-admin/profile.php');
    $product_id = get_post_meta($request_id, '_product_id', true);
    $product_name = $product_id ? get_the_title($product_id) : 'your custom item';

    $subject_template = isset($options['new_user_email_subject']) && !empty(trim($options['new_user_email_subject']))
                        ? $options['new_user_email_subject']
                        : 'Your MunchMakers Account is Ready!';

    $default_body = "<h3>Welcome to MunchMakers, {customer_name}!</h3>" .
                    "<p>An account has been created for you on MunchMakers.com so you can easily manage and track your custom design requests.</p>" .
                    "<p><strong>Username:</strong> {username}</p>" .
                    "<p>Please set your password by clicking the link below:<br>" .
                    "<a href='{password_set_link}' style='padding:10px 15px; background-color:#93BC48; color:#fff; text-decoration:none; border-radius:5px;'>Set Your Password</a></p>" . // MunchMakers Green
                    "<p>Once logged in, you can view all your requests in your account area: <a href='{my_account_link}'>My Account</a></p>" .
                    "<p>Your first request (# {request_id}) for <strong>{product_name}</strong> can also be tracked directly here: <a href='{tracking_link}'>Track This Request</a></p>" .
                    "<p>Welcome aboard!<br>The MunchMakers Team</p>";
    $body_template = isset($options['new_user_email_body']) && !empty(trim($options['new_user_email_body']))
                     ? $options['new_user_email_body']
                     : $default_body;

    $placeholders = array(
        '{customer_name}'     => esc_html( $customer_name ?: 'Valued Customer' ),
        '{username}'          => esc_html($username),
        '{password_set_link}' => esc_url($password_set_url),
        '{my_account_link}'   => esc_url($my_account_url),
        '{request_id}'        => esc_html($request_id),
        '{product_name}'      => esc_html($product_name),
        '{tracking_link}'     => esc_url($tracking_link),
    );
    $final_subject = str_replace(array_keys($placeholders), array_values($placeholders), $subject_template);
    $final_body    = str_replace(array_keys($placeholders), array_values($placeholders), $body_template);

    $headers = array('Content-Type: text/html; charset=UTF-8');
    if (wp_mail( $customer_email, $final_subject, $final_body, $headers )) {
        mcr_log("New user account email sent for user #{$user_id} ({$customer_email}) related to request #{$request_id}.", 'INFO');
    } else {
        mcr_log("FAILED to send new user account email for user #{$user_id} ({$customer_email}).", 'ERROR');
    }
}

/**
 * Sends an email notification to the customer when their request status changes.
 * This is triggered by an admin updating the status in the dashboard.
 *
 * @since 1.0.0
 * @param int    $request_id The ID of the 'mcr_request' CPT post.
 * @param string $new_status The new status key (e.g., 'design_in_progress').
 * @param string $old_status The old status key (optional, for context).
 */
function mcr_send_customer_status_update_email( $request_id, $new_status, $old_status = '' ) {
    // Avoid sending email if status hasn't actually changed,
    // unless it's for 'awaiting_customer' which might be triggered by proof sending (handled separately)
    // or if admin manually sets it.
    if ( $new_status === $old_status && $new_status !== 'awaiting_customer' ) {
        return;
    }

    $options = get_option('mcr_options');
    $customer_email = get_post_meta( $request_id, '_customer_email', true );
    $customer_name  = get_post_meta( $request_id, '_customer_name', true ) ?: 'Valued Customer';
    $product_id     = get_post_meta( $request_id, '_product_id', true );
    $product_name   = $product_id ? get_the_title( $product_id ) : 'your custom item';
    $access_key     = get_post_meta( $request_id, '_request_access_key', true );
    $tracking_link  = $access_key ? home_url('/track-your-order/?key=' . $access_key) : home_url('/track-your-order/'); // Ensure tracking page exists

    if ( !is_email($customer_email) ) {
        mcr_log("Status Update Email: Failed for request #{$request_id}. Invalid customer email: {$customer_email}.", 'ERROR');
        return;
    }

    $subject_template = '';
    $body_template = '';

    // Default email templates for various statuses
    $default_subjects = [
        'design_in_progress' => 'Your MunchMakers Design is in Progress!',
        'awaiting_customer'  => 'Action Required: Your MunchMakers Proof is Ready!', // For manual status change
        'revisions_requested'=> 'Update on Your MunchMakers Design Revisions',
        'in_production'      => 'Your MunchMakers Order is in Production!',
        'completed'          => 'Your MunchMakers Order is Complete!',
        'cancelled'          => 'Update on Your MunchMakers Request #{request_id}',
    ];
    $default_bodies = [
        'design_in_progress' => "<h3>Design Underway!</h3><p>Hi {customer_name},</p><p>Great news! Our designers have started working on your custom design for <strong>{product_name}</strong>.</p><p>We'll be in touch soon with a proof for your review. You can track progress here: <a href='{tracking_link}'>Track Your Request</a></p><p>The MunchMakers Team</p>",
        'awaiting_customer'  => "<h3>Your Proof is Ready!</h3><p>Hi {customer_name},</p><p>Your design proof for <strong>{product_name}</strong> is ready for your review. Please check your email for the proof attachment (if sent separately by our team) or find details on your tracking page: <a href='{tracking_link}'>Track Your Request</a></p><p>The MunchMakers Team</p>",
        'revisions_requested'=> "<h3>Revisions in Progress</h3><p>Hi {customer_name},</p><p>We've noted your request for revisions on the design for <strong>{product_name}</strong>. Our team will work on these and send an updated proof soon. Track progress: <a href='{tracking_link}'>Track Your Request</a></p><p>The MunchMakers Team</p>",
        'in_production'      => "<h3>Production Started!</h3><p>Hi {customer_name},</p><p>Your custom design for <strong>{product_name}</strong> has been approved and is now in production!</p><p>We'll notify you once it's shipped. Track progress: <a href='{tracking_link}'>Track Your Request</a></p><p>The MunchMakers Team</p>",
        'completed'          => "<h3>Your Order is Complete!</h3><p>Hi {customer_name},</p><p>Great news! Your custom order for <strong>{product_name}</strong> is complete and has been shipped (or is ready for pickup, if applicable).</p><p>Thank you for choosing MunchMakers! View final details: <a href='{tracking_link}'>Track Your Request</a></p><p>The MunchMakers Team</p>",
        'cancelled'          => "<h3>Request Cancelled</h3><p>Hi {customer_name},</p><p>This email is to confirm that your custom request (# {request_id}) for <strong>{product_name}</strong> has been cancelled. If you have any questions, please contact us.</p><p>The MunchMakers Team</p>",
    ];

    // Get templates from settings, or use defaults
    $subject_template = isset($options["status_{$new_status}_subject"]) && !empty(trim($options["status_{$new_status}_subject"]))
                        ? $options["status_{$new_status}_subject"]
                        : ($default_subjects[$new_status] ?? ''); // If no default, empty subject means no email

    $body_template = isset($options["status_{$new_status}_body"]) && !empty(trim($options["status_{$new_status}_body"]))
                     ? $options["status_{$new_status}_body"]
                     : ($default_bodies[$new_status] ?? '');

    // Only send if a template is defined for this status
    if (empty($subject_template) || empty($body_template)) {
        mcr_log("No email template defined for status '{$new_status}' for request #{$request_id}. Status update email not sent.", 'INFO');
        return;
    }

    $placeholders = array(
        '{customer_name}' => esc_html($customer_name),
        '{product_name}'  => esc_html($product_name),
        '{tracking_link}' => esc_url($tracking_link),
        '{request_id}'    => esc_html($request_id),
    );
    $final_subject = str_replace(array_keys($placeholders), array_values($placeholders), $subject_template);
    $final_body    = str_replace(array_keys($placeholders), array_values($placeholders), $body_template);

    $headers = array('Content-Type: text/html; charset=UTF-8');
    if ( wp_mail( $customer_email, $final_subject, $final_body, $headers ) ) {
        mcr_log("Status update email ('{$new_status}') sent to {$customer_email} for request #{$request_id}.", 'INFO');
    } else {
        mcr_log("FAILED to send status update email ('{$new_status}') to {$customer_email} for request #{$request_id}.", 'ERROR');
    }
}
