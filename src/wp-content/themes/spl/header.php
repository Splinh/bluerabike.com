<?php

/**
 * The template for displaying the header.
 * This is the template that displays all the <head> section, opens the <body> tag and adds the site's header.
 *
 * @author Gaudev
 */

\defined('ABSPATH') || die;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">

<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-SZGYTRMLQV"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());
        gtag('config', 'G-SZGYTRMLQV');
    </script>
    <!-- Google Tag Manager -->
    <script>
        (function(w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start': new Date().getTime(),
                event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src =
                'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-PV6NX7L');
    </script>
    <!-- End Google Tag Manager -->
    <meta name="google-site-verification" content="ub8yAS-O1W4CwjV2Ku4rd91wlKEvTQWaTXZcPFAWodM" />
    <meta charset="<?php bloginfo('charset'); ?>" />
    <?php
    /**
     * HOOK: wp_head
     *
     * @see Hook::wp_head_action - 1
     * @see Hook::other_head_action - 10
     * @see Hook::external_fonts_action - 11
     */
    wp_head();
    ?>
</head>

<body <?php body_class(); ?> <?= \HD_Helper::microdata('body') ?>>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PV6NX7L"
            height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <?php
    /**
     * HOOK: wp_body_open
     *
     * @see CustomScript::body_scripts_top__hook - 99
     */
    do_action('wp_body_open');

    /**
     * HOOK: hd_header_before_action
     *
     * @see Hook::skip_to_content_link_action - 2
     * @see Hook::off_canvas_menu_action - 11
     */
    do_action('hd_header_before_action');
    ?>
    <header id="header" class="<?= apply_filters('hd_header_class_filter', 'site-header') ?>" <?= \HD_Helper::microdata('header') ?>>
        <?php
        /**
         * HOOK: hd_header_action
         *
         * @see Hook::construct_header_action - 10
         */
        do_action('hd_header_action');
        ?>
    </header><!-- #header -->
    <?php
    /**
     * HOOK: hd_header_after_action
     */
    do_action('hd_header_after_action');
    ?>
    <main class="main site-content" id="site-content" role="main">
        <?php
        /**
         * HOOK: hd_site_content_before_action
         */
        do_action('hd_site_content_before_action');
