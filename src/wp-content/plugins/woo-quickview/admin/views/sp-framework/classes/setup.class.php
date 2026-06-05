<?php
/**
 * The Framework setup class of the plugin.
 *
 * @since      1.0.7
 * @package    Woo_Quick_View
 * @subpackage Woo_Quick_View/views
 * @author     ShapedPlugin <support@shapedplugin.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access directly.

if ( ! class_exists( 'SP_WQV_Framework' ) ) {
	/**
	 *
	 * Setup Class
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	class SP_WQV_Framework {

		// Default constants.
		/**
		 * Premium
		 *
		 * @var boolean
		 */
		public static $premium = true;
		/**
		 * Version
		 *
		 * @var string
		 */
		public static $version = '2.2.8';
		/**
		 * Dir
		 *
		 * @var string
		 */
		public static $dir = '';
		/**
		 * URL
		 *
		 * @var string
		 */
		public static $url = '';
		/**
		 * CSS
		 *
		 * @var String
		 */
		public static $css = '';
		/**
		 * File
		 *
		 * @var string
		 */
		public static $file = '';
		/**
		 * Enqueue
		 *
		 * @var boolean
		 */
		public static $enqueue = false;
		/**
		 * WebFonts
		 *
		 * @var array
		 */
		public static $webfonts = array();
		/**
		 * SubSets
		 *
		 * @var array
		 */
		public static $subsets = array();
		/**
		 * Inited
		 *
		 * @var array
		 */
		public static $inited = array();
		/**
		 * Fields
		 *
		 * @var array
		 */
		public static $fields = array();
		/**
		 * Args
		 *
		 * @var array
		 */
		public static $args = array(
			'admin_options' => array(),
		);


		/**
		 * Instance
		 *
		 * @var boolean
		 */
		private static $instance = null;

		/**
		 * Init
		 *
		 * @param  mixed $file file.
		 * @return statement
		 */
		public static function init( $file = __FILE__ ) {

			// Set file constant.
			self::$file = $file;

			// Set constants.
			self::constants();

			// Include files.
			self::includes();

			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Initialize.
		 *
		 * @return void
		 */
		public function __construct() {

			// Init action.
			do_action( 'sp_wqv_init' );

			add_action( 'after_setup_theme', array( 'SP_WQV_Framework', 'setup' ) );
			add_action( 'init', array( 'SP_WQV_Framework', 'setup' ) );
			add_action( 'switch_theme', array( 'SP_WQV_Framework', 'setup' ) );
			add_action( 'admin_enqueue_scripts', array( 'SP_WQV_Framework', 'add_admin_enqueue_scripts' ), 11, 1 );
		}

		/**
		 * Setup frameworks
		 *
		 * @return void
		 */
		public static function setup() {

			// Welcome page.
			self::include_plugin_file( 'config/framework.config.php' );

			// Setup admin option framework.
			$params = array();
			if ( class_exists( 'SP_WQV_Framework_Options' ) && ! empty( self::$args['admin_options'] ) ) {
				foreach ( self::$args['admin_options'] as $key => $value ) {
					if ( ! empty( self::$args['sections'][ $key ] ) && ! isset( self::$inited[ $key ] ) ) {

						$params['args']       = $value;
						$params['sections']   = self::$args['sections'][ $key ];
						self::$inited[ $key ] = true;

						SP_WQV_Framework_Options::instance( $key, $params );
					}
				}
			}

			do_action( 'sp_wqv_loaded' );
		}

		/**
		 * Create options
		 *
		 * @param  mixed $id id.
		 * @param  mixed $args array.
		 * @return void
		 */
		public static function createOptions( $id, $args = array() ) {
			self::$args['admin_options'][ $id ] = $args;
		}

		/**
		 * Create section.
		 *
		 * @param  mixed $id id.
		 * @param  mixed $sections Section.
		 * @return void
		 */
		public static function createSection( $id, $sections ) {
			self::$args['sections'][ $id ][] = $sections;
			self::set_used_fields( $sections );
		}

		/**
		 * Set directory constants
		 *
		 * @return void
		 */
		public static function constants() {

			// We need this path-finder code for set URL of framework.
			$dirname        = str_replace( '//', '/', wp_normalize_path( dirname( dirname( self::$file ) ) ) );
			$theme_dir      = str_replace( '//', '/', wp_normalize_path( get_parent_theme_file_path() ) );
			$plugin_dir     = str_replace( '//', '/', wp_normalize_path( WP_PLUGIN_DIR ) );
			$plugin_dir     = str_replace( '/opt/bitnami', '/bitnami', $plugin_dir );
			$located_plugin = ( preg_match( '#' . self::sanitize_dirname( $plugin_dir ) . '#', self::sanitize_dirname( $dirname ) ) ) ? true : false;
			$directory      = ( $located_plugin ) ? $plugin_dir : $theme_dir;
			$directory_uri  = ( $located_plugin ) ? WP_PLUGIN_URL : get_parent_theme_file_uri();
			$foldername     = str_replace( $directory, '', $dirname );
			$protocol_uri   = ( is_ssl() ) ? 'https' : 'http';
			$directory_uri  = set_url_scheme( $directory_uri, $protocol_uri );

			self::$dir = $dirname;
			self::$url = $directory_uri . $foldername;
		}

		/**
		 * Include file helper.
		 *
		 * @param  mixed $file file.
		 * @param  mixed $load load.
		 * @return Statement.
		 */
		public static function include_plugin_file( $file, $load = true ) {
			$path     = '';
			$file     = ltrim( $file, '/' );
			$override = apply_filters( 'sp_wqv_override', 'sp_wqv-override' );

			if ( file_exists( get_parent_theme_file_path( $override . '/' . $file ) ) ) {
				$path = get_parent_theme_file_path( $override . '/' . $file );
			} elseif ( file_exists( get_theme_file_path( $override . '/' . $file ) ) ) {
				$path = get_theme_file_path( $override . '/' . $file );
			} elseif ( file_exists( self::$dir . '/' . $override . '/' . $file ) ) {
				$path = self::$dir . '/' . $override . '/' . $file;
			} elseif ( file_exists( self::$dir . '/' . $file ) ) {
				$path = self::$dir . '/' . $file;
			}

			if ( ! empty( $path ) && ! empty( $file ) && $load ) {

				global $wp_query;

				if ( is_object( $wp_query ) && function_exists( 'load_template' ) ) {

					load_template( $path, true );

				} else {

					require_once $path;

				}
			} else {
				return self::$dir . '/' . $file;
			}
		}

		/**
		 * Is active plugin helper.
		 *
		 * @param  mixed $file file path.
		 * @return array.
		 */
		public static function is_active_plugin( $file = '' ) {
			return in_array( $file, (array) get_option( 'active_plugins', array() ), true );
		}

		/**
		 * Sanitize dirname
		 *
		 * @param  mixed $dirname dir name.
		 * @return mixed
		 */
		public static function sanitize_dirname( $dirname ) {
			return preg_replace( '/[^A-Za-z]/', '', $dirname );
		}

		/**
		 * Set url constant.
		 *
		 * @param  mixed $file file url.
		 * @return url
		 */
		public static function include_plugin_url( $file ) {
			return esc_url( SP_WQV_URL . '/admin/views/sp-framework' ) . '/' . ltrim( $file, '/' );
		}

		/**
		 * Include files
		 *
		 * @return void
		 */
		public static function includes() {

			// Helpers.
			self::include_plugin_file( 'functions/actions.php' );
			self::include_plugin_file( 'functions/helpers.php' );
			self::include_plugin_file( 'functions/sanitize.php' );
			self::include_plugin_file( 'functions/validate.php' );

			// Includes free version classes.
			self::include_plugin_file( 'classes/abstract.class.php' );
			self::include_plugin_file( 'classes/fields.class.php' );
			self::include_plugin_file( 'classes/admin-options.class.php' );

			// Include all framework fields.
			$fields = apply_filters(
				'sp_wqv_fields',
				array(
					'border',
					'button_set',
					'code_editor',
					'color',
					'color_group',
					'dimensions_advanced',
					'icon',
					'image_select',
					'slider',
					'content',
					'checkbox',
					'select',
					'spacing',
					'spinner',
					'subheading',
					'switcher',
					'sortable',
					'text',
					'typography',
					'wqv_help',
				)
			);

			if ( ! empty( $fields ) ) {
				foreach ( $fields as $field ) {
					if ( ! class_exists( 'SP_WQV_Framework_Field_' . $field ) && class_exists( 'SP_WQV_Framework_Fields' ) ) {
						self::include_plugin_file( 'fields/' . $field . '/' . $field . '.php' );
					}
				}
			}
		}

		/**
		 * Set all of used fields
		 *
		 * @param  mixed $sections section.
		 * @return void
		 */
		public static function set_used_fields( $sections ) {

			if ( ! empty( $sections['fields'] ) ) {

				foreach ( $sections['fields'] as $field ) {

					if ( ! empty( $field['fields'] ) ) {
						self::set_used_fields( $field );
					}

					if ( ! empty( $field['tabs'] ) ) {
						self::set_used_fields( array( 'fields' => $field['tabs'] ) );
					}

					if ( ! empty( $field['accordions'] ) ) {
						self::set_used_fields( array( 'fields' => $field['accordions'] ) );
					}

					if ( ! empty( $field['type'] ) ) {
						self::$fields[ $field['type'] ] = $field;
					}
				}
			}
		}

		/**
		 * Enqueue admin and fields styles and scripts
		 *
		 * @return void
		 */
		public static function add_admin_enqueue_scripts() {

			// Loads scripts and styles only when needed.
			$wpscreen = get_current_screen();

			if ( ! empty( self::$args['admin_options'] ) ) {
				foreach ( self::$args['admin_options'] as $argument ) {
					if ( substr( $wpscreen->id, -strlen( $argument['menu_slug'] ) ) === $argument['menu_slug'] ) {
						self::$enqueue = true;
					}
				}
			}

			if ( ! apply_filters( 'sp_wqv_enqueue_assets', self::$enqueue ) ) {
				return;
			}

			// Check for developer mode.
			$min = ( self::$premium && SCRIPT_DEBUG ) ? '' : '.min';

			// Admin utilities.
			wp_enqueue_media();

			// Wp color picker.
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker' );

			// Dequeue fontawesome of WP Carousel Free from Quickview plugin page.
			wp_dequeue_style( 'font-awesome' );
			// Fontawesome icons.
			wp_enqueue_style( 'sp_wqv-fontawesome-icons', self::include_plugin_url( 'assets/css/font-awesome' . $min . '.css' ), array(), SP_WQV_VERSION, 'all' );

			// Main style.
			wp_enqueue_style( 'sp_wqv', self::include_plugin_url( 'assets/css/style' . $min . '.css' ), array(), SP_WQV_VERSION, 'all' );
			wp_enqueue_style( 'sp_wqv-button-icons', self::include_plugin_url( 'assets/css/icons' . $min . '.css' ), array(), SP_WQV_VERSION, 'all' );
			wp_enqueue_style( 'sp_wqv-fontello-icons', self::include_plugin_url( 'assets/css/fontello' . $min . '.css' ), array(), SP_WQV_VERSION, 'all' );
			// Main RTL styles.
			if ( is_rtl() ) {
				wp_enqueue_style( 'sp_wqv-rtl', self::include_plugin_url( 'assets/css/style-rtl' . $min . '.css' ), array(), SP_WQV_VERSION, 'all' );
			}

			// Main scripts.
			wp_enqueue_script( 'sp_wqv-plugins', self::include_plugin_url( 'assets/js/plugins' . $min . '.js' ), array(), SP_WQV_VERSION, true );
			wp_enqueue_script( 'sp_wqv', self::include_plugin_url( 'assets/js/main' . $min . '.js' ), array( 'sp_wqv-plugins' ), SP_WQV_VERSION, true );

			// Main variables.
			wp_localize_script(
				'sp_wqv',
				'sp_wqv_vars',
				array(
					'color_palette' => apply_filters( 'sp_wqv_color_palette', array() ),
					'i18n'          => array(
						'confirm'         => esc_html__( 'Are you sure?', 'woo-quickview' ), // translators: %s is the number of characters.
						'typing_text'     => esc_html__( 'Please enter %s or more characters', 'woo-quickview' ),
						'searching_text'  => esc_html__( 'Searching...', 'woo-quickview' ),
						'no_results_text' => esc_html__( 'No results found.', 'woo-quickview' ),
					),
				)
			);

			// Enqueue fields scripts and styles.
			$enqueued = array();

			if ( ! empty( self::$fields ) ) {
				foreach ( self::$fields as $field ) {
					if ( ! empty( $field['type'] ) ) {
						$classname = 'SP_WQV_Framework_Field_' . $field['type'];
						if ( class_exists( $classname ) && method_exists( $classname, 'enqueue' ) ) {
							$instance = new $classname( $field );
							if ( method_exists( $classname, 'enqueue' ) ) {
								$instance->enqueue();
							}
							unset( $instance );
						}
					}
				}
			}

			do_action( 'sp_wqv_enqueue' );
		}

		/**
		 * Add a new framework field.
		 *
		 * @param  array  $field field.
		 * @param  string $value string.
		 * @param  mixed  $unique unique id.
		 * @param  mixed  $where where to add.
		 * @param  mixed  $parent parent.
		 * @return void
		 */
		public static function field( $field = array(), $value = '', $unique = '', $where = '', $parent = '' ) {

			// Check for disallow fields.
			if ( ! empty( $field['_notice'] ) ) {

				$field_type = $field['type'];

				$field            = array();
				$field['content'] = esc_html__( 'Oops! Not allowed.', 'woo-quickview' ) . ' <strong>(' . $field_type . ')</strong>';
				$field['type']    = 'notice';
				$field['style']   = 'danger';

			}

			$depend     = '';
			$visible    = '';
			$unique     = ( ! empty( $unique ) ) ? $unique : '';
			$class      = ( ! empty( $field['class'] ) ) ? ' ' . esc_attr( $field['class'] ) : '';
			$is_pseudo  = ( ! empty( $field['pseudo'] ) ) ? ' sp_wqv-pseudo-field' : '';
			$field_type = ( ! empty( $field['type'] ) ) ? esc_attr( $field['type'] ) : '';

			if ( ! empty( $field['dependency'] ) ) {

				$dependency      = $field['dependency'];
				$depend_visible  = '';
				$data_controller = '';
				$data_condition  = '';
				$data_value      = '';
				$data_global     = '';

				if ( is_array( $dependency[0] ) ) {
					$data_controller = implode( '|', array_column( $dependency, 0 ) );
					$data_condition  = implode( '|', array_column( $dependency, 1 ) );
					$data_value      = implode( '|', array_column( $dependency, 2 ) );
					$data_global     = implode( '|', array_column( $dependency, 3 ) );
					$depend_visible  = implode( '|', array_column( $dependency, 4 ) );
				} else {
					$data_controller = ( ! empty( $dependency[0] ) ) ? $dependency[0] : '';
					$data_condition  = ( ! empty( $dependency[1] ) ) ? $dependency[1] : '';
					$data_value      = ( ! empty( $dependency[2] ) ) ? $dependency[2] : '';
					$data_global     = ( ! empty( $dependency[3] ) ) ? $dependency[3] : '';
					$depend_visible  = ( ! empty( $dependency[4] ) ) ? $dependency[4] : '';
				}

				$depend .= ' data-controller="' . esc_attr( $data_controller ) . '"';
				$depend .= ' data-condition="' . esc_attr( $data_condition ) . '"';
				$depend .= ' data-value="' . esc_attr( $data_value ) . '"';
				$depend .= ( ! empty( $data_global ) ) ? ' data-depend-global="true"' : '';

				$visible = ( ! empty( $depend_visible ) ) ? ' sp_wqv-depend-visible' : ' sp_wqv-depend-hidden';

			}

			if ( ! empty( $field_type ) ) {

				// These attributes has been sanitized above.
				echo wp_kses_post( '<div class="sp_wqv-field sp_wqv-field-' . $field_type . $is_pseudo . $class . $visible . '"' . $depend . '>' );

				if ( ! empty( $field['fancy_title'] ) ) {
					echo '<div class="sp_wqv-fancy-title">' . wp_kses_post( $field['fancy_title'] ) . '</div>';
				}

				if ( ! empty( $field['title'] ) ) {
					echo '<div class="sp_wqv-title">';
					$help_title = ( ! empty( $field['help_title'] ) ) ? '<div class="sp_wqv-help"><span class="sp_wqv-help-text">' . wp_kses_post( $field['help_title'] ) . '</span><i class="fas fa-question-circle"></i></div>' : '';

					echo '<h4>' . wp_kses_post( $field['title'] ) . wp_kses_post( $help_title ) . '</h4>';
					echo ( ! empty( $field['subtitle'] ) ) ? '<div class="sp_wqv-subtitle-text">' . wp_kses_post( $field['subtitle'] ) . '</div>' : '';
					echo '</div>';
				}

				echo ( ! empty( $field['title'] ) || ! empty( $field['fancy_title'] ) ) ? '<div class="sp_wqv-fieldset">' : '';

				$value = ( ! isset( $value ) && isset( $field['default'] ) ) ? $field['default'] : $value;
				$value = ( isset( $field['value'] ) ) ? $field['value'] : $value;

				$classname = 'SP_WQV_Framework_Field_' . $field_type;

				if ( class_exists( $classname ) ) {
					$instance = new $classname( $field, $value, $unique, $where, $parent );
					$instance->render();
				} else {
					echo '<p>' . esc_html__( 'Field not found!', 'woo-quickview' ) . '</p>';
				}
			} else {
				echo '<p>' . esc_html__( 'Field not found!', 'woo-quickview' ) . '</p>';
			}

			echo ( ! empty( $field['title'] ) || ! empty( $field['fancy_title'] ) ) ? '</div>' : '';
			echo '<div class="clear"></div>';
			echo '</div>';
		}
	}

}

SP_WQV_Framework::init( __FILE__ );
