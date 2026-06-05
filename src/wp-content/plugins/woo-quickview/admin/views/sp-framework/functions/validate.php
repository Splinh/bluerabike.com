<?php
/**
 * The validate file of the plugin.
 *
 * @since      1.0.7
 * @package    Woo_Quick_View
 * @subpackage Woo_Quick_View/views
 * @author     ShapedPlugin <support@shapedplugin.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access directly.

if ( ! function_exists( 'sp_wqv_validate_email' ) ) {
	/**
	 *
	 * Email validate
	 *
	 * @param string $value email address.
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	function sp_wqv_validate_email( $value ) {

		if ( ! filter_var( $value, FILTER_VALIDATE_EMAIL ) ) {
			return esc_html__( 'Please enter a valid email address.', 'woo-quickview' );
		}
	}
}

if ( ! function_exists( 'sp_wqv_validate_numeric' ) ) {
	/**
	 *
	 * Numeric validate
	 *
	 * @param mixed $value number.
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	function sp_wqv_validate_numeric( $value ) {

		if ( ! is_numeric( $value ) ) {
			return esc_html__( 'Please enter a valid number.', 'woo-quickview' );
		}
	}
}

if ( ! function_exists( 'sp_wqv_validate_required' ) ) {
	/**
	 * Required validate
	 *
	 * @param  mixed $value value.
	 * @return checkdate
	 */
	function sp_wqv_validate_required( $value ) {

		if ( empty( $value ) ) {
			return esc_html__( 'This field is required.', 'woo-quickview' );
		}
	}
}

if ( ! function_exists( 'sp_wqv_validate_url' ) ) {
	/**
	 * URL validate
	 *
	 * @param  mixed $value value.
	 * @return url
	 */
	function sp_wqv_validate_url( $value ) {

		if ( ! filter_var( $value, FILTER_VALIDATE_URL ) ) {
			return esc_html__( 'Please enter a valid URL.', 'woo-quickview' );
		}
	}
}
