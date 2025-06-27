<?php
/**
 * Upload Tab Template
 *
 * @package MunchMakers_Product_Customizer
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="tab-panel active" id="tab-upload">
    <div id="munch-drop-zone" class="munch-drop-zone">
        <div class="munch-drop-zone-text">
            <p><strong><?php esc_html_e( 'Drag & Drop Files Here', 'munchmakers-product-customizer' ); ?></strong></p>
            <p><?php esc_html_e( 'or', 'munchmakers-product-customizer' ); ?></p>
            <label class="file-upload" for="artwork-file-input">
                <?php esc_html_e( 'Choose Files', 'munchmakers-product-customizer' ); ?>
            </label>
            <input type="file" id="artwork-file-input" name="munchmakers_artwork_files[]" accept="image/*" multiple style="display: none;">
        </div>
    </div>
    <div id="file-preview-list" class="file-preview-list"></div>
</div>