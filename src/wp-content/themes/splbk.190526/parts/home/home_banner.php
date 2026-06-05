<?php

\defined('ABSPATH') || die;

$acf_fc_layout = $args['acf_fc_layout'] ?? '';
if ($acf_fc_layout !== 'home_banner') {
    return;
}

// Enqueue section-specific CSS
\HD_Helper::enqueueSectionStyle('section-banner');

$banner_full = $args['banner_full'] ?? true;
$banner_list_desktop = $args['banner_list'] ?? [];
$banner_list_mobile  = $args['banner_list_mobile'] ?? [];

// Fallback: nếu không có mobile banner thì dùng desktop
if (empty($banner_list_mobile)) {
    $banner_list_mobile = $banner_list_desktop;
}

$id = $args['id'] ?? 0;
$id = substr(md5($acf_fc_layout . '-' . $id), 0, 10);
$full_class = 'full';

?>
<style>.section-banner-home .hide-on-desktop{display:none!important}@media(max-width:47.99875rem){.section-banner-home .hide-on-mobile{display:none!important}.section-banner-home .hide-on-desktop{display:block!important}}</style>
<section id="section-<?= $id ?>" class="section-banner-home">
    <div class="container<?= $full_class ?>">
        <?php
        $_data = [
            'loop' => true,
            'autoview' => true,
            'pagination' => 'bullets',
            'autoplay' => false,
        ];

        $swiper_data = json_encode($_data, JSON_THROW_ON_ERROR | JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE);
        if ($swiper_data):

        // === DESKTOP BANNER ===
        if (!empty($banner_list_desktop)):
        ?>
            <div class="swiper-container hide-on-mobile">
                <div class="w-swiper swiper">
                    <div class="swiper-wrapper" data-options='<?= $swiper_data ?>'>
                        <?php
                        $lcp_registered = false;
                        foreach ($banner_list_desktop as $customer):
                            $re_img = $customer['re_img'] ?? 0;
                            $re_url = $customer['re_url'] ?? '';
                            $is_lcp = $re_img && !$lcp_registered;

                            if ($is_lcp) {
                                $lcp_registered = true;
                                add_action('wp_head', static function () use ($re_img) {
                                    $src = \HD_Helper::attachmentImageSrc($re_img, 'widescreen') ?: \HD_Helper::attachmentImageSrc($re_img, 'large');
                                    $srcset = wp_get_attachment_image_srcset($re_img, 'widescreen');
                                    $sizes = wp_get_attachment_image_sizes($re_img, 'widescreen');
                                    if ($src) {
                                        printf(
                                            '<link rel="preload" as="image" href="%1$s"%2$s%3$s fetchpriority="high" />',
                                            esc_url($src),
                                            $srcset ? ' imagesrcset="' . esc_attr($srcset) . '"' : '',
                                            $sizes ? ' imagesizes="' . esc_attr($sizes) . '"' : ''
                                        );
                                    }
                                }, 5);
                            }
                        ?>
                            <div class="swiper-slide">
                                <div class="item">
                                    <?php if ($re_url): ?>
                                        <a href="<?= esc_url(is_array($re_url) ? ($re_url['url'] ?? '') : $re_url) ?>"
                                            target="<?= is_array($re_url) && !empty($re_url['target']) ? esc_attr($re_url['target']) : '_self' ?>"
                                            title="<?= is_array($re_url) && !empty($re_url['title']) ? esc_attr($re_url['title']) : '' ?>">
                                            <?= \HD_Helper::pictureHTML('home-img block' . ($is_lcp ? ' home-img--lcp' : ''), $re_img, false, true, true, $is_lcp) ?>
                                        </a>
                                    <?php else: ?>
                                        <?= \HD_Helper::pictureHTML('home-img block' . ($is_lcp ? ' home-img--lcp' : ''), $re_img, false, true, true, $is_lcp) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php
        // === MOBILE BANNER ===
        if (!empty($banner_list_mobile)):
        ?>
            <div class="swiper-container hide-on-desktop">
                <div class="w-swiper swiper">
                    <div class="swiper-wrapper" data-options='<?= $swiper_data ?>'>
                        <?php foreach ($banner_list_mobile as $customer):
                            $re_img = $customer['re_img'] ?? 0;
                            $re_url = $customer['re_url'] ?? '';
                        ?>
                            <div class="swiper-slide">
                                <div class="item">
                                    <?php if ($re_url): ?>
                                        <a href="<?= esc_url(is_array($re_url) ? ($re_url['url'] ?? '') : $re_url) ?>"
                                            target="<?= is_array($re_url) && !empty($re_url['target']) ? esc_attr($re_url['target']) : '_self' ?>"
                                            title="<?= is_array($re_url) && !empty($re_url['title']) ? esc_attr($re_url['title']) : '' ?>">
                                            <?= \HD_Helper::pictureHTML('home-img block', $re_img, false, true, true, false) ?>
                                        </a>
                                    <?php else: ?>
                                        <?= \HD_Helper::pictureHTML('home-img block', $re_img, false, true, true, false) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php endif; ?>
    </div>
</section>