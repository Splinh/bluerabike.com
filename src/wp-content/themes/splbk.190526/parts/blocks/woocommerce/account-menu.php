<?php
// account-menu.php

\defined('ABSPATH') || die;

$account_link = \function_exists('wc_get_page_permalink') ? esc_url(wc_get_page_permalink('myaccount')) : '#';
if (!$account_link) {
    return;
}

if (!is_user_logged_in()):

    ?>
    <div class="account-item not-logged-in">
        <a rel="nofollow" class="account-btn-link" href="<?= $account_link ?>" data-open="#login-form-popup"
            title="<?= esc_attr__('Đăng nhập / Đăng ký', TEXT_DOMAIN) ?>">
            <svg xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 640 512"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                <path
                    d="M320 16a104 104 0 1 1 0 208 104 104 0 1 1 0-208zM96 88a72 72 0 1 1 0 144 72 72 0 1 1 0-144zM0 416c0-70.7 57.3-128 128-128 12.8 0 25.2 1.9 36.9 5.4-32.9 36.8-52.9 85.4-52.9 138.6l0 16c0 11.4 2.4 22.2 6.7 32L32 480c-17.7 0-32-14.3-32-32l0-32zm521.3 64c4.3-9.8 6.7-20.6 6.7-32l0-16c0-53.2-20-101.8-52.9-138.6 11.7-3.5 24.1-5.4 36.9-5.4 70.7 0 128 57.3 128 128l0 32c0 17.7-14.3 32-32 32l-86.7 0zM472 160a72 72 0 1 1 144 0 72 72 0 1 1 -144 0zM160 432c0-88.4 71.6-160 160-160s160 71.6 160 160l0 16c0 17.7-14.3 32-32 32l-256 0c-17.7 0-32-14.3-32-32l0-16z" />
            </svg>
            <span>
                <strong class="title"><?php echo \HD_Helper::pll_text('Đăng nhập', 'Login'); ?></strong>

            </span>
        </a>
    </div>
<?php endif; ?>

<?php if (is_user_logged_in()):
    $current_user = wp_get_current_user();
    $display_name = $current_user->display_name;
    ?>
    <div class="account-item logged-in-as">
        <a data-toggle="account-dropdown" rel="nofollow" class="account-btn-link" href="<?= $account_link ?>"
            title="<?= esc_attr__('Tài khoản', TEXT_DOMAIN) ?>">
            <svg xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 640 512"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                <path
                    d="M320 16a104 104 0 1 1 0 208 104 104 0 1 1 0-208zM96 88a72 72 0 1 1 0 144 72 72 0 1 1 0-144zM0 416c0-70.7 57.3-128 128-128 12.8 0 25.2 1.9 36.9 5.4-32.9 36.8-52.9 85.4-52.9 138.6l0 16c0 11.4 2.4 22.2 6.7 32L32 480c-17.7 0-32-14.3-32-32l0-32zm521.3 64c4.3-9.8 6.7-20.6 6.7-32l0-16c0-53.2-20-101.8-52.9-138.6 11.7-3.5 24.1-5.4 36.9-5.4 70.7 0 128 57.3 128 128l0 32c0 17.7-14.3 32-32 32l-86.7 0zM472 160a72 72 0 1 1 144 0 72 72 0 1 1 -144 0zM160 432c0-88.4 71.6-160 160-160s160 71.6 160 160l0 16c0 17.7-14.3 32-32 32l-256 0c-17.7 0-32-14.3-32-32l0-16z" />
            </svg>
            <span>
                <strong class="title"><?= esc_html($display_name) ?></strong>
            </span>
        </a>
        <div class="logged-in-dropdown dropdown-pane" id="account-dropdown" data-dropdown data-alignment="right"
            data-hover="true" data-hover-pane="true">
            <ul>
                <?php foreach (wc_get_account_menu_items() as $endpoint => $label): ?>
                    <li class="<?php echo wc_get_account_menu_item_classes($endpoint); ?>">
                        <a href="<?php echo esc_url(wc_get_account_endpoint_url($endpoint)); ?>"
                            title="<?php echo esc_attr($label); ?>">
                            <?php echo esc_html($label); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
<?php endif; ?>