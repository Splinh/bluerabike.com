=== DevVN - Trang trí Tết Việt Nam ===
Contributors: levantoan
Donate link: https://levantoan.com/donate/
Tags: trang trí Tết, Tet holiday, hoa đào, hoa mai, câu đối
Requires at least: 4.3
Tested up to: 6.9
Stable tag: 1.0.10
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0

Trang trí Tết cho website của bạn. Có hoa mai, hoa đào, câu đối 2 bên và pháo hoa bắn cực đẹp

== Description ==

Trang trí Tết cho website của bạn. Có hoa mai, hoa đào, câu đối 2 bên và pháo hoa bắn cực đẹp

* Chọn kiểu hiển thị câu đối 2 bên: Có 6 kiểu và tương lai còn nhiều hơn nữa. Có thể tắt
* Chọn kiểu chân trang: Có 2 kiểu và có thể ẩn. Tương lai còn nhiều hơn nữa
* Bật/Tắt bắn pháo hoa
* Bật/Tắt âm thanh khi bắn pháo hoa
* Bật/Tắt hiệu ứng hoa đào, hoa mai bay trên web
* Có tuỳ chọn kích thước màn hình để ẩn các trang trí đi
Và còn nhiều option khác. Các bạn sử dụng và khám phá tiếp nhé


== Screenshots ==

1. screenshot-1.png
2. screenshot-2.png

== Changelog ==

= 1.0.10 =

* Cập nhật câu đối 2 bên cho Tết 2026

= 1.0.9 =

* SECURITY: Thêm callback sanitization cho register_setting() để validate và sanitize tất cả các trường dữ liệu
* SECURITY: Thêm re-sanitization khi load dữ liệu từ database để ngăn chặn XSS attacks
* SECURITY: Tăng cường input sanitization cho tất cả các trường (content, URLs, text fields, numeric values)
* SECURITY: Thêm capability check (manage_options) cho trang settings
* CODE: Sửa lỗi WordPress Coding Standards (function prefixes, hook names, input sanitization)
* CODE: Cải thiện code quality và tuân thủ WordPress Plugin Check requirements
* CODE: Thêm proper escaping và sanitization xuyên suốt plugin (esc_url, esc_attr, esc_html_e, esc_js, absint)
* CODE: Sử dụng absint() thay vì intval() cho tất cả numeric values
* CODE: Cải thiện formatting và spacing theo WordPress Coding Standards
* CODE: Thay thế tất cả _e() bằng esc_html_e() để đảm bảo output được escape đúng cách
* CODE: Thêm License và License URI vào plugin header để tuân thủ WordPress Plugin Check
* CODE: Giảm số lượng tags từ 7 xuống 5 để tuân thủ yêu cầu của WordPress Plugin Directory

= 1.0.8 =

* Thêm mục up ảnh hoa mai, hoa đào bất kỳ (Theo kích thước của ảnh nên chú ý up ảnh nhỏ nhé)

= 1.0.7 =

* sửa lỗi mất ảnh bên trái khi tự up ảnh

= 1.0.6 =

* Cập nhật tăng cường bảo mật

= 1.0.5 =

* Thêm option tắt câu đối 2 bên
* Thêm option tốc độ bắn pháo hoa

= 1.0.4 =

* Thêm câu đối 2 bên cho Giáp Thìn 2024
* Thêm kiểu bắn pháo hoa mới học theo topzone. Có thể chỉnh được màu pháo hoa
* Thêm ô nhập thời gian bắn pháo hoa. mặc định 30s

= 1.0.3 =

* Thêm câu đối 2 bên cho năm Mão

= 1.0.2 =

* Thêm tuỳ chọn cả hoa đào và hoa mai rơi cùng lúc

= 1.0.1 =

* Cho phép tự upload ảnh câu đối 2 bên
* Cho phép chỉnh số pháo hoa bắn cùng lúc. Mặc định là 5
* Thêm style câu đối con hổ cho năm 2022 (Nhâm Dần)

= 1.0 =

* Update new plugin