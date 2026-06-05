<?php
/**
 * Template for displaying single local_store (DevVN Local Store Pro)
 * Premium store detail page — Swiper gallery + lightbox, contact card, map
 *
 * Design tokens: Montserrat, #11283c (text), #1e78c2 (raised surface), WCAG 2.2 AA
 * @author SPL
 */

\defined('ABSPATH') || die;

get_header();

if (have_posts()) {
    the_post();
}

$post_id = get_the_ID();

// ── Meta data ────────────────────────────────────────────
$address  = get_post_meta($post_id, 'localstore_address', true);
$phone    = get_post_meta($post_id, 'localstore_phone', true);
$hotline  = get_post_meta($post_id, 'localstore_hotline', true);
$email    = get_post_meta($post_id, 'localstore_email', true);
$open     = get_post_meta($post_id, 'localstore_open', true);
$website  = get_post_meta($post_id, 'localstore_link_to', true);
$lat      = get_post_meta($post_id, 'localstore_maps_lat', true);
$lng      = get_post_meta($post_id, 'localstore_maps_lng', true);

// ── Taxonomy ─────────────────────────────────────────────
$types      = get_the_terms($post_id, 'store_type');
$states     = get_the_terms($post_id, 'local_store_state');
$type_slug  = ($types && !is_wp_error($types)) ? $types[0]->slug : '';
$type_name  = ($types && !is_wp_error($types)) ? $types[0]->name : '';
$state_name = ($states && !is_wp_error($states)) ? $states[0]->name : '';

// ── Map URL ──────────────────────────────────────────────
$valid_coords = ($lat && $lng && (float)$lat >= 8 && (float)$lat <= 24 && (float)$lng >= 100 && (float)$lng <= 112);
$dir_url = '';
$embed_url = '';
if ($valid_coords) {
    $dir_url   = "https://www.google.com/maps/dir/?api=1&destination={$lat}%2C{$lng}";
    $embed_url = "https://www.google.com/maps?q={$lat},{$lng}&z=16&output=embed";
} elseif ($address) {
    $dir_url   = 'https://www.google.com/maps/dir/?api=1&destination=' . urlencode($address);
    $embed_url = 'https://www.google.com/maps?q=' . urlencode($address) . '&z=16&output=embed';
}

// ── Gallery images ───────────────────────────────────────
$gallery_ids = [];
// 1) ACF Gallery field (ưu tiên)
if (function_exists('get_field')) {
    $acf_gallery = get_field('store_gallery', $post_id);
    if (!empty($acf_gallery)) {
        $gallery_ids = (array) $acf_gallery;
    }
}

// 2) Fallback: featured image + attached images
if (empty($gallery_ids)) {
    $thumb_id = get_post_thumbnail_id($post_id);
    $attached = get_posts([
        'post_parent'    => $post_id,
        'post_type'      => 'attachment',
        'post_mime_type' => 'image',
        'posts_per_page' => 20,
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
        'fields'         => 'ids',
    ]);
    if ($thumb_id) $gallery_ids[] = $thumb_id;
    if ($attached) $gallery_ids = array_unique(array_merge($gallery_ids, $attached));
}

// ── Store listing page URL ───────────────────────────────
$store_page = get_page_by_path('he-thong-cua-hang');
if (!$store_page) $store_page = get_page_by_path('he-thong-dai-ly');
$store_page_url = $store_page ? get_permalink($store_page->ID) : home_url('/he-thong-cua-hang/');

// Enqueue section CSS
\HD_Helper::enqueueSectionStyle('section-store-detail', ['index-css']);
?>

