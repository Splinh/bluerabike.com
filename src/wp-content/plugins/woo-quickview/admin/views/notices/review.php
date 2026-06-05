<?php
/**
 * The review admin notice file.
 *
 * @link http://shapedplugin.com
 * @since 2.0.0
 * @package Woo_Quick_View.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}  // if direct access.

/**
 * The review admin notice.
 */
class Woo_Quick_View_Review {

	/**
	 * Constructor function the class
	 */
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'display_admin_notice' ) );
		add_action( 'wp_ajax_sp-woo-quick-view-never-show-review-notice', array( $this, 'dismiss_review_notice' ) );
	}

	/**
	 * Display admin notice.
	 *
	 * @return void
	 */
	public function display_admin_notice() {
		// Show only to Admins.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Variable default value.
		$review = get_option( 'sp_woo_quick_view_review_notice_dismiss' );
		$time   = time();
		$load   = false;

		if ( ! $review ) {
			$review = array(
				'time'      => $time,
				'dismissed' => false,
			);
			add_option( 'sp_woo_quick_view_review_notice_dismiss', $review );
		} else {
			// Check if it has been dismissed or not.
			if ( ( isset( $review['dismissed'] ) && ! $review['dismissed'] ) && ( isset( $review['time'] ) && ( ( $review['time'] + ( DAY_IN_SECONDS * 3 ) ) <= $time ) ) ) {
				$load = true;
			}
		}

		// If we cannot load, return early.
		if ( ! $load ) {
			return;
		}
		?>
		<div id="sp-woo-quick-view-review-notice" class="sp-woo-quick-view-review-notice">
			<div class="sp-woo-quick-view-plugin-icon">
				<img src="<?php echo esc_url( SP_WQV_URL . 'admin/views/img/woo-quick-view-notice.svg' ); ?>" alt="Quick View for WooCommerce">
			</div>
			<div class="sp-woo-quick-view-notice-text">
				<h3>Enjoying <strong>Quick View for WooCommerce</strong>?</h3>
				<p>We hope you had a wonderful experience using <strong>Quick View for WooCommerce</strong>. Please take a moment to leave a review on <a href="https://wordpress.org/support/plugin/woo-quickview/reviews/" target="_blank"><strong>WordPress.org</strong></a>.
					Your positive review will help us improve. Thank you! 😊</p>

				<p class="sp-woo-quick-view-review-actions">
					<a href="https://wordpress.org/support/plugin/woo-quickview/reviews/" target="_blank" class="button button-primary notice-dismissed rate-woo-quick-view">Ok, you deserve ★★★★★</a>
					<a href="#" class="notice-dismissed remind-me-later"><span class="dashicons dashicons-clock"></span>Nope, maybe later
					</a>
					<a href="#" class="notice-dismissed never-show-again"><span class="dashicons dashicons-dismiss"></span>Never show again</a>
				</p>
			</div>
		</div>

		<script type='text/javascript'>

			jQuery(document).ready( function($) {
				$(document).on('click', '#sp-woo-quick-view-review-notice.sp-woo-quick-view-review-notice .notice-dismissed', function( event ) {
					if ( $(this).hasClass('rate-woo-quick-view') ) {
						var notice_dismissed_value = "1";
					}
					if ( $(this).hasClass('remind-me-later') ) {
						var notice_dismissed_value =  "2";
						event.preventDefault();
					}
					if ( $(this).hasClass('never-show-again') ) {
						var notice_dismissed_value =  "3";
						event.preventDefault();
					}

					$.post( ajaxurl, {
						action: 'sp-woo-quick-view-never-show-review-notice',
						notice_dismissed_data : notice_dismissed_value,
						nonce: '<?php echo esc_attr( wp_create_nonce( 'sp_woo_quick_view_review_notice' ) ); ?>'
					});

					$('#sp-woo-quick-view-review-notice.sp-woo-quick-view-review-notice').hide();
				});
			});

		</script>
		<?php
	}

	/**
	 * Dismiss review notice
	 *
	 * @since  2.1.14
	 *
	 * @return void
	 **/
	public function dismiss_review_notice() {
		$post_data = wp_unslash( $_POST );

		if ( ! isset( $post_data['nonce'] ) || ! wp_verify_nonce( sanitize_key( $post_data['nonce'] ), 'sp_woo_quick_view_review_notice' ) ) {
			return;
		}
		$review = get_option( 'sp_woo_quick_view_review_notice_dismiss' );
		if ( ! $review ) {
			$review = array();
		}

		$dismiss_status = isset( $post_data['notice_dismissed_data'] ) ? sanitize_text_field( wp_unslash( $post_data['notice_dismissed_data'] ) ) : '';

		switch ( $dismiss_status ) {
			case '1':
				$review['time']      = time();
				$review['dismissed'] = true;
				break;
			case '2':
				$review['time']      = time();
				$review['dismissed'] = false;
				break;
			case '3':
				$review['time']      = time();
				$review['dismissed'] = true;
				break;
		}
		update_option( 'sp_woo_quick_view_review_notice_dismiss', $review );
		die;
	}
}

new Woo_Quick_View_Review();
