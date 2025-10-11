<?php
/**
 * VSS Order Splitting Module
 * 
 * Split orders between multiple vendors/suppliers with full tracking
 * 
 * @package VendorOrderManager
 * @subpackage Modules
 * @since 7.0.2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class for Order Splitting functionality
 */
class VSS_Order_Splitting {

    /**
     * Initialize order splitting
     */
    public static function init() {
        // Admin hooks
        add_action( 'add_meta_boxes', [ self::class, 'add_split_order_metabox' ] );
        add_action( 'wp_ajax_vss_split_order', [ self::class, 'handle_split_order_ajax' ] );
        add_action( 'wp_ajax_vss_get_split_preview', [ self::class, 'get_split_preview_ajax' ] );
        
        // Order status hooks
        add_action( 'woocommerce_order_status_changed', [ self::class, 'sync_split_order_status' ], 10, 3 );
        
        // Display hooks
        add_action( 'woocommerce_admin_order_data_after_order_details', [ self::class, 'display_split_order_info' ] );
        add_filter( 'woocommerce_admin_order_item_headers', [ self::class, 'add_vendor_column_header' ] );
        add_action( 'woocommerce_admin_order_item_values', [ self::class, 'add_vendor_column_value' ], 10, 3 );
        
        // Vendor portal hooks
        add_filter( 'vss_vendor_order_items', [ self::class, 'filter_vendor_order_items' ], 10, 3 );
    }

    /**
     * Add split order metabox
     */
    public static function add_split_order_metabox() {
        add_meta_box(
            'vss_order_splitting',
            __( 'Split Order Between Vendors', 'vss' ),
            [ self::class, 'render_split_order_metabox' ],
            'shop_order',
            'side',
            'high'
        );
    }

