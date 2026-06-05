<?php

\defined('ABSPATH') || die;

$acf_fc_layout = $args['acf_fc_layout'] ?? '';
if ($acf_fc_layout !== 'home_latest_news') {
    return;
}

// Enqueue section-specific CSS
\HD_Helper::enqueueSectionStyle('section-latest-news');


$home_title        = $args['home_title'] ?? '';
$home_desc         = $args['home_desc'] ?? '';
$home_category     = $args['home_category'] ?? [];
$home_display_mode = $args['home_display_mode'] ?? 'slider';
$home_number_max   = $args['home_number_max'] ?? 10;
$home_view_more    = $args['home_view_more'] ?? '';
$home_navigation   = $args['home_navigation'] ?? '';
$home_pagination   = $args['home_pagination'] ?? '';

$post_query = \HD_Helper::queryByTerms($home_category, 'post', 'category', $home_number_max);
$id         = $args['id'] ?? 0;
$id         = substr(md5($acf_fc_layout . '-' . $id), 0, 10);

?>
<section id="section-<?= $id ?>" class="section section-latest-news section-slides">
    <div class="container">

        <div class="group-title">
            <?= $home_title ? '<h2 class="heading-title">' . $home_title . '</h2>' : '' ?>
            <?= $home_desc ? '<div class="home-desc">' . $home_desc . '</div>' : '' ?>
        </div>

        <?php if ($post_query) : ?>
            <!-- Slider -->
            <?php if ($home_display_mode === 'slider') : ?>

                <div class="posts-list items-list">
                    <?php
                    $_data = [
                        'loop'     => true,
                        'autoview' => true,
                        'gap'      => true,
                    ];

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
                            <div class="w-swiper swiper">
                                <div class="swiper-wrapper" data-options='<?= $swiper_data ?>'>
                                    <?php
                                    $i = 0;
                                    while ($post_query?->have_posts() && $i < $home_number_max) : $post_query->the_post();

                                        echo '<div class="swiper-slide">';
                                        \HD_Helper::blockTemplate('parts/post/loop', ['title_tag' => 'h3']);
                                        echo '</div>';

                                        $i++;
                                    endwhile;
                                    wp_reset_postdata();
                                    ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <!-- List -->
            <?php elseif ($home_display_mode === 'list') :
                if ($home_number_max > 5) {
                    $home_number_max = 5;
                }
            ?>

                <div class="news-list inspiration-grid">
                    <?php
                    $i = 0;
                    while ($post_query?->have_posts() && $i < $home_number_max) : $post_query->the_post();
                        $post           = get_post();
                        $post_title     = get_the_title($post->ID);
                        $post_title     = (! empty($post_title)) ? $post_title : __('(no title)', TEXT_DOMAIN);
                        $ratio_class    = \HD_Helper::aspectRatioClass('post');
                        $post_thumbnail = get_the_post_thumbnail($post, 'medium', ['alt' => \HD_Helper::escAttr($post_title)]);

                        $class = ($i === 0) ? ' large' : '';
                    ?>
                        <div class="item inspiration-card<?= $class ?>">
                            <span class="cover">
                                <span class="scale res <?= $ratio_class ?>">
                                    <?= $post_thumbnail ?>
                                    <a class="link-cover" href="<?= get_permalink($post->ID) ?>" aria-label="<?= \HD_Helper::escAttr($post_title) ?>"></a>
                                </span>
                            </span>
                            <div class="content inspiration-overlay">
                                <a class="title" href="<?= get_permalink($post->ID) ?>" aria-label="<?= \HD_Helper::escAttr($post_title) ?>"><?= $post_title ?></a>
                                <?= \HD_Helper::loopExcerpt($post) ?>
                            </div>
                        </div>
                    <?php $i++;
                    endwhile;
                    wp_reset_postdata(); ?>
                </div>

            <?php endif; ?>

        <?php endif; ?>

        <?php echo \HD_Helper::ACFLink($home_view_more, 'btn-link btn-link-third'); ?>
    </div>
</section>