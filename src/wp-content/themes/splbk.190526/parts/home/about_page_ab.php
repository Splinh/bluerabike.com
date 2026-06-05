<?php

\defined('ABSPATH') || die;

$acf_fc_layout = $args['acf_fc_layout'] ?? '';
if ($acf_fc_layout !== 'about_page_ab') {
    return;
}
$section_title  = $args['section_title'] ?? '';
$section_des  = $args['section_des'] ?? '';
$link_video  = $args['link_video'] ?? '';
$thumbnail = $args['thumbnail'] ?? '';
if (!$thumbnail) {
    // Nếu không có thumbnail thì lấy ảnh từ YouTube
    if ($link_video && preg_match('/v=([^&]+)/', $link_video, $m)) {
        $video_id = $m[1];
        $thumbnail = '<img src="https://img.youtube.com/vi/' . esc_attr($video_id) . '/hqdefault.jpg" alt="Giới thiệu Bluera Việt Nhật" />';
    } else {
        $thumbnail = '<img src="' . wp_get_attachment_image_url($thumbnail, 'large') . '" alt="Giới thiệu Bluera Việt Nhật" />';
    }
}

?>

<section class="about-section" id="about-page-ab">
    <div class="container">
        <div class="row">
            <div class="left col">
                <h2 class="heading-title"><?php echo $section_title; ?></h2>
                <div class="desc">
                    <?= $section_des; ?>
                </div>
            </div>
            <div class="right video-item col">
                <div class="cover">
                    <span class="scale res ar-4-3">
                        <?php echo wp_get_attachment_image($thumbnail, 'large');  ?>
                        <?php if ($link_video): ?>
                            <a class="link-cover play-video" href="#" data-youtube="<?= esc_url($link_video) ?>" aria-label="<?= esc_attr($title) ?>">
                                <span class="play-icon">▶</span>
                            </a>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</section>