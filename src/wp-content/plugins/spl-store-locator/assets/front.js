(function(){
    const cfg = (window.SPL_SL||{});
    const rest = cfg.rest || '';
    const locationsJson = cfg.locationsJson || '';
    const primary = cfg.primaryColor || '#1e78c2';
    let map, markersLayer, userPos = null, markerById = {};

    function qs(n){return document.querySelector(n)}
    function ce(tag,cls){const el=document.createElement(tag); if(cls) el.className=cls; return el;}

    async function fetchJSON(url){ const r = await fetch(url); return await r.json(); }

    // Load VN provinces & districts from bundled JSON
    let VN = null;
    async function ensureVN(){
        if(VN) return VN;
        try { VN = await fetchJSON(locationsJson); } catch(e){ VN = null; }
        return VN;
    }

    async function populateProvinces(){
        const vn = await ensureVN();
        const pSel = qs('#spl-filter-province');
        pSel.innerHTML = '<option value="">Chọn tỉnh, thành phố</option>';
        if(vn && vn.provinces){
            vn.provinces.forEach(p=>{ const o=ce('option'); o.value=p.code; o.textContent=p.name; pSel.appendChild(o); });
        }
    }
    async function loadDistrictsByJson(provinceCode){
        const sel = qs('#spl-filter-district');
        sel.innerHTML = '<option value="">Chọn quận, huyện</option>';
        if(!provinceCode) return;
        const vn = await ensureVN(); if(!vn) return;
        const p = vn.provinces.find(x=>String(x.code)===String(provinceCode));
        if(!p) return;
        (p.districts||[]).forEach(d=>{ const o=ce('option'); o.value=d.code; o.textContent=d.name; sel.appendChild(o); });
    }

    async function search(){
        const q = qs('#spl-q').value.trim();
        const prov = qs('#spl-filter-province').value;
        const dist = qs('#spl-filter-district').value;
        const near = qs('#spl-near').checked;
        let url = `${rest}?q=${encodeURIComponent(q)}`;
        const vn = await ensureVN();
        if(prov && vn){ const p = vn.provinces.find(x=>String(x.code)===String(prov)); if(p) url += `&province=${encodeURIComponent(p.slug||p.name)}`; }
        if(dist && vn){ const p = vn.provinces.find(x=>String(x.code)===String(prov)); const d = p?(p.districts||[]).find(x=>String(x.code)===String(dist)):null; if(d) url += `&district=${encodeURIComponent(d.slug||d.name)}`; }
        if(near && userPos){ url += `&near=1&lat=${userPos.lat}&lng=${userPos.lng}`; }
        const data = await fetchJSON(url);
        const items = data.items||[];
        renderList(items);
        renderMap(items);
        qs('#spl-count').textContent = items.length;
    }

    function markerPopupHtml(it){
        const img = it.thumb ? `<img class="cover" src="${it.thumb}" alt="${it.title}"/>` : '';
        const addr = it.address ? `<div class="addr">${it.address}</div>` : '';
        const phone = it.phone ? `<div class="phone">ĐT: ${it.phone}</div>` : '';
        const ggl = (it.lat && it.lng) ? `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(it.lat+','+it.lng)}&travelmode=driving` : (it.btn||'#');
        const btns = `<div class="act"><a class="primary" href="${ggl}" target="_blank" rel="nofollow noopener">Chỉ đường</a> ${it.btn?`<a href="${it.btn}" target="_blank">Mở link</a>`:''}</div>`;
        return `<div class="spl-popup"><div>${img}</div><h5>${it.title}</h5>${addr}${phone}${btns}</div>`;
    }

    function renderMap(items){
        if(!map){
            map = L.map('spl-map').setView([15.9,105.8], 5);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution:'© OpenStreetMap' }).addTo(map);
        }
        if(markersLayer){ markersLayer.clearLayers(); markerById = {}; } else { markersLayer = L.layerGroup().addTo(map); }
        if(items.length){
            const bounds = [];
            items.forEach(it=>{
                if(!it.lat || !it.lng) return;
                const markerOpt = {};
                if(it.marker){ markerOpt.icon = L.icon({iconUrl: it.marker, iconSize:[28,28], iconAnchor:[14,28]}); }
                const m = L.marker([parseFloat(it.lat), parseFloat(it.lng)], markerOpt).addTo(markersLayer);
                m.bindPopup(markerPopupHtml(it));
                markerById[it.id] = m;
                bounds.push([it.lat, it.lng]);
            });
            if(bounds.length){ map.fitBounds(bounds, {padding:[20,20]}); }
        }
    }

    function renderList(items){
        const wrap = qs('#spl-list'); wrap.innerHTML = '';
        items.forEach(it=>{
            const div = ce('div','spl-item');
            const thumb = ce('img','spl-thumb'); thumb.src = it.thumb || ''; thumb.alt = it.title; div.appendChild(thumb);
            const info = ce('div'); div.appendChild(info);
            const h = ce('h4'); const a = ce('a'); a.href = it.permalink || '#'; a.textContent = it.title; h.appendChild(a); info.appendChild(h);
            const p1 = ce('div','muted'); p1.textContent = it.address||''; info.appendChild(p1);
            const p2 = ce('div','muted'); p2.innerHTML = `${it.phone?('📞 '+it.phone+' '):''}${it.hotline?('<span class="spl-pill">Hotline: '+it.hotline+'</span>'):''}`; info.appendChild(p2);
            const acts = ce('div','spl-actions');
            if(it.lat && it.lng){ 
                const gg = `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(it.lat+','+it.lng)}&travelmode=driving`;
                const a1 = ce('a'); a1.href = gg; a1.target = '_blank'; a1.textContent = 'Chỉ đường'; acts.appendChild(a1);
            }
            if(it.btn){ const a2 = ce('a'); a2.href = it.btn; a2.target = '_blank'; a2.textContent = 'Mở link'; acts.appendChild(a2); }
            info.appendChild(acts);

            div.addEventListener('mouseenter', ()=>{
                const m = markerById[it.id]; if(m){ m.openPopup(); try{ map.panTo(m.getLatLng(),{animate:true}); }catch(e){} }
            });
            wrap.appendChild(div);
        });
    }

    async function geolocate(){
        return new Promise((resolve)=>{
            if(!navigator.geolocation) return resolve();
            navigator.geolocation.getCurrentPosition(pos=>{
                userPos = {lat: pos.coords.latitude, lng: pos.coords.longitude};
                resolve();
            },()=>resolve(), {enableHighAccuracy:true, timeout:8000});
        });
    }

    function init(){
        const style = document.createElement('style'); style.innerHTML = `:root{--spl-primary:${primary}}`; document.head.appendChild(style);
        document.getElementById('spl-form').addEventListener('submit', function(e){ e.preventDefault(); search(); });
        document.getElementById('spl-filter-province').addEventListener('change', function(){ loadDistrictsByJson(this.value); search(); });
        document.getElementById('spl-filter-district').addEventListener('change', search);
        document.getElementById('spl-near').addEventListener('change', async function(){ if(this.checked){ await geolocate(); } search(); });
        populateProvinces().then(search);
    }

    document.addEventListener('DOMContentLoaded', init);
})();