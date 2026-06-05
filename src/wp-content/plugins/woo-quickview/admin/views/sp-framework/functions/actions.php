<?php
/**
 * The action functionality of the plugin.
 *
 * @since      1.0.7
 * @package    Woo_Quick_View
 * @subpackage Woo_Quick_View/views
 * @author     ShapedPlugin <support@shapedplugin.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access directly.

if ( ! function_exists( 'sp_wqv_get_option' ) ) {
	/**
	 * Get option
	 *
	 * @param  mixed $option_name option name.
	 * @param  mixed $default default value.
	 * @return statement
	 */
	function sp_wqv_get_option( $option_name = '', $default = '' ) {

		$options = apply_filters( 'sp_wqv_get_option', get_option( '_sp_wqvpro_options' ), $option_name, $default );

		if ( isset( $option_name ) && isset( $options[ $option_name ] ) ) {
			return $options[ $option_name ];
		} else {
			return ( isset( $default ) ) ? $default : null;
		}
	}
}

if ( ! function_exists( 'sp_wqv_set_option' ) ) {
	/**
	 * Set option
	 *
	 * @param  mixed $option_name option name.
	 * @param  mixed $new_value new value.
	 * @return void
	 */
	function sp_wqv_set_option( $option_name = '', $new_value = '' ) {

		$options = apply_filters( 'sp_wqv_set_option', get_option( '_sp_wqvpro_options' ), $option_name, $new_value );

		if ( ! empty( $option_name ) ) {
			$options[ $option_name ] = $new_value;
			update_option( '_sp_wqvpro_options', $options );
		}
	}
}


if ( ! function_exists( 'sp_quick_view_get_icons' ) ) {
	/**
	 *
	 * Get icons from admin ajax
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	function sp_quick_view_get_icons() {

		if ( ! empty( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'spf_icon_nonce' ) ) {

			ob_start();

			SP_WQV_Framework::include_plugin_file( 'fields/icon/default-icons.php' );

			$icon_lists = apply_filters( 'sp_quick_view_field_icon_add_icons', sp_qvf_get_default_icons() );

			if ( ! empty( $icon_lists ) ) {

				foreach ( $icon_lists as $list ) {

					echo ( count( $icon_lists ) >= 2 ) ? '<div class="sp_wqvp-icon-title">' . wp_kses_post( $list['title'] ) . '</div>' : '';
					foreach ( $list['icons'] as $icon ) {
						echo '<a class="sp_wqvp-icon-tooltip" data-sp_wqvp-icon="' . $icon . '" title="' . $icon . '"><span class="sp_wqvp-icon sp_wqvp-selector"><i class="' . $icon . '"></i></span></a>'; // phpcs:ignore
					}
				}
			} else {
				echo '<div class="sp_wqvp-text-error">' . esc_html__( 'No data provided by developer', 'woo-quickview' ) . '</div>';

			}

			wp_send_json_success(
				array(
					'success' => true,
					'content' => ob_get_clean(),
				)
			);

		} else {

			wp_send_json_error(
				array(
					'success' => false,
					'error'   => esc_html__( 'Error while saving.', 'woo-quickview' ),
					'debug'   => $_REQUEST,
				)
			);

		}

	}
	add_action( 'wp_ajax_spqvp-get-icons', 'sp_quick_view_get_icons' );
}

if ( ! function_exists( 'sp_wqv_get_all_option' ) ) {
	/**
	 *
	 * Get all option
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	function sp_wqv_get_all_option() {
		return get_option( '_sp_wqvpro_options' );
	}
}

if ( ! function_exists( 'sp_quick_view_set_icons' ) ) {
	/**
	 *
	 * Set icons for wp dialog
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	function sp_quick_view_set_icons() {
		$screen = get_current_screen();
		if ( 'toplevel_page_wqv_settings' === $screen->id ) {
			?>
			<div id="sp_wqvp-modal-icon" class="sp_wqvp-modal sp_wqvp-modal-icon">
				<div class="sp_wqvp-modal-table">
				<div class="sp_wqvp-modal-table-cell">
					<div class="sp_wqvp-modal-overlay"></div>
					<div class="sp_wqvp-modal-inner">
					<div class="sp_wqvp-modal-title">
						<?php esc_html_e( 'Add Icon', 'woo-quickview' ); ?>
						<div class="sp_wqvp-modal-close sp_wqvp-icon-close"></div>
					</div>
					<div class="sp_wqvp-modal-header sp_wqvp-text-center">
						<input type="text" placeholder="<?php esc_html_e( 'Search a Icon...', 'woo-quickview' ); ?>" class="sp_wqvp-icon-search" />
					</div>
					<div class="sp_wqvp-modal-content">
						<div class="sp_wqvp-modal-loading"><div class="sp_wqvp-loading"></div></div>
						<div class="sp_wqvp-modal-load"></div>
					</div>
					</div>
				</div>
				</div>
			</div>
			<?php
		}
	}

	add_action( 'admin_footer', 'sp_quick_view_set_icons' );
	add_action( 'customize_controls_print_footer_scripts', 'sp_quick_view_set_icons' );
}
