<?php require_once('wp-load.php'); 
var_dump(\HD\Core\Helper::isWoocommerceActive());
var_dump(\HD\Modules\PLL\PLLModule::isWCActive());
var_dump(has_filter('pll_get_post_types'));
