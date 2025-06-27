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

    public function __construct( $pricing ) {
        $this->pricing = $pricing;
        
        // Only add hooks if we're in the right context
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ), 15 );
        // Use woocommerce_before_add_to_cart_button but let JavaScript handle placement
        add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'output_customizer_html' ), 999 );
        add_action( 'wp_footer', array( $this, 'output_modal_html' ), 25 );
    }

    public function enqueue_frontend_scripts() {
        if ( ! is_product() ) {
            return;
        }

        wp_enqueue_style( 'nouislider', 'https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.7.1/nouislider.min.css', array(), '15.7.1' );
        wp_enqueue_script( 'nouislider', 'https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.7.1/nouislider.min.js', array(), '15.7.1', true );

        wp_enqueue_script( 'wc-add-to-cart' );

        wp_localize_script( 'nouislider', 'munchmakers_ajax', array(
            'ajax_url'         => admin_url( 'admin-ajax.php' ),
            'cart_url'         => wc_get_cart_url(),
            'add_to_cart_nonce' => wp_create_nonce( 'munchmakers_add_to_cart_nonce' ),
            'upload_nonce'     => wp_create_nonce( 'munchmakers_upload_nonce' ),
            'pricing_nonce'    => wp_create_nonce( 'munchmakers_pricing_nonce' ),
            'currency'         => get_woocommerce_currency_symbol(),
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
        if ( ! empty( $woocommerce_loop['loop'] ) || ! empty( $woocommerce_loop['is_shortcode'] ) || ! empty( $woocommerce_loop['name'] ) ) {
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
        static $already_output = false;
        if ( $already_output ) {
            return;
        }
        $already_output = true;
        
        // Only output the hidden fields and scripts, NOT the visible button
        // JavaScript will create and position the button properly
        ?>
        <input type="hidden" name="munchmakers_artwork_option" id="munchmakers_artwork_option" value="Upload Image">
        <input type="hidden" name="munchmakers_selected_options" id="munchmakers_selected_options" value="">
        <input type="hidden" name="munchmakers_artwork_file_urls" id="munchmakers_artwork_file_urls" value="">
        <input type="hidden" name="munchmakers_product_id" id="munchmakers_product_id" value="<?php echo esc_attr( $product->get_id() ); ?>">
        
        <?php $this->output_frontend_scripts(); ?>
        <?php
    }

    private function output_frontend_scripts() {
        ?>
        <style>
            /* 
             * Strategy: Hide elements in the product form including quantity
             * The Start Order button will be dynamically positioned right before the add to cart button
             * Users will select quantity in the modal after clicking Start Order
             */
            
            /* Initially hide ALL customizer elements to prevent jumping */
            #munchmakers-customizer-root,
            .munchmakers-start-order-btn {
                opacity: 0 !important;
                visibility: hidden !important;
                transition: opacity 0.3s ease, visibility 0.3s ease;
            }
            
            /* Only show when properly positioned in form.cart and marked ready */
            form.cart #munchmakers-customizer-root.ready,
            form.cart #munchmakers-customizer-root.ready .munchmakers-start-order-btn {
                opacity: 1 !important;
                visibility: visible !important;
            }
            
            /* Force hide WooCommerce elements only in the main product form - WP Rocket compatible */
            .single-product form.cart .variations,
            .single-product form.cart .xt_woovs-swatches-wrap,
            .single-product form.cart .quantity,
            .single-product form.cart .woocommerce-variation-add-to-cart .quantity,
            .single-product form.cart .woocommerce-variation-add-to-cart .single_add_to_cart_button:not(.munchmakers-start-order-btn),
            .single-product form.cart > .single_add_to_cart_button:not(.munchmakers-start-order-btn),
            .single-product form.cart .single_add_to_cart_button:not(.munchmakers-start-order-btn),
            .single-product form.cart button[type="submit"]:not(.munchmakers-start-order-btn),
            .single-product form.cart .button.alt:not(.munchmakers-start-order-btn),
            .single-product form.cart .zakeke-button,
            .single-product form.cart .zakeke-designer-button,
            .single-product form.cart .zakeke-launch-button,
            .single-product form.cart [data-zakeke],
            .single-product form.cart .zakeke-customizer-button,
            .single-product form.cart .woocommerce-variation-price,
            .single-product form.cart .woocommerce-variation-availability { 
                display: none !important; 
                visibility: hidden !important;
            }
            
            /* Ensure quantity is visible on all devices */
            .single-product form.cart .quantity,
            .single-product .woocommerce-variation-add-to-cart .quantity {
                display: flex !important;
                visibility: visible !important;
                opacity: 1 !important;
                position: relative !important;
                left: auto !important;
                right: auto !important;
                top: auto !important;
                bottom: auto !important;
                z-index: 1 !important;
                width: auto !important;
                height: auto !important;
                overflow: visible !important;
            }
            
            /* Force quantity visibility with higher specificity */
            .woocommerce .single-product form.cart .quantity,
            .woocommerce-page .single-product form.cart .quantity,
            body.single-product form.cart .quantity,
            form.cart .quantity,
            .quantity,
            div.quantity,
            .woocommerce-variation-add-to-cart .quantity,
            .variations_button .quantity {
                display: flex !important;
                visibility: visible !important;
                opacity: 1 !important;
            }
            
            /* Override any mobile-specific hiding */
            @media screen and (max-width: 768px) {
                .woocommerce .quantity,
                .woocommerce-page .quantity,
                form.cart .quantity,
                div.quantity,
                .single-product .quantity,
                #quantity_685e4ca2a4051,
                input.qty,
                .quantity-input-product-42924 {
                    display: flex !important;
                    visibility: visible !important;
                    opacity: 1 !important;
                }
                
                /* Ensure parent containers are visible too */
                .quantity:has(input.qty) {
                    display: flex !important;
                    visibility: visible !important;
                    opacity: 1 !important;
                }
            }
            
            /* Hide when modal is active */
            .munchmakers-active .variations,
            .munchmakers-active .quantity,
            .munchmakers-active button.single_add_to_cart_button:not(.munchmakers-start-order-btn) { 
                display: none !important; 
                visibility: hidden !important;
            }
            
            /* Ensure the container takes full width */
            #munchmakers-customizer-root {
                width: 100% !important;
                max-width: none !important;
                display: block !important;
                margin: 0 !important;
                padding: 0 !important;
                box-sizing: border-box !important;
            }
            
            /* Hide any Start Order button that's not in the proper container */
            .munchmakers-start-order-btn:not(#munchmakers-customizer-root .munchmakers-start-order-btn) {
                display: none !important;
                visibility: hidden !important;
            }
            
            /* Hide customizer root in specific unwanted locations */
            .woocommerce-product-details__short-description #munchmakers-customizer-root,
            .product-short-description #munchmakers-customizer-root,
            .summary > #munchmakers-customizer-root:first-child,
            body > #munchmakers-customizer-root,
            .entry-content > #munchmakers-customizer-root:first-child,
            .product-info #munchmakers-customizer-root,
            #munchmakers-customizer-root:not(form #munchmakers-customizer-root) {
                display: none !important;
                visibility: hidden !important;
            }

            /* Ensure parent containers don't constrain the button */
            form.cart #munchmakers-customizer-root,
            .woocommerce form.cart #munchmakers-customizer-root,
            .single-product form.cart #munchmakers-customizer-root {
                width: 100% !important;
                max-width: none !important;
                display: block !important;
            }
            
            /* Loading state for button container */
            #munchmakers-customizer-root.loading {
                min-height: 80px;
                position: relative;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            }
            
            #munchmakers-customizer-root.loading::before {
                content: '';
                width: 40px;
                height: 40px;
                border: 3px solid #f3f3f3;
                border-top: 3px solid #9DC645;
                border-radius: 50%;
                animation: munchmakers-spin 1s linear infinite;
            }
            
            @keyframes munchmakers-spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            
            /* Force Start Order button styling - Enhanced Full Width */
            .munchmakers-start-order-btn,
            button.munchmakers-start-order-btn,
            #munchmakers-customizer-root .munchmakers-start-order-btn { 
                display: block !important; 
                visibility: visible !important;
                width: 100% !important; 
                max-width: 100% !important;
                min-width: 100% !important;
                padding: 18px 32px !important; 
                background: #9DC645 !important;
                background-color: #9DC645 !important; 
                color: #fff !important; 
                border: none !important; 
                border-radius: 8px !important; 
                font-size: 18px !important; 
                font-weight: 700 !important; 
                cursor: pointer !important; 
                margin: 24px 0 !important; 
                transition: all 0.3s ease !important; 
                box-shadow: 0 4px 12px rgba(157,198,69,0.3) !important;
                letter-spacing: 0.3px !important;
                text-align: center !important;
                line-height: normal !important;
                text-transform: none !important;
                text-decoration: none !important;
                outline: none !important;
                box-sizing: border-box !important;
                flex: 1 !important;
                flex-grow: 1 !important;
                flex-shrink: 0 !important;
                position: relative !important;
                left: 0 !important;
                right: 0 !important;
                align-self: stretch !important;
                justify-self: stretch !important;
                opacity: 1 !important;
                transform: none !important;
            }
            
            .munchmakers-start-order-btn:hover,
            button.munchmakers-start-order-btn:hover,
            #munchmakers-customizer-root .munchmakers-start-order-btn:hover { 
                background: #8AB83A !important;
                background-color: #8AB83A !important; 
                transform: translateY(-2px) !important;
                box-shadow: 0 6px 16px rgba(157,198,69,0.4) !important;
            }
            
            .munchmakers-start-order-btn:focus,
            button.munchmakers-start-order-btn:focus,
            #munchmakers-customizer-root .munchmakers-start-order-btn:focus {
                background: #8AB83A !important;
                background-color: #8AB83A !important;
                outline: 2px solid #9DC645 !important;
                outline-offset: 2px !important;
            }

            .munchmakers-start-order-btn:active,
            button.munchmakers-start-order-btn:active,
            #munchmakers-customizer-root .munchmakers-start-order-btn:active {
                transform: translateY(0) !important;
                box-shadow: 0 2px 8px rgba(157,198,69,0.3) !important;
            }
            
            /* Sticky Start Order Button - WP Rocket compatible */
            .munchmakers-sticky-start-order {
                position: fixed !important;
                bottom: 0 !important;
                left: 0 !important;
                right: 0 !important;
                z-index: 999999 !important;
                background: #fff !important;
                border-top: 2px solid #9DC645 !important;
                box-shadow: 0 -4px 20px rgba(0,0,0,0.15) !important;
                padding: 12px 15px !important;
                backdrop-filter: blur(10px) !important;
                -webkit-backdrop-filter: blur(10px) !important;
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
                box-shadow: 0 6px 20px rgba(157,198,69,0.4) !important;
                letter-spacing: 0.5px !important;
                text-transform: uppercase !important;
                text-decoration: none !important;
                outline: none !important;
                box-sizing: border-box !important;
            }
            
            .munchmakers-start-order-btn-sticky:hover {
                background: linear-gradient(135deg, #8AB83A, #7AA32F) !important;
                transform: translateY(-2px) !important;
                box-shadow: 0 8px 25px rgba(157,198,69,0.5) !important;
            }
            
            .munchmakers-start-order-btn-sticky:active {
                transform: translateY(0) !important;
                box-shadow: 0 4px 15px rgba(157,198,69,0.4) !important;
            }
            
            .sticky-btn-text {
                font-size: 18px !important;
                font-weight: 700 !important;
            }
            
            .sticky-btn-icon {
                font-size: 20px !important;
                filter: drop-shadow(0 1px 2px rgba(0,0,0,0.1));
            }
            
            /* Add bottom padding to body when sticky button is visible */
            body.munchmakers-sticky-visible {
                padding-bottom: 80px !important;
            }
            
            /* Mobile responsive */
            @media (max-width: 768px) {
                .munchmakers-start-order-btn,
                button.munchmakers-start-order-btn,
                #munchmakers-customizer-root .munchmakers-start-order-btn {
                    padding: 16px 24px !important;
                    font-size: 16px !important;
                    margin: 20px 0 !important;
                }

                .munchmakers-sticky-start-order {
                    padding: 10px 12px !important;
                }
                
                .munchmakers-start-order-btn-sticky {
                    padding: 16px 24px !important;
                    font-size: 16px !important;
                }
                
                .sticky-btn-text {
                    font-size: 16px !important;
                }
                
                .sticky-btn-icon {
                    font-size: 18px !important;
                }
                
                body.munchmakers-sticky-visible {
                    padding-bottom: 70px !important;
                }
            }
        </style>
        <script>
        (function() {
            'use strict';
            
            let initialized = false;
            let retryCount = 0;
            const maxRetries = 20; // Increased retries
            
            // Immediately start watching for any Start Order buttons that might appear
            const earlyObserver = new MutationObserver(function(mutations) {
                // Look for any Start Order buttons or roots appearing in wrong places
                // Only remove if we haven't initialized yet
                if (!initialized) {
                    const allButtons = document.querySelectorAll('.munchmakers-start-order-btn');
                    allButtons.forEach(btn => {
                        if (!btn.closest('form.cart')) {
                            btn.style.display = 'none';
                            btn.remove();
                        }
                    });
                    
                    const allRoots = document.querySelectorAll('#munchmakers-customizer-root');
                    allRoots.forEach(root => {
                        if (!root.closest('form.cart')) {
                            root.style.display = 'none';
                            root.remove();
                        }
                    });
                }
            });
            
            // Start observing immediately
            earlyObserver.observe(document.body, {
                childList: true,
                subtree: true
            });
            
            // Wait for DOM to be fully ready
            function waitForCartForm() {
                const cartForm = document.querySelector('form.cart, form.variations_form');
                const addToCartBtn = document.querySelector(
                    'form.cart .single_add_to_cart_button, ' +
                    'form.cart button[type="submit"], ' +
                    'form.cart .button.alt, ' +
                    '.woocommerce-variation-add-to-cart button'
                );
                
                if (cartForm || addToCartBtn) {
                    // Form is ready, initialize
                    initializeMunchmakers();
                } else {
                    // Keep waiting
                    retryCount++;
                    if (retryCount < maxRetries) {
                        setTimeout(waitForCartForm, 250);
                    }
                }
            }
            
            // Main initialization function
            function initializeMunchmakers() {
                if (initialized) return;
                
                // Look for the actual add to cart button with multiple selectors
                const addToCartBtn = document.querySelector(
                    'form.cart .single_add_to_cart_button, ' +
                    'form.cart button[type="submit"]:not(.munchmakers-start-order-btn), ' +
                    'form.cart .button.alt, ' +
                    'form.cart button.button.alt, ' +
                    '.woocommerce-variation-add-to-cart button[type="submit"]'
                );
                
                // Also check if form exists
                const cartForm = document.querySelector('form.cart, form.variations_form');
                
                if (!addToCartBtn && !cartForm) {
                    retryCount++;
                    if (retryCount < maxRetries) {
                        setTimeout(initializeMunchmakers, 200); // Increased delay
                    }
                    return;
                }
                
                initialized = true;
                
                // Stop the early observer now that we're taking control
                if (earlyObserver) {
                    earlyObserver.disconnect();
                }
                
                // Hide unwanted elements more efficiently
                hideUnwantedElements();
                
                // Create and position our button correctly
                createStartOrderButton();
                
                // Set up event handlers
                setupEventHandlers();
                
                // Initialize sticky button
                initializeStickyButton();
                
                // Set up mutation observer for dynamic content
                setupMutationObserver();
                
                // Extra cleanup after a delay
                setTimeout(() => {
                    cleanupMisplacedElements();
                }, 500);
            }
            
            // Create and position the Start Order button
            function createStartOrderButton() {
                // Find the add to cart button - handle different selectors
                const addToCartBtn = document.querySelector(
                    'form.cart .single_add_to_cart_button, ' +
                    'form.cart button[type="submit"]:not(.munchmakers-start-order-btn), ' +
                    'form.cart .button.alt'
                );
                if (!addToCartBtn) return;
                
                // First, clean up any existing roots in wrong locations
                const existingRoots = document.querySelectorAll('#munchmakers-customizer-root');
                existingRoots.forEach(root => {
                    // Check if this root is NOT right before an add to cart button
                    const nextElement = root.nextElementSibling;
                    const isInCorrectPosition = nextElement && (
                        nextElement.classList.contains('single_add_to_cart_button') ||
                        nextElement.classList.contains('button') ||
                        nextElement.type === 'submit' ||
                        nextElement === addToCartBtn ||
                        (nextElement.tagName === 'BUTTON' && nextElement.textContent.includes('Customize'))
                    );
                    if (!isInCorrectPosition) {
                        root.remove();
                    }
                });
                
                // Check if our container already exists in the correct position
                let root = null;
                const rootBeforeBtn = addToCartBtn.previousElementSibling;
                if (rootBeforeBtn && rootBeforeBtn.id === 'munchmakers-customizer-root') {
                    root = rootBeforeBtn;
                }
                
                // If root doesn't exist in the correct position, create it
                if (!root) {
                    // Create new root
                    root = document.createElement('div');
                    root.id = 'munchmakers-customizer-root';
                    const productIdInput = addToCartBtn.closest('form').querySelector('[name="product_id"], [name="add-to-cart"]');
                    const productId = productIdInput ? productIdInput.value : '';
                    root.setAttribute('data-product-id', productId);
                    
                    // Insert right before the add to cart button
                    addToCartBtn.parentNode.insertBefore(root, addToCartBtn);
                }
                
                // Create the Start Order button if it doesn't exist
                let startBtn = root.querySelector('.munchmakers-start-order-btn');
                if (!startBtn) {
                    startBtn = document.createElement('button');
                    startBtn.type = 'button';
                    startBtn.className = 'munchmakers-start-order-btn';
                    startBtn.textContent = 'Start Order';
                    root.appendChild(startBtn);
                }
                
                // Style the button
                startBtn.style.cssText = `
                    display: block !important;
                    visibility: visible !important;
                    opacity: 1 !important;
                    width: 100% !important;
                    max-width: 100% !important;
                    padding: 18px 32px !important;
                    background: #9DC645 !important;
                    color: #fff !important;
                    border: none !important;
                    border-radius: 8px !important;
                    font-size: 18px !important;
                    font-weight: 700 !important;
                    cursor: pointer !important;
                    margin: 24px 0 !important;
                    box-shadow: 0 4px 12px rgba(157,198,69,0.3) !important;
                    position: relative !important;
                    transition: all 0.3s ease !important;
                `;
                
                
                // Hide the original add to cart button
                addToCartBtn.style.display = 'none';
                addToCartBtn.style.visibility = 'hidden';
                addToCartBtn.setAttribute('data-munchmakers-hidden', 'true');
                
                // Also hide variations and quantity in the form
                const form = addToCartBtn.closest('form.cart');
                if (form) {
                    const variations = form.querySelector('.variations');
                    if (variations) variations.style.display = 'none';
                    
                    // Hide quantity selector
                    const quantityDiv = form.querySelector('.quantity');
                    if (quantityDiv) {
                        quantityDiv.style.display = 'none';
                        quantityDiv.style.visibility = 'hidden';
                    }
                }
                
                // Final safety check: ensure our button is visible and in the right place
                if (root.nextElementSibling !== addToCartBtn) {
                    // Something went wrong, try to fix it
                    addToCartBtn.parentNode.insertBefore(root, addToCartBtn);
                }
            }
            
            // Hide unwanted elements efficiently
            function hideUnwantedElements() {
                const style = document.createElement('style');
                style.textContent = `
                    .single-product form.cart .variations,
                    .single-product form.cart .xt_woovs-swatches-wrap,
                    .single-product form.cart .quantity,
                    .single-product form.cart .single_add_to_cart_button:not(.munchmakers-start-order-btn),
                    .single-product form.cart button[type="submit"]:not(.munchmakers-start-order-btn),
                    .single-product form.cart .button.alt:not(.munchmakers-start-order-btn),
                    .single-product form.cart .woocommerce-variation-price,
                    .single-product form.cart .woocommerce-variation-availability,
                    .single-product form.cart .zakeke-button,
                    .single-product form.cart .zakeke-designer-button,
                    .single-product form.cart .zakeke-launch-button,
                    .single-product form.cart [data-zakeke],
                    .single-product form.cart .zakeke-customizer-button {
                        display: none !important;
                        visibility: hidden !important;
                        opacity: 0 !important;
                        position: absolute !important;
                        left: -9999px !important;
                    }
                `;
                document.head.appendChild(style);
            }
            
            // Set up event handlers
            function setupEventHandlers() {
                // Modal open function
                function openModal() {
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
                
                // Add click handler to main button
                const mainBtn = document.querySelector('.munchmakers-start-order-btn');
                if (mainBtn) {
                    mainBtn.removeEventListener('click', openModal); // Remove any existing listeners
                    mainBtn.addEventListener('click', openModal);
                }
                
                // Add click handler to sticky button
                const stickyBtn = document.querySelector('.munchmakers-start-order-btn-sticky');
                if (stickyBtn) {
                    stickyBtn.removeEventListener('click', openModal);
                    stickyBtn.addEventListener('click', openModal);
                }
            }
            
            // Initialize sticky button functionality
            function initializeStickyButton() {
                const startBtn = document.querySelector('.munchmakers-start-order-btn');
                const stickyContainer = document.querySelector('.munchmakers-sticky-start-order');
                
                if (!startBtn || !stickyContainer) return;
                
                let isSticky = false;
                let scrollTimeout;
                
                // Set initial styles
                stickyContainer.style.transition = 'transform 0.3s ease-out, opacity 0.3s ease-out';
                stickyContainer.style.transform = 'translateY(100%)';
                stickyContainer.style.opacity = '0';
                stickyContainer.style.display = 'none';
                
                function checkButtonVisibility() {
                    const rect = startBtn.getBoundingClientRect();
                    const windowHeight = window.innerHeight;
                    const buffer = 50;
                    
                    const isVisible = rect.bottom > -buffer && rect.top < windowHeight + buffer;
                    
                    if (!isVisible && !isSticky) {
                        // Show sticky button
                        stickyContainer.style.display = 'block';
                        stickyContainer.style.visibility = 'visible';
                        stickyContainer.style.pointerEvents = 'auto';
                        stickyContainer.classList.add('show');
                        document.body.classList.add('munchmakers-sticky-visible');
                        isSticky = true;
                        
                        setTimeout(() => {
                            stickyContainer.style.transform = 'translateY(0)';
                            stickyContainer.style.opacity = '1';
                        }, 10);
                        
                    } else if (isVisible && isSticky) {
                        // Hide sticky button
                        stickyContainer.style.transform = 'translateY(100%)';
                        stickyContainer.style.opacity = '0';
                        stickyContainer.classList.remove('show');
                        document.body.classList.remove('munchmakers-sticky-visible');
                        isSticky = false;
                        
                        setTimeout(() => {
                            if (!isSticky) {
                                stickyContainer.style.display = 'none';
                                stickyContainer.style.visibility = 'hidden';
                                stickyContainer.style.pointerEvents = 'none';
                            }
                        }, 300);
                    }
                }
                
                // Throttled scroll handler
                function handleScroll() {
                    if (scrollTimeout) clearTimeout(scrollTimeout);
                    scrollTimeout = setTimeout(checkButtonVisibility, 10);
                }
                
                // Add event listeners
                window.addEventListener('scroll', handleScroll, { passive: true });
                window.addEventListener('resize', checkButtonVisibility, { passive: true });
                
                // Initial check
                setTimeout(checkButtonVisibility, 100);
            }
            
            // Set up mutation observer for dynamic content
            function setupMutationObserver() {
                let observerTimeout;
                const observer = new MutationObserver(function(mutations) {
                    // Debounce the observer callback
                    if (observerTimeout) clearTimeout(observerTimeout);
                    observerTimeout = setTimeout(() => {
                        // Clean up any misplaced elements immediately
                        cleanupMisplacedElements();
                        
                        // Check if our button still exists
                        const btn = document.querySelector('form.cart .munchmakers-start-order-btn');
                        if (!btn) {
                            createStartOrderButton();
                            setupEventHandlers();
                        }
                        
                        // Ensure the add to cart button stays hidden
                        const addToCartBtn = document.querySelector(
                            'form.cart .single_add_to_cart_button:not(.munchmakers-start-order-btn), ' +
                            'form.cart button[type="submit"]:not(.munchmakers-start-order-btn)'
                        );
                        if (addToCartBtn && addToCartBtn.style.display !== 'none') {
                            addToCartBtn.style.display = 'none';
                            addToCartBtn.style.visibility = 'hidden';
                        }
                        
                        // Ensure quantity stays hidden
                        const quantityDiv = document.querySelector('form.cart .quantity');
                        if (quantityDiv && quantityDiv.style.display !== 'none') {
                            quantityDiv.style.display = 'none';
                            quantityDiv.style.visibility = 'hidden';
                        }
                    }, 50);
                });
                
                // Observe only the form.cart element to reduce overhead
                const cartForm = document.querySelector('form.cart');
                if (cartForm) {
                    observer.observe(cartForm, {
                        childList: true,
                        subtree: true,
                        attributes: true,
                        attributeFilter: ['style', 'class']
                    });
                }
                
                // Also observe the document body for any elements appearing outside form.cart
                observer.observe(document.body, {
                    childList: true,
                    subtree: false
                });
            }
            
            // Initialize based on document state
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initializeMunchmakers);
            } else {
                // DOM already loaded, initialize immediately
                initializeMunchmakers();
            }
            
            // Also initialize on load event for extra safety
            window.addEventListener('load', function() {
                setTimeout(initializeMunchmakers, 100);
                
                // Clean up any wrongly placed elements
                setTimeout(() => {
                    // Remove any Start Order roots that are not right before the add to cart button
                    const allRoots = document.querySelectorAll('#munchmakers-customizer-root');
                    allRoots.forEach(root => {
                        const nextElement = root.nextElementSibling;
                        const isInCorrectPosition = nextElement && (
                            nextElement.classList.contains('single_add_to_cart_button') ||
                            nextElement.classList.contains('button') ||
                            nextElement.type === 'submit'
                        );
                        if (!isInCorrectPosition) {
                            root.remove();
                        }
                    });
                    
                    // Ensure the add to cart button is hidden
                    const addToCartBtns = document.querySelectorAll(
                        'form.cart .single_add_to_cart_button:not(.munchmakers-start-order-btn), ' +
                        'form.cart button[type="submit"]:not(.munchmakers-start-order-btn), ' +
                        'form.cart .button.alt:not(.munchmakers-start-order-btn)'
                    );
                    addToCartBtns.forEach(btn => {
                        btn.style.display = 'none';
                        btn.style.visibility = 'hidden';
                    });
                    
                    // Ensure quantity is hidden
                    const quantityDivs = document.querySelectorAll('form.cart .quantity');
                    quantityDivs.forEach(qty => {
                        qty.style.display = 'none';
                        qty.style.visibility = 'hidden';
                    });
                }, 200);
            });
            
            // Handle WP Rocket and other lazy loading
            if (window.addEventListener) {
                window.addEventListener('rocket-lazy-loaded', waitForCartForm);
                window.addEventListener('lazyloaded', waitForCartForm);
            }
        })();
        
        // Additional failsafe CSS injection
        (function() {
            const style = document.createElement('style');
            style.textContent = `
                /* Emergency hide for misplaced elements */
                body > #munchmakers-customizer-root,
                body > .munchmakers-start-order-btn,
                .site-content > #munchmakers-customizer-root:first-child,
                .entry-content > #munchmakers-customizer-root:first-child {
                    display: none !important;
                }
            `;
            document.head.appendChild(style);
        })();
        </script>
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

        $product_id = $product->get_id();
        $is_variable_product = $product->is_type( 'variable' );
        $initial_pricing = $this->pricing->get_initial_pricing_data( $product_id );
        $pricing_json = json_encode( $initial_pricing );
        $available_variations = $is_variable_product ? $product->get_available_variations() : array();
        $attributes = $is_variable_product ? $product->get_variation_attributes() : array();
        
        // Find default variation
        $default_variation = $this->find_default_variation( $available_variations );
        
        // Create a map of attribute slugs to names for JS
        $attribute_map = $this->create_attribute_map( $attributes, $is_variable_product );
        $attribute_map_json = json_encode( $attribute_map );
        $default_variation_json = json_encode( $default_variation );
        
        // Get modal CSS
        $modal_css = include MUNCHMAKERS_PLUGIN_DIR . 'assets/modal-styles.php';
        
        // Output the modal with inline CSS
        echo '<style>' . $modal_css . '</style>';
        
        // Output sticky button HTML
        $this->output_sticky_button_html();
        
        // Include the modal template
        include MUNCHMAKERS_PLUGIN_DIR . 'templates/modal-template.php';
        
        // Include the modal scripts
        include MUNCHMAKERS_PLUGIN_DIR . 'assets/modal-scripts.php';
    }

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
        <!-- Sticky Start Order Button -->
        <div class="munchmakers-sticky-start-order">
            <button type="button" class="munchmakers-start-order-btn-sticky">
                <span class="sticky-btn-icon">🛒</span>
                <span class="sticky-btn-text"><?php esc_html_e( 'Start Order', 'munchmakers-product-customizer' ); ?></span>
            </button>
        </div>
        <?php
    }
}