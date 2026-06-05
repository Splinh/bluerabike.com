<?php
/**
 * Pagination - Show numbered pagination for catalog pages
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/pagination.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 */

\defined( 'ABSPATH' ) || die;

$total   = $total ?? wc_get_loop_prop( 'total_pages' );
$current = $current ?? wc_get_loop_prop( 'current_page' );
$base    = $base ?? esc_url_raw( str_replace( 999999999, '%#%', remove_query_arg( 'add-to-cart', get_pagenum_link( 999999999, false ) ) ) );
$format  = $format ?? '';

if ( $total <= 1 ) {
	return;
}

// http://codex.wordpress.org/Function_Reference/paginate_links
$paginate_links = paginate_links(
	apply_filters(
		'woocommerce_pagination_args',
		[   // WPCS: XSS ok.
			'base'      => $base,
			'format'    => $format,
			'add_args'  => false,
			'current'   => max( 1, $current ),
			'total'     => $total,
			'prev_next' => true,
			'prev_text' => '<i data-fa=""></i>',
			'next_text' => '<i data-fa=""></i>',
			'type'      => 'list',
			'end_size'  => 1,
			'mid_size'  => 2,
		]
	)
);

$paginate_links = str_replace(
	[
		"<ul class='page-numbers'>",
		'<li><span class="page-numbers dots">&hellip;</span></li>',
		'<li><span aria-current="page" class="page-numbers current">',
		'</span></li>',
	],
	[
		'<ul class="pagination page-numbers">',
		'<li class="ellipsis"></li>',
		'<li class="current"><span aria-current="page" class="sr-only">You\'re on page </span>',
		'</li>',
	],
	$paginate_links
);

$paginate_links = preg_replace( [ '/page-numbers\s*/', '/\s*class=""/' ], '', $paginate_links );

// Display the pagination if more than one page is found.
if ( $paginate_links ) {
	$paginate_links = '<nav class="nav-pagination woocommerce-pagination" aria-label="Pagination">' . $paginate_links . '</nav>';

	echo $paginate_links;
}
