<?php

\defined('ABSPATH') || die;

$acf_fc_layout = $args['acf_fc_layout'] ?? '';
if ($acf_fc_layout !== 'home_doitac') {
    return;
}

// Enqueue section-specific CSS
\HD_Helper::enqueueSectionStyle('section-doitac');

$home_title = $args['home_title'] ?? '';
$list_dt    = $args['list_dt'] ?? [];

// Ensure list_dt is an array
if (!is_array($list_dt) || empty($list_dt)) {
    return;
}

?>
<section class="section section-doitac sc-pd">
    <div class="container">
        <h2 class="heading-title"><?= $home_title ?></h2>
        <?php
        $_data = [
            'loop'       => true,
            'smallgap'   => 15,
            'slidesPerGroup' => 1,
            'desktop' => [
                'slidesPerView' => 5,
            ],
            'tablet' => [
                'slidesPerView' => 3,
            ],
            'mobile' => [
                'slidesPerView' => 2,
            ]
        ];

        $swiper_data = json_encode($_data, JSON_THROW_ON_ERROR | JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE);
        ?>
        <div class="swiper-container">
            <div class="w-swiper swiper">
                <div class="swiper-wrapper" data-options='<?= $swiper_data ?>'>
                    <?php foreach ($list_dt as $dt) :
                        $img = $dt['img'] ?? 0;
                        $link = $dt['link'] ?? [];
                        $link_url = is_array($link) ? ($link['url'] ?? '') : '';
                        $link_title = is_array($link) ? ($link['title'] ?? '') : '';

                        // Skip if no image
                        if (!$img) continue;

                        // Get alt text from image if no title
                        $img_alt = get_post_meta($img, '_wp_attachment_image_alt', true);
                        $aria_label = !empty($link_title) ? $link_title : (!empty($img_alt) ? $img_alt : __('Đối tác', TEXT_DOMAIN));
                    ?>
                        <div class="swiper-slide">
                            <?php if (!empty($link_url)) : ?>
                                <a href="<?= esc_url($link_url); ?>" title="<?= esc_attr($link_title); ?>" aria-label="<?= esc_attr($aria_label); ?>" class="item">
                                    <?php echo \HD_Helper::pictureHTML('doitac-img block', $img) ?>
                                </a>
                            <?php else : ?>
                                <span class="item" aria-label="<?= esc_attr($aria_label); ?>">
                                    <?php echo \HD_Helper::pictureHTML('doitac-img block', $img) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>