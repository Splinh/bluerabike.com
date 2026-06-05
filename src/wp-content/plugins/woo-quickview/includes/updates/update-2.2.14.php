<?php
/**
 * Update version.
 *
 * @package Woo Quick View
 * @subpackage Woo Quick View/Includes/Updates
 * @since 2.2.14
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}  // if direct access.

update_option( 'woo_quick_view_version', '2.2.14' );
update_option( 'woo_quick_view_db_version', '2.2.14' );

// Clear the transient.
if ( get_transient( 'wooqv_plugins' ) ) {
	delete_transient( 'wooqv_plugins' );
}
