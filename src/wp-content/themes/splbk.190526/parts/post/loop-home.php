<?php

/**
 * The loop.php file in WordPress handles displaying post's summaries in lists,
 * such as archives or blog pages v.v...
 *
 * @author Gaudev
 */

\defined('ABSPATH') || die;

global $post;

$title     = $args['title'] ?? get_the_title($post->ID);
$title_tag = $args['title_tag'] ?? 'p';
$ratio     = $args['ratio'] ?? \HD_Helper::aspectRatioClass(get_post_type($post->ID));

$thumbnail = \HD_Helper::postImageHTML($post->ID, 'medium', ['alt' => \HD_Helper::escAttr($title)]);
if (! $thumbnail) {
    $thumbnail = \HD_Helper::placeholderSrc();
}

$title = ! empty($title) ? $title : __('(no title)', TEXT_DOMAIN);

?>
<div class="item">
    <div class="cover">
        <div class="meta">
            <span class="date">
                <?php echo get_the_date('j'); ?><br>
                <?php echo get_the_date('M'); ?>
            </span>
        </div>
        <span class="scale res <?= $ratio ?>">
            <?php echo $thumbnail; ?>
            <a class="link-cover" href="<?= get_permalink($post->ID) ?>" aria-label="<?= \HD_Helper::escAttr($title) ?>"></a>
        </span>
    </div>
    <div class="content">
        <div class="cat">
            <?php echo \HD_Helper::getPrimaryTerm($post); ?>
        </div>
        <?php echo '<' . $title_tag . ' class="title"><a href="' . get_permalink($post->ID) . '" title="' . \HD_Helper::escAttr($title) . '">' . $title . '</a></' . $title_tag . '>'; ?>

    </div>
</div>