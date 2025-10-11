<?php
/**
 * VSS Enhanced Tracking System
 * 
 * Comprehensive tracking system for product views, cart additions, questions, and issues
 * 
 * @package VendorOrderManager
 * @subpackage Modules
 * @since 7.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Trait for Enhanced Tracking System
 */
trait VSS_Tracking_System {

    /**
     * Initialize tracking system
     */
    public static function init_tracking_system() {
        // Create tracking tables
        self::create_tracking_tables();
        
        // AJAX handlers for tracking
        add_action( 'wp_ajax_vss_track_product_view', [ self::class, 'track_product_view_ajax' ] );
        add_action( 'wp_ajax_nopriv_vss_track_product_view', [ self::class, 'track_product_view_ajax' ] );
        add_action( 'wp_ajax_vss_track_cart_addition', [ self::class, 'track_cart_addition_ajax' ] );
        add_action( 'wp_ajax_nopriv_vss_track_cart_addition', [ self::class, 'track_cart_addition_ajax' ] );
        add_action( 'wp_ajax_vss_submit_product_question', [ self::class, 'submit_product_question' ] );
        add_action( 'wp_ajax_nopriv_vss_submit_product_question', [ self::class, 'submit_product_question' ] );
        add_action( 'wp_ajax_vss_report_issue', [ self::class, 'report_product_issue' ] );
        add_action( 'wp_ajax_nopriv_vss_report_issue', [ self::class, 'report_product_issue' ] );
        
        // WooCommerce hooks for automatic tracking
        add_action( 'woocommerce_add_to_cart', [ self::class, 'track_cart_addition_wc' ], 10, 6 );
        add_action( 'woocommerce_order_status_completed', [ self::class, 'track_purchase_completion' ] );
        add_action( 'woocommerce_before_single_product', [ self::class, 'enqueue_tracking_scripts' ] );
        
        // Frontend tracking scripts
        add_action( 'wp_enqueue_scripts', [ self::class, 'enqueue_frontend_tracking' ] );
        
        // Schedule analytics aggregation
        if ( ! wp_next_scheduled( 'vss_aggregate_tracking_data' ) ) {
            wp_schedule_event( time(), 'hourly', 'vss_aggregate_tracking_data' );
        }
        add_action( 'vss_aggregate_tracking_data', [ self::class, 'aggregate_tracking_data' ] );
    }

