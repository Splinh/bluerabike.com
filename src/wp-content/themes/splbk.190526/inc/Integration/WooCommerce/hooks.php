<?php

\defined('ABSPATH') || die;

//-----------------------------------------------------------------
// Custom functions
//-----------------------------------------------------------------

//-----------------------------------------------------------------
// Custom hooks
//-----------------------------------------------------------------

/**
 * Custom Product Gallery - Replace default WooCommerce gallery
 * 
 * @see wc_custom_product_gallery - 20
 */
remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20);
add_action('woocommerce_before_single_product_summary', 'wc_custom_product_gallery', 20);

function wc_custom_product_gallery(): void
{
    wc_get_template('single-product/product-gallery.php');
}

//-----------------------------------------------------------------

/**
 * @see wc_product_taxonomy_archive_footer - 10
 */
add_action('woocommerce_shop_loop_footer', 'wc_product_taxonomy_archive_footer');

function wc_product_taxonomy_archive_footer(): void
{
    wc_get_template('loop/footer.php');
}

//-----------------------------------------------------------------

//-----------------------------------------------------------------

/**
 * @see wc_add_buy_now_button - 10
 */
add_action('woocommerce_after_add_to_cart_button', 'wc_add_buy_now_button');

function wc_add_buy_now_button(): void
{
    global $product;

    if ($product->is_type('simple') || $product->is_type('variable')) {
?>
        <style>
            .buy-now-button {
                display: flex !important;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 12px 20px !important;
            }

            .buy-now-button strong {
                color: #fff;
                font-size: 14px;
                text-transform: uppercase;
            }

            .buy-now-button .btn-subtitle {
                color: #fff;
                font-size: 11px;
                font-weight: normal;
                opacity: 0.9;
                margin-top: 2px;
            }
        </style>
        <button type="submit" name="buy_now" class="button buy-now-button">
            <strong><?php echo __('THANH TOÁN TRỰC TUYẾN', TEXT_DOMAIN); ?></strong>
            <span class="btn-subtitle"><?php echo __('Qua website chính thức', TEXT_DOMAIN); ?></span>
        </button>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                let form = document.querySelector('form.cart');
                let input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'buy_now_product_id';
                input.value = '<?= esc_attr($product->get_id()) ?>';
                form.appendChild(input);
            });
        </script>
    <?php
    }
}

//-----------------------------------------------------------------

/**
 * @see wc_add_wishlist_button - 99
 */
add_action('woocommerce_after_add_to_cart_button', 'wc_add_wishlist_button', 99);

function wc_add_wishlist_button(): void
{
    echo \HD_Helper::doShortcode('yith_wcwl_add_to_wishlist');
}

//-----------------------------------------------------------------

/**
 * @see wc_handle_buy_now_redirect - 10
 */
add_action('template_redirect', 'wc_handle_buy_now_redirect');

/**
 * @throws Exception
 */
function wc_handle_buy_now_redirect(): void
{
    if (isset($_POST['buy_now'])) {
        if (isset($_POST['variation_id']) && empty($_POST['variation_id'])) {
            wp_safe_redirect(\HD_Helper::current());
            exit;
        }

        $product_id = !empty($_POST['variation_id']) ? (int) $_POST['variation_id'] : (int) $_POST['buy_now_product_id'];

        WC()->cart->empty_cart();
        WC()->cart->add_to_cart($product_id);
        wp_safe_redirect(wc_get_checkout_url());
        exit;
    }
}

//-----------------------------------------------------------------

/**
 * @see wc_acf_cam_ket - 49
 */
add_action('woocommerce_single_product_summary', 'wc_acf_cam_ket', 49);


// function wc_acf_specifications(): void {
// 	if ( ! isset( $GLOBALS['post'] ) ) {
// 		return;
// 	}

// 	$specs = get_field('specifications');
// 	if ( empty( $specs ) || ! is_array( $specs ) ) {
// 		return;
// 	}

