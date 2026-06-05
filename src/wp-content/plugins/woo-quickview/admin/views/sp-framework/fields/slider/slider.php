<?php
/**
 * The slider field of the plugin.
 *
 * @link       http://shapedplugin.com/
 * @since      1.0.0
 *
 * @package    Woo_Quick_View_Pro
 * @subpackage Woo_Quick_View_Pro/sp-framework
 * @author     ShapedPlugin <support@shapedplugin.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access directly.


if ( ! class_exists( 'SP_WQV_Framework_Field_slider' ) ) {
	/**
	 *
	 * Field: slider
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	class SP_WQV_Framework_Field_slider extends SP_WQV_Framework_Fields {
		/**
		 * The class constructor.
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
					'max'  => 100,
					'min'  => 0,
					'step' => 1,
					'unit' => '',
				)
			);

			$is_unit = ( ! empty( $args['unit'] ) ) ? ' sp_wqvp--is-unit' : '';

			echo wp_kses_post( $this->field_before() );

			echo '<div class="sp_wqv--wrap">';
			echo '<div class="sp_wqv-slider-ui"></div>';
			echo '<div class="sp_wqvp--input">';
			echo '<input type="number" name="' . esc_attr( $this->field_name() ) . '" value="' . esc_attr( $this->value ) . '"' . $this->field_attributes( array( 'class' => 'sp_wqvp-input-number' . esc_attr( $is_unit ) ) ) . ' data-min="' . esc_attr( $args['min'] ) . '" data-max="' . esc_attr( $args['max'] ) . '" data-step="' . esc_attr( $args['step'] ) . '" step="any" />';// phpcs:ignore
			echo ( ! empty( $args['unit'] ) ) ? '<span class="sp_wqvp--unit">' . esc_attr( $args['unit'] ) . '</span>' : '';
			echo '</div>';
			echo '</div>';

			echo wp_kses_post( $this->field_after() );
		}

		/**
		 * Enqueue
		 *
		 * @return void
		 */
		public function enqueue() {

			if ( ! wp_script_is( 'jquery-ui-slider' ) ) {
				wp_enqueue_script( 'jquery-ui-slider' );
			}
		}
	}
}
