<?php
/**
 * Plugin Name: AiEbike Popup Form
 * Description: Popup form đăng ký đại lý với shortcode, ACF fields, tự động hiện theo trang & thời gian.
 * Version: 1.0.0
 * Author: SPL DevVN
 */

defined('ABSPATH') || exit;

define('AEPF_VERSION', '1.0.0');
define('AEPF_PATH', plugin_dir_path(__FILE__));
define('AEPF_URL', plugin_dir_url(__FILE__));

// ─── ACF Field Group (Nội dung + Cài đặt, tất cả trên 1 trang) ───────────────
add_action('acf/init', 'aepf_register_acf_fields');
function aepf_register_acf_fields() {
    if (!function_exists('acf_add_local_field_group')) return;

    // Lấy danh sách CF7 để build choices
    $cf7_choices = [0 => '— Dùng form built-in của plugin —'];
    if (class_exists('WPCF7_ContactForm')) {
        foreach (WPCF7_ContactForm::find(['posts_per_page' => 50]) as $f) {
            $cf7_choices[$f->id()] = $f->title();
        }
    }

    acf_add_local_field_group([
        'key'    => 'group_aepf_popup',
        'title'  => 'Popup Form — Toàn Bộ Cài Đặt',
        'fields' => [

            // ── SECTION: Shortcode hướng dẫn ──────────────────────────────
            [
                'key'     => 'field_aepf_sc_info',
                'label'   => '📌 Shortcode & Cách dùng',
                'name'    => '',
                'type'    => 'message',
                'message' => '<div style="background:#f0f6fc;border-left:4px solid #2271b1;padding:12px 16px;border-radius:4px;line-height:1.8">
                    <strong>Nút mở popup (gắn vào trang/widget/menu):</strong><br>
                    <code style="background:#fff;padding:2px 8px;border-radius:3px;font-size:14px">[aepf_button]</code>
                    &nbsp;hoặc tuỳ chỉnh label:
                    <code style="background:#fff;padding:2px 8px;border-radius:3px;font-size:14px">[aepf_button label="Liên Hệ Ngay" class="btn-custom"]</code><br><br>
                    <strong>Nhúng form trực tiếp vào trang (không cần popup):</strong><br>
                    <code style="background:#fff;padding:2px 8px;border-radius:3px;font-size:14px">[aepf_form]</code><br><br>
                    <strong>Mở popup bằng link/nút HTML:</strong><br>
                    <code style="background:#fff;padding:2px 8px;border-radius:3px;font-size:14px">&lt;a href="#" onclick="aepfOpenPopup();return false;"&gt;Đăng ký&lt;/a&gt;</code>
                </div>',
            ],

            // ── SECTION: Nội dung hiển thị ────────────────────────────────
            [
                'key'     => 'field_aepf_sec_content',
                'label'   => '✏️ Nội dung hiển thị trong Popup',
                'name'    => '',
                'type'    => 'message',
                'message' => '<p style="color:#666;margin:0">Chỉnh văn bản xuất hiện bên <strong>trái</strong> popup và các nhãn nút bên <strong>phải</strong>.</p>',
            ],
            [
                'key'           => 'field_aepf_heading',
                'label'         => 'Tiêu đề chính (cột trái)',
                'name'          => 'aepf_heading',
                'type'          => 'text',
                'instructions'  => 'Dòng lớn màu trắng hiện ở cột trái popup. VD: "Đừng Bỏ Lỡ Cơ Hội! Đăng Ký Ngay Hôm Nay"',
                'default_value' => 'Đừng Bỏ Lỡ Cơ Hội! Đăng Ký Ngay Hôm Nay',
            ],
            [
                'key'           => 'field_aepf_desc',
                'label'         => 'Đoạn mô tả (cột trái)',
                'name'          => 'aepf_desc',
                'type'          => 'textarea',
                'rows'          => 4,
                'instructions'  => 'Văn bản nhỏ hơn bên dưới tiêu đề. Xuống dòng = 1 đoạn mới trong popup.',
                'default_value' => "Số lượng đại lý tại mỗi khu vực có giới hạn. Đừng bỏ lỡ cơ hội hợp tác kinh doanh hấp dẫn 2026 với thương hiệu xe điện đang tăng trưởng nhanh hàng đầu Việt Nam.\n\nTrở thành đối tác của chúng tôi để cùng nhau phát triển bền vững, thành công lâu dài!",
            ],
            [
                'key'           => 'field_aepf_left_image',
                'label'         => 'Hình ảnh cột trái (popup)',
                'name'          => 'aepf_left_image',
                'type'          => 'image',
                'instructions'  => 'Upload hình để hiện ở cột trái popup thay cho nền cam + text. Khuyến nghị tỉ lệ dọc 2:3. Để trống = giữ nền cam + text mặc định.',
                'return_format' => 'array',
                'preview_size'  => 'medium',
                'allow_null'    => 1,
            ],
            [
                'key'           => 'field_aepf_btn_label',
                'label'         => 'Nhãn nút Submit (trong form)',
                'name'          => 'aepf_btn_label',
                'type'          => 'text',
                'instructions'  => 'Text trên nút gửi form bên phải popup. Mặc định: "GỬI"',
                'default_value' => 'GỬI',
            ],
            [
                'key'           => 'field_aepf_trigger_label',
                'label'         => 'Text nút mở popup [aepf_button]',
                'name'          => 'aepf_trigger_label',
                'type'          => 'text',
                'instructions'  => 'Khi dùng shortcode [aepf_button] mà không truyền label="...", plugin lấy text này. Mặc định: "Đăng Ký Ngay"',
                'default_value' => 'Đăng Ký Ngay',
            ],
            [
                'key'           => 'field_aepf_success_msg',
                'label'         => 'Thông báo sau khi gửi thành công',
                'name'          => 'aepf_success_msg',
                'type'          => 'text',
                'instructions'  => 'Hiện lên sau khi người dùng submit form thành công. Popup tự đóng sau ~3 giây.',
                'default_value' => 'Cảm ơn bạn! Chúng tôi sẽ liên hệ sớm nhất.',
            ],

            // ── SECTION: Cài đặt hiển thị ─────────────────────────────────
            [
                'key'     => 'field_aepf_sec_display',
                'label'   => '⚙️ Cài đặt hiển thị tự động',
                'name'    => '',
                'type'    => 'message',
                'message' => '<p style="color:#666;margin:0">Kiểm soát popup <strong>tự động hiện</strong> khi vào trang (không cần bấm nút). Nếu tắt, popup chỉ hiện khi người dùng bấm nút shortcode.</p>',
            ],
            [
                'key'          => 'field_aepf_auto_show',
                'label'        => 'Tự động hiện popup',
                'name'         => 'aepf_auto_show',
                'type'         => 'true_false',
                'instructions' => 'Bật để popup tự hiện sau X giây khi tải trang. Tắt nếu chỉ muốn dùng nút shortcode [aepf_button].',
                'default_value'=> 1,
                'ui'           => 1,
                'ui_on_text'   => 'Bật',
                'ui_off_text'  => 'Tắt',
            ],
            [
                'key'          => 'field_aepf_delay',
                'label'        => 'Delay — Chờ bao nhiêu giây trước khi hiện',
                'name'         => 'aepf_delay_seconds',
                'type'         => 'number',
                'instructions' => 'Tính từ khi trang tải xong. Đặt 0 = hiện ngay lập tức. Khuyến nghị: 5–10 giây.',
                'default_value'=> 5,
                'min'          => 0,
                'max'          => 120,
                'step'         => 1,
                'append'       => 'giây',
            ],
            [
                'key'          => 'field_aepf_frequency',
                'label'        => 'Hiện mấy lần — Tần suất popup',
                'name'         => 'aepf_show_frequency',
                'type'         => 'select',
                'instructions' => '"1 lần" dùng cookie lưu 1 năm. "1 lần/phiên" reset khi đóng tab. "Luôn luôn" hiện mỗi lần tải trang — dùng để test.',
                'choices'      => [
                    'once'    => '1 lần duy nhất (dùng cookie, lưu 1 năm)',
                    'session' => '1 lần mỗi phiên trình duyệt',
                    'always'  => 'Luôn luôn hiện (dùng để test)',
                ],
                'default_value'=> 'once',
                'ui'           => 1,
            ],

            // ── SECTION: Trang hiển thị ───────────────────────────────────
            [
                'key'     => 'field_aepf_sec_pages',
                'label'   => '📄 Chọn trang hiển thị popup',
                'name'    => '',
                'type'    => 'message',
                'message' => '<p style="color:#666;margin:0">Giới hạn popup chỉ tự hiện trên một số trang nhất định. <strong>Không ảnh hưởng</strong> đến shortcode [aepf_button] — nút luôn hoạt động ở mọi trang.</p>',
            ],
            [
                'key'          => 'field_aepf_show_on',
                'label'        => 'Hiện tự động trên trang nào',
                'name'         => 'aepf_show_on',
                'type'         => 'select',
                'instructions' => 'Chọn phạm vi trang popup tự động xuất hiện.',
                'choices'      => [
                    'all'      => 'Tất cả trang',
                    'specific' => 'Chỉ các trang được chọn bên dưới',
                ],
                'default_value'=> 'all',
                'ui'           => 1,
            ],
            // Checkbox: bao gồm trang chủ
            [
                'key'              => 'field_aepf_include_front',
                'label'            => 'Bao gồm Trang Chủ (Homepage)',
                'name'             => 'aepf_include_front',
                'type'             => 'true_false',
                'instructions'     => 'Bật nếu muốn popup cũng hiện ở trang chủ, kèm theo các trang cố định chọn bên dưới.',
                'default_value'    => 0,
                'ui'               => 1,
                'ui_on_text'       => 'Có',
                'ui_off_text'      => 'Không',
                'conditional_logic'=> [[['field' => 'field_aepf_show_on', 'operator' => '==', 'value' => 'specific']]],
            ],
            // Post object: chọn các trang cố định từ danh sách
            [
                'key'              => 'field_aepf_specific_pages',
                'label'            => 'Chọn trang cố định',
                'name'             => 'aepf_specific_pages',
                'type'             => 'post_object',
                'instructions'     => 'Tìm và chọn từng trang trong danh sách. Có thể chọn nhiều trang. Popup sẽ tự hiện trên các trang được chọn + trang chủ (nếu bật ở trên).',
                'post_type'        => ['page'],
                'taxonomy'         => [],
                'allow_null'       => 1,
                'multiple'         => 1,
                'return_format'    => 'id',
                'ui'               => 1,
                'conditional_logic'=> [[['field' => 'field_aepf_show_on', 'operator' => '==', 'value' => 'specific']]],
            ],


            // ── SECTION: Form nguồn ───────────────────────────────────────
            [
                'key'     => 'field_aepf_sec_form',
                'label'   => '📝 Nguồn Form (CF7 hoặc Built-in)',
                'name'    => '',
                'type'    => 'message',
                'message' => '<p style="color:#666;margin:0">Mặc định plugin dùng <strong>form built-in</strong> (5 trường: Họ tên, SĐT, Email, Khu vực, Nội dung). Nếu đã tạo sẵn CF7 form, có thể chọn thay thế.</p>',
            ],
            [
                'key'          => 'field_aepf_cf7_id',
                'label'        => 'Contact Form 7 (tuỳ chọn)',
                'name'         => 'aepf_cf7_id',
                'type'         => 'select',
                'instructions' => 'Để mặc định nếu không dùng CF7. Khi chọn CF7 form, các cài đặt "Thông báo gửi thành công" ở trên sẽ không có hiệu lực (CF7 tự xử lý).',
                'choices'      => $cf7_choices,
                'default_value'=> 0,
                'ui'           => 1,
            ],

            // ── SECTION: Thông báo email ──────────────────────────────────
            [
                'key'     => 'field_aepf_sec_email',
                'label'   => '📧 Cài đặt Email thông báo',
                'name'    => '',
                'type'    => 'message',
                'message' => '<p style="color:#666;margin:0">Mỗi khi có đăng ký mới, hệ thống gửi mail thông báo đến địa chỉ bên dưới. Để trống = dùng <strong>email quản trị WordPress</strong> mặc định.</p>',
            ],
            [
                'key'           => 'field_aepf_notify_email',
                'label'         => 'Email nhận thông báo lead',
                'name'          => 'aepf_notify_email',
                'type'          => 'text',
                'instructions'  => 'Điền email của bạn để nhận thông báo khi có người đăng ký. Nhiều email cách nhau dấu phẩy. VD: a@gmail.com, b@gmail.com',
                'placeholder'   => 'vidu@gmail.com',
                'default_value' => '',
            ],
        ],
        'location' => [[['param' => 'options_page', 'operator' => '==', 'value' => 'aepf-acf-options']]],
    ]);
}

