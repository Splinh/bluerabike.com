<?php
defined('ABSPATH') || exit;

$acf_fc_layout = $args['acf_fc_layout'] ?? '';
if ($acf_fc_layout !== 'cohoi_camket') {
    return;
}

// Lấy dữ liệu từ ACF
$bg_image     = $args['bg_image']['url'] ?? '';
$subtitle     = $args['subtitle'] ?? 'BLUERA VIỆT NHẬT';
$title        = $args['title'] ?? 'Cam Kết Từ Bluera Việt Nhật';
$desc_main    = $args['desc_main'] ?? 'Đồng hành lâu dài cùng đại lý, hỗ trợ mọi mặt trong quá trình kinh doanh.';
$desc_sub     = $args['desc_sub'] ?? 'Với hệ thống hỗ trợ bài bản và chính sách linh hoạt, Bluera Việt Nhật cam kết đồng hành cùng đại lý trên hành trình phát triển bền vững.';

$list_gallery = $args['list_gallery'] ?? []; // repeater: image
$list_ck      = $args['list_ck'] ?? []; // repeater: icon_svg, title, 
$logo      = $args['logo'] ?? []; // repeater: icon_svg, title, 

?>

<!-- ===== HERO SECTION ===== -->
<section class="hero" style="background-image: url('<?= esc_url($bg_image); ?>');">
    <div class="hero__overlay"></div>

    <div class="container hero__content">

        <?php if ($subtitle): ?>
            <p class="hero__subtitle"><?= esc_html($subtitle); ?></p>
        <?php endif; ?>

        <?php if ($title): ?>
            <h1 class="hero__title">“<?= esc_html($title); ?>”</h1>
        <?php endif; ?>

        <?php if ($desc_main): ?>
            <p class="hero__description"><?= esc_html($desc_main); ?></p>
        <?php endif; ?>

        <?php if ($desc_sub): ?>
            <p class="hero__description hero__description--sub"><?= esc_html($desc_sub); ?></p>
        <?php endif; ?>



        <!-- ===== FEATURES SECTION ===== -->
        <?php if (!empty($list_ck)): ?>
            <div class="features-container">
                <div class="features-grid">
                    <?php foreach ($list_ck as $feature):
                        $icon_svg = $feature['icon_svg'] ?? '';
                        $f_title  = $feature['title'] ?? '';
                        $f_desc   = $feature['subtitle'] ?? '';
                    ?>
                        <div class="feature-item">
                            <?php if ($icon_svg): ?>
                                <div class="feature-item__icon"><?= $icon_svg; ?></div>
                            <?php endif; ?>
                            <?php if ($f_title): ?>
                                <h3 class="feature-item__title"><?= esc_html($f_title); ?></h3>
                            <?php endif; ?>
                            <?php if ($f_desc): ?>
                                <p class="feature-item__description"><?= esc_html($f_desc); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</section>

<!-- ===== LOGO SECTION ===== -->
<section class="logo-section">
    <div class="logo-section__background">
        <div class="logo-section__inner">
            <?php echo wp_get_attachment_image($logo, 'medium'); ?>
        </div>
    </div>
</section>