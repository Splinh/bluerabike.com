<?php
/**
 * Framework icon field.
 *
 * @link https://shapedplugin.com
 * @since 2.0.4
 *
 * @package    Woo_Quick_View_Pro
 * @subpackage Woo_Quick_View_Pro/sp-framework
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access directly.

if ( ! class_exists( 'SP_WQV_Framework_Field_icon' ) ) {
	/**
	 *
	 * Field: icon
	 *
	 * @since 2.0.4
	 * @version 2.0.4
	 */
	class SP_WQV_Framework_Field_icon extends SP_WQV_Framework_Fields {
		/**
		 * Field constructor.
		 *
		 * @param array  $field The field type.
		 * @param string $value The values of the field.
		 * @param string $unique The unique ID for the field.
		 * @param string $where To where show the output CSS.
		 * @param string $parent The parent args.
		 */
		public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
			parent::__construct( $field, $value, $unique, $where, $parent );
		}

		/**
		 * Render
		 *
		 * @return void
		 */
		public function render() {

			$args = wp_parse_args(
				$this->field,
				array(
					'button_title' => esc_html__( 'Add Icon', 'woo-quickview' ),
					'remove_title' => '',
				)
			);

			echo wp_kses_post( $this->field_before() );

			$nonce  = wp_create_nonce( 'spf_icon_nonce' );
			$hidden = ( empty( $this->value ) ) ? ' hidden' : '';

			echo '<div class="sp_wqvp-icon-select">';
			echo '<span class="sp_wqvp-icon-preview' . esc_attr( $hidden ) . '"><i class="' . esc_attr( $this->value ) . '"></i></span>';
			echo '<a href="#" class="button button-primary sp_wqvp-icon-add" data-nonce="' . esc_attr( $nonce ) . '">' . esc_attr( $args['button_title'] ) . '</a>';
			echo '<a href="#" class="button sp_wqvp-warning-primary fa fa-trash' . esc_attr( $hidden ) . '">' . esc_attr( $args['remove_title'] ) . '</a>';
			echo '<input type="hidden" name="' . esc_attr( $this->field_name() ) . '" value="' . esc_attr( $this->value ) . '" class="sp_wqvp-icon-value"' . $this->field_attributes() . ' />'; // phpcs:ignore
			echo '</div>';

			echo wp_kses_post( $this->field_after() );
		}
	}
}
