<?php

/**
 * Custom Product Gallery Template
 * 
 * Replaces default WooCommerce gallery with Swiper-based gallery
 * including main slider, thumbnail slider, zoom hover, lightbox popup, and video support
 */

defined('ABSPATH') || exit;

global $product;

$attachment_ids = $product->get_gallery_image_ids();
$post_thumbnail_id = $product->get_image_id();

// Get video URL from ACF field (supports YouTube, Vimeo, TikTok, or direct video URL)
$product_video = \HD_Helper::getField('product_video', $product->get_id());
$has_video = !empty($product_video);

// If no images, show placeholder
if (!$post_thumbnail_id) {
    echo wc_placeholder_img(apply_filters('single_product_large_thumbnail_size', 'woocommerce_single'));
    return;
}

// Build full images array (featured + gallery)
$all_image_ids = array_merge([$post_thumbnail_id], $attachment_ids);
$total_items = count($all_image_ids) + ($has_video ? 1 : 0);

// Helper function to detect video type
function get_video_type($video_url)
{
    if (preg_match('/(?:youtube\.com|youtu\.be)/', $video_url)) {
        return 'youtube';
    }
    if (preg_match('/vimeo\.com/', $video_url)) {
        return 'vimeo';
    }
    if (preg_match('/tiktok\.com/', $video_url)) {
        return 'tiktok';
    }
    return 'direct';
}

// Helper function to get TikTok video ID
function get_gallery_tiktok_video_id($url)
{
    // Format 1: https://www.tiktok.com/@username/video/1234567890
    if (preg_match('/tiktok\.com\/@[^\/]+\/video\/(\d+)/', $url, $matches)) {
        return $matches[1];
    }
    // Format 2: /video/ID anywhere in URL
    if (preg_match('/\/video\/(\d+)/', $url, $matches)) {
        return $matches[1];
    }
    return '';
}

// Helper function to get YouTube/Vimeo thumbnail
function get_video_thumbnail($video_url)
{
    // YouTube
    if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $video_url, $matches)) {
        return 'https://img.youtube.com/vi/' . $matches[1] . '/hqdefault.jpg';
    }
    // Vimeo - return placeholder, actual thumbnail requires API call
    if (preg_match('/vimeo\.com\/(\d+)/', $video_url, $matches)) {
        return '';
    }
    // TikTok - no direct thumbnail API, will use placeholder
    if (preg_match('/tiktok\.com/', $video_url)) {
        return '';
    }
    return '';
}

// Helper function to get embed URL
function get_video_embed_url($video_url)
{
    // YouTube
    if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $video_url, $matches)) {
        return 'https://www.youtube.com/embed/' . $matches[1] . '?autoplay=1&rel=0';
    }
    // Vimeo
    if (preg_match('/vimeo\.com\/(\d+)/', $video_url, $matches)) {
        return 'https://player.vimeo.com/video/' . $matches[1] . '?autoplay=1';
    }
    // TikTok - use player/v1 with autoplay
    $tiktok_id = get_gallery_tiktok_video_id($video_url);
    if ($tiktok_id) {
        return 'https://www.tiktok.com/player/v1/' . $tiktok_id . '?autoplay=1&controls=1';
    }
    // Direct video URL
    return $video_url;
}

// Helper function to get TikTok grid embed URL (no autoplay)
function get_tiktok_grid_url($video_url)
{
    $tiktok_id = get_gallery_tiktok_video_id($video_url);
    if ($tiktok_id) {
        return 'https://www.tiktok.com/player/v1/' . $tiktok_id . '?controls=1';
    }
    return '';
}

$video_type = $has_video ? get_video_type($product_video) : '';
$video_thumb = $has_video ? get_video_thumbnail($product_video) : '';
$video_embed = $has_video ? get_video_embed_url($product_video) : '';
$is_tiktok = $video_type === 'tiktok';
$tiktok_grid_url = $is_tiktok ? get_tiktok_grid_url($product_video) : '';
?>