<section class="sd" id="store-detail" aria-label="Chi tiết cửa hàng">
    <div class="container">

        <!-- ═══ Breadcrumb ═══ -->
        <nav class="sd__breadcrumb" aria-label="Breadcrumb">
            <ol>
                <li><a href="<?php echo esc_url(home_url('/')); ?>">Trang chủ</a></li>
                <li><a href="<?php echo esc_url($store_page_url); ?>">Hệ thống cửa hàng</a></li>
                <li aria-current="page"><?php the_title(); ?></li>
            </ol>
        </nav>

        <!-- ═══ Hero ═══ -->
        <header class="sd__hero">
            <div class="sd__hero-meta">
                <?php if ($type_name): ?>
                    <span class="sd__badge sd__badge--<?php echo esc_attr($type_slug); ?>">
                        <?php echo esc_html($type_name); ?>
                    </span>
                <?php endif; ?>
                <?php if ($state_name): ?>
                    <span class="sd__location"><i class="fas fa-map-marker-alt" aria-hidden="true"></i> <?php echo esc_html($state_name); ?></span>
                <?php endif; ?>
            </div>
            <h1 class="sd__title"><?php the_title(); ?></h1>
        </header>

        <!-- ═══ Grid: 2 columns ═══ -->
        <div class="sd__grid">

            <!-- Left: Gallery + Content + Map -->
            <main class="sd__main">

                <?php if (!empty($gallery_ids)): ?>
                <!-- Gallery: Swiper Main + Thumbs -->
                <div class="sd__gallery" id="sd-gallery">
                    <!-- Main slider -->
                    <div class="swiper sd__gallery-main" id="sd-gallery-main">
                        <div class="swiper-wrapper">
                            <?php foreach ($gallery_ids as $i => $img_id):
                                $full_url = wp_get_attachment_image_url($img_id, 'full');
                            ?>
                            <div class="swiper-slide">
                                <button class="sd__gallery-zoom" type="button"
                                        data-index="<?php echo $i; ?>"
                                        aria-label="Phóng to ảnh <?php echo ($i + 1); ?>"
                                        tabindex="0">
                                    <?php echo wp_get_attachment_image($img_id, 'large', false, [
                                        'class' => 'sd__gallery-img',
                                        'loading' => $i === 0 ? 'eager' : 'lazy',
                                    ]); ?>
                                    <span class="sd__gallery-zoom-icon" aria-hidden="true"><i class="fas fa-search-plus"></i></span>
                                </button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (count($gallery_ids) > 1): ?>
                        <div class="swiper-button-prev" tabindex="0" role="button" aria-label="Ảnh trước"></div>
                        <div class="swiper-button-next" tabindex="0" role="button" aria-label="Ảnh tiếp"></div>
                        <?php endif; ?>
                    </div>

                    <?php if (count($gallery_ids) > 1): ?>
                    <!-- Thumbnail slider -->
                    <div class="swiper sd__gallery-thumbs" id="sd-gallery-thumbs">
                        <div class="swiper-wrapper">
                            <?php foreach ($gallery_ids as $img_id): ?>
                            <div class="swiper-slide">
                                <?php echo wp_get_attachment_image($img_id, 'thumbnail', false, [
                                    'loading' => 'lazy',
                                ]); ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if (get_the_content()): ?>
                <!-- Description -->
                <article class="sd__content-box">
                    <h2 class="sd__section-title"><i class="fas fa-store" aria-hidden="true"></i> Giới thiệu cửa hàng</h2>
                    <div class="sd__entry-content entry-content"><?php the_content(); ?></div>
                </article>
                <?php endif; ?>

                <?php if ($embed_url): ?>
                <!-- Map -->
                <div class="sd__map-box">
                    <h2 class="sd__section-title"><i class="fas fa-map-marked-alt" aria-hidden="true"></i> Bản đồ</h2>
                    <div class="sd__map-frame">
                        <iframe src="<?php echo esc_url($embed_url); ?>" width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Bản đồ <?php the_title(); ?>"></iframe>
                    </div>
                </div>
                <?php endif; ?>
            </main>

            <!-- Right: Contact Sidebar -->
            <aside class="sd__sidebar" aria-label="Thông tin liên hệ">
                <div class="sd__contact-card">
                    <h3 class="sd__card-heading"><i class="fas fa-address-card" aria-hidden="true"></i> Thông tin liên hệ</h3>

                    <dl class="sd__info-list">
                        <?php if ($address): ?>
                        <div class="sd__info-row">
                            <dt><span class="sd__icon sd__icon--address" aria-hidden="true"><i class="fas fa-map-pin"></i></span> Địa chỉ</dt>
                            <dd><?php echo esc_html($address); ?></dd>
                        </div>
                        <?php endif; ?>

                        <?php if ($phone): ?>
                        <div class="sd__info-row">
                            <dt><span class="sd__icon sd__icon--phone" aria-hidden="true"><i class="fas fa-phone-alt"></i></span> Điện thoại</dt>
                            <dd><a href="tel:<?php echo esc_attr(preg_replace('/\D/', '', $phone)); ?>"><?php echo esc_html($phone); ?></a></dd>
                        </div>
                        <?php endif; ?>

                        <?php if ($hotline && $hotline !== $phone): ?>
                        <div class="sd__info-row">
                            <dt><span class="sd__icon sd__icon--hotline" aria-hidden="true"><i class="fas fa-headset"></i></span> Hotline</dt>
                            <dd><a href="tel:<?php echo esc_attr(preg_replace('/\D/', '', $hotline)); ?>"><?php echo esc_html($hotline); ?></a></dd>
                        </div>
                        <?php endif; ?>

                        <?php if ($email): ?>
                        <div class="sd__info-row">
                            <dt><span class="sd__icon sd__icon--email" aria-hidden="true"><i class="fas fa-envelope"></i></span> Email</dt>
                            <dd><a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a></dd>
                        </div>
                        <?php endif; ?>

                        <?php if ($open): ?>
                        <div class="sd__info-row">
                            <dt><span class="sd__icon sd__icon--time" aria-hidden="true"><i class="fas fa-clock"></i></span> Giờ mở cửa</dt>
                            <dd><?php echo esc_html($open); ?></dd>
                        </div>
                        <?php endif; ?>

                        <?php if ($website): ?>
                        <div class="sd__info-row">
                            <dt><span class="sd__icon sd__icon--web" aria-hidden="true"><i class="fas fa-globe"></i></span> Website</dt>
                            <dd><a href="<?php echo esc_url($website); ?>" target="_blank" rel="noopener"><?php echo esc_html(preg_replace('#^https?://#', '', $website)); ?></a></dd>
                        </div>
                        <?php endif; ?>
                    </dl>

                    <!-- CTA Buttons -->
                    <div class="sd__cta-row">
                        <?php if ($phone): ?>
                        <a href="tel:<?php echo esc_attr(preg_replace('/\D/', '', $phone)); ?>" class="sd__cta sd__cta--call" aria-label="Gọi <?php echo esc_attr($phone); ?>">
                            <i class="fas fa-phone-alt" aria-hidden="true"></i> Gọi ngay
                        </a>
                        <?php endif; ?>
                        <?php if ($dir_url): ?>
                        <a href="<?php echo esc_url($dir_url); ?>" target="_blank" rel="noopener" class="sd__cta sd__cta--dir" aria-label="Chỉ đường trên Google Maps">
                            <i class="fas fa-directions" aria-hidden="true"></i> Chỉ đường
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Back link -->
                <a href="<?php echo esc_url($store_page_url); ?>" class="sd__back" aria-label="Quay về danh sách cửa hàng">
                    <i class="fas fa-arrow-left" aria-hidden="true"></i> Xem tất cả cửa hàng
                </a>
            </aside>

        </div>

        <!-- ═══ Related Stores ═══ -->
        <?php
        $related_args = [
            'post_type'      => 'local_store',
            'post_status'    => 'publish',
            'posts_per_page' => 3,
            'post__not_in'   => [$post_id],
            'orderby'        => 'rand',
        ];
        if ($states && !is_wp_error($states)) {
            $related_args['tax_query'] = [[
                'taxonomy' => 'local_store_state',
                'field'    => 'term_id',
                'terms'    => [$states[0]->term_id],
            ]];
        }
        $related = new WP_Query($related_args);
        if ($related->found_posts < 3) {
            wp_reset_postdata();
            unset($related_args['tax_query']);
            $related = new WP_Query($related_args);
        }
        if ($related->have_posts()):
        ?>
        <section class="sd__related" aria-label="Cửa hàng khác">
            <h2 class="sd__section-title"><i class="fas fa-store-alt" aria-hidden="true"></i> Cửa hàng khác</h2>
            <div class="sd__related-grid">
                <?php while ($related->have_posts()): $related->the_post();
                    $r_id        = get_the_ID();
                    $r_address   = get_post_meta($r_id, 'localstore_address', true);
                    $r_phone     = get_post_meta($r_id, 'localstore_phone', true);
                    $r_types     = get_the_terms($r_id, 'store_type');
                    $r_type_slug = ($r_types && !is_wp_error($r_types)) ? $r_types[0]->slug : '';
                    $r_type_name = ($r_types && !is_wp_error($r_types)) ? $r_types[0]->name : '';
                    $r_img       = get_the_post_thumbnail_url($r_id, 'medium');
                ?>
                <a href="<?php the_permalink(); ?>" class="sd__rcard" data-type="<?php echo esc_attr($r_type_slug); ?>">
                    <div class="sd__rcard-img"><?php if ($r_img): ?><img src="<?php echo esc_url($r_img); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" /><?php endif; ?></div>
                    <div class="sd__rcard-body">
                        <h3 class="sd__rcard-name"><?php the_title(); ?></h3>
                        <?php if ($r_type_name): ?><span class="sd__badge sd__badge--<?php echo esc_attr($r_type_slug); ?>"><?php echo esc_html($r_type_name); ?></span><?php endif; ?>
                        <?php if ($r_address): ?><p class="sd__rcard-addr"><i class="fas fa-map-pin" aria-hidden="true"></i> <?php echo esc_html($r_address); ?></p><?php endif; ?>
                    </div>
                </a>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        </section>
        <?php endif; ?>

    </div>
