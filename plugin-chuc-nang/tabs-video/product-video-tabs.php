<?php

/**
 * Plugin Name: Product Video Tabs
 * Plugin URI: https://splworks.com
 * Description: Add YouTube & TikTok video tabs to WooCommerce product pages with popup player support.
 * Version: 1.0.0
 * Author: SPLinh
 * Author URI: https://splworks.com
 * License: GPL v2 or later
 * Text Domain: product-video-tabs
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * WC requires at least: 7.0
 * WC tested up to: 8.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('PVT_VERSION', '1.0.0');
define('PVT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PVT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PVT_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Plugin Class
 */
final class Product_Video_Tabs
{

    private static $instance = null;

    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->init_hooks();
    }

    private function init_hooks()
    {
        // Load text domain
        add_action('init', [$this, 'load_textdomain']);

        // Check dependencies
        add_action('admin_init', [$this, 'check_dependencies']);

        // Register ACF fields - use multiple hooks for compatibility
        add_action('acf/init', [$this, 'register_acf_fields']);
        add_action('acf/include_fields', [$this, 'register_acf_fields']);

        // Fallback: register on init if ACF is already loaded
        add_action('init', [$this, 'maybe_register_acf_fields'], 20);

        // Add video tab to WooCommerce
        add_filter('woocommerce_product_tabs', [$this, 'add_video_tab'], 50);

        // PHP-based video gallery injection (WPGS plugin compatible)
        add_action('woocommerce_before_single_product_summary', [$this, 'inject_video_gallery_php'], 5);

        // Also add inline script for WPGS Slick slider integration
        add_action('wp_footer', [$this, 'output_wpgs_video_script'], 99);

        // Enqueue assets
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);

        // Add popup modal to footer
        add_action('wp_footer', [$this, 'render_video_popup_modal']);
    }

    public function load_textdomain()
    {
        load_plugin_textdomain('product-video-tabs', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    /**
     * Fallback ACF registration on init
     */
    public function maybe_register_acf_fields()
    {
        if (function_exists('acf_add_local_field_group') && !did_action('acf/init')) {
            $this->register_acf_fields();
        }
    }

    /**
     * Check if WooCommerce and ACF are active
     */
    public function check_dependencies()
    {
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', function () {
                echo '<div class="error"><p><strong>Product Video Tabs</strong> requires WooCommerce to be installed and active.</p></div>';
            });
            deactivate_plugins(PVT_PLUGIN_BASENAME);
        }

        if (!class_exists('ACF')) {
            add_action('admin_notices', function () {
                echo '<div class="error"><p><strong>Product Video Tabs</strong> requires Advanced Custom Fields (ACF) to be installed and active.</p></div>';
            });
        }
    }

    /**
     * Register ACF Fields for Videos
     */
    public function register_acf_fields()
    {
        // Prevent duplicate registration
        static $registered = false;
        if ($registered) {
            return;
        }

        if (!function_exists('acf_add_local_field_group')) {
            return;
        }

        $registered = true;

        // Field Group 1: Videos for Tab (repeater for multiple videos)
        acf_add_local_field_group([
            'key' => 'group_pvt_product_videos',
            'title' => __('Video Tab - Danh sách video', 'product-video-tabs'),
            'fields' => [
                // YouTube Videos Repeater
                [
                    'key' => 'field_pvt_youtube_videos',
                    'label' => __('YouTube Videos', 'product-video-tabs'),
                    'name' => 'youtube_videos',
                    'type' => 'repeater',
                    'instructions' => __('Add YouTube video URLs for Video tab', 'product-video-tabs'),
                    'layout' => 'table',
                    'button_label' => __('Add YouTube Video', 'product-video-tabs'),
                    'sub_fields' => [
                        [
                            'key' => 'field_pvt_youtube_url',
                            'label' => __('YouTube URL', 'product-video-tabs'),
                            'name' => 'youtube_url',
                            'type' => 'url',
                            'placeholder' => 'https://www.youtube.com/watch?v=...',
                        ],
                    ],
                ],
                // TikTok Videos Repeater
                [
                    'key' => 'field_pvt_tiktok_videos',
                    'label' => __('TikTok Videos', 'product-video-tabs'),
                    'name' => 'tiktok_videos',
                    'type' => 'repeater',
                    'instructions' => __('Add TikTok video URLs for Video tab', 'product-video-tabs'),
                    'layout' => 'table',
                    'button_label' => __('Add TikTok Video', 'product-video-tabs'),
                    'sub_fields' => [
                        [
                            'key' => 'field_pvt_tiktok_url',
                            'label' => __('TikTok URL', 'product-video-tabs'),
                            'name' => 'tiktok_url',
                            'type' => 'url',
                            'placeholder' => 'https://www.tiktok.com/@user/video/123456789',
                        ],
                    ],
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'product',
                    ],
                ],
            ],
            'menu_order' => 10,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
        ]);

        // Field Group 2: Video for Gallery (single video, shows first)
        acf_add_local_field_group([
            'key' => 'group_pvt_gallery_video',
            'title' => __('🎬 Video Gallery - Hiển thị trong gallery ảnh', 'product-video-tabs'),
            'fields' => [
                [
                    'key' => 'field_pvt_gallery_video_url',
                    'label' => __('Video URL (YouTube hoặc TikTok)', 'product-video-tabs'),
                    'name' => 'gallery_video_url',
                    'type' => 'url',
                    'instructions' => __('Video này sẽ hiển thị ĐẦU TIÊN trong gallery ảnh sản phẩm', 'product-video-tabs'),
                    'placeholder' => 'https://www.youtube.com/watch?v=... hoặc https://www.tiktok.com/@user/video/...',
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'product',
                    ],
                ],
            ],
            'menu_order' => 5,
            'position' => 'side',
            'style' => 'default',
            'label_placement' => 'top',
        ]);
    }

    /**
     * Add Video Tab to WooCommerce Product Tabs
     */
    public function add_video_tab($tabs)
    {
        global $product;

        // Get valid product object
        $product_obj = $this->get_product_object($product);
        if (!$product_obj) {
            // Debug mode: check if PVT_DEBUG is defined
            if (defined('PVT_DEBUG') && PVT_DEBUG) {
                error_log('PVT Debug: Could not get valid product object');
            }
            return $tabs;
        }

        $product_id = $product_obj->get_id();

        // Try multiple field name variations for compatibility
        $youtube_videos = get_field('youtube_videos', $product_id);
        $tiktok_videos = get_field('tiktok_videos', $product_id);

        // Fallback: try with post meta directly (for manual ACF setup)
        if (empty($youtube_videos)) {
            $youtube_meta = get_post_meta($product_id, 'youtube_videos', true);
            if (!empty($youtube_meta)) {
                $youtube_videos = maybe_unserialize($youtube_meta);
            }
        }

        if (empty($tiktok_videos)) {
            $tiktok_meta = get_post_meta($product_id, 'tiktok_videos', true);
            if (!empty($tiktok_meta)) {
                $tiktok_videos = maybe_unserialize($tiktok_meta);
            }
        }

        // Debug mode
        if (defined('PVT_DEBUG') && PVT_DEBUG) {
            error_log('PVT Debug: Product ID = ' . $product_id);
            error_log('PVT Debug: YouTube Videos = ' . print_r($youtube_videos, true));
            error_log('PVT Debug: TikTok Videos = ' . print_r($tiktok_videos, true));
        }

        if (empty($youtube_videos) && empty($tiktok_videos)) {
            return $tabs;
        }

        $tabs['pvt_videos'] = [
            'title' => __('Video', 'product-video-tabs'),
            'priority' => 25,
            'callback' => [$this, 'render_video_tab_content'],
        ];

        return $tabs;
    }

    /**
     * Render Video Tab Content
     */
    public function render_video_tab_content()
    {
        global $product;

        // Ensure we have a valid product object
        $product_obj = $this->get_product_object($product);
        if (!$product_obj) {
            return;
        }

        $product_id = $product_obj->get_id();

        // Get videos with fallback
        $youtube_videos = get_field('youtube_videos', $product_id) ?: [];
        $tiktok_videos = get_field('tiktok_videos', $product_id) ?: [];

        // Fallback: try with post meta directly
        if (empty($youtube_videos)) {
            $youtube_meta = get_post_meta($product_id, 'youtube_videos', true);
            if (!empty($youtube_meta)) {
                $youtube_videos = maybe_unserialize($youtube_meta);
            }
        }

        if (empty($tiktok_videos)) {
            $tiktok_meta = get_post_meta($product_id, 'tiktok_videos', true);
            if (!empty($tiktok_meta)) {
                $tiktok_videos = maybe_unserialize($tiktok_meta);
            }
        }

        include PVT_PLUGIN_DIR . 'templates/video-tab-content.php';
    }

    /**
     * Enqueue Plugin Assets
     */
    public function enqueue_assets()
    {
        if (!is_product()) {
            return;
        }

        // Get product ID safely
        $product_id = get_queried_object_id();
        if (!$product_id) {
            return;
        }

        $youtube_videos = get_field('youtube_videos', $product_id);
        $tiktok_videos = get_field('tiktok_videos', $product_id);

        if (empty($youtube_videos) && empty($tiktok_videos)) {
            return;
        }

        wp_enqueue_style(
            'pvt-video-tabs',
            PVT_PLUGIN_URL . 'assets/css/video-tabs.css',
            [],
            PVT_VERSION
        );

        wp_enqueue_script(
            'pvt-video-tabs',
            PVT_PLUGIN_URL . 'assets/js/video-tabs.js',
            [],
            PVT_VERSION,
            true
        );
    }

    /**
     * Helper: Get valid WC_Product object
     */
    private function get_product_object($product)
    {
        if ($product instanceof \WC_Product) {
            return $product;
        }

        // If it's a post ID or post object
        if (is_numeric($product)) {
            return wc_get_product($product);
        }

        if (is_object($product) && isset($product->ID)) {
            return wc_get_product($product->ID);
        }

        // Try to get from queried object
        $product_id = get_queried_object_id();
        if ($product_id) {
            return wc_get_product($product_id);
        }

        return null;
    }

    /**
     * PHP-based Video Gallery Injection
     * Outputs hidden video slide HTML that will be moved by minimal JS
     */
    public function inject_video_gallery_php()
    {
        global $product;

        $product_obj = $this->get_product_object($product);
        if (!$product_obj) {
            return;
        }

        // Get gallery video URL
        $gallery_video_url = get_field('gallery_video_url', $product_obj->get_id());

        if (empty($gallery_video_url)) {
            return;
        }

        // Detect video type
        $video_type = $this->detect_video_type($gallery_video_url);

        if ($video_type === 'tiktok') {
            $video_id = self::get_tiktok_video_id($gallery_video_url);
            $embed_url = self::get_tiktok_embed_url($gallery_video_url, false);
?>
            <!-- PVT: Video slides for WPGS gallery (LAZY LOAD) -->
            <div id="pvt-video-slides" style="display:none !important;">
                <!-- Main slide (large video) - LAZY LOAD -->
                <div id="pvt-main-slide" class="pvt-video-main-slide" data-video-type="tiktok" data-embed-url="<?php echo esc_url($embed_url); ?>">
                    <div class="pvt-video-main-wrapper pvt-video-main-wrapper--tiktok pvt-video-placeholder pvt-video-placeholder--tiktok">
                        <div class="pvt-video-placeholder-content">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="48" height="48" fill="#fff">
                                <path d="M448,209.91a210.06,210.06,0,0,1-122.77-39.25V349.38A162.55,162.55,0,1,1,185,188.31V278.2a74.62,74.62,0,1,0,52.23,71.18V0l88,0a121.18,121.18,0,0,0,1.86,22.17h0A122.18,122.18,0,0,0,381,102.39a121.43,121.43,0,0,0,67,20.14Z" />
                            </svg>
                            <span class="pvt-video-placeholder-play">▶ Xem Video</span>
                        </div>
                    </div>
                </div>
                <!-- Thumbnail slide (small) -->
                <div id="pvt-thumb-slide" class="pvt-video-thumb-slide pvt-video-thumb-slide--tiktok">
                    <div class="pvt-video-thumb-inner">
                        <svg class="pvt-video-thumb-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" fill="#000">
                            <path d="M448,209.91a210.06,210.06,0,0,1-122.77-39.25V349.38A162.55,162.55,0,1,1,185,188.31V278.2a74.62,74.62,0,1,0,52.23,71.18V0l88,0a121.18,121.18,0,0,0,1.86,22.17h0A122.18,122.18,0,0,0,381,102.39a121.43,121.43,0,0,0,67,20.14Z" />
                        </svg>
                        <span class="pvt-video-thumb-play">▶</span>
                    </div>
                </div>
            </div>
        <?php
        } elseif ($video_type === 'youtube') {
            $embed_url = self::get_youtube_embed_url($gallery_video_url);
            $thumbnail = self::get_youtube_thumbnail($gallery_video_url);
        ?>
            <!-- PVT: Video slides for WPGS gallery (LAZY LOAD) -->
            <div id="pvt-video-slides" style="display:none !important;">
                <!-- Main slide (large video) - LAZY LOAD -->
                <div id="pvt-main-slide" class="pvt-video-main-slide" data-video-type="youtube" data-embed-url="<?php echo esc_url($embed_url); ?>">
                    <div class="pvt-video-main-wrapper pvt-video-main-wrapper--youtube pvt-video-placeholder" style="background-image: url('<?php echo esc_url($thumbnail); ?>');">
                        <div class="pvt-video-placeholder-content">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" width="60" height="42" fill="#FF0000">
                                <path d="M549.655 124.083c-6.281-23.65-24.787-42.276-48.284-48.597C458.781 64 288 64 288 64S117.22 64 74.629 75.486c-23.497 6.322-42.003 24.947-48.284 48.597-11.412 42.867-11.412 132.305-11.412 132.305s0 89.438 11.412 132.305c6.281 23.65 24.787 41.5 48.284 47.821C117.22 448 288 448 288 448s170.78 0 213.371-11.486c23.497-6.321 42.003-24.171 48.284-47.821 11.412-42.867 11.412-132.305 11.412-132.305s0-89.438-11.412-132.305z" />
                                <path d="M232 337V175l142 81z" fill="#fff" />
                            </svg>
                        </div>
                    </div>
                </div>
                <!-- Thumbnail slide (small) -->
                <div id="pvt-thumb-slide" class="pvt-video-thumb-slide pvt-video-thumb-slide--youtube">
                    <div class="pvt-video-thumb-inner">
                        <img src="<?php echo esc_url($thumbnail); ?>" alt="Video" class="pvt-video-thumb-img">
                        <span class="pvt-video-thumb-play">▶</span>
                    </div>
                </div>
            </div>
        <?php
        }
    }

    /**
     * Output minimal JS to add video slides to WPGS Slick slider
     */
    public function output_wpgs_video_script()
    {
        if (!is_product()) {
            return;
        }

        $product_id = get_queried_object_id();
        $gallery_video_url = get_field('gallery_video_url', $product_id);

        if (empty($gallery_video_url)) {
            return;
        }
        ?>
        <script>
            (function() {
                // Wait for Slick to initialize
                function tryAddVideoSlide() {
                    var mainSlide = document.getElementById('pvt-main-slide');
                    var thumbSlide = document.getElementById('pvt-thumb-slide');

                    if (!mainSlide || !thumbSlide) return;

                    var $wpgsFor = jQuery('.wpgs-for');
                    var $wpgsNav = jQuery('.wpgs-nav');

                    if ($wpgsFor.length && $wpgsNav.length && $wpgsFor.hasClass('slick-initialized')) {
                        // Clone and add to sliders via Slick's slickAdd method
                        var mainClone = mainSlide.cloneNode(true);
                        var thumbClone = thumbSlide.cloneNode(true);

                        mainClone.removeAttribute('id');
                        thumbClone.removeAttribute('id');
                        mainClone.style.display = '';
                        thumbClone.style.display = '';

                        // Add at last position (end of slider)
                        $wpgsFor.slick('slickAdd', mainClone);
                        $wpgsNav.slick('slickAdd', thumbClone);

                        // Add click handler for thumbnail - go to last slide
                        var lastIndex = $wpgsFor.slick('getSlick').slideCount - 1;
                        jQuery(thumbClone).on('click', function(e) {
                            e.preventDefault();
                            $wpgsFor.slick('slickGoTo', lastIndex);
                        });

                        // LAZY LOAD: Click on placeholder to load iframe
                        var placeholder = mainClone.querySelector('.pvt-video-placeholder');
                        if (placeholder) {
                            placeholder.addEventListener('click', function() {
                                loadVideoIframe(mainClone);
                            });
                        }

                        console.log('PVT: Video slides added (lazy load mode)!');
                        return true;
                    }
                    return false;
                }

                // Load iframe when placeholder is clicked
                function loadVideoIframe(slideEl) {
                    var embedUrl = slideEl.getAttribute('data-embed-url');
                    var videoType = slideEl.getAttribute('data-video-type');
                    var wrapper = slideEl.querySelector('.pvt-video-main-wrapper');

                    if (!embedUrl || !wrapper) return;

                    // Replace placeholder with iframe
                    wrapper.classList.remove('pvt-video-placeholder');
                    wrapper.classList.remove('pvt-video-placeholder--tiktok');

                    var iframe = document.createElement('iframe');
                    iframe.src = embedUrl;
                    iframe.allowFullscreen = true;
                    iframe.allow = 'autoplay; encrypted-media';
                    iframe.frameBorder = '0';

                    wrapper.innerHTML = '';
                    wrapper.appendChild(iframe);

                    // Refresh Slick slider to recalculate height
                    var $wpgsFor = jQuery('.wpgs-for');
                    if ($wpgsFor.length && $wpgsFor.hasClass('slick-initialized')) {
                        // Immediate refresh
                        $wpgsFor.slick('setPosition');

                        // Also refresh after iframe loads
                        iframe.onload = function() {
                            $wpgsFor.slick('setPosition');
                        };

                        // Additional delayed refresh for TikTok
                        setTimeout(function() {
                            $wpgsFor.slick('setPosition');
                        }, 500);
                    }
                }

                // Try immediately, then with delays
                if (document.readyState === 'complete') {
                    setTimeout(tryAddVideoSlide, 100);
                } else {
                    window.addEventListener('load', function() {
                        setTimeout(tryAddVideoSlide, 300);
                    });
                }
            })();
        </script>
<?php
    }

    /**
     * Detect video type from URL
     */
    private function detect_video_type($url)
    {
        if (preg_match('/(?:youtube\.com|youtu\.be)/', $url)) {
            return 'youtube';
        }
        if (preg_match('/tiktok\.com/', $url)) {
            return 'tiktok';
        }
        return 'unknown';
    }

    /**
     * Render Video Popup Modal
     */
    public function render_video_popup_modal()
    {
        if (!is_product()) {
            return;
        }

        include PVT_PLUGIN_DIR . 'templates/video-popup-modal.php';
    }

    /**
     * Helper: Get YouTube Video ID
     */
    public static function get_youtube_video_id($url)
    {
        if (preg_match('/(?:youtube\.com\/(?:watch\?(?:.*&)?v=|embed\/|v\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $matches)) {
            return $matches[1];
        }
        return '';
    }

    /**
     * Helper: Get YouTube Embed URL
     */
    public static function get_youtube_embed_url($url)
    {
        $video_id = self::get_youtube_video_id($url);
        if ($video_id) {
            return 'https://www.youtube.com/embed/' . $video_id . '?autoplay=1&rel=0';
        }
        return $url;
    }

    /**
     * Helper: Get YouTube Thumbnail
     */
    public static function get_youtube_thumbnail($url)
    {
        $video_id = self::get_youtube_video_id($url);
        if ($video_id) {
            return 'https://img.youtube.com/vi/' . $video_id . '/hqdefault.jpg';
        }
        return '';
    }

    /**
     * Helper: Get TikTok Video ID
     */
    public static function get_tiktok_video_id($url)
    {
        if (preg_match('/tiktok\.com\/@[^\/]+\/video\/(\d+)/', $url, $matches)) {
            return $matches[1];
        }
        if (preg_match('/\/video\/(\d+)/', $url, $matches)) {
            return $matches[1];
        }
        return '';
    }

    /**
     * Helper: Get TikTok Embed URL
     * Includes loop=1 to auto-replay and hide recommended videos
     */
    public static function get_tiktok_embed_url($url, $autoplay = false)
    {
        $video_id = self::get_tiktok_video_id($url);
        if ($video_id) {
            // loop=1 makes video replay, rel=0 hides recommendations
            $params = '?controls=1&loop=1&rel=0';
            if ($autoplay) {
                $params .= '&autoplay=1';
            }
            return 'https://www.tiktok.com/player/v1/' . $video_id . $params;
        }
        return $url;
    }
}

// Initialize plugin
function pvt_init()
{
    return Product_Video_Tabs::instance();
}
add_action('plugins_loaded', 'pvt_init');
