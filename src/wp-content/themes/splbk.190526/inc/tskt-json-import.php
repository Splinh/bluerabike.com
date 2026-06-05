<?php
/**
 * TSKT JSON Import Tool – Import thông số kỹ thuật từ file JSON (exported từ dailyxedien)
 * vào ACF Repeater trên babyshop/bluerabike.
 *
 * JSON format expected:
 * [
 *   {
 *     "product_name": "Tên SP",
 *     "product_slug": "slug",
 *     "product_sku":  "SKU",
 *     "tskt_rows": [ {"tskt_label":"...", "tskt_value":"..."}, ... ]
 *   }, ...
 * ]
 */

if (!defined('ABSPATH')) exit;

// ── Admin menu ──
add_action('admin_menu', function () {
    add_submenu_page(
        'edit.php?post_type=product',
        'Import TSKT (JSON)',
        '⚡ Import TSKT',
        'manage_woocommerce',
        'tskt-json-import',
        'tskt_json_import_render_page'
    );
});

// ── Enqueue Select2 ──
add_action('admin_enqueue_scripts', function ($hook) {
    if (strpos($hook, 'tskt-json-import') === false) return;
    wp_enqueue_style('select2', WC()->plugin_url() . '/assets/css/select2.css');
    wp_enqueue_script('select2', WC()->plugin_url() . '/assets/js/select2/select2.full.min.js', ['jquery'], null, true);
});

