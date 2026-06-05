<?php
/**
 * Show options for ordering
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/orderby.php.
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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$id_suffix = wp_unique_id();

?>
<form class="woocommerce-ordering" method="get">
	<?php if ( $use_label ) : ?>
		<label for="woocommerce-orderby-<?php echo esc_attr( $id_suffix ); ?>"><?php echo esc_html__( 'Sort by', 'woocommerce' ); ?></label>
	<?php endif; ?>

    <div class="woocommerce-orderby-trigger">
		<?php
		$sorting_options = array(
			'menu_order' => __( 'Sắp xếp mặc định', TEXT_DOMAIN ),
			'popularity' => __( 'Xem nhiều nhất', TEXT_DOMAIN ),
			'rating'     => __( 'xếp hạng trung bình', TEXT_DOMAIN ),
			'date'       => __( 'Sản phẩm mới nhất', TEXT_DOMAIN ),
			'price-desc' => __( 'Giá cao đến thấp', TEXT_DOMAIN ),
			'price'      => __( 'Giá thấp đến cao', TEXT_DOMAIN ),
		);
		?>
        <span><?= $sorting_options[$orderby] ?? '' ?></span>
        <ul>
			<?php foreach ( $sorting_options as $id => $name ) :
				$class = (string)$orderby === (string)$id ? ' class="selected"' : '';
            ?>
            <li<?=$class?> data-id="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $name ); ?></li>
			<?php endforeach; ?>
        </ul>
    </div>

	<select
		name="orderby"
		class="orderby"
		<?php if ( $use_label ) : ?>
			id="woocommerce-orderby-<?php echo esc_attr( $id_suffix ); ?>"
		<?php else : ?>
			aria-label="<?php esc_attr_e( 'Shop order', 'woocommerce' ); ?>"
		<?php endif; ?>
	>
		<?php foreach ( $catalog_orderby_options as $id => $name ) : ?>
			<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $orderby, $id ); ?>><?php echo esc_html( $name ); ?></option>
		<?php endforeach; ?>
	</select>
	<input type="hidden" name="paged" value="1" />
	<?php wc_query_string_form_fields( null, array( 'orderby', 'submit', 'paged', 'product-page' ) ); ?>
</form>
