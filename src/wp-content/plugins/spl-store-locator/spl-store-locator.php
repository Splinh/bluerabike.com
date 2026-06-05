<?php
/**
 * Plugin Name: SPL Store Locator
 * Description: Hệ Thống Cửa Hàng (Leaflet). Tìm theo tên, lọc Tỉnh/Quận, tìm gần bạn (<20km). Popup có ảnh đại diện + nút Chỉ đường. Shortcode: [spl_store_locator]
 * Version: 1.1.0
 * Author: SPL
 * Text Domain: spl-store
 */

if ( ! defined('ABSPATH') ) exit;

class SPL_Store_Locator {
    const CPT = 'spl_store';
    const TAX_PROVINCE = 'spl_province';
    const TAX_DISTRICT = 'spl_district';

    const META_ADDR   = '_spl_address';
    const META_PHONE  = '_spl_phone';
    const META_HOTLINE= '_spl_hotline';
    const META_EMAIL  = '_spl_email';
    const META_OPEN   = '_spl_open';
    const META_BTN_URL= '_spl_btn_url';
    const META_MARKER = '_spl_marker_id'; // attachment id
    const META_LAT    = '_spl_lat';
    const META_LNG    = '_spl_lng';

    public function __construct(){
        add_action('init', [$this,'register_types']);
        add_action('add_meta_boxes', [$this,'add_boxes']);
        add_action('save_post', [$this,'save_meta']);
        add_action('admin_enqueue_scripts', [$this,'admin_assets']);
        add_action('wp_enqueue_scripts', [$this,'front_assets']);
        add_shortcode('spl_store_locator', [$this,'shortcode']);

        add_action('rest_api_init', function(){
            register_rest_route('spl-store/v1','/locations', [
                'methods' => 'GET',
                'callback' => [$this,'rest_locations'],
                'permission_callback' => '__return_true'
            ]);
        });
    }

    public function register_types(){
        register_post_type(self::CPT, [
            'label' => __('Hệ Thống Cửa Hàng','spl-store'),
            'labels' => [
                'name' => __('Hệ Thống Cửa Hàng','spl-store'),
                'singular_name' => __('Cửa hàng','spl-store'),
                'add_new_item' => __('Thêm cửa hàng','spl-store'),
                'edit_item' => __('Sửa cửa hàng','spl-store'),
            ],
            'public' => true,
            'menu_icon' => 'dashicons-store',
            'supports' => ['title','editor','thumbnail'],
            'has_archive' => false,
            'show_in_rest' => true,
        ]);

        register_taxonomy(self::TAX_PROVINCE, self::CPT, [
            'label' => __('Tỉnh/Thành','spl-store'),
            'public' => true,
            'hierarchical' => true,
            'show_in_rest' => true,
        ]);

        register_taxonomy(self::TAX_DISTRICT, self::CPT, [
            'label' => __('Quận/Huyện','spl-store'),
            'public' => true,
            'hierarchical' => true,
            'show_in_rest' => true,
        ]);
    }

    public function add_boxes(){
        add_meta_box('spl_store_info', __('Thông tin cửa hàng','spl-store'), [$this,'box_render'], self::CPT, 'normal','high');
    }

    public function admin_assets($hook){
        global $post;
        if( ($hook === 'post-new.php' || $hook === 'post.php') && isset($post->post_type) && $post->post_type===self::CPT ){
            wp_enqueue_style('leaflet','https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',[], '1.9.4');
            wp_enqueue_script('leaflet','https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',[], '1.9.4', true);
            wp_enqueue_style('spl-sl-admin', plugins_url('assets/admin.css', __FILE__), [], '1.0.0');
            wp_enqueue_script('spl-sl-admin', plugins_url('assets/admin.js', __FILE__), ['leaflet','jquery'], '1.0.0', true);
            wp_enqueue_media();
        }
    }

