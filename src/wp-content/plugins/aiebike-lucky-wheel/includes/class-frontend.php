<?php

/**
 * Frontend Handler
 * 
 * Xử lý frontend: enqueue scripts, styles
 */

if (!defined('ABSPATH')) {
    exit;
}

class AIEbike_LW_Frontend
{

    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'), 100);
        add_action('wp_head', array($this, 'output_preload'), 5); // Early preload for wheel image
        add_filter('body_class', array($this, 'add_body_class')); // Add class to body
    }

    /**
     * Add body class for Lucky Wheel page
     */
    public function add_body_class($classes)
    {
        if ($this->has_lucky_wheel()) {
            $classes[] = 'lucky-wheel-page';
        }
        return $classes;
    }

    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts()
    {
        // Only on pages with Lucky Wheel
        if (!$this->has_lucky_wheel()) {
            return;
        }

        // Main CSS
        wp_enqueue_style(
            'aiebike-lucky-wheel',
            AIEBIKE_LW_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            AIEBIKE_LW_VERSION
        );

        // Main JS - defer for non-blocking load
        wp_enqueue_script(
            'aiebike-lucky-wheel',
            AIEBIKE_LW_PLUGIN_URL . 'assets/js/frontend.js',
            array('jquery'),
            AIEBIKE_LW_VERSION,
            array('strategy' => 'defer', 'in_footer' => true)
        );

        // Wheel customization JS - defer
        wp_enqueue_script(
            'aiebike-wheel-custom',
            AIEBIKE_LW_PLUGIN_URL . 'assets/js/wheel-custom.js',
            array('jquery'),
            AIEBIKE_LW_VERSION,
            array('strategy' => 'defer', 'in_footer' => true)
        );

        // Get custom wheel image from ACF if available
        $custom_wheel = '';
        if (function_exists('get_field')) {
            global $post;
            if ($post) {
                $custom_wheel = get_field('aiebike_lw_custom_wheel', $post->ID);
            }
        }

        // Localize script
        wp_localize_script('aiebike-lucky-wheel', 'aiebikeWheelConfig', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('aiebike_lucky_wheel_nonce'),
            'enableFrameValidation' => get_option('aiebike_lw_enable_frame_validation', 'yes'),
            'frameFieldLabel' => get_option('aiebike_lw_frame_field_label', __('Số khung hoặc số động cơ xe', 'aiebike-lucky-wheel')),
            'autoEmailDomain' => get_option('aiebike_lw_auto_email_domain', 'aiebike.store'),
            'customWheelImage' => $custom_wheel,
            'i18n' => array(
                'frameRequired' => __('Vui lòng nhập số khung hoặc số động cơ xe!', 'aiebike-lucky-wheel'),
                'checking' => __('Đang kiểm tra...', 'aiebike-lucky-wheel'),
                'valid' => __('Số khung hợp lệ!', 'aiebike-lucky-wheel'),
                'note' => __('Mỗi xe chỉ được quay 1 lần. Số động cơ có trên giấy tờ xe.', 'aiebike-lucky-wheel'),
            )
        ));
    }

    /**
     * Check if page has Lucky Wheel
     */
    private function has_lucky_wheel()
    {
        global $post;

        if (!$post) {
            return false;
        }

        // Check for shortcodes
        $shortcodes = array('[woocommerce_lucky_wheel]', '[aiebike_lucky_wheel_page]', '[aiebike_winners_list');

        foreach ($shortcodes as $shortcode) {
            if (has_shortcode($post->post_content, str_replace(array('[', ']'), '', $shortcode))) {
                return true;
            }
        }

        // Check page template
        $template = get_page_template_slug($post->ID);
        if (strpos($template, 'vong-quay') !== false || strpos($template, 'lucky-wheel') !== false) {
            return true;
        }

        return false;
    }

    /**
     * Output preload hints for faster resource loading
     */
    public function output_preload()
    {
        if (!$this->has_lucky_wheel()) {
            return;
        }

        // Preload custom wheel image for faster display
        $custom_wheel = '';
        if (function_exists('get_field')) {
            global $post;
            if ($post) {
                $custom_wheel = get_field('aiebike_lw_custom_wheel', $post->ID);
            }
        }

        if ($custom_wheel) {
            echo '<link rel="preload" as="image" href="' . esc_url($custom_wheel) . '" fetchpriority="high">';
        }
    }
}

// Initialize
new AIEbike_LW_Frontend();
