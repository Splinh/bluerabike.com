<?php

namespace HD\Utilities\Helpers;

use HD\Utilities\Traits\Wp;
use MatthiasMullie\Minify;
use Random\RandomException;

\defined('ABSPATH') || die;

/**
 * Helper Class
 *
 * @author Gaudev
 */
final class Helper
{
    use Wp;

    // -------------------------------------------------------------

    /**
     * @return bool
     */
    public static function development(): bool
    {
        return wp_get_environment_type() === 'development' || (defined('WP_DEBUG') && \WP_DEBUG === true);
    }

	// -------------------------------------------------------------

    /**
     * Global debug-based version string.
     * Returns a timestamp in development or when FORCE_VERSION is enabled.
     * In production it returns false (i.e. no cache buster). Prefer using
     * assetVersion() for CSS/JS files to get file-specific versions.
     *
     * @return string|bool|null
     */
    public static function version(): bool|string|null
    {
        $timestamp = time();

        if (
            wp_get_environment_type() === 'development' ||
            (defined('WP_DEBUG') && \WP_DEBUG === true) ||
            (defined('FORCE_VERSION') && \FORCE_VERSION === true)
        ) {
            return (string) $timestamp;
        }

        // In production, return theme version instead of false to avoid stale caches
        return defined('THEME_VERSION') ? (string) THEME_VERSION : false;
    }

    /**
     * Generate a cache-busting version for a theme asset based on filemtime.
     * Falls back to THEME_VERSION or timestamp in development.
     *
     * @param string|null $relative Relative path under `assets/`, e.g. 'css/index.css'
     * @return string
     */
    public static function assetVersion(?string $relative): string
    {
        // In development, always use timestamp to avoid caching issues
        if (self::development() || (defined('FORCE_VERSION') && \FORCE_VERSION === true)) {
            return (string) time();
        }
        // Resolve to absolute file path inside theme assets
        $relative = ltrim((string) $relative, '/\\');
        $basePath = defined('THEME_PATH') ? THEME_PATH : get_template_directory() . DIRECTORY_SEPARATOR;
        $assetDir = $basePath . 'assets' . DIRECTORY_SEPARATOR;
        $filePath = $assetDir . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative);
        if (is_readable($filePath)) {
            // Per-request cache to avoid repeated hashing
            static $cache = [];
            $mtime = @filemtime($filePath) ?: 0;
            $size  = @filesize($filePath) ?: 0;
            $key   = $filePath . '|' . $mtime . '|' . $size;
            if (isset($cache[$key])) {
                return $cache[$key];
            }
            // Some FTP setups preserve timestamps → mtime may not change
            // Use a content hash to guarantee a new version when file changes
            $hash = @md5_file($filePath);
            if (!$hash) {
                // Very unlikely, but keep a deterministic fallback
                $hash = md5($filePath . '|' . $mtime . '|' . $size);
            }

            // Compact, yet unique: 10 chars of hash + mtime
            $version = substr($hash, 0, 10) . '-' . $mtime;
            $cache[$key] = $version;

            // if (is_readable($filePath)) {
            // 	$mtime = filemtime($filePath);
            // 	if ($mtime) {
            // 		return (string) $mtime;
            // 	}

            // }    
            return $version;
        }

        // Fallback to theme version to ensure a stable cache key

