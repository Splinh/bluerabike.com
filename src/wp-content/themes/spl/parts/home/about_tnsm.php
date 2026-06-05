<?php
\defined('ABSPATH') || die;

$acf_fc_layout = $args['acf_fc_layout'] ?? '';
if ($acf_fc_layout !== 'about_tnsm') {
    return;
}

$section_title  = $args['section_title'] ?? 'Khách Hàng Nói Về Chúng Tôi';
$list_tnsm   = $args['list_tnsm'] ?? [];
?>


<section class="section-section-tnsm">
    <div class="container">

        <div class="tnsm-list">
            <?php if ($list_tnsm) : ?>
                <?php foreach ($list_tnsm as $item) :
                    $icon = $item['icon'] ?? '';
                    $title = $item['title'] ?? '';
                    $description = $item['description'] ?? '';
                ?>
                    <div class="tnsm-item">
                        <?php if ($icon) : ?>
                            <div class="img">
                                <?= wp_get_attachment_image($icon, 'large', false, ['class' => 'img-tnsm']); ?>
                            </div>
                        <?php endif; ?>
                        <div class="info">
                            <h3 class="item-title"><?= esc_html($title) ?></h3>
                            <div class="item-description"><?= wp_kses_post($description) ?></div>
                        </div>

                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>