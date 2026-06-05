<?php

/**
 * Plugin Name: DevVN Store Type
 * Description: Thêm phân loại cửa hàng (Cửa Hàng / Đại Lý / Cửa Hàng Uỷ Quyền) và bộ lọc tìm kiếm cho DevVN Local Stores Pro.
 * Version: 1.0.0
 * Author: SPL
 */

if (!defined('ABSPATH')) exit;

// ════════════════════════════════════════════════════════════
//  0. BẬT TRANG CHI TIẾT CHO CPT local_store
//     Plugin DevVN mặc định tắt publicly_queryable → 404
//     Filter này bật lại để có URL /cua-hang/ten-cua-hang/
// ════════════════════════════════════════════════════════════

add_filter('register_post_type_args', 'sdt_enable_store_single_page', 99, 2);
function sdt_enable_store_single_page($args, $post_type)
{
    if ($post_type === 'local_store') {
        $args['publicly_queryable'] = true;
    }
    return $args;
}

// ════════════════════════════════════════════════════════════
//  1. ĐĂNG KÝ TAXONOMY "store_type"
// ════════════════════════════════════════════════════════════

add_action('init', 'sdt_register_taxonomy');
function sdt_register_taxonomy()
{
    $labels = [
        'name'              => 'Loại Cửa Hàng',
        'singular_name'     => 'Loại Cửa Hàng',
        'search_items'      => 'Tìm loại cửa hàng',
        'all_items'         => 'Tất cả loại',
        'edit_item'         => 'Sửa loại',
        'update_item'       => 'Cập nhật loại',
        'add_new_item'      => 'Thêm loại mới',
        'new_item_name'     => 'Tên loại mới',
        'menu_name'         => 'Loại Cửa Hàng',
    ];

    register_taxonomy('store_type', ['local_store'], [
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => ['slug' => 'loai-cua-hang'],
        'show_in_rest'      => false,
    ]);
}

// ════════════════════════════════════════════════════════════
//  2. TẠO CÁC TERM MẶC ĐỊNH KHI KÍCH HOẠT PLUGIN
// ════════════════════════════════════════════════════════════

register_activation_hook(__FILE__, 'sdt_create_default_terms_on_activate');
function sdt_create_default_terms_on_activate()
{
    // Taxonomy chưa register ở thời điểm activation hook → gọi thủ công
    sdt_register_taxonomy();
    sdt_create_default_terms();
}

// Fallback: tạo terms khi init nếu chưa có (hữu ích khi deactivate-reactivate)
add_action('init', 'sdt_create_default_terms', 20);
function sdt_create_default_terms()
{
    // Chỉ chạy nếu taxonomy đã register
    if (!taxonomy_exists('store_type')) return;

    $terms = [
        ['name' => 'Đại Lý Uỷ Quyền',   'slug' => 'dai-ly-uy-quyen'],
        ['name' => 'Cửa Hàng Uỷ Quyền', 'slug' => 'cua-hang-uy-quyen'],
    ];

    foreach ($terms as $term) {
        if (!term_exists($term['slug'], 'store_type')) {
            wp_insert_term($term['name'], 'store_type', ['slug' => $term['slug']]);
        }
    }
}

// ════════════════════════════════════════════════════════════
//  2b. ACF GALLERY — Ảnh slide cửa hàng
//      Cần ACF Pro để dùng Gallery field
// ════════════════════════════════════════════════════════════

add_action('acf/include_fields', 'sdt_register_store_gallery_field');
function sdt_register_store_gallery_field()
{
    if (!function_exists('acf_add_local_field_group')) return;

    acf_add_local_field_group([
        'key'      => 'group_store_gallery',
        'title'    => 'Hình ảnh cửa hàng',
        'fields'   => [
            [
                'key'           => 'field_store_gallery',
                'label'         => 'Gallery ảnh',
                'name'          => 'store_gallery',
                'type'          => 'gallery',
                'instructions'  => 'Upload nhiều ảnh cho slide hình ảnh trang chi tiết cửa hàng. Ảnh đầu tiên sẽ là ảnh chính.',
                'required'      => 0,
                'return_format' => 'id',
                'library'       => 'all',
                'min'           => 0,
                'max'           => 20,
                'insert'        => 'append',
                'preview_size'  => 'medium',
                'mime_types'    => 'jpg, jpeg, png, webp',
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'local_store',
                ],
            ],
        ],
        'menu_order'            => 5,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'active'                => true,
    ]);
}

// ════════════════════════════════════════════════════════════
//  3. INJECT DROPDOWN "LOẠI CỬA HÀNG" VÀO SEARCH FORM
//     Hook: localstore_after_form (sau dropdown Quận/Huyện)
// ════════════════════════════════════════════════════════════

add_action('localstore_after_form', 'sdt_inject_store_type_filter', 10, 2);
function sdt_inject_store_type_filter($thisClass, $atts)
{
    $terms = get_terms([
        'taxonomy'   => 'store_type',
        'hide_empty' => false,
        'orderby'    => 'term_id',
        'order'      => 'ASC',
    ]);

    if (is_wp_error($terms) || empty($terms)) return;

    $selected = isset($_POST['store_type']) ? sanitize_text_field($_POST['store_type']) : '';
?>
    <div class="dvls_flex_box dvls_store_type_box" style="max-width:100%;flex-basis:100%;padding:0 5px;margin-bottom:10px;">
        <select name="store_type" id="dvls_store_type">
            <option value="">Tất cả loại</option>
            <?php foreach ($terms as $term): ?>
                <option value="<?php echo esc_attr($term->slug); ?>"
                    <?php selected($selected, $term->slug); ?>>
                    <?php echo esc_html($term->name); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
<?php
}

// ════════════════════════════════════════════════════════════
//  4. LỌC KẾT QUẢ THEO LOẠI CỬA HÀNG — CUSTOM AJAX ENDPOINT
//     Plugin DevVN dùng ionCube + custom SQL → posts_clauses
//     không hoạt động. Thay vào đó ta tạo AJAX riêng query
//     WP_Query theo taxonomy store_type → render HTML giống
//     item.php → thay thế danh sách trên frontend.
// ════════════════════════════════════════════════════════════

// ── 4a. ENQUEUE AJAX URL ──
add_action('wp_enqueue_scripts', 'sdt_enqueue_ajax');
function sdt_enqueue_ajax()
{
    if (!is_page()) return;
    global $post;
    if (!$post || strpos($post->post_content, 'local_store') === false) return;

    wp_enqueue_script('jquery');
    wp_localize_script('jquery', 'sdt_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('sdt_filter_nonce'),
    ]);
}