<div class="swiper-product-gallery" <?= $has_video ? ' data-has-video="1"' : '' ?>>

    <!-- Main Images Slider -->
    <div class="swiper swiper-images">
        <div class="swiper-wrapper">
            <?php foreach ($all_image_ids as $index => $image_id):
                $image_src = wp_get_attachment_image_src($image_id, 'woocommerce_single');
                $image_full = wp_get_attachment_image_src($image_id, 'full');
                $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true) ?: $product->get_name();
                $image_caption = wp_get_attachment_caption($image_id);
                $first_class = $index === 0 ? ' swiper-images-first' : '';
            ?>
                <div class="swiper-slide<?= esc_attr($first_class) ?>">
                    <a href="<?= esc_url($image_full[0]) ?>"
                        class="image-popup"
                        data-src="<?= esc_url($image_full[0]) ?>"
                        data-caption="<?= esc_attr($image_caption) ?>"
                        data-type="image"
                        data-index="<?= esc_attr($index) ?>">
                        <div class="zoom-container">
                            <img src="<?= esc_url($image_src[0]) ?>"
                                alt="<?= esc_attr($image_alt) ?>"
                                width="<?= esc_attr($image_src[1]) ?>"
                                height="<?= esc_attr($image_src[2]) ?>"
                                loading="<?= $index === 0 ? 'eager' : 'lazy' ?>"
                                decoding="async">
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>

            <?php if ($has_video):
                $video_index = count($all_image_ids);
                // Use first image as video thumbnail fallback
                $fallback_thumb = wp_get_attachment_image_src($post_thumbnail_id, 'woocommerce_single');
            ?>
                <?php if ($is_tiktok): ?>
                    <!-- TikTok Video - uses player/v1 iframe with popup -->
                    <div class="swiper-slide swiper-slide-video swiper-slide-video-tiktok">
                        <div class="gallery-tiktok-wrapper"
                            data-video-type="tiktok"
                            data-video-embed="<?php echo esc_url($video_embed); ?>">
                            <div class="gallery-tiktok-overlay"></div>
                            <div class="video-wrapper video-wrapper--vertical">
                                <iframe src="<?php echo esc_url($tiktok_grid_url); ?>"
                                    frameborder="0"
                                    allow="encrypted-media"
                                    allowfullscreen
                                    loading="lazy"></iframe>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- YouTube/Vimeo/Direct Video - uses thumbnail with play button -->
                    <div class="swiper-slide swiper-slide-video">
                        <a href="<?= esc_url($video_embed) ?>"
                            class="video-popup"
                            data-src="<?= esc_url($video_embed) ?>"
                            data-type="video"
                            data-index="<?= esc_attr($video_index) ?>">
                            <div class="video-thumbnail">
                                <img src="<?= esc_url($video_thumb ?: $fallback_thumb[0]) ?>"
                                    alt="<?= esc_attr__('Video sản phẩm', TEXT_DOMAIN) ?>"
                                    loading="lazy"
                                    decoding="async">
                                <div class="video-play-button">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" aria-hidden="true" focusable="false">
                                        <path d="M73 39c-14.8-9.1-33.4-9.4-48.5-.9S0 62.6 0 80L0 432c0 17.4 9.4 33.4 24.5 41.9s33.7 8.1 48.5-.9L361 297c14.3-8.8 23-24.2 23-41s-8.7-32.2-23-41L73 39z" />
                                    </svg>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <?php if ($total_items > 1): ?>
            <!-- Navigation buttons -->
            <button class="swiper-button swiper-button-prev" type="button" aria-label="<?= esc_attr__('Ảnh trước', TEXT_DOMAIN) ?>">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" aria-hidden="true" focusable="false">
                    <path d="M9.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l160 160c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L109.3 288 480 288c17.7 0 32-14.3 32-32s-14.3-32-32-32l-370.7 0 105.4-105.4c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-160 160z" />
                </svg>
            </button>
            <button class="swiper-button swiper-button-next" type="button" aria-label="<?= esc_attr__('Ảnh tiếp theo', TEXT_DOMAIN) ?>">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" aria-hidden="true" focusable="false">
                    <path d="M502.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L402.7 224 32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l370.7 0-105.4 105.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z" />
                </svg>
            </button>
        <?php endif; ?>
    </div>

    <?php if ($total_items > 1): ?>
        <!-- Thumbnail Slider -->
        <div class="swiper swiper-thumbs">
            <div class="swiper-wrapper">
                <?php foreach ($all_image_ids as $index => $image_id):
                    $thumb_src = wp_get_attachment_image_src($image_id, 'woocommerce_gallery_thumbnail');
                    $image_full = wp_get_attachment_image_src($image_id, 'full');
                    $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true) ?: $product->get_name();
                    $first_class = $index === 0 ? ' swiper-thumbs-first' : '';
                ?>
                    <div class="swiper-slide<?= esc_attr($first_class) ?>">
                        <img src="<?= esc_url($thumb_src[0]) ?>"
                            alt="<?= esc_attr($image_alt) ?>"
                            width="<?= esc_attr($thumb_src[1]) ?>"
                            height="<?= esc_attr($thumb_src[2]) ?>"
                            data-large_image="<?= esc_url($image_full[0]) ?>"
                            loading="lazy"
                            decoding="async">
                    </div>
                <?php endforeach; ?>

                <?php if ($has_video):
                    // Use YouTube thumbnail or product image as fallback
                    $thumb_fallback = wp_get_attachment_image_src($post_thumbnail_id, 'woocommerce_gallery_thumbnail');
                ?>
                    <div class="swiper-slide swiper-slide-video-thumb<?= $is_tiktok ? ' swiper-slide-tiktok-thumb' : '' ?>">
                        <div class="video-thumb-wrapper">
                            <?php if ($is_tiktok): ?>
                                <!-- TikTok thumbnail with TikTok logo -->
                                <div class="tiktok-thumb-placeholder">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="tiktok-icon" aria-hidden="true">
                                        <path d="M448,209.91a210.06,210.06,0,0,1-122.77-39.25V349.38A162.55,162.55,0,1,1,185,188.31V278.2a74.62,74.62,0,1,0,52.23,71.18V0l88,0a121.18,121.18,0,0,0,1.86,22.17h0A122.18,122.18,0,0,0,381,102.39a121.43,121.43,0,0,0,67,20.14Z" />
                                    </svg>
                                </div>
                            <?php else: ?>
                                <img src="<?= esc_url($video_thumb ?: $thumb_fallback[0]) ?>"
                                    alt="<?= esc_attr__('Video sản phẩm', TEXT_DOMAIN) ?>"
                                    loading="lazy"
                                    decoding="async">
                            <?php endif; ?>
                            <span class="video-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" aria-hidden="true" focusable="false">
                                    <path d="M73 39c-14.8-9.1-33.4-9.4-48.5-.9S0 62.6 0 80L0 432c0 17.4 9.4 33.4 24.5 41.9s33.7 8.1 48.5-.9L361 297c14.3-8.8 23-24.2 23-41s-8.7-32.2-23-41L73 39z" />
                                </svg>
                            </span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Thumb Navigation buttons -->
            <button class="swiper-button swiper-button-prev" type="button" aria-label="<?= esc_attr__('Ảnh trước', TEXT_DOMAIN) ?>">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" aria-hidden="true" focusable="false">
                    <path d="M9.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l160 160c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L109.3 288 480 288c17.7 0 32-14.3 32-32s-14.3-32-32-32l-370.7 0 105.4-105.4c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-160 160z" />
                </svg>
            </button>
            <button class="swiper-button swiper-button-next" type="button" aria-label="<?= esc_attr__('Ảnh tiếp theo', TEXT_DOMAIN) ?>">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" aria-hidden="true" focusable="false">
                    <path d="M502.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L402.7 224 32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l370.7 0-105.4 105.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z" />
                </svg>
            </button>
        </div>
    <?php endif; ?>
