<?php
/**
 * The advance dimension field of the plugin.
 *
 * @link        http://shapedplugin.com/
 * @since      1.0.0
 *
 * @package    Woo_Quick_View_Pro
 * @subpackage Woo_Quick_View_Pro/sp-framework
 * @author     ShapedPlugin <support@shapedplugin.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access directly.

if ( ! class_exists( 'SP_WQV_Framework_Field_dimensions_advanced' ) ) {

	/**
	 * The Advanced Dimensions field class.
	 *
	 * @since 3.5
	 */
	class SP_WQV_Framework_Field_dimensions_advanced extends SP_WQV_Framework_Fields {

		/**
		 * Advanced Dimensions field constructor.
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
					'top_icon'           => '<i class="fa fa-long-arrow-up"></i>',
					'right_icon'         => '<i class="fa fa-long-arrow-right"></i>',
					'left_icon'          => '<i class="fa fa-long-arrow-left"></i>',
					'bottom_icon'        => '<i class="fa fa-long-arrow-down"></i>',
					'all_icon'           => '<i class="fa fa-arrows"></i>',
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
					'style'              => true,
					'styles'             => array( 'Soft-crop', 'Hard-crop' ),
					'unit'               => 'px',
					'min'                => '0',
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
				'min'    => '',
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

			$default_value = ( ! empty( $this->field['default'] ) ) ? wp_parse_args( $this->field['default'], $default_value ) : $default_value;

			$value = wp_parse_args( $this->value, $default_value );

			echo wp_kses_post( $this->field_before() );

			$min = ( isset( $args['min'] ) ) ? ' min="' . $args['min'] . '"' : '';
			if ( ! empty( $args['all'] ) ) {

				$placeholder = ( ! empty( $args['all_placeholder'] ) ) ? ' placeholder="' . $args['all_placeholder'] . '"' : '';

				echo '<div class="sp_wqv--left sp_wqv--input">';
				echo ( ! empty( $args['all_icon'] ) ) ? '<span class="sp_wqv--label sp_wqv--label-icon">' . wp_kses_post( $args['all_icon'] ) . '</span>' : '';
				echo '<input type="number" name="' . esc_attr( $this->field_name( '[all]' ) ) . '" value="' . esc_attr( $value['all'] ) . '"' . wp_kses_post( $placeholder . $min ) . ' class="sp_wqv-number" />';
				echo ( ! empty( $args['unit'] ) ) ? '<span class="sp_wqv--label sp_wqv--label-unit">' . esc_html( $args['unit'] ) . '</span>' : '';
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

					$placeholder = ( ! empty( $args[ $property . '_placeholder' ] ) ) ? ' placeholder="' . $args[ $property . '_placeholder' ] . '"' : '';

					echo '<div class="sp_wqv--left sp_wqv--input">';
					echo ( ! empty( $args[ $property . '_icon' ] ) ) ? '<span class="sp_wqv--label sp_wqv--label-icon">' . wp_kses_post( $args[ $property . '_icon' ] ) . '</span>' : '';
					echo '<input type="number" name="' . esc_attr( $this->field_name( '[' . $property . ']' ) ) . '" value="' . esc_attr( $value[ $property ] ) . '"' . wp_kses_post( $placeholder . $min ) . ' class="sp_wqv-number" />';
					echo ( ! empty( $args['unit'] ) ) ? '<span class="sp_wqv--label sp_wqv--label-unit">' . esc_html( $args['unit'] ) . '</span>' : '';
					echo '</div>';

				}
			}

			if ( ! empty( $args['style'] ) ) {
				echo '<div class="sp_wqv--left sp_wqv--input">';
				echo '<select name="' . esc_attr( $this->field_name( '[style]' ) ) . '">';
				foreach ( $args['styles'] as $style_prop ) {
					$selected = ( $value['style'] === $style_prop ) ? ' selected' : '';
					echo '<option value="' . esc_attr( $style_prop ) . '"' . esc_attr( $selected ) . '>' . esc_html( $style_prop ) . '</option>';
				}
				echo '</select>';
				echo '</div>';
			}

			if ( ! empty( $args['color'] ) ) {
				$default_color_attr = ( ! empty( $default_value['color'] ) ) ? $default_value['color'] : '';
				echo '<div class="sp_wqv--left sp_wqv-field-color">';
				echo '<input type="text" name="' . esc_attr( $this->field_name( '[color]' ) ) . '" value="' . esc_attr( $value['color'] ) . '" class="sp_wqv-color" data-default-color="' . esc_attr( $default_color_attr ) . '" />';
				echo '</div>';
			}

			echo '<div class="clear"></div>';

			echo wp_kses_post( $this->field_after() );
		}
	}
}
