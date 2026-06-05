<?php
/**
 * Single Product Sale Flash
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/sale-flash.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 */

use HD\Integration\WooCommerce\WOO_Helper;

defined( 'ABSPATH' ) || exit;

global $post, $product;

if ( $product->is_on_sale() ) {

	$sale_flash = WOO_Helper::wc_sale_flash_percent( $product );
	if ( $sale_flash ) {
		echo apply_filters( 'woocommerce_sale_flash', '<div class="saleoff onsale"><span>-' . $sale_flash . '%</span></div>', $post, $product );
	} else {
		echo apply_filters( 'woocommerce_sale_flash', '<div class="saleoff onsale"><span>' . esc_html__( 'Sale!', 'woocommerce' ) . '</span></div>', $post, $product );
	}
}

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
