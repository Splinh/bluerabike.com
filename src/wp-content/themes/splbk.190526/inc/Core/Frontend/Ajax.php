<?php

namespace HD\Core\Frontend;

use HD\Utilities\Traits\Singleton;

\defined('ABSPATH') || die;

/**
 * Ajax Class
 *
 * @author Gaudev
 */
final class Ajax
{
    use Singleton;

    // --------------------------------------------------

    private function init(): void
    {
        // Landing page form submission
        add_action('wp_ajax_lp_submit_form', [$this, 'handleLandingFormSubmit']);
        add_action('wp_ajax_nopriv_lp_submit_form', [$this, 'handleLandingFormSubmit']);
    }

	// -----------------------------------------------

    /**
     * Handle landing page (Cơ hội hợp tác) form submission
     */
    public function handleLandingFormSubmit(): void
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['_nonce'] ?? '', 'lp_form_nonce')) {
            wp_send_json_error(['message' => 'Xác thực không hợp lệ. Vui lòng tải lại trang.'], 403);
        }

        // Rate limiting: 1 submission per 60 seconds per IP
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $transient_key = 'lp_form_' . md5($ip);
        if (get_transient($transient_key)) {
            wp_send_json_error(['message' => 'Bạn đã gửi form gần đây. Vui lòng thử lại sau 1 phút.'], 429);
        }

        // Sanitize inputs
        $fullname = sanitize_text_field($_POST['fullname'] ?? '');
        $phone    = sanitize_text_field($_POST['phone'] ?? '');
        $region   = sanitize_text_field($_POST['region'] ?? '');
        $message  = sanitize_textarea_field($_POST['message'] ?? '');

        // Validate required fields
        if (empty($fullname) || empty($phone) || empty($region) || empty($message)) {
            wp_send_json_error(['message' => 'Vui lòng điền đầy đủ tất cả các trường bắt buộc.'], 422);
        }

        // Validate phone format (Vietnam)
        if (!preg_match('/^(0|\+84)[0-9]{9,10}$/', preg_replace('/[\s\-\.]/', '', $phone))) {
            wp_send_json_error(['message' => 'Số điện thoại không hợp lệ.'], 422);
        }

        // Build email
        $admin_email = get_option('admin_email');
        $site_name   = get_bloginfo('name');
        $subject     = "[{$site_name}] Đăng ký đại lý mới - {$fullname}";

        /**
         * ====================================================
         * CẤU HÌNH EMAIL NHẬN FORM
         * Thêm/bớt email tại đây, mỗi email một dòng
         * ====================================================
         */
        $recipients = [
            $admin_email,                          // Email admin mặc định (Settings > General)
            'dailyxedien.com.vn@gmail.com',  // Ví dụ: thêm email ở đây
            // 'splworks.info@gmail.com',              // Ví dụ: email phòng kinh doanh
        ];

        // Lọc email hợp lệ + loại trùng
        $recipients = array_unique(array_filter($recipients, 'is_email'));
        $to = implode(', ', $recipients);

        $body = $this->buildEmailBody($fullname, $phone, $region, $message);

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            "From: {$site_name} <{$admin_email}>",
            "Reply-To: {$fullname} <{$admin_email}>",
        ];

        $sent = wp_mail($to, $subject, $body, $headers);

        if ($sent) {
            // Set rate limit
            set_transient($transient_key, true, 60);

            wp_send_json_success([
                'message' => 'Đăng ký thành công! Chúng tôi sẽ liên hệ bạn trong thời gian sớm nhất.'
            ]);
        } else {
            wp_send_json_error([
                'message' => 'Không thể gửi email. Vui lòng thử lại hoặc liên hệ Hotline.'
            ], 500);
        }
    }

	// -----------------------------------------------

    /**
     * Build HTML email body
     */
    private function buildEmailBody(string $fullname, string $phone, string $region, string $message): string
    {
        $date = wp_date('d/m/Y H:i');
        $site_name = get_bloginfo('name');

        $region_row = '';
        if (!empty($region)) {
            $region_row = "
			<tr>
				<td style='padding:12px 16px;border-bottom:1px solid #e2e8f0;color:#64748b;font-weight:600;width:140px;'>Khu vực</td>
				<td style='padding:12px 16px;border-bottom:1px solid #e2e8f0;color:#1e293b;'>" . esc_html($region) . "</td>
			</tr>";
        }

        $message_row = '';
        if (!empty($message)) {
            $message_row = "
			<tr>
				<td style='padding:12px 16px;border-bottom:1px solid #e2e8f0;color:#64748b;font-weight:600;width:140px;'>Nội dung cần tư vấn</td>
				<td style='padding:12px 16px;border-bottom:1px solid #e2e8f0;color:#1e293b;'>" . nl2br(esc_html($message)) . "</td>
			</tr>";
        }

        return "
		<div style='max-width:600px;margin:0 auto;font-family:Arial,Helvetica,sans-serif;'>
			<div style='background:linear-gradient(135deg,#1e78c2,#15578e);padding:32px 24px;border-radius:12px 12px 0 0;text-align:center;'>
				<h1 style='color:#fff;margin:0 0 8px;font-size:22px;'>🚀 Đăng ký đại lý mới</h1>
				<p style='color:#bfdbfe;margin:0;font-size:14px;'>Từ trang Cơ hội hợp tác - {$site_name}</p>
			</div>
			<div style='background:#fff;padding:24px;border:1px solid #e2e8f0;border-top:none;'>
				<table style='width:100%;border-collapse:collapse;font-size:14px;'>
					<tr>
						<td style='padding:12px 16px;border-bottom:1px solid #e2e8f0;color:#64748b;font-weight:600;width:140px;'>Họ và tên</td>
						<td style='padding:12px 16px;border-bottom:1px solid #e2e8f0;color:#1e293b;font-weight:700;'>" . esc_html($fullname) . "</td>
					</tr>
					<tr>
						<td style='padding:12px 16px;border-bottom:1px solid #e2e8f0;color:#64748b;font-weight:600;width:140px;'>Số điện thoại</td>
						<td style='padding:12px 16px;border-bottom:1px solid #e2e8f0;color:#1e293b;'>
							<a href='tel:" . esc_attr($phone) . "' style='color:#1e78c2;text-decoration:none;font-weight:700;'>" . esc_html($phone) . "</a>
						</td>
					</tr>
					{$region_row}
					{$message_row}
					<tr>
						<td style='padding:12px 16px;color:#64748b;font-weight:600;width:140px;'>Thời gian</td>
						<td style='padding:12px 16px;color:#1e293b;'>{$date}</td>
					</tr>
				</table>
			</div>
			<div style='background:#f8fafc;padding:16px 24px;border:1px solid #e2e8f0;border-top:none;border-radius:0 0 12px 12px;text-align:center;'>
				<p style='color:#94a3b8;font-size:12px;margin:0;'>Email tự động từ hệ thống {$site_name}</p>
			</div>
		</div>";
    }
}