// 	// Danh sách icon SVG cố định (đổi dễ)
// 	$icons = [
// 		'app'      => '<svg viewBox="0 0 64 64" width="44" height="44" role="img"><rect x="18" y="6" width="28" height="52" rx="6" ry="6" fill="none" stroke="currentColor" stroke-width="3"/><circle cx="32" cy="50" r="2" fill="currentColor"/><path d="M47 19c-2 2-4 2-5 2 0-2 1-4 2-5 1-1 3-2 4-2-1 2-1 3-1 5zM40 18c2 0 4 1 5 3 1 1 2 3 2 5 0 4-3 9-7 9-2 0-3-1-5-1s-3 1-5 1c-4 0-7-5-7-9 0-3 1-5 3-6 1-1 3-2 4-2 2 0 3 1 5 1 1 0 3-1 5-1z" fill="currentColor" opacity=".9"/></svg>',
// 		'power'    => '<svg viewBox="0 0 64 64" width="44" height="44"><path d="M36 4 14 34h12l-6 26 30-36H38l6-20z" fill="currentColor"/></svg>',
// 		'distance' => '<svg viewBox="0 0 64 64" width="44" height="44"><path d="M8 48c6-10 14-15 24-15s18 5 24 15" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"/><circle cx="16" cy="48" r="3" fill="currentColor"/><circle cx="32" cy="33" r="3" fill="currentColor"/><circle cx="48" cy="48" r="3" fill="currentColor"/><path d=\"M32 10c-5 0-9 4-9 9 0 7 9 17 9 17s9-10 9-17c0-5-4-9-9-9zm0 13a4 4 0 1 1 0-8 4 4 0 0 1 0 8z\" fill=\"currentColor\"/></svg>',
// 		'speed'    => '<svg viewBox="0 0 64 64" width="44" height="44"><path d="M8 40a24 24 0 1 1 48 0" fill="none" stroke="currentColor" stroke-width="3"/><path d="M32 40 46 26" stroke="currentColor" stroke-width="3" stroke-linecap="round"/><circle cx="32" cy="40" r="2.5" fill="currentColor"/><g opacity=".9" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M32 16v6"/><path d="M20 20l3 5"/><path d="M44 20l-3 5"/></g></svg>',
// 		'pin'      => '<svg viewBox="0 0 64 64" width="44" height="44"><rect x="12" y="18" width="40" height="28" rx="4" ry="4" fill="none" stroke="currentColor" stroke-width="3"/><rect x="16" y="22" width="24" height="20" fill="currentColor"/><rect x="52" y="24" width="4" height="16" rx="2" fill="currentColor"/></svg>',
// 		'time'     => '<svg viewBox="0 0 64 64" width="44" height="44"><circle cx="28" cy="32" r="14" fill="none" stroke="currentColor" stroke-width="3"/><path d="M28 24v8l6 3" stroke="currentColor" stroke-width="3" stroke-linecap="round" fill="none"/><path d="M42 20h10v10l-4-3-6 8v-6l4-5h-4z" fill="currentColor"/></svg>',
// 	];

// 	echo '<div class="product-specs" style="--icon-color:#1e78c2; --icon-hover:#F18721;">';
// 	echo '<div class="spec-grid">';

// 	foreach ( $specs as $key => $value ) {
// 		if ( empty( $value ) || ! isset( $icons[ $key ] ) ) {
// 			continue;
// 		}

// 		echo '<div class="spec-item">';
// 		echo $icons[ $key ];
// 		echo '<h4>' . esc_html( $value ) . '</h4>';
// 		echo '</div>';
// 	}

// 	echo '</div></div>';
// }

