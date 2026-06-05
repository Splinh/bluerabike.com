<?php
/*
Plugin Name: Baokim Plus
Plugin URI: http://plus.baokim.vn/
Description: Giải pháp thanh toán trực tuyến trên các website thương mại điện tử.
Version: 1.0
Author: PhungHT
Author URI: 
*/
?>
<?php
/**
@ Chèn CSS và Javascript vào theme
@ sử dụng hook wp_enqueue_scripts() để hiển thị nó ra ngoài front-end
**/
function wp_include_bk_css_js() {
	/**
	$handle: Tên của style (Tên này phải đặt duy nhất)
	$src: Đường dẫn đến file CSS
	$deps: Mảng chứa tên style phụ thuộc (Tên style phụ thuộc phải được đăng ký trước. Khi load WordPress sẽ load đối tượng được phụ thuộc trước)
	$ver: Phiên bản của style (Nếu để giá trị false, hệ thống sẽ tự lấy theo phiên bản của WordPress)
	$media: Media của style (Ví dụ: 'all', 'aural', 'braille', 'handheld', 'projection', 'print')
	**/
	wp_enqueue_style( 'bk-popup', 'https://pc.baokim.vn/css/bk.css');

	/**
	$handle: Tên của script (Tên này phải đặt tên duy nhất)
	$src: Đường dẫn tới file js
	$deps: Mảng chứa tên script phụ thuộc (Tên script phụ thuộc phải được đăng ký trước. Khi load WordPress sẽ load đối tượng được phụ thuộc trước)
	$ver: Phiên bản của script (Nếu để giá trị false, hệ thống sẽ tự lấy theo phiên bản của WordPress)
	$in_footer: Chuyển script xuống footer nếu giá trị là true
	**/

// 	wp_enqueue_script('bk-popup', 'https://pc.baokim.vn/js/bk_plus_v2.popup.js', [], false, true);
}
add_action( 'wp_enqueue_scripts', 'wp_include_bk_css_js', 20, 1);


/**
--------------
Trang chi tiết
--------------
**/

/**
Thêm nút btn và modal vào trang chi tiết
**/
function baokim_btn_detail(){
	?>
	<div class="bk-btn" style="margin-top: 10px">
	</div>
	<?php
}
add_action('woocommerce_after_add_to_cart_form','baokim_btn_detail');

/**
Xử lý để lấy ra dữ liệu
**/
function get_info($product){
	global $product;
	ob_start();
	$id = $product->get_id();
	$productPrice = (int)$product->get_price();
	?>
	<div style="display: none">
		<p class="bk-product-price"><?php echo $productPrice ?></p>
		<p class="bk-product-name"><?php echo the_title(); ?></p>
		<?php 
		echo get_the_post_thumbnail( $id, 'medium', array('class' =>'bk-product-image')); 
		if ( method_exists( $product, 'get_stock_status' ) ) {
            $stock_status = $product->get_stock_status(); // For version 3.0+
        } else {
            $stock_status = $product->stock_status; // Older than version 3.0
        }
        $list_stock = [
        	"instock"     => "Trong kho",
        	"outofstock"  => "Hết hàng",
        	"onbackorder" => "Đặt trước",
        	"contact"     => "Liên hệ",
        	"preorder"    => "Đặt hàng trước"
        ];
        ?>
        <p class="bk-check-out-of-stock"><?php echo isset($list_stock[$stock_status]) ? $list_stock[$stock_status] : "" ?></p>
    </div>
    <?php
    echo ob_get_clean();
}
add_action('woocommerce_after_single_product','get_info');

/**
Chèn class vào trang
**/
function hook_javascript_footer() {
	?>
	<script src="https://pc.baokim.vn/js/bk_plus_v2.popup.js"></script>
	<style>
		#bk-btn-paynow, #bk-btn-installment, .bk-btn-paynow, .bk-btn-installment {
			outline: none;
		}
		#bk-modal-close, #bk-modal-notify-close {
			margin: 0;
			padding: 0;
			outline: none;
		}
		.bk-btn .bk-btn-paynow {
			width: 100%;
			line-height: 1.6rem;
		}

		.bk-btn .bk-btn-installment {
			width: 100%;
			line-height: 1.6rem;
			margin-right: 0px;
		}

		.bk-btn .bk-btn-installment-amigo {
			width: 100%;
			margin-right: 0px;
			line-height: 1.6rem;
		}
		.bk-container-fluid {
			height: 60px;
		}
	</style>
	<script type="text/javascript">
		var productQuantityClass = document.getElementsByClassName("product-quantity");
        for(var i = 0; i < productQuantityClass.length; i++) {
            if(productQuantityClass[i].querySelector('.input-text')) {
                productQuantityClass[i].querySelector('.input-text').classList.add("bk-product-qty");
            }
        }
	</script>
	<?php
}
// add_action('woocommerce_after_main_content', 'hook_javascript_footer');
add_action('woocommerce_after_single_product', 'hook_javascript_footer');

