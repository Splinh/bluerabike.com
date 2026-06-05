<?php
/**
 * This class defines all code necessary to run during the popup open.
 *
 * @since      1.0.7
 * @package    Woo_Quick_View
 * @subpackage Woo_Quick_View/includes
 * @author     ShapedPlugin <support@shapedplugin.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Woo Quick View- popup class
 *
 * @since 1.0
 */
class SP_WQV_Popup {

	/**
	 * GetInstance
	 *
	 * @var SP_WQV_Popup single instance of the class
	 *
	 * @since 1.0
	 */
	protected static $_instance = null;

	/**
	 * The popup self instance.
	 *
	 * @since 1.0
	 *
	 * @static
	 *
	 * @return SP_WQV_Popup
	 */
	public static function getInstance() {
		if ( ! self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Initialize the class
	 */
	public function __construct() {
		add_action( 'wp_ajax_wqv_popup_content', array( $this, 'wqv_popup_content' ) );
		add_action( 'wp_ajax_nopriv_wqv_popup_content', array( $this, 'wqv_popup_content' ) );

		add_action( 'wc_ajax_sp_ajax_add_cart', array( $this, 'ql_woocommerce_ajax_add_to_cart' ) );

		$plugin_setting       = get_option( '_sp_wqvpro_options' );
		$show_product_content = isset( $plugin_setting['wqvpro_show_product_content'] ) ? $plugin_setting['wqvpro_show_product_content'] : '';
		$show_title           = isset( $show_product_content['title'] ) ? $show_product_content['title'] : true;
		$show_price           = isset( $show_product_content['price'] ) ? $show_product_content['price'] : true;
		$show_excerpt         = isset( $show_product_content['excerpt'] ) ? $show_product_content['excerpt'] : true;
		$show_add_to_cart     = isset( $show_product_content['add_to_cart'] ) ? $show_product_content['add_to_cart'] : true;
		$show_rating          = isset( $show_product_content['rating'] ) ? $show_product_content['rating'] : true;
		$show_meta            = isset( $show_product_content['meta'] ) ? $show_product_content['meta'] : true;
		if ( $show_title ) {
			add_action( 'wqv_product_content', 'woocommerce_template_single_title', 5 );
		}
		if ( $show_rating ) {
			add_action( 'wqv_product_content', 'woocommerce_template_single_rating', 10 );
		}
		if ( $show_price ) {
			add_action( 'wqv_product_content', 'woocommerce_template_single_price', 15 );
		}
		if ( $show_excerpt ) {
			add_action( 'wqv_product_content', 'woocommerce_template_single_excerpt', 20 );
		}
		if ( $show_add_to_cart ) {
			add_action( 'wqv_product_content', 'woocommerce_template_single_add_to_cart', 25 );
		}
		if ( $show_meta ) {
			add_action( 'wqv_product_content', 'woocommerce_template_single_meta', 30 );
		}
		$redirect_after_cart = isset( $plugin_setting['wqvpro_redirect_after_cart'] ) ? $plugin_setting['wqvpro_redirect_after_cart'] : '';
		if ( 'same_page' === $redirect_after_cart ) {
			add_filter( 'woocommerce_add_to_cart_form_action', array( $this, 'avoid_redirect_to_single_page' ), 10, 1 );
		}
	}

	/**
	 * Check if is quick view
	 *
	 * @access public
	 * @since  1.3.1
	 * @return bool
	 */
	public function wqv_is_quick_view() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return ( defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $_REQUEST['action'] ) && 'wqv_popup_content' === $_REQUEST['action'] );
	}

	/**
	 * Avoid redirect to single product page on add to cart action in quick view
	 *
	 * @since  1.3.1
	 * @param string $value The redirect url value.
	 * @return string
	 */
	public function avoid_redirect_to_single_page( $value ) {
		if ( $this->wqv_is_quick_view() ) {
			return '';
		}
		return $value;
	}

