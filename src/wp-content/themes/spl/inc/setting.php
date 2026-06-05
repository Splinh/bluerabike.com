<?php

/**
 * Theme Settings
 *
 * @author Gaudev
 */

\defined('ABSPATH') || die;

// --------------------------------------------------
// Menu location
// --------------------------------------------------

add_action('after_setup_theme', 'register_nav_menu_callback', 11);

function register_nav_menu_callback(): void
{
    register_nav_menus(
        [
            'main-nav'   => __('Primary Menu', TEXT_DOMAIN),
            //'second-nav' => __( 'Second Menu', TEXT_DOMAIN ),
            'mobile-nav' => __('Handheld Menu', TEXT_DOMAIN),
            'policy-nav' => __('Term Menu', TEXT_DOMAIN),
            'top-nav' => __('Top Menu', TEXT_DOMAIN),
            'product-nav' => __('Products Category Menu', TEXT_DOMAIN),


        ]
    );
}

// --------------------------------------------------
// Hook widgets_init
// --------------------------------------------------

add_action('widgets_init', 'register_sidebar_callback');
add_action('admin_footer-edit.php', function () {
    echo "<script>
    document.querySelectorAll('table.wp-list-table.fixed').forEach(t => t.classList.remove('fixed'));
    </script>";
});

function register_sidebar_callback(): void
{

    //----------------------------------------------------------
    // Homepage
    //----------------------------------------------------------

    //	register_sidebar(
    //		[
    //			'container'     => false,
    //			'id'            => 'home-sidebar',
    //			'name'          => __( 'Homepage', TEXT_DOMAIN ),
    //			'description'   => __( 'Widgets added here will appear in homepage.', TEXT_DOMAIN ),
    //			'before_widget' => '<div class="%2$s">',
    //			'after_widget'  => '</div>',
    //			'before_title'  => '<span>',
    //			'after_title'   => '</span>',
    //		]
    //	);

    //----------------------------------------------------------
    // Product Attributes
    //----------------------------------------------------------

    register_sidebar(
        [
            'container'     => false,
            'id'            => 'product-attributes-sidebar',
            'name'          => __('Product Attributes', TEXT_DOMAIN),
            'description'   => __('Widgets added here will appear in product archives sidebar.', TEXT_DOMAIN),
            'before_widget' => '<div class="%2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<span>',
            'after_title'   => '</span>',
        ]
    );

    //----------------------------------------------------------
    // Other...
    //----------------------------------------------------------

    // News sidebar
    register_sidebar(
        [
            'container'     => false,
            'id'            => 'news-sidebar',
            'name'          => __('News Sidebar', TEXT_DOMAIN),
            'description'   => __('Widgets added here will appear in news sidebar.', TEXT_DOMAIN),
            'before_widget' => '<div class="%2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<span>',
            'after_title'   => '</span>',
        ]
    );

    // Page sidebar
    register_sidebar(
        [
            'container'     => false,
            'id'            => 'page-sidebar',
            'name'          => __('Page Sidebar', TEXT_DOMAIN),
            'description'   => __('Widgets added here will appear in page sidebar.', TEXT_DOMAIN),
            'before_widget' => '<div class="%2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<span>',
            'after_title'   => '</span>',
        ]
    );

    // Archive sidebar
    register_sidebar(
        [
            'container'     => false,
            'id'            => 'archive-sidebar',
            'name'          => __('Archive Sidebar', TEXT_DOMAIN),
            'description'   => __('Widgets added here will appear in archive sidebar.', TEXT_DOMAIN),
            'before_widget' => '<div class="%2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<span>',
            'after_title'   => '</span>',
        ]
    );
}

// --------------------------------------------------
// Hook default scripts
// --------------------------------------------------

add_action('wp_default_scripts', 'wp_default_scripts_callback', 11, 1);

function wp_default_scripts_callback($scripts): void
{
    if (isset($scripts->registered['jquery']) && ! is_admin()) {
        $script = $scripts->registered['jquery'];
        if ($script->deps) {

            // Check whether the script has any dependencies

            // remove jquery-migrate
            $script->deps = array_diff($script->deps, ['jquery-migrate']);
        }
    }
}

