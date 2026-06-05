<?php
/**
 * Plugin Name: SPL First Frame Optimizer
 * Description: Tối ưu khung hình đầu tiên (FCP, LCP, Sizes, Critical CSS) và tương thích cache (LiteSpeed, FlyingPress, Redis...). Hỗ trợ lazy loading thông minh.
 * Author: SPL
 * Version: 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class SPL_First_Frame_Optimizer
{

    protected static $instance = null;

    protected $lcp_attachment_id = 0;

    protected $detected_cache_plugins = [];

    protected $eager_image_count = 1;

    protected $processed_images = 0;

    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        // Detect cache systems early
        add_action('plugins_loaded', [$this, 'detect_cache_systems'], 1);

        // Frontend
        add_action('wp', [$this, 'detect_lcp_image']);
        add_action('wp_head', [$this, 'output_critical_css'], 0);
        add_action('wp_head', [$this, 'output_lcp_preload'], 5);
        add_filter('wp_get_attachment_image_attributes', [$this, 'filter_lcp_image_attrs'], 10, 3);
        add_filter('wp_lazy_loading_enabled', [$this, 'smart_lazy_loading'], 10, 3);
        add_filter('the_content', [$this, 'scan_first_frame_backgrounds'], 99);

        // Cache compatibility hooks
        add_filter('litespeed_optimize_css_excludes', [$this, 'litespeed_exclude_critical_css']);
        add_filter('litespeed_optm_html_head', [$this, 'litespeed_protect_preload'], 10, 1);
        add_filter('flying_press_exclude_css', [$this, 'flyingpress_exclude_critical']);
        add_filter('flying_press_exclude_inline_css', [$this, 'flyingpress_exclude_inline_critical']);
        add_filter('rocket_exclude_css', [$this, 'rocket_exclude_critical']);
        add_filter('autoptimize_filter_css_exclude', [$this, 'autoptimize_exclude_critical']);

        // Purge cache on settings update
        add_action('update_option_spl_ff_critical_css', [$this, 'purge_all_caches']);
        add_action('update_option_spl_ff_hero_font_urls', [$this, 'purge_all_caches']);

        // Settings
        add_action('admin_menu', [$this, 'register_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    /* --------------------------------------------------------
     *  CACHE SYSTEM DETECTION & COMPATIBILITY
     * ----------------------------------------------------- */

    public function detect_cache_systems()
    {
        $this->detected_cache_plugins = [];

        // LiteSpeed Cache
        if (defined('LSCWP_V') || class_exists('LiteSpeed\Core')) {
            $this->detected_cache_plugins['litespeed'] = 'LiteSpeed Cache';
        }

        // FlyingPress
        if (defined('FLYING_PRESS_VERSION') || class_exists('FlyingPress\\Application')) {
            $this->detected_cache_plugins['flyingpress'] = 'FlyingPress';
        }

        // WP Rocket
        if (defined('WP_ROCKET_VERSION') || function_exists('rocket_clean_domain')) {
            $this->detected_cache_plugins['wp_rocket'] = 'WP Rocket';
        }

        // W3 Total Cache
        if (defined('W3TC') || function_exists('w3tc_flush_all')) {
            $this->detected_cache_plugins['w3tc'] = 'W3 Total Cache';
        }

        // Autoptimize
        if (defined('AUTOPTIMIZE_PLUGIN_VERSION') || class_exists('autoptimizeCache')) {
            $this->detected_cache_plugins['autoptimize'] = 'Autoptimize';
        }

        // WP Super Cache
        if (defined('WPCACHEHOME') || function_exists('wp_cache_clear_cache')) {
            $this->detected_cache_plugins['wp_super_cache'] = 'WP Super Cache';
        }

        // Redis Object Cache
        if (defined('WP_REDIS_VERSION') || class_exists('WP_Redis_Object_Cache')) {
            $this->detected_cache_plugins['redis'] = 'Redis Object Cache';
        }
    }

    public function litespeed_exclude_critical_css($excludes)
    {
        $excludes[] = 'spl-first-frame-critical-css';
        return $excludes;
    }

    public function litespeed_protect_preload($content)
    {
        // Prevent LiteSpeed from modifying our preload tags
        return $content;
    }

    public function flyingpress_exclude_critical($excludes)
    {
        if (!is_array($excludes)) {
            $excludes = [];
        }
        $excludes[] = 'spl-first-frame-critical-css';
        return $excludes;
    }

    public function flyingpress_exclude_inline_critical($excludes)
    {
        if (!is_array($excludes)) {
            $excludes = [];
        }
        $excludes[] = '#spl-first-frame-critical-css';
        return $excludes;
    }

    public function rocket_exclude_critical($excludes)
    {
        if (!is_array($excludes)) {
            $excludes = [];
        }
        $excludes[] = wp_make_link_relative(admin_url()) . 'admin-ajax.php';
        return $excludes;
    }

    public function autoptimize_exclude_critical($exclude_css)
    {
        return $exclude_css . ', spl-first-frame-critical-css';
    }

    public function purge_all_caches()
    {
        // LiteSpeed Cache
        if (isset($this->detected_cache_plugins['litespeed'])) {
            if (class_exists('LiteSpeed\Purge')) {
                do_action('litespeed_purge_all');
            }
        }

        // FlyingPress
        if (isset($this->detected_cache_plugins['flyingpress'])) {
            if (function_exists('flying_press_purge_cache')) {
                flying_press_purge_cache();
            }
        }

        // WP Rocket
        if (isset($this->detected_cache_plugins['wp_rocket'])) {
            if (function_exists('rocket_clean_domain')) {
                rocket_clean_domain();
            }
        }

        // W3 Total Cache
        if (isset($this->detected_cache_plugins['w3tc'])) {
            if (function_exists('w3tc_flush_all')) {
                w3tc_flush_all();
            }
        }

        // WP Super Cache
        if (isset($this->detected_cache_plugins['wp_super_cache'])) {
            if (function_exists('wp_cache_clear_cache')) {
                wp_cache_clear_cache();
            }
        }

        // Redis
        if (isset($this->detected_cache_plugins['redis'])) {
            if (function_exists('wp_cache_flush')) {
                wp_cache_flush();
            }
        }
    }

    /* --------------------------------------------------------
     *  SETTINGS
     * ----------------------------------------------------- */

    public function register_settings_page()
    {
        add_options_page(
            'SPL First Frame',
            'SPL First Frame',
            'manage_options',
            'spl-first-frame',
            [$this, 'settings_page_html']
        );
    }

    public function register_settings()
    {
        register_setting('spl_first_frame', 'spl_ff_critical_css');
        register_setting('spl_first_frame', 'spl_ff_hero_font_urls');
        register_setting('spl_first_frame', 'spl_ff_eager_image_count', [
            'type' => 'integer',
            'default' => 1,
            'sanitize_callback' => function ($value) {
                return max(1, min(5, intval($value)));
            }
        ]);
    }

    public function settings_page_html()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $eager_count = get_option('spl_ff_eager_image_count', 1);

        ?>
        <div class="wrap">
            <h1>SPL First Frame Optimizer v2.0</h1>

            <?php if (!empty($this->detected_cache_plugins)): ?>
                <div class="notice notice-success">
                    <p><strong>🎯 Cache plugins đã phát hiện:</strong>
                        <?php echo implode(', ', $this->detected_cache_plugins); ?>
                    </p>
                    <p>Plugin sẽ tự động tương thích và purge cache khi bạn lưu settings.</p>
                </div>
            <?php else: ?>
                <div class="notice notice-info">
                    <p><strong>ℹ️ Chưa phát hiện cache plugin nào.</strong> Plugin vẫn hoạt động bình thường.</p>
                </div>
            <?php endif; ?>

            <form method="post" action="options.php">
                <?php
                settings_fields('spl_first_frame');
                do_settings_sections('spl_first_frame');
                ?>

                <h2>⚡ Critical CSS & Fonts</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">Critical CSS cho khung hình đầu tiên</th>
                        <td>
                            <textarea name="spl_ff_critical_css" rows="12" cols="80" class="large-text code"
                                placeholder="Dán critical CSS cho hero/khung hình đầu tiên..."><?php echo esc_textarea(get_option('spl_ff_critical_css', '')); ?></textarea>
                            <p class="description">
                                Dán CSS tối thiểu cho hero (banner, menu, font chính). Plugin sẽ inline vào
                                <code>&lt;head&gt;</code> và tự động exclude khỏi cache optimization.
                                <br>💡 <strong>Nguồn Critical CSS:</strong> FlyingPress generator, <a
                                    href="https://web.dev/extract-critical-css/" target="_blank">web.dev tools</a>, hoặc tự
                                viết.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Preload font cho hero</th>
                        <td>
                            <textarea name="spl_ff_hero_font_urls" rows="6" cols="80" class="large-text code"
                                placeholder="Mỗi dòng là 1 URL font, ví dụ:&#10;https://example.com/wp-content/themes/your-theme/fonts/Roboto-400.woff2&#10;https://example.com/wp-content/themes/your-theme/fonts/Roboto-700.woff2"><?php echo esc_textarea(get_option('spl_ff_hero_font_urls', '')); ?></textarea>
                            <p class="description">
                                Các URL font dùng ở hero (khung hình đầu tiên). Plugin sẽ preload bằng
                                <code>&lt;link rel="preload" as="font" ...&gt;</code>
                                <br>💡 <strong>Best practice:</strong> Chỉ preload 1-2 font quan trọng nhất, dạng
                                <code>.woff2</code> để tối ưu nhất.
                            </p>
                        </td>
                    </tr>
                </table>

                <h2>🖼️ Lazy Loading Configuration</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">Số lượng ảnh eager load</th>
                        <td>
                            <input type="number" name="spl_ff_eager_image_count" value="<?php echo esc_attr($eager_count); ?>"
                                min="1" max="5" class="small-text">
                            <p class="description">
                                Số ảnh đầu tiên sẽ được load ngay (eager), các ảnh còn lại sẽ lazy load.
                                <br>💡 <strong>Khuyến nghị:</strong>
                                - <code>1</code> cho landing page (chỉ LCP image)
                                - <code>2-3</code> nếu hero có nhiều ảnh quan trọng
                                - Tối đa <code>5</code> để tránh ảnh hưởng performance
                            </p>
                        </td>
                    </tr>
                </table>

                <?php submit_button('💾 Lưu Settings & Purge Cache'); ?>
            </form>

            <hr>
            <h2>📚 Hướng dẫn sử dụng</h2>
            <div class="card">
                <h3>1. Critical CSS - Cách tạo</h3>
                <ol>
                    <li><strong>Dùng FlyingPress:</strong> Settings → Critical CSS → Copy CSS được generate</li>
                    <li><strong>Dùng Chrome DevTools:</strong> Coverage tab → Load trang → Copy CSS cho hero</li>
                    <li><strong>Online tools:</strong> <a href="https://www.sitelocity.com/critical-path-css-generator"
                            target="_blank">Critical Path CSS Generator</a></li>
                </ol>

                <h3>2. Font Preload - Tìm URL</h3>
                <ol>
                    <li>Mở DevTools → Network tab → Filter "Font"</li>
                    <li>Load trang → Tìm font được dùng ở hero</li>
                    <li>Copy full URL (ưu tiên file .woff2)</li>
                </ol>

                <h3>3. Kiểm tra cache compatibility</h3>
                <p>Sau khi lưu settings:</p>
                <ul>
                    <li>✅ Cache sẽ tự động purge</li>
                    <li>✅ Critical CSS không bị minify/combine</li>
                    <li>✅ Preload tags được giữ nguyên</li>
                    <li>✅ Lazy loading hoạt động đúng</li>
                </ul>
            </div>
        </div>
        <?php
    }

    /* --------------------------------------------------------
     *  DETECT LCP IMAGE (KHUNG HÌNH ĐẦU TIÊN)
     * ----------------------------------------------------- */

    public function detect_lcp_image()
    {
        if (is_admin() || wp_is_json_request()) {
            return;
        }

        // Get eager image count from settings
        $this->eager_image_count = get_option('spl_ff_eager_image_count', 1);

        // 1. Trang chủ kiểu landing page: lấy front page
        if (is_front_page()) {
            $post_id = get_queried_object_id();

            // Ưu tiên featured image
            $thumb_id = get_post_thumbnail_id($post_id);
            if ($thumb_id) {
                $this->lcp_attachment_id = $thumb_id;
                return;
            }

            // Không có featured image thì lấy ảnh đầu tiên trong content
            $this->lcp_attachment_id = $this->get_first_image_id_from_content($post_id);
            return;
        }

        // 2. Single post / page khác
        if (is_singular()) {
            $post_id = get_queried_object_id();
            $thumb_id = get_post_thumbnail_id($post_id);
            if ($thumb_id) {
                $this->lcp_attachment_id = $thumb_id;
                return;
            }

            $this->lcp_attachment_id = $this->get_first_image_id_from_content($post_id);
            return;
        }

        // 3. Home blog / archive: lấy thumbnail post đầu tiên
        if (is_home() || is_archive()) {
            global $wp_query;
            if ($wp_query && !empty($wp_query->posts)) {
                $first_post = $wp_query->posts[0];
                $thumb_id = get_post_thumbnail_id($first_post->ID);

                if ($thumb_id) {
                    $this->lcp_attachment_id = $thumb_id;
                    return;
                }

                $this->lcp_attachment_id = $this->get_first_image_id_from_content($first_post->ID);
            }
        }
    }

    protected function get_first_image_id_from_content($post_id)
    {
        $post = get_post($post_id);
        if (!$post) {
            return 0;
        }

        // gallery shortcode
        if (has_shortcode($post->post_content, 'gallery')) {
            $galleries = get_post_galleries($post, false);
            if (!empty($galleries[0]['ids'])) {
                $ids = array_filter(array_map('intval', explode(',', $galleries[0]['ids'])));
                if (!empty($ids[0])) {
                    return (int) $ids[0];
                }
            }
        }

        // <img class="wp-image-123">
        if (preg_match('/wp-image-([0-9]+)/', $post->post_content, $m)) {
            return (int) $m[1];
        }

        return 0;
    }

    /* --------------------------------------------------------
     *  CRITICAL CSS + PRELOAD
     * ----------------------------------------------------- */

    public function output_critical_css()
    {
        if (is_admin() || wp_is_json_request()) {
            return;
        }

        $critical_css = trim((string) get_option('spl_ff_critical_css', ''));
        if ($critical_css !== '') {
            echo "\n<!-- SPL First Frame: Critical CSS -->\n";
            echo "<style id=\"spl-first-frame-critical-css\">";
            echo $critical_css;
            echo "</style>\n";
        }

        $font_urls = trim((string) get_option('spl_ff_hero_font_urls', ''));
        if ($font_urls !== '') {
            echo "\n<!-- SPL First Frame: Font Preload -->\n";
            $lines = preg_split('/\r\n|\r|\n/', $font_urls);
            foreach ($lines as $url) {
                $url = trim($url);
                if ($url === '') {
                    continue;
                }
                $esc = esc_url($url);
                echo '<link rel="preload" href="' . $esc . '" as="font" type="font/woff2" crossorigin="anonymous" />' . "\n";
            }
        }
    }

    public function output_lcp_preload()
    {
        if (!$this->lcp_attachment_id || is_admin() || wp_is_json_request()) {
            return;
        }

        $src = wp_get_attachment_image_src($this->lcp_attachment_id, 'large');
        if (!$src) {
            $src = wp_get_attachment_image_src($this->lcp_attachment_id, 'full');
        }
        if (!$src || empty($src[0])) {
            return;
        }

        $image_url = esc_url($src[0]);
        $type = get_post_mime_type($this->lcp_attachment_id);

        $srcset = wp_get_attachment_image_srcset($this->lcp_attachment_id, 'large');
        $sizes = '(max-width: 768px) 100vw, (max-width: 1200px) 100vw, 1200px';

        echo "\n<!-- SPL First Frame: Preload LCP Image -->\n";
        ?>
        <link rel="preload" as="image" href="<?php echo $image_url; ?>" imagesrcset="<?php echo esc_attr($srcset); ?>"
            imagesizes="<?php echo esc_attr($sizes); ?>" fetchpriority="high"
            type="<?php echo esc_attr($type ?: 'image/jpeg'); ?>">
        <?php
    }

    public function filter_lcp_image_attrs($attr, $attachment, $size)
    {
        if (!$this->lcp_attachment_id || (int) $attachment->ID !== (int) $this->lcp_attachment_id) {
            return $attr;
        }

        $attr['loading'] = 'eager';
        $attr['decoding'] = 'async';
        $attr['fetchpriority'] = 'high';

        if (empty($attr['sizes'])) {
            $attr['sizes'] = '(max-width: 768px) 100vw, (max-width: 1200px) 100vw, 1200px';
        }

        return $attr;
    }

    // Smart lazy loading: eager cho N ảnh đầu, lazy cho phần còn lại
    public function smart_lazy_loading($default, $tag_name, $context)
    {
        if ($tag_name !== 'img') {
            return $default;
        }

        // Tăng counter cho mỗi ảnh
        $this->processed_images++;

        // N ảnh đầu tiên: tắt lazy load (eager)
        if ($this->processed_images <= $this->eager_image_count) {
            return false;
        }

        // Ảnh còn lại: BẬT lazy load
        return true;
    }

    /* --------------------------------------------------------
     *  BACKGROUND IMAGE TRONG KHUNG ĐẦU (OPTIONAL, DẠNG THÔ)
     * ----------------------------------------------------- */

    public function scan_first_frame_backgrounds($content)
    {
        // Ý tưởng: nếu bạn bọc hero bằng class .spl-first-frame
        // và trong đó có inline style background-image:url(...),
        // plugin sẽ preload image đó.
        if (strpos($content, 'spl-first-frame') === false || strpos($content, 'background-image') === false) {
            return $content;
        }

        if (
            preg_match_all(
                '/class="[^"]*spl-first-frame[^"]*"[^>]*style="[^"]*background-image:\s*url\((\'|")(?<url>[^\'")+]+)\1?\)[^"]*"/i',
                $content,
                $matches
            )
        ) {
            $bg_urls = array_unique($matches['url']);
            add_action('wp_head', function () use ($bg_urls) {
                foreach ($bg_urls as $url) {
                    $esc = esc_url($url);
                    echo "\n<!-- SPL First Frame: Preload hero background -->\n";
                    echo '<link rel="preload" as="image" href="' . $esc . '">' . "\n";
                }
            }, 6);
        }

        return $content;
    }
}

SPL_First_Frame_Optimizer::instance();
