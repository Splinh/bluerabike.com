<?php
/**
 * The image select field of the plugin.
 *
 * @since      1.0.7
 * @package    Woo_Quick_View
 * @subpackage Woo_Quick_View/views
 * @author     ShapedPlugin <support@shapedplugin.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access directly.

if ( ! class_exists( 'SP_WQV_Framework_Field_image_select' ) ) {
	/**
	 *
	 * Field: image_select
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	class SP_WQV_Framework_Field_image_select extends SP_WQV_Framework_Fields {
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
					'multiple' => false,
					'inline'   => false,
					'options'  => array(),
				)
			);

			$inline = ( $args['inline'] ) ? ' sp_wqv--inline-list' : '';

			$value = ( is_array( $this->value ) ) ? $this->value : array_filter( (array) $this->value );

			echo wp_kses_post( $this->field_before() );

			if ( ! empty( $args['options'] ) ) {

				echo '<div class="sp_wqv-siblings sp_wqv--image-group' . esc_attr( $inline ) . '" data-multiple="' . esc_attr( $args['multiple'] ) . '">';

				$num = 1;

				foreach ( $args['options'] as $key => $option ) {

					$type           = ( $args['multiple'] ) ? 'checkbox' : 'radio';
					$extra          = ( $args['multiple'] ) ? '[]' : '';
					$active         = ( in_array( $key, $value, true ) ) ? ' sp_wqv--active' : '';
					$checked        = ( in_array( $key, $value, true ) ) ? ' checked' : '';
					$pro_only_class = ( isset( $option['pro_only'] ) && true === $option['pro_only'] ) ? ' sp_wqv-pro-only' : '';

					echo '<div class="sp_wqv--sibling sp_wqv--image' . esc_attr( $active . $pro_only_class ) . '" title="' . ( isset( $option['option_name'] ) ? esc_html( $option['option_name'] ) : '' ) . '">';
					echo '<figure>';
					echo '<img src="' . esc_url( $option['image'] ) . '" alt="img-' . esc_attr( $num++ ) . '" />';
					echo '<input type="' . esc_attr( $type ) . '" name="' . esc_attr( $this->field_name( $extra ) ) . '" value="' . esc_attr( $key ) . '"' . $this->field_attributes() . esc_attr( $checked ) . '/>'; // phpcs:ignore -- values are escaped inside in 'field_attributes()' function.
					if ( isset( $option['option_name'] ) ) {
						echo '<p>' . esc_html( $option['option_name'] ) . '</p>';
					}
					echo '</figure>';
					echo '</div>';

				}

				echo '</div>';

			}

			echo wp_kses_post( $this->field_after() );
		}
	}
}
