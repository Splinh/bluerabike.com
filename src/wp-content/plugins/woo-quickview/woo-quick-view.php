<?php
/**
 * Plugin Name:   Quick View for WooCommerce
 * Plugin URI:    https://shapedplugin.com/quick-view-for-woocommerce/?ref=1
 * Description:   <strong>Quick View for WooCommerce</strong> allows you to add a quick view button in product loop so that visitors to quickly view product information (using AJAX) in a nice modal without opening the product page.
 * Version:       2.2.17
 * Author:        ShapedPlugin LLC
 * Author URI:    https://shapedplugin.com/
 * Text Domain:   woo-quickview
 * Domain Path:   /languages
 * Requires Plugins: woocommerce
 * WC requires at least: 4.0
 * WC tested up to: 10.2.1
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package Woo_Quick_View
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'activate_woo_quick_view' ) ) {
	/**
	 * The code that runs during plugin activation.
	 * This action is documented in includes/class-woo-quick-view-activator.php
	 */
	function activate_woo_quick_view() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-quick-view-activator.php';
		Woo_Quick_View_Activator::activate();
	}
}

register_activation_hook( __FILE__, 'activate_woo_quick_view' );

/**
 * Handles core plugin hooks and action setup.
 *
 * @package Woo_Quick_View
 *
 * @since 1.0
 */
