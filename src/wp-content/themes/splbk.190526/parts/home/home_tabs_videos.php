<?php
\defined('ABSPATH') || die;

$acf_fc_layout = $args['acf_fc_layout'] ?? '';
if ($acf_fc_layout !== 'home_tabs_videos') {
    return;
}

// Enqueue section-specific CSS
\HD_Helper::enqueueSectionStyle('section-tabs');


$home_title = $args['home_title'] ?? '';
$home_number_max = $args['home_video_max'] ?? 9;
$home_video_category = $args['home_video_category'] ?? [];
$home_view_more = $args['home_view_more'] ?? '';
$sl_slide = $args['sl_slide'] ?? 0;
$id = substr(md5($acf_fc_layout . '-' . ($args['id'] ?? 0)), 0, 10);

// Dữ liệu cho Swiper
$_data = [
    'loop' => true,
    'autoview' => true,
    'smallgap' => 15,
    'slidesPerGroup' => 1,
    'desktop' => ['slidesPerView' => 3],
];
$swiper_data = json_encode($_data, JSON_THROW_ON_ERROR | JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE);
?>

<section id="section-<?= $id ?>" class="section section-home-video section-home-video-tab">
    <div class="container">

        <div class="filter-tabs">

            <div class="row-title">
                <div class="home-header-group">
                    <?= $home_title ? '<h2 class="heading-title">' . esc_html($home_title) . '</h2>' : '' ?>
                </div>

                <div class="tabs-nav">
                    <ul>
                        <?php
                        $term_arr = [];
                        foreach ($home_video_category as $key => $value):
                            $term = \HD_Helper::getTerm($value, 'video-cat');
                            if (!$term)
                                continue;
                            $current = (0 === $key) ? ' class="current title_tab"' : 'title_tab';
                            $href = 'tabvideo_' . $key;
                            $term_arr[$key] = $term;
                        ?>
                            <li>
                                <a <?= $current ?> href="#<?= esc_attr($href) ?>" title="<?= esc_attr($term->name) ?>">
                                    <?= esc_html($term->name) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                        <li>
                            <a class="xemtatca_post" href="<?= esc_url($home_view_more); ?>">
                                <span><?php echo \HD_Helper::pll_text('Xem tất cả', 'See all'); ?></span>
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 512 512"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                    <path
                                        d="M502.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L402.7 224 32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l370.7 0-105.4 105.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z" />
                                </svg>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="tabs-content">
                <?php
                foreach ($home_video_category as $key => $value):
                    $term = \HD_Helper::getTerm($value, 'video-cat');
                    if (!$term)
                        continue;
                    $current = (0 === $key) ? ' current' : '';
                    $href = 'tabvideo_' . $key;

                    $video_query = new WP_Query([
                        'post_type' => 'video',
                        'posts_per_page' => $home_number_max,
                        'tax_query' => [
                            [
                                'taxonomy' => 'video-cat',
                                'terms' => $term->term_id,
                            ]
                        ],
                    ]);
                ?>
                    <div id="<?= esc_attr($href) ?>" class="group tabs-panel<?= esc_attr($current) ?>">
                        <div class="panel-content">
                            <?php if ($video_query->have_posts()): ?>

                                <?php if ($sl_slide == 1): ?>
                                    <div class="swiper-container">
                                        <div class="w-swiper swiper">
                                            <div class="swiper-wrapper" data-options='<?= esc_attr($swiper_data) ?>'>
                                                <?php while ($video_query->have_posts()):
                                                    $video_query->the_post(); ?>
                                                    <div class="swiper-slide">
                                                        <?php \HD_Helper::blockTemplate('parts/post/loop-video', ['title_tag' => 'h3']); ?>
                                                    </div>
                                                <?php endwhile;
                                                wp_reset_postdata(); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="videos-grid flex flex-x gap sm-up-2 md-up-3 lg-up-3">
                                        <?php while ($video_query->have_posts()):
                                            $video_query->the_post(); ?>
                                            <?php \HD_Helper::blockTemplate('parts/post/loop-video', ['title_tag' => 'h3']); ?>
                                        <?php endwhile;
                                        wp_reset_postdata(); ?>
                                    </div>
                                <?php endif; ?>

                            <?php else: ?>
                                <p class="no-posts">
                                    <?php echo \HD_Helper::pll_text('Chưa có video nào trong danh mục này.', 'There are no videos in this category.'); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>