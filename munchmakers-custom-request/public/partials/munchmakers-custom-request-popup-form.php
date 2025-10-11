<?php
/**
 * Provides the HTML for the popup form for MunchMakers Custom Requests.
 * This template is included in the footer if the [munchmakers_button] shortcode is used.
 *
 * @package    MunchMakers_Custom_Request
 * @subpackage MunchMakers_Custom_Request/public/partials
 * @since      1.0.0
 * @updated    1.2.2 (Added missing mcr-custom-request-form class to form tag)
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

global $post; // Get global $post object to fetch current product ID if on a product page
$product_id = 0;
if ( is_object( $post ) && isset( $post->ID ) && function_exists('is_product') && ( is_product() || get_post_type( $post->ID ) === 'product' ) ) {
    $product_id = $post->ID;
}
// Fallback if $product global is available (might be set by WooCommerce hooks)
if ( ! $product_id && isset( $product ) && is_a( $product, 'WC_Product' ) ) {
    $product_id = $product->get_id();
}

?>
<div id="mcr-popup-overlay" class="mcr-popup-overlay" style="display: none;" role="dialog" aria-modal="true" aria-labelledby="mcr-popup-title" aria-hidden="true">
    <div id="mcr-popup-container" class="mcr-popup-container">
        <button id="mcr-popup-close-btn" class="mcr-popup-close-btn" aria-label="<?php esc_attr_e( 'Close popup', 'munchmakers-custom-request' ); ?>">&times;</button>
        <h2 id="mcr-popup-title" class="mcr-popup-title"><?php esc_html_e( 'Request a Custom Design', 'munchmakers-custom-request' ); ?></h2>

        <p class="mcr-popup-explanation">
            <?php
            /* translators: Explanation shown under "Request a Custom Design" */
            esc_html_e(
                'Unsure about your design? Let us do it for you. Send your request directly to our designers, and we will typically have it ready within a couple of hours. View your status on the tracking page, give feedback, and perfect your product!',
                'munchmakers-custom-request'
            );
            ?>
        </p>
        
        <div id="mcr-form-messages" class="mcr-form-messages" style="display:none;" role="alert"></div>

        <?php // Ensure the form has the class "mcr-custom-request-form" for JS to target it for AJAX submission ?>
        <form id="mcr-custom-request-form" class="mcr-custom-request-form" enctype="multipart/form-data">
            <?php
            // Nonce field for security. The AJAX handler checks for 'mcr_handle_custom_request_nonce' via $_POST['nonce'].
            wp_nonce_field( 'mcr_handle_custom_request_nonce', 'mcr_form_nonce_field' );
            ?>
            <input type="hidden" name="action" value="mcr_handle_custom_request">
            <input type="hidden" name="mcr_product_id" value="<?php echo esc_attr( $product_id ); ?>">

            <div class="mcr-form-field">
                <label for="mcr_name"><?php esc_html_e( 'Your Name', 'munchmakers-custom-request' ); ?></label>
                <input type="text" id="mcr_name" name="mcr_name" autocomplete="name">
            </div>

            <div class="mcr-form-field">
                <label for="mcr_phone"><?php esc_html_e( 'Phone (Optional)', 'munchmakers-custom-request' ); ?></label>
                <input type="tel" id="mcr_phone" name="mcr_phone" autocomplete="tel">
            </div>

            <div class="mcr-form-field">
                <label for="mcr_email"><?php esc_html_e( 'Email (Required)', 'munchmakers-custom-request' ); ?> <span class="mcr-required" aria-hidden="true">*</span></label>
                <input type="email" id="mcr_email" name="mcr_email" required aria-required="true" autocomplete="email">
            </div>

            <div class="mcr-form-field">
                <label for="mcr_request_details"><?php esc_html_e( 'Describe Your Custom Design Request (Required)', 'munchmakers-custom-request' ); ?> <span class="mcr-required" aria-hidden="true">*</span></label>
                <textarea id="mcr_request_details" name="mcr_request_details" rows="5" required aria-required="true" placeholder="<?php esc_attr_e( 'e.g., Product selections, color preferences, text to include, specific elements, desired style...', 'munchmakers-custom-request' ); ?>"></textarea>
            </div>

            <div class="mcr-form-field">
                <label for="mcr_image_upload"><?php esc_html_e( 'Upload Your Image/Logo (Optional)', 'munchmakers-custom-request' ); ?></label>
                <input type="file" id="mcr_image_upload" name="mcr_image_upload" accept="image/jpeg,image/png,image/gif,image/svg+xml,.pdf,.ai,.eps">
                <small><?php esc_html_e( 'Accepted: JPG, PNG, GIF, SVG, PDF, AI, EPS. Max 20MB.', 'munchmakers-custom-request' ); ?></small>
            </div>

            <div class="mcr-form-field mcr-form-submit">
                <button type="submit" id="mcr-submit-request-btn" class="mcr-submit-button">
                    <?php esc_html_e( 'Send My Request', 'munchmakers-custom-request' ); ?>
                    <span class="mcr-spinner" style="display:none;" aria-hidden="true"></span>
                </button>
            </div>
        </form>
    </div>
</div>