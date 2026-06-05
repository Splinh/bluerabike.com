<?php

namespace HD\Utilities\Traits;

use HD\Utilities\Navigation\HorizontalNavWalker;
use HD\Utilities\Navigation\VerticalNavWalker;

\defined('ABSPATH') || die;

trait Wp
{
    use Cast;
    use DateTime;
    use File;
    use Str;
    use Url;
    use Db;
    use Encryption;

    private static int $post_limit = -1;
    private static array $target_post_types = [];

	// -------------------------------------------------------------

    /**
     * @param $slug
     * @param array $args
     * @param bool $use_cache
     * @param int $cache_in_hours
     *
     * @return void
     */
    public static function blockTemplate($slug, array $args = [], bool $use_cache = false, int $cache_in_hours = 12): void
    {
        $block_slug = basename($slug, '.php');
        $hook_name = 'enqueue_assets_blocks_' . str_replace('-', '_', $block_slug);
        do_action($hook_name);

        if (!$use_cache) {
            ob_start();
            get_template_part($slug, null, $args);
            $output = ob_get_clean();
            echo $output;

            return;
        }

        $cache_key = 'hd_block_cache_' . md5($slug . serialize($args));
        $cached_output = get_transient($cache_key);
        if (!empty($cached_output)) {
            if (mb_strlen($cached_output, 'UTF-8') <= 10240) { // 10kb
                echo $cached_output;

                return;
            }
            delete_transient($cache_key);
        }

        // buffer
        ob_start();
        get_template_part($slug, null, $args);
        $output = ob_get_clean();

        if (!empty($output) && mb_strlen($output, 'UTF-8') <= 10240) {
            set_transient($cache_key, $output, $cache_in_hours * HOUR_IN_SECONDS);
        }

        echo $output;
    }


    public static function pll_text(string $text_vi, string $text_en): string
    {
        $lang = function_exists('pll_current_language') ? pll_current_language() : 'vi';
        return ($lang === 'en') ? $text_en : $text_vi;
    }


	// -------------------------------------------------------------

    /**
     * @param mixed $action
     * @param string $name
     * @param bool $referer
     * @param bool $display
     *
     * @return string|void
     */
    public static function CSRFToken(string|int $action = -1, string $name = '_csrf_token', bool $referer = false, bool $display = false)
    {
        $name = esc_attr($name);
        $token = wp_create_nonce($action);
        $nonce_field = '<input type="hidden" id="' . self::random(10) . '" name="' . $name . '" value="' . esc_attr($token) . '" />';

        if ($referer) {
            $nonce_field .= wp_referer_field(false);
        }

        if ($display) {
            echo $nonce_field;
        } else {
            return $nonce_field;
        }
    }

	// -------------------------------------------------------------

    /**
     * @param ?string $path
     * @param bool $require_path
     * @param bool $init_class
     * @param string $FQN
     * @param bool $is_widget
     *
     * @return void
     */
    public static function FQNLoad(?string $path, bool $require_path = false, bool $init_class = false, string $FQN = '\\', bool $is_widget = false): void
    {
        // Validate $path
        if (empty($path) || !is_dir($path)) {
            self::errorLog("Invalid or inaccessible path: $path");

            return;
        }

        // Retrieve PHP files in the directory
        $files = glob($path . '/*.php', GLOB_NOSORT);

        // Check if glob() failed
        if ($files === false) {
            self::errorLog("Failed to read files in directory: $path");

            return;
        }

        foreach ($files as $file_path) {
            $filename = basename($file_path, '.php');
            $filenameFQN = rtrim($FQN, '\\') . '\\' . $filename;

            // Skip unreadable files
            if (!is_readable($file_path)) {
                self::errorLog("Unreadable file skipped: $file_path");
                continue;
            }

            // Check if the file is a malicious PHP script
            if (self::_isMaliciousFile($file_path)) {
                self::errorLog("Skipped potentially malicious file: $file_path");
                continue;
            }

            // Include the file if $require_path is true
            if ($require_path) {
                try {
                    require_once $file_path;
                } catch (\Exception $e) {
                    self::errorLog("Error including file $file_path: " . $e->getMessage());
                    continue;
                }
            }

            // Initialize the class or register as widget if `$init_class` is true
            if ($init_class && class_exists($filenameFQN)) {
                try {
                    if ($is_widget) {
                        register_widget(new $filenameFQN());
                    } else {
                        new $filenameFQN();
                    }
                } catch (\Exception $e) {
                    // Log any error that occurs during class initialization
                    self::errorLog("Error initializing class $filenameFQN: " . $e->getMessage());
                }
            }
        }
    }

	// -------------------------------------------------------------

