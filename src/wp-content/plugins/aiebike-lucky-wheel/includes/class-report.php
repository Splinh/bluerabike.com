<?php

/**
 * Report Handler - Extended from WooCommerce Lucky Wheel
 * 
 * Hiển thị báo cáo với cột "Số khung" và xuất Excel
 */

if (!defined('ABSPATH')) {
    exit;
}

class AIEbike_LW_Report
{
    /**
     * Constructor
     */
    public function __construct()
    {
        // Hook to save frame number when Lucky Wheel saves email
        add_action('woo_lucky_wheel_get_email', array($this, 'save_frame_number_to_email'), 10, 5);

        // Handle export
        add_action('admin_init', array($this, 'export_report'));
    }

    /**
     * Save frame number to wlwl_email post meta
     */
    public function save_frame_number_to_email($email, $name, $mobile, $wheel_label, $result_notification)
    {
        // Get frame number from session or transient
        $frame_number = '';

        // Try to get from transient (set by frontend validation)
        $transient_key = 'aiebike_frame_' . sanitize_key($mobile);
        $frame_number = get_transient($transient_key);

        if (!$frame_number) {
            // Try to get from our own table
            global $wpdb;
            $table = $wpdb->prefix . 'aiebike_frame_numbers';

            // Find by phone or email
            $result = $wpdb->get_row($wpdb->prepare(
                "SELECT frame_number FROM $table 
                 WHERE (customer_phone = %s OR customer_email = %s) 
                 ORDER BY used_date DESC LIMIT 1",
                $mobile,
                $email
            ));

            if ($result) {
                $frame_number = $result->frame_number;
            }
        }

        if ($frame_number) {
            // Find the wlwl_email post by email
            $args = array(
                'post_type' => 'wlwl_email',
                'title' => $email,
                'posts_per_page' => 1,
                'post_status' => 'publish',
                'orderby' => 'date',
                'order' => 'DESC'
            );

            $posts = get_posts($args);

            if (!empty($posts)) {
                $email_id = $posts[0]->ID;
                update_post_meta($email_id, 'wlwl_email_frame_number', sanitize_text_field($frame_number));
            }

            // Delete transient
            delete_transient($transient_key);
        }
    }

    /**
     * Render report page
     */
    public function render_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Get statistics
        $stats = $this->get_statistics();

        // Get emails with pagination
        $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $per_page = 50;
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

        $emails = $this->get_emails($paged, $per_page, $search);
        $total_emails = $this->get_total_emails($search);
        $total_pages = ceil($total_emails / $per_page);

?>
        <div class="wrap">
            <h1><?php _e('Báo Cáo Vòng Quay May Mắn', 'aiebike-lucky-wheel'); ?></h1>

