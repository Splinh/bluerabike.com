<?php

/**
 * Customize Payment Buttons - Bao Kim & Quick Buy
 * 
 * @package SPL
 */

defined('ABSPATH') || exit;

/**
 * 1. Change "Mua ngay" button text to "Thanh Toán Trực Tuyến"
 * And add subtitle via JavaScript
 */
add_action('wp_footer', 'aiebike_customize_buy_now_button', 98);
function aiebike_customize_buy_now_button()
{
    if (!is_product()) return;
?>
    <style>
        /* ONLY in single product summary section - exclude related/upsells */
        .single-product .summary form.cart:not(.related form.cart):not(.upsells form.cart) {
            gap: 0 !important;
            margin-bottom: 0 !important;
        }

        /* Buy Now button in summary only - exclude related/upsells */
        .single-product .summary .buy-now-button {
            margin-top: 5px !important;
            margin-bottom: 0 !important;
        }

        /* Woosb buttons in summary only */
        .single-product .summary .woosb-btn,
        .single-product .summary .single_add_to_cart_button.button.alt:not(.add_to_cart_button) {
            display: flex !important;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 12px 20px !important;
            text-align: center;
        }

        .single-product .summary .buy-now-subtitle {
            display: block;
            font-size: 12px;
            font-weight: normal;
            opacity: 0.9;
            margin-top: 3px;
        }

        /* Fix: Reset related/upsells product buttons to normal */
        .single-product .related .woosb-btn,
        .single-product .upsells .woosb-btn,
        .single-product .cross-sells .woosb-btn {
            display: inline-flex !important;
            flex-direction: row !important;
            padding: 8px 12px !important;
        }

        /* Fix: Reset actions_button height in related products */
        .single-product .related .actions_button,
        .single-product .upsells .actions_button,
        .product_size .custom-product-wrapper .product .actions_button {
            height: auto !important;
            min-height: unset !important;
        }
    </style>
    <script>
        (function($) {
            function customizeBuyNowButton() {
                // Try multiple selectors for "Mua ngay" button
                var selectors = [
                    '.woosb-btn',
                    '.woosb-btn-buy_now',
                    '.woosq-btn',
                    'button[name="woosb-single"]',
                    '.single_add_to_cart_button.button.alt:not(.add_to_cart_button)',
                    '.button.woosb-btn'
                ];

                var buyNowBtn = null;

                // Try each selector
                for (var i = 0; i < selectors.length; i++) {
                    buyNowBtn = document.querySelector(selectors[i]);
                    if (buyNowBtn) break;
                }

                // If no button found by selector, find by text content
                if (!buyNowBtn) {
                    var allButtons = document.querySelectorAll('button, a.button');
                    for (var j = 0; j < allButtons.length; j++) {
                        var btnText = allButtons[j].textContent.trim().toLowerCase();
                        if (btnText === 'mua ngay' || btnText.includes('mua ngay')) {
                            buyNowBtn = allButtons[j];
                            break;
                        }
                    }
                }

                if (buyNowBtn && !buyNowBtn.classList.contains('aiebike-customized')) {
                    buyNowBtn.classList.add('aiebike-customized');

                    // Check current text
                    var currentText = buyNowBtn.textContent.trim().toLowerCase();
                    if (currentText.includes('mua ngay')) {
                        buyNowBtn.innerHTML = '<strong style="color:#fff;">THANH TOÁN TRỰC TUYẾN</strong><span class="buy-now-subtitle" style="color:#fff;">Qua website chính thức</span>';
                    }

                    console.log('AIEbike: Buy Now button customized', buyNowBtn);
                }
            }

            // Run on load with delay
            if (document.readyState === 'complete') {
                setTimeout(customizeBuyNowButton, 1000);
            } else {
                window.addEventListener('load', function() {
                    setTimeout(customizeBuyNowButton, 1000);
                });
            }

            // Re-run on variation change
            $(document).on('found_variation', function() {
                setTimeout(customizeBuyNowButton, 500);
            });

            // Also run after a longer delay in case of lazy loading
            setTimeout(customizeBuyNowButton, 3000);
        })(jQuery);
    </script>
<?php
}

/**
 * 2. Customize Bao Kim Buttons via JavaScript
 */
