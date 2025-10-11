<?php
/**
 * Slack Integration for Vendor Order Manager
 *
 * @package VendorOrderManager
 */

if (!defined('ABSPATH')) {
    exit;
}

class VSS_Slack_Integration {
    
    private static $instance = null;
    
    private $webhook_url;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public static function init() {
        $instance = self::get_instance();
        $instance->setup_hooks();
    }
    
    private function __construct() {
        $this->webhook_url = 'https://hooks.slack.com/services/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';
    }
    
    private function setup_hooks() {
        add_action('woocommerce_thankyou', array($this, 'send_new_order_notification'), 10, 1);
        add_action('woocommerce_order_status_changed', array($this, 'send_status_change_notification'), 10, 3);
        
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    public function send_new_order_notification($order_id) {
        if (!$order_id || !get_option('vss_slack_enabled', 1) || !get_option('vss_slack_notify_new_orders', 1)) {
            return;
        }
        
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }
        
        if ($order->is_paid() || $order->get_status() === 'processing') {
            $this->send_slack_message($this->format_new_order_message($order));
        }
    }
    
    public function send_status_change_notification($order_id, $from_status, $to_status) {
        if (!$order_id || !get_option('vss_slack_enabled', 1) || !get_option('vss_slack_notify_status_changes', 1)) {
            return;
        }
        
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }
        
        $important_statuses = array('processing', 'shipped', 'completed', 'cancelled', 'refunded');
        
