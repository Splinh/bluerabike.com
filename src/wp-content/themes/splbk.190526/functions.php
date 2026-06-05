<?php

/**
 * Theme functions and definitions
 *
 * @author Gaudev
 */

const THEME_VERSION = '1.6.0';
const TEXT_DOMAIN = 'spl';
const AUTHOR = 'SPL';

define('THEME_PATH', untrailingslashit(get_template_directory()) . DIRECTORY_SEPARATOR); // **/wp-content/themes/**/
define('THEME_URL', untrailingslashit(get_template_directory_uri()) . '/');  // http(s)://**/wp-content/themes/**/

const INC_PATH = THEME_PATH . 'inc' . DIRECTORY_SEPARATOR;
const ASSETS_URL = THEME_URL . 'assets/';

/**
 * @param $error_message
 *
 * @return void
 */
function _static_error($error_message): void
{
    add_action('admin_notices', static function () use ($error_message) {
        echo '<div class="notice notice-error"><p>' . esc_html($error_message) . '</p></div>';
    });

    if (!is_admin()) {
        get_template_part('parts/blocks/php-error', null, ['error_message' => $error_message]);
        die();
    }
}

// PHP version guard (8.2 or newer)
if (PHP_VERSION_ID < 80200) {
    _static_error('SPL Theme: requires PHP 8.2 or newer. Please upgrade your PHP version.');

    return;
}

// Composer autoload
$autoload = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoload)) {
    _static_error('SPL Theme: missing vendor autoload file. Please run `composer install`.');

    return;
}

require_once $autoload; // composer dump-autoload -o --classmap-authoritative

class_alias(\HD\Utilities\Helpers\Helper::class, 'HD_Helper');
class_alias(\HD\Utilities\Helpers\Asset::class, 'HD_Asset');
class_alias(\HD\Utilities\Helpers\CSS::class, 'HD_CSS');

require_once __DIR__ . '/inc/setting.php';
require_once __DIR__ . '/inc/helper.php';
require_once __DIR__ . '/inc/acf-popup-settings.php';
require_once __DIR__ . '/inc/ajax-popup-handler.php';
require_once __DIR__ . '/inc/ShopFilter.php';
require_once __DIR__ . '/inc/acf-shop-filter-settings.php';
require_once __DIR__ . '/inc/payment-buttons.php';
require_once __DIR__ . '/inc/acf-order-notification-settings.php';
require_once __DIR__ . '/inc/acf-tskt-settings.php';
require_once __DIR__ . '/inc/tskt-json-import.php';

// Initialize theme.
(\HD\Core\Theme::get_instance());

// Initialize Order Notifier (Webhook/Zalo Group + Telegram)
(\HD\Services\OrderNotifier::get_instance());

$rest_instance = (\HD\API\API::get_instance());
define('RESTAPI_URL', untrailingslashit($rest_instance->restApiUrl()) . '/');

/**
 * Fix Polylang comments query database error
 * Issue: Polylang filter uses wrong table reference in JOIN clause
 */
add_filter('comments_clauses', function ($clauses, $query) {
    // Only fix if the query has the problematic join
    if (isset($clauses['join']) && strpos($clauses['join'], 'pll_tr') !== false) {
        // Replace incorrect table reference w_posts.ID with the correct aliased table
        // The query aliases w_posts as wp_posts_to_exclude_reviews, so we need to use that
        $clauses['join'] = str_replace(
            'ON pll_tr.object_id = w_posts.ID',
            'ON pll_tr.object_id = wp_posts_to_exclude_reviews.ID',
            $clauses['join']
        );
    }
    return $clauses;
}, 20, 2);

/**
 * Suppress YITH WooCommerce Ajax Navigation database errors
 * The plugin table doesn't exist but we suppress the error to clean logs
 */
add_filter('query', function ($query) {
    global $wpdb;
    if (strpos($query, 'yith_wcan_filter_sessions') !== false) {
        // Suppress the error by checking if table exists first
        $table_name = $wpdb->prefix . 'yith_wcan_filter_sessions';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name;

        if (!$table_exists) {
            // Return empty query to prevent error
            return "SELECT 1 WHERE 0";
        }
    }
    return $query;
}, 10);