function wc_acf_cam_ket(): void
{
    if (!isset($GLOBALS['post'])) {
        return;
    }

    $specs = get_field('specifications');
    $specs_image_id = get_field('specs_image');
    if (empty($specs) || !is_array($specs)) {
        return;
    }

    // Icon SVG cố định
    $icons = [
        'app' => '<svg viewBox="0 0 64 64" width="44" height="44" role="img"><rect x="18" y="6" width="28" height="52" rx="6" ry="6" fill="none" stroke="currentColor" stroke-width="3"/><circle cx="32" cy="50" r="2" fill="currentColor"/><path d="M47 19c-2 2-4 2-5 2 0-2 1-4 2-5 1-1 3-2 4-2-1 2-1 3-1 5zM40 18c2 0 4 1 5 3 1 1 2 3 2 5 0 4-3 9-7 9-2 0-3-1-5-1s-3 1-5 1c-4 0-7-5-7-9 0-3 1-5 3-6 1-1 3-2 4-2 2 0 3 1 5 1 1 0 3-1 5-1z" fill="currentColor" opacity=".9"/></svg>',
        'power' => '<svg viewBox="0 0 64 64" width="44" height="44"><path d="M36 4 14 34h12l-6 26 30-36H38l6-20z" fill="currentColor"/></svg>',
        'distance' => '<svg viewBox="0 0 64 64" width="44" height="44"><path d="M8 48c6-10 14-15 24-15s18 5 24 15" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"/><circle cx="16" cy="48" r="3" fill="currentColor"/><circle cx="32" cy="33" r="3" fill="currentColor"/><circle cx="48" cy="48" r="3" fill="currentColor"/><path d=\"M32 10c-5 0-9 4-9 9 0 7 9 17 9 17s9-10 9-17c0-5-4-9-9-9zm0 13a4 4 0 1 1 0-8 4 4 0 0 1 0 8z\" fill=\"currentColor\"/></svg>',
        'speed' => '<svg viewBox="0 0 64 64" width="44" height="44"><path d="M8 40a24 24 0 1 1 48 0" fill="none" stroke="currentColor" stroke-width="3"/><path d="M32 40 46 26" stroke="currentColor" stroke-width="3" stroke-linecap="round"/><circle cx="32" cy="40" r="2.5" fill="currentColor"/><g opacity=".9" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M32 16v6"/><path d="M20 20l3 5"/><path d="M44 20l-3 5"/></g></svg>',
        'pin' => '<svg viewBox="0 0 64 64" width="44" height="44"><rect x="12" y="18" width="40" height="28" rx="4" ry="4" fill="none" stroke="currentColor" stroke-width="3"/><rect x="16" y="22" width="24" height="20" fill="currentColor"/><rect x="52" y="24" width="4" height="16" rx="2" fill="currentColor"/></svg>',
        'time' => '<svg viewBox="0 0 64 64" width="44" height="44"><circle cx="28" cy="32" r="14" fill="none" stroke="currentColor" stroke-width="3"/><path d="M28 24v8l6 3" stroke="currentColor" stroke-width="3" stroke-linecap="round" fill="none"/><path d="M42 20h10v10l-4-3-6 8v-6l4-5h-4z" fill="currentColor"/></svg>',
    ];

    echo '<ul class="product-cam-ket">';

    // Lặp qua group specifications
    foreach ($specs as $key => $value) {
        if (empty($value) || !isset($icons[$key])) {
            continue;
        }

        echo '<li class="cam-ket-item">';
        echo '<span class="cam-ket-link">';

        echo '<span class="icon-wrap">' . $icons[$key] . '</span>';
        echo '<span class="title-text">' . esc_html($value) . '</span>';

        echo '</span>';
        echo '</li>';
    }

    echo '</ul>';

    // Lấy ảnh thông số kỹ thuật từ ACF (hoặc field khác)
    // thêm field ảnh này trong ACF
    if ($specs_image_id) {
        $specs_image_url = wp_get_attachment_image_url($specs_image_id, 'full');
        $specs_image_alt = get_post_meta($specs_image_id, '_wp_attachment_image_alt', true) ?: 'Thông số kỹ thuật';

        // Nút mở popup
        echo '<div class="view-specs-wrap">';
        echo '<button class="btn-view-specs" data-popup="#specs-popup">Xem cấu hình chi tiết</button>';
        echo '</div>';

        // Popup ẩn
        echo '<div id="specs-popup" class="specs-popup" aria-hidden="true">';
        echo '  <div class="specs-overlay" data-popup-close></div>';
        echo '  <div class="specs-content">';
        echo '      <button class="popup-close" data-popup-close>&times;</button>';
        echo '      <img src="' . esc_url($specs_image_url) . '" alt="' . esc_attr($specs_image_alt) . '" loading="lazy" decoding="async">';
        echo '  </div>';
        echo '</div>';
    }
}
//-----------------------------------------------------------------

