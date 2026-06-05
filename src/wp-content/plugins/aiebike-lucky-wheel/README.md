# AIEbike Vòng Quay May Mắn

Plugin mở rộng cho **WooCommerce Lucky Wheel** - Thêm tính năng xác thực số khung xe, trang landing page tùy chỉnh, và giao diện vòng quay với icons.

## 📋 Mô Tả

Plugin này được thiết kế để:

- Giới hạn mỗi xe chỉ được quay **1 lần** thông qua xác thực số khung/động cơ
- Tự động tạo email cho khách hàng không có email (từ SĐT)
- Tạo trang landing page vòng quay hoàn chỉnh
- Hiển thị danh sách người trúng thưởng
- Tùy chỉnh giao diện wheel với icons

## ⚙️ Yêu Cầu

| Yêu cầu                     | Phiên bản   |
| --------------------------- | ----------- |
| WordPress                   | 5.0+        |
| PHP                         | 7.2+        |
| WooCommerce                 | 3.0+        |
| **WooCommerce Lucky Wheel** | ✅ Bắt buộc |

> ⚠️ **Lưu ý**: Plugin WooCommerce Lucky Wheel phải được cài đặt và kích hoạt trước.

## 🚀 Cài Đặt

### Cách 1: Upload qua WordPress Admin

1. Tải folder `aiebike-lucky-wheel` về máy
2. Nén thành file `.zip`
3. Vào **WordPress Admin → Plugins → Add New → Upload Plugin**
4. Chọn file `.zip` và cài đặt
5. Kích hoạt plugin

### Cách 2: Upload qua FTP

1. Upload folder `aiebike-lucky-wheel` vào `/wp-content/plugins/`
2. Vào **WordPress Admin → Plugins**
3. Tìm "AIEbike Vòng Quay May Mắn" và kích hoạt

## 📖 Hướng Dẫn Sử Dụng

### Shortcodes

#### 1. Trang Vòng Quay Hoàn Chỉnh

```
[aiebike_lucky_wheel_page]
```

Hiển thị trang vòng quay đầy đủ với:

- Header (tiêu đề, subtitle, giá trị giải)
- Vòng quay WooCommerce Lucky Wheel
- Tabs: Danh sách trúng thưởng | Giải thưởng | Thể lệ

**Tham số tùy chỉnh:**

```
[aiebike_lucky_wheel_page title="VÒNG QUAY MAY MẮN" subtitle="Cơ hội nhận quà" prize_text="Giảm ngay 500K"]
```

#### 2. Danh Sách Trúng Thưởng

```
[aiebike_winners_list limit="50" show_phone="yes" show_date="yes"]
```

**Tham số:**
| Tham số | Mặc định | Mô tả |
|---------|----------|-------|
| `limit` | 50 | Số lượng người hiển thị |
| `show_phone` | yes | Hiển thị SĐT (đã ẩn bớt) |
| `show_date` | yes | Hiển thị ngày quay |

### Trang Admin

#### Menu: Vòng Quay

- **Trang chủ**: Hướng dẫn + Thống kê nhanh
- **Số khung đã quay**: Danh sách tất cả số khung đã sử dụng
- **Cài đặt**: Tùy chỉnh plugin

### Cài Đặt

Vào **Vòng Quay → Cài đặt** để tùy chỉnh:

| Cài đặt               | Mô tả                                        |
| --------------------- | -------------------------------------------- |
| Bật xác thực số khung | Bật/tắt yêu cầu nhập số khung                |
| Nhãn field số khung   | Text placeholder cho field                   |
| Domain email tự động  | VD: aiebike.store → 0901234567@aiebike.store |
| Số ký tự ẩn SĐT       | VD: 5 → 0962**\***94                         |

## 🎯 Tính Năng Chi Tiết

### 1. Xác Thực Số Khung/Động Cơ

- Field bắt buộc với viền cam nổi bật
- Kiểm tra AJAX realtime trước khi quay
- Thông báo lỗi nếu số khung đã sử dụng
- Lưu kết quả vào database riêng

### 2. Auto-Generate Email

- Khách không có email → Tự động tạo từ SĐT
- Format: `[SĐT]@[domain]`
- VD: `0901234567@aiebike.store`

### 3. Wheel với Icons

- Tiêu đề giải thưởng phía ngoài (gần mép)
- Icon emoji phía trong (gần tâm)
- Icon tự động theo nội dung:
  - 💰 Giảm giá
  - 🎫 Voucher
  - 💎 300K+
  - 🎁 Free/Miễn phí
  - 🛵 Xe
  - 🍀 May mắn
  - 😢 Not Lucky

### 4. ACF Fields (nếu có ACF Pro)

Tự động đăng ký các field cho trang:

- Header: Tiêu đề, subtitle, giá trị giải, hình nền
- Giải thưởng: Repeater với hình + giá trị + mô tả
- Thể lệ: WYSIWYG editor + thông tin liên hệ

## 📁 Cấu Trúc Plugin

```
aiebike-lucky-wheel/
├── aiebike-lucky-wheel.php     # Main plugin file
├── README.md                    # Tài liệu
├── includes/
│   ├── class-admin.php         # Trang admin
│   ├── class-frame-validation.php  # Xác thực số khung
│   ├── class-frontend.php      # Frontend scripts
│   ├── class-shortcode.php     # Shortcodes
│   └── class-acf-fields.php    # ACF fields
├── templates/
│   └── page-lucky-wheel.php    # Template trang
├── assets/
│   ├── css/
│   │   └── frontend.css        # Styles
│   └── js/
│       ├── frontend.js         # Xác thực + auto email
│       └── wheel-custom.js     # Vẽ wheel với icons
└── languages/                   # Translations (nếu có)
```

## 🔧 Hooks & Filters

### Actions

```php
// Sau khi xác thực số khung thành công
do_action('aiebike_lw_frame_validated', $frame_number, $customer_data);

// Sau khi lưu số khung
do_action('aiebike_lw_frame_saved', $frame_number, $prize_won);
```

### Filters

```php
// Tùy chỉnh icon cho label
add_filter('aiebike_lw_icon_map', function($icons) {
    $icons['custom'] = '🚀';
    return $icons;
});

// Tùy chỉnh domain email tự động
add_filter('aiebike_lw_auto_email_domain', function($domain) {
    return 'mydomain.com';
});
```

## ❓ FAQ

### Q: Plugin có hoạt động độc lập không?

A: Không, plugin yêu cầu **WooCommerce Lucky Wheel** phải được cài đặt.

### Q: Có thể sử dụng trên subdomain không?

A: Có, plugin hoạt động độc lập với theme.

### Q: Dữ liệu số khung lưu ở đâu?

A: Bảng `wp_aiebike_frame_numbers` trong database.

### Q: Làm sao để tùy chỉnh icon trên wheel?

A: Sửa file `assets/js/wheel-custom.js`, phần `iconMap`.

## 📝 Changelog

### 1.0.0 (2026-01-22)

- 🎉 Phiên bản đầu tiên
- ✅ Xác thực số khung/động cơ xe
- ✅ Auto-generate email từ SĐT
- ✅ Shortcode trang vòng quay
- ✅ Shortcode danh sách trúng thưởng
- ✅ Trang admin quản lý
- ✅ ACF fields integration
- ✅ Wheel với icons

## 👨‍💻 Tác Giả

**AI Ebike Team**

- Website: [aiebike.vn](https://aiebike.vn)

## 📄 License

GPL v2 or later
