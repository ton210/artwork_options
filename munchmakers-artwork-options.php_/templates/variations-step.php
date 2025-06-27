<?php
/**
 * Variations Step Template
 *
 * @package MunchMakers_Product_Customizer
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Helper functions
function munchmakers_is_color_attribute( $attribute_name ) {
    return ( strtolower( wc_attribute_label( $attribute_name ) ) === 'color' || strpos( strtolower( $attribute_name ), 'color' ) !== false );
}

function munchmakers_get_variation_image_url( $option, $attribute_name, $available_variations ) {
    $variation_image_url = '';
    
    if ( ! empty( $available_variations ) ) {
        foreach ( $available_variations as $variation ) {
            if ( isset( $variation['attributes']['attribute_' . $attribute_name] ) && 
                 $variation['attributes']['attribute_' . $attribute_name] === $option &&
                 ! empty( $variation['image']['url'] ) ) {
                $variation_image_url = $variation['image']['url'];
                break;
            }
        }
    }
    
    return $variation_image_url;
}
?>

<div class="modal-step active" id="step-variations">
    <h3>
        <?php esc_html_e( 'Step 1: Select Your Options', 'munchmakers-product-customizer' ); ?>
    </h3>
    <div id="modal-variations-container">
        <?php foreach ( $attributes as $attribute_name => $options ) : ?>
            <div class="modal-variation-row">
                <label for="attribute_<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>">
                    <?php echo esc_html( wc_attribute_label( $attribute_name ) ); ?>:
                </label>
                
                <?php if ( munchmakers_is_color_attribute( $attribute_name ) ) : ?>
                    <div class="color-swatches" data-attribute="attribute_<?php echo esc_attr( $attribute_name ); ?>">
                        <?php foreach ( $options as $option ) : ?>
                            <?php
                            $term = get_term_by( 'slug', $option, $attribute_name );
                            $option_name = $term ? $term->name : ucwords( str_replace( '-', ' ', $option ) );
                            $variation_image_url = munchmakers_get_variation_image_url( $option, $attribute_name, $available_variations );
                            ?>
                            <div class="color-swatch" data-value="<?php echo esc_attr( $option ); ?>" title="<?php echo esc_attr( $option_name ); ?>">
                                <?php if ( $variation_image_url ) : ?>
                                    <img src="<?php echo esc_url( $variation_image_url ); ?>" alt="<?php echo esc_attr( $option_name ); ?>">
                                <?php else : ?>
                                    <div class="color-placeholder" style="background-color: <?php echo esc_attr( strtolower( $option ) ); ?>;">
                                        <span><?php echo esc_html( substr( $option_name, 0, 2 ) ); ?></span>
                                    </div>
                                <?php endif; ?>
                                <span class="swatch-label"><?php echo esc_html( $option_name ); ?></span>
                            </div>
                        <?php endforeach; ?>
                        <input type="hidden" id="attribute_<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>" name="attribute_<?php echo esc_attr( $attribute_name ); ?>" data-attribute_name="attribute_<?php echo esc_attr( $attribute_name ); ?>">
                    </div>
                <?php else : ?>
                    <select id="attribute_<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>" name="attribute_<?php echo esc_attr( $attribute_name ); ?>" data-attribute_name="attribute_<?php echo esc_attr( $attribute_name ); ?>">
                        <option value=""><?php esc_html_e( 'Choose an option...', 'munchmakers-product-customizer' ); ?></option>
                        <?php foreach ( $options as $option ) : ?>
                            <option value="<?php echo esc_attr( $option ); ?>">
                                <?php echo esc_html( $attribute_map[ $attribute_name ][ $option ] ?? ucwords( str_replace( '-', ' ', $option ) ) ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="current-selection-display" id="variation-step-selection"></div>
    <div class="step-footer step-footer-sticky">
        <button type="button" class="btn btn-primary" id="next-to-quantity" disabled>
            <?php esc_html_e( 'Next: Choose Quantity', 'munchmakers-product-customizer' ); ?>
        </button>
    </div>
</div>