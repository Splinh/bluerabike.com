<?php

/**
 * ACF Popup Settings
 *
 * @author Gaudev
 */

\defined('ABSPATH') || die;

// Register ACF Options Page
if (function_exists('acf_add_options_page')) {
    acf_add_options_page([
        'page_title' => __('Popup Settings', TEXT_DOMAIN),
        'menu_title' => __('Popup Settings', TEXT_DOMAIN),
        'menu_slug'  => 'popup-settings',
        'capability' => 'edit_posts',
        'icon_url'   => 'dashicons-admin-customizer',
        'position'   => 30,
    ]);
}

// Register ACF Fields for Popup
if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group([
        'key'                   => 'group_popup_promo',
        'title'                 => __('Popup Promotional Settings', TEXT_DOMAIN),
        'fields'                => [
            // Enable Popup
            [
                'key'           => 'field_popup_enabled',
                'label'         => __('Enable Popup', TEXT_DOMAIN),
                'name'          => 'popup_enabled',
                'type'          => 'true_false',
                'default_value' => 0,
                'ui'            => 1,
            ],

            // Popup Title
            [
                'key'           => 'field_popup_title',
                'label'         => __('Popup Title', TEXT_DOMAIN),
                'name'          => 'popup_title',
                'type'          => 'textarea',
                'default_value' => __('CHƯƠNG TRÌNH KHUYẾN MÃI CUỐI NĂM 2025', TEXT_DOMAIN),
                'placeholder'   => __('Enter popup title', TEXT_DOMAIN),
            ],

            // Promotional Content (Repeater for offers)
            [
                'key'        => 'field_popup_offers',
                'label'      => __('Promotional Offers', TEXT_DOMAIN),
                'name'       => 'popup_offers',
                'type'       => 'repeater',
                'layout'     => 'table',
                'button_label' => __('Add Offer', TEXT_DOMAIN),
                'sub_fields' => [
                    [
                        'key'   => 'field_offer_icon',
                        'label' => __('Icon', TEXT_DOMAIN),
                        'name'  => 'icon',
                        'type'  => 'image',
                        'return_format' => 'id',
                    ],
                    [
                        'key'   => 'field_offer_text',
                        'label' => __('Text', TEXT_DOMAIN),
                        'name'  => 'text',
                        'type'  => 'wysiwyg',
                        'toolbar' => 'basic',
                        'media_upload' => 0,
                    ],
                ],
            ],

            // Promotional Image
            [
                'key'           => 'field_popup_image',
                'label'         => __('Promotional Image', TEXT_DOMAIN),
                'name'          => 'popup_image',
                'type'          => 'image',
                'return_format' => 'id',
                'preview_size'  => 'medium',
            ],

            // Form Settings
            [
                'key'   => 'field_popup_form_tab',
                'label' => __('Form Settings', TEXT_DOMAIN),
                'type'  => 'tab',
            ],

            [
                'key'           => 'field_popup_form_placeholder_name',
                'label'         => __('Name Placeholder', TEXT_DOMAIN),
                'name'          => 'popup_form_placeholder_name',
                'type'          => 'text',
                'default_value' => __('Họ và tên', TEXT_DOMAIN),
            ],

            [
                'key'           => 'field_popup_form_placeholder_phone',
                'label'         => __('Phone Placeholder', TEXT_DOMAIN),
                'name'          => 'popup_form_placeholder_phone',
                'type'          => 'text',
                'default_value' => __('Số điện thoại', TEXT_DOMAIN),
            ],

            [
                'key'           => 'field_popup_form_button_text',
                'label'         => __('Button Text', TEXT_DOMAIN),
                'name'          => 'popup_form_button_text',
                'type'          => 'text',
                'default_value' => __('Đăng ký ưu đãi ngay', TEXT_DOMAIN),
            ],

            // Advanced Settings
            [
                'key'   => 'field_popup_advanced_tab',
                'label' => __('Advanced Settings', TEXT_DOMAIN),
                'type'  => 'tab',
            ],

            [
                'key'           => 'field_popup_seasonal_effect',
                'label'         => __('Hiệu ứng trong Popup', TEXT_DOMAIN),
                'name'          => 'popup_seasonal_effect',
                'type'          => 'select',
                'choices'       => [
                    'none' => __('Không có', TEXT_DOMAIN),
                    'mai'  => __('🌸 Hoa Mai (Tết miền Nam)', TEXT_DOMAIN),
                    'dao'  => __('🌺 Hoa Đào (Tết miền Bắc)', TEXT_DOMAIN),
                    'snow' => __('❄️ Tuyết rơi (Noel)', TEXT_DOMAIN),
                ],
                'default_value' => 'mai',
                'ui'            => 1,
                'return_format' => 'value',
            ],

            [
                'key'           => 'field_global_seasonal_effect',
                'label'         => __('Hiệu ứng toàn Website', TEXT_DOMAIN),
                'name'          => 'global_seasonal_effect',
                'type'          => 'select',
                'instructions'  => __('Hiệu ứng sẽ hiển thị trên toàn bộ trang web', TEXT_DOMAIN),
                'choices'       => [
                    'none' => __('Không có', TEXT_DOMAIN),
                    'mai'  => __('🌸 Hoa Mai (Tết miền Nam)', TEXT_DOMAIN),
                    'dao'  => __('🌺 Hoa Đào (Tết miền Bắc)', TEXT_DOMAIN),
                    'snow' => __('❄️ Tuyết rơi (Noel)', TEXT_DOMAIN),
                ],
                'default_value' => 'none',
                'ui'            => 1,
                'return_format' => 'value',
            ],

            [
                'key'           => 'field_popup_cookie_duration',
                'label'         => __('Cookie Duration (days)', TEXT_DOMAIN),
                'name'          => 'popup_cookie_duration',
                'type'          => 'number',
                'default_value' => 7,
                'min'           => 1,
                'max'           => 365,
            ],

            [
                'key'           => 'field_popup_delay',
                'label'         => __('Popup Delay (milliseconds)', TEXT_DOMAIN),
                'name'          => 'popup_delay',
                'type'          => 'number',
                'default_value' => 2000,
                'min'           => 0,
                'max'           => 10000,
                'step'          => 100,
            ],
        ],
        'location'              => [
            [
                [
                    'param'    => 'options_page',
                    'operator' => '==',
                    'value'    => 'popup-settings',
                ],
            ],
        ],
    ]);
}
