<?php

\defined('ABSPATH') || die;

$acf_fc_layout = $args['acf_fc_layout'] ?? '';
if ($acf_fc_layout !== 'home_form') {
    return;
}

// Enqueue section-specific CSS
\HD_Helper::enqueueSectionStyle('section-form');


$home_bg = $args['home_bg'] ?? 0;
$home_title  = $args['home_title'] ?? '';
$home_form   = $args['home_form'] ?? '';




?>
<section class="section section-form-home">

    <div class="container">

        <div class="contact-form">
            <div class="bg"></div>
            <?= \HD_Helper::pictureHTML('bg-cta', $home_bg) ?>

            <div class="inner">
                <?php echo $home_title ? '<h2 class="heading-title">' . $home_title . '</h2>' : ''; ?>
                <?php echo $home_form ? '<div class="form-wrapper">' . \HD_Helper::doShortcode('contact-form-7', ['id' => $home_form]) . '</div>' : ''; ?>
            </div>
        </div>
</section>