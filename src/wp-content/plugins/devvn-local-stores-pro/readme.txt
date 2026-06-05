/*
* Author: Lê Văn Toản
*/

/*Code hỗ trợ custom lại HTML*/

add_filter('localstore_html_box', 'localstore_html_box_func', 10, 3);
function localstore_html_box_func($html, $post_id, $localStore){
    ob_start();
    extract($localStore->get_meta_data($post_id));
    $prod_cat = $localStore->get_product_cat($post_id);
    $icon = $localStore->get_localstore_icon($post_id);
    ?>
    <div class="localstore_box" data-id="<?php echo get_the_ID();?>">
        <div class="localstore_img">
            <?php the_post_thumbnail('full');?>
            <?php do_action('localstore_after_thumb', get_the_ID(), $this);?>
        </div>
        <div class="localstore_info">
            <div class="localstore_info_name">
            <strong><?php the_title();?></strong>
            <?php do_action('localstore_after_title', get_the_ID(), $this);?>
            </div>
            <ul>
                <?php if($localstore_address):?><li><i class="fas fa-map-marked-alt"></i> <?php echo $localstore_address;?></li><?php endif;?>
                <?php if($localstore_phone):?><li><i class="fas fas fa-phone-alt"></i> <?php echo $localstore_phone;?></li><?php endif;?>
                <?php if($localstore_hotline):?><li><i class="fa-solid fa-mobile-screen"></i> <?php echo $localstore_hotline;?></li><?php endif;?>
                <?php if($localstore_email):?><li><i class="fa-solid fa-envelope"></i> <?php echo $localstore_email;?></li><?php endif;?>
                <?php if($localstore_open):?><li><i class="fa-solid fa-door-open"></i> <?php echo $localstore_open;?></li><?php endif;?>
                <?php do_action('localstore_li_info', get_the_ID(), $this);?>
                <?php if($prod_cat):?><li><i class="fas fa-caret-right"></i> <?php echo $prod_cat;?></li><?php endif;?>
            </ul>
            <?php do_action('localstore_after_info', get_the_ID(), $this);?>
            <div class="localstore_action">
                <?php if($localstore_link_to):?>
                    <a href="<?php echo esc_url($localstore_link_to);?>" title="" target="_blank" class="localstore_btn"><?php _e('Xem thêm','devvn-local-stores-pro');?></a>
                <?php endif;?>
                <?php if($this->get_option('allow_direct')):?>
                <a href="https://www.google.com/maps/dir//<?php echo esc_attr($localstore_maps_lat);?>,<?php echo esc_attr($localstore_maps_lng);?>" target="_blank" title="Chi đường"><i class="fa-solid fa-location-arrow"></i> <?php _e('Chỉ đường','devvn-local-stores-pro');?></a>
                <?php endif;?>
                <?php do_action('localstore_action', get_the_ID(), $this);?>
            </div>
            <?php do_action('localstore_after_action', get_the_ID(), $this);?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

////////////////////////////////////////////////////

function my_text_strings( $translated_text, $text, $domain ) {
    if($domain != 'devvn-local-stores-pro') return $translated_text;
    switch ( $translated_text ) {
        case 'Có <strong></strong> cửa hàng' :
            $translated_text = __( 'Tìm thấy <strong></strong> Đại lý' );
            break;
        case 'Có <strong></strong> cửa hàng' :
            $translated_text = __( 'Tìm thấy <strong></strong> cửa hàng tại VN' );
            break;
        case 'Xem thêm' :
            $translated_text = __( 'VR360' );
            break;
        case 'Toàn Quốc' :
              $translated_text = __( 'Toàn cầu', 'devvn-local-stores-pro' );
              break;
        case 'Tìm kiếm cửa hàng gần bạn (<=%dkm)' :
              $translated_text = __( 'Tìm kiếm địa điểm gần bạn (<=%dkm)', 'devvn-local-stores-pro' );
              break;
    }
    return $translated_text;
}
add_filter( 'gettext', 'my_text_strings', 20, 3 );

////////////////////////////////////////////////////

function localstore_text_strings( $translated_text, $text, $domain ) {
    if($domain != 'devvn-local-stores-pro') return $translated_text;
    switch ( $text ) {
        case 'Quận/Huyện' :
            $translated_text = __( 'Xã/Phường' );
            break;
        case 'Chọn Quận/Huyện' :
            $translated_text = __( 'Chọn Xã/Phường' );
            break;
    }
    return $translated_text;
}
add_filter( 'gettext', 'localstore_text_strings', 999, 3 );

////////////////////////////////////////////////////
////////////////////////////////////////////////////
$('body').on('after_render_local_store', function (e, a, b){
    console.log(a);
    console.log(b);
});
$('body').on('after_load_local_store', function (e, a){
    console.log(a);
});

////////////////////////////////////////////////////
Hook để enable plugin ở tất cả các trang
add_filter('enable_local_store', '__return_true');

add_filter( 'enable_local_store', function( $enabled ) {
    if ( is_page( 64 ) ) {
        return true;
    }

    return $enabled;
});

////////////////////////////////////////////////////
Filter để thêm metabox

add_filter('localstore_meta_field', 'localstore_meta_field_custom');
function localstore_meta_field_custom($field){
    $field[] = 'custom_field';
    return $field;
}

Code để hiện field metabox trong giao diện đăng bài

add_action('localstore_mid_metabox_field','localstore_mid_metabox_field_func');
function localstore_mid_metabox_field_func($post_id){
    extract(devvn_localstores()->get_meta_data($post_id));
    ?>
    <tr>
        <td class="localstore_label"><?php _e('Custom Field','devvn-local-stores-pro')?></td>
        <td><input type="text" name="localstore_custom_field" id="localstore_custom_field" value="<?php echo esc_attr($localstore_custom_field);?>"/></td>
    </tr>
    <?php
}

////////////////////////////////////////////////////

function get_field_data($args, $field = ''){
    if(is_serialized($args)) {
        $args = maybe_unserialize($args);
        return isset($args[$field]) ? str_replace('Địa chỉ: ', '', $args[$field]) : '';
    }
    return '';
}

//////////////////////////////////////////////////
/*
* Mở public cho post type
*/

add_filter('register_local_store_post_type_args', 'local_store_public');
function local_store_public($args){
    $args['public'] = true;
    return $args;
}
add_filter('register_local_store_state_taxonomy_args', 'local_store_state_public');
function local_store_state_public($args){
    $args['public'] = true;
    return $args;
}

/////////////////////////////////
Set tỉnh thành mặc định theo từng page
add_filter('option_localstore_default_state', function ($value){
    if(is_page()) {
        $default = get_field('localstore_default');
        if($default) return $default;
    }
    return $value;
});
/////////////////////////////////

=== Những thay đổi ===

= V1.2.6.1 - 01.04.2026 =

* Thêm chức năng Fullscreen
* Thêm chức năng "Tối ưu hiệu suất" nếu Bật tối ưu: cache GeoJSON, gom DOM update, tối ưu marker lookup, dedup icon. Tắt để quay về code gốc nếu có lỗi
* Load load Tiếng Việt cho leaflet

= V1.2.5 - 04.12.2025 =

* Fix lỗi nén js

= V1.2.4 - 27.11.2025 =

* Thêm chế độ lazyload, load từng phần, tối ưu bằng js template

= V1.2.3 - 04.09.2025 =

* Fix js bị lỗi khi nén

= V1.2.2 - 04.09.2025 =

* Update thêm hook localstore_before_map_field để mở rộng plugin
* Thêm option để chèn css/js ở mọi nơi trong web. Mặc định sẽ tự xác định trang nào có shortcode mới chèn css/js

= V1.2.1 - 06.08.2025 =

* Hỗ trợ thêm bản đồ của Goong

= V1.2.0 - 27.11.2024 =

* Tối ưu lại query
* Update hàm dịch tương thích với WordPress 6.7.x
* Thêm tuỳ chọn hiển thị cấp Xã/phường/thị trấn trong bộ lọc

= V1.1.9 - 26.12.2023 =

* Update lại format các ký tự đặc biệt trong title của các điểm trên maps
* Thêm filter "localstore_default_state" để tuỳ chỉnh tỉnh mặc định

= V1.1.8 - 25.11.2023 =

* Update lại các lỗi cảnh báo
* Mở rộng core để tương thích với các addon
* Trên mobile: Di chuyển mục địa chỉ xuống dưới maps

= V1.1.7 - 23.11.2023 =

* Thay đổi phương thức lấy dữ liệu của ajax từ POST sang GET

= V1.1.6 - 01.10.2023 =

* Update để tương thích với nhiều addon khác nhau
* Update core

= V1.1.5 - 30.07.2023 =

* Update core tăng bảo mật

= V1.1.4 - 06.06.2023 =

* Thêm alt và title vào icon map marker
* Thêm QĐ. Hoàng Sa và QĐ. Trường Sa vào maps

= V1.1.3 - 31.05.2023 =

* Thêm tính năng sắp xếp các địa điểm theo khoảng khách gần nhất trước khi sử dụng tìm kiếm các cửa hàng gần bạn nhất

= V1.1.2 - 09.05.2023 =

* Tối ưu core để mở rộng addon dễ dàng hơn
* Hỗ trợ đa ngôn ngữ

= V1.1.1 - 17.04.2023 =

* Tối ưu core

= V1.1.0 - 27.03.2023 =

* Cho phép tìm kiếm từ khoá trong địa chỉ
* Fix 1 số lỗi ở bản trước

= V1.0.9 - 09.11.2022 =

* Cho phép chọn tỉnh thành mặc định khi load lần đầu để tối ưu tốc độ khi có quá nhiều địa điểm

= V1.0.8 - 24.10.2022 =

* Fix css trên mobile khi không có ảnh đại diện
* Fix option tương thích với 1 số plugin khác

= V1.0.7 - 08.10.2022 =

* FIX lỗi quan trọng khi sử dụng chung với 1 số plugin khác

= V1.0.6 - 06.10.2022 =

* Update để loại bỏ cách cảnh báo PHP ở bản trước

= V1.0.5 - 25.09.2022 =

* Thêm tính năng lọc các cửa hàng ở gần vị trí hiện tại của khách hàng

= V1.0.4 - 16.09.2022 =

* Thêm templates để tuỳ chỉnh giao diện ở theme hoặc child theme
để custom giao diện bạn hãy copy file php trong plugins/devvn-local-stores-pro/templates sang [your-theme]/devvn-localstore/ để sửa mã php

= V1.0.3 - 28.07.2022 =

* Thêm 2 đường data email và open
* Thêm field bằng filter localstore_meta_field
* Hiển thị box meta vào post bằng hook localstore_before_metabox_field, localstore_mid_metabox_field, localstore_bottom_metabox_field
* Tối ưu trải nghiệm: Click vào danh sách địa chỉ trên MOBILE sẽ tự động sroll xuống dưới maps
* Fix Zoom trên mobile

= V1.0.2 - 22.06.2022 =

* Thêm hiển thị số kết quả cửa hàng tìm được

= V1.0.1 - 20.06.2022 =

* Thêm .pot để có thể dùng cho đa ngôn ngữ
* Thêm chế độ hiển thị bản đồ (satellite) - Tuỳ chỉnh