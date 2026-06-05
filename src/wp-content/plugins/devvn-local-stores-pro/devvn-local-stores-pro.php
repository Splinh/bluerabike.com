<?php
/*
* Plugin Name: DevVN - Local Store Pro
* Version: 1.2.6.1
* Requires PHP: 7.2
* Description: Plugin tạo danh sách cửa hàng và hiển thị lên maps trực quan dễ dàng tìm kiếm. Shortcode hiển thị bản đồ [local_store]
* Author: Lê Văn Toản
* Author URI: https://levantoan.com/
* Plugin URI: https://levantoan.com/san-pham/plugin-danh-sach-dia-diem-local-store-pro/
* Text Domain: devvn-local-stores-pro
* Domain Path: /languages
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if ( !defined( 'DEVVN_LOCALSTORE_VERSION_NUM' ) )
    define( 'DEVVN_LOCALSTORE_VERSION_NUM', '1.2.6.1' );
if ( !defined( 'DEVVN_LOCALSTORE_URL' ) )
    define( 'DEVVN_LOCALSTORE_URL', plugin_dir_url( __FILE__ ) );
if ( !defined( 'DEVVN_LOCALSTORE_BASENAME' ) )
    define( 'DEVVN_LOCALSTORE_BASENAME', plugin_basename( __FILE__ ) );
if ( !defined( 'DEVVN_LOCALSTORE_PLUGIN_DIR' ) )
    define( 'DEVVN_LOCALSTORE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
if ( !defined( 'DEVVN_LOCALSTORE_TEXTDOMAIN' ) )
    define( 'DEVVN_LOCALSTORE_TEXTDOMAIN', 'devvn-local-stores-pro' );

if(extension_loaded('ionCube Loader')) {
    if(file_exists(plugin_dir_path(__FILE__) . 'license.php')) {
        include_once plugin_dir_path(__FILE__) . 'license.php';
    }
    include 'includes/main.php';
}else{
    function devvn_localstore_admin_notice__error() {
        $class = 'notice notice-alt notice-warning notice-error';
        $title = '<h2 class="notice-title">Chú ý!</h2>';
        $message = __( 'Để Plugin <strong>DevVN - Local Store Pro</strong> hoạt động, bắt buộc cần kích hoạt <strong>php extension ionCube</strong>.', DEVVN_LOCALSTORE_TEXTDOMAIN );
        $btn = '<p><a href="https://levantoan.com/huong-dan-kich-hoat-extension-ioncube/" target="_blank" rel="nofollow" class="button-primary">Xem hướng dẫn tại đây</a></a></p>';

        printf( '<div class="%1$s">%2$s<p>%3$s</p>%4$s</div>', esc_attr( $class ), $title, $message, $btn );
    }
    add_action( 'admin_notices', 'devvn_localstore_admin_notice__error' );
}