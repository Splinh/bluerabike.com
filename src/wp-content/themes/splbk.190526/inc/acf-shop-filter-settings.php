<?php

/**
 * ACF Shop Filter Settings
 *
 * @author Gaudev
 */

\defined('ABSPATH') || exit;

// Register ACF Options Sub-page under WooCommerce
if (function_exists('acf_add_options_page')) {
    acf_add_options_page([
        'page_title'  => __('Bộ lọc sản phẩm', TEXT_DOMAIN),
        'menu_title'  => __('Bộ lọc sản phẩm', TEXT_DOMAIN),
        'menu_slug'   => 'shop-filter-settings',
        'parent_slug' => 'woocommerce',
        'capability'  => 'manage_woocommerce',
    ]);
}

// Register ACF Fields for Shop Filter
if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group([
        'key'      => 'group_shop_filter_settings',
        'title'    => __('Cài đặt Bộ lọc sản phẩm', TEXT_DOMAIN),
        'fields'   => [
            // General Tab
            [
                'key'   => 'field_sf_general_tab',
                'label' => __('Chung', TEXT_DOMAIN),
                'type'  => 'tab',
            ],

            // Enable Price Filter
            [
                'key'           => 'field_sf_enable_price',
                'label'         => __('Hiển thị lọc theo giá', TEXT_DOMAIN),
                'name'          => 'sf_enable_price',
                'type'          => 'true_false',
                'default_value' => 1,
                'ui'            => 1,
            ],

            // Attributes Tab
            [
                'key'   => 'field_sf_attributes_tab',
                'label' => __('Thuộc tính', TEXT_DOMAIN),
                'type'  => 'tab',
            ],

            // Select Attributes to Display
            [
                'key'           => 'field_sf_enabled_attributes',
                'label'         => __('Chọn thuộc tính hiển thị', TEXT_DOMAIN),
                'name'          => 'sf_enabled_attributes',
                'type'          => 'checkbox',
                'choices'       => spl_get_attribute_choices(),
                'layout'        => 'vertical',
                'toggle'        => 1,
                'instructions'  => __('Chọn các thuộc tính sẽ hiển thị trong bộ lọc. Bỏ chọn để ẩn.', TEXT_DOMAIN),
            ],

            // Display Tab
            [
                'key'   => 'field_sf_display_tab',
                'label' => __('Hiển thị', TEXT_DOMAIN),
                'type'  => 'tab',
            ],

            // Show Product Count
            [
                'key'           => 'field_sf_show_count',
                'label'         => __('Hiển thị số lượng sản phẩm', TEXT_DOMAIN),
                'name'          => 'sf_show_count',
                'type'          => 'true_false',
                'default_value' => 1,
                'ui'            => 1,
                'instructions'  => __('Hiển thị số lượng sản phẩm bên cạnh mỗi tùy chọn lọc.', TEXT_DOMAIN),
            ],

            // Clear Button Text
            [
                'key'           => 'field_sf_clear_text',
                'label'         => __('Nút xóa bộ lọc', TEXT_DOMAIN),
                'name'          => 'sf_clear_text',
                'type'          => 'text',
                'default_value' => __('Xóa bộ lọc', TEXT_DOMAIN),
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'options_page',
                    'operator' => '==',
                    'value'    => 'shop-filter-settings',
                ],
            ],
        ],
    ]);
}

/**
 * Get WooCommerce attribute choices for ACF field
 *
 * @return array
 */
function spl_get_attribute_choices(): array
{
    $choices = [];

    if (!function_exists('wc_get_attribute_taxonomies')) {
        return $choices;
    }

    $attribute_taxonomies = wc_get_attribute_taxonomies();

    if (empty($attribute_taxonomies)) {
        return $choices;
    }

    foreach ($attribute_taxonomies as $attribute) {
        $choices[$attribute->attribute_name] = $attribute->attribute_label;
    }

    return $choices;
}