        return defined('THEME_VERSION') ? (string) THEME_VERSION : (string) time();
    }

	// public static function assetVersion(?string $relative): string
	// {
	// 	// Nếu đang ở môi trường dev hoặc có FORCE_VERSION → luôn dùng timestamp để phá cache
	// 	if (self::development() || (defined('FORCE_VERSION') && \FORCE_VERSION === true)) {
	// 		return (string) time();
	// 	}

	// 	// Chuẩn hoá đường dẫn file
	// 	$relative = ltrim((string) $relative, '/\\');
	// 	$basePath = defined('THEME_PATH') ? THEME_PATH : get_template_directory() . DIRECTORY_SEPARATOR;
	// 	$assetDir = $basePath . 'assets' . DIRECTORY_SEPARATOR;

	// 	// === 1️⃣ Nếu có manifest.json (Vite / Mix build) thì đọc hash file ===
	// 	$manifestPath = $assetDir . 'manifest.json';
	// 	if (is_readable($manifestPath)) {
	// 		$manifest = json_decode(file_get_contents($manifestPath), true);
	// 		if (isset($manifest[$relative]['file'])) {
	// 			$hashedFile = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $manifest[$relative]['file']);
	// 			$filePath   = $assetDir . $hashedFile;

	// 			if (is_readable($filePath)) {
	// 				return (string) filemtime($filePath);
	// 			}
	// 		}
	// 	}

	// 	// === 2️⃣ Nếu không có manifest.json thì đọc filemtime trực tiếp ===
	// 	$filePath = $assetDir . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative);
	// 	if (is_readable($filePath)) {
	// 		$mtime = filemtime($filePath);
	// 		if ($mtime) {
	// 			return (string) $mtime;
	// 		}
	// 	}

	// 	// === 3️⃣ Fallback: nếu vẫn không đọc được → trả THEME_VERSION hoặc timestamp ===
	// 	return defined('THEME_VERSION') ? (string) THEME_VERSION : (string) time();
	// }


	// -------------------------------------------------------------

    /**
     * @param int $length
     *
     * @return string
     * @throws RandomException
     */
    public static function makeUsername(int $length = 8): string
    {
        if ($length < 1) {
            return '';
        }

        $letters            = 'abcdefghijklmnopqrstuvwxyz';
        $letters_and_digits = 'abcdefghijklmnopqrstuvwxyz0123456789';

        $username = $letters[random_int(0, strlen($letters) - 1)];

        for ($i = 1; $i < $length; $i++) {
            $username .= $letters_and_digits[random_int(0, strlen($letters_and_digits) - 1)];
        }

        return $username;
    }


	// -------------------------------------------------------------

    /**
     * Generate a unique slug with desired length.
     *
     * @param int $length Total desired slug length
     * @param string $prefix
     *
     * @return string
     * @throws RandomException
     */
    public static function makeUnique(int $length = 32, string $prefix = ''): string
    {
        // microtime
        $time        = microtime(true);
        $timeEncoded = base_convert((string) ($time * 1000000), 10, 36);

        // Process ID
        $pidEncoded = base_convert((string) getmypid(), 10, 36);

        // uniqid
        $uniq        = uniqid('', true);
        $uniqEncoded = base_convert(str_replace('.', '', $uniq), 10, 36);

        // Random supplement
        $base   = $timeEncoded . $pidEncoded . $uniqEncoded;
        $need   = max(0, $length - strlen($base));
        $random = '';
        if ($need > 0) {
            $bytes  = random_bytes((int) ceil($need * 0.75));
            $random = substr(base_convert(bin2hex($bytes), 16, 36), 0, $need);
        }

        return $prefix . substr($base . $random, 0, $length);
    }

	// -------------------------------------------------------------

    /**
     * @param string $content
     *
     * @return string
     */
    public static function extractJS(string $content): string
    {
        $script_pattern = '/<script\b[^>]*>(.*?)<\/script>/is';
        preg_match_all($script_pattern, $content, $matches, PREG_SET_ORDER);

        $valid_scripts = [];

        // Patterns for detecting potentially malicious code
        $malicious_patterns = [
            '/eval\(/i',
            '/document\.write\(/i',
            //'/<script.*?src=[\'"]?data:/i',
            '/base64,/i',
        ];

        foreach ($matches as $match) {
            $scriptTag     = $match[0]; // Full <script> tag
            $scriptContent = trim($match[1] ?? ''); // Script content inside <script>...</script>
            $hasSrc        = preg_match('/\bsrc=["\'][^"\']+["\']/i', $scriptTag);

            $isMalicious = false;
            if (! $hasSrc && $scriptContent !== '') {
                foreach ($malicious_patterns as $pattern) {
                    if (preg_match($pattern, $scriptContent)) {
                        $isMalicious = true;
                        break;
                    }
                }
            }

            // Retain scripts that have valid src or are clean inline scripts
            if (! $isMalicious || $hasSrc) {
                $valid_scripts[] = $scriptTag;
            }
        }

        // Re-construct content with valid <script> tags
        return preg_replace_callback($script_pattern, static function () use (&$valid_scripts) {
            return array_shift($valid_scripts) ?? '';
        }, $content);
    }

	// -------------------------------------------------------------

    /**
     * @param string $css
     *
     * @return string
     */
    public static function extractCss(string $css): string
    {
        if (empty($css)) {
            return '';
        }

        // Convert encoding to UTF-8 if needed
        if (mb_detect_encoding($css, 'UTF-8', true) !== 'UTF-8') {
            $css = mb_convert_encoding($css, 'UTF-8', 'auto');
        }

        // Log if dangerous content is detected
        if (preg_match('/<script\b[^>]*>/i', $css)) {
            self::errorLog('Warning: `<script>` inside CSS');
        }

        //$css = (string) $css;
        $css = preg_replace([
            '/<script\b[^>]*>.*?(?:<\/script>|$)/is',
            '/<style\b[^>]*>(.*?)<\/style>/is',
            '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u',
            '/\bexpression\s*\([^)]*\)/i',
            '/url\s*\(\s*[\'"]?\s*javascript:[^)]*\)/i',
            '/[^\S\r\n\t]+/',
        ], ['', '$1', '', '', '', ' '], $css);

        return trim($css);
    }

	// -------------------------------------------------------------

    /**
     * @param string|null $js
     * @param bool $respectDebug
     *
     * @return string|null
     */
    public static function JSMinify(?string $js, bool $respectDebug = true): ?string
    {
        if ($js === null || $js === '') {
            return null;
        }
        if ($respectDebug && self::development()) {
            return $js;
        }

        return class_exists(Minify\JS::class) ? (new Minify\JS())->add($js)->minify() : $js;
    }

	// -------------------------------------------------------------

    /**
     * @param string|null $css
     * @param bool $respectDebug
     *
     * @return string|null
     */
    public static function CSSMinify(?string $css, bool $respectDebug = true): ?string
    {
        if ($css === null || $css === '') {
            return null;
        }
        if ($respectDebug && self::development()) {
            return $css;
        }

        return class_exists(Minify\CSS::class) ? (new Minify\CSS())->add($css)->minify() : $css;
    }

	// -------------------------------------------------------------

    /**
     * @param $name
     * @param mixed $default
     *
     * @return array|mixed
     */
    public static function filterSettingOptions($name, mixed $default = []): mixed
    {
        $filters = apply_filters('hd_theme_settings_filter', []);

        if (isset($filters[$name])) {
            return $filters[$name] ?: $default;
        }

        return [];
    }

	// -------------------------------------------------------------

    /**
     * @return mixed|string
     */
    public static function currentLanguage(): mixed
    {
        // Polylang
        if (function_exists("pll_current_language")) {
            return \pll_current_language("slug");
        }

        // Weglot
        if (function_exists("weglot_get_current_language")) {
            return \weglot_get_current_language();
        }

        // WMPL
        $currentLanguage = apply_filters('wpml_current_language', null);

        // Try to fall back on the current language
        if (! $currentLanguage) {
            return strtolower(substr(get_bloginfo('language'), 0, 2));
        }

        return $currentLanguage;
    }

	// --------------------------------------------------

    /**
     * @return bool
     */
    public static function lightHouse(): bool
    {
        $ua       = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
        $patterns = [
            'lighthouse',
            'headlesschrome',
            'chrome-lighthouse',
            'pagespeed',
        ];

        foreach ($patterns as $pattern) {
            if (stripos($ua, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

	// --------------------------------------------------

    /**
     * Enqueue section-specific CSS for lazy loading optimization.
     * Each section of flexible content will only load its CSS when used.
     *
     * @param string $section_name Section name (e.g., 'section-banner', 'section-tabs')
     * @param array  $deps         Optional dependencies array
     *
     * @return void
     */
    public static function enqueueSectionStyle(string $section_name, array $deps = ['home-css']): void
    {
        $handle = $section_name . '-css';
        $file = 'css/sections/' . $section_name . '.css';

        // Check if already enqueued to avoid duplicates
        if (!wp_style_is($handle, 'enqueued')) {
            \HD_Asset::enqueueStyle(
                $handle,
                ASSETS_URL . $file,
                $deps,
                self::assetVersion($file)
            );
        }
    }

	

	// --------------------------------------------------

    /**
     * @return void
     */
    public static function clearAllCache(): void
    {
        global $wpdb;

        // Clear all WordPress transients
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", '_transient_%'));
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", '_transient_timeout_%'));

        // Clear object cache (e.g., Redis or Memcached)
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }

        // WP-Rocket cache
        if (self::checkPluginActive('wp-rocket/wp-rocket.php')) {
            $actions = [
                'save_post',            // Save a post
                'deleted_post',         // Delete a post
                'trashed_post',         // Empty Trashed post
                'edit_post',            // Edit a post - includes leaving comments
                'delete_attachment',    // Delete an attachment - includes re-uploading
                'switch_theme',         // Change theme
            ];

            // Add the action for each event
            foreach ($actions as $event) {
                add_action($event, static function () {
                    \function_exists('rocket_clean_domain') && \rocket_clean_domain();
                });
            }
        }

        // Clear FlyingPress cache
        if (self::checkPluginActive('flying-press/flying-press.php')) {
            class_exists(\FlyingPress\Purge::class) && \FlyingPress\Purge::purge_everything();
        }

        // LiteSpeed cache
        if (self::checkPluginActive('litespeed-cache/litespeed-cache.php')) {
            class_exists(\LiteSpeed\Purge::class) && \LiteSpeed\Purge::purge_all();
        }
    }

	// --------------------------------------------------

    /**
     * @param $value
     * @param $min
     * @param $max
     *
     * @return bool
     */
    public static function inRange($value, $min, $max): bool
    {
        $inRange = filter_var($value, FILTER_VALIDATE_INT, [
            'options' => [
                'min_range' => (int) $min,
                'max_range' => (int) $max,
            ],
        ]);

        return false !== $inRange;
    }

	// -------------------------------------------------------------

    /**
     * @param array $array_a
     * @param array $array_b
     *
     * @return bool
     */
    public static function checkValuesNotInRanges(array $array_a, array $array_b): bool
    {
        foreach ($array_a as $range) {

            // Ensure range is valid
            if (count($range) !== 2 || ! is_numeric($range[0]) || ! is_numeric($range[1])) {
                continue;
            }

            $start = min($range);
            $end   = max($range);

            foreach ($array_b as $value) {
                if ($value >= $start && $value < $end) {
                    return false;
                }
            }

            // Additional check for whether array_b contains the entire range of array_a
            if (min($array_b) <= $start && max($array_b) >= $end) {
                return false;
            }
        }

        return true;
    }

	// --------------------------------------------------

    /**
     * A fallback when no navigation is selected by default.
     *
     * @param bool $container
     *
     * @return void
     */
    public static function menuFallback(bool $container = false): void
    {
        echo '<div class="menu-fallback">';
        if ($container) {
            echo '<div class="container">';
        }

        /* translators: %1$s: link to menus, %2$s: link to customize. */
        printf(
            __('Please assign a menu to the primary menu location under %1$s or %2$s the design.', TEXT_DOMAIN),
            /* translators: %s: menu url */
            sprintf(
                __('<a class="_blank" href="%s">Menus</a>', TEXT_DOMAIN),
                get_admin_url(get_current_blog_id(), 'nav-menus.php')
            ),
            /* translators: %s: customize url */
            sprintf(
                __('<a class="_blank" href="%s">Customize</a>', TEXT_DOMAIN),
                get_admin_url(get_current_blog_id(), 'customize.php')
            )
        );

        if ($container) {
            echo '</div>';
        }

        echo '</div>';
    }

	// --------------------------------------------------

    /**
     * @param string $img
     *
     * @return string
     */
    public static function pixelImg(string $img = ''): string
    {
        if (file_exists($img)) {
            return $img;
        }

        return 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
    }

	// --------------------------------------------------

    /**
     * @param bool $img_wrap
     * @param bool $thumb
     *
     * @return string
     */
    public static function placeholderSrc(bool $img_wrap = true, bool $thumb = true): string
    {
        $src = ASSETS_URL . 'img/placeholder.png';
        if ($thumb) {
            $src = ASSETS_URL . 'img/placeholder-320x320.png';
        }
        if ($img_wrap) {
            $src = "<img loading=\"lazy\" src=\"{$src}\" alt=\"place-holder\" class=\"wp-placeholder\">";
        }

        return $src;
    }

	// --------------------------------------------------

    /**
     * @param string $str
     * @param string $attr
     * @param string $content_extra
     * @param bool $unique
     *
     * @return string
     */
    public static function appendToAttribute(string $str, string $attr, string $content_extra, bool $unique = false): string
    {
        // Check if the attribute has single or double quotes.
        if (($start = stripos($str, $attr . '="')) !== false) {
            $quote = '"';
        } elseif (($start = stripos($str, $attr . "='")) !== false) {
            $quote = "'";
        } else {
            // Not found
            return $str;
        }

        // Add a quote (for filtering purposes).
        $attr          .= '=' . $quote;
        $content_extra = trim($content_extra);

        if ($unique) {
            $start += strlen($attr);
            $end   = strpos($str, $quote, $start);

            // Get the current content.
            $content = explode(' ', substr($str, $start, $end - $start));

            // Append extra content uniquely.
            foreach (explode(' ', $content_extra) as $class) {
                if (! empty($class) && ! in_array($class, $content, false)) {
                    $content[] = $class;
                }
            }

            // Remove duplicates and empty values.
            $content        = array_unique(array_filter($content));
            $content        = implode(' ', $content);
            $before_content = substr($str, 0, $start);
            $after_content  = substr($str, $end);
            $str            = $before_content . $content . $after_content;
        } else {
            $str = preg_replace(
                '/' . preg_quote($attr, '/') . '/',
                $attr . $content_extra . ' ',
                $str,
                1
            );
        }

        return $str;
    }

	// --------------------------------------------------

    /**
     * @param $url
     * @param int $resolution_key
     *
     * @return string
     */
    public static function youtubeImage($url, int $resolution_key = 0): string
    {
        if (! $url) {
            return '';
        }

        $resolution = [
            'sddefault',
            'hqdefault',
            'mqdefault',
            'default',
            'maxresdefault',
        ];

        $url_img      = self::pixelImg();
        $query_string = wp_parse_url($url, PHP_URL_QUERY);

        if ($query_string) {
            parse_str($query_string, $vars);
            if (isset($vars['v'])) {
                $id      = $vars['v'];
                $res_key = $resolution[$resolution_key] ?? $resolution[0];
                $url_img = 'https://img.youtube.com/vi/' . $id . '/' . $res_key . '.jpg';
            }
        }

        return $url_img;
    }

	// --------------------------------------------------

    /**
     * @param $url
     * @param int $autoplay
     * @param bool $lazyload
     * @param bool $control
     *
     * @return string|null
     */
    public static function youtubeIframe($url, int $autoplay = 0, bool $lazyload = true, bool $control = true): ?string
    {
        if (! $url) {
            return null;
        }

        $videoId = null;

        // Parse URL and extract query string
        $query_string = wp_parse_url($url, PHP_URL_QUERY);
        if ($query_string) {
            parse_str($query_string, $vars);
            if (isset($vars['v'])) {
                $videoId = esc_attr($vars['v']);
            }
        }

        // Fallback: extract from youtu.be/VIDEO_ID or embed/VIDEO_ID
        if (! $videoId) {
            $path = wp_parse_url($url, PHP_URL_PATH);
            if ($path) {
                $segments = explode('/', trim($path, '/'));
                if (isset($segments[0])) {
                    $videoId = esc_attr(end($segments));
                }
            }
        }

        if (! $videoId) {
            return null;
        }

        $home            = esc_url(trailingslashit(network_home_url()));
        $iframeSize      = ' width="800" height="450"';
        $allowAttributes = 'accelerometer; encrypted-media; gyroscope; picture-in-picture';
        $src             = "https://www.youtube.com/embed/{$videoId}?wmode=transparent&origin={$home}";

        if ($autoplay) {
            $allowAttributes .= '; autoplay';
            $src             .= '&autoplay=1';
        }

        if (! $control) {
            $src .= '&modestbranding=1&controls=0&rel=0&version=3&loop=1&enablejsapi=1&iv_load_policy=3&playlist=' . $videoId;
        }

        $src               .= '&html5=1';
        $lazyLoadAttribute = $lazyload ? ' loading="lazy"' : '';

        return sprintf(
            '<iframe id="ytb_iframe_%1$s" title="YouTube Video Player"%2$s allow="%3$s"%4$s src="%5$s" style="border:0"></iframe>',
            $videoId,
            $iframeSize,
            $allowAttributes,
            $lazyLoadAttribute,
            esc_url($src)
        );
    }

	// --------------------------------------------------

    /**
     * @param string $email
     * @param string $title
     * @param array|string $attributes
     *
     * @return string|null
     */
    public static function safeMailTo(string $email, string $title = '', array|string $attributes = ''): ?string
    {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        $title        = $title ?: $email;
        $encodedEmail = '';

        // Convert email characters to HTML entities to obfuscate
        for ($i = 0, $len = strlen($email); $i < $len; $i++) {
            $encodedEmail .= '&#' . ord($email[$i]) . ';';
        }

        $encodedTitle = '';
        for ($i = 0, $len = strlen($title); $i < $len; $i++) {
            $encodedTitle .= '&#' . ord($title[$i]) . ';';
        }

        // Handle attributes
        $attrString = '';
        if (is_array($attributes)) {
            foreach ($attributes as $key => $val) {
                $attrString .= ' ' . htmlspecialchars($key, ENT_QUOTES | ENT_HTML5) . '="' . htmlspecialchars($val, ENT_QUOTES | ENT_HTML5) . '"';
            }
        } elseif (is_string($attributes)) {
            $attrString = ' ' . $attributes;
        }

        // Return obfuscated email using HTML entities only
        return '<a href="mailto:' . $encodedEmail . '"' . $attrString . '>' . $encodedTitle . '</a>';
    }
}