    public function box_render($post){
        wp_nonce_field('spl_store_save','spl_store_nonce');
        $v = function($key,$d=''){ return esc_attr(get_post_meta($post->ID,$key,true)?:$d); };
        $marker_id = (int) get_post_meta($post->ID,self::META_MARKER,true);
        $marker_src = $marker_id ? wp_get_attachment_image_url($marker_id,'thumbnail') : '';
        ?>
        <div class="spl-store-grid">
            <div class="spl-left">
                <p><label>Địa chỉ<br><input class="widefat" type="text" name="spl_address" value="<?php echo esc_attr(get_post_meta($post->ID,self::META_ADDR,true)); ?>"></label></p>
                <p><label>Số điện thoại<br><input class="widefat" type="text" name="spl_phone" value="<?php echo $v(self::META_PHONE); ?>"></label></p>
                <p><label>Hotline<br><input class="widefat" type="text" name="spl_hotline" value="<?php echo $v(self::META_HOTLINE); ?>"></label></p>
                <p><label>Email<br><input class="widefat" type="email" name="spl_email" value="<?php echo $v(self::META_EMAIL); ?>"></label></p>
                <p><label>Open (Giờ mở cửa)<br><input class="widefat" type="text" name="spl_open" value="<?php echo $v(self::META_OPEN); ?>"></label></p>
                <p><label>Đường dẫn của nút (Chỉ đường/Website)<br><input class="widefat" type="text" name="spl_btn_url" value="<?php echo $v(self::META_BTN_URL); ?>"></label></p>
                <p class="spl-row">
                    <button class="button" id="spl_choose_marker" type="button"><?php _e('Chọn icon','spl-store'); ?></button>
                    <input type="hidden" id="spl_marker_id" name="spl_marker_id" value="<?php echo esc_attr($marker_id); ?>">
                    <span id="spl_marker_preview"><?php if($marker_src) echo '<img style="height:28px" src="'.esc_url($marker_src).'" />'; ?></span>
                </p>
            </div>
            <div class="spl-right">
                <div class="spl-row-2">
                    <p><label>Latitude<br><input id="spl_lat" type="text" name="spl_lat" value="<?php echo $v(self::META_LAT,'10.776'); ?>"></label></p>
                    <p><label>Longitude<br><input id="spl_lng" type="text" name="spl_lng" value="<?php echo $v(self::META_LNG,'106.700'); ?>"></label></p>
                </div>
                <div id="spl-admin-map" class="spl-map"></div>
                <p class="spl-hint">Kéo icon hoặc click vào bản đồ để chọn vị trí chính xác</p>
            </div>
        </div>
        <?php
    }

    public function save_meta($post_id){
        if( !isset($_POST['spl_store_nonce']) || !wp_verify_nonce($_POST['spl_store_nonce'], 'spl_store_save') ) return;
        if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
        if( get_post_type($post_id)!==self::CPT ) return;

        $fields = [
            self::META_ADDR => sanitize_text_field($_POST['spl_address'] ?? ''),
            self::META_LAT => sanitize_text_field($_POST['spl_lat'] ?? ''),
            self::META_LNG => sanitize_text_field($_POST['spl_lng'] ?? ''),
            self::META_PHONE => sanitize_text_field($_POST['spl_phone'] ?? ''),
            self::META_HOTLINE => sanitize_text_field($_POST['spl_hotline'] ?? ''),
            self::META_EMAIL => sanitize_email($_POST['spl_email'] ?? ''),
            self::META_OPEN => sanitize_text_field($_POST['spl_open'] ?? ''),
            self::META_BTN_URL => esc_url_raw($_POST['spl_btn_url'] ?? ''),
            self::META_MARKER => (int) ($_POST['spl_marker_id'] ?? 0),
        ];
        foreach($fields as $k=>$v){ update_post_meta($post_id,$k,$v); }
    }

    public function front_assets(){
        wp_enqueue_style('leaflet','https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',[], '1.9.4');
        wp_enqueue_script('leaflet','https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',[], '1.9.4', true);

        wp_enqueue_style('spl-sl-front', plugins_url('assets/front.css', __FILE__), [], '1.1.0');
        wp_enqueue_script('spl-sl-front', plugins_url('assets/front.js', __FILE__), ['leaflet','jquery'], '1.1.0', true);
        wp_localize_script('spl-sl-front','SPL_SL',[
            'rest' => esc_url_raw( rest_url('spl-store/v1/locations') ),
            'locationsJson' => plugins_url('assets/vn_locations.json', __FILE__),
            'primaryColor' => '#1e78c2'
        ]);
    }