// ── AJAX: Import single product TSKT from JSON data ──
add_action('wp_ajax_tskt_json_do_import', function () {
    check_ajax_referer('tskt_json_import_nonce', 'nonce');
    if (!current_user_can('manage_woocommerce')) wp_send_json_error('No permission');

    $product_id  = intval($_POST['product_id'] ?? 0);
    $create_new  = sanitize_text_field($_POST['create_new'] ?? '');
    $rows_raw    = stripslashes($_POST['tskt_rows'] ?? '[]');
    $rows        = json_decode($rows_raw, true);
    $mode        = sanitize_text_field($_POST['mode'] ?? 'replace');

    if (empty($rows)) wp_send_json_error('Missing TSKT data');

    // ── Create new product if needed ──
    if (!$product_id && $create_new === 'yes') {
        try {
            $name  = sanitize_text_field($_POST['source_name'] ?? 'Sản phẩm mới');
            $slug  = sanitize_title($_POST['source_slug'] ?? '');
            $sku   = sanitize_text_field($_POST['source_sku'] ?? '');
            $price = sanitize_text_field($_POST['source_price'] ?? '');
            $sale  = sanitize_text_field($_POST['source_sale_price'] ?? '');
            $desc  = wp_kses_post($_POST['source_short_desc'] ?? '');

            $product = new \WC_Product_Simple();
            $product->set_name($name);
            $product->set_status('draft');
            if ($slug) $product->set_slug($slug);
            // SKU có thể trùng → bỏ qua nếu lỗi
            if ($sku) {
                try { $product->set_sku($sku); } catch (\Exception $e) { /* SKU trùng, bỏ qua */ }
            }
            if ($price) $product->set_regular_price($price);
            if ($sale)  $product->set_sale_price($sale);
            if ($desc)  $product->set_short_description($desc);
            $product->set_catalog_visibility('visible');
            $product_id = $product->save();

            if (!$product_id) wp_send_json_error('Không thể tạo sản phẩm mới');

            // ── Featured image ──
            $featured_url = esc_url_raw($_POST['source_featured_image'] ?? '');
            if ($featured_url) {
                require_once ABSPATH . 'wp-admin/includes/media.php';
                require_once ABSPATH . 'wp-admin/includes/file.php';
                require_once ABSPATH . 'wp-admin/includes/image.php';

                $attach_id = @media_sideload_image($featured_url, $product_id, $name, 'id');
                if (!is_wp_error($attach_id) && $attach_id) {
                    set_post_thumbnail($product_id, $attach_id);
                }
            }

            // ── Gallery images ──
            $gallery_raw = stripslashes($_POST['source_gallery'] ?? '[]');
            $gallery_urls = json_decode($gallery_raw, true);
            if (!empty($gallery_urls) && is_array($gallery_urls)) {
                $gallery_ids = [];
                foreach ($gallery_urls as $gurl) {
                    $gurl = esc_url_raw($gurl);
                    if (!$gurl) continue;
                    $gid = @media_sideload_image($gurl, $product_id, '', 'id');
                    if (!is_wp_error($gid) && $gid) $gallery_ids[] = $gid;
                }
                if (!empty($gallery_ids)) {
                    $product = wc_get_product($product_id);
                    $product->set_gallery_image_ids($gallery_ids);
                    $product->save();
                }
            }

            // ── Categories ──
            $cats_raw = stripslashes($_POST['source_categories'] ?? '[]');
            $cats = json_decode($cats_raw, true);
            if (!empty($cats) && is_array($cats)) {
                $cat_ids = [];
                foreach ($cats as $cat) {
                    $cat_name = sanitize_text_field($cat['name'] ?? '');
                    $cat_slug = sanitize_title($cat['slug'] ?? '');
                    if (!$cat_name) continue;

                    $term = get_term_by('slug', $cat_slug, 'product_cat');
                    if (!$term) {
                        $parent_id = 0;
                        $parent_slug = sanitize_title($cat['parent'] ?? '');
                        if ($parent_slug) {
                            $parent_term = get_term_by('slug', $parent_slug, 'product_cat');
                            if ($parent_term) $parent_id = $parent_term->term_id;
                        }
                        $result = wp_insert_term($cat_name, 'product_cat', [
                            'slug'   => $cat_slug,
                            'parent' => $parent_id,
                        ]);
                        if (!is_wp_error($result)) {
                            $cat_ids[] = $result['term_id'];
                        }
                    } else {
                        $cat_ids[] = $term->term_id;
                    }
                }
                if (!empty($cat_ids)) {
                    wp_set_object_terms($product_id, $cat_ids, 'product_cat');
                }
            }
        } catch (\Exception $e) {
            wp_send_json_error('Lỗi tạo SP: ' . $e->getMessage());
        } catch (\Error $e) {
            wp_send_json_error('Lỗi PHP: ' . $e->getMessage());
        }
    }

    if (!$product_id) wp_send_json_error('Missing product_id');

    // Sanitize rows
    $new_rows = [];
    foreach ($rows as $r) {
        $new_rows[] = [
            'tskt_label' => sanitize_text_field($r['tskt_label'] ?? ''),
            'tskt_value' => sanitize_text_field($r['tskt_value'] ?? ''),
        ];
    }

    if ($mode === 'append' && function_exists('get_field')) {
        $existing = get_field('tskt_rows', $product_id) ?: [];
        $new_rows = array_merge($existing, $new_rows);
    }

    // Save via ACF
    if (function_exists('update_field')) {
        update_field('tskt_rows', $new_rows, $product_id);
        $label = $create_new === 'yes' ? '🆕 Tạo mới + ' : '';
        wp_send_json_success([
            'message'    => sprintf('%sĐã import %d dòng TSKT cho "%s"', $label, count($new_rows), get_the_title($product_id)),
            'count'      => count($new_rows),
            'product_id' => $product_id,
            'created'    => $create_new === 'yes',
        ]);
    }

    // Fallback: raw post_meta
    $count = count($new_rows);
    delete_post_meta($product_id, 'tskt_rows');
    update_post_meta($product_id, 'tskt_rows', $count);

    global $wpdb;
    $wpdb->query($wpdb->prepare(
        "DELETE FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key LIKE %s",
        $product_id, 'tskt_rows_%'
    ));

    foreach ($new_rows as $i => $row) {
        update_post_meta($product_id, "tskt_rows_{$i}_tskt_label", $row['tskt_label']);
        update_post_meta($product_id, "tskt_rows_{$i}_tskt_value", $row['tskt_value']);
    }
    update_post_meta($product_id, '_tskt_rows', 'field_tskt_rows');
    for ($i = 0; $i < $count; $i++) {
        update_post_meta($product_id, "_tskt_rows_{$i}_tskt_label", 'field_tskt_label');
        update_post_meta($product_id, "_tskt_rows_{$i}_tskt_value", 'field_tskt_value');
    }

    wp_send_json_success([
        'message'    => sprintf('Đã import %d dòng (raw meta) cho "%s"', $count, get_the_title($product_id)),
        'count'      => $count,
        'product_id' => $product_id,
    ]);
});

