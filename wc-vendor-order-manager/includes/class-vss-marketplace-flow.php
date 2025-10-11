<?php
/**
 * VSS Marketplace Flow Module
 * 
 * Enhanced self-serve marketplace functionality with improved vendor experience
 * 
 * @package VendorOrderManager
 * @subpackage Modules
 * @since 7.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Trait for Enhanced Marketplace Flow functionality
 */
trait VSS_Marketplace_Flow {

    /**
     * Initialize marketplace flow
     */
    public static function init_marketplace_flow() {
        // Initialize enhanced dashboard
        add_action( 'init', [ self::class, 'setup_marketplace_pages' ] );
        add_action( 'wp', [ self::class, 'handle_marketplace_routes' ] );
        
        // AJAX handlers for marketplace functionality
        add_action( 'wp_ajax_vss_vendor_onboarding_step', [ self::class, 'handle_onboarding_step' ] );
        add_action( 'wp_ajax_vss_quick_product_upload', [ self::class, 'handle_quick_product_upload' ] );
        add_action( 'wp_ajax_vss_bulk_product_upload', [ self::class, 'handle_bulk_product_upload' ] );
        add_action( 'wp_ajax_vss_get_product_stats', [ self::class, 'get_product_stats' ] );
        add_action( 'wp_ajax_vss_get_analytics_data', [ self::class, 'get_analytics_data' ] );
        
        // Enhanced shortcodes
        add_shortcode( 'vss_marketplace_portal', [ self::class, 'render_marketplace_portal' ] );
        add_shortcode( 'vss_vendor_analytics', [ self::class, 'render_vendor_analytics' ] );
        add_shortcode( 'vss_product_manager', [ self::class, 'render_product_manager' ] );
    }

    /**
     * Render enhanced marketplace portal
     */
    public static function render_marketplace_portal( $atts ) {
        $atts = shortcode_atts( [
            'view' => 'dashboard',
            'lang' => get_locale(),
        ], $atts );

        if ( ! is_user_logged_in() ) {
            return self::render_vendor_login_form();
        }

        if ( ! self::is_current_user_vendor() ) {
            return self::render_vendor_application_form();
        }

        // Get current view
        $view = get_query_var( 'vss_action', $atts['view'] );
        
        ob_start();
        ?>
        <div class="vss-marketplace-portal" data-lang="<?php echo esc_attr( $atts['lang'] ); ?>">
            <?php self::render_marketplace_navigation( $view ); ?>
            
            <div class="vss-marketplace-content">
                <?php
                switch ( $view ) {
                    case 'dashboard':
                        self::render_enhanced_dashboard();
                        break;
                    case 'products':
                        self::render_product_manager();
                        break;
                    case 'analytics':
                        self::render_vendor_analytics();
                        break;
                    case 'profile':
                        self::render_vendor_profile();
                        break;
                    case 'orders':
                        self::render_vendor_orders();
                        break;
                    case 'support':
                        self::render_vendor_support();
                        break;
                    default:
                        self::render_enhanced_dashboard();
                }
                ?>
            </div>
        </div>
        
        <!-- Load marketplace scripts -->
        <script>
        if (typeof vssMarketplace === 'undefined') {
            window.vssMarketplace = {
                init: function() {
                    this.bindEvents();
                    this.initCharts();
                    this.checkNotifications();
                },
                
                bindEvents: function() {
                    jQuery('.vss-nav-tab').on('click', this.handleNavigation);
                    jQuery('.vss-quick-action').on('click', this.handleQuickActions);
                    jQuery('.vss-analytics-filter').on('change', this.updateAnalytics);
                },
                
                handleNavigation: function(e) {
                    e.preventDefault();
                    const target = jQuery(this).data('target');
                    window.location.hash = target;
                    vssMarketplace.loadView(target);
                },
                
                loadView: function(view) {
                    jQuery.post(vss_frontend_ajax.ajax_url, {
                        action: 'vss_load_marketplace_view',
                        view: view,
                        nonce: vss_frontend_ajax.nonce
                    }, function(response) {
                        if (response.success) {
                            jQuery('.vss-marketplace-content').html(response.data);
                        }
                    });
                },
                
                initCharts: function() {
                    if (typeof Chart !== 'undefined' && jQuery('#analytics-chart').length) {
                        // Initialize analytics charts
                    }
                },
                
                checkNotifications: function() {
                    setInterval(function() {
                        jQuery.post(vss_frontend_ajax.ajax_url, {
                            action: 'vss_check_notifications',
                            nonce: vss_frontend_ajax.nonce
                        }, function(response) {
                            if (response.success && response.data.count > 0) {
                                jQuery('.vss-notification-bell').addClass('has-notifications');
                            }
                        });
                    }, 30000); // Check every 30 seconds
                }
            };
            
            jQuery(document).ready(function() {
                vssMarketplace.init();
            });
        }
        </script>
        <?php
        return ob_get_clean();
    }

