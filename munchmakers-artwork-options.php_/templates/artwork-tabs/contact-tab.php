<?php
/**
 * Contact Tab Template
 *
 * @package MunchMakers_Product_Customizer
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="tab-panel" id="tab-contact">
    <h4><?php esc_html_e( 'Need a Professional Touch?', 'munchmakers-product-customizer' ); ?></h4>
    <p>
        <?php 
        printf(
            esc_html__( 'No problem. Complete your order, and our design team will help you create the perfect artwork. You can email your ideas or questions to %s after checkout.', 'munchmakers-product-customizer' ),
            '<a href="mailto:help@munchmakers.com">help@munchmakers.com</a>'
        );
        ?>
    </p>
</div>