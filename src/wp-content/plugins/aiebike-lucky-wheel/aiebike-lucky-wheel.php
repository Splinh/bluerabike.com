<?php

/**
 * Plugin Name: AIEbike Vòng Quay May Mắn
 * Plugin URI: https://aiebike.vn
 * Description: Plugin mở rộng cho WooCommerce Lucky Wheel - Thêm xác thực số khung xe, trang landing page, và tùy chỉnh giao diện vòng quay.
 * Version: 1.0.0
 * Author: AI Ebike Team
 * Author URI: https://aiebike.vn
 * Text Domain: aiebike-lucky-wheel
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * WC requires at least: 3.0
 * WC tested up to: 8.0
 * 
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('AIEBIKE_LW_VERSION', '1.0.0');
define('AIEBIKE_LW_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AIEBIKE_LW_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AIEBIKE_LW_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Plugin Class
 */
class AIEbike_Lucky_Wheel
{

    /**
     * Instance
     */
    private static $instance = null;

    /**
     * Get instance
     */
    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct()
    {
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks()
    {
        // Activation/Deactivation
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        // Init
        add_action('plugins_loaded', array($this, 'init'));

        // Load textdomain
        add_action('init', array($this, 'load_textdomain'));
    }

    /**
     * Plugin activation
     */
    public function activate()
    {
        // Create database table
        $this->create_tables();

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation
     */
    public function deactivate()
    {
        flush_rewrite_rules();
    }

    /**
     * Create database tables
     */
    private function create_tables()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'aiebike_frame_numbers';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            frame_number varchar(100) NOT NULL,
            customer_name varchar(255) DEFAULT '',
            customer_phone varchar(50) DEFAULT '',
            customer_email varchar(255) DEFAULT '',
            prize_won varchar(255) DEFAULT '',
            used_date datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY frame_number (frame_number)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Load textdomain
     */
    public function load_textdomain()
    {
        load_plugin_textdomain('aiebike-lucky-wheel', false, dirname(AIEBIKE_LW_PLUGIN_BASENAME) . '/languages');
    }

    /**
     * Initialize plugin
     */
    public function init()
    {
        // Check if WooCommerce Lucky Wheel is active
        if (!$this->check_dependencies()) {
            add_action('admin_notices', array($this, 'dependency_notice'));
            return;
        }

        // Load includes
        $this->includes();
    }

    /**
     * Check dependencies
     */
    private function check_dependencies()
    {
        // Check for WooCommerce Lucky Wheel plugin
        return class_exists('VI_WOOCOMMERCE_LUCKY_WHEEL') ||
            defined('VI_WOOCOMMERCE_LUCKY_WHEEL_VERSION') ||
            is_plugin_active('woocommerce-lucky-wheel/woocommerce-lucky-wheel.php');
    }

    /**
     * Dependency notice
     */
    public function dependency_notice()
    {
?>
        <div class="notice notice-error">
            <p><strong>AIEbike Vòng Quay May Mắn:</strong> Plugin này yêu cầu <strong>WooCommerce Lucky Wheel</strong> được cài đặt và kích hoạt.</p>
        </div>
<?php
    }

    /**
     * Include required files
     */
    private function includes()
    {
        // Frame validation
        require_once AIEBIKE_LW_PLUGIN_DIR . 'includes/class-frame-validation.php';

        // Admin settings
        require_once AIEBIKE_LW_PLUGIN_DIR . 'includes/class-admin.php';

        // Report (integrated with WooCommerce Lucky Wheel)
        require_once AIEBIKE_LW_PLUGIN_DIR . 'includes/class-report.php';

        // Frontend
        require_once AIEBIKE_LW_PLUGIN_DIR . 'includes/class-frontend.php';

        // Shortcode
        require_once AIEBIKE_LW_PLUGIN_DIR . 'includes/class-shortcode.php';

        // ACF Fields (if ACF is active)
        if (function_exists('acf_add_local_field_group')) {
            require_once AIEBIKE_LW_PLUGIN_DIR . 'includes/class-acf-fields.php';
        }
    }
}

/**
 * Initialize plugin
 */
function aiebike_lucky_wheel_init()
{
    return AIEbike_Lucky_Wheel::get_instance();
}

// Start the plugin
add_action('plugins_loaded', 'aiebike_lucky_wheel_init', 5);
