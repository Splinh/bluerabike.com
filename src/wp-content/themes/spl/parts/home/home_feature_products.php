<?php

\defined('ABSPATH') || die;

$acf_fc_layout = $args['acf_fc_layout'] ?? '';
if ($acf_fc_layout !== 'home_feature_products') {
    return;
}

if (! \HD_Helper::isWoocommerceActive()) {
    return;
}

// Enqueue section-specific CSS
\HD_Helper::enqueueSectionStyle('section-slide-shadow');


$home_product_title = $args['home_product_title'] ?? '';
$home_product_desc  = $args['home_product_desc'] ?? '';
$home_product_qty   = $args['home_product_qty'] ?? 10;
$home_terms         = $args['home_terms'] ?? 'featured';
$home_view_more     = $args['home_view_more'] ?? [];
$pagination         = $args['pagination'] ?? false;
$navigation         = $args['navigation'] ?? false;


$id = $args['id'] ?? 0;
$id = substr(md5($acf_fc_layout . '-' . $id), 0, 10);

$lang = function_exists('pll_current_language') ? pll_current_language() : 'vi';
$xt = ($lang === 'en') ? 'See more >' : 'Xem thêm >';
?>
<section id="section-<?= $id ?>" class="section section-products section-<?= $home_terms ?>-products section-slide-products section-slides section-slide-shadow">
    <div class="container">

        <div class="group-title">
            <?= $home_product_title ? '<h2 class="heading-title">' . $home_product_title . '</h2>' : '' ?>
            <?= $home_product_desc ? '<div class="home-desc">' . $home_product_desc . '</div>' : '' ?>

            <a class="view_more" href="<?= $home_view_more ?>"><?php echo \HD_Helper::pll_text('Xem thêm >', 'See more >');  ?></a>
        </div>

        <?php
        $product_visibility_term_ids = wc_get_product_visibility_term_ids();
        $args = [
            'post_type'           => 'product',
            'posts_per_page'      => $home_product_qty,
            'post_status'         => 'publish',
            'no_found_rows'       => true,
            'ignore_sticky_posts' => true,
            'orderby'             => 'menu_order',
            'order'               => 'ASC',
            'tax_query'           => [
                [
                    'taxonomy' => 'product_visibility',
                    'field'    => 'term_taxonomy_id',
                    'terms'    => $product_visibility_term_ids[$home_terms],
                    'operator' => 'IN',
                ],
            ],
        ];

        $post_query = new \WP_Query($args);
        if ($post_query?->have_posts()) :
        ?>
            <div class="products products-list">
                <?php
                $_data = [
                    'loop'       => true,
                    'autoview'   => true,
                    'smallgap'   => 0,
                    'slidesPerGroup' => 1,
                    'desktop' => [
                        'slidesPerGroup' => 1
                    ],

                ];

                if ($navigation) {
                    $_data['navigation'] = false;
                }
                if ($pagination) {
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
                                while ($post_query?->have_posts() && $i < $home_product_qty) : $post_query->the_post();
                                    $i++;

                                    echo '<div class="swiper-slide">';

                                    // ---- ĐÁNH DẤU ẢNH LCP CHO SẢN PHẨM ĐẦU TIÊN ----
                                    if ($i === 1) {
                                        $GLOBALS['NT_LCP_FIRST_THUMB_ID'] = get_post_thumbnail_id();
                                        $GLOBALS['NT_LCP_FIRST_ACTIVE']   = true;
                                    }

                                    do_action('woocommerce_shop_loop');
                                    wc_get_template_part('content', 'product');

                                    // tắt cờ sau item đầu
                                    if ($i === 1) {
                                        unset($GLOBALS['NT_LCP_FIRST_ACTIVE'], $GLOBALS['NT_LCP_FIRST_THUMB_ID']);
                                    }
                                    echo '</div>';
                                endwhile;

                                wp_reset_postdata();
                                ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        <?php endif; ?>
    </div>
</section>