<?php
// wishlist-icon.php

\defined( 'ABSPATH' ) || die;

if ( ! defined( 'YITH_WCWL_INC' ) ) {
	return;
}

$wishlist_count = YITH_WCWL()->count_products();
$wishlist_link  = YITH_WCWL()->get_wishlist_url();

?>
<div class="wishlist-icon">
    <a href="<?php echo $wishlist_link; ?>" data-fa="" title="<?php echo __( 'Danh sách yêu thích', TEXT_DOMAIN ); ?>">
        <span class="wishlist-count"><?php echo $wishlist_count; ?></span>
    </a>
</div>