</section>

<!-- ═══ Lightbox Popup ═══ -->
<div class="sd__lightbox" id="sd-lightbox" role="dialog" aria-modal="true" aria-label="Xem ảnh cửa hàng" hidden>
    <button class="sd__lightbox-close" type="button" aria-label="Đóng" tabindex="0"><i class="fas fa-times"></i></button>
    <div class="swiper sd__lightbox-swiper" id="sd-lightbox-swiper">
        <div class="swiper-wrapper">
            <?php foreach ($gallery_ids as $img_id): ?>
            <div class="swiper-slide">
                <?php echo wp_get_attachment_image($img_id, 'full', false, ['class' => 'sd__lightbox-img', 'loading' => 'lazy']); ?>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="swiper-button-prev" tabindex="0" role="button" aria-label="Ảnh trước"></div>
        <div class="swiper-button-next" tabindex="0" role="button" aria-label="Ảnh tiếp"></div>
        <div class="swiper-pagination"></div>
    </div>
    <div class="sd__lightbox-counter" aria-live="polite"><span id="sd-lb-current">1</span> / <?php echo count($gallery_ids); ?></div>
</div>

<!-- ═══ Gallery + Lightbox JS ═══ -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Swiper === 'undefined') return;

    // ── Gallery Thumbs ──
    var thumbsSwiper = null;
    var thumbsEl = document.getElementById('sd-gallery-thumbs');
    if (thumbsEl) {
        thumbsSwiper = new Swiper(thumbsEl, {
            spaceBetween: 8,
            slidesPerView: 5,
            freeMode: true,
            watchSlidesProgress: true,
            breakpoints: {
                0:   { slidesPerView: 4 },
                640: { slidesPerView: 5 },
                1024:{ slidesPerView: 5 }
            }
        });
    }

    // ── Gallery Main ──
    var mainEl = document.getElementById('sd-gallery-main');
    var mainSwiper = null;
    if (mainEl) {
        var mainOpts = {
            spaceBetween: 0,
            loop: false,
            navigation: {
                nextEl: mainEl.querySelector('.swiper-button-next'),
                prevEl: mainEl.querySelector('.swiper-button-prev'),
            },
            keyboard: { enabled: true },
        };
        if (thumbsSwiper) mainOpts.thumbs = { swiper: thumbsSwiper };
        mainSwiper = new Swiper(mainEl, mainOpts);
    }

    // ── Lightbox ──
    var lightbox   = document.getElementById('sd-lightbox');
    var lbSwiperEl = document.getElementById('sd-lightbox-swiper');
    var lbCounter  = document.getElementById('sd-lb-current');
    var lbSwiper   = null;

    function openLightbox(index) {
        if (!lightbox || !lbSwiperEl) return;
        lightbox.hidden = false;
        document.body.style.overflow = 'hidden';

        if (!lbSwiper) {
            lbSwiper = new Swiper(lbSwiperEl, {
                spaceBetween: 0,
                initialSlide: index || 0,
                navigation: {
                    nextEl: lbSwiperEl.querySelector('.swiper-button-next'),
                    prevEl: lbSwiperEl.querySelector('.swiper-button-prev'),
                },
                pagination: { el: lbSwiperEl.querySelector('.swiper-pagination'), clickable: true },
                keyboard: { enabled: true },
                on: {
                    slideChange: function() {
                        if (lbCounter) lbCounter.textContent = this.activeIndex + 1;
                    }
                }
            });
        } else {
            lbSwiper.slideTo(index || 0, 0);
        }
        if (lbCounter) lbCounter.textContent = (index || 0) + 1;

        // Focus trap
        setTimeout(function() { lightbox.querySelector('.sd__lightbox-close').focus(); }, 100);
    }

    function closeLightbox() {
        if (!lightbox) return;
        lightbox.hidden = true;
        document.body.style.overflow = '';
    }

    // Click zoom buttons
    document.querySelectorAll('.sd__gallery-zoom').forEach(function(btn) {
        btn.addEventListener('click', function() { openLightbox(parseInt(this.dataset.index) || 0); });
    });

    // Close button
    var closeBtn = lightbox ? lightbox.querySelector('.sd__lightbox-close') : null;
    if (closeBtn) closeBtn.addEventListener('click', closeLightbox);

    // Click backdrop
    if (lightbox) {
        lightbox.addEventListener('click', function(e) {
            if (e.target === lightbox) closeLightbox();
        });
    }

    // Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && lightbox && !lightbox.hidden) closeLightbox();
    });
});
</script>

<?php get_footer(); ?>