        if (in_array($to_status, $important_statuses) && $from_status !== $to_status) {
            $this->send_slack_message($this->format_status_change_message($order, $from_status, $to_status));
        }
    }
    
    private function format_new_order_message($order) {
        $customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
        $order_total = $this->get_clean_order_total($order);
        $order_items = array();
        
        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            $order_items[] = sprintf('%s × %d', $product ? $product->get_name() : $item->get_name(), $item->get_quantity());
        }
        
        $items_text = implode(', ', array_slice($order_items, 0, 3));
        if (count($order_items) > 3) {
            $items_text .= ' and ' . (count($order_items) - 3) . ' more items';
        }
        
        $message = array(
            'text' => '🛍️ New Order Received!',
            'attachments' => array(
                array(
                    'color' => 'good',
                    'fields' => array(
                        array(
                            'title' => 'Order Details',
                            'value' => sprintf(
                                "*Order #%s*\n*Customer:* %s\n*Total:* %s\n*Items:* %s\n*Status:* %s",
                                $order->get_order_number(),
                                $customer_name,
                                $order_total,
                                $items_text,
                                ucfirst($order->get_status())
                            ),
                            'short' => false
                        )
                    ),
                    'footer' => 'Vendor Order Manager',
                    'ts' => time()
                )
            )
        );
        
        return $message;
    }
    
    private function format_status_change_message($order, $from_status, $to_status) {
        $customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
        $order_total = $this->get_clean_order_total($order);
        
        $status_emoji = array(
            'processing' => '⚡',
            'shipped' => '🚚',
            'completed' => '✅',
            'cancelled' => '❌',
            'refunded' => '💰'
        );
        
        $emoji = isset($status_emoji[$to_status]) ? $status_emoji[$to_status] : '📋';
        
        $message = array(
            'text' => sprintf('%s Order Status Changed', $emoji),
            'attachments' => array(
                array(
                    'color' => $this->get_status_color($to_status),
                    'fields' => array(
                        array(
                            'title' => 'Order Update',
                            'value' => sprintf(
                                "*Order #%s*\n*Customer:* %s\n*Total:* %s\n*Status:* %s → %s",
                                $order->get_order_number(),
                                $customer_name,
                                $order_total,
                                ucfirst($from_status),
                                ucfirst($to_status)
                            ),
                            'short' => false
                        )
                    ),
                    'footer' => 'Vendor Order Manager',
                    'ts' => time()
                )
            )
        );
        
        return $message;
    }
    
    private function get_status_color($status) {
        $colors = array(
            'processing' => '#36a64f',
            'shipped' => '#439fe0',
            'completed' => '#36a64f',
            'cancelled' => '#ff0000',
            'refunded' => '#ff9500'
        );
        
        return isset($colors[$status]) ? $colors[$status] : '#cccccc';
    }
    
    private function send_slack_message($message) {
        if (empty($this->webhook_url)) {
            return false;
        }
        
        $payload = json_encode($message);
        
        $args = array(
            'body' => $payload,
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'timeout' => 15,
            'method' => 'POST'
        );
        
        $response = wp_remote_post($this->webhook_url, $args);
        
        if (is_wp_error($response)) {
            error_log('VSS Slack Integration Error: ' . $response->get_error_message());
            return false;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        
        if ($response_code !== 200) {
            error_log('VSS Slack Integration Error: HTTP ' . $response_code);
            return false;
        }
        
        return true;
    }
    
    public function add_admin_menu() {
        add_submenu_page(
            'vss-admin-dashboard',
            __('Slack Integration', 'vss'),
            __('Slack', 'vss'),
            'manage_options',
            'vss-slack-settings',
            array($this, 'admin_page')
        );
    }
    
    public function register_settings() {
        register_setting('vss_slack_settings', 'vss_slack_webhook_url');
        register_setting('vss_slack_settings', 'vss_slack_enabled');
        register_setting('vss_slack_settings', 'vss_slack_notify_new_orders');
        register_setting('vss_slack_settings', 'vss_slack_notify_status_changes');
    }
    
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Slack Integration Settings', 'vss'); ?></h1>
            
            <form method="post" action="options.php">
                <?php settings_fields('vss_slack_settings'); ?>
                <?php do_settings_sections('vss_slack_settings'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Enable Slack Integration', 'vss'); ?></th>
                        <td>
                            <input type="checkbox" name="vss_slack_enabled" value="1" <?php checked(get_option('vss_slack_enabled'), 1); ?> />
                            <label><?php _e('Enable Slack notifications', 'vss'); ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Slack Webhook URL', 'vss'); ?></th>
                        <td>
                            <input type="url" name="vss_slack_webhook_url" value="<?php echo esc_attr($this->webhook_url); ?>" class="regular-text" readonly />
                            <p class="description"><?php _e('Your Slack webhook URL is pre-configured.', 'vss'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Notify on New Orders', 'vss'); ?></th>
                        <td>
                            <input type="checkbox" name="vss_slack_notify_new_orders" value="1" <?php checked(get_option('vss_slack_notify_new_orders', 1), 1); ?> />
                            <label><?php _e('Send notification when new orders are placed', 'vss'); ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Notify on Status Changes', 'vss'); ?></th>
                        <td>
                            <input type="checkbox" name="vss_slack_notify_status_changes" value="1" <?php checked(get_option('vss_slack_notify_status_changes', 1), 1); ?> />
                            <label><?php _e('Send notification when order status changes', 'vss'); ?></label>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
            
            <h3><?php _e('Test Integration', 'vss'); ?></h3>
            <p><?php _e('Click the button below to send a test message to Slack.', 'vss'); ?></p>
            <button type="button" id="test-slack-integration" class="button button-secondary"><?php _e('Send Test Message', 'vss'); ?></button>
            
            <script>
            jQuery(document).ready(function($) {
                $('#test-slack-integration').on('click', function() {
                    var button = $(this);
                    button.prop('disabled', true).text('<?php _e('Sending...', 'vss'); ?>');
                    
                    $.post(ajaxurl, {
                        action: 'vss_test_slack',
                        nonce: '<?php echo wp_create_nonce('vss_slack_test'); ?>'
                    }, function(response) {
                        if (response.success) {
                            alert('<?php _e('Test message sent successfully!', 'vss'); ?>');
                        } else {
                            alert('<?php _e('Failed to send test message. Check your webhook URL.', 'vss'); ?>');
                        }
                        button.prop('disabled', false).text('<?php _e('Send Test Message', 'vss'); ?>');
                    });
                });
            });
            </script>
        </div>
        <?php
    }
    
    public function handle_test_slack() {
        check_ajax_referer('vss_slack_test', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die();
        }
        
        $test_message = array(
            'text' => '🧪 Test Message from Vendor Order Manager',
            'attachments' => array(
                array(
                    'color' => 'good',
                    'fields' => array(
                        array(
                            'title' => 'Integration Test',
                            'value' => 'This is a test message to verify your Slack integration is working correctly.',
                            'short' => false
                        )
                    ),
                    'footer' => 'Vendor Order Manager - Slack Integration',
                    'ts' => time()
                )
            )
        );
        
        $result = $this->send_slack_message($test_message);
        
        wp_send_json_success($result);
    }

    /**
     * Clean order total for plain text display (removes HTML)
     * 
     * @param WC_Order $order Order object
     * @return string Clean order total
     */
    private function get_clean_order_total($order) {
        return html_entity_decode( strip_tags( $order->get_formatted_order_total() ) );
    }
}

add_action('wp_ajax_vss_test_slack', array(VSS_Slack_Integration::get_instance(), 'handle_test_slack'));