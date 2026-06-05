<?php

/**
 * Template: Lucky Wheel Page
 * 
 * Template cho shortcode [aiebike_lucky_wheel_page]
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get ACF values if available
$lw_title = function_exists('get_field') ? get_field('aiebike_lw_title') : '';
$lw_subtitle = function_exists('get_field') ? get_field('aiebike_lw_subtitle') : '';
$lw_prize_text = function_exists('get_field') ? get_field('aiebike_lw_prize_text') : '';
$lw_background = function_exists('get_field') ? get_field('aiebike_lw_background') : '';
$lw_header_image = function_exists('get_field') ? get_field('aiebike_lw_header_image') : '';
$lw_prizes_title = function_exists('get_field') ? get_field('aiebike_lw_prizes_title') : '';
$lw_prizes = function_exists('get_field') ? get_field('aiebike_lw_prizes') : array();
$lw_total_prize = function_exists('get_field') ? get_field('aiebike_lw_total_prize') : '';
$lw_rules_content = function_exists('get_field') ? get_field('aiebike_lw_rules_content') : '';
$lw_contact_info = function_exists('get_field') ? get_field('aiebike_lw_contact_info') : '';

// Defaults from shortcode atts
$lw_title = $lw_title ?: (isset($atts['title']) ? $atts['title'] : __('🎯 VÒNG QUAY MAY MẮN 🎯', 'aiebike-lucky-wheel'));
$lw_subtitle = $lw_subtitle ?: (isset($atts['subtitle']) ? $atts['subtitle'] : __('', 'aiebike-lucky-wheel'));
$lw_prize_text = $lw_prize_text ?: (isset($atts['prize_text']) ? $atts['prize_text'] : __('', 'aiebike-lucky-wheel'));
$lw_prizes_title = $lw_prizes_title ?: __('🎁 CÁC GIẢI THƯỞNG HẤP DẪN', 'aiebike-lucky-wheel');
$lw_total_prize = $lw_total_prize ?: '1.500.000đ';
?>

<div class="aiebike-lucky-wheel-page" style="<?php if ($lw_background): ?>background-image: url('<?php echo esc_url($lw_background); ?>');<?php endif; ?>">
    <div class="aiebike-lw-container">

        <!-- Header -->
        <div class="aiebike-lw-header">
            <?php if (!empty($lw_header_image)): ?>
                <!-- Header Image Banner -->
                <img src="<?php echo esc_url($lw_header_image); ?>" alt="<?php echo esc_attr($lw_title); ?>" class="aiebike-lw-header-image" fetchpriority="high">
            <?php else: ?>
                <!-- Text Header (fallback) -->
                <h1><?php echo esc_html($lw_title); ?></h1>
                <?php if (!empty($lw_subtitle)): ?>
                    <p class="aiebike-lw-subtitle"><?php echo esc_html($lw_subtitle); ?></p>
                <?php endif; ?>
                <?php if (!empty($lw_prize_text)): ?>
                    <p class="aiebike-lw-prize-amount"><?php echo esc_html($lw_prize_text); ?></p>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Main Wheel Section -->
        <div class="aiebike-lw-main">
            <?php echo do_shortcode('[woocommerce_lucky_wheel]'); ?>
        </div>

        <!-- Tabs -->
        <div class="aiebike-lw-tabs">
            <ul class="aiebike-lw-tabs-nav">
                <li class="active" data-tab="winners">
                    <a href="#winners">🏆 <?php _e('Danh Sách Trúng Thưởng', 'aiebike-lucky-wheel'); ?></a>
                </li>
                <li data-tab="prizes">
                    <a href="#prizes">🎁 <?php _e('Giải Thưởng', 'aiebike-lucky-wheel'); ?></a>
                </li>
                <li data-tab="rules">
                    <a href="#rules">📋 <?php _e('Thể Lệ', 'aiebike-lucky-wheel'); ?></a>
                </li>
            </ul>

            <div class="aiebike-lw-tabs-content">
                <!-- Winners Panel -->
                <div id="winners" class="aiebike-lw-tab-panel active">
                    <?php echo do_shortcode('[aiebike_winners_list limit="50"]'); ?>
                </div>

                <!-- Prizes Panel -->
                <div id="prizes" class="aiebike-lw-tab-panel">
                    <h3 style="color: #FF6B00; margin-top: 0;"><?php echo esc_html($lw_prizes_title); ?></h3>
                    <div class="aiebike-prize-showcase">
                        <?php
                        if ($lw_prizes && is_array($lw_prizes) && count($lw_prizes) > 0) {
                            foreach ($lw_prizes as $prize) {
                                $prize_image = isset($prize['image']) ? $prize['image'] : null;
                                $prize_value = isset($prize['value']) ? $prize['value'] : '';
                                $prize_label = isset($prize['label']) ? $prize['label'] : '';
                        ?>
                                <div class="aiebike-prize-item">
                                    <?php if ($prize_image && isset($prize_image['url'])): ?>
                                        <img src="<?php echo esc_url($prize_image['sizes']['thumbnail'] ?? $prize_image['url']); ?>" alt="<?php echo esc_attr($prize_value); ?>" loading="lazy">
                                    <?php endif; ?>
                                    <div class="aiebike-prize-value"><?php echo esc_html($prize_value); ?></div>
                                    <div class="aiebike-prize-label"><?php echo esc_html($prize_label); ?></div>
                                </div>
                            <?php
                            }
                        } else {
                            // Default prizes
                            ?>
                            <div class="aiebike-prize-item">
                                <div class="aiebike-prize-value">100K</div>
                                <div class="aiebike-prize-label"><?php _e('Giảm giá', 'aiebike-lucky-wheel'); ?></div>
                            </div>
                            <div class="aiebike-prize-item">
                                <div class="aiebike-prize-value">200K</div>
                                <div class="aiebike-prize-label"><?php _e('Giảm giá', 'aiebike-lucky-wheel'); ?></div>
                            </div>
                            <div class="aiebike-prize-item">
                                <div class="aiebike-prize-value">300K</div>
                                <div class="aiebike-prize-label"><?php _e('Giảm giá', 'aiebike-lucky-wheel'); ?></div>
                            </div>
                            <div class="aiebike-prize-item">
                                <div class="aiebike-prize-value">🎉</div>
                                <div class="aiebike-prize-label"><?php _e('Và nhiều phần quà khác', 'aiebike-lucky-wheel'); ?></div>
                            </div>
                        <?php } ?>
                    </div>
                    <p style="text-align: center; margin-top: 30px; color: #666;">
                        <?php _e('Tổng giá trị giải thưởng lên đến', 'aiebike-lucky-wheel'); ?>
                        <strong style="color: #FF6B00; font-size: 24px;"><?php echo esc_html($lw_total_prize); ?></strong>
                    </p>
                </div>

                <!-- Rules Panel -->
                <div id="rules" class="aiebike-lw-tab-panel">
                    <div class="aiebike-rules-content">
                        <?php
                        if ($lw_rules_content) {
                            echo wp_kses_post($lw_rules_content);
                        } else {
                        ?>
                            <h3>📌 <?php _e('Đối Tượng Tham Gia', 'aiebike-lucky-wheel'); ?></h3>
                            <ul>
                                <li><?php _e('Tất cả khách hàng đã <strong>đặt hàng thành công</strong>', 'aiebike-lucky-wheel'); ?></li>
                                <li><?php _e('Mỗi xe được <strong>1 lượt quay</strong>', 'aiebike-lucky-wheel'); ?></li>
                            </ul>

                            <h3>🎮 <?php _e('Cách Thức Tham Gia', 'aiebike-lucky-wheel'); ?></h3>
                            <ul>
                                <li><strong><?php _e('Bước 1:', 'aiebike-lucky-wheel'); ?></strong> <?php _e('Hoàn tất mua hàng', 'aiebike-lucky-wheel'); ?></li>
                                <li><strong><?php _e('Bước 2:', 'aiebike-lucky-wheel'); ?></strong> <?php _e('Nhập số động cơ xe', 'aiebike-lucky-wheel'); ?></li>
                                <li><strong><?php _e('Bước 3:', 'aiebike-lucky-wheel'); ?></strong> <?php _e('Nhấn nút để quay', 'aiebike-lucky-wheel'); ?></li>
                                <li><strong><?php _e('Bước 4:', 'aiebike-lucky-wheel'); ?></strong> <?php _e('Nhân viên sẽ liên hệ gửi hàng', 'aiebike-lucky-wheel'); ?></li>
                            </ul>

                            <h3>⚠️ <?php _e('Điều Kiện Sử Dụng Voucher', 'aiebike-lucky-wheel'); ?></h3>
                            <ul>
                                <li><?php _e('Voucher có thời hạn <strong>30 ngày</strong> kể từ ngày nhận', 'aiebike-lucky-wheel'); ?></li>
                                <li><?php _e('Mỗi voucher chỉ được sử dụng <strong>1 lần duy nhất</strong>', 'aiebike-lucky-wheel'); ?></li>
                                <li><?php _e('Không áp dụng cộng gộp với các chương trình khuyến mãi khác', 'aiebike-lucky-wheel'); ?></li>
                            </ul>
                        <?php } ?>

                        <div class="aiebike-rules-note">
                            <?php if ($lw_contact_info): ?>
                                <?php echo nl2br(esc_html($lw_contact_info)); ?>
                            <?php else: ?>
                                <p><strong>📞 <?php _e('Liên hệ hỗ trợ:', 'aiebike-lucky-wheel'); ?></strong> Hotline 1900.xxxx</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabNavItems = document.querySelectorAll('.aiebike-lw-tabs-nav li');
        const tabPanels = document.querySelectorAll('.aiebike-lw-tab-panel');

        tabNavItems.forEach(function(navItem) {
            navItem.addEventListener('click', function(e) {
                e.preventDefault();
                tabNavItems.forEach(item => item.classList.remove('active'));
                tabPanels.forEach(panel => panel.classList.remove('active'));
                this.classList.add('active');
                const tabId = this.getAttribute('data-tab');
                document.getElementById(tabId).classList.add('active');
            });
        });
    });
</script>