    /**
     * Render split order metabox
     */
    public static function render_split_order_metabox( $post ) {
        $order = wc_get_order( $post->ID );
        if ( ! $order ) {
            return;
        }

        $is_split = get_post_meta( $order->get_id(), '_vss_is_split_order', true );
        $split_data = get_post_meta( $order->get_id(), '_vss_split_order_data', true );
        $parent_order = get_post_meta( $order->get_id(), '_vss_parent_order_id', true );
        $child_orders = get_post_meta( $order->get_id(), '_vss_child_order_ids', true );

        wp_nonce_field( 'vss_split_order', 'vss_split_order_nonce' );
        ?>
        
        <div class="vss-split-order-container">
            <?php if ( $parent_order ) : ?>
                <div class="vss-split-info notice notice-info">
                    <p>
                        <?php 
                        printf( 
                            __( 'This is a split order from parent order #%s', 'vss' ),
                            '<a href="' . admin_url( 'post.php?post=' . $parent_order . '&action=edit' ) . '">' . $parent_order . '</a>'
                        );
                        ?>
                    </p>
                </div>
            <?php elseif ( $is_split && ! empty( $child_orders ) ) : ?>
                <div class="vss-split-info notice notice-success">
                    <p><?php esc_html_e( 'This order has been split into multiple vendor orders:', 'vss' ); ?></p>
                    <ul>
                        <?php foreach ( $child_orders as $child_id ) : 
                            $child_order = wc_get_order( $child_id );
                            if ( $child_order ) :
                                $vendor_id = get_post_meta( $child_id, '_vss_vendor_user_id', true );
                                $vendor = get_userdata( $vendor_id );
                        ?>
                            <li>
                                <a href="<?php echo admin_url( 'post.php?post=' . $child_id . '&action=edit' ); ?>">
                                    #<?php echo $child_order->get_order_number(); ?>
                                </a>
                                - <?php echo $vendor ? esc_html( $vendor->display_name ) : __( 'Unknown Vendor', 'vss' ); ?>
                                (<?php echo wc_get_order_status_name( $child_order->get_status() ); ?>)
                            </li>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </ul>
                </div>
            <?php else : ?>
                <div class="vss-split-controls">
                    <h4><?php esc_html_e( 'Split Method', 'vss' ); ?></h4>
                    <label>
                        <input type="radio" name="vss_split_method" value="by_product" checked>
                        <?php esc_html_e( 'By Product/Item', 'vss' ); ?>
                    </label>
                    <label>
                        <input type="radio" name="vss_split_method" value="by_category">
                        <?php esc_html_e( 'By Product Category', 'vss' ); ?>
                    </label>
                    <label>
                        <input type="radio" name="vss_split_method" value="by_quantity">
                        <?php esc_html_e( 'By Quantity', 'vss' ); ?>
                    </label>
                    <label>
                        <input type="radio" name="vss_split_method" value="manual">
                        <?php esc_html_e( 'Manual Assignment', 'vss' ); ?>
                    </label>

                    <button type="button" class="button button-primary" id="vss-configure-split">
                        <?php esc_html_e( 'Configure Split', 'vss' ); ?>
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <!-- Split Configuration Modal -->
        <div id="vss-split-modal" class="vss-modal" style="display:none;">
            <div class="vss-modal-content">
                <div class="vss-modal-header">
                    <h2><?php esc_html_e( 'Configure Order Split', 'vss' ); ?></h2>
                    <button type="button" class="vss-modal-close">&times;</button>
                </div>
                <div class="vss-modal-body">
                    <div id="vss-split-configuration">
                        <!-- Dynamic content loaded here -->
                    </div>
                </div>
                <div class="vss-modal-footer">
                    <button type="button" class="button vss-modal-cancel"><?php esc_html_e( 'Cancel', 'vss' ); ?></button>
                    <button type="button" class="button button-primary" id="vss-preview-split">
                        <?php esc_html_e( 'Preview Split', 'vss' ); ?>
                    </button>
                    <button type="button" class="button button-primary" id="vss-execute-split" style="display:none;">
                        <?php esc_html_e( 'Execute Split', 'vss' ); ?>
                    </button>
                </div>
            </div>
        </div>

        <style>
        .vss-split-order-container {
            padding: 10px 0;
        }
        
        .vss-split-controls label {
            display: block;
            margin: 5px 0;
        }
        
        .vss-split-controls button {
            margin-top: 10px;
            width: 100%;
        }
        
        .vss-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 100000;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .vss-modal-content {
            background: white;
            border-radius: 8px;
            width: 90%;
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .vss-modal-header {
            padding: 20px;
            border-bottom: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .vss-modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
        }
        
        .vss-modal-body {
            padding: 20px;
        }
        
        .vss-modal-footer {
            padding: 20px;
            border-top: 1px solid #ddd;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        .vss-item-assignment {
            border: 1px solid #ddd;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
        }
        
        .vss-item-assignment h4 {
            margin-top: 0;
        }
        
        .vss-vendor-select {
            width: 100%;
            margin: 5px 0;
        }
        
        .vss-quantity-split {
            display: flex;
            gap: 10px;
            align-items: center;
            margin: 5px 0;
        }
        
        .vss-quantity-input {
            width: 80px;
        }
        
        .vss-split-preview {
            background: #f0f8ff;
            border: 1px solid #0073aa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        
        .vss-split-preview h3 {
            margin-top: 0;
            color: #0073aa;
        }
        
        .vss-preview-vendor {
            background: white;
            padding: 10px;
            margin: 10px 0;
            border-radius: 3px;
            border-left: 3px solid #0073aa;
        }
        
        .vss-preview-vendor h4 {
            margin: 0 0 10px 0;
        }
        
        .vss-preview-items {
            margin-left: 20px;
        }
        
        .vss-preview-totals {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
        }
        </style>

        <script>
        jQuery(document).ready(function($) {
            var orderId = <?php echo $order->get_id(); ?>;
            
            // Configure split button
            $('#vss-configure-split').on('click', function() {
                var method = $('input[name="vss_split_method"]:checked').val();
                loadSplitConfiguration(method);
                $('#vss-split-modal').show();
            });
            
            // Close modal
            $('.vss-modal-close, .vss-modal-cancel').on('click', function() {
                $('#vss-split-modal').hide();
            });
            
            // Load split configuration
            function loadSplitConfiguration(method) {
                var items = <?php echo json_encode( self::get_order_items_data( $order ) ); ?>;
                var vendors = <?php echo json_encode( self::get_available_vendors() ); ?>;
                var html = '';
                
                switch(method) {
                    case 'by_product':
                        html = generateProductSplitUI(items, vendors);
                        break;
                    case 'by_category':
                        html = generateCategorySplitUI(items, vendors);
                        break;
                    case 'by_quantity':
                        html = generateQuantitySplitUI(items, vendors);
                        break;
                    case 'manual':
                        html = generateManualSplitUI(items, vendors);
                        break;
                }
                
                $('#vss-split-configuration').html(html);
            }
            
            // Generate product-based split UI
            function generateProductSplitUI(items, vendors) {
                var html = '<h3><?php esc_html_e( 'Assign Products to Vendors', 'vss' ); ?></h3>';
                
                items.forEach(function(item) {
                    html += '<div class="vss-item-assignment">';
                    html += '<h4>' + item.name + ' (Qty: ' + item.quantity + ')</h4>';
                    html += '<select class="vss-vendor-select" data-item-id="' + item.id + '">';
                    html += '<option value=""><?php esc_html_e( 'Select Vendor', 'vss' ); ?></option>';
                    
                    vendors.forEach(function(vendor) {
                        html += '<option value="' + vendor.id + '">' + vendor.name + '</option>';
                    });
                    
                    html += '</select>';
                    html += '</div>';
                });
                
                return html;
            }
            
            // Generate category-based split UI
            function generateCategorySplitUI(items, vendors) {
                var categories = {};
                
                // Group items by category
                items.forEach(function(item) {
                    if (!categories[item.category]) {
                        categories[item.category] = [];
                    }
                    categories[item.category].push(item);
                });
                
                var html = '<h3><?php esc_html_e( 'Assign Categories to Vendors', 'vss' ); ?></h3>';
                
                for (var category in categories) {
                    html += '<div class="vss-item-assignment">';
                    html += '<h4>' + category + ' (' + categories[category].length + ' items)</h4>';
                    html += '<ul>';
                    categories[category].forEach(function(item) {
                        html += '<li>' + item.name + ' (Qty: ' + item.quantity + ')</li>';
                    });
                    html += '</ul>';
                    html += '<select class="vss-vendor-select" data-category="' + category + '">';
                    html += '<option value=""><?php esc_html_e( 'Select Vendor', 'vss' ); ?></option>';
                    
                    vendors.forEach(function(vendor) {
                        html += '<option value="' + vendor.id + '">' + vendor.name + '</option>';
                    });
                    
                    html += '</select>';
                    html += '</div>';
                }
                
                return html;
            }
            
            // Generate quantity-based split UI
            function generateQuantitySplitUI(items, vendors) {
                var html = '<h3><?php esc_html_e( 'Split Quantities Between Vendors', 'vss' ); ?></h3>';
                
                items.forEach(function(item) {
                    if (item.quantity > 1) {
                        html += '<div class="vss-item-assignment">';
                        html += '<h4>' + item.name + ' (Total Qty: ' + item.quantity + ')</h4>';
                        html += '<div class="vss-quantity-splits" data-item-id="' + item.id + '" data-total-qty="' + item.quantity + '">';
                        
                        // Add initial split row
                        html += '<div class="vss-quantity-split">';
                        html += '<select class="vss-vendor-select">';
                        html += '<option value=""><?php esc_html_e( 'Select Vendor', 'vss' ); ?></option>';
                        vendors.forEach(function(vendor) {
                            html += '<option value="' + vendor.id + '">' + vendor.name + '</option>';
                        });
                        html += '</select>';
                        html += '<input type="number" class="vss-quantity-input" min="1" max="' + item.quantity + '" placeholder="Qty">';
                        html += '<button type="button" class="button vss-add-split">+</button>';
                        html += '</div>';
                        
                        html += '</div>';
                        html += '<div class="vss-remaining">Remaining: <span>' + item.quantity + '</span></div>';
                        html += '</div>';
                    } else {
                        // Single quantity item
                        html += '<div class="vss-item-assignment">';
                        html += '<h4>' + item.name + ' (Qty: 1)</h4>';
                        html += '<select class="vss-vendor-select" data-item-id="' + item.id + '">';
                        html += '<option value=""><?php esc_html_e( 'Select Vendor', 'vss' ); ?></option>';
                        vendors.forEach(function(vendor) {
                            html += '<option value="' + vendor.id + '">' + vendor.name + '</option>';
                        });
                        html += '</select>';
                        html += '</div>';
                    }
                });
                
                return html;
            }
            
            // Generate manual split UI
            function generateManualSplitUI(items, vendors) {
                var html = '<h3><?php esc_html_e( 'Manual Order Split', 'vss' ); ?></h3>';
                html += '<p><?php esc_html_e( 'Create custom split configurations for complex orders.', 'vss' ); ?></p>';
                
                // Vendor assignment sections
                html += '<div id="vss-manual-splits">';
                html += '<div class="vss-manual-split" data-split-index="0">';
                html += '<h4><?php esc_html_e( 'Vendor Assignment 1', 'vss' ); ?></h4>';
                html += '<select class="vss-vendor-select">';
                html += '<option value=""><?php esc_html_e( 'Select Vendor', 'vss' ); ?></option>';
                vendors.forEach(function(vendor) {
                    html += '<option value="' + vendor.id + '">' + vendor.name + '</option>';
                });
                html += '</select>';
                
                html += '<div class="vss-items-checklist">';
                items.forEach(function(item) {
                    html += '<label>';
                    html += '<input type="checkbox" value="' + item.id + '" data-item-name="' + item.name + '">';
                    html += ' ' + item.name + ' (Qty: ' + item.quantity + ')';
                    html += '</label><br>';
                });
                html += '</div>';
                html += '</div>';
                html += '</div>';
                
                html += '<button type="button" class="button" id="vss-add-vendor-split">';
                html += '<?php esc_html_e( 'Add Another Vendor', 'vss' ); ?>';
                html += '</button>';
                
                return html;
            }
            
            // Add split row for quantity splitting
            $(document).on('click', '.vss-add-split', function() {
                var container = $(this).closest('.vss-quantity-splits');
                var vendors = <?php echo json_encode( self::get_available_vendors() ); ?>;
                var totalQty = container.data('total-qty');
                
                var newRow = '<div class="vss-quantity-split">';
                newRow += '<select class="vss-vendor-select">';
                newRow += '<option value=""><?php esc_html_e( 'Select Vendor', 'vss' ); ?></option>';
                vendors.forEach(function(vendor) {
                    newRow += '<option value="' + vendor.id + '">' + vendor.name + '</option>';
                });
                newRow += '</select>';
                newRow += '<input type="number" class="vss-quantity-input" min="1" max="' + totalQty + '" placeholder="Qty">';
                newRow += '<button type="button" class="button vss-remove-split">-</button>';
                newRow += '</div>';
                
                container.append(newRow);
                updateRemainingQuantity(container);
            });
            
            // Remove split row
            $(document).on('click', '.vss-remove-split', function() {
                $(this).parent().remove();
                updateRemainingQuantity($(this).closest('.vss-quantity-splits'));
            });
            
            // Update remaining quantity
            $(document).on('change', '.vss-quantity-input', function() {
                updateRemainingQuantity($(this).closest('.vss-quantity-splits'));
            });
            
            function updateRemainingQuantity(container) {
                var totalQty = container.data('total-qty');
                var allocated = 0;
                
                container.find('.vss-quantity-input').each(function() {
                    allocated += parseInt($(this).val()) || 0;
                });
                
                var remaining = totalQty - allocated;
                container.siblings('.vss-remaining').find('span').text(remaining);
                
                if (remaining < 0) {
                    container.siblings('.vss-remaining').css('color', 'red');
                } else if (remaining === 0) {
                    container.siblings('.vss-remaining').css('color', 'green');
                } else {
                    container.siblings('.vss-remaining').css('color', '');
                }
            }
            
            // Add vendor for manual split
            var vendorIndex = 1;
            $(document).on('click', '#vss-add-vendor-split', function() {
                var vendors = <?php echo json_encode( self::get_available_vendors() ); ?>;
                var items = <?php echo json_encode( self::get_order_items_data( $order ) ); ?>;
                
                var html = '<div class="vss-manual-split" data-split-index="' + vendorIndex + '">';
                html += '<h4><?php esc_html_e( 'Vendor Assignment', 'vss' ); ?> ' + (vendorIndex + 1) + '</h4>';
                html += '<select class="vss-vendor-select">';
                html += '<option value=""><?php esc_html_e( 'Select Vendor', 'vss' ); ?></option>';
                vendors.forEach(function(vendor) {
                    html += '<option value="' + vendor.id + '">' + vendor.name + '</option>';
                });
                html += '</select>';
                
                html += '<div class="vss-items-checklist">';
                items.forEach(function(item) {
                    html += '<label>';
                    html += '<input type="checkbox" value="' + item.id + '" data-item-name="' + item.name + '">';
                    html += ' ' + item.name + ' (Qty: ' + item.quantity + ')';
                    html += '</label><br>';
                });
                html += '</div>';
                html += '<button type="button" class="button vss-remove-vendor-split">Remove</button>';
                html += '</div>';
                
                $('#vss-manual-splits').append(html);
                vendorIndex++;
            });
            
            // Remove vendor split
            $(document).on('click', '.vss-remove-vendor-split', function() {
                $(this).parent().remove();
            });
            
            // Preview split
            $('#vss-preview-split').on('click', function() {
                var method = $('input[name="vss_split_method"]:checked').val();
                var splitData = collectSplitData(method);
                
                if (!validateSplitData(splitData)) {
                    alert('<?php esc_html_e( 'Please complete all vendor assignments before previewing.', 'vss' ); ?>');
                    return;
                }
                
                // Generate preview
                $.post(ajaxurl, {
                    action: 'vss_get_split_preview',
                    order_id: orderId,
                    split_data: splitData,
                    nonce: '<?php echo wp_create_nonce( 'vss_split_order' ); ?>'
                }, function(response) {
                    if (response.success) {
                        displaySplitPreview(response.data);
                        $('#vss-preview-split').hide();
                        $('#vss-execute-split').show();
                    } else {
                        alert(response.data.message);
                    }
                });
            });
            
            // Collect split data
            function collectSplitData(method) {
                var data = {
                    method: method,
                    assignments: []
                };
                
                switch(method) {
                    case 'by_product':
                        $('.vss-item-assignment').each(function() {
                            var itemId = $(this).find('.vss-vendor-select').data('item-id');
                            var vendorId = $(this).find('.vss-vendor-select').val();
                            if (itemId && vendorId) {
                                data.assignments.push({
                                    item_id: itemId,
                                    vendor_id: vendorId
                                });
                            }
                        });
                        break;
                        
                    case 'by_category':
                        $('.vss-item-assignment').each(function() {
                            var category = $(this).find('.vss-vendor-select').data('category');
                            var vendorId = $(this).find('.vss-vendor-select').val();
                            if (category && vendorId) {
                                data.assignments.push({
                                    category: category,
                                    vendor_id: vendorId
                                });
                            }
                        });
                        break;
                        
                    case 'by_quantity':
                        $('.vss-item-assignment').each(function() {
                            var itemId = $(this).find('.vss-quantity-splits').data('item-id');
                            if (itemId) {
                                $(this).find('.vss-quantity-split').each(function() {
                                    var vendorId = $(this).find('.vss-vendor-select').val();
                                    var quantity = $(this).find('.vss-quantity-input').val();
                                    if (vendorId && quantity) {
                                        data.assignments.push({
                                            item_id: itemId,
                                            vendor_id: vendorId,
                                            quantity: parseInt(quantity)
                                        });
                                    }
                                });
                            } else {
                                // Single quantity item
                                var singleItemId = $(this).find('.vss-vendor-select').data('item-id');
                                var vendorId = $(this).find('.vss-vendor-select').val();
                                if (singleItemId && vendorId) {
                                    data.assignments.push({
                                        item_id: singleItemId,
                                        vendor_id: vendorId,
                                        quantity: 1
                                    });
                                }
                            }
                        });
                        break;
                        
                    case 'manual':
                        $('.vss-manual-split').each(function() {
                            var vendorId = $(this).find('.vss-vendor-select').val();
                            var items = [];
                            $(this).find('.vss-items-checklist input:checked').each(function() {
                                items.push($(this).val());
                            });
                            if (vendorId && items.length > 0) {
                                data.assignments.push({
                                    vendor_id: vendorId,
                                    items: items
                                });
                            }
                        });
                        break;
                }
                
                return data;
            }
            
            // Validate split data
            function validateSplitData(data) {
                return data.assignments.length > 0;
            }
            
            // Display split preview
            function displaySplitPreview(previewData) {
                var html = '<div class="vss-split-preview">';
                html += '<h3><?php esc_html_e( 'Split Preview', 'vss' ); ?></h3>';
                html += '<p><?php esc_html_e( 'The following vendor orders will be created:', 'vss' ); ?></p>';
                
                previewData.vendors.forEach(function(vendor) {
                    html += '<div class="vss-preview-vendor">';
                    html += '<h4>' + vendor.name + '</h4>';
                    html += '<div class="vss-preview-items">';
                    vendor.items.forEach(function(item) {
                        html += '<div>' + item.name + ' x ' + item.quantity + ' - ' + item.total + '</div>';
                    });
                    html += '</div>';
                    html += '<div class="vss-preview-totals">';
                    html += '<span><?php esc_html_e( 'Subtotal:', 'vss' ); ?> ' + vendor.subtotal + '</span>';
                    html += '<span><?php esc_html_e( 'Total:', 'vss' ); ?> ' + vendor.total + '</span>';
                    html += '</div>';
                    html += '</div>';
                });
                
                html += '</div>';
                
                $('#vss-split-configuration').html(html);
            }
            
            // Execute split
            $('#vss-execute-split').on('click', function() {
                if (!confirm('<?php esc_html_e( 'Are you sure you want to split this order? This action cannot be undone.', 'vss' ); ?>')) {
                    return;
                }
                
                var method = $('input[name="vss_split_method"]:checked').val();
                var splitData = collectSplitData(method);
                
                $(this).prop('disabled', true).text('<?php esc_html_e( 'Processing...', 'vss' ); ?>');
                
                $.post(ajaxurl, {
                    action: 'vss_split_order',
                    order_id: orderId,
                    split_data: splitData,
                    nonce: '<?php echo wp_create_nonce( 'vss_split_order' ); ?>'
                }, function(response) {
                    if (response.success) {
                        alert('<?php esc_html_e( 'Order split successfully!', 'vss' ); ?>');
                        location.reload();
                    } else {
                        alert('Error: ' + response.data.message);
                        $('#vss-execute-split').prop('disabled', false).text('<?php esc_html_e( 'Execute Split', 'vss' ); ?>');
                    }
                });
            });
        });
        </script>
        <?php
    }

    /**
     * Handle split order AJAX
     */
    public static function handle_split_order_ajax() {
        check_ajax_referer( 'vss_split_order', 'nonce' );

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( [ 'message' => __( 'Insufficient permissions', 'vss' ) ] );
        }

        $order_id = intval( $_POST['order_id'] );
        $split_data = $_POST['split_data'];

        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            wp_send_json_error( [ 'message' => __( 'Invalid order', 'vss' ) ] );
        }

        // Check if order is already split
        if ( get_post_meta( $order_id, '_vss_is_split_order', true ) ) {
            wp_send_json_error( [ 'message' => __( 'Order has already been split', 'vss' ) ] );
        }

        $result = self::split_order( $order, $split_data );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( [ 'message' => $result->get_error_message() ] );
        }

