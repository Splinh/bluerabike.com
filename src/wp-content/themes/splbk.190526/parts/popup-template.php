<?php

/**
 * Promotional Popup Template
 *
 * @author Gaudev
 */

\defined('ABSPATH') || die;

// Get ACF fields
$popup_enabled      = \HD_Helper::getField('popup_enabled', 'option');
$popup_title        = \HD_Helper::getField('popup_title', 'option');
$popup_offers       = \HD_Helper::getField('popup_offers', 'option');
$popup_image        = \HD_Helper::getField('popup_image', 'option');
$popup_name_ph      = \HD_Helper::getField('popup_form_placeholder_name', 'option') ?: __('Họ và tên', TEXT_DOMAIN);
$popup_phone_ph     = \HD_Helper::getField('popup_form_placeholder_phone', 'option') ?: __('Số điện thoại', TEXT_DOMAIN);
$popup_button_text  = \HD_Helper::getField('popup_form_button_text', 'option') ?: __('Đăng ký ưu đãi ngay', TEXT_DOMAIN);
$popup_seasonal_effect = \HD_Helper::getField('popup_seasonal_effect', 'option') ?: 'none';
$cookie_duration    = \HD_Helper::getField('popup_cookie_duration', 'option') ?: 7;
$popup_delay        = \HD_Helper::getField('popup_delay', 'option') ?: 2000;

// Exit if popup is disabled
if (!$popup_enabled) {
    return;
}
?>

<div id="promo-popup-overlay" class="promo-popup-overlay" data-cookie-days="<?= esc_attr($cookie_duration) ?>" data-delay="<?= esc_attr($popup_delay) ?>" data-effect="<?= esc_attr($popup_seasonal_effect) ?>">
    <!-- Snow Container (if enabled) - Full Overlay Background -->
    <?php if ($popup_seasonal_effect && $popup_seasonal_effect !== 'none') : ?>
        <div class="promo-popup-effect" aria-hidden="true"></div>
    <?php endif; ?>

    <div class="promo-popup-container">
        <!-- Close Button -->
        <button class="promo-popup-close" aria-label="<?= esc_attr__('Close popup', TEXT_DOMAIN) ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>

        <!-- Popup Content -->
        <div class="promo-popup-content">
            <!-- Title Section -->
            <?php if ($popup_title) : ?>
                <div class="promo-popup-header">
                    <div class="promo-popup-icon">
                        <?php echo \HD_Helper::siteTitleOrLogo(); ?>
                    </div>
                    <h2 class="promo-popup-title"><?= $popup_title ?></h2>
                </div>
            <?php endif; ?>

            <!-- Promotional Content Section -->
            <div class="promo-popup-offers-wrapper">
                <?php if ($popup_offers && is_array($popup_offers)) : ?>
                    <div class="promo-popup-offers">
                        <?php foreach ($popup_offers as $offer) :
                            $icon = $offer['icon'] ?? '';
                            $text = $offer['text'] ?? '';
                        ?>
                            <div class="promo-offer-item">
                                <?php if ($icon) : ?>
                                    <div class="promo-offer-icon">
                                        <?= wp_get_attachment_image($icon, 'thumbnail') ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($text) : ?>
                                    <div class="promo-offer-text">
                                        <?= $text ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($popup_image) : ?>
                    <div class="promo-popup-image">
                        <?= wp_get_attachment_image($popup_image, 'medium') ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Form Section -->
            <div class="promo-popup-form-wrapper">
                <form class="promo-popup-form" id="promo-popup-form">
                    <input
                        type="text"
                        name="customer_name"
                        placeholder="<?= esc_attr($popup_name_ph) ?>"
                        required
                        class="promo-form-input">
                    <input
                        type="tel"
                        name="customer_phone"
                        placeholder="<?= esc_attr($popup_phone_ph) ?>"
                        required
                        pattern="[0-9]{10,11}"
                        class="promo-form-input">
                    <button type="submit" class="promo-form-submit">
                        <?= esc_html($popup_button_text) ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>