<?php
if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access directly.
/**
 *
 * Field: license
 *
 * @since 2.1.0
 * @version 2.1.0
 */
if ( ! class_exists( 'SP_WQV_Framework_Field_wqv_help' ) ) {
	class SP_WQV_Framework_Field_wqv_help extends SP_WQV_Framework_Fields {

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
			echo wp_kses_post( $this->field_before() );
			Woo_Quick_View_Help::instance();
			echo wp_kses_post( $this->field_after() );
		}
	}
}
