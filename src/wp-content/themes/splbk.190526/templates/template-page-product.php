<?php
/**
 * The template for displaying `Product page`
 * Template Name: Product page
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

$ACF               = \HD_Helper::getFields( get_the_ID() );
$product_img       = $ACF['product_img'] ?? '';
$product_title     = $ACF['product_title'] ?? '';
$product_desc      = $ACF['product_desc'] ?? '';
$product_term_list = $ACF['product_term_list'] ?? [];

$title = ( ! empty( $product_title ) ) ? $product_title : get_the_title();

?>
<section class="section section-product-banner">
    <div class="container">
        <?php echo $product_img ? \HD_Helper::pictureHTML( 'product-banner', $product_img ) : ''; ?>
        <h1 class="heading-title"><?= $title ?></h1>
        <div class="content">
            <?php echo $product_desc ? '<div class="desc">' . $product_desc . '</div>' : ''; ?>
            <?php the_content(); ?>
        </div>
    </div>
</section>

<?php if ( $product_term_list ) :
	foreach ( $product_term_list as $term_id ) :
		$term = \HD_Helper::getTerm( $term_id, 'product_cat' );
		if ( ! $term ) {
			continue;
		}

		$post_query = \HD_Helper::queryByTerm( $term, 'product', 8 );
		if ( ! $post_query ) {
			continue;
		}
?>
<section class="section section-terms section-product-terms">
    <div class="container">
        <div class="title">
            <h2 class="section-title section-sub-title"><?= $term?->name ?></h2>
            <a class="btn-link btn-link-third" href="<?= get_term_link( $term ) ?>" title="<?= esc_attr( $term?->name ) ?>"><?= __( 'Xem thêm', TEXT_DOMAIN  ) ?></a>
        </div>

        <?php
            $i = 0;
	        woocommerce_product_loop_start();

	        // Start the Loop.
	        while ( $post_query?->have_posts() && $i < 8 ) : $post_query->the_post();

		        do_action( 'woocommerce_shop_loop' );
		        wc_get_template_part( 'content', 'product' );

		        // End the loop.
		        $i++;
	        endwhile;
            wp_reset_postdata();

            woocommerce_product_loop_end();
        ?>
    </div>
</section>
<?php
	endforeach;
    endif;

// footer
get_footer( 'shop' );
