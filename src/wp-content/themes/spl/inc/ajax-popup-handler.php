<?php

/**
 * AJAX Handler for Promotional Popup Form
 *
 * @author Gaudev
 */

\defined('ABSPATH') || die;

// AJAX handler for logged-in users
add_action('wp_ajax_submit_promo_form', 'handle_promo_popup_form_submission');

// AJAX handler for non-logged-in users
add_action('wp_ajax_nopriv_submit_promo_form', 'handle_promo_popup_form_submission');

function handle_promo_popup_form_submission()
{
    // Verify nonce for security (optional but recommended)
    // if (!isset($_POST[' nonce']) || !wp_verify_nonce($_POST['nonce'], 'promo_form_nonce')) {
    // 	wp_send_json_error(['message' => __('Security check failed', TEXT_DOMAIN)]);
    // }

    // Get form data
    $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
    $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';

    // Validate data
    if (empty($name) || empty($phone)) {
        wp_send_json_error([
            'message' => __('Vui lòng điền đầy đủ thông tin!', TEXT_DOMAIN)
        ]);
    }

    // Validate phone number
    if (!preg_match('/^[0-9]{10,11}$/', $phone)) {
        wp_send_json_error([
            'message' => __('Số điện thoại không hợp lệ!', TEXT_DOMAIN)
        ]);
    }

    // Here you can:
    // 1. Save to database
    // 2. Send email notification
    // 3. Integrate with CRM
    // 4. etc.

    // Example: Send email to admin
    // $admin_email = get_option('admin_email');
    $admin_email = 'dailyxedien.com.vn@gmail.com';
    $subject = __('Đăng ký tư vấn mở đại lý/cửa hàng ủy quyền', TEXT_DOMAIN);
    $message = sprintf(
        __("Thông tin đăng ký:\n\nHọ tên: %s\nSố điện thoại: %s\n\nThời gian: %s", TEXT_DOMAIN),
        $name,
        $phone,
        current_time('mysql')
    );

    // Send email
    $email_sent = wp_mail($admin_email, $subject, $message);

    // Optional: Save to custom table or post meta
    // Example: Save as a custom post type
    $post_data = [
        'post_title'   => $name,
        'post_content' => $phone,
        'post_status'  => 'publish',
        'post_type'    => 'promo_registration', // You'd need to register this CPT
        'meta_input'   => [
            'customer_name'  => $name,
            'customer_phone' => $phone,
            'submitted_at'   => current_time('mysql'),
        ]
    ];

    // Uncomment if you have 'promo_registration' custom post type registered
    // $post_id = wp_insert_post($post_data);

    // Return success response
    wp_send_json_success([
        'message' => __('Cảm ơn bạn đã đăng ký! Chúng tôi sẽ liên hệ sớm.', TEXT_DOMAIN),
        'name'    => $name,
        'phone'   => $phone,
    ]);
}
