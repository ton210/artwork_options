<?php
/**
 * Admin Class
 *
 * @package MunchMakers_Product_Customizer
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MunchMakers_Admin {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'woocommerce_admin_order_item_meta_end', array( $this, 'display_custom_data_in_admin_order' ), 10, 3 );
    }

    public function add_admin_menu() {
        add_menu_page(
            __( 'MunchMakers Customizer', 'munchmakers-product-customizer' ),
            __( 'MunchMakers Options', 'munchmakers-product-customizer' ),
            'manage_options',
            'munchmakers-product-customizer',
            array( $this, 'render_settings_page' ),
            'dashicons-admin-customizer',
            58
        );
    }

    public function register_settings() {
        register_setting( 'munchmakers-artwork-options-group', 'munchmakers_artwork_options_active' );
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'MunchMakers Product Page Customizer', 'munchmakers-product-customizer' ); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'munchmakers-artwork-options-group' ); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e( 'Activate Customizer Module', 'munchmakers-product-customizer' ); ?></th>
                        <td>
                            <input type="checkbox" name="munchmakers_artwork_options_active" id="munchmakers_artwork_options_active" value="1" <?php checked( 1, get_option( 'munchmakers_artwork_options_active' ), true ); ?> />
                            <label for="munchmakers_artwork_options_active"><?php esc_html_e( 'Enable', 'munchmakers-product-customizer' ); ?></label>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
            <hr>
            <?php $this->render_pricing_instructions(); ?>
        </div>
        <?php
    }

    private function render_pricing_instructions() {
        ?>
        <h2><?php esc_html_e( 'How to Set Up Pricing Rules', 'munchmakers-product-customizer' ); ?></h2>
        <p><?php esc_html_e( 'To enable quantity pricing for a product or its variations:', 'munchmakers-product-customizer' ); ?></p>
        <ul>
            <li><?php printf( esc_html__( 'Set a Minimum Order Quantity using the custom field %s', 'munchmakers-product-customizer' ), '<code>_tiered_price_minimum_qty</code>' ); ?></li>
            <li><?php printf( esc_html__( 'Set a Quantity Interval using the custom field %s', 'munchmakers-product-customizer' ), '<code>quantity_interval</code>' ); ?></li>
            <li><?php printf( esc_html__( 'Define pricing tiers using the custom field %s', 'munchmakers-product-customizer' ), '<code>_fixed_price_rules</code>' ); ?></li>
        </ul>
        <p><strong><?php esc_html_e( 'Example \'_fixed_price_rules\' Value:', 'munchmakers-product-customizer' ); ?></strong></p>
        <pre>a:3:{i:10;s:5:"18.00";i:25;s:5:"15.00";i:50;s:5:"12.50";}</pre>
        <?php
    }

    public function display_custom_data_in_admin_order( $item_id, $item, $product ) {
        if ( ! is_a( $item, 'WC_Order_Item' ) ) {
            return;
        }

        // Display selected options
        if ( $selection = $item->get_meta( 'Selected Options' ) ) {
            echo '<div style="margin-top: 8px; padding: 8px; background: #f8f9fa; border-left: 3px solid #007cba; border-radius: 3px;">';
            echo '<strong style="color: #007cba;">' . esc_html__( 'Selected Options:', 'munchmakers-product-customizer' ) . '</strong><br>';
            echo '<span style="color: #333; font-weight: 500;">' . esc_html( $selection ) . '</span>';
            echo '</div>';
        }

        // Display artwork option
        if ( $artwork_option = $item->get_meta( 'Artwork Option' ) ) {
            echo '<div style="margin-top: 8px; padding: 8px; background: #fff3cd; border-left: 3px solid #ffc107; border-radius: 3px;">';
            echo '<strong style="color: #856404;">' . esc_html__( 'Artwork Option:', 'munchmakers-product-customizer' ) . '</strong><br>';
            echo '<span style="color: #333;">' . esc_html( $artwork_option ) . '</span>';
            echo '</div>';
        }

        // Display uploaded files
        if ( $artwork_files_html = $item->get_meta( 'Uploaded Artwork Files' ) ) {
            echo '<div style="margin-top: 8px; padding: 8px; background: #d1ecf1; border-left: 3px solid #17a2b8; border-radius: 3px;">';
            echo '<strong style="color: #0c5460;">' . esc_html__( 'Uploaded Artwork Files:', 'munchmakers-product-customizer' ) . '</strong><br>';
            echo '<div style="margin-top: 5px;">' . wp_kses_post( $artwork_files_html ) . '</div>';
            echo '</div>';
        }

        // Legacy support for old meta keys
        if ( $legacy_selection = $item->get_meta( 'Selection' ) ) {
            echo '<div style="margin-top: 5px;"><strong>' . esc_html__( 'Selection:', 'munchmakers-product-customizer' ) . '</strong> ' . esc_html( $legacy_selection ) . '</div>';
        }

        if ( $legacy_artwork = $item->get_meta( 'Artwork' ) ) {
            echo '<div style="margin-top: 5px;"><strong>' . esc_html__( 'Artwork:', 'munchmakers-product-customizer' ) . '</strong> ' . esc_html( $legacy_artwork ) . '</div>';
        }

        if ( $legacy_files = $item->get_meta( 'Uploaded Files' ) ) {
            echo '<div style="margin-top: 5px;"><strong>' . esc_html__( 'Uploaded Files:', 'munchmakers-product-customizer' ) . '</strong><br>' . wp_kses_post( $legacy_files ) . '</div>';
        }
    }
}