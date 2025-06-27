<?php
/**
 * Modal Template
 *
 * @package MunchMakers_Product_Customizer
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div id="munchmakers-modal" style="display: none;">
    <div class="modal-backdrop"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h2><?php esc_html_e( 'Customize Your Order', 'munchmakers-product-customizer' ); ?></h2>
            <button type="button" class="modal-close">&times;</button>
        </div>
        
        <?php include MUNCHMAKERS_PLUGIN_DIR . 'templates/progress-bar.php'; ?>
        
        <div class="modal-body">
            <?php if ( $is_variable_product ) : ?>
                <?php include MUNCHMAKERS_PLUGIN_DIR . 'templates/variations-step.php'; ?>
            <?php endif; ?>
            
            <?php include MUNCHMAKERS_PLUGIN_DIR . 'templates/quantity-step.php'; ?>
            <?php include MUNCHMAKERS_PLUGIN_DIR . 'templates/artwork-step.php'; ?>
        </div>
    </div>
</div>