// ── 4b. AJAX HANDLER: query stores theo store_type + city ──
add_action('wp_ajax_sdt_filter_stores', 'sdt_ajax_filter_stores');
add_action('wp_ajax_nopriv_sdt_filter_stores', 'sdt_ajax_filter_stores');
function sdt_ajax_filter_stores()
{
    check_ajax_referer('sdt_filter_nonce', 'nonce');

    $store_type = sanitize_text_field($_POST['store_type'] ?? '');
    $city       = sanitize_text_field($_POST['city'] ?? '');
    $district   = sanitize_text_field($_POST['district'] ?? '');
    $keyword    = sanitize_text_field($_POST['keyword'] ?? '');

    $args = [
        'post_type'      => 'local_store',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ];

    // Keyword search
    if ($keyword) {
        $args['s'] = $keyword;
    }

    // Tax queries
    $tax_query = [];

    if ($store_type) {
        $tax_query[] = [
            'taxonomy' => 'store_type',
            'field'    => 'slug',
            'terms'    => [$store_type],
        ];
    }

    // City: plugin dùng term_id (số) trong select → tự detect ID vs slug
    // Loại bỏ các giá trị rỗng, '0', 'null' (JS có thể gửi string 'null')
    $city_clean = (isset($city) && trim($city) !== '' && $city !== '0' && strtolower($city) !== 'null') ? trim($city) : '';
    if ($city_clean) {
        $tax_query[] = [
            'taxonomy' => 'local_store_state',
            'field'    => is_numeric($city_clean) ? 'term_id' : 'slug',
            'terms'    => [is_numeric($city_clean) ? (int) $city_clean : $city_clean],
        ];
    }

    // District: tương tự city
    $district_clean = (isset($district) && trim($district) !== '' && $district !== '0' && strtolower($district) !== 'null') ? trim($district) : '';
    if ($district_clean) {
        $tax_query[] = [
            'taxonomy' => 'local_store_district',
            'field'    => is_numeric($district_clean) ? 'term_id' : 'slug',
            'terms'    => [is_numeric($district_clean) ? (int) $district_clean : $district_clean],
        ];
    }

    if (count($tax_query) > 1) {
        $tax_query['relation'] = 'AND';
    }
    if (!empty($tax_query)) {
        $args['tax_query'] = $tax_query;
    }

    // DEBUG: log để trace vấn đề kết hợp fields
    error_log('[SDT AJAX] Received: store_type=' . $store_type . ', city=' . $city . ', district=' . $district . ', keyword=' . $keyword);
    error_log('[SDT AJAX] tax_query: ' . print_r($tax_query, true));

    $query = new WP_Query($args);

    // DEBUG: log SQL query và kết quả
    error_log('[SDT AJAX] SQL: ' . $query->request);
    error_log('[SDT AJAX] Found: ' . $query->found_posts . ' posts');

    $html  = '';
    $count = 0;
    $markers = [];

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $postid = get_the_ID();

            // Meta data
            $address = get_post_meta($postid, 'localstore_address', true);
            $phone   = get_post_meta($postid, 'localstore_phone', true);
            $hotline = get_post_meta($postid, 'localstore_hotline', true);
            $email   = get_post_meta($postid, 'localstore_email', true);
            $lat     = get_post_meta($postid, 'localstore_maps_lat', true);
            $lng     = get_post_meta($postid, 'localstore_maps_lng', true);

            // Badge
            $type_terms = get_the_terms($postid, 'store_type');
            $badge_html = '';
            if ($type_terms && !is_wp_error($type_terms)) {
                foreach ($type_terms as $tt) {
                    $badge_html .= '<span class="sdt-badge sdt-badge--' . esc_attr($tt->slug) . '">' . esc_html($tt->name) . '</span>';
                }
            }

            // Thumbnail
            $has_thumb = has_post_thumbnail($postid);
            $thumb_class = $has_thumb ? 'has_thumb' : 'no_thumb';

            // Directions URL
            $dir_url = '';
            if ($lat && $lng) {
                $dir_url = 'https://www.google.com/maps/dir/?api=1&destination=' . esc_attr($lat) . '%2C' . esc_attr($lng);
            } elseif ($address) {
                $dir_url = 'https://www.google.com/maps/dir/?api=1&destination=' . urlencode($address);
            }

            // Marker data for map
            if ($lat && $lng) {
                $markers[] = [
                    'lat'   => (float) $lat,
                    'lng'   => (float) $lng,
                    'title' => get_the_title(),
                ];
            }

            // Render HTML giống item.php
            ob_start();
            ?>
            <div class="localstore_box <?php echo $thumb_class; ?>" data-id="<?php echo $postid; ?>">
                <?php if ($has_thumb): ?>
                <div class="localstore_img">
                    <?php the_post_thumbnail('full'); ?>
                </div>
                <?php endif; ?>
                <div class="localstore_info">
                    <div class="localstore_info_name">
                        <strong><?php the_title(); ?></strong>
                        <?php echo $badge_html; ?>
                    </div>
                    <ul>
                        <?php if ($address): ?><li><i class="fas fa-map-marked-alt"></i> <?php echo esc_html($address); ?></li><?php endif; ?>
                        <?php if ($phone): ?><li><i class="fas fa-phone-alt"></i> <?php echo esc_html($phone); ?></li><?php endif; ?>
                        <?php if ($hotline): ?><li><i class="fa-solid fa-mobile-screen"></i> <?php echo esc_html($hotline); ?></li><?php endif; ?>
                        <?php if ($email): ?><li><i class="fa-solid fa-envelope"></i> <?php echo esc_html($email); ?></li><?php endif; ?>
                    </ul>
                    <div class="localstore_action">
                        <?php if ($dir_url): ?>
                            <a href="<?php echo esc_url($dir_url); ?>" target="_blank" title="Chỉ đường">
                                <i class="fa-solid fa-location-arrow"></i> Chỉ đường
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php
            $html .= ob_get_clean();
            $count++;
        }
        wp_reset_postdata();
    }

    wp_send_json_success([
        'html'    => $html,
        'count'   => $count,
        'markers' => $markers,
        // DEBUG — xóa sau khi fix xong
        '_debug'  => [
            'received'   => ['store_type' => $store_type, 'city' => $city, 'district' => $district, 'keyword' => $keyword],
            'tax_query'  => $tax_query,
            'sql'        => $query->request,
            'found'      => $query->found_posts,
        ],
    ]);
}

// ── 4c. CSS cho panel width + loading state ──
add_action('wp_head', 'sdt_inject_filter_css');
function sdt_inject_filter_css()
{
?>
    <style id="sdt-filter-css">
        /* Mở rộng panel trái để tên cửa hàng không xuống dòng */
        @media (min-width: 768px) {
            .localstore_search_wrap {
                width: 50% !important;
            }
        }

        /* Loading overlay cho kết quả */
        .sdt-loading-overlay {
            position: absolute;
            inset: 0;
            background: rgba(255,255,255,.85);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 99;
            font-size: 14px;
            color: #666;
        }
        .sdt-loading-overlay .sdt-spinner {
            width: 24px;
            height: 24px;
            border: 3px solid #e0e0e0;
            border-top-color: #1565c0;
            border-radius: 50%;
            animation: sdt-spin .6s linear infinite;
            margin-right: 10px;
        }
        @keyframes sdt-spin {
            to { transform: rotate(360deg); }
        }

        /* Thông báo kết quả custom */
        .sdt-result-info {
            padding: 8px 12px;
            background: #e8f5e9;
            border-left: 3px solid #43a047;
            margin-bottom: 8px;
            font-size: 13px;
            color: #2e7d32;
            border-radius: 0 4px 4px 0;
        }
        .sdt-result-info.sdt-result-empty {
            background: #fff3e0;
            border-left-color: #e65100;
            color: #bf360c;
        }
    </style>
<?php
}

