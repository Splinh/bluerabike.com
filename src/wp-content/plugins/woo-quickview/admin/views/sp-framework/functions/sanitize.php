<?php
/**
 * The sanitize functionality of the plugin.
 *
 * @since      1.0.7
 * @package    Woo_Quick_View
 * @subpackage Woo_Quick_View/views
 * @author     ShapedPlugin <support@shapedplugin.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access directly.

if ( ! function_exists( 'sp_wqv_sanitize_replace_a_to_b' ) ) {
	/**
	 * Sanitize
	 * Replace letter a to letter b
	 *
	 * @param  mixed $value value.
	 * @return string
	 */
	function sp_wqv_sanitize_replace_a_to_b( $value ) {
		return str_replace( 'a', 'b', $value );
	}
}

if ( ! function_exists( 'sp_wqv_sanitize_title' ) ) {

	/**
	 * Sanitize title
	 *
	 * @param  mixed $value title.
	 * @return string
	 */
	function sp_wqv_sanitize_title( $value ) {
		return sanitize_title( $value );
	}
}