// --------------------------------------------------
// Hook body_class
// --------------------------------------------------

add_filter('body_class', 'body_class_callback', 11, 1);

function body_class_callback(array $classes): array
{
    // Check whether we're in the customizer preview.
    if (is_customize_preview()) {
        $classes[] = 'customizer-preview';
    }

    foreach ($classes as $class) {
        if (
            str_contains($class, 'wp-custom-logo') ||
            str_contains($class, 'page-template-templates') ||
            str_contains($class, 'page-id-') ||
            str_contains($class, 'postid-') ||
            str_contains($class, 'single-format-standard') ||
            str_contains($class, 'no-customize-support')
        ) {
            $classes = array_diff($classes, [$class]);
        }
    }

    if (\HD_Helper::isWoocommerceActive()) {
        $classes[] = 'woocommerce';
    }

    // ...

    return $classes;
}

// --------------------------------------------------
// Hook post_class
// --------------------------------------------------

add_filter('post_class', 'post_class_callback', 11, 1);

function post_class_callback(array $classes): array
{
    // remove_sticky_class
    if (in_array('sticky', $classes, false)) {
        $classes   = array_diff($classes, ['sticky']);
        $classes[] = 'wp-sticky';
    }

    // remove 'tag-', 'category-' classes
    foreach ($classes as $class) {
        if (
            str_contains($class, 'tag-') ||
            str_contains($class, 'category-')
        ) {
            $classes = array_diff($classes, [$class]);
        }
    }

    return $classes;
}

// --------------------------------------------------
// Filter nav_menu_css_class
// --------------------------------------------------

add_filter('nav_menu_css_class', 'nav_menu_css_class_callback', 999, 4);

function nav_menu_css_class_callback($classes, $menu_item, $args, $depth): array
{
    if (! is_array($classes)) {
        $classes = [];
    }

    // Remove 'menu-item-type-', 'menu-item-object-' classes
    foreach ($classes as $class) {
        if (
            str_contains($class, 'menu-item-type-') ||
            str_contains($class, 'menu-item-object-') ||
            str_contains($class, 'menu-item') ||
            str_contains($class, 'menu-item-')
        ) {
            $classes = array_diff($classes, [$class]);
        }
    }

    if (1 === $menu_item->current || $menu_item->current_item_ancestor || $menu_item->current_item_parent) {
        $classes[] = 'active';
    }

    // li_class
    // li_depth_class

    if ($depth === 0) {
        if (! empty($args->li_class)) {
            $classes[] = $args->li_class;
        }

        return $classes;
    }

    if (! empty($args->li_depth_class)) {
        $classes[] = $args->li_depth_class;
    }

    return $classes;
}

// --------------------------------------------------
// Filter nav_menu_link_attributes
// --------------------------------------------------

add_filter('nav_menu_link_attributes', 'nav_menu_link_attributes_callback', 999, 4);

function nav_menu_link_attributes_callback($atts, $menu_item, $args, $depth): array
{
    // link_class
    // link_depth_class

    if ($depth === 0) {
        if (property_exists($args, 'link_class')) {
            $atts['class'] = esc_attr($args->link_class);
        }
    } elseif (property_exists($args, 'link_depth_class')) {
        $atts['class'] = esc_attr($args->link_depth_class);
    }

    // menu_link_class
    if (! empty($menu_item->menu_link_class)) {
        //		if ( ! empty( $atts['class'] ) ) {
        //			$atts['class'] .= ' ' . esc_attr( $menu_item->menu_link_class );
        //		} else {
        //			$atts['class'] = esc_attr( $menu_item->menu_link_class );
        //		}

        $atts['class'] = esc_attr($menu_item->menu_link_class);
    }

    return $atts;
}

// --------------------------------------------------
// Filter nav_menu_item_title
// --------------------------------------------------

add_filter('nav_menu_item_title', 'nav_menu_item_title_callback', 999, 4);

function nav_menu_item_title_callback($title, $item, $args, $depth)
{
    //	if ($args->theme_location === 'main-nav') {
    //		$title = '<span>' . $title . '</span>';
    //	}

    return $title;
}

