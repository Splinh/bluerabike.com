<?php
/**
 * The template for displaying `Cart & Checkout page`
 * Template Name: Cart & Checkout page
 * Template Post Type: page
 *
 * @author Gaudev
 */

\defined( 'ABSPATH' ) || die;

// header
get_header( 'cart' );

if ( have_posts() ) {
	the_post();
}

// breadcrumbs
\HD_Helper::blockTemplate( 'parts/blocks/breadcrumbs', [ 'title' => get_the_title( $post->ID ) ] );

?>
<section class="section section-page singular">
	<div class="container flex flex-x">
		<div class="content">
			<h1 class="heading-title" <?= \HD_Helper::microdata( 'headline' ) ?>><?= get_the_title() ?></h1>
			<?php echo \HD_Helper::postExcerpt( $post, 'excerpt', false ); ?>
			<article <?= \HD_Helper::microdata( 'article' ) ?>>
				<?php the_content(); ?>
			</article>
		</div>
	</div>
</section>
<?php

// footer
get_footer( 'cart' );