/**
Chèn class vào trang lấy thuộc tính trang chi tiết
**/
add_filter( 'woocommerce_dropdown_variation_attribute_options_args', static function( $args ) {
	$args['class'] = 'bk-product-property';
	return $args;
}, 2 );

add_filter( 'woocommerce_variation_options_pricing', static function( $args ) {
	if(is_array( $args )) {
		$args['class'] = 'bk-product-property';
	}
	return $args;
}, 2 );



/**
--------------
Trang giỏ hàng
--------------
**/

/**
Thêm nút btn vào trang giỏ hàng
**/
// woocommerce_after_cart
function baokim_btn_cart(){
	?>
	<div class="bk-btn" style="margin-top: 10px">
	</div>
	<?php
}
add_action('woocommerce_proceed_to_checkout','baokim_btn_cart');

/**
Chèn modal, class vào trang cart
**/
function hook_modal_javascript_cart() {
	?>
	<script src="https://pc.baokim.vn/js/bk_plus_v2.popup.js"></script>
	<style>
		#bk-btn-paynow, #bk-btn-installment, .bk-btn-paynow, .bk-btn-installment {
			outline: none;
		}
		#bk-modal-close, #bk-modal-notify-close {
			margin: 0;
			padding: 0;
			outline: none;
		}
		.bk-btn .bk-btn-paynow {
			width: 100%;
			line-height: 1.6rem;
		}

		.bk-btn .bk-btn-installment {
			width: 100%;
			line-height: 1.6rem;
			margin-right: 0px;
		}

		.bk-btn .bk-btn-installment-amigo {
			width: 100%;
			margin-right: 0px;
			line-height: 1.6rem;
		}
		.bk-container-fluid {
			height: 60px;
		}
	</style>
	<script type="text/javascript">
		var productImageClass = document.getElementsByClassName("product-thumbnail");
        for(var i = 0; i < productImageClass.length; i++) {
            if(productImageClass[i].querySelector('img')) {
                productImageClass[i].querySelector('img').classList.add("bk-product-image");
            }
        }

        var productNameClass = document.getElementsByClassName("product-name");
        for(var i = 0; i < productNameClass.length; i++) {
            if(productNameClass[i].querySelector('a')) {
                productNameClass[i].querySelector('a').classList.add("bk-product-name");
            }
        }

        var productPriceClass = document.getElementsByClassName("product-price");
        for(var i = 0; i < productPriceClass.length; i++) {
            if(productPriceClass[i].querySelector('.amount')) {
                productPriceClass[i].querySelector('.amount').classList.add("bk-product-price");
            }
        }

        var productQuantityClass = document.getElementsByClassName("product-quantity");
        for(var i = 0; i < productQuantityClass.length; i++) {
            if(productQuantityClass[i].querySelector('.input-text')) {
                productQuantityClass[i].querySelector('.input-text').classList.add("bk-product-qty");
            }
        }
	</script>
	<?php
}
add_action('woocommerce_after_cart', 'hook_modal_javascript_cart');

add_action('wp_footer', 'wpshout_action_example'); 
function wpshout_action_example() {
	?>
	<div id='bk-modal'></div>
	<script>
		window.addEventListener("load", function(event) {
			var btnCloseModal = document.getElementById('bk-modal-close');
			btnCloseModal.addEventListener("click", function(){ 
				location.reload();
			});
			jQuery( '.variations_form' ).each( function() {
				jQuery(this).on( 'found_variation', function( event, variation ) {
					console.log(variation);//all details here
					var price = variation.display_price;//selectedprice
					document.getElementsByClassName('bk-product-price')[0].innerHTML = price;
				});
			});
		});
	</script>
	<?php
}
