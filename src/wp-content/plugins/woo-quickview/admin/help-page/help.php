<?php
/**
 * The help page for the Woo QuickView Free
 *
 * @package Woo QuickView Free
 * @subpackage woo-quick-view/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}  // if direct access.

/**
 * The help class for the Woo QuickView Free
 */
class Woo_Quick_View_Help {

	/**
	 * Single instance of the class
	 *
	 * @var null
	 */
	protected static $instance = null;

	/**
	 * Plugins Path variable.
	 *
	 * @var array
	 */
	protected static $plugins = array(
		'woo-product-slider'             => 'main.php',
		'gallery-slider-for-woocommerce' => 'woo-gallery-slider.php',
		'post-carousel'                  => 'main.php',
		'easy-accordion-free'            => 'plugin-main.php',
		'logo-carousel-free'             => 'main.php',
		'location-weather'               => 'main.php',
		'woo-quickview'                  => 'woo-quick-view.php',
		'wp-expand-tabs-free'            => 'plugin-main.php',

	);

	/**
	 * Welcome pages
	 *
	 * @var array
	 */
	public $pages = array(
		'wqv_settings',
	);


	/**
	 * Not show this plugin list.
	 *
	 * @var array
	 */
	protected static $not_show_plugin_list = array( 'aitasi-coming-soon', 'latest-posts', 'widget-post-slider', 'easy-lightbox-wp', 'woo-quickview' );

	/**
	 * Help Page construct function.
	 */
	public function __construct() {
		$this->help_page_callback();
	}