// ─── Admin Menu ───────────────────────────────────────────────────────────────
add_action('admin_menu', 'aepf_admin_menu');
function aepf_admin_menu() {
    // ACF options page — đây là trang chính chứa TẤT CẢ cài đặt
    if (function_exists('acf_add_options_page')) {
        acf_add_options_page([
            'page_title'  => 'Popup Form — Toàn Bộ Cài Đặt',
            'menu_title'  => 'Popup Form',
            'menu_slug'   => 'aepf-acf-options',
            'parent_slug' => 'options-general.php',
            'icon_url'    => 'dashicons-megaphone',
            'redirect'    => false,
        ]);
        // Sub-page: Leads
        add_submenu_page('options-general.php', 'Leads Popup Form', 'Leads Popup', 'manage_options', 'aepf-leads', 'aepf_leads_page');
    } else {
        // Fallback nếu không có ACF Pro
        add_options_page('Popup Form', 'Popup Form', 'manage_options', 'aepf-settings', 'aepf_no_acf_notice');
    }
}
function aepf_no_acf_notice() {
    echo '<div class="wrap"><h1>Popup Form</h1><div class="notice notice-error"><p>⚠️ Plugin này yêu cầu <strong>Advanced Custom Fields PRO</strong> để hoạt động. Vui lòng kích hoạt plugin ACF PRO.</p></div></div>';
}

