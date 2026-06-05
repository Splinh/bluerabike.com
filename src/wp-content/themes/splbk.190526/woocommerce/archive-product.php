<?php

/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 */

defined('ABSPATH') || exit;

get_header('shop');

// breadcrumbs
\HD_Helper::blockTemplate('parts/blocks/breadcrumbs', ['title' => get_the_archive_title()]);

?>
<section class="section archive archive-product">
    <div class="container">
        <?php

        /**
         * Hook: woocommerce_sidebar.
         *
         * @see woocommerce_get_sidebar - 10
         */
        do_action('woocommerce_sidebar');

        ?>
        <!-- Mobile Filter Button -->
        <button type="button" class="mobile-filter-toggle" aria-label="<?= esc_attr__('Lọc sản phẩm', 'flavor') ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
            </svg>
            <span><?= __('Lọc sản phẩm', 'flavor') ?></span>
        </button>

        <!-- Mobile Sidebar Overlay -->
        <div class="mobile-sidebar-overlay"></div>

        <div class="cell-content">
            <?php
            /**
             * Hook: woocommerce_before_main_content.
             *
             * @see woocommerce_output_content_wrapper - 10 (outputs opening divs for the content) [ -- null ]
             * @see woocommerce_breadcrumb - 20
             * @see WC_Structured_Data::generate_website_data() - 30
             * 
             */

            do_action('woocommerce_before_main_content');

            /**
             * Hook: woocommerce_shop_loop_header.
             *
             * @since 8.6.0
             *
             * @see woocommerce_product_taxonomy_archive_header - 10
             */
            do_action('woocommerce_shop_loop_header');

            $object = get_queried_object();
            $product_cat_child = \HD_Helper::getField('product_cat_child', $object);
            if ($product_cat_child):

                ?>
                <div class="woocommerce-child-terms">
                    <?php

                    foreach ($product_cat_child as $child_term_id):
                        $child_term = \HD_Helper::getTerm($child_term_id, 'product_cat');
                        if (!$child_term || is_wp_error($child_term)) {
                            continue;
                        }

                        $thumbnail_id = get_term_meta($child_term->term_id, 'thumbnail_id', true);
                        ?>
                        <div class="item">
                            <a href="<?= get_term_link($child_term, 'product_cat') ?>"
                                aria-label="<?= esc_attr($child_term->name) ?>">
                                <span class="thumb"><?= \HD_Helper::attachmentImageHTML($thumbnail_id); ?></span>
                                <p class="name"><?= $child_term->name ?></p>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif;

            // Product Attributes
            if (!is_search() && is_active_sidebar('product-attributes-sidebar')):
                echo '<section class="section product-attributes"><div class="inner">';

                dynamic_sidebar('product-attributes-sidebar');

                echo '</div></section>';
            endif;

            // Should the WooCommerce loop be displayed
            if (woocommerce_product_loop()) {

                /**
                 * Hook: woocommerce_before_shop_loop.
                 *
                 * @see woocommerce_output_all_notices - 10
                 * @see woocommerce_result_count - 20
                 * @see woocommerce_catalog_ordering - 30
                 */
                echo '<div class="woocommerce-shop-info">';
                do_action('woocommerce_before_shop_loop');
                echo '</div>';
                woocommerce_product_loop_start();

                if (wc_get_loop_prop('total')) {
                    while (have_posts()) {
                        the_post();

                        do_action('woocommerce_shop_loop');
                        wc_get_template_part('content', 'product');
                    }
                }

                woocommerce_product_loop_end();

                /**
                 * Hook: woocommerce_after_shop_loop.
                 *
                 * @see woocommerce_pagination - 10
                 */
                do_action('woocommerce_after_shop_loop');
            } else {
                /**
                 * Hook: woocommerce_no_products_found.
                 *
                 * @see wc_no_products_found - 10
                 */
                do_action('woocommerce_no_products_found');
            }

            /**
             * Hook: woocommerce_after_main_content.
             *
             * @see woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content) [ -- null ]
             */
            do_action('woocommerce_after_main_content');

            ?>
        </div>
        <?php

        /**
         * Hook: woocommerce_shop_loop_footer.
         *
         * @see wc_product_taxonomy_archive_footer - 10
         */
        do_action('woocommerce_shop_loop_footer');

        ?>
    </div>
</section>
<?php

get_footer('shop');
