<?php
/**
 * The color field of the plugin.
 *
 * @since      1.0.7
 * @package    Woo_Quick_View
 * @subpackage Woo_Quick_View/views
 * @author     ShapedPlugin <support@shapedplugin.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access directly.

if ( ! class_exists( 'SP_WQV_Framework_Field_color' ) ) {
	/**
	 *
	 * Field: color
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	class SP_WQV_Framework_Field_color extends SP_WQV_Framework_Fields {

		/**
		 * Create fields.
		 *
		 * @param  mixed $field field.
		 * @param  mixed $value value.
		 * @param  mixed $unique unique id.
		 * @param  mixed $where where to add.
		 * @param  mixed $parent parent.
		 * @return void
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

			$default_attr = ( ! empty( $this->field['default'] ) ) ? ' data-default-color="' . esc_attr( $this->field['default'] ) . '"' : '';

			echo wp_kses_post( $this->field_before() );
			echo '<input type="text" name="' . esc_attr( $this->field_name() ) . '" value="' . esc_attr( $this->value ) . '" class="sp_wqv-color"' . $default_attr . $this->field_attributes() . '/>'; // phpcs:ignore
			// default_attr already escaping in the above.
			echo wp_kses_post( $this->field_after() );
		}
	}
}
