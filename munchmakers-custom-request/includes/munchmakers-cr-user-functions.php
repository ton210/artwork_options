<?php
/**
 * MunchMakers Custom Request User Account Functions
 *
 * This file contains functions related to WordPress user account
 * creation, checking, and linking for custom requests.
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
 * Checks if a user exists by email. If not, creates a new WordPress user
 * and associates them with the custom request.
 *
 * Calls mcr_send_new_user_account_email if a new user is created and configured to send.
 *
 * @since 1.0.0
 * @updated 1.2.4 (Clarified new user email sending responsibility)
 * @param string $email         The customer's email address.
 * @param string $name          The customer's full name (optional, used for display name/first/last).
 * @param int    $request_id    The ID of the 'mcr_request' CPT post to link.
 * @return array An array containing 'user_id' (int|WP_Error) and 'new_user_created' (bool).
 */
function mcr_maybe_create_customer_account( $email, $name, $request_id ) {
    $result = array(
        'user_id' => 0,
        'new_user_created' => false
    );

    if ( !is_email( $email ) ) {
        mcr_log("User Account: Attempt to create/link account for request #{$request_id} failed. Invalid email provided: '{$email}'.", 'WARNING', __FUNCTION__);
        $result['user_id'] = new WP_Error('invalid_email', __('Invalid email address provided for user account.', 'munchmakers-custom-request'));
        return $result;
    }

    $existing_user_id = email_exists( $email );

    if ( $existing_user_id ) {
        mcr_log("User Account: Existing user #{$existing_user_id} found for email {$email}. Linking request #{$request_id}.", 'INFO', __FUNCTION__);
        wp_update_post( array( 'ID' => $request_id, 'post_author' => $existing_user_id ) );
        update_user_meta( $existing_user_id, '_mcr_last_request_id', $request_id );
        $result['user_id'] = $existing_user_id;
        return $result;
    } else {
        mcr_log("User Account: No existing user for {$email}. Attempting to create new account for request #{$request_id}.", 'INFO', __FUNCTION__);

        $username_base = sanitize_user( strtok( $email, '@' ), true );
        $username_base = preg_replace( '/[^a-z0-9_.\-]/i', '', $username_base );
        if (empty($username_base)) {
            $username_base = 'user';
        }
        $username = $username_base;
        $i = 1;
        while ( username_exists( $username ) ) {
            $username = $username_base . $i;
            $i++;
        }

        $random_password = wp_generate_password( 20, true, true );
        $display_name = !empty($name) ? $name : $username;
        $first_name = '';
        $last_name = '';
        if (!empty($name)) {
            $name_parts = explode(' ', $name, 2);
            $first_name = $name_parts[0];
            $last_name = isset($name_parts[1]) ? trim($name_parts[1]) : '';
        }

        $user_data = array(
            'user_login' => $username,
            'user_email' => $email,
            'user_pass'  => $random_password,
            'display_name' => $display_name,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'role'       => class_exists('WooCommerce') ? 'customer' : 'subscriber',
        );

        $user_id = 0;
        // Configuration: Should this plugin send its own custom "New User Account" email?
        // True by default, can be made a plugin setting.
        $send_mcr_custom_new_user_email = apply_filters('mcr_send_custom_new_user_email', true);

        if ( class_exists('WooCommerce') && function_exists('wc_create_new_customer') ) {
            try {
                if ($send_mcr_custom_new_user_email) {
                    add_filter('woocommerce_email_enabled_customer_new_account', '__return_false', 9999);
                }
                $user_id = wc_create_new_customer( $email, $username, $random_password, array(
                    'first_name' => $first_name,
                    'last_name'  => $last_name,
                ) );
                if ($send_mcr_custom_new_user_email) {
                    remove_filter('woocommerce_email_enabled_customer_new_account', '__return_false', 9999);
                }

                 if (is_wp_error($user_id)) {
                    mcr_log("User Account: WooCommerce account creation FAILED for {$email} (request #{$request_id}): " . $user_id->get_error_message(), 'ERROR', __FUNCTION__);
                    $result['user_id'] = $user_id;
                    return $result;
                }
            } catch (Exception $e) { // Catch generic Exception if WC_Data_Exception is not specific enough or for other issues
                 mcr_log("User Account: WooCommerce account creation EXCEPTION for {$email} (request #{$request_id}): " . $e->getMessage(), 'ERROR', __FUNCTION__);
                 $result['user_id'] = new WP_Error('wc_create_customer_exception', $e->getMessage());
                 return $result;
            }
        } else {
            if ($send_mcr_custom_new_user_email) {
                add_filter( 'send_new_user_notifications', '__return_false', 9999); // Suppress WP default new user email
                $user_id = wp_insert_user( $user_data );
                remove_filter( 'send_new_user_notifications', '__return_false', 9999);
            } else {
                // If not sending our custom email, let WP send its default to the user (includes password reset).
                $user_id = wp_insert_user( $user_data );
                if ( !is_wp_error($user_id) ) {
                     wp_new_user_notification( $user_id, null, 'user' );
                }
            }
            
            if ( is_wp_error( $user_id ) ) {
                mcr_log("User Account: WordPress account creation FAILED for {$email} (request #{$request_id}): " . $user_id->get_error_message(), 'ERROR', __FUNCTION__);
                $result['user_id'] = $user_id;
                return $result;
            }
        }

        mcr_log("User Account: New user #{$user_id} created for email {$email}. Linking request #{$request_id}.", 'INFO', __FUNCTION__);
        wp_update_post( array( 'ID' => $request_id, 'post_author' => $user_id ) );
        update_user_meta( $user_id, '_mcr_last_request_id', $request_id );
        $result['user_id'] = $user_id;
        $result['new_user_created'] = true;

        // If a new user was created AND we are configured to send the custom new user email
        if ( $send_mcr_custom_new_user_email && function_exists('mcr_send_new_user_account_email') && !is_wp_error($user_id) && $user_id > 0 ) {
            $access_key = get_post_meta( $request_id, '_request_access_key', true );
            if (!$access_key) { // Should not happen if generated before CPT creation, but as a fallback.
                 $access_key = wp_generate_uuid4(); // Generate it now if somehow missed.
                 update_post_meta( $request_id, '_request_access_key', $access_key );
                 mcr_log("User Account: Access key was missing for request #{$request_id}, generated one for new user email.", 'WARNING', __FUNCTION__);
            }
            mcr_send_new_user_account_email( $user_id, $email, $name, $request_id, $access_key );
        }
        return $result;
    }
}