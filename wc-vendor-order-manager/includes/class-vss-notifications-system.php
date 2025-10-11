<?php
/**
 * VSS Enhanced Notifications System
 * 
 * Comprehensive notification and confirmation system for vendor marketplace
 * 
 * @package VendorOrderManager
 * @subpackage Modules
 * @since 7.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Trait for Enhanced Notifications System
 */
trait VSS_Notifications_System {

    /**
     * Initialize notifications system
     */
    public static function init_notifications_system() {
        // Create notifications tables
        self::create_notifications_tables();
        
        // AJAX handlers for notifications
        add_action( 'wp_ajax_vss_mark_notification_read', [ self::class, 'mark_notification_read' ] );
        add_action( 'wp_ajax_vss_mark_all_notifications_read', [ self::class, 'mark_all_notifications_read' ] );
        add_action( 'wp_ajax_vss_get_notifications', [ self::class, 'get_notifications_ajax' ] );
        add_action( 'wp_ajax_vss_delete_notification', [ self::class, 'delete_notification' ] );
        add_action( 'wp_ajax_vss_get_notification_count', [ self::class, 'get_notification_count' ] );
        
        // Email notification handlers
        add_action( 'wp_ajax_vss_send_custom_email', [ self::class, 'send_custom_email' ] );
        add_action( 'wp_ajax_vss_update_email_preferences', [ self::class, 'update_email_preferences' ] );
        
        // Hook into various actions for automatic notifications
        add_action( 'vss_product_uploaded', [ self::class, 'notify_product_uploaded' ] );
        add_action( 'vss_product_approved', [ self::class, 'notify_product_approved' ] );
        add_action( 'vss_product_rejected', [ self::class, 'notify_product_rejected' ] );
        add_action( 'vss_order_status_changed', [ self::class, 'notify_order_status_changed' ] );
        add_action( 'vss_question_received', [ self::class, 'notify_question_received' ] );
        add_action( 'vss_question_answered', [ self::class, 'notify_question_answered' ] );
        
        // WeChat/SMS notification hooks (if enabled)
        add_action( 'vss_send_wechat_notification', [ self::class, 'send_wechat_notification' ], 10, 3 );
        add_action( 'vss_send_sms_notification', [ self::class, 'send_sms_notification' ], 10, 3 );
        
        // Schedule notification cleanup
        if ( ! wp_next_scheduled( 'vss_cleanup_notifications' ) ) {
            wp_schedule_event( time(), 'weekly', 'vss_cleanup_notifications' );
        }
        add_action( 'vss_cleanup_notifications', [ self::class, 'cleanup_old_notifications' ] );
    }

