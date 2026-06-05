<?php

/**
 * Shortcode Handler
 * 
 * Đăng ký các shortcodes
 */

if (!defined('ABSPATH')) {
    exit;
}

class AIEbike_LW_Shortcode
{

    /**
     * Constructor
     */
    public function __construct()
    {
        add_shortcode('aiebike_lucky_wheel_page', array($this, 'render_full_page'));
        add_shortcode('aiebike_winners_list', array($this, 'render_winners_list'));
    }

    /**
     * Render full Lucky Wheel page
     */
    public function render_full_page($atts)
    {
        $atts = shortcode_atts(array(
            'title' => __('🎯 VÒNG QUAY MAY MẮN 🎯', 'aiebike-lucky-wheel'),
            'subtitle' => __('CƠ HỘI RINH XE MIỄN PHÍ', 'aiebike-lucky-wheel'),
            'prize_text' => __('GIẢM NGAY 1.500.000đ', 'aiebike-lucky-wheel'),
            'bg_color' => '#87CEEB',
        ), $atts, 'aiebike_lucky_wheel_page');

        ob_start();
        include AIEBIKE_LW_PLUGIN_DIR . 'templates/page-lucky-wheel.php';
        return ob_get_clean();
    }

    /**
     * Render winners list
     */
    public function render_winners_list($atts)
    {
        $atts = shortcode_atts(array(
            'limit' => 50,
            'show_phone' => 'yes',
            'show_date' => 'yes',
        ), $atts, 'aiebike_winners_list');

        ob_start();
?>
        <div class="aiebike-winners-list">
            <?php
            // Get winners from WooCommerce Lucky Wheel CPT
            $winners = get_posts(array(
                'post_type'      => 'wlwl_email',
                'posts_per_page' => intval($atts['limit']),
                'orderby'        => 'date',
                'order'          => 'DESC',
                'post_status'    => 'publish',
            ));

            $phone_mask = get_option('aiebike_lw_phone_mask_length', 5);
            $has_winners = false;

            if ($winners && count($winners) > 0) {
                foreach ($winners as $winner) {
                    $email = $winner->post_title;
                    // WooCommerce Lucky Wheel stores the actual name in post_content
                    $name = $winner->post_content;
                    // Fallback to meta if post_content is empty
                    if (empty($name)) {
                        $name = get_post_meta($winner->ID, 'wlwl_email_name', true);
                    }
                    $phone = get_post_meta($winner->ID, 'wlwl_email_mobile', true);
                    $labels = get_post_meta($winner->ID, 'wlwl_email_labels', true);
                    $coupon = get_post_meta($winner->ID, 'wlwl_email_coupons', true);

                    // Ensure labels and coupon are strings
                    if (is_array($labels)) {
                        $labels = implode(', ', $labels);
                    }
                    if (is_array($coupon)) {
                        $coupon = implode(', ', $coupon);
                    }

                    // Skip if no prize
                    if (empty($labels) && empty($coupon)) {
                        continue;
                    }

                    // Skip "Not Lucky"
                    if (!empty($labels) && stripos($labels, 'not lucky') !== false) {
                        continue;
                    }

                    $has_winners = true;

                    // Clean up name - remove any HTML tags
                    $name = wp_strip_all_tags($name);

                    // Fallback if name is still empty
                    if (empty($name)) {
                        $emailPrefix = explode('@', $email)[0];
                        if (preg_match('/^[0-9]+$/', $emailPrefix)) {
                            $name = __('Khách hàng', 'aiebike-lucky-wheel');
                        } else {
                            $name = $emailPrefix;
                        }
                    }

                    // Mask phone
                    if ($phone && strlen($phone) >= 10 && $atts['show_phone'] === 'yes') {
                        $phone = substr($phone, 0, 4) . str_repeat('*', $phone_mask) . substr($phone, -2);
                    } elseif ($atts['show_phone'] !== 'yes') {
                        $phone = '';
                    }

                    // Avatar initial
                    $initial = strtoupper(substr($name, 0, 1));

                    // Prize
                    $prize = !empty($labels) ? $labels : $coupon;

                    // Date
                    $date = date_i18n('d/m/Y', strtotime($winner->post_date));
            ?>
                    <div class="aiebike-winner-item">
                        <div class="aiebike-winner-avatar"><?php echo esc_html($initial); ?></div>
                        <div class="aiebike-winner-info">
                            <div class="aiebike-winner-name"><?php echo esc_html($name); ?></div>
                            <?php if ($phone): ?>
                                <div class="aiebike-winner-phone"><?php echo esc_html($phone); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="aiebike-winner-prize"><?php echo esc_html($prize); ?></div>
                        <?php if ($atts['show_date'] === 'yes'): ?>
                            <div class="aiebike-winner-date"><?php echo esc_html($date); ?></div>
                        <?php endif; ?>
                    </div>
                <?php
                }
            }

            if (!$has_winners) {
                ?>
                <div class="aiebike-no-winners">
                    <span class="dashicons dashicons-clock" style="font-size: 48px; width: 48px; height: 48px; opacity: 0.5;"></span>
                    <p><?php _e('Chưa có ai trúng thưởng. Hãy là người đầu tiên!', 'aiebike-lucky-wheel'); ?></p>
                </div>
            <?php
            }
            ?>
        </div>
<?php
        return ob_get_clean();
    }
}

// Initialize
new AIEbike_LW_Shortcode();
