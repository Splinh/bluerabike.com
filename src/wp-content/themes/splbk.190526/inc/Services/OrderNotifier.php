<?php

namespace HD\Services;

use HD\Utilities\Traits\Singleton;

\defined('ABSPATH') || die;

/**
 * OrderNotifier Service
 *
 * Sends WooCommerce order notifications to configured channels:
 * - Zalo OA Group API (with OAuth token auto-refresh)
 * - Webhook (Slack, Discord, n8n, custom)
 * - Telegram Bot API
 *
 * @author Gaudev
 */
final class OrderNotifier
{
    use Singleton;

    private const LOG_OPTION = 'on_notification_log';
    private const MAX_LOG_ENTRIES = 10;
    private const ZALO_TOKEN_OPTION = 'on_zalo_access_token_data';

    private function init(): void
    {
        if (!\HD_Helper::isWoocommerceActive()) {
            return;
        }

        // New order created
        add_action('woocommerce_new_order', [$this, 'onNewOrder'], 20, 2);

        // Order status changed
        add_action('woocommerce_order_status_changed', [$this, 'onStatusChanged'], 20, 4);

        // AJAX test endpoint
        add_action('wp_ajax_test_order_notification', [$this, 'ajaxTest']);
    }

    /* ─── EVENT HANDLERS ─────────────────────────────────────── */

    public function onNewOrder(int $order_id, $order = null): void
    {
        if (!$this->isEventEnabled('new_order')) {
            return;
        }

        if (!$order) {
            $order = wc_get_order($order_id);
        }

        if (!$order) {
            return;
        }

        $message = $this->formatNewOrderMessage($order);
        $this->dispatch($message, 'new_order', $order_id);
    }

    public function onStatusChanged(int $order_id, string $old_status, string $new_status, $order): void
    {
        if (!$this->isEventEnabled($new_status)) {
            return;
        }

        // Skip if this is also a new order (avoid double notification)
        if ($old_status === 'new' || $old_status === 'checkout-draft') {
            return;
        }

        if (!$order) {
            $order = wc_get_order($order_id);
        }

        if (!$order) {
            return;
        }

        $message = $this->formatStatusChangeMessage($order, $old_status, $new_status);
        $this->dispatch($message, 'status_' . $new_status, $order_id);
    }

