<script type="text/html" id="tmpl-localstore-item">
<div class="localstore_box {{ data.has_thumb ? 'has_thumb' : 'no_thumb' }}" data-id="{{ data.id }}">
    <# if(data.thumbnail) { #>
    <div class="localstore_img">
        <img src="{{ data.thumbnail }}" alt="{{ data.title }}">
    </div>
    <# } #>
    <div class="localstore_info">
        <div class="localstore_info_name">
            <strong>{{{ data.title }}}</strong>
            <# if(data.rating) { #>
            <span class="localstore_rating">
                <span class="localstore_rating_first">
                    <# for(var i=1; i<=5; i++) { #>
                        <i class="fa-regular fa-star"></i>
                    <# } #>
                </span>
                <span style="width:{{ (data.rating/5)*100 }}%">
                    <# for(var i=1; i<=5; i++) { #>
                        <i class="fa-solid fa-star"></i>
                    <# } #>
                </span>
            </span>
            <# } #>
        </div>
        <ul>
            <# if(data.address) { #><li><i class="fas fa-map-marked-alt"></i> {{{ data.address }}}</li><# } #>
            <# if(data.phone) { #><li><i class="fas fas fa-phone-alt"></i> {{{ data.phone }}}</li><# } #>
            <# if(data.hotline) { #><li><i class="fa-solid fa-mobile-screen"></i> {{{ data.hotline }}}</li><# } #>
            <# if(data.email) { #><li><i class="fa-solid fa-envelope"></i> {{{ data.email }}}</li><# } #>
            <# if(data.open) { #><li><i class="fa-solid fa-door-open"></i> {{{ data.open }}}</li><# } #>
            <# if(data.prod_cat) { #><li><i class="fas fa-caret-right"></i> {{{ data.prod_cat }}}</li><# } #>
        </ul>
        <div class="localstore_action">
            <# if(data.link_to) { #>
                <a href="{{ data.link_to }}" title="" target="_blank" class="localstore_btn">Xem thêm</a>
            <# } #>
            <# if(data.allow_direct) { #>
                <a href="https://www.google.com/maps/dir/Current+Location/{{ data.maps_lat }},{{ data.maps_lng }}" target="_blank" title="Chi đường"><i class="fa-solid fa-location-arrow"></i> Chỉ đường</a>
            <# } #>
        </div>
    </div>
</div>
</script>

