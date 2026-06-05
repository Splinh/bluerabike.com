<?php

namespace HD\Integration\WooCommerce;

use HD\Utilities\Traits\Singleton;
use Random\RandomException;

\defined('ABSPATH') || die;

require __DIR__ . '/hooks.php';

/**
 * WooCommerce Plugin
 *
 * @author   Gaudev
 */
final class WooCommerce
{
    use Singleton;

    /* ---------- TRANSIENT & META KEYS ----------------------------------- */

    private const KEY_OTP = 'wc_loginotp_%d';     // hash (OTP)
    private const KEY_ATTEMPT = 'wc_loginotp_try_%d'; // int
    private const META_LASTSEND = '_wc_otp_last_send';  // timestamp
    private const META_TOKEN = '_wc_otp_dnc_token';  // random

    /* ---------- CONFIG -------------------------------------------------- */

    public const OTP_DIGITS = 6;
    public const OTP_LIFETIME = 4 * MINUTE_IN_SECONDS; // 4 minutes (transient and form)
    public const RESEND_INTERVAL = 4 * MINUTE_IN_SECONDS; // 4 minutes (cool-down email)
    public const COOKIE_LIFETIME = DAY_IN_SECONDS; // 1 day
    public const MAX_ATTEMPTS = 5;
    public const ACTION_VALIDATE = '_wc_otp_validate';

    /* ---------- CONSTRUCT ----------------------------------------------- */

    private function init(): void
    {
        //-----------------------------------------------------------------
        // Setup
        //-----------------------------------------------------------------

        add_action('widgets_init', [$this, 'unregisterDefaultWidgets'], 33);
        add_action('widgets_init', [$this, 'registerWidgets'], 34);
        add_action('after_setup_theme', [$this, 'afterSetupTheme'], 33);
        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets'], 98);

        add_filter('wp_theme_json_data_theme', [$this, 'jsonDataTheme']);

        //-----------------------------------------------------------------
        // Custom Hooks
        //-----------------------------------------------------------------

        // Remove header from the WooCommerce administrator panel
        add_action('admin_head', static function () {
            echo '<style>#wpadminbar ~ #wpbody { margin-top: 0 !important; }.woocommerce-layout__header { display: none !important; }</style>';
        });

        add_filter('woocommerce_defer_transactional_emails', '__return_true');
        add_filter('woocommerce_product_get_rating_html', [$this, 'getRatingHtml'], 10, 3);
        add_filter('woocommerce_product_description_heading', '__return_empty_string');
        add_filter('woocommerce_product_additional_information_heading', '__return_empty_string');
        add_filter('woocommerce_product_brands_output', '__return_empty_string');
        add_filter('woocommerce_add_to_cart_fragments', [$this, 'cartFragment'], 11, 1);
        add_filter('woocommerce_widget_cart_item_quantity', [$this, 'wc_mini_cart_item_quantity'], 10, 3);
        add_filter('woocommerce_variable_price_html', [$this, 'custom_variable_price_html'], 10, 2);

        add_action('wp_ajax_update_mini_cart_qty', [$this, 'wc_ajax_update_mini_cart_qty']);
        add_action('wp_ajax_nopriv_update_mini_cart_qty', [$this, 'wc_ajax_update_mini_cart_qty']);

        // woocommerce_before_shop_loop
        // Remove default link wrappers
        remove_action('woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10);
        remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5);
        remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
        remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);

        // Add custom wrapper structure
        add_action('woocommerce_before_shop_loop_item', function () {
            global $product;
            $classes = wc_get_product_class('', $product);
            echo '<div class="product-item">';
            //  echo '<div class="' . esc_attr(implode(' ', $classes)) . '">';
            echo '<div class="custom-product-wrapper">';
        }, 5);

        // Open product image link
        add_action('woocommerce_before_shop_loop_item', function () {
            echo '<a href="' . esc_url(get_the_permalink()) . '" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">';
        }, 6);

        // Close image link (before actions)
        add_action('woocommerce_after_shop_loop_item_title', function () {
            echo '</a>';
        }, 5);

