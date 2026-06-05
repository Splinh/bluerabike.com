<?php

\defined('ABSPATH') || die;

$acf_fc_layout = $args['acf_fc_layout'] ?? '';
if ($acf_fc_layout !== 'home_product_cat') {
    return;
}

if (! \HD_Helper::isWoocommerceActive()) {
    return;
}

// Enqueue section-specific CSS
\HD_Helper::enqueueSectionStyle('section-product-cat');




$home_title       = $args['home_title'] ?? '';
$home_desc        = $args['home_desc'] ?? '';
$home_product_cat = $args['home_product_cat'] ?? [];

if (! $home_product_cat) {
    return;
}

$id         = $args['id'] ?? 0;
$id         = substr(md5($acf_fc_layout . '-' . $id), 0, 10);


?>
<section id="section-<?= $id ?>" class="section section-product-cat section-slides section-slide-shadow">
    <div class="container">

        <?php if ($home_title || $home_desc) : ?>
            <div class="group-title">
                <?= $home_title ? '<h2 class="section-title home-title">' . $home_title . '</h2>' : '' ?>
                <?= $home_desc ? '<div class="home-desc">' . $home_desc . '</div>' : '' ?>
            </div>
        <?php endif; ?>

        <?php
        $swiper_css = 'swiper';
        $_data = [
            'loop'       => true,
            'autoview'   => true,
            'smallgap'   => 12,
            'navigation' => false,
            'pagination' => 'bullets'
        ];



        $swiper_data = json_encode($_data, JSON_THROW_ON_ERROR | JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE);
        if ($swiper_data) :

        ?>
            <div class="swiper-container">
                <div class="w-swiper <?= $swiper_css ?>">
                    <div class="swiper-wrapper" data-options='<?= $swiper_data ?>'>
                        <?php
                        $i = 0;
                        foreach ($home_product_cat as $product_cat) :
                            $re_product_cat     = $product_cat['re_product_cat'] ?? 0;
                            $re_product_cat_img = $product_cat['re_product_cat_img'] ?? 0;
                            $re_custom_link     = $product_cat['re_custom_link'] ?? [];

                            $title = '';
                            $icon  = '';
                            $link  = '';

                            if ($re_product_cat) {
                                $term  = \HD_Helper::getTerm($re_product_cat, 'product_cat');
                                $title = $term->name ?? '';
                                $icon  = \HD_Helper::attachmentImageHTML(get_term_meta($re_product_cat, 'thumbnail_id'), 'thumbnail');
                                $link  = get_term_link($re_product_cat, 'product_cat');
                            }

                            $title = ! empty($re_custom_link) ? \HD_Helper::ACFLinkLabel($re_custom_link) : $title;
                            $icon  = ! empty($re_product_cat_img) ? \HD_Helper::attachmentImageHTML($re_product_cat_img, 'thumbnail') : $icon;
                            $link  = ! empty($re_custom_link) ? $re_custom_link : $link;
                        ?>
                            <div class="swiper-slide">
                                <div class="item">
                                    <?= \HD_Helper::ACFLinkOpen($link, 'item-link', $title) ?>
                                    <p class="title"><?= $title ?></p>
                                    <div class="thumb"><?= $icon ?></div>
                                    <?= \HD_Helper::ACFLinkClose($link, 'a') ?>
                                </div>
                            </div>
                        <?php $i++;
                        endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>