// ── 4d. CLIENT-SIDE JS: gọi AJAX khi user chọn loại cửa hàng ──
add_action('wp_footer', 'sdt_inject_client_filter_js');
function sdt_inject_client_filter_js()
{
?>
    <script>
        (function($) {
            'use strict';

            // Trạng thái
            var sdtIsFiltering = false;  // true = đang hiển thị kết quả filter riêng
            var sdtCurrentXHR = null;

            // ── Fix nút Chỉ đường ──
            function sdtFixDirectionsLinks() {
                document.querySelectorAll('.localstore_action a[href*="google.com/maps/dir"]').forEach(function(link) {
                    var href = link.getAttribute('href') || '';
                    var m = href.match(/\/dir\/[^\/]+\/([-\d.]+),([-\d.]+)/);
                    if (m) {
                        link.setAttribute('href', 'https://www.google.com/maps/dir/?api=1&destination=' + m[1] + '%2C' + m[2]);
                        return;
                    }
                    var box = link.closest('.localstore_box');
                    var addrEl = box ? box.querySelector('.localstore_info > ul li:first-child') : null;
                    var addr = addrEl ? addrEl.textContent.trim() : '';
                    if (addr) link.setAttribute('href', 'https://www.google.com/maps/dir/?api=1&destination=' + encodeURIComponent(addr));
                });
            }

            // ── Hiển thị loading ──
            function sdtShowLoading() {
                var $results = $('.localstore_search_results');
                if (!$results.find('.sdt-loading-overlay').length) {
                    $results.css('position', 'relative');
                    $results.append(
                        '<div class="sdt-loading-overlay">' +
                            '<div class="sdt-spinner"></div>' +
                            '<span>Đang tìm kiếm...</span>' +
                        '</div>'
                    );
                }
            }

            function sdtHideLoading() {
                $('.sdt-loading-overlay').remove();
            }

            // ── Lưu nội dung gốc để restore khi "Tất cả" ──
            var sdtOriginalHTML = null;
            var sdtOriginalCount = null;

            function sdtSaveOriginal() {
                if (sdtOriginalHTML === null) {
                    sdtOriginalHTML = $('.localstore_search_results').html();
                    var countEl = $('.localstore_search_count span');
                    sdtOriginalCount = countEl.length ? countEl.text() : null;
                }
            }

            function sdtRestoreOriginal() {
                if (sdtOriginalHTML !== null) {
                    $('.localstore_search_results').html(sdtOriginalHTML);
                    if (sdtOriginalCount !== null) {
                        $('.localstore_search_count span').text(sdtOriginalCount);
                    }
                }
                sdtIsFiltering = false;
            }

            // ── Gọi AJAX lọc theo type ──
            function sdtFilterByType(storeType) {
                // Hủy request cũ nếu đang chạy
                if (sdtCurrentXHR) sdtCurrentXHR.abort();

                // Lưu HTML gốc lần đầu
                sdtSaveOriginal();

                // Nếu "Tất cả" → khôi phục gốc
                if (!storeType) {
                    sdtRestoreOriginal();
                    sdtFixDirectionsLinks();
                    return;
                }

                sdtIsFiltering = true;

                // Hàm chuẩn hóa giá trị select (tránh gửi null/undefined/"null"/"0")
                function sdtVal(selector) {
                    var v = $(selector).val();
                    if (!v || v === '0' || v === 'null' || v === 'undefined') return '';
                    return v;
                }

                // Đọc city/district hiện tại từ form
                var city     = sdtVal('#dvls_city');
                var district = sdtVal('#dvls_district');
                var keyword  = ($('input[name="local_address"]').val() || '').trim();

                sdtShowLoading();

                sdtCurrentXHR = $.ajax({
                    url: (typeof sdt_ajax !== 'undefined') ? sdt_ajax.ajax_url : '/wp-admin/admin-ajax.php',
                    type: 'POST',
                    data: {
                        action:     'sdt_filter_stores',
                        nonce:      (typeof sdt_ajax !== 'undefined') ? sdt_ajax.nonce : '',
                        store_type: storeType,
                        city:       city,
                        district:   district,
                        keyword:    keyword
                    },
                    success: function(res) {
                        sdtHideLoading();
                        if (!res.success) return;

                        // DEBUG — xóa sau khi fix xong
                        if (res.data._debug) console.log('[SDT DEBUG]', res.data._debug);

                        var $results = $('.localstore_search_results');

                        if (res.data.count > 0) {
                            // Thêm thông báo kết quả + HTML stores
                            $results.html(
                                '<div class="sdt-result-info">Tìm thấy <strong>' +
                                res.data.count + '</strong> cửa hàng</div>' +
                                res.data.html
                            );
                        } else {
                            $results.html(
                                '<div class="sdt-result-info sdt-result-empty">' +
                                'Không tìm thấy cửa hàng phù hợp với bộ lọc.</div>'
                            );
                        }

                        // Cập nhật count
                        $('.localstore_search_count span').text(res.data.count);

                        // Fix links
                        setTimeout(sdtFixDirectionsLinks, 100);
                    },
                    error: function(xhr, status) {
                        sdtHideLoading();
                        if (status === 'abort') return;
                        console.error('[SDT] Filter AJAX error:', status);
                    }
                });
            }

            // ── Init ──
            $(document).ready(function() {

                // Khi đổi dropdown loại → gọi AJAX filter
                $(document).on('change', '#dvls_store_type', function() {
                    sdtFilterByType($(this).val());
                });

                // Khi đổi tỉnh/quận mà đang filter type → re-trigger AJAX
                $(document).on('change', '#dvls_city, #dvls_district', function() {
                    var currentType = $('#dvls_store_type').val();
                    if (currentType) {
                        // Invalidate cache vì city/district đã đổi
                        sdtOriginalHTML = null;
                        sdtOriginalCount = null;
                        sdtFilterByType(currentType);
                    }
                });

                // Intercept search form submit khi có store_type
                $(document).on('submit', '.localstore_search_form', function(e) {
                    var currentType = $('#dvls_store_type').val();
                    if (currentType) {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        sdtOriginalHTML = null;
                        sdtOriginalCount = null;
                        sdtFilterByType(currentType);
                        return false;
                    }
                });

                // Intercept search button click khi có store_type
                $(document).on('click', '.localstore_search_form .dvls_search_icon, .localstore_search_form button[type="submit"]', function(e) {
                    var currentType = $('#dvls_store_type').val();
                    if (currentType) {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        sdtOriginalHTML = null;
                        sdtOriginalCount = null;
                        sdtFilterByType(currentType);
                        return false;
                    }
                });

                // Khi plugin load xong AJAX (search theo city/district) → reset trạng thái
                $(document).on(
                    'localstore_after_render_layer after_render_local_store after_load_local_store after_set_store_html_local_store',
                    function() {
                        // Plugin đã reload data → invalidate original cache
                        sdtOriginalHTML = null;
                        sdtOriginalCount = null;

                        // Nếu đang filter theo type → re-apply filter
                        var currentType = $('#dvls_store_type').val();
                        if (currentType) {
                            setTimeout(function() {
                                sdtFilterByType(currentType);
                            }, 200);
                        } else {
                            setTimeout(sdtFixDirectionsLinks, 300);
                        }
                    }
                );

                // Fix links on initial load
                setTimeout(sdtFixDirectionsLinks, 1000);
            });

        })(jQuery);
    </script>
<?php
}




// ════════════════════════════════════════════════════════════
//  5. HIỂN THỊ BADGE LOẠI CỬA HÀNG TRÊN MỖI KẾT QUẢ
//     Hook: localstore_after_title (sau tên cửa hàng)
// ════════════════════════════════════════════════════════════

add_action('localstore_after_title', 'sdt_display_store_type_badge', 10, 2);
function sdt_display_store_type_badge($postid, $thisClass)
{
    $terms = get_the_terms($postid, 'store_type');
    if (!$terms || is_wp_error($terms)) return;

    foreach ($terms as $term) {
        $slug  = sanitize_html_class($term->slug);
        $label = esc_html($term->name);
        echo '<span class="sdt-badge sdt-badge--' . $slug . '">' . $label . '</span>';
    }
}

// ════════════════════════════════════════════════════════════
//  6. KHI SYNC TỪ API: TỰ ĐỘNG GÁN LOẠI THEO dealer level
//     Mapping: AuthorizedDealer → Đại Lý
//              AuthorizedStore  → Cửa Hàng Uỷ Quyền
//              RetailStore      → Cửa Hàng
//     Hook vào save_post của CPT devvn_local_store
//     (dành cho sync thủ công hoặc nhập tay)
// ════════════════════════════════════════════════════════════

add_action('save_post_local_store', 'sdt_auto_assign_from_api_level', 20, 2);
function sdt_auto_assign_from_api_level($post_id, $post)
{
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    // Chỉ chạy khi lưu qua sync API (có meta _spl_dealer_level)
    $level_code = get_post_meta($post_id, '_spl_dealer_level', true);
    if (empty($level_code)) return;

    // Kiểm tra đã có taxonomy chưa — nếu đã gán thủ công thì không override
    $existing = get_the_terms($post_id, 'store_type');
    if (!empty($existing) && !is_wp_error($existing)) return;

    $level_map = [
        'AuthorizedDealer' => 'dai-ly-uy-quyen',
        'AuthorizedStore'  => 'cua-hang-uy-quyen',
    ];

    $slug = $level_map[$level_code] ?? '';
    if (empty($slug)) return;

    $term = get_term_by('slug', $slug, 'store_type');
    if ($term) {
        wp_set_object_terms($post_id, [$term->term_id], 'store_type', false);
    }
}

// ════════════════════════════════════════════════════════════
//  7. THÊM NÚT "GÁN LOẠI TỪ API" TRONG ADMIN — BULK ACTION
// ════════════════════════════════════════════════════════════

add_filter('bulk_actions-edit-local_store', 'sdt_add_bulk_action');
function sdt_add_bulk_action($actions)
{
    $actions['sdt_assign_from_api'] = 'Gán loại từ level API';
    return $actions;
}