// ── AJAX: Bulk import ──
add_action('wp_ajax_tskt_json_bulk_import', function () {
    check_ajax_referer('tskt_json_import_nonce', 'nonce');
    if (!current_user_can('manage_woocommerce')) wp_send_json_error('No permission');

    $items = json_decode(stripslashes($_POST['items'] ?? '[]'), true);
    if (empty($items)) wp_send_json_error('No items');

    $results = [];
    foreach ($items as $item) {
        $pid  = intval($item['product_id'] ?? 0);
        $rows = $item['tskt_rows'] ?? [];
        if (!$pid || empty($rows)) continue;

        $clean = [];
        foreach ($rows as $r) {
            $clean[] = [
                'tskt_label' => sanitize_text_field($r['tskt_label'] ?? ''),
                'tskt_value' => sanitize_text_field($r['tskt_value'] ?? ''),
            ];
        }

        if (function_exists('update_field')) {
            update_field('tskt_rows', $clean, $pid);
        }
        $results[] = get_the_title($pid) . " ← " . count($clean) . " dòng";
    }

    wp_send_json_success(['results' => $results, 'count' => count($results)]);
});

// ── Render page ──
function tskt_json_import_render_page() {
    $products = wc_get_products(['limit' => -1, 'status' => 'publish', 'orderby' => 'title', 'order' => 'ASC']);
    $nonce = wp_create_nonce('tskt_json_import_nonce');
    ?>
    <div class="wrap" id="tskt-json-import-app">
        <h1>⚡ Import TSKT từ JSON</h1>
        <p>Upload file JSON đã export từ site nguồn (dailyxedien) → map sản phẩm → Import.</p>

        <style>
            .tskt-upload-area{background:#fff;border:2px dashed #2271b1;border-radius:12px;padding:40px;text-align:center;margin:20px 0;cursor:pointer;transition:all .2s}
            .tskt-upload-area:hover,.tskt-upload-area.dragover{background:#f0f6fc;border-color:#135e96}
            .tskt-upload-area h2{margin:0 0 8px;color:#2271b1;font-size:20px}
            .tskt-upload-area p{color:#646970;margin:0}
            .tskt-card{background:#fff;border:1px solid #c3c4c7;border-radius:8px;padding:20px;margin:20px 0}
            .tskt-card h3{margin-top:0;color:#1d2327}
            .tskt-mapping-table{width:100%;border-collapse:collapse}
            .tskt-mapping-table th,.tskt-mapping-table td{padding:10px 12px;border-bottom:1px solid #eee;text-align:left;vertical-align:middle}
            .tskt-mapping-table th{background:#f0f0f1;font-weight:600;position:sticky;top:0}
            .tskt-mapping-table tr:hover{background:#f9f9f9}
            .tskt-mapping-table select{min-width:280px}
            .tskt-badge{display:inline-block;padding:2px 8px;border-radius:12px;font-size:11px;font-weight:600}
            .tskt-badge-ok{background:#d4edda;color:#155724}
            .tskt-badge-skip{background:#f8d7da;color:#721c24}
            .tskt-badge-rows{background:#cce5ff;color:#004085}
            .tskt-preview-mini{max-height:120px;overflow-y:auto;font-size:12px;margin-top:6px}
            .tskt-preview-mini table{width:100%;border-collapse:collapse}
            .tskt-preview-mini th,.tskt-preview-mini td{padding:3px 6px;border:1px solid #e5e5e5}
            .tskt-preview-mini th{background:#f5f5f5;width:40%}
            .tskt-result{margin-top:12px;padding:12px;border-radius:6px;display:none}
            .tskt-result.success{background:#d4edda;color:#155724;display:block}
            .tskt-result.error{background:#f8d7da;color:#721c24;display:block}
            #tskt-bulk-log{margin-top:12px;max-height:300px;overflow-y:auto}
            #tskt-bulk-log p{margin:4px 0;padding:6px 10px;background:#f0f6fc;border-radius:4px;font-size:13px}
            .tskt-stats{display:flex;gap:16px;margin:16px 0;flex-wrap:wrap}
            .tskt-stat{background:#f0f6fc;border-radius:8px;padding:12px 20px;text-align:center;min-width:120px}
            .tskt-stat .num{font-size:28px;font-weight:700;color:#2271b1}
            .tskt-stat .label{font-size:12px;color:#646970;margin-top:2px}
            .tskt-actions{display:flex;gap:12px;align-items:center;margin-top:16px;flex-wrap:wrap}
            .hidden{display:none!important}
            .tskt-search-filter{margin:12px 0;display:flex;gap:12px;align-items:center}
            .tskt-search-filter input{padding:6px 12px;border:1px solid #c3c4c7;border-radius:4px;min-width:250px}
        </style>

        <!-- UPLOAD AREA -->
        <div class="tskt-upload-area" id="tskt-drop-zone">
            <h2>📁 Kéo thả file JSON vào đây</h2>
            <p>hoặc <a href="#" id="tskt-browse-link">chọn file từ máy</a></p>
            <input type="file" id="tskt-file-input" accept=".json" style="display:none">
        </div>

        <!-- MAPPING SECTION (hidden until file loaded) -->
        <div class="tskt-card hidden" id="tskt-mapping-section">
            <h3>📋 Mapping sản phẩm</h3>
            <div class="tskt-stats" id="tskt-stats"></div>

            <div class="tskt-search-filter">
                <input type="text" id="tskt-filter" placeholder="🔍 Lọc theo tên sản phẩm...">
                <label><input type="checkbox" id="tskt-auto-match" checked> Tự động match theo slug/SKU</label>
            </div>

            <div style="max-height:500px;overflow-y:auto">
                <table class="tskt-mapping-table">
                    <thead>
                        <tr>
                            <th style="width:30px"><input type="checkbox" id="tskt-check-all" checked></th>
                            <th>SP nguồn (JSON)</th>
                            <th>TSKT</th>
                            <th>→ SP đích (site này)</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody id="tskt-mapping-body"></tbody>
                </table>
            </div>

            <div class="tskt-actions">
                <button class="button button-primary button-hero" id="tskt-import-all-btn" type="button">🚀 Import các mục đã chọn</button>
                <label><input type="radio" name="tskt-import-mode" value="replace" checked> Ghi đè</label>
                <label><input type="radio" name="tskt-import-mode" value="append"> Nối thêm</label>
                <span id="tskt-import-status" style="margin-left:12px;color:#555"></span>
            </div>
            <div id="tskt-bulk-log"></div>
        </div>
    </div>

    <script>
    (function($){
        var nonce = '<?php echo $nonce; ?>';
        var siteProducts = <?php
            $list = [];
            foreach ($products as $p) {
                $existing = function_exists('get_field') ? get_field('tskt_rows', $p->get_id()) : [];
                $list[] = [
                    'id'    => $p->get_id(),
                    'name'  => $p->get_name(),
                    'slug'  => $p->get_slug(),
                    'sku'   => $p->get_sku(),
                    'tskt'  => !empty($existing) ? count($existing) : 0,
                ];
            }
            echo json_encode($list, JSON_UNESCAPED_UNICODE);
        ?>;
        var jsonData = [];

        // Build product options HTML
        var prodOptsHtml = '<option value="">-- Chọn SP đích --</option>';
        prodOptsHtml += '<option value="__NEW__" style="color:#2271b1;font-weight:bold">🆕 Tạo sản phẩm mới</option>';
        siteProducts.forEach(function(p){
            var extra = p.tskt ? ' ✅('+p.tskt+')' : '';
            prodOptsHtml += '<option value="'+p.id+'" data-slug="'+p.slug+'" data-sku="'+p.sku+'">'+p.name + extra + '</option>';
        });

        // ── File upload ──
        var $zone = $('#tskt-drop-zone'), $input = $('#tskt-file-input');

        $('#tskt-browse-link').on('click', function(e){ e.preventDefault(); $input.click(); });
        $input.on('change', function(){ if(this.files[0]) loadFile(this.files[0]); });

        $zone.on('dragover', function(e){ e.preventDefault(); $(this).addClass('dragover'); });
        $zone.on('dragleave drop', function(e){ e.preventDefault(); $(this).removeClass('dragover'); });
        $zone.on('drop', function(e){
            var files = e.originalEvent.dataTransfer.files;
            if(files.length) loadFile(files[0]);
        });

        function loadFile(file){
            if(!file.name.endsWith('.json')){ alert('Vui lòng chọn file .json!'); return; }
            var reader = new FileReader();
            reader.onload = function(e){
                try {
                    jsonData = JSON.parse(e.target.result);
                    if(!Array.isArray(jsonData)){ alert('JSON không hợp lệ (cần là array)'); return; }
                    $zone.html('<h2>✅ '+file.name+'</h2><p>'+jsonData.length+' sản phẩm — <a href="#" id="tskt-reload">chọn file khác</a></p>');
                    $('#tskt-reload').on('click', function(e){ e.preventDefault(); location.reload(); });
                    renderMapping();
                } catch(err){ alert('Lỗi parse JSON: ' + err.message); }
            };
            reader.readAsText(file);
        }

        // ── Auto-match by slug or SKU ──
        function findMatch(item){
            var slug = (item.product_slug||'').toLowerCase();
            var sku  = (item.product_sku||'').toLowerCase();
            for(var i=0;i<siteProducts.length;i++){
                var p = siteProducts[i];
                if(sku && p.sku && p.sku.toLowerCase() === sku) return p.id;
                if(slug && p.slug && p.slug.toLowerCase() === slug) return p.id;
            }
            // Fuzzy: check if product name contains source name
            var name = (item.product_name||'').toLowerCase();
            for(var i=0;i<siteProducts.length;i++){
                var p = siteProducts[i];
                var pname = p.name.toLowerCase();
                if(name && pname && (pname.indexOf(name) !== -1 || name.indexOf(pname) !== -1)) return p.id;
            }
            return '';
        }

        // ── Render mapping table ──
        function renderMapping(){
            var $body = $('#tskt-mapping-body').empty();
            var matched=0, total=jsonData.length;

            jsonData.forEach(function(item, idx){
                var autoId = $('#tskt-auto-match').is(':checked') ? findMatch(item) : '';
                if(autoId) matched++;
                var rowCount = (item.tskt_rows||[]).length;

                var $tr = $('<tr data-idx="'+idx+'">');
                $tr.append('<td><input type="checkbox" class="tskt-row-check" checked></td>');

                // Source info with thumbnail
                var thumbHtml = item.featured_image
                    ? '<img src="'+item.featured_image+'" style="width:50px;height:50px;object-fit:cover;border-radius:4px;margin-right:10px;vertical-align:middle">'
                    : '<span style="display:inline-block;width:50px;height:50px;background:#f0f0f1;border-radius:4px;margin-right:10px;vertical-align:middle;text-align:center;line-height:50px;color:#ccc">📷</span>';
                var catHtml = '';
                if(item.categories && item.categories.length) {
                    catHtml = '<br><small>';
                    item.categories.forEach(function(c){ catHtml += '<span style="background:#e8f0fe;color:#1a73e8;padding:1px 6px;border-radius:8px;font-size:10px;margin-right:4px">'+escHtml(c.name)+'</span>'; });
                    catHtml += '</small>';
                }
                $tr.append('<td style="display:flex;align-items:center">'+thumbHtml+'<div><strong>'+escHtml(item.product_name||'N/A')+'</strong><br><small style="color:#888">slug: '+escHtml(item.product_slug||'-')+' | SKU: '+escHtml(item.product_sku||'-')+'</small>'+catHtml+'</div></td>');
                $tr.append('<td><span class="tskt-badge tskt-badge-rows">'+rowCount+' dòng</span></td>');

                var $sel = $('<select class="tskt-target-product">'+prodOptsHtml+'</select>');
                // Auto-match existing or default to create new
                if(autoId) {
                    $sel.val(autoId);
                } else {
                    $sel.val('__NEW__');
                }
                $tr.append($('<td>').append($sel));

                var status;
                if(autoId) {
                    status = '<span class="tskt-badge tskt-badge-ok">✅ Matched</span>';
                } else {
                    status = '<span class="tskt-badge" style="background:#fff3cd;color:#856404">🆕 Tạo mới</span>';
                }
                $tr.append('<td class="tskt-status-cell">'+status+'</td>');

                $body.append($tr);
            });

            // Init Select2
            $body.find('.tskt-target-product').select2({width:'100%', placeholder:'Tìm kiếm SP...'});

            // Update status on change
            $body.on('change', '.tskt-target-product', function(){
                var $cell = $(this).closest('tr').find('.tskt-status-cell');
                var val = $(this).val();
                if(val === '__NEW__') $cell.html('<span class="tskt-badge" style="background:#fff3cd;color:#856404">🆕 Tạo mới</span>');
                else if(val) $cell.html('<span class="tskt-badge tskt-badge-ok">✅ Matched</span>');
                else $cell.html('<span class="tskt-badge tskt-badge-skip">⚠️ Bỏ qua</span>');
                updateStats();
            });

            // Stats
            var newCount = total - matched;
            $('#tskt-stats').html(
                '<div class="tskt-stat"><div class="num">'+total+'</div><div class="label">Tổng SP</div></div>'+
                '<div class="tskt-stat"><div class="num" id="stat-matched">'+matched+'</div><div class="label">Đã match</div></div>'+
                '<div class="tskt-stat"><div class="num" id="stat-new" style="color:#856404">'+newCount+'</div><div class="label">🆕 Tạo mới</div></div>'
            );

            $('#tskt-mapping-section').removeClass('hidden');
        }

        function updateStats(){
            var matched=0, newCount=0;
            $('#tskt-mapping-body tr').each(function(){
                var val = $(this).find('.tskt-target-product').val();
                if(val === '__NEW__') newCount++;
                else if(val) matched++;
            });
            $('#stat-matched').text(matched);
            $('#stat-new').text(newCount);
        }

        // ── Check all ──
        $('#tskt-check-all').on('change', function(){
            $('.tskt-row-check').prop('checked', $(this).is(':checked'));
        });

        // ── Filter ──
        $('#tskt-filter').on('input', function(){
            var q = $(this).val().toLowerCase();
            $('#tskt-mapping-body tr').each(function(){
                var name = $(this).find('td:eq(1)').text().toLowerCase();
                $(this).toggle(name.indexOf(q) !== -1);
            });
        });

        // ── Import all checked ──
        $('#tskt-import-all-btn').on('click', function(){
            var btn = $(this), $log = $('#tskt-bulk-log').empty(), $status = $('#tskt-import-status');
            var mode = $('input[name=tskt-import-mode]:checked').val();

            // Collect checked items (mapped OR create new)
            var items = [];
            $('#tskt-mapping-body tr').each(function(){
                if(!$(this).find('.tskt-row-check').is(':checked')) return;
                var idx = $(this).data('idx');
                var val = $(this).find('.tskt-target-product').val();
                if(!val) return; // skip empty (bỏ qua)
                var item = {
                    tskt_rows: jsonData[idx].tskt_rows,
                    source_name: jsonData[idx].product_name,
                    source_slug: jsonData[idx].product_slug || '',
                    source_sku: jsonData[idx].product_sku || '',
                    source_featured_image: jsonData[idx].featured_image || '',
                    source_gallery: jsonData[idx].gallery_images || [],
                    source_categories: jsonData[idx].categories || [],
                    source_price: jsonData[idx].price || '',
                    source_sale_price: jsonData[idx].sale_price || '',
                    source_short_desc: jsonData[idx].short_description || ''
                };
                if(val === '__NEW__') {
                    item.product_id = 0;
                    item.create_new = 'yes';
                } else {
                    item.product_id = parseInt(val);
                    item.create_new = 'no';
                }
                items.push(item);
            });

            if(!items.length){ $log.html('<p style="color:red">⚠️ Không có mục nào được chọn!</p>'); return; }

            btn.prop('disabled',true).text('Đang import '+items.length+' SP...');
            $status.text('');

            // Import one by one for progress feedback
            var done=0, errors=0;
            function importNext(){
                if(done >= items.length){
                    btn.prop('disabled',false).text('🚀 Import các mục đã chọn');
                    $log.append('<p><strong>🎉 Hoàn tất! '+done+' SP, '+errors+' lỗi</strong></p>');
                    $status.css('color','green').text('✅ Xong!');
                    return;
                }
                var item = items[done];
                $.post(ajaxurl, {
                    action: 'tskt_json_do_import',
                    nonce: nonce,
                    product_id: item.product_id,
                    create_new: item.create_new,
                    source_name: item.source_name,
                    source_slug: item.source_slug,
                    source_sku: item.source_sku,
                    source_featured_image: item.source_featured_image,
                    source_gallery: JSON.stringify(item.source_gallery),
                    source_categories: JSON.stringify(item.source_categories),
                    source_price: item.source_price,
                    source_sale_price: item.source_sale_price,
                    source_short_desc: item.source_short_desc,
                    tskt_rows: JSON.stringify(item.tskt_rows),
                    mode: mode
                }, function(res){
                    done++;
                    if(res.success){
                        $log.append('<p>✅ '+escHtml(item.source_name)+' → '+res.data.message+'</p>');
                    } else {
                        errors++;
                        var errMsg = (res && res.data) ? (typeof res.data === 'string' ? res.data : JSON.stringify(res.data)) : 'Lỗi không xác định (server trả về không hợp lệ)';
                        $log.append('<p style="color:red">❌ '+escHtml(item.source_name)+': '+errMsg+'</p>');
                    }
                    $status.text(done+'/'+items.length);
                    $log.scrollTop($log[0].scrollHeight);
                    importNext();
                }).fail(function(){
                    done++; errors++;
                    $log.append('<p style="color:red">❌ '+escHtml(item.source_name)+': Request failed</p>');
                    importNext();
                });
            }
            importNext();
        });

        function escHtml(s){ return $('<span>').text(s).html(); }

    })(jQuery);
    </script>
    <?php
}