// --------------------------------------------------
// query_vars
// --------------------------------------------------

add_filter('query_vars', 'query_vars_callback', 99, 1);

function query_vars_callback($vars): array
{
    $vars[] = 'page';
    $vars[] = 'paged';

    return $vars;
}

// --------------------------------------------------
// custom filter
// --------------------------------------------------

add_filter('hd_theme_settings_filter', 'hd_theme_settings_filter_callback', 99, 1);

function hd_theme_settings_filter_callback(array $arr): array
{
    static $setting_filter_cache = [];

    // Return a cached value if static caching is enabled and the value is already cached
    if (! empty($setting_filter_cache['hd_theme_setting'])) {
        return $setting_filter_cache['hd_theme_setting'];
    }

    $arr_new = [
        //
        // Customize table column information, table display content, etc.
        //
        'admin_list_table'     => [
            // Add ID to the admin category page.
            'term_row_actions'                => [
                'category',
                'post_tag',
            ],

            // Add ID to the admin post-page.
            'post_row_actions'                => [
                'user',
                'post',
                'page',
            ],

            // Terms thumbnail (term_thumb).
            'term_thumb_columns'              => [
                'category',
                //'post_tag',
            ],

            // Exclude thumb post_type columns.
            'post_type_exclude_thumb_columns' => [
                //'page',
                'product-badge',
            ],
        ],

        //
        // Custom post-type and taxonomy.
        //
        'post_type_terms'      => [
            'post' => 'category',
        ],

        //
        // Aspect Ratio.
        //
        'aspect_ratio'         => [
            'post_type_term'       => [
                'post',
                'video'
            ],
            'aspect_ratio_default' => [
                '1-1',
                '2-1',
                '3-2',
                '4-3',
                '16-9',
                '21-9',
            ],
        ],

        //
        // defer, delay script - default 5s.
        //
        'defer_script'         => [
            // defer.
            'contact-form-7' => 'defer',

            // delay.
            'comment-reply'  => 'delay',
            'wp-embed'       => 'delay',
        ],

        //
        // defer style.
        //
        'defer_style'          => [
            'dashicons',
            'contact-form-7',
        ],

        //
        // Admin menu sidebar
        //
        'admin_menu'           => [
            // hide admin menu
            'admin_hide_menu'             => [
                //'edit.php',
            ],

            // hide admin submenu
            'admin_hide_submenu'          => [
                //				'options-general.php' => [
                //					'options-discussion.php',
                //					'options-privacy.php',
                //				]
            ],

            // ignore user
            'admin_hide_menu_ignore_user' => [1],
        ],

        //
        // ACF menu
        //
        'acf_menu'             => [
            // ACF attributes in `menu` locations.
            'acf_menu_items_locations' => [
                'main-nav',
                'policy-nav',
                'product-nav'
            ],

            // ACF attributes `mega-menu` locations.
            'acf_mega_menu_locations'  => [
                'main-nav',
                'product-nav'
            ],
        ],

        //
        // LazyLoad
        //
        'lazyload_exclude'     => [
            'no-lazy',
            'skip-lazy',
        ],

        //
        // Custom Email list (mailto).
        //
        'custom_emails'        => [
            //			'contact'     => __( 'Contacts', TEXT_DOMAIN ),
        ],

        //
        // security
        //
        'security'             => [
            // Allowlist IPs Login Access
            'allowlist_ips_login_access'          => [],

            // Blocked IPs Access
            'blocked_ips_login_access'            => [],

            // IDs of users allowed changing custom-login, OTP settings v.v...
            'privileged_user_ids'                 => [1, 2],

            // List of admin IDs allowed to show 'hd-addons' plugins.
            'allowed_users_ids_show_plugins'      => [1, 2],

            // List of admin IDs allowed installing plugins.
            'allowed_users_ids_install_plugins'   => [1],

            // List of user IDs that are not allowed to be deleted.
            'disallowed_users_ids_delete_account' => [1],
        ],

        //
        // Social Links.
        //
        'social_follows_links' => [
            'facebook'  => [
                'name' => __('Facebook', TEXT_DOMAIN),
                'icon' => \HD_Helper::svg('facebook'),
                'url'  => '',
            ],
            'instagram' => [
                'name' => __('Instagram', TEXT_DOMAIN),
                'icon' => \HD_Helper::svg('instagram'),
                'url'  => '',
            ],
            'youtube'   => [
                'name' => __('Youtube', TEXT_DOMAIN),
                'icon' => \HD_Helper::svg('youtube'),
                'url'  => '',
            ],
            'x'         => [
                'name' => __('X (Twitter)', TEXT_DOMAIN),
                'icon' => \HD_Helper::svg('x'),
                'url'  => '',
            ],
            'tiktok'    => [
                'name' => __('Tiktok', TEXT_DOMAIN),
                'icon' => \HD_Helper::svg('tiktok'),
                'url'  => '',
            ],
            'telegram'  => [
                'name' => __('Telegram', TEXT_DOMAIN),
                'icon' => \HD_Helper::svg('telegram'),
                'url'  => '',
            ],
            'linkedin'  => [
                'name' => __('Linkedin', TEXT_DOMAIN),
                'icon' => \HD_Helper::svg('linkedin'),
                'url'  => '',
            ],
            'zalo'      => [
                'name' => __('Zalo', TEXT_DOMAIN),
                'icon' => \HD_Helper::svg('zalo'),
                'url'  => '',
            ],
            //			'hotline'   => [
            //				'name' => __( 'Hotline', TEXT_DOMAIN ),
            //				'icon' => \HD_Helper::svg( 'phone' ),
            //				'url'  => '',
            //			],
            //			'email'     => [
            //				'name' => __( 'Email', TEXT_DOMAIN ),
            //				'icon' => \HD_Helper::svg( 'envelope' ),
            //				'url'  => '',
            //			],
            'shopee'    => [
                'name' => __('Shopee', TEXT_DOMAIN),
                'icon' => \HD_Helper::svg('shopee'),
                'url'  => '',
            ],
        ],

        //
        // Contact Links.
        //
        'contact_links'        => [
            'tiktok'       => [
                'name'        => __('Tiktok', TEXT_DOMAIN),
                'icon'        => \HD_Helper::svg('tiktok'),
                'value'       => '',
                'placeholder' => __('Link tiktok', TEXT_DOMAIN),
                'target'      => '_blank',
                'class'       => 'tiktok',
            ],
            'messenger'    => [
                'name'        => __('Messenger', TEXT_DOMAIN),
                'icon'        => \HD_Helper::svg('messenger'),
                'value'       => '',
                'placeholder' => __('Link messenger', TEXT_DOMAIN),
                'target'      => '_blank',
                'class'       => 'messenger',
            ],
            'zalo'         => [
                'name'        => __('Zalo', TEXT_DOMAIN),
                'icon'        => \HD_Helper::svg('zalo'),
                'value'       => '',
                'placeholder' => '0123 456 789',
                'target'      => '_blank',
                'class'       => 'zalo',
            ],
            'hotline'      => [
                'name'        => __('Hotline', TEXT_DOMAIN),
                'icon'        => \HD_Helper::svg('phone'),
                'value'       => '',
                'placeholder' => '0123 456 789',
                'class'       => 'hotline',
            ],
            'contact_map'  => [
                'name'        => __('Bản đồ', TEXT_DOMAIN),
                'icon'        => \HD_Helper::svg('location'),
                'value'       => '',
                'placeholder' => __('Link google map', TEXT_DOMAIN),
                'target'      => '_blank',
                'class'       => 'contact-map',
            ],
            'contact_link' => [
                'name'        => __('Liên hệ', TEXT_DOMAIN),
                'icon'        => \HD_Helper::svg('contact'),
                'value'       => '',
                'placeholder' => __('Liên hệ', TEXT_DOMAIN),
                'class'       => 'contact-link',
            ],
        ],
    ];

    // --------------------------------------------------

    if (\HD_Helper::isWoocommerceActive()) {
        $arr_new['aspect_ratio']['post_type_term'][]                      = 'product';
        $arr_new['aspect_ratio']['post_type_term'][]                      = 'product_cat';
        $arr_new['admin_list_table']['term_row_actions'][]                = 'product_cat';
        $arr_new['admin_list_table']['post_type_exclude_thumb_columns'][] = 'product';
        $arr_new['post_type_terms']['product']                            = 'product_cat';
    }

    if (\HD_Helper::isCf7Active()) {
        $arr_new['admin_list_table']['post_type_exclude_thumb_columns'][] = 'wpcf7_contact_form';
    }

    // --------------------------------------------------

    // Merge the new array with the old array, prioritize the value of $arr
    $arr_new = array_merge($arr, $arr_new);

    // Add to static cache
    $setting_filter_cache['hd_theme_setting'] = $arr_new;

    return $arr_new;
}

