<?php

/**
 * Video Tab Content Template
 * 
 * @package Product_Video_Tabs
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="pvt-video-content">

    <?php if (!empty($youtube_videos)) : ?>
        <!-- YouTube Videos Section -->
        <div class="pvt-video-section pvt-video-section--youtube">
            <h3 class="pvt-video-section__title">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" width="24" height="24" fill="#FF0000">
                    <path d="M549.655 124.083c-6.281-23.65-24.787-42.276-48.284-48.597C458.781 64 288 64 288 64S117.22 64 74.629 75.486c-23.497 6.322-42.003 24.947-48.284 48.597-11.412 42.867-11.412 132.305-11.412 132.305s0 89.438 11.412 132.305c6.281 23.65 24.787 41.5 48.284 47.821C117.22 448 288 448 288 448s170.78 0 213.371-11.486c23.497-6.321 42.003-24.171 48.284-47.821 11.412-42.867 11.412-132.305 11.412-132.305s0-89.438-11.412-132.305zm-317.51 213.508V175.185l142.739 81.205-142.739 81.201z" />
                </svg>
                YouTube
            </h3>
            <div class="pvt-video-grid pvt-video-grid--youtube">
                <?php
                foreach ($youtube_videos as $video) :
                    $youtube_url = $video['youtube_url'] ?? '';
                    if (empty($youtube_url)) continue;

                    $embed_url = Product_Video_Tabs::get_youtube_embed_url($youtube_url);
                    $thumbnail_url = Product_Video_Tabs::get_youtube_thumbnail($youtube_url);
                    $video_id = Product_Video_Tabs::get_youtube_video_id($youtube_url);
                ?>
                    <div class="pvt-video-item pvt-video-item--youtube">
                        <a href="<?php echo esc_url($embed_url); ?>"
                            class="pvt-video-thumb-link"
                            data-video-type="youtube"
                            data-video-embed="<?php echo esc_url($embed_url); ?>">
                            <div class="pvt-video-thumb pvt-video-thumb--horizontal">
                                <img src="<?php echo esc_url($thumbnail_url); ?>"
                                    alt="YouTube Video"
                                    loading="lazy"
                                    onerror="this.src='https://img.youtube.com/vi/<?php echo esc_attr($video_id); ?>/hqdefault.jpg'">
                                <div class="pvt-video-play-btn pvt-video-play-btn--youtube">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 68 48" width="68" height="48">
                                        <path class="ytp-large-play-button-bg" d="M66.52,7.74c-0.78-2.93-2.49-5.41-5.42-6.19C55.79,.13,34,0,34,0S12.21,.13,6.9,1.55 C3.97,2.33,2.27,4.81,1.48,7.74C0.06,13.05,0,24,0,24s0.06,10.95,1.48,16.26c0.78,2.93,2.49,5.41,5.42,6.19 C12.21,47.87,34,48,34,48s21.79-0.13,27.1-1.55c2.93-0.78,4.64-3.26,5.42-6.19C67.94,34.95,68,24,68,24S67.94,13.05,66.52,7.74z" fill="#FF0000" />
                                        <path d="M 45,24 27,14 27,34" fill="#fff" />
                                    </svg>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($tiktok_videos)) : ?>
        <!-- TikTok Videos Section -->
        <div class="pvt-video-section pvt-video-section--tiktok">
            <h3 class="pvt-video-section__title">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="24" height="24">
                    <path d="M448,209.91a210.06,210.06,0,0,1-122.77-39.25V349.38A162.55,162.55,0,1,1,185,188.31V278.2a74.62,74.62,0,1,0,52.23,71.18V0l88,0a121.18,121.18,0,0,0,1.86,22.17h0A122.18,122.18,0,0,0,381,102.39a121.43,121.43,0,0,0,67,20.14Z" />
                </svg>
                TikTok
            </h3>
            <div class="pvt-video-grid pvt-video-grid--tiktok">
                <?php
                foreach ($tiktok_videos as $video) :
                    $tiktok_url = $video['tiktok_url'] ?? '';
                    if (empty($tiktok_url)) continue;

                    $video_id = Product_Video_Tabs::get_tiktok_video_id($tiktok_url);
                    if (empty($video_id)) continue;

                    $player_url_grid = Product_Video_Tabs::get_tiktok_embed_url($tiktok_url, false);
                    $player_url_popup = Product_Video_Tabs::get_tiktok_embed_url($tiktok_url, true);
                ?>
                    <div class="pvt-video-item pvt-video-item--tiktok pvt-tiktok-player-wrapper"
                        data-video-type="tiktok"
                        data-video-embed="<?php echo esc_url($player_url_popup); ?>">
                        <!-- Click overlay to open popup -->
                        <div class="pvt-tiktok-click-overlay"></div>
                        <div class="pvt-video-wrapper pvt-video-wrapper--vertical">
                            <iframe src="<?php echo esc_url($player_url_grid); ?>"
                                frameborder="0"
                                allow="encrypted-media"
                                allowfullscreen
                                loading="lazy"></iframe>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</div>