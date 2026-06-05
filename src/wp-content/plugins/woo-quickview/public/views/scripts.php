<?php
/**
 * The public scripts file.
 *
 * This class defines public scripts.
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
 * Scripts and styles
 */
class SP_WQV_Front_Scripts {

	/**
	 * Instance
	 *
	 * @var null
	 * @since 1.0
	 */
	protected static $_instance = null;

	/**
	 * Instance
	 *
	 * @return SP_WQV_Front_Scripts
	 * @since 1.0
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Initialize the class
	 */
	public function __construct() {

		add_action( 'wp_enqueue_scripts', array( $this, 'front_scripts' ) );
	}

	/**
	 * Plugin Scripts and Styles
	 */
	public function front_scripts() {
			$plugin_setting = get_option( '_sp_wqvpro_options' );
			$image_lightbox = isset( $plugin_setting['wqvpro_product_image_lightbox'] ) ? $plugin_setting['wqvpro_product_image_lightbox'] : false;
			// CSS Files.
			wp_enqueue_style( 'sp_wqv-button-icons', SP_WQV_URL . 'admin/views/sp-framework/assets/css/icons.min.css', array(), SP_WQV_VERSION, 'all' );
			wp_enqueue_style( 'wqv-magnific-popup', SP_WQV_URL . 'public/assets/css/magnific-popup.css', array(), SP_WQV_VERSION );
			wp_enqueue_style( 'wqv-perfect-scrollbar', SP_WQV_URL . 'public/assets/css/perfect-scrollbar.css', array(), SP_WQV_VERSION );
			wp_enqueue_style( 'wqv-fontello', SP_WQV_URL . 'public/assets/css/fontello.min.css', array(), SP_WQV_VERSION );
			wp_register_style( 'wqv-fancy-box', SP_WQV_URL . 'public/assets/css/jquery.fancybox.min.css', array(), SP_WQV_VERSION );
		if ( $image_lightbox ) {
			wp_enqueue_style( 'wqv-fancy-box' );
		}
			wp_enqueue_style( 'wqv-style', SP_WQV_URL . 'public/assets/css/style.css', array(), SP_WQV_VERSION );
			wp_enqueue_style( 'wqv-custom', SP_WQV_URL . 'public/assets/css/custom.css', array(), SP_WQV_VERSION );

			include SP_WQV_PATH . '/includes/custom-css.php';
			wp_add_inline_style( 'wqv-custom', $custom_css );

			// JS Files.
			wp_enqueue_script( 'wc-add-to-cart-variation' );
			wp_enqueue_script( 'wqv-perfect-scrollbar-js', SP_WQV_URL . 'public/assets/js/perfect-scrollbar.min.js', array( 'jquery' ), SP_WQV_VERSION, true );
			wp_enqueue_script( 'wqv-magnific-popup-js', SP_WQV_URL . 'public/assets/js/magnific-popup.min.js', array( 'jquery' ), SP_WQV_VERSION, true );
			wp_register_script( 'wqv-facybox', SP_WQV_URL . 'public/assets/js/jquery.fancybox.min.js', array( 'jquery' ), SP_WQV_VERSION, true );
			wp_enqueue_script( 'wqv-config-js', SP_WQV_URL . 'public/assets/js/config.js', array( 'jquery', 'wc-add-to-cart-variation' ), SP_WQV_VERSION, true );
			wp_localize_script(
				'wqv-config-js',
				'wqv_vars',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'wc_ajax'  => urldecode( add_query_arg( 'wc-ajax', '%%endpoint%%', home_url( '/' ) ) ),
					'nonce'    => wp_create_nonce( 'nonce_name' ),
				)
			);
			$wqv_custom_js = sp_wqv_get_option( 'wqvpro_custom_js' );

		if ( ! empty( $wqv_custom_js ) ) {
			wp_add_inline_script( 'wqv-config-js', $wqv_custom_js );
		}

		if ( $image_lightbox ) {
			wp_enqueue_script( 'wqv-facybox' );
		}
	}
}
new SP_WQV_Front_Scripts();
