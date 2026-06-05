<?php
/**
 * Update version.
 *
 * @package Woo Quick View
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}  // if direct access.

update_option( 'woo_quick_view_version', '2.1.0' );
update_option( 'woo_quick_view_db_version', '2.1.0' );

$old_settings = get_option( '_sp_wqv_options' );

/**
 * Update settings.
 */
update_option( '_sp_wqvpro_options', $old_settings );
