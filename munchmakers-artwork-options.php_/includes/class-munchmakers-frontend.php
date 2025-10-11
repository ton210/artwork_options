<?php
/**
 * Frontend Class
 *
 * @package MunchMakers_Product_Customizer
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MunchMakers_Frontend {

    private $pricing;
    private static $instance_count = 0;

    public function __construct( $pricing ) {
        $this->pricing = $pricing;

        // Only add hooks if we're in the right context
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ), 15 );
        add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'output_customizer_html' ), 999 );
        add_action( 'wp_footer', array( $this, 'output_modal_html' ), 25 );
    }

    public function enqueue_frontend_scripts() {
        if ( ! is_product() ) {
            return;
        }

        // Enqueue external dependencies
        wp_enqueue_style(
            'nouislider',
            'https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.7.1/nouislider.min.css',
            array(),
            '15.7.1'
        );

        wp_enqueue_script(
            'nouislider',
            'https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.7.1/nouislider.min.js',
            array(),
            '15.7.1',
            true
        );

        // Ensure WooCommerce scripts are loaded
        wp_enqueue_script( 'wc-add-to-cart' );

        // Localize script data
        wp_localize_script( 'nouislider', 'munchmakers_ajax', array(
            'ajax_url'          => admin_url( 'admin-ajax.php' ),
            'cart_url'          => wc_get_cart_url(),
            'add_to_cart_nonce' => wp_create_nonce( 'munchmakers_add_to_cart_nonce' ),
            'upload_nonce'      => wp_create_nonce( 'munchmakers_upload_nonce' ),
            'pricing_nonce'     => wp_create_nonce( 'munchmakers_pricing_nonce' ),
            'currency'          => get_woocommerce_currency_symbol(),
        ));
    }

    public function output_customizer_html() {
        global $product, $woocommerce_loop;

        if ( ! $product ) {
            return;
        }

        // Only output on single product pages, not in loops or other locations
        if ( ! is_product() || is_shop() || is_product_category() || is_product_tag() ) {
            return;
        }

        // Don't output in product loops
        if ( ! empty( $woocommerce_loop['loop'] ) ||
             ! empty( $woocommerce_loop['is_shortcode'] ) ||
             ! empty( $woocommerce_loop['name'] ) ) {
            return;
        }

        // Check if we're inside the main product form by checking the current action
        if ( ! doing_action( 'woocommerce_before_add_to_cart_button' ) ) {
            return;
        }

        // Additional check: ensure we're in the main content area
        if ( ! in_the_loop() || ! is_main_query() ) {
            return;
        }

        // Only output once per page to prevent duplicates
        self::$instance_count++;
        if ( self::$instance_count > 1 ) {
            return;
        }

        // Output hidden fields and inline scripts
        ?>
        <input type="hidden" name="munchmakers_artwork_option" id="munchmakers_artwork_option" value="Upload Image">
        <input type="hidden" name="munchmakers_selected_options" id="munchmakers_selected_options" value="">
        <input type="hidden" name="munchmakers_artwork_file_urls" id="munchmakers_artwork_file_urls" value="">
        <input type="hidden" name="munchmakers_product_id" id="munchmakers_product_id" value="<?php echo esc_attr( $product->get_id() ); ?>">
        <?php
    }

    public function output_modal_html() {
        if ( ! is_product() ) {
            return;
        }

        global $product;
        if ( ! $product ) {
            return;
        }

        // Prepare data for the modal
        $product_id = $product->get_id();
        $is_variable_product = $product->is_type( 'variable' );
        $initial_pricing = $this->pricing->get_initial_pricing_data( $product_id );
        $pricing_json = json_encode( $initial_pricing );
        $available_variations = $is_variable_product ? $product->get_available_variations() : array();
        $attributes = $is_variable_product ? $product->get_variation_attributes() : array();

        // Find default variation
        $default_variation = $this->find_default_variation( $available_variations );

        // Create attribute map
        $attribute_map = $this->create_attribute_map( $attributes, $is_variable_product );
        $attribute_map_json = json_encode( $attribute_map );
        $default_variation_json = json_encode( $default_variation );

        // Output properly scoped styles
        $this->output_modal_styles();

        // Output inline styles for Start Order button positioning
        $this->output_inline_styles();

        // Output sticky button
        $this->output_sticky_button_html();

        // Include the modal template
        include MUNCHMAKERS_PLUGIN_DIR . 'templates/modal-template.php';

        // Output modal scripts
        $this->output_modal_scripts( $is_variable_product, $pricing_json, $available_variations,
                                    $attribute_map_json, $default_variation_json, $product_id );

        // Output button positioning script
        $this->output_button_positioning_script();
    }

    /**
     * Output modal styles in a contained manner
     */
    private function output_modal_styles() {
        $modal_css = include MUNCHMAKERS_PLUGIN_DIR . 'assets/modal-styles.php';
        ?>
        <!-- MunchMakers Modal Styles - Start -->
        <style id="munchmakers-modal-styles" type="text/css">
        <?php echo $modal_css; ?>
        </style>
        <!-- MunchMakers Modal Styles - End -->
        <?php
    }

    /**
     * Output inline styles for Start Order button
     */
    private function output_inline_styles() {
        ?>
        <!-- MunchMakers Inline Styles - Start -->
        <style id="munchmakers-inline-styles" type="text/css">
            /* Start Order Button Positioning */
            #munchmakers-customizer-root {
                opacity: 0;
                visibility: hidden;
                transition: opacity 0.3s ease, visibility 0.3s ease;
            }

            #munchmakers-customizer-root.ready {
                opacity: 1;
                visibility: visible;
            }

            /* Hide WooCommerce elements in product form */
            .single-product form.cart .variations,
            .single-product form.cart .quantity,
            .single-product form.cart .single_add_to_cart_button:not(.munchmakers-start-order-btn) {
                display: none !important;
            }

            /* Start Order Button Styling */
            .munchmakers-start-order-btn {
                display: block !important;
                width: 100% !important;
                padding: 18px 32px !important;
                background: #9DC645 !important;
                color: #fff !important;
                border: none !important;
                border-radius: 8px !important;
                font-size: 18px !important;
                font-weight: 700 !important;
                cursor: pointer !important;
                margin: 24px 0 !important;
                transition: all 0.3s ease !important;
                box-shadow: 0 4px 12px rgba(157, 198, 69, 0.3) !important;
            }

            .munchmakers-start-order-btn:hover {
                background: #8AB83A !important;
                transform: translateY(-2px) !important;
                box-shadow: 0 6px 16px rgba(157, 198, 69, 0.4) !important;
            }

            /* Sticky Button Container */
            .munchmakers-sticky-start-order {
                position: fixed !important;
                bottom: 0 !important;
                left: 0 !important;
                right: 0 !important;
                z-index: 9998 !important;
                background: #fff !important;
                border-top: 2px solid #9DC645 !important;
                box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.15) !important;
                padding: 12px 15px !important;
                display: none !important;
                transform: translateY(100%) !important;
                opacity: 0 !important;
                transition: all 0.3s ease-out !important;
            }

            .munchmakers-sticky-start-order.show {
                display: block !important;
                transform: translateY(0) !important;
                opacity: 1 !important;
            }

            .munchmakers-start-order-btn-sticky {
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 10px !important;
                width: 100% !important;
                padding: 18px 32px !important;
                background: linear-gradient(135deg, #9DC645, #8AB83A) !important;
                color: #fff !important;
                border: none !important;
                border-radius: 12px !important;
                font-size: 18px !important;
                font-weight: 700 !important;
                cursor: pointer !important;
                transition: all 0.3s ease !important;
                box-shadow: 0 6px 20px rgba(157, 198, 69, 0.4) !important;
            }

            .munchmakers-start-order-btn-sticky:hover {
                background: linear-gradient(135deg, #8AB83A, #7AA32F) !important;
                transform: translateY(-2px) !important;
                box-shadow: 0 8px 25px rgba(157, 198, 69, 0.5) !important;
            }

            /* Body padding when sticky is visible */
            body.munchmakers-sticky-visible {
                padding-bottom: 80px !important;
            }

            /* Mobile adjustments */
            @media (max-width: 768px) {
                .munchmakers-start-order-btn,
                .munchmakers-start-order-btn-sticky {
                    padding: 16px 24px !important;
                    font-size: 16px !important;
                }

                body.munchmakers-sticky-visible {
                    padding-bottom: 70px !important;
                }
            }
        </style>
        <!-- MunchMakers Inline Styles - End -->
        <?php
    }

    /**
     * Output modal scripts
     */
    private function output_modal_scripts( $is_variable_product, $pricing_json, $available_variations,
                                          $attribute_map_json, $default_variation_json, $product_id ) {
        ?>
        <!-- MunchMakers Modal Scripts - Start -->
        <script id="munchmakers-modal-scripts">
        (function() {
            'use strict';

            // Ensure we only initialize once
            if (window.munchmakersModalInitialized) {
                return;
            }
            window.munchmakersModalInitialized = true;

            // Include the modal scripts
            <?php include MUNCHMAKERS_PLUGIN_DIR . 'assets/modal-scripts.php'; ?>
        })();
        </script>
        <!-- MunchMakers Modal Scripts - End -->
        <?php
    }

    /**
     * Output button positioning script
     */
    private function output_button_positioning_script() {
        ?>
        <!-- MunchMakers Button Positioning - Start -->
        <script id="munchmakers-button-positioning">
        (function() {
            'use strict';

            let initialized = false;
            let retryCount = 0;
            const maxRetries = 20;

            // Mark elements to track what we've created
            const MUNCHMAKERS_MARKER = 'data-munchmakers-processed';

            function initializeMunchmakers() {
                if (initialized) return;

                const addToCartBtn = document.querySelector(
                    'form.cart .single_add_to_cart_button, ' +
                    'form.cart button[type="submit"]:not(.munchmakers-start-order-btn)'
                );

                const cartForm = document.querySelector('form.cart, form.variations_form');

                if (!addToCartBtn && !cartForm) {
                    retryCount++;
                    if (retryCount < maxRetries) {
                        setTimeout(initializeMunchmakers, 200);
                    }
                    return;
                }

                initialized = true;

                // Create and position our button
                createStartOrderButton();

                // Initialize sticky button
                initializeStickyButton();

                // Set up continuous monitoring
                setupContinuousMonitoring();
            }

            function createStartOrderButton() {
                const addToCartBtn = document.querySelector(
                    'form.cart .single_add_to_cart_button, ' +
                    'form.cart button[type="submit"]:not(.munchmakers-start-order-btn)'
                );

                if (!addToCartBtn) return;

                // Mark this add to cart button as processed
                addToCartBtn.setAttribute(MUNCHMAKERS_MARKER, 'true');

                // Remove any existing roots first
                const existingRoots = document.querySelectorAll('#munchmakers-customizer-root');
                existingRoots.forEach(root => root.remove());

                // Create new container
                const root = document.createElement('div');
                root.id = 'munchmakers-customizer-root';
                root.setAttribute(MUNCHMAKERS_MARKER, 'true');

                // Create button
                const startBtn = document.createElement('button');
                startBtn.type = 'button';
                startBtn.className = 'munchmakers-start-order-btn';
                startBtn.textContent = 'Start Order';
                startBtn.setAttribute(MUNCHMAKERS_MARKER, 'true');
                root.appendChild(startBtn);

                // Insert before the add to cart button
                addToCartBtn.parentNode.insertBefore(root, addToCartBtn);

                // Set up event handlers
                setupButtonEventHandlers();

                // Mark as ready
                setTimeout(() => {
                    root.classList.add('ready');
                }, 10);

                // Hide original elements
                addToCartBtn.style.display = 'none';
                const form = addToCartBtn.closest('form.cart');
                if (form) {
                    const variations = form.querySelector('.variations');
                    if (variations) variations.style.display = 'none';

                    const quantity = form.querySelector('.quantity');
                    if (quantity) quantity.style.display = 'none';
                }
            }

            function setupButtonEventHandlers() {
                function openModal(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const form = document.querySelector('form.cart');
                    if (form) {
                        form.classList.add('munchmakers-active');
                    }
                    const modal = document.getElementById('munchmakers-modal');
                    if (modal) {
                        modal.style.display = 'flex';
                        document.body.style.overflow = 'hidden';
                        document.dispatchEvent(new CustomEvent('munchmakersModalOpen'));
                    }
                }

                // Use event delegation on document level for all start order buttons
                if (!document.body.hasAttribute('data-munchmakers-delegation')) {
                    document.body.addEventListener('click', function(e) {
                        if (e.target.classList.contains('munchmakers-start-order-btn') ||
                            e.target.classList.contains('munchmakers-start-order-btn-sticky')) {
                            openModal(e);
                        }
                    });
                    document.body.setAttribute('data-munchmakers-delegation', 'true');
                }
            }

            function initializeStickyButton() {
                const startBtn = document.querySelector('.munchmakers-start-order-btn');
                const stickyContainer = document.querySelector('.munchmakers-sticky-start-order');

                if (!startBtn || !stickyContainer) {
                    setTimeout(initializeStickyButton, 500);
                    return;
                }

                let isSticky = false;
                let scrollTimeout;

                function checkButtonVisibility() {
                    const startBtnCheck = document.querySelector('.munchmakers-start-order-btn');
                    if (!startBtnCheck) return;

                    const rect = startBtnCheck.getBoundingClientRect();
                    const windowHeight = window.innerHeight;
                    const isVisible = rect.bottom > -50 && rect.top < windowHeight + 50;

                    if (!isVisible && !isSticky) {
                        stickyContainer.classList.add('show');
                        document.body.classList.add('munchmakers-sticky-visible');
                        isSticky = true;
                    } else if (isVisible && isSticky) {
                        stickyContainer.classList.remove('show');
                        document.body.classList.remove('munchmakers-sticky-visible');
                        isSticky = false;
                    }
                }

                function handleScroll() {
                    if (scrollTimeout) clearTimeout(scrollTimeout);
                    scrollTimeout = setTimeout(checkButtonVisibility, 10);
                }

                if (!window.hasAttribute || !window.hasAttribute('data-munchmakers-scroll-setup')) {
                    window.addEventListener('scroll', handleScroll, { passive: true });
                    window.addEventListener('resize', checkButtonVisibility, { passive: true });
                    if (window.setAttribute) window.setAttribute('data-munchmakers-scroll-setup', 'true');
                }

                // Initial check
                setTimeout(checkButtonVisibility, 100);
            }

            function cleanupDuplicates() {
                const roots = document.querySelectorAll('#munchmakers-customizer-root');
                if (roots.length > 1) {
                    console.log('MunchMakers: Found ' + roots.length + ' Start Order buttons, removing extras...');

                    // Find the one that's properly positioned (before an add to cart button)
                    let validRoot = null;
                    roots.forEach(root => {
                        const nextElement = root.nextElementSibling;
                        if (nextElement &&
                            (nextElement.classList.contains('single_add_to_cart_button') ||
                             nextElement.type === 'submit')) {
                            if (!validRoot) validRoot = root;
                        }
                    });

                    // If we found a valid one, keep it and remove others
                    if (validRoot) {
                        roots.forEach(root => {
                            if (root !== validRoot) root.remove();
                        });
                    } else {
                        // Keep the first one
                        for (let i = 1; i < roots.length; i++) {
                            roots[i].remove();
                        }
                    }
                }
            }

            function setupContinuousMonitoring() {
                let observerTimeout;

                // Set up mutation observer for dynamic changes
                const observer = new MutationObserver(function(mutations) {
                    // Debounce to avoid processing too many mutations at once
                    if (observerTimeout) clearTimeout(observerTimeout);

                    observerTimeout = setTimeout(() => {
                        // Check if mutations added new forms or buttons
                        let relevantChange = false;

                        for (let mutation of mutations) {
                            // Check if nodes were added
                            if (mutation.addedNodes.length > 0) {
                                for (let node of mutation.addedNodes) {
                                    if (node.nodeType === 1) { // Element node
                                        // Check if it's a form, button, or contains them
                                        if (node.matches && (
                                            node.matches('form.cart, .single_add_to_cart_button, button[type="submit"], #munchmakers-customizer-root') ||
                                            node.querySelector && node.querySelector('form.cart, .single_add_to_cart_button, button[type="submit"], #munchmakers-customizer-root')
                                        )) {
                                            relevantChange = true;
                                            break;
                                        }
                                    }
                                }
                            }
                            if (relevantChange) break;
                        }

                        if (relevantChange) {
                            // Look for new add to cart buttons that aren't processed
                            const newAddToCartBtn = document.querySelector(
                                'form.cart .single_add_to_cart_button:not([' + MUNCHMAKERS_MARKER + ']), ' +
                                'form.cart button[type="submit"]:not(.munchmakers-start-order-btn):not([' + MUNCHMAKERS_MARKER + '])'
                            );

                            if (newAddToCartBtn) {
                                console.log('MunchMakers: Found new unprocessed add to cart button, processing...');
                                createStartOrderButton();
                            }

                            // Clean up any duplicates
                            cleanupDuplicates();
                        }
                    }, 100);
                });

                // Only observe the cart form area for better performance
                const cartForm = document.querySelector('form.cart');
                if (cartForm && cartForm.parentElement) {
                    observer.observe(cartForm.parentElement, {
                        childList: true,
                        subtree: true,
                        // Don't observe attributes for better performance
                        attributes: false,
                        characterData: false
                    });
                }
            }

            // Initialize based on document state
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initializeMunchmakers);
            } else {
                initializeMunchmakers();
            }

            // Also initialize on load event
            window.addEventListener('load', function() {
                setTimeout(initializeMunchmakers, 100);
            });

            // Handle WooCommerce AJAX events
            if (typeof jQuery !== 'undefined') {
                jQuery(document).on('found_variation reset_variations update_variation_values', function() {
                    setTimeout(() => {
                        cleanupDuplicates();

                        // Check if we need to recreate the button
                        const root = document.getElementById('munchmakers-customizer-root');
                        const addToCartBtn = document.querySelector(
                            'form.cart .single_add_to_cart_button:not([' + MUNCHMAKERS_MARKER + ']), ' +
                            'form.cart button[type="submit"]:not(.munchmakers-start-order-btn):not([' + MUNCHMAKERS_MARKER + '])'
                        );

                        if (!root && addToCartBtn) {
                            createStartOrderButton();
                        }
                    }, 100);
                });
            }
        })();
        </script>
        <!-- MunchMakers Button Positioning - End -->
        <?php
    }

    /**
     * Find default variation from available variations
     */
    private function find_default_variation( $available_variations ) {
        $default_variation = null;

        if ( ! empty( $available_variations ) ) {
            // Try to find a variation with all attributes filled
            foreach ( $available_variations as $variation ) {
                $has_empty_attribute = false;
                foreach ( $variation['attributes'] as $attr_value ) {
                    if ( empty( $attr_value ) ) {
                        $has_empty_attribute = true;
                        break;
                    }
                }
                if ( ! $has_empty_attribute ) {
                    $default_variation = $variation;
                    break;
                }
            }

            // If no complete variation found, use the first one
            if ( ! $default_variation && isset( $available_variations[0] ) ) {
                $default_variation = $available_variations[0];
            }
        }

        return $default_variation;
    }

    /**
     * Create attribute map for variations
     */
    private function create_attribute_map( $attributes, $is_variable_product ) {
        $attribute_map = array();

        if ( $is_variable_product ) {
            foreach ( $attributes as $attribute_name => $options ) {
                $attribute_map[ $attribute_name ] = array();
                foreach ( $options as $slug ) {
                    $term = get_term_by( 'slug', $slug, $attribute_name );
                    $attribute_map[ $attribute_name ][ $slug ] = $term ? $term->name : ucwords( str_replace( '-', ' ', $slug ) );
                }
            }
        }

        return $attribute_map;
    }

    /**
     * Output sticky button HTML
     */
    private function output_sticky_button_html() {
        ?>
        <!-- MunchMakers Sticky Button - Start -->
        <div class="munchmakers-sticky-start-order">
            <button type="button" class="munchmakers-start-order-btn-sticky">
                <span class="sticky-btn-icon">🛒</span>
                <span class="sticky-btn-text"><?php esc_html_e( 'Start Order', 'munchmakers-product-customizer' ); ?></span>
            </button>
        </div>
        <!-- MunchMakers Sticky Button - End -->
        <?php
    }
}