add_filter('handle_bulk_actions-edit-local_store', 'sdt_handle_bulk_action', 10, 3);
function sdt_handle_bulk_action($redirect_to, $action, $post_ids)
{
    if ($action !== 'sdt_assign_from_api') return $redirect_to;

    $level_map = [
        'AuthorizedDealer' => 'dai-ly-uy-quyen',
        'AuthorizedStore'  => 'cua-hang-uy-quyen'

    ];

    $count = 0;
    foreach ($post_ids as $post_id) {
        $level_code = get_post_meta($post_id, '_spl_dealer_level', true);
        $slug = $level_map[$level_code] ?? '';
        if (!$slug) continue;

        $term = get_term_by('slug', $slug, 'store_type');
        if ($term) {
            wp_set_object_terms($post_id, [$term->term_id], 'store_type', false);
            $count++;
        }
    }

    return add_query_arg('sdt_assigned', $count, $redirect_to);
}

add_action('admin_notices', 'sdt_bulk_action_notice');
function sdt_bulk_action_notice()
{
    if (empty($_GET['sdt_assigned'])) return;
    $count = (int) $_GET['sdt_assigned'];
    echo '<div class="notice notice-success is-dismissible"><p>Đã gán loại cho <strong>' . $count . '</strong> cửa hàng.</p></div>';
}

// ════════════════════════════════════════════════════════════
//  8. CSS — BADGE + DROPDOWN
// ════════════════════════════════════════════════════════════

add_action('wp_head', 'sdt_frontend_styles');
function sdt_frontend_styles()
{
    // Chỉ load khi có shortcode localstore trên trang
    if (!is_page()) return;
    global $post;
    if (!$post || strpos($post->post_content, 'local_store') === false) return;
?>
    <style id="sdt-styles">
        /* ── Dropdown loại cửa hàng — giống style select gốc của plugin ── */
        .dvls_store_type_box {
            max-width: 100%;
            flex-basis: 100%;
        }

        .dvls_store_type_box select {
            flex: 1;
            min-width: 0;
            width: 100%;
            border: 1px solid #fff;
            border-radius: 0;
            background-color: #ffffff66;
            height: 40px;
            padding: 0 10px;
            font-size: 14px;
            outline: none;
            margin: 0;
            box-shadow: none;
            color: inherit;
            cursor: pointer;
        }

        .dvls_store_type_box select:focus {
            box-shadow: none;
            background-color: #ffffff66;
        }

        /* ── Badge loại ── */
        .sdt-badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 20px;
            margin-left: 6px;
            vertical-align: middle;
            white-space: nowrap;
            letter-spacing: 0.3px;
        }

        .sdt-badge--cua-hang {
            background: #e3f2fd;
            color: #1565c0;
            border: 1px solid #90caf9;
        }

        .sdt-badge--dai-ly {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #a5d6a7;
        }

        .sdt-badge--cua-hang-uy-quyen {
            background: #fff3e0;
            color: #e65100;
            border: 1px solid #ffcc80;
            margin-bottom: 14px;
        }

        /* Badge mặc định cho các loại custom */
        .sdt-badge:not([class*="--cua-hang"]):not([class*="--dai-ly"]) {
            background: #f3e5f5;
            color: #6a1b9a;
            border: 1px solid #ce93d8;
        }

        .sdt-badge--dai-ly-uy-quyen {
            background: #e8f5e9;
            color: #1b5e20;
            border: 1px solid #81c784;
            margin-bottom: 14px;
        }
    </style>
<?php
}

// ════════════════════════════════════════════════════════════
//  8b. SHORTCODE [sdt_store_grid] — Grid cửa hàng với tab lọc
// ════════════════════════════════════════════════════════════

