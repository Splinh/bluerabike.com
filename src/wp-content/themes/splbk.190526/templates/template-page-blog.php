<?php
/**
 * The template for displaying `Blog page`
 * Template Name: Blog page
 * Template Post Type: page
 *
 * @author Gaudev
 */

\defined( 'ABSPATH' ) || die;

// header
get_header( 'blog' );

if ( have_posts() ) {
	the_post();
}

if ( post_password_required() ) {
	echo get_the_password_form();
	get_footer( 'blog' );

	return;
}

// breadcrumbs
\HD_Helper::blockTemplate( 'parts/blocks/breadcrumbs', [ 'title' => get_the_title() ] );

$ACF          = \HD_Helper::getFields( get_the_ID() );
$post_feature = $ACF['feature-list'] ?? [];
if ( $post_feature ) :
?>
<section class="section section-feature-posts">
    <div class="container">
        <h1 class="heading-title sr-only" <?= \HD_Helper::microdata( 'headline' ) ?>><?= get_the_title() ?></h1>
        <div class="news-list feature-list">
            <?php
                $i = 0;
                foreach ( $post_feature as $feature ) :
                    if ( $i > 4 ) {
                        break;
                    }

                    $post           = get_post( $feature );
                    $post_title     = get_the_title( $post->ID );
                    $post_title     = ( ! empty( $post_title ) ) ? $post_title : __( '(no title)', TEXT_DOMAIN );
                    $ratio_class    = \HD_Helper::aspectRatioClass( 'post' );
                    $post_thumbnail = get_the_post_thumbnail( $post, 'medium', [ 'alt' => \HD_Helper::escAttr( $post_title ) ] );

                    $class = ( $i === 0 ) ? ' large' : '';
            ?>
            <div class="item<?= $class ?>">
                <span class="cover">
                    <span class="scale res <?= $ratio_class ?>">
                        <?= $post_thumbnail ?>
                        <a class="link-cover" href="<?= get_permalink( $post->ID ) ?>" aria-label="<?= \HD_Helper::escAttr( $post_title ) ?>"></a>
                    </span>
                </span>
                <div class="content">
                    <a class="title" href="<?= get_permalink( $post->ID ) ?>" aria-label="<?= \HD_Helper::escAttr( $post_title ) ?>"><?= $post_title ?></a>
		            <?= \HD_Helper::loopExcerpt( $post ) ?>
                </div>
            </div>
            <?php
                $i++;
            endforeach;
            wp_reset_postdata();
            ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php
$post_query = \HD_Helper::queryByLatestPosts( 'post', 9 );
if ( $post_query ) :
?>
<section class="section section-latest-posts">
    <div class="container">
        <h2 class="section-title section-sub-title"><?= __( 'Các bài viết gần đây', TEXT_DOMAIN ) ?></h2>
        <div class="posts-list archive-list items-list flex flex-x gap sm-up-1 md-up-2 lg-up-3">
		    <?php
		    \HD_Helper::blockTemplate( 'parts/blocks/post/grid', [
			    'title_tag' => 'p',
			    'max'       => 9,
			    'query'     => $post_query,
		    ] );
		    ?>
        </div>
    </div>
</section>
<?php endif; ?>



<?php
$terms_list = $ACF['terms_list'] ?? '';
foreach ( $terms_list as $term_id ) :
    $term = \HD_Helper::getTerm( $term_id );
    if ( ! $term ) {
        continue;
    }

	$post_query = \HD_Helper::queryByTerm( $term, 'post', 8 );
    if ( ! $post_query ) {
        continue;
    }
?>
<section class="section section-terms">
    <div class="container">
        <div class="title">
            <h2 class="section-title section-sub-title"><?= $term?->name ?></h2>
            <a class="btn-link btn-link-third" href="<?= get_term_link( $term ); ?>" title="<?= esc_attr( $term?->name ) ?>"><?= __( 'Xem thêm', TEXT_DOMAIN  ) ?></a>
        </div>
        <div class="posts-list archive-list items-list flex flex-x gap sm-up-1 md-up-2 lg-up-4">
		    <?php
		    \HD_Helper::blockTemplate( 'parts/blocks/post/grid', [
			    'title_tag' => 'p',
			    'max'       => 8,
			    'query'     => $post_query,
		    ] );
		    ?>
        </div>
    </div>
</section>
<?php endforeach; ?>

<?php

// footer
get_footer( 'blog' );
