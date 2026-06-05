<?php

/**
 * Cookie Consent Banner Template
 * 
 * @author SPL Theme
 */

\defined('ABSPATH') || die;

// Get policy page URL - defaults to empty
$policy_url = function_exists('get_privacy_policy_url') ? get_privacy_policy_url() : '';
if (empty($policy_url)) {
    // Try to find the privacy policy page by slug
    $policy_page = get_page_by_path('chinh-sach-bao-mat');
    if ($policy_page) {
        $policy_url = get_permalink($policy_page->ID);
    }
}
?>

<div id="cookie-consent-banner" class="cookie-consent" style="display: none;">
    <div class="cookie-consent__container">
        <div class="cookie-consent__icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10a10 10 0 0 0-.5-3.125c-.11.043-.233.089-.367.125-.357.09-.777.125-1.133.125a3 3 0 0 1-3-3c0-.356.035-.776.125-1.133.036-.134.082-.256.125-.367A10 10 0 0 0 12 2zm-1 4a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm-3.5 3a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zM12 10a2 2 0 1 1 0 4 2 2 0 0 1 0-4zm4.5 2.5a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zM9 15a1 1 0 1 1 0 2 1 1 0 0 1 0-2z" />
            </svg>
        </div>
        <div class="cookie-consent__content">
            <p class="cookie-consent__text">
                <?php
                echo esc_html__(
                    'Chúng tôi sử dụng cookie để đảm bảo rằng chúng tôi cung cấp cho bạn trải nghiệm tốt nhất trên trang web của chúng tôi. Nếu bạn tiếp tục sử dụng trang web này, chúng tôi sẽ cho rằng bạn hài lòng với nó.',
                    'theme-spl'
                );
                ?>
            </p>
        </div>
        <div class="cookie-consent__actions">
            <?php if ($policy_url) : ?>
                <a href="<?php echo esc_url($policy_url); ?>" class="cookie-consent__btn cookie-consent__btn--info" target="_blank" rel="noopener">
                    <?php echo esc_html__('Thêm thông tin', 'theme-spl'); ?>
                </a>
            <?php endif; ?>
            <button type="button" id="cookie-consent-accept" class="cookie-consent__btn cookie-consent__btn--accept">
                <?php echo esc_html__('Chấp nhận', 'theme-spl'); ?>
            </button>
        </div>
    </div>
</div>