        wp_send_json_success( [
            'message' => __( 'Order split successfully', 'vss' ),
            'child_orders' => $result
        ] );
    }

    /**
     * Split order into multiple vendor orders
     */
    public static function split_order( $order, $split_data ) {
        global $wpdb;

        // Start transaction
        $wpdb->query( 'START TRANSACTION' );

        try {
            $child_orders = [];
            $method = $split_data['method'];
            $assignments = $split_data['assignments'];

            // Group assignments by vendor
            $vendor_groups = self::group_assignments_by_vendor( $assignments, $method, $order );

            foreach ( $vendor_groups as $vendor_id => $vendor_data ) {
                // Create child order
                $child_order = self::create_child_order( $order, $vendor_id, $vendor_data );
                
                if ( is_wp_error( $child_order ) ) {
                    throw new Exception( $child_order->get_error_message() );
                }

                $child_orders[] = $child_order->get_id();

                // Store vendor assignment
                update_post_meta( $child_order->get_id(), '_vss_vendor_user_id', $vendor_id );
                update_post_meta( $child_order->get_id(), '_vss_parent_order_id', $order->get_id() );
                update_post_meta( $child_order->get_id(), '_vss_split_method', $method );
                update_post_meta( $child_order->get_id(), '_vss_split_data', $vendor_data );
            }

            // Mark parent order as split
            update_post_meta( $order->get_id(), '_vss_is_split_order', 'yes' );
            update_post_meta( $order->get_id(), '_vss_child_order_ids', $child_orders );
            update_post_meta( $order->get_id(), '_vss_split_date', current_time( 'mysql' ) );
            update_post_meta( $order->get_id(), '_vss_split_data', $split_data );

            // Add order note
            $order->add_order_note( 
                sprintf( 
                    __( 'Order split into %d vendor orders: %s', 'vss' ),
                    count( $child_orders ),
                    implode( ', ', array_map( function( $id ) { return '#' . $id; }, $child_orders ) )
                )
            );

            // Update parent order status
            $order->update_status( 'on-hold', __( 'Order split into vendor orders', 'vss' ) );

            $wpdb->query( 'COMMIT' );

            return $child_orders;

        } catch ( Exception $e ) {
            $wpdb->query( 'ROLLBACK' );
            return new WP_Error( 'split_failed', $e->getMessage() );
        }
    }

    /**
     * Create child order for vendor
     */
    private static function create_child_order( $parent_order, $vendor_id, $vendor_data ) {
        // Create new order
        $child_order = wc_create_order( [
            'customer_id' => $parent_order->get_customer_id(),
            'created_via' => 'order_split',
            'parent' => $parent_order->get_id(),
        ] );

        if ( is_wp_error( $child_order ) ) {
            return $child_order;
        }

        // Copy billing and shipping addresses
        $child_order->set_address( $parent_order->get_address( 'billing' ), 'billing' );
        $child_order->set_address( $parent_order->get_address( 'shipping' ), 'shipping' );

        // Add items to child order
        foreach ( $vendor_data['items'] as $item_data ) {
            if ( $item_data['type'] === 'line_item' ) {
                $parent_item = $parent_order->get_item( $item_data['parent_item_id'] );
                
                $child_item = new WC_Order_Item_Product();
                $child_item->set_product( $parent_item->get_product() );
                $child_item->set_quantity( $item_data['quantity'] );
                $child_item->set_subtotal( $item_data['subtotal'] );
                $child_item->set_total( $item_data['total'] );
                
                // Copy item meta
                foreach ( $parent_item->get_meta_data() as $meta ) {
                    $child_item->add_meta_data( $meta->key, $meta->value );
                }
                
                $child_order->add_item( $child_item );
            }
        }

        // Calculate proportional shipping
        if ( isset( $vendor_data['shipping'] ) && $vendor_data['shipping'] > 0 ) {
            $shipping_item = new WC_Order_Item_Shipping();
            $shipping_item->set_method_title( __( 'Split Shipping', 'vss' ) );
            $shipping_item->set_total( $vendor_data['shipping'] );
            $child_order->add_item( $shipping_item );
        }

        // Calculate proportional tax
        if ( isset( $vendor_data['tax'] ) && $vendor_data['tax'] > 0 ) {
            $child_order->set_cart_tax( $vendor_data['tax'] );
        }

        // Set payment method
        $child_order->set_payment_method( $parent_order->get_payment_method() );
        $child_order->set_payment_method_title( $parent_order->get_payment_method_title() );

        // Calculate totals
        $child_order->calculate_totals();

        // Set order status
        $child_order->set_status( 'processing' );

        // Add order note
        $vendor = get_userdata( $vendor_id );
        $child_order->add_order_note( 
            sprintf( 
                __( 'Split order created from parent order #%s for vendor: %s', 'vss' ),
                $parent_order->get_id(),
                $vendor ? $vendor->display_name : 'Unknown'
            )
        );

        // Save the order
        $child_order->save();

        return $child_order;
    }

    /**
     * Group assignments by vendor
     */
    private static function group_assignments_by_vendor( $assignments, $method, $order ) {
        $groups = [];

        foreach ( $assignments as $assignment ) {
            $vendor_id = $assignment['vendor_id'];
            
            if ( ! isset( $groups[$vendor_id] ) ) {
                $groups[$vendor_id] = [
                    'items' => [],
                    'subtotal' => 0,
                    'total' => 0,
                    'shipping' => 0,
                    'tax' => 0,
                ];
            }

            switch ( $method ) {
                case 'by_product':
                case 'by_quantity':
                    $item = $order->get_item( $assignment['item_id'] );
                    if ( $item ) {
                        $quantity = isset( $assignment['quantity'] ) ? $assignment['quantity'] : $item->get_quantity();
                        $item_total = ( $item->get_total() / $item->get_quantity() ) * $quantity;
                        
                        $groups[$vendor_id]['items'][] = [
                            'type' => 'line_item',
                            'parent_item_id' => $assignment['item_id'],
                            'product_id' => $item->get_product_id(),
                            'variation_id' => $item->get_variation_id(),
                            'quantity' => $quantity,
                            'subtotal' => $item_total,
                            'total' => $item_total,
                        ];
                        
                        $groups[$vendor_id]['subtotal'] += $item_total;
                        $groups[$vendor_id]['total'] += $item_total;
                    }
                    break;

                case 'by_category':
                    // Get all items in this category
                    foreach ( $order->get_items() as $item_id => $item ) {
                        $product = $item->get_product();
                        if ( $product ) {
                            $categories = wp_get_post_terms( $product->get_id(), 'product_cat', [ 'fields' => 'names' ] );
                            if ( in_array( $assignment['category'], $categories ) ) {
                                $groups[$vendor_id]['items'][] = [
                                    'type' => 'line_item',
                                    'parent_item_id' => $item_id,
                                    'product_id' => $item->get_product_id(),
                                    'variation_id' => $item->get_variation_id(),
                                    'quantity' => $item->get_quantity(),
                                    'subtotal' => $item->get_subtotal(),
                                    'total' => $item->get_total(),
                                ];
                                
                                $groups[$vendor_id]['subtotal'] += $item->get_subtotal();
                                $groups[$vendor_id]['total'] += $item->get_total();
                            }
                        }
                    }
                    break;

                case 'manual':
                    foreach ( $assignment['items'] as $item_id ) {
                        $item = $order->get_item( $item_id );
                        if ( $item ) {
                            $groups[$vendor_id]['items'][] = [
                                'type' => 'line_item',
                                'parent_item_id' => $item_id,
                                'product_id' => $item->get_product_id(),
                                'variation_id' => $item->get_variation_id(),
                                'quantity' => $item->get_quantity(),
                                'subtotal' => $item->get_subtotal(),
                                'total' => $item->get_total(),
                            ];
                            
                            $groups[$vendor_id]['subtotal'] += $item->get_subtotal();
                            $groups[$vendor_id]['total'] += $item->get_total();
                        }
                    }
                    break;
            }
        }

        // Calculate proportional shipping and tax for each vendor
        $order_total = $order->get_subtotal();
        $order_shipping = $order->get_shipping_total();
        $order_tax = $order->get_total_tax();

        foreach ( $groups as $vendor_id => &$group ) {
            if ( $order_total > 0 ) {
                $proportion = $group['subtotal'] / $order_total;
                $group['shipping'] = round( $order_shipping * $proportion, 2 );
                $group['tax'] = round( $order_tax * $proportion, 2 );
                $group['total'] += $group['shipping'] + $group['tax'];
            }
        }

        return $groups;
    }

    /**
     * Get order items data
     */
    private static function get_order_items_data( $order ) {
        $items_data = [];

        foreach ( $order->get_items() as $item_id => $item ) {
            $product = $item->get_product();
            $categories = $product ? wp_get_post_terms( $product->get_id(), 'product_cat', [ 'fields' => 'names' ] ) : [];
            
            $items_data[] = [
                'id' => $item_id,
                'name' => $item->get_name(),
                'quantity' => $item->get_quantity(),
                'category' => ! empty( $categories ) ? $categories[0] : __( 'Uncategorized', 'vss' ),
                'total' => wc_price( $item->get_total() ),
            ];
        }

        return $items_data;
    }

    /**
     * Get available vendors
     */
    private static function get_available_vendors() {
        $vendors = [];
        
        $args = [
            'role' => 'vendor',
            'orderby' => 'display_name',
            'order' => 'ASC',
        ];
        
        $users = get_users( $args );
        
        foreach ( $users as $user ) {
            $vendors[] = [
                'id' => $user->ID,
                'name' => $user->display_name,
            ];
        }
        
        return $vendors;
    }

    /**
     * Get split preview AJAX
     */
    public static function get_split_preview_ajax() {
        check_ajax_referer( 'vss_split_order', 'nonce' );

        $order_id = intval( $_POST['order_id'] );
        $split_data = $_POST['split_data'];

        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            wp_send_json_error( [ 'message' => __( 'Invalid order', 'vss' ) ] );
        }

        $preview_data = self::generate_split_preview( $order, $split_data );
        
        wp_send_json_success( $preview_data );
    }

    /**
     * Generate split preview
     */
    private static function generate_split_preview( $order, $split_data ) {
        $vendor_groups = self::group_assignments_by_vendor( 
            $split_data['assignments'], 
            $split_data['method'], 
            $order 
        );

        $preview = [
            'vendors' => []
        ];

        foreach ( $vendor_groups as $vendor_id => $group ) {
            $vendor = get_userdata( $vendor_id );
            
            $vendor_preview = [
                'name' => $vendor ? $vendor->display_name : __( 'Unknown Vendor', 'vss' ),
                'items' => [],
                'subtotal' => wc_price( $group['subtotal'] ),
                'shipping' => wc_price( $group['shipping'] ),
                'tax' => wc_price( $group['tax'] ),
                'total' => wc_price( $group['total'] ),
            ];

            foreach ( $group['items'] as $item_data ) {
                $parent_item = $order->get_item( $item_data['parent_item_id'] );
                $vendor_preview['items'][] = [
                    'name' => $parent_item->get_name(),
                    'quantity' => $item_data['quantity'],
                    'total' => wc_price( $item_data['total'] ),
                ];
            }

            $preview['vendors'][] = $vendor_preview;
        }

        return $preview;
    }

    /**
     * Display split order info in admin
     */
    public static function display_split_order_info( $order ) {
        $is_split = get_post_meta( $order->get_id(), '_vss_is_split_order', true );
        $parent_order = get_post_meta( $order->get_id(), '_vss_parent_order_id', true );
        $child_orders = get_post_meta( $order->get_id(), '_vss_child_order_ids', true );

        if ( $parent_order ) {
            ?>
            <p class="form-field form-field-wide">
                <strong><?php esc_html_e( 'Parent Order:', 'vss' ); ?></strong>
                <a href="<?php echo admin_url( 'post.php?post=' . $parent_order . '&action=edit' ); ?>">
                    #<?php echo $parent_order; ?>
                </a>
            </p>
            <?php
        }

        if ( $is_split && ! empty( $child_orders ) ) {
            ?>
            <p class="form-field form-field-wide">
                <strong><?php esc_html_e( 'Split Orders:', 'vss' ); ?></strong><br>
                <?php
                foreach ( $child_orders as $child_id ) {
                    $child_order = wc_get_order( $child_id );
                    if ( $child_order ) {
                        $vendor_id = get_post_meta( $child_id, '_vss_vendor_user_id', true );
                        $vendor = get_userdata( $vendor_id );
                        ?>
                        <a href="<?php echo admin_url( 'post.php?post=' . $child_id . '&action=edit' ); ?>">
                            #<?php echo $child_order->get_order_number(); ?>
                        </a>
                        (<?php echo $vendor ? esc_html( $vendor->display_name ) : __( 'Unknown', 'vss' ); ?>)
                        - <?php echo wc_get_order_status_name( $child_order->get_status() ); ?><br>
                        <?php
                    }
                }
                ?>
            </p>
            <?php
        }
    }

    /**
     * Sync split order status
     */
    public static function sync_split_order_status( $order_id, $old_status, $new_status ) {
        // Check if this is a parent order
        $child_orders = get_post_meta( $order_id, '_vss_child_order_ids', true );
        
        if ( ! empty( $child_orders ) && $new_status === 'completed' ) {
            // Check if all child orders are completed
            $all_completed = true;
            foreach ( $child_orders as $child_id ) {
                $child_order = wc_get_order( $child_id );
                if ( $child_order && ! $child_order->has_status( 'completed' ) ) {
                    $all_completed = false;
                    break;
                }
            }
            
            if ( ! $all_completed ) {
                // Prevent parent from being completed if children aren't
                $order = wc_get_order( $order_id );
                $order->update_status( $old_status, __( 'Cannot complete parent order until all split orders are completed', 'vss' ) );
            }
        }
        
        // Check if this is a child order
        $parent_order_id = get_post_meta( $order_id, '_vss_parent_order_id', true );
        
        if ( $parent_order_id && $new_status === 'completed' ) {
            // Check if all sibling orders are completed
            $parent_order = wc_get_order( $parent_order_id );
            if ( $parent_order ) {
                $child_orders = get_post_meta( $parent_order_id, '_vss_child_order_ids', true );
                
                $all_completed = true;
                foreach ( $child_orders as $child_id ) {
                    if ( $child_id != $order_id ) {
                        $sibling = wc_get_order( $child_id );
                        if ( $sibling && ! $sibling->has_status( 'completed' ) ) {
                            $all_completed = false;
                            break;
                        }
                    }
                }
                
                if ( $all_completed ) {
                    // All child orders completed, complete parent
                    $parent_order->update_status( 'completed', __( 'All split orders completed', 'vss' ) );
                }
            }
        }
    }

    /**
     * Filter vendor order items
     */
    public static function filter_vendor_order_items( $items, $order, $vendor_id ) {
        // Check if this is a split order
        $parent_order_id = get_post_meta( $order->get_id(), '_vss_parent_order_id', true );
        
        if ( $parent_order_id ) {
            // This is already a split order, return all items
            return $items;
        }
        
        // Check if order has split data
        $split_data = get_post_meta( $order->get_id(), '_vss_vendor_split_items_' . $vendor_id, true );
        
        if ( ! empty( $split_data ) ) {
            // Filter items based on split data
            $filtered_items = [];
            foreach ( $items as $item_id => $item ) {
                if ( in_array( $item_id, $split_data ) ) {
                    $filtered_items[$item_id] = $item;
                }
            }
            return $filtered_items;
        }
        
        return $items;
    }

    /**
     * Add vendor column header to order items table
     */
    public static function add_vendor_column_header( $order ) {
        $is_split = get_post_meta( $order->get_id(), '_vss_is_split_order', true );
        if ( $is_split ) {
            ?>
            <th class="item-vendor"><?php esc_html_e( 'Vendor', 'vss' ); ?></th>
            <?php
        }
    }

    /**
     * Add vendor column value to order items table
     */
    public static function add_vendor_column_value( $product, $item, $item_id ) {
        $order = $item->get_order();
        $is_split = get_post_meta( $order->get_id(), '_vss_is_split_order', true );
        
        if ( $is_split ) {
            $vendor_id = get_post_meta( $order->get_id(), '_vss_item_vendor_' . $item_id, true );
            if ( $vendor_id ) {
                $vendor = get_userdata( $vendor_id );
                ?>
                <td class="item-vendor">
                    <?php echo $vendor ? esc_html( $vendor->display_name ) : __( 'Not assigned', 'vss' ); ?>
                </td>
                <?php
            } else {
                ?>
                <td class="item-vendor">—</td>
                <?php
            }
        }
    }
}

// Initialize the module
VSS_Order_Splitting::init();