/**
 * @see wc_acf_custom_meta - 39
 */
//add_action( 'woocommerce_single_product_summary', 'wc_acf_custom_meta', 39 );

function wc_acf_custom_meta(): void
{
    if (!isset($GLOBALS['post'])) {
        return;
    }

    global $post;

    $ACF = \HD_Helper::getFields($post->ID);
    $ingredients = $ACF['ingredients'] ?? '';
    $how_to_use = $ACF['how-to-use'] ?? '';

    if (empty($ingredients) && empty($how_to_use)) {
        return;
    }
    ?>
    <ul class="accordion" data-accordion data-allow-all-closed="true">
        <?php if (!empty($ingredients)): ?>
            <li class="accordion-item" data-accordion-item>
                <a href="#" class="accordion-title"><?php echo __('Thành phần', TEXT_DOMAIN); ?></a>
                <div class="accordion-content" data-tab-content>
                    <?= $ingredients ?>
                </div>
            </li>
        <?php endif; ?>
        <?php if (!empty($how_to_use)): ?>
            <li class="accordion-item" data-accordion-item>
                <a href="#" class="accordion-title"><?php echo __('Hướng dẫn sử dụng', TEXT_DOMAIN); ?></a>
                <div class="accordion-content" data-tab-content>
                    <?= $how_to_use ?>
                </div>
            </li>
        <?php endif; ?>
    </ul>
<?php
}

//-----------------------------------------------------------------

/**
 * @see wc_product_brand_label - 9
 */
add_action('woocommerce_shop_loop_item_title', 'wc_product_brand_label', 9);

function wc_product_brand_label(): void
{
    global $product;

    $brands = wp_get_post_terms($product->get_id(), 'product_brand');
    if (!empty($brands) && !is_wp_error($brands)) {
        $brand = $brands[0];
        echo '<span class="product-brand-label">' . $brand->name . '</span>';
    }
}

//-----------------------------------------------------------------

/**
 * @see wc_product_product_badge - 10
 */
add_action('woocommerce_before_shop_loop_item_title', 'wc_product_product_badge');

function wc_product_product_badge(): void
{
    global $product;

    $product_badge = \HD_Helper::getField('product_badge', $product->get_id());
    if ($product_badge) {
        echo '<div class="product-badge">';
        foreach ($product_badge as $badge) {
            $badge_bgcolor = \HD_Helper::getField('badge_bgcolor', $badge);
            $badge_color = \HD_Helper::getField('badge_color', $badge);

            $css = !empty($badge_bgcolor) ? 'background-color:' . $badge_bgcolor . ';' : '';
            $css .= !empty($badge_color) ? 'color:' . $badge_color . ';' : '';
            $css = !empty($css) ? ' style="' . $css . '"' : '';

            echo '<span' . $css . '>' . get_the_title($badge) . '</span>';
        }
        echo '</div>';
    }
}

//-----------------------------------------------------------------

/**
 * @see wc_product_sale_extra - 99
 */
add_action('woocommerce_before_single_product', 'wc_product_sale_extra', 99);

