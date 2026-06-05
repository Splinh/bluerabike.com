<?php

/**
 * Custom Single Product Tabs (Tabs + Review toggle form + Specs popup)
 * Copy to: yourtheme/woocommerce/single-product/tabs/tabs.php
 */

if (!defined('ABSPATH'))
    exit;

global $product;

// Woo data
$description = $product->get_description();
$rating_avg = floatval($product->get_average_rating());
$review_count = intval($product->get_review_count());
$rating_counts = (array) $product->get_rating_counts();
$reviews = get_comments([
    'post_id' => $product->get_id(),
    'status' => 'approve',
    'type' => 'review',
    'orderby' => 'comment_date_gmt',
    'order' => 'DESC',
]);

// ACF Thông số kỹ thuật - Ảnh hoặc repeater
$specs_image_id = function_exists('get_field') ? get_field('specs_image', $product->get_id()) : null;
$tskt_rows = function_exists('get_field') ? (get_field('tskt_rows', $product->get_id()) ?: []) : [];

// ACF Video - Lấy danh sách video YouTube và TikTok
$youtube_videos = function_exists('get_field') ? get_field('youtube_videos', $product->get_id()) : [];
$tiktok_videos = function_exists('get_field') ? get_field('tiktok_videos', $product->get_id()) : [];
$has_videos = !empty($youtube_videos) || !empty($tiktok_videos);