if ( ! class_exists( 'SP_Woo_Quick_View' ) && ! class_exists( 'SP_Woo_Quick_View_Pro' ) ) {
	/**
	 * SP_Woo_Quick_View class
	 */
	class SP_Woo_Quick_View {
		/**
		 * Plugin version
		 *
		 * @var string
		 */
		public $version = '2.2.17';

		/**
		 * Router
		 *
		 * @var SP_WQV_Router $router
		 */
		public $router;

		/**
		 * Shortcode
		 *
		 * @var SP_WQV_Shortcode $shortcode
		 */
		public $shortcode;

		/**
		 * Popup
		 *
		 * @var SP_WQV_Popup $popup
		 */
		public $popup;

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
		 * @return SP_Woo_Quick_View
		 * @since 1.0
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Constructor.
		 */
		public function __construct() {
			// Define constants.
			$this->define_constants();

			// Initialize the action hooks.
			$this->init_actions();

			// Initialize the filter hooks.
			$this->init_filters();

			// Required class file include.
			spl_autoload_register( array( $this, 'autoload' ) );

			// Include required files.
			$this->includes();

			// instantiate classes.
			$this->instantiate();
		}

		/**
		 * Define constants
		 *
		 * @since 1.0
		 */
		public function define_constants() {
			define( 'SP_WQV_VERSION', $this->version );
			define( 'SP_WQV_PATH', plugin_dir_path( __FILE__ ) );
			define( 'SP_WQV_URL', plugin_dir_url( __FILE__ ) );
			define( 'SP_WQV_BASENAME', plugin_basename( __FILE__ ) );
		}

		/**
		 * Initialize WordPress action hooks
		 *
		 * @return void
		 */
		public function init_actions() {
			add_action( 'init', array( $this, 'init_button_position' ) );
			add_action( 'before_woocommerce_init', array( $this, 'declare_compatibility_with_woo_hpos_feature' ) );
		}

		/**
		 * Quick view button position
		 */
		public function init_button_position() {
			$wqv_button_position = sp_wqv_get_option( 'wqvpro_quick_view_button_position' );
			switch ( $wqv_button_position ) {
				case 'before_add_to_cart':
				case 'after_add_to_cart':
					add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'sp_wqv_quick_view_button' ), 15 );
					break;
				case 'below_product':
					// For shop or product archive page.
					add_action( 'woocommerce_after_shop_loop_item', array( $this, 'wqvpro_quick_view_button_after_product_price' ), 99 );
					// For product slider pro / free.
					add_action( 'sp_wps_after_product_details_inner', array( $this, 'wqvpro_quick_view_button_after_product_price' ), 99 );
					break;
			}
		}

		/**
		 * Declare the compatibility of WooCommerce High-Performance Order Storage (HPOS) feature.
		 *
		 * @since 1.4.15
		 *
		 * @return void
		 */
		public function declare_compatibility_with_woo_hpos_feature() {
			if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			}
		}

		/**
		 * Initialize WordPress filter hooks
		 *
		 * @return void
		 */
		public function init_filters() {
			add_filter( 'plugin_action_links', array( $this, 'add_plugin_action_links' ), 10, 2 );
			if ( ( class_exists( 'WEPOF_Extra_Product_Options' ) ) ) {
				add_filter( 'thwepof_hook_names_before_single_product', array( $this, 'woo_extra_product_addons' ), 80, 2 );
			}
		}

		/**
		 * Support fields of WooCommerce Custom Product Addons.
		 *
		 * @param  array $value quick view button action hook.
		 * @return array
		 */
		public function woo_extra_product_addons( $value ) {
			$value = array( 'wqv_product_content' );
			return $value;
		}

		/**
		 * Add plugin action menu
		 *
		 * @param array  $links action link.
		 * @param string $file plugin file.
		 *
		 * @return array
		 */
		public function add_plugin_action_links( $links, $file ) {
			if ( SP_WQV_BASENAME === $file ) {
				$new_links = sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=wqv_settings' ), __( 'Settings', 'woo-quickview' ) );
				$links     = is_array( $links ) ? $links : array();
				array_unshift( $links, $new_links );

				$links['go_pro'] = sprintf( '<a target="_blank" href="%1$s" style="color: #35b747; font-weight: 700;">Go Pro!</a>', 'https://shapedplugin.com/quick-view-for-woocommerce/?ref=1' );
			}

			return $links;
		}

		/**
		 * Autoload class files on demand
		 *
		 * @param string $class requested class name.
		 */
		public function autoload( $class ) {
			$name = explode( '_', $class );
			if ( isset( $name[2] ) ) {
				$class_name = strtolower( $name[2] );
				$filename   = SP_WQV_PATH . '/class/' . $class_name . '.php';

				if ( file_exists( $filename ) ) {
					require_once $filename;
				}
			}
		}

		/**
		 * Instantiate all the required classes
		 *
		 * @since 2.4
		 */
		public function instantiate() {
			$this->popup = SP_WQV_Popup::getInstance();

			do_action( 'sp_wqv_instantiate', $this );
		}

		/**
		 * Page router instantiate
		 *
		 * @since 1.0
		 */
		public function page() {
			$this->router = SP_WQV_Router::instance();

			return $this->router;
		}

		/**
		 * Include the required files
		 *
		 * @return void
		 */
		public function includes() {
			$this->page()->sp_wqv_function();
			$this->page()->sp_wqv_framework();
			$this->router->includes();
			add_action( 'activated_plugin', array( $this, 'redirect_help_page' ) );
		}

		/**
		 * Redirect after active
		 *
		 * @param string $plugin The plugin help page.
		 */
		public function redirect_help_page( $plugin ) {
			if ( SP_WQV_BASENAME === $plugin && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) && ( ! ( defined( 'WP_CLI' ) && WP_CLI ) ) ) {
				wp_safe_redirect( admin_url( 'admin.php?page=wqv_settings#tab=get-help' ) );
				exit();
			}
		}

		/**
		 * Add quick view button after product price.
		 */
		public function wqvpro_quick_view_button_after_product_price() {
			global $woocommerce, $product;
			if ( $woocommerce->version >= '3.0' ) {
				$product_id = $product->get_id();
			} else {
				$product_id = $product->id;
			}
			$wqv_plugin_settings = get_option( '_sp_wqvpro_options' );
			$enable_quick_view   = isset( $wqv_plugin_settings['wqvpro_enable_quick_view'] ) ? $wqv_plugin_settings['wqvpro_enable_quick_view'] : true;
			$quick_view_button   = $enable_quick_view ? $this->sp_wqv_view_button( $product_id ) : '';

			echo '<div class="sp-wqv-view-button-wrapper">' . $quick_view_button . '</div>'; // phpcs:ignore
		}

		/**
		 * Quick view button
		 *
		 * @param [string] $add_to_cart_url add_to_cart_url.
		 *
		 * @return return
		 */
		public function sp_wqv_quick_view_button( $add_to_cart_url ) {

			global $woocommerce;

			if ( wp_is_block_theme() ) {
				$product = wc_get_product( get_the_ID() );
			} else {
				global $product;
			}

			if ( version_compare( $woocommerce->version, '3.0', '>=' ) ) {
				$product_id = $product->get_id();
			} else {
				$product_id = $product->id;
			}
			$wqv_plugin_settings = get_option( '_sp_wqvpro_options' );
			$enable_quick_view   = isset( $wqv_plugin_settings['wqvpro_enable_quick_view'] ) ? $wqv_plugin_settings['wqvpro_enable_quick_view'] : true;

			$quick_view_button   = $enable_quick_view ? $this->sp_wqv_view_button( $product_id ) : '';
			$wqv_button_position = isset( $wqv_plugin_settings['wqvpro_quick_view_button_position'] ) ? $wqv_plugin_settings['wqvpro_quick_view_button_position'] : 'after_add_to_cart';
			if ( 'before_add_to_cart' === $wqv_button_position || 'above_add_to_cart' === $wqv_button_position ) {
				$buttons_url = $quick_view_button . $add_to_cart_url;
			} else {
				$buttons_url = $add_to_cart_url . $quick_view_button;
			}

			if ( strpos( $add_to_cart_url, 'wc-block-components-product-button__button' ) !== false && apply_filters( 'sp_wqv_add_wrapper_to_add_to_cart_button', true ) ) {
				// Add wrapper to style for block theme.
				$buttons_url = '<div class="sp-wqv-woocommerce-loop-product-buttons">' . $buttons_url . '</div>';
			}

			return $buttons_url;
		}

		/**
		 * Quick view button content
		 *
		 * @param [int] $product_id product id.
		 * @return statement
		 */
		public function sp_wqv_view_button( $product_id = null ) {
			if ( ! $product_id ) {
				global $woocommerce, $product;
				if ( $woocommerce->version >= '3.0' ) {
					$product_id = $product->get_id();
				} else {
					$product_id = $product->id;
				}
			}
			$settings                        = get_option( '_sp_wqvpro_options' );
			$close_button                    = isset( $settings['wqvpro_popup_close_button'] ) ? $settings['wqvpro_popup_close_button'] : true;
			$quick_view_button_icon_option   = sp_wqv_get_option( 'wqvpro_quick_view_button_icon_option' );
			$quick_view_button_icon_position = sp_wqv_get_option( 'wqvpro_quick_view_button_icon_position' );
			$quick_view_button_font_icon     = sp_wqv_get_option( 'wqvpro_quick_view_button_font_icon' );
			$quick_view_button_text          = isset( $settings['wqvpro_quick_view_button_text'] ) ? $settings['wqvpro_quick_view_button_text'] : 'Quick View';
			$wqv_button_position             = isset( $settings['wqvpro_quick_view_button_position'] ) ? $settings['wqvpro_quick_view_button_position'] : 'after_add_to_cart';
			$_product                        = wc_get_product( $product_id );
			$qv_ajax_add_to_cart             = $_product->is_type( 'external' ) ? '""' : sp_wqv_get_option( 'wqvpro_aj_add_to_cart' );
			$quick_view_icon                 = '';
			if ( 'font_icon' === $quick_view_button_icon_option ) {
				$quick_view_icon = ! empty( $quick_view_button_font_icon ) ? '<i class="wqv-icon ' . $quick_view_button_font_icon . '"></i>' : '';
			}

			// Hide Quick View Button Label if the option is true.
			if ( '1' === sp_wqv_get_option( 'wqvpro_quick_view_button_icon_only' ) ) {
				$quick_view_button_text = '';
			} else {
				$quick_view_button_text = sp_wqv_get_option( 'wqvpro_quick_view_button_text' );
			}

			// quick view button text & icon.
			$quick_view_icon_and_text = $quick_view_icon . $quick_view_button_text;
			if ( 'icon_on_right' === $quick_view_button_icon_position ) {
				$quick_view_icon_and_text = $quick_view_button_text . $quick_view_icon;
				$wqv_button_position     .= ' wqv-right-icon';
			}

			$preloader               = isset( $settings['wqvpro_qv_preloader'] ) ? $settings['wqvpro_qv_preloader'] : true;
			$preloader_label         = isset( $settings['wqvpro_loading_label'] ) ? $settings['wqvpro_loading_label'] : 'Loading...';
			$quick_view_button_style = sp_wqv_get_option( 'wqvpro_qv_button_style' );
			$button_class            = 'button_css_class' === $quick_view_button_style ? ' ' . sp_wqv_get_option( 'wqvpro_qv_button_css_class' ) : '';
			$image_lightbox          = isset( $settings['wqvpro_product_image_lightbox'] ) ? $settings['wqvpro_product_image_lightbox'] : false;
			$image_lightbox          = $image_lightbox ? 1 : 0;
			$image_title             = ! empty( sp_wqv_get_option( 'wqvpro_qv_image_title' ) ) ? sp_wqv_get_option( 'wqvpro_qv_image_title' ) : 'false';

			$outline = '';
			if ( $product_id ) {
				$outline .= '<a href="#" id="sp-wqv-view-button" class="button sp-wqv-view-button ' . $wqv_button_position . $button_class . '" data-id="' . esc_attr( $product_id ) . '" data-effect="' . sp_wqv_get_option( 'wqvpro_popup_effect' ) . '" data-wqv=\'{"close_button": ' . $close_button . ', "ajax_cart": ' . $qv_ajax_add_to_cart . ', "image_title" : ' . $image_title . ', "lightbox": ' . $image_lightbox . ',"preloader": ' . $preloader . ',"preloader_label": "' . $preloader_label . '" } \'>' . $quick_view_icon_and_text . '</a>';
			}
			return $outline;
		}
	}

	/**
	 * Returns the main instance.
	 *
	 * @since 1.0
	 *
	 * @return SP_Woo_Quick_View
	 */
	function sp_woo_quick_view() {
		return SP_Woo_Quick_View::instance();
	}

	/**
	 * SP_woo_quick_view instance.
	 */
	sp_woo_quick_view();
}