        // Product actions wrapper
        add_action('woocommerce_after_shop_loop_item', function () {
            global $product;
            $product_id = $product->get_id();
            $product_url = get_permalink($product_id);
            $product_name = $product->get_name();

            echo '<div class="product-actions">';

            // Check if product has price - hide cart buttons for products without price
            $has_price = $product->get_price() !== '' && $product->get_price() !== null;

            // 🔹 Nút icon (chỉ hiển thị icon, không có text)
            if ($product->is_type('variable')) {
                echo '<a href="' . esc_url($product_url) . '" class="button buy-now-button" aria-label="' . esc_attr(sprintf(__('Xem chi tiết %s', TEXT_DOMAIN), $product_name)) . '">';
                echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" aria-hidden="true" focusable="false"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M502.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L402.7 224 32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l370.7 0-105.4 105.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z"/></svg>';
                echo '</a>';
            } elseif ($has_price) {
                // Only show buy now + cart buttons if product has price
                echo '<a href="' . esc_url($product_url) . '" class="button buy-now-button" aria-label="' . esc_attr(sprintf(__('Xem chi tiết %s', TEXT_DOMAIN), $product_name)) . '">';
                echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" aria-hidden="true" focusable="false"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M502.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L402.7 224 32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l370.7 0-105.4 105.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z"/></svg>';
                echo '</a>';
                // Với sản phẩm đơn giản có giá → nút giỏ hàng (Ajax)
                echo apply_filters(
                    'woocommerce_loop_add_to_cart_link',
                    sprintf(
                        '<a href="%s" data-quantity="%s" data-product_id="%s" data-product_sku="%s" class="button add_to_cart_button ajax_add_to_cart" aria-label="%s">%s</a>',
                        esc_url($product->add_to_cart_url()),
                        esc_attr(isset($quantity) ? $quantity : 1),
                        esc_attr($product_id),
                        esc_attr($product->get_sku()),
                        esc_attr(sprintf(__('Thêm %s vào giỏ hàng', TEXT_DOMAIN), $product_name)),
                        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512" aria-hidden="true" focusable="false"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M24-16C10.7-16 0-5.3 0 8S10.7 32 24 32l45.3 0c3.9 0 7.2 2.8 7.9 6.6l52.1 286.3c6.2 34.2 36 59.1 70.8 59.1L456 384c13.3 0 24-10.7 24-24s-10.7-24-24-24l-255.9 0c-11.6 0-21.5-8.3-23.6-19.7l-5.1-28.3 303.6 0c30.8 0 57.2-21.9 62.9-52.2L568.9 69.9C572.6 50.2 557.5 32 537.4 32l-412.7 0-.4-2c-4.8-26.6-28-46-55.1-46L24-16zM208 512a48 48 0 1 0 0-96 48 48 0 1 0 0 96zm224 0a48 48 0 1 0 0-96 48 48 0 1 0 0 96z"/></svg>'
                    ),
                    $product
                );
            } else {
                // Product without price → only show view details button
                echo '<a href="' . esc_url($product_url) . '" class="button buy-now-button view-details-button" aria-label="' . esc_attr(sprintf(__('Xem chi tiết %s', TEXT_DOMAIN), $product_name)) . '">';
                echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" aria-hidden="true" focusable="false"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M502.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L402.7 224 32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l370.7 0-105.4 105.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z"/></svg>';
                echo '</a>';
            }

            echo '</div>';
        }, 7);
        // Add wishlist button or custom buttons here
        add_action('woocommerce_after_shop_loop_item', [$this, 'wc_template_loop_product_wishlist_button'], 7);