// spl new performance settings
// --------------------------------------------------
// --------------------------------------------------
// --------------------------------------------------
/**
 * 🔹 1. Remove unneeded WP styles/scripts
 */
add_action('wp_enqueue_scripts', function () {
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('global-styles');
    wp_dequeue_style('classic-theme-styles');
    wp_dequeue_script('wp-embed');
}, 99);

/**
 * 🔹 2. WooCommerce optimization (keep mini-cart active)
 */
add_action('wp_enqueue_scripts', function () {

    // Chỉ giữ script tối cần cho mini-cart hoạt động
    if (class_exists('WooCommerce')) {
        wp_enqueue_script('wc-cart-fragments');
        wp_enqueue_script('woocommerce');
        wp_enqueue_script('wc-add-to-cart');
    }

    // Có thể bỏ style không cần thiết nếu bạn tự style
    if (!is_cart() && !is_checkout()) {
        wp_dequeue_style('woocommerce-smallscreen');
    }
}, 999);

/**
 * 🔹 3. Safe defer – exclude WooCommerce + jQuery scripts
 */



/**
 * 🔹 5. Lazyload images + decoding async
 */
add_filter('wp_get_attachment_image_attributes', function ($attr) {
    $attr['loading'] = 'lazy';
    $attr['decoding'] = 'async';
    return $attr;
}, 10);

