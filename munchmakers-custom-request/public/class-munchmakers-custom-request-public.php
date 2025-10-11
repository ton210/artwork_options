<?php
/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and registers all public-facing
 * hooks, shortcodes, and AJAX handlers.
 *
 * @package    MunchMakers_Custom_Request
 * @subpackage MunchMakers_Custom_Request/public
 * @since      1.0.0
 */
class MunchMakers_Custom_Request_Public {

    private $plugin_name;
    private $version;
    private static $shortcode_button_used_on_page = false; // Flag to check if any button shortcode is used
    private static $inline_form_rendered_on_page = false; // Flag for inline form
    private $tracker_page_slug; // For configurable tracker page slug

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        // Get tracker page slug from options, default if not set
        $options = get_option('mcr_options');
        $this->tracker_page_slug = !empty($options['tracker_page_slug']) ? $options['tracker_page_slug'] : 'track-your-order';
    }

    /**
     * Enqueue styles and scripts for the public-facing side of the site.
     *
     * @since 1.0.0
     * @updated 1.2.4 (More comprehensive asset loading for various page types)
     */
    public function enqueue_styles_and_scripts() {
        if (function_exists('mcr_log')) { mcr_log('enqueue_styles_and_scripts called.', 'DEBUG', __FUNCTION__); }

        $load_assets = false;
        $current_post_id = get_the_ID(); // Get current post ID if available. False if not on a singular page.
        $tracker_page_slug_from_options = !empty($this->tracker_page_slug) ? $this->tracker_page_slug : 'track-your-order';
        
        // 1. Always load for the designated tracker page
        if ($current_post_id && function_exists('is_page') && is_page($tracker_page_slug_from_options)) {
            $load_assets = true;
            if (function_exists('mcr_log')) { mcr_log('Assets WILL be loaded: On designated tracker page.', 'DEBUG', __FUNCTION__); }
        }

        // 2. Check for shortcodes in post_content of singular pages (if not already loading)
        // This is primarily for buttons/trackers embedded directly in page/post editor content.
        if (!$load_assets && is_singular() && $current_post_id) {
            global $post; // $post should be set on singular pages
            if (is_a($post, 'WP_Post')) {
                if (has_shortcode($post->post_content, 'munchmakers_button') || has_shortcode($post->post_content, 'munchmakers_header_button')) {
                    $load_assets = true;
                    self::$shortcode_button_used_on_page = true; // A button is detected
                    if (function_exists('mcr_log')) { mcr_log('Assets WILL be loaded: Button shortcode found in post_content.', 'DEBUG', __FUNCTION__); }
                } elseif (has_shortcode($post->post_content, 'mcr_request_tracker')) {
                     $load_assets = true; // For tracker styles even if not the designated tracker page
                     if (function_exists('mcr_log')) { mcr_log('Assets WILL be loaded: Tracker shortcode found in post_content.', 'DEBUG', __FUNCTION__); }
                }
            }
        }

        // 3. Broader heuristic for pages where a global header button (or other plugin buttons) are likely used,
        // especially when shortcodes are in theme templates (e.g., header.php) or page builder templates.
        $is_general_content_page = false;
        if (is_front_page() || is_home() || is_singular() || is_archive()) {
            // is_singular() covers single posts, pages, CPTs (like single products, single blog posts).
            // is_archive() covers blog archives, category/tag archives, CPT archives (like shop, product categories/tags).
            $is_general_content_page = true;
        }

        if (class_exists('WooCommerce')) {
            // Specific WooCommerce pages that might not be caught by the general conditions above,
            // or where we want to be absolutely sure if a header button is site-wide.
            if (is_cart() || is_checkout() || is_account_page() || is_shop() || is_product_category() || is_product_tag()) {
                $is_general_content_page = true;
            }
             // is_woocommerce() is a broader check that could also be used if the above is not enough.
             // if (is_woocommerce()) $is_general_content_page = true;
        }

        if ($is_general_content_page) {
            // If it's a type of page where a header (and thus a header button) is commonly displayed globally.
            $load_assets = true;
            self::$shortcode_button_used_on_page = true; // CRITICAL: Assume a button might be present, so popup HTML is loaded.
            if (function_exists('mcr_log')) {
                mcr_log('Assets WILL be loaded (heuristic): Common page type (Front/Home/Singular/Archive/Woo Carts/Checkout/Account/Shop/Tax). Set shortcode_button_used_on_page to true.', 'DEBUG', __FUNCTION__);
            }
        }

        // Allow external override via filter for $load_assets
        $load_assets = apply_filters('mcr_should_load_public_assets', $load_assets, $current_post_id);

        // If assets are forced to load via the filter, also ensure the button flag is set if intended for button functionality.
        // This is important if the filter makes $load_assets true, but the heuristics above didn't set self::$shortcode_button_used_on_page.
        if ($load_assets && !self::$shortcode_button_used_on_page && apply_filters('mcr_force_assume_button_on_filtered_asset_load', true) ) {
            self::$shortcode_button_used_on_page = true;
            if (function_exists('mcr_log')) { mcr_log('shortcode_button_used_on_page set to true due to mcr_force_assume_button_on_filtered_asset_load filter.', 'DEBUG', __FUNCTION__); }
        }


        if ($load_assets) {
            wp_enqueue_style( $this->plugin_name, MCR_PLUGIN_URL . 'public/assets/css/munchmakers-custom-request-public.css', array(), $this->version, 'all' );
            wp_enqueue_script( $this->plugin_name, MCR_PLUGIN_URL . 'public/assets/js/munchmakers-custom-request-public.js', array( 'jquery' ), $this->version, true );
            
            wp_localize_script( $this->plugin_name, 'mcr_public_ajax', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'mcr_handle_custom_request_nonce' ),
                'error_message' => __( 'An unexpected error occurred. Please try again.', 'munchmakers-custom-request' ),
                'fill_required_fields_message' => __('Please fill in all required fields.', 'munchmakers-custom-request'),
                'debug_mode' => (defined('WP_DEBUG') && WP_DEBUG),
            ) );
            if (function_exists('mcr_log')) { mcr_log('Public assets (CSS & JS) ACTUALLY ENQUEUED.', 'INFO', __FUNCTION__); }
        } else {
            if (function_exists('mcr_log')) { mcr_log('Public assets NOT enqueued for current page.', 'DEBUG', __FUNCTION__); }
        }
    }

    /**
     * Register all public-facing shortcodes.
     *
     * @since 1.0.0
     */
    public function register_shortcodes() {
        add_shortcode( 'munchmakers_button', array( $this, 'render_munchmakers_button_shortcode' ) );
        add_shortcode( 'munchmakers_header_button', array( $this, 'render_munchmakers_header_button_shortcode' ) );
        add_shortcode( 'mcr_request_tracker', array( $this, 'render_request_tracker_shortcode' ) );
        if (function_exists('mcr_log')) {
            mcr_log('Shortcodes registered: [munchmakers_button], [munchmakers_header_button], [mcr_request_tracker].', 'DEBUG', __FUNCTION__);
        }
    }

    /**
     * Render the [munchmakers_button] shortcode output.
     * This will display the "Request a Custom Design" button with a tooltip.
     *
     * @since 1.0.0
     * @param array $atts Shortcode attributes (e.g., color, text).
     * @return string HTML output for the button.
     */
    public function render_munchmakers_button_shortcode( $atts ) {
        self::$shortcode_button_used_on_page = true; 

        $atts = shortcode_atts( array(
            'text' => '', 
            'color' => 'black', 
            'class' => '', 
        ), $atts, 'munchmakers_button' );

        $options = get_option('mcr_options');
        $button_text_setting = isset($options['button_text']) && !empty(trim($options['button_text'])) ? $options['button_text'] : __('Request a Custom Design', 'munchmakers-custom-request');
        $button_text = !empty(trim($atts['text'])) ? $atts['text'] : $button_text_setting;
        
        $button_color_class = 'mcr-button-' . sanitize_html_class($atts['color']);
        $additional_classes = sanitize_html_class($atts['class']);
        $tooltip_text = __("Unsure about the design? Our experts create it for you FREE! Get a complimentary mockup in ~24 hrs & track its progress. Just share your ideas in detail!", 'munchmakers-custom-request');

        ob_start();
        ?>
        <button type="button" 
                id="mcr-open-popup-btn-<?php echo esc_attr(uniqid('main_')); ?>" 
                class="mcr-request-button <?php echo esc_attr($button_color_class); ?> <?php echo esc_attr($additional_classes); ?> mcr-popup-trigger"
                data-tooltip="<?php echo esc_attr($tooltip_text); ?>"
                aria-haspopup="dialog" 
                aria-expanded="false" 
                aria-controls="mcr-popup-overlay">
            <?php echo esc_html( $button_text ); ?>
        </button>
        <?php
        if (function_exists('mcr_log')) { mcr_log('Shortcode [munchmakers_button] rendered. Color: ' . $atts['color'] . ', Text: ' . $button_text, 'DEBUG', __FUNCTION__); }
        return ob_get_clean();
    }

    /**
     * Render the [munchmakers_header_button] shortcode output.
     *
     * @since 1.1.0
     * @param array $atts Shortcode attributes (e.g., text, class).
     * @return string HTML output for the header button.
     */
    public function render_munchmakers_header_button_shortcode( $atts ) {
        self::$shortcode_button_used_on_page = true; 

        $atts = shortcode_atts( array(
            'text' => __('Request Your Free Mockup With Your Logo!', 'munchmakers-custom-request'), 
            'class' => '', 
        ), $atts, 'munchmakers_header_button' );
        
        $button_text = !empty(trim($atts['text'])) ? $atts['text'] : __('Request Your Free Mockup With Your Logo!', 'munchmakers-custom-request');
        $additional_classes = sanitize_html_class($atts['class']);

        ob_start();
        ?>
        <button type="button" 
                id="mcr-open-popup-btn-header-<?php echo esc_attr(uniqid('header_')); ?>"
                class="mcr-header-request-button <?php echo esc_attr($additional_classes); ?> mcr-popup-trigger"
                aria-haspopup="dialog" 
                aria-expanded="false" 
                aria-controls="mcr-popup-overlay">
            <span class="mcr-header-button-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="1.2em" height="1.2em" style="vertical-align: middle; margin-right: 8px;" aria-hidden="true">
                    <path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM14 13v4h-4v-4H7l5-5 5 5h-3z"/>
                </svg>
            </span>
            <?php echo esc_html( $button_text ); ?>
        </button>
        <?php
        if (function_exists('mcr_log')) { mcr_log('Shortcode [munchmakers_header_button] rendered. Text: ' . $button_text, 'DEBUG', __FUNCTION__); }
        return ob_get_clean();
    }


    /**
     * Conditionally add the popup HTML structure to the footer.
     * Only adds if a button shortcode was used AND we are NOT on the tracker page showing the inline form.
     *
     * @since 1.1.0
     * @updated 1.2.1 (Using configurable tracker_page_slug)
     */
    public function add_popup_html_if_needed() {
        $tracker_page_slug_from_options = !empty($this->tracker_page_slug) ? $this->tracker_page_slug : 'track-your-order';
        $is_tracker_page = (function_exists('is_page') && is_page($tracker_page_slug_from_options));
        $is_tracker_page_showing_inline_form = $is_tracker_page && empty($_GET['key']);

        if ( self::$shortcode_button_used_on_page && !$is_tracker_page_showing_inline_form ) {
            if (did_action('wp_footer') > 0 || current_filter() === 'wp_footer') { 
                 if (function_exists('mcr_log')) { mcr_log('Popup HTML being injected into footer because a button shortcode was used and not on tracker page with inline form.', 'DEBUG', __FUNCTION__); }
                 require_once MCR_PLUGIN_DIR . 'public/partials/munchmakers-custom-request-popup-form.php';
            }
        } else {
            if (function_exists('mcr_log')) { 
                $log_msg = 'Popup HTML NOT injected. ';
                $log_msg .= 'shortcode_button_used_on_page: ' . (self::$shortcode_button_used_on_page ? 'Yes' : 'No') . '. ';
                $log_msg .= 'is_tracker_page_showing_inline_form: ' . ($is_tracker_page_showing_inline_form ? 'Yes' : 'No') . '.';
                mcr_log($log_msg, 'DEBUG', __FUNCTION__);
            }
        }
    }

    /**
     * Renders the [mcr_request_tracker] shortcode output.
     *
     * @since 1.0.0
     * @return string HTML output for the tracking page or inline form.
     */
    public function render_request_tracker_shortcode() {
        if (function_exists('mcr_log')) { mcr_log('Shortcode [mcr_request_tracker] rendering started.', 'DEBUG', __FUNCTION__); }
        
        if ( !isset($_GET['key']) || empty( trim($_GET['key']) ) ) {
            if (function_exists('mcr_log')) { mcr_log('Request Tracker: No key provided. Rendering inline request form.', 'INFO', __FUNCTION__); }
            self::$inline_form_rendered_on_page = true; 
            return $this->render_inline_request_form();
        }

        $access_key = sanitize_text_field( $_GET['key'] );
        if (function_exists('mcr_log')) { mcr_log("Request Tracker: Access key '{$access_key}' received.", 'DEBUG', __FUNCTION__); }

        $args = array(
            'post_type'      => MCR_POST_TYPE,
            'meta_key'       => '_request_access_key',
            'meta_value'     => $access_key,
            'posts_per_page' => 1,
            'post_status'    => 'publish', 
        );
        $requests_query = new WP_Query( $args ); 

        if ( ! $requests_query->have_posts() ) {
            if (function_exists('mcr_log')) { mcr_log("Request Tracker: No request found for key '{$access_key}'.", 'INFO', __FUNCTION__); }
            $error_message = '<div class="mcr-tracker-wrap"><p class="mcr-tracker-error-message">' . esc_html__('Sorry, we could not find a request with that key or it is no longer available.', 'munchmakers-custom-request') . '</p></div>';
            return $error_message;
        }
        
        ob_start();
        while ( $requests_query->have_posts() ) {
            $requests_query->the_post(); 
            if (function_exists('mcr_log')) { mcr_log("Request Tracker: Rendering status for request ID #" . get_the_ID(), 'INFO', __FUNCTION__); }
            include( MCR_PLUGIN_DIR . 'public/partials/request-status-page.php' );
        }
        wp_reset_postdata(); 
        return ob_get_clean();
    }

    /**
     * Renders the inline request form.
     *
     * @since 1.2.0
     * @return string HTML for the inline form.
     */
    private function render_inline_request_form() {
        ob_start();
        echo '<div class="mcr-inline-form-wrapper">'; 
        require_once MCR_PLUGIN_DIR . 'public/partials/munchmakers-custom-request-inline-form.php';
        echo '</div>';
        return ob_get_clean();
    }


    /**
     * Handles the AJAX form submission for custom requests.
     *
     * @since 1.0.0
     * @updated 1.2.4 
     */
    public function handle_ajax_form_submission() {
        check_ajax_referer( 'mcr_handle_custom_request_nonce', 'nonce' ); 
        mcr_log('AJAX: Form submission started.', 'INFO', __FUNCTION__);
        
        $customer_ip = 'IP Not Found';
        if ( ! empty( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) { $customer_ip = sanitize_text_field( $_SERVER['HTTP_CF_CONNECTING_IP'] ); }
        elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) { $customer_ip = sanitize_text_field( explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0] ); } 
        elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) { $customer_ip = sanitize_text_field( $_SERVER['REMOTE_ADDR'] ); }
        
        $location_info = __('N/A', 'munchmakers-custom-request');
        if ( filter_var( $customer_ip, FILTER_VALIDATE_IP ) && $customer_ip !== 'IP Not Found' ) { 
            $geo_api_url = 'http://ip-api.com/json/' . $customer_ip . '?fields=status,message,country,regionName,city';
            $response = wp_remote_get( $geo_api_url, array('timeout' => 5) ); 
            if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
                $geo_data = json_decode( wp_remote_retrieve_body( $response ), true );
                if ( $geo_data && isset($geo_data['status']) && 'success' === $geo_data['status'] ) { 
                    $location_parts = array_filter([$geo_data['city'] ?? null, $geo_data['regionName'] ?? null, $geo_data['country'] ?? null]);
                    if (!empty($location_parts)) {
                        $location_info = implode( ', ', $location_parts );
                    }
                }
            } else {
                mcr_log('Geolocation lookup failed: ' . (is_wp_error($response) ? $response->get_error_message() : 'HTTP error ' . wp_remote_retrieve_response_code( $response )), 'WARNING', __FUNCTION__);
            }
        }
        mcr_log("Customer IP: {$customer_ip}, Location: {$location_info}", 'DEBUG', __FUNCTION__);

        $name            = isset( $_POST['mcr_name'] ) ? sanitize_text_field( wp_unslash( $_POST['mcr_name'] ) ) : '';
        $email           = isset( $_POST['mcr_email'] ) ? sanitize_email( wp_unslash( $_POST['mcr_email'] ) ) : '';
        $phone           = isset( $_POST['mcr_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['mcr_phone'] ) ) : ''; 
        $request_details = isset( $_POST['mcr_request_details'] ) ? sanitize_textarea_field( wp_unslash( $_POST['mcr_request_details'] ) ) : '';
        $product_id      = isset( $_POST['mcr_product_id'] ) ? absint( $_POST['mcr_product_id'] ) : 0; 

        if ( empty( $email ) || ! is_email( $email ) ) { 
            mcr_log("AJAX: Form validation failed - invalid email: {$email}", "WARNING", __FUNCTION__); 
            wp_send_json_error( array( 'message' => __( 'A valid email address is required.', 'munchmakers-custom-request' ) ) ); 
        }
        if ( empty( $request_details ) ) {
             mcr_log("AJAX: Form validation failed - request details empty.", "WARNING", __FUNCTION__); 
            wp_send_json_error( array( 'message' => __( 'Please describe your request.', 'munchmakers-custom-request' ) ) );
        }

        $file_url = '';
        if ( isset( $_FILES['mcr_image_upload'] ) && $_FILES['mcr_image_upload']['error'] === UPLOAD_ERR_OK ) {
             $allowed_mime_types = array( 
                'jpg|jpeg|jpe' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif',
                'svg' => 'image/svg+xml', 'pdf' => 'application/pdf',
                'ai' => 'application/postscript', 'eps' => 'application/postscript',
            );
            $max_file_size = apply_filters('mcr_customer_upload_max_file_size', 5 * 1024 * 1024); 
            if ($_FILES['mcr_image_upload']['size'] > $max_file_size) {
                mcr_log("AJAX: File upload error: File too large. Size: " . $_FILES['mcr_image_upload']['size'], "ERROR", __FUNCTION__);
                wp_send_json_error( array( 'message' => sprintf(__('File is too large. Maximum size is %s.', 'munchmakers-custom-request'), size_format($max_file_size)) ) );
            }

            $upload_result = mcr_handle_file_upload( $_FILES['mcr_image_upload'], 'munchmakers_requests', $allowed_mime_types );

            if ( isset($upload_result['url']) && !isset($upload_result['error']) ) { 
                $file_url = $upload_result['url']; 
                mcr_log("AJAX: File uploaded successfully via helper: {$file_url}", "INFO", __FUNCTION__);
            } else { 
                $error_msg = $upload_result['error'] ?? __('Unknown upload processing error.', 'munchmakers-custom-request');
                mcr_log("AJAX: File upload error via helper: " . $error_msg, "ERROR", __FUNCTION__); 
                wp_send_json_error( array( 'message' => __('File upload error: ', 'munchmakers-custom-request') . $error_msg ) ); 
            }
        }
        
        $access_key = wp_generate_uuid4(); 
        $post_title = sprintf(
            __('Request from %1$s - %2$s', 'munchmakers-custom-request'),
            ( !empty($name) ? esc_html($name) : esc_html($email) ),
            date_i18n(get_option('date_format'))
        );
        
        $new_request_id = wp_insert_post( array(
            'post_title'   => $post_title,
            'post_content' => $request_details, 
            'post_status'  => 'publish', 
            'post_type'    => MCR_POST_TYPE,
        ) );

        if ( is_wp_error( $new_request_id ) ) { 
            mcr_log('AJAX: Error creating CPT: ' . $new_request_id->get_error_message(), 'ERROR', __FUNCTION__); 
            wp_send_json_error( array( 'message' => __('Error creating your request. Please try again.', 'munchmakers-custom-request') ) ); 
        }
        mcr_log("AJAX: New request CPT #{$new_request_id} created.", 'INFO', __FUNCTION__);

        $user_account_result = mcr_maybe_create_customer_account( $email, $name, $new_request_id ); 
        $user_id = 0;
        if ( !is_wp_error($user_account_result['user_id']) && $user_account_result['user_id'] > 0 ) {
            $user_id = $user_account_result['user_id'];
            wp_update_post(array('ID' => $new_request_id, 'post_author' => $user_id));
            mcr_log("Request #{$new_request_id} linked to user #{$user_id}.", 'INFO', __FUNCTION__);
        } else if (is_wp_error($user_account_result['user_id'])) {
             mcr_log("AJAX: User account handling error for request #{$new_request_id}: " . $user_account_result['user_id']->get_error_message(), 'ERROR', __FUNCTION__);
        }
        
        update_post_meta( $new_request_id, '_request_status', 'new' );
        update_post_meta( $new_request_id, '_request_access_key', $access_key );
        update_post_meta( $new_request_id, '_customer_name', $name );
        update_post_meta( $new_request_id, '_customer_email', $email );
        update_post_meta( $new_request_id, '_customer_phone', $phone );
        update_post_meta( $new_request_id, '_product_id', $product_id );
        update_post_meta( $new_request_id, '_customer_ip', $customer_ip );
        update_post_meta( $new_request_id, '_customer_location', $location_info );
        if (!empty($file_url)) { 
            update_post_meta( $new_request_id, '_customer_image_url', $file_url );
        }
        if ($user_id > 0) {
            update_post_meta( $new_request_id, '_customer_user_id', $user_id );
        }

        $product_name_for_email = $product_id ? get_the_title( $product_id ) : __('N/A', 'munchmakers-custom-request');

        if (function_exists('mcr_send_customer_confirmation_email')) {
            mcr_send_customer_confirmation_email( $new_request_id, $email, $name, $product_name_for_email, $access_key );
        }

        $admin_email_options = get_option('mcr_options');
        $admin_notify_email = !empty($admin_email_options['recipient_email']) && is_email($admin_email_options['recipient_email']) 
                            ? $admin_email_options['recipient_email'] 
                            : get_option('admin_email');
        $admin_edit_link = admin_url('post.php?post=' . $new_request_id . '&action=edit');
        
        $admin_subject = sprintf(__('New MunchMakers Custom Request: %1$s (#%2$d)', 'munchmakers-custom-request'), $product_name_for_email, $new_request_id);
        
        $admin_body  = "<h3>" . __('New Request Received', 'munchmakers-custom-request') . " (#{$new_request_id})</h3>";
        $admin_body .= "<p><strong>" . __('Customer:', 'munchmakers-custom-request') . "</strong> ".esc_html($name)." (".esc_html($email).")</p>";
        if ($phone) $admin_body .= "<p><strong>" . __('Phone:', 'munchmakers-custom-request') . "</strong> ".esc_html($phone)."</p>";
        $admin_body .= "<p><strong>" . __('Product:', 'munchmakers-custom-request') . "</strong> ".esc_html($product_name_for_email)."</p>";
        if ($file_url) $admin_body .= "<p><strong>" . __('Uploaded File:', 'munchmakers-custom-request') . "</strong> <a href='".esc_url($file_url)."'>".esc_html(basename($file_url))."</a></p>";
        $admin_body .= "<p><strong>" . __('Request Details:', 'munchmakers-custom-request') . "</strong></p><div style='background:#f9f9f9;border:1px solid #eee;padding:10px;'>".wpautop(esc_html($request_details))."</div>";
        $admin_body .= "<p><strong><a href='".esc_url($admin_edit_link)."' style='padding:10px 15px;background-color:#93BC48;color:#fff;text-decoration:none;border-radius:5px;'>".__('View in Dashboard', 'munchmakers-custom-request')."</a></strong></p>";
        
        wp_mail( $admin_notify_email, $admin_subject, $admin_body, array('Content-Type: text/html; charset=UTF-8') );
        mcr_log("AJAX: Admin notification email sent for #{$new_request_id} to {$admin_notify_email}.", 'INFO', __FUNCTION__);
        
        if (function_exists('mcr_notify_slack_new_request')) { 
            mcr_notify_slack_new_request( $new_request_id ); 
        }
        
        $tracker_page_slug_from_options = !empty($this->tracker_page_slug) ? $this->tracker_page_slug : 'track-your-order';
        $redirect_url = home_url( '/' . $tracker_page_slug_from_options . '/?key=' . $access_key ); 
        mcr_log("AJAX: Form submission complete for #{$new_request_id}. Redirecting customer to {$redirect_url}.", 'INFO', __FUNCTION__);
        wp_send_json_success( array( 'redirect_url' => $redirect_url ) );
    }
}