function wc_product_sale_extra(): void
{
    global $product;

    $product_sale_extra = \HD_Helper::getField('product_sale_extra', $product->get_id());
    $product_extra_bgcolor = $product_sale_extra['product_extra_bgcolor'] ?? '';
    $product_extra_color = $product_sale_extra['product_extra_color'] ?? '';
    $product_extra_content = $product_sale_extra['product_extra_content'] ?? '';

    if (!\HD_Helper::stripSpace($product_extra_content)) {
        return;
    }

    $css = !empty($product_extra_bgcolor) ? 'background-color:' . $product_extra_bgcolor . ';' : '';
    $css .= !empty($product_extra_color) ? 'color:' . $product_extra_color . ';' : '';
    echo !empty($product_extra_bgcolor) ? '<style>.product-sale-extra>.container>.entry{' . $css . '}</style>' : '';
?>
    <div class="product-sale-extra">
        <div class="container">
            <div class="entry">
                <?= $product_extra_content ?>
            </div>
        </div>
    </div>
<?php
}

//-----------------------------------------------------------------

/**
 * @see wc_product_mini_cart_total - 10
 */
add_action('woocommerce_widget_shopping_cart_total', 'wc_product_mini_cart_total');

function wc_product_mini_cart_total(): void
{
    echo '<span><span>' . esc_html__('Tổng sản phẩm:', TEXT_DOMAIN) . '</span> ' . number_format_i18n(WC()->cart->get_cart_contents_count()) . '</span>';
    echo '<span><span>' . esc_html__('Tạm tính:', TEXT_DOMAIN) . '</span> ' . WC()->cart->get_cart_subtotal() . '</span>';
}

//-----------------------------------------------------------------

/**
 * @see wc_account_menu_items - 10
 */
add_filter('woocommerce_account_menu_items', 'wc_account_menu_items', 10, 2);

/**
 * @param $items
 * @param $endpoints
 *
 * @return mixed
 */
function wc_account_menu_items($items, $endpoints): mixed
{
    unset($items['downloads']);

    return $items;
}

//-----------------------------------------------------------------

/**
 * @see wc_add_phone_gender_field_to_edit_account - 10
 */
add_action('woocommerce_edit_account_form_fields', 'wc_add_phone_gender_field_to_edit_account');

/**
 * @return void
 */
function wc_add_phone_gender_field_to_edit_account(): void
{
    $current_user = wp_get_current_user();
    $phone = get_user_meta($current_user?->ID, 'account_tel', true);
    $gender = get_user_meta($current_user?->ID, 'account_gender', true);

?>
    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="account_tel"><?php esc_html_e('Số điện thoại', TEXT_DOMAIN); ?>&nbsp;<span class="required"
                aria-hidden="true">*</span></label>
        <input required type="tel" class="woocommerce-Input woocommerce-Input--tel input-text" name="account_tel"
            id="account_tel" autocomplete="tel" value="<?php echo esc_attr($phone); ?>" aria-required="true" />
    </p>
    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="account_gender"><?php _e('Giới tính', TEXT_DOMAIN); ?></label>
        <select name="account_gender" id="account_gender" class="account_gender_select" autocomplete="gender">
            <option value=""><?php esc_html_e('Chọn giới tính', TEXT_DOMAIN); ?></option>
            <option value="male" <?php selected($gender, 'male'); ?>><?php esc_html_e('Nam', TEXT_DOMAIN); ?></option>
            <option value="female" <?php selected($gender, 'female'); ?>><?php esc_html_e('Nữ', TEXT_DOMAIN); ?></option>
        </select>
    </p>
<?php
}

//-----------------------------------------------------------------

/**
 * @see wc_save_account_details - 10
 */
add_action('woocommerce_save_account_details', 'wc_save_account_details');

/**
 * @param $user_id
 *
 * @return void
 */
function wc_save_account_details($user_id): void
{
    if (isset($_POST['account_tel'])) {
        update_user_meta($user_id, 'account_tel', sanitize_text_field($_POST['account_tel']));
    }

    if (isset($_POST['account_gender'])) {
        update_user_meta($user_id, 'account_gender', sanitize_text_field($_POST['account_gender']));
    }
}

//-----------------------------------------------------------------