    /**
     * Create tracking database tables
     */
    public static function create_tracking_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        // Product views tracking
        $table_name = $wpdb->prefix . 'vss_product_views_detailed';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            product_id bigint(20) NOT NULL,
            vendor_id bigint(20) DEFAULT NULL,
            user_id bigint(20) DEFAULT NULL,
            session_id varchar(255) NOT NULL,
            ip_address varchar(45) NOT NULL,
            user_agent text DEFAULT NULL,
            referrer text DEFAULT NULL,
            page_url text DEFAULT NULL,
            device_type varchar(50) DEFAULT NULL,
            browser varchar(100) DEFAULT NULL,
            country varchar(100) DEFAULT NULL,
            region varchar(100) DEFAULT NULL,
            city varchar(100) DEFAULT NULL,
            view_duration int(11) DEFAULT NULL,
            scroll_depth int(11) DEFAULT NULL,
            interactions longtext DEFAULT NULL,
            viewed_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY product_id (product_id),
            KEY vendor_id (vendor_id),
            KEY user_id (user_id),
            KEY session_id (session_id),
            KEY viewed_at (viewed_at),
            KEY device_type (device_type)
        ) $charset_collate;";
        
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
        
        // Cart additions tracking
        $table_name = $wpdb->prefix . 'vss_cart_additions_detailed';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            product_id bigint(20) NOT NULL,
            vendor_id bigint(20) DEFAULT NULL,
            user_id bigint(20) DEFAULT NULL,
            session_id varchar(255) NOT NULL,
            cart_key varchar(255) DEFAULT NULL,
            quantity int(11) DEFAULT 1,
            unit_price decimal(10,2) DEFAULT NULL,
            total_price decimal(10,2) DEFAULT NULL,
            variation_id bigint(20) DEFAULT NULL,
            variation_data longtext DEFAULT NULL,
            source_page text DEFAULT NULL,
            conversion_time int(11) DEFAULT NULL,
            added_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY product_id (product_id),
            KEY vendor_id (vendor_id),
            KEY user_id (user_id),
            KEY session_id (session_id),
            KEY added_at (added_at)
        ) $charset_collate;";
        
        dbDelta( $sql );
        
        // Product questions tracking
        $table_name = $wpdb->prefix . 'vss_product_questions_detailed';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            product_id bigint(20) NOT NULL,
            vendor_id bigint(20) DEFAULT NULL,
            customer_id bigint(20) DEFAULT NULL,
            customer_email varchar(255) NOT NULL,
            customer_name varchar(255) NOT NULL,
            customer_phone varchar(50) DEFAULT NULL,
            question longtext NOT NULL,
            question_category varchar(100) DEFAULT NULL,
            answer longtext DEFAULT NULL,
            status enum('pending','answered','archived','spam') DEFAULT 'pending',
            priority enum('low','normal','high','urgent') DEFAULT 'normal',
            is_public tinyint(1) DEFAULT 1,
            admin_notes text DEFAULT NULL,
            tags varchar(500) DEFAULT NULL,
            source varchar(100) DEFAULT 'website',
            ip_address varchar(45) DEFAULT NULL,
            user_agent text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            answered_at datetime DEFAULT NULL,
            answered_by bigint(20) DEFAULT NULL,
            last_updated datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY product_id (product_id),
            KEY vendor_id (vendor_id),
            KEY customer_id (customer_id),
            KEY status (status),
            KEY priority (priority),
            KEY created_at (created_at),
            KEY question_category (question_category)
        ) $charset_collate;";
        
        dbDelta( $sql );
        
        // Product issues tracking
        $table_name = $wpdb->prefix . 'vss_product_issues';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            product_id bigint(20) NOT NULL,
            vendor_id bigint(20) DEFAULT NULL,
            reporter_id bigint(20) DEFAULT NULL,
            reporter_email varchar(255) DEFAULT NULL,
            reporter_name varchar(255) DEFAULT NULL,
            issue_type enum('quality','shipping','description','other') NOT NULL,
            severity enum('low','medium','high','critical') DEFAULT 'medium',
            title varchar(255) NOT NULL,
            description longtext NOT NULL,
            attachments longtext DEFAULT NULL,
            order_id bigint(20) DEFAULT NULL,
            status enum('open','investigating','resolved','closed','duplicate') DEFAULT 'open',
            resolution longtext DEFAULT NULL,
            admin_notes text DEFAULT NULL,
            vendor_response longtext DEFAULT NULL,
            public_visible tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            resolved_at datetime DEFAULT NULL,
            resolved_by bigint(20) DEFAULT NULL,
            PRIMARY KEY (id),
            KEY product_id (product_id),
            KEY vendor_id (vendor_id),
            KEY issue_type (issue_type),
            KEY severity (severity),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        dbDelta( $sql );
        
        // Purchase tracking
        $table_name = $wpdb->prefix . 'vss_purchase_tracking_detailed';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            product_id bigint(20) NOT NULL,
            vendor_id bigint(20) DEFAULT NULL,
            order_id bigint(20) NOT NULL,
            order_item_id bigint(20) DEFAULT NULL,
            customer_id bigint(20) DEFAULT NULL,
            quantity int(11) DEFAULT 1,
            unit_price decimal(10,2) DEFAULT NULL,
            total_price decimal(10,2) DEFAULT NULL,
            cost_price decimal(10,2) DEFAULT NULL,
            profit_amount decimal(10,2) DEFAULT NULL,
            commission_rate decimal(5,2) DEFAULT NULL,
            commission_amount decimal(10,2) DEFAULT NULL,
            variation_id bigint(20) DEFAULT NULL,
            variation_data longtext DEFAULT NULL,
            purchase_source varchar(100) DEFAULT NULL,
            customer_type enum('new','returning','vip') DEFAULT 'new',
            payment_method varchar(100) DEFAULT NULL,
            shipping_method varchar(100) DEFAULT NULL,
            coupon_used varchar(255) DEFAULT NULL,
            discount_amount decimal(10,2) DEFAULT NULL,
            purchased_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY product_id (product_id),
            KEY vendor_id (vendor_id),
            KEY order_id (order_id),
            KEY customer_id (customer_id),
            KEY purchased_at (purchased_at),
            KEY customer_type (customer_type)
        ) $charset_collate;";
        
        dbDelta( $sql );
        
        // Analytics aggregation table
        $table_name = $wpdb->prefix . 'vss_analytics_summary';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            product_id bigint(20) NOT NULL,
            vendor_id bigint(20) NOT NULL,
            date_recorded date NOT NULL,
            period_type enum('hourly','daily','weekly','monthly') DEFAULT 'daily',
            views_total int(11) DEFAULT 0,
            views_unique int(11) DEFAULT 0,
            cart_additions int(11) DEFAULT 0,
            cart_conversion_rate decimal(5,4) DEFAULT 0,
            purchases int(11) DEFAULT 0,
            purchase_conversion_rate decimal(5,4) DEFAULT 0,
            revenue decimal(10,2) DEFAULT 0,
            profit decimal(10,2) DEFAULT 0,
            questions_received int(11) DEFAULT 0,
            questions_answered int(11) DEFAULT 0,
            issues_reported int(11) DEFAULT 0,
            issues_resolved int(11) DEFAULT 0,
            avg_view_duration int(11) DEFAULT 0,
            bounce_rate decimal(5,4) DEFAULT 0,
            return_visitor_rate decimal(5,4) DEFAULT 0,
            mobile_traffic_rate decimal(5,4) DEFAULT 0,
            PRIMARY KEY (id),
            UNIQUE KEY unique_product_date_period (product_id, date_recorded, period_type),
            KEY vendor_id (vendor_id),
            KEY date_recorded (date_recorded),
            KEY period_type (period_type)
        ) $charset_collate;";
        
        dbDelta( $sql );
    }

    /**
     * Enqueue frontend tracking scripts
     */
    public static function enqueue_frontend_tracking() {
        if ( ! is_product() && ! is_shop() && ! is_product_category() ) {
            return;
        }
        
        wp_enqueue_script(
            'vss-tracking',
            VSS_PLUGIN_URL . 'assets/js/vss-tracking.js',
            [ 'jquery' ],
            VSS_VERSION,
            true
        );
        
        wp_localize_script( 'vss-tracking', 'vssTracking', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'vss_tracking_nonce' ),
            'user_id' => get_current_user_id(),
            'session_id' => session_id() ?: wp_generate_password( 32, false ),
            'product_id' => is_product() ? get_the_ID() : null,
            'track_views' => true,
            'track_interactions' => true,
            'track_scroll' => true,
            'view_threshold' => 3000, // 3 seconds
            'interaction_events' => [ 'click', 'scroll', 'focus', 'mouseenter' ]
        ] );
        
        // Add inline tracking script
        wp_add_inline_script( 'vss-tracking', self::get_tracking_init_script() );
    }

    /**
     * Get tracking initialization script
     */
    private static function get_tracking_init_script() {
        return '
        window.VSSTracking = {
            init: function() {
                this.startTime = Date.now();
                this.maxScroll = 0;
                this.interactions = [];
                this.viewTracked = false;
                
                if (vssTracking.product_id) {
                    this.setupViewTracking();
                    this.setupInteractionTracking();
                    this.setupScrollTracking();
                    this.setupCartTracking();
                }
            },
            
            setupViewTracking: function() {
                const self = this;
                this.viewTimer = setTimeout(function() {
                    if (!self.viewTracked && document.visibilityState === "visible") {
                        self.trackProductView();
                        self.viewTracked = true;
                    }
                }, vssTracking.view_threshold);
                
                document.addEventListener("visibilitychange", function() {
                    if (document.visibilityState === "hidden") {
                        self.trackViewEnd();
                    }
                });
                
                window.addEventListener("beforeunload", function() {
                    self.trackViewEnd();
                });
            },
            
            setupInteractionTracking: function() {
                const self = this;
                vssTracking.interaction_events.forEach(function(event) {
                    document.addEventListener(event, function(e) {
                        self.recordInteraction(e);
                    });
                });
            },
            
            setupScrollTracking: function() {
                const self = this;
                let scrollTimer = null;
                
                window.addEventListener("scroll", function() {
                    const scrollPercent = Math.round(
                        (window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100
                    );
                    
                    if (scrollPercent > self.maxScroll) {
                        self.maxScroll = Math.min(scrollPercent, 100);
                    }
                    
                    clearTimeout(scrollTimer);
                    scrollTimer = setTimeout(function() {
                        self.recordInteraction({ type: "scroll", depth: self.maxScroll });
                    }, 100);
                });
            },
            
            setupCartTracking: function() {
                const self = this;
                
                // Track add to cart button clicks
                jQuery(document).on("click", ".single_add_to_cart_button", function(e) {
                    const $form = jQuery(this).closest("form.cart");
                    const productId = $form.find("input[name=add-to-cart]").val() || vssTracking.product_id;
                    const quantity = $form.find("input[name=quantity]").val() || 1;
                    
                    // Track the cart addition attempt
                    self.trackCartAddition(productId, quantity);
                });
            },
            
            trackProductView: function() {
                jQuery.ajax({
                    url: vssTracking.ajax_url,
                    type: "POST",
                    data: {
                        action: "vss_track_product_view",
                        product_id: vssTracking.product_id,
                        nonce: vssTracking.nonce,
                        session_id: vssTracking.session_id,
                        referrer: document.referrer,
                        page_url: window.location.href,
                        device_type: this.getDeviceType(),
                        browser: this.getBrowser(),
                        country: this.getCountry(),
                        interactions: JSON.stringify(this.interactions)
                    },
                    success: function(response) {
                        if (response.success) {
                            console.log("Product view tracked");
                        }
                    }
                });
            },
            
            trackViewEnd: function() {
                if (this.viewTracked) {
                    const viewDuration = Math.round((Date.now() - this.startTime) / 1000);
                    
                    navigator.sendBeacon(vssTracking.ajax_url, new URLSearchParams({
                        action: "vss_update_view_duration",
                        product_id: vssTracking.product_id,
                        session_id: vssTracking.session_id,
                        duration: viewDuration,
                        scroll_depth: this.maxScroll,
                        interactions: JSON.stringify(this.interactions),
                        nonce: vssTracking.nonce
                    }));
                }
            },
            
            trackCartAddition: function(productId, quantity) {
                jQuery.ajax({
                    url: vssTracking.ajax_url,
                    type: "POST",
                    data: {
                        action: "vss_track_cart_addition",
                        product_id: productId,
                        quantity: quantity,
                        nonce: vssTracking.nonce,
                        session_id: vssTracking.session_id,
                        source_page: window.location.href,
                        conversion_time: Math.round((Date.now() - this.startTime) / 1000)
                    },
                    success: function(response) {
                        if (response.success) {
                            console.log("Cart addition tracked");
                        }
                    }
                });
            },
            
            recordInteraction: function(event) {
                this.interactions.push({
                    type: event.type || "unknown",
                    timestamp: Date.now() - this.startTime,
                    target: event.target ? event.target.tagName : null,
                    data: event.data || null
                });
                
                // Keep only last 50 interactions
                if (this.interactions.length > 50) {
                    this.interactions = this.interactions.slice(-50);
                }
            },
            
            getDeviceType: function() {
                const width = window.screen.width;
                if (width <= 768) return "mobile";
                if (width <= 1024) return "tablet"; 
                return "desktop";
            },
            
            getBrowser: function() {
                const userAgent = navigator.userAgent;
                if (userAgent.indexOf("Chrome") > -1) return "Chrome";
                if (userAgent.indexOf("Firefox") > -1) return "Firefox";
                if (userAgent.indexOf("Safari") > -1) return "Safari";
                if (userAgent.indexOf("Edge") > -1) return "Edge";
                return "Other";
            },
            
            getCountry: function() {
                // This would typically use a geolocation service
                return "Unknown";
            }
        };
        
        jQuery(document).ready(function() {
            VSSTracking.init();
        });
        ';
    }

    /**
     * Track product view via AJAX
     */
    public static function track_product_view_ajax() {
        check_ajax_referer( 'vss_tracking_nonce', 'nonce' );
        
        $product_id = intval( $_POST['product_id'] );
        $session_id = sanitize_text_field( $_POST['session_id'] );
        $user_id = get_current_user_id();
        
        $view_data = [
            'product_id' => $product_id,
            'user_id' => $user_id ?: null,
            'session_id' => $session_id,
            'ip_address' => self::get_client_ip(),
            'user_agent' => sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ?? '' ),
            'referrer' => sanitize_url( $_POST['referrer'] ?? '' ),
            'page_url' => sanitize_url( $_POST['page_url'] ?? '' ),
            'device_type' => sanitize_text_field( $_POST['device_type'] ?? '' ),
            'browser' => sanitize_text_field( $_POST['browser'] ?? '' ),
            'country' => sanitize_text_field( $_POST['country'] ?? '' ),
            'interactions' => sanitize_text_field( $_POST['interactions'] ?? '' )
        ];
        
        // Get vendor ID
        $vendor_id = self::get_product_vendor_id( $product_id );
        if ( $vendor_id ) {
            $view_data['vendor_id'] = $vendor_id;
        }
        
        global $wpdb;
        $result = $wpdb->insert(
            $wpdb->prefix . 'vss_product_views_detailed',
            $view_data
        );
        
        if ( $result ) {
            wp_send_json_success( 'View tracked' );
        } else {
            wp_send_json_error( 'Failed to track view' );
        }
    }

    /**
     * Track cart addition via AJAX
     */
    public static function track_cart_addition_ajax() {
        check_ajax_referer( 'vss_tracking_nonce', 'nonce' );
        
        $product_id = intval( $_POST['product_id'] );
        $quantity = intval( $_POST['quantity'] );
        $session_id = sanitize_text_field( $_POST['session_id'] );
        $user_id = get_current_user_id();
        
        $product = wc_get_product( $product_id );
        if ( ! $product ) {
            wp_send_json_error( 'Invalid product' );
        }
        
        $cart_data = [
            'product_id' => $product_id,
            'user_id' => $user_id ?: null,
            'session_id' => $session_id,
            'quantity' => $quantity,
            'unit_price' => $product->get_price(),
            'total_price' => $product->get_price() * $quantity,
            'source_page' => sanitize_url( $_POST['source_page'] ?? '' ),
            'conversion_time' => intval( $_POST['conversion_time'] ?? 0 )
        ];
        
        // Get vendor ID
        $vendor_id = self::get_product_vendor_id( $product_id );
        if ( $vendor_id ) {
            $cart_data['vendor_id'] = $vendor_id;
        }
        
        global $wpdb;
        $result = $wpdb->insert(
            $wpdb->prefix . 'vss_cart_additions_detailed',
            $cart_data
        );
        
        if ( $result ) {
            wp_send_json_success( 'Cart addition tracked' );
        } else {
            wp_send_json_error( 'Failed to track cart addition' );
        }
    }

    /**
     * Submit product question
     */
    public static function submit_product_question() {
        check_ajax_referer( 'vss_tracking_nonce', 'nonce' );
        
        $product_id = intval( $_POST['product_id'] );
        $customer_email = sanitize_email( $_POST['customer_email'] );
        $customer_name = sanitize_text_field( $_POST['customer_name'] );
        $customer_phone = sanitize_text_field( $_POST['customer_phone'] ?? '' );
        $question = sanitize_textarea_field( $_POST['question'] );
        $question_category = sanitize_text_field( $_POST['question_category'] ?? 'general' );
        
        if ( ! $product_id || ! $customer_email || ! $customer_name || ! $question ) {
            wp_send_json_error( 'Missing required fields' );
        }
        
        // Get vendor ID
        $vendor_id = self::get_product_vendor_id( $product_id );
        if ( ! $vendor_id ) {
            wp_send_json_error( 'Invalid product' );
        }
        
        $question_data = [
            'product_id' => $product_id,
            'vendor_id' => $vendor_id,
            'customer_id' => get_current_user_id() ?: null,
            'customer_email' => $customer_email,
            'customer_name' => $customer_name,
            'customer_phone' => $customer_phone,
            'question' => $question,
            'question_category' => $question_category,
            'ip_address' => self::get_client_ip(),
            'user_agent' => sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ?? '' ),
            'source' => 'website'
        ];
        
        global $wpdb;
        $question_id = $wpdb->insert(
            $wpdb->prefix . 'vss_product_questions_detailed',
            $question_data
        );
        
        if ( $question_id ) {
            // Notify vendor
            do_action( 'vss_question_received', $question_id, $product_id, $vendor_id );
            
            wp_send_json_success( [
                'message' => __( 'Your question has been submitted successfully', 'vss' ),
                'question_id' => $question_id
            ] );
        } else {
            wp_send_json_error( 'Failed to submit question' );
        }
    }

    /**
     * Report product issue
     */
    public static function report_product_issue() {
        check_ajax_referer( 'vss_tracking_nonce', 'nonce' );
        
        $product_id = intval( $_POST['product_id'] );
        $issue_type = sanitize_text_field( $_POST['issue_type'] );
        $severity = sanitize_text_field( $_POST['severity'] ?? 'medium' );
        $title = sanitize_text_field( $_POST['title'] );
        $description = sanitize_textarea_field( $_POST['description'] );
        $order_id = intval( $_POST['order_id'] ?? 0 );
        
        if ( ! $product_id || ! $issue_type || ! $title || ! $description ) {
            wp_send_json_error( 'Missing required fields' );
        }
        
        // Get vendor ID
        $vendor_id = self::get_product_vendor_id( $product_id );
        if ( ! $vendor_id ) {
            wp_send_json_error( 'Invalid product' );
        }
        
        $user_id = get_current_user_id();
        $user = wp_get_current_user();
        
        $issue_data = [
            'product_id' => $product_id,
            'vendor_id' => $vendor_id,
            'reporter_id' => $user_id ?: null,
            'reporter_email' => $user_id ? $user->user_email : sanitize_email( $_POST['reporter_email'] ?? '' ),
            'reporter_name' => $user_id ? $user->display_name : sanitize_text_field( $_POST['reporter_name'] ?? '' ),
            'issue_type' => $issue_type,
            'severity' => $severity,
            'title' => $title,
            'description' => $description,
            'order_id' => $order_id ?: null
        ];
        
        global $wpdb;
        $issue_id = $wpdb->insert(
            $wpdb->prefix . 'vss_product_issues',
            $issue_data
        );
        
        if ( $issue_id ) {
            // Notify vendor and admin
            do_action( 'vss_issue_reported', $issue_id, $product_id, $vendor_id );
            
            wp_send_json_success( [
                'message' => __( 'Your issue has been reported successfully', 'vss' ),
                'issue_id' => $issue_id
            ] );
        } else {
            wp_send_json_error( 'Failed to report issue' );
        }
    }

    /**
     * Track cart addition from WooCommerce
     */
    public static function track_cart_addition_wc( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
        $user_id = get_current_user_id();
        $session_id = WC()->session ? WC()->session->get_customer_id() : wp_generate_password( 32, false );
        
        $product = wc_get_product( $product_id );
        if ( ! $product ) {
            return;
        }
        
        $cart_data = [
            'product_id' => $product_id,
            'user_id' => $user_id ?: null,
            'session_id' => $session_id,
            'cart_key' => $cart_item_key,
            'quantity' => $quantity,
            'unit_price' => $product->get_price(),
            'total_price' => $product->get_price() * $quantity,
            'variation_id' => $variation_id ?: null,
            'variation_data' => ! empty( $variation ) ? json_encode( $variation ) : null,
            'source_page' => sanitize_url( $_SERVER['HTTP_REFERER'] ?? '' )
        ];
        
        // Get vendor ID
        $vendor_id = self::get_product_vendor_id( $product_id );
        if ( $vendor_id ) {
            $cart_data['vendor_id'] = $vendor_id;
        }
        
        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . 'vss_cart_additions_detailed',
            $cart_data
        );
    }

    /**
     * Track purchase completion
     */
    public static function track_purchase_completion( $order_id ) {
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }
        
        $customer_id = $order->get_user_id();
        $customer_type = self::get_customer_type( $customer_id );
        
        foreach ( $order->get_items() as $item_id => $item ) {
            $product_id = $item->get_product_id();
            $vendor_id = self::get_product_vendor_id( $product_id );
            
            if ( ! $vendor_id ) {
                continue;
            }
            
            $product = $item->get_product();
            $cost_price = get_post_meta( $product_id, '_cost_price', true ) ?: 0;
            $commission_rate = get_option( 'vss_commission_rate', 15 );
            $commission_amount = ( $item->get_subtotal() * $commission_rate ) / 100;
            $profit_amount = $item->get_subtotal() - $cost_price - $commission_amount;
            
            $purchase_data = [
                'product_id' => $product_id,
                'vendor_id' => $vendor_id,
                'order_id' => $order_id,
                'order_item_id' => $item_id,
                'customer_id' => $customer_id ?: null,
                'quantity' => $item->get_quantity(),
                'unit_price' => $item->get_subtotal() / $item->get_quantity(),
                'total_price' => $item->get_subtotal(),
                'cost_price' => $cost_price,
                'profit_amount' => $profit_amount,
                'commission_rate' => $commission_rate,
                'commission_amount' => $commission_amount,
                'variation_id' => $item->get_variation_id() ?: null,
                'customer_type' => $customer_type,
                'payment_method' => $order->get_payment_method(),
                'shipping_method' => $order->get_shipping_method(),
                'discount_amount' => $order->get_discount_total(),
                'purchased_at' => $order->get_date_created()->date( 'Y-m-d H:i:s' )
            ];
            
            // Add variation data if exists
            if ( $item->get_variation_id() ) {
                $purchase_data['variation_data'] = json_encode( $item->get_formatted_meta_data() );
            }
            
            // Add coupon information
            if ( $order->get_coupon_codes() ) {
                $purchase_data['coupon_used'] = implode( ',', $order->get_coupon_codes() );
            }
            
            global $wpdb;
            $wpdb->insert(
                $wpdb->prefix . 'vss_purchase_tracking_detailed',
                $purchase_data
            );
        }
    }

    /**
     * Get product vendor ID
     */
    private static function get_product_vendor_id( $product_id ) {
        global $wpdb;
        
        // First check if it's a VSS uploaded product
        $vendor_id = $wpdb->get_var( $wpdb->prepare(
            "SELECT vendor_id FROM {$wpdb->prefix}vss_product_uploads WHERE id = %d",
            $product_id
        ) );
        
        if ( $vendor_id ) {
            return $vendor_id;
        }
        
        // Check WooCommerce product meta
        return get_post_meta( $product_id, '_vss_vendor_id', true );
    }

    /**
     * Get customer type
     */
    private static function get_customer_type( $customer_id ) {
        if ( ! $customer_id ) {
            return 'new';
        }
        
        global $wpdb;
        
        $order_count = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}posts 
             WHERE post_type = 'shop_order' 
             AND post_author = %d 
             AND post_status IN ('wc-completed', 'wc-processing', 'wc-shipped')",
            $customer_id
        ) );
        
        if ( $order_count > 10 ) {
            return 'vip';
        } elseif ( $order_count > 1 ) {
            return 'returning';
        } else {
            return 'new';
        }
    }

    /**
     * Get client IP address
     */
    private static function get_client_ip() {
        $ip_keys = [ 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR' ];
        
        foreach ( $ip_keys as $key ) {
            if ( ! empty( $_SERVER[ $key ] ) ) {
                $ip = sanitize_text_field( $_SERVER[ $key ] );
                if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
                    return $ip;
                }
            }
        }
        
        return '127.0.0.1';
    }

    /**
     * Aggregate tracking data for analytics
     */
    public static function aggregate_tracking_data() {
        global $wpdb;
        
        $yesterday = date( 'Y-m-d', strtotime( '-1 day' ) );
        
        // Get all products that had activity yesterday
        $active_products = $wpdb->get_results(
            "SELECT DISTINCT pv.product_id, pv.vendor_id 
             FROM {$wpdb->prefix}vss_product_views_detailed pv
             WHERE DATE(pv.viewed_at) = '$yesterday'
             AND pv.vendor_id IS NOT NULL
             UNION
             SELECT DISTINCT ca.product_id, ca.vendor_id
             FROM {$wpdb->prefix}vss_cart_additions_detailed ca
             WHERE DATE(ca.added_at) = '$yesterday'
             AND ca.vendor_id IS NOT NULL"
        );
        
        foreach ( $active_products as $product ) {
            $product_id = $product->product_id;
            $vendor_id = $product->vendor_id;
            
            // Calculate metrics for this product
            $metrics = self::calculate_daily_metrics( $product_id, $vendor_id, $yesterday );
            
            // Insert or update summary
            $wpdb->replace(
                $wpdb->prefix . 'vss_analytics_summary',
                array_merge( $metrics, [
                    'product_id' => $product_id,
                    'vendor_id' => $vendor_id,
                    'date_recorded' => $yesterday,
                    'period_type' => 'daily'
                ] )
            );
        }
    }

    /**
     * Calculate daily metrics for a product
     */
    private static function calculate_daily_metrics( $product_id, $vendor_id, $date ) {
        global $wpdb;
        
        // Views metrics
        $views_data = $wpdb->get_row( $wpdb->prepare(
            "SELECT 
                COUNT(*) as views_total,
                COUNT(DISTINCT session_id) as views_unique,
                AVG(view_duration) as avg_view_duration,
                AVG(scroll_depth) as avg_scroll_depth
             FROM {$wpdb->prefix}vss_product_views_detailed 
             WHERE product_id = %d AND DATE(viewed_at) = %s",
            $product_id, $date
        ) );
        
        // Cart additions
        $cart_additions = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}vss_cart_additions_detailed 
             WHERE product_id = %d AND DATE(added_at) = %s",
            $product_id, $date
        ) );
        
        // Purchases
        $purchase_data = $wpdb->get_row( $wpdb->prepare(
            "SELECT 
                COUNT(*) as purchases,
                SUM(total_price) as revenue,
                SUM(profit_amount) as profit
             FROM {$wpdb->prefix}vss_purchase_tracking_detailed 
             WHERE product_id = %d AND DATE(purchased_at) = %s",
            $product_id, $date
        ) );
        
        // Questions
        $questions_data = $wpdb->get_row( $wpdb->prepare(
            "SELECT 
                COUNT(*) as questions_received,
                SUM(CASE WHEN status = 'answered' THEN 1 ELSE 0 END) as questions_answered
             FROM {$wpdb->prefix}vss_product_questions_detailed 
             WHERE product_id = %d AND DATE(created_at) = %s",
            $product_id, $date
        ) );
        
        // Issues
        $issues_data = $wpdb->get_row( $wpdb->prepare(
            "SELECT 
                COUNT(*) as issues_reported,
                SUM(CASE WHEN status IN ('resolved', 'closed') THEN 1 ELSE 0 END) as issues_resolved
             FROM {$wpdb->prefix}vss_product_issues 
             WHERE product_id = %d AND DATE(created_at) = %s",
            $product_id, $date
        ) );
        
        // Calculate conversion rates
        $views_total = (int) $views_data->views_total;
        $cart_conversion_rate = $views_total > 0 ? ( $cart_additions / $views_total ) : 0;
        $purchase_conversion_rate = $views_total > 0 ? ( (int) $purchase_data->purchases / $views_total ) : 0;
        
        // Calculate other metrics
        $bounce_rate = 0; // Would need more detailed tracking
        $return_visitor_rate = 0; // Would need session analysis
        $mobile_traffic_rate = 0; // Would need device type analysis
        
        return [
            'views_total' => $views_total,
            'views_unique' => (int) $views_data->views_unique,
            'cart_additions' => (int) $cart_additions,
            'cart_conversion_rate' => round( $cart_conversion_rate, 4 ),
            'purchases' => (int) $purchase_data->purchases,
            'purchase_conversion_rate' => round( $purchase_conversion_rate, 4 ),
            'revenue' => (float) $purchase_data->revenue ?: 0,
            'profit' => (float) $purchase_data->profit ?: 0,
            'questions_received' => (int) $questions_data->questions_received,
            'questions_answered' => (int) $questions_data->questions_answered,
            'issues_reported' => (int) $issues_data->issues_reported,
            'issues_resolved' => (int) $issues_data->issues_resolved,
            'avg_view_duration' => (int) $views_data->avg_view_duration ?: 0,
            'bounce_rate' => $bounce_rate,
            'return_visitor_rate' => $return_visitor_rate,
            'mobile_traffic_rate' => $mobile_traffic_rate
        ];
    }

    /**
     * Get analytics summary for vendor
     */
    public static function get_vendor_analytics_summary( $vendor_id, $start_date, $end_date ) {
        global $wpdb;
        
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT 
                product_id,
                SUM(views_total) as total_views,
                SUM(views_unique) as unique_views,
                SUM(cart_additions) as total_cart_additions,
                AVG(cart_conversion_rate) as avg_cart_conversion,
                SUM(purchases) as total_purchases,
                AVG(purchase_conversion_rate) as avg_purchase_conversion,
                SUM(revenue) as total_revenue,
                SUM(profit) as total_profit,
                SUM(questions_received) as total_questions,
                SUM(questions_answered) as total_answers,
                SUM(issues_reported) as total_issues,
                SUM(issues_resolved) as total_resolutions
             FROM {$wpdb->prefix}vss_analytics_summary 
             WHERE vendor_id = %d 
             AND date_recorded BETWEEN %s AND %s
             AND period_type = 'daily'
             GROUP BY product_id
             ORDER BY total_views DESC",
            $vendor_id, $start_date, $end_date
        ) );
    }
}