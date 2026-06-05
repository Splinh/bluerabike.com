<?php

/**
 * The Template for displaying all single posts.
 * http://codex.wordpress.org/Template_Hierarchy
 *
 * @author Gaudev
 */

\defined('ABSPATH') || die;

// header
get_header('single');

if (have_posts()) {
	the_post();
}

if (post_password_required()) {
	echo get_the_password_form();
	get_footer('single');

	return;
}

// breadcrumbs
\HD_Helper::blockTemplate('parts/blocks/breadcrumbs', ['title' => \HD_Helper::primaryTerm($post)?->name]);

/**
 * HOOK: hd_single_before_action
 */
do_action('hd_single_before_action');

$alternative_title = \HD_Helper::getField('alternative_title', $post->ID);
$featured_banner   = \HD_Helper::getField('featured_banner', $post->ID);

$icon_eye = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path d="M320 96C239.2 96 174.5 132.8 127.4 176.6C80.6 220.1 49.3 272 34.4 307.7C31.1 315.6 31.1 324.4 34.4 332.3C49.3 368 80.6 420 127.4 463.4C174.5 507.1 239.2 544 320 544C400.8 544 465.5 507.2 512.6 463.4C559.4 419.9 590.7 368 605.6 332.3C608.9 324.4 608.9 315.6 605.6 307.7C590.7 272 559.4 220 512.6 176.6C465.5 132.9 400.8 96 320 96zM176 320C176 240.5 240.5 176 320 176C399.5 176 464 240.5 464 320C464 399.5 399.5 464 320 464C240.5 464 176 399.5 176 320zM320 256C320 291.3 291.3 320 256 320C244.5 320 233.7 317 224.3 311.6C223.3 322.5 224.2 333.7 227.2 344.8C240.9 396 293.6 426.4 344.8 412.7C396 399 426.4 346.3 412.7 295.1C400.5 249.4 357.2 220.3 311.6 224.3C316.9 233.6 320 244.4 320 256z"/></svg>';

$icon_calendar = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path d="M224 64C241.7 64 256 78.3 256 96L256 128L384 128L384 96C384 78.3 398.3 64 416 64C433.7 64 448 78.3 448 96L448 128L480 128C515.3 128 544 156.7 544 192L544 480C544 515.3 515.3 544 480 544L160 544C124.7 544 96 515.3 96 480L96 192C96 156.7 124.7 128 160 128L192 128L192 96C192 78.3 206.3 64 224 64zM160 304L160 336C160 344.8 167.2 352 176 352L208 352C216.8 352 224 344.8 224 336L224 304C224 295.2 216.8 288 208 288L176 288C167.2 288 160 295.2 160 304zM288 304L288 336C288 344.8 295.2 352 304 352L336 352C344.8 352 352 344.8 352 336L352 304C352 295.2 344.8 288 336 288L304 288C295.2 288 288 295.2 288 304zM432 288C423.2 288 416 295.2 416 304L416 336C416 344.8 423.2 352 432 352L464 352C472.8 352 480 344.8 480 336L480 304C480 295.2 472.8 288 464 288L432 288zM160 432L160 464C160 472.8 167.2 480 176 480L208 480C216.8 480 224 472.8 224 464L224 432C224 423.2 216.8 416 208 416L176 416C167.2 416 160 423.2 160 432zM304 416C295.2 416 288 423.2 288 432L288 464C288 472.8 295.2 480 304 480L336 480C344.8 480 352 472.8 352 464L352 432C352 423.2 344.8 416 336 416L304 416zM416 432L416 464C416 472.8 423.2 480 432 480L464 480C472.8 480 480 472.8 480 464L480 432C480 423.2 472.8 416 464 416L432 416C423.2 416 416 423.2 416 432z"/></svg>';

?>
<section class="section section-page section-single singular">
	<div class="container flex flex-x">
		<div class="content">
			<h1 class="heading-title" <?= \HD_Helper::microdata('headline') ?>><?= $alternative_title ?: get_the_title() ?></h1>
			<div class="meta">
				<?php echo \HD_Helper::getPrimaryTerm($post); ?>
				<span class="" <?= \HD_Helper::microdata('date-published') ?>> <?= \HD_Helper::humanizeTime($post->ID) ?></span>
				<?php
				$views = get_post_meta($post->ID, '_post_views', true);
				$views = $views ? (int) $views : 1;
				?> /
				<div class="view">
					<?php echo \HD_Helper::pll_text('Lượt xem:', 'View:');  ?><span class=""> <?= number_format_i18n($views) ?></span>

				</div>
			</div>

			<?php echo $featured_banner ? \HD_Helper::pictureHTML('featured-img', $featured_banner) : ''; ?>
			<?php echo \HD_Helper::postExcerpt($post, 'excerpt', 'div', false); ?>

			<article class="entry-content" <?= \HD_Helper::microdata('article') ?>>
				<?php
				the_content();
				\HD_Helper::blockTemplate('parts/blocks/post/suggestion-posts');
				?>
			</article>
			<?php
			\HD_Helper::hashTags();
			\HD_Helper::blockTemplate('parts/blocks/social-share', [], true);
			\HD_Helper::blockTemplate('parts/blocks/author');

			// If comments are open, or we have at least one comment, load up the comment template.
			comments_template();
			?>
		</div>
		<?php if (is_active_sidebar('news-sidebar')) : ?>
			<aside class="sidebar" <?= \HD_Helper::microdata('sidebar') ?>>
				<div class="sidebar-inner">
					<?php dynamic_sidebar('news-sidebar'); ?>
				</div>
			</aside>
		<?php endif;

		/**
		 * HOOK: hd_singular_sidebar_action
		 */
		do_action('hd_singular_sidebar_action');

		?>
	</div>
</section>
<?php

\HD_Helper::blockTemplate('parts/blocks/post/related-posts', [
	'title'     => __('Bài viết liên quan', TEXT_DOMAIN),
	'title_tag' => 'h2',
	'id'        => $post->ID,
	'max'       => 12,
	'rows'      => 1,
	'taxonomy'  => 'category',
]);

/**
 * HOOK: hd_single_after_action
 */
do_action('hd_single_after_action');

// footer
get_footer('single');