	/**
	 * Handles AJAX request for adding a product to the cart in WooCommerce.
	 *
	 * This function processes an AJAX request to add a product to the cart. It retrieves
	 * the product ID from the request parameters and triggers an action related to the
	 * added product. It also refreshes the cart fragments to update the cart display.
	 */
	public function ql_woocommerce_ajax_add_to_cart() {
		// Disable nonce check for read-only data.
		// No need to use nonce for read-only data.

		// Initialize the product ID variable.
		$product_id = 0;

		// Retrieve the product ID from the 'product_id' parameter.
		if ( ! empty( $_POST['product_id'] ) ) { // phpcs:ignore -- WordPress.Security.NonceVerification.Recommended
			$product_id = apply_filters( 'ql_woocommerce_add_to_cart_product_id', absint( $_POST['product_id'] ) ); // phpcs:ignore -- WordPress.Security.NonceVerification.Recommended
		}

		// If product ID is still not set, retrieve it from the 'add-to-cart' parameter.
		if ( empty( $product_id ) && ! empty( $_POST['add-to-cart'] ) ) {  // phpcs:ignore
			$product_id = apply_filters( 'ql_woocommerce_add_to_cart_product_id', absint( $_POST['add-to-cart'] ) ); // phpcs:ignore -- WordPress.Security.NonceVerification.Recommended
		}

		// Trigger the 'ql_woocommerce_ajax_added_to_cart' action if product ID is set.
		if ( ! empty( $product_id ) ) {
			do_action( 'ql_woocommerce_ajax_added_to_cart', $product_id );
		}

		// Refresh the cart fragments to update the cart display.
		WC_Ajax::get_refreshed_fragments();
	}


	/**
	 * Popup Content
	 *
	 * @return void
	 */
	public function wqv_popup_content() {
		global $post, $product;

		if ( isset( $_GET['product_id'] ) ) { // phpcs:ignore -- WordPress.Security.NonceVerification.Recommended
			$post = get_post( sanitize_text_field( wp_unslash( $_GET['product_id'] ) ) ); // phpcs:ignore
		}
		setup_postdata( $post );
		$post_id   = $post->ID;
		$product   = wc_get_product( $post_id );
		$thumb_ids = array();
		$image_ids = $product->get_gallery_image_ids();

		$thumb_ids      = array_merge( $thumb_ids, $image_ids );
		$plugin_setting = get_option( '_sp_wqvpro_options' );
		$image_lightbox = isset( $plugin_setting['wqvpro_product_image_lightbox'] ) ? $plugin_setting['wqvpro_product_image_lightbox'] : false;

		?>

		<div id="wqv-quick-view-content" class="mfp-with-anim sp-wqv-content sp-wqv-content-<?php echo esc_attr( get_the_id() ); ?>" data-id="<?php echo esc_attr( get_the_id() ); ?>">

			<div class="wqv-product-images" data-attachments="<?php echo count( $thumb_ids ); ?>">
				<div class="wqv-product-images-slider">
					<?php
					if ( has_post_thumbnail( $post->ID ) ) {
						$attachment_id = get_post_thumbnail_id();
						$image_title   = get_the_title( $attachment_id );
						$props         = wc_get_product_attachment_props( $attachment_id, $post );
						if ( $image_lightbox ) {
							$lightbox_start = '<a data-fancybox="wqvpro-gallery" title="' . esc_attr( $image_title ) . '" " href="' . esc_url( $props['url'] ) . '">';
							$lightbox_end   = '</a>';
						} else {
							$lightbox_start = '';
							$lightbox_end   = '';
						}
						$image       = get_the_post_thumbnail( $post->ID, apply_filters( 'single_product_large_thumbnail_size', 'shop_single' ), 0, $props );
						$thumb_image = wp_get_attachment_image_src( $attachment_id, apply_filters( 'single_product_small_thumbnail_size', 'shop_thumbnail' ), 0 );
						$thumb_image = is_array( $thumb_image ) ? $thumb_image : array( '' );
						echo apply_filters( // phpcs:ignore
							'woocommerce_single_product_image_html',
							sprintf(
								'<span data-thumb="%s" class="selected" itemprop="image" title="%s">%s%s%s</span>',
								$thumb_image[0],
								esc_attr( $props['caption'] ),
								$lightbox_start,
								$image,
								$lightbox_end
							),
							$post->ID
						);

					} else {
						echo apply_filters( 'woocommerce_single_product_image_html', sprintf( '<span data-thumb="%s" class="selected" itemprop="image"><img src="%s" alt="%s" /></span>', wc_placeholder_img_src(), wc_placeholder_img_src(), __( 'Placeholder', 'woo-quickview' ) ), $post->ID ); // phpcs:ignore
					}
					?>
				</div> <!-- wqv-product-images-slider -->

			</div>

			<div class="wqv-product-info">
				<div class="wqv-product-content">

				<?php do_action( 'wqv_product_content', $post_id ); ?>

				</div>
			</div>

		</div>
		<?php
		wp_reset_postdata();
		die();
	}
}

new SP_WQV_Popup();
