<?php

defined('ABSPATH') || exit;

$acf_fc_layout = $args['acf_fc_layout'] ?? '';
if ($acf_fc_layout !== 'home_products_new') {
    return;
}

// Enqueue section-specific CSS
\HD_Helper::enqueueSectionStyle('section-products-new');


$home_title  = $args['home_title'] ?? '';
$list_dt     = $args['list_dt'] ?? [];
$bg_section  = $args['bg_section'] ?? '';

?>

<section class="section section-products-new">
    <div class="container">
        <?php if (!empty($home_title)) : ?>
            <h2 class="heading-title"><?= esc_html($home_title); ?></h2>
        <?php endif; ?>

        <?php
        $_data = [
            'loop'            => true,
            'slidesPerGroup'  => 1,
            'autoplay'   => true,
            'speed' => 500,
            'desktop'         => ['slidesPerView' => 1],
        ];

        if (!empty($navigation)) {
            $_data['navigation'] = true;
        }
        if (!empty($pagination)) {
            $_data['pagination'] = 'bullets';
        }

        $swiper_data = wp_json_encode($_data);
        ?>

        <div class="swiper-container">
            <div class="w-swiper swiper">
                <div class="swiper-wrapper" data-options='<?= esc_attr($swiper_data); ?>'>

                    <?php foreach ($list_dt as $dt) :
                        $img_id     = $dt['img'] ?? 0;
                        $link       = $dt['link'] ?? [];
                        $link_url   = is_array($link) ? ($link['url'] ?? '') : $link;
                        $link_title = is_array($link) ? ($link['title'] ?? '') : '';

                        if (!$img_id) continue;

                        // Lấy thông tin ảnh
                        $img_src    = wp_get_attachment_image_src($img_id, 'full');
                        $img_width  = $img_src[1] ?? 700;
                        $img_height = $img_src[2] ?? 500;
                        $img_url    = $img_src[0] ?? '';
                        $img_alt    = get_post_meta($img_id, '_wp_attachment_image_alt', true);

                        // Fallback aria-label
                        $aria_label = !empty($link_title) ? $link_title : (!empty($img_alt) ? $img_alt : __('Sản phẩm mới', TEXT_DOMAIN));
                    ?>
                        <div class="swiper-slide">
                            <?php if (!empty($link_url)) : ?>
                                <a href="<?= esc_url($link_url); ?>" title="<?= esc_attr($link_title); ?>" aria-label="<?= esc_attr($aria_label); ?>" class="item" style="aspect-ratio: <?= esc_attr($img_width); ?>/<?= esc_attr($img_height); ?>;">
                                    <img
                                        src="<?= esc_url($img_url); ?>"
                                        width="<?= esc_attr($img_width); ?>"
                                        height="<?= esc_attr($img_height); ?>"
                                        alt="<?= esc_attr($img_alt); ?>"
                                        loading="lazy"
                                        decoding="async"
                                        class="attachment-large size-large" />
                                </a>
                            <?php else : ?>
                                <span class="item" style="aspect-ratio: <?= esc_attr($img_width); ?>/<?= esc_attr($img_height); ?>;">
                                    <img
                                        src="<?= esc_url($img_url); ?>"
                                        width="<?= esc_attr($img_width); ?>"
                                        height="<?= esc_attr($img_height); ?>"
                                        alt="<?= esc_attr($img_alt); ?>"
                                        loading="lazy"
                                        decoding="async"
                                        class="attachment-large size-large" />
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                </div>
            </div>
        </div>
    </div>
</section>