</div>

<!-- Lightbox Popup -->
<div id="gallery-lightbox" class="gallery-lightbox" aria-hidden="true">
    <div class="lightbox-overlay"></div>
    <div class="lightbox-content">
        <div class="lightbox-header">
            <span class="lightbox-counter">1/<?= esc_html($total_images) ?></span>
            <div class="lightbox-actions">
                <button class="lightbox-zoom" type="button" aria-label="<?= esc_attr__('Phóng to', TEXT_DOMAIN) ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" aria-hidden="true" focusable="false">
                        <path d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM184 296V232H120c-13.3 0-24-10.7-24-24s10.7-24 24-24h64V120c0-13.3 10.7-24 24-24s24 10.7 24 24v64h64c13.3 0 24 10.7 24 24s-10.7 24-24 24H232v64c0 13.3-10.7 24-24 24s-24-10.7-24-24z" />
                    </svg>
                </button>
                <button class="lightbox-close" type="button" aria-label="<?= esc_attr__('Đóng', TEXT_DOMAIN) ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" aria-hidden="true" focusable="false">
                        <path d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z" />
                    </svg>
                </button>
            </div>
        </div>
        <div class="lightbox-body">
            <button class="lightbox-nav lightbox-prev" type="button" aria-label="<?= esc_attr__('Ảnh trước', TEXT_DOMAIN) ?>">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" aria-hidden="true" focusable="false">
                    <path d="M9.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l192 192c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L77.3 256 246.6 86.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-192 192z" />
                </svg>
            </button>
            <div class="lightbox-image-wrapper">
                <img src="" alt="" class="lightbox-image">
            </div>
            <button class="lightbox-nav lightbox-next" type="button" aria-label="<?= esc_attr__('Ảnh tiếp theo', TEXT_DOMAIN) ?>">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" aria-hidden="true" focusable="false">
                    <path d="M310.6 233.4c12.5 12.5 12.5 32.8 0 45.3l-192 192c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3L242.7 256 73.4 86.6c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0l192 192z" />
                </svg>
            </button>
        </div>
        <div class="lightbox-caption"></div>
    </div>
</div>