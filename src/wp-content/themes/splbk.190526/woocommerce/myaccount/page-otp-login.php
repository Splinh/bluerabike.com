<?php

\defined( 'ABSPATH' ) || die;

// header
get_header( 'page' );

// breadcrumbs
$myaccount_page_id = wc_get_page_id( 'myaccount' );
\HD_Helper::blockTemplate( 'parts/blocks/breadcrumbs', [ 'title' => get_the_title( $myaccount_page_id ) ] );

$customer_id    = $args['uid'] ?? 0;
$sentAt         = $args['send_at'] ?? 0;
$resendInterval = $args['resend_interval'] ?? 0;
$secondsLeft    = max( 0, ( $sentAt + $resendInterval ) - current_time( 'timestamp' ) );

?>
<section class="section section-page section-otp-page singular">
	<div class="container flex flex-x">
		<div class="content">
			<h1 class="heading-title sr-only" <?= \HD_Helper::microdata( 'headline' ) ?>><?= get_the_title() ?></h1>
			<article <?= \HD_Helper::microdata( 'article' ) ?>>

				<?php do_action( 'woocommerce_before_customer_login_form' ); ?>

                <div id="customer_login" class="wc-otp-customer-login">
                    <p class="h6 title"><?php esc_html_e( 'Nhập mã xác thực đang gửi đến email của bạn.', TEXT_DOMAIN ); ?></p>
                    <form id="loginform" action="<?= esc_url( $args['action'] ?? '' ) ?>" method="post" class="woocommerce-form woocommerce-form-register register">
                        <p class="auth extra woocommerce-form-row form-row">
                            <label for="authcode">
                                Recovery Code:
                                <span id="countdown" data-time="<?= (int) $secondsLeft ?>" data-interval="<?= (int) $resendInterval ?>">
                            </label>
                            <input required autofocus type="text" name="authcode" id="authcode" class="woocommerce-Input woocommerce-Input--text input-text authcode"
                                   value=""
                                   inputmode="numeric"
                                   autocomplete="off"
                                   pattern="[0-9]*"
                                   size="<?= (int) ( $args['otp_digits'] ?? 20 ) ?>"
                                   maxlength="<?= (int) ( $args['otp_digits'] ?? 20 ) ?>"
                                   placeholder="<?= str_repeat( 'x', (int) ( $args['otp_digits'] ?? 6 ) ) ?>"
                                   data-digits="<?= (int) ( $args['otp_digits'] ?? 6 ) ?>"/>
                        </p>

                        <p class="woocommerce-form-row form-row">
	                        <?php if ( ! empty( $args['redirect_to'] ) ) : ?>
                            <input type="hidden" name="redirect_to" value="<?= esc_url( $args['redirect_to'] ) ?>"/>
	                        <?php endif; ?>
                            <input type="hidden" name="uid" value="<?= $customer_id ?>">

	                        <?= \HD_Helper::CSRFToken( 'wc_otp_validate_nonce', '_csrf_token', false ) ?>

                            <button type="submit" class="button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="otp_register" value="<?php esc_attr_e( 'Xác thực', TEXT_DOMAIN ); ?>">
                                <?php esc_html_e( 'Xác thực', TEXT_DOMAIN ); ?>
                            </button>
                        </p>
                    </form>
                </div>

				<?php do_action( 'woocommerce_after_customer_login_form' ); ?>

			</article>
		</div>
	</div>
</section>
<?php

// footer
get_footer( 'page' );