add_shortcode('sdt_store_grid', 'sdt_render_store_grid');
function sdt_render_store_grid($atts)
{
    $atts = shortcode_atts(['columns' => 3], $atts);

    // ── Query tất cả stores ──────────────────────────────────
    $posts = get_posts([
        'post_type'      => 'local_store',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ]);
    if (empty($posts)) return '<p>Chưa có cửa hàng nào.</p>';

    // ── Thu thập tỉnh (có store) ─────────────────────────────
    $province_map = []; // slug => name
    foreach ($posts as $p) {
        $states = get_the_terms($p->ID, 'local_store_state');
        if ($states && !is_wp_error($states)) {
            foreach ($states as $s) {
                $province_map[$s->slug] = $s->name;
            }
        }
    }
    asort($province_map);

    // ── Build cards ──────────────────────────────────────────
    ob_start();
?>
    <div class="sdt-grid-section">

        <div class="sdt-filter-bar">
            <?php /* --- Tab tỉnh (chỉ text, bỏ emoji/icon) --- */ ?>
            <div class="sdt-tabs-scroll-wrap">
                <button class="sdt-scroll-arrow sdt-scroll-left" aria-label="Scroll left">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div class="sdt-province-tabs" role="tablist" id="sdt-province-tabs">
                    <button class="sdt-tab active" data-filter-province="all"
                        aria-selected="true">Tất cả</button>
                    <?php foreach ($province_map as $slug => $name):
                        // Bỏ emoji và ký tự unicode icon khỏi tên tỉnh
                        $clean_name = trim(preg_replace('/[\x{1F000}-\x{1FFFF}\x{2600}-\x{27BF}\x{1F300}-\x{1F9FF}]/u', '', $name));
                    ?>
                        <button class="sdt-tab" data-filter-province="<?php echo esc_attr($slug); ?>"
                            aria-selected="false">
                            <?php echo esc_html($clean_name); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
                <button class="sdt-scroll-arrow sdt-scroll-right" aria-label="Scroll right">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>

            <?php /* --- Tab loại cửa hàng — underline style với icon --- */ ?>
            <div class="sdt-type-tabs">
                <button class="sdt-type-btn active" data-filter-type="dai-ly-uy-quyen">
                    <i class="fas fa-user-tie"></i> Đại lý ủy quyền
                </button>
                <button class="sdt-type-btn" data-filter-type="cua-hang-uy-quyen">
                    <i class="fas fa-shield-alt"></i> Cửa hàng ủy quyền
                </button>
            </div>
        </div>

        <?php /* --- Grid --- */ ?>
        <div class="sdt-grid" id="sdt-store-grid">
            <?php foreach ($posts as $p):
                $id       = $p->ID;
                $address  = get_post_meta($id, 'localstore_address', true);
                $phone    = get_post_meta($id, 'localstore_phone',   true);
                $hotline  = get_post_meta($id, 'localstore_hotline', true);
                $lat      = (float) get_post_meta($id, 'localstore_maps_lat', true);
                $lng      = (float) get_post_meta($id, 'localstore_maps_lng', true);
                $img      = get_the_post_thumbnail_url($id, 'medium') ?: '';
                $permalink = get_permalink($id);

                // Taxonomy
                $states   = get_the_terms($id, 'local_store_state');
                $types    = get_the_terms($id, 'store_type');
                $province_slug = ($states && !is_wp_error($states)) ? $states[0]->slug : 'unknown';
                $type_slug     = ($types  && !is_wp_error($types))  ? $types[0]->slug  : '';
                $type_name     = ($types  && !is_wp_error($types))  ? $types[0]->name  : '';

                // Map URL — format chỉ đường chuẩn api=1
                $valid_coords = ($lat >= 8 && $lat <= 24 && $lng >= 100 && $lng <= 112);
                if ($valid_coords) {
                    $map_url = "https://www.google.com/maps/dir/?api=1&destination={$lat}%2C{$lng}";
                } else {
                    $map_url = 'https://www.google.com/maps/dir/?api=1&destination=' . urlencode($address);
                }
            ?>
                <div class="sdt-card"
                    data-province="<?php echo esc_attr($province_slug); ?>"
                    data-type="<?php echo esc_attr($type_slug); ?>">

                    <?php if ($img): ?>
                        <a href="<?php echo esc_url($permalink); ?>" class="sdt-card__img">
                            <img src="<?php echo esc_url($img); ?>"
                                alt="<?php echo esc_attr($p->post_title); ?>"
                                loading="lazy" />
                        </a>
                    <?php endif; ?>

                    <div class="sdt-card__body">
                        <h3 class="sdt-card__name"><a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($p->post_title); ?></a></h3>

                        <?php if ($type_name): ?>
                            <span class="sdt-badge sdt-badge--<?php echo esc_attr($type_slug); ?>">
                                <?php echo esc_html($type_name); ?>
                            </span>
                        <?php endif; ?>

                        <div class="sdt-card__contacts">
                            <?php if ($phone): ?>
                                <a class="sdt-contact sdt-contact--phone" href="tel:<?php echo esc_attr(preg_replace('/\D/', '', $phone)); ?>">
                                    <span class="sdt-contact__icon"><i class="fas fa-phone-alt"></i></span>
                                    <span><?php echo esc_html($phone); ?></span>
                                </a>
                            <?php endif; ?>
                            <?php if ($hotline && $hotline !== $phone): ?>
                                <a class="sdt-contact sdt-contact--hotline" href="tel:<?php echo esc_attr(preg_replace('/\D/', '', $hotline)); ?>">
                                    <span class="sdt-contact__icon"><i class="fas fa-headset"></i></span>
                                    <span><?php echo esc_html($hotline); ?></span>
                                </a>
                            <?php endif; ?>
                        </div>

                        <div class="sdt-card__actions">
                            <a class="sdt-detail-btn" href="<?php echo esc_url($permalink); ?>">
                                <i class="fas fa-info-circle"></i>
                                Xem chi tiết
                            </a>
                            <a class="sdt-map-btn" href="<?php echo esc_url($map_url); ?>"
                                target="_blank" rel="noopener">
                                <i class="fas fa-map-marker-alt"></i>
                                Chỉ đường
                            </a>
                        </div>

                        <?php if ($address): ?>
                            <p class="sdt-card__address">
                                <i class="fas fa-map-pin"></i>
                                <?php echo esc_html($address); ?>

                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <p class="sdt-no-result" id="sdt-no-result" style="display:none;">
            Không tìm thấy cửa hàng phù hợp.
        </p>
    </div>

    <style>
        /* ── Wrapper ───────────────────────────────────────────── */
        .sdt-grid-section {
            margin: 0 auto;
            padding: 0 16px 48px;

        }

        /* ── Filter bar wrapper — sticky + blur ───────────────── */
        .sdt-filter-bar {
            position: sticky;
            top: 0;
            z-index: 10;
            background: rgba(255, 255, 255, .92);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-bottom: 1px solid #e8e8e8;
            padding: 12px 0 0;
            margin-bottom: 24px;
        }

        /* ── Province tabs wrapper với nút arrow ─────────────────── */
        .sdt-tabs-scroll-wrap {
            display: flex;
            align-items: center;
            gap: 4px;
            position: relative;
        }

        .sdt-scroll-arrow {
            flex-shrink: 0;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: 1.5px solid #ddd;
            background: #fff;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: #555;
            transition: all .16s ease;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .08);
        }

        .sdt-scroll-arrow:hover {
            border-color: #1565c0;
            color: #1565c0;
        }

        .sdt-scroll-arrow.hidden {
            opacity: 0;
            pointer-events: none;
        }

        /* ── Province tabs — scroll ngang ──────────────────────── */
        .sdt-province-tabs {
            display: flex;
            gap: 6px;
            flex: 1;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            padding-bottom: 12px;
            flex-wrap: nowrap;
            scroll-behavior: smooth;
        }

        /* Scrollbar mỏng 2px để nhận biết có thể scroll */
        .sdt-province-tabs {
            scrollbar-width: thin;
            scrollbar-color: #bbcef0 #f0f0f0;
        }

        .sdt-province-tabs::-webkit-scrollbar {
            height: 1px;
        }

        .sdt-province-tabs::-webkit-scrollbar-track {
            background: #f0f0f0;
            border-radius: 999px;
        }

        .sdt-province-tabs::-webkit-scrollbar-thumb {
            background: #90aee0;
            border-radius: 999px;
        }

        .sdt-province-tabs::-webkit-scrollbar-thumb:hover {
            background: #1565c0;
        }

        .sdt-tab {
            padding: 6px 14px;
            border: none;
            border-radius: 999px;
            background: #f0f0f0;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: .4px;
            text-transform: uppercase;
            transition: all .16s ease;
            white-space: nowrap;
            color: #666;
            flex-shrink: 0;
        }

        .sdt-tab:hover {
            background: #e0e8ff;
            color: #1565c0;
        }

        .sdt-tab.active {
            background: #1565c0;
            color: #fff;
            box-shadow: 0 2px 8px rgba(21, 101, 192, .4);
        }

        /* ── Type filter — underline tab style ──────────────────── */
        .sdt-type-tabs {
            display: flex;
            gap: 0;
            border-bottom: 2px solid #e8e8e8;
            margin: 4px 0 0;
            padding: 0;
        }

        .sdt-type-btn {
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 10px 18px 11px;
            border: none;
            border-bottom: 2.5px solid transparent;
            background: transparent;
            cursor: pointer;
            font-size: 13.5px;
            font-weight: 600;
            transition: all .16s ease;
            color: #888;
            white-space: nowrap;
            margin-bottom: -2px;
            /* overlap container border */
        }

        .sdt-type-btn i {
            font-size: 13px;
        }

        .sdt-type-btn:hover {
            color: #555;
        }

        .sdt-type-btn.active {
            color: #1565c0;
            border-bottom-color: #1565c0;
        }

        .sdt-type-btn.active[data-filter-type="cua-hang-uy-quyen"] {
            color: #e65100;
            border-bottom-color: #e65100;
        }

        .sdt-type-btn.active[data-filter-type="dai-ly-uy-quyen"] {
            color: #1b5e20;
            border-bottom-color: #1b5e20;
        }

        /* ── Grid ──────────────────────────────────────────────── */
        .sdt-grid {
            display: grid;
            grid-template-columns: repeat(<?php echo (int)$atts['columns']; ?>, 1fr);
            gap: 22px;
        }

        @media (max-width: 960px) {
            .sdt-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 580px) {
            .sdt-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }
        }

        /* ── Card ──────────────────────────────────────────────── */
        .sdt-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .06), 0 4px 12px rgba(0, 0, 0, .06);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform .2s ease, box-shadow .2s ease;
            border-top: 3px solid #e0e0e0;
            /* override per type below */
        }

        .sdt-card[data-type="cua-hang-uy-quyen"] {
            border-top-color: #e65100;
        }

        .sdt-card[data-type="dai-ly-uy-quyen"] {
            border-top-color: #1b5e20;
        }

        .sdt-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 28px rgba(0, 0, 0, .12);
        }

        /* Image — always same ratio, placeholder if no image */
        .sdt-card__img {
            aspect-ratio: 16/10;
            overflow: hidden;
            position: relative;
            background: linear-gradient(135deg, #e8f0fe 0%, #c5d8fa 100%);
        }

        .sdt-card__img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform .35s ease;
        }

        .sdt-card:hover .sdt-card__img img {
            transform: scale(1.04);
        }

        /* Placeholder icon khi không có ảnh */
        .sdt-card__img--placeholder::after {
            content: '🏪';
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            opacity: .4;
        }

        /* Body */
        .sdt-card__body {
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            flex: 1;
        }

        .sdt-card__name {
            font-size: 14.5px;
            font-weight: 700;
            margin: 0;
            color: #1a237e;
            line-height: 1.35;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Badge — nhỏ gọn */
        .sdt-card__body .sdt-badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: .3px;
            width: fit-content;
        }

        .sdt-badge--cua-hang-uy-quyen {
            background: #fff3e0;
            color: #e65100;
            border: 1px solid #ffcc80;
        }

        .sdt-badge--dai-ly-uy-quyen {
            background: #e8f5e9;
            color: #1b5e20;
            border: 1px solid #81c784;
        }

        /* Contacts — 1 hàng ngang với divider */
        .sdt-card__contacts {
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 0;
            flex-wrap: wrap;
        }

        .sdt-contact {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #333;
            text-decoration: none;
            font-weight: 500;
            padding: 4px 10px 4px 0;
        }

        .sdt-contact+.sdt-contact {
            border-left: 1px solid #ddd;
            padding-left: 10px;
        }

        .sdt-contact:hover {
            color: #1565c0;
        }

        .sdt-contact__icon {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 10px;
        }

        .sdt-contact--phone .sdt-contact__icon {
            background: #e8f0fe;
            color: #1565c0;
        }

        .sdt-contact--hotline .sdt-contact__icon {
            background: #fff3e0;
            color: #e65100;
        }

        /* Actions row — 2 buttons side by side */
        .sdt-card__actions {
            margin-top: auto;
            display: flex;
            gap: 8px;
        }

        .sdt-detail-btn,
        .sdt-map-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 9px 0;
            border-radius: 8px;
            flex: 1;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            transition: all .18s ease;
            letter-spacing: .3px;
        }

        .sdt-detail-btn {
            background: #f5f5f5;
            color: #333;
            border: 1.5px solid #e0e0e0;
        }

        .sdt-detail-btn:hover {
            background: #1565c0;
            color: #fff;
            border-color: #1565c0;
        }

        .sdt-map-btn {
            background: #1565c0;
            color: #fff;
        }

        .sdt-map-btn:hover {
            background: #0d47a1;
            color: #fff;
        }

        /* Card name link */
        .sdt-card__name a {
            color: inherit;
            text-decoration: none;
            transition: color .15s ease;
        }
        .sdt-card__name a:hover {
            color: #1565c0;
        }

        .sdt-card[data-type="dai-ly-uy-quyen"] .sdt-map-btn {
            background: #1b5e20;
        }

        .sdt-card[data-type="dai-ly-uy-quyen"] .sdt-map-btn:hover {
            background: #145214;
        }

        .sdt-card[data-type="cua-hang-uy-quyen"] .sdt-map-btn {
            background: #e65100;
        }

        .sdt-card[data-type="cua-hang-uy-quyen"] .sdt-map-btn:hover {
            background: #bf360c;
        }

        /* Address */
        .sdt-card__address {
            font-size: 12px;
            color: #888;
            margin: 0;
            line-height: 1.5;
            display: flex;
            gap: 6px;
            align-items: flex-start;
            border-top: 1px solid #f0f0f0;
            padding-top: 8px;
        }

        .sdt-card__address i {
            color: #bbb;
            margin-top: 2px;
            flex-shrink: 0;
        }

        /* No result */
        .sdt-no-result {
            text-align: center;
            color: #aaa;
            padding: 60px 0;
            font-size: 15px;
        }
    </style>


    <script>
        (function() {
            var activeProvince = 'all',
                activeType = 'dai-ly-uy-quyen';

            function filterGrid() {
                var cards = document.querySelectorAll('#sdt-store-grid .sdt-card');
                var visible = 0;
                cards.forEach(function(c) {
                    var mp = activeProvince === 'all' || c.dataset.province === activeProvince;
                    var mt = activeType === 'all' || c.dataset.type === activeType;
                    c.style.display = (mp && mt) ? '' : 'none';
                    if (mp && mt) visible++;
                });
                var noResult = document.getElementById('sdt-no-result');
                if (noResult) noResult.style.display = visible === 0 ? '' : 'none';
            }

            document.addEventListener('DOMContentLoaded', function() {
                // Lọc ngay khi load theo tab active mặc định
                filterGrid();

                // ── Scroll arrows cho province tabs ──────────────
                var tabsEl = document.getElementById('sdt-province-tabs');
                var btnLeft = document.querySelector('.sdt-scroll-left');
                var btnRight = document.querySelector('.sdt-scroll-right');

                function updateArrows() {
                    if (!tabsEl || !btnLeft || !btnRight) return;
                    btnLeft.classList.toggle('hidden', tabsEl.scrollLeft <= 4);
                    btnRight.classList.toggle('hidden', tabsEl.scrollLeft + tabsEl.clientWidth >= tabsEl.scrollWidth - 4);
                }

                if (tabsEl) {
                    updateArrows();
                    tabsEl.addEventListener('scroll', updateArrows);

                    // Mouse wheel → horizontal scroll
                    tabsEl.addEventListener('wheel', function(e) {
                        if (e.deltaY !== 0) {
                            e.preventDefault();
                            tabsEl.scrollLeft += e.deltaY;
                        }
                    }, {
                        passive: false
                    });

                    if (btnLeft) btnLeft.addEventListener('click', function() {
                        tabsEl.scrollLeft -= 220;
                    });
                    if (btnRight) btnRight.addEventListener('click', function() {
                        tabsEl.scrollLeft += 220;
                    });
                }

                // Province tabs
                document.querySelectorAll('.sdt-tab').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        document.querySelectorAll('.sdt-tab').forEach(function(b) {
                            b.classList.remove('active');
                            b.setAttribute('aria-selected', 'false');
                        });
                        this.classList.add('active');
                        this.setAttribute('aria-selected', 'true');
                        activeProvince = this.dataset.filterProvince;
                        filterGrid();
                    });
                });

                // Type tabs
                document.querySelectorAll('.sdt-type-btn').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        document.querySelectorAll('.sdt-type-btn').forEach(function(b) {
                            b.classList.remove('active');
                        });
                        this.classList.add('active');
                        activeType = this.dataset.filterType;
                        filterGrid();
                    });
                });
            });
        })();
    </script>
