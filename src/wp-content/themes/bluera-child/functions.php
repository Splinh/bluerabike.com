<?php
/**
 * Theme functions and definitions
 *
 * @author SPL
 */

add_action( 'wp_enqueue_scripts', '_wp_enqueue_scripts', 99 );
function _wp_enqueue_scripts(): void {
	$version = \HD_Helper::version();
	\HD_Asset::enqueueStyle( '_style', get_stylesheet_directory_uri() . '/style.css', [ 'index-css' ], $version );
	\HD_Asset::enqueueScript( '_script', get_stylesheet_directory_uri() . '/script.js', [ 'index-js' ], $version, true, [ 'defer' ] );
}
