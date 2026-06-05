<?php
/**
 * The border field of the plugin.
 *
 * @since      1.0.7
 * @package    Woo_Quick_View
 * @subpackage Woo_Quick_View/views
 * @author     ShapedPlugin <support@shapedplugin.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access directly.

if ( ! class_exists( 'SP_WQV_Framework_Field_border' ) ) {
	/**
	 *
	 * Field: border
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	class SP_WQV_Framework_Field_border extends SP_WQV_Framework_Fields {

		/**
		 * Render
		 *
		 * @return void
		 */
		public function render() {

			$args = wp_parse_args(
				$this->field,
				array(
					'top_icon'           => '<i class="fas fa-long-arrow-alt-up"></i>',
					'left_icon'          => '<i class="fas fa-long-arrow-alt-left"></i>',
					'bottom_icon'        => '<i class="fas fa-long-arrow-alt-down"></i>',
					'right_icon'         => '<i class="fas fa-long-arrow-alt-right"></i>',
					'all_icon'           => '<i class="fas fa-arrows-alt"></i>',
					'top_placeholder'    => esc_html__( 'top', 'woo-quickview' ),
					'right_placeholder'  => esc_html__( 'right', 'woo-quickview' ),
					'bottom_placeholder' => esc_html__( 'bottom', 'woo-quickview' ),
					'left_placeholder'   => esc_html__( 'left', 'woo-quickview' ),
					'all_placeholder'    => esc_html__( 'all', 'woo-quickview' ),
					'top'                => true,
					'left'               => true,
					'bottom'             => true,
					'right'              => true,
					'all'                => false,
					'color'              => true,
					'hover_color'        => false,
					'style'              => true,
					'unit'               => 'px',
				)
			);

			$default_value = array(
				'top'    => '',
				'right'  => '',
				'bottom' => '',
				'left'   => '',
				'color'  => '',
				'style'  => 'solid',
				'all'    => '',
			);

			$border_props = array(
				'solid'  => esc_html__( 'Solid', 'woo-quickview' ),
				'dashed' => esc_html__( 'Dashed', 'woo-quickview' ),
				'dotted' => esc_html__( 'Dotted', 'woo-quickview' ),
				'double' => esc_html__( 'Double', 'woo-quickview' ),
				'inset'  => esc_html__( 'Inset', 'woo-quickview' ),
				'outset' => esc_html__( 'Outset', 'woo-quickview' ),
				'groove' => esc_html__( 'Groove', 'woo-quickview' ),
				'ridge'  => esc_html__( 'ridge', 'woo-quickview' ),
				'none'   => esc_html__( 'None', 'woo-quickview' ),
			);
			$allowed_tags = array(
				'i' => array(
					'class' => array(
						'fa',
					),
				),
			);

			$default_value = ( ! empty( $this->field['default'] ) ) ? wp_parse_args( $this->field['default'], $default_value ) : $default_value;

			$value = wp_parse_args( $this->value, $default_value );

			echo wp_kses_post( $this->field_before() );

			echo '<div class="sp_wqv--inputs" data-depend-id="' . esc_attr( $this->field['id'] ) . '">';

			if ( ! empty( $args['all'] ) ) {

				$placeholder = ( ! empty( $args['all_placeholder'] ) ) ? ' placeholder="' . esc_attr( $args['all_placeholder'] ) . '"' : '';

				echo '<div class="sp_wqv--input">';
				echo ( ! empty( $args['all_icon'] ) ) ? '<span class="sp_wqv--label sp_wqv--icon">' . wp_kses( $args['all_icon'], $allowed_tags ) . '</span>' : '';
				echo '<input type="number" name="' . esc_attr( $this->field_name( '[all]' ) ) . '" value="' . esc_attr( $value['all'] ) . '"' . $placeholder . ' class="sp_wqv-input-number sp_wqv--is-unit" step="any" />'; // phpcs:ignore 
				// placeholder already escaping in the above.
				echo ( ! empty( $args['unit'] ) ) ? '<span class="sp_wqv--label sp_wqv--unit">' . esc_attr( $args['unit'] ) . '</span>' : '';
				echo '</div>';

			} else {

				$properties = array();

				foreach ( array( 'top', 'right', 'bottom', 'left' ) as $prop ) {
					if ( ! empty( $args[ $prop ] ) ) {
						$properties[] = $prop;
					}
				}

				$properties = ( array( 'right', 'left' ) === $properties ) ? array_reverse( $properties ) : $properties;

				foreach ( $properties as $property ) {

					$placeholder = ( ! empty( $args[ $property . '_placeholder' ] ) ) ? ' placeholder="' . esc_attr( $args[ $property . '_placeholder' ] ) . '"' : '';

					echo '<div class="sp_wqv--input">';
					echo ( ! empty( $args[ $property . '_icon' ] ) ) ? '<span class="sp_wqv--label sp_wqv--icon">' . wp_kses( $args[ $property . '_icon' ], $allowed_tags ) . '</span>' : '';
					echo '<input type="number" name="' . esc_attr( $this->field_name( '[' . $property . ']' ) ) . '" value="' . esc_attr( $value[ $property ] ) . '"' . $placeholder . ' class="sp_wqv-input-number sp_wqv--is-unit" step="any" />';// phpcs:ignore 
					// placeholder already escaping in the above.
					echo ( ! empty( $args['unit'] ) ) ? '<span class="sp_wqv--label sp_wqv--unit">' . esc_attr( $args['unit'] ) . '</span>' : '';
					echo '</div>';

				}
			}

			if ( ! empty( $args['style'] ) ) {
				echo '<div class="sp_wqv--input">';
				echo '<select name="' . esc_attr( $this->field_name( '[style]' ) ) . '">';
				foreach ( $border_props as $border_prop_key => $border_prop_value ) {
					$selected = ( $value['style'] === $border_prop_key ) ? ' selected' : '';
					echo '<option value="' . esc_attr( $border_prop_key ) . '"' . esc_attr( $selected ) . '>' . esc_attr( $border_prop_value ) . '</option>';
				}
				echo '</select>';
				echo '</div>';
			}

			echo '</div>';

			if ( ! empty( $args['color'] ) ) {
				$default_color_attr = ( ! empty( $default_value['color'] ) ) ? ' data-default-color="' . esc_attr( $default_value['color'] ) . '"' : '';
				echo '<div class="sp_wqv--color">';
				echo '<div class="sp_wqv-field-color">';
				echo '<input type="text" name="' . esc_attr( $this->field_name( '[color]' ) ) . '" value="' . esc_attr( $value['color'] ) . '" class="sp_wqv-color"' . $default_color_attr . ' />';// phpcs:ignore 
				// default_color_attr already escaping in the above.
				echo '</div>';
				echo '</div>';
			}
			if ( ! empty( $args['hover_color'] ) ) {
				$default_color_attr = ( ! empty( $default_value['hover_color'] ) ) ? ' data-default-color="' . esc_attr( $default_value['hover_color'] ) . '"' : '';
				echo '<div class="sp_wqv--color">';
				echo '<div class="sp_wqv-field-color">';
				echo '<input type="text" name="' . esc_attr( $this->field_name( '[hover_color]' ) ) . '" value="' . esc_attr( $value['hover_color'] ) . '" class="sp_wqv-color"' . $default_color_attr . ' />'; // phpcs:ignore 
				// default_color_attr already escaping in the above.
				echo '</div>';
				echo '</div>';
			}

			echo wp_kses_post( $this->field_after() );
		}
	}
}
