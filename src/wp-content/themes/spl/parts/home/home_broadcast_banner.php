<?php

\defined('ABSPATH') || die;

$acf_fc_layout = $args['acf_fc_layout'] ?? '';
if ($acf_fc_layout !== 'home_broadcast_banner') {
    return;
}

// Enqueue section-specific CSS
\HD_Helper::enqueueSectionStyle('section-broadcast-banner');


$home_full_width      = $args['home_full_width'] ?? true;
$home_navigation      = $args['home_navigation'] ?? true;
$home_pagination      = $args['home_pagination'] ?? false;
$home_gap             = $args['home_gap'] ?? false;
$home_heading_tag     = $args['home_heading_tag'] ?? 'h3';
$home_banner_repeater = $args['home_banner_repeater'] ?? [];

if (empty($home_banner_repeater)) {
    return;
}

$id         = $args['id'] ?? 0;
$id         = substr(md5($acf_fc_layout . '-' . $id), 0, 10);
$full_class = $home_full_width ? ' full' : '';

?>
<section id="section-<?= $id ?>" class="section section-broadcast-banner section-slides">
    <div class="container<?= $full_class ?>">
        <?php
        $swiper_css = 'swiper';
        $swiper_css = $home_gap ? $swiper_css . ' swiper-gap' : $swiper_css;

        $_data = [
            'loop'       => true,
            'autoview'   => true,
            'smallgap'   => 12,
        ];

        if ($home_gap) {
            $_data['gap'] = true;
        }
        if ($home_navigation) {
            $_data['navigation'] = true;
        }
        if ($home_pagination) {
            $_data['pagination'] = 'bullets';
        }

        $swiper_data = json_encode($_data, JSON_THROW_ON_ERROR | JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE);
        if ($swiper_data) :

        ?>
            <div class="swiper-container">
                <div class="w-swiper <?= $swiper_css ?>">
                    <div class="swiper-wrapper" data-options='<?= $swiper_data ?>'>
                        <?php
                        $i = 0;
                        foreach ($home_banner_repeater as $banner_id => $banner) :
                            $re_img     = $banner['re_img'] ?? 0;
                            $re_title   = $banner['re_title'] ?? '';
                            $re_desc    = $banner['re_desc'] ?? '';
                            $re_link    = $banner['re_link'] ?? [];
                            $re_bgcolor = $banner['re_bgcolor'] ?? '';
                            $re_color   = $banner['re_color'] ?? '';

                            $css = ! empty($re_bgcolor) ? 'background-color:' . $re_bgcolor . ';' : '';
                            $css .= ! empty($re_color) ? 'color:' . $re_color . ';' : '';
                        ?>
                            <div class="swiper-slide">
                                <?= ! empty($re_bgcolor) ? '<style>#section-' . $id . ' .swiper .item-' . $banner_id . '{' . $css . '}</style>' : '' ?>
                                <div class="item item-<?= $banner_id ?>">

                                    <?= \HD_Helper::ACFLinkOpen($re_link) ?>

                                    <span class="broadcast-img cover">
                                        <?= \HD_Helper::attachmentImageHTML($re_img, 'medium') ?>
                                    </span>

                                    <?php if ($re_title) : ?>
                                        <div class="cover-content">

                                            <?= '<' . $home_heading_tag . ' class="broadcast-title">' . $re_title . '</' . $home_heading_tag . '>' ?>
                                            <?= $re_desc ? '<div class="broadcast-desc">' . $re_desc . '</div>' : '' ?>
                                            <?= \HD_Helper::ACFLinkLabel($re_link, 'broadcast-link') ?>

                                        </div>
                                    <?php endif; ?>

                                    <?= \HD_Helper::ACFLinkClose($re_link) ?>

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