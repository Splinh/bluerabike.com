<?php

namespace HD\Integration\WooCommerce;

\defined( 'ABSPATH' ) || die;

/**
 * WOO_Helper Class
 *
 * @author Gaudev
 */
final class WOO_Helper {
	// -------------------------------------------------------------

	/**
	 * @param string $orderby
	 *
	 * @return string[]
	 */
	public static function wc_get_catalog_ordering_args( string $orderby = 'menu_order' ): array {
		$args = [
			'orderby'  => 'menu_order',
			'order'    => 'asc',
			'meta_key' => '',
		];

		switch ( $orderby ) {
			case 'price':
				$args['orderby']  = 'meta_value_num';
				$args['order']    = 'asc';
				$args['meta_key'] = '_price';
				break;

			case 'price-desc':
				$args['orderby']  = 'meta_value_num';
				$args['order']    = 'desc';
				$args['meta_key'] = '_price';
				break;

			case 'popularity':
				$args['orderby']  = 'meta_value_num';
				$args['order']    = 'desc';
				$args['meta_key'] = 'total_sales';
				break;

			case 'rating':
				$args['orderby']  = 'meta_value_num';
				$args['order']    = 'desc';
				$args['meta_key'] = '_wc_average_rating';
				break;

			case 'date':
				$args['orderby'] = 'date';
				$args['order']   = 'desc';
				break;

			case 'title':
				$args['orderby'] = 'title';
				$args['order']   = 'asc';
				break;

			case 'title-desc':
				$args['orderby'] = 'title';
				$args['order']   = 'desc';
				break;

			case 'relevance':
				$args['orderby'] = 'relevance';
				break;

			case 'menu_order':
			default:
				$args['orderby'] = [ 'menu_order' => 'asc', 'title' => 'asc' ];
				$args['order']   = '';
				break;
		}

		return $args;
	}


	// -------------------------------------------------------------

	/**
	 * @param $product
	 *
	 * @return float|string
	 */
	public static function wc_sale_flash_percent( $product ): float|string {
		global $product;

		$percent_off = '';
		if ( $product->is_on_sale() ) {
			if ( $product->is_type( 'variable' ) && $product->get_variation_regular_price( 'min' ) ) {
				$percent_off = ceil( 100 - ( $product->get_variation_sale_price() / $product->get_variation_regular_price( 'min' ) ) * 100 );
			} elseif ( $product->get_regular_price() && ! $product->is_type( 'grouped' ) ) {
				$percent_off = ceil( 100 - ( $product->get_sale_price() / $product->get_regular_price() ) * 100 );
			}
		}

		return $percent_off;
	}
}
