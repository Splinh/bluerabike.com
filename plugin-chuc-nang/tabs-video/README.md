# Product Video Tabs

WordPress plugin để thêm tab Video (YouTube & TikTok) vào trang chi tiết sản phẩm WooCommerce.

## Yêu cầu

- WordPress 6.0+
- WooCommerce 7.0+
- Advanced Custom Fields (ACF) Pro hoặc Free

## Tương thích

- ✅ Flatsome theme
- ✅ Flavor theme
- ✅ Các theme WooCommerce khác

## Cài đặt

1. Copy thư mục `tabs-video` vào `wp-content/plugins/`
2. Đổi tên thành `product-video-tabs` (hoặc giữ nguyên)
3. Vào WordPress Admin > Plugins > Activate

## Sử dụng

### Video trong Gallery (hiển thị đầu tiên)

1. Vào trang Edit Product
2. Tìm box **"🎬 Video Gallery"** ở sidebar phải
3. Dán YouTube hoặc TikTok URL
4. Save → Video sẽ hiển thị ĐẦU TIÊN trong gallery

### Video trong Tab

1. Vào trang Edit Product
2. Scroll xuống field group **"Video Tab - Danh sách video"**
3. Thêm nhiều YouTube/TikTok URLs
4. Save → Tab "Video" sẽ xuất hiện

## Chức năng

- ✅ **Video gallery**: Hiển thị ĐẦU TIÊN trong thumbnail gallery
- ✅ **Video tab**: Danh sách nhiều video trong tab riêng
- ✅ YouTube + TikTok support
- ✅ Popup player khi click
- ✅ Tương thích Flatsome, Flavor, WooCommerce default
- ✅ ACF fields riêng biệt (gallery ≠ tab)

## Cấu trúc file

```
tabs-video/
├── product-video-tabs.php   # Main plugin file
├── assets/
│   ├── css/
│   │   └── video-tabs.css   # Styles
│   └── js/
│       └── video-tabs.js    # Popup JS
├── templates/
│   ├── video-tab-content.php      # Video grid (trong tab)
│   ├── video-gallery-section.php  # Video thumbnails (dưới gallery)
│   └── video-popup-modal.php      # Popup modal
└── README.md
```

## URL Formats hỗ trợ

**YouTube:**

- `https://www.youtube.com/watch?v=VIDEO_ID`
- `https://youtu.be/VIDEO_ID`
- `https://www.youtube.com/embed/VIDEO_ID`
- `https://www.youtube.com/shorts/VIDEO_ID`

**TikTok:**

- `https://www.tiktok.com/@username/video/VIDEO_ID`

## Tùy chỉnh CSS

Override styles bằng cách thêm CSS với prefix `.pvt-` vào theme của bạn.

## Changelog

### 1.0.1

- Fix: Lỗi "Call to a member function get_id() on string" trên Flatsome theme
- Fix: ACF fields không hiển thị - thêm fallback hooks
- Improve: Tương thích tốt hơn với nhiều theme

### 1.0.0

- Initial release

## Author

Gaudev - https://bluera.vn
