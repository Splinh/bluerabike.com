# Hướng dẫn: Bỏ `/src` khỏi URL WordPress (Bedrock)

## Tổng quan

Khi sử dụng cấu trúc Bedrock, WordPress core nằm trong folder `src/`. Mặc định URL sẽ là:

- `example.com/src/wp-admin/`
- `example.com/src/wp-content/...`

Hướng dẫn này giúp "làm sạch" URL thành:

- `example.com/wp-admin/`
- `example.com/wp-content/...`

> **Lưu ý**: Files vẫn nằm trong `src/`, chỉ URL được rewrite.

---

## Bước 1: Cấu hình `.htaccess` (Root)

Thêm vào file `.htaccess` ở thư mục gốc:

```apache
# BEGIN WordPress Proxy (Allow removing /src from WP_SITEURL)
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
RewriteBase /

# Proxy wp-login.php first (handle login POST)
RewriteRule ^wp-login\.php$ /src/wp-login.php [L,PT,QSA]

# Proxy other wp-*.php files
RewriteCond %{REQUEST_URI} ^/wp-[^/]+\.php$
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)$ /src/$1 [L,PT,QSA]

# Proxy wp-admin to src folder
RewriteCond %{REQUEST_URI} ^/wp-admin
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ /src/$1 [L,PT,QSA]

# Proxy wp-includes to src folder
RewriteCond %{REQUEST_URI} ^/wp-includes
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)$ /src/$1 [L,PT,QSA]

# Proxy wp-content to src folder
RewriteCond %{REQUEST_URI} ^/wp-content
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)$ /src/$1 [L,PT,QSA]

# Standard WordPress routing
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
# END WordPress
```

---

## Bước 2: Cấu hình `.env`

```env
WP_HOME=https://example.com
WP_SITEURL="${WP_HOME}"
```

> Không thêm `/src` vào `WP_SITEURL`

---

## Bước 3: Cấu hình `config/application.php`

Thêm sau khi define `WP_HOME` và `WP_SITEURL`:

```php
/**
 * Cookie paths - ensure cookies work at root "/" even when WP is in /src
 */
Config::define('COOKIEPATH', '/');
Config::define('SITECOOKIEPATH', '/');
Config::define('ADMIN_COOKIE_PATH', '/');

/**
 * Content paths - serve wp-content from root URL while files remain in /src
 */
Config::define('WP_CONTENT_DIR', $root_dir . '/src/wp-content');
Config::define('WP_CONTENT_URL', env('WP_HOME') . '/wp-content');
Config::define('WP_PLUGIN_DIR', $root_dir . '/src/wp-content/plugins');
Config::define('WP_PLUGIN_URL', env('WP_HOME') . '/wp-content/plugins');
```

---

## Bước 4: Cập nhật Database

Chạy SQL trong phpMyAdmin (thay `wp_` bằng prefix của bạn):

```sql
UPDATE wp_options SET option_value = 'https://example.com' WHERE option_name = 'siteurl';
UPDATE wp_options SET option_value = 'https://example.com' WHERE option_name = 'home';
```

---

## Bước 5: Xóa cache

1. Xóa cookies browser cho domain
2. Xóa cache plugin (nếu có LiteSpeed, FlyingPress, etc.)
3. Đăng nhập lại

---

## Kiểm tra

| URL                                        | Kết quả mong đợi        |
| ------------------------------------------ | ----------------------- |
| `example.com/wp-admin/`                    | Vào được trang quản trị |
| `example.com/wp-login.php`                 | Đăng nhập thành công    |
| `example.com/wp-content/uploads/image.jpg` | Load được ảnh           |

---

## Troubleshooting

### Lỗi CSS/JS không load

- Kiểm tra `.htaccess` đã có rewrite rule cho `/wp-includes`
- Kiểm tra `WP_CONTENT_URL` đã đúng

### Đăng nhập không được (redirect loop)

- Xóa cookies cũ
- Kiểm tra `COOKIEPATH` đã set là `/`
- Kiểm tra database `siteurl` và `home` đã cập nhật

### Vẫn thấy `/src` trong URL

- Cập nhật database (Bước 4)
- Xóa cache browser
