<?php
/**
 * Update version.
 *
 * @package Woo Quick View
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}  // if direct access.

update_option( 'woo_quick_view_version', '2.0.0' );
update_option( 'woo_quick_view_db_version', '2.0.0' );

$old_settings = get_option( '_sp_wqv_options' );

$old_settings['wqvpro_quick_view_button_position'] = isset( $old_settings['wqv_quick_view_button_position'] ) ? $old_settings['wqv_quick_view_button_position'] : 'after_add_to_cart';

$quick_view_button_color                          = isset( $old_settings['wqv_quick_view_button_color'] ) ? $old_settings['wqv_quick_view_button_color'] : '';
$old_settings['wqvpro_quick_view_button_color']   = array(
	'color1' => isset( $quick_view_button_color['color1'] ) ? $quick_view_button_color['color1'] : '#ffffff',
	'color2' => isset( $quick_view_button_color['color2'] ) ? $quick_view_button_color['color2'] : '#ffffff',
	'color3' => isset( $quick_view_button_color['color3'] ) ? $quick_view_button_color['color3'] : '#1A79BF',
	'color4' => isset( $quick_view_button_color['color4'] ) ? $quick_view_button_color['color4'] : '#176AA6',
);
$quick_view_button_padding                        = isset( $old_settings['wqv_quick_view_button_padding'] ) ? $old_settings['wqv_quick_view_button_padding'] : '';
$old_settings['wqvpro_quick_view_button_padding'] = array(
	'left'   => isset( $quick_view_button_padding['left'] ) ? $quick_view_button_padding['left'] : '16',
	'right'  => isset( $quick_view_button_padding['right'] ) ? $quick_view_button_padding['right'] : '16',
	'top'    => isset( $quick_view_button_padding['top'] ) ? $quick_view_button_padding['top'] : '9',
	'bottom' => isset( $quick_view_button_padding['bottom'] ) ? $quick_view_button_padding['bottom'] : '9',
);
$quick_view_button_border                         = isset( $old_settings['wqv_quick_view_button_border'] ) ? $old_settings['wqv_quick_view_button_border'] : '';
$old_settings['wqvpro_quick_view_button_border']  = array(
	'all'         => isset( $quick_view_button_border['all'] ) ? $quick_view_button_border['all'] : '0',
	'style'       => isset( $quick_view_button_border['style'] ) ? $quick_view_button_border['style'] : 'solid',
	'color'       => isset( $quick_view_button_border['color'] ) ? $quick_view_button_border['color'] : '#1A79BF',
	'hover_color' => isset( $quick_view_button_border['hover_color'] ) ? $quick_view_button_border['hover_color'] : '#176AA6',
);
$old_settings['wqvpro_quick_view_button_text']    = isset( $old_settings['wqv_quick_view_button_text'] ) ? $old_settings['wqv_quick_view_button_text'] : 'Quick View';
$old_settings['wqvpro_popup_bg']                  = isset( $old_settings['wqv_popup_overlay_bg'] ) ? $old_settings['wqv_popup_overlay_bg'] : 'rgba( 0, 0, 0, 0.8)';
$old_settings['wqvpro_popup_effect']              = isset( $old_settings['wqv_popup_effect'] ) ? $old_settings['wqv_popup_effect'] : 'mfp-move-from-top';
$old_settings['wqvpro_popup_close_button']        = isset( $old_settings['wqv_popup_close_button'] ) ? $old_settings['wqv_popup_close_button'] : true;
$old_settings['wqvpro_popup_close_btn_color']     = array(
	'color1' => isset( $old_settings['wqv_popup_close_btn_color']['color1'] ) ? $old_settings['wqv_popup_close_btn_color']['color1'] : '#9a9a9a',
	'color2' => isset( $old_settings['wqv_popup_close_btn_color']['color2'] ) ? $old_settings['wqv_popup_close_btn_color']['color2'] : '#ffffff',
);
$old_settings['wqvpro_popup_close_size']          = isset( $old_settings['wqv_popup_close_icon_size'] ) ? $old_settings['wqv_popup_close_icon_size'] : '18';
$old_settings['wqvpro_rating_start_color']        = array(
	'color1' => isset( $old_settings['wqv_rating_start_color']['color1'] ) ? $old_settings['wqv_rating_start_color']['color1'] : '#dadada',
	'color2' => isset( $old_settings['wqv_rating_start_color']['color2'] ) ? $old_settings['wqv_rating_start_color']['color2'] : '#ff9800',
);

$add_to_cart_btn_color                          = isset( $old_settings['wqv_add_to_cart_btn_color'] ) ? $old_settings['wqv_add_to_cart_btn_color'] : '';
$old_settings['wqvpro_add_to_cart_btn_color']   = array(
	'color1' => isset( $add_to_cart_btn_color['color1'] ) ? $add_to_cart_btn_color['color1'] : '#ffffff',
	'color2' => isset( $add_to_cart_btn_color['color2'] ) ? $add_to_cart_btn_color['color2'] : '#ffffff',
	'color3' => isset( $add_to_cart_btn_color['color3'] ) ? $add_to_cart_btn_color['color3'] : '#333333',
	'color4' => isset( $add_to_cart_btn_color['color4'] ) ? $add_to_cart_btn_color['color4'] : '#1a1a1a',
);
$add_to_cart_btn_padding                        = isset( $old_settings['wqv_add_to_cart_btn_padding'] ) ? $old_settings['wqv_add_to_cart_btn_padding'] : '';
$old_settings['wqvpro_add_to_cart_btn_padding'] = array(
	'left'   => isset( $add_to_cart_btn_padding['left'] ) ? $add_to_cart_btn_padding['left'] : '16',
	'right'  => isset( $add_to_cart_btn_padding['right'] ) ? $add_to_cart_btn_padding['right'] : '16',
	'top'    => isset( $add_to_cart_btn_padding['top'] ) ? $add_to_cart_btn_padding['top'] : '0',
	'bottom' => isset( $add_to_cart_btn_padding['bottom'] ) ? $add_to_cart_btn_padding['bottom'] : '0',
);
$old_settings['wqvpro_popup_box_bg']            = isset( $old_settings['wqv_popup_box_bg'] ) ? $old_settings['wqv_popup_box_bg'] : '#ffffff';
$old_settings['wqvpro_custom_css']              = isset( $old_settings['wqv_custom_css'] ) ? $old_settings['wqv_custom_css'] : '';

/**
 * Update settings.
 */
update_option( '_sp_wqv_options', $old_settings );