// ─── Đọc cài đặt từ ACF options ───────────────────────────────────────────────────────────────
function aepf_get_options() {
    $acf = function_exists('get_field');
    // specific_pages: ACF post_object trả về mảng ID nếu multiple
    $raw_pages = $acf ? get_field('aepf_specific_pages', 'option') : [];
    $specific_ids = [];
    if (!empty($raw_pages)) {
        foreach ((array) $raw_pages as $p) {
            $specific_ids[] = is_object($p) ? (int) $p->ID : (int) $p;
        }
    }
    return [
        'auto_show'      => $acf ? (bool) get_field('aepf_auto_show', 'option')              : true,
        'delay_seconds'  => $acf ? (int)  get_field('aepf_delay_seconds', 'option')           : 5,
        'show_frequency' => $acf ? (get_field('aepf_show_frequency', 'option') ?: 'once')     : 'once',
        'show_on'        => $acf ? (get_field('aepf_show_on', 'option')        ?: 'all')       : 'all',
        'include_front'  => $acf ? (bool) get_field('aepf_include_front', 'option')           : false,
        'specific_ids'   => $specific_ids,
        'cf7_id'         => $acf ? (int)  get_field('aepf_cf7_id', 'option')                  : 0,
    ];
}

// (Không còn settings page riêng — tất cả nằm trong ACF options page)

