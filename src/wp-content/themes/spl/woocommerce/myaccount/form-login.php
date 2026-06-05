<?php
/**
 * Login Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-login.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

do_action( 'woocommerce_before_customer_login_form' );

?>
<div id="customer_login" class="wc-otp-customer-login">
    <p class="h6 title"><?php esc_html_e( 'Đăng nhập hoặc đăng ký ngay tài khoản', TEXT_DOMAIN ); ?></p>
    <form method="post" class="woocommerce-form woocommerce-form-register register" <?php do_action( 'woocommerce_register_form_tag' ); ?> >
	    <?php do_action( 'woocommerce_register_form_start' ); ?>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="reg_email">
                <?php esc_html_e( 'Địa chỉ Email', TEXT_DOMAIN ); ?>
                <sup class="required" aria-hidden="true">*</sup>
                <span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span>
            </label>
            <input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" required aria-required="true" placeholder="example@gmail.com" />
        </p>

	    <?php do_action( 'woocommerce_register_form' ); ?>

        <p class="woocommerce-form-row form-row">
	        <?= \HD_Helper::CSRFToken( 'wc_otp_register_nonce', '_csrf_token', true ) ?>
            <button type="submit" class="woocommerce-Button woocommerce-button button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?> woocommerce-form-register__submit" name="otp_register" value="<?php esc_attr_e( 'Tiếp tục', TEXT_DOMAIN ); ?>">
                <?php esc_html_e( 'Tiếp tục', TEXT_DOMAIN ); ?>
            </button>
        </p>

	    <?php do_action( 'woocommerce_register_form_end' ); ?>
    </form>
</div>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