<?php
    return ob_get_clean();
}

// ════════════════════════════════════════════════════════════
//  9. IMPORT / EXPORT CSV
// ════════════════════════════════════════════════════════════

// ── 9a. Admin Menu ──────────────────────────────────────────
add_action('admin_menu', 'sdt_add_import_export_menu');
function sdt_add_import_export_menu()
{
    add_submenu_page(
        'edit.php?post_type=local_store',
        'Import / Export Cửa Hàng',
        '📥 Import / Export',
        'manage_options',
        'sdt-import-export',
        'sdt_import_export_page'
    );
}

function sdt_import_export_page()
{
    $msg    = sanitize_text_field($_GET['sdt_msg']    ?? '');
    $count  = (int)($_GET['count']  ?? 0);
    $detail = sanitize_text_field($_GET['detail'] ?? '');
    $tmpl_url = wp_nonce_url(
        admin_url('admin-post.php?action=sdt_export_template'),
        'sdt_export',
        'sdt_nonce'
    );
?>
    <div class="wrap">
        <h1 style="display:flex;align-items:center;gap:8px;">📥 Import / Export Cửa Hàng</h1>

        <?php if ($msg === 'imported'): ?>
            <div class="notice notice-success is-dismissible">
                <p>✅ Import thành công <strong><?php echo $count; ?></strong> cửa hàng.</p>
            </div>
        <?php elseif ($msg === 'updated'): ?>
            <div class="notice notice-info is-dismissible">
                <p>🔄 Đã cập nhật <strong><?php echo $count; ?></strong> cửa hàng.</p>
            </div>
        <?php elseif ($msg === 'synced'): ?>
            <div class="notice notice-success is-dismissible">
                <p>✅ Đã đồng bộ loại cho <strong><?php echo $count; ?></strong> cửa hàng.</p>
            </div>
        <?php elseif ($msg === 'error'): ?>
            <div class="notice notice-error is-dismissible">
                <p>❌ Lỗi: <?php echo esc_html($detail); ?></p>
            </div>
        <?php endif; ?>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;max-width:1000px;margin-top:16px;">

            <!-- EXPORT -->
            <div class="card" style="padding:20px;">
                <h2 style="margin-top:0;">📤 Export CSV</h2>
                <p>Tải toàn bộ danh sách cửa hàng hiện có ra file CSV.</p>
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                    <?php wp_nonce_field('sdt_export', 'sdt_nonce'); ?>
                    <input type="hidden" name="action" value="sdt_export_csv">
                    <button type="submit" class="button button-primary button-large">⬇ Tải xuống CSV</button>
                </form>
                <p style="margin-top:12px;"><a href="<?php echo esc_url($tmpl_url); ?>">📄 Tải file CSV mẫu (template)</a></p>
            </div>

            <!-- IMPORT -->
            <div class="card" style="padding:20px;">
                <h2 style="margin-top:0;">📥 Import CSV</h2>
                <p>Upload file CSV để thêm hoặc cập nhật cửa hàng hàng loạt.</p>
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" enctype="multipart/form-data">
                    <?php wp_nonce_field('sdt_import', 'sdt_nonce'); ?>
                    <input type="hidden" name="action" value="sdt_import_csv">
                    <table class="form-table" style="margin:0;">
                        <tr>
                            <th style="padding:8px 0;">File CSV</th>
                            <td><input type="file" name="sdt_csv_file" accept=".csv" required></td>
                        </tr>
                        <tr>
                            <th style="padding:8px 0;">Nếu trùng mã/tên</th>
                            <td>
                                <select name="sdt_duplicate">
                                    <option value="skip">Bỏ qua</option>
                                    <option value="update">Cập nhật</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                    <button type="submit" class="button button-primary button-large" style="margin-top:12px;">⬆ Bắt đầu Import</button>
                </form>
            </div>
        </div>

        <!-- SYNC TYPE FROM TITLE -->
        <div class="card" style="max-width:1000px;margin-top:20px;padding:20px;border-left:4px solid #2271b1;">
            <h2 style="margin-top:0;">🔄 Đồng bộ Loại từ Tiêu đề</h2>
            <p>Tự động gán loại dựa theo tên cửa hàng:</p>
            <ul style="margin:0 0 12px 20px;">
                <li><strong>Bắt đầu bằng "Đại lý"</strong> → <code>dai-ly-uy-quyen</code></li>
                <li><strong>Bắt đầu bằng "Cửa hàng"</strong> → <code>cua-hang-uy-quyen</code></li>
            </ul>
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <?php wp_nonce_field('sdt_sync_type', 'sdt_nonce'); ?>
                <input type="hidden" name="action" value="sdt_sync_type_from_title">
                <label style="display:block;margin-bottom:10px;">
                    <input type="checkbox" name="sdt_override" value="1">
                    Ghi đè cả những cửa hàng đã có loại
                </label>
                <button type="submit" class="button button-secondary button-large">🔄 Đồng bộ ngay</button>
            </form>
        </div>

        <!-- FORMAT GUIDE -->
        <div class="card" style="max-width:1000px;margin-top:20px;padding:20px;">
            <h3 style="margin-top:0;">📋 Cấu trúc cột CSV</h3>
            <table class="widefat striped" style="font-size:13px;">
                <thead>
                    <tr>
                        <th>Tên cột</th>
                        <th>Bắt buộc</th>
                        <th>Mô tả</th>
                        <th>Ví dụ</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>ma_cua_hang</code></td>
                        <td>—</td>
                        <td>Mã định danh, dùng để match khi update</td>
                        <td>KH027</td>
                    </tr>
                    <tr>
                        <td><code>ten_cua_hang</code></td>
                        <td>✅</td>
                        <td>Tên cửa hàng (post title)</td>
                        <td>BLUERA Tại Dùng</td>
                    </tr>
                    <tr>
                        <td><code>dia_chi</code></td>
                        <td>—</td>
                        <td>Địa chỉ đầy đủ</td>
                        <td>Ấp An Hòa, Xã An Hòa, An Giang</td>
                    </tr>
                    <tr>
                        <td><code>dien_thoai</code></td>
                        <td>—</td>
                        <td>Số điện thoại chính</td>
                        <td>0975131218</td>
                    </tr>
                    <tr>
                        <td><code>hotline</code></td>
                        <td>—</td>
                        <td>Hotline (nếu khác phone)</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td><code>email</code></td>
                        <td>—</td>
                        <td>Email liên hệ</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td><code>gio_mo_cua</code></td>
                        <td>—</td>
                        <td>Giờ mở cửa</td>
                        <td>8:00 - 17:30</td>
                    </tr>
                    <tr>
                        <td><code>website</code></td>
                        <td>—</td>
                        <td>Link website hoặc fanpage</td>
                        <td>https://...</td>
                    </tr>
                    <tr>
                        <td><code>lat</code></td>
                        <td>—</td>
                        <td>Vĩ độ GPS</td>
                        <td>10.8231</td>
                    </tr>
                    <tr>
                        <td><code>lng</code></td>
                        <td>—</td>
                        <td>Kinh độ GPS</td>
                        <td>106.6297</td>
                    </tr>
                    <tr>
                        <td><code>tinh_thanh</code></td>
                        <td>✅</td>
                        <td>Tên tỉnh/thành phố (khớp taxonomy tỉnh của plugin)</td>
                        <td>An Giang</td>
                    </tr>
                    <tr>
                        <td><code>loai_cua_hang</code></td>
                        <td>—</td>
                        <td><code>cua-hang</code> | <code>dai-ly-uy-quyen</code> | <code>cua-hang-uy-quyen</code></td>
                        <td>cua-hang-uy-quyen</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
<?php
}

