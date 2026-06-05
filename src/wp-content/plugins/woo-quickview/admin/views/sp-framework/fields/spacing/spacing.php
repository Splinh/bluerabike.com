<?php
/**
 * The spacing field of the plugin.
 *
 * @since      1.0.7
 * @package    Woo_Quick_View
 * @subpackage Woo_Quick_View/views
 * @author     ShapedPlugin <support@shapedplugin.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access directly.

if ( ! class_exists( 'SP_WQV_Framework_Field_spacing' ) ) {
	/**
	 *
	 * Field: spacing
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	class SP_WQV_Framework_Field_spacing extends SP_WQV_Framework_Fields {

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
					'top_icon'           => '<i class="fa fa-arrows-v"></i>',
					'right_icon'         => '<i class="fas fa-long-arrow-alt-right"></i>',
					'bottom_icon'        => '<i class="fas fa-arrows-alt"></i>',
					'left_icon'          => '<i class="fa fa-arrows-h"></i>',
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
					'unit'               => true,
					'show_units'         => true,
					'all'                => false,
					'units'              => array( 'px', '%', 'em' ),
				)
			);

			$default_values = array(
				'top'    => '',
				'right'  => '',
				'bottom' => '',
				'left'   => '',
				'all'    => '',
				'unit'   => 'px',
			);
			$allowed_tags   = array(
				'i' => array(
					'class' => array(
						'fa',
					),
				),
			);

			$value   = wp_parse_args( $this->value, $default_values );
			$unit    = ( count( $args['units'] ) === 1 && ! empty( $args['unit'] ) ) ? $args['units'][0] : '';
			$is_unit = ( ! empty( $unit ) ) ? ' sp_wqv--is-unit' : '';

			echo wp_kses_post( $this->field_before() );

			echo '<div class="sp_wqv--inputs" data-depend-id="' . esc_attr( $this->field['id'] ) . '">';

			if ( ! empty( $args['all'] ) ) {

				$placeholder = ( ! empty( $args['all_placeholder'] ) ) ? ' placeholder="' . esc_attr( $args['all_placeholder'] ) . '"' : '';

				echo '<div class="sp_wqv--input">';
				echo ( ! empty( $args['all_icon'] ) ) ? '<span class="sp_wqv--label sp_wqv--icon">' . wp_kses( $args['all_icon'], $allowed_tags ) . '</span>' : '';
				echo '<input type="number" name="' . esc_attr( $this->field_name( '[all]' ) ) . '" value="' . esc_attr( $value['all'] ) . '"' . $placeholder . ' class="sp_wqv-input-number' . esc_attr( $is_unit ) . '" step="any" />';// phpcs:ignore
				// placeholder already escaping in the above.
				echo ( $unit ) ? '<span class="sp_wqv--label sp_wqv--unit">' . esc_attr( $args['units'][0] ) . '</span>' : '';
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

					echo '<div class="sp_wqv--spacing-input">';
					if ( 'top' === $property || 'left' === $property ) {
						$spacing_title = ( 'top' === $property ) ? 'Top-Bottom' : 'Left-Right';
						echo '<div class="sp_wqv--title">' . esc_html( $spacing_title ) . '</div>';
					}

					$placeholder = ( ! empty( $args[ $property . '_placeholder' ] ) ) ? ' placeholder="' . esc_attr( $args[ $property . '_placeholder' ] ) . '"' : '';

					echo '<div class="sp_wqv--input">';
					echo ( ! empty( $args[ $property . '_icon' ] ) ) ? '<span class="sp_wqv--label sp_wqv--icon">' . wp_kses( $args[ $property . '_icon' ], $allowed_tags ) . '</span>' : '';
					echo '<input type="number" name="' . esc_attr( $this->field_name( '[' . $property . ']' ) ) . '" value="' . esc_attr( $value[ $property ] ) . '"' . $placeholder . ' class="sp_wqv-input-number' . esc_attr( $is_unit ) . '" step="any" />';// phpcs:ignore
					// placeholder already escaping in the above.
					echo ( $unit ) ? '<span class="sp_wqv--label sp_wqv--unit">' . esc_attr( $args['units'][0] ) . '</span>' : '';
					echo '</div>';
					echo '</div>';

				}
			}

			if ( ! empty( $args['unit'] ) && ! empty( $args['show_units'] ) && count( $args['units'] ) > 1 ) {
				echo '<div class="sp_wqv--input">';
				echo '<select name="' . esc_attr( $this->field_name( '[unit]' ) ) . '">';
				foreach ( $args['units'] as $unit ) {
					$selected = ( $value['unit'] === $unit ) ? ' selected' : '';
					echo '<option value="' . esc_attr( $unit ) . '"' . esc_attr( $selected ) . '>' . esc_attr( $unit ) . '</option>';
				}
				echo '</select>';
				echo '</div>';
			}

			echo '</div>';

			echo wp_kses_post( $this->field_after() );
		}
	}
}