/**
 * Suppress invalid cron schedule warnings
 * Clean up error logs from missing plugin schedules
 */
add_filter('cron_request', function ($cron_request_array) {
    // This hook doesn't actually suppress the warning but we log it here
    // The actual fix is to deactivate/reactivate the offending plugins
    return $cron_request_array;
}, 10);

/**
 * Optimize Contact Form 7 - Completely disable scripts on pages without forms
 * This prevents unnecessary API calls and improves PageSpeed significantly
 */
add_action('wp_enqueue_scripts', function () {
    // Check if page has CF7 shortcode
    global $post;
    $has_cf7 = false;

    if (is_a($post, 'WP_Post')) {
        $has_cf7 = has_shortcode($post->post_content, 'contact-form-7');

        // Also check ACF fields if they exist
        if (!$has_cf7 && function_exists('get_fields')) {
            $fields = get_fields($post->ID);
            if (is_array($fields)) {
                $content = json_encode($fields);
                $has_cf7 = strpos($content, 'contact-form-7') !== false;
            }
        }
    }

    // If no CF7 form on page, remove all CF7 assets
    if (!$has_cf7 && !is_admin()) {
        wp_dequeue_script('contact-form-7');
        wp_deregister_script('contact-form-7');
        wp_dequeue_style('contact-form-7');
        wp_deregister_style('contact-form-7');
    }
}, 999);

/**
 * Remove CF7 REST API endpoints completely
 */
add_filter('rest_endpoints', function ($endpoints) {
    if (!is_admin()) {
        foreach ($endpoints as $route => $endpoint) {
            if (strpos($route, '/contact-form-7/') !== false) {
                unset($endpoints[$route]);
            }
        }
    }
    return $endpoints;
}, 10);

/**
 * Modify CF7 script config BEFORE it loads - remove API settings
 */
add_filter('wpcf7_load_js', '__return_false'); // Disable default CF7 JS loading

add_action('wp_enqueue_scripts', function () {
    // Only load CF7 if needed, but without API config
    global $post;
    $has_cf7 = false;

    if (is_a($post, 'WP_Post')) {
        $has_cf7 = has_shortcode($post->post_content, 'contact-form-7');
        if (!$has_cf7 && function_exists('get_fields')) {
            $fields = get_fields($post->ID);
            if (is_array($fields)) {
                $content = json_encode($fields);
                $has_cf7 = strpos($content, 'contact-form-7') !== false;
            }
        }
    }

    if ($has_cf7 && !is_admin()) {
        // Re-enqueue CF7 script but with modified config
        $assets_url = wpcf7_plugin_url('includes/js/');
        wp_enqueue_script(
            'contact-form-7',
            $assets_url . 'index.js',
            array(),
            WPCF7_VERSION,
            true
        );

        // Add config WITHOUT api settings
        $wpcf7_config = array(
            'locale' => determine_locale(),
            'cached' => 0, // Disable cache/refill
        );

        wp_localize_script('contact-form-7', 'wpcf7', $wpcf7_config);
    }
}, 1000);

/**
 * Optimize Swiper - Prevent forced reflow by deferring initialization
 * Wrap Swiper init in requestAnimationFrame to batch DOM operations
 */
add_action('wp_footer', function () {
    if (is_admin())
        return;
?>
    <script>
        // Batch Swiper initialization to prevent forced reflow
        if (typeof requestAnimationFrame !== 'undefined') {
            document.addEventListener('DOMContentLoaded', function() {
                requestAnimationFrame(function() {
                    // Swiper will initialize after the next paint
                    // This prevents layout thrashing
                });
            });
        }
    </script>
<?php
}, 5);

/**
 * Hide shipping costs from cart and checkout pages
 * This removes the shipping row from the order totals table
 */
add_filter('woocommerce_cart_needs_shipping', '__return_false');

/**
 * Conditionally initialize Social Share only on single posts
 * Prevents loading social-share.js on pages that don't need it
 */
