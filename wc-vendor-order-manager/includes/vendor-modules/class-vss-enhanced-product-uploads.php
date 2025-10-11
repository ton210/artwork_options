<?php
/**
 * VSS Enhanced Product Uploads Module
 * 
 * Comprehensive product upload system with improved UI, validation, and features
 * 
 * @package VendorOrderManager
 * @subpackage Modules
 * @since 7.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Trait for Enhanced Product Upload functionality
 */
trait VSS_Enhanced_Product_Uploads {

    /**
     * Initialize enhanced product uploads
     */
    public static function init_enhanced_product_uploads() {
        // AJAX handlers
        add_action( 'wp_ajax_vss_save_product_draft', [ self::class, 'save_product_draft' ] );
        add_action( 'wp_ajax_vss_upload_product_images', [ self::class, 'upload_product_images' ] );
        add_action( 'wp_ajax_vss_get_product_templates', [ self::class, 'get_product_templates' ] );
        add_action( 'wp_ajax_vss_validate_product_data', [ self::class, 'validate_product_data' ] );
        add_action( 'wp_ajax_vss_duplicate_product', [ self::class, 'duplicate_product' ] );
        add_action( 'wp_ajax_vss_get_category_suggestions', [ self::class, 'get_category_suggestions' ] );
        
        // Enhanced database tables
        self::create_enhanced_product_tables();
    }

