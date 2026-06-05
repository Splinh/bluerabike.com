<?php
/**
 * The dynamic style file.
 *
 * @since      1.0.7
 * @package    Woo_Quick_View
 * @subpackage Woo_Quick_View/includes
 * @author     ShapedPlugin <support@shapedplugin.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}  // if direct access.

$settings                      = get_option( '_sp_wqvpro_options' );
$wqvpro_custom_css             = isset( $settings['wqvpro_custom_css'] ) ? trim( html_entity_decode( $settings['wqvpro_custom_css'] ) ) : '';
$wqv_font_icon_size            = sp_wqv_get_option( 'wqvpro_font_icon_size' );
$modal_z_index                 = sp_wqv_get_option( 'wqvpro_modal_z_index' );
$popup_size                    = sp_wqv_get_option( 'wqvpro_modal_size' );
$quick_view_add_button_border  = sp_wqv_get_option( 'wqvpro_add_button_border' );
$quick_view_add_button_padding = sp_wqv_get_option( 'wqvpro_add_button_padding' );
$popup_max_width               = ! empty( $popup_size['top'] ) ? $popup_size['top'] : '876';
$popup_max_height              = ! empty( $popup_size['right'] ) ? $popup_size['right'] : '438';

// Quick view button color.
$quick_view_button_bg     = isset( $settings['wqvpro_quick_view_button_color'] ) ? $settings['wqvpro_quick_view_button_color'] : '';
$qv_button_color          = isset( $quick_view_button_bg['color1'] ) ? $quick_view_button_bg['color1'] : '#ffffff';
$qv_button_hover_color    = isset( $quick_view_button_bg['color2'] ) ? $quick_view_button_bg['color2'] : '#ffffff';
$qv_button_bg_color       = isset( $quick_view_button_bg['color3'] ) ? $quick_view_button_bg['color3'] : '#1A79BF';
$qv_button_bg_hover_color = isset( $quick_view_button_bg['color4'] ) ? $quick_view_button_bg['color4'] : '#176AA6';

// Quick view button padding.
$quick_view_button_padding = isset( $settings['wqvpro_quick_view_button_padding'] ) ? $settings['wqvpro_quick_view_button_padding'] : '';

$quick_view_top_bottom_padding = isset( $quick_view_button_padding['top'] ) ? $quick_view_button_padding['top'] : '9';
$quick_view_left_right_padding = isset( $quick_view_button_padding['left'] ) ? $quick_view_button_padding['left'] : '16';

// Quick View Button border.
$quick_view_button_border     = isset( $settings['wqvpro_quick_view_button_border'] ) ? $settings['wqvpro_quick_view_button_border'] : '';
$qv_button_border             = isset( $quick_view_button_border['all'] ) ? $quick_view_button_border['all'] : '0';
$qv_button_border_style       = isset( $quick_view_button_border['style'] ) ? $quick_view_button_border['style'] : 'solid';
$qv_button_border_color       = isset( $quick_view_button_border['color'] ) ? $quick_view_button_border['color'] : '#1A79BF';
$qv_button_border_hover_color = isset( $quick_view_button_border['hover_color'] ) ? $quick_view_button_border['hover_color'] : '#176AA6';

// Add to Cart button color.
$add_to_cart_btn_color = isset( $settings['wqvpro_add_to_cart_btn_bg'] ) ? $settings['wqvpro_add_to_cart_btn_bg'] : '';
$cart_color            = isset( $add_to_cart_btn_color['color1'] ) ? $add_to_cart_btn_color['color1'] : '#ffffff';
$cart_hover_color      = isset( $add_to_cart_btn_color['color2'] ) ? $add_to_cart_btn_color['color2'] : '#ffffff';
$cart_bg_color         = isset( $add_to_cart_btn_color['color3'] ) ? $add_to_cart_btn_color['color3'] : '#333333';
$cart_bg_hover_color   = isset( $add_to_cart_btn_color['color4'] ) ? $add_to_cart_btn_color['color4'] : '#1a1a1a';

// Add to cart padding.
$add_to_cart_btn_padding = isset( $settings['wqvpro_add_to_cart_btn_padding'] ) ? $settings['wqvpro_add_to_cart_btn_padding'] : '';
$cart_top_bottom_padding = isset( $add_to_cart_btn_padding['top'] ) ? $add_to_cart_btn_padding['top'] : '0';
$cart_left_right_padding = isset( $add_to_cart_btn_padding['left'] ) ? $add_to_cart_btn_padding['left'] : '16';

// Rating star color.
$rating_start_color = isset( $settings['wqvpro_rating_start_color'] ) ? $settings['wqvpro_rating_start_color'] : '';
$empty_color        = isset( $rating_start_color['color1'] ) ? $rating_start_color['color1'] : '#dadada';
$full_color         = isset( $rating_start_color['color2'] ) ? $rating_start_color['color2'] : '#ff9800';
$popup_box_bg       = isset( $settings['wqvpro_popup_box_bg'] ) ? $settings['wqvpro_popup_box_bg'] : '#ffffff';
$qv_iphone_android  = isset( $settings['wqvpro_add_iphone_android'] ) ? $settings['wqvpro_add_iphone_android'] : false;
$qv_add_ipad        = isset( $settings['wqvpro_add_ipad'] ) ? $settings['wqvpro_add_ipad'] : false;

// Content Padding.
$qv_content_padding = isset( $settings['wqvpro_content_padding']['bottom'] ) ? $settings['wqvpro_content_padding']['bottom'] : '';

$modal_bg = isset( $settings['wqvpro_popup_bg'] ) ? $settings['wqvpro_popup_bg'] : 'rgba( 0, 0, 0, 0.8)';
// Close icon color.
$popup_close_icon_color = isset( $settings['wqvpro_popup_close_color'] ) ? $settings['wqvpro_popup_close_color'] : '';
$close_icon_color       = isset( $popup_close_icon_color['color1'] ) ? $popup_close_icon_color['color1'] : '#9a9a9a';
$close_icon_hover_color = isset( $popup_close_icon_color['color2'] ) ? $popup_close_icon_color['color2'] : '#ffffff';
$popup_close_icon_size  = isset( $settings['wqvpro_popup_close_size'] ) ? $settings['wqvpro_popup_close_size'] : '18';

$wqvpro_loading_color = isset( $settings['wqvpro_loading_color'] ) ? $settings['wqvpro_loading_color'] : 'ffffff';


/**
 * Custom CSS
 */
