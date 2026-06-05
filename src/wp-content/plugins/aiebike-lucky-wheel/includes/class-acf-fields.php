<?php

/**
 * ACF Fields Registration
 * 
 * Đăng ký các fields ACF cho trang vòng quay
 */

if (!defined('ABSPATH')) {
    exit;
}

class AIEbike_LW_ACF_Fields
{

    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('acf/init', array($this, 'register_fields'));
    }

    /**
     * Register ACF fields
     */
    public function register_fields()
    {
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }

        acf_add_local_field_group(array(
            'key' => 'group_aiebike_lucky_wheel_page',
            'title' => __('Cài đặt Vòng Quay May Mắn', 'aiebike-lucky-wheel'),
            'fields' => array(
                // Tab Header
                array(
                    'key' => 'field_aiebike_lw_tab_header',
                    'label' => __('Header', 'aiebike-lucky-wheel'),
                    'name' => '',
                    'type' => 'tab',
                ),
                array(
                    'key' => 'field_aiebike_lw_title',
                    'label' => __('Tiêu đề chính', 'aiebike-lucky-wheel'),
                    'name' => 'aiebike_lw_title',
                    'type' => 'text',
                    'default_value' => '🎯 VÒNG QUAY MAY MẮN 🎯',
                ),
                array(
                    'key' => 'field_aiebike_lw_subtitle',
                    'label' => __('Tiêu đề phụ', 'aiebike-lucky-wheel'),
                    'name' => 'aiebike_lw_subtitle',
                    'type' => 'text',
                    'default_value' => 'BÙNG NỔ MÙA HÈ - CƠ HỘI RINH XE MIỄN PHÍ',
                ),
                array(
                    'key' => 'field_aiebike_lw_prize_text',
                    'label' => __('Text giải thưởng lớn', 'aiebike-lucky-wheel'),
                    'name' => 'aiebike_lw_prize_text',
                    'type' => 'text',
                    'default_value' => 'GIẢM NGAY 1.500.000đ',
                ),
                array(
                    'key' => 'field_aiebike_lw_background',
                    'label' => __('Hình nền trang', 'aiebike-lucky-wheel'),
                    'name' => 'aiebike_lw_background',
                    'type' => 'image',
                    'return_format' => 'url',
                    'preview_size' => 'medium',
                ),
                array(
                    'key' => 'field_aiebike_lw_header_image',
                    'label' => __('Ảnh tiêu đề (thay thế text)', 'aiebike-lucky-wheel'),
                    'name' => 'aiebike_lw_header_image',
                    'type' => 'image',
                    'return_format' => 'url',
                    'preview_size' => 'large',
                    'instructions' => __('Upload ảnh banner tiêu đề. Nếu có ảnh này sẽ thay thế hoàn toàn các text tiêu đề phía trên.', 'aiebike-lucky-wheel'),
                ),

                // Tab Vòng Quay
                array(
                    'key' => 'field_aiebike_lw_tab_wheel',
                    'label' => __('Vòng Quay', 'aiebike-lucky-wheel'),
                    'name' => '',
                    'type' => 'tab',
                ),
                array(
                    'key' => 'field_aiebike_lw_custom_wheel',
                    'label' => __('Hình ảnh vòng quay (tùy chỉnh)', 'aiebike-lucky-wheel'),
                    'name' => 'aiebike_lw_custom_wheel',
                    'type' => 'image',
                    'return_format' => 'url',
                    'preview_size' => 'large',
                    'instructions' => __('Upload hình vòng quay đã thiết kế. Nếu để trống sẽ dùng vòng quay mặc định của plugin. Ảnh nên vuông (1:1), recommended 800x800 hoặc lớn hơn.', 'aiebike-lucky-wheel'),
                ),

                // Tab Giải thưởng
                array(
                    'key' => 'field_aiebike_lw_tab_prizes',
                    'label' => __('Giải thưởng', 'aiebike-lucky-wheel'),
                    'name' => '',
                    'type' => 'tab',
                ),
                array(
                    'key' => 'field_aiebike_lw_prizes_title',
                    'label' => __('Tiêu đề phần giải thưởng', 'aiebike-lucky-wheel'),
                    'name' => 'aiebike_lw_prizes_title',
                    'type' => 'text',
                    'default_value' => '🎁 CÁC GIẢI THƯỞNG HẤP DẪN',
                ),
                array(
                    'key' => 'field_aiebike_lw_prizes',
                    'label' => __('Danh sách giải thưởng', 'aiebike-lucky-wheel'),
                    'name' => 'aiebike_lw_prizes',
                    'type' => 'repeater',
                    'layout' => 'block',
                    'button_label' => __('Thêm giải thưởng', 'aiebike-lucky-wheel'),
                    'sub_fields' => array(
                        array(
                            'key' => 'field_aiebike_lw_prize_image',
                            'label' => __('Hình ảnh', 'aiebike-lucky-wheel'),
                            'name' => 'image',
                            'type' => 'image',
                            'return_format' => 'array',
                            'preview_size' => 'thumbnail',
                            'wrapper' => array('width' => '30%'),
                        ),
                        array(
                            'key' => 'field_aiebike_lw_prize_value',
                            'label' => __('Giá trị giải', 'aiebike-lucky-wheel'),
                            'name' => 'value',
                            'type' => 'text',
                            'placeholder' => 'VD: 100K, 200K...',
                            'wrapper' => array('width' => '35%'),
                        ),
                        array(
                            'key' => 'field_aiebike_lw_prize_label',
                            'label' => __('Mô tả', 'aiebike-lucky-wheel'),
                            'name' => 'label',
                            'type' => 'text',
                            'placeholder' => 'VD: Giảm giá, Voucher...',
                            'wrapper' => array('width' => '35%'),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_aiebike_lw_total_prize',
                    'label' => __('Tổng giá trị giải thưởng', 'aiebike-lucky-wheel'),
                    'name' => 'aiebike_lw_total_prize',
                    'type' => 'text',
                    'default_value' => '1.500.000đ',
                ),

                // Tab Thể lệ
                array(
                    'key' => 'field_aiebike_lw_tab_rules',
                    'label' => __('Thể lệ', 'aiebike-lucky-wheel'),
                    'name' => '',
                    'type' => 'tab',
                ),
                array(
                    'key' => 'field_aiebike_lw_rules_content',
                    'label' => __('Nội dung thể lệ', 'aiebike-lucky-wheel'),
                    'name' => 'aiebike_lw_rules_content',
                    'type' => 'wysiwyg',
                    'toolbar' => 'full',
                    'media_upload' => 0,
                ),
                array(
                    'key' => 'field_aiebike_lw_contact_info',
                    'label' => __('Thông tin liên hệ', 'aiebike-lucky-wheel'),
                    'name' => 'aiebike_lw_contact_info',
                    'type' => 'textarea',
                    'rows' => 3,
                    'default_value' => "📞 Liên hệ hỗ trợ: Hotline 1900.xxxx hoặc email support@example.com\n* Chúng tôi có quyền thay đổi thể lệ chương trình mà không cần báo trước.",
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'page',
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'active' => true,
        ));
    }
}

// Initialize
new AIEbike_LW_ACF_Fields();