add_filter('the_content', function ($content) {
    if (is_feed() || is_preview() || (defined('REST_REQUEST') && REST_REQUEST))
        return $content;

    return preg_replace('/<img(.*?)src=/', '<img$1loading="lazy" decoding="async" src=', $content);
}, 99);

/**
 * 🔹 6. Remove query strings
 */
add_filter('script_loader_src', 'bluera_remove_query_strings', 15);
add_filter('style_loader_src', 'bluera_remove_query_strings', 15);
function bluera_remove_query_strings($src)
{
    $parts = explode('?ver', $src);
    return $parts[0];
}

/**
 * 🔹 7. Disable emojis, embeds, XML-RPC
 */
add_action('init', function () {
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_action('wp_head', 'wp_oembed_add_discovery_links');
    remove_action('wp_head', 'wp_oembed_add_host_js');
    add_filter('xmlrpc_enabled', '__return_false');
});

/**
 * 🔹 8. Disable heartbeat API (optional)
 */
add_action('init', function () {
    wp_deregister_script('heartbeat');
});

/**
 * 🔹 9. Debug: LiteSpeed cache check
 */
add_action('wp_footer', function () {
    if (defined('LSCACHE_ENABLED') && LSCACHE_ENABLED) {
        echo "<!-- LiteSpeed Cache: HIT / Woo MiniCart Safe -->";
    }
});

add_action('init', function () {
    if (class_exists('WooCommerce')) {
        // Kích hoạt session sớm để tránh mất dữ liệu cart
        if (null === WC()->session && function_exists('wc_load_cart')) {
            wc_load_cart();
        }
    }
}, 5);

// ===============================================
// WP Rocket - Exclude YITH AJAX Filter scripts from optimization
// Fixes issue where filters don't appear for non-logged-in users
// ===============================================



// Don't cache shop pages for guests (optional - use if above doesn't work)
// add_filter('rocket_cache_reject_uri', function($uris) {
// 	$uris[] = '/san-pham/(.*)';
// 	$uris[] = '/product-category/(.*)';
// 	return $uris;
// });