$custom_css = '
.mfp-bg.mfp-wqv,
.mfp-wrap.mfp-wqv {
	z-index: ' . $modal_z_index . ';
}

.wqvp-fancybox-wrapper, .mfp-wqv~.fancybox-container {
	z-index: ' . ( $modal_z_index . 9 ) . ';
}
#wqv-quick-view-content .wqv-product-info .woocommerce-product-rating .star-rating::before{
	color: ' . $empty_color . ';
	opacity: 1;
}
#wqv-quick-view-content .wqv-product-info .woocommerce-product-rating .star-rating span:before{
	color: ' . $full_color . ';
}
#wqv-quick-view-content .wqv-product-info a.added_to_cart,
#wqv-quick-view-content .wqv-product-info .single_add_to_cart_button.button:not(.components-button):not(.customize-partial-edit-shortcut-button){
	color: ' . $cart_color . ';
	background: ' . $cart_bg_color . ';
	padding: ' . $cart_top_bottom_padding . 'px ' . $cart_left_right_padding . 'px;
	line-height: 35px;
}
#wqv-quick-view-content .wqv-product-info a.added_to_cart:hover,
#wqv-quick-view-content .wqv-product-info .single_add_to_cart_button.button:not(.components-button):not(.customize-partial-edit-shortcut-button):hover {
	color: ' . $cart_hover_color . ';
	background: ' . $cart_bg_hover_color . ';
}
#wqv-quick-view-content .wqv-product-info .single_add_to_cart_button.button:not(.components-button):not(.customize-partial-edit-shortcut-button){
	margin-right: 5px;
}
a#sp-wqv-view-button.button.sp-wqv-view-button,
#wps-slider-section .button.sp-wqv-view-button,
#wpsp-slider-section .button.sp-wqv-view-button {
	background: ' . $qv_button_bg_color . ';
	color: ' . $qv_button_color . ';
}
a#sp-wqv-view-button.button.sp-wqv-view-button:hover,
#wps-slider-section .button.sp-wqv-view-button:hover,
#wpsp-slider-section .button.sp-wqv-view-button:hover {
	background: ' . $qv_button_bg_hover_color . ';
	color: ' . $qv_button_hover_color . ';
}
#wqv-quick-view-content.sp-wqv-content {
	background: ' . $popup_box_bg . ';
}