    /**
     * Create enhanced product tables
     */
    public static function create_enhanced_product_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        // Product analytics table
        $table_name = $wpdb->prefix . 'vss_product_analytics';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            product_id bigint(20) NOT NULL,
            date_recorded date NOT NULL,
            views_count int(11) DEFAULT 0,
            cart_additions int(11) DEFAULT 0,
            purchases int(11) DEFAULT 0,
            questions_count int(11) DEFAULT 0,
            rating_average decimal(3,2) DEFAULT 0,
            revenue decimal(10,2) DEFAULT 0,
            PRIMARY KEY (id),
            UNIQUE KEY unique_product_date (product_id, date_recorded),
            KEY product_id (product_id),
            KEY date_recorded (date_recorded)
        ) $charset_collate;";
        
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
        
        // Product questions table
        $table_name = $wpdb->prefix . 'vss_product_questions';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            product_id bigint(20) NOT NULL,
            customer_id bigint(20) DEFAULT NULL,
            customer_email varchar(255) NOT NULL,
            customer_name varchar(255) NOT NULL,
            question longtext NOT NULL,
            answer longtext DEFAULT NULL,
            status enum('pending','answered','archived') DEFAULT 'pending',
            is_public tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            answered_at datetime DEFAULT NULL,
            answered_by bigint(20) DEFAULT NULL,
            PRIMARY KEY (id),
            KEY product_id (product_id),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        dbDelta( $sql );
        
        // Product templates table
        $table_name = $wpdb->prefix . 'vss_product_templates';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            vendor_id bigint(20) NOT NULL,
            template_name varchar(255) NOT NULL,
            template_data longtext NOT NULL,
            category varchar(255) DEFAULT NULL,
            is_public tinyint(1) DEFAULT 0,
            usage_count int(11) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY vendor_id (vendor_id),
            KEY category (category)
        ) $charset_collate;";
        
        dbDelta( $sql );
    }

    /**
     * Render enhanced product upload form
     */
    public static function render_enhanced_product_form( $product_id = null ) {
        $vendor_id = get_current_user_id();
        $preferred_lang = get_user_meta( $vendor_id, 'vss_preferred_language', true );
        $is_chinese = ( $preferred_lang === 'zh_CN' );
        
        // Load existing product data if editing
        $product_data = null;
        if ( $product_id ) {
            global $wpdb;
            $product_data = $wpdb->get_row( $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}vss_product_uploads WHERE id = %d AND vendor_id = %d",
                $product_id, $vendor_id
            ), ARRAY_A );
        }
        
        ?>
        <div class="vss-enhanced-product-form">
            <div class="vss-form-header">
                <h2><?php echo $product_id ? 
                    ($is_chinese ? '编辑产品' : 'Edit Product') : 
                    ($is_chinese ? '添加新产品' : 'Add New Product'); ?></h2>
                
                <div class="vss-form-actions">
                    <button type="button" class="vss-btn secondary vss-save-draft">
                        <?php echo $is_chinese ? '保存草稿' : 'Save Draft'; ?>
                    </button>
                    <button type="button" class="vss-btn secondary vss-use-template">
                        <?php echo $is_chinese ? '使用模板' : 'Use Template'; ?>
                    </button>
                    <button type="button" class="vss-btn secondary vss-preview-product">
                        <?php echo $is_chinese ? '预览' : 'Preview'; ?>
                    </button>
                </div>
            </div>

            <form id="vss-product-upload-form" class="vss-enhanced-form" enctype="multipart/form-data">
                <?php wp_nonce_field( 'vss_product_upload', 'vss_product_nonce' ); ?>
                <input type="hidden" name="product_id" value="<?php echo esc_attr( $product_id ); ?>">
                <input type="hidden" name="vendor_id" value="<?php echo esc_attr( $vendor_id ); ?>">

                <!-- Progress Indicator -->
                <div class="vss-form-progress">
                    <div class="progress-steps">
                        <div class="step active" data-step="1">
                            <div class="step-number">1</div>
                            <div class="step-label"><?php echo $is_chinese ? '基本信息' : 'Basic Info'; ?></div>
                        </div>
                        <div class="step" data-step="2">
                            <div class="step-number">2</div>
                            <div class="step-label"><?php echo $is_chinese ? '产品详情' : 'Details'; ?></div>
                        </div>
                        <div class="step" data-step="3">
                            <div class="step-number">3</div>
                            <div class="step-label"><?php echo $is_chinese ? '图片上传' : 'Images'; ?></div>
                        </div>
                        <div class="step" data-step="4">
                            <div class="step-number">4</div>
                            <div class="step-label"><?php echo $is_chinese ? '定价' : 'Pricing'; ?></div>
                        </div>
                        <div class="step" data-step="5">
                            <div class="step-number">5</div>
                            <div class="step-label"><?php echo $is_chinese ? '提交' : 'Submit'; ?></div>
                        </div>
                    </div>
                </div>

                <!-- Step 1: Basic Information -->
                <div class="vss-form-step active" data-step="1">
                    <h3><?php echo $is_chinese ? '基本产品信息' : 'Basic Product Information'; ?></h3>
                    
                    <div class="vss-form-grid">
                        <div class="vss-form-group full-width">
                            <label><?php echo $is_chinese ? '产品名称 (中文) *' : 'Product Name (Chinese) *'; ?></label>
                            <input type="text" name="product_name_zh" required 
                                   value="<?php echo esc_attr( $product_data['product_name_zh'] ?? '' ); ?>"
                                   class="vss-input-lg vss-required">
                            <div class="vss-field-help">
                                <?php echo $is_chinese ? '请输入清晰、准确的中文产品名称' : 'Enter a clear, accurate Chinese product name'; ?>
                            </div>
                        </div>
                        
                        <div class="vss-form-group full-width">
                            <label><?php echo $is_chinese ? '产品名称 (英文)' : 'Product Name (English)'; ?></label>
                            <input type="text" name="product_name_en" 
                                   value="<?php echo esc_attr( $product_data['product_name_en'] ?? '' ); ?>"
                                   class="vss-input-lg">
                            <button type="button" class="vss-translate-btn" data-source="product_name_zh" data-target="product_name_en">
                                <?php echo $is_chinese ? '自动翻译' : 'Auto Translate'; ?>
                            </button>
                        </div>
                        
                        <div class="vss-form-group">
                            <label><?php echo $is_chinese ? '产品分类 *' : 'Category *'; ?></label>
                            <select name="category" required class="vss-select-lg vss-required">
                                <option value=""><?php echo $is_chinese ? '选择分类' : 'Select Category'; ?></option>
                                <?php self::render_category_options( $product_data['category'] ?? '', $is_chinese ); ?>
                            </select>
                        </div>
                        
                        <div class="vss-form-group">
                            <label><?php echo $is_chinese ? '子分类' : 'Subcategory'; ?></label>
                            <select name="subcategory" class="vss-select-lg" id="subcategory-select">
                                <option value=""><?php echo $is_chinese ? '选择子分类' : 'Select Subcategory'; ?></option>
                            </select>
                        </div>
                        
                        <div class="vss-form-group">
                            <label><?php echo $is_chinese ? '产品SKU' : 'Product SKU'; ?></label>
                            <input type="text" name="sku" 
                                   value="<?php echo esc_attr( $product_data['sku'] ?? '' ); ?>"
                                   class="vss-input-lg">
                            <button type="button" class="vss-generate-sku">
                                <?php echo $is_chinese ? '自动生成' : 'Generate'; ?>
                            </button>
                        </div>
                        
                        <div class="vss-form-group">
                            <label><?php echo $is_chinese ? '产品重量 (克)' : 'Product Weight (grams)'; ?></label>
                            <input type="number" name="product_weight" step="0.1" 
                                   value="<?php echo esc_attr( $product_data['product_weight'] ?? '' ); ?>"
                                   class="vss-input-lg">
                        </div>
                    </div>
                </div>

                <!-- Step 2: Product Details -->
                <div class="vss-form-step" data-step="2">
                    <h3><?php echo $is_chinese ? '产品详细信息' : 'Product Details'; ?></h3>
                    
                    <div class="vss-form-group">
                        <label><?php echo $is_chinese ? '产品描述 (中文) *' : 'Product Description (Chinese) *'; ?></label>
                        <div class="vss-editor-container">
                            <textarea name="description_zh" rows="6" required class="vss-rich-editor vss-required"><?php echo esc_textarea( $product_data['description_zh'] ?? '' ); ?></textarea>
                            <div class="vss-editor-toolbar">
                                <button type="button" class="editor-btn" data-action="bold">B</button>
                                <button type="button" class="editor-btn" data-action="italic">I</button>
                                <button type="button" class="editor-btn" data-action="bullet">•</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="vss-form-group">
                        <label><?php echo $is_chinese ? '产品描述 (英文)' : 'Product Description (English)'; ?></label>
                        <textarea name="description_en" rows="6" class="vss-rich-editor"><?php echo esc_textarea( $product_data['description_en'] ?? '' ); ?></textarea>
                        <button type="button" class="vss-translate-btn" data-source="description_zh" data-target="description_en">
                            <?php echo $is_chinese ? '自动翻译描述' : 'Auto Translate Description'; ?>
                        </button>
                    </div>
                    
                    <div class="vss-form-grid">
                        <div class="vss-form-group">
                            <label><?php echo $is_chinese ? '生产时间 (天)' : 'Production Time (days)'; ?></label>
                            <input type="number" name="production_time" min="1" max="90"
                                   value="<?php echo esc_attr( $product_data['production_time'] ?? '' ); ?>"
                                   class="vss-input-lg">
                        </div>
                        
                        <div class="vss-form-group">
                            <label><?php echo $is_chinese ? '最小订单量' : 'Minimum Order Quantity'; ?></label>
                            <input type="number" name="min_order_qty" min="1"
                                   value="<?php echo esc_attr( $product_data['min_order_qty'] ?? 1 ); ?>"
                                   class="vss-input-lg">
                        </div>
                    </div>
                    
                    <!-- Customization Options -->
                    <div class="vss-form-group">
                        <label><?php echo $is_chinese ? '定制选项' : 'Customization Options'; ?></label>
                        <div class="vss-customization-builder" id="customization-options">
                            <div class="customization-option-template" style="display: none;">
                                <div class="option-row">
                                    <input type="text" name="custom_option_name[]" placeholder="<?php echo $is_chinese ? '选项名称' : 'Option Name'; ?>">
                                    <select name="custom_option_type[]">
                                        <option value="text"><?php echo $is_chinese ? '文字' : 'Text'; ?></option>
                                        <option value="color"><?php echo $is_chinese ? '颜色' : 'Color'; ?></option>
                                        <option value="size"><?php echo $is_chinese ? '尺寸' : 'Size'; ?></option>
                                        <option value="dropdown"><?php echo $is_chinese ? '下拉菜单' : 'Dropdown'; ?></option>
                                    </select>
                                    <input type="text" name="custom_option_values[]" placeholder="<?php echo $is_chinese ? '选项值 (用逗号分隔)' : 'Option values (comma separated)'; ?>">
                                    <button type="button" class="remove-option">×</button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="vss-btn secondary" id="add-customization-option">
                            <?php echo $is_chinese ? '+ 添加定制选项' : '+ Add Customization Option'; ?>
                        </button>
                    </div>
                    
                    <!-- Target Stores -->
                    <div class="vss-form-group">
                        <label><?php echo $is_chinese ? '目标销售渠道' : 'Target Sales Channels'; ?></label>
                        <div class="vss-checkbox-group">
                            <?php
                            $target_stores = json_decode( $product_data['target_stores'] ?? '[]', true );
                            $store_options = [
                                'woocommerce' => $is_chinese ? 'WooCommerce商店' : 'WooCommerce Store',
                                'amazon' => 'Amazon',
                                'ebay' => 'eBay',
                                'etsy' => 'Etsy',
                                'taobao' => $is_chinese ? '淘宝' : 'Taobao',
                                'tmall' => $is_chinese ? '天猫' : 'Tmall'
                            ];
                            
                            foreach ( $store_options as $value => $label ) :
                            ?>
                                <label class="vss-checkbox-label">
                                    <input type="checkbox" name="target_stores[]" value="<?php echo esc_attr( $value ); ?>"
                                           <?php checked( in_array( $value, $target_stores ) ); ?>>
                                    <span class="checkmark"></span>
                                    <?php echo esc_html( $label ); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Images Upload -->
                <div class="vss-form-step" data-step="3">
                    <h3><?php echo $is_chinese ? '产品图片上传' : 'Product Images Upload'; ?></h3>
                    
                    <div class="vss-image-upload-area">
                        <div class="vss-image-categories">
                            <!-- Main Product Images -->
                            <div class="image-category">
                                <h4><?php echo $is_chinese ? '主要产品图片 *' : 'Main Product Images *'; ?></h4>
                                <p class="category-desc"><?php echo $is_chinese ? '展示产品的主要外观，建议上传3-5张高质量图片' : 'Show main product appearance, recommend 3-5 high quality images'; ?></p>
                                <div class="vss-dropzone" data-category="main_images" data-max-files="5">
                                    <div class="dropzone-content">
                                        <span class="dashicons dashicons-upload"></span>
                                        <p><?php echo $is_chinese ? '拖拽图片到这里或点击上传' : 'Drag images here or click to upload'; ?></p>
                                        <small><?php echo $is_chinese ? '支持JPG, PNG, GIF格式，最大5MB' : 'Support JPG, PNG, GIF formats, max 5MB'; ?></small>
                                    </div>
                                    <input type="file" multiple accept="image/*" class="file-input">
                                </div>
                                <div class="uploaded-images-grid" data-category="main_images"></div>
                            </div>
                            
                            <!-- Design Tool Files -->
                            <div class="image-category">
                                <h4><?php echo $is_chinese ? '设计工具文件' : 'Design Tool Files'; ?></h4>
                                <p class="category-desc"><?php echo $is_chinese ? '可编辑的设计文件，如PSD, AI, SVG等' : 'Editable design files like PSD, AI, SVG, etc.'; ?></p>
                                <div class="vss-dropzone" data-category="design_files" data-max-files="10">
                                    <div class="dropzone-content">
                                        <span class="dashicons dashicons-admin-tools"></span>
                                        <p><?php echo $is_chinese ? '上传设计源文件' : 'Upload design source files'; ?></p>
                                        <small><?php echo $is_chinese ? '支持PSD, AI, SVG, PDF等格式' : 'Support PSD, AI, SVG, PDF formats'; ?></small>
                                    </div>
                                    <input type="file" multiple accept=".psd,.ai,.svg,.pdf,.eps" class="file-input">
                                </div>
                                <div class="uploaded-files-list" data-category="design_files"></div>
                            </div>
                            
                            <!-- Finished Production Pictures -->
                            <div class="image-category">
                                <h4><?php echo $is_chinese ? '完成品实拍图' : 'Finished Production Pictures'; ?></h4>
                                <p class="category-desc"><?php echo $is_chinese ? '实际生产完成的产品照片' : 'Actual photos of finished products'; ?></p>
                                <div class="vss-dropzone" data-category="production_images" data-max-files="10">
                                    <div class="dropzone-content">
                                        <span class="dashicons dashicons-camera-alt"></span>
                                        <p><?php echo $is_chinese ? '上传完成品照片' : 'Upload finished product photos'; ?></p>
                                    </div>
                                    <input type="file" multiple accept="image/*" class="file-input">
                                </div>
                                <div class="uploaded-images-grid" data-category="production_images"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Pricing -->
                <div class="vss-form-step" data-step="4">
                    <h3><?php echo $is_chinese ? '产品定价' : 'Product Pricing'; ?></h3>
                    
                    <div class="vss-pricing-section">
                        <div class="vss-form-grid">
                            <div class="vss-form-group">
                                <label><?php echo $is_chinese ? '生产成本 (¥)' : 'Production Cost (¥)'; ?></label>
                                <input type="number" name="production_cost" step="0.01" min="0" 
                                       class="vss-input-lg vss-cost-input"
                                       value="<?php echo esc_attr( $product_data['production_cost'] ?? '' ); ?>">
                            </div>
                            
                            <div class="vss-form-group">
                                <label><?php echo $is_chinese ? '建议售价 (¥)' : 'Suggested Selling Price (¥)'; ?></label>
                                <input type="number" name="suggested_price" step="0.01" min="0" 
                                       class="vss-input-lg vss-price-input"
                                       value="<?php echo esc_attr( $product_data['suggested_price'] ?? '' ); ?>">
                            </div>
                        </div>
                        
                        <div class="vss-pricing-calculator">
                            <h4><?php echo $is_chinese ? '定价计算器' : 'Pricing Calculator'; ?></h4>
                            <div class="calculator-row">
                                <span><?php echo $is_chinese ? '生产成本:' : 'Production Cost:'; ?></span>
                                <span class="cost-display">¥0.00</span>
                            </div>
                            <div class="calculator-row">
                                <span><?php echo $is_chinese ? '平台费用 (15%):' : 'Platform Fee (15%):'; ?></span>
                                <span class="platform-fee-display">¥0.00</span>
                            </div>
                            <div class="calculator-row">
                                <span><?php echo $is_chinese ? '建议利润率:' : 'Suggested Profit Margin:'; ?></span>
                                <select id="profit-margin-select">
                                    <option value="0.3">30%</option>
                                    <option value="0.5" selected>50%</option>
                                    <option value="0.8">80%</option>
                                    <option value="1.0">100%</option>
                                    <option value="custom"><?php echo $is_chinese ? '自定义' : 'Custom'; ?></option>
                                </select>
                            </div>
                            <div class="calculator-row total">
                                <span><?php echo $is_chinese ? '推荐售价:' : 'Recommended Price:'; ?></span>
                                <span class="recommended-price-display">¥0.00</span>
                            </div>
                        </div>
                        
                        <!-- Quantity-based pricing -->
                        <div class="vss-form-group">
                            <label>
                                <input type="checkbox" name="enable_quantity_pricing" id="enable-quantity-pricing">
                                <?php echo $is_chinese ? '启用阶梯定价' : 'Enable Quantity-based Pricing'; ?>
                            </label>
                            
                            <div class="quantity-pricing-table" id="quantity-pricing-section" style="display: none;">
                                <h5><?php echo $is_chinese ? '阶梯定价设置' : 'Quantity Pricing Setup'; ?></h5>
                                <div class="pricing-tiers">
                                    <div class="tier-header">
                                        <span><?php echo $is_chinese ? '最小数量' : 'Min Qty'; ?></span>
                                        <span><?php echo $is_chinese ? '单价' : 'Unit Price'; ?></span>
                                        <span><?php echo $is_chinese ? '操作' : 'Actions'; ?></span>
                                    </div>
                                    <div class="pricing-tier">
                                        <input type="number" name="qty_min[]" placeholder="1" min="1">
                                        <input type="number" name="qty_price[]" step="0.01" min="0" placeholder="0.00">
                                        <button type="button" class="remove-tier">×</button>
                                    </div>
                                </div>
                                <button type="button" class="vss-btn secondary" id="add-pricing-tier">
                                    <?php echo $is_chinese ? '+ 添加阶梯' : '+ Add Tier'; ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 5: Review & Submit -->
                <div class="vss-form-step" data-step="5">
                    <h3><?php echo $is_chinese ? '检查并提交' : 'Review & Submit'; ?></h3>
                    
                    <div class="vss-product-preview">
                        <div class="preview-section">
                            <h4><?php echo $is_chinese ? '产品预览' : 'Product Preview'; ?></h4>
                            <div id="product-preview-content">
                                <!-- Preview will be populated by JavaScript -->
                            </div>
                        </div>
                        
                        <div class="submission-options">
                            <h4><?php echo $is_chinese ? '提交选项' : 'Submission Options'; ?></h4>
                            
                            <label class="vss-checkbox-label">
                                <input type="checkbox" name="save_as_template" id="save-as-template">
                                <span class="checkmark"></span>
                                <?php echo $is_chinese ? '保存为模板以便将来使用' : 'Save as template for future use'; ?>
                            </label>
                            
                            <div class="template-options" id="template-options" style="display: none;">
                                <input type="text" name="template_name" placeholder="<?php echo $is_chinese ? '模板名称' : 'Template name'; ?>">
                                <label class="vss-checkbox-label">
                                    <input type="checkbox" name="template_public">
                                    <span class="checkmark"></span>
                                    <?php echo $is_chinese ? '允许其他供应商使用此模板' : 'Allow other vendors to use this template'; ?>
                                </label>
                            </div>
                            
                            <div class="vss-form-group">
                                <label><?php echo $is_chinese ? '提交备注' : 'Submission Notes'; ?></label>
                                <textarea name="submission_notes" rows="3" 
                                          placeholder="<?php echo $is_chinese ? '向管理员说明任何特殊要求或注意事项...' : 'Any special requirements or notes for admin...'; ?>"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="vss-submission-actions">
                        <button type="button" class="vss-btn secondary vss-save-draft">
                            <?php echo $is_chinese ? '保存草稿' : 'Save Draft'; ?>
                        </button>
                        <button type="submit" class="vss-btn primary vss-submit-product">
                            <?php echo $is_chinese ? '提交审核' : 'Submit for Review'; ?>
                        </button>
                    </div>
                </div>

                <!-- Form Navigation -->
                <div class="vss-form-navigation">
                    <button type="button" class="vss-btn secondary" id="prev-step" style="display: none;">
                        <?php echo $is_chinese ? '上一步' : 'Previous'; ?>
                    </button>
                    <button type="button" class="vss-btn primary" id="next-step">
                        <?php echo $is_chinese ? '下一步' : 'Next'; ?>
                    </button>
                </div>
            </form>
        </div>

        <!-- Templates Modal -->
        <div id="vss-templates-modal" class="vss-modal" style="display: none;">
            <div class="vss-modal-content">
                <div class="vss-modal-header">
                    <h3><?php echo $is_chinese ? '选择产品模板' : 'Choose Product Template'; ?></h3>
                    <button class="vss-modal-close">×</button>
                </div>
                <div class="vss-modal-body">
                    <div class="vss-templates-grid" id="templates-grid">
                        <!-- Templates loaded via AJAX -->
                    </div>
                </div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            const productForm = new VSSProductForm();
            productForm.init();
        });

        class VSSProductForm {
            constructor() {
                this.currentStep = 1;
                this.totalSteps = 5;
                this.isValid = {};
                this.autoSaveTimer = null;
            }

            init() {
                this.bindEvents();
                this.initValidation();
                this.initAutoSave();
                this.initImageUploads();
                this.loadExistingData();
            }

            bindEvents() {
                const self = this;
                
                $('#next-step').on('click', () => this.nextStep());
                $('#prev-step').on('click', () => this.prevStep());
                $('.vss-save-draft').on('click', () => this.saveDraft());
                $('.vss-use-template').on('click', () => this.showTemplates());
                $('.vss-preview-product').on('click', () => this.previewProduct());
                $('#vss-product-upload-form').on('submit', (e) => this.submitProduct(e));
                
                // Auto-translate buttons
                $('.vss-translate-btn').on('click', function() {
                    const source = $('input[name="' + $(this).data('source') + '"], textarea[name="' + $(this).data('source') + '"]').val();
                    const target = $(this).data('target');
                    if (source.trim()) {
                        self.translateText(source, target);
                    }
                });
                
                // Category change handler
                $('select[name="category"]').on('change', function() {
                    self.loadSubcategories($(this).val());
                });
                
                // SKU generator
                $('.vss-generate-sku').on('click', () => this.generateSKU());
                
                // Pricing calculator
                $('.vss-cost-input, .vss-price-input').on('input', () => this.updatePricingCalculator());
                $('#profit-margin-select').on('change', () => this.updatePricingCalculator());
                
                // Template checkbox
                $('#save-as-template').on('change', function() {
                    $('#template-options').toggle($(this).is(':checked'));
                });
                
                // Quantity pricing
                $('#enable-quantity-pricing').on('change', function() {
                    $('#quantity-pricing-section').toggle($(this).is(':checked'));
                });
                
                $('#add-pricing-tier').on('click', () => this.addPricingTier());
                $(document).on('click', '.remove-tier', function() {
                    $(this).closest('.pricing-tier').remove();
                });
                
                // Customization options
                $('#add-customization-option').on('click', () => this.addCustomizationOption());
                $(document).on('click', '.remove-option', function() {
                    $(this).closest('.option-row').remove();
                });
            }

            nextStep() {
                if (this.validateCurrentStep()) {
                    if (this.currentStep < this.totalSteps) {
                        this.showStep(this.currentStep + 1);
                    }
                }
            }

            prevStep() {
                if (this.currentStep > 1) {
                    this.showStep(this.currentStep - 1);
                }
            }

            showStep(step) {
                $('.vss-form-step').removeClass('active');
                $(`.vss-form-step[data-step="${step}"]`).addClass('active');
                
                $('.progress-steps .step').removeClass('active completed');
                for (let i = 1; i < step; i++) {
                    $(`.progress-steps .step[data-step="${i}"]`).addClass('completed');
                }
                $(`.progress-steps .step[data-step="${step}"]`).addClass('active');
                
                this.currentStep = step;
                
                $('#prev-step').toggle(step > 1);
                $('#next-step').toggle(step < this.totalSteps);
                
                if (step === this.totalSteps) {
                    this.updateProductPreview();
                }
            }

            validateCurrentStep() {
                const $currentStep = $(`.vss-form-step[data-step="${this.currentStep}"]`);
                const $requiredFields = $currentStep.find('.vss-required');
                let isValid = true;

                $requiredFields.each(function() {
                    const $field = $(this);
                    const value = $field.val().trim();
                    
                    if (!value) {
                        $field.addClass('error').focus();
                        isValid = false;
                    } else {
                        $field.removeClass('error');
                    }
                });

                if (!isValid) {
                    this.showError(vss_frontend_ajax.i18n.required_fields_error || 'Please fill in all required fields');
                }

                return isValid;
            }

            initAutoSave() {
                const self = this;
                $('input, textarea, select').on('input change', function() {
                    clearTimeout(self.autoSaveTimer);
                    self.autoSaveTimer = setTimeout(() => {
                        self.saveDraft(true); // Silent save
                    }, 5000);
                });
            }

            saveDraft(silent = false) {
                const formData = new FormData($('#vss-product-upload-form')[0]);
                formData.append('action', 'vss_save_product_draft');
                formData.append('nonce', vss_frontend_ajax.nonce);

                $.ajax({
                    url: vss_frontend_ajax.ajax_url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            if (!silent) {
                                self.showSuccess('Draft saved successfully');
                            }
                            // Update product ID if new
                            if (response.data.product_id) {
                                $('input[name="product_id"]').val(response.data.product_id);
                            }
                        }
                    }
                });
            }

            translateText(text, targetField) {
                $.post(vss_frontend_ajax.ajax_url, {
                    action: 'vss_translate_product',
                    text: text,
                    target_lang: 'en',
                    nonce: vss_frontend_ajax.nonce
                }, function(response) {
                    if (response.success) {
                        $(`input[name="${targetField}"], textarea[name="${targetField}"]`).val(response.data.translated_text);
                    }
                });
            }

            submitProduct(e) {
                e.preventDefault();
                
                if (!this.validateCurrentStep()) {
                    return;
                }

                const formData = new FormData($('#vss-product-upload-form')[0]);
                formData.append('action', 'vss_upload_product');

                $.ajax({
                    url: vss_frontend_ajax.ajax_url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            window.location.href = response.data.redirect_url;
                        } else {
                            this.showError(response.data.message);
                        }
                    }.bind(this),
                    error: function() {
                        this.showError('Submission failed. Please try again.');
                    }.bind(this)
                });
            }

            showError(message) {
                // Implement error notification
                console.error(message);
            }

            showSuccess(message) {
                // Implement success notification
                console.log(message);
            }
        }
        </script>
        <?php
    }

    /**
     * Render category options
     */
    private static function render_category_options( $selected = '', $is_chinese = false ) {
        $categories = [
            'electronics' => $is_chinese ? '电子产品' : 'Electronics',
            'clothing' => $is_chinese ? '服装' : 'Clothing & Apparel',
            'home_garden' => $is_chinese ? '家居园艺' : 'Home & Garden',
            'sports' => $is_chinese ? '运动户外' : 'Sports & Outdoors',
            'beauty' => $is_chinese ? '美容护理' : 'Beauty & Personal Care',
            'toys' => $is_chinese ? '玩具游戏' : 'Toys & Games',
            'automotive' => $is_chinese ? '汽车配件' : 'Automotive',
            'books' => $is_chinese ? '图书音像' : 'Books & Media',
            'health' => $is_chinese ? '健康保健' : 'Health & Wellness',
            'industrial' => $is_chinese ? '工业用品' : 'Industrial & Scientific'
        ];

        foreach ( $categories as $value => $label ) {
            printf( 
                '<option value="%s" %s>%s</option>',
                esc_attr( $value ),
                selected( $selected, $value, false ),
                esc_html( $label )
            );
        }
    }
}