<?php

/**
 * Admin Page Handler
 * 
 * Trang quản trị trong WP Admin
 */

if (!defined('ABSPATH')) {
    exit;
}

class AIEbike_LW_Admin
{

    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'handle_actions'));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu()
    {
        // Main menu - uses Report page
        add_menu_page(
            __('Vòng Quay May Mắn', 'aiebike-lucky-wheel'),
            __('Vòng Quay', 'aiebike-lucky-wheel'),
            'manage_options',
            'aiebike-lucky-wheel',
            array($this, 'render_report_page'),
            'dashicons-tickets-alt',
            56
        );

        // Submenu - Report (same as main)
        add_submenu_page(
            'aiebike-lucky-wheel',
            __('Báo cáo', 'aiebike-lucky-wheel'),
            __('Báo cáo', 'aiebike-lucky-wheel'),
            'manage_options',
            'aiebike-lucky-wheel',
            array($this, 'render_report_page')
        );

        // Submenu - Settings
        add_submenu_page(
            'aiebike-lucky-wheel',
            __('Cài đặt', 'aiebike-lucky-wheel'),
            __('Cài đặt', 'aiebike-lucky-wheel'),
            'manage_options',
            'aiebike-lw-settings',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Render report page (uses class-report.php)
     */
    public function render_report_page()
    {
        $report = new AIEbike_LW_Report();
        $report->render_page();
    }

    /**
     * Handle admin actions
     */
    public function handle_actions()
    {
        if (!isset($_GET['page']) || strpos($_GET['page'], 'aiebike') === false) {
            return;
        }

        // Delete frame number
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
            if (!wp_verify_nonce($_GET['_wpnonce'], 'delete_frame_' . $_GET['id'])) {
                wp_die(__('Lỗi bảo mật', 'aiebike-lucky-wheel'));
            }

            global $wpdb;
            $wpdb->delete($wpdb->prefix . 'aiebike_frame_numbers', array('id' => intval($_GET['id'])));

            wp_redirect(admin_url('admin.php?page=aiebike-frame-numbers&deleted=1'));
            exit;
        }
    }

    /**
     * Render main page
     */
    public function render_main_page()
    {
?>
        <div class="wrap">
            <h1><?php _e('AIEbike Vòng Quay May Mắn', 'aiebike-lucky-wheel'); ?></h1>

            <div class="card" style="max-width: 800px; padding: 20px;">
                <h2><?php _e('Hướng dẫn sử dụng', 'aiebike-lucky-wheel'); ?></h2>

                <h3><?php _e('1. Shortcode trang Vòng Quay', 'aiebike-lucky-wheel'); ?></h3>
                <p><?php _e('Sử dụng shortcode sau để hiển thị trang vòng quay hoàn chỉnh:', 'aiebike-lucky-wheel'); ?></p>
                <code style="display: block; padding: 10px; background: #f0f0f0; margin: 10px 0;">[aiebike_lucky_wheel_page]</code>

                <h3><?php _e('2. Shortcode Danh sách trúng thưởng', 'aiebike-lucky-wheel'); ?></h3>
                <p><?php _e('Hiển thị danh sách người đã quay trúng thưởng:', 'aiebike-lucky-wheel'); ?></p>
                <code style="display: block; padding: 10px; background: #f0f0f0; margin: 10px 0;">[aiebike_winners_list limit="50"]</code>

                <h3><?php _e('3. Tính năng xác thực số khung', 'aiebike-lucky-wheel'); ?></h3>
                <ul>
                    <li><?php _e('Mỗi số khung/động cơ chỉ được quay 1 lần', 'aiebike-lucky-wheel'); ?></li>
                    <li><?php _e('Hệ thống tự động kiểm tra trước khi cho quay', 'aiebike-lucky-wheel'); ?></li>
                    <li><?php _e('Xem danh sách tại menu "Số khung đã quay"', 'aiebike-lucky-wheel'); ?></li>
                </ul>

                <h3><?php _e('4. Yêu cầu', 'aiebike-lucky-wheel'); ?></h3>
                <ul>
                    <li><?php _e('Plugin WooCommerce Lucky Wheel phải được cài đặt', 'aiebike-lucky-wheel'); ?></li>
                    <li><?php _e('Cấu hình Lucky Wheel trong WooCommerce → Lucky Wheel', 'aiebike-lucky-wheel'); ?></li>
                </ul>
            </div>

            <div class="card" style="max-width: 800px; padding: 20px; margin-top: 20px;">
                <h2><?php _e('Thống kê nhanh', 'aiebike-lucky-wheel'); ?></h2>
                <?php
                global $wpdb;
                $table = $wpdb->prefix . 'aiebike_frame_numbers';
                $total = $wpdb->get_var("SELECT COUNT(*) FROM $table");
                $today = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $table WHERE DATE(used_date) = %s",
                    current_time('Y-m-d')
                ));
                ?>
                <table class="widefat" style="max-width: 400px;">
                    <tr>
                        <td><strong><?php _e('Tổng số lượt quay', 'aiebike-lucky-wheel'); ?></strong></td>
                        <td><?php echo esc_html($total); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Lượt quay hôm nay', 'aiebike-lucky-wheel'); ?></strong></td>
                        <td><?php echo esc_html($today); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    <?php
    }

    /**
     * Render frame numbers page
     */
    public function render_frame_numbers_page()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'aiebike_frame_numbers';

        // Search
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

        if ($search) {
            $like = '%' . $wpdb->esc_like($search) . '%';
            $results = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table WHERE frame_number LIKE %s OR customer_name LIKE %s OR customer_phone LIKE %s ORDER BY used_date DESC LIMIT 100",
                $like,
                $like,
                $like
            ));
        } else {
            $results = $wpdb->get_results("SELECT * FROM $table ORDER BY used_date DESC LIMIT 100");
        }
    ?>
        <div class="wrap">
            <h1><?php _e('Danh sách số khung/động cơ đã quay', 'aiebike-lucky-wheel'); ?></h1>

            <?php if (isset($_GET['deleted'])): ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php _e('Đã xóa thành công!', 'aiebike-lucky-wheel'); ?></p>
                </div>
            <?php endif; ?>

            <form method="get" style="margin-bottom: 20px;">
                <input type="hidden" name="page" value="aiebike-frame-numbers">
                <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="<?php esc_attr_e('Tìm kiếm...', 'aiebike-lucky-wheel'); ?>">
                <button type="submit" class="button"><?php _e('Tìm kiếm', 'aiebike-lucky-wheel'); ?></button>
                <?php if ($search): ?>
                    <a href="<?php echo admin_url('admin.php?page=aiebike-frame-numbers'); ?>" class="button"><?php _e('Xóa bộ lọc', 'aiebike-lucky-wheel'); ?></a>
                <?php endif; ?>
            </form>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th><?php _e('Số khung/Động cơ', 'aiebike-lucky-wheel'); ?></th>
                        <th><?php _e('Tên khách', 'aiebike-lucky-wheel'); ?></th>
                        <th><?php _e('SĐT', 'aiebike-lucky-wheel'); ?></th>
                        <th><?php _e('Email', 'aiebike-lucky-wheel'); ?></th>
                        <th><?php _e('Giải thưởng', 'aiebike-lucky-wheel'); ?></th>
                        <th><?php _e('Ngày quay', 'aiebike-lucky-wheel'); ?></th>
                        <th style="width: 80px;"><?php _e('Thao tác', 'aiebike-lucky-wheel'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($results): ?>
                        <?php foreach ($results as $row): ?>
                            <tr>
                                <td><?php echo esc_html($row->id); ?></td>
                                <td><strong><?php echo esc_html($row->frame_number); ?></strong></td>
                                <td><?php echo esc_html($row->customer_name); ?></td>
                                <td><?php echo esc_html($row->customer_phone); ?></td>
                                <td><?php echo esc_html($row->customer_email); ?></td>
                                <td><span style="background: #ff6b00; color: #fff; padding: 3px 8px; border-radius: 3px; font-size: 12px;"><?php echo esc_html($row->prize_won); ?></span></td>
                                <td><?php echo date_i18n('d/m/Y H:i', strtotime($row->used_date)); ?></td>
                                <td>
                                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=aiebike-frame-numbers&action=delete&id=' . $row->id), 'delete_frame_' . $row->id); ?>"
                                        class="button button-small"
                                        onclick="return confirm('<?php esc_attr_e('Bạn có chắc muốn xóa?', 'aiebike-lucky-wheel'); ?>');">
                                        <?php _e('Xóa', 'aiebike-lucky-wheel'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8"><?php _e('Chưa có dữ liệu.', 'aiebike-lucky-wheel'); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <p class="description" style="margin-top: 15px;">
                <?php printf(__('Hiển thị tối đa 100 kết quả gần nhất. Tổng cộng: %d bản ghi.', 'aiebike-lucky-wheel'), count($results)); ?>
            </p>
        </div>
    <?php
    }

    /**
     * Render settings page
     */
    public function render_settings_page()
    {
        // Save settings
        if (isset($_POST['aiebike_lw_save_settings']) && wp_verify_nonce($_POST['_wpnonce'], 'aiebike_lw_settings')) {
            update_option('aiebike_lw_enable_frame_validation', isset($_POST['enable_frame_validation']) ? 'yes' : 'no');
            update_option('aiebike_lw_frame_field_label', sanitize_text_field($_POST['frame_field_label']));
            update_option('aiebike_lw_auto_email_domain', sanitize_text_field($_POST['auto_email_domain']));
            update_option('aiebike_lw_phone_mask_length', intval($_POST['phone_mask_length']));

            echo '<div class="notice notice-success"><p>' . __('Đã lưu cài đặt!', 'aiebike-lucky-wheel') . '</p></div>';
        }

        // Get settings
        $enable_frame = get_option('aiebike_lw_enable_frame_validation', 'yes');
        $frame_label = get_option('aiebike_lw_frame_field_label', 'Số khung hoặc số động cơ xe');
        $email_domain = get_option('aiebike_lw_auto_email_domain', 'aiebike.store');
        $phone_mask = get_option('aiebike_lw_phone_mask_length', 5);
    ?>
        <div class="wrap">
            <h1><?php _e('Cài đặt Vòng Quay', 'aiebike-lucky-wheel'); ?></h1>

            <form method="post">
                <?php wp_nonce_field('aiebike_lw_settings'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Bật xác thực số khung', 'aiebike-lucky-wheel'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="enable_frame_validation" value="1" <?php checked($enable_frame, 'yes'); ?>>
                                <?php _e('Yêu cầu nhập số khung/động cơ trước khi quay', 'aiebike-lucky-wheel'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Nhãn field số khung', 'aiebike-lucky-wheel'); ?></th>
                        <td>
                            <input type="text" name="frame_field_label" value="<?php echo esc_attr($frame_label); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Domain email tự động', 'aiebike-lucky-wheel'); ?></th>
                        <td>
                            <input type="text" name="auto_email_domain" value="<?php echo esc_attr($email_domain); ?>" class="regular-text">
                            <p class="description"><?php _e('Khi khách không có email, hệ thống sẽ tạo: [SĐT]@[domain]', 'aiebike-lucky-wheel'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Số ký tự ẩn SĐT', 'aiebike-lucky-wheel'); ?></th>
                        <td>
                            <input type="number" name="phone_mask_length" value="<?php echo esc_attr($phone_mask); ?>" min="3" max="7" style="width: 80px;">
                            <p class="description"><?php _e('VD: 5 → 0962*****94', 'aiebike-lucky-wheel'); ?></p>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="submit" name="aiebike_lw_save_settings" class="button button-primary"><?php _e('Lưu cài đặt', 'aiebike-lucky-wheel'); ?></button>
                </p>
            </form>
        </div>
<?php
    }
}

// Initialize
new AIEbike_LW_Admin();
