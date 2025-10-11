<?php
/**
 * Template for displaying the customer-facing request status page for MunchMakers.
 * This template is loaded by the [mcr_request_tracker] shortcode when a valid key is present.
 *
 * It expects to be included within a WordPress loop where the global $post
 * object is set to the 'mcr_request' CPT post for the current request.
 *
 * @package    MunchMakers_Custom_Request
 * @subpackage MunchMakers_Custom_Request/public/partials
 * @since      1.2.0
 * @updated    1.2.1 (Timezone and date format update)
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Get all the necessary data for the current request post
$post_id = get_the_ID(); // This is the ID of the mcr_request CPT post
$customer_name = get_post_meta( $post_id, '_customer_name', true );
$customer_email = get_post_meta( $post_id, '_customer_email', true );
$request_content = get_post_field('post_content', $post_id); // Request details are stored in post_content
$customer_image_url = get_post_meta( $post_id, '_customer_image_url', true );
$current_status_key = get_post_meta( $post_id, '_request_status', true ) ?: 'new'; // Default to 'new' if not set
$product_id = get_post_meta( $post_id, '_product_id', true );
$product_name = $product_id ? get_the_title($product_id) : __('the requested item', 'munchmakers-custom-request');


// Timezone conversion for submission date
$submitted_date_utc_str = get_the_date( 'Y-m-d H:i:s', $post_id ); // Get CPT post date in UTC (WordPress stores post_date as GMT/UTC)
$submitted_date_display_str = __('Date not available', 'munchmakers-custom-request');

if ($submitted_date_utc_str) {
    try {
        // Create DateTime object from UTC string
        $utc_datetime = new DateTime( $submitted_date_utc_str, new DateTimeZone('UTC') );
        
        // Get WordPress site's configured timezone string
        $site_timezone_string = wp_timezone_string();
        $site_timezone = new DateTimeZone($site_timezone_string);
        
        // Convert the DateTime object to the site's timezone
        $utc_datetime->setTimezone( $site_timezone );
        
        // Get WordPress date and time format settings for display
        $date_format_option = get_option('date_format', 'F j, Y');
        $time_format_option = get_option('time_format', 'g:i A');
        
        // Format the date and time using WordPress settings and include the timezone abbreviation
        $submitted_date_display_str = $utc_datetime->format($date_format_option . ' ' . $time_format_option) . ' ' . $utc_datetime->format('T');
    
    } catch (Exception $e) {
        // Fallback if date conversion fails for any reason
        if (function_exists('mcr_log')) {
            mcr_log("Error converting date for request #{$post_id} to site timezone: " . $e->getMessage(), 'WARNING', 'request-status-page.php');
        }
        // Fallback to WordPress's get_the_date which should respect site timezone if possible, or show raw if conversion failed badly.
        $submitted_date_display_str = get_the_date( get_option('date_format') . ' ' . get_option('time_format'), $post_id ) . ' (Site Time)';
    }
}


// Define tracker steps and their corresponding icons/labels for MunchMakers
$tracker_statuses = mcr_get_request_statuses(); // Get all statuses from core functions
$ordered_tracker_steps = array( // Define the visual order and icons for the tracker
    'new'                   => array('label' => $tracker_statuses['new']                   ?? __('New', 'munchmakers-custom-request'),                   'icon' => '📝'),
    'design_in_progress'    => array('label' => $tracker_statuses['design_in_progress']    ?? __('Design in Progress', 'munchmakers-custom-request'),    'icon' => '🎨'),
    'awaiting_customer'     => array('label' => $tracker_statuses['awaiting_customer']     ?? __('Proof Ready', 'munchmakers-custom-request'),     'icon' => '🖼️'), // Adjusted label for clarity
    'revisions_requested'   => array('label' => $tracker_statuses['revisions_requested']   ?? __('Revisions Requested', 'munchmakers-custom-request'),   'icon' => '🔄'),
    'in_production'         => array('label' => $tracker_statuses['in_production']         ?? __('In Production', 'munchmakers-custom-request'),         'icon' => '🏭'),
    'completed'             => array('label' => $tracker_statuses['completed']             ?? __('Completed', 'munchmakers-custom-request'),             'icon' => '🎉'),
);
// Handle 'cancelled' state separately or as a final step if it's part of the linear flow
if ($current_status_key === 'cancelled' && isset($tracker_statuses['cancelled'])) {
    // Add cancelled to the end of the steps if it's the current status and not already a main step
    if(!isset($ordered_tracker_steps['cancelled'])){
        $ordered_tracker_steps['cancelled'] = array('label' => $tracker_statuses['cancelled'], 'icon' => '❌');
    }
}


$status_keys_ordered = array_keys($ordered_tracker_steps);
$current_step_index = array_search($current_status_key, $status_keys_ordered);

if ($current_step_index === false) {
    // If current status is 'cancelled' and it's the last defined step (possibly added dynamically), use that index
    if ($current_status_key === 'cancelled' && end($status_keys_ordered) === 'cancelled') {
        $current_step_index = count($ordered_tracker_steps) - 1;
    } else {
        $current_step_index = 0; // Default to the first step if status is unknown or not in ordered list
        if (function_exists('mcr_log')) {
            mcr_log("Request Tracker: Status '{$current_status_key}' for request #{$post_id} not found in ordered visual steps. Defaulting to first step.", 'WARNING', 'request-status-page.php');
        }
    }
}

$has_image = !empty($customer_image_url);
$grid_columns_attr = $has_image ? 'data-columns="2"' : 'data-columns="1"';

?>
<div class="mcr-tracker-wrap">
    <header class="mcr-tracker-header">
        <h1><?php esc_html_e( 'Your Custom Design Status', 'munchmakers-custom-request' ); ?></h1>
        <p class="mcr-tracker-intro">
            <?php if ( !empty( $customer_name ) ): ?>
                <?php printf( esc_html__( 'Thank you, %s! Here is the live status of your MunchMakers design request.', 'munchmakers-custom-request' ), esc_html( $customer_name ) ); ?>
            <?php else: ?>
                <?php esc_html_e( 'Thank you! Here is the live status of your MunchMakers design request.', 'munchmakers-custom-request' ); ?>
            <?php endif; ?>
        </p>
    </header>

    <div class="mcr-tracker-container">
        <div class="mcr-tracker-bar">
            <?php
            $step_counter = 0;
            foreach ( $ordered_tracker_steps as $key => $step_data ):
                $class = '';
                if ( $current_status_key === 'cancelled' ) {
                    if ($key === 'cancelled') {
                        $class = 'active cancelled'; // Highlight the cancelled step
                    } else if ($current_step_index !== false && $step_counter < $current_step_index) {
                        // If cancelled, but this step was passed before cancellation, mark as completed
                        $class = 'completed';
                    } else {
                        $class = 'disabled'; 
                    }
                } elseif ( $current_step_index !== false && $step_counter < $current_step_index ) {
                    $class = 'completed';
                } elseif ( $current_step_index !== false && $step_counter == $current_step_index ) {
                    $class = 'active';
                } else {
                    $class = 'pending'; // For steps not yet reached
                }
            ?>
                <div class="mcr-tracker-step <?php echo esc_attr( $class ); ?>">
                    <div class="mcr-tracker-icon" aria-hidden="true"><?php echo esc_html( $step_data['icon'] ); ?></div>
                    <div class="mcr-tracker-label"><?php echo esc_html( $step_data['label'] ); ?></div>
                </div>
            <?php
            $step_counter++;
            endforeach;
            ?>
        </div>
    </div>

    <div class="mcr-tracker-details-grid" <?php echo $grid_columns_attr; ?>>
        <div class="mcr-tracker-details-card">
            <h2><?php esc_html_e( 'Request Summary', 'munchmakers-custom-request' ); ?></h2>
            <p><strong><?php esc_html_e( 'Request ID:', 'munchmakers-custom-request' ); ?></strong> #<?php echo esc_html( $post_id ); ?></p>
            <p><strong><?php esc_html_e( 'Submitted On:', 'munchmakers-custom-request' ); ?></strong> <?php echo esc_html( $submitted_date_display_str ); ?></p>
            <?php if ( !empty( $customer_name ) ): ?>
                <p><strong><?php esc_html_e( 'Your Name:', 'munchmakers-custom-request' ); ?></strong> <?php echo esc_html( $customer_name ); ?></p>
            <?php endif; ?>
            <p><strong><?php esc_html_e( 'Your Email:', 'munchmakers-custom-request' ); ?></strong> <?php echo esc_html( $customer_email ); ?></p>
            <?php if ( $product_id && $product_name !== __('the requested item', 'munchmakers-custom-request') ): ?>
                 <p><strong><?php esc_html_e( 'Regarding Product:', 'munchmakers-custom-request' ); ?></strong> <?php echo esc_html( $product_name ); ?></p>
            <?php endif; ?>
            <hr>
            <h3><?php esc_html_e( 'Your Request Details:', 'munchmakers-custom-request' ); ?></h3>
            <div class="mcr-request-content">
                <?php echo wp_kses_post( wpautop( $request_content ) ); // wpautop for paragraph formatting, wp_kses_post for safe HTML ?>
            </div>
        </div>

        <?php if ( $customer_image_url ): ?>
        <div class="mcr-tracker-details-card">
            <h2><?php esc_html_e( 'Your Uploaded Image/Logo', 'munchmakers-custom-request' ); ?></h2>
            <a href="<?php echo esc_url($customer_image_url); ?>" target="_blank" rel="noopener noreferrer">
                <img src="<?php echo esc_url($customer_image_url); ?>" alt="<?php esc_attr_e( 'Customer Uploaded Image', 'munchmakers-custom-request' ); ?>" class="mcr-tracker-image-preview">
            </a>
        </div>
        <?php endif; ?>
    </div>

    <footer class="mcr-tracker-footer">
        <p><?php esc_html_e( 'If you have any questions about your request, please reply to the confirmation email we sent you or contact our support team.', 'munchmakers-custom-request' ); ?></p>
        <p>&copy; <?php echo date('Y'); ?> <?php echo esc_html(get_bloginfo('name')); // Use site name dynamically ?></p>
    </footer>
</div>