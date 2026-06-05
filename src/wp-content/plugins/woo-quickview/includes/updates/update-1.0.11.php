<?php
/**
 * Update version.
 *
 * @package Woo Quick View
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}  // if direct access.

update_option( 'woo_quick_view_version', '1.0.11' );
update_option( 'woo_quick_view_db_version', '1.0.11' );

$old_settings = get_option( '_sp_wqv_options' );
$border_width = $old_settings['wqvpro_quick_view_button_border']['width'];
$old_settings['wqvpro_quick_view_button_border']['all'] = $border_width;
/**
 * Update settings.
 */
update_option( '_sp_wqv_options', $old_settings );
