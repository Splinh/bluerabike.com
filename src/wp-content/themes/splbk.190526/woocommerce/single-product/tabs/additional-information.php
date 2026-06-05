<?php
/**
 * Additional Information tab – Bluera (babyshop)
 *
 * Logic:
 *  - Nếu có ảnh thông tin bổ sung (_additional_info_image_id) → hiển thị ảnh
 *  - Nếu KHÔNG có ảnh → hiển thị layout động:
 *      • Tiêu đề "THÔNG SỐ KỸ THUẬT"
 *      • Bảng ACF repeater (tskt_rows)
 *      • Dải icon bảo hành (nếu có ACF gallery tskt_badges)
 *      • Footer: logo + tên công ty + SĐT + website
 */

defined('ABSPATH') || exit;

global $product;

// ── 1. Ảnh tĩnh đã upload (cũ) ──────────────────────────────────────────────
$additional_info_image_id = get_post_meta($product->get_id(), '_additional_info_image_id', true);

if ($additional_info_image_id) :
    $image_url = wp_get_attachment_image_url($additional_info_image_id, 'full');
    if ($image_url) : ?>
        <div class="additional-info-image">
            <img src="<?php echo esc_url($image_url); ?>"
                 alt="<?php echo esc_attr($product->get_name() . ' - Thông số kỹ thuật'); ?>"
                 style="max-width:100%;height:auto;" />
        </div>
    <?php endif;

// ── 2. Fallback UI động ─────────────────────────────────────────────────────
else :

    // ACF repeater rows: [{tskt_label, tskt_value}]
    $rows = [];
    if (function_exists('get_field')) {
        $rows = get_field('tskt_rows', $product->get_id()) ?: [];
    }
    ?>

    <div class="tskt-wrapper" id="tskt-spec-block">

        <?php /* ── Tiêu đề ── */ ?>
        <div class="tskt-header">
            <span class="tskt-header__dot"></span>
            <h2 class="tskt-header__title">THÔNG SỐ KỸ THUẬT</h2>
            <span class="tskt-header__dot"></span>
        </div>

        <?php /* ── Bảng thông số ── */ ?>
        <div class="tskt-body">
            <div class="tskt-body__table-wrap tskt-body__table-wrap--full">
                <?php if (!empty($rows)) : ?>
                    <table class="tskt-table">
                        <tbody>
                            <?php foreach ($rows as $row) :
                                $label = isset($row['tskt_label']) ? $row['tskt_label'] : '';
                                $value = isset($row['tskt_value']) ? $row['tskt_value'] : '';
                                if (!$label && !$value) continue;
                            ?>
                            <tr>
                                <th><?php echo esc_html($label); ?></th>
                                <td><?php echo wp_kses_post($value); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <p class="tskt-empty">Chưa có thông số kỹ thuật.</p>
                <?php endif; ?>
            </div>
        </div><!-- /.tskt-body -->

        <?php
        // ── Icon bảo hành (tùy chọn – ACF gallery 'tskt_badges') ──
        $badges = [];
        if (function_exists('get_field')) {
            $badges = get_field('tskt_badges', $product->get_id()) ?: [];
        }
        if (!empty($badges)) : ?>
        <div class="tskt-badges">
            <?php foreach ($badges as $badge) :
                $burl = is_array($badge) ? ($badge['url'] ?? '') : $badge;
                if (!$burl) continue;
            ?>
                <div class="tskt-badges__item">
                    <img src="<?php echo esc_url($burl); ?>" alt="badge" loading="lazy" />
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php /* ── Footer công ty ── */ ?>
        <?php
        $custom_logo_id  = get_theme_mod('custom_logo');
        $custom_logo_url = $custom_logo_id ? wp_get_attachment_image_url($custom_logo_id, 'medium') : '';
        if (!$custom_logo_url) {
            $site_icon_id = get_option('site_icon');
            $custom_logo_url = $site_icon_id ? wp_get_attachment_image_url($site_icon_id, 'medium') : '';
        }
        $company_name    = get_theme_mod('tskt_company_name', 'Công Ty TNHH Xe Điện Bluera Việt Nhật');
        $address         = get_theme_mod('tskt_company_address', '466 Nguyễn Duy Trinh, P. Bình Trưng, TP. Hồ Chí Minh');
        $phone           = get_theme_mod('tskt_company_phone', '0933.505.222');
        $website         = get_theme_mod('tskt_company_website', 'https://bluerabike.com');
        $website_display = preg_replace('#^https?://#', '', rtrim($website, '/'));
        ?>
        <div class="tskt-footer">
            <div class="tskt-footer__divider"></div>
            <div class="tskt-footer__inner">
                <?php if ($custom_logo_url) : ?>
                <div class="tskt-footer__logo">
                    <img src="<?php echo esc_url($custom_logo_url); ?>"
                         alt="<?php echo esc_attr($company_name); ?>" />
                </div>
                <?php endif; ?>
                <div class="tskt-footer__company">
                    <p class="tskt-footer__company-name"><?php echo esc_html(mb_strtoupper($company_name)); ?></p>
                    <p class="tskt-footer__company-address"><em>Địa Chỉ: <?php echo esc_html($address); ?></em></p>
                </div>
                <div class="tskt-footer__contact">
                    <p class="tskt-footer__phone">ĐT: <strong><?php echo esc_html($phone); ?></strong></p>
                    <p class="tskt-footer__website"><?php echo esc_html($website_display); ?></p>
                </div>
            </div>
        </div>

    </div><!-- /.tskt-wrapper -->

<?php endif; ?>