	/**
	 * Help Page Instance
	 *
	 * @static
	 * @return self Main instance
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Wooqv_ajax_help_page function.
	 *
	 * @return void
	 */
	public function wooqv_plugins_info_api_help_page() {
		$plugins_arr = get_transient( 'wooqv_plugins' );
		if ( false === $plugins_arr ) {
			$args    = (object) array(
				'author'   => 'shapedplugin',
				'per_page' => '120',
				'page'     => '1',
				'fields'   => array(
					'slug',
					'name',
					'version',
					'downloaded',
					'active_installs',
					'last_updated',
					'rating',
					'num_ratings',
					'short_description',
					'author',
					'icons',
				),
			);
			$request = array(
				'action'  => 'query_plugins',
				'timeout' => 30,
				'request' => serialize( $args ),
			);
			// https://codex.wordpress.org/WordPress.org_API.
			$url      = 'http://api.wordpress.org/plugins/info/1.0/';
			$response = wp_remote_post( $url, array( 'body' => $request ) );

			if ( ! is_wp_error( $response ) ) {

				$plugins_arr = array();
				$plugins     = unserialize( $response['body'] );

				if ( isset( $plugins->plugins ) && ( count( $plugins->plugins ) > 0 ) ) {
					foreach ( $plugins->plugins as $pl ) {
						if ( ! in_array( $pl->slug, self::$not_show_plugin_list, true ) ) {
							$plugins_arr[] = array(
								'slug'              => $pl->slug,
								'name'              => $pl->name,
								'version'           => $pl->version,
								'downloaded'        => $pl->downloaded,
								'active_installs'   => $pl->active_installs,
								'last_updated'      => strtotime( $pl->last_updated ),
								'rating'            => $pl->rating,
								'num_ratings'       => $pl->num_ratings,
								'short_description' => $pl->short_description,
								'icons'             => $pl->icons['2x'],
							);
						}
					}
				}

				set_transient( 'wooqv_plugins', $plugins_arr, 24 * HOUR_IN_SECONDS );
			}
		}

		$woocommerce_plugin = array( 'gallery-slider-for-woocommerce', 'woo-category-slider-grid', 'woo-product-slider', 'smart-brands-for-woocommerce' );
		$woo_plugins        = array();
		$normal_plugins     = array();

		foreach ( $plugins_arr as $plugin ) {
			if ( in_array( $plugin['slug'], $woocommerce_plugin, true ) ) {
				array_push( $woo_plugins, $plugin );
			} else {
				array_push( $normal_plugins, $plugin );
			}
		}

		$plugins_arr = array_merge( $woo_plugins, $normal_plugins );

		if ( is_array( $plugins_arr ) && ( count( $plugins_arr ) > 0 ) ) {

			foreach ( $plugins_arr as $plugin ) {
				$plugin_slug = $plugin['slug'];
				$plugin_icon = $plugin['icons'];
				$image_type  = 'png';
				if ( isset( self::$plugins[ $plugin_slug ] ) ) {
					$plugin_file = self::$plugins[ $plugin_slug ];
				} else {
					$plugin_file = $plugin_slug . '.php';
				}

				// switch ( $plugin_slug ) {
				// case 'styble':
				// $image_type = 'jpg';
				// break;
				// case 'location-weather':
				// case 'testimonial-free':
				// case 'logo-carousel-free':
				// case 'easy-accordion-free':
				// case 'gallery-slider-for-woocommerce':
				// $image_type = 'gif';
				// break;
				// }

				$details_link = network_admin_url( 'plugin-install.php?tab=plugin-information&amp;plugin=' . $plugin['slug'] . '&amp;TB_iframe=true&amp;width=745&amp;height=550' );
				?>
				<div class="plugin-card <?php echo esc_attr( $plugin_slug ); ?>" id="<?php echo esc_attr( $plugin_slug ); ?>">
					<div class="plugin-card-top">
						<div class="name column-name">
							<h3>
								<a class="thickbox" title="<?php echo esc_attr( $plugin['name'] ); ?>" href="<?php echo esc_url( $details_link ); ?>">
								<?php echo esc_html( $plugin['name'] ); ?>
								<img src="<?php echo esc_url( $plugin_icon ); ?>" class="plugin-icon"/>
										</a>
							</h3>
						</div>
						<div class="action-links">
							<ul class="plugin-action-buttons">
								<li>
						<?php
						if ( $this->is_plugin_installed( $plugin_slug, $plugin_file ) ) {
							if ( $this->is_plugin_active( $plugin_slug, $plugin_file ) ) {
								?>
										<button type="button" class="button button-disabled" disabled="disabled">Active</button>
									<?php
							} else {
								?>
											<a href="<?php echo esc_url( $this->activate_plugin_link( $plugin_slug, $plugin_file ) ); ?>" class="button button-primary activate-now">
									<?php esc_html_e( 'Activate', 'woo-quickview' ); ?>
											</a>
									<?php
							}
						} else {
							?>
										<a href="<?php echo esc_url( $this->install_plugin_link( $plugin_slug ) ); ?>" class="button install-now">
								<?php esc_html_e( 'Install Now', 'woo-quickview' ); ?>
										</a>
								<?php } ?>
								</li>
								<li>
									<a href="<?php echo esc_url( $details_link ); ?>" class="thickbox open-plugin-details-modal" aria-label="<?php echo esc_attr( 'More information about ' . $plugin['name'] ); ?>" title="<?php echo esc_attr( $plugin['name'] ); ?>">
								<?php esc_html_e( 'More Details', 'woo-quickview' ); ?>
									</a>
								</li>
							</ul>
						</div>
						<div class="desc column-description">
							<p><?php echo esc_html( isset( $plugin['short_description'] ) ? $plugin['short_description'] : '' ); ?></p>
							<p class="authors"> <cite>By <a href="https://shapedplugin.com/">ShapedPlugin LLC</a></cite></p>
						</div>
					</div>
					<?php
					echo '<div class="plugin-card-bottom">';

					if ( isset( $plugin['rating'], $plugin['num_ratings'] ) ) {
						?>
						<div class="vers column-rating">
							<?php
							wp_star_rating(
								array(
									'rating' => $plugin['rating'],
									'type'   => 'percent',
									'number' => $plugin['num_ratings'],
								)
							);
							?>
							<span class="num-ratings">(<?php echo esc_html( number_format_i18n( $plugin['num_ratings'] ) ); ?>)</span>
						</div>
						<?php
					}
					if ( isset( $plugin['version'] ) ) {
						?>
						<div class="column-updated">
							<strong><?php esc_html_e( 'Version:', 'woo-quickview' ); ?></strong>
							<span><?php echo esc_html( $plugin['version'] ); ?></span>
						</div>
							<?php
					}

					if ( isset( $plugin['active_installs'] ) ) {
						?>
						<div class="column-downloaded">
						<?php echo esc_html( number_format_i18n( $plugin['active_installs'] ) ) . esc_html__( '+ Active Installations', 'woo-quickview' ); ?>
						</div>
									<?php
					}

					if ( isset( $plugin['last_updated'] ) ) {
						?>
						<div class="column-compatibility">
							<strong><?php esc_html_e( 'Last Updated:', 'woo-quickview' ); ?></strong>
							<span><?php echo esc_html( human_time_diff( $plugin['last_updated'] ) ) . ' ' . esc_html__( 'ago', 'woo-quickview' ); ?></span>
						</div>
									<?php
					}

					echo '</div>';
					?>
				</div>
				<?php
			}
		}
	}

	/**
	 * Check plugins installed function.
	 *
	 * @param string $plugin_slug Plugin slug.
	 * @param string $plugin_file Plugin file.
	 * @return boolean
	 */
	public function is_plugin_installed( $plugin_slug, $plugin_file ) {
		return file_exists( WP_PLUGIN_DIR . '/' . $plugin_slug . '/' . $plugin_file );
	}

	/**
	 * Check active plugin function
	 *
	 * @param string $plugin_slug Plugin slug.
	 * @param string $plugin_file Plugin file.
	 * @return boolean
	 */
	public function is_plugin_active( $plugin_slug, $plugin_file ) {
		return is_plugin_active( $plugin_slug . '/' . $plugin_file );
	}

