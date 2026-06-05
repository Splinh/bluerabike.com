<?php
/**
 * The helper functionality of the plugin.
 *
 * @since      1.0.7
 * @package    Woo_Quick_View
 * @subpackage Woo_Quick_View/views
 * @author     ShapedPlugin <support@shapedplugin.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access directly.

if ( ! function_exists( 'sp_wqv_array_search' ) ) {
	/**
	 * Array search key & value
	 *
	 * @param  mixed $array array.
	 * @param  mixed $key key.
	 * @param  mixed $value value.
	 * @return array
	 */
	function sp_wqv_array_search( $array, $key, $value ) {

		$results = array();

		if ( is_array( $array ) ) {
			if ( isset( $array[ $key ] ) && $array[ $key ] == $value ) {
				$results[] = $array;
			}

			foreach ( $array as $sub_array ) {
				$results = array_merge( $results, sp_wqv_array_search( $sub_array, $key, $value ) );
			}
		}

		return $results;
	}
}

if ( ! function_exists( 'sp_wqv_timeout' ) ) {

	/**
	 * Between Microtime
	 *
	 * @param  mixed $timenow current time.
	 * @param  mixed $starttime start time.
	 * @param  mixed $timeout time out.
	 * @return boolean
	 */
	function sp_wqv_timeout( $timenow, $starttime, $timeout = 30 ) {
		return ( ( $timenow - $starttime ) < $timeout ) ? true : false;
	}
}

if ( ! function_exists( 'sp_wqv_wp_editor_api' ) ) {
	/**
	 * Check for wp editor api
	 *
	 * @return string
	 */
	function sp_wqv_wp_editor_api() {
		global $wp_version;
		return version_compare( $wp_version, '4.8', '>=' );
	}
}
