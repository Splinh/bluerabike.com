jQuery(function($){
    const mapEl = document.getElementById('spl-admin-map');
    if(!mapEl) return;
    const latFld = $('#spl_lat'), lngFld = $('#spl_lng');
    const lat = parseFloat(latFld.val()||'10.776'), lng = parseFloat(lngFld.val()||'106.700');
    const map = L.map(mapEl).setView([lat,lng], 16);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);
    const marker = L.marker([lat,lng],{draggable:true}).addTo(map);
    marker.on('dragend', function(e){
        const p = e.target.getLatLng(); latFld.val(p.lat.toFixed(6)); lngFld.val(p.lng.toFixed(6));
    });
    map.on('click', function(e){ marker.setLatLng(e.latlng); latFld.val(e.latlng.lat.toFixed(6)); lngFld.val(e.latlng.lng.toFixed(6)); });

    // Media chooser
    $(document).on('click','#spl_choose_marker', function(e){
        e.preventDefault();
        const frame = wp.media({title:'Chọn icon đánh dấu', button:{text:'Chọn'}, multiple:false});
        frame.on('select', function(){
            const att = frame.state().get('selection').first().toJSON();
            $('#spl_marker_id').val(att.id);
            $('#spl_marker_preview').html('<img src=\"'+att.url+'\" />');
        });
        frame.open();
    });
});