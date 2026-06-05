<?php
/**
 * This class defines all code necessary to the admin.
 *
 * @since      1.0.7
 * @package    Woo_Quick_View
 * @subpackage Woo_Quick_View/includes
 * @author     ShapedPlugin <support@shapedplugin.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}  // if direct access

/**
 * Functions
 */
class SP_WQV_Functions {

	/**
	 * Initialize the class
	 */
	public function __construct() {
		add_filter( 'admin_footer_text', array( $this, 'admin_footer' ), 1, 2 );
		add_filter( 'update_footer', array( $this, 'admin_footer_version' ), 11 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'sp_wqv_enqueue', array( $this, 'admin_enqueue_scripts_help_page' ) );
	}

	/**
	 * Review Text.
	 *
	 * @param string $text text.
	 *
	 * @return string
	 */
	public function admin_footer( $text ) {
		$screen = get_current_screen();
		if ( 'toplevel_page_wqv_settings' === $screen->id ) {
			$text = sprintf(
				/* translators: 1: start strong tag, 2: close strong tag. 3: start link 4: close link */
				__( 'Enjoying %1$sQuick View for WooCommerce?%2$s Please rate us %3$sWordPress.org%4$s. Your positive feedback will help us grow more. Thank you! 😊', 'woo-quickview' ),
				'<strong>',
				'</strong>',
				'<span class="spwpcp-footer-text-star">★★★★★</span> <a href="https://wordpress.org/support/plugin/woo-quickview/reviews/" target="_blank">',
				'</a>'
			);
		}

		return $text;
	}

	/**
	 * Review Text.
	 *
	 * @param string $text text.
	 *
	 * @return string
	 */
	public function admin_footer_version( $text ) {
		$screen = get_current_screen();
		if ( 'toplevel_page_wqv_settings' === $screen->id ) {
			$text = 'WooCommerce Quick View ' . SP_WQV_VERSION;
		}

		return $text;
	}

	/**
	 * Admin enqueue scripts.
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts() {
		// Notice style.
		wp_enqueue_style( 'woo-quick-view-notices', SP_WQV_URL . 'admin/views/notices/notices.min.css', array(), SP_WQV_VERSION, 'all' );
	}

	/**
	 * Admin enqueue help page scripts.
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts_help_page() {
		// Assets file fore help page.
		wp_enqueue_style( 'woo-quick-view-help', SP_WQV_URL . 'admin/help-page/css/help-page.min.css', array(), SP_WQV_VERSION, 'all' );
		wp_enqueue_style( 'woo-quick-view-fontello-help', SP_WQV_URL . 'admin/help-page/css/fontello.min.css', array(), SP_WQV_VERSION, 'all' );

		// Js file.
		wp_enqueue_script( 'woo_quick_view_help', SP_WQV_URL . 'admin/help-page/js/help-page.min.js', array( 'jquery' ), SP_WQV_VERSION, true );
	}
}

new SP_WQV_Functions();