        // Close product-actions + custom-product-wrapper
        add_action('woocommerce_after_shop_loop_item', function () {

            echo '</div>'; // close custom-product-wrapper
        }, 99);
        // Close product + product-item
        add_action('woocommerce_after_shop_loop_item', function () {
            echo '</div>'; // close .product and .product-item
        }, 100);
        add_action('woocommerce_after_shop_loop_item', function () {
            echo '<div class="all_content_product">';
        }, 101);
        // Move product title + price OUTSIDE wrapper
        remove_action('woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10);
        add_action('woocommerce_after_shop_loop_item', function () {
            global $product;
            echo '<div class="product-details">'; // close all_content_product
            echo '<h2 class="woocommerce-loop-product__title">';
            echo '<a href="' . esc_url(get_the_permalink()) . '">' . get_the_title() . '</a>';
            echo '</h2>';
            woocommerce_template_loop_price(); // hiển thị giá ở ngoài custom wrapper
            echo '</div>';
        }, 102);

        add_action('woocommerce_after_shop_loop_item', function () {
            global $product;
            $specs = get_field('specifications', $product->get_id());

            if ($specs) {
                echo '<div class="product-specs">';

                if (!empty($specs['pin'])) {
                    echo '<span class="spec-item"><img src="' . esc_url(get_stylesheet_directory_uri() . '/assets/img/icon_pin.png') . '" alt="Pin"> ' . esc_html($specs['pin']) . '</span>';
                }

                if (!empty($specs['distance'])) {
                    echo '<span class="spec-item"><img src="' . esc_url(get_stylesheet_directory_uri() . '/assets/img/icon_qd.png') . '" alt="Quãng đường"> ' . esc_html($specs['distance']) . '</span>';
                }

                if (!empty($specs['speed'])) {
                    echo '<span class="spec-item"><img src="' . esc_url(get_stylesheet_directory_uri() . '/assets/img/icon_td.png') . '" alt="Tốc độ"> ' . esc_html($specs['speed']) . '</span>';
                }

                echo '</div>';
            }
        }, 102);
        add_action('woocommerce_after_shop_loop_item', function () {
            echo '</div>';
            //  echo '</div>'; 
        }, 103);
        // Close product + product-item

        //remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
        remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
        remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10);
        remove_action('woocommerce_widget_shopping_cart_total', 'woocommerce_widget_shopping_cart_subtotal', 10);
        //   remove_action('woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10);
        remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);

        //  add_action( 'woocommerce_before_shop_loop_item', [ $this, 'wc_template_loop_product_link_open' ], 10 );
        add_action('wp_loaded', [$this, 'process_otp_login'], 20);
        add_action('wp_loaded', [$this, 'validate_otp_login'], 21);

        add_action('woocommerce_after_shop_loop_item', static function () {
            echo '<div class="wc-loop-product__buttons buttons-wrap">';
        }, 8);
        add_action('woocommerce_after_shop_loop_item', [$this, 'wc_template_loop_product_wishlist_button'], 9);
        add_action('woocommerce_after_shop_loop_item', static function () {
            echo '</div>';
        }, 999);

        add_action('hdwc_woocommerce_checkout_payment', 'woocommerce_checkout_payment', 20);

        /** Custom xxx */

        add_action('wp_enqueue_scripts', static function () {
            if (function_exists('is_woocommerce')) {
                wp_enqueue_script('jquery');
                wp_enqueue_script('wc-add-to-cart-variation');
            }

            wp_add_inline_script('jquery', 'window.wc_checkout_url = "' . esc_js(wc_get_checkout_url()) . '";', 'after');

            $inline = <<<'JS'
(function($){
    // --- myCRED ---
  function ensureMycredSafe(){
    if (typeof window.mycred_variable_rewards === 'undefined') {
      window.mycred_variable_rewards = {};
    }
    if (typeof window.call_rewards_points === 'function' && !window.__mycred_call_rewards_patched){
      var _orig = window.call_rewards_points;
      window.call_rewards_points = function(){
        if (typeof window.mycred_variable_rewards === 'undefined') return;
        try { return _orig.apply(this, arguments); }
        catch(e){ console.warn('[myCRED] call_rewards_points guarded:', e); }
      };
      window.__mycred_call_rewards_patched = true;
    }
  }
  
    function ensureBuyNowInput($root){
      $root.find('form.cart').each(function(){
      var $f = $(this);
      
      var isVariation = $f.hasClass('variations_form') || $f.find('.variations select').length;
      if (isVariation) return;
      
      if ($f.find('input[name="buy_now_product_id"]').length) return;
      
      var pid = $f.data('product_id')
             || $f.find('input[name="add-to-cart"]').val()
             || $f.find('button[name="add-to-cart"]').val()
             || $f.find('input[name="product_id"]').val()
             || $f.closest('[data-product_id]').data('product_id');

      if (pid) {
        $('<input>', {
          type: 'hidden',
          name: 'buy_now_product_id',
          value: pid
        }).appendTo($f);
      }
    });
  }
  
  // ---- init variation form ----
  function initVariationsIn($root){
    var $forms = $root.find('form.variations_form').not('.qv-variations-initialized');
    if(!$forms.length) {
      ensureBuyNowInput($root);
      return;
    }

    $forms.each(function(){
      var $f = $(this);
      
      var $scope = $('.mfp-content').length ? $('.mfp-content') : $f;
      var $priceTarget = $scope.find('.product-detail .single-price');

      // Fallback
      if (!$priceTarget.length) {
        $priceTarget = $scope.find('.single-price, .summary .price, .product .summary .price').first();
      }
      
      if ($priceTarget.length && !$f.data('base-price-html')) {
        $f.data('base-price-html', $priceTarget.html());
      }

      // ----- re-init variation & swatches -----
      $f.removeData('product_variations');
      if (typeof $f.wc_variation_form === 'function') {
        $f.wc_variation_form();
      }
      $f.addClass('qv-variations-initialized');
      
      $f.off('found_variation.qv hide_variation.qv reset_data.qv');
      
      $f.on('found_variation.qv', function(e, variation){
          ensureMycredSafe();
        if ($priceTarget.length && variation && typeof variation.price_html !== 'undefined' && variation.price_html !== '') {
          $priceTarget.html(variation.price_html);
        }
      });
      
      $f.on('hide_variation.qv reset_data.qv', function(){
        var base = $f.data('base-price-html');
        if ($priceTarget.length && typeof base !== 'undefined') {
          $priceTarget.html(base);
        }
      });
      
      ensureMycredSafe();
      $f.find('.variations select').trigger('change');
    });
    
    ensureBuyNowInput($root);
  }

  // ---- AJAX quick view ----
  $(document).ajaxComplete(function(e, xhr, settings){
    var s = settings || {};
    var data = (typeof s.data === 'string') ? s.data : '';
    var isAdminAjax = s.url && s.url.indexOf('admin-ajax.php') !== -1;
    var isQuickView  = data && (data.indexOf('action=wqv_popup_content') !== -1 || data.indexOf('action=wqv_quick_view') !== -1);
    if (isAdminAjax && isQuickView){
      setTimeout(function(){
          ensureMycredSafe();
        initVariationsIn($('.mfp-content'));
        ensureBuyNowInput($('.mfp-content'));
      }, 0);
      setTimeout(function(){
          ensureMycredSafe();
        initVariationsIn($('.mfp-content'));
        ensureBuyNowInput($('.mfp-content'));
      }, 100);
      setTimeout(function(){
          ensureMycredSafe();
        initVariationsIn($('.mfp-content'));
        ensureBuyNowInput($('.mfp-content'));
      }, 300);
    }
  });

  // ---- Magnific Popup events ----
  $(document).on('mfpOpen mfpContentAdded mfpAjaxContentAdded', function(){
      ensureMycredSafe();
    initVariationsIn($('.mfp-content'));
    ensureBuyNowInput($('.mfp-content'));
  });

  // ---- Click Quick View ----
  $(document).on('click', '.sp-wqv-view-button', function(){
    setTimeout(function(){
        ensureMycredSafe();
      initVariationsIn($('.mfp-content'));
      ensureBuyNowInput($('.mfp-content'));
    }, 50);
    setTimeout(function(){
        ensureMycredSafe();
      initVariationsIn($('.mfp-content'));
      ensureBuyNowInput($('.mfp-content'));
    }, 200);
  });

  // ---- Fallback ----
  try {
    var mo = new MutationObserver(function(muts){
      muts.forEach(function(m){
        $(m.addedNodes).each(function(){
          var $n = $(this);
          if ($n.find){
              ensureMycredSafe();
            if ($n.find('form.variations_form').length){
              initVariationsIn($n);
            }
            ensureBuyNowInput($n);
          }
        });
      });
    });
    mo.observe(document.body, {childList:true, subtree:true});
  } catch(e){}
  
  $(function(){
      ensureMycredSafe();
    ensureBuyNowInput($(document));
  });

})(jQuery);
JS;

            wp_add_inline_script('jquery', $inline, 'after');
        }, 101);
    }

    /* ---------- PUBLIC -------------------------------------------------- */

    /**
     * @param $theme_json
     *
     * @return mixed
     */
    public function jsonDataTheme($theme_json): mixed
    {
        $new_data = [
            'version' => 1,
            'settings' => [
                'typography' => [
                    'fontFamilies' => [
                        'theme' => [],
                    ],
                ],
            ],
        ];
        $theme_json->update_with($new_data);

        return $theme_json;
    }

    /**
     * Registers a WP_Widget widget
     *
     * @return void
     */
    public function registerWidgets(): void
    {
        $widgets_dir = INC_PATH . 'Integration/WooCommerce/Widgets';
        $FQN = '\\HD\\Integration\\WooCommerce\\Widgets\\';

        \HD_Helper::createDirectory($widgets_dir);
        \HD_Helper::FQNLoad($widgets_dir, false, true, $FQN, true);
    }

    /**
     * Unregister a WP_Widget widget
     *
     * @return void
     */
    public function unregisterDefaultWidgets(): void
    {
        unregister_widget('WC_Widget_Product_Search');
        unregister_widget('WC_Widget_Products');
    }

    /**
     * @return void
     */
    public function enqueueAssets(): void
    {
        \HD_Asset::enqueueStyle('hdwc-css', ASSETS_URL . 'css/components/woocommerce.css', ['index-css'], \HD_Helper::version());
        \HD_Asset::enqueueScript('hdwc-js', ASSETS_URL . 'js/components/woocommerce.js', [
            'jquery-core',
            'index-js'
        ], \HD_Helper::version(), true, ['module', 'defer']);
    }

    /**
     * @return void
     */
    public function afterSetupTheme(): void
    {
        // Add support for WC features.
        //add_theme_support( 'wc-product-gallery-zoom' );
        //add_theme_support( 'wc-product-gallery-lightbox' );
        //add_theme_support( 'wc-product-gallery-slider' );

        add_theme_support('woocommerce');
    }

    /**
     * Cart Fragments, ensure cart contents update when products are added to the cart via AJAX
     *
     * @param array $fragments Fragments to refresh via AJAX.
     *
     * @return array            Fragments to refresh via AJAX
     */
    public function cartFragment(array $fragments): array
    {
        ob_start();
        echo '<span class="cart-count">' . WC()->cart->get_cart_contents_count() . '</span>';
        $fragments['.cart-count'] = ob_get_clean();

        ob_start(); ?>
        <div class="mini-cart-dropdown">
            <?php woocommerce_mini_cart(); ?>
        </div>
        <?php
        $fragments['div.mini-cart-dropdown'] = ob_get_clean();

        return $fragments;
    }

    /**
     * @param $content
     * @param $cart_item
     * @param $cart_item_key
     *
     * @return void
     */
    public function wc_mini_cart_item_quantity($content, $cart_item, $cart_item_key): void
    {
        $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
        $product_price = apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key);

        ?>
        <div class="quantity">
            <span class="price"><?= number_format_i18n($cart_item['quantity']) . ' x ' . $product_price ?></span>
            <div class="qty-control">
                <button class="minus" type="button">-</button>
                <input type="number" class="qty" name="qty" value="<?php echo $cart_item['quantity']; ?>" min="1" />
                <button class="plus" type="button">+</button>
            </div>
        </div>
        <?php
    }

    /**
     * @param $price_html
     * @param $product
     *
     * @return string
     */
    public function custom_variable_price_html($price_html, $product): string
    {
        $variation = $this->_get_default_or_min_variation_in_stock($product);

        if (!$variation) {
            return '<span class="out-of-stock block">' . __('Hết hàng', TEXT_DOMAIN) . '</span>';
        }

        if ($variation->is_on_sale()) {
            return wc_format_sale_price(
                wc_price($variation->get_regular_price()),
                wc_price($variation->get_sale_price())
            ) . $product->get_price_suffix();
        }

        return wc_price($variation->get_price()) . $product->get_price_suffix();
    }

    /**
     * @param \WC_Product_Variable $product
     *
     * @return \WC_Product|null
     */
    private function _get_default_or_min_variation_in_stock(\WC_Product_Variable $product): ?\WC_Product
    {
        $variation = null;

        $default_attributes = $product->get_default_attributes();
        if (!empty($default_attributes)) {
            foreach ($product->get_children() as $variation_id) {
                $child = wc_get_product($variation_id);
                if ($child && $child->get_attributes() === $default_attributes && $child->is_in_stock()) {
                    $variation = $child;
                    break;
                }
            }
        }

        if (!$variation) {
            $min_price = null;
            foreach ($product->get_children() as $variation_id) {
                $child = wc_get_product($variation_id);
                if ($child && $child->is_in_stock()) {
                    $price = (float) $child->get_price();
                    if ($min_price === null || $price < $min_price) {
                        $min_price = $price;
                        $variation = $child;
                    }
                }
            }
        }

        return $variation;
    }

    /**
     * @return bool|null
     */
    public function wc_ajax_update_mini_cart_qty(): ?bool
    {
        if (!wp_doing_ajax()) {
            return false;
        }

        $cart_item_key = wc_clean($_POST['cart_item_key'] ?? '');
        $quantity = max(0, (int) ($_POST['quantity'] ?? 0));

        if (!$cart_item_key || !WC()->cart->get_cart_item($cart_item_key)) {
            wp_send_json_error(['message' => 'Invalid cart item.']);
        }

        \WC()->cart->set_quantity($cart_item_key, $quantity);
        \WC_AJAX::get_refreshed_fragments();
        die();
    }

    /**
     * @param $html
     * @param $rating
     * @param $count
     *
     * @return string
     */
    public function getRatingHtml($html, $rating, $count): string
    {
        $return = '';

        if (0 < $rating) {
            $label = sprintf(__('Rated %s out of 5', 'woocommerce'), $rating);

            $return .= '<div class="loop-stars-rating" role="img" aria-label="' . esc_attr($label) . '">';
            $return .= \wc_get_star_rating_html($rating, $count);
            $return .= '</div>';
        }

        return $return;
    }

    /**
     * @return void
     */
    public function validate_otp_login(): void
    {
        if (
            empty($_POST['authcode']) ||
            empty($_POST['uid']) ||
            empty($_POST['_csrf_token']) ||
            !wp_verify_nonce(wp_unslash($_POST['_csrf_token']), 'wc_otp_validate_nonce')
        ) {
            return;
        }

        $customer_id = (int) $_POST['uid'];
        $entered = preg_replace('/\D/', '', (string) wp_unslash($_POST['authcode']));

        // Transient data
        $hash = get_transient(sprintf(self::KEY_OTP, $customer_id));
        $attempts = (int) get_transient(sprintf(self::KEY_ATTEMPT, $customer_id));

        try {
            $validation_error = new \WP_Error();
            $validation_errors = $validation_error->get_error_messages();

            if (1 === count($validation_errors)) {
                throw new \Exception($validation_error->get_error_message());
            }

            if ($validation_errors) {
                foreach ($validation_errors as $message) {
                    wc_add_notice('<strong>' . __('Error:', 'woocommerce') . '</strong> ' . $message, 'error');
                }
                throw new \Exception();
            }

            if (false === $hash) {
                $this->_load_otp_form([
                    'action' => esc_url(add_query_arg('action', self::ACTION_VALIDATE, wc_get_page_permalink('myaccount'))),
                    'template' => 'myaccount/page-otp-login.php',
                    'uid' => $customer_id,
                    'send_at' => (int) get_user_meta($customer_id, self::META_LASTSEND, true),
                    'error' => __('Verification code expired – please request a new code.', TEXT_DOMAIN),
                ]);
            }

            // Compare
            if (!hash_equals($hash, wp_hash($entered))) {
                // +1 failed attempt
                $attempts++;
                set_transient(sprintf(self::KEY_ATTEMPT, $customer_id), $attempts, self::OTP_LIFETIME);

                // Too many attempts?
                if ($attempts >= self::MAX_ATTEMPTS) {
                    $this->_wc_clear_otp_data($customer_id);
                    throw new \Exception(__('Too many incorrect attempts. Please try again later.', TEXT_DOMAIN));
                }

                $this->_load_otp_form([
                    'action' => esc_url(add_query_arg('action', self::ACTION_VALIDATE, wc_get_page_permalink('myaccount'))),
                    'template' => 'myaccount/page-otp-login.php',
                    'uid' => $customer_id,
                    'send_at' => (int) get_user_meta($customer_id, self::META_LASTSEND, true),
                    'error' => sprintf(__('Invalid code. You have %1$d of %2$d attempts left.', TEXT_DOMAIN), self::MAX_ATTEMPTS - $attempts, self::MAX_ATTEMPTS),
                ]);
            }

            // Success → log the user in and redirect
            wc_set_customer_auth_cookie($customer_id);

            $redirect = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : wc_get_page_permalink('myaccount');
            wp_safe_redirect(esc_url_raw(wp_unslash($redirect)));
            exit;
        } catch (\Exception $e) {
            if ($e->getMessage()) {
                wc_add_notice('<strong>' . __('Error:', 'woocommerce') . '</strong> ' . $e->getMessage(), 'error');
            }
        }
    }

    /**
     * @return void
     */
    public function process_otp_login(): void
    {
        if (
            empty($_POST['otp_register']) ||
            empty($_POST['email']) ||
            empty($_POST['_csrf_token']) ||
            !wp_verify_nonce(wp_unslash($_POST['_csrf_token']), 'wc_otp_register_nonce')
        ) {
            return;
        }

        $username = '';
        $password = '';
        $email = wp_unslash($_POST['email']);

        try {
            $validation_error = new \WP_Error();
            $validation_errors = $validation_error->get_error_messages();

            if (1 === count($validation_errors)) {
                throw new \Exception($validation_error->get_error_message());
            }

            if ($validation_errors) {
                foreach ($validation_errors as $message) {
                    wc_add_notice('<strong>' . __('Error:', 'woocommerce') . '</strong> ' . $message, 'error');
                }
                throw new \Exception();
            }

            $new_customer = $this->_wc_create_new_customer(sanitize_email($email), wc_clean($username), $password);

            if (is_wp_error($new_customer)) {
                throw new \Exception($new_customer->get_error_message());
            }

            // Send Email (respects cool-down)
            $result = $this->_wc_maybe_send_otp_email($new_customer, $email);
            if ($result === false) {
                $this->_wc_clear_otp_data($new_customer);
                throw new \Exception(__('OTP could not be sent.', TEXT_DOMAIN));
            }

            // Show an OTP form
            $this->_load_otp_form([
                'action' => esc_url(add_query_arg('action', self::ACTION_VALIDATE, wc_get_page_permalink('myaccount'))),
                'template' => 'myaccount/page-otp-login.php',
                'uid' => $new_customer,
                'send_at' => (int) get_user_meta($new_customer, self::META_LASTSEND, true),
            ]);
        } catch (\Exception $e) {
            if ($e->getMessage()) {
                wc_add_notice('<strong>' . __('Error:', 'woocommerce') . '</strong> ' . $e->getMessage(), 'error');
            }
        }
    }

    /* ---------- INTERNAL ------------------------------------------------ */

    /**
     * @param $args
     *
     * @return void
     */
    private function _load_otp_form($args): void
    {
        if (empty($args['template'])) {
            return;
        }

        $args = array_merge($args, [
            'otp_digits' => self::OTP_DIGITS,
            'resend_interval' => self::RESEND_INTERVAL,
            'redirect_to' => wc_get_page_permalink('myaccount'),
        ]);

        if (!empty($args['error'])) {
            wc_add_notice('<strong>' . __('Error:', 'woocommerce') . '</strong> ' . $args['error'], 'error');
        }

        wc_get_template($args['template'], $args);
        exit();
    }

    /**
     * @param int $customer_id
     *
     * @return void
     */
    private function _wc_clear_otp_data(int $customer_id = 0): void
    {
        delete_transient(sprintf(self::KEY_OTP, $customer_id));
        delete_transient(sprintf(self::KEY_ATTEMPT, $customer_id));
        delete_user_meta($customer_id, self::META_LASTSEND);
        delete_user_meta($customer_id, self::META_TOKEN);
    }

    /**
     * @param $customer_id
     * @param $email
     *
     * @return bool|null
     * @throws RandomException
     */
    private function _wc_maybe_send_otp_email($customer_id, $email): ?bool
    {
        $last_sent = (int) get_user_meta($customer_id, self::META_LASTSEND, true);
        if ($last_sent && (current_time('timestamp') - $last_sent) < self::RESEND_INTERVAL) {
            return null;
        }

        // generate OTP
        $otp = str_pad((string) random_int(0, (10 ** self::OTP_DIGITS) - 1), self::OTP_DIGITS, '0', STR_PAD_LEFT);

        $sent = \wp_mail(
            $email,
            __('Your One-Time OTP', TEXT_DOMAIN),
            sprintf(
                __("Hello %s,\n\nYour OTP is: %s\nThis code will expire in %s minutes.\n\nIf you didn't request this login, please ignore this email.", TEXT_DOMAIN),
                $email,
                $otp,
                self::OTP_LIFETIME / MINUTE_IN_SECONDS,
            )
        );

        if (!$sent) {
            return false;
        }

        // Success → store cool-down and transients
        update_user_meta($customer_id, self::META_LASTSEND, current_time('timestamp'));
        set_transient(sprintf(self::KEY_OTP, $customer_id), wp_hash($otp), self::OTP_LIFETIME);
        set_transient(sprintf(self::KEY_ATTEMPT, $customer_id), 0, self::OTP_LIFETIME);

        return true;
    }

    /**
     * @param $email
     * @param string $username
     * @param string $password
     * @param array $args
     *
     * @return int|\WP_Error
     * @throws RandomException
     */
    private function _wc_create_new_customer($email, string $username = '', string $password = '', array $args = []): \WP_Error|int
    {
        if (empty($email) || !is_email($email)) {
            return new \WP_Error('registration-error-invalid-email', __('Please provide a valid email address.', 'woocommerce'));
        }

        // ----------------------------------

        // return customer ID
        if ($customer_id = email_exists($email)) {
            return $customer_id;
        }

        // ----------------------------------

        if (empty($username)) {
            $username = wc_create_new_customer_username($email, $args);
        }

        $username = sanitize_user($username);
        if (empty($username) || !validate_username($username)) {
            $username = \HD_Helper::makeUsername(8);
        }

        if (username_exists($username)) {
            return new \WP_Error('registration-error-username-exists', __('An account is already registered with that username. Please choose another.', 'woocommerce'));
        }

        // Handle password creation.
        $password_generated = false;

        if (empty($password)) {
            $password = wp_generate_password();
            $password_generated = true;
        }

        if (empty($password)) {
            return new \WP_Error('registration-error-missing-password', __('Please create a password for your account.', 'woocommerce'));
        }

        // Use WP_Error to handle registration errors.
        $errors = new \WP_Error();

        /**
         * Fires before a customer account is registered.
         *
         * This hook fires before customer accounts are created and passes the form data (username, email) and an array
         * of errors.
         *
         * This could be used to add extra validation logic and append errors to the array.
         *
         * @param string $username Customer username.
         * @param string $user_email Customer email address.
         * @param \WP_Error $errors Error object.
         *
         * @internal Matches filter name in WooCommerce core.
         *
         * @since 7.2.0
         *
         */
        do_action('woocommerce_register_post', $username, $email, $errors);

        /**
         * Filters registration errors before a customer account is registered.
         *
         * This hook filters registration errors. This can be used to manipulate the array of errors before
         * they are displayed.
         *
         * @param \WP_Error $errors Error object.
         * @param string $username Customer username.
         * @param string $user_email Customer email address.
         *
         * @return \WP_Error
         * @since 7.2.0
         *
         * @internal Matches filter name in WooCommerce core.
         */
        $errors = apply_filters('woocommerce_registration_errors', $errors, $username, $email);

        if (is_wp_error($errors) && $errors->get_error_code()) {
            return $errors;
        }

        // Merged passed args with sanitized username, email, and password.
        $customer_data = array_merge(
            $args,
            array(
                'user_login' => $username,
                'user_pass' => $password,
                'user_email' => $email,
                'role' => 'customer',
            )
        );

        /**
         * Filters customer data before a customer account is registered.
         *
         * This hook filters customer data. It allows user data to be changed, for example, username, password, email,
         * first name, last name, and role.
         *
         * @param array $customer_data An array of customer (user) data.
         *
         * @return array
         * @since 7.2.0
         */
        $new_customer_data = apply_filters(
            'woocommerce_new_customer_data',
            wp_parse_args(
                $customer_data,
                array(
                    'first_name' => '',
                    'last_name' => '',
                    'source' => 'unknown',
                )
            )
        );

        $customer_id = wp_insert_user($new_customer_data);

        if (is_wp_error($customer_id)) {
            return $customer_id;
        }

        // Set account flag to remind customer to update generated password.
        if ($password_generated) {
            update_user_option($customer_id, 'default_password_nag', true, true);
        }

        /**
         * Fires after a customer account has been registered.
         *
         * This hook fires after customer accounts are created and passes the customer data.
         *
         * @param integer $customer_id New customer (user) ID.
         * @param array $new_customer_data Array of customer (user) data.
         * @param string $password_generated The generated password for the account.
         *
         * @internal Matches filter name in WooCommerce core.
         *
         * @since 7.2.0
         */
        do_action('woocommerce_created_customer', $customer_id, $new_customer_data, $password_generated);

        return $customer_id;
    }

    /**
     * @return void
     */
    public function wc_template_loop_product_link_open(): void
    {
        global $product;

        if (!($product instanceof \WC_Product)) {
            return;
        }

        $link = apply_filters('woocommerce_loop_product_link', get_the_permalink(), $product);

        echo '<a href="' . esc_url($link) . '" class="woocommerce-LoopProduct-link woocommerce-loop-product__link" title="' . esc_attr($product->get_title()) . '">';
    }

    /**
     * @return void
     */
    public function wc_template_loop_product_wishlist_button(): void
    {
        echo \HD_Helper::doShortcode('yith_wcwl_add_to_wishlist');
    }
}
