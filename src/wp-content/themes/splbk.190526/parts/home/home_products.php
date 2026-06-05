<?php

\defined('ABSPATH') || die;

$acf_fc_layout = $args['acf_fc_layout'] ?? '';
if ($acf_fc_layout !== 'home_products') {
    return;
}

if (! \HD_Helper::isWoocommerceActive()) {
    return;
}

// Enqueue section-specific CSS
\HD_Helper::enqueueSectionStyle('section-products');


$home_product_title = $args['home_product_title'] ?? '';
$home_product_desc  = $args['home_product_desc'] ?? '';
$home_product_cat   = $args['home_product_cat'] ?? [];
$home_product_qty   = $args['home_product_qty'] ?? 10;
$home_view_more     = $args['home_view_more'] ?? [];
$pagination         = $args['pagination'] ?? false;
$navigation         = $args['navigation'] ?? false;

$id = $args['id'] ?? 0;
$id = substr(md5($acf_fc_layout . '-' . $id), 0, 10);

?>
<section id="section-<?= $id ?>" class="section section-products section-slide-products section-slides section-slide-shadow">
    <div class="container">
        <div class="group-title">
            <?= $home_product_title ? '<h2 class="heading-title">' . $home_product_title . '</h2>' : '' ?>
            <?= $home_product_desc ? '<div class="home-desc">' . $home_product_desc . '</div>' : '' ?>
        </div>

        <?php
        if ($home_product_cat) :
            $post_query = \HD_Helper::queryByTerms($home_product_cat, 'product', 'product_cat', $home_product_qty);
            if ($post_query) :
        ?>
                <div class="products products-list">
                    <?php
                    $_data = [
                        'loop'       => true,
                        'autoview'   => true,
                        'smallgap'   => 15,
                        'slidesPerGroup' => 1,
                        'desktop' => [
                            'slidesPerGroup' => 5
                        ]
                    ];

                    if ($navigation) {
                        $_data['navigation'] = true;
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
                                        echo '<div class="swiper-slide">';
                                        do_action('woocommerce_shop_loop');
                                        wc_get_template_part('content', 'product');
                                        echo '</div>';

                                        $i++;
                                    endwhile;
                                    wp_reset_postdata();
                                    ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php echo \HD_Helper::ACFLink($home_view_more, 'btn-link btn-link-third'); ?>
                </div>
        <?php endif;
        endif; ?>
    </div>
</section>