add_action('wp_footer', 'aiebike_customize_baokim_buttons', 99);
function aiebike_customize_baokim_buttons()
{
    if (!is_product()) return;
?>
    <style>
        /* Bao Kim Promotion Section - Collapsible */
        .bk-promotion-title {
            cursor: pointer;
            user-select: none;
            position: relative;
        }

        .bk-promotion-title::after {
            content: " ▼";
            font-size: 10px;
            margin-left: 5px;
        }

        .bk-promotion-title.collapsed::after {
            content: " ▶";
        }

        .bk-promotion-content.collapsed {
            display: none !important;
        }

        /* Bao Kim Button Styling - Equal spacing */
        .bk-btn-paynow,
        .bk-btn-installment,
        .bk-btn-installment-amigo {
            display: block;
            width: 100%;
            padding: 12px 20px !important;
            font-size: 16px !important;
            line-height: 1.4 !important;
            border: none !important;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
            margin-top: 5px !important;
            margin-bottom: 0 !important;
        }

        /* Red button - Bao Kim Pay Now */
        .bk-btn-paynow {
            background: linear-gradient(135deg, #0095d9, #0077b5) !important;
            color: #fff !important;
        }

        /* Yellow button - Installment */
        .bk-btn-installment {
            background: linear-gradient(135deg, #f7c600, #f7a600) !important;
            color: #333 !important;
        }

        /* HomePaylater button */
        .bk-btn-installment-amigo {
            background: linear-gradient(135deg, #ffe600, #ffd000) !important;
            color: #333 !important;
        }

        .bk-btn-paynow strong,
        .bk-btn-installment strong,
        .bk-btn-installment-amigo strong {
            font-size: 16px !important;
            text-transform: uppercase;
            display: block;
        }
    </style>
    <script>
        (function($) {
            function customizeBaokim() {
                // Nút ĐỎ -> XANH (bk-btn-paynow)
                var btnPaynow = document.querySelector('.bk-btn-paynow');
                if (btnPaynow && !btnPaynow.classList.contains('bk-customized')) {
                    btnPaynow.classList.add('bk-customized');
                    var mainText = btnPaynow.querySelector('strong');
                    if (mainText) mainText.textContent = 'Thanh Toán Qua Bảo Kim';
                    var subText = btnPaynow.querySelector('span:not(.d-flex)');
                    if (subText) subText.textContent = 'Nhanh chóng - Bảo Mật - Tiện lợi';
                }

                // Nút VÀNG (bk-btn-installment)
                var btnInstallment = document.querySelector('.bk-btn-installment');
                if (btnInstallment && !btnInstallment.classList.contains('bk-customized')) {
                    btnInstallment.classList.add('bk-customized');
                    var mainText = btnInstallment.querySelector('strong');
                    if (mainText) mainText.textContent = 'Trả Góp Qua Thẻ Tín Dụng';
                    var subText = btnInstallment.querySelector('span:not(.d-flex)');
                    if (subText) subText.textContent = 'Visa - Master - JCB';
                }

                // Nút HomePaylater (bk-btn-installment-amigo)
                var btnAmigo = document.querySelector('.bk-btn-installment-amigo');
                if (btnAmigo && !btnAmigo.classList.contains('bk-customized')) {
                    btnAmigo.classList.add('bk-customized');
                    var mainText = btnAmigo.querySelector('strong');
                    if (mainText) mainText.textContent = 'Mua Trước Trả Sau (0%)';
                    // Remove old subtitle if exists
                    var oldSubtitle = btnAmigo.querySelector('.bk-subtitle-custom');
                    if (oldSubtitle) oldSubtitle.remove();
                }

                // Ưu đãi thanh toán collapsible
                var promoTitle = document.querySelector('.bk-promotion-title');
                var promoContent = document.querySelector('.bk-promotion-content');
                if (promoTitle && promoContent && !promoTitle.classList.contains('bk-customized')) {
                    promoTitle.classList.add('bk-customized', 'collapsed');
                    promoContent.classList.add('collapsed');
                    promoTitle.addEventListener('click', function() {
                        this.classList.toggle('collapsed');
                        promoContent.classList.toggle('collapsed');
                    });
                }
            }

            // Run on page load
            if (document.readyState === 'complete') {
                setTimeout(customizeBaokim, 2000);
            } else {
                window.addEventListener('load', function() {
                    setTimeout(customizeBaokim, 2000);
                });
            }

            // Re-run when variation changes
            $(document).on('found_variation', function() {
                setTimeout(customizeBaokim, 1000);
            });
        })(jQuery);
    </script>
<?php
}
