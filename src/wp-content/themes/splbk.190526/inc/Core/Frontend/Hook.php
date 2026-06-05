<?php

namespace HD\Core\Frontend;

use HD\Utilities\Traits\Singleton;

\defined('ABSPATH') || die;

/**
 * Hook Class
 *
 * @author Gaudev
 */
final class Hook
{
    use Singleton;

    /* ---------- CONSTRUCT ----------------------------------------------- */

    private function init(): void
    {

        // -----------------------------------------------
        // wp_head
        // -----------------------------------------------
        add_action('wp_head', [$this, 'wp_head_action'], 1);
        add_action('wp_head', [$this, 'other_head_action'], 10);
        add_action('wp_head', [$this, 'external_fonts_action'], 11);

        // -----------------------------------------------
        // hd_header_before_action
        // -----------------------------------------------
        add_action('hd_header_before_action', [$this, 'skip_to_content_link_action'], 2);
        add_action('hd_header_before_action', [$this, 'off_canvas_menu_action'], 11);

        // -----------------------------------------------
        // hd_header_action
        // -----------------------------------------------
        add_action('hd_header_action', [$this, 'construct_header_action'], 10);

        //	add_action('masthead', [$this, '_masthead_top_header'], 12);
        add_action('masthead', [$this, '_masthead_header'], 13);
        add_action('masthead', [$this, '_masthead_bottom_header'], 14);
        add_action('masthead', [$this, '_masthead_custom'], 98);

        // -----------------------------------------------
        // hd_header_after_action
        // -----------------------------------------------

        // -----------------------------------------------
        // hd_site_content_before_action
        // -----------------------------------------------

        // -----------------------------------------------
        // wp_footer
        // -----------------------------------------------
        add_action('wp_footer', [$this, 'wp_footer_action'], 32);
        add_action('wp_footer', [$this, 'wp_footer_custom_js_action'], 99);
        add_action('wp_footer', [$this, 'popup_promo_output'], 100);
        add_action('wp_footer', [$this, 'cookie_consent_output'], 101);

        // -----------------------------------------------
        // hd_footer_after_action
        // -----------------------------------------------

        // -----------------------------------------------
        // hd_footer_action
        // -----------------------------------------------
        add_action('hd_footer_action', [$this, 'construct_footer_action'], 10);

        add_action('construct_footer', [$this, '_construct_footer_cta'], 10);
        add_action('construct_footer', [$this, '_construct_footer_columns'], 11);
        add_action('construct_footer', [$this, '_construct_footer_credit'], 12);
        add_action('construct_footer', [$this, '_construct_footer_custom'], 98);

        // -----------------------------------------------
        // hd_footer_before_action
        // -----------------------------------------------

        // -----------------------------------------------
        // hd_site_content_after_action
        // -----------------------------------------------

        // -----------------------------------------------
        // wp_enqueue_scripts
        // -----------------------------------------------
        add_action('wp_enqueue_scripts', [$this, 'custom_css_action'], 99);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_popup_promo_assets'], 98);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_seasonal_effect_assets'], 97);

        // Seasonal effect output in footer
        add_action('wp_footer', [$this, 'seasonal_effect_output'], 5);

        // --------------------------------------------------
        // enqueue_assets_extra
        // --------------------------------------------------
        add_action('enqueue_assets_extra', static function () {});

        // --------------------------------------------------
        // `template-page-home.php` file
        // --------------------------------------------------
        add_action('enqueue_assets_template_page_home', static function () {
            $version = \HD_Helper::version();

            \HD_Asset::enqueueStyle('home-css', ASSETS_URL . 'css/components/home.css', ['index-css'], $version);
            \HD_Asset::enqueueScript('home-js', ASSETS_URL . 'js/components/home.js', ['index-js'], $version, true, ['module', 'defer']);
        });
        add_action('enqueue_assets_template_page_cohoi', static function () {
            $version = \HD_Helper::version();

            \HD_Asset::enqueueStyle('cohoi-css', ASSETS_URL . 'css/components/cohoi.css', ['index-css'], $version);
        });
        add_action('enqueue_assets_template_page_landing_cohoi', static function () {
            $version = \HD_Helper::version();

            \HD_Asset::enqueueStyle('landing-cohoi-css', ASSETS_URL . 'css/components/landing-cohoi.css', ['index-css'], $version);
        });
        add_action('enqueue_assets_template_page_about_us', static function () {
            $version = \HD_Helper::version();

            \HD_Asset::enqueueStyle('about-css', ASSETS_URL . 'css/components/about.css', ['index-css'], $version);
            // Load home JS for flexible content sections (sliders, etc.)
            \HD_Asset::enqueueStyle('home-css', ASSETS_URL . 'css/components/home.css', ['index-css'], $version);
            \HD_Asset::enqueueScript('home-js', ASSETS_URL . 'js/components/home.js', ['index-js'], $version, true, ['module', 'defer']);
        });
    }

    /* -------------------------------------------------------------------- */
    /* ---------- PUBLIC -------------------------------------------------- */
    /* -------------------------------------------------------------------- */

    public function wp_head_action(): void
    {
        //echo '<meta name="viewport" content="user-scalable=yes, width=device-width, initial-scale=1.0, maximum-scale=2.0, minimum-scale=1.0" />';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0" />';

        if (is_singular() && pings_open()) {
            printf('<link rel="pingback" href="%s" />', esc_url(get_bloginfo('pingback_url')));
        }
    }

    // -----------------------------------------------

    public function other_head_action(): void
    {
        // manifest.json
        if (is_file(ABSPATH . 'manifest.json')) {
            printf('<link rel="manifest" href="%s" />', esc_url(home_url('manifest.json')));
        }

        // Theme color
        $theme_color = \HD_Helper::getThemeMod('theme_color_setting');
        if ($theme_color) {
            printf('<meta name="theme-color" content="%s" />', \HD_Helper::escAttr($theme_color));
        }
    }

    // -----------------------------------------------

    public function external_fonts_action(): void
    {
        echo <<<HTML
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,200..900;1,200..900&display=swap" rel="stylesheet">
        HTML;
    }

    // -----------------------------------------------

    public function skip_to_content_link_action(): void
    {
        printf(
            '<a class="screen-reader-text skip-link" href="#site-content" title="%1$s">%2$s</a>',
            esc_attr__('Skip to content', TEXT_DOMAIN),
            esc_html__('Skip to content', TEXT_DOMAIN)
        );
    }

    // -----------------------------------------------

    public function off_canvas_menu_action(): void
    {
        \HD_Helper::blockTemplate('parts/blocks/off-canvas');
    }

    // -----------------------------------------------

    public function construct_header_action(): void
    {
        /**
         * @see self::_masthead_top_header - 12
         * @see self::_masthead_header - 13
         * @see self::_masthead_bottom_header - 14
         * @see self::_masthead_custom - 98
         */
        do_action('masthead');
    }

    // -----------------------------------------------

    public function _masthead_top_header(): void
    {
        $header_broadcast = \HD_Helper::getField('header_broadcast', 'option');
        $gr_content = $header_broadcast['gr_content'] ?? '';
        if (empty($gr_content)) {
            return;
        }
?>
        <div class="header-broadcast">
            <div class="container">
                <?= $gr_content ?>
            </div>
        </div>
    <?php
    }

    // -----------------------------------------------

    public function _masthead_header(): void
    {
        $header_hotline = \HD_Helper::getField('header_hotline', 'option');
        $gr_icon = $header_hotline['gr_icon'] ?? '';
        $gr_hotline = $header_hotline['gr_hotline'] ?? '';
        $gr_hotline_txt = $header_hotline['gr_hotline_txt'] ?? $gr_hotline;

    ?>
        <!-- Mobile Top Header -->
        <div class="mobile-top-header">
            <div class="container flex flex-x">
                <nav class="mobile-top-nav">
                    <?php
                    echo \HD_Helper::doShortcode('horizontal_menu', [
                        'location' => 'top-nav',
                        'extra_class' => 'mobile-top-menu',
                        'depth' => 1,
                    ]);
                    ?>
                </nav>
                <div class="mobile-lang-switcher">
                    <?php echo \HD_Helper::doShortcode('spl_lang_switcher'); ?>
                </div>
            </div>
        </div>

        <div id="masthead" class="masthead">
            <div class="container flex flex-x">
                <?php echo \HD_Helper::doShortcode('off_canvas_button', ['hide_if_desktop' => 1]); ?>
                <?php echo \HD_Helper::siteTitleOrLogo(); ?>
                <div class="header-content">
                    <a class="name_compa" href="<?= home_url('/') ?>">
                        <span> <?php echo \HD_Helper::pll_text('BLUERA VIỆT NHẬT', 'BLUERA VIET NHAT'); ?></span>
                    </a>
                    <div class="menu_top">
                        <nav class="nav" id="top-nav">
                            <?php
                            echo \HD_Helper::doShortcode('horizontal_menu', [
                                'location' => 'top-nav',
                                'extra_class' => 'top-nav',
                                'depth' => 1,
                            ]);
                            ?>
                        </nav>
                    </div>
                    <?php echo \HD_Helper::doShortcode('inline_search'); ?>

                    <?php if ($gr_hotline): ?>
                        <a class="header-hotline hotline" href="tel:<?= $gr_hotline ?>" title="<?= esc_attr($gr_hotline_txt) ?>">
                            <span> HOTLINE: </span>
                            <span><?= $gr_hotline_txt ?></span>
                        </a>
                    <?php endif; ?>

                    <?php //\HD_Helper::blockTemplate( 'parts/blocks/woocommerce/order-history' ); 
                    ?>
                    <?php // 
                    ?>
                    <?php // \HD_Helper::blockTemplate('parts/blocks/woocommerce/wishlist-icon'); 
                    ?>
                    <div class="is_mobile_header">
                        <?php
                        // \HD_Helper::doShortcode('spl_lang_switcher'); // Moved to mobile-top-header
                        //	\HD_Helper::blockTemplate('parts/blocks/woocommerce/account-menu');
                        \HD_Helper::blockTemplate('parts/blocks/woocommerce/mini-cart');
                        ?>
                        <!-- Mobile Product Menu Button -->
                        <button type="button" class="mobile-product-menu-btn"
                            aria-label="<?= esc_attr__('Sản phẩm', 'flavor') ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="7" height="7"></rect>
                                <rect x="14" y="3" width="7" height="7"></rect>
                                <rect x="14" y="14" width="7" height="7"></rect>
                                <rect x="3" y="14" width="7" height="7"></rect>
                            </svg>
                            <span><?= __('Sản phẩm', 'flavor') ?></span>
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <!-- Mobile Product Menu Popup -->
        <div class="mobile-product-popup" aria-hidden="true">
            <div class="mobile-product-popup__overlay"></div>
            <div class="mobile-product-popup__content">
                <div class="mobile-product-popup__header">
                    <h3 class="mobile-product-popup__title"><?= __('Danh mục sản phẩm', 'flavor') ?></h3>
                    <button type="button" class="mobile-product-popup__close" aria-label="<?= esc_attr__('Đóng', 'flavor') ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
                <div class="mobile-product-popup__body">
                    <nav class="mobile-product-nav">
                        <?php
                        echo \HD_Helper::doShortcode('vertical_menu', [
                            'location' => 'product-nav',
                            'extra_class' => 'mobile-product-menu',
                        ]);
                        ?>
                    </nav>
                </div>
            </div>
        </div>
    <?php
    }

    // -----------------------------------------------

    public function _masthead_bottom_header(): void
    {
    ?>
        <div class="header-navigation">
            <div class="container flex flex-x">
                <div class="row">
                    <div class="left">
                        <?php // echo \HD_Helper::doShortcode('categories_menu', ['title' => __('Product Categories', TEXT_DOMAIN)]); 
                        ?>
                        <nav class="nav" id="product-nav">
                            <?php
                            echo \HD_Helper::doShortcode('vertical_menu', [
                                'location' => 'product-nav',
                                'extra_class' => 'product-nav',

                            ]);
                            ?>
                        </nav>
                    </div>
                    <div class="right">
                        <div class="cent-r">
                            <nav class="nav" id="main-nav">
                                <?php
                                echo \HD_Helper::doShortcode('horizontal_menu', [
                                    'location' => 'main-nav',
                                    'extra_class' => 'main-nav',
                                ]);
                                ?>
                            </nav>
                            <?php echo \HD_Helper::doShortcode('spl_lang_switcher'); ?>
                            <?php \HD_Helper::blockTemplate('parts/blocks/woocommerce/mini-cart'); ?>
                        </div>

                        <?php \HD_Helper::blockTemplate('parts/blocks/woocommerce/account-menu'); ?>
                    </div>
                </div>

            </div>
        </div>
    <?php
    }




    // -----------------------------------------------

    public function _masthead_custom(): void {}

    // -----------------------------------------------

    public function wp_footer_action(): void
    {
        if (apply_filters('hd_back_to_top_filter', true)) {
            echo apply_filters(
                'hd_back_to_top_output_filter',
                sprintf(
                    '<a title="%1$s" aria-label="%1$s" rel="nofollow" href="#" class="back-to-top toTop" data-scroll-speed="%2$s" data-scroll-start="%3$s">%4$s</a>',
                    esc_attr__('Scroll back to top', TEXT_DOMAIN),
                    absint(apply_filters('hd_back_to_top_scroll_speed_filter', 400)),
                    absint(apply_filters('hd_back_to_top_scroll_start_filter', 300)),
                    '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 24 24"><g fill="none"><path d="M8.47 4.22a.75.75 0 0 0 0 1.06L15.19 12l-6.72 6.72a.75.75 0 1 0 1.06 1.06l7.25-7.25a.75.75 0 0 0 0-1.06L9.53 4.22a.75.75 0 0 0-1.06 0z" fill="currentColor"></path></g></svg>'
                )
            );
        }
    }

    // -----------------------------------------------

    public function construct_footer_action(): void
    {
        /**
         * @see self::_construct_footer_cta - 10
         * @see self::_construct_footer_columns - 11
         * @see self::_construct_footer_credit - 12
         * @see self::_construct_footer_custom - 32
         */
        do_action('construct_footer');
    }

    // -----------------------------------------------

    public function _construct_footer_cta(): void
    {
        $footer_banner = \HD_Helper::getField('footer_cta_button', 'option');
        $gr_content = $footer_banner['gr_content'] ?? '';

        if (empty($gr_content)) {
            return;
        }

    ?>
        <div class="section footer-cta-section">
            <div class="container">
                <?= $gr_content ?>
            </div>
        </div>
    <?php
    }

    // -----------------------------------------------

    public function _construct_footer_columns(): void
    {
        // Detect current Polylang language
        $lang = function_exists('pll_current_language') ? pll_current_language() : 'vi';
        $suffix = ($lang === 'en') ? '_en' : '';

        // Dynamic field names depending on language
        $footer_menu1 = \HD_Helper::getField('footer_menu1' . $suffix, 'option');
        $footer_menu2 = \HD_Helper::getField('footer_menu2' . $suffix, 'option');
        $footer_menu3 = \HD_Helper::getField('footer_menu3' . $suffix, 'option');
        $footer_newsletter = \HD_Helper::getField('footer_newsletter' . $suffix, 'option');
        $footer_infomation = \HD_Helper::getField('footer_infomation' . $suffix, 'option');
        $company_name = ($lang === 'en') ? 'BLUERA VIET NHAT' : 'BLUERA VIỆT NHẬT';
    ?>
        <div id="footer-columns" class="footer-columns">
            <div class="row-2">
                <div class="container flex flex-x gap">
                    <div class="cell cell-menu flex flex-x gap">
                        <?php if ($footer_newsletter):
                            $gr_title = $footer_newsletter['gr_title'] ?? '';
                            $list_thong_tin = $footer_newsletter['list_thong_tin'] ?? '';
                        ?>
                            <div class="menu-info">
                                <div class="logo-footer">
                                    <?php echo \HD_Helper::siteTitleOrLogo(); ?>
                                    <div class="name_company">

                                        <span> <?php echo $company_name; ?></span>
                                    </div>
                                </div>
                                <div class="cell-newsletter">
                                    <?php echo !empty($gr_title) ? '<p class="footer-newsletter-title">' . esc_html($gr_title) . '</p>' : ''; ?>
                                    <ul class="list_thongtin">
                                        <?php
                                        if ($list_thong_tin):
                                            foreach ($list_thong_tin as $thong_tin):
                                                $info = $thong_tin['info'] ?? '';
                                        ?>
                                                <li><?php echo $info; ?></li>
                                        <?php endforeach;
                                        endif; ?>
                                    </ul>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="cell right">
                            <div class="r1 flex flex-x gap">
                                <?php foreach ([$footer_menu1, $footer_menu2, $footer_menu3] as $key => $menu):
                                    $gr_title = $menu['gr_title'] ?? '';
                                    $gr_menu = $menu['gr_menu'] ?? '';
                                ?>
                                    <div class="menu-<?php echo $key; ?> menu-ft">
                                        <?php echo !empty($gr_title) ? '<p class="footer-title">' . esc_html($gr_title) . '</p>' : ''; ?>
                                        <?php echo !empty($gr_menu) ? '<div class="footer-menu">' . wp_nav_menu(['menu' => $gr_menu, 'echo' => false]) . '</div>' : ''; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="r2 flex flex-x gap">
                                <?php if ($footer_infomation):
                                    foreach ($footer_infomation as $footer_info):
                                        $re_icon = '<div class="icon">' . $footer_info['re_icon'] . '</div>' ?? 0;
                                        $re_title = $footer_info['re_title'] ?? '';
                                        $re_desc = $footer_info['re_desc'] ?? '';
                                        $re_link = $footer_info['re_link'] ?? [];

                                        $content = $re_icon;
                                        $content .= '<span class="info-right">';
                                        $re_title && $content .= '<span class="link-target">' . $re_title . '</span>';
                                        $re_title && $re_desc && $content .= '<br>';
                                        $re_desc && $content .= '<strong class="info-target">' . $re_desc . '</strong>';
                                        $content .= '</span>';

                                        if (!$content)
                                            continue;
                                ?>
                                        <div class="cell">
                                            <div class="item">
                                                <?php echo \HD_Helper::ACFLinkWrap($content, $re_link, 'item-link'); ?>
                                            </div>
                                        </div>
                                <?php endforeach;
                                endif; ?>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php
    }

    // -----------------------------------------------

    public function _construct_footer_credit(): void
    {
    ?>
        <div id="footer-credit" class="footer-credit">
            <div class="container flex flex-x gap">
                <?php

                $lang = function_exists('pll_current_language') ? pll_current_language() : 'vi';
                $suffix = ($lang === 'en') ? '_en' : '';

                $footer_credit = \HD_Helper::getThemeMod('footer_credit_setting');
                $footer_credit = !empty($footer_credit) ? esc_html($footer_credit) : '&copy; ' . date('Y') . ' ' . get_bloginfo('name') . '. ' . esc_html__('All rights reserved.', TEXT_DOMAIN);
                $img_bct = \HD_Helper::getField('img_bct_ft', 'option');
                $link_bct = \HD_Helper::getField('link_bct_ft', 'option');

                $info_copyright = \HD_Helper::getField('info_copyright' . $suffix, 'option');
                ?>
                <div class="cell left">
                    <a href="<?= $link_bct; ?>" title="Thông báo Bộ Công Thương" target="_blank" rel="noopener noreferrer">
                        <?php echo wp_get_attachment_image($img_bct, 'medium', false, ['class' => 'bct-ft']); ?>
                    </a>
                </div>
                <div class="cell auto center">
                    <p><?= $info_copyright ?></p>
                    <p class="copyright"><?php echo apply_filters('hd_footer_credit_filter', $footer_credit); ?></p>
                    <?php echo \HD_Helper::doShortcode('horizontal_menu', ['location' => 'policy-nav', 'depth' => 1]); ?>
                </div>
                <div class="cell right">
                    <div class="social-links">
                        <span class="txt"><?= __('Follow Bluera', TEXT_DOMAIN); ?></span>
                        <?php echo \HD_Helper::doShortcode('social_menu'); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    // -----------------------------------------------

    public function _construct_footer_custom(): void {}

    // -----------------------------------------------

    public function wp_footer_custom_js_action(): void
    {
        ob_start();

        //-------------------------------------------------
        // Single page
        //-------------------------------------------------

        if (is_single() && $ID = get_the_ID()):
        ?>
            <script>
                document.addEventListener('DOMContentLoaded', async () => {
                    let postID = <?= $ID ?>;
                    const dateEl = document.querySelector('section.singular .meta > .date');
                    const viewsEl = document.querySelector('section.singular .meta > .views');

                    if (typeof window.hdConfig !== 'undefined') {
                        const endpointURL = window.hdConfig.restApiUrl + 'single/track_views';
                        try {
                            const resp = await fetch(endpointURL, {
                                method: 'POST',
                                credentials: 'same-origin',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-WP-Nonce': window.hdConfig.restToken,
                                },
                                body: JSON.stringify({
                                    id: postID
                                })
                            });
                            const json = await resp.json();
                            if (json.success) {
                                if (dateEl) dateEl.textContent = json.date;
                                if (viewsEl) viewsEl.textContent = json.views;
                            }
                        } catch (err) {}
                    }
                });
            </script>
<?php endif;

        $content = ob_get_clean();
        if ($content) {
            echo \HD_Helper::JSMinify($content, true);
        }
    }

    // -----------------------------------------------

    public function custom_css_action(): void
    {
        $css = new \HD_CSS();

        //-------------------------------------------------
        // Breadcrumb
        //-------------------------------------------------

        $object = get_queried_object();

        $breadcrumb_max = \HD_Helper::getThemeMod('breadcrumb_max_height_setting', 0);
        $breadcrumb_min = \HD_Helper::getThemeMod('breadcrumb_min_height_setting', 0);
        $breadcrumb_bgcolor = \HD_Helper::getThemeMod('breadcrumb_bgcolor_setting');

        if ($breadcrumb_max > 0 || $breadcrumb_min > 0 || $breadcrumb_bgcolor) {
            $css->set_selector('.section.section-breadcrumb');
        }

        $breadcrumb_min && $css->add_property('min-height', $breadcrumb_min . 'px !important');
        $breadcrumb_max && $css->add_property('max-height', $breadcrumb_max . 'px !important');
        $breadcrumb_bgcolor && $css->add_property('background-color', $breadcrumb_bgcolor . ' !important');

        $breadcrumb_title_color = \HD_Helper::getField('breadcrumb_title_color', $object) ?: \HD_Helper::getThemeMod('breadcrumb_color_setting');

        if ($breadcrumb_title_color) {
            $css->set_selector('.section.section-breadcrumb .breadcrumb-title')
                ->add_property('color', $breadcrumb_title_color . ' !important');
        }

        //-------------------------------------------------
        // Header
        //-------------------------------------------------

        $header_broadcast = \HD_Helper::getField('header_broadcast', 'option');
        $gr_bgcolor = $header_broadcast['gr_bgcolor'] ?? '';
        $gr_color = $header_broadcast['gr_color'] ?? '';

        if ($gr_bgcolor) {
            $css->set_selector('.header-broadcast')
                ->add_property('background-color', $gr_bgcolor . ' !important');
        }

        if ($gr_color) {
            $css->set_selector('.header-broadcast')
                ->add_property('color', $gr_color . ' !important');

            $css->set_selector('.header-broadcast a')
                ->add_property('color', $gr_color . ' !important');
        }

        //-------------------------------------------------
        // Header
        //-------------------------------------------------

        $footer_cta = \HD_Helper::getField('footer_cta_button', 'option');
        $gr_bgcolor = $footer_cta['gr_bgcolor'] ?? '';
        $gr_color = $footer_cta['gr_color'] ?? '';

        if ($gr_bgcolor) {
            $css->set_selector('.footer-cta-section')
                ->add_property('background-color', $gr_bgcolor . ' !important');
        }

        if ($gr_color) {
            $css->set_selector('.footer-cta-section')
                ->add_property('color', $gr_color . ' !important');

            $css->set_selector('.footer-cta-section a')
                ->add_property('color', $gr_color . ' !important');
        }

        // -----------------------------------------------

        $css_output = $css->css_output();
        if ($css_output) {
            \HD_Asset::inlineStyle('index-css', $css_output);
        }
    }

    // -----------------------------------------------
    // Popup Promo Assets
    // -----------------------------------------------

    public function enqueue_popup_promo_assets(): void
    {
        // Check if popup is enabled
        $popup_enabled = \HD_Helper::getField('popup_enabled', 'option');
        if (!$popup_enabled) {
            return;
        }

        $version = \HD_Helper::version();

        // Enqueue CSS
        \HD_Asset::enqueueStyle(
            'popup-promo-css',
            ASSETS_URL . 'css/popup-promo.css',
            ['index-css'],
            $version
        );

        // Add inline CSS for popup trigger button in add-this fixed sidebar
        $button_css = '
		/* Popup trigger button in add-this fixed sidebar */
		.add-this .promo-popup-item {
			list-style: none;
		}
		.add-this .popup-trigger-btn {
			display: flex !important;
			align-items: center;
			justify-content: center;
			width: 48px;
			height: 48px;
			padding: 0;
			background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
			color: #fff !important;
			border: none;
			border-radius: 50%;
			cursor: pointer;
			transition: all 0.3s ease;
			box-shadow: 0 2px 8px rgba(59, 130, 246, 0.4);
		}
		.add-this .popup-trigger-btn:hover {
			background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
			transform: scale(1.1);
			box-shadow: 0 4px 12px rgba(59, 130, 246, 0.5);
		}
		.add-this .popup-trigger-btn svg {
			width: 24px;
			height: 24px;
			flex-shrink: 0;
			stroke: #fff;
		}
		/* Hide text in fixed sidebar, show only icon */
		.add-this .popup-trigger-text {
			display: none;
		}
		/* Mobile: add margin to avoid Subiz chat icon */
		@media (max-width: 768px) {
			.add-this .promo-popup-item {
				margin-right: 60px;
			}
		}
		';
        \HD_Asset::inlineStyle('popup-promo-css', $button_css);

        // Enqueue JS
        \HD_Asset::enqueueScript(
            'popup-promo-js',
            ASSETS_URL . 'js/popup-promo.js',
            ['index-js'],
            $version,
            true,
            ['defer']
        );
    }

    // -----------------------------------------------

    public function popup_promo_output(): void
    {
        // Check if popup is enabled
        $popup_enabled = \HD_Helper::getField('popup_enabled', 'option');
        if (!$popup_enabled) {
            return;
        }

        // Include popup template
        \HD_Helper::blockTemplate('parts/popup-template');
    }

    // -----------------------------------------------

    /**
     * Output Cookie Consent Banner
     */
    public function cookie_consent_output(): void
    {
        // Include cookie consent template
        \HD_Helper::blockTemplate('parts/blocks/cookie-consent');
    }

    // -----------------------------------------------

    // -----------------------------------------------
    // Global Seasonal Effect Assets
    // -----------------------------------------------

    public function enqueue_seasonal_effect_assets(): void
    {
        // Check if global seasonal effect is enabled
        $global_effect = \HD_Helper::getField('global_seasonal_effect', 'option');
        if (!$global_effect || $global_effect === 'none') {
            return;
        }

        $version = \HD_Helper::version();

        // Enqueue CSS
        \HD_Asset::enqueueStyle(
            'seasonal-effect-css',
            ASSETS_URL . 'css/seasonal-effect.css',
            ['index-css'],
            $version
        );

        // Enqueue JS
        \HD_Asset::enqueueScript(
            'seasonal-effect-js',
            ASSETS_URL . 'js/seasonal-effect.js',
            ['index-js'],
            $version,
            true,
            ['defer']
        );
    }

    // -----------------------------------------------

    public function seasonal_effect_output(): void
    {
        // Check if global seasonal effect is enabled
        $global_effect = \HD_Helper::getField('global_seasonal_effect', 'option');
        if (!$global_effect || $global_effect === 'none') {
            return;
        }

        // Include seasonal effect template
        \HD_Helper::blockTemplate('parts/seasonal-effect');
    }

    // -----------------------------------------------
}
