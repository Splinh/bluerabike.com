<?php
\defined('ABSPATH') || die;

global $post;

$title     = $args['title'] ?? get_the_title($post->ID);
$title_tag = $args['title_tag'] ?? 'h3';
$ratio     = $args['ratio'] ?? \HD_Helper::aspectRatioClass(get_post_type($post->ID));

// Lấy link video từ ACF
$link_video = get_field('link_video', $post->ID);

// Lấy ảnh đại diện (thumbnail)
$thumbnail = \HD_Helper::postImageHTML($post->ID, 'medium', ['alt' => \HD_Helper::escAttr($title)]);
if (!$thumbnail) {
    // Nếu không có thumbnail thì lấy ảnh từ YouTube
    if ($link_video && preg_match('/v=([^&]+)/', $link_video, $m)) {
        $video_id = $m[1];
        $thumbnail = '<img src="https://img.youtube.com/vi/' . esc_attr($video_id) . '/hqdefault.jpg" alt="' . esc_attr($title) . '" />';
    } else {
        $thumbnail = '<img src="' . esc_url(\HD_Helper::placeholderSrc()) . '" alt="' . esc_attr($title) . '" />';
    }
}
?>

<div class="item video-item">
    <div class="cover">
        <span class="scale res <?= esc_attr($ratio) ?>">
            <?= $thumbnail ?>
            <?php if ($link_video): ?>
                <a class="link-cover play-video" href="#" data-youtube="<?= esc_url($link_video) ?>" aria-label="<?= esc_attr($title) ?>">
                    <span class="play-icon">▶</span>
                </a>
            <?php endif; ?>
        </span>
    </div>
    <div class="content">
        <div class="cat">
            <?php echo \HD_Helper::getPrimaryTerm($post,'video-cat'); ?>
        </div>
    
        <<?= $title_tag ?> class="title">
    
             <?php if ($link_video): ?>
                <a class="link-cover play-video" href="#" data-youtube="<?= esc_url($link_video) ?>" aria-label="<?= esc_attr($title) ?>">
                   <?= esc_html($title) ?>
                </a>
            <?php endif; ?>
        </<?= $title_tag ?>>
    </div>
</div>