/**
 * Geocode CSV - Tự động lấy lat/lng từ địa chỉ
 * Dùng Nominatim (OpenStreetMap) - miễn phí, không cần API key
 * Giới hạn: 1 request/giây (tuân thủ ToS)
 *
 * Chạy: node geocode-csv.js
 */

const fs   = require('fs');
const path = require('path');
const https = require('https');

const INPUT_FILE  = path.join(__dirname, 'data-import-cua-hang.csv');
const OUTPUT_FILE = path.join(__dirname, 'data-import-cua-hang-geo.csv');

// ────────────────────────────────────────────────────────────
// Đọc CSV → array of objects
// ────────────────────────────────────────────────────────────
function parseCSV(content) {
    const lines = content.split('\n').filter(l => l.trim());
    const headers = parseCSVLine(lines[0]);
    return {
        headers,
        rows: lines.slice(1).map(line => {
            const vals = parseCSVLine(line);
            const obj = {};
            headers.forEach((h, i) => obj[h] = (vals[i] || '').trim());
            return obj;
        })
    };
}

function parseCSVLine(line) {
    const result = [];
    let cur = '', inQ = false;
    for (let i = 0; i < line.length; i++) {
        const c = line[i];
        if (c === '"') { inQ = !inQ; continue; }
        if (c === ',' && !inQ) { result.push(cur); cur = ''; continue; }
        cur += c;
    }
    result.push(cur);
    return result;
}

function toCSVLine(headers, obj) {
    return headers.map(h => {
        const v = obj[h] || '';
        return v.includes(',') || v.includes('"') || v.includes('\n')
            ? `"${v.replace(/"/g, '""')}"` : v;
    }).join(',');
}

// ────────────────────────────────────────────────────────────
// Geocode 1 địa chỉ qua Nominatim
// ────────────────────────────────────────────────────────────
function geocode(address) {
    return new Promise((resolve) => {
        const query  = encodeURIComponent(address + ', Việt Nam');
        const url    = `https://nominatim.openstreetmap.org/search?q=${query}&format=json&limit=1&accept-language=vi`;
        const opts   = {
            headers: { 'User-Agent': 'Bluera-Geocoder/1.0 (dailyxedien.vn)' }
        };

        https.get(url, opts, (res) => {
            let data = '';
            res.on('data', d => data += d);
            res.on('end', () => {
                try {
                    const json = JSON.parse(data);
                    if (json.length > 0) {
                        resolve({ lat: json[0].lat, lng: json[0].lon });
                    } else {
                        resolve(null);
                    }
                } catch (e) {
                    resolve(null);
                }
            });
        }).on('error', () => resolve(null));
    });
}

function sleep(ms) {
    return new Promise(r => setTimeout(r, ms));
}

// ────────────────────────────────────────────────────────────
// Main
// ────────────────────────────────────────────────────────────
async function main() {
    console.log('📂 Đọc file:', INPUT_FILE);
    const content = fs.readFileSync(INPUT_FILE, 'utf8').replace(/^\uFEFF/, '');
    const { headers, rows } = parseCSV(content);

    console.log(`📋 Tổng: ${rows.length} cửa hàng\n`);

    let success = 0, failed = 0;

    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const name    = row['ten_cua_hang'];
        const address = row['dia_chi'];
        const tinh    = row['tinh_thanh'];

        // Bỏ qua nếu đã có tọa độ
        if (row['lat'] && row['lng']) {
            console.log(`[${i+1}/${rows.length}] ⏭  Bỏ qua (đã có tọa độ): ${name}`);
            continue;
        }

        // Thử địa chỉ đầy đủ trước, fallback về tỉnh nếu không tìm thấy
        const queries = [
            address,
            `${address}, ${tinh}`,
            tinh
        ].filter(Boolean);

        let geo = null;
        for (const q of queries) {
            geo = await geocode(q);
            if (geo) break;
            await sleep(1100); // Rate limit
        }

        if (geo) {
            row['lat'] = geo.lat;
            row['lng'] = geo.lng;
            console.log(`[${i+1}/${rows.length}] ✅ ${name}`);
            console.log(`         lat: ${geo.lat}, lng: ${geo.lng}`);
            success++;
        } else {
            console.log(`[${i+1}/${rows.length}] ❌ Không tìm được: ${name} | ${address}`);
            failed++;
        }

        // Nominatim rate limit: 1 req/sec
        await sleep(1100);
    }

    // Ghi file output
    const lines = [
        '\uFEFF' + headers.join(','), // BOM for Excel
        ...rows.map(r => toCSVLine(headers, r))
    ];
    fs.writeFileSync(OUTPUT_FILE, lines.join('\n'), 'utf8');

    console.log('\n════════════════════════════════════');
    console.log(`✅ Thành công : ${success}`);
    console.log(`❌ Thất bại  : ${failed}`);
    console.log(`📁 File output: ${OUTPUT_FILE}`);
    console.log('════════════════════════════════════');
}

main().catch(console.error);
