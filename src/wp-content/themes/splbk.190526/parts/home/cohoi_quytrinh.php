<?php
defined('ABSPATH') || exit;

$acf_fc_layout = $args['acf_fc_layout'] ?? '';
if ($acf_fc_layout !== 'cohoi_quytrinh') {
    return;
}

// === Lấy dữ liệu từ ACF ===
$section_title = $args['section_title'] ?? 'Quy Trình Đăng Ký Nhanh Gọn';
$list_steps     = $args['list_steps'] ?? []; // repeater: icon, title, description
?>

<section class="process-section" id="process">
    <div class="container">
        <?php if ($section_title): ?>
            <h2 class="section-title"><?= esc_html($section_title); ?></h2>
        <?php endif; ?>

        <?php if (!empty($list_steps)) : ?>
            <div class="process-grid">
                <?php foreach ($list_steps as $step): 
                    $icon_class = $step['icon'] ?? '';
                    $title      = $step['title'] ?? '';
                    $desc       = $step['description'] ?? '';
                ?>
                    <div class="process-card">
                        <?php if ($icon_class): ?>
                            <?= $icon_class; ?>
                        <?php endif; ?>

                        <?php if ($title): ?>
                            <h3><?= esc_html($title); ?></h3>
                        <?php endif; ?>

                        <?php if ($desc): ?>
                            <p><?= esc_html($desc); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