    public function ajaxTest(): void
    {
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => 'Không có quyền truy cập']);
        }

        $results = [];
        $test_message = $this->getTestMessage();

        // Test Zalo OA
        if ($this->isChannelEnabled('zalo')) {
            $result = $this->sendZaloOA($test_message, 'test', 0);
            $results[] = 'Zalo OA: ' . ($result ? '✅ Thành công' : '❌ Thất bại');
        }

        // Test Webhook
        if ($this->isChannelEnabled('webhook')) {
            $result = $this->sendWebhook($test_message, 'test', 0);
            $results[] = 'Webhook: ' . ($result ? '✅ Thành công' : '❌ Thất bại');
        }

        // Test Telegram
        if ($this->isChannelEnabled('telegram')) {
            $result = $this->sendTelegram($test_message, 'test', 0);
            $results[] = 'Telegram: ' . ($result ? '✅ Thành công' : '❌ Thất bại');
        }

        if (empty($results)) {
            wp_send_json_error(['message' => 'Chưa bật kênh nào. Vui lòng bật Zalo OA, Webhook hoặc Telegram và lưu cài đặt.']);
        }

        wp_send_json_success(['message' => implode(' | ', $results)]);
    }

    /* ─── DISPATCHER ─────────────────────────────────────────── */

    private function dispatch(array $message, string $event, int $order_id): void
    {
        $log_entries = [];

        $channels = [
            'zalo'     => 'sendZaloOA',
            'webhook'  => 'sendWebhook',
            'telegram' => 'sendTelegram',
        ];

        foreach ($channels as $name => $method) {
            if ($this->isChannelEnabled($name)) {
                $ok = $this->$method($message, $event, $order_id);
                $log_entries[] = sprintf(
                    '[%s] %s %s — Order #%d (%s)',
                    current_time('Y-m-d H:i:s'),
                    ucfirst($name),
                    $ok ? 'OK' : 'FAIL',
                    $order_id,
                    $event
                );
            }
        }

        if (!empty($log_entries)) {
            $this->appendLog($log_entries);
        }
    }

    /* ─── CHANNELS ───────────────────────────────────────────── */

    private function sendWebhook(array $message, string $event, int $order_id): bool
    {
        $url = get_field('on_webhook_url', 'option');
        if (empty($url)) {
            return false;
        }

        $headers = [
            'Content-Type' => 'application/json',
            'X-Event-Type' => $event,
        ];

        // Add secret header
        $secret = get_field('on_webhook_secret', 'option');
        if (!empty($secret)) {
            $headers['X-Webhook-Secret'] = $secret;
        }

        // Parse custom headers
        $custom_headers = get_field('on_webhook_headers', 'option');
        if (!empty($custom_headers)) {
            foreach (explode("\n", $custom_headers) as $line) {
                $line = trim($line);
                if (empty($line) || !str_contains($line, ':')) {
                    continue;
                }
                [$key, $value] = explode(':', $line, 2);
                $headers[trim($key)] = trim($value);
            }
        }

        $payload = [
            'event'    => $event,
            'order_id' => $order_id,
            'text'     => $message['text'],
            'data'     => $message['data'] ?? [],
            'site'     => get_bloginfo('name'),
            'time'     => current_time('c'),
        ];

        $response = wp_remote_post($url, [
            'timeout'  => 15,
            'headers'  => $headers,
            'body'     => wp_json_encode($payload, JSON_UNESCAPED_UNICODE),
        ]);

        if (is_wp_error($response)) {
            error_log('[OrderNotifier] Webhook error: ' . $response->get_error_message());
            return false;
        }

        $code = wp_remote_retrieve_response_code($response);
        return $code >= 200 && $code < 300;
    }

    private function sendZaloOA(array $message, string $event, int $order_id): bool
    {
        $group_id = get_field('on_zalo_group_id', 'option');
        if (empty($group_id)) {
            return false;
        }

        $access_token = $this->getZaloAccessToken();
        if (empty($access_token)) {
            error_log('[OrderNotifier] Zalo OA: Không lấy được access_token');
            return false;
        }

        // Strip HTML tags for Zalo (Zalo doesn't support HTML formatting)
        $plain_text = strip_tags($message['text']);

        $response = wp_remote_post('https://openapi.zalo.me/v3.0/oa/message/cs', [
            'timeout' => 15,
            'headers' => [
                'Content-Type'  => 'application/json',
                'access_token'  => $access_token,
            ],
            'body' => wp_json_encode([
                'recipient' => ['group_id' => $group_id],
                'message'   => ['text' => $plain_text],
            ], JSON_UNESCAPED_UNICODE),
        ]);

        if (is_wp_error($response)) {
            error_log('[OrderNotifier] Zalo OA error: ' . $response->get_error_message());
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        // Token expired → try refresh once and retry
        if (isset($body['error']) && $body['error'] === -216) {
            $access_token = $this->refreshZaloToken();
            if (empty($access_token)) {
                return false;
            }

            $response = wp_remote_post('https://openapi.zalo.me/v3.0/oa/message/cs', [
                'timeout' => 15,
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'access_token'  => $access_token,
                ],
                'body' => wp_json_encode([
                    'recipient' => ['group_id' => $group_id],
                    'message'   => ['text' => $plain_text],
                ], JSON_UNESCAPED_UNICODE),
            ]);

            if (is_wp_error($response)) {
                return false;
            }

            $body = json_decode(wp_remote_retrieve_body($response), true);
        }

        $success = isset($body['error']) && $body['error'] === 0;
        if (!$success) {
            error_log('[OrderNotifier] Zalo OA API error: ' . wp_json_encode($body));
        }

        return $success;
    }

    private function getZaloAccessToken(): string
    {
        $token_data = get_option(self::ZALO_TOKEN_OPTION, []);

        // Check if token exists and not expired (with 5 min buffer)
        if (
            !empty($token_data['access_token']) &&
            !empty($token_data['expires_at']) &&
            $token_data['expires_at'] > (time() + 300)
        ) {
            return $token_data['access_token'];
        }

        // Token expired or missing → refresh
        return $this->refreshZaloToken();
    }

    private function refreshZaloToken(): string
    {
        $app_id = get_field('on_zalo_app_id', 'option');
        $secret = get_field('on_zalo_secret', 'option');
        $refresh_token = get_field('on_zalo_refresh_token', 'option');

        if (empty($app_id) || empty($secret) || empty($refresh_token)) {
            error_log('[OrderNotifier] Zalo: Thiếu App ID, Secret hoặc Refresh Token');
            return '';
        }

        $response = wp_remote_post('https://oauth.zaloapp.com/v4/oa/access_token', [
            'timeout' => 15,
            'headers' => [
                'secret_key'   => $secret,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => [
                'app_id'        => $app_id,
                'grant_type'    => 'refresh_token',
                'refresh_token' => $refresh_token,
            ],
        ]);

        if (is_wp_error($response)) {
            error_log('[OrderNotifier] Zalo token refresh error: ' . $response->get_error_message());
            return '';
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (empty($body['access_token'])) {
            error_log('[OrderNotifier] Zalo token refresh failed: ' . wp_json_encode($body));
            return '';
        }

        // Store new tokens
        $token_data = [
            'access_token' => $body['access_token'],
            'expires_at'   => time() + ($body['expires_in'] ?? 3600),
        ];
        update_option(self::ZALO_TOKEN_OPTION, $token_data, false);

        // Update refresh token if a new one is returned
        if (!empty($body['refresh_token']) && function_exists('update_field')) {
            update_field('on_zalo_refresh_token', $body['refresh_token'], 'option');
        }

        return $body['access_token'];
    }

    private function sendTelegram(array $message, string $event, int $order_id): bool
    {
        $token = get_field('on_telegram_bot_token', 'option');
        $chat_id = get_field('on_telegram_chat_id', 'option');

        if (empty($token) || empty($chat_id)) {
            return false;
        }

        $url = sprintf('https://api.telegram.org/bot%s/sendMessage', $token);

        $response = wp_remote_post($url, [
            'timeout' => 15,
            'body'    => [
                'chat_id'    => $chat_id,
                'text'       => $message['text'],
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ],
        ]);

        if (is_wp_error($response)) {
            error_log('[OrderNotifier] Telegram error: ' . $response->get_error_message());
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        return !empty($body['ok']);
    }

    /* ─── MESSAGE FORMATTING ─────────────────────────────────── */

    private function formatNewOrderMessage($order): array
    {
        $items_text = '';
        foreach ($order->get_items() as $item) {
            $items_text .= sprintf(
                "  • %s x%d — %s\n",
                $item->get_name(),
                $item->get_quantity(),
                wc_price($item->get_total())
            );
        }

        // Strip HTML from wc_price for plain text
        $total = strip_tags(wc_price($order->get_total()));
        $items_plain = strip_tags($items_text);

        $text = sprintf(
            "🛒 <b>ĐƠN HÀNG MỚI #%d</b>\n\n" .
            "👤 Khách: %s\n" .
            "📞 SĐT: %s\n" .
            "📧 Email: %s\n" .
            "📍 Địa chỉ: %s\n\n" .
            "📦 Sản phẩm:\n%s\n" .
            "💰 Tổng: <b>%s</b>\n" .
            "💳 Thanh toán: %s\n",
            $order->get_id(),
            $order->get_formatted_billing_full_name(),
            $order->get_billing_phone(),
            $order->get_billing_email(),
            $order->get_formatted_billing_address(),
            $items_plain,
            $total,
            $order->get_payment_method_title()
        );

        $note = $order->get_customer_note();
        if (!empty($note)) {
            $text .= sprintf("📝 Ghi chú: %s\n", $note);
        }

        $admin_url = admin_url('post.php?post=' . $order->get_id() . '&action=edit');
        $text .= sprintf("\n🔗 <a href=\"%s\">Xem chi tiết</a>", $admin_url);

        return [
            'text' => $text,
            'data' => [
                'order_id'       => $order->get_id(),
                'customer_name'  => $order->get_formatted_billing_full_name(),
                'customer_phone' => $order->get_billing_phone(),
                'customer_email' => $order->get_billing_email(),
                'total'          => $order->get_total(),
                'currency'       => $order->get_currency(),
                'payment_method' => $order->get_payment_method_title(),
                'status'         => $order->get_status(),
                'items'          => array_map(fn($item) => [
                    'name' => $item->get_name(),
                    'qty'  => $item->get_quantity(),
                    'total' => $item->get_total(),
                ], array_values($order->get_items())),
            ],
        ];
    }

    private function formatStatusChangeMessage($order, string $old, string $new): array
    {
        $status_labels = [
            'pending'    => '⏳ Chờ thanh toán',
            'processing' => '⚙️ Đang xử lý',
            'on-hold'    => '⏸️ Tạm giữ',
            'completed'  => '✅ Hoàn thành',
            'cancelled'  => '❌ Đã hủy',
            'refunded'   => '💰 Hoàn tiền',
            'failed'     => '🚫 Thất bại',
        ];

        $total = strip_tags(wc_price($order->get_total()));

        $text = sprintf(
            "🔄 <b>CẬP NHẬT ĐƠN #%d</b>\n\n" .
            "📊 Trạng thái: %s → <b>%s</b>\n" .
            "👤 Khách: %s\n" .
            "📞 SĐT: %s\n" .
            "💰 Tổng: %s\n",
            $order->get_id(),
            $status_labels[$old] ?? $old,
            $status_labels[$new] ?? $new,
            $order->get_formatted_billing_full_name(),
            $order->get_billing_phone(),
            $total
        );

        $admin_url = admin_url('post.php?post=' . $order->get_id() . '&action=edit');
        $text .= sprintf("\n🔗 <a href=\"%s\">Xem chi tiết</a>", $admin_url);

        return [
            'text' => $text,
            'data' => [
                'order_id'      => $order->get_id(),
                'old_status'    => $old,
                'new_status'    => $new,
                'customer_name' => $order->get_formatted_billing_full_name(),
                'total'         => $order->get_total(),
            ],
        ];
    }

    private function getTestMessage(): array
    {
        return [
            'text' => sprintf(
                "🧪 <b>TEST THÔNG BÁO</b>\n\n" .
                "✅ Hệ thống thông báo đơn hàng hoạt động bình thường!\n" .
                "🌐 Website: %s\n" .
                "⏰ Thời gian: %s",
                get_bloginfo('name'),
                current_time('d/m/Y H:i:s')
            ),
            'data' => ['type' => 'test', 'site' => get_bloginfo('name')],
        ];
    }

    /* ─── HELPERS ─────────────────────────────────────────────── */

    private function isChannelEnabled(string $channel): bool
    {
        return (bool) get_field('on_' . $channel . '_enabled', 'option');
    }

    private function isEventEnabled(string $event): bool
    {
        $events = get_field('on_events', 'option');
        if (!is_array($events)) {
            return false;
        }
        return in_array($event, $events, true);
    }

    private function appendLog(array $new_entries): void
    {
        $log = get_option(self::LOG_OPTION, []);
        if (!is_array($log)) {
            $log = [];
        }

        $log = array_merge($new_entries, $log);
        $log = array_slice($log, 0, self::MAX_LOG_ENTRIES);

        update_option(self::LOG_OPTION, $log);

        // Also update ACF field for display
        if (function_exists('update_field')) {
            update_field('on_last_log', implode("\n", $log), 'option');
        }
    }
}
