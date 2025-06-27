<?php
/**
 * Designer Tab Template
 *
 * @package MunchMakers_Product_Customizer
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="tab-panel" id="tab-designer">
    <div class="designer-content-simple">
        <p class="designer-description">
            <?php esc_html_e( 'Use our free online design tool to create custom text, upload images, and personalize your product.', 'munchmakers-product-customizer' ); ?>
        </p>
        
        <div class="designer-action-center">
            <button type="button" class="btn-design-now" id="open-designer">
                <span class="btn-text">
                    <span class="design-icon">🎨</span>
                    <?php esc_html_e( 'Design Now', 'munchmakers-product-customizer' ); ?>
                </span>
                <span class="btn-spinner" style="display: none;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-dasharray="60" stroke-dashoffset="60">
                            <animate attributeName="stroke-dashoffset" values="60;0" dur="1s" repeatCount="indefinite"/>
                        </circle>
                    </svg>
                    <?php esc_html_e( 'Loading Designer...', 'munchmakers-product-customizer' ); ?>
                </span>
            </button>
            
            <p class="designer-note-simple">
                <?php esc_html_e( 'Opens in a new window - save your design to continue', 'munchmakers-product-customizer' ); ?>
            </p>
        </div>
    </div>
</div>