add_action('wp_enqueue_scripts', function () {
    if (is_admin() || is_shop() || is_product_taxonomy() || is_product()) {
        return;
    }

    foreach (['ion.range-slider', 'yith-wcan-shortcodes'] as $handle) {
        wp_dequeue_style($handle);
        wp_dequeue_script($handle);
    }
}, 1005);

add_filter('wp_get_attachment_image_attributes', function ($attr) {
    $classes     = isset($attr['class']) ? explode(' ', $attr['class']) : [];
    $is_skip_lazy = in_array('skip-lazy', $classes, true) || (!empty($attr['fetchpriority']) && 'high' === $attr['fetchpriority']);

    if ($is_skip_lazy) {
        $attr['loading']       = 'eager';
        $attr['decoding']      = $attr['decoding'] ?? 'async';
        $attr['fetchpriority'] = $attr['fetchpriority'] ?? 'high';
        return $attr;
    }

    if (empty($attr['loading'])) {
        $attr['loading'] = 'lazy';
    }
    if (empty($attr['decoding'])) {
        $attr['decoding'] = 'async';
    }

    return $attr;
}, 10);

// Đặt priority tải cao cho ảnh LCP trên trang chủ
add_filter('wp_get_attachment_image_attributes', function ($attr, $attachment, $size) {
    // Chỉ áp dụng ở trang chủ (tùy site bạn chỉnh lại điều kiện)
    if (! is_front_page() && ! is_home()) {
        return $attr;
    }

    // Biến static để chỉ set cho 1 ảnh đầu tiên
    static $lcp_done = false;

    // Nếu đã set cho 1 ảnh rồi thì thôi
    if ($lcp_done) {
        return $attr;
    }

    // Chỉ ưu tiên ảnh đang được load "eager" (thường là ảnh LCP)
    if (isset($attr['loading']) && $attr['loading'] === 'eager') {
        $attr['fetchpriority'] = 'high';
        $lcp_done = true;
    }

    return $attr;
}, 20, 3);

// Ảnh sản phẩm đầu tiên: bỏ lazy + ưu tiên cao
add_filter('wp_get_attachment_image_attributes', function ($attr, $attachment, $size) {
    $lcp_thumb_id = $GLOBALS['NT_LCP_PRELOAD_THUMB_ID'] ?? null;

    if (!$lcp_thumb_id && !empty($GLOBALS['NT_LCP_FIRST_THUMB_ID'])) {
        $lcp_thumb_id = $GLOBALS['NT_LCP_FIRST_THUMB_ID'];
    }

    if (!$lcp_thumb_id || (int)$attachment->ID !== (int)$lcp_thumb_id) {
        return $attr;
    }

    $attr['fetchpriority'] = 'high';
    $attr['loading']       = 'eager';
    $attr['decoding']      = 'async';

    if (empty($attr['sizes'])) {
        $attr['sizes'] = '(max-width: 767px) 100vw, 400px';
    }

    if (!empty($attr['class'])) {
        $attr['class'] = trim(str_replace(
            ['lazy', 'lazyload', 'lazy-load'],
            '',
            $attr['class']
        )) . ' lcp-first';
    }

    return $attr;
}, 10, 3);

add_filter('woocommerce_product_get_image', function ($html, $product, $size) {
    $lcp_thumb_id = $GLOBALS['NT_LCP_PRELOAD_THUMB_ID'] ?? null;

    if (!$lcp_thumb_id && !empty($GLOBALS['NT_LCP_FIRST_THUMB_ID'])) {
        $lcp_thumb_id = $GLOBALS['NT_LCP_FIRST_THUMB_ID'];
    }

    if (!$lcp_thumb_id) {
        return $html;
    }

    $thumb_id = $product->get_image_id();
    if (!$thumb_id || (int)$thumb_id !== (int)$lcp_thumb_id) {
        return $html;
    }

    $html = str_replace(['loading="lazy"', "loading='lazy'"], 'loading="eager"', $html);
    $html = preg_replace('#\sdata-([a-zA-Z0-9\-]+)=("[^"]*"|\'[^\']*\')#', '', $html);

    if (strpos($html, 'fetchpriority=') === false) {
        $html = str_replace('<img ', '<img fetchpriority="high" ', $html);
    }

    $html = str_replace('<img ', '<img class="lcp-first" ', $html);

    return $html;
}, 10, 3);

