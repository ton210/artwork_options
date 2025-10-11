<?php
/**
 * MunchMakers Custom Request WooCommerce Integration
 *
 * This file handles the integration with WooCommerce, primarily
 * for adding a "My Custom Requests" tab to the My Account page.
 *
 * @package    MunchMakers_Custom_Request
 * @subpackage MunchMakers_Custom_Request/includes
 * @since      1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

class MCR_WooCommerce_Integration {

    /**
     * Constructor.
     * Hooks are added in class-munchmakers-custom-request.php if WooCommerce is active.
     *
     * @since 1.0.0
     */
    public function __construct() {
        // Hooks are registered by the main plugin class to ensure WooCommerce is active.
    }

    /**
     * Add new query var for the "My Custom Requests" endpoint.
     * This allows WordPress to recognize the endpoint in URLs.
     *
     * @since 1.0.0
     * @param array $vars Existing query vars.
     * @return array Modified query vars.
     */
    public function add_query_vars( $vars ) {
        $vars[] = 'my-custom-requests'; // The slug for our new endpoint
        return $vars;
    }

    /**
     * Add new endpoint for My Account page.
     * This tells WordPress to create a rewrite rule for our new endpoint.
     * This method is static because it's called during plugin activation.
     *
     * @since 1.0.0
     */
    public static function add_endpoints_static() {
        add_rewrite_endpoint( 'my-custom-requests', EP_ROOT | EP_PAGES );
        // EP_ROOT | EP_PAGES makes it available on account pages.
    }

    /**
     * Instance method to call the static add_endpoints method.
     * Used when hooking to 'init' from the main plugin class instance.
     *
     * @since 1.0.0
     */
    public function add_endpoints() {
        self::add_endpoints_static();
    }


    /**
     * Add "My Custom Requests" link to the WooCommerce My Account navigation menu.
     *
     * @since 1.0.0
     * @param array $items Existing menu items.
     * @return array Modified menu items.
     */
    public function add_my_requests_link( $items ) {
        // Make a copy to insert the new item before 'customer-logout'
        $new_items = array();
        $logout_item = null;

        if (isset($items['customer-logout'])) {
            $logout_item = $items['customer-logout'];
            unset($items['customer-logout']); // Remove logout temporarily
        }

        // Add our new item
        $items['my-custom-requests'] = __( 'My Custom Requests', 'munchmakers-custom-request' );

        // Add logout back if it was there
        if ($logout_item) {
            $items['customer-logout'] = $logout_item;
        }

        return $items;
    }

    /**
     * Content for the "My Custom Requests" endpoint/tab in My Account.
     * Displays a table of the current user's custom requests.
     *
     * @since 1.0.0
     */
    public function my_requests_endpoint_content() {
        $current_user_id = get_current_user_id();
        if ( !$current_user_id ) {
            // This should ideally not happen if the page is protected by WooCommerce
            echo '<p class="woocommerce-info">' . esc_html__( 'You must be logged in to view your custom requests.', 'munchmakers-custom-request' ) . '</p>';
            return;
        }

        // Arguments to query the 'mcr_request' CPT posts authored by the current user
        $args = array(
            'post_type'      => MCR_POST_TYPE, // Our rebranded CPT
            'post_status'    => 'publish',     // Only published requests
            'author'         => $current_user_id,
            'posts_per_page' => -1,            // Show all requests
            'orderby'        => 'date',
            'order'          => 'DESC',
        );
        $user_requests = new WP_Query( $args );

        echo '<h2>' . esc_html__( 'My Custom Requests', 'munchmakers-custom-request' ) . '</h2>';

        if ( $user_requests->have_posts() ) : ?>
            <table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table mcr-requests-table">
                <thead>
                    <tr>
                        <th class="woocommerce-orders-table__header mcr-request-id"><span class="nobr"><?php esc_html_e( 'Request', 'munchmakers-custom-request' ); ?></span></th>
                        <th class="woocommerce-orders-table__header mcr-date"><span class="nobr"><?php esc_html_e( 'Date', 'munchmakers-custom-request' ); ?></span></th>
                        <th class="woocommerce-orders-table__header mcr-status"><span class="nobr"><?php esc_html_e( 'Status', 'munchmakers-custom-request' ); ?></span></th>
                        <th class="woocommerce-orders-table__header mcr-product"><span class="nobr"><?php esc_html_e( 'Product', 'munchmakers-custom-request' ); ?></span></th>
                        <th class="woocommerce-orders-table__header mcr-actions"><span class="nobr"><?php esc_html_e( 'Actions', 'munchmakers-custom-request' ); ?></span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ( $user_requests->have_posts() ) : $user_requests->the_post();
                        $request_id    = get_the_ID();
                        $product_id    = get_post_meta( $request_id, '_product_id', true );
                        $product_name  = $product_id ? get_the_title( $product_id ) : __('N/A', 'munchmakers-custom-request');
                        $status_key    = get_post_meta( $request_id, '_request_status', true ) ?: 'new';
                        $status_label  = mcr_get_status_label( $status_key ); // Use our helper function
                        $access_key    = get_post_meta( $request_id, '_request_access_key', true );
                        $tracking_url  = $access_key ? home_url('/track-your-order/?key=' . $access_key) : '#'; // Ensure your tracking page slug is correct
                    ?>
                        <tr class="woocommerce-orders-table__row order">
                            <td class="woocommerce-orders-table__cell mcr-request-id" data-title="<?php esc_attr_e( 'Request ID', 'munchmakers-custom-request' ); ?>">
                                <a href="<?php echo esc_url( $tracking_url ); ?>">#<?php echo esc_html( $request_id ); ?></a>
                            </td>
                            <td class="woocommerce-orders-table__cell mcr-date" data-title="<?php esc_attr_e( 'Date', 'munchmakers-custom-request' ); ?>">
                                <time datetime="<?php echo esc_attr( get_the_date( 'c', $request_id ) ); ?>"><?php echo esc_html( get_the_date( '', $request_id ) ); ?></time>
                            </td>
                            <td class="woocommerce-orders-table__cell mcr-status" data-title="<?php esc_attr_e( 'Status', 'munchmakers-custom-request' ); ?>" style="white-space:nowrap;">
                                <?php echo esc_html( $status_label ); ?>
                            </td>
                            <td class="woocommerce-orders-table__cell mcr-product" data-title="<?php esc_attr_e( 'Product', 'munchmakers-custom-request' ); ?>">
                                <?php echo esc_html( $product_name ); ?>
                            </td>
                            <td class="woocommerce-orders-table__cell mcr-actions" data-title="<?php esc_attr_e( 'Actions', 'munchmakers-custom-request' ); ?>">
                                <a href="<?php echo esc_url( $tracking_url ); ?>" class="woocommerce-button button view"><?php esc_html_e( 'View Details', 'munchmakers-custom-request' ); ?></a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else : ?>
            <div class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">
                <?php esc_html_e( 'You have not placed any custom requests yet.', 'munchmakers-custom-request' ); ?>
                <a class="woocommerce-Button button" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>"><?php esc_html_e( 'Browse products', 'munchmakers-custom-request' ); ?></a>
            </div>
        <?php endif;
        wp_reset_postdata(); // Important after a custom WP_Query loop
    }
}
