<?php
/**
 * Progress Bar Template
 *
 * @package MunchMakers_Product_Customizer
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="progress-container">
    <div class="progress-bar">
        <div class="progress-step active" data-step="1">
            <div class="step-circle">
                <span class="step-number">1</span>
                <svg class="step-check" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <span class="step-label">
                <?php echo $is_variable_product ? esc_html__( 'Select Options', 'munchmakers-product-customizer' ) : esc_html__( 'Choose Quantity', 'munchmakers-product-customizer' ); ?>
            </span>
        </div>
        <div class="progress-line"></div>
        <div class="progress-step" data-step="2">
            <div class="step-circle">
                <span class="step-number">2</span>
                <svg class="step-check" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <span class="step-label">
                <?php echo $is_variable_product ? esc_html__( 'Choose Quantity', 'munchmakers-product-customizer' ) : esc_html__( 'Add Artwork', 'munchmakers-product-customizer' ); ?>
            </span>
        </div>
        <div class="progress-line"></div>
        <div class="progress-step" data-step="3">
            <div class="step-circle">
                <span class="step-number">3</span>
                <svg class="step-check" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <span class="step-label"><?php esc_html_e( 'Add Artwork', 'munchmakers-product-customizer' ); ?></span>
        </div>
    </div>
</div>