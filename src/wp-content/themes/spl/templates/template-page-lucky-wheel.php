<?php

/**
 * Template Name: Lucky Wheel Full Width
 * Template Post Type: page
 * 
 * Template cho trang Vòng Quay May Mắn - Full width, không sidebar
 */

// Remove default WordPress admin bar spacing
add_filter('show_admin_bar', '__return_false');

get_header();
?>

<style>
    /* Remove all default spacing */
    body.page-template-template-page-lucky-wheel {
        margin: 0;
        padding: 0;
    }

    /* Hide default page elements */
    .lucky-wheel-page .breadcrumbs,
    .lucky-wheel-page .page-header,
    .lucky-wheel-page .entry-header,
    .lucky-wheel-page .page-title,
    .lucky-wheel-page .entry-title,
    .wlwl_lucky_wheel_wrap.wlwl_lucky_wheel_active {
        display: none !important;
    }

    /* Full width container */
    .lucky-wheel-page {
        width: 100%;
        max-width: 100%;
        padding: 0;
        margin: 0;
    }

    /* Beautiful gradient background */
    .lucky-wheel-page .page-content {

        background-attachment: fixed;
        min-height: 100vh;
        padding: 60px 20px;
        position: relative;
        overflow: hidden;
    }

    /* Animated background pattern */
    .lucky-wheel-page .page-content::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-image:
            radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
            radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
        pointer-events: none;
    }

    /* Content wrapper */
    .lucky-wheel-page .content-wrapper {
        max-width: 1400px;
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }

    /* Ensure wheel is centered and visible */
    .lucky-wheel-page .wc-lucky-wheel-shortcode-container {
        margin: 0 auto;
        background: rgba(255, 255, 255, 0.95);
        border-radius: 20px;
        padding: 40px 20px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .lucky-wheel-page .page-content {
            padding: 30px 15px;
        }

        .lucky-wheel-page .wc-lucky-wheel-shortcode-container {
            padding: 30px 15px;
            border-radius: 15px;
        }
    }

    /* Hide sidebar if any */
    .lucky-wheel-page .sidebar,
    .lucky-wheel-page aside {
        display: none !important;
    }

    /* Make main content full width */
    .lucky-wheel-page .site-content,
    .lucky-wheel-page .content-area {
        width: 100% !important;
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    .page-template-template-page-lucky-wheel div#ez-toc-container {
        display: none;
    }

    .page-template-template-page-lucky-wheel .site-footer {
        margin-top: 0;
    }

    .wc-lucky-wheel-shortcode-container input.wc-lucky-wheel-shortcode-wheel-field {
        font-weight: 500;
    }
</style>

<main class="lucky-wheel-page">
    <div class="page-content">
        <div class="content-wrapper">
            <?php
            while (have_posts()) :
                the_post();

                // Output page content (shortcode will be here)
                the_content();

            endwhile;
            ?>
        </div>
    </div>
</main>

<?php
get_footer();
