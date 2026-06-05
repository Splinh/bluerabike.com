<?php

\defined('ABSPATH') || die;

$acf_fc_layout = $args['acf_fc_layout'] ?? '';
if ($acf_fc_layout !== 'home_tabs_products') {
    return;
}

// Enqueue section-specific CSS
\HD_Helper::enqueueSectionStyle('section-tabs');


$home_title = $args['home_title'] ?? '';
$home_product_max = $args['home_product_max'] ?? 9;
$home_product_category = $args['home_product_category'] ?? [];
$sl_slide = $args['sl_slide'] ?? 0;
$home_view_more = $args['home_view_more'] ?? '';

$id = $args['id'] ?? 0;
$id = substr(md5($acf_fc_layout . '-' . $id), 0, 10);


?>
<section id="section-<?= $id ?>" class="section section-home-product section-home-product-tab">
    <div class="container">


        <div class="filter-tabs">
            <div class="row-title">
                <div class="home-header-group">
                    <?= $home_title ? '<h2 class="heading-title">' . $home_title . '</h2>' : '' ?>
                </div>
                <div class="tabs-nav">
                    <ul>
                        <?php
                        $term_arr = [];

                        foreach ($home_product_category as $key => $value):
                            $current = (0 === $key) ? ' class="current title_tab"' : 'title_tab';
                            $href = 'tab' . '_' . $key;
                            $term = \HD_Helper::getTerm($value, 'product_cat');

                            $term_arr[$key] = $term;
                        ?>
                            <li>
                                <a <?= $current ?> href="#<?= $href ?>"
                                    title="<?= esc_attr($term->name) ?>"><?= $term->name ?></a>
                            </li>

                        <?php endforeach; ?>
                        <li>
                            <a class="xemtatca_product" href="<?= esc_url($home_view_more); ?>">
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

                $_data = [
                    'loop' => true,
                    'autoview' => true,
                    'smallgap' => 15,
                    'slidesPerGroup' => 1,
                    'desktop' => [
                        'slidesPerView' => 4
                    ]
                ];


                $swiper_data = json_encode($_data, JSON_THROW_ON_ERROR | JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE);


                foreach ($home_product_category as $key => $value):
                    $current = (0 === $key) ? ' current' : '';
                    $href = 'tab' . '_' . $key;
                    $term = \HD_Helper::getTerm($value, 'product_cat');
                    $post_query = \HD_Helper::queryByTerm($term, 'product', $home_product_max);
                ?>
                    <div id="<?= $href ?>" class="group tabs-panel<?= $current ?>">
                        <div class="panel-content">
                            <?php if ($post_query): ?>
                                <div class="products products-list swiper-container flex flex-x gap sm-up-2 md-up-2 lg-up-4">
                                    <?php
                                    if ($sl_slide == 1) {
                                        echo '<div class="w-swiper swiper">';
                                        echo '<div class="swiper-wrapper" data-options=' . $swiper_data . '>';
                                    }

                                    $i = 0;
                                    while ($post_query?->have_posts() && $i < $home_product_max):
                                        $post_query->the_post();

                                        if ($sl_slide == 1) {
                                            echo '<div class="swiper-slide">';
                                        }
                                        do_action('woocommerce_shop_loop');
                                        wc_get_template_part('content', 'product');
                                        if ($sl_slide == 1) {
                                            echo '</div>';
                                        }
                                        $i++;
                                    endwhile;
                                    wp_reset_postdata();
                                    ?>
                                </div>

                                <?php if ($sl_slide == 1) {
                                    echo '</div></div>';
                                } ?>


                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>