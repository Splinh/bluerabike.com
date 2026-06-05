<div class="devvn_localstore_wrap">
    <div class="localstore_search_wrap">
        <div class="localstore_searchbox">
            <form action="" method="post" id="localstore_search_form">
                <div class="dvls_flex">
                    <?php do_action('localstore_before_form', $thisClass, $atts);?>
                    <div class="dvls_flex_box dvls_col_full keywork_box">
                        <input type="text" name="keywork" id="keywork" placeholder="<?php _e('Nhập từ khoá tìm kiếm theo tên','devvn-local-stores-pro');?>">
                        <input type="hidden" name="action" value="get_local_stores">
                        <button type="submit" class="btton_submit"><i class="fas fa-search"></i></button>
                    </div>
                    <?php do_action('localstore_mid_form', $thisClass, $atts);?>
                    <div class="dvls_flex_box dvls_city_box">
                        <select name="city" id="dvls_city" data-value="<?php echo esc_attr(apply_filters('localstore_default_state', $thisClass->get_option('default_state'), $thisClass, $atts));?>">
                            <option value=""><?php _e('Toàn Quốc','devvn-local-stores-pro');?></option>
                        </select>
                    </div>
                    <div class="dvls_flex_box dvls_district_box">
                        <select name="district" id="dvls_district" data-value="">
                            <option value=""><?php _e('Quận/Huyện','devvn-local-stores-pro');?></option>
                        </select>
                    </div>
                    <?php do_action('localstore_after_form', $thisClass, $atts);?>
                </div>
            </form>
        </div>
        <?php if($thisClass->get_option('show_count')):?><div class="localstore_search_count"></div><?php endif;?>
        <div class="localstore_search_results hide_mobile" <?php echo apply_filters('localstore_search_results', '', $thisClass, $atts);?>></div>
        <span class="close_search"><i class="fas fa-angle-left"></i></span>
    </div>
    <div class="localstore_maps">
        <div id="localstore_maps"></div>
    </div>
    <div class="localstore_search_results show_mobile" <?php echo apply_filters('localstore_search_results', '', $thisClass, $atts);?>></div>
</div>