<?php
/**
 * This class defines all code necessary to include framework .
 *
 * @since      1.0.7
 * @package    Woo_Quick_View
 * @subpackage Woo_Quick_View/includes
 * @author     ShapedPlugin <support@shapedplugin.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Woo Quick View- route class
 *
 * @since 1.0
 */
class SP_WQV_Router {

	/**
	 * Instance
	 *
	 * @var SP_WQV_Router single instance of the class
	 *
	 * @since 1.0
	 */
	protected static $_instance = null;


	/**
	 * Class Instance
	 *
	 * @since 1.0
	 *
	 * @static
	 *
	 * @return SP_WQV_Router
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Include the required files
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function includes() {
		include_once SP_WQV_PATH . 'includes/loader.php';
	}

	/**
	 * Function
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function sp_wqv_function() {
		include_once SP_WQV_PATH . 'includes/functions.php';
	}

	/**
	 * Function
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function sp_wqv_framework() {
		include_once SP_WQV_PATH . 'admin/views/sp-framework/classes/setup.class.php';
	}
}