    /**
     * Render marketplace navigation
     */
    private static function render_marketplace_navigation( $current_view ) {
        $vendor_id = get_current_user_id();
        $vendor_data = get_userdata( $vendor_id );
        $preferred_lang = get_user_meta( $vendor_id, 'vss_preferred_language', true );
        $is_chinese = ( $preferred_lang === 'zh_CN' );
        
        $nav_items = [
            'dashboard' => [
                'zh' => '仪表板',
                'en' => 'Dashboard',
                'icon' => 'dashicons-dashboard'
            ],
            'products' => [
                'zh' => '产品管理',
                'en' => 'Products',
                'icon' => 'dashicons-products'
            ],
            'analytics' => [
                'zh' => '数据分析',
                'en' => 'Analytics', 
                'icon' => 'dashicons-chart-line'
            ],
            'orders' => [
                'zh' => '订单管理',
                'en' => 'Orders',
                'icon' => 'dashicons-clipboard'
            ],
            'profile' => [
                'zh' => '个人资料',
                'en' => 'Profile',
                'icon' => 'dashicons-admin-users'
            ],
            'support' => [
                'zh' => '客户支持',
                'en' => 'Support',
                'icon' => 'dashicons-sos'
            ]
        ];
        ?>
        
        <div class="vss-marketplace-header">
            <div class="vss-header-top">
                <div class="vss-vendor-info">
                    <div class="vss-avatar">
                        <?php echo get_avatar( $vendor_id, 40 ); ?>
                    </div>
                    <div class="vss-vendor-details">
                        <h3><?php echo esc_html( $vendor_data->display_name ); ?></h3>
                        <span class="vss-vendor-status">
                            <?php echo $is_chinese ? '活跃供应商' : 'Active Vendor'; ?>
                        </span>
                    </div>
                </div>
                
                <div class="vss-header-actions">
                    <div class="vss-notifications">
                        <button class="vss-notification-bell">
                            <span class="dashicons dashicons-bell"></span>
                            <span class="vss-notification-count" style="display: none;">0</span>
                        </button>
                    </div>
                    
                    <div class="vss-language-switcher">
                        <select id="vss-lang-switcher">
                            <option value="zh_CN" <?php selected( $preferred_lang, 'zh_CN' ); ?>>中文</option>
                            <option value="en_US" <?php selected( $preferred_lang, 'en_US' ); ?>>English</option>
                        </select>
                    </div>
                    
                    <div class="vss-user-menu">
                        <button class="vss-user-menu-toggle">
                            <span class="dashicons dashicons-admin-users"></span>
                        </button>
                        <ul class="vss-user-menu-dropdown">
                            <li><a href="<?php echo esc_url( wp_logout_url() ); ?>">
                                <?php echo $is_chinese ? '退出登录' : 'Logout'; ?>
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <nav class="vss-marketplace-nav">
                <ul class="vss-nav-tabs">
                    <?php foreach ( $nav_items as $key => $item ) : ?>
                        <li>
                            <a href="#<?php echo esc_attr( $key ); ?>" 
                               class="vss-nav-tab <?php echo $current_view === $key ? 'active' : ''; ?>"
                               data-target="<?php echo esc_attr( $key ); ?>">
                                <span class="dashicons <?php echo esc_attr( $item['icon'] ); ?>"></span>
                                <span class="nav-text">
                                    <?php echo esc_html( $is_chinese ? $item['zh'] : $item['en'] ); ?>
                                </span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Language switcher
            $('#vss-lang-switcher').on('change', function() {
                const lang = $(this).val();
                $.post(vss_frontend_ajax.ajax_url, {
                    action: 'vss_switch_language',
                    language: lang,
                    nonce: vss_frontend_ajax.nonce
                }, function(response) {
                    if (response.success) {
                        location.reload();
                    }
                });
            });
            
            // User menu toggle
            $('.vss-user-menu-toggle').on('click', function() {
                $('.vss-user-menu-dropdown').toggle();
            });
            
            // Close dropdown when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.vss-user-menu').length) {
                    $('.vss-user-menu-dropdown').hide();
                }
            });
        });
        </script>
        <?php
    }

    /**
     * Render enhanced dashboard
     */
    private static function render_enhanced_dashboard() {
        $vendor_id = get_current_user_id();
        $preferred_lang = get_user_meta( $vendor_id, 'vss_preferred_language', true );
        $is_chinese = ( $preferred_lang === 'zh_CN' );
        
        // Get enhanced analytics
        $analytics = self::get_vendor_marketplace_analytics( $vendor_id );
        ?>
        
        <div class="vss-enhanced-dashboard">
            <!-- Welcome Section -->
            <div class="vss-welcome-section">
                <h1><?php echo $is_chinese ? '欢迎回来！' : 'Welcome back!'; ?></h1>
                <p><?php echo $is_chinese ? '这是您的业务概览' : "Here's your business overview"; ?></p>
                
                <!-- Quick Stats -->
                <div class="vss-quick-stats">
                    <div class="vss-stat-card">
                        <div class="stat-icon products">
                            <span class="dashicons dashicons-products"></span>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo esc_html( $analytics['total_products'] ); ?></div>
                            <div class="stat-label"><?php echo $is_chinese ? '产品总数' : 'Total Products'; ?></div>
                        </div>
                        <div class="stat-trend positive">
                            <span>+<?php echo esc_html( $analytics['products_growth'] ); ?>%</span>
                        </div>
                    </div>
                    
                    <div class="vss-stat-card">
                        <div class="stat-icon views">
                            <span class="dashicons dashicons-visibility"></span>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo esc_html( number_format( $analytics['total_views'] ) ); ?></div>
                            <div class="stat-label"><?php echo $is_chinese ? '产品浏览量' : 'Product Views'; ?></div>
                        </div>
                        <div class="stat-trend positive">
                            <span>+<?php echo esc_html( $analytics['views_growth'] ); ?>%</span>
                        </div>
                    </div>
                    
                    <div class="vss-stat-card">
                        <div class="stat-icon cart">
                            <span class="dashicons dashicons-cart"></span>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo esc_html( $analytics['cart_additions'] ); ?></div>
                            <div class="stat-label"><?php echo $is_chinese ? '加入购物车' : 'Cart Additions'; ?></div>
                        </div>
                        <div class="stat-trend positive">
                            <span>+<?php echo esc_html( $analytics['cart_growth'] ); ?>%</span>
                        </div>
                    </div>
                    
                    <div class="vss-stat-card">
                        <div class="stat-icon questions">
                            <span class="dashicons dashicons-editor-help"></span>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo esc_html( $analytics['pending_questions'] ); ?></div>
                            <div class="stat-label"><?php echo $is_chinese ? '待回复咨询' : 'Pending Questions'; ?></div>
                        </div>
                        <?php if ( $analytics['pending_questions'] > 0 ) : ?>
                            <div class="stat-alert">
                                <span class="dashicons dashicons-warning"></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Action Cards -->
            <div class="vss-action-cards">
                <div class="vss-action-card primary">
                    <div class="card-header">
                        <h3><?php echo $is_chinese ? '快速上传产品' : 'Quick Product Upload'; ?></h3>
                        <span class="dashicons dashicons-upload"></span>
                    </div>
                    <p><?php echo $is_chinese ? '快速添加新产品到您的店铺' : 'Quickly add new products to your store'; ?></p>
                    <button class="vss-btn primary vss-quick-upload">
                        <?php echo $is_chinese ? '开始上传' : 'Start Upload'; ?>
                    </button>
                </div>
                
                <div class="vss-action-card">
                    <div class="card-header">
                        <h3><?php echo $is_chinese ? '批量导入' : 'Bulk Import'; ?></h3>
                        <span class="dashicons dashicons-database-import"></span>
                    </div>
                    <p><?php echo $is_chinese ? '使用CSV文件批量上传产品' : 'Upload products in bulk using CSV'; ?></p>
                    <button class="vss-btn vss-bulk-upload">
                        <?php echo $is_chinese ? '批量导入' : 'Bulk Import'; ?>
                    </button>
                </div>
                
                <div class="vss-action-card">
                    <div class="card-header">
                        <h3><?php echo $is_chinese ? '查看分析' : 'View Analytics'; ?></h3>
                        <span class="dashicons dashicons-chart-line"></span>
                    </div>
                    <p><?php echo $is_chinese ? '了解您的产品表现' : 'Understand your product performance'; ?></p>
                    <button class="vss-btn vss-view-analytics">
                        <?php echo $is_chinese ? '查看详情' : 'View Details'; ?>
                    </button>
                </div>
            </div>
            
            <!-- Recent Activity -->
            <div class="vss-recent-activity">
                <h3><?php echo $is_chinese ? '最近活动' : 'Recent Activity'; ?></h3>
                <div class="vss-activity-list">
                    <?php self::render_recent_vendor_activity( $vendor_id, $is_chinese ); ?>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Quick upload handler
            $('.vss-quick-upload').on('click', function() {
                vssMarketplace.loadView('products');
                setTimeout(function() {
                    $('#vss-quick-product-form').slideDown();
                }, 300);
            });
            
            // Bulk upload handler
            $('.vss-bulk-upload').on('click', function() {
                $('#vss-bulk-upload-modal').fadeIn();
            });
            
            // Analytics handler
            $('.vss-view-analytics').on('click', function() {
                vssMarketplace.loadView('analytics');
            });
        });
        </script>
        <?php
    }

    /**
     * Get vendor marketplace analytics
     */
    private static function get_vendor_marketplace_analytics( $vendor_id ) {
        global $wpdb;
        
        // Get current period stats
        $current_month_start = date( 'Y-m-01' );
        $last_month_start = date( 'Y-m-01', strtotime( '-1 month' ) );
        $last_month_end = date( 'Y-m-t', strtotime( '-1 month' ) );
        
        // Total products
        $total_products = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}vss_product_uploads WHERE vendor_id = %d AND status = 'approved'",
            $vendor_id
        ) );
        
        // Products growth
        $last_month_products = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}vss_product_uploads 
             WHERE vendor_id = %d AND status = 'approved' 
             AND approval_date BETWEEN %s AND %s",
            $vendor_id, $last_month_start, $last_month_end
        ) );
        
        $products_growth = $last_month_products > 0 ? 
            round( ( ( $total_products - $last_month_products ) / $last_month_products ) * 100, 1 ) : 
            100;
        
        // Get product views
        $total_views = $wpdb->get_var( $wpdb->prepare(
            "SELECT COALESCE(SUM(meta_value), 0) FROM {$wpdb->postmeta} pm
             JOIN {$wpdb->prefix}vss_product_uploads pu ON pm.post_id = pu.id
             WHERE pu.vendor_id = %d AND pm.meta_key = 'vss_product_views'",
            $vendor_id
        ) );
        
        // Mock data for demo - replace with actual tracking
        $analytics = [
            'total_products' => $total_products,
            'products_growth' => $products_growth,
            'total_views' => $total_views ?: rand( 1000, 5000 ),
            'views_growth' => rand( 5, 25 ),
            'cart_additions' => rand( 50, 200 ),
            'cart_growth' => rand( 10, 30 ),
            'pending_questions' => rand( 0, 5 ),
            'issues' => rand( 0, 2 ),
            'conversion_rate' => rand( 15, 35 ) / 10
        ];
        
        return apply_filters( 'vss_vendor_marketplace_analytics', $analytics, $vendor_id );
    }

    /**
     * Render recent vendor activity
     */
    private static function render_recent_vendor_activity( $vendor_id, $is_chinese = false ) {
        global $wpdb;
        
        $activities = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}vss_activity_log 
             WHERE user_id = %d 
             ORDER BY created_at DESC 
             LIMIT 10",
            $vendor_id
        ) );
        
        if ( empty( $activities ) ) {
            echo '<div class="vss-no-activity">';
            echo $is_chinese ? '暂无活动记录' : 'No recent activity';
            echo '</div>';
            return;
        }
        
        foreach ( $activities as $activity ) {
            $data = json_decode( $activity->activity_data, true );
            $time_diff = human_time_diff( strtotime( $activity->created_at ), current_time( 'timestamp' ) );
            
            echo '<div class="vss-activity-item">';
            echo '<div class="activity-icon">';
            
            switch ( $activity->activity_type ) {
                case 'product_uploaded':
                    echo '<span class="dashicons dashicons-upload"></span>';
                    $message = $is_chinese ? '上传了产品' : 'uploaded a product';
                    break;
                case 'product_approved':
                    echo '<span class="dashicons dashicons-yes-alt"></span>';
                    $message = $is_chinese ? '产品获得批准' : 'product was approved';
                    break;
                case 'order_received':
                    echo '<span class="dashicons dashicons-cart"></span>';
                    $message = $is_chinese ? '收到新订单' : 'received new order';
                    break;
                default:
                    echo '<span class="dashicons dashicons-admin-generic"></span>';
                    $message = $activity->activity_type;
            }
            
            echo '</div>';
            echo '<div class="activity-content">';
            echo '<div class="activity-message">' . esc_html( $message ) . '</div>';
            echo '<div class="activity-time">' . esc_html( $time_diff ) . '</div>';
            echo '</div>';
            echo '</div>';
        }
    }

    /**
     * Handle language switching
     */
    public static function handle_language_switch() {
        check_ajax_referer( 'vss_frontend_nonce', 'nonce' );
        
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( 'Not authenticated' );
        }
        
        $language = sanitize_key( $_POST['language'] );
        $user_id = get_current_user_id();
        
        update_user_meta( $user_id, 'vss_preferred_language', $language );
        
        wp_send_json_success( 'Language updated' );
    }
}