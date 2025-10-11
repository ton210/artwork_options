<?php
/**
 * Provides the HTML for the INLINE request form for MunchMakers Custom Requests.
 * This template is included by the render_inline_request_form() method in the public class,
 * typically when the [mcr_request_tracker] shortcode is used on the "Track Your Order" page
 * and no specific tracking key is provided in the URL.
 *
 * @package    MunchMakers_Custom_Request
 * @subpackage MunchMakers_Custom_Request/public/partials
 * @since      1.2.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

global $post; // Get global $post object to fetch current product ID if on a product page
$product_id = 0;
// Try to get product ID if this form is somehow embedded on a product page context
// For the "Track Your Order" page, product_id will likely be 0 unless explicitly passed or determined.
// This form is more generic, so product_id might not always be relevant here.
if ( is_object($post) && isset($post->ID) && function_exists('is_product') && (is_product($post->ID) || get_post_type($post->ID) === 'product') ) {
    $product_id = $post->ID;
} else {
    // If not on a product page, check if a product_id was passed via shortcode attribute or other means (not implemented here)
    // For now, it defaults to 0 if not on a product page.
}


// Instructional text - REVISED
$instructional_text = __("Let our experts handle the design – it's FREE! Simply tell us your ideas, and we'll send a custom mockup (typically in 24 hrs). You can track everything online. The more details you can share with us about your project, the better!", 'munchmakers-custom-request');

?>
<div class="mcr-inline-form-content">
    <h2 id="mcr-inline-form-title" class="mcr-inline-form-title"><?php esc_html_e( 'Start a New Custom Design Request', 'munchmakers-custom-request' ); ?></h2>
    
    <div class="mcr-popup-instructions mcr-inline-form-instructions">
        <p><?php echo esc_html($instructional_text); ?></p>
    </div>
    
    <div id="mcr-form-messages-inline" class="mcr-form-messages" style="display:none;" role="alert"></div>

    <form id="mcr-custom-request-form-inline" class="mcr-custom-request-form" enctype="multipart/form-data"> <?php // Use a distinct ID if JS needs to differentiate, or keep same if JS is generic ?>
        <?php
        // Nonce field for security.
        wp_nonce_field( 'mcr_handle_custom_request_nonce', 'mcr_form_nonce_field_inline' ); // Unique nonce field name for this form instance
        ?>
        <input type="hidden" name="action" value="mcr_handle_custom_request">
        <input type="hidden" name="mcr_product_id" value="<?php echo esc_attr($product_id); ?>">
        <?php // Note: If this form is NOT on a product page, mcr_product_id will be 0. This is usually fine. ?>

        <div class="mcr-form-field">
            <label for="mcr_name_inline"><?php esc_html_e( 'Your Name', 'munchmakers-custom-request' ); ?></label>
            <input type="text" id="mcr_name_inline" name="mcr_name" autocomplete="name">
        </div>

        <div class="mcr-form-field">
            <label for="mcr_phone_inline"><?php esc_html_e( 'Phone (Optional)', 'munchmakers-custom-request' ); ?></label>
            <input type="tel" id="mcr_phone_inline" name="mcr_phone" autocomplete="tel">
        </div>

        <div class="mcr-form-field">
            <label for="mcr_email_inline"><?php esc_html_e( 'Email (Required)', 'munchmakers-custom-request' ); ?> <span class="mcr-required" aria-hidden="true">*</span></label>
            <input type="email" id="mcr_email_inline" name="mcr_email" required aria-required="true" autocomplete="email">
        </div>

        <div class="mcr-form-field">
            <label for="mcr_request_details_inline"><?php esc_html_e( 'Describe Your Custom Design Request (Required)', 'munchmakers-custom-request' ); ?> <span class="mcr-required" aria-hidden="true">*</span></label>
            <textarea id="mcr_request_details_inline" name="mcr_request_details" rows="5" required aria-required="true" placeholder="<?php esc_attr_e('e.g., Product requests, branding guidelines, specific elements, desired style...', 'munchmakers-custom-request'); ?>"></textarea>
        </div>

        <div class="mcr-form-field">
            <label for="mcr_image_upload_inline"><?php esc_html_e( 'Upload Your Image/Logo (Optional)', 'munchmakers-custom-request' ); ?></label>
            <input type="file" id="mcr_image_upload_inline" name="mcr_image_upload" accept="image/jpeg,image/png,image/gif,image/svg+xml,.pdf,.ai,.eps">
             <small><?php esc_html_e( 'Accepted: JPG, PNG, GIF, SVG, PDF, AI, EPS. Max 20MB.', 'munchmakers-custom-request' ); ?></small>
        </div>

        <div class="mcr-form-field mcr-form-submit">
            <button type="submit" id="mcr-submit-request-btn-inline" class="mcr-submit-button">
                <?php esc_html_e( 'Send My Request', 'munchmakers-custom-request' ); ?>
                <span class="mcr-spinner" style="display:none;" aria-hidden="true"></span>
            </button>
        </div>
    </form>
</div>