// Helper function to extract YouTube video ID
function get_youtube_video_id($url)
{
    // Support: youtube.com/watch?v=, youtu.be/, youtube.com/embed/, youtube.com/v/, youtube.com/shorts/
    if (preg_match('/(?:youtube\.com\/(?:watch\?(?:.*&)?v=|embed\/|v\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $matches)) {
        return $matches[1];
    }
    return '';
}

// Helper function to get YouTube embed URL
function get_youtube_embed_url($url)
{
    $video_id = get_youtube_video_id($url);
    if ($video_id) {
        return 'https://www.youtube.com/embed/' . $video_id . '?autoplay=1&rel=0';
    }
    return $url;
}

// Helper function to get YouTube thumbnail (use hqdefault as it always exists)
function get_youtube_thumbnail($url)
{
    $video_id = get_youtube_video_id($url);
    if ($video_id) {
        // Use hqdefault (always exists) instead of maxresdefault (may not exist)
        return 'https://img.youtube.com/vi/' . $video_id . '/hqdefault.jpg';
    }
    return '';
}

// Helper function to extract TikTok video ID - supports multiple URL formats
function get_tiktok_video_id($url)
{
    // Format 1: https://www.tiktok.com/@username/video/1234567890
    if (preg_match('/tiktok\.com\/@[^\/]+\/video\/(\d+)/', $url, $matches)) {
        return $matches[1];
    }
    // Format 2: https://vm.tiktok.com/CODE/ or https://vt.tiktok.com/CODE/
    // These are short URLs that redirect, so we return empty and use original URL
    if (preg_match('/(?:vm|vt)\.tiktok\.com\/([a-zA-Z0-9]+)/', $url, $matches)) {
        return ''; // Short URLs need to be expanded
    }
    // Format 3: https://www.tiktok.com/t/CODE/
    if (preg_match('/tiktok\.com\/t\/([a-zA-Z0-9]+)/', $url, $matches)) {
        return ''; // Short URLs
    }
    // Format 4: Mobile share - https://www.tiktok.com/@username/video/1234567890?...
    if (preg_match('/\/video\/(\d+)/', $url, $matches)) {
        return $matches[1];
    }
    return '';
}

// Helper function to get TikTok embed URL
function get_tiktok_embed_url($url)
{
    $video_id = get_tiktok_video_id($url);
    if ($video_id) {
        return 'https://www.tiktok.com/embed/v2/' . $video_id;
    }
    // If can't extract ID, try to use oEmbed approach or return original
    return $url;
}

// SVG star helper
function wc_svg_star($filled = false)
{
    $cls = $filled ? 'star filled' : 'star';
    return '<svg class="' . esc_attr($cls) . '" viewBox="0 0 20 20" aria-hidden="true"><polygon points="9.9,1.1 12.3,6.8 18.4,7.3 13.5,11.3 15,17.3 9.9,14.1 4.8,17.3 6.3,11.3 1.4,7.3 7.5,6.8"></polygon></svg>';
}
$has_description = !empty(trim($description));
$has_specs = $specs_image_id || !empty($tskt_rows);
$default_tab = $has_description ? 'info' : ($has_specs ? 'specs' : 'info');
?>
<div class="wc-custom-tabs">
    <!-- Tabs header: Mô tả → TSKT → Video → Đánh giá -->
    <div class="wc-tabs-nav" role="tablist">
        <?php if ($has_description) : ?>
            <button class="tab-btn<?php echo $default_tab === 'info' ? ' active' : ''; ?>" aria-controls="tab-info" aria-selected="<?php echo $default_tab === 'info' ? 'true' : 'false'; ?>"
                id="btn-info"><?php echo \HD_Helper::pll_text('Thông tin sản phẩm', 'Product information'); ?></button>
        <?php endif; ?>
        <button class="tab-btn<?php echo $default_tab === 'specs' ? ' active' : ''; ?>" aria-controls="tab-specs" aria-selected="<?php echo $default_tab === 'specs' ? 'true' : 'false'; ?>"
            id="btn-specs"><?php echo \HD_Helper::pll_text('Thông số kỹ thuật', 'Specifications'); ?></button>
        <?php if ($has_videos) : ?>
            <button class="tab-btn" aria-controls="tab-video" aria-selected="false"
                id="btn-video"><?php echo \HD_Helper::pll_text('Video', 'Video'); ?></button>
        <?php endif; ?>
        <button class="tab-btn" aria-controls="tab-reviews" aria-selected="false"
            id="btn-reviews"><?php echo \HD_Helper::pll_text('Đánh giá sản phẩm', 'Product reviews'); ?></button>
    </div>

    <!-- Tab: Thông tin sản phẩm -->
    <?php if ($has_description) : ?>
    <div class="wc-tab-panel" id="tab-info" role="tabpanel" aria-labelledby="btn-info"<?php echo $default_tab !== 'info' ? ' hidden' : ''; ?>>
        <div class="wc-content description-content">
            <div class="inner"><?php echo wpautop($description); ?></div>
        </div>
        <button class="wc-toggle desc-toggle" type="button">Xem thêm</button>
    </div>
    <?php endif; ?>

    <?php if ($has_videos) : ?>
        <!-- Tab: Video -->
        <div class="wc-tab-panel" id="tab-video" role="tabpanel" aria-labelledby="btn-video" hidden>
            <div class="wc-content video-content">

                <?php if (!empty($youtube_videos)) : ?>
                    <!-- YouTube Videos Section -->
                    <div class="video-section video-section--youtube">
                        <h3 class="video-section__title">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" width="24" height="24" fill="#FF0000">
                                <path d="M549.655 124.083c-6.281-23.65-24.787-42.276-48.284-48.597C458.781 64 288 64 288 64S117.22 64 74.629 75.486c-23.497 6.322-42.003 24.947-48.284 48.597-11.412 42.867-11.412 132.305-11.412 132.305s0 89.438 11.412 132.305c6.281 23.65 24.787 41.5 48.284 47.821C117.22 448 288 448 288 448s170.78 0 213.371-11.486c23.497-6.321 42.003-24.171 48.284-47.821 11.412-42.867 11.412-132.305 11.412-132.305s0-89.438-11.412-132.305zm-317.51 213.508V175.185l142.739 81.205-142.739 81.201z" />
                            </svg>
                            YouTube
                        </h3>
                        <div class="video-grid video-grid--youtube">
                            <?php foreach ($youtube_videos as $video) :
                                $youtube_url = $video['youtube_url'] ?? '';
                                if (empty($youtube_url)) continue;
                                $embed_url = get_youtube_embed_url($youtube_url);
                                $thumbnail_url = get_youtube_thumbnail($youtube_url);
                            ?>
                                <div class="video-item video-item--youtube">
                                    <a href="<?php echo esc_url($embed_url); ?>" class="video-thumb-link" data-video-type="youtube" data-video-embed="<?php echo esc_url($embed_url); ?>">
                                        <div class="video-thumb video-thumb--horizontal">
                                            <img src="<?php echo esc_url($thumbnail_url); ?>" alt="YouTube Video" loading="lazy" onerror="this.src='https://img.youtube.com/vi/<?php echo esc_attr(get_youtube_video_id($youtube_url)); ?>/hqdefault.jpg'">
                                            <div class="video-play-btn video-play-btn--youtube">
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
                    <!-- TikTok Videos Section - Using player/v1 iframe -->
                    <div class="video-section video-section--tiktok">
                        <h3 class="video-section__title">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="24" height="24">
                                <path d="M448,209.91a210.06,210.06,0,0,1-122.77-39.25V349.38A162.55,162.55,0,1,1,185,188.31V278.2a74.62,74.62,0,1,0,52.23,71.18V0l88,0a121.18,121.18,0,0,0,1.86,22.17h0A122.18,122.18,0,0,0,381,102.39a121.43,121.43,0,0,0,67,20.14Z" />
                            </svg>
                            TikTok
                        </h3>
                        <div class="video-grid video-grid--tiktok">
                            <?php foreach ($tiktok_videos as $video) :
                                $tiktok_url = $video['tiktok_url'] ?? '';
                                if (empty($tiktok_url)) continue;
                                $video_id = get_tiktok_video_id($tiktok_url);
                                if (empty($video_id)) continue;

                                // Player URL - no autoplay for grid view, with autoplay for popup
                                $player_url_grid = 'https://www.tiktok.com/player/v1/' . $video_id . '?controls=1';
                                $player_url_popup = 'https://www.tiktok.com/player/v1/' . $video_id . '?autoplay=1&controls=1';
                            ?>
                                <div class="video-item video-item--tiktok tiktok-player-wrapper"
                                    data-video-type="tiktok"
                                    data-video-embed="<?php echo esc_url($player_url_popup); ?>">
                                    <!-- Click overlay to open popup -->
                                    <div class="tiktok-click-overlay"></div>
                                    <div class="video-wrapper video-wrapper--vertical">
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
        </div>
    <?php endif; ?>

    <!-- Video Popup Modal -->
    <div id="video-popup-modal" class="video-popup-modal">
        <div class="video-popup-overlay"></div>
        <div class="video-popup-content">
            <button class="video-popup-close" type="button" aria-label="Đóng">&times;</button>
            <div class="video-popup-iframe-wrapper"></div>
        </div>
    </div>

    <!-- Tab: Đánh giá sản phẩm -->
    <div class="wc-tab-panel" id="tab-reviews" role="tabpanel" aria-labelledby="btn-reviews" hidden>
        <div class="wc-block wc-reviews">
            <!-- Tổng quan -->
            <div class="wc-review-summary">
                <div class="wc-rating-average">
                    <div class="score-line"><span class="score"><?php echo number_format($rating_avg, 1); ?></span><span
                            class="total">/5</span></div>
                    <div class="stars">
                        <?php $r = round($rating_avg);
                        for ($i = 1; $i <= 5; $i++) {
                            echo wc_svg_star($i <= $r);
                        } ?>
                    </div>
                    <p class="count"><?php echo $review_count; ?> lượt đánh giá</p>
                </div>
                <div class="wc-rating-bars">
                    <?php for ($i = 5; $i >= 1; $i--):
                        $c = $rating_counts[$i] ?? 0;
                        $p = $review_count ? round($c / $review_count * 100) : 0;
                    ?>
                        <div class="bar">
                            <span class="star-label"><?php echo $i; ?> <?php echo wc_svg_star(true); ?></span>
                            <div class="progress">
                                <div style="width:<?php echo $p; ?>%"></div>
                            </div>
                            <span class="count"><?php echo $c; ?> đánh giá</span>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Form đánh giá (hiện mặc định) -->
            <div class="wc-review-form" id="review_form">
                <?php
                if (comments_open($product->get_id())) {
                    $commenter = wp_get_current_commenter();
                    $req = get_option('require_name_email');
                    $aria_req = $req ? " aria-required='true' required" : '';

                    $fields = [];
                    if (!is_user_logged_in()) {
                        $fields['author'] = '<p class="comment-form-author"><label for="author">Tên của bạn</label><input id="author" name="author" type="text" value="' . esc_attr($commenter['comment_author'] ?? '') . '" size="30"' . $aria_req . ' /></p>';
                        $fields['email'] = '<p class="comment-form-email"><label for="email">Email</label><input id="email" name="email" type="email" value="' . esc_attr($commenter['comment_author_email'] ?? '') . '" size="30"' . $aria_req . ' /></p>';
                    }

                    ob_start();
                ?>
                    <div class="form-row rating-select">
                        <label for="rating">Đánh giá của bạn</label>
                        <div class="rating-stars" data-target="#rating">
                            <?php for ($i = 1; $i <= 5; $i++) { ?>
                                <button type="button" class="rate-btn"
                                    data-value="<?php echo $i; ?>"><?php echo wc_svg_star(); ?></button>
                            <?php } ?>
                        </div>
                        <select name="rating" id="rating" required style="display:none">
                            <option value="">Chọn…</option>
                            <?php for ($i = 5; $i >= 1; $i--) {
                                echo "<option value='$i'>$i</option>";
                            } ?>
                        </select>
                    </div>
                    <p class="comment-form-comment"><label for="comment">Nội dung đánh giá</label><textarea id="comment"
                            name="comment" cols="45" rows="5" required></textarea></p>
                <?php
                    $comment_field = ob_get_clean();

                    comment_form([
                        'title_reply' => 'Viết đánh giá',
                        'title_reply_before' => '<h3 class="reply-title">',
                        'title_reply_after' => '</h3>',
                        'comment_notes_before' => '',
                        'comment_notes_after' => '',
                        'label_submit' => 'Gửi đánh giá',
                        'class_submit' => 'submit wc-submit-review',
                        'logged_in_as' => '',
                        'comment_field' => $comment_field,
                        'fields' => apply_filters('comment_form_default_fields', $fields),
                    ], $product->get_id());
                }
                ?>
            </div>

            <!-- Danh sách đánh giá -->
            <div class="wc-review-list">
                <?php if ($reviews):
                    foreach ($reviews as $comment):
                        $rating = intval(get_comment_meta($comment->comment_ID, 'rating', true));
                        $is_owner = function_exists('wc_review_is_from_verified_owner') ? wc_review_is_from_verified_owner($comment->comment_ID) : false;
                        $initial = strtoupper(mb_substr($comment->comment_author, 0, 1, 'UTF-8'));
                ?>
                        <div class="review-item">
                            <div class="review-header">
                                <div class="avatar"><?php echo esc_html($initial); ?></div>
                                <div class="meta">
                                    <div class="line-1">
                                        <strong><?php echo esc_html($comment->comment_author); ?></strong><?php if ($is_owner): ?><span
                                                class="badge-owned">Đã mua hàng</span><?php endif; ?>
                                    </div>
                                    <div class="stars"><?php for ($i = 1; $i <= 5; $i++) {
                                                            echo wc_svg_star($i <= $rating);
                                                        } ?></div>
                                    <div class="date"><?php echo esc_html(get_comment_date('', $comment)); ?></div>
                                </div>
                            </div>
                            <div class="review-body"><?php echo wpautop(wp_kses_post($comment->comment_content)); ?></div>
                        </div>
                    <?php endforeach;
                else: ?>
                    <p>Chưa có đánh giá nào.</p><?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Tab: Thông số kỹ thuật -->
<div class="wc-tab-panel" id="tab-specs" role="tabpanel" aria-labelledby="btn-specs"<?php echo $default_tab !== 'specs' ? ' hidden' : ''; ?>>
    <div class="wc-content spec-content">
        <div class="inner">
            <?php if ($specs_image_id):
                // Ưu tiên 1: ACF field specs_image
                $specs_image_url = wp_get_attachment_image_url($specs_image_id, 'full');
                $specs_image_alt = get_post_meta($specs_image_id, '_wp_attachment_image_alt', true) ?: 'Thông số kỹ thuật';
            ?>
                <div class="spec-image-wrapper">
                    <img src="<?php echo esc_url($specs_image_url); ?>" alt="<?php echo esc_attr($specs_image_alt); ?>"
                        loading="lazy" decoding="async"
                        style="max-width: 100%; height: auto; display: block; margin: 0 auto;">
                </div>
            <?php elseif (!empty($tskt_rows)):
                // Ưu tiên 3: ACF repeater tskt_rows → bảng động
                $spec_img_url = wp_get_attachment_image_url($product->get_image_id(), 'large');
                $company_name    = get_theme_mod('tskt_company_name', 'Công Ty TNHH Xe Điện Bluera Việt Nhật');
                $address         = get_theme_mod('tskt_company_address', '466 Nguyễn Duy Trinh, P. Bình Trưng, TP. Hồ Chí Minh');
                $phone           = get_theme_mod('tskt_company_phone', '0933.505.222');
                $website         = get_theme_mod('tskt_company_website', 'https://bluerabike.com');
                $website_display = preg_replace('#^https?://#', '', rtrim($website, '/'));
                $custom_logo_id  = get_theme_mod('custom_logo');
                $custom_logo_url = $custom_logo_id ? wp_get_attachment_image_url($custom_logo_id, 'medium') : '';
            ?>
                <div class="tskt-wrapper" id="tskt-spec-block">
                    <div class="tskt-header">
                        <span class="tskt-header__dot"></span>
                        <h2 class="tskt-header__title">THÔNG SỐ KỸ THUẬT</h2>
                        <span class="tskt-header__dot"></span>
                    </div>
                    <div class="tskt-body">
                        <div class="tskt-body__table-wrap tskt-body__table-wrap--full">
                            <table class="tskt-table">
                                <tbody>
                                    <?php foreach ($tskt_rows as $row) :
                                        $label = isset($row['tskt_label']) ? $row['tskt_label'] : '';
                                        $value = isset($row['tskt_value']) ? $row['tskt_value'] : '';
                                        if (!$label && !$value) continue;
                                    ?>
                                    <tr>
                                        <th><?php echo esc_html($label); ?></th>
                                        <td><?php echo wp_kses_post($value); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php
                    // Icon bảo hành
                    $badges = function_exists('get_field') ? (get_field('tskt_badges', $product->get_id()) ?: []) : [];
                    if (!empty($badges)) : ?>
                    <div class="tskt-badges">
                        <?php foreach ($badges as $badge) :
                            $burl = is_array($badge) ? ($badge['url'] ?? '') : $badge;
                            if (!$burl) continue;
                        ?>
                            <div class="tskt-badges__item">
                                <img src="<?php echo esc_url($burl); ?>" alt="badge" loading="lazy" />
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <div class="tskt-footer">
                        <div class="tskt-footer__divider"></div>
                        <div class="tskt-footer__inner">
                            <?php if ($custom_logo_url) : ?>
                            <div class="tskt-footer__logo">
                                <img src="<?php echo esc_url($custom_logo_url); ?>"
                                     alt="<?php echo esc_attr($company_name); ?>" />
                            </div>
                            <?php endif; ?>
                            <div class="tskt-footer__company">
                                <p class="tskt-footer__company-name"><?php echo esc_html(mb_strtoupper($company_name)); ?></p>
                                <p class="tskt-footer__company-address"><em>Địa Chỉ: <?php echo esc_html($address); ?></em></p>
                            </div>
                            <div class="tskt-footer__contact">
                                <p class="tskt-footer__phone">ĐT: <strong><?php echo esc_html($phone); ?></strong></p>
                                <p class="tskt-footer__website"><?php echo esc_html($website_display); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <p><?php echo \HD_Helper::pll_text('Chưa có thông số kỹ thuật.', 'No specifications yet.'); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        /* Tab switch */
        const tabs = {
            info: document.getElementById('tab-info'),
            video: document.getElementById('tab-video'),
            rev: document.getElementById('tab-reviews'),
            specs: document.getElementById('tab-specs')
        };
        const btns = {
            info: document.getElementById('btn-info'),
            video: document.getElementById('btn-video'),
            rev: document.getElementById('btn-reviews'),
            specs: document.getElementById('btn-specs')
        };

        function act(t) {
            for (let k in tabs) {
                if (tabs[k]) tabs[k].hidden = (k !== t);
                if (btns[k]) btns[k].classList.toggle('active', k === t);
            }
        }
        btns.info && btns.info.addEventListener('click', () => act('info'));
        btns.video && btns.video.addEventListener('click', () => act('video'));
        btns.rev && btns.rev.addEventListener('click', () => act('rev'));
        btns.specs && btns.specs.addEventListener('click', () => act('specs'));

        /* Toggle mô tả */
        const desc = document.querySelector('.description-content .inner');
        const descBtn = document.querySelector('.desc-toggle');
        if (desc && descBtn && desc.scrollHeight > 305) {
            desc.classList.add('collapsed');
            descBtn.onclick = () => {
                desc.classList.toggle('collapsed');
                descBtn.textContent = desc.classList.contains('collapsed') ? 'Xem thêm' : 'Rút gọn';
            };
        } else if (descBtn) descBtn.style.display = 'none';

        /* Form review hiện mặc định - không cần toggle */

        /* Rating stars sync */
        document.querySelectorAll('.rating-stars').forEach(cont => {
            const sel = document.querySelector(cont.dataset.target);
            const btns = cont.querySelectorAll('.rate-btn');

            function paint(v) {
                btns.forEach((b, i) => {
                    const s = b.querySelector('svg');
                    s && s.classList.toggle('filled', i < v);
                });
            }
            btns.forEach(b => b.addEventListener('click', () => {
                const v = parseInt(b.dataset.value) || 0;
                sel.value = v;
                paint(v);
            }));
        });

        /* Video Popup */
        const videoModal = document.getElementById('video-popup-modal');
        if (videoModal) {
            const overlay = videoModal.querySelector('.video-popup-overlay');
            const closeBtn = videoModal.querySelector('.video-popup-close');
            const iframeWrapper = videoModal.querySelector('.video-popup-iframe-wrapper');

            // Open video popup - for YouTube thumbnails
            document.querySelectorAll('.video-thumb-link').forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    openVideoPopup(link.dataset.videoEmbed, link.dataset.videoType);
                });
            });

            // Open video popup - for TikTok player iframes (via overlay)
            document.querySelectorAll('.tiktok-click-overlay').forEach(tiktokOverlay => {
                tiktokOverlay.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    const wrapper = tiktokOverlay.closest('.tiktok-player-wrapper');
                    if (wrapper) {
                        openVideoPopup(wrapper.dataset.videoEmbed, wrapper.dataset.videoType);
                    }
                });
            });

            // Function to open popup
            function openVideoPopup(embedUrl, videoType) {
                // Clear previous iframe
                iframeWrapper.innerHTML = '';
                iframeWrapper.className = 'video-popup-iframe-wrapper';

                // Create iframe
                const iframe = document.createElement('iframe');
                iframe.src = embedUrl;
                iframe.setAttribute('frameborder', '0');
                iframe.setAttribute('allowfullscreen', 'true');
                iframe.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen');

                // Set wrapper class based on video type
                if (videoType === 'youtube') {
                    iframeWrapper.classList.add('is-youtube');
                } else if (videoType === 'tiktok') {
                    iframeWrapper.classList.add('is-tiktok');
                }

                iframeWrapper.appendChild(iframe);
                videoModal.classList.add('is-active');
                document.body.style.overflow = 'hidden';
            }

            // Close video popup
            const closeVideoPopup = () => {
                videoModal.classList.remove('is-active');
                document.body.style.overflow = '';
                // Clear iframe to stop video
                iframeWrapper.innerHTML = '';
            };

            overlay && overlay.addEventListener('click', closeVideoPopup);
            closeBtn && closeBtn.addEventListener('click', closeVideoPopup);

            // Close on Escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && videoModal.classList.contains('is-active')) {
                    closeVideoPopup();
                }
            });
        }
    });
</script>