<?php
/**
 * ─── THÔNG SỐ KỸ THUẬT – ACF Repeater Group ───────────────────────────────
 * Register via PHP. Fields: tskt_rows (repeater: tskt_label|tskt_value)
 * và tskt_badges (gallery icon bảo hành, tùy chọn).
 *
 * Cấu trúc giống hệt dailyxedien.vn để có thể import/export sản phẩm
 * giữa 2 site mà không mất dữ liệu ACF.
 */

if (!defined('ABSPATH')) exit;

add_action('acf/init', 'tskt_register_acf_fields');
function tskt_register_acf_fields() {
    if (!function_exists('acf_add_local_field_group')) return;

    acf_add_local_field_group([
        'key'    => 'group_tskt_specs',
        'title'  => 'Thông Số Kỹ Thuật (Repeater)',
        'fields' => [
            [
                'key'          => 'field_tskt_rows',
                'label'        => 'Các dòng thông số',
                'name'         => 'tskt_rows',
                'type'         => 'repeater',
                'instructions' => 'Mỗi dòng: tên thông số + giá trị. Dòng đầu nên là tên model (sẽ in đậm nếu dùng <strong>).',
                'min'          => 0,
                'max'          => 0,
                'layout'       => 'table',
                'button_label' => 'Thêm dòng thông số',
                'sub_fields'   => [
                    [
                        'key'          => 'field_tskt_label',
                        'label'        => 'Thông số',
                        'name'         => 'tskt_label',
                        'type'         => 'text',
                        'placeholder'  => 'VD: Công suất',
                        'column_width' => 40,
                    ],
                    [
                        'key'          => 'field_tskt_value',
                        'label'        => 'Giá trị',
                        'name'         => 'tskt_value',
                        'type'         => 'text',
                        'placeholder'  => 'VD: 650W',
                        'column_width' => 60,
                    ],
                ],
            ],
            [
                'key'           => 'field_tskt_badges',
                'label'         => 'Icon bảo hành (tùy chọn)',
                'name'          => 'tskt_badges',
                'type'          => 'gallery',
                'instructions'  => 'Upload icon bảo hành/cam kết. Để trống nếu không cần.',
                'return_format' => 'url',
                'preview_size'  => 'thumbnail',
                'insert'        => 'append',
                'min'           => 0,
                'max'           => 10,
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'product',
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


/**
 * ─── Force hiện tab "Thông tin bổ sung" khi có ACF tskt_rows ───────────────
 * WooCommerce mặc định ẩn tab này nếu sản phẩm không có Attributes.
 * Filter bên dưới đảm bảo tab luôn hiện khi tskt_rows có dữ liệu.
 */
add_filter('woocommerce_product_tabs', 'tskt_force_show_additional_info_tab', 98);
function tskt_force_show_additional_info_tab($tabs) {
    // Nếu tab đã có → không cần làm gì
    if (isset($tabs['additional_information'])) return $tabs;

    global $product;
    if (!$product) return $tabs;

    // Kiểm tra có tskt_rows không
    $rows = function_exists('get_field') ? get_field('tskt_rows', $product->get_id()) : [];

    if (!empty($rows)) {
        $tabs['additional_information'] = [
            'title'    => __('Thông tin bổ sung', 'woocommerce'),
            'priority' => 20,
            'callback' => 'woocommerce_product_additional_information_tab',
        ];
    }

    return $tabs;
}

/**
 * ─── Enqueue TSKT CSS on single product pages ─────────────────────────────
 */
add_action('wp_enqueue_scripts', function () {
    if (is_product()) {
        wp_enqueue_style(
            'tskt-spec',
            get_stylesheet_directory_uri() . '/assets/css/tskt-spec.css',
            [],
            filemtime(get_stylesheet_directory() . '/assets/css/tskt-spec.css')
        );
    }
});

/**
 * ─── Rename additional_information tab → "Thông số kỹ thuật" ──────────────
 */
add_filter('woocommerce_product_tabs', function ($tabs) {
    if (isset($tabs['additional_information'])) {
        $tabs['additional_information']['title'] = 'Thông số kỹ thuật';
    }
    return $tabs;
}, 99);