    public function shortcode($atts){
        ob_start(); ?>
        <div class="spl-sl">
            <div>
                <form id="spl-form" class="spl-form">
                    <label>Tìm theo tên<br>
                        <input id="spl-q" class="widefat" placeholder="Nhập từ khóa tìm kiếm theo tên" type="text" />
                    </label>
                    <div class="spl-2col">
                        <select id="spl-filter-province"><option value="">Chọn tỉnh, thành phố</option></select>
                        <select id="spl-filter-district"><option value="">Chọn quận, huyện</option></select>
                    </div>
                    <label class="spl-near"><input id="spl-near" type="checkbox"/> Tìm kiếm cửa hàng gần bạn (&lt;=20km)</label>
                    <button class="button button-primary spl-btn" type="submit">TÌM KIẾM</button>
                    <div class="spl-count">Có <strong id="spl-count">0</strong> cửa hàng</div>
                </form>
                <div id="spl-list" class="spl-list"></div>
            </div>
            <div id="spl-map" class="spl-map-out"></div>
        </div>
        <?php return ob_get_clean();
    }

    public function rest_locations( WP_REST_Request $req ){
        if( $req->get_param('onlyTerms') ){
            $provs = get_terms(['taxonomy'=>self::TAX_PROVINCE,'hide_empty'=>false]);
            $dists = [];
            if( $province = sanitize_text_field($req->get_param('province')) ){
                $dists = get_terms(['taxonomy'=>self::TAX_DISTRICT,'hide_empty'=>false]);
            }
            return [
                'provinces' => array_map(function($t){ return ['slug'=>$t->slug,'name'=>$t->name]; }, $provs?:[]),
                'districts' => array_map(function($t){ return ['slug'=>$t->slug,'name'=>$t->name]; }, $dists?:[]),
            ];
        }

        $q = sanitize_text_field($req->get_param('q'));
        $province = sanitize_text_field($req->get_param('province'));
        $district = sanitize_text_field($req->get_param('district'));
        $near = $req->get_param('near');
        $lat = floatval($req->get_param('lat'));
        $lng = floatval($req->get_param('lng'));

        $args = [
            'post_type' => self::CPT,
            'post_status' => 'publish',
            's' => $q,
            'posts_per_page' => 500,
            'tax_query' => ['relation'=>'AND']
        ];
        if($province){ $args['tax_query'][] = ['taxonomy'=>self::TAX_PROVINCE,'field'=>'slug','terms'=>[$province]]; }
        if($district){ $args['tax_query'][] = ['taxonomy'=>self::TAX_DISTRICT,'field'=>'slug','terms'=>[$district]]; }
        if(count($args['tax_query'])===1) unset($args['tax_query']);

        $query = new WP_Query($args);
        $items = [];
        if($query->have_posts()){
            foreach($query->posts as $p){
                $item = [
                    'id' => $p->ID,
                    'title' => get_the_title($p),
                    'address' => get_post_meta($p->ID,self::META_ADDR,true),
                    'phone' => get_post_meta($p->ID,self::META_PHONE,true),
                    'hotline' => get_post_meta($p->ID,self::META_HOTLINE,true),
                    'email' => get_post_meta($p->ID,self::META_EMAIL,true),
                    'open' => get_post_meta($p->ID,self::META_OPEN,true),
                    'btn'  => get_post_meta($p->ID,self::META_BTN_URL,true),
                    'lat'  => get_post_meta($p->ID,self::META_LAT,true),
                    'lng'  => get_post_meta($p->ID,self::META_LNG,true),
                    'marker'=> ($id = get_post_meta($p->ID,self::META_MARKER,true)) ? wp_get_attachment_image_url($id,'full') : null,
                    'permalink' => get_permalink($p),
                    'thumb' => get_the_post_thumbnail_url($p, 'medium'),
                ];
                $items[] = $item;
            }
        }

        if( $near && $lat && $lng ){
            $items = array_filter($items, function($it) use($lat,$lng){
                if(empty($it['lat'])||empty($it['lng'])) return false;
                $d = $this->haversine($lat,$lng, floatval($it['lat']), floatval($it['lng']));
                return ($d <= 20);
            });
            usort($items, function($a,$b) use($lat,$lng){
                $da = $this->haversine($lat,$lng, floatval($a['lat']), floatval($a['lng']));
                $db = $this->haversine($lat,$lng, floatval($b['lat']), floatval($b['lng']));
                return $da <=> $db;
            });
        }

        return [ 'items' => array_values($items) ];
    }

    private function haversine($lat1,$lon1,$lat2,$lon2){
        $R = 6371; // km
        $dLat = deg2rad($lat2-$lat1);
        $dLon = deg2rad($lon2-$lon1);
        $a = sin($dLat/2)*sin($dLat/2) + cos(deg2rad($lat1))*cos(deg2rad($lat2))*sin($dLon/2)*sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $R*$c;
    }
}
new SPL_Store_Locator();