// ── 9b. Export CSV ───────────────────────────────────────────
add_action('admin_post_sdt_export_csv', 'sdt_handle_export_csv');
function sdt_handle_export_csv()
{
    if (!current_user_can('manage_options')) wp_die('Unauthorized');
    check_admin_referer('sdt_export', 'sdt_nonce');

    $posts = get_posts([
        'post_type'      => 'local_store',
        'post_status'    => ['publish', 'draft'],
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ]);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="cua-hang-' . date('Y-m-d') . '.csv"');
    header('Cache-Control: no-cache, no-store');
    $out = fopen('php://output', 'w');
    fputs($out, "\xEF\xBB\xBF"); // BOM → Excel đọc được UTF-8

    fputcsv($out, [
        'ma_cua_hang',
        'ten_cua_hang',
        'dia_chi',
        'dien_thoai',
        'hotline',
        'email',
        'gio_mo_cua',
        'website',
        'lat',
        'lng',
        'tinh_thanh',
        'loai_cua_hang',
        'featured_image_url'
    ]);

    foreach ($posts as $p) {
        $m     = get_post_meta($p->ID);
        $state = get_the_terms($p->ID, 'local_store_state');
        $type  = get_the_terms($p->ID, 'store_type');
        fputcsv($out, [
            get_post_meta($p->ID, '_sdt_store_code', true),
            $p->post_title,
            $m['localstore_address'][0]  ?? '',
            $m['localstore_phone'][0]    ?? '',
            $m['localstore_hotline'][0]  ?? '',
            $m['localstore_email'][0]    ?? '',
            $m['localstore_open'][0]     ?? '',
            $m['localstore_link_to'][0]  ?? '',
            $m['localstore_maps_lat'][0] ?? '',
            $m['localstore_maps_lng'][0] ?? '',
            (!is_wp_error($state) && $state) ? $state[0]->name : '',
            (!is_wp_error($type)  && $type)  ? $type[0]->slug  : '',
            get_the_post_thumbnail_url($p->ID, 'full') ?: '',
        ]);
    }
    fclose($out);
    exit;
}

// ── 9c. Export Template ──────────────────────────────────────
add_action('admin_post_sdt_export_template', 'sdt_handle_export_template');
function sdt_handle_export_template()
{
    if (!current_user_can('manage_options')) wp_die('Unauthorized');
    check_admin_referer('sdt_export', 'sdt_nonce');

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="template-import-cua-hang.csv"');
    $out = fopen('php://output', 'w');
    fputs($out, "\xEF\xBB\xBF");
    fputcsv($out, [
        'ma_cua_hang',
        'ten_cua_hang',
        'dia_chi',
        'dien_thoai',
        'hotline',
        'email',
        'gio_mo_cua',
        'website',
        'lat',
        'lng',
        'tinh_thanh',
        'loai_cua_hang',
        'featured_image_url'
    ]);
    fputcsv($out, [
        'KH027',
        'BLUERA Tài Dùng',
        'Ấp An Hòa, Xã An Hòa, Tịnh Biên, An Giang',
        '0975131218',
        '',
        '',
        '8:00 - 17:30',
        '',
        '',
        '',
        'An Giang',
        'cua-hang-uy-quyen',
        'https://dailyxedien.vn/wp-content/uploads/2025/12/z7295601335572_7f2a4c7d95dc282bc-1.jpg'
    ]);
    fclose($out);
    exit;
}

