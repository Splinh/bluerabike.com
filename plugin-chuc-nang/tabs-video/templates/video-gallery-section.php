<?php

/**
 * Video Gallery Section Template
 * Displays video thumbnails below product gallery
 * 
 * @package Product_Video_Tabs
 */

if (!defined('ABSPATH')) {
    exit;
}

$total_youtube = is_array($youtube_videos) ? count($youtube_videos) : 0;
$total_tiktok = is_array($tiktok_videos) ? count($tiktok_videos) : 0;
$total_videos = $total_youtube + $total_tiktok;

if ($total_videos === 0) {
    return;
}
?>

<div class="pvt-gallery-section">
    <h4 class="pvt-gallery-section__title">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" width="18" height="18">
            <path d="M0 128C0 92.7 28.7 64 64 64H320c35.3 0 64 28.7 64 64V384c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V128zM559.1 99.8c10.4 5.6 16.9 16.4 16.9 28.2V384c0 11.8-6.5 22.6-16.9 28.2s-23 5-32.9-1.6l-96-64-32-21.3V186.7l32-21.3 96-64c9.8-6.5 22.4-7.2 32.9-1.6z" />
        </svg>
        <?php echo esc_html__('Video sản phẩm', 'product-video-tabs'); ?>
        <span class="pvt-gallery-section__count">(<?php echo esc_html($total_videos); ?>)</span>
    </h4>

    <div class="pvt-gallery-thumbs">
        <?php
        // YouTube Videos
        if (!empty($youtube_videos)) :
            foreach ($youtube_videos as $video) :
                $youtube_url = $video['youtube_url'] ?? '';
                if (empty($youtube_url)) continue;

                $embed_url = Product_Video_Tabs::get_youtube_embed_url($youtube_url);
                $thumbnail_url = Product_Video_Tabs::get_youtube_thumbnail($youtube_url);
                $video_id = Product_Video_Tabs::get_youtube_video_id($youtube_url);
        ?>
                <div class="pvt-gallery-thumb pvt-gallery-thumb--youtube">
                    <a href="<?php echo esc_url($embed_url); ?>"
                        class="pvt-video-thumb-link"
                        data-video-type="youtube"
                        data-video-embed="<?php echo esc_url($embed_url); ?>">
                        <img src="<?php echo esc_url($thumbnail_url); ?>"
                            alt="YouTube Video"
                            loading="lazy"
                            onerror="this.src='https://img.youtube.com/vi/<?php echo esc_attr($video_id); ?>/hqdefault.jpg'">
                        <span class="pvt-gallery-thumb__play">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" width="16" height="16" fill="#fff">
                                <path d="M73 39c-14.8-9.1-33.4-9.4-48.5-.9S0 62.6 0 80L0 432c0 17.4 9.4 33.4 24.5 41.9s33.7 8.1 48.5-.9L361 297c14.3-8.8 23-24.2 23-41s-8.7-32.2-23-41L73 39z" />
                            </svg>
                        </span>
                        <span class="pvt-gallery-thumb__badge pvt-gallery-thumb__badge--youtube">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" width="14" height="14" fill="#FF0000">
                                <path d="M549.655 124.083c-6.281-23.65-24.787-42.276-48.284-48.597C458.781 64 288 64 288 64S117.22 64 74.629 75.486c-23.497 6.322-42.003 24.947-48.284 48.597-11.412 42.867-11.412 132.305-11.412 132.305s0 89.438 11.412 132.305c6.281 23.65 24.787 41.5 48.284 47.821C117.22 448 288 448 288 448s170.78 0 213.371-11.486c23.497-6.321 42.003-24.171 48.284-47.821 11.412-42.867 11.412-132.305 11.412-132.305s0-89.438-11.412-132.305z" />
                            </svg>
                        </span>
                    </a>
                </div>
            <?php
            endforeach;
        endif;

        // TikTok Videos
        if (!empty($tiktok_videos)) :
            foreach ($tiktok_videos as $video) :
                $tiktok_url = $video['tiktok_url'] ?? '';
                if (empty($tiktok_url)) continue;

                $video_id = Product_Video_Tabs::get_tiktok_video_id($tiktok_url);
                if (empty($video_id)) continue;

                $player_url_popup = Product_Video_Tabs::get_tiktok_embed_url($tiktok_url, true);
            ?>
                <div class="pvt-gallery-thumb pvt-gallery-thumb--tiktok pvt-tiktok-player-wrapper"
                    data-video-type="tiktok"
                    data-video-embed="<?php echo esc_url($player_url_popup); ?>">
                    <div class="pvt-tiktok-click-overlay"></div>
                    <div class="pvt-gallery-thumb__tiktok-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="24" height="24" fill="#000">
                            <path d="M448,209.91a210.06,210.06,0,0,1-122.77-39.25V349.38A162.55,162.55,0,1,1,185,188.31V278.2a74.62,74.62,0,1,0,52.23,71.18V0l88,0a121.18,121.18,0,0,0,1.86,22.17h0A122.18,122.18,0,0,0,381,102.39a121.43,121.43,0,0,0,67,20.14Z" />
                        </svg>
                    </div>
                    <span class="pvt-gallery-thumb__play">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" width="16" height="16" fill="#fff">
                            <path d="M73 39c-14.8-9.1-33.4-9.4-48.5-.9S0 62.6 0 80L0 432c0 17.4 9.4 33.4 24.5 41.9s33.7 8.1 48.5-.9L361 297c14.3-8.8 23-24.2 23-41s-8.7-32.2-23-41L73 39z" />
                        </svg>
                    </span>
                </div>
        <?php
            endforeach;
        endif;
        ?>
    </div>
</div>