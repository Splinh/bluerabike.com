<?php

\defined('ABSPATH') || die;

$acf_fc_layout = $args['acf_fc_layout'] ?? '';
if ($acf_fc_layout !== 'home_map') {
    return;
}

// Enqueue section-specific CSS
\HD_Helper::enqueueSectionStyle('section-map');



$home_title  = $args['home_title'] ?? '';
$map   = $args['map'] ?? '';


?>
<section class="section section-map sc-pd">
    <h2 class="heading-title"><?= $home_title ?></h2>
    <div class="map">
        <?= $map; ?>
    </div>
</section>