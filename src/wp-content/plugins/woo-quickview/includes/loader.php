<?php
/**
 * This class defines all code necessary to run during the plugin's public functionality.
 *
 * @since      1.0.7
 * @package    Woo_Quick_View
 * @subpackage Woo_Quick_View/includes
 * @author     ShapedPlugin <support@shapedplugin.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}  // if direct access.

/**
 * The Loader Class
 *
 * @package Woo_Quick_View
 *
 * @since 1.0
 */
class SP_WQV_Loader {

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {
		require_once SP_WQV_PATH . 'public/views/scripts.php';
		require_once SP_WQV_PATH . 'public/views/shortcode.php';
		require_once SP_WQV_PATH . 'includes/class-woo-quick-view-updates.php';
		require_once SP_WQV_PATH . 'admin/views/notices/review.php';
		require_once SP_WQV_PATH . 'admin/help-page/help.php';
	}
}

new SP_WQV_Loader();
