<?php

/**
 * The template for displaying `homepage`
 * Template Name: Home page
 * Template Post Type: page
 *
 * @author Gaudev
 */

\defined('ABSPATH') || die;

// header
get_header('home');

if (have_posts()) {
    the_post();
}

if (post_password_required()) {
    echo get_the_password_form();
    get_footer('home');

    return;
}

// H1 for SEO - Site Title + Description (visually hidden but accessible)
?>
<h1 class="homepage-seo-heading sr-only"><?php echo esc_html(get_bloginfo('name')); ?> - <?php echo esc_html(get_bloginfo('description')); ?></h1>
<?php

$ACF                   = \HD_Helper::getFields(get_the_ID());
$home_flexible_content = ! empty($ACF['home_flexible_content']) ? (array) $ACF['home_flexible_content'] : false;
if ($home_flexible_content) {

    foreach ($home_flexible_content as $i => $section) {
        $section['id'] = $i;
        $acf_fc_layout = $section['acf_fc_layout'] ?? '';

        if ($acf_fc_layout) {
            \HD_Helper::blockTemplate('parts/home/' . $acf_fc_layout, $section);
        }
    }
}

// footer
get_footer('home');