	/**
	 * Install plugin link.
	 *
	 * @param string $plugin_slug Plugin slug.
	 * @return string
	 */
	public function install_plugin_link( $plugin_slug ) {
		return wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=' . $plugin_slug ), 'install-plugin_' . $plugin_slug );
	}

	/**
	 * Active Plugin Link function
	 *
	 * @param string $plugin_slug Plugin slug.
	 * @param string $plugin_file Plugin file.
	 * @return string
	 */
	public function activate_plugin_link( $plugin_slug, $plugin_file ) {
		return wp_nonce_url( admin_url( 'admin.php?page=wqv_settings&action=activate&plugin=' . $plugin_slug . '/' . $plugin_file . '#tab=get-help#recommended' ), 'activate-plugin_' . $plugin_slug . '/' . $plugin_file );
	}

	/**
	 * The Woo QuickView Help Callback.
	 *
	 * @return void
	 */
	public function help_page_callback() {
		add_thickbox();

		$action   = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
		$plugin   = isset( $_GET['plugin'] ) ? sanitize_text_field( wp_unslash( $_GET['plugin'] ) ) : '';
		$_wpnonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';

		if ( isset( $action, $plugin ) && ( 'activate' === $action ) && wp_verify_nonce( $_wpnonce, 'activate-plugin_' . $plugin ) ) {
			activate_plugin( $plugin, '', false, true );
		}

		if ( isset( $action, $plugin ) && ( 'deactivate' === $action ) && wp_verify_nonce( $_wpnonce, 'deactivate-plugin_' . $plugin ) ) {
			deactivate_plugins( $plugin, '', false, true );
		}

		?>
		<div class="sp-woo-quick-view-help">
			<!-- Header section start -->
			<div class="wooqv-header-nav">
				<div class="wooqv-container">
					<div class="wooqv-header-nav-menu">
						<ul>
							<li><a class="active" data-id="get-start-tab"  href="<?php echo esc_url( home_url( '' ) . '/wp-admin/admin.php?page=wqv_settings#tab=get-help#get-started' ); ?>">Get Started</a></li>
							<li><a href="<?php echo esc_url( home_url( '' ) . '/wp-admin/admin.php?page=wqv_settings#tab=get-help#recommended' ); ?>" data-id="recommended-tab">Recommended</a></li>
							<li><a href="<?php echo esc_url( home_url( '' ) . '/wp-admin/admin.php?page=wqv_settings#tab=get-help#lite-to-pro' ); ?>" data-id="lite-to-pro-tab">Lite Vs Pro</a></li>
							<li><a href="<?php echo esc_url( home_url( '' ) . '/wp-admin/admin.php?page=wqv_settings#tab=get-help#about-us' ); ?>" data-id="about-us-tab">About Us</a></li>
						</ul>
					</div>
				</div>
			</div>
			<!-- Header section end -->

			<!-- Start Page -->
			<section class="wooqv__help start-page" id="get-start-tab">
				<div class="wooqv-container">
					<div class="wooqv-start-page-wrap">
						<div class="wooqv-video-area">
							<h2 class='wooqv-section-title-help'>Welcome to Woo QuickView!</h2>
							<span class='wooqv-normal-paragraph'>Thank you for installing Woo QuickView! This video will help you get started with the plugin. Enjoy!</span>
							<iframe width="724" height="405" src="https://www.youtube.com/embed/aVznU7U7Hv4?si=1b1jkUCN2_K_3_xP" title="YouTube video player" frameborder="0" allowfullscreen></iframe>
							<ul>
								<li><a class='wooqv-medium-btn' href="<?php echo esc_url( home_url( '/' ) . 'wp-admin/admin.php?page=wqv_settings#tab=general' ); ?>">Configure Settings</a></li>
								<li><a target="_blank" class='wooqv-medium-btn' href="https://demo.shapedplugin.com/woocommerce-quick-view/">Live Demo</a></li>
								<li><a target="_blank" class='wooqv-medium-btn arrow-btn' href="https://shapedplugin.com/quick-view-for-woocommerce/?ref=1">Explore Woo QuickView <i class="wooqv-icon-button-arrow-icon"></i></a></li>
							</ul>
						</div>
						<div class="wooqv-start-page-sidebar">
							<div class="wooqv-start-page-sidebar-info-box">
								<div class="wooqv-info-box-title">
									<h4><i class="wooqv-icon-doc-icon"></i> Documentation</h4>
								</div>
								<span class='wooqv-normal-paragraph'>Explore Woo QuickView plugin capabilities in our enriched documentation.</span>
								<a target="_blank" class='wooqv-small-btn' href="https://docs.shapedplugin.com/docs/woocommerce-quick-view/overview/">Browse Now</a>
							</div>
							<div class="wooqv-start-page-sidebar-info-box">
								<div class="wooqv-info-box-title">
									<h4><i class="wooqv-icon-support"></i> Technical Support</h4>
								</div>
								<span class='wooqv-normal-paragraph'>For personalized assistance, reach out to our skilled support team for prompt help.</span>
								<a target="_blank" class='wooqv-small-btn' href="https://shapedplugin.com/create-new-ticket/">Ask Now</a>
							</div>
							<div class="wooqv-start-page-sidebar-info-box">
								<div class="wooqv-info-box-title">
									<h4><i class="wooqv-icon-team-icon"></i> Join The Community</h4>
								</div>
								<span class='wooqv-normal-paragraph'>Join the official ShapedPlugin Facebook group to share your experiences, thoughts, and ideas.</span>
								<a target="_blank" class='wooqv-small-btn' href="https://www.facebook.com/groups/ShapedPlugin/">Join Now</a>
							</div>
						</div>
					</div>
				</div>
			</section>

			<!-- Lite To Pro Page -->
			<section class="wooqv__help lite-to-pro-page" id="lite-to-pro-tab">
				<div class="wooqv-container">
					<div class="wooqv-call-to-action-top">
						<h2 class="wooqv-section-title-help">Lite vs Pro Comparison</h2>
						<a target="_blank" href="https://shapedplugin.com/quick-view-for-woocommerce/?ref=1" class='wooqv-big-btn'>Upgrade to Pro Now!</a>
					</div>
					<div class="wooqv-lite-to-pro-wrap">
						<div class="wooqv-features">
							<ul>
								<li class='wooqv-header'>
									<span class='wooqv-title'>FEATURES</span>
									<span class='wooqv-free'>Lite</span>
									<span class='wooqv-pro'><i class='wooqv-icon-pro'></i> PRO</span>
								</li>
								<li class='wooqv-body'>
									<span class='wooqv-title'>All Free Version Features</span>
									<span class='wooqv-free wooqv-check-icon'></span>
									<span class='wooqv-pro wooqv-check-icon'></span>
								</li>
								<li class='wooqv-body'>
									<span class='wooqv-title'>Amazing Quick view layouts</span>
									<span class='wooqv-free'><b>1</b></span>
									<span class='wooqv-pro'><b>4</b></span>
								</li>
								<li class='wooqv-body'>
									<span class='wooqv-title'>Beautiful Modal Effects</span>
									<span class='wooqv-free'><b>1</b></span>
									<span class='wooqv-pro'><b>8</b></span>
								</li>
								<li class='wooqv-body'>
									<span class='wooqv-title'>Flexible Position for a Quick View Button <i class="wooqv-hot">Hot</i></span>
									<span class='wooqv-free'><b>3</b></span>
									<span class='wooqv-pro'><b>12+</b></span>
								</li>

								<li class='wooqv-body'>
									<span class='wooqv-title'>Quick View Button Icon Style (Icon, Custom Image)</span>
									<span class='wooqv-free'><b>1</b></span>
									<span class='wooqv-pro'><b>3</b></span>
								</li>
								<li class='wooqv-body'>
									<span class='wooqv-title'>Quick View Button Styles (Color, Border, Padding, etc.)</span>
									<span class='wooqv-free wooqv-check-icon'></span>
									<span class='wooqv-pro wooqv-check-icon'></span>
								</li>
								<li class='wooqv-body'>
									<span class='wooqv-title'>Quick View Modal Custom Dimension (Width and Height)</span>
									<span class='wooqv-free wooqv-close-icon'></span>
									<span class='wooqv-pro wooqv-check-icon'></span>
								</li>
								<li class='wooqv-body'>
									<span class='wooqv-title'>Ability to Assign Quick View by Clicking Product Name or Image <i class="wooqv-hot">Hot</i></span>
									<span class='wooqv-free wooqv-close-icon'></span>
									<span class='wooqv-pro wooqv-check-icon'></span>
								</li>
								<li class='wooqv-body'>
									<span class='wooqv-title'>Check to Make Modal Image Right</span>
									<span class='wooqv-free wooqv-close-icon'></span>
									<span class='wooqv-pro wooqv-check-icon'></span>
								</li>
								<li class='wooqv-body'>
									<span class='wooqv-title'>Image and content area width (Allocate the Area Reciprocally)</span>
									<span class='wooqv-free wooqv-close-icon'></span>
									<span class='wooqv-pro wooqv-check-icon'></span>
								</li>
								<li class='wooqv-body'>
									<span class='wooqv-title'>Custom Modal Background and Overlay Background, Content Padding</span>
									<span class='wooqv-free wooqv-check-icon'></span>
									<span class='wooqv-pro wooqv-check-icon'></span>
								</li>
								<li class='wooqv-body'>
									<span class='wooqv-title'>Quick View Modal Effects</span>
									<span class='wooqv-free'><b>8</b></span>
									<span class='wooqv-pro'><b>10+</b></span>
								</li>
								<li class='wooqv-body'>
									<span class='wooqv-title'>Display Quick View on Specific Product Categories <i class="wooqv-new">New</i></span>
									<span class='wooqv-free wooqv-close-icon'></span>
									<span class='wooqv-pro wooqv-check-icon'></span>
								</li>
								<li class='wooqv-body'>
									<span class='wooqv-title'>Show/Hide Sale Flash on the Modal</span>
									<span class='wooqv-free wooqv-close-icon'></span>
									<span class='wooqv-pro wooqv-check-icon'></span>
								</li>
								<li class='wooqv-body'>
									<span class='wooqv-title'>Show/Hide Product Content Information (Title, Rating, Price, Excerpt, Add to Cart, Meta Fields, etc.)</span>
									<span class='wooqv-free wooqv-check-icon'></span>
									<span class='wooqv-pro wooqv-check-icon'></span>
								</li>
								<li class='wooqv-body'>
									<span class='wooqv-title'>Drag and Drop Sorting Product Content in Quick View Modal <i class="wooqv-hot">Hot</i></span>
									<span class='wooqv-free wooqv-close-icon'></span>
									<span class='wooqv-pro wooqv-check-icon'></span>
								</li>
								<li class='wooqv-body'>
									<span class='wooqv-title'>Display and Stylize Social Share Icons (Icon Shape, Alignment, Custom Color, etc.)</span>
									<span class='wooqv-free wooqv-close-icon'></span>
									<span class='wooqv-pro wooqv-check-icon'></span>
								</li>
								<li class='wooqv-body'>
									<span class='wooqv-title'>Product Image Custom Sizing for Modal</span>
									<span class='wooqv-free wooqv-close-icon'></span>
									<span class='wooqv-pro wooqv-check-icon'></span>
								</li>
								<li class='wooqv-body'>
									<span class='wooqv-title'>Show Product Descriptions, Tabs, and Related Products</span>
									<span class='wooqv-free wooqv-close-icon'></span>
									<span class='wooqv-pro wooqv-check-icon'></span>
								</li>
								<li class='wooqv-body'>
									<span class='wooqv-title'>Enable Ajax Add to Cart Button on Modal <i class="wooqv-hot">Hot</i></span>
									<span class='wooqv-free wooqv-check-icon'></span>
									<span class='wooqv-pro wooqv-check-icon'></span>
								</li>
								<li class='wooqv-body'>
									<span class='wooqv-title'>Customize Add to Cart Button Color, Padding</span>
									<span class='wooqv-free wooqv-check-icon'></span>
									<span class='wooqv-pro wooqv-check-icon'></span>
								</li>
								<li class='wooqv-body'>
									<span class='wooqv-title'>Close Modal after Adding a Product to the Cart</span>
									<span class='wooqv-free wooqv-close-icon'></span>
									<span class='wooqv-pro wooqv-check-icon'></span>
								</li>
								<li class='wooqv-body'>
									<span class='wooqv-title'>Add View Details Button Besides Add to Cart</span>
									<span class='wooqv-free wooqv-close-icon'></span>
									<span class='wooqv-pro wooqv-check-icon'></span>
								</li>
								<li class='wooqv-body'>
									<span class='wooqv-title'>Enable/Disable Quick View on Wishlist</span>
									<span class='wooqv-free wooqv-close-icon'></span>
									<span class='wooqv-pro wooqv-check-icon'></span>
								</li>
								<li class='wooqv-body'>
									<span class='wooqv-title'>Product Thumbnails Gallery Type in Modal (Slider and Classic) <i class="wooqv-hot">Hot</i></span>
									<span class='wooqv-free wooqv-close-icon'></span>
									<span class='wooqv-pro wooqv-check-icon'></span>
								</li>
								<li class='wooqv-body'>
									<span class='wooqv-title'>Customize Thumbnails Position, AutoPlay, Navigation, Pagination, Arrow Icon Size, etc.</span>
									<span class='wooqv-free wooqv-close-icon'></span>
									<span class='wooqv-pro wooqv-check-icon'></span>
								</li>
								<li class='wooqv-body'>
									<span class='wooqv-title'>Thumbnails Grayscale, Zoom Effects</span>
									<span class='wooqv-free wooqv-close-icon'></span>
									<span class='wooqv-pro wooqv-check-icon'></span>
								</li>
								<li class='wooqv-body'>
									<span class='wooqv-title'>Lightbox for Product Image in Modal</span>
									<span class='wooqv-free wooqv-check-icon'></span>
									<span class='wooqv-pro wooqv-check-icon'></span>
								</li>
								<li class='wooqv-body'>
									<span class='wooqv-title'>Close the Modal when Clicking on the Background overlay.</span>
									<span class='wooqv-free wooqv-close-icon'></span>
									<span class='wooqv-pro wooqv-check-icon'></span>
								</li>
								<li class='wooqv-body'>
									<span class='wooqv-title'>Modal Close Button Position</span>
									<span class='wooqv-free'><b>1</b></span>
									<span class='wooqv-pro'><b>3</b></span>
								</li>
								<li class='wooqv-body'>
									<span class='wooqv-title'>10+ Navigation Styles for Modal</span>
									<span class='wooqv-free wooqv-close-icon'></span>
									<span class='wooqv-pro wooqv-check-icon'></span>
								</li>
								<li class='wooqv-body'>
									<span class='wooqv-title'>Modal Preloader Types, Icon Sets, Icon Sizing and Color</span>
									<span class='wooqv-free wooqv-close-icon'></span>
									<span class='wooqv-pro wooqv-check-icon'></span>
								</li>
								<li class='wooqv-body'>
									<span class='wooqv-title'>Enable or Disable the Quick View Button on Mobile or iPad</span>
									<span class='wooqv-free wooqv-close-icon'></span>
									<span class='wooqv-pro wooqv-check-icon'></span>
								</li>
								<li class='wooqv-body'>
									<span class='wooqv-title'>All Premium Features, Security Enhancements, and Compatibility</span>
									<span class='wooqv-free wooqv-close-icon'></span>
									<span class='wooqv-pro wooqv-check-icon'></span>
								</li>
								<li class='wooqv-body'>
									<span class='wooqv-title'>Priority Top-notch Support</span>
									<span class='wooqv-free wooqv-close-icon'></span>
									<span class='wooqv-pro wooqv-check-icon'></span>
								</li>
							</ul>
						</div>
						<div class="wooqv-upgrade-to-pro">
							<h2 class='wooqv-section-title-help'>Upgrade To PRO & Enjoy Advanced Features!</h2>
							<span class='wooqv-section-subtitle'>Already, <b>3000+</b> people are using Woo QuickView on their websites to create beautiful carousels, sliders, and galleries; why won’t you!</span>
							<div class="wooqv-upgrade-to-pro-btn">
								<div class="wooqv-action-btn">
									<a target="_blank" href="https://shapedplugin.com/quick-view-for-woocommerce/?ref=1" class='wooqv-big-btn'>Upgrade to Pro Now!</a>
									<span class='wooqv-small-paragraph'>14-Day No-Questions-Asked <a target="_blank" href="https://shapedplugin.com/refund-policy/">Refund Policy</a></span>
								</div>
								<a target="_blank" href="https://shapedplugin.com/quick-view-for-woocommerce/?ref=1" class='wooqv-big-btn-border'>See All Features</a>
								<a target="_blank" href="https://demo.shapedplugin.com/woocommerce-quick-view-pro/" class="wooqv-big-btn-border wooqv-pro-live-demo-btn">Pro Live Demo</a>
							</div>
						</div>
					</div>
					<div class="wooqv-testimonial">
						<div class="wooqv-testimonial-title-section">
							<span class='wooqv-testimonial-subtitle'>NO NEED TO TAKE OUR WORD FOR IT</span>
							<h2 class="wooqv-section-title-help">Our Users Love Woo QuickView Pro!</h2>
						</div>
						<div class="wooqv-testimonial-wrap">
							<div class="wooqv-testimonial-area">
								<div class="wooqv-testimonial-content">
									<p>I have tried most of the Quick View plugins. In my opinion this is the best of the free offerings, and when I needed some support with styling, I received a same-day resolution from the very helpful and friendly support...</p>
								</div>
								<div class="wooqv-testimonial-info">
									<div class="wooqv-img">
										<img src="<?php echo esc_url( SP_WQV_URL . 'admin/help-page/img/johntosh.png' ); ?>" alt="">
									</div>
									<div class="wooqv-info">
										<h3>Johntosh</h3>
										<div class="wooqv-star">
											<i>★★★★★</i>
										</div>
									</div>
								</div>
							</div>
							<div class="wooqv-testimonial-area">
								<div class="wooqv-testimonial-content">
									<p>There are a few options out there. None of them worked straight away very well for me. The design is beautiful. The support helped me with CSS and I just cannot recommend highly enough.</p>
								</div>
								<div class="wooqv-testimonial-info">
									<div class="wooqv-img">
										<img src="<?php echo esc_url( SP_WQV_URL . 'admin/help-page/img/michaelh.png' ); ?>" alt="">
									</div>
									<div class="wooqv-info">
										<h3>Michaelh</h3>
										<div class="wooqv-star">
											<i>★★★★★</i>
										</div>
									</div>
								</div>
							</div>
							<div class="wooqv-testimonial-area">
								<div class="wooqv-testimonial-content">
									<p>This “quick view” plugin is a must-have, very useful and beautiful. I bought 3 plugins from this company already, and I never get disappointed so far. The team is very professional.</p>
								</div>
								<div class="wooqv-testimonial-info">
									<div class="wooqv-img">
										<img src="<?php echo esc_url( SP_WQV_URL . 'admin/help-page/img/martin.png' ); ?>" alt="">
									</div>
									<div class="wooqv-info">
										<h3>Martin Frederic </h3>
										<div class="wooqv-star">
											<i>★★★★★</i>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>

			<!-- Recommended Page -->
			<section id="recommended-tab" class="wooqv-recommended-page">
				<div class="wooqv-container">
					<h2 class="wooqv-section-title-help">Enhance your Website with our Free Robust Plugins</h2>
					<div class="wooqv-wp-list-table plugin-install-php">
						<div class="wooqv-recommended-plugins" id="the-list">
							<?php
								$this->wooqv_plugins_info_api_help_page();
							?>
						</div>
					</div>
				</div>
			</section>

			<!-- About Page -->
			<section id="about-us-tab" class="wooqv__help about-page">
				<div class="wooqv-container">
					<div class="wooqv-about-box">
						<div class="wooqv-about-info">
							<h3>The Ultimate WooCommerce Quick View plugin from the Woo Quick View Team, ShapedPlugin, LLC</h3>
							<p>At <strong>ShapedPlugin LLC</strong>, we are committed to helping WooCommerce store owners increase their sales with the help of various easy sales booster plugins. However, we were trying to redefine the shopping experience. Regrettably, we didn't find any suitable plugin. As a result, we developed a powerful Quick View plugin designed for WooCommerce stores.</p>
							<p>Our goal was clear: to provide a seamless and efficient solution that enables customers to preview products without navigating away from the shop page.</p>
							<div class="wooqv-about-btn">
								<a target="_blank" href="https://shapedplugin.com/quick-view-for-woocommerce/?ref=1" class='wooqv-medium-btn'>Explore Woo QuickView</a>
								<a target="_blank" href="https://shapedplugin.com/about-us/" class='wooqv-medium-btn wooqv-arrow-btn'>More About Us <i class="wooqv-icon-button-arrow-icon"></i></a>
							</div>
						</div>
						<div class="wooqv-about-img">
							<img src="<?php echo esc_url( SP_WQV_URL . 'admin/help-page/img/shapedplugin-team.jpg' ); ?>" alt="ShapedPlugin Team">
							<span>Team ShapedPlugin LLC at WordCamp Sylhet</span>
						</div>
					</div>
					<?php
					$plugins_arr = get_transient( 'wooqv_plugins' );
					$plugin_icon = array();
					if ( is_array( $plugins_arr ) && ( count( $plugins_arr ) > 0 ) ) {
						foreach ( $plugins_arr as $plugin ) {
							$plugin_icon[ $plugin['slug'] ] = $plugin['icons'];
						}
					}
					?>
					<div class="wooqv-our-plugin-list">
						<h3 class="wooqv-section-title-help">Upgrade your Website with our High-quality Plugins!</h3>
						<div class="wooqv-our-plugin-list-wrap">
							<a target="_blank" class="wooqv-our-plugin-list-box" href="https://wpcarousel.io/?ref=1">
								<i class="wooqv-icon-button-arrow-icon"></i>
								<img src="<?php echo esc_url( $plugin_icon['wp-carousel-free'] ); ?>" alt="WP Carousel">
								<h4>WP Carousel</h4>
								<p>The most powerful and user-friendly multi-purpose carousel, slider, & gallery plugin for WordPress.</p>
							</a>
							<a target="_blank" class="wooqv-our-plugin-list-box" href="https://realtestimonials.io/?ref=1">
								<i class="wooqv-icon-button-arrow-icon"></i>
								<img src="<?php echo esc_url( $plugin_icon['testimonial-free'] ); ?>" alt="Real Testimonials">
								<h4>Real Testimonials</h4>
								<p>Simply collect, manage, and display Testimonials on your website and boost conversions.</p>
							</a>
							<a target="_blank" class="wooqv-our-plugin-list-box" href="https://smartpostshow.com/?ref=1">
								<i class="wooqv-icon-button-arrow-icon"></i>
								<img src="<?php echo esc_url( $plugin_icon['post-carousel'] ); ?>" alt="Smart Post Show">
								<h4>Smart Post Show</h4>
								<p>Filter and display posts (any post types), pages, taxonomy, custom taxonomy, and custom field, in beautiful layouts.</p>
							</a>
							<a target="_blank" href="https://wooproductslider.io/?ref=1" class="wooqv-our-plugin-list-box">
								<i class="wooqv-icon-button-arrow-icon"></i>
								<img src="<?php echo esc_url( $plugin_icon['woo-product-slider'] ); ?>" alt="Product Slider for WooCommerce">
								<h4>Product Slider for WooCommerce</h4>
								<p>Boost sales by interactive product Slider, Grid, and Table in your WooCommerce website or store.</p>
							</a>
							<a target="_blank" class="wooqv-our-plugin-list-box" href="https://woogallery.io/?ref=1">
								<i class="wooqv-icon-button-arrow-icon"></i>
								<img src="<?php echo esc_url( $plugin_icon['gallery-slider-for-woocommerce'] ); ?>" alt="WooGallery">
								<h4>WooGallery</h4>
								<p>Product gallery slider and additional variation images gallery for WooCommerce and boost your sales.</p>
							</a>
							<a target="_blank" class="wooqv-our-plugin-list-box" href="https://getwpteam.com/?ref=1">
								<i class="wooqv-icon-button-arrow-icon"></i>
								<img src="<?php echo esc_url( $plugin_icon['team-free'] ); ?>" alt="Smart Team">
								<h4>Smart Team</h4>
								<p>Display your team members smartly who are at the heart of your company or organization!</p>
							</a>
							<a target="_blank" class="wooqv-our-plugin-list-box" href="https://logocarousel.com/?ref=1">
								<i class="wooqv-icon-button-arrow-icon"></i>
								<img src="<?php echo esc_url( $plugin_icon['logo-carousel-free'] ); ?>" alt="Logo Carousel">
								<h4>Logo Carousel</h4>
								<p>Showcase a group of logo images with Title, Description, Tooltips, Links, and Popup as a grid or in a carousel.</p>
							</a>
							<a target="_blank" class="wooqv-our-plugin-list-box" href="https://easyaccordion.io/?ref=1">
								<i class="wooqv-icon-button-arrow-icon"></i>
								<img src="<?php echo esc_url( $plugin_icon['easy-accordion-free'] ); ?>" alt="Easy Accordion">
								<h4>Easy Accordion</h4>
								<p>Minimize customer support by offering comprehensive FAQs and increasing conversions.</p>
							</a>
							<a target="_blank" class="wooqv-our-plugin-list-box" href="https://shapedplugin.com/woocategory/?ref=1">
								<i class="wooqv-icon-button-arrow-icon"></i>
								<img src="<?php echo esc_url( $plugin_icon['woo-category-slider-grid'] ); ?>" alt="WooCategory">
								<h4>WooCategory</h4>
								<p>Display by filtering the list of categories aesthetically and boosting sales.</p>
							</a>
							<a target="_blank" class="wooqv-our-plugin-list-box" href="https://wptabs.com/?ref=1">
								<i class="wooqv-icon-button-arrow-icon"></i>
								<img src="<?php echo esc_url( $plugin_icon['wp-expand-tabs-free'] ); ?>" alt="Smart Tabs">
								<h4>Smart Tabs</h4>
								<p>Display tabbed content smartly & quickly on your WooCommerce & WordPress site without coding skills.</p>
							</a>
							<a target="_blank" class="wooqv-our-plugin-list-box" href="https://shapedplugin.com/smart-swatches-for-woocommerce/?ref=1">
								<i class="wooqv-icon-button-arrow-icon"></i>
								<img src="<?php echo esc_url( $plugin_icon['smart-swatches'] ); ?>" alt="Smart Swatches for WooCommerce">
								<h4>Smart Swatches for WooCommerce</h4>
								<p>Appealing color, image, and button variation swatches on your WooCommerce Shop and Product pages in minutes to increase sales.</p>
							</a>
							<a target="_blank" class="wooqv-our-plugin-list-box" href="https://shapedplugin.com/plugin/smart-brands-for-woocommerce/?ref=1">
								<i class="wooqv-icon-button-arrow-icon"></i>
								<img src="<?php echo esc_url( $plugin_icon['smart-brands-for-woocommerce'] ); ?>" alt="Smart Brands for WooCommerce">
								<h4>Smart Brands for WooCommerce</h4>
								<p>Smart Brands for WooCommerce Pro helps you display product brands in an attractive way on your online store.</p>
							</a>
						</div>
					</div>
				</div>
			</section>

			<!-- Footer Section -->
			<section class="wooqv-footer-help">
				<div class="wooqv-footer-help-top">
					<p><span>Made With <i class="wooqv-icon-heart"></i> </span> By the Team <a target="_blank" href="https://shapedplugin.com/">ShapedPlugin LLC</a></p>
					<p>Get connected with</p>
					<ul>
						<li><a target="_blank" href="https://www.facebook.com/ShapedPlugin/"><i class="wooqv-icon-fb"></i></a></li>
						<li><a target="_blank" href="https://twitter.com/intent/follow?screen_name=ShapedPlugin"><i class="wooqv-icon-x"></i></a></li>
						<li><a target="_blank" href="https://profiles.wordpress.org/shapedplugin/#content-plugins"><i class="wooqv-icon-wp-icon"></i></a></li>
						<li><a target="_blank" href="https://youtube.com/@ShapedPlugin?sub_confirmation=1"><i class="wooqv-icon-youtube-play"></i></a></li>
					</ul>
				</div>
			</section>
		</div>
		<?php
	}
}