            <!-- Statistics -->
            <div class="aiebike-report-stats" style="display: flex; gap: 20px; margin: 20px 0;">
                <div class="stat-box" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); flex: 1; text-align: center;">
                    <div style="font-size: 32px; font-weight: bold; color: #2271b1;"><?php echo esc_html($stats['total_spins']); ?></div>
                    <div style="color: #666;"><?php _e('Tổng lượt quay', 'aiebike-lucky-wheel'); ?></div>
                </div>
                <div class="stat-box" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); flex: 1; text-align: center;">
                    <div style="font-size: 32px; font-weight: bold; color: #00a32a;"><?php echo esc_html($stats['total_emails']); ?></div>
                    <div style="color: #666;"><?php _e('Email đăng ký', 'aiebike-lucky-wheel'); ?></div>
                </div>
                <div class="stat-box" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); flex: 1; text-align: center;">
                    <div style="font-size: 32px; font-weight: bold; color: #dba617;"><?php echo esc_html($stats['coupons_given']); ?></div>
                    <div style="color: #666;"><?php _e('Mã giảm giá đã phát', 'aiebike-lucky-wheel'); ?></div>
                </div>
            </div>

            <!-- Export Form -->
            <form method="post" style="margin: 20px 0; display: flex; gap: 10px; align-items: center; background: #fff; padding: 15px; border-radius: 8px;">
                <label for="export_start"><?php _e('Từ:', 'aiebike-lucky-wheel'); ?></label>
                <input type="date" name="export_start" id="export_start">

                <label for="export_end"><?php _e('Đến:', 'aiebike-lucky-wheel'); ?></label>
                <input type="date" name="export_end" id="export_end">

                <?php wp_nonce_field('aiebike_export_report', 'aiebike_export_nonce'); ?>

                <button type="submit" name="aiebike_export_excel" class="button button-primary">
                    📥 <?php _e('Xuất Excel', 'aiebike-lucky-wheel'); ?>
                </button>
            </form>

            <!-- Search Form -->
            <form method="get" style="margin: 20px 0;">
                <input type="hidden" name="page" value="aiebike-lucky-wheel">
                <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="<?php esc_attr_e('Tìm theo email, SĐT, số khung...', 'aiebike-lucky-wheel'); ?>" style="width: 300px;">
                <button type="submit" class="button"><?php _e('Tìm kiếm', 'aiebike-lucky-wheel'); ?></button>
                <?php if ($search): ?>
                    <a href="<?php echo admin_url('admin.php?page=aiebike-lucky-wheel'); ?>" class="button"><?php _e('Xóa bộ lọc', 'aiebike-lucky-wheel'); ?></a>
                <?php endif; ?>
            </form>

            <!-- Data Table -->
            <table class="wp-list-table widefat fixed striped" style="margin-top: 20px;">
                <thead>
                    <tr>
                        <th style="width: 50px;"><?php _e('#', 'aiebike-lucky-wheel'); ?></th>
                        <th><?php _e('Số Khung/Động Cơ', 'aiebike-lucky-wheel'); ?></th>
                        <th><?php _e('Email', 'aiebike-lucky-wheel'); ?></th>
                        <th><?php _e('Tên', 'aiebike-lucky-wheel'); ?></th>
                        <th><?php _e('SĐT', 'aiebike-lucky-wheel'); ?></th>
                        <th><?php _e('Giải thưởng', 'aiebike-lucky-wheel'); ?></th>
                        <th><?php _e('Mã giảm giá', 'aiebike-lucky-wheel'); ?></th>
                        <th><?php _e('Ngày quay', 'aiebike-lucky-wheel'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($emails)): ?>
                        <?php $i = ($paged - 1) * $per_page + 1; ?>
                        <?php foreach ($emails as $email): ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td>
                                    <?php if ($email->frame_number): ?>
                                        <strong style="color: #2271b1;"><?php echo esc_html($email->frame_number); ?></strong>
                                    <?php else: ?>
                                        <span style="color: #999;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html($email->email); ?></td>
                                <td><?php echo esc_html($email->name); ?></td>
                                <td><?php echo esc_html($email->mobile); ?></td>
                                <td>
                                    <?php if ($email->labels): ?>
                                        <span style="background: #ff6b00; color: #fff; padding: 3px 8px; border-radius: 3px; font-size: 12px;">
                                            <?php echo esc_html($email->labels); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><code><?php echo esc_html($email->coupons); ?></code></td>
                                <td><?php echo date_i18n('d/m/Y H:i', strtotime($email->date)); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8"><?php _e('Chưa có dữ liệu.', 'aiebike-lucky-wheel'); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="tablenav bottom">
                    <div class="tablenav-pages">
                        <span class="displaying-num"><?php printf(__('%d mục', 'aiebike-lucky-wheel'), $total_emails); ?></span>
                        <?php
                        $pagination_args = array(
                            'base' => add_query_arg('paged', '%#%'),
                            'format' => '',
                            'prev_text' => '&laquo;',
                            'next_text' => '&raquo;',
                            'total' => $total_pages,
                            'current' => $paged
                        );
                        echo paginate_links($pagination_args);
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
<?php
    }

    /**
     * Get statistics
     */
    private function get_statistics()
    {
        $total_spins = 0;
        $coupons_given = 0;

        $args = array(
            'post_type' => 'wlwl_email',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        );

        $query = new WP_Query($args);
        $total_emails = $query->post_count;

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $id = get_the_ID();

                $spin_times = get_post_meta($id, 'wlwl_spin_times', true);
                if ($spin_times && isset($spin_times['spin_num'])) {
                    $total_spins += intval($spin_times['spin_num']);
                }

                $coupons = get_post_meta($id, 'wlwl_email_coupons', true);
                if (is_array($coupons)) {
                    $coupons_given += count($coupons);
                }
            }
            wp_reset_postdata();
        }

        return array(
            'total_spins' => $total_spins,
            'total_emails' => $total_emails,
            'coupons_given' => $coupons_given,
        );
    }

    /**
     * Get emails with pagination
     */
    private function get_emails($paged = 1, $per_page = 50, $search = '')
    {
        global $wpdb;

        $args = array(
            'post_type' => 'wlwl_email',
            'posts_per_page' => $per_page,
            'paged' => $paged,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
        );

        if ($search) {
            $args['meta_query'] = array(
                'relation' => 'OR',
                array(
                    'key' => 'wlwl_email_mobile',
                    'value' => $search,
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'wlwl_email_frame_number',
                    'value' => $search,
                    'compare' => 'LIKE'
                ),
            );

            // Also search by title (email)
            $args['s'] = $search;
        }

        $query = new WP_Query($args);
        $emails = array();

        // Get frame numbers table
        $frame_table = $wpdb->prefix . 'aiebike_frame_numbers';

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $id = get_the_ID();
                $email_address = get_the_title();
                $mobile = get_post_meta($id, 'wlwl_email_mobile', true);

                $coupons = get_post_meta($id, 'wlwl_email_coupons', true);
                $labels = get_post_meta($id, 'wlwl_email_labels', true);

                // Try to get frame number from post meta first
                $frame_number = get_post_meta($id, 'wlwl_email_frame_number', true);

                // If empty, try to get from aiebike_frame_numbers table
                if (empty($frame_number) && !empty($mobile)) {
                    // Normalize phone for flexible matching
                    $phone_digits = preg_replace('/[^0-9]/', '', $mobile);
                    $phone_last_9 = substr($phone_digits, -9); // Last 9 digits

                    // Also check by email (remove @aiebike.store part if exists)
                    $email_phone = preg_replace('/@.*/', '', $email_address);
                    $email_phone_digits = preg_replace('/[^0-9]/', '', $email_phone);

                    // Try multiple matching strategies
                    $frame_result = $wpdb->get_row($wpdb->prepare(
                        "SELECT frame_number FROM $frame_table 
                         WHERE customer_email = %s 
                            OR customer_phone = %s 
                            OR customer_phone LIKE %s
                            OR customer_email LIKE %s
                         ORDER BY used_date DESC LIMIT 1",
                        $email_address,
                        $mobile,
                        '%' . $phone_last_9,
                        $email_phone_digits . '%'
                    ));

                    if ($frame_result) {
                        $frame_number = $frame_result->frame_number;
                        // Save to post meta for future use
                        update_post_meta($id, 'wlwl_email_frame_number', $frame_number);
                    }
                }

                $emails[] = (object) array(
                    'id' => $id,
                    'email' => $email_address,
                    'name' => get_the_content(),
                    'mobile' => $mobile,
                    'frame_number' => $frame_number,
                    'coupons' => is_array($coupons) ? implode(', ', $coupons) : '',
                    'labels' => is_array($labels) ? implode(', ', array_map('html_entity_decode', $labels)) : '',
                    'date' => get_the_date('Y-m-d H:i:s'),
                );
            }
            wp_reset_postdata();
        }

        return $emails;
    }

    /**
     * Get total emails count
     */
    private function get_total_emails($search = '')
    {
        $args = array(
            'post_type' => 'wlwl_email',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        );

        if ($search) {
            $args['s'] = $search;
        }

        $query = new WP_Query($args);
        return $query->post_count;
    }

    /**
     * Export to Excel/CSV
     */
    public function export_report()
    {
        if (!isset($_POST['aiebike_export_excel'])) {
            return;
        }

        if (!wp_verify_nonce($_POST['aiebike_export_nonce'], 'aiebike_export_report')) {
            wp_die(__('Lỗi bảo mật', 'aiebike-lucky-wheel'));
        }

        if (!current_user_can('manage_options')) {
            return;
        }

        $start = isset($_POST['export_start']) ? sanitize_text_field($_POST['export_start']) : '';
        $end = isset($_POST['export_end']) ? sanitize_text_field($_POST['export_end']) : '';

        // Build date query
        $date_query = array();
        if ($start) {
            $date_query['after'] = $start;
            $date_query['inclusive'] = true;
        }
        if ($end) {
            $date_query['before'] = $end;
            $date_query['inclusive'] = true;
        }

        $args = array(
            'post_type' => 'wlwl_email',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
        );

        if (!empty($date_query)) {
            $args['date_query'] = array($date_query);
        }

        $query = new WP_Query($args);

        // Prepare data
        $data = array();
        $header = array(
            'STT',
            'Số Khung/Động Cơ',
            'Email',
            'Tên Khách',
            'SĐT',
            'Giải Thưởng',
            'Mã Giảm Giá',
            'Ngày Quay'
        );

        if ($query->have_posts()) {
            $i = 1;
            while ($query->have_posts()) {
                $query->the_post();
                $id = get_the_ID();

                $coupons = get_post_meta($id, 'wlwl_email_coupons', true);
                $labels = get_post_meta($id, 'wlwl_email_labels', true);

                $data[] = array(
                    $i++,
                    get_post_meta($id, 'wlwl_email_frame_number', true) ?: '',
                    get_the_title(),
                    strip_tags(get_the_content()),
                    get_post_meta($id, 'wlwl_email_mobile', true) ?: '',
                    is_array($labels) ? implode(', ', array_map('html_entity_decode', $labels)) : '',
                    is_array($coupons) ? implode(', ', $coupons) : '',
                    get_the_date('d/m/Y H:i')
                );
            }
            wp_reset_postdata();
        }

        // Generate filename
        $filename = 'lucky_wheel_report_';
        if ($start && $end) {
            $filename .= $start . '_to_' . $end;
        } else {
            $filename .= date('Y-m-d_H-i-s');
        }
        $filename .= '.csv';

        // Output CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // UTF-8 BOM for Excel
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Header row
        fputcsv($output, $header);

        // Data rows
        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }
}

// Initialize
new AIEbike_LW_Report();
