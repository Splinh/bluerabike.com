<?php
/**
 * The template for displaying `Featured product page`
 * Template Name: Featured-product page
 * Template Post Type: page
 *
 * @author Gaudev
 */

\defined( 'ABSPATH' ) || die;

if ( ! \HD_Helper::isWoocommerceActive() ) {
	return;
}


// header
get_header( 'shop' );

if ( have_posts() ) {
	the_post();
}

if ( post_password_required() ) {
	echo get_the_password_form();
	get_footer( 'shop' );

	return;
}

// breadcrumbs
\HD_Helper::blockTemplate( 'parts/blocks/breadcrumbs', [ 'title' => get_the_title( $post->ID ) ] );

/**
 * HOOK: hd_page_before_action
 */
do_action( 'hd_page_before_action' );

$paged                       = max( 1, get_query_var( 'paged' ) );
$home_terms                  = \HD_Helper::getField( 'home_terms', $post->ID ) ?: 'featured';
$product_visibility_term_ids = wc_get_product_visibility_term_ids();

$args  = [
	'post_type'      => 'product',
	'posts_per_page' => wc_get_default_products_per_row() * wc_get_default_product_rows_per_page(),
	'paged'          => $paged,
	'post_status'    => 'publish',
	'tax_query'           => [
		[
			'taxonomy' => 'product_visibility',
			'field'    => 'term_taxonomy_id',
			'terms'    => $product_visibility_term_ids[$home_terms],
			'operator' => 'IN',
		],
	],
];

$query               = new WP_Query( $args );
$GLOBALS['wp_query'] = $query;

wc_set_loop_prop( 'is_paginated', true );
wc_set_loop_prop( 'page', $paged );
wc_set_loop_prop( 'per_page', $args['posts_per_page'] );
wc_set_loop_prop( 'total', $query->found_posts );
wc_set_loop_prop( 'total_pages', $query->max_num_pages );

?>
<section class="section section-featured-page archive archive-product">
    <div class="container">
	    <?php
	    /**
	     * Hook: woocommerce_sidebar.
	     *
	     * @see woocommerce_get_sidebar - 10
	     */
	    do_action( 'woocommerce_sidebar' );

	    ?>
        <div class="cell-content">
            <h1 class="heading-title sr-only" <?= \HD_Helper::microdata( 'headline' ) ?>><?= get_the_title() ?></h1>
            <?php echo \HD_Helper::postExcerpt( $post, 'excerpt', false ); ?>
            <?php
            if ( $query->have_posts() ) {

                /**
                 * Hook: woocommerce_before_shop_loop.
                 *
                 * @see woocommerce_output_all_notices - 10
                 * @see woocommerce_result_count - 20
                 * @see woocommerce_catalog_ordering - 30
                 */
                do_action( 'woocommerce_before_shop_loop' );

                woocommerce_product_loop_start();

                while ( $query->have_posts() ) {
                    $query->the_post();

                    do_action( 'woocommerce_shop_loop' );
                    wc_get_template_part( 'content', 'product' );
                }

                woocommerce_product_loop_end();

                /**
                 * Hook: woocommerce_after_shop_loop.
                 * Hook: woocommerce_after_shop_loop.
                 *
                 * @see woocommerce_pagination - 10
                 */
                do_action( 'woocommerce_after_shop_loop' );
            } else {
                /**
                 * Hook: woocommerce_no_products_found.
                 *
                 * @see wc_no_products_found - 10
                 */
                do_action( 'woocommerce_no_products_found' );
            }

            wp_reset_postdata();

            ?>
        </div>
    </div>
</section>
<?php

/**
 * HOOK: hd_page_after_action
 */
do_action( 'hd_page_after_action' );

// footer
get_footer( 'shop' );