@media (min-width: 1023px) {
	#wqv-quick-view-content.sp-wqv-content {
		max-width: ' . $popup_max_width . 'px;
		max-height: ' . $popup_max_height . 'px;
	}
	#wqv-quick-view-content .wqv-product-images img,
	#wqv-quick-view-content .wqv-product-images img{
		max-height: ' . $popup_max_height . 'px;
	}
	.wqv-product-info .wqv-product-content{
		height:100%;
		padding: ' . $qv_content_padding . 'px;
		overflow: auto;
		max-height: ' . $popup_max_height . 'px;
		max-width: ' . $popup_max_width . 'px;
		position: relative;
	}
}
.mfp-bg.mfp-wqv{
	background: ' . $modal_bg . ';
	opacity: 1;
}
.mfp-wqv #wqv-quick-view-content .mfp-close{
	width: 35px;
    height: 35px;
    opacity: 1;
    cursor: pointer;
    top: 0px;
    right: 0;
    position: absolute;
    background: transparent;
    font-size: 0;
}

.mfp-wqv #wqv-quick-view-content .mfp-close:before{
	color: ' . $close_icon_color . ';
	font-size: ' . $popup_close_icon_size . 'px;
    transition: .2s;
    margin-top: 8px;
}
.wqv-product-info{
	padding: ' . $qv_content_padding . 'px;

}
.mfp-preloader{
	color: ' . $wqvpro_loading_color . ';
}
.mfp-wqv #wqv-quick-view-content .mfp-close:hover {
    background: #F95600;
    font-size: 0;
    border-radius: 0px;
}
.mfp-wqv #wqv-quick-view-content .mfp-close:hover:before{
	color: ' . $close_icon_hover_color . ';
}
#sp-wqv-view-button.sp-wqv-view-button.button i:before{
	font-size: ' . $wqv_font_icon_size . 'px;
}
';
// make sure border checkbox is checked before adding border style on the button.
if ( $quick_view_add_button_border || (int) $qv_button_border > 0 ) {
	$custom_css .= 'a#sp-wqv-view-button.button.sp-wqv-view-button,
	#wps-slider-section .button.sp-wqv-view-button,
	#wpsp-slider-section .button.sp-wqv-view-button {
		border: ' . $qv_button_border . 'px ' . $qv_button_border_style . ' ' . $qv_button_border_color . ';
	}
	a#sp-wqv-view-button.button.sp-wqv-view-button:hover,
	#wps-slider-section .button.sp-wqv-view-button:hover,
	#wpsp-slider-section .button.sp-wqv-view-button:hover {
		border-color: ' . $qv_button_border_hover_color . ';
}';
}
// make sure padding checkbox is checked before adding button padding.
if ( $quick_view_add_button_padding || $quick_view_top_bottom_padding > 0 ) {
	$custom_css .= 'a#sp-wqv-view-button.button.sp-wqv-view-button,
	#wps-slider-section .button.sp-wqv-view-button,
	#wpsp-slider-section .button.sp-wqv-view-button {
		padding: ' . $quick_view_top_bottom_padding . 'px ' . $quick_view_left_right_padding . 'px;
	}';
}

if ( ! $qv_iphone_android ) {
	$custom_css .= '@media all and (max-width: 480px){
		#sp-wqv-view-button.sp-wqv-view-button.button{
		 display: none !important;
	   }
	  }';
}
if ( ! $qv_add_ipad ) {
	$custom_css .= '@media all and (min-width: 481px) and (max-width: 768px) {
		#sp-wqv-view-button.sp-wqv-view-button.button{
		  display: none !important;
		}
	  }';
}
if ( $wqvpro_custom_css ) {
	$custom_css .= $wqvpro_custom_css;
}