// ─── Shortcodes ────────────────────────────────────────────────────────────────
add_shortcode('aepf_button', 'aepf_button_shortcode');
function aepf_button_shortcode($atts) {
    $atts = shortcode_atts(['label' => '', 'class' => '', 'id' => 'aepf-trigger-btn'], $atts);
    $label = $atts['label'] ?: (function_exists('get_field') ? get_field('aepf_trigger_label', 'option') : '') ?: 'Đăng Ký Ngay';
    $class = esc_attr($atts['class']);
    $id    = esc_attr($atts['id']);
    return '<button type="button" id="' . $id . '" class="aepf-trigger-btn ' . $class . '" onclick="aepfOpenPopup()">' . esc_html($label) . '</button>';
}

add_shortcode('aepf_form', 'aepf_inline_form_shortcode');
function aepf_inline_form_shortcode() {
    // Đảm bảo assets được load khi dùng shortcode ngoài popup
    if (!wp_script_is('aepf-script', 'enqueued')) {
        wp_enqueue_style('aepf-style', AEPF_URL . 'assets/popup.css', [], AEPF_VERSION);
        wp_enqueue_script('aepf-script', AEPF_URL . 'assets/popup.js', ['jquery'], AEPF_VERSION, true);
    }

    $heading = (function_exists('get_field') ? get_field('aepf_heading', 'option') : '') ?: 'Đừng Bỏ Lỡ Cơ Hội! Đăng Ký Ngay Hôm Nay';
    $desc    = (function_exists('get_field') ? get_field('aepf_desc',    'option') : '') ?: "Số lượng đại lý tại mỗi khu vực có giới hạn.\n\nTrở thành đối tác của chúng tôi để cùng nhau phát triển bền vững, thành công lâu dài!";

    ob_start(); ?>
    <div class="aepf-inline-wrap">
        <div class="aepf-popup aepf-popup--inline">
            <div class="aepf-inner">
                <div class="aepf-left">
                    <h2 class="aepf-heading"><?php echo esc_html($heading); ?></h2>
                    <div class="aepf-desc"><?php echo nl2br(esc_html($desc)); ?></div>
                </div>
                <div class="aepf-right">
                    <?php aepf_render_form_content(); ?>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// ─── Check should show popup ──────────────────────────────────────────────────
function aepf_should_auto_show() {
    $opts = aepf_get_options();
    if (!$opts['auto_show']) return false;

    $show_on = $opts['show_on'];
    if ($show_on === 'all') return true;

    if ($show_on === 'specific') {
        // Trang chủ
        if ($opts['include_front'] && is_front_page()) return true;
        // Các trang cố định được chọn
        if (!empty($opts['specific_ids']) && in_array(get_the_ID(), $opts['specific_ids'])) return true;
        return false;
    }
    return true;
}

// ─── Enqueue Assets & Render Popup ──────────────────────────────────────────
add_action('wp_enqueue_scripts', 'aepf_enqueue');
function aepf_enqueue() {
    wp_enqueue_style('aepf-style', AEPF_URL . 'assets/popup.css', [], AEPF_VERSION);
    wp_enqueue_script('aepf-script', AEPF_URL . 'assets/popup.js', ['jquery'], AEPF_VERSION, true);

    $opts = aepf_get_options();
    wp_localize_script('aepf-script', 'aepfConfig', [
        'autoShow'  => aepf_should_auto_show() ? 1 : 0,
        'delay'     => intval($opts['delay_seconds']) * 1000,
        'frequency' => $opts['show_frequency'],
        'ajaxurl'   => admin_url('admin-ajax.php'),
        'nonce'     => wp_create_nonce('aepf_submit'),
        'btnLabel'  => (function_exists('get_field') ? get_field('aepf_btn_label', 'option') : '') ?: 'GỬI',
    ]);
}

// ─── Render form content (dùng chung cho popup và shortcode [aepf_form]) ────
function aepf_render_form_content() {
    $btn_label = (function_exists('get_field') ? get_field('aepf_btn_label', 'option') : '') ?: 'GỬI';
    $opts      = aepf_get_options();
    $cf7_id    = intval($opts['cf7_id']);
    ?>
    <?php if ($cf7_id && function_exists('do_shortcode')): ?>
        <?php echo do_shortcode('[contact-form-7 id="' . $cf7_id . '"]'); ?>
    <?php else: ?>
        <h3 class="aepf-form-title">THÔNG TIN ĐĂNG KÝ</h3>
        <form id="aepf-form" class="aepf-form" novalidate>
            <?php wp_nonce_field('aepf_submit', 'aepf_nonce'); ?>
            <div class="aepf-row">
                <div class="aepf-field">
                    <label for="aepf_name">Họ và tên <span class="aepf-req">*</span></label>
                    <input type="text" id="aepf_name" name="aepf_name" required placeholder="Nguyễn Văn A">
                </div>
                <div class="aepf-field">
                    <label for="aepf_phone">Số điện thoại <span class="aepf-req">*</span></label>
                    <input type="tel" id="aepf_phone" name="aepf_phone" required placeholder="0988 xxx xxx">
                </div>
            </div>
            <div class="aepf-row">
                <div class="aepf-field">
                    <label for="aepf_email">Email</label>
                    <input type="email" id="aepf_email" name="aepf_email" placeholder="email@gmail.com">
                </div>
                <div class="aepf-field">
                    <label for="aepf_area">Khu vực / Tỉnh thành <span class="aepf-req">*</span></label>
                    <input type="text" id="aepf_area" name="aepf_area" required placeholder="VD: TP. Hồ Chí Minh">
                </div>
            </div>
            <div class="aepf-field">
                <label for="aepf_object">Đối tượng đăng ký <span class="aepf-req">*</span></label>
                <div class="aepf-select-wrap">
                    <select id="aepf_object" name="aepf_object" required>
                        <option value="">— Chọn đối tượng —</option>
                        <option value="Khách hàng trải nghiệm">Khách hàng trải nghiệm</option>
                        <option value="Đại lý / Cửa hàng phân phối">Đại lý / Cửa hàng phân phối</option>
                        <option value="Đối tác hợp tác">Đối tác hợp tác</option>
                    </select>
                </div>
            </div>
            <div class="aepf-field">
                <label for="aepf_product">Sản phẩm quan tâm <span class="aepf-req">*</span></label>
                <div class="aepf-select-wrap">
                    <select id="aepf_product" name="aepf_product" required>
                        <option value="">— Chọn sản phẩm —</option>
                        <option value="XE ĐIỆN AI">XE ĐIỆN AI</option>
                        <option value="XE ĐẠP ĐIỆN TRỢ LỰC">XE ĐẠP ĐIỆN TRỢ LỰC</option>
                        <option value="XE MÁY ĐIỆN">XE MÁY ĐIỆN</option>
                        <option value="Khác">Khác</option>
                    </select>
                </div>
            </div>
            <div class="aepf-field">
                <label for="aepf_need">Nhu cầu <span class="aepf-req">*</span></label>
                <div class="aepf-select-wrap">
                    <select id="aepf_need" name="aepf_need" required>
                        <option value="">— Chọn nhu cầu —</option>
                        <option value="Đăng ký lái thử">Đăng ký lái thử</option>
                        <option value="Nhận báo giá">Nhận báo giá</option>
                        <option value="Tư vấn mở đại lý">Tư vấn mở đại lý</option>
                        <option value="Nhập hàng / phân phối">Nhập hàng / phân phối</option>
                        <option value="Hợp tác kinh doanh">Hợp tác kinh doanh</option>
                    </select>
                </div>
            </div>
            <div class="aepf-field">
                <label for="aepf_message">Nội dung cần hỗ trợ</label>
                <textarea id="aepf_message" name="aepf_message" rows="2" placeholder="Nhập nội dung cần tư vấn thêm..."></textarea>
            </div>
            <div class="aepf-msg"></div>
            <button type="submit" class="aepf-submit"><?php echo esc_html($btn_label); ?></button>
        </form>
    <?php endif;
}

// ─── Render Popup (wrapper + left column + form) ──────────────────────────
add_action('wp_footer', 'aepf_render_popup', 99);
function aepf_render_popup() {
    $heading     = (function_exists('get_field') ? get_field('aepf_heading', 'option') : '') ?: 'Đừng Bỏ Lỡ Cơ Hội! Đăng Ký Ngay Hôm Nay';
    $desc        = (function_exists('get_field') ? get_field('aepf_desc', 'option') : '') ?: "Số lượng đại lý tại mỗi khu vực có giới hạn. Đừng bỏ lỡ cơ hội hợp tác kinh doanh hấp dẫn 2026 với thương hiệu xe điện đang tăng trưởng nhanh hàng đầu Việt Nam.\n\nTrở thành đối tác của chúng tôi để cùng nhau phát triển bền vững, thành công lâu dài!";
    ?>
    <div id="aepf-overlay" class="aepf-overlay" role="dialog" aria-modal="true" aria-labelledby="aepf-heading">
        <div class="aepf-popup">
            <button class="aepf-close" onclick="aepfClosePopup()" aria-label="Đóng">&#x2715;</button>
            <div class="aepf-inner">
                <!-- Cột trái: hình (nếu có) hoặc nền cam + text -->
                <?php $left_img = function_exists('get_field') ? get_field('aepf_left_image', 'option') : null; ?>
                <?php if (!empty($left_img['url'])): ?>
                <div class="aepf-left aepf-left--image" style="background-image:url('<?php echo esc_url($left_img['url']); ?>')">
                    <span id="aepf-heading" class="screen-reader-text"><?php echo esc_html($heading); ?></span>
                </div>
                <?php else: ?>
                <div class="aepf-left">
                    <h2 id="aepf-heading" class="aepf-heading"><?php echo esc_html($heading); ?></h2>
                    <div class="aepf-desc"><?php echo nl2br(esc_html($desc)); ?></div>
                </div>
                <?php endif; ?>
                <!-- Cột phải: form -->
                <div class="aepf-right">
                    <?php aepf_render_form_content(); ?>
                </div>
            </div>
        </div>
    </div>
    <?php
}

// ─── AJAX Form Handler ──────────────────────────────────────────────────────
add_action('wp_ajax_aepf_submit', 'aepf_handle_submit');
add_action('wp_ajax_nopriv_aepf_submit', 'aepf_handle_submit');
function aepf_handle_submit() {
    check_ajax_referer('aepf_submit', 'aepf_nonce');

    $name    = sanitize_text_field($_POST['aepf_name']    ?? '');
    $phone   = sanitize_text_field($_POST['aepf_phone']   ?? '');
    $email   = sanitize_email($_POST['aepf_email']        ?? '');
    $area    = sanitize_text_field($_POST['aepf_area']    ?? '');
    $object  = sanitize_text_field($_POST['aepf_object']  ?? '');
    $product = sanitize_text_field($_POST['aepf_product'] ?? '');
    $need    = sanitize_text_field($_POST['aepf_need']    ?? '');
    $message = sanitize_textarea_field($_POST['aepf_message'] ?? '');

    if (empty($name) || empty($phone) || empty($area) || empty($object) || empty($product) || empty($need)) {
        wp_send_json_error(['msg' => 'Vui lòng điền đầy đủ các trường bắt buộc (*).' ]);
    }

    // Xác định email nhận thông báo: lấy từ ACF, fallback về admin_email
    $notify_raw  = (function_exists('get_field') ? get_field('aepf_notify_email', 'option') : '') ?: '';
    if (!empty($notify_raw)) {
        // Hỗ trợ nhiều email cách nhau dấu phẩy
        $to_emails = array_filter(array_map('trim', explode(',', $notify_raw)), 'is_email');
    }
    if (empty($to_emails)) {
        $to_emails = [get_option('admin_email')];
    }

    $subject = '[Đăng Ký Bluerabike] ' . $name . ' — ' . $phone;
    $body    = "Họ tên:   $name\nSĐT:      $phone\nEmail:    $email\nKhu vực:  $area\n\nĐối tượng: $object\nSản phẩm:  $product\nNhu cầu:   $need\n\nNội dung:\n$message";
    $headers = ['Content-Type: text/plain; charset=UTF-8'];
    if ($email) $headers[] = 'Reply-To: ' . $email;

    foreach ($to_emails as $to) {
        wp_mail($to, $subject, $body, $headers);
    }

    // Lưu vào DB
    $leads = get_option('aepf_leads', []);
    $leads[] = [
        'time'    => current_time('mysql'),
        'name'    => $name,
        'phone'   => $phone,
        'email'   => $email,
        'area'    => $area,
        'object'  => $object,
        'product' => $product,
        'need'    => $need,
        'message' => $message,
    ];
    update_option('aepf_leads', array_slice($leads, -500));

    $success_msg = (function_exists('get_field') ? get_field('aepf_success_msg', 'option') : '') ?: 'Cảm ơn bạn! Chúng tôi sẽ liên hệ sớm nhất.';
    wp_send_json_success(['msg' => $success_msg]);
}

// ─── Leads Admin Page ──────────────────────────────────────────────────────
add_action('admin_menu', 'aepf_leads_menu');
function aepf_leads_menu() {
    add_submenu_page('options-general.php', 'Leads Đăng Ký', 'Leads Popup', 'manage_options', 'aepf-leads', 'aepf_leads_page');
}

function aepf_leads_page() {
    $leads = array_reverse(get_option('aepf_leads', []));
    ?>
    <div class="wrap">
        <h1>📋 Leads Đăng Ký Đại Lý (<?php echo count($leads); ?> người)</h1>
        <?php if (empty($leads)): ?>
            <p>Chưa có lead nào.</p>
        <?php else: ?>
        <table class="widefat striped">
            <thead><tr>
                <th>Thời gian</th><th>Họ tên</th><th>SĐT</th><th>Email</th><th>Khu vực</th>
                <th>Đối tượng</th><th>Sản phẩm</th><th>Nhu cầu</th><th>Nội dung</th>
            </tr></thead>
            <tbody>
            <?php foreach ($leads as $lead): ?>
                <tr>
                    <td><?php echo esc_html($lead['time']); ?></td>
                    <td><?php echo esc_html($lead['name']); ?></td>
                    <td><?php echo esc_html($lead['phone']); ?></td>
                    <td><?php echo esc_html($lead['email']); ?></td>
                    <td><?php echo esc_html($lead['area']); ?></td>
                    <td><?php echo esc_html($lead['object']  ?? ''); ?></td>
                    <td><?php echo esc_html($lead['product'] ?? ''); ?></td>
                    <td><?php echo esc_html($lead['need']    ?? ''); ?></td>
                    <td><?php echo esc_html($lead['message'] ?? ''); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
    <?php
}
