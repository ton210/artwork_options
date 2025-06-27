<?php
/**
 * Quantity Step Template
 *
 * @package MunchMakers_Product_Customizer
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="modal-step <?php echo ! $is_variable_product ? 'active' : ''; ?>" id="step-quantity">
    <h3>
        <?php printf( esc_html__( 'Step %s: Choose Quantity', 'munchmakers-product-customizer' ), $is_variable_product ? '2' : '1' ); ?> 
        <span class="pricing-tooltip-trigger" data-for-step="2" title="<?php esc_attr_e( 'Click to view wholesale pricing', 'munchmakers-product-customizer' ); ?>">
            <?php esc_html_e( 'Wholesale Pricing', 'munchmakers-product-customizer' ); ?> ⓘ
        </span>
    </h3>
    <div class="current-selection-display" id="quantity-step-selection"></div>
    <div class="quantity-controls">
        <p><?php esc_html_e( 'Slide to select the quantity you need:', 'munchmakers-product-customizer' ); ?></p>
        <div id="modal-slider" class="quantity-slider"></div>
        <div class="quantity-fields">
            <div class="field-group">
                <label for="modal-quantity"><?php esc_html_e( 'Quantity', 'munchmakers-product-customizer' ); ?></label>
                <input type="number" id="modal-quantity" value="<?php echo esc_attr( $initial_pricing['moq'] ); ?>">
            </div>
            <div class="field-group">
                <label><?php esc_html_e( 'Price Per Unit', 'munchmakers-product-customizer' ); ?></label>
                <input type="text" id="modal-unit-price" readonly>
            </div>
        </div>
        <div class="pricing-summary">
            <p class="subtotal">
                <?php esc_html_e( 'Subtotal:', 'munchmakers-product-customizer' ); ?> 
                <span id="modal-subtotal">$0.00</span>
            </p>
            <div class="savings" style="display:none;">
                <span class="savings-icon">💰</span> 
                <?php esc_html_e( 'You save', 'munchmakers-product-customizer' ); ?> 
                <strong id="modal-savings">$0.00</strong>!
            </div>
        </div>
    </div>
    <div class="step-footer step-footer-sticky">
        <?php if ( $is_variable_product ) : ?>
            <button type="button" class="btn btn-secondary" id="back-to-variations">
                <?php esc_html_e( '← Back to Options', 'munchmakers-product-customizer' ); ?>
            </button>
        <?php endif; ?>
        <button type="button" class="btn btn-primary" id="next-to-artwork">
            <?php esc_html_e( 'Next: Add Artwork', 'munchmakers-product-customizer' ); ?>
        </button>
    </div>
</div>