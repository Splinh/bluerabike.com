<?php

/**
 * Frame Number Validation Handler
 * 
 * Xử lý xác thực số khung/động cơ xe
 */

if (!defined('ABSPATH')) {
    exit;
}

class AIEbike_LW_Frame_Validation
{

    /**
     * Table name
     */
    private $table_name;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'aiebike_frame_numbers';

        // Register AJAX handlers
        add_action('wp_ajax_aiebike_check_frame_number', array($this, 'check_frame_number'));
        add_action('wp_ajax_nopriv_aiebike_check_frame_number', array($this, 'check_frame_number'));

        add_action('wp_ajax_aiebike_save_frame_number', array($this, 'save_frame_number'));
        add_action('wp_ajax_nopriv_aiebike_save_frame_number', array($this, 'save_frame_number'));
    }

    /**
     * AJAX: Check frame number
     */
    public function check_frame_number()
    {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'aiebike_lucky_wheel_nonce')) {
            wp_send_json_error(array('message' => __('Lỗi bảo mật, vui lòng tải lại trang.', 'aiebike-lucky-wheel')));
        }

        global $wpdb;

        $frame_number = isset($_POST['frame_number']) ? sanitize_text_field(trim($_POST['frame_number'])) : '';
        $mobile = isset($_POST['mobile']) ? sanitize_text_field(trim($_POST['mobile'])) : '';

        if (empty($frame_number)) {
            wp_send_json_error(array('message' => __('Vui lòng nhập số khung hoặc số động cơ xe.', 'aiebike-lucky-wheel')));
        }

        // Normalize frame number
        $frame_number = strtoupper(preg_replace('/\s+/', '', $frame_number));

        // Check minimum length
        if (strlen($frame_number) < 5) {
            wp_send_json_error(array('message' => __('Số khung/động cơ không hợp lệ (tối thiểu 5 ký tự).', 'aiebike-lucky-wheel')));
        }

        // Check if already used
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE frame_number = %s",
            $frame_number
        ));

        if ($existing) {
            $date_used = date_i18n('d/m/Y', strtotime($existing->used_date));
            wp_send_json_error(array(
                'message' => sprintf(
                    __('Mã số khung/động cơ này đã được sử dụng để quay thưởng vào ngày %s. Mỗi xe chỉ được quay 1 lần.', 'aiebike-lucky-wheel'),
                    $date_used
                ),
                'already_used' => true
            ));
        }

        // Save frame number to transient for later retrieval by report class
        // Use mobile as key to link frame_number with email when Lucky Wheel saves email
        if ($mobile) {
            $transient_key = 'aiebike_frame_' . sanitize_key($mobile);
            set_transient($transient_key, $frame_number, HOUR_IN_SECONDS);
        }

        wp_send_json_success(array('message' => 'OK', 'frame_number' => $frame_number));
    }

    /**
     * AJAX: Save frame number after successful spin
     */
    public function save_frame_number()
    {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'aiebike_lucky_wheel_nonce')) {
            wp_send_json_error(array('message' => __('Lỗi bảo mật.', 'aiebike-lucky-wheel')));
        }

        global $wpdb;

        $frame_number = isset($_POST['frame_number']) ? strtoupper(preg_replace('/\s+/', '', sanitize_text_field($_POST['frame_number']))) : '';
        $customer_name = isset($_POST['customer_name']) ? sanitize_text_field($_POST['customer_name']) : '';
        $customer_phone = isset($_POST['customer_phone']) ? sanitize_text_field($_POST['customer_phone']) : '';
        $customer_email = isset($_POST['customer_email']) ? sanitize_email($_POST['customer_email']) : '';
        $prize_won = isset($_POST['prize_won']) ? sanitize_text_field($_POST['prize_won']) : '';

        if (empty($frame_number)) {
            wp_send_json_error(array('message' => __('Thiếu số khung.', 'aiebike-lucky-wheel')));
        }

        // Insert into database
        $result = $wpdb->insert(
            $this->table_name,
            array(
                'frame_number' => $frame_number,
                'customer_name' => $customer_name,
                'customer_phone' => $customer_phone,
                'customer_email' => $customer_email,
                'prize_won' => $prize_won,
                'used_date' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s')
        );

        if ($result) {
            wp_send_json_success(array('message' => __('Đã lưu thành công.', 'aiebike-lucky-wheel')));
        } else {
            wp_send_json_error(array('message' => __('Không thể lưu dữ liệu.', 'aiebike-lucky-wheel')));
        }
    }

    /**
     * Get all frame numbers
     */
    public function get_all($limit = 100)
    {
        global $wpdb;
        return $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM {$this->table_name} ORDER BY used_date DESC LIMIT %d", $limit)
        );
    }

    /**
     * Delete frame number
     */
    public function delete($id)
    {
        global $wpdb;
        return $wpdb->delete($this->table_name, array('id' => $id), array('%d'));
    }

    /**
     * Search frame numbers
     */
    public function search($query)
    {
        global $wpdb;
        $like = '%' . $wpdb->esc_like($query) . '%';
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_name} 
             WHERE frame_number LIKE %s 
                OR customer_name LIKE %s 
                OR customer_phone LIKE %s 
             ORDER BY used_date DESC LIMIT 50",
            $like,
            $like,
            $like
        ));
    }
}

// Initialize
new AIEbike_LW_Frame_Validation();
