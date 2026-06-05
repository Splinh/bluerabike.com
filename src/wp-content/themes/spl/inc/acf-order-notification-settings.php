<?php
/**
 * ACF Order Notification Settings
 * Admin settings for multi-channel order notifications (Webhook/Zalo Group, Telegram)
 * @author Gaudev
 */
\defined('ABSPATH') || die;

if (function_exists('acf_add_options_page')) {
    acf_add_options_page([
        'page_title' => __('Thông Báo Đơn Hàng', TEXT_DOMAIN),
        'menu_title' => __('🔔 Thông Báo Đơn', TEXT_DOMAIN),
        'menu_slug'  => 'order-notification-settings',
        'capability' => 'manage_woocommerce',
        'icon_url'   => 'dashicons-bell',
        'position'   => 56,
    ]);
}

if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group([
        'key'    => 'group_order_notification',
        'title'  => __('Cài Đặt Thông Báo Đơn Hàng', TEXT_DOMAIN),
        'fields' => [
            // TAB: WEBHOOK
            ['key' => 'field_on_tab_webhook', 'label' => '🔗 Webhook (Zalo Group)', 'type' => 'tab'],
            [
                'key' => 'field_on_webhook_enabled', 'label' => 'Bật Webhook', 'name' => 'on_webhook_enabled',
                'type' => 'true_false', 'default_value' => 0, 'ui' => 1,
                'instructions' => 'Gửi thông báo đơn hàng qua Webhook URL (Zalo Group, Slack, Discord, n8n...)',
            ],
            [
                'key' => 'field_on_webhook_url', 'label' => 'Webhook URL', 'name' => 'on_webhook_url',
                'type' => 'url', 'placeholder' => 'https://',
                'instructions' => 'URL endpoint nhận webhook POST request',
                'conditional_logic' => [[['field' => 'field_on_webhook_enabled', 'operator' => '==', 'value' => '1']]],
            ],
            [
                'key' => 'field_on_webhook_secret', 'label' => 'Webhook Secret (tùy chọn)', 'name' => 'on_webhook_secret',
                'type' => 'text', 'placeholder' => 'your-secret-key',
                'instructions' => 'Secret key xác thực (gửi trong header X-Webhook-Secret)',
                'conditional_logic' => [[['field' => 'field_on_webhook_enabled', 'operator' => '==', 'value' => '1']]],
            ],
            [
                'key' => 'field_on_webhook_headers', 'label' => 'Custom Headers (tùy chọn)', 'name' => 'on_webhook_headers',
                'type' => 'textarea', 'rows' => 3,
                'placeholder' => "Authorization: Bearer token\nX-Custom: value",
                'instructions' => 'Mỗi header trên 1 dòng: Header-Name: value',
                'conditional_logic' => [[['field' => 'field_on_webhook_enabled', 'operator' => '==', 'value' => '1']]],
            ],

            // TAB: ZALO OA GROUP
            ['key' => 'field_on_tab_zalo', 'label' => '💬 Zalo OA Group', 'type' => 'tab'],
            [
                'key' => 'field_on_zalo_enabled', 'label' => 'Bật Zalo OA', 'name' => 'on_zalo_enabled',
                'type' => 'true_false', 'default_value' => 0, 'ui' => 1,
                'instructions' => 'Gửi thông báo đơn hàng vào nhóm Zalo qua Zalo OA API',
            ],
            [
                'key' => 'field_on_zalo_app_id', 'label' => 'App ID', 'name' => 'on_zalo_app_id',
                'type' => 'text', 'placeholder' => '1234567890',
                'instructions' => 'App ID từ <a href="https://developers.zalo.me/app" target="_blank">developers.zalo.me</a>',
                'conditional_logic' => [[['field' => 'field_on_zalo_enabled', 'operator' => '==', 'value' => '1']]],
            ],
            [
                'key' => 'field_on_zalo_secret', 'label' => 'Secret Key', 'name' => 'on_zalo_secret',
                'type' => 'text', 'placeholder' => 'your-secret-key',
                'instructions' => 'Secret Key của ứng dụng Zalo',
                'conditional_logic' => [[['field' => 'field_on_zalo_enabled', 'operator' => '==', 'value' => '1']]],
            ],
            [
                'key' => 'field_on_zalo_refresh_token', 'label' => 'Refresh Token', 'name' => 'on_zalo_refresh_token',
                'type' => 'textarea', 'rows' => 2,
                'instructions' => 'Refresh Token từ OAuth flow. Hệ thống sẽ tự động refresh access_token. <br><em>Lấy qua: developers.zalo.me → App → Công cụ → Lấy Access Token</em>',
                'conditional_logic' => [[['field' => 'field_on_zalo_enabled', 'operator' => '==', 'value' => '1']]],
            ],
            [
                'key' => 'field_on_zalo_group_id', 'label' => 'Group ID', 'name' => 'on_zalo_group_id',
                'type' => 'text', 'placeholder' => 'group-id-here',
                'instructions' => 'ID nhóm Zalo mà OA đã tham gia. Lấy qua webhook event hoặc API quản lý nhóm.',
                'conditional_logic' => [[['field' => 'field_on_zalo_enabled', 'operator' => '==', 'value' => '1']]],
            ],
            [
                'key' => 'field_on_zalo_token_status', 'label' => 'Trạng thái Token', 'name' => 'on_zalo_token_status',
                'type' => 'message', 'message' => '<div id="zalo-token-status">Kiểm tra sau khi lưu cài đặt</div>',
                'conditional_logic' => [[['field' => 'field_on_zalo_enabled', 'operator' => '==', 'value' => '1']]],
            ],

            // TAB: TELEGRAM
            ['key' => 'field_on_tab_telegram', 'label' => '🤖 Telegram', 'type' => 'tab'],
            [
                'key' => 'field_on_telegram_enabled', 'label' => 'Bật Telegram', 'name' => 'on_telegram_enabled',
                'type' => 'true_false', 'default_value' => 0, 'ui' => 1,
                'instructions' => 'Gửi thông báo đơn hàng vào nhóm/kênh Telegram',
            ],
            [
                'key' => 'field_on_telegram_bot_token', 'label' => 'Bot Token', 'name' => 'on_telegram_bot_token',
                'type' => 'text', 'placeholder' => '123456789:ABCdefGhIjKlMnOpQrStUvWxYz',
                'instructions' => 'Token từ @BotFather',
                'conditional_logic' => [[['field' => 'field_on_telegram_enabled', 'operator' => '==', 'value' => '1']]],
            ],
            [
                'key' => 'field_on_telegram_chat_id', 'label' => 'Chat ID', 'name' => 'on_telegram_chat_id',
                'type' => 'text', 'placeholder' => '-1001234567890',
                'instructions' => 'Chat ID nhóm (thường bắt đầu bằng -100)',
                'conditional_logic' => [[['field' => 'field_on_telegram_enabled', 'operator' => '==', 'value' => '1']]],
            ],

            // TAB: EVENTS
            ['key' => 'field_on_tab_events', 'label' => '⚡ Sự Kiện', 'type' => 'tab'],
            [
                'key' => 'field_on_events', 'label' => 'Gửi thông báo khi', 'name' => 'on_events',
                'type' => 'checkbox', 'layout' => 'vertical',
                'choices' => [
                    'new_order'  => '🛒 Đơn hàng mới',
                    'processing' => '⚙️ Đang xử lý',
                    'completed'  => '✅ Hoàn thành',
                    'cancelled'  => '❌ Đã hủy',
                    'refunded'   => '💰 Hoàn tiền',
                    'on-hold'    => '⏸️ Tạm giữ',
                ],
                'default_value' => ['new_order', 'processing', 'cancelled'],
                'instructions' => 'Chọn các sự kiện bạn muốn nhận thông báo',
            ],

            // TAB: TEST
            ['key' => 'field_on_tab_test', 'label' => '🧪 Test', 'type' => 'tab'],
            [
                'key' => 'field_on_test_info', 'label' => 'Gửi Test', 'name' => '', 'type' => 'message',
                'message' => '<p><button type="button" class="button button-primary" id="btn-test-on" onclick="testON()">📤 Gửi Test Ngay</button></p><div id="test-on-result" style="margin-top:10px"></div><script>function testON(){var b=document.getElementById("btn-test-on"),r=document.getElementById("test-on-result");b.disabled=true;b.textContent="Đang gửi...";r.innerHTML="";fetch(ajaxurl+"?action=test_order_notification&_wpnonce="+(document.querySelector("[name=_wpnonce]")||{}).value).then(function(x){return x.json()}).then(function(d){r.innerHTML=d.success?"<div class=notice notice-success inline><p>"+d.data.message+"</p></div>":"<div class=notice notice-error inline><p>"+(d.data&&d.data.message||"Lỗi")+"</p></div>"}).catch(function(e){r.innerHTML="<div class=notice notice-error inline><p>"+e.message+"</p></div>"}).finally(function(){b.disabled=false;b.textContent="📤 Gửi Test Ngay"})}</script>',
            ],
            [
                'key' => 'field_on_last_log', 'label' => 'Log gần nhất', 'name' => 'on_last_log',
                'type' => 'textarea', 'readonly' => 1, 'rows' => 8,
                'instructions' => 'Log 10 thông báo gần nhất (tự động cập nhật)',
            ],
        ],
        'location' => [[['param' => 'options_page', 'operator' => '==', 'value' => 'order-notification-settings']]],
        'style' => 'default',
    ]);
}