    /**
     * Create notifications tables
     */
    public static function create_notifications_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        // Main notifications table
        $table_name = $wpdb->prefix . 'vss_notifications';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            title varchar(255) NOT NULL,
            message longtext NOT NULL,
            type enum('info','success','warning','error','urgent') DEFAULT 'info',
            action_url varchar(500) DEFAULT NULL,
            action_text varchar(100) DEFAULT NULL,
            is_read tinyint(1) DEFAULT 0,
            is_dismissed tinyint(1) DEFAULT 0,
            metadata longtext DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            read_at datetime DEFAULT NULL,
            expires_at datetime DEFAULT NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY type (type),
            KEY is_read (is_read),
            KEY created_at (created_at),
            KEY expires_at (expires_at)
        ) $charset_collate;";
        
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
        
        // Email notification preferences
        $table_name = $wpdb->prefix . 'vss_email_preferences';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            notification_type varchar(100) NOT NULL,
            email_enabled tinyint(1) DEFAULT 1,
            sms_enabled tinyint(1) DEFAULT 0,
            wechat_enabled tinyint(1) DEFAULT 0,
            frequency enum('immediate','hourly','daily','weekly') DEFAULT 'immediate',
            last_sent datetime DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY unique_user_type (user_id, notification_type),
            KEY user_id (user_id)
        ) $charset_collate;";
        
        dbDelta( $sql );
        
        // Notification templates
        $table_name = $wpdb->prefix . 'vss_notification_templates';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            template_key varchar(100) NOT NULL,
            language varchar(10) DEFAULT 'en',
            subject_template text NOT NULL,
            body_template longtext NOT NULL,
            variables longtext DEFAULT NULL,
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_template_lang (template_key, language),
            KEY template_key (template_key)
        ) $charset_collate;";
        
        dbDelta( $sql );
        
        // Initialize default templates
        self::create_default_notification_templates();
    }

    /**
     * Create default notification templates
     */
    public static function create_default_notification_templates() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'vss_notification_templates';
        
        $templates = [
            // English templates
            [
                'template_key' => 'product_uploaded',
                'language' => 'en',
                'subject_template' => 'Product Uploaded Successfully',
                'body_template' => 'Your product "{product_name}" has been uploaded and is now pending approval.',
                'variables' => json_encode(['product_name', 'product_url', 'vendor_name'])
            ],
            [
                'template_key' => 'product_approved',
                'language' => 'en',
                'subject_template' => 'Product Approved!',
                'body_template' => 'Great news! Your product "{product_name}" has been approved and is now live on the marketplace.',
                'variables' => json_encode(['product_name', 'product_url', 'approval_date'])
            ],
            [
                'template_key' => 'product_rejected',
                'language' => 'en',
                'subject_template' => 'Product Needs Updates',
                'body_template' => 'Your product "{product_name}" needs some updates before it can be approved. Reason: {rejection_reason}',
                'variables' => json_encode(['product_name', 'product_url', 'rejection_reason', 'admin_notes'])
            ],
            [
                'template_key' => 'order_received',
                'language' => 'en',
                'subject_template' => 'New Order Received - #{order_number}',
                'body_template' => 'You have received a new order #{order_number} for {order_total}. Please review and process it.',
                'variables' => json_encode(['order_number', 'order_total', 'order_url', 'customer_name'])
            ],
            [
                'template_key' => 'question_received',
                'language' => 'en',
                'subject_template' => 'New Customer Question',
                'body_template' => 'You have received a new question about "{product_name}" from {customer_name}: {question}',
                'variables' => json_encode(['product_name', 'customer_name', 'question', 'answer_url'])
            ],
            
            // Chinese templates
            [
                'template_key' => 'product_uploaded',
                'language' => 'zh_CN',
                'subject_template' => '产品上传成功',
                'body_template' => '您的产品"{product_name}"已上传成功，正在等待审核。',
                'variables' => json_encode(['product_name', 'product_url', 'vendor_name'])
            ],
            [
                'template_key' => 'product_approved',
                'language' => 'zh_CN',
                'subject_template' => '产品审核通过！',
                'body_template' => '好消息！您的产品"{product_name}"已审核通过，现已在市场上线。',
                'variables' => json_encode(['product_name', 'product_url', 'approval_date'])
            ],
            [
                'template_key' => 'product_rejected',
                'language' => 'zh_CN',
                'subject_template' => '产品需要更新',
                'body_template' => '您的产品"{product_name}"在审核通过前需要一些更新。原因：{rejection_reason}',
                'variables' => json_encode(['product_name', 'product_url', 'rejection_reason', 'admin_notes'])
            ],
            [
                'template_key' => 'order_received',
                'language' => 'zh_CN',
                'subject_template' => '收到新订单 - #{order_number}',
                'body_template' => '您收到了一个新订单#{order_number}，金额为{order_total}。请审查并处理。',
                'variables' => json_encode(['order_number', 'order_total', 'order_url', 'customer_name'])
            ],
            [
                'template_key' => 'question_received',
                'language' => 'zh_CN',
                'subject_template' => '新的客户咨询',
                'body_template' => '您收到了关于"{product_name}"的新咨询，来自{customer_name}：{question}',
                'variables' => json_encode(['product_name', 'customer_name', 'question', 'answer_url'])
            ]
        ];
        
        foreach ( $templates as $template ) {
            $existing = $wpdb->get_var( $wpdb->prepare(
                "SELECT id FROM $table_name WHERE template_key = %s AND language = %s",
                $template['template_key'],
                $template['language']
            ) );
            
            if ( ! $existing ) {
                $wpdb->insert( $table_name, $template );
            }
        }
    }

    /**
     * Create notification
     */
    public static function create_notification( $user_id, $title, $message, $type = 'info', $action_url = null, $action_text = null, $metadata = [] ) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'vss_notifications';
        
        // Set expiration (30 days from now for info, 7 days for urgent)
        $expires_at = $type === 'urgent' ? 
            date( 'Y-m-d H:i:s', strtotime( '+7 days' ) ) : 
            date( 'Y-m-d H:i:s', strtotime( '+30 days' ) );
        
        $notification_id = $wpdb->insert(
            $table_name,
            [
                'user_id' => $user_id,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'action_url' => $action_url,
                'action_text' => $action_text,
                'metadata' => json_encode( $metadata ),
                'expires_at' => $expires_at,
                'created_at' => current_time( 'mysql' )
            ]
        );
        
        // Trigger email notification if enabled
        self::maybe_send_email_notification( $user_id, $title, $message, $type );
        
        // Trigger real-time notification via WebSocket/Server-Sent Events
        do_action( 'vss_notification_created', $user_id, $notification_id, $type );
        
        return $notification_id;
    }

    /**
     * Get user notifications
     */
    public static function get_user_notifications( $user_id, $limit = 20, $offset = 0, $unread_only = false ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'vss_notifications';
        
        $where_clause = "user_id = %d AND (expires_at IS NULL OR expires_at > NOW())";
        $params = [ $user_id ];
        
        if ( $unread_only ) {
            $where_clause .= " AND is_read = 0";
        }
        
        $sql = $wpdb->prepare(
            "SELECT * FROM $table_name 
             WHERE $where_clause 
             ORDER BY created_at DESC 
             LIMIT %d OFFSET %d",
            array_merge( $params, [ $limit, $offset ] )
        );
        
        return $wpdb->get_results( $sql );
    }

    /**
     * Get notification count
     */
    public static function get_notification_count() {
        check_ajax_referer( 'vss_frontend_nonce', 'nonce' );
        
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( 'Not authenticated' );
        }
        
        $user_id = get_current_user_id();
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'vss_notifications';
        
        $unread_count = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name 
             WHERE user_id = %d AND is_read = 0 
             AND (expires_at IS NULL OR expires_at > NOW())",
            $user_id
        ) );
        
        wp_send_json_success( [
            'count' => (int) $unread_count
        ] );
    }

    /**
     * Render notifications panel
     */
    public static function render_notifications_panel( $user_id = null ) {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }
        
        $preferred_lang = get_user_meta( $user_id, 'vss_preferred_language', true );
        $is_chinese = ( $preferred_lang === 'zh_CN' );
        
        $notifications = self::get_user_notifications( $user_id, 10 );
        $unread_count = self::get_unread_notification_count( $user_id );
        ?>
        
        <div class="vss-notifications-panel">
            <div class="notifications-header">
                <h3>
                    <?php echo $is_chinese ? '通知' : 'Notifications'; ?>
                    <?php if ( $unread_count > 0 ) : ?>
                        <span class="unread-badge"><?php echo esc_html( $unread_count ); ?></span>
                    <?php endif; ?>
                </h3>
                
                <div class="notifications-controls">
                    <?php if ( $unread_count > 0 ) : ?>
                        <button class="vss-btn small mark-all-read" data-user-id="<?php echo esc_attr( $user_id ); ?>">
                            <?php echo $is_chinese ? '全部已读' : 'Mark All Read'; ?>
                        </button>
                    <?php endif; ?>
                    
                    <button class="vss-btn small secondary notifications-settings">
                        <span class="dashicons dashicons-admin-settings"></span>
                        <?php echo $is_chinese ? '设置' : 'Settings'; ?>
                    </button>
                </div>
            </div>
            
            <div class="notifications-list">
                <?php if ( empty( $notifications ) ) : ?>
                    <div class="no-notifications">
                        <div class="no-notifications-icon">
                            <span class="dashicons dashicons-bell"></span>
                        </div>
                        <p><?php echo $is_chinese ? '暂无通知' : 'No notifications'; ?></p>
                    </div>
                <?php else : ?>
                    <?php foreach ( $notifications as $notification ) : ?>
                        <?php self::render_notification_item( $notification, $is_chinese ); ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <?php if ( count( $notifications ) >= 10 ) : ?>
                <div class="notifications-footer">
                    <button class="vss-btn secondary load-more-notifications" data-user-id="<?php echo esc_attr( $user_id ); ?>" data-offset="10">
                        <?php echo $is_chinese ? '加载更多' : 'Load More'; ?>
                    </button>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Notification Settings Modal -->
        <div id="vss-notification-settings-modal" class="vss-modal" style="display: none;">
            <div class="vss-modal-content">
                <div class="vss-modal-header">
                    <h3><?php echo $is_chinese ? '通知设置' : 'Notification Settings'; ?></h3>
                    <button class="vss-modal-close">×</button>
                </div>
                <div class="vss-modal-body">
                    <?php self::render_notification_settings( $user_id, $is_chinese ); ?>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Mark notification as read
            $('.notification-item:not(.read)').on('click', function() {
                const notificationId = $(this).data('notification-id');
                const $notification = $(this);
                
                if (!$notification.hasClass('read')) {
                    $.post(vss_frontend_ajax.ajax_url, {
                        action: 'vss_mark_notification_read',
                        notification_id: notificationId,
                        nonce: vss_frontend_ajax.nonce
                    }, function(response) {
                        if (response.success) {
                            $notification.addClass('read');
                            updateNotificationCount();
                        }
                    });
                }
            });
            
            // Mark all as read
            $('.mark-all-read').on('click', function() {
                const userId = $(this).data('user-id');
                
                $.post(vss_frontend_ajax.ajax_url, {
                    action: 'vss_mark_all_notifications_read',
                    user_id: userId,
                    nonce: vss_frontend_ajax.nonce
                }, function(response) {
                    if (response.success) {
                        $('.notification-item').addClass('read');
                        $('.unread-badge, .mark-all-read').hide();
                        updateNotificationCount();
                    }
                });
            });
            
            // Load more notifications
            $('.load-more-notifications').on('click', function() {
                const userId = $(this).data('user-id');
                const offset = $(this).data('offset');
                const $button = $(this);
                
                $button.prop('disabled', true).text('<?php echo $is_chinese ? "加载中..." : "Loading..."; ?>');
                
                $.post(vss_frontend_ajax.ajax_url, {
                    action: 'vss_get_notifications',
                    user_id: userId,
                    offset: offset,
                    nonce: vss_frontend_ajax.nonce
                }, function(response) {
                    if (response.success && response.data.notifications.length > 0) {
                        $('.notifications-list').append(response.data.html);
                        $button.data('offset', offset + 10);
                        
                        if (response.data.notifications.length < 10) {
                            $button.remove();
                        } else {
                            $button.prop('disabled', false).text('<?php echo $is_chinese ? "加载更多" : "Load More"; ?>');
                        }
                    } else {
                        $button.remove();
                    }
                });
            });
            
            // Notification settings
            $('.notifications-settings').on('click', function() {
                $('#vss-notification-settings-modal').fadeIn();
            });
            
            // Update notification count in header
            function updateNotificationCount() {
                $.post(vss_frontend_ajax.ajax_url, {
                    action: 'vss_get_notification_count',
                    nonce: vss_frontend_ajax.nonce
                }, function(response) {
                    if (response.success) {
                        const count = response.data.count;
                        if (count > 0) {
                            $('.vss-notification-bell .vss-notification-count').text(count).show();
                            $('.vss-notification-bell').addClass('has-notifications');
                        } else {
                            $('.vss-notification-bell .vss-notification-count').hide();
                            $('.vss-notification-bell').removeClass('has-notifications');
                        }
                    }
                });
            }
            
            // Auto-refresh notification count every 30 seconds
            setInterval(updateNotificationCount, 30000);
        });
        </script>
        <?php
    }

    /**
     * Render notification item
     */
    private static function render_notification_item( $notification, $is_chinese = false ) {
        $metadata = json_decode( $notification->metadata, true ) ?: [];
        $time_diff = human_time_diff( strtotime( $notification->created_at ), current_time( 'timestamp' ) );
        
        $type_classes = [
            'info' => 'info',
            'success' => 'success',
            'warning' => 'warning',
            'error' => 'error',
            'urgent' => 'urgent'
        ];
        
        $type_icons = [
            'info' => 'dashicons-info',
            'success' => 'dashicons-yes-alt',
            'warning' => 'dashicons-warning',
            'error' => 'dashicons-dismiss',
            'urgent' => 'dashicons-bell'
        ];
        ?>
        
        <div class="notification-item <?php echo esc_attr( $type_classes[ $notification->type ] ); ?> <?php echo $notification->is_read ? 'read' : 'unread'; ?>"
             data-notification-id="<?php echo esc_attr( $notification->id ); ?>">
            <div class="notification-icon">
                <span class="dashicons <?php echo esc_attr( $type_icons[ $notification->type ] ); ?>"></span>
            </div>
            
            <div class="notification-content">
                <div class="notification-title"><?php echo esc_html( $notification->title ); ?></div>
                <div class="notification-message"><?php echo wp_kses_post( $notification->message ); ?></div>
                <div class="notification-time">
                    <?php echo esc_html( $time_diff ); ?> <?php echo $is_chinese ? '前' : 'ago'; ?>
                </div>
            </div>
            
            <?php if ( $notification->action_url && $notification->action_text ) : ?>
                <div class="notification-action">
                    <a href="<?php echo esc_url( $notification->action_url ); ?>" class="vss-btn small primary">
                        <?php echo esc_html( $notification->action_text ); ?>
                    </a>
                </div>
            <?php endif; ?>
            
            <div class="notification-dismiss">
                <button class="dismiss-notification" data-notification-id="<?php echo esc_attr( $notification->id ); ?>">
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            </div>
        </div>
        <?php
    }

    /**
     * Render notification settings
     */
    private static function render_notification_settings( $user_id, $is_chinese = false ) {
        $preferences = self::get_user_notification_preferences( $user_id );
        
        $notification_types = [
            'product_uploaded' => $is_chinese ? '产品上传' : 'Product Uploaded',
            'product_approved' => $is_chinese ? '产品审核通过' : 'Product Approved',
            'product_rejected' => $is_chinese ? '产品被拒绝' : 'Product Rejected',
            'order_received' => $is_chinese ? '收到订单' : 'Order Received',
            'order_status_changed' => $is_chinese ? '订单状态变更' : 'Order Status Changed',
            'question_received' => $is_chinese ? '收到咨询' : 'Question Received',
            'question_answered' => $is_chinese ? '咨询已回复' : 'Question Answered',
            'payment_received' => $is_chinese ? '收到付款' : 'Payment Received',
            'system_updates' => $is_chinese ? '系统更新' : 'System Updates'
        ];
        ?>
        
        <form id="vss-notification-preferences-form">
            <?php wp_nonce_field( 'vss_update_email_preferences', 'vss_preferences_nonce' ); ?>
            
            <div class="notification-preferences">
                <h4><?php echo $is_chinese ? '通知偏好设置' : 'Notification Preferences'; ?></h4>
                
                <table class="vss-preferences-table">
                    <thead>
                        <tr>
                            <th><?php echo $is_chinese ? '通知类型' : 'Notification Type'; ?></th>
                            <th><?php echo $is_chinese ? '邮件' : 'Email'; ?></th>
                            <th><?php echo $is_chinese ? '短信' : 'SMS'; ?></th>
                            <th><?php echo $is_chinese ? '微信' : 'WeChat'; ?></th>
                            <th><?php echo $is_chinese ? '频率' : 'Frequency'; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $notification_types as $type => $label ) : ?>
                            <?php
                            $pref = $preferences[ $type ] ?? [
                                'email_enabled' => 1,
                                'sms_enabled' => 0,
                                'wechat_enabled' => 0,
                                'frequency' => 'immediate'
                            ];
                            ?>
                            <tr>
                                <td><?php echo esc_html( $label ); ?></td>
                                <td>
                                    <label class="vss-toggle">
                                        <input type="checkbox" name="preferences[<?php echo esc_attr( $type ); ?>][email_enabled]" 
                                               value="1" <?php checked( $pref['email_enabled'] ); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </td>
                                <td>
                                    <label class="vss-toggle">
                                        <input type="checkbox" name="preferences[<?php echo esc_attr( $type ); ?>][sms_enabled]" 
                                               value="1" <?php checked( $pref['sms_enabled'] ); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </td>
                                <td>
                                    <label class="vss-toggle">
                                        <input type="checkbox" name="preferences[<?php echo esc_attr( $type ); ?>][wechat_enabled]" 
                                               value="1" <?php checked( $pref['wechat_enabled'] ); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </td>
                                <td>
                                    <select name="preferences[<?php echo esc_attr( $type ); ?>][frequency]" class="vss-select-small">
                                        <option value="immediate" <?php selected( $pref['frequency'], 'immediate' ); ?>>
                                            <?php echo $is_chinese ? '即时' : 'Immediate'; ?>
                                        </option>
                                        <option value="hourly" <?php selected( $pref['frequency'], 'hourly' ); ?>>
                                            <?php echo $is_chinese ? '每小时' : 'Hourly'; ?>
                                        </option>
                                        <option value="daily" <?php selected( $pref['frequency'], 'daily' ); ?>>
                                            <?php echo $is_chinese ? '每日' : 'Daily'; ?>
                                        </option>
                                        <option value="weekly" <?php selected( $pref['frequency'], 'weekly' ); ?>>
                                            <?php echo $is_chinese ? '每周' : 'Weekly'; ?>
                                        </option>
                                    </select>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="preferences-actions">
                    <button type="submit" class="vss-btn primary">
                        <?php echo $is_chinese ? '保存设置' : 'Save Settings'; ?>
                    </button>
                    <button type="button" class="vss-btn secondary" onclick="jQuery('#vss-notification-settings-modal').fadeOut();">
                        <?php echo $is_chinese ? '取消' : 'Cancel'; ?>
                    </button>
                </div>
            </div>
        </form>
        
        <script>
        jQuery(document).ready(function($) {
            $('#vss-notification-preferences-form').on('submit', function(e) {
                e.preventDefault();
                
                const formData = $(this).serialize();
                
                $.post(vss_frontend_ajax.ajax_url, formData + '&action=vss_update_email_preferences', function(response) {
                    if (response.success) {
                        $('#vss-notification-settings-modal').fadeOut();
                        // Show success notification
                        vssNotifications.show('<?php echo $is_chinese ? "设置已保存" : "Settings saved"; ?>', 'success');
                    } else {
                        vssNotifications.show(response.data.message || '<?php echo $is_chinese ? "保存失败" : "Failed to save"; ?>', 'error');
                    }
                });
            });
        });
        </script>
        <?php
    }

    /**
     * Get user notification preferences
     */
    private static function get_user_notification_preferences( $user_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'vss_email_preferences';
        
        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM $table_name WHERE user_id = %d",
            $user_id
        ), ARRAY_A );
        
        $preferences = [];
        foreach ( $results as $result ) {
            $preferences[ $result['notification_type'] ] = [
                'email_enabled' => (bool) $result['email_enabled'],
                'sms_enabled' => (bool) $result['sms_enabled'],
                'wechat_enabled' => (bool) $result['wechat_enabled'],
                'frequency' => $result['frequency']
            ];
        }
        
        return $preferences;
    }

    /**
     * Get unread notification count
     */
    private static function get_unread_notification_count( $user_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'vss_notifications';
        
        return (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name 
             WHERE user_id = %d AND is_read = 0 
             AND (expires_at IS NULL OR expires_at > NOW())",
            $user_id
        ) );
    }

    /**
     * Maybe send email notification
     */
    private static function maybe_send_email_notification( $user_id, $title, $message, $type ) {
        // Check if user has email notifications enabled for this type
        $preferences = self::get_user_notification_preferences( $user_id );
        
        if ( ! isset( $preferences[ $type ] ) || ! $preferences[ $type ]['email_enabled'] ) {
            return;
        }
        
        // Get user data
        $user = get_userdata( $user_id );
        if ( ! $user ) {
            return;
        }
        
        // Send email
        $subject = $title;
        $body = $message;
        
        // Add unsubscribe link
        $unsubscribe_url = add_query_arg( [
            'action' => 'vss_unsubscribe',
            'user_id' => $user_id,
            'type' => $type,
            'nonce' => wp_create_nonce( 'vss_unsubscribe_' . $user_id )
        ], home_url() );
        
        $body .= "\n\n---\n" . sprintf( 
            __( 'To unsubscribe from these notifications, click here: %s', 'vss' ), 
            $unsubscribe_url 
        );
        
        wp_mail( $user->user_email, $subject, $body );
    }

    /**
     * Notification event handlers
     */
    public static function notify_product_uploaded( $product_id, $vendor_id ) {
        $product_name = get_the_title( $product_id );
        $preferred_lang = get_user_meta( $vendor_id, 'vss_preferred_language', true );
        $is_chinese = ( $preferred_lang === 'zh_CN' );
        
        $title = $is_chinese ? '产品上传成功' : 'Product Uploaded Successfully';
        $message = $is_chinese ? 
            "您的产品「{$product_name}」已成功上传，正在等待审核。" : 
            "Your product \"{$product_name}\" has been uploaded successfully and is pending approval.";
        
        $action_url = add_query_arg( 'vss_action', 'products', vss_get_vendor_portal_url() );
        $action_text = $is_chinese ? '查看产品' : 'View Products';
        
        self::create_notification( $vendor_id, $title, $message, 'info', $action_url, $action_text, [
            'product_id' => $product_id,
            'product_name' => $product_name
        ] );
    }

    public static function notify_product_approved( $product_id, $vendor_id ) {
        $product_name = get_the_title( $product_id );
        $preferred_lang = get_user_meta( $vendor_id, 'vss_preferred_language', true );
        $is_chinese = ( $preferred_lang === 'zh_CN' );
        
        $title = $is_chinese ? '产品审核通过！' : 'Product Approved!';
        $message = $is_chinese ? 
            "恭喜！您的产品「{$product_name}」已审核通过，现已在市场上线。" : 
            "Congratulations! Your product \"{$product_name}\" has been approved and is now live on the marketplace.";
        
        $action_url = get_permalink( $product_id );
        $action_text = $is_chinese ? '查看产品' : 'View Product';
        
        self::create_notification( $vendor_id, $title, $message, 'success', $action_url, $action_text, [
            'product_id' => $product_id,
            'product_name' => $product_name
        ] );
    }

    public static function notify_product_rejected( $product_id, $vendor_id, $rejection_reason = '' ) {
        $product_name = get_the_title( $product_id );
        $preferred_lang = get_user_meta( $vendor_id, 'vss_preferred_language', true );
        $is_chinese = ( $preferred_lang === 'zh_CN' );
        
        $title = $is_chinese ? '产品需要修改' : 'Product Needs Updates';
        $message = $is_chinese ? 
            "您的产品「{$product_name}」需要修改后才能通过审核。" : 
            "Your product \"{$product_name}\" needs updates before it can be approved.";
        
        if ( $rejection_reason ) {
            $message .= $is_chinese ? "原因：{$rejection_reason}" : " Reason: {$rejection_reason}";
        }
        
        $action_url = add_query_arg( [
            'vss_action' => 'edit_product',
            'product_id' => $product_id
        ], vss_get_vendor_portal_url() );
        $action_text = $is_chinese ? '编辑产品' : 'Edit Product';
        
        self::create_notification( $vendor_id, $title, $message, 'warning', $action_url, $action_text, [
            'product_id' => $product_id,
            'product_name' => $product_name,
            'rejection_reason' => $rejection_reason
        ] );
    }

    public static function notify_order_status_changed( $order_id, $old_status, $new_status ) {
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }
        
        $vendor_id = get_post_meta( $order_id, '_vss_vendor_user_id', true );
        if ( ! $vendor_id ) {
            return;
        }
        
        $preferred_lang = get_user_meta( $vendor_id, 'vss_preferred_language', true );
        $is_chinese = ( $preferred_lang === 'zh_CN' );
        
        $status_labels = [
            'zh_CN' => [
                'processing' => '处理中',
                'shipped' => '已发货',
                'completed' => '已完成',
                'cancelled' => '已取消'
            ],
            'en' => [
                'processing' => 'Processing',
                'shipped' => 'Shipped', 
                'completed' => 'Completed',
                'cancelled' => 'Cancelled'
            ]
        ];
        
        $lang = $is_chinese ? 'zh_CN' : 'en';
        $new_status_label = $status_labels[ $lang ][ $new_status ] ?? $new_status;
        
        $title = $is_chinese ? '订单状态更新' : 'Order Status Updated';
        $message = $is_chinese ? 
            "订单 #{$order->get_order_number()} 状态已更新为：{$new_status_label}" :
            "Order #{$order->get_order_number()} status has been updated to: {$new_status_label}";
        
        $action_url = add_query_arg( [
            'vss_action' => 'view_order',
            'order_id' => $order_id
        ], vss_get_vendor_portal_url() );
        $action_text = $is_chinese ? '查看订单' : 'View Order';
        
        self::create_notification( $vendor_id, $title, $message, 'info', $action_url, $action_text, [
            'order_id' => $order_id,
            'old_status' => $old_status,
            'new_status' => $new_status
        ] );
    }

    public static function notify_question_received( $question_id, $product_id, $vendor_id ) {
        global $wpdb;
        $question_data = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}vss_product_questions WHERE id = %d",
            $question_id
        ) );
        
        if ( ! $question_data ) {
            return;
        }
        
        $product_name = get_the_title( $product_id );
        $preferred_lang = get_user_meta( $vendor_id, 'vss_preferred_language', true );
        $is_chinese = ( $preferred_lang === 'zh_CN' );
        
        $title = $is_chinese ? '收到新的客户咨询' : 'New Customer Question';
        $message = $is_chinese ? 
            "您收到关于产品「{$product_name}」的新咨询：" . wp_trim_words( $question_data->question, 20 ) :
            "You received a new question about \"{$product_name}\": " . wp_trim_words( $question_data->question, 20 );
        
        $action_url = add_query_arg( [
            'vss_action' => 'answer_question',
            'question_id' => $question_id
        ], vss_get_vendor_portal_url() );
        $action_text = $is_chinese ? '回答咨询' : 'Answer Question';
        
        self::create_notification( $vendor_id, $title, $message, 'info', $action_url, $action_text, [
            'question_id' => $question_id,
            'product_id' => $product_id,
            'customer_name' => $question_data->customer_name
        ] );
    }

    /**
     * Cleanup old notifications
     */
    public static function cleanup_old_notifications() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'vss_notifications';
        
        // Delete expired notifications
        $wpdb->query(
            "DELETE FROM $table_name 
             WHERE expires_at IS NOT NULL AND expires_at < NOW()"
        );
        
        // Delete very old read notifications (older than 90 days)
        $wpdb->query(
            "DELETE FROM $table_name 
             WHERE is_read = 1 AND created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)"
        );
    }
}