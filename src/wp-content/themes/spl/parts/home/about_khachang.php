<?php
\defined('ABSPATH') || die;

$acf_fc_layout = $args['acf_fc_layout'] ?? '';
if ($acf_fc_layout !== 'about_khachang') {
    return;
}

$section_title  = $args['section_title'] ?? 'Khách Hàng Nói Về Chúng Tôi';
$list_danhgia   = $args['list_danhgia'] ?? [];
?>

<section class="section-khachhang">
    <div class="container">
        <h2 class="khachhang-title heading-title"><?= esc_html($section_title) ?></h2>

        <?php
        $_data = [
            'loop' => true,
            'smallgap' => 15,
            'slidesPerGroup' => 1,
            'desktop' => [
                'slidesPerView' => 3,
                'spaceBetween' => 30,
            ],
            'tablet' => ['slidesPerView' => 2,'spaceBetween' => 20],
            'mobile' => ['slidesPerView' => 1,  'spaceBetween' => 10],
        ];

        $swiper_data = json_encode($_data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        ?>

        <div class="swiper-container">
            <div class="w-swiper swiper">
                <div class="swiper-wrapper" data-options='<?= $swiper_data ?>'>
                    <?php if ($list_danhgia) : ?>
                        <?php foreach ($list_danhgia as $item) :
                            $avatar = $item['avatar'] ?? '';
                            $name = $item['name'] ?? '';
                            $position = $item['position'] ?? '';
                            $content = $item['content'] ?? '';
                        ?>
                            <div class="swiper-slide">
                                <div class="khachhang-box">
                                    <div class="rating">
                                        <?php for ($i = 0; $i < 5; $i++) : ?>
                                            <i class="fa-solid fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <p class="content">“<?= esc_html($content) ?>”</p>
                                    <div class="info">
                                        <?php if ($avatar) : ?>
                                            <div class="avatar">
                                                <?= wp_get_attachment_image($avatar, 'thumbnail', false, ['class' => 'img']); ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="text">
                                            <p class="name"><?= esc_html($name) ?></p>
                                            <p class="position"><?= esc_html($position) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
