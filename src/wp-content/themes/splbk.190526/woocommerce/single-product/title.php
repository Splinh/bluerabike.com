<?php
/**
 * Single Product title
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/title.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see        https://woocommerce.com/document/template-structure/
 * @package    WooCommerce\Templates
 */

use Automattic\WooCommerce\Enums\ProductType;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $product;

echo '<h1 class="product_title entry-title">';
    $brands = wp_get_post_terms( $product->get_id(), 'product_brand' );
    if ( ! empty( $brands ) && ! is_wp_error( $brands ) ) {
        $brand = $brands[0];
        echo '<a class="brand-name" href="' . get_term_link( $brand, 'product_brand' ) . '" title="' . esc_attr( $brand->name ) . '">' . $brand->name . '</a>';
    }

    the_title( '<span>', '</span>' );

echo '</h1>';

if ( wc_product_sku_enabled() && ( $product->get_sku() || $product->is_type( ProductType::VARIABLE ) ) ) : ?>
    <span class="sku_wrapper"><?php esc_html_e( 'SKU:', 'woocommerce' ); ?> <span class="sku"><?php echo ( $sku = $product->get_sku() ) ? $sku : esc_html__( 'N/A', 'woocommerce' ); ?></span></span>
<?php endif;
