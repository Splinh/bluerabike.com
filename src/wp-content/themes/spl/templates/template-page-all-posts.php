<?php

/**
 * The template for displaying `All Posts page` with pagination
 * Template Name: All Posts
 * Template Post Type: page
 *
 * @author Gaudev
 */

\defined('ABSPATH') || die;

// header
get_header('archive');

if (have_posts()) {
    the_post();
}

if (post_password_required()) {
    echo get_the_password_form();
    get_footer('archive');

    return;
}

// breadcrumbs
\HD_Helper::blockTemplate('parts/blocks/breadcrumbs', ['title' => get_the_title()]);

/**
 * HOOK: hd_all_posts_before_action
 */
do_action('hd_all_posts_before_action');

// Get pagination
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

// Get posts per page from settings or default to 12
$posts_per_page = get_option('posts_per_page', 12);

// Query all posts
$args = [
    'post_type'      => 'post',
    'post_status'    => 'publish',
    'posts_per_page' => $posts_per_page,
    'paged'          => $paged,
    'orderby'        => 'date',
    'order'          => 'DESC',
];

$post_query = new WP_Query($args);

?>
<section class="section section-page section-all-posts archive">
    <div class="container flex flex-x">
        <div class="content">
            <h1 class="heading-title" <?= \HD_Helper::microdata('headline') ?>><?= get_the_title() ?></h1>
            <?php if (has_excerpt()) : ?>
                <div class="page-excerpt"><?= get_the_excerpt() ?></div>
            <?php endif; ?>

            <?php if ($post_query->have_posts()) : ?>
                <div class="posts-list archive-list items-list flex flex-x gap sm-up-1 md-up-2 lg-up-4">
                    <?php
                    // Start the Loop.
                    while ($post_query->have_posts()) : $post_query->the_post();

                        echo "<div class=\"cell\">";
                        \HD_Helper::blockTemplate('parts/post/loop', ['title_tag' => 'h2']);
                        echo "</div>";

                    // End the loop.
                    endwhile;
                    ?>
                </div>
            <?php
                // Pagination
                \HD_Helper::paginateLinks($post_query);
                wp_reset_postdata();
            else :
                \HD_Helper::blockTemplate('parts/blocks/no-results', [], true);
            endif;
            ?>
        </div>
        <?php if (is_active_sidebar('archive-sidebar')) : ?>
            <aside class="sidebar" <?= \HD_Helper::microdata('sidebar') ?>>
                <div class="sidebar-inner">
                    <?php dynamic_sidebar('archive-sidebar'); ?>
                </div>
            </aside>
        <?php endif;

        /**
         * HOOK: hd_all_posts_sidebar_action
         */
        do_action('hd_all_posts_sidebar_action');
        ?>
    </div>
</section>
<?php

/**
 * HOOK: hd_all_posts_after_action
 */
do_action('hd_all_posts_after_action');

// footer
get_footer('archive');
