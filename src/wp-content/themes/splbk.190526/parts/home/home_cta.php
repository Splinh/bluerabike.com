<?php

\defined('ABSPATH') || die;

$acf_fc_layout = $args['acf_fc_layout'] ?? '';
if ($acf_fc_layout !== 'home_cta') {
    return;
}

// Enqueue section-specific CSS
\HD_Helper::enqueueSectionStyle('section-cta');


$home_banner = $args['home_banner'] ?? 0;
$home_title  = $args['home_title'] ?? '';
$home_desc   = $args['home_desc'] ?? '';
$button_link = $args['button_link'] ?? [];

if (! $home_banner) {
    return;
}

?>
<section class="section section-cta">
    <?= \HD_Helper::pictureHTML('bg-cta', $home_banner) ?>
    <div class="container">
        <div class="inner">
            <div class="title"><?= $home_title ?></div>
            <div class="desc"><?= $home_desc ?></div>
            <?= \HD_Helper::ACFLink($button_link, 'btn-link') ?>
        </div>
    </div>
</section>