// ── 9d. Import CSV ───────────────────────────────────────────
add_action('admin_post_sdt_import_csv', 'sdt_handle_import_csv');
function sdt_handle_import_csv()
{
    if (!current_user_can('manage_options')) wp_die('Unauthorized');
    check_admin_referer('sdt_import', 'sdt_nonce');

    $redirect = admin_url('edit.php?post_type=local_store&page=sdt-import-export');

    if (empty($_FILES['sdt_csv_file']['tmp_name'])) {
        wp_redirect(add_query_arg(['sdt_msg' => 'error', 'detail' => 'Không tìm thấy file'], $redirect));
        exit;
    }

    $duplicate = sanitize_text_field($_POST['sdt_duplicate'] ?? 'skip');
    $handle    = fopen($_FILES['sdt_csv_file']['tmp_name'], 'r');
    if (!$handle) {
        wp_redirect(add_query_arg(['sdt_msg' => 'error', 'detail' => 'Không mở được file'], $redirect));
        exit;
    }

    // Bỏ BOM nếu có
    $bom = fread($handle, 3);
    if ($bom !== "\xEF\xBB\xBF") rewind($handle);

    // Đọc header
    $headers = fgetcsv($handle);
    if (!$headers) {
        fclose($handle);
        wp_redirect(add_query_arg(['sdt_msg' => 'error', 'detail' => 'File CSV rỗng hoặc sai format'], $redirect));
        exit;
    }
    $headers = array_map('trim', $headers);
    $col     = array_flip($headers);

    $created = 0;
    $updated = 0;

    while (($row = fgetcsv($handle)) !== false) {
        if (count($row) < 2) continue;

        $get = function ($key) use ($row, $col) {
            return isset($col[$key]) ? trim($row[$col[$key]] ?? '') : '';
        };

        $title = $get('ten_cua_hang');
        if (empty($title)) continue;

        $ma = $get('ma_cua_hang');

        // Tìm post hiện có theo mã hoặc tên
        $existing_id = 0;
        if ($ma) {
            $found = get_posts([
                'post_type'  => 'local_store',
                'meta_key'   => '_sdt_store_code',
                'meta_value' => $ma,
                'fields'     => 'ids',
                'numberposts' => 1,
            ]);
            if ($found) $existing_id = $found[0];
        }
        if (!$existing_id) {
            $found = get_posts([
                'post_type'  => 'local_store',
                'title'      => $title,
                'fields'     => 'ids',
                'numberposts' => 1,
            ]);
            if ($found) $existing_id = $found[0];
        }

        if ($existing_id && $duplicate === 'skip') continue;

        // Tạo hoặc update post
        $post_data = [
            'post_title'  => $title,
            'post_type'   => 'local_store',
            'post_status' => 'publish',
        ];
        if ($existing_id) {
            $post_data['ID'] = $existing_id;
            wp_update_post($post_data);
            $post_id = $existing_id;
            $updated++;
        } else {
            $post_id = wp_insert_post($post_data);
            $created++;
        }

        if (!$post_id || is_wp_error($post_id)) continue;

        // Meta fields
        $meta_map = [
            'dia_chi'    => 'localstore_address',
            'dien_thoai' => 'localstore_phone',
            'hotline'    => 'localstore_hotline',
            'email'      => 'localstore_email',
            'gio_mo_cua' => 'localstore_open',
            'website'    => 'localstore_link_to',
            'lat'        => 'localstore_maps_lat',
            'lng'        => 'localstore_maps_lng',
        ];
        foreach ($meta_map as $csv_col => $meta_key) {
            $val = $get($csv_col);
            if ($val !== '') update_post_meta($post_id, $meta_key, $val);
        }
        if ($ma) update_post_meta($post_id, '_sdt_store_code', $ma);

        // Taxonomy: Tỉnh/Thành
        $tinh = $get('tinh_thanh');
        if ($tinh) {
            $state_term = get_term_by('name', $tinh, 'local_store_state');
            if (!$state_term) $state_term = get_term_by('slug', sanitize_title($tinh), 'local_store_state');
            // Tự tạo term nếu chưa tồn tại
            if (!$state_term) {
                $new = wp_insert_term($tinh, 'local_store_state');
                if (!is_wp_error($new)) {
                    $state_term = get_term($new['term_id'], 'local_store_state');
                }
            }
            if ($state_term && !is_wp_error($state_term)) {
                wp_set_object_terms($post_id, [$state_term->term_id], 'local_store_state', false);
            }
        }

        // Taxonomy: Loại cửa hàng
        $loai = $get('loai_cua_hang');
        if ($loai) {
            $type_term = get_term_by('slug', $loai, 'store_type');
            if ($type_term) wp_set_object_terms($post_id, [$type_term->term_id], 'store_type', false);
        }

        // Featured image từ URL
        $img_url = $get('featured_image_url');
        if ($img_url && filter_var($img_url, FILTER_VALIDATE_URL)) {
            // Chỉ set nếu chưa có thumbnail (tránh override khi update)
            $has_thumb = has_post_thumbnail($post_id);
            if (!$has_thumb || $duplicate === 'update') {
                // Kiểm tra ảnh đã tồn tại trong media library chưa
                $existing_att = get_posts([
                    'post_type'  => 'attachment',
                    'meta_key'   => '_sdt_source_url',
                    'meta_value' => $img_url,
                    'fields'     => 'ids',
                    'numberposts' => 1,
                ]);
                if ($existing_att) {
                    // Dùng lại attachment đã có
                    set_post_thumbnail($post_id, $existing_att[0]);
                } else {
                    // Download và sài load nhảnh lần đầu
                    require_once(ABSPATH . 'wp-admin/includes/media.php');
                    require_once(ABSPATH . 'wp-admin/includes/file.php');
                    require_once(ABSPATH . 'wp-admin/includes/image.php');
                    $att_id = media_sideload_image($img_url, $post_id, null, 'id');
                    if (!is_wp_error($att_id)) {
                        set_post_thumbnail($post_id, $att_id);
                        update_post_meta($att_id, '_sdt_source_url', $img_url);
                    }
                }
            }
        }
    } // end while

    fclose($handle);

    $msg = ($updated > 0 && $created === 0) ? 'updated' : 'imported';
    wp_redirect(add_query_arg(['sdt_msg' => $msg, 'count' => $created + $updated], $redirect));
    exit;
}

// ── 9e. Đồng bộ loại từ tiêu đề ─────────────────────────────
add_action('admin_post_sdt_sync_type_from_title', 'sdt_handle_sync_type_from_title');
function sdt_handle_sync_type_from_title()
{
    if (!current_user_can('manage_options')) wp_die('Unauthorized');
    check_admin_referer('sdt_sync_type', 'sdt_nonce');

    $override  = !empty($_POST['sdt_override']);
    $redirect  = admin_url('edit.php?post_type=local_store&page=sdt-import-export');

    // Mapping prefix → slug
    $prefix_map = [
        'Đại lý'    => 'dai-ly-uy-quyen',
        'Cửa hàng'  => 'cua-hang-uy-quyen',
    ];

    $posts = get_posts([
        'post_type'      => 'local_store',
        'post_status'    => ['publish', 'draft'],
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ]);

    $count = 0;
    foreach ($posts as $post_id) {
        // Bỏ qua nếu đã có loại và không override
        if (!$override) {
            $existing = get_the_terms($post_id, 'store_type');
            if (!empty($existing) && !is_wp_error($existing)) continue;
        }

        $title = get_the_title($post_id);
        $slug  = '';

        foreach ($prefix_map as $prefix => $type_slug) {
            if (mb_stripos($title, $prefix) === 0) {
                $slug = $type_slug;
                break;
            }
        }

        if (!$slug) continue;

        $term = get_term_by('slug', $slug, 'store_type');
        if ($term) {
            wp_set_object_terms($post_id, [$term->term_id], 'store_type', false);
            $count++;
        }
    }

    wp_redirect(add_query_arg(['sdt_msg' => 'synced', 'count' => $count], $redirect));
    exit;
}
