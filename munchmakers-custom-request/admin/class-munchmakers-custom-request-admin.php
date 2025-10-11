<?php
// munchmakers-custom-request/admin/class-munchmakers-custom-request-admin.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MunchMakers_Custom_Request_Admin {

    private $plugin_name;
    private $version;

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
    }

    /**
     * Enqueue styles and scripts for the admin area.
     */
    public function enqueue_admin_assets( $hook_suffix ) {
        $screen = get_current_screen();

        // Only load assets on our CPT’s list/edit pages, and our settings page
        if ( $screen
             && (
                    ( $screen->post_type === MCR_POST_TYPE
                        && in_array( $hook_suffix, array( 'post.php', 'post-new.php', 'edit.php' ), true )
                    )
                    || $hook_suffix === MCR_POST_TYPE . '_page_mcr-request-settings' // Corrected settings page hook
                )
           ) {
            // ─── Enqueue Admin CSS ───────────────────────────────────
            wp_enqueue_style(
                "{$this->plugin_name}-admin",
                MCR_PLUGIN_URL . 'admin/assets/css/munchmakers-custom-request-admin.css',
                array(),
                $this->version,
                'all'
            );

            // ─── Enqueue Admin JS (only on the CPT “edit” screen) ────
            if ( $screen->post_type === MCR_POST_TYPE && $hook_suffix === 'post.php' ) {
                wp_enqueue_script(
                    "{$this->plugin_name}-admin-js",
                    MCR_PLUGIN_URL . 'admin/assets/js/munchmakers-custom-request-admin.js',
                    array( 'jquery', 'wp-i18n' ), // Added wp-i18n for future use if needed
                    $this->version,
                    true
                );

                wp_localize_script(
                    "{$this->plugin_name}-admin-js",
                    'mcr_admin_ajax',
                    array(
                        'ajax_url'         => admin_url( 'admin-ajax.php' ),
                        'send_proof_nonce' => wp_create_nonce( 'mcr_send_proof_nonce' ),
                        'add_note_nonce'   => wp_create_nonce( 'mcr_add_note_nonce' ),
                        'sending_text'     => __( 'Sending...', 'munchmakers-custom-request' ),
                        'adding_note_text' => __( 'Adding Note...', 'munchmakers-custom-request' ),
                        'error_text'       => __( 'Error. Please try again.', 'munchmakers-custom-request' ),
                    )
                );
            }

            // ─── Inline "Copy Link" JS (must be attached to 'jquery') ─
            // Consider moving to the admin JS file if it grows.
            wp_add_inline_script(
                'jquery', // Ensure this handle is enqueued
                "jQuery(document).ready(function($){
                    $(document).on('click', '.mcr-copy-button', function(e){
                        e.preventDefault();
                        var textToCopy = $(this).data('copy');
                        var tempTextarea = $('<textarea style=\"position:absolute;left:-9999px\">');
                        $('body').append(tempTextarea);
                        tempTextarea.val(textToCopy).select();
                        try {
                            var successful = document.execCommand('copy');
                            var originalText = $(this).text();
                            $(this).text(successful ? '" . esc_js(__('Copied!', 'munchmakers-custom-request')) . "' : '" . esc_js(__('Failed', 'munchmakers-custom-request')) . "');
                            setTimeout(function(){ $('.mcr-copy-button').text(originalText); }, 2000);
                        } catch(err) {
                            var originalText = $(this).text();
                            $(this).text('" . esc_js(__('Failed', 'munchmakers-custom-request')) . "');
                            setTimeout(function(){ $('.mcr-copy-button').text(originalText); }, 2000);
                        }
                        tempTextarea.remove();
                    });
                });"
            );
        }
    }

    /**
     * Register the Custom Post Type for requests.
     */
    public function register_custom_post_type() {
        $labels = array(
            'name'                  => _x( 'Custom Requests', 'Post Type General Name', 'munchmakers-custom-request' ),
            'singular_name'         => _x( 'Custom Request', 'Post Type Singular Name', 'munchmakers-custom-request' ),
            'menu_name'             => __( 'MunchMakers Requests', 'munchmakers-custom-request' ),
            'name_admin_bar'        => _x( 'Custom Request', 'Add New on Admin Bar', 'munchmakers-custom-request' ),
            'archives'              => __( 'Request Archives', 'munchmakers-custom-request' ),
            'attributes'            => __( 'Request Attributes', 'munchmakers-custom-request' ),
            'parent_item_colon'     => __( 'Parent Request:', 'munchmakers-custom-request' ),
            'all_items'             => __( 'All Custom Requests', 'munchmakers-custom-request' ),
            'add_new_item'          => __( 'Add New Request', 'munchmakers-custom-request' ),
            'add_new'               => __( 'Add New', 'munchmakers-custom-request' ),
            'new_item'              => __( 'New Request', 'munchmakers-custom-request' ),
            'edit_item'             => __( 'Edit Custom Request', 'munchmakers-custom-request' ),
            'update_item'           => __( 'Update Request', 'munchmakers-custom-request' ),
            'view_item'             => __( 'View Request', 'munchmakers-custom-request' ),
            'view_items'            => __( 'View Requests', 'munchmakers-custom-request' ),
            'search_items'          => __( 'Search Requests', 'munchmakers-custom-request' ),
            'not_found'             => __( 'No requests found', 'munchmakers-custom-request' ),
            'not_found_in_trash'    => __( 'No requests found in Trash', 'munchmakers-custom-request' ),
            'featured_image'        => __( 'Featured Image', 'munchmakers-custom-request' ), // Example if needed
            'set_featured_image'    => __( 'Set featured image', 'munchmakers-custom-request' ),
            'remove_featured_image' => __( 'Remove featured image', 'munchmakers-custom-request' ),
            'use_featured_image'    => __( 'Use as featured image', 'munchmakers-custom-request' ),
            'insert_into_item'      => __( 'Insert into request', 'munchmakers-custom-request' ),
            'uploaded_to_this_item' => __( 'Uploaded to this request', 'munchmakers-custom-request' ),
            'items_list'            => __( 'Requests list', 'munchmakers-custom-request' ),
            'items_list_navigation' => __( 'Requests list navigation', 'munchmakers-custom-request' ),
            'filter_items_list'     => __( 'Filter requests list', 'munchmakers-custom-request' ),
        );
        $args = array(
            'label'               => __( 'Custom Request', 'munchmakers-custom-request' ),
            'description'         => __( 'Custom design requests submitted by customers.', 'munchmakers-custom-request' ),
            'labels'              => $labels,
            'supports'            => array( 'title' ), // Requests are primarily data, content is in meta or description.
            'hierarchical'        => false,
            'public'              => false,  // Not publicly queryable unless through specific interfaces like the tracker.
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_position'       => 25,
            'menu_icon'           => 'dashicons-edit-page', // Changed icon
            'show_in_admin_bar'   => true,
            'show_in_nav_menus'   => false,
            'can_export'          => true,
            'has_archive'         => false, // No public archive page.
            'exclude_from_search' => true,
            'publicly_queryable'  => false, // True if using REST API and want it to be public.
            'capability_type'     => 'post', // Use 'post' for standard permissions, can be custom.
            'capabilities' => array(
                'create_posts' => 'do_not_allow', // Prevents users from using "Add New" from the CPT menu.
            ),
            'map_meta_cap'        => true, // Required to make 'do_not_allow' work as expected.
            'rewrite'             => false, // No rewrite rules needed if not public.
        );
        register_post_type( MCR_POST_TYPE, $args );

        // Flush rewrite rules on activation if needed (handled by transient)
        $this->maybe_flush_rewrite_rules_after_activation();
    }

    /**
     * Check if rewrite rules need to be flushed after activation.
     * This is hooked to admin_init.
     */
    public function maybe_flush_rewrite_rules_after_activation() {
        if ( get_transient( 'mcr_activated_flush_rewrite' ) ) {
            flush_rewrite_rules();
            delete_transient( 'mcr_activated_flush_rewrite' );
            if ( function_exists( 'mcr_log' ) ) {
                mcr_log( 'Rewrite rules flushed on admin_init after plugin activation.', 'INFO', __FUNCTION__ );
            }
        }
    }

    /**
     * Add Meta Boxes to the CPT edit screen.
     */
    public function add_meta_boxes() {
        add_meta_box(
            'mcr_request_details_mb', // ID
            __( 'Request Details', 'munchmakers-custom-request' ), // Title
            array( $this, 'render_details_meta_box' ), // Callback
            MCR_POST_TYPE, // Screen (post type)
            'normal', // Context (normal, side, advanced)
            'high' // Priority (high, core, default, low)
        );
        add_meta_box(
            'mcr_internal_notes_mb',
            __( 'Internal Notes & Activity Log', 'munchmakers-custom-request' ),
            array( $this, 'render_internal_notes_meta_box' ),
            MCR_POST_TYPE,
            'normal',
            'default'
        );
        add_meta_box(
            'mcr_request_actions_mb',
            __( 'Request Management', 'munchmakers-custom-request' ),
            array( $this, 'render_actions_meta_box' ),
            MCR_POST_TYPE,
            'side',
            'core'
        );
        add_meta_box(
            'mcr_send_proof_mb',
            __( 'Send Design Proof', 'munchmakers-custom-request' ),
            array( $this, 'render_send_proof_meta_box' ),
            MCR_POST_TYPE,
            'side',
            'default'
        );
    }

    /**
     * Render the main details Meta Box content.
     */
    public function render_details_meta_box( $post ) {
        // Add a nonce field so we can check for it later.
        wp_nonce_field( 'mcr_save_meta_box_data', 'mcr_meta_box_nonce' );

        $customer_name = get_post_meta( $post->ID, '_customer_name', true );
        $customer_email = get_post_meta( $post->ID, '_customer_email', true );
        $customer_phone = get_post_meta( $post->ID, '_customer_phone', true );
        $product_id = get_post_meta( $post->ID, '_product_id', true );
        $product_name = $product_id ? get_the_title( $product_id ) : __( 'N/A', 'munchmakers-custom-request' );
        $customer_image_url = get_post_meta( $post->ID, '_customer_image_url', true );
        $request_content = $post->post_content; // Original request details
        $customer_ip = get_post_meta( $post->ID, '_customer_ip', true );
        $customer_location = get_post_meta( $post->ID, '_customer_location', true );
        $access_key = get_post_meta( $post->ID, '_request_access_key', true );
        $tracking_link = $access_key ? home_url('/track-your-order/?key=' . $access_key) : '#';


        ?>
        <table class="form-table mcr-details-table">
            <tbody>
                <tr>
                    <th><?php esc_html_e( 'Customer Name:', 'munchmakers-custom-request' ); ?></th>
                    <td><?php echo esc_html( $customer_name ?: 'N/A' ); ?></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Customer Email:', 'munchmakers-custom-request' ); ?></th>
                    <td><a href="mailto:<?php echo esc_attr( $customer_email ); ?>"><?php echo esc_html( $customer_email ); ?></a></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Customer Phone:', 'munchmakers-custom-request' ); ?></th>
                    <td><?php echo esc_html( $customer_phone ?: 'N/A' ); ?></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Related Product:', 'munchmakers-custom-request' ); ?></th>
                    <td>
                        <?php if ( $product_id && $product_name !== 'N/A' ) : ?>
                            <a href="<?php echo esc_url( get_edit_post_link( $product_id ) ); ?>" target="_blank">
                                <?php echo esc_html( $product_name ); ?> (ID: <?php echo esc_html( $product_id ); ?>)
                            </a>
                        <?php else : ?>
                            <?php echo esc_html( $product_name ); ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Submitted On:', 'munchmakers-custom-request' ); ?></th>
                    <td><?php echo esc_html( get_the_date( get_option('date_format') . ' ' . get_option('time_format'), $post->ID ) ); ?></td>
                </tr>
                 <tr>
                    <th><?php esc_html_e( 'Customer Tracking Link:', 'munchmakers-custom-request' ); ?></th>
                    <td>
                        <?php if ($access_key): ?>
                            <a href="<?php echo esc_url($tracking_link); ?>" target="_blank"><?php echo esc_url($tracking_link); ?></a>
                            <button type="button" class="button button-small mcr-copy-button" data-copy="<?php echo esc_attr($tracking_link); ?>" style="margin-left: 10px;"><?php esc_html_e('Copy Link', 'munchmakers-custom-request'); ?></button>
                        <?php else: ?>
                            <?php esc_html_e('N/A', 'munchmakers-custom-request'); ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><hr></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Request Details:', 'munchmakers-custom-request' ); ?></th>
                    <td><div class="mcr-request-content-admin"><?php echo wp_kses_post( wpautop( $request_content ) ); ?></div></td>
                </tr>

                <?php if ( $customer_image_url ) : ?>
                <tr>
                    <th><?php esc_html_e( 'Uploaded Image/Logo:', 'munchmakers-custom-request' ); ?></th>
                    <td>
                        <a href="<?php echo esc_url( $customer_image_url ); ?>" target="_blank">
                            <img src="<?php echo esc_url( $customer_image_url ); ?>" alt="<?php esc_attr_e( 'Customer Uploaded Image', 'munchmakers-custom-request' ); ?>" style="max-width: 200px; height: auto; border: 1px solid #ddd; padding: 5px;">
                        </a> <br>
                        <a href="<?php echo esc_url( $customer_image_url ); ?>" target="_blank"><?php esc_html_e( 'View full size', 'munchmakers-custom-request' ); ?></a>
                    </td>
                </tr>
                <?php endif; ?>
                 <tr>
                    <td colspan="2"><hr></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Customer IP:', 'munchmakers-custom-request' ); ?></th>
                    <td><?php echo esc_html( $customer_ip ?: 'N/A' ); ?></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Customer Location (Approx.):', 'munchmakers-custom-request' ); ?></th>
                    <td><?php echo esc_html( $customer_location ?: 'N/A' ); ?></td>
                </tr>
            </tbody>
        </table>
        <?php
    }

    /**
     * Render the Internal Notes & Activity Log Meta Box content.
     */
    public function render_internal_notes_meta_box( $post ) {
        $internal_notes = get_post_meta( $post->ID, '_internal_notes_log', true );
        if ( ! is_array( $internal_notes ) ) {
            $internal_notes = array();
        }
        ?>
        <div id="mcr-internal-notes-log-wrapper" class="mcr-notes-wrapper">
            <?php if ( empty( $internal_notes ) ) : ?>
                <p><?php esc_html_e( 'No internal notes or activity yet.', 'munchmakers-custom-request' ); ?></p>
            <?php else : ?>
                <ul class="mcr-internal-notes-log">
                    <?php foreach ( array_reverse( $internal_notes ) as $note_entry ) : // Show newest first ?>
                        <li>
                            <div class="note-meta">
                                <span class="note-author"><?php echo esc_html( $note_entry['user'] ?? 'System' ); ?></span>
                                <span class="note-date"><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $note_entry['time'] ?? time() ) ); ?></span>
                            </div>
                            <div class="note-content"><?php echo wp_kses_post( wpautop( $note_entry['note'] ?? '' ) ); ?></div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <div id="mcr-add-internal-note-form" style="margin-top: 20px;">
            <h4><?php esc_html_e( 'Add New Internal Note', 'munchmakers-custom-request' ); ?></h4>
            <textarea id="mcr_new_internal_note" name="mcr_new_internal_note_content" rows="4" class="widefat" placeholder="<?php esc_attr_e( 'Type your note here...', 'munchmakers-custom-request' ); ?>"></textarea>
            <p>
                <button type="button" id="mcr-add-note-button" class="button button-secondary" data-postid="<?php echo esc_attr( $post->ID ); ?>">
                    <?php esc_html_e( 'Add Note', 'munchmakers-custom-request' ); ?>
                </button>
                <span class="spinner" style="float:none; vertical-align: middle; margin-left: 5px;"></span>
            </p>
            <div id="mcr-add-note-feedback" style="margin-top:10px;"></div>
        </div>
        <?php
    }

    /**
     * Render the Request Management (Actions) Meta Box content.
     */
    public function render_actions_meta_box( $post ) {
        $current_status = get_post_meta( $post->ID, '_request_status', true ) ?: 'new';
        $price_quote    = get_post_meta( $post->ID, '_price_quote', true );
        $all_statuses   = mcr_get_request_statuses();
        $user_id        = get_post_meta( $post->ID, '_customer_user_id', true ); // Assuming this meta key exists from user creation. Or use post_author.
        $user_info      = $post->post_author ? get_userdata($post->post_author) : null;

        ?>
        <div class="mcr-actions-metabox">
            <p>
                <label for="mcr_request_status"><strong><?php esc_html_e( 'Request Status:', 'munchmakers-custom-request' ); ?></strong></label><br>
                <select name="mcr_request_status" id="mcr_request_status" class="widefat">
                    <?php foreach ( $all_statuses as $key => $label ) : ?>
                        <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $current_status, $key ); ?>><?php echo esc_html( $label ); ?></option>
                    <?php endforeach; ?>
                </select>
            </p>
            <hr>

            <h4><?php esc_html_e( 'Customer Account', 'munchmakers-custom-request' ); ?></h4>
            <?php if ($user_info): ?>
                <p>
                    <?php esc_html_e('Linked to user:', 'munchmakers-custom-request'); ?>
                    <a href="<?php echo esc_url(get_edit_user_link($user_info->ID)); ?>" target="_blank">
                        <?php echo esc_html($user_info->display_name); ?> (<?php echo esc_html($user_info->user_email); ?>)
                    </a>
                </p>
            <?php else: ?>
                 <p><?php esc_html_e('No WordPress user account linked.', 'munchmakers-custom-request'); ?></p>
            <?php endif; ?>
            <hr>

            <h4><?php esc_html_e( 'Quoting (Optional)', 'munchmakers-custom-request' ); ?></h4>
            <p>
                <label for="mcr_price_quote"><strong><?php esc_html_e( 'Price Quote', 'munchmakers-custom-request' ); ?> (<?php echo get_woocommerce_currency_symbol(); ?>)</strong></label><br>
                <input type="text" id="mcr_price_quote" name="mcr_price_quote" value="<?php echo esc_attr( wc_format_localized_price( $price_quote ) ); ?>" class="widefat" placeholder="<?php esc_attr_e( 'e.g., 99.95', 'munchmakers-custom-request' ); ?>">
            </p>
            <p>
                <button type="button" class="button button-secondary widefat" disabled title="<?php esc_attr_e( 'Future: Send quote and payment link to customer.', 'munchmakers-custom-request' ); ?>">
                    <?php esc_html_e( 'Send Quote & Payment Link', 'munchmakers-custom-request' ); ?>
                </button>
            </p>
            <div class="clear"></div>

            <div id="major-publishing-actions">
                <div id="delete-action">
                    <?php
                    if ( current_user_can( "delete_post", $post->ID ) ) {
                        if ( !EMPTY_TRASH_DAYS ) {
                            $delete_text = __('Delete Permanently', 'munchmakers-custom-request');
                        } else {
                            $delete_text = __('Move to Trash', 'munchmakers-custom-request');
                        }
                        ?>
                        <a class="submitdelete deletion" href="<?php echo get_delete_post_link($post->ID); ?>"><?php echo $delete_text; ?></a><?php
                    } ?>
                </div>
                <div id="publishing-action">
                    <span class="spinner"></span>
                    <?php // This uses the main 'Publish' or 'Update' button from WordPress core publish meta box.
                          // If this meta box REPLACES the core publish box, you'd need a proper save button.
                          // Since it's 'side' context, it complements it. The save is handled by WordPress.
                          // We just need to ensure our fields are saved in save_meta_box_data.
                          // For clarity, let's assume the main "Update" button handles saving.
                          // If you want a dedicated save button here:
                          // submit_button( __( 'Update Request', 'munchmakers-custom-request' ), 'primary large', 'mcr_save_request_actions', false );
                    ?>
                </div>
                <div class="clear"></div>
            </div>
        </div>
        <?php
    }

    /**
     * Render the Send Design Proof Meta Box content.
     */
    public function render_send_proof_meta_box( $post ) {
        ?>
        <div id="send-proof-container">
            <p>
                <label for="mcr_proof_file"><strong><?php esc_html_e( 'Attach Proof (JPG, PNG, PDF):', 'munchmakers-custom-request' ); ?></strong></label><br>
                <input type="file" id="mcr_proof_file" name="mcr_proof_file" accept=".jpg,.jpeg,.png,.pdf" style="width:100%;">
            </p>
            <p>
                <label for="mcr_proof_message"><strong><?php esc_html_e( 'Optional Message to Customer:', 'munchmakers-custom-request' ); ?></strong></label><br>
                <textarea id="mcr_proof_message" name="mcr_proof_message" rows="4" class="widefat" placeholder="<?php esc_attr_e( 'e.g., Hi {customer_name}, here is your design proof! Let me know if you approve or need any changes.', 'munchmakers-custom-request' ); ?>"></textarea>
                <small><?php esc_html_e( 'Placeholders: {customer_name}, {product_name}, {request_id}', 'munchmakers-custom-request' ); ?></small>
            </p>
            <p>
                <button type="button" id="mcr-send-proof-button" class="button button-primary widefat" data-postid="<?php echo esc_attr( $post->ID ); ?>">
                    <?php esc_html_e( 'Send Proof & Update Status', 'munchmakers-custom-request' ); ?>
                </button>
                <span class="spinner" style="float:none; vertical-align: middle; margin-left: 5px;"></span>
            </p>
            <div id="mcr-send-proof-feedback" style="margin-top:10px;"></div>
        </div>
        <?php
    }

    /**
     * Save the meta box data when the post is saved.
     */
    public function save_meta_box_data( $post_id, $post_object ) { // $post_object is passed by save_post hook
        // Check if our nonce is set.
        if ( ! isset( $_POST['mcr_meta_box_nonce'] ) ) {
            return;
        }
        // Verify that the nonce is valid.
        if ( ! wp_verify_nonce( $_POST['mcr_meta_box_nonce'], 'mcr_save_meta_box_data' ) ) {
            return;
        }
        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        // Check the user's permissions.
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
        // Make sure it's our CPT.
        if ( MCR_POST_TYPE !== $post_object->post_type ) {
            return;
        }

        $current_user = wp_get_current_user();

        // ─── Update Status ──────────────────────────────────────────────
        $old_status = get_post_meta( $post_id, '_request_status', true ) ?: 'new';
        $new_status = isset( $_POST['mcr_request_status'] ) ? sanitize_text_field( $_POST['mcr_request_status'] ) : $old_status;

        if ( $new_status !== $old_status ) {
            update_post_meta( $post_id, '_request_status', $new_status );

            $note_content = sprintf(
                // translators: 1: Old status label, 2: New status label
                __( 'Status changed from "%1$s" to "%2$s".', 'munchmakers-custom-request' ),
                mcr_get_status_label( $old_status ),
                mcr_get_status_label( $new_status )
            );
            $this->add_internal_note_entry( $post_id, $note_content, $current_user->display_name );

            // Send notifications if status actually changed
            if ( function_exists( 'mcr_send_customer_status_update_email' ) ) {
                mcr_send_customer_status_update_email( $post_id, $new_status, $old_status );
            }
            if ( function_exists( 'mcr_notify_slack_status_update' ) ) {
                mcr_notify_slack_status_update( $post_id, $new_status, $old_status );
            }
            mcr_log( "Request #{$post_id} status updated from {$old_status} to {$new_status} by {$current_user->user_login}.", 'INFO', __FUNCTION__ );
        }

        // ─── Update Price Quote ─────────────────────────────────────────
        if ( isset( $_POST['mcr_price_quote'] ) ) {
            $old_quote_raw = get_post_meta( $post_id, '_price_quote', true );
            // Sanitize and format the price to store consistently (e.g., '100.00' or empty)
            $new_quote_raw = trim( $_POST['mcr_price_quote'] );
            $new_quote_sanitized = wc_format_decimal( $new_quote_raw, wc_get_price_decimals() ); // Standard WooCommerce decimal format

            if ( $new_quote_sanitized !== wc_format_decimal( $old_quote_raw, wc_get_price_decimals() ) ) {
                update_post_meta( $post_id, '_price_quote', $new_quote_sanitized );
                $this->add_internal_note_entry( $post_id, sprintf( 
                    // translators: %s: Price quote amount
                    __( 'Price quote updated to %s.', 'munchmakers-custom-request' ), 
                    wc_price($new_quote_sanitized) 
                ), $current_user->display_name );
                mcr_log( "Request #{$post_id} price quote updated to {$new_quote_sanitized} by {$current_user->user_login}.", 'INFO', __FUNCTION__ );
            }
        }
    }

    /**
     * AJAX handler for adding an internal note.
     */
    public function ajax_add_internal_note() {
        check_ajax_referer( 'mcr_add_note_nonce', 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) { // Check appropriate capability for editing these posts
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'munchmakers-custom-request' ) ) );
        }

        $post_id      = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
        $note_content = isset( $_POST['note'] ) ? wp_kses_post( trim( $_POST['note'] ) ) : ''; // wp_kses_post for HTML content

        if ( ! $post_id || empty( $note_content ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid data. Note cannot be empty.', 'munchmakers-custom-request' ) ) );
        }

        $current_user = wp_get_current_user();
        $result       = $this->add_internal_note_entry( $post_id, $note_content, $current_user->display_name );

        if ( $result ) {
            mcr_log( "Internal note added to request #{$post_id} by {$current_user->user_login}: " . substr( $note_content, 0, 100 ), 'INFO', __FUNCTION__ );
            // Prepare the HTML for the newly added note to prepend to the list via JS
            $note_entry_for_js = array(
                'user' => esc_html( $current_user->display_name ),
                'time' => esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), time() ) ),
                'note' => wp_kses_post( wpautop( $note_content ) ), // wpautop for display
            );
            $note_html  = '<li><div class="note-meta"><span class="note-author">' . $note_entry_for_js['user'] . '</span> <span class="note-date">' . $note_entry_for_js['time'] . '</span></div><div class="note-content">' . $note_entry_for_js['note'] . '</div></li>';
            
            wp_send_json_success( array( 
                'message' => __( 'Note added successfully!', 'munchmakers-custom-request' ),
                'note_html' => $note_html 
            ) );
        } else {
            mcr_log( "Failed to add internal note to request #{$post_id} by {$current_user->user_login}.", 'ERROR', __FUNCTION__ );
            wp_send_json_error( array( 'message' => __( 'Failed to add note. Please try again.', 'munchmakers-custom-request' ) ) );
        }
    }

    /**
     * Helper function to add an internal note entry to post meta.
     * Stores notes as an array of associative arrays in a single meta field.
     *
     * @param int    $post_id      The ID of the post.
     * @param string $note_content The content of the note.
     * @param string $user_name    The display name of the user adding the note.
     * @return bool True on success, false on failure.
     */
    private function add_internal_note_entry( $post_id, $note_content, $user_name ) {
        $notes_log = get_post_meta( $post_id, '_internal_notes_log', true );
        if ( ! is_array( $notes_log ) ) {
            $notes_log = array();
        }
        $new_note_entry = array(
            'time' => time(), // Store as Unix timestamp
            'user' => $user_name,
            'note' => $note_content, // Raw content, will be processed on display
        );
        $notes_log[] = $new_note_entry;
        return update_post_meta( $post_id, '_internal_notes_log', $notes_log );
    }

    /**
     * AJAX handler for sending the proof email.
     * @updated 1.2.1 (Using mcr_handle_file_upload helper)
     */
    public function ajax_send_proof_email() {
        mcr_log( "AJAX Send Proof: Action started.", 'DEBUG', __FUNCTION__ );
        check_ajax_referer( 'mcr_send_proof_nonce', 'nonce' );
        mcr_log( "AJAX Send Proof: Nonce verified.", 'DEBUG', __FUNCTION__ );

        if ( ! current_user_can( 'edit_posts' ) ) { // Or a more specific capability if defined
            mcr_log( "AJAX Send Proof: Permission denied.", 'ERROR', __FUNCTION__ );
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'munchmakers-custom-request' ) ) );
        }

        $post_id            = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
        $custom_message_raw = isset( $_POST['message'] ) ? wp_kses_post( trim( $_POST['message'] ) ) : ''; // Use wp_kses_post for safety
        mcr_log( "AJAX Send Proof: Post ID: {$post_id}", 'DEBUG', __FUNCTION__ );

        if ( ! $post_id || get_post_type($post_id) !== MCR_POST_TYPE ) {
            mcr_log( "AJAX Send Proof: Invalid request ID or post type.", 'ERROR', __FUNCTION__ );
            wp_send_json_error( array( 'message' => __( 'Invalid request ID.', 'munchmakers-custom-request' ) ) );
        }

        $customer_email = get_post_meta( $post_id, '_customer_email', true );
        $customer_name  = get_post_meta( $post_id, '_customer_name', true ) ?: 'Valued Customer';
        $product_id     = get_post_meta( $post_id, '_product_id', true );
        $product_name   = $product_id ? get_the_title( $product_id ) : __('the requested item', 'munchmakers-custom-request');


        if ( ! is_email( $customer_email ) ) {
            mcr_log( "AJAX Send Proof: Invalid customer email: {$customer_email}", 'ERROR', __FUNCTION__ );
            wp_send_json_error( array( 'message' => __( 'Invalid customer email address.', 'munchmakers-custom-request' ) ) );
        }

        // ─── Handle the file upload using the new helper ─────────────────
        $file_path_for_attachment = '';
        $file_name_for_log_email = 'N/A';

        if ( isset( $_FILES['mcr_proof_file'] ) && $_FILES['mcr_proof_file']['error'] === UPLOAD_ERR_OK ) {
            mcr_log( "AJAX Send Proof: File detected: " . sanitize_file_name( $_FILES['mcr_proof_file']['name'] ), 'DEBUG', __FUNCTION__ );
            
            $allowed_mime_types = array( // Define allowed types for proofs
                'jpg|jpeg|jpe' => 'image/jpeg',
                'png'          => 'image/png',
                'pdf'          => 'application/pdf',
            );
            // Max file size check (example: 10MB for proofs)
            $max_file_size = 10 * 1024 * 1024; // 10MB
            if ($_FILES['mcr_proof_file']['size'] > $max_file_size) {
                mcr_log("AJAX Send Proof: File upload error: File too large. Size: " . $_FILES['mcr_proof_file']['size'], "ERROR", __FUNCTION__);
                wp_send_json_error( array( 'message' => __('Proof file is too large. Maximum size is 10MB.', 'munchmakers-custom-request') ) );
            }

            $upload_result = mcr_handle_file_upload( $_FILES['mcr_proof_file'], 'munchmakers_proofs', $allowed_mime_types );

            if ( isset($upload_result['file']) && !isset($upload_result['error']) ) { // Check for 'file' key specifically
                $file_path_for_attachment = $upload_result['file']; // This is the server path
                $file_name_for_log_email = basename( $upload_result['file'] );
                mcr_log( "AJAX Send Proof: File uploaded via helper: {$file_path_for_attachment}", 'INFO', __FUNCTION__ );
            } else {
                $error_msg = $upload_result['error'] ?? __('Unknown upload processing error.', 'munchmakers-custom-request');
                mcr_log( "AJAX Send Proof: File upload error via helper: {$error_msg}", 'ERROR', __FUNCTION__ );
                wp_send_json_error( array( 'message' => __( 'Proof file upload error: ', 'munchmakers-custom-request' ) . $error_msg ) );
            }
        } else {
            $upload_error_code = isset( $_FILES['mcr_proof_file']['error'] ) ? $_FILES['mcr_proof_file']['error'] : 'Not Set';
            mcr_log( "AJAX Send Proof: No proof file or upload error. Code: {$upload_error_code}", 'WARNING', __FUNCTION__ );
            // It's crucial to have a proof file if this action is "Send Proof"
            wp_send_json_error( array( 'message' => __( 'No proof file selected or an upload error occurred. Please select a file.', 'munchmakers-custom-request' ) ) );
        }

        // ─── Build and send the email ─────────────────────────────────────
        $options = get_option('mcr_options');
        
        // Subject: Use setting or default
        $subject_template = isset($options['status_awaiting_customer_subject']) && !empty(trim($options['status_awaiting_customer_subject']))
                            ? $options['status_awaiting_customer_subject']
                            : "Your MunchMakers Custom Design Proof is Ready! (Request #{request_id})";

        // Body: Use setting or default. This is specifically for the proof sending email.
        $body_template_setting_key = 'proof_sent_email_body'; // A dedicated setting might be better
        $default_body = "Hi {customer_name},\n\nYour design proof (\"" . esc_html( $file_name_for_log_email ) . "\") for your request regarding {product_name} is attached.\n\n";
        if ( ! empty( $custom_message_raw ) ) {
             $default_body .= "Message from our team:\n" . strip_tags($custom_message_raw) . "\n\n"; // Strip tags for plain text message part
        }
        $default_body .= "Please review the proof and let us know if you approve it or if you require any revisions. You can reply to this email or contact us through your request tracking page: {tracking_link}\n\nThank you,\nThe MunchMakers Team";
        
        $email_body_template = isset($options[$body_template_setting_key]) && !empty(trim($options[$body_template_setting_key]))
                            ? $options[$body_template_setting_key]
                            : $default_body;

        $tracking_link = get_post_meta($post_id, '_request_access_key', true) ? home_url('/track-your-order/?key=' . get_post_meta($post_id, '_request_access_key', true)) : home_url('/track-your-order/');
        
        $placeholders = array(
            '{customer_name}' => $customer_name,
            '{product_name}'  => $product_name,
            '{request_id}'    => $post_id,
            '{tracking_link}' => $tracking_link,
            '{proof_file_name}' => $file_name_for_log_email, // New placeholder
        );

        $final_subject = str_replace(array_keys($placeholders), array_values($placeholders), $subject_template);
        $final_body    = str_replace(array_keys($placeholders), array_values($placeholders), $email_body_template);


        $headers       = array( 'Content-Type: text/plain; charset=UTF-8' ); // Plain text email is often better for deliverability with attachments
        $attachments   = array( $file_path_for_attachment );
        mcr_log( "AJAX Send Proof: Sending email to {$customer_email} with subject '{$final_subject}' and attachment {$file_path_for_attachment}", 'DEBUG', __FUNCTION__ );

        if ( wp_mail( $customer_email, $final_subject, $final_body, $headers, $attachments ) ) {
            $old_status = get_post_meta( $post_id, '_request_status', true );
            update_post_meta( $post_id, '_request_status', 'awaiting_customer' );
            update_post_meta( $post_id, '_last_proof_sent_file', $file_name_for_log_email); // Store filename of sent proof
            update_post_meta( $post_id, '_last_proof_sent_date', time() );


            $current_user = wp_get_current_user();
            $note = sprintf( 
                // translators: %s: File name of the proof
                __( 'Design proof ("%s") sent to customer.', 'munchmakers-custom-request' ), 
                esc_html( $file_name_for_log_email ) 
            );
            $this->add_internal_note_entry( $post_id, $note, $current_user->display_name );

            // This notification is specific to proof sending.
            if ( function_exists( 'mcr_notify_slack_proof_sent' ) ) {
                mcr_notify_slack_proof_sent( $post_id );
            }
            // The mcr_send_customer_status_update_email for 'awaiting_customer' might be redundant if the proof email content is sufficient.
            // However, it's also okay to send it as it might have different wording from settings.
            // For now, let's assume the proof email IS the status update email for this state.
            // If a separate status update email is desired, call it here:
            // if ( function_exists( 'mcr_send_customer_status_update_email' ) ) {
            //     mcr_send_customer_status_update_email( $post_id, 'awaiting_customer', $old_status );
            // }
            
            mcr_log( "AJAX Send Proof: Email sent for #{$post_id} by {$current_user->user_login}. Status updated to awaiting_customer.", 'INFO', __FUNCTION__ );

            wp_send_json_success( array( 
                'message' => __( 'Proof sent successfully and status updated to "Proof Ready for Review"!', 'munchmakers-custom-request' ),
                'new_status_label' => mcr_get_status_label('awaiting_customer') // For UI update if needed
            ) );
        } else {
            global $phpmailer;
            $mail_error = 'wp_mail() returned false.';
            if ( isset( $phpmailer ) && is_object( $phpmailer ) && property_exists($phpmailer, 'ErrorInfo') && ! empty( $phpmailer->ErrorInfo ) ) {
                $mail_error = $phpmailer->ErrorInfo;
            }
            mcr_log( "AJAX Send Proof: FAILED to send proof email for #{$post_id}. Mailer Error: {$mail_error}", 'ERROR', __FUNCTION__ );
            wp_send_json_error( array( 'message' => __( 'Failed to send email. Error: ', 'munchmakers-custom-request' ) . esc_html($mail_error) ) );
        }
    }

    /**
     * Set up the custom columns for the CPT list table.
     */
    public function set_custom_edit_columns( $columns ) {
        // Remove default columns we don't need or will re-order
        unset( $columns['title'], $columns['date'], $columns['author'] ); // Example: remove author

        $new_columns = array();
        $new_columns['cb']             = '<input type="checkbox" />'; // Checkbox for bulk actions
        $new_columns['request_title']  = __( 'Request (From)', 'munchmakers-custom-request' ); // Custom title
        $new_columns['request_status'] = __( 'Status', 'munchmakers-custom-request' );
        $new_columns['customer_email'] = __( 'Customer Email', 'munchmakers-custom-request' );
        $new_columns['product']        = __( 'Product', 'munchmakers-custom-request' );
        $new_columns['request_date']   = __( 'Date Submitted', 'munchmakers-custom-request' ); // Re-add date with custom key
        // $new_columns['price_quote']    = __( 'Quote', 'munchmakers-custom-request' ); // Example new column

        return $new_columns;
    }

    /**
     * Add the data to the custom columns for the CPT.
     */
    public function custom_column_content( $column, $post_id ) {
        switch ( $column ) {
            case 'request_title':
                $title = get_the_title( $post_id );
                $edit_link = get_edit_post_link( $post_id );
                echo '<strong><a class="row-title" href="' . esc_url( $edit_link ) . '">' . esc_html( $title ) . '</a></strong>';
                // Add quick actions (view, trash) below title
                // $actions = array(); // Build actions array if needed
                // echo $this->row_actions($actions); // WordPress method for row actions
                break;

            case 'request_status':
                $status_key = get_post_meta( $post_id, '_request_status', true ) ?: 'new';
                // Add a class for potential styling based on status
                echo '<span class="mcr-status-label status-' . esc_attr( $status_key ) . '">' . esc_html( mcr_get_status_label( $status_key ) ) . '</span>';
                break;

            case 'customer_email':
                $email = get_post_meta( $post_id, '_customer_email', true );
                if ( $email ) {
                    echo '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>';
                } else {
                    echo '&#8212;'; // Em dash for empty
                }
                break;

            case 'product':
                $product_id    = get_post_meta( $post_id, '_product_id', true );
                $product_title = $product_id ? get_the_title( $product_id ) : false;
                if ( $product_title ) {
                    echo '<a href="' . esc_url( get_edit_post_link( $product_id ) ) . '" target="_blank">' . esc_html( $product_title ) . '</a>';
                } else {
                    echo __( 'N/A', 'munchmakers-custom-request' );
                }
                break;
            
            // case 'price_quote':
            //     $quote = get_post_meta( $post_id, '_price_quote', true );
            //     echo $quote ? esc_html( wc_price( $quote ) ) : '&#8212;';
            //     break;

            case 'request_date':
                echo esc_html( get_the_date( '', $post_id ) ); // Uses WP date format
                break;
        }
    }

    /**
     * Make custom columns sortable.
     */
    public function set_custom_sortable_columns( $columns ) {
        $columns['request_title']  = 'title'; // Sort by post title
        $columns['request_status'] = '_request_status'; // Sort by meta value
        $columns['request_date']   = 'date'; // Sort by post date (WordPress default)
        // $columns['price_quote']    = '_price_quote'; // Sort by meta value (numeric)
        return $columns;
    }
    
    // If sorting by meta value, you might need to hook into 'pre_get_posts'
    // to set 'meta_key' and 'orderby' => 'meta_value_num' or 'meta_value'.
    // Example:
    // add_action( 'pre_get_posts', array( $this, 'sort_mcr_request_columns' ) );
    // public function sort_mcr_request_columns( $query ) {
    //     if ( ! is_admin() || ! $query->is_main_query() ) {
    //         return;
    //     }
    //     if ( MCR_POST_TYPE === $query->get('post_type') ) {
    //         $orderby = $query->get('orderby');
    //         if ( '_request_status' === $orderby ) {
    //             $query->set('meta_key', '_request_status');
    //             $query->set('orderby', 'meta_value');
    //         } elseif ( '_price_quote' === $orderby ) {
    //             $query->set('meta_key', '_price_quote');
    //             $query->set('orderby', 'meta_value_num');
    //         }
    //     }
    // }


    /**
     * Add the plugin settings page under the CPT menu.
     */
    public function add_settings_page() {
        add_submenu_page(
            'edit.php?post_type=' . MCR_POST_TYPE, // Parent slug
            __( 'MunchMakers Request Settings', 'munchmakers-custom-request' ), // Page title
            __( 'Settings', 'munchmakers-custom-request' ), // Menu title
            'manage_options', // Capability
            'mcr-request-settings', // Menu slug
            array( $this, 'render_settings_page' ) // Callback function
        );
    }

    /**
     * Render the settings page content.
     */
    public function render_settings_page() {
        ?>
        <div class="wrap mcr-settings-wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <?php settings_errors(); // Display any settings errors ?>
            
            <?php
            // Optional: Add tabs for better organization if settings grow
            // $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'main_settings';
            ?>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'mcr_settings_group' ); // Option group
                // if ( $active_tab == 'main_settings' ) {
                //     do_settings_sections( 'mcr-settings-main-page' ); // Section page for main settings
                // } else {
                //     do_settings_sections( 'mcr-settings-emails-page' ); // Section page for email settings
                // }
                do_settings_sections( 'mcr-settings-page' ); // Use single page for all sections for now

                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register the settings with the Settings API.
     */
    public function register_settings() {
        register_setting( 
            'mcr_settings_group', // Option group
            'mcr_options', // Option name
            array( $this, 'sanitize_settings' ) // Sanitize callback
        );

        // Main Settings Section
        add_settings_section(
            'mcr_main_settings_section', // ID
            __( 'Main Settings', 'munchmakers-custom-request' ), // Title
            null, // Callback for section description (optional)
            'mcr-settings-page' // Page to display on
        );

        add_settings_field(
            'recipient_email',
            __( 'Admin Notification Email', 'munchmakers-custom-request' ),
            array( $this, 'render_text_field' ),
            'mcr-settings-page',
            'mcr_main_settings_section',
            array(
                'label_for'   => 'mcr_options_recipient_email',
                'option_name' => 'recipient_email',
                'type'        => 'email',
                'default'     => get_option( 'admin_email' ),
                'description' => __( 'Admin email for new request notifications. Defaults to site admin email.', 'munchmakers-custom-request' )
            )
        );
        
        add_settings_field(
            'tracker_page_slug',
            __( 'Tracker Page Slug', 'munchmakers-custom-request' ),
            array( $this, 'render_text_field' ),
            'mcr-settings-page',
            'mcr_main_settings_section',
            array(
                'label_for'   => 'mcr_options_tracker_page_slug',
                'option_name' => 'tracker_page_slug',
                'default'     => 'track-your-order',
                'description' => __( 'The slug of the page where the <code>[mcr_request_tracker]</code> shortcode is used. Ensure this page exists.', 'munchmakers-custom-request' )
            )
        );

        add_settings_field(
            'button_text',
            __( 'Default Button Text', 'munchmakers-custom-request' ),
            array( $this, 'render_text_field' ),
            'mcr-settings-page',
            'mcr_main_settings_section',
            array(
                'label_for'   => 'mcr_options_button_text',
                'option_name' => 'button_text',
                'default'     => __( 'Request a Custom Design', 'munchmakers-custom-request' ),
                'description' => __( 'Default text for the <code>[munchmakers_button]</code> shortcode.', 'munchmakers-custom-request' )
            )
        );

        add_settings_field(
            'slack_webhook_url',
            __( 'Slack Webhook URL', 'munchmakers-custom-request' ),
            array( $this, 'render_text_field' ),
            'mcr-settings-page',
            'mcr_main_settings_section',
            array(
                'label_for'   => 'mcr_options_slack_webhook_url',
                'option_name' => 'slack_webhook_url',
                'type'        => 'url',
                'placeholder' => 'https://hooks.slack.com/services/...',
                'description' => __( 'Enter your Slack Incoming Webhook URL for notifications.', 'munchmakers-custom-request' )
            )
        );

        add_settings_field(
            'enable_logging',
            __( 'Enable Debug Logging', 'munchmakers-custom-request' ),
            array( $this, 'render_checkbox_field' ),
            'mcr-settings-page',
            'mcr_main_settings_section',
            array(
                'label_for'   => 'mcr_options_enable_logging',
                'option_name' => 'enable_logging',
                'description' => sprintf(
                    // translators: %s: Path to the log directory.
                    __( 'Enable detailed logging to %s.', 'munchmakers-custom-request' ),
                    '<code>wp-content/uploads/munchmakers_logs/</code>'
                )
            )
        );
        
        // Optional: Add log level setting if mcr_log supports it
        // add_settings_field(
        //     'log_level',
        //     __( 'Logging Level', 'munchmakers-custom-request' ),
        //     array( $this, 'render_select_field' ),
        //     'mcr-settings-page',
        //     'mcr_main_settings_section',
        //     array(
        //         'label_for'   => 'mcr_options_log_level',
        //         'option_name' => 'log_level',
        //         'options'     => array( 'DEBUG' => 'Debug', 'INFO' => 'Info', 'WARNING' => 'Warning', 'ERROR' => 'Error' ),
        //         'default'     => 'INFO',
        //         'description' => __( 'Minimum severity of messages to log. Only active if logging is enabled.', 'munchmakers-custom-request' )
        //     )
        // );


        // Email Templates Section
        add_settings_section(
            'mcr_email_templates_section',
            __( 'Email Templates', 'munchmakers-custom-request' ),
            array( $this, 'render_email_templates_section_text' ),
            'mcr-settings-page'
        );

        $email_fields = array(
            'customer_confirmation' => __('New Request: Customer Confirmation', 'munchmakers-custom-request'),
            'new_user_account'      => __('New User Account Created', 'munchmakers-custom-request'),
            // Add proof sent email specifically
            'proof_sent_email'      => __('Proof Sent to Customer', 'munchmakers-custom-request'),
        );
        $statuses_for_templates = mcr_get_request_statuses();
        // Remove 'new' and 'awaiting_customer' as they are covered by confirmation/proof_sent
        unset($statuses_for_templates['new']); 
        // We will handle 'awaiting_customer' via 'proof_sent_email' or a generic status update if changed manually.

        foreach ($statuses_for_templates as $status_key => $status_label) {
            $email_fields["status_{$status_key}"] = sprintf(__('Status Update: %s', 'munchmakers-custom-request'), $status_label);
        }
        
        foreach ($email_fields as $key => $label) {
            add_settings_field(
                "{$key}_subject",
                sprintf('%s: %s', $label, __('Subject', 'munchmakers-custom-request')),
                array( $this, 'render_text_field' ),
                'mcr-settings-page',
                'mcr_email_templates_section',
                array( 'option_name' => "{$key}_subject", 'description' => __('Subject line for this email.', 'munchmakers-custom-request') )
            );
            add_settings_field(
                "{$key}_body",
                 sprintf('%s: %s', $label, __('Body (HTML)', 'munchmakers-custom-request')),
                array( $this, 'render_wp_editor_field' ),
                'mcr-settings-page',
                'mcr_email_templates_section',
                array( 'option_name' => "{$key}_body", 'description' => __('Main content for this email. Use HTML.', 'munchmakers-custom-request') )
            );
        }
    }

    /** Generic text field renderer */
    public function render_text_field( $args ) {
        $options = get_option( 'mcr_options' );
        $option_name = $args['option_name'];
        $value = isset( $options[$option_name] ) ? $options[$option_name] : (isset($args['default']) ? $args['default'] : '');
        $type = isset($args['type']) ? $args['type'] : 'text';
        $placeholder = isset($args['placeholder']) ? $args['placeholder'] : '';
        echo "<input type='{$type}' id='mcr_options_{$option_name}' name='mcr_options[{$option_name}]' value='" . esc_attr( $value ) . "' class='regular-text' placeholder='" . esc_attr($placeholder) . "'>";
        if (isset($args['description'])) {
            echo '<p class="description">' . wp_kses_post( $args['description'] ) . '</p>';
        }
    }

    /** Generic checkbox field renderer */
    public function render_checkbox_field( $args ) {
        $options = get_option( 'mcr_options' );
        $option_name = $args['option_name'];
        $checked = isset( $options[$option_name] ) && $options[$option_name] ? 'checked="checked"' : '';
        echo "<input type='checkbox' id='mcr_options_{$option_name}' name='mcr_options[{$option_name}]' value='1' {$checked}>";
        if (isset($args['description'])) {
             echo '<label for="mcr_options_'.esc_attr($option_name).'"> ' . wp_kses_post( $args['description'] ) . '</label>';
        }
    }
    
    /** Generic select field renderer */
    // public function render_select_field( $args ) {
    //     $options_val = get_option( 'mcr_options' );
    //     $option_name = $args['option_name'];
    //     $current_value = isset( $options_val[$option_name] ) ? $options_val[$option_name] : (isset($args['default']) ? $args['default'] : '');
    //     echo "<select id='mcr_options_{$option_name}' name='mcr_options[{$option_name}]'>";
    //     foreach ($args['options'] as $value => $label) {
    //         echo "<option value='" . esc_attr($value) . "' " . selected($current_value, $value, false) . ">" . esc_html($label) . "</option>";
    //     }
    //     echo "</select>";
    //     if (isset($args['description'])) {
    //         echo '<p class="description">' . wp_kses_post( $args['description'] ) . '</p>';
    //     }
    // }


    /** WP Editor field renderer */
    public function render_wp_editor_field( $args ) {
        $options = get_option( 'mcr_options' );
        $option_name = $args['option_name'];
        $content = isset( $options[$option_name] ) ? $options[$option_name] : (isset($args['default']) ? $args['default'] : '');
        wp_editor(
            $content,
            "mcr_options_{$option_name}", // HTML ID
            array(
                'textarea_name' => "mcr_options[{$option_name}]", // Name attribute for the textarea
                'media_buttons' => false, // Disable media buttons
                'textarea_rows' => 8,
                'teeny' => true, // Use a simpler editor interface
                'quicktags' => true,
            )
        );
        if (isset($args['description'])) {
            echo '<p class="description">' . wp_kses_post( $args['description'] ) . '</p>';
        }
    }


    public function render_email_templates_section_text() {
        echo '<p>' . esc_html__( 'Customize the content of emails sent by the plugin. Use the following placeholders:', 'munchmakers-custom-request' ) . '</p>';
        echo '<ul>';
        echo '<li><code>{customer_name}</code> - ' . esc_html__('The customer\'s name.', 'munchmakers-custom-request') . '</li>';
        echo '<li><code>{product_name}</code> - ' . esc_html__('The name of the product related to the request.', 'munchmakers-custom-request') . '</li>';
        echo '<li><code>{request_id}</code> - ' . esc_html__('The ID of the custom request.', 'munchmakers-custom-request') . '</li>';
        echo '<li><code>{tracking_link}</code> - ' . esc_html__('The direct link to track the request status.', 'munchmakers-custom-request') . '</li>';
        echo '<li><code>{site_name}</code> - ' . esc_html__('Your website name.', 'munchmakers-custom-request') . '</li>';
        echo '<li><code>{site_url}</code> - ' . esc_html__('Your website URL.', 'munchmakers-custom-request') . '</li>';
        echo '</ul>';
        echo '<p><strong>' . esc_html__('For New User Account Email additionally use:', 'munchmakers-custom-request') . '</strong></p>';
        echo '<ul>';
        echo '<li><code>{username}</code> - ' . esc_html__('The new user\'s username.', 'munchmakers-custom-request') . '</li>';
        echo '<li><code>{password_set_link}</code> - ' . esc_html__('Link for the user to set their password.', 'munchmakers-custom-request') . '</li>';
        echo '<li><code>{my_account_link}</code> - ' . esc_html__('Link to the WooCommerce My Account page (if WC active).', 'munchmakers-custom-request') . '</li>';
        echo '</ul>';
         echo '<p><strong>' . esc_html__('For Proof Sent Email additionally use:', 'munchmakers-custom-request') . '</strong></p>';
        echo '<ul>';
        echo '<li><code>{proof_file_name}</code> - ' . esc_html__('The filename of the attached proof.', 'munchmakers-custom-request') . '</li>';
        echo '</ul>';
    }


    public function sanitize_settings( $input ) {
        $new_input = array();
        $options_cache = get_option('mcr_options'); // Get existing options to preserve untouched ones

        if ( isset( $input['recipient_email'] ) ) {
            $new_input['recipient_email'] = sanitize_email( $input['recipient_email'] );
        } else {
            $new_input['recipient_email'] = $options_cache['recipient_email'] ?? get_option( 'admin_email' );
        }
        
        if ( isset( $input['tracker_page_slug'] ) ) {
            $new_input['tracker_page_slug'] = sanitize_key( $input['tracker_page_slug'] ); // sanitize_key for slugs
             if(empty($new_input['tracker_page_slug'])) $new_input['tracker_page_slug'] = 'track-your-order'; // default if empty
        } else {
            $new_input['tracker_page_slug'] = $options_cache['tracker_page_slug'] ?? 'track-your-order';
        }

        if ( isset( $input['button_text'] ) ) {
            $new_input['button_text'] = sanitize_text_field( $input['button_text'] );
        } else {
            $new_input['button_text'] = $options_cache['button_text'] ?? __( 'Request a Custom Design', 'munchmakers-custom-request' );
        }

        if ( isset( $input['slack_webhook_url'] ) ) {
            $new_input['slack_webhook_url'] = esc_url_raw( trim( $input['slack_webhook_url'] ) );
        } else {
            $new_input['slack_webhook_url'] = $options_cache['slack_webhook_url'] ?? '';
        }
        
        $new_input['enable_logging'] = isset( $input['enable_logging'] ) ? 1 : 0;
        // $new_input['log_level'] = isset( $input['log_level'] ) ? sanitize_text_field( $input['log_level'] ) : ($options_cache['log_level'] ?? 'INFO');


        // Sanitize all email template fields
        $email_template_keys = array('customer_confirmation', 'new_user_account', 'proof_sent_email');
        $statuses = mcr_get_request_statuses();
        unset($statuses['new']); // 'new' is covered by customer_confirmation

        foreach (array_keys($statuses) as $status_key) {
            $email_template_keys[] = "status_{$status_key}";
        }

        foreach ($email_template_keys as $key) {
            if ( isset( $input["{$key}_subject"] ) ) {
                $new_input["{$key}_subject"] = sanitize_text_field( $input["{$key}_subject"] );
            } else if (isset($options_cache["{$key}_subject"])) {
                $new_input["{$key}_subject"] = $options_cache["{$key}_subject"];
            }

            if ( isset( $input["{$key}_body"] ) ) {
                $new_input["{$key}_body"] = wp_kses_post( $input["{$key}_body"] ); // Allows HTML
            } else if (isset($options_cache["{$key}_body"])) {
                 $new_input["{$key}_body"] = $options_cache["{$key}_body"];
            }
        }
        mcr_log('Plugin settings sanitized and saved.', 'INFO', __FUNCTION__);
        return $new_input;
    }

} // ─── End of class MunchMakers_Custom_Request_Admin