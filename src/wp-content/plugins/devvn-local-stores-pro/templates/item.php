<?php
extract($thisClass->get_meta_data($postid));
$prod_cat = $thisClass->get_product_cat($postid);
$icon = $thisClass->get_localstore_icon($postid);
?>
<div class="localstore_box <?php echo (has_post_thumbnail($postid)) ? 'has_thumb' : 'no_thumb';?>" data-id="<?php echo $postid;?>">
    <?php if(has_post_thumbnail($postid)):?>
    <div class="localstore_img">
        <?php the_post_thumbnail('full');?>
        <?php do_action('localstore_after_thumb', $postid, $thisClass);?>
    </div>
    <?php endif;?>
    <div class="localstore_info">
        <div class="localstore_info_name">
            <strong><?php the_title();?></strong>
            <?php
            $ratings = $thisClass->rating_field();
            if($ratings && is_array($ratings) && !empty($ratings)){
                $max_rating = count($ratings);
                if($max_rating){
                    $rating_value = $localstore_rating;
                    $percent = ($rating_value/$max_rating)*100;
                    ?>
                    <span class="localstore_rating">
                        <span class="localstore_rating_first">
                            <?php for ($i=1; $i<=$max_rating; $i++){?>
                                <i class="fa-regular fa-star"></i>
                            <?php }?>
                        </span>
                        <span style="width:<?php echo $percent;?>%">
                            <?php for ($i=1; $i<=$max_rating; $i++){?>
                                <i class="fa-solid fa-star"></i>
                            <?php }?>
                        </span>
                    </span>
                <?php }?>
            <?php }?>
            <?php do_action('localstore_after_title', $postid, $thisClass);?>
        </div>
        <ul>
            <?php if($localstore_address):?><li><i class="fas fa-map-marked-alt"></i> <?php echo $localstore_address;?></li><?php endif;?>
            <?php if($localstore_phone):?><li><i class="fas fas fa-phone-alt"></i> <?php echo $localstore_phone;?></li><?php endif;?>
            <?php if($localstore_hotline):?><li><i class="fa-solid fa-mobile-screen"></i> <?php echo $localstore_hotline;?></li><?php endif;?>
            <?php if($localstore_email):?><li><i class="fa-solid fa-envelope"></i> <?php echo $localstore_email;?></li><?php endif;?>
            <?php if($localstore_open):?><li><i class="fa-solid fa-door-open"></i> <?php echo $localstore_open;?></li><?php endif;?>
            <?php do_action('localstore_li_info', $postid, $thisClass);?>
            <?php if($prod_cat):?><li><i class="fas fa-caret-right"></i> <?php echo $prod_cat;?></li><?php endif;?>
        </ul>
        <?php do_action('localstore_after_info', $postid, $thisClass);?>
        <div class="localstore_action">
            <?php if($localstore_link_to):?>
                <a href="<?php echo esc_url($localstore_link_to);?>" title="" target="_blank" class="localstore_btn"><?php _e('Xem thêm','devvn-local-stores-pro');?></a>
            <?php endif;?>
            <?php if($thisClass->get_option('allow_direct')):?>
                <a href="https://www.google.com/maps/dir/Current+Location/<?php echo esc_attr($localstore_maps_lat);?>,<?php echo esc_attr($localstore_maps_lng);?>" target="_blank" title="Chi đường"><i class="fa-solid fa-location-arrow"></i> <?php _e('Chỉ đường','devvn-local-stores-pro');?></a>
            <?php endif;?>
            <?php do_action('localstore_action', $postid, $thisClass);?>
        </div>
        <?php do_action('localstore_after_action', $postid, $thisClass);?>
    </div>
</div>