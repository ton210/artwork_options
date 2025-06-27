<?php
/**
 * Artwork Step Template
 *
 * @package MunchMakers_Product_Customizer
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="modal-step" id="step-artwork">
    <h3>
        <?php printf( esc_html__( 'Step %s: Add Your Artwork', 'munchmakers-product-customizer' ), $is_variable_product ? '3' : '2' ); ?>
    </h3>
    
    <div class="artwork-choice">
        <label>
            <input type="radio" name="modal_artwork_choice" value="add_now" checked> 
            <?php esc_html_e( 'Add Now', 'munchmakers-product-customizer' ); ?>
        </label>
        <label>
            <input type="radio" name="modal_artwork_choice" value="send_later"> 
            <?php esc_html_e( 'Send After Checkout', 'munchmakers-product-customizer' ); ?>
        </label>
    </div>
    
    <div class="artwork-options" id="artwork-now-options">
        <div class="artwork-tabs">
            <button type="button" class="tab-btn active" data-tab="upload">
                <span class="icon">🖼️</span> <?php esc_html_e( 'Upload Image', 'munchmakers-product-customizer' ); ?>
            </button>
            <button type="button" class="tab-btn" data-tab="designer">
                <span class="icon">🎨</span> <?php esc_html_e( 'Design Tool', 'munchmakers-product-customizer' ); ?>
            </button>
            <button type="button" class="tab-btn" data-tab="contact">
                <span class="icon">💬</span> <?php esc_html_e( 'Contact Us', 'munchmakers-product-customizer' ); ?>
            </button>
        </div>
        
        <div class="tab-content">
            <?php include MUNCHMAKERS_PLUGIN_DIR . 'templates/artwork-tabs/upload-tab.php'; ?>
            <?php include MUNCHMAKERS_PLUGIN_DIR . 'templates/artwork-tabs/designer-tab.php'; ?>
            <?php include MUNCHMAKERS_PLUGIN_DIR . 'templates/artwork-tabs/contact-tab.php'; ?>
        </div>
    </div>
    
    <div class="artwork-options" id="artwork-later-options" style="display:none;">
        <div class="send-later-info">
            <h4><?php esc_html_e( 'Complete Order & Send Artwork Later', 'munchmakers-product-customizer' ); ?></h4>
            <p>
                <?php 
                printf( 
                    esc_html__( 'You can finalize your purchase now and email your design files to %s whenever you\'re ready.', 'munchmakers-product-customizer' ),
                    '<a href="mailto:help@munchmakers.com">help@munchmakers.com</a>'
                );
                ?>
            </p>
        </div>
    </div>
    
    <p class="proof-notice">
        <?php esc_html_e( 'A free digital art proof will be emailed for your approval before production begins.', 'munchmakers-product-customizer' ); ?>
    </p>
    
    <!-- Footer for artwork step -->
    <div class="step-footer">
        <button type="button" class="btn btn-secondary" id="back-to-quantity">
            <?php esc_html_e( '← Back to Quantity', 'munchmakers-product-customizer' ); ?>
        </button>
        <button type="button" class="btn btn-success" id="add-to-cart">
            <?php esc_html_e( 'Add to Cart', 'munchmakers-product-customizer' ); ?>
        </button>
    </div>
</div>