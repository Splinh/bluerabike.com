<?php

/**
 * Video Data Container Template
 * Outputs video data as JSON for JavaScript injection into gallery
 * 
 * @package Product_Video_Tabs
 */

if (!defined('ABSPATH')) {
    exit;
}

$videos = [];

// YouTube Videos
if (!empty($youtube_videos)) {
    foreach ($youtube_videos as $video) {
        $youtube_url = $video['youtube_url'] ?? '';
        if (empty($youtube_url)) continue;

        $videos[] = [
            'type' => 'youtube',
            'url' => $youtube_url,
            'embed' => Product_Video_Tabs::get_youtube_embed_url($youtube_url),
            'thumbnail' => Product_Video_Tabs::get_youtube_thumbnail($youtube_url),
        ];
    }
}

// TikTok Videos
if (!empty($tiktok_videos)) {
    foreach ($tiktok_videos as $video) {
        $tiktok_url = $video['tiktok_url'] ?? '';
        if (empty($tiktok_url)) continue;

        $video_id = Product_Video_Tabs::get_tiktok_video_id($tiktok_url);
        if (empty($video_id)) continue;

        $videos[] = [
            'type' => 'tiktok',
            'url' => $tiktok_url,
            'embed' => Product_Video_Tabs::get_tiktok_embed_url($tiktok_url, true),
        ];
    }
}

if (empty($videos)) {
    return;
}
?>

<!-- Hidden container with video data for JS injection -->
<div id="pvt-video-data"
    data-videos="<?php echo esc_attr(json_encode($videos)); ?>"
    style="display: none !important;"></div>