    /**
     * @param string $file_path
     *
     * @return bool
     */
    private static function _isMaliciousFile(string $file_path): bool
    {
        $handle = fopen($file_path, 'rb');
        if (!$handle) {
            return false;
        }

        $chunk_size = 10240; // 10kb
        $content = fread($handle, $chunk_size);
        fclose($handle);

        if (!$content) {
            return false;
        }

        $pattern = '/
	        (b\s*a\s*s\s*e\s*6\s*4\s*_decode|
	        e\s*v\s*a\s*l|
	        g\s*z\s*i\s*nflate|
	        s\s*t\s*r\s*_rot13|
	        h\s*e\s*x\s*2\s*b\s*i\s*n)
	        \s*\( |
	        \$\{?"?(eval|base64_decode|gzinflate|str_rot13|hex2bin)"?\}?\s*\( |
	        \$\w+\s*\(
	    /ix';

        return preg_match($pattern, $content) === 1;
    }

	// -------------------------------------------------------------

    /**
     * @param mixed $link
     * @param string|null $class
     * @param string|null $label
     * @param string|null $extra_title
     *
     * @return string
     */
    public static function ACFLink(mixed $link, ?string $class = '', ?string $label = '', ?string $extra_title = ''): string
    {
        // string
        if (!empty($link) && is_string($link)) {
            $link_return = sprintf(
                '<a class="%3$s" href="%1$s" title="%2$s">',
                esc_url(trim($link)),
                self::escAttr($label),
                self::escAttr($class)
            );

            $link_return .= $label . $extra_title;
            $link_return .= '</a>';

            return $link_return;
        }

        // array
        if (!empty($link) && is_array($link)) {
            $_link_title = $link['title'] ?? '';
            $_link_url = $link['url'] ?? '';
            $_link_target = $link['target'] ?? '';

            // force label
            if (!empty($label)) {
                $_link_title = $label;
            }

            if (!empty($_link_url)) {
                $link_return = sprintf(
                    '<a class="%3$s" href="%1$s" title="%2$s"',
                    esc_url($_link_url),
                    self::escAttr($_link_title),
                    self::escAttr($class)
                );

                if (!empty($_link_target)) {
                    $link_return .= ' target="_blank" rel="noopener noreferrer nofollow"';
                }

                $link_return .= '>';
                $link_return .= $_link_title . $extra_title;
                $link_return .= '</a>';

                return $link_return;
            }
        }

        return '';
    }

	// -------------------------------------------------------------

    /**
     * @param mixed $link
     * @param string|null $class
     * @param string|null $label
     * @param string|null $extra_title
     *
     * @return string|null
     */
    public static function ACFLinkLabel(mixed $link, ?string $class = '', ?string $label = '', ?string $extra_title = ''): ?string
    {
        if (!empty($link) && is_string($link)) {
            return $label;
        }

        if (!empty($link) && is_array($link)) {
            $_link_title = $link['title'] ?? '';

            if (!empty($label)) {
                $_link_title = $label;
            }

            return !empty($class) ?
                '<span class="' . $class . '">' . $_link_title . $extra_title . '</span>' :
                $_link_title . $extra_title;
        }

        return '';
    }

	// -------------------------------------------------------------

    /**
     * @param mixed $link
     * @param string|bool $empty_link_default_tag
     *
     * @return string
     */
    public static function ACFLinkClose(mixed $link, string|bool $empty_link_default_tag = 'span'): string
    {
        if (!empty($link)) {
            return '</a>';
        }

        return !empty($empty_link_default_tag) ? '</' . $empty_link_default_tag . '>' : '';
    }

	// -------------------------------------------------------------

    /**
     * @param mixed $link
     * @param string|null $class
     * @param string|null $label
     * @param string|bool $empty_link_default_tag
     *
     * @return string
     */
    public static function ACFLinkOpen(mixed $link, ?string $class = '', ?string $label = '', string|bool $empty_link_default_tag = 'span'): string
    {
        // string
        if (!empty($link) && is_string($link)) {
            return sprintf(
                '<a class="%3$s" href="%1$s" title="%2$s">',
                esc_url(trim($link)),
                self::escAttr($label),
                self::escAttr($class)
            );
        }

        // array
        if (!empty($link) && is_array($link)) {
            $_link_title = $link['title'] ?? '';
            $_link_url = $link['url'] ?? '';
            $_link_target = $link['target'] ?? '';

            if (!empty($label)) {
                $_link_title = $label;
            }

            if (!empty($_link_url)) {
                $link_return = sprintf(
                    '<a class="%3$s" href="%1$s" title="%2$s"',
                    esc_url($_link_url),
                    self::escAttr($_link_title),
                    self::escAttr($class)
                );

                if (!empty($_link_target)) {
                    $link_return .= ' target="_blank" rel="noopener noreferrer nofollow"';
                }
                $link_return .= '>';

                return $link_return;
            }
        }

        if ($empty_link_default_tag) {
            return '<' . $empty_link_default_tag . ' class="' . self::escAttr($class) . '">';
        }

        return '';
    }

	// -------------------------------------------------------------

    /**
     * @param string|null $content
     * @param mixed $link
     * @param string|null $class
     * @param string|null $label
     * @param string|bool $empty_link_default_tag
     *
     * @return string
     */
    public static function ACFLinkWrap(?string $content, mixed $link, ?string $class = '', ?string $label = '', string|bool $empty_link_default_tag = 'span'): string
    {
        // string
        if (is_string($link) && !empty($link)) {
            $link_return = sprintf(
                '<a class="%3$s" href="%1$s" title="%2$s">',
                esc_url(trim($link)),
                self::escAttr($label),
                self::escAttr($class)
            );
            $link_return .= $content . '</a>';

            return $link_return;
        }

        // array
        $link = (array) $link;
        if (!empty($link)) {
            $_link_title = $link['title'] ?? '';
            $_link_url = $link['url'] ?? '';
            $_link_target = $link['target'] ?? '';

            if (!empty($label)) {
                $_link_title = $label;
            }

            if (!empty($_link_url)) {
                $link_return = sprintf(
                    '<a class="%3$s" href="%1$s" title="%2$s"',
                    esc_url($_link_url),
                    self::escAttr($_link_title),
                    self::escAttr($class)
                );

                if (!empty($_link_target)) {
                    $link_return .= ' target="_blank" rel="noopener noreferrer nofollow"';
                }

                $link_return .= '>';
                $link_return .= $content;
                $link_return .= '</a>';

                return $link_return;
            }
        }

        // empty link
        $link_return = $content;
        if ($empty_link_default_tag) {
            $link_return = '<' . $empty_link_default_tag . ' class="' . self::escAttr($class) . '">' . $content . '</' . $empty_link_default_tag . '>';
        }

        return $link_return;
    }

	// -------------------------------------------------------------

    /**
     * @param array $args
     *
     * @return bool|string|void
     */
    public static function verticalNav(array $args = [])
    {
        $args = wp_parse_args(
            $args,
            [
                'container' => false, // Remove nav container
                'menu_id' => '',
                'menu_class' => 'menu vertical',
                'theme_location' => '',
                'depth' => 4,
                'fallback_cb' => false,
                'walker' => new VerticalNavWalker(),
                'items_wrap' => '<ul id="%1$s" class="%2$s" data-accordion-menu data-submenu-toggle="true">%3$s</ul>',
                'echo' => false,
            ]
        );

        if (true === $args['echo']) {
            echo wp_nav_menu($args);
        } else {
            return wp_nav_menu($args);
        }
    }

	// -------------------------------------------------------------

    /**
     * @link http://codex.wordpress.org/Function_Reference/wp_nav_menu
     *
     * @param array $args
     *
     * @return bool|string|void
     */
    public static function horizontalNav(array $args = [])
    {
        $args = wp_parse_args(
            $args,
            [
                'container' => false,
                'menu_id' => '',
                'menu_class' => 'dropdown menu horizontal',
                'theme_location' => '',
                'depth' => 4,
                'fallback_cb' => false,
                'walker' => new HorizontalNavWalker(),
                'items_wrap' => '<ul id="%1$s" class="%2$s" data-dropdown-menu>%3$s</ul>',
                'echo' => false,
            ]
        );

        if (true === $args['echo']) {
            echo wp_nav_menu($args);
        } else {
            return wp_nav_menu($args);
        }
    }

	// -------------------------------------------------------------

    /**
     * @param string $tag
     * @param array $atts
     * @param string|null $content
     *
     * @return mixed
     */
    public static function doShortcode(string $tag, array $atts = [], ?string $content = null): mixed
    {
        global $shortcode_tags;

        // Check if the shortcode exists
        if (!isset($shortcode_tags[$tag])) {
            return false;
        }

        // Call the shortcode function and return its output
        try {
            return \call_user_func($shortcode_tags[$tag], $atts, $content, $tag);
        } catch (\Throwable $e) {
            self::errorLog('[Shortcode error] ' . $e->getMessage());

            return false;
        }
    }

	// -------------------------------------------------------------

    /**
     * Retrieves attachment details by its ID.
     *
     * @param mixed $attachment_id
     * @param bool $return_object Optional. Whether to return the result as an object. Default true.
     *
     * @return object|array|null Attachment details as an object or array, or null if not found.
     */
    public static function getAttachment(mixed $attachment_id, bool $return_object = true): object|array|null
    {
        $attachment = get_post($attachment_id);

        // Check if the attachment exists
        if (!$attachment) {
            return null;
        }

        // Prepare the attachment details
        $attachment_details = [
            'alt' => get_post_meta($attachment->ID, '_wp_attachment_image_alt', true),
            'caption' => $attachment->post_excerpt,
            'description' => $attachment->post_content,
            'href' => get_permalink($attachment->ID),
            'src' => $attachment->guid,
            'title' => $attachment->post_title,
        ];

        // Convert to object if specified
        if ($return_object) {
            return self::toObject($attachment_details);
        }

        // Return attachment details as an array
        return $attachment_details;
    }

	// -------------------------------------------------------------

    /**
     * @param array|null $arr_parsed
     *
     * @return bool
     */
    public static function hasDelayScriptTag(?array $arr_parsed): bool
    {
        if (is_null($arr_parsed)) {
            return false;
        }

        foreach ($arr_parsed as $str => $value) {
            if ('delay' === $value) {
                return true;
            }
        }

        return false;
    }

	// -------------------------------------------------------------

    /**
     * @param array|null $arr_parsed
     * @param string $tag
     * @param string $handle
     *
     * @return string
     */
    public static function lazyScriptTag(?array $arr_parsed, string $tag, string $handle): string
    {
        if (is_null($arr_parsed)) {
            return $tag;
        }

        foreach ($arr_parsed as $str => $value) {
            if (str_contains($handle, $str)) {
                if ('defer' === $value) {
                    return preg_replace(
                        ['/\s+defer\s+/', '/\s+src=/'],
                        [' ', ' defer src='],
                        $tag
                    );
                }

                if ('delay' === $value && !self::isAdmin()) {
                    return preg_replace(
                        ['/\s+defer\s+/', '/\s+src=/'],
                        [' ', ' defer data-type="lazy" data-src='],
                        $tag
                    );
                }
            }
        }

        return $tag;
    }

	// -------------------------------------------------------------

    /**
     * @param array|null $arr_styles
     * @param string $html
     * @param string $handle
     *
     * @return string
     */
    public static function lazyStyleTag(?array $arr_styles, string $html, string $handle): string
    {
        if (is_null($arr_styles)) {
            return $html;
        }

        foreach ($arr_styles as $style) {
            if (str_contains($handle, $style)) {
                return preg_replace(
                    ['/media=\'all\'/', '/media="all"/'],
                    'media=\'print\' onload="this.media=\'all\'"',
                    $html
                );
            }
        }

        return $html;
    }

	// -------------------------------------------------------------

    /**
     * @param string $option
     * @param mixed $default
     * @param int $cache_in_hours
     *
     * @return mixed
     */
    public static function getOption(string $option, mixed $default = false, int $cache_in_hours = 12): mixed
    {
        $option = strtolower(trim($option));
        if (empty($option)) {
            return $default;
        }

        $site_id = is_multisite() ? get_current_blog_id() : null;
        $cache_key = $site_id ? "hd_site_option_{$site_id}_{$option}" : "hd_option_{$option}";

        $cached_value = get_transient($cache_key);
        if (!empty($cached_value)) {
            return $cached_value;
        }

        $option_value = is_multisite() ? get_site_option($option, $default) : get_option($option, $default);
        set_transient($cache_key, $option_value, $cache_in_hours * HOUR_IN_SECONDS);

        // Retrieve the option value
        return $option_value;
    }

	// -------------------------------------------------------------

    /**
     * @param string $option
     * @param mixed $new_value
     * @param int $cache_in_hours
     * @param bool|null $autoload
     *
     * @return bool
     */
    public static function updateOption(string $option, mixed $new_value, int $cache_in_hours = 12, ?bool $autoload = null): bool
    {
        $option = strtolower(trim($option));
        if (empty($option)) {
            return false;
        }

        $site_id = is_multisite() ? get_current_blog_id() : null;
        $cache_key = $site_id ? "hd_site_option_{$site_id}_{$option}" : "hd_option_{$option}";

        // Update the option in the appropriate context (multisite or not)
        $updated = is_multisite() ? update_site_option($option, $new_value) : update_option($option, $new_value, $autoload);

        if ($updated) {
            set_transient($cache_key, $new_value, $cache_in_hours * HOUR_IN_SECONDS);
        }

        return $updated;
    }

	// --------------------------------------------------

    /**
     * @param string $option
     *
     * @return bool
     */
    public static function removeOption(string $option): bool
    {
        $option = strtolower(trim($option));
        if (empty($option)) {
            return false;
        }

        $site_id = is_multisite() ? get_current_blog_id() : null;
        $cache_key = $site_id ? "hd_site_option_{$site_id}_{$option}" : "hd_option_{$option}";

        // Remove the option from the appropriate context (multisite or not)
        $removed = is_multisite() ? delete_site_option($option) : delete_option($option);
        if ($removed) {
            delete_transient($cache_key);
        }

        return $removed;
    }

	// -------------------------------------------------------------

    /**
     * @param string|null $mod_name
     * @param mixed $default
     * @param int $cache_in_hours
     *
     * @return mixed
     */
    public static function getThemeMod(?string $mod_name, mixed $default = false, int $cache_in_hours = 12): mixed
    {
        if (empty($mod_name)) {
            return $default;
        }

        $mod_name_lower = strtolower($mod_name);
        $cache_key = "hd_theme_mod_{$mod_name_lower}";

        $cached_value = get_transient($cache_key);
        if (!empty($cached_value)) {
            return $cached_value;
        }

        $_mod = get_theme_mod($mod_name, $default);
        if (!is_string($_mod) && !is_array($_mod)) {
            return $_mod; // Return early if not a string or array
        }

        $mod_value = is_ssl() ? str_replace('http://', 'https://', $_mod) : $_mod;
        set_transient($cache_key, $mod_value, $cache_in_hours * HOUR_IN_SECONDS);

        return $mod_value;
    }

	// -------------------------------------------------------------

    /**
     * @param string $mod_name
     * @param mixed $value
     * @param int $cache_in_hours
     *
     * @return bool
     */
    public static function setThemeMod(string $mod_name, mixed $value, int $cache_in_hours = 12): bool
    {
        if (empty($mod_name)) {
            return false;
        }

        $mod_name_lower = strtolower($mod_name);
        $cache_key = "hd_theme_mod_{$mod_name_lower}";

        set_theme_mod($mod_name, $value);
        set_transient($cache_key, $value, $cache_in_hours * HOUR_IN_SECONDS);

        return true;
    }

	// -------------------------------------------------------------

    /**
     * @param mixed $term_id
     * @param string $taxonomy
     * @param string $output
     *
     * @return array|false|\WP_Error|\WP_Term|null
     */
    public static function getTerm(mixed $term_id, string $taxonomy = 'category', string $output = OBJECT): \WP_Term|\WP_Error|false|array|null
    {
        // Check if the term ID is numeric and retrieve the term by ID
        if (is_numeric($term_id)) {
            $term = get_term((int) $term_id, $taxonomy, $output);
        } else {
            // If term_id is not numeric, attempt to retrieve the term by slug or name
            $term = get_term_by('slug', $term_id, $taxonomy, $output) ?: get_term_by('name', $term_id, $taxonomy, $output);
        }

        return $term;
    }

	// -------------------------------------------------------------

    /**
     * Set custom posts per page limit for specific post-types
     *
     * @param int $post_limit Number of posts to display per page
     * @param array $target_post_types Post types to apply the limit
     *
     * @return void
     */
    public static function setPostsPerPage(int $post_limit = -1, array $target_post_types = []): void
    {
        if (self::isAdmin() || wp_doing_ajax()) {
            return;
        }

        // Get default posts per page from WordPress settings
        $limit_default = (int) self::getOption('posts_per_page');

        // Only proceed if the new limit is greater than default
        if ($post_limit <= $limit_default) {
            return;
        }

        // Store limit and target post-types
        self::$post_limit = $post_limit;
        self::$target_post_types = $target_post_types;

        // Add a hook with high priority to modify a query
        add_action('pre_get_posts', [__CLASS__, 'modifyPostsPerPage'], 9999);
    }

	// -------------------------------------------------------------

    /**
     * Modify posts per page for custom queries
     *
     * @param \WP_Query $query Current WordPress query
     *
     * @return void
     */
    public static function modifyPostsPerPage(\WP_Query $query): void
    {
        if (self::isAdmin() || $query->is_main_query()) {
            return;
        }

        // Check if specific post-types are targeted
        if (!empty(self::$target_post_types)) {
            $query_post_type = (array) $query->get('post_type');

            // Skip if no matching post-types
            if (empty(array_intersect($query_post_type, self::$target_post_types))) {
                return;
            }
        }

        // Set custom posts per page limit
        $query->set('posts_per_page', self::$post_limit);

        // Remove the hook immediately after execution to prevent affecting other queries
        remove_action('pre_get_posts', [__CLASS__, 'modifyPostsPerPage'], 9999);
    }

	// -------------------------------------------------------------

    /**
     * Query posts/products by a single term.
     *
     * @param mixed $term
     * @param string|bool $post_type
     * @param int|null $limit
     * @param bool $return_query
     * @param bool|null $include_children
     * @param array $exclude_ids
     * @param string $orderby
     * @param string $order
     * @param int $cache_expire
     *
     * @return array|false|\WP_Query
     */
    public static function queryByTerm(
        mixed $term,
        string|bool $post_type = 'post',
        ?int $limit = 12,
        bool $return_query = true,
        ?bool $include_children = true,
        array $exclude_ids = [],
        string $orderby = 'date',
        string $order = 'DESC',
        int $cache_expire = 600,
    ): \WP_Query|false|array {
        if (!$term) {
            return false;
        }

        // Convert a term to object if it is not already
        $term = is_object($term) ? $term : self::toObject($term);
        if (!$term || empty($term->taxonomy) || empty($term->term_id) || empty($term->slug)) {
            return false;
        }

        $exclude_ids = array_values(array_unique(array_map('intval', $exclude_ids)));
        $include_children = (bool) $include_children;
        $taxonomy = (string) $term->taxonomy;
        $term_ids[] = (int) $term->term_id;
        $term_slugs[] = (string) $term->slug;

        $field = !empty($term_ids) ? 'term_id' : 'slug';
        $terms = !empty($term_ids) ? $term_ids : $term_slugs;

        // Normalize limit
        $limit = (int) $limit;
        if ($limit < 0) {
            $limit = -1;
        }

        // Normalize orderby & order
        $is_product = ($post_type === 'product' && \function_exists('wc_get_products'));
        $allowed_wp = ['date', 'title', 'menu_order', 'rand', 'modified', 'id'];
        $allowed_wc = ['date', 'price', 'rating', 'popularity', 'title', 'menu_order', 'rand', 'id', 'modified'];
        $allowed = $is_product ? $allowed_wc : $allowed_wp;
        $ob = in_array(strtolower($orderby), $allowed, true) ? strtolower($orderby) : 'date';
        $od = (strtoupper($order) === 'ASC') ? 'ASC' : 'DESC';
        $is_rand = ($ob === 'rand');

        // Woo: hide out of stock
        $hide_oos = ($is_product && self::getOption('woocommerce_hide_out_of_stock_items') === 'yes');

        // Cache key
        $ckey_parts = [$post_type, $taxonomy, $field, $terms, $limit, $ob, $od, $hide_oos, $include_children];
        $ckey = 'qbt:byterm:' . md5(wp_json_encode($ckey_parts));

        $ids = false;

        // If rand
        if (!$is_rand) {
            $ids = wp_cache_get($ckey, 'qbt');
        }

        if ($ids === false) {

            // Products
            if ($is_product) {
                $wc_args = [
                    'status' => 'publish',
                    'limit' => $limit,
                    'return' => 'ids',
                    'orderby' => $ob,
                    'order' => $od,
                    'exclude' => $exclude_ids,
                ];

                // product_cat/product_tag
                if (in_array($taxonomy, ['product_cat', 'product_tag'], true)) {
                    if ($include_children === true) {
                        $taxonomy === 'product_cat' && $wc_args['category'] = $term_slugs;
                        $taxonomy === 'product_tag' && $wc_args['tag'] = $term_slugs;
                    } else {
                        $wc_args['tax_query'] = [
                            [
                                'taxonomy' => $taxonomy,
                                'field' => $field,
                                'terms' => $terms,
                                'operator' => 'IN',
                                'include_children' => false,
                            ],
                        ];
                    }

                    if ($hide_oos) {
                        $wc_args['stock_status'] = 'instock';
                    }

                    $ids = \wc_get_products($wc_args);
                }
            } else { /* not product */

                $args = [
                    'post_type' => $post_type,
                    'post_status' => 'publish',
                    'posts_per_page' => $limit,
                    'fields' => 'ids',
                    'no_found_rows' => true,
                    'ignore_sticky_posts' => true,
                    'tax_query' => [
                        [
                            'taxonomy' => $taxonomy,
                            'field' => $field,
                            'terms' => $terms,
                            'operator' => 'IN',
                            'include_children' => $include_children,
                        ],
                    ],
                    'orderby' => $ob,
                    'order' => $od,
                    'post__not_in' => $exclude_ids,
                ];

                $q = new \WP_Query($args);
                $ids = $q->posts ?: [];
            }

            if (!$is_rand) {
                wp_cache_set($ckey, $ids, 'qbt', $cache_expire);
            }
        }

        if (empty($ids)) {
            return false;
        }

        // return ids
        if (!$return_query) {
            return $ids;
        }

        // return \WP_Query
        return new \WP_Query([
            'post_type' => $post_type,
            'post_status' => 'publish',
            'posts_per_page' => count($ids),
            'post__in' => $ids,
            'orderby' => 'post__in',
            'no_found_rows' => true,
            'ignore_sticky_posts' => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
            'lazy_load_term_meta' => false,
        ]);
    }

	// -------------------------------------------------------------

    /**
     * Query posts by multiple terms.
     *
     * @param array|null $term_ids
     * @param string|bool $post_type
     * @param string $taxonomy
     * @param int|null $limit
     * @param bool $return_query
     * @param bool|null $include_children
     * @param array $exclude_ids
     * @param string $orderby
     * @param string $order
     * @param int $cache_expire
     *
     * @return \WP_Query|false|array
     */
    public static function queryByTerms(
        ?array $term_ids,
        string|bool $post_type = 'post',
        string $taxonomy = '',
        ?int $limit = 12,
        bool $return_query = true,
        ?bool $include_children = false,
        array $exclude_ids = [],
        string $orderby = 'date',
        string $order = 'DESC',
        int $cache_expire = 600,
    ): \WP_Query|false|array {
        $term_ids = array_values(array_unique(array_map('intval', $term_ids)));
        $exclude_ids = array_values(array_unique(array_map('intval', $exclude_ids)));

        if (empty($term_ids)) {
            return false;
        }

        if (!$taxonomy) {
            $taxonomy = ($post_type === 'product') ? 'product_cat' : 'category';
        }

        // Normalize limit
        $limit = (int) $limit;
        if ($limit < 0) {
            $limit = -1;
        }

        // Normalize orderby & order
        $is_product = ($post_type === 'product' && \function_exists('wc_get_products'));
        $allowed_wp = ['date', 'title', 'menu_order', 'rand', 'modified', 'id'];
        $allowed_wc = [
            'date',
            'price',
            'rating',
            'popularity',
            'title',
            'menu_order',
            'rand',
            'id',
            'modified'
        ];
        $allowed = $is_product ? $allowed_wc : $allowed_wp;
        $ob = in_array(strtolower($orderby), $allowed, true) ? strtolower($orderby) : 'date';
        $od = (strtoupper($order) === 'ASC') ? 'ASC' : 'DESC';
        $is_rand = ($ob === 'rand');
        $include_children = (bool) $include_children;

        // Woo: hide out of stock
        $hide_oos = ($is_product && self::getOption('woocommerce_hide_out_of_stock_items') === 'yes');

        // Cache key
        $ckey_parts = [$post_type, $taxonomy, $term_ids, $limit, $ob, $od, $hide_oos, $include_children];
        $ckey = 'qbt:byterms:' . md5(wp_json_encode($ckey_parts));

        $ids = false;

        // If rand
        if (!$is_rand) {
            $ids = wp_cache_get($ckey, 'qbt');
        }

        if ($ids === false) {

            // Products
            if ($is_product) {
                $wc_args = [
                    'status' => 'publish',
                    'limit' => $limit,
                    'return' => 'ids',
                    'orderby' => $ob,
                    'order' => $od,
                    'tax_query' => [
                        [
                            'taxonomy' => $taxonomy,
                            'field' => 'term_id',
                            'terms' => $term_ids,
                            'operator' => 'IN',
                            'include_children' => $include_children,
                        ]
                    ],
                    'exclude' => $exclude_ids,
                ];

                if ($hide_oos) {
                    $wc_args['stock_status'] = 'instock';
                }

                $ids = \wc_get_products($wc_args);
            } else { /* not product */

                $args = [
                    'post_type' => $post_type,
                    'post_status' => 'publish',
                    'posts_per_page' => $limit,
                    'fields' => 'ids',
                    'no_found_rows' => true,
                    'ignore_sticky_posts' => true,
                    'tax_query' => [
                        [
                            'taxonomy' => $taxonomy,
                            'field' => 'term_id',
                            'terms' => $term_ids,
                            'operator' => 'IN',
                            'include_children' => $include_children,
                        ]
                    ],
                    'orderby' => $ob,
                    'order' => $od,
                    'post__not_in' => $exclude_ids,
                ];

                $q = new \WP_Query($args);
                $ids = $q->posts ?: [];
            }

            if (!$is_rand) {
                wp_cache_set($ckey, $ids, 'qbt', $cache_expire);
            }
        }

        if (empty($ids)) {
            return false;
        }

        // return ids
        if (!$return_query) {
            return $ids;
        }

        // \WP_Query
        return new \WP_Query([
            'post_type' => $post_type,
            'post_status' => 'publish',
            'posts_per_page' => count($ids),
            'post__in' => $ids,
            'orderby' => 'post__in',
            'no_found_rows' => true,
            'ignore_sticky_posts' => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
            'lazy_load_term_meta' => false,
        ]);
    }

	// -------------------------------------------------------------

    /**
     * @param string|bool $post_type
     * @param int|null $posts_per_page
     * @param bool $return_query
     * @param array $exclude_ids
     * @param string|bool $strtotime_str
     * @param int $cache_expire
     *
     * @return array|false|\WP_Query
     */
    public static function queryByLatestPosts(
        string|bool $post_type = 'post',
        ?int $posts_per_page = 10,
        bool $return_query = true,
        array $exclude_ids = [],
        string|bool $strtotime_str = false,
        int $cache_expire = 600,
    ): \WP_Query|false|array {
        $exclude_ids = array_values(array_unique(array_map('intval', $exclude_ids)));
        $posts_per_page = (int) $posts_per_page;
        if ($posts_per_page < 0) {
            $posts_per_page = -1;
        }

        // Handle date_query for recent posts
        $since_ts = false;
        if ($strtotime_str) {
            $tmp = strtotime((string) $strtotime_str);
            if ($tmp && $tmp > 0) {
                $since_ts = ($tmp <= time()) ? $tmp : false;
            }
        }

        $is_product = ($post_type === 'product' && \function_exists('wc_get_products'));
        $ckey_parts = ['latest', $post_type, $posts_per_page, (int) $since_ts];
        $ckey = 'qbt:latest:' . md5(wp_json_encode($ckey_parts));

        $ids = wp_cache_get($ckey, 'qbt');
        if ($ids === false) {

            if ($is_product && $since_ts === false) {
                $wc_args = [
                    'status' => 'publish',
                    'limit' => $posts_per_page,
                    'return' => 'ids',
                    'orderby' => 'date',
                    'order' => 'DESC',
                    'exclude' => $exclude_ids,
                ];

                // Woo: hide out of stock
                $hide_oos = (self::getOption('woocommerce_hide_out_of_stock_items') === 'yes');
                if ($hide_oos) {
                    $wc_args['stock_status'] = 'instock';
                }

                $ids = \wc_get_products($wc_args);
                $ids = is_array($ids) ? $ids : [];
            } else {

                $_args = [
                    'post_type' => $post_type,
                    'post_status' => 'publish',
                    'fields' => 'ids',
                    'posts_per_page' => $posts_per_page,
                    'no_found_rows' => true,
                    'ignore_sticky_posts' => true,
                    'orderby' => 'date',
                    'order' => 'DESC',
                    'post__not_in' => $exclude_ids,
                ];

                if ($since_ts) {
                    $_args['date_query'] = [
                        [
                            'after' => [
                                'year' => (int) date('Y', $since_ts),
                                'month' => (int) date('n', $since_ts),
                                'day' => (int) date('j', $since_ts),
                            ],
                            'inclusive' => true,
                        ],
                    ];
                }

                $q = new \WP_Query($_args);
                $ids = $q->posts ?: [];
            }

            wp_cache_set($ckey, $ids, 'qbt', $cache_expire);
        }

        if (empty($ids)) {
            return false;
        }

        // return ids
        if (!$return_query) {
            return $ids;
        }

        // \WP_Query
        return new \WP_Query([
            'post_type' => $post_type,
            'post_status' => 'publish',
            'posts_per_page' => count($ids),
            'post__in' => $ids,
            'orderby' => 'post__in',
            'no_found_rows' => true,
            'ignore_sticky_posts' => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
            'lazy_load_term_meta' => false,
        ]);
    }

	// -------------------------------------------------------------

    /**
     * @param $post_id
     * @param string $taxonomy
     * @param int $post_count
     * @param bool $return_query
     * @param int $cache_expire
     *
     * @return array|false|\WP_Query
     */
    public static function queryByRelated(
        $post_id,
        string $taxonomy = '',
        int $post_count = 6,
        bool $return_query = true,
        int $cache_expire = 600,
    ): \WP_Query|false|array {
        $post_terms = get_the_terms($post_id, $taxonomy);
        if (!is_array($post_terms) || empty($post_terms)) {
            return false;
        }

        $term_ids = wp_list_pluck($post_terms, 'term_id');
        $post_type = get_post_type($post_id);

        return self::queryByTerms(
            $term_ids,
            $post_type,
            $taxonomy,
            $post_count,
            $return_query,
            false,
            [$post_id],
            'date',
            'DESC',
            $cache_expire
        );
    }

	// -------------------------------------------------------------

    /**
     * @param mixed $blog_id
     *
     * @return string
     *
     * Modified from the native get_custom_logo() function
     */
    public static function customSiteLogo(mixed $blog_id = 0): string
    {
        if (!$blog_id) {
            $blog_id = 0;
        }

        $blog_id = (int) $blog_id;
        $html = '';
        $switched_blog = false;

        if (is_multisite() && get_current_blog_id() !== $blog_id) {
            switch_to_blog($blog_id);
            $switched_blog = true;
        }

        $custom_logo_id = self::getThemeMod('custom_logo');

        // We have a logo. Logo is go.
        if ($custom_logo_id) {
            $custom_logo_attr = [
                'class' => 'custom-logo',
                'loading' => false,
            ];

            $unlink_homepage_logo = (bool) get_theme_support('custom-logo', 'unlink-homepage-logo');
            $unlink_logo = $unlink_homepage_logo;

            if ($unlink_homepage_logo && self::isHomeOrFrontPage() && !is_paged()) {
                /*
				 * If on the home page, set the logo alt attribute to an empty string,
				 * as the image is decorative and doesn't need its purpose to be described.
				 */
                $custom_logo_attr['alt'] = '';
            } elseif ($unlink_logo) {

                // set the logo alt attribute to an empty string
                $custom_logo_attr['alt'] = '';
            } else {
                /*
				 * If the logo alt attribute is empty, get the site title and explicitly pass it
				 * to the attributes used by wp_get_attachment_image().
				 */
                $image_alt = get_post_meta($custom_logo_id, '_wp_attachment_image_alt', true);
                if (empty($image_alt)) {
                    $custom_logo_attr['alt'] = get_bloginfo('name', 'display');
                }
            }

            /**
             * Filters the list of custom logo image attributes.
             *
             * @param array $custom_logo_attr Custom logo image attributes.
             * @param int $custom_logo_id Custom logo attachment ID.
             * @param int $blog_id ID of the blog to get the custom logo for.
             *
             * @since 5.5.0
             */
            $custom_logo_attr = apply_filters('get_custom_logo_image_attributes', $custom_logo_attr, $custom_logo_id, $blog_id);

            /*
			 * If the alt attribute is not empty, there's no need to explicitly pass it
			 * because wp_get_attachment_image() already adds the alt attribute.
			 */
            $image = self::attachmentImageHTML($custom_logo_id, 'full', $custom_logo_attr);

            if ($unlink_homepage_logo && self::isHomeOrFrontPage() && !is_paged()) {
                // If on the home page, don't link the logo to home.
                $html = sprintf('<span class="custom-logo-link">%1$s</span>', $image);
            } elseif ($unlink_logo) {
                // Remove logo link
                $html = sprintf('<span class="custom-logo-link">%1$s</span>', $image);
            } else {
                $aria_current = self::isHomeOrFrontPage() && !is_paged() ? ' aria-current="page"' : '';
                $html = sprintf(
                    '<a href="%1$s" class="custom-logo-link" rel="home"%2$s>%3$s</a>',
                    self::home(),
                    $aria_current,
                    $image
                );
            }
        } elseif (is_customize_preview()) {
            // If no logo is set, but we're in the Customizer, leave a placeholder (needed for the live preview).
            $html = sprintf(
                '<a href="%1$s" class="custom-logo-link" style="display:none;">' . esc_html(get_bloginfo('name')) . '</a>',
                self::home(),
            );
        }

        if ($switched_blog) {
            restore_current_blog();
        }

        /**
         * Filters the custom logo output.
         *
         * @param string $html Custom logo HTML output.
         * @param int $blog_id ID of the blog to get the custom logo for.
         *
         * @since 4.5.0
         * @since 4.6.0 Added the `$blog_id` parameter.
         *
         */
        return apply_filters('get_custom_logo', $html, $blog_id);
    }

	// -------------------------------------------------------------

    /**
     * @param string $home_template
     *
     * @return bool
     */
    public static function isHomeOrFrontPage(string $home_template = ''): bool
    {
        $home_template = $home_template ?: 'templates/template-page-home.php';

        return is_home() || is_front_page() || self::isPageTemplate($home_template);
    }

	// -------------------------------------------------------------

    /**
     * @param bool $echo
     * @param string|null $home_heading
     * @param string|null $class
     * @param int $cache_in_hours
     *
     * @return string|void
     */
    public static function siteTitleOrLogo(bool $echo = true, ?string $home_heading = 'h1', ?string $class = 'logo', int $cache_in_hours = 12)
    {
        $lang = function_exists('pll_current_language') ? pll_current_language() : '';
        $cache_key = 'hd_site_title_or_logo' . ($lang ? '_' . $lang : '');
        $cached_html = get_transient($cache_key);

        if (!empty($cached_html)) {
            if (!$echo) {
                return $cached_html;
            }
            echo $cached_html;

            return;
        }

        $logo_title = '';
        $logo_class = !empty($class) ? ' class="' . $class . '"' : '';

        if (function_exists('the_custom_logo') && has_custom_logo()) {
            // replace \get_custom_logo() with self::customSiteLogo()
            $logo = self::customSiteLogo();
            $html = '<a' . $logo_class . ' title="' . esc_attr(get_bloginfo('name')) . '" href="' . self::home('/') . '" rel="home">' . $logo . $logo_title . '</a>';
        } else {
            $html = '<a' . $logo_class . ' title="' . esc_attr(get_bloginfo('name')) . '" href="' . self::home('/') . '" rel="home">' . esc_html(get_bloginfo('name')) . $logo_title . '</a>';
            if ('' !== get_bloginfo('description')) {
                $html .= '<p class="site-description">' . esc_html(get_bloginfo('description', 'display')) . '</p>';
            }
        }

        if (is_string($home_heading) && !empty($home_heading)) {
            $is_home_or_front_page = self::isHomeOrFrontPage();
            $tag = $is_home_or_front_page ? $home_heading : 'div';
            $logo_heading = self::getThemeMod('home_heading_setting');

            if ($logo_heading && $is_home_or_front_page) {
                $html .= '<' . esc_attr($tag) . ' class="sr-only">' . $logo_heading . '</' . esc_attr($tag) . '>';
            }
        }

        $html = '<div class="site-logo">' . $html . '</div>';

        set_transient($cache_key, $html, $cache_in_hours * HOUR_IN_SECONDS);
        if (!$echo) {
            return $html;
        }

        echo $html;
    }

	// -------------------------------------------------------------

    /**
     * @param string $theme - default|light|dark
     * @param string|null $class
     * @param int $cache_in_hours
     *
     * @return string
     */
    public static function siteLogo(string $theme = 'default', ?string $class = '', int $cache_in_hours = 12): string
    {
        $lang = function_exists('pll_current_language') ? pll_current_language() : '';
        $cache_key = 'hd_site_logo_' . $theme . ($lang ? '_' . $lang : '');
        $cached_html = get_transient($cache_key);

        if (!empty($cached_html)) {
            return $cached_html;
        }

        $html = '';
        $custom_logo_id = null;

        if ('default' !== $theme && $theme_logo = self::getThemeMod($theme . '_logo')) {
            $custom_logo_id = attachment_url_to_postid($theme_logo);
        } elseif (has_custom_logo()) {
            $custom_logo_id = self::getThemeMod('custom_logo');
        }

        // We have a logo. Logo is go.
        if ($custom_logo_id) {
            $custom_logo_attr = [
                'class' => $theme . '-logo',
                'loading' => 'lazy',
            ];

            /**
             * If the logo alt attribute is empty, get the site title and explicitly pass it
             * to the attributes used by wp_get_attachment_image().
             */
            $image_alt = get_post_meta($custom_logo_id, '_wp_attachment_image_alt', true);
            if (empty($image_alt)) {
                $image_alt = get_bloginfo('name', 'display');
            }

            $custom_logo_attr['alt'] = $image_alt;

            $logo_title = self::getThemeMod('logo_title_setting');
            $logo_title = $logo_title ? '<span>' . $logo_title . '</span>' : '';

            /**
             * If the alt attribute is not empty, there's no need to explicitly pass it
             * because wp_get_attachment_image() already adds the alt attribute.
             */
            $logo = self::attachmentImageHTML($custom_logo_id, 'full', $custom_logo_attr);
            if ($class) {
                $html = '<div class="' . $class . '"><a title="' . $image_alt . '" href="' . self::home() . '">' . $logo . $logo_title . '</a></div>';
            } else {
                $html = '<a title="' . $image_alt . '" href="' . self::home() . '">' . $logo . $logo_title . '</a>';
            }
        }

        set_transient($cache_key, $html, $cache_in_hours * HOUR_IN_SECONDS);

        return $html;
    }

	// -------------------------------------------------------------

    /**
     * @param mixed|null $post
     * @param string|null $class
     * @param string|null $default_tag
     *
     * @return string|null
     */
    public static function loopExcerpt(mixed $post = null, ?string $class = 'excerpt', ?string $default_tag = 'div'): ?string
    {
        $excerpt = get_the_excerpt($post);
        if (!self::stripSpace($excerpt)) {
            return null;
        }

        $excerpt = strip_tags($excerpt);
        if (!$class) {
            return $excerpt;
        }

        $tag = $default_tag ?? 'p';

        return '<' . $tag . " class=\"$class\">{$excerpt}</" . $tag . '>';
    }

	// -------------------------------------------------------------

    /**
     * @param mixed|null $post
     * @param string|null $class
     * @param string|null $default_tag
     * @param string|null $fa_glyph
     *
     * @return string|null
     */
    public static function postExcerpt(mixed $post = null, ?string $class = 'excerpt', ?string $default_tag = 'div', ?string $fa_glyph = ''): ?string
    {
        $post = get_post($post);
        if (!$post || !self::stripSpace($post->post_excerpt)) {
            return null;
        }

        $open = '';
        $close = '';
        $glyph = '';

        if ($fa_glyph) {
            $glyph = ' data-fa="' . $fa_glyph . '"';
        }

        if ($class) {
            $tag = $default_tag ?? 'div';

            $open = '<' . $tag . ' class="' . $class . '"' . $glyph . '>';
            $close = '</' . $tag . '>';
        }

        return $open . $post->post_excerpt . $close;
    }

	// -------------------------------------------------------------

    /**
     * @param mixed $term
     * @param string|null $class
     * @param string|null $default_tag
     *
     * @return string|null
     */
    public static function termExcerpt(mixed $term = 0, ?string $class = 'excerpt', ?string $default_tag = 'div'): ?string
    {
        if (!$term) {
            $term = 0;
        }

        $description = term_description((int) $term);
        if (!self::stripSpace($description)) {
            return null;
        }

        $description = strip_tags($description);
        if (!$class) {
            return $description;
        }

        $tag = $default_tag ?? 'p';

        return '<' . $tag . " class=\"$class\">$description</" . $tag . '>';
    }

	// -------------------------------------------------------------

    /**
     * @param string|null $post_type
     *
     * @return string|null
     */
    public static function getTaxonomyByPostType(?string $post_type): ?string
    {
        if (empty($post_type)) {
            return null;
        }

        // The default taxonomy for 'post' is 'category'
        if ('post' === $post_type) {
            return 'category';
        }

        // 'product' is 'product_cat'
        if ('product' === $post_type && self::isWoocommerceActive()) {
            return 'product_cat';
        }

        // Use custom filter to retrieve taxonomy mapping for the post-type
        $post_type_terms = self::filterSettingOptions('post_type_terms', []);

        return $post_type_terms[$post_type] ?? null;
    }

	// -------------------------------------------------------------

    /**
     * Retrieves the appropriate taxonomy for a given post.
     *
     * @param \WP_Post|int|null $post Optional. Post object or ID. Defaults to current post.
     * @param string|null $taxonomy Optional. Specific taxonomy to use.
     *
     * @return string|null The taxonomy name or null if no valid taxonomy found.
     */
    public static function getTaxonomy(mixed $post, ?string $taxonomy = null): ?string
    {
        // Ensure $post is a valid post object
        $post = get_post($post);
        if (!$post || is_wp_error($post)) {
            return null;
        }

        $post_type = get_post_type($post);
        if (!$post_type) {
            return null;
        }

        // Determine the taxonomy if not explicitly provided
        if (empty($taxonomy)) {
            // Additional check: try "{$post_type}_cat" format
            $taxonomy = self::getTaxonomyByPostType($post_type) ?: "{$post_type}_cat";
        }

        // Validate taxonomy
        return taxonomy_exists($taxonomy) ? $taxonomy : null;
    }

	// -------------------------------------------------------------

    /**
     * @param mixed $post
     * @param string|null $taxonomy
     * @param string|null $wrapper_open
     * @param string|null $wrapper_close
     *
     * @return string|null
     */
    public static function postTerms(mixed $post, ?string $taxonomy = '', ?string $wrapper_open = '<div class="terms">', ?string $wrapper_close = '</div>'): ?string
    {
        // Ensure $post is a valid post object
        $post = get_post($post);
        if (!$post || is_wp_error($post)) {
            return null;
        }

        // Determine the taxonomy if not explicitly provided
        $taxonomy = self::getTaxonomy($post, $taxonomy);
        if (!$taxonomy) {
            return null;
        }

        // Get all terms associated with the post for the specified taxonomy
        $post_terms = get_the_terms($post, $taxonomy);
        if (!is_array($post_terms) || empty($post_terms) || is_wp_error($post_terms)) {
            return null;
        }

        $link = '';
        foreach ($post_terms as $term) {
            if ($term->slug) {
                $link .= '<a href="' . esc_url(get_term_link($term)) . '" title="' . esc_attr($term->name) . '">' . $term->name . '</a>';
            }
        }

        if ($wrapper_open && $wrapper_close) {
            $link = $wrapper_open . $link . $wrapper_close;
        }

        return $link;
    }

	// -------------------------------------------------------------

    /**
     * @param mixed $post
     * @param string|null $taxonomy
     *
     * @return mixed
     */
    public static function primaryTerm(mixed $post, ?string $taxonomy = ''): mixed
    {
        // Ensure $post is a valid post object
        $post = get_post($post);
        if (!$post || is_wp_error($post)) {
            return null;
        }

        // Determine the taxonomy if not explicitly provided
        $taxonomy = self::getTaxonomy($post, $taxonomy);
        if (!$taxonomy) {
            return null;
        }

        // Get all terms associated with the post for the specified taxonomy
        $post_terms = get_the_terms($post, $taxonomy);
        if (!is_array($post_terms) || empty($post_terms) || is_wp_error($post_terms)) {
            return null;
        }

        // Extract term IDs for further processing
        $term_ids = wp_list_pluck($post_terms, 'term_id');

        // Support for Rank Math SEO plugin
        $primary_term_id = get_post_meta($post->ID, 'rank_math_primary_' . $taxonomy, true);
        if ($primary_term_id && in_array($primary_term_id, $term_ids, false)) {
            $term = get_term($primary_term_id, $taxonomy);
            if ($term && !is_wp_error($term)) {
                return $term;
            }
        }

        // Support for Yoast SEO plugin
        if (class_exists('\WPSEO_Primary_Term')) {
            try {
                $yoast_primary_term = new \WPSEO_Primary_Term($taxonomy, $post);
                if (method_exists($yoast_primary_term, 'get_primary_term')) {
                    $primary_term_id = $yoast_primary_term->get_primary_term();
                    if ($primary_term_id && in_array($primary_term_id, $term_ids, false)) {
                        $term = get_term($primary_term_id, $taxonomy);
                        if ($term && !is_wp_error($term)) {
                            return $term;
                        }
                    }
                }
            } catch (\Exception $e) {
                self::errorLog('Error getting Yoast primary term: ' . $e->getMessage());
            } catch (\Error $e) {
                self::errorLog('PHP Error in Yoast primary term: ' . $e->getMessage());
            }
        }

        // Support for the All-in-one SEO plugin
        if (function_exists('aioseo')) {
            $aioseo_primary_term_id = get_post_meta($post->ID, '_aioseo_primary_' . $taxonomy, true);
            if ($aioseo_primary_term_id && in_array($aioseo_primary_term_id, $term_ids, false)) {
                $term = get_term($aioseo_primary_term_id, $taxonomy);
                if ($term && !is_wp_error($term)) {
                    return $term;
                }
            }
        }

        // Default: return the first term if no primary term is found
        return $post_terms[0] ?? null;
    }

	// -------------------------------------------------------------

    /**
     * @param mixed|null $post
     * @param string $taxonomy
     * @param string|null $wrapper_open
     * @param string|null $wrapper_close
     *
     * @return string|null
     */
    public static function getPrimaryTerm(mixed $post = null, string $taxonomy = '', ?string $wrapper_open = '<div class="terms">', ?string $wrapper_close = '</div>'): ?string
    {
        $term = self::primaryTerm($post, $taxonomy);
        if (!$term || is_wp_error($term)) {
            return null;
        }

        $link = '<a href="' . esc_url(get_term_link($term, $taxonomy)) . '" title="' . esc_attr($term->name) . '">' . $term->name . '</a>';
        if ($wrapper_open && $wrapper_close) {
            $link = $wrapper_open . $link . $wrapper_close;
        }

        return $link;
    }

	// -------------------------------------------------------------

    /**
     * @param string|null $taxonomy
     * @param int $id
     * @param string $sep
     *
     * @return void
     */
    public static function hashTags(?string $taxonomy = 'post_tag', int $id = 0, string $sep = ''): void
    {
        if (!$taxonomy) {
            $taxonomy = 'post_tag';
        }

        // Get Tags for posts.
        $hashtag_list = get_the_term_list($id, $taxonomy, '', $sep);

        // We don't want to output if it is empty, so make sure it's not.
        if ($hashtag_list) {
            echo '<div class="hashtags">';
            printf(
                /* translators: 1: SVG icon. 2: posted in a label, only visible to screen readers. 3: list of tags. */
                '<div class="hashtag-links links">%1$s<span class="sr-only">%2$s</span>%3$s</div>',
                '<i data-fa="#"></i>',
                __('Từ khóa', TEXT_DOMAIN),
                $hashtag_list
            );

            echo '</div>';
        }
    }

	// -------------------------------------------------------------

    /**
     * @param int|\WP_Post|null $post_id
     * @param string $size
     *
     * @return string|false
     */
    public static function postImageSrc(int|\WP_Post|null $post_id = null, string $size = 'thumbnail'): string|false
    {
        if (!$post_id) {
            $post_id = null;
        }

        return get_the_post_thumbnail_url($post_id, $size);
    }

	// -------------------------------------------------------------

    /**
     * @param mixed $attachment_id
     * @param string $size
     *
     * @return string|false
     */
    public static function attachmentImageSrc(mixed $attachment_id, string $size = 'thumbnail'): string|false
    {
        if (!$attachment_id) {
            return false;
        }

        return wp_get_attachment_image_url((int) $attachment_id, $size, false);
    }

	// -------------------------------------------------------------

    /**
     * @param int|\WP_Post|null $post_id
     * @param string $size
     * @param string|array $attr
     * @param bool $filter
     *
     * @return string
     */
    public static function postImageHTML(int|\WP_Post|null $post_id = null, string $size = 'post-thumbnail', string|array $attr = '', bool $filter = true): string
    {
        if (!$post_id) {
            $post_id = null;
        }

        $html = get_the_post_thumbnail($post_id, $size, $attr);

        return $filter ? apply_filters('hd_post_image_html_filter', $html, $post_id, $size, $attr) : $html;
    }

	// -------------------------------------------------------------

    /**
     * @param mixed $attachment_id
     * @param string $size
     * @param string|array $attr
     * @param bool $filter
     *
     * @return string
     */
    public static function attachmentImageHTML(mixed $attachment_id, string $size = 'thumbnail', string|array $attr = '', bool $filter = true): string
    {
        if (!$attachment_id) {
            return '';
        }

        $attachment_id = (int) $attachment_id;
        $html = wp_get_attachment_image($attachment_id, $size, false, $attr);

        return $filter ? apply_filters('hd_attachment_image_html_filter', $html, $attachment_id, $size, $attr) : $html;
    }

	// -------------------------------------------------------------

    /**
     * @param mixed $attachment_id
     * @param string $size
     * @param string|array $attr
     * @param bool $filter
     *
     * @return string
     */
    public static function iconImageHTML(mixed $attachment_id, string $size = 'thumbnail', string|array $attr = '', bool $filter = false): string
    {
        if (!$attachment_id) {
            return '';
        }

        $attachment_id = (int) $attachment_id;

        $html = '';
        $image = wp_get_attachment_image_src($attachment_id, $size, true);

        if ($image) {
            [$src, $width, $height] = $image;

            $attachment = get_post($attachment_id);
            $hwstring = image_hwstring($width, $height);

            $default_attr = [
                'src' => $src,
                'alt' => trim(strip_tags(get_post_meta($attachment_id, '_wp_attachment_image_alt', true))),
                'loading' => 'lazy',
            ];

            $context = apply_filters('wp_get_attachment_image_context', 'wp_get_attachment_image');
            $attr = wp_parse_args($attr, $default_attr);

            $loading_attr = $attr;
            $loading_attr['width'] = $width;
            $loading_attr['height'] = $height;
            $loading_optimization_attr = wp_get_loading_optimization_attributes(
                'img',
                $loading_attr,
                $context
            );

            // Add loading optimization attributes if not available.
            $attr = array_merge($attr, $loading_optimization_attr);

            // Omit the `decoding` attribute if the value is invalid, according to the spec.
            if (empty($attr['decoding']) || !in_array($attr['decoding'], ['async', 'sync', 'auto'], false)) {
                unset($attr['decoding']);
            }

            /*
			 * If the default value of `lazy` for the `loading` attribute is overridden
			 * to omit the attribute for this image, ensure it is not included.
			 */
            if (isset($attr['loading']) && !$attr['loading']) {
                unset($attr['loading']);
            }

            // If the `fetchpriority` attribute is overridden and set to false or an empty string.
            if (isset($attr['fetchpriority']) && !$attr['fetchpriority']) {
                unset($attr['fetchpriority']);
            }

            $attr = apply_filters('wp_get_attachment_image_attributes', $attr, $attachment, $size);
            $attr = array_map('esc_attr', $attr);
            $html = rtrim("<img $hwstring");

            foreach ($attr as $name => $value) {
                $html .= " $name=" . '"' . $value . '"';
            }

            $html .= ' />';
        }

        return $filter ? apply_filters('hd_icon_image_html_filter', $html, $attachment_id, $size, $attr) : $html;
    }

	// -------------------------------------------------------------

    /**
     * @param string|null $class
     * @param mixed $attachment_id
     * @param mixed $attachment_mobile_id
     * @param bool $widescreen
     * @param bool $filter
     *
     * @return string
     */
    // public static function pictureHTML(?string $class = null, mixed $attachment_id = 0, mixed $attachment_mobile_id = false, bool $widescreen = true, bool $filter = true): string
    // {
    // 	if (! $attachment_id) {
    // 		return '';
    // 	}

    // 	$html = $class ? '<picture class="' . $class . '">' : '<picture>';

    // 	if ($widescreen) {
    // 		$html .= '<source srcset="' . self::attachmentImageSrc($attachment_id, 'widescreen') . '" media="(min-width: 1024px)">';
    // 	}

    // 	$html .= '<source srcset="' . self::attachmentImageSrc($attachment_id, 'large') . '" media="(min-width: 768px)">';
    // 	$html .= self::iconImageHTML($attachment_mobile_id ?: $attachment_id, 'medium', ['class' => 'lazy'], false);
    // 	$html .= '</picture>';

    // 	return $filter ? apply_filters('hd_picture_html_filter', $html, $class, $attachment_id, $attachment_mobile_id) : $html;
    // }


    public static function pictureHTML(
        ?string $class = null,
        mixed $attachment_id = 0,
        mixed $attachment_mobile_id = false,
        bool $widescreen = true,
        bool $filter = true,
        bool $is_lcp = false // <— thêm tham số này
    ): string {
        if (!$attachment_id) {
            return '';
        }

        $html = $class ? '<picture class="' . $class . '">' : '<picture>';

        if ($widescreen) {
            $html .= '<source srcset="' . self::attachmentImageSrc($attachment_id, 'full') . '" media="(min-width: 1024px)">';
        }

        $html .= '<source srcset="' . self::attachmentImageSrc($attachment_id, 'widescreen') . '" media="(min-width: 768px)">';

        // cấu hình attribute cho thẻ <img>
        if ($is_lcp) {
            // Ảnh LCP: KHÔNG lazy, ưu tiên tải cao
            $img_attr = [
                'class' => 'home-lcp-img skip-lazy', // skip-lazy để LiteSpeed khỏi lazy
                'loading' => 'eager',
                'fetchpriority' => 'high',
            ];
        } else {
            // Ảnh thường vẫn lazy bình thường
            $img_attr = [
                'class' => 'lazy',
            ];
        }

        $html .= self::iconImageHTML(
            $attachment_mobile_id ?: $attachment_id,
            'large',
            $img_attr,
            false
        );

        $html .= '</picture>';

        return $filter ? apply_filters('hd_picture_html_filter', $html, $class, $attachment_id, $attachment_mobile_id) : $html;
    }


	// -------------------------------------------------------------

    /**
     * @param mixed $post_id
     * @param bool $force_object
     * @param bool $format_value
     * @param bool $escape_html
     *
     * @return object|bool|array
     */
    public static function getFields(mixed $post_id = false, bool $force_object = false, bool $format_value = true, bool $escape_html = false): object|bool|array
    {
        if (!self::isAcfActive()) {
            return [];
        }

        $fields = \function_exists('get_fields') ? \get_fields($post_id, $format_value, $escape_html) : [];

        return (true === $force_object) ? self::toObject($fields) : $fields;
    }

	// -------------------------------------------------------------

    /**
     * @param string|null $selector
     * @param mixed $post_id
     * @param bool $format_value
     * @param bool $escape_html
     *
     * @return mixed
     */
    public static function getField(?string $selector, mixed $post_id = false, bool $format_value = true, bool $escape_html = false): mixed
    {
        if (!$selector || !self::isAcfActive()) {
            return false;
        }

        return \function_exists('get_field') ? \get_field($selector, $post_id, $format_value, $escape_html) : false;
    }

	// -------------------------------------------------------------

    /**
     * @param $term
     * @param $acf_field_name
     * @param string $size
     * @param bool $img_wrap
     * @param string|array $attr
     *
     * @return string|null
     */
    public static function acfTermThumb($term, $acf_field_name = null, string $size = 'thumbnail', bool $img_wrap = false, string|array $attr = ''): ?string
    {
        if (!$term) {
            return null;
        }

        if (is_numeric($term)) {
            $term = self::getTerm($term);
        }

        $attach_id = self::getField($acf_field_name, $term);
        if ($attach_id) {
            $img_src = self::attachmentImageSrc($attach_id, $size);
            if ($img_wrap) {
                $img_src = self::attachmentImageHTML($attach_id, $size, $attr);
            }

            return $img_src;
        }

        return null;
    }

	// -------------------------------------------------------------

    /**
     * @return void
     */
    public static function breadCrumbs(): void
    {
        global $post, $wp_query;

        // If it's the front page, no need to display breadcrumbs
        if (is_front_page()) {
            return;
        }

        $before = '<li class="current">';
        $after = '</li>';
        $breadcrumbs = [];

        // Home
        $breadcrumbs[] = '<li><a class="home" href="' . self::home() . '">' . __('Trang chủ', TEXT_DOMAIN) . '</a></li>';

        // WooCommerce Shop Page
        if (self::isWoocommerceActive() && \is_shop()) {
            $breadcrumbs[] = $before . get_the_title(self::getOption('woocommerce_shop_page_id')) . $after;
        } // Posts Page
        elseif ($wp_query?->is_posts_page) {
            $breadcrumbs[] = $before . get_the_title(self::getOption('page_for_posts', true)) . $after;
        } // Post-type Archive
        elseif ($wp_query?->is_post_type_archive) {
            $breadcrumbs[] = $before . post_type_archive_title('', false) . $after;
        } // Page or Attachment
        elseif (is_page() || is_attachment()) {

            // Breadcrumb for child pages (Parent page)
            if ($post?->post_parent) {
                $parent_id = $post->post_parent;
                $parent_breadcrumbs = [];

                while ($parent_id) {
                    $page = get_post($parent_id);
                    $parent_breadcrumbs[] = '<li><a href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a></li>';
                    $parent_id = $page->post_parent;
                }

                $parent_breadcrumbs = array_reverse($parent_breadcrumbs);
                $breadcrumbs = array_merge($breadcrumbs, $parent_breadcrumbs);
            }
            $breadcrumbs[] = $before . get_the_title() . $after;
        } // Single
        elseif (is_single() && !is_attachment()) {
            $post_type = get_post_type_object(get_post_type());
            $taxonomies = get_object_taxonomies($post_type?->name, 'names');

            if (empty($taxonomies)) {
                $slug = $post_type?->rewrite;
                if (!is_bool($slug)) {
                    $breadcrumbs[] = '<li><a href="' . self::home() . $slug['slug'] . '/">' . $post_type?->labels?->singular_name . '</a></li>';
                }
            } else {

                // taxonomy (primary term)
                $term = self::primaryTerm($post);
                if ($term) {
                    $cat_code = get_term_parents_list($term->term_id, $term->taxonomy, ['separator' => '']);
                    $cat_code = str_replace('<a', '<li><a', $cat_code);
                    $breadcrumbs[] = str_replace('</a>', '</a></li>', $cat_code);
                }
            }

            $breadcrumbs[] = $before . get_the_title() . $after;
        } // Search page
        elseif (is_search()) {
            $breadcrumbs[] = $before . sprintf(__('Kết quả tìm kiếm cho: %s', TEXT_DOMAIN), get_search_query()) . $after;
        } // Tag Archive
        elseif (is_tag()) {
            $breadcrumbs[] = $before . sprintf(__('Lưu trữ: %s', TEXT_DOMAIN), single_tag_title('', false)) . $after;
        } // Author
        elseif (is_author()) {
            global $author;
            $userdata = get_userdata($author);
            $breadcrumbs[] = $before . $userdata?->display_name . $after;
        } // Day, Month, Year Archives
        elseif (is_day() || is_month() || is_year()) {
            if (is_day()) {
                $breadcrumbs[] = '<li><a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a></li>';
                $breadcrumbs[] = '<li><a href="' . get_month_link(get_the_time('Y'), get_the_time('m')) . '">' . get_the_time('F') . '</a></li>';
                $breadcrumbs[] = $before . get_the_time('d') . $after;
            } elseif (is_month()) {
                $breadcrumbs[] = '<li><a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a></li>';
                $breadcrumbs[] = $before . get_the_time('F') . $after;
            } elseif (is_year()) {
                $breadcrumbs[] = $before . get_the_time('Y') . $after;
            }
        } // Category, Taxonomy
        elseif (is_category() || is_tax()) {
            $cat_obj = get_queried_object();

            if ($cat_obj && $cat_obj->parent) {
                $cat_code = get_term_parents_list($cat_obj?->term_id, $cat_obj->taxonomy, [
                    'separator' => '',
                    'inclusive' => false
                ]);
                $cat_code = str_replace('<a', '<li><a', $cat_code);
                $breadcrumbs[] = str_replace('</a>', '</a></li>', $cat_code);
            }

            $breadcrumbs[] = $before . single_cat_title('', false) . $after;
        } // 404 Page
        elseif (is_404()) {
            $breadcrumbs[] = $before . __('Không tìm thấy', TEXT_DOMAIN) . $after;
        }

        // If there is pagination
        if (get_query_var('paged')) {
            $breadcrumbs[] = $before . ' (' . __('trang', TEXT_DOMAIN) . ' ' . get_query_var('paged') . ')' . $after;
        }

        // Display Breadcrumbs.
        echo '<ul id="breadcrumbs" class="breadcrumbs" aria-label="Breadcrumbs">';
        echo implode('', $breadcrumbs);
        echo '</ul>';

        // Reset query
        wp_reset_query();
    }

	// -------------------------------------------------------------

    /**
     * @param string $template
     *
     * @return array|\WP_Post|null
     */
    public static function getPageTemplate(string $template): array|\WP_Post|null
    {
        $query = new \WP_Query([
            'post_type' => 'page',
            'posts_per_page' => 1,
            'meta_key' => '_wp_page_template',
            'meta_value' => $template,
            'orderby' => 'menu_order',
            'order' => 'ASC',
        ]);

        if ($query->have_posts()) {
            $query->the_post();

            $post = get_post();
            wp_reset_postdata();

            return $post;
        }

        wp_reset_postdata();

        return null;
    }



    /**
     * In ra thẻ picture hỗ trợ WebP + fallback
     * @param string $class - class CSS
     * @param int $image_id - ID ảnh ACF
     */
    public function bluera_picture_webp($class, $image_id)
    {
        if (!$image_id)
            return '';

        $src = wp_get_attachment_image_src($image_id, 'full');
        if (!$src)
            return '';

        $url = $src[0];
        $alt = get_post_meta($image_id, '_wp_attachment_image_alt', true) ?: get_the_title($image_id);

        // Tạo link webp từ file gốc
        $webp = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $url);

        ob_start(); ?>
        <picture>
            <source srcset="<?= esc_url($webp) ?>" type="image/webp">
            <img src="<?= esc_url($url) ?>" alt="<?= esc_attr($alt) ?>" class="<?= esc_attr($class) ?>" loading="lazy"
                decoding="async" width="<?= esc_attr($src[1]) ?>" height="<?= esc_attr($src[2]) ?>">
        </picture>
<?php
        return ob_get_clean();
    }


	// -------------------------------------------------------------

    /**
     * @param string $template
     *
     * @return bool|string|null
     */
    public static function getPageLinkTemplate(string $template): bool|string|null
    {
        $query = new \WP_Query([
            'post_type' => 'page',
            'posts_per_page' => 1,
            'meta_key' => '_wp_page_template',
            'meta_value' => $template,
            'orderby' => 'menu_order',
            'order' => 'ASC',
        ]);

        if ($query->have_posts()) {
            $query->the_post();
            $url = get_permalink();
            wp_reset_postdata();

            return $url;
        }

        wp_reset_postdata();

        return null;
    }

	// -------------------------------------------------------------

    /**
     * @param mixed $user_id
     *
     * @return string
     */
    public static function getUserLink(mixed $user_id = null): string
    {
        if (!$user_id) {
            $user_id = get_the_author_meta('ID');
        }

        return esc_url(get_author_posts_url((int) $user_id));
    }

	// -------------------------------------------------------------

    /**
     * @param string $post_type
     * @param string|null $option
     *
     * @return string|string[]
     */
    public static function getAspectRatioOption(string $post_type = '', ?string $option = ''): array|string
    {
        $post_type = $post_type ?: 'post';
        $option = !empty($option) ? $option : 'aspect_ratio__options';

        $aspect_ratio_options = self::getOption($option);
        $width = $aspect_ratio_options['ar-' . $post_type . '-width'] ?? '';
        $height = $aspect_ratio_options['ar-' . $post_type . '-height'] ?? '';

        return ($width && $height) ? [$width, $height] : '';
    }

	// -------------------------------------------------------------

    /**
     * @param string $post_type
     * @param string $default
     *
     * @return string
     */
    public static function aspectRatioClass(string $post_type = 'post', string $default = 'ar-3-2'): string
    {
        $ratio = self::getAspectRatioOption($post_type);

        $ratio_x = $ratio[0] ?? '';
        $ratio_y = $ratio[1] ?? '';
        if (!$ratio_x || !$ratio_y) {
            return $default;
        }

        return 'ar-' . $ratio_x . '-' . $ratio_y;
    }

	// -------------------------------------------------------------

    /**
     * @param string $post_type
     * @param string|null $option
     * @param string $default
     *
     * @return object
     */
    public static function getAspectRatio(string $post_type = 'post', ?string $option = '', string $default = 'ar-3-2'): object
    {
        $ratio = self::getAspectRatioOption($post_type, $option);

        $ratio_x = $ratio[0] ?? '';
        $ratio_y = $ratio[1] ?? '';

        $ratio_style = '';
        if (!$ratio_x || !$ratio_y) {
            $ratio_class = $default;
        } else {

            $ratio_class = 'ar-' . $ratio_x . '-' . $ratio_y;
            $ar_aspect_ratio_default = self::filterSettingOptions('aspect_ratio_default', []);

            if (is_array($ar_aspect_ratio_default) && !in_array($ratio_x . '-' . $ratio_y, $ar_aspect_ratio_default, false)) {
                $css = new \HD_CSS();
                $css->set_selector('.' . $ratio_class);
                //$css->add_property( 'height', 0 );

                //$pb = ( $ratio_y / $ratio_x ) * 100;
                //$css->add_property( 'padding-bottom', $pb . '%' );
                $css->add_property('aspect-ratio', $ratio_x . '/' . $ratio_y);
                $ratio_style = $css->css_output();
            }
        }

        return (object) [
            'class' => $ratio_class,
            'style' => $ratio_style,
        ];
    }

	// -------------------------------------------------------------

    /**
     * Get any necessary microdata.
     *
     * @param string|null $context The element to target.
     *
     * @return string Our final attribute to add to the element.
     */
    public static function microdata(?string $context): string
    {
        $data = null;

        if ('body' === $context) {
            $type = 'WebPage';

            if (is_home() || is_archive() || is_attachment() || is_tax() || is_single()) {
                $type = 'Blog';
            }

            if (is_search()) {
                $type = 'SearchResultsPage';
            }

            if (function_exists('is_shop') && \is_shop()) {
                $type = 'Collection';
            }

            if (function_exists('is_product_category') && \is_product_category()) {
                $type = 'Collection';
            }

            $data = sprintf('itemtype="https://schema.org/%s" itemscope', esc_html($type));
        }

        if ('header' === $context) {
            $data = 'itemtype="https://schema.org/WPHeader" itemscope';
        }

        if ('navigation' === $context) {
            $data = 'itemtype="https://schema.org/SiteNavigationElement" itemscope';
        }

        if ('article' === $context) {
            $data = 'itemtype="https://schema.org/CreativeWork" itemscope';
        }

        if ('product' === $context) {
            $data = 'itemtype="https://schema.org/Product" itemscope';
        }

        if ('post-author' === $context) {
            $data = 'itemprop="author" itemtype="https://schema.org/Person" itemscope';
        }

        if ('comment-body' === $context) {
            $data = 'itemtype="https://schema.org/Comment" itemscope';
        }

        if ('comment-author' === $context) {
            $data = 'itemprop="author" itemtype="https://schema.org/Person" itemscope';
        }

        if ('sidebar' === $context) {
            $data = 'itemtype="https://schema.org/WPSideBar" itemscope';
        }

        if ('footer' === $context) {
            $data = 'itemtype="https://schema.org/WPFooter" itemscope';
        }

        if ('headline' === $context) {
            $data = 'itemprop="headline"';
        }

        if ('url' === $context) {
            $data = 'itemprop="url"';
        }

        if ('name' === $context) {
            $data = 'itemprop="name"';
        }

        if ('review' === $context) {
            $data = 'itemtype="https://schema.org/Review" itemscope';
        }

        if ('publisher' === $context) {
            $data = 'itemtype="https://schema.org/Organization" itemscope';
        }

        if ('date-published' === $context) {
            $data = 'itemprop="datePublished"';
        }

        if ('date-modified' === $context) {
            $data = 'itemprop="dateModified"';
        }

        if ('rating' === $context) {
            $data = 'itemtype="https://schema.org/Rating" itemscope';
        }

        if ('faq' === $context) {
            $data = 'itemtype="https://schema.org/FAQPage" itemscope';
        }

        if ('question' === $context) {
            $data = 'itemtype="https://schema.org/Question" itemscope';
        }

        if ('answer' === $context) {
            $data = 'itemtype="https://schema.org/Answer" itemscope';
        }

        return apply_filters("hd_{$context}_microdata_filter", $data);
    }

	// -------------------------------------------------------------

    /**
     * @param mixed $term_id
     * @param string $taxonomy
     * @param bool $hide_empty
     *
     * @return int[]|string|string[]|\WP_Error|\WP_Term[]|null
     */
    public static function childTerms(mixed $term_id, string $taxonomy, bool $hide_empty = true): array|\WP_Error|string|null
    {
        if (!$term_id || !taxonomy_exists($taxonomy)) {
            return null;
        }

        $child_terms = get_terms([
            'taxonomy' => $taxonomy,
            'parent' => (int) $term_id,
            'hide_empty' => $hide_empty,
        ]);

        if (empty($child_terms) || is_wp_error($child_terms)) {
            return null;
        }

        return $child_terms;
    }

	// -------------------------------------------------------------

    /**
     * @param string|null $taxonomy
     * @param bool $hide_empty
     * @param mixed $parent
     * @param mixed|null $selected_request
     * @param int|null $disabled_parent
     * @param bool $only_parent
     *
     * @return array|null
     */
    public static function hierarchyTerms(
        ?string $taxonomy,
        bool $hide_empty = true,
        mixed $parent = null,
        mixed $selected_request = null,
        ?int $disabled_parent = null,
        bool $only_parent = false
    ): ?array {
        if ($taxonomy === null || !taxonomy_exists($taxonomy)) {
            return null;
        }

        $args = [
            'taxonomy' => $taxonomy,
            'hide_empty' => $hide_empty,
            'hierarchical' => true,
            'parent' => 0,
        ];

        if (!is_null($parent)) {
            $args['parent'] = (int) $parent;
        }

        $terms = get_terms($args);
        if (empty($terms) || is_wp_error($terms)) {
            return null;
        }

        $options = [];
        foreach ($terms as $term) {

            // Append options from the term and its children using the spread operator
            $options = [
                ...$options,
                ...self::_buildTreeTerms($term, $hide_empty, 0, $selected_request, $disabled_parent, $only_parent),
            ];
        }

        return $options;
    }

	// -------------------------------------------------------------

    /**
     * @param mixed $term
     * @param bool $hide_empty
     * @param int $depth
     * @param mixed|null $selected_request
     * @param int|null $disabled_parent
     * @param bool $only_parent
     *
     * @return array
     * @private
     */
    private static function _buildTreeTerms(
        mixed $term,
        bool $hide_empty = true,
        int $depth = 0,
        mixed $selected_request = null,
        ?int $disabled_parent = null,
        bool $only_parent = false
    ): array {
        $options = [];

        if ($term?->term_id) {

            $prefix = str_repeat('— ', $depth);
            $selected = '';

            if (!is_array($selected_request)) {
                $selected = ' ' . selected($selected_request, $term->term_id, false);
            } elseif (in_array($term->term_id, $selected_request, false)) {
                $selected = ' selected="selected"';
            }

            $disabled = '';
            if (isset($disabled_parent) && $term?->parent === $disabled_parent) {
                $disabled = ' disabled="disabled"';
            }

            $options[] = [
                'value' => $term?->term_id,
                'label' => $prefix . $term?->name,
                'selected' => !empty($selected),
                'disabled' => !empty($disabled),
            ];

            if (!$only_parent) {
                $child_terms = get_terms([
                    'taxonomy' => $term?->taxonomy,
                    'hide_empty' => $hide_empty,
                    'parent' => $term?->term_id,
                ]);

                if (!empty($child_terms) && !is_wp_error($child_terms)) {
                    foreach ($child_terms as $child_term) {

                        // Append child options directly to the array
                        $options = [
                            ...$options,
                            ...self::_buildTreeTerms($child_term, $hide_empty, $depth + 1, $selected_request, $disabled_parent),
                        ];
                    }
                }
            }
        }

        return $options;
    }

	// -------------------------------------------------------------

    /**
     * @param $query
     * @param bool $get
     *
     * @return void
     */
    public static function paginateLinks($query = null, bool $get = false): void
    {
        global $wp_query;

        $query = $query ?: $wp_query;

        // Ensure the query is valid and has multiple pages
        if (!$query || $query->max_num_pages <= 1) {
            return;
        }

        // Setting up default values based on the current URL
        $pagenum_link = html_entity_decode(get_pagenum_link());
        $url_parts = explode('?', $pagenum_link, 2);

        // Append the format placeholder to the base URL
        $pagenum_link = trailingslashit($url_parts[0]) . '%_%';

        $current = max(1, get_query_var('paged'));
        $base = $pagenum_link;

        if ($get) {
            $base = add_query_arg('page', '%#%');
        }

        if (!empty($_GET['page']) && $get) {
            $current = (int) $_GET['page'];
        }

        // For more options and info views the docs for paginate_links()
        // http://codex.wordpress.org/Function_Reference/paginate_links
        $paginate_links = paginate_links([
            'base' => $base,
            'current' => $current,
            'total' => $query->max_num_pages,
            'end_size' => 1,
            'mid_size' => 2,
            'prev_next' => true,
            'prev_text' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" fill="currentColor"><path d="M9.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l192 192c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L77.3 256 246.6 86.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-192 192z"/></svg>',
            'next_text' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" fill="currentColor"><path d="M310.6 233.4c12.5 12.5 12.5 32.8 0 45.3l-192 192c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3L242.7 256 73.4 86.6c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0l192 192z"/></svg>',
            'type' => 'list',
        ]);

        $paginate_links = str_replace(
            [
                "<ul class='page-numbers'>",
                '<li><span class="page-numbers dots">&hellip;</span></li>',
                '<li><span aria-current="page" class="page-numbers current">',
                '</span></li>',
            ],
            [
                '<ul class="pagination page-numbers">',
                '<li class="ellipsis"></li>',
                '<li class="current"><span aria-current="page" class="sr-only">You\'re on page </span>',
                '</li>',
            ],
            $paginate_links
        );

        $paginate_links = preg_replace(['/page-numbers\s*/', '/\s*class=""/'], '', $paginate_links);

        // Display the pagination if more than one page is found.
        if ($paginate_links) {
            $paginate_links = '<nav class="nav-pagination" aria-label="Pagination">' . $paginate_links . '</nav>';

            echo $paginate_links;
        }
    }

    // -------------------------------------------------------------
}
