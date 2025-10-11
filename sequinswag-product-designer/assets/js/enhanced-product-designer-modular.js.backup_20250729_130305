/**
 * Enhanced Product Designer - Modular Version
 * Version: 3.3.1
 * 
 * This is the modularized version of the Enhanced Product Designer.
 * All methods have been organized into separate modules for better maintainability.
 * 
 * Generated on: C:\Users\billi\AppData\Roaming\JetBrains\PyCharm2024.2\scratches
 */

// Import helper classes
import { ImageLoader } from './modules/utils/ImageLoader.js';
import { DesignAnalytics } from './modules/utils/DesignAnalytics.js';

// Import method modules
import { initializationMethods } from './modules/core/initialization.js';
import { canvasMethods } from './modules/core/canvas.js';
import { controlMethods } from './modules/ui/controls.js';
import { mobileMethods } from './modules/ui/mobile.js';
import { lightboxMethods } from './modules/ui/lightbox.js';
import { textMethods } from './modules/features/text.js';
import { imageMethods } from './modules/features/images.js';
import { designMethods } from './modules/features/designs.js';
import { historyMethods } from './modules/features/history.js';
import { variantMethods } from './modules/features/variants.js';
import { croppingMethods } from './modules/features/cropping.js';
import { cartMethods } from './modules/features/cart.js';
import { eventMethods } from './modules/handlers/events.js';
import { utilityMethods } from './modules/utils/utilities.js';

// Prevent duplicate loading
if (typeof window.EnhancedProductDesigner === 'undefined') {

	// Main Enhanced Product Designer Class
	class EnhancedProductDesigner {
		constructor(config) {
	    this.config = config || {};
	    this.canvas = null;
	    this.fabricCanvas = null;
	    this.backgroundImage = null;
	    this.maskImage = null;
	    this.unclippedMaskImage = null;
	    this.elements = [];
	    this.savedDesigns = this.loadSavedDesigns();
	    this.currentVariantId = null;
	    this.history = [];
	    this.historyStep = -1;
	    this.isRedoing = false;
	    this.cropRect = null;
	    this.isCropping = false;
	    this.croppingObject = null;
	    this.isReordering = false;
	    this.sessionDesign = null;
	    this.isLoadingDesign = false;
	    this.preservedCanvasData = null;

	    // New feature properties
	    this.selectedObjects = [];
	    this.clipartLibrary = [];
	    this.templates = [];
	    this.productColors = [];
	    this.currentProductColor = null;
	    this.quantityInfo = null;

	    // Performance optimization
	    this.renderTimeout = null;
	    this.autoSaveInterval = null;

	    // WordPress AJAX configuration
	    this.wpAjaxConfig = {
	      url: swpdDesignerConfig?.ajax_url || '/wp-admin/admin-ajax.php',
	      nonce: swpdDesignerConfig?.nonce || '',
	    };

	    // Check if Cloudinary is available
	    if (typeof swpdDesignerConfig !== 'undefined' && swpdDesignerConfig.cloudinary) {
	      console.log('Cloudinary configuration detected:', {
	        enabled: swpdDesignerConfig.cloudinary.enabled,
	        cloud_name: !!swpdDesignerConfig.cloudinary.cloud_name,
	        upload_preset: !!swpdDesignerConfig.cloudinary.upload_preset
	      });
	    }

	    // Initialize new features
	    this.imageLoader = new ImageLoader();
	    this.analytics = new DesignAnalytics();

	    this.init();
	  }

		/**
		 * Bind all imported methods to this instance
		 */
		_bindMethods() {
			const methodGroups = [
				initializationMethods,
				canvasMethods,
				controlMethods,
				mobileMethods,
				lightboxMethods,
				textMethods,
				imageMethods,
				designMethods,
				historyMethods,
				variantMethods,
				croppingMethods,
				cartMethods,
				eventMethods,
				utilityMethods
			];

			methodGroups.forEach(methods => {
				Object.keys(methods).forEach(methodName => {
					this[methodName] = methods[methodName].bind(this);
				});
			});
		}
	}

	// Initialize the designer and set up event handlers when DOM is ready
	document.addEventListener('DOMContentLoaded', () => {
		// Initialize the designer if config is available
		if (typeof swpdDesignerConfig !== 'undefined') {
			try {
				window.customDesigner = new EnhancedProductDesigner(swpdDesignerConfig);
			} catch (error) {
				console.error('Error initializing custom designer:', error);
			}
		} else {
			console.warn('swpdDesignerConfig not found. Custom designer not initialized.');
		}

		// FIXED: Add event listener for the product page's "Customize Design" button
		document.body.addEventListener('click', function(e) {
			if (e.target && e.target.id === 'swpd-customize-design-button') {
				e.preventDefault();
				console.log('Customize Design button clicked!');

				if (window.customDesigner) {
					var productId = swpdDesignerConfig.product_id;
					var variantId = productId;

					// For variable products, find the currently selected variation ID
					var variationInput = document.querySelector('input[name="variation_id"]');
					if (variationInput && variationInput.value > 0) {
						variantId = variationInput.value;
					}

					if (variantId) {
						try {
							window.customDesigner.openLightbox(variantId);
						} catch (error) {
							console.error('Error calling openLightbox:', error);
							alert('Error opening designer: ' + error.message);
						}
					} else {
						alert('Please select product options before customizing.');
					}
				} else {
					console.error('Designer not initialized.');
					alert('Designer could not be opened. Please refresh the page and try again.');
				}
			}
		});

		// Global handler for "Edit Design" buttons in the cart
		document.body.addEventListener('click', function(e) {
			if (e.target && e.target.matches('.swpd-edit-design-button')) {
				e.preventDefault();
				console.log('Edit design handler triggered');

				const button = e.target;
				const productUrl = button.dataset.productUrl;
				const variantId = button.dataset.variantId;
				const cartKey = button.id ? button.id.replace('swpd-edit-', '') : null;

				if (cartKey && window.swpdCanvasData && window.swpdCanvasData[cartKey]) {
					let canvasData = window.swpdCanvasData[cartKey];
					if (typeof canvasData !== 'string') {
						canvasData = JSON.stringify(canvasData);
					}

					sessionStorage.setItem('edit_design_data', canvasData);
					sessionStorage.setItem('edit_design_variant', variantId.toString());
					sessionStorage.setItem('edit_cart_key', cartKey);

					window.location.href = productUrl + '?edit_design=1&variant=' + variantId;
				} else {
					console.error('Could not find canvas data');
					alert('Unable to load design data. Please refresh the page and try again.');
				}
			}
		});

		// Automatically open designer if in edit mode
		const urlParams = new URLSearchParams(window.location.search);
		if (urlParams.get('edit_design') === '1') {
			let attempts = 0;
			const initInterval = setInterval(() => {
				if (window.customDesigner && typeof window.customDesigner.openLightbox === 'function') {
					clearInterval(initInterval);
					const variantId = urlParams.get('variant');
					console.log('Auto-opening designer for edit mode.');
					window.customDesigner.openLightbox(variantId);
					// Clean up the URL
					const cleanUrl = window.location.pathname;
					window.history.replaceState({}, document.title, cleanUrl);
				} else if (++attempts > 50) { // 5-second timeout
					clearInterval(initInterval);
					console.error("Designer didn't initialize for auto-open.");
				}
			}, 100);
		}
	});

	// Make constructor available globally
	window.EnhancedProductDesigner = EnhancedProductDesigner;

} // End of duplicate prevention wrapper