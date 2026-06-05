<?php
/**
 * Template for displaying single local_store (DevVN Local Store Pro)
 * Premium store detail page with contact info, gallery, map
 *
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
$types     = get_the_terms($post_id, 'store_type');
$states    = get_the_terms($post_id, 'local_store_state');
$type_slug = ($types && !is_wp_error($types)) ? $types[0]->slug : '';
$type_name = ($types && !is_wp_error($types)) ? $types[0]->name : '';
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

// ── Gallery images (post content images or attached images) ──
$gallery_ids = [];
$attached = get_posts([
    'post_parent'    => $post_id,
    'post_type'      => 'attachment',
    'post_mime_type' => 'image',
    'posts_per_page' => 20,
    'orderby'        => 'menu_order',
    'order'          => 'ASC',
    'fields'         => 'ids',
]);
if ($attached) {
    $gallery_ids = $attached;
}
// Always include featured image at the start if exists
$thumb_id = get_post_thumbnail_id($post_id);
if ($thumb_id) {
    $gallery_ids = array_unique(array_merge([$thumb_id], $gallery_ids));
}

// ── Store system page URL ────────────────────────────────
$store_page_url = '';
$store_pages = get_pages([
    'meta_key'   => '_wp_page_template',
    'meta_value' => 'default',
    'number'     => 1,
]);
// Try to find the store listing page
$store_page = get_page_by_path('he-thong-cua-hang');
if (!$store_page) {
    $store_page = get_page_by_path('he-thong-dai-ly');
}
$store_page_url = $store_page ? get_permalink($store_page->ID) : home_url('/he-thong-cua-hang/');

// Enqueue section CSS
\HD_Helper::enqueueSectionStyle('section-store-detail', ['index-css']);

// Breadcrumbs
\HD_Helper::blockTemplate('parts/blocks/breadcrumbs', [
    'title' => get_the_title(),
]);
?>

<section class="section section-store-detail">
    <div class="container">

        <!-- ═══ Hero ═══ -->
        <div class="store-detail__hero">
            <div class="store-detail__hero-content">
                <div class="store-detail__title-row">
                    <h1 class="store-detail__name"><?php the_title(); ?></h1>
                    <?php if ($type_name): ?>
                        <span class="sdt-badge sdt-badge--<?php echo esc_attr($type_slug); ?>">
                            <?php echo esc_html($type_name); ?>
                        </span>
                    <?php endif; ?>
                </div>
                <?php if ($state_name): ?>
                    <p class="store-detail__location">
                        <i class="fas fa-map-marker-alt"></i>
                        <?php echo esc_html($state_name); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- ═══ Main Content: 2 columns ═══ -->
        <div class="store-detail__grid">

            <!-- Left: Gallery + Content -->
            <div class="store-detail__main">

                <?php if (!empty($gallery_ids)): ?>
                <!-- Gallery -->
                <div class="store-detail__gallery">
                    <div class="store-detail__gallery-main">
                        <?php
                        $main_img_id = $gallery_ids[0];
                        echo wp_get_attachment_image($main_img_id, 'large', false, [
                            'class' => 'store-detail__gallery-img',
                            'id'    => 'store-gallery-main-img',
                        ]);
                        ?>
                    </div>
                    <?php if (count($gallery_ids) > 1): ?>
                    <div class="store-detail__gallery-thumbs">
                        <?php foreach ($gallery_ids as $i => $img_id): ?>
                        <button class="store-detail__gallery-thumb<?php echo $i === 0 ? ' active' : ''; ?>"
                                data-full="<?php echo esc_url(wp_get_attachment_image_url($img_id, 'large')); ?>"
                                data-srcset="<?php echo esc_attr(wp_get_attachment_image_srcset($img_id, 'large')); ?>">
                            <?php echo wp_get_attachment_image($img_id, 'thumbnail', false, ['loading' => 'lazy']); ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if (get_the_content()): ?>
                <!-- Description -->
                <div class="store-detail__content">
                    <h2 class="store-detail__section-title">
                        <i class="fas fa-store"></i>
                        Giới thiệu cửa hàng
                    </h2>
                    <div class="entry-content">
                        <?php the_content(); ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($embed_url): ?>
                <!-- Map -->
                <div class="store-detail__map-section">
                    <h2 class="store-detail__section-title">
                        <i class="fas fa-map-marked-alt"></i>
                        Bản đồ
                    </h2>
                    <div class="store-detail__map-container">
                        <iframe
                            src="<?php echo esc_url($embed_url); ?>"
                            width="100%"
                            height="400"
                            style="border:0;"
                            allowfullscreen=""
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            title="Bản đồ <?php the_title(); ?>">
                        </iframe>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right: Contact Info Card -->
            <aside class="store-detail__sidebar">
                <div class="store-detail__contact-card">
                    <h3 class="store-detail__card-title">
                        <i class="fas fa-address-card"></i>
                        Thông tin liên hệ
                    </h3>

                    <ul class="store-detail__info-list">
                        <?php if ($address): ?>
                        <li class="store-detail__info-item">
                            <span class="store-detail__info-icon store-detail__info-icon--address">
                                <i class="fas fa-map-pin"></i>
                            </span>
                            <div class="store-detail__info-content">
                                <span class="store-detail__info-label">Địa chỉ</span>
                                <span class="store-detail__info-value"><?php echo esc_html($address); ?></span>
                            </div>
                        </li>
                        <?php endif; ?>

                        <?php if ($phone): ?>
                        <li class="store-detail__info-item">
                            <span class="store-detail__info-icon store-detail__info-icon--phone">
                                <i class="fas fa-phone-alt"></i>
                            </span>
                            <div class="store-detail__info-content">
                                <span class="store-detail__info-label">Điện thoại</span>
                                <a href="tel:<?php echo esc_attr(preg_replace('/\D/', '', $phone)); ?>" class="store-detail__info-value store-detail__info-link">
                                    <?php echo esc_html($phone); ?>
                                </a>
                            </div>
                        </li>
                        <?php endif; ?>

                        <?php if ($hotline && $hotline !== $phone): ?>
                        <li class="store-detail__info-item">
                            <span class="store-detail__info-icon store-detail__info-icon--hotline">
                                <i class="fas fa-headset"></i>
                            </span>
                            <div class="store-detail__info-content">
                                <span class="store-detail__info-label">Hotline</span>
                                <a href="tel:<?php echo esc_attr(preg_replace('/\D/', '', $hotline)); ?>" class="store-detail__info-value store-detail__info-link">
                                    <?php echo esc_html($hotline); ?>
                                </a>
                            </div>
                        </li>
                        <?php endif; ?>

                        <?php if ($email): ?>
                        <li class="store-detail__info-item">
                            <span class="store-detail__info-icon store-detail__info-icon--email">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <div class="store-detail__info-content">
                                <span class="store-detail__info-label">Email</span>
                                <a href="mailto:<?php echo esc_attr($email); ?>" class="store-detail__info-value store-detail__info-link">
                                    <?php echo esc_html($email); ?>
                                </a>
                            </div>
                        </li>
                        <?php endif; ?>

                        <?php if ($open): ?>
                        <li class="store-detail__info-item">
                            <span class="store-detail__info-icon store-detail__info-icon--time">
                                <i class="fas fa-clock"></i>
                            </span>
                            <div class="store-detail__info-content">
                                <span class="store-detail__info-label">Giờ mở cửa</span>
                                <span class="store-detail__info-value"><?php echo esc_html($open); ?></span>
                            </div>
                        </li>
                        <?php endif; ?>

                        <?php if ($website): ?>
                        <li class="store-detail__info-item">
                            <span class="store-detail__info-icon store-detail__info-icon--web">
                                <i class="fas fa-globe"></i>
                            </span>
                            <div class="store-detail__info-content">
                                <span class="store-detail__info-label">Website</span>
                                <a href="<?php echo esc_url($website); ?>" target="_blank" rel="noopener" class="store-detail__info-value store-detail__info-link">
                                    <?php echo esc_html(preg_replace('#^https?://#', '', $website)); ?>
                                </a>
                            </div>
                        </li>
                        <?php endif; ?>
                    </ul>

                    <!-- CTA Buttons -->
                    <div class="store-detail__cta-group">
                        <?php if ($phone): ?>
                        <a href="tel:<?php echo esc_attr(preg_replace('/\D/', '', $phone)); ?>" class="store-detail__cta store-detail__cta--call">
                            <i class="fas fa-phone-alt"></i>
                            Gọi ngay
                        </a>
                        <?php endif; ?>

                        <?php if ($dir_url): ?>
                        <a href="<?php echo esc_url($dir_url); ?>" target="_blank" rel="noopener" class="store-detail__cta store-detail__cta--dir">
                            <i class="fas fa-directions"></i>
                            Chỉ đường
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Back to listing -->
                <a href="<?php echo esc_url($store_page_url); ?>" class="store-detail__back-link">
                    <i class="fas fa-arrow-left"></i>
                    Xem tất cả cửa hàng
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

        // Prefer same province
        if ($states && !is_wp_error($states)) {
            $related_args['tax_query'] = [[
                'taxonomy' => 'local_store_state',
                'field'    => 'term_id',
                'terms'    => [$states[0]->term_id],
            ]];
        }

        $related = new WP_Query($related_args);

        // Fallback: if not enough from same province, get any
        if ($related->found_posts < 3) {
            wp_reset_postdata();
            unset($related_args['tax_query']);
            $related = new WP_Query($related_args);
        }

        if ($related->have_posts()):
        ?>
        <div class="store-detail__related">
            <h2 class="store-detail__section-title">
                <i class="fas fa-store-alt"></i>
                Cửa hàng khác
            </h2>
            <div class="store-detail__related-grid">
                <?php while ($related->have_posts()): $related->the_post();
                    $r_id       = get_the_ID();
                    $r_address  = get_post_meta($r_id, 'localstore_address', true);
                    $r_phone    = get_post_meta($r_id, 'localstore_phone', true);
                    $r_types    = get_the_terms($r_id, 'store_type');
                    $r_type_slug = ($r_types && !is_wp_error($r_types)) ? $r_types[0]->slug : '';
                    $r_type_name = ($r_types && !is_wp_error($r_types)) ? $r_types[0]->name : '';
                    $r_img      = get_the_post_thumbnail_url($r_id, 'medium');
                ?>
                <a href="<?php the_permalink(); ?>" class="store-detail__related-card" data-type="<?php echo esc_attr($r_type_slug); ?>">
                    <?php if ($r_img): ?>
                    <div class="store-detail__related-img">
                        <img src="<?php echo esc_url($r_img); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" />
                    </div>
                    <?php endif; ?>
                    <div class="store-detail__related-body">
                        <h3 class="store-detail__related-name"><?php the_title(); ?></h3>
                        <?php if ($r_type_name): ?>
                        <span class="sdt-badge sdt-badge--<?php echo esc_attr($r_type_slug); ?>"><?php echo esc_html($r_type_name); ?></span>
                        <?php endif; ?>
                        <?php if ($r_address): ?>
                        <p class="store-detail__related-address"><i class="fas fa-map-pin"></i> <?php echo esc_html($r_address); ?></p>
                        <?php endif; ?>
                    </div>
                </a>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        </div>
        <?php endif; ?>

    </div>
</section>

<!-- Gallery thumbnail JS -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var thumbs = document.querySelectorAll('.store-detail__gallery-thumb');
    var mainImg = document.getElementById('store-gallery-main-img');
    if (!mainImg || !thumbs.length) return;

    thumbs.forEach(function(btn) {
        btn.addEventListener('click', function() {
            thumbs.forEach(function(t) { t.classList.remove('active'); });
            btn.classList.add('active');
            mainImg.src = btn.dataset.full;
            if (btn.dataset.srcset) mainImg.srcset = btn.dataset.srcset;
        });
    });
});
</script>

<?php
get_footer();