add_action('wp_head', function () {
    if (!is_front_page() && !is_home()) {
        return;
    }

    if (!class_exists('WooCommerce')) {
        return;
    }

    $product_visibility_term_ids = wc_get_product_visibility_term_ids();
    if (empty($product_visibility_term_ids['featured'])) {
        return;
    }

    $args = [
        'post_type'           => 'product',
        'posts_per_page'      => 1,
        'post_status'         => 'publish',
        'no_found_rows'       => true,
        'ignore_sticky_posts' => true,
        'orderby'             => 'menu_order',
        'order'               => 'ASC',
        'fields'              => 'ids',
        'tax_query'           => [
            [
                'taxonomy' => 'product_visibility',
                'field'    => 'term_taxonomy_id',
                'terms'    => $product_visibility_term_ids['featured'],
                'operator' => 'IN',
            ],
        ],
    ];

    $products = get_posts($args);
    if (empty($products[0])) {
        return;
    }

    $thumb_id = get_post_thumbnail_id($products[0]);
    if (!$thumb_id) {
        return;
    }

    $GLOBALS['NT_LCP_PRELOAD_THUMB_ID'] = $thumb_id;

    $src    = wp_get_attachment_image_url($thumb_id, 'medium');
    $srcset = wp_get_attachment_image_srcset($thumb_id, 'medium');

    if (!$src) {
        return;
    }

    echo '<link rel="preload" as="image" href="' . esc_url($src) . '"';
    if ($srcset) {
        echo ' imagesrcset="' . esc_attr($srcset) . '"';
    }
    echo ' imagesizes="(max-width: 480px) 100vw, (max-width: 768px) 50vw, 400px"';
    echo ' fetchpriority="high">' . "\n";
}, 1);








function custom_post_css_js_metabox()
{
    add_meta_box(
        'custom_css_js_box',
        'Custom CSS & JS cho bài viết này',
        'custom_css_js_callback',
        'post', // áp dụng cho post. Muốn cho page thì thêm 'page'
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'custom_post_css_js_metabox');

// Giao diện meta box
function custom_css_js_callback($post)
{
    $custom_css = get_post_meta($post->ID, '_custom_post_css', true);
    $custom_js  = get_post_meta($post->ID, '_custom_post_js', true);

    echo '<p><strong>Custom CSS:</strong></p>';
    echo '<textarea style="width:100%;height:150px;" name="custom_post_css">' . esc_textarea($custom_css) . '</textarea>';

    echo '<p><strong>Custom JS:</strong></p>';
    echo '<textarea style="width:100%;height:150px;" name="custom_post_js">' . esc_textarea($custom_js) . '</textarea>';
}

// Lưu dữ liệu
function save_custom_post_css_js($post_id)
{
    if (array_key_exists('custom_post_css', $_POST)) {
        update_post_meta($post_id, '_custom_post_css', wp_kses_post($_POST['custom_post_css']));
    }
    if (array_key_exists('custom_post_js', $_POST)) {
        update_post_meta($post_id, '_custom_post_js', wp_kses_post($_POST['custom_post_js']));
    }
}
add_action('save_post', 'save_custom_post_css_js');


function print_custom_css_js_single()
{
    if (is_single()) {
        global $post;
        $custom_css = get_post_meta($post->ID, '_custom_post_css', true);
        $custom_js  = get_post_meta($post->ID, '_custom_post_js', true);

        if (!empty($custom_css)) {
            echo '<style id="custom-post-css-' . $post->ID . '">' . $custom_css . '</style>';
        }
        if (!empty($custom_js)) {
            echo '<script id="custom-post-js-' . $post->ID . '">(function(){' . $custom_js . '})();</script>';
        }
    }
}

add_action('wp_head', 'print_custom_css_js_single', 100);
add_action('wp_footer', 'print_custom_css_js_single', 100);
