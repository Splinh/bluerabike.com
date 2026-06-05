<?php

/**
 * Global Seasonal Effect Template
 * Displays falling effects (hoa mai, hoa dao, snow) across the entire website
 *
 * @author Gaudev
 */

\defined('ABSPATH') || die;

// Get ACF field
$global_effect = \HD_Helper::getField('global_seasonal_effect', 'option') ?: 'none';

// Exit if effect is disabled
if (!$global_effect || $global_effect === 'none') {
    return;
}
?>

<?php
// Get theme assets URL where compiled/copied images reside
$theme_url = get_stylesheet_directory_uri() . '/assets';
?>
<div id="global-seasonal-effect" class="global-seasonal-effect" data-effect="<?= esc_attr($global_effect) ?>" data-theme-url="<?= esc_url($theme_url) ?>" aria-hidden="true"></div>