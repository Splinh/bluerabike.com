# Babyshop Theme - Project Documentation

## Quick Reference

### Build Commands

```bash
cd d:\laragon\www\babyshop\src\wp-content\themes\spl
npm run build      # Build production
npm run dev        # Development mode
```

---

## Directory Structure

```
spl/
├── assets/           # Built CSS/JS files
├── inc/              # PHP classes & settings
├── parts/            # Template parts
├── resources/        # Source SCSS/JS files
├── templates/        # Page templates
└── woocommerce/      # WooCommerce overrides
```

---

## Core Files

### Entry Points

| File                          | Purpose                |
| ----------------------------- | ---------------------- |
| `resources/styles/index.scss` | Main frontend styles   |
| `resources/scripts/index.js`  | Main frontend JS       |
| `vite.config.js`              | Build configuration    |
| `functions.php`               | Theme setup & autoload |

### Settings & Configuration

| File                               | Purpose                             |
| ---------------------------------- | ----------------------------------- |
| `inc/setting.php`                  | Theme Customizer settings           |
| `inc/acf-popup-settings.php`       | Popup & Seasonal effects ACF fields |
| `inc/acf-shop-filter-settings.php` | Shop filter ACF settings            |

---

## Features

### 1. Seasonal Effects (Hoa Mai/Đào/Tuyết)

**Files:**

- `inc/acf-popup-settings.php` - ACF fields (dòng 124-155)
- `parts/seasonal-effect.php` - Global template
- `resources/scripts/seasonal-effect.js` - Animation logic
- `resources/styles/seasonal-effect.scss` - Petal/snow styles

**Admin:** Popup Settings > Advanced Settings  
**Options:** Hoa Mai (vàng), Hoa Đào (hồng), Tuyết, Không có

---

### 2. Promotional Popup

**Files:**

- `inc/acf-popup-settings.php` - ACF fields
- `inc/ajax-popup-handler.php` - Form submission handler
- `parts/popup-template.php` - Popup HTML template
- `resources/scripts/popup-promo.js` - Popup JS
- `resources/styles/popup-promo.scss` - Popup styles

**Admin:** Popup Settings

---

### 3. Cookie Consent Banner

**Files:**

- `parts/blocks/cookie-consent.php` - Template
- `resources/scripts/cookie-consent.js` - JS handler
- Styles in `index.scss`

---

### 4. Shop Filter

**Files:**

- `inc/ShopFilter.php` - Filter logic
- `inc/acf-shop-filter-settings.php` - ACF settings
- `sidebar-shop.php` - Shop sidebar template

---

### 5. WooCommerce Customizations

**Directory:** `woocommerce/`

- `single-product/product-gallery.php` - Gallery + video support
- `single-product/tabs/tabs.php` - Custom tabs + video popup
- `global/quantity-input.php` - Quantity input style
- `archive-product.php` - Shop archive

**Styles:** `resources/styles/components/woocommerce.scss`  
**JS:** `resources/scripts/components/woocommerce.js`

---

### 5b. Video Tabs Plugin (Standalone)

**Location:** `plugin-chuc-nang/tabs-video/`

Plugin độc lập để cài cho các web khác (Flatsome, etc.)

**Files:**

- `product-video-tabs.php` - Main plugin
- `assets/css/video-tabs.css` - Styles
- `assets/js/video-tabs.js` - Popup JS
- `templates/video-tab-content.php` - Video grid
- `templates/video-popup-modal.php` - Modal

**Features:** YouTube + TikTok với ACF fields tự động đăng ký

---

### 6. Page Templates

| Template          | File                                           |
| ----------------- | ---------------------------------------------- |
| Homepage          | `templates/template-page-home.php`             |
| Blog              | `templates/template-page-blog.php`             |
| About             | `templates/template-page-about-us.php`         |
| Contact           | `templates/template-page-contact-us.php`       |
| Featured Products | `templates/template-page-featured-product.php` |
| Sale Products     | `templates/template-page-sale-product.php`     |

---

### 7. Home Sections (ACF Flexible Content)

**Directory:** `parts/home/`

- `home_broadcast_banner.php` - Banner slider
- `home_product_tabs.php` - Product tabs
- `home_latest_news.php` - Latest posts
- `home_map.php` - Google Map
- ... and more

---

### 8. Header & Footer

**Header:** `inc/Core/Frontend/Hook.php`

- `_masthead_header()` - Main header
- `_masthead_bottom_header()` - Navigation

**Footer:**

- `_construct_footer_columns()` - Footer widgets
- `_construct_footer_credit()` - Copyright

---

### 9. Swiper Sliders

**Files:**

- `resources/scripts/components/swiper.js`
- `resources/styles/components/swiper.scss`

---

## Hook Class Reference

**File:** `inc/Core/Frontend/Hook.php`

| Method                             | Hook               | Priority |
| ---------------------------------- | ------------------ | -------- |
| `enqueue_popup_promo_assets()`     | wp_enqueue_scripts | 98       |
| `enqueue_seasonal_effect_assets()` | wp_enqueue_scripts | 97       |
| `popup_promo_output()`             | wp_footer          | 100      |
| `seasonal_effect_output()`         | wp_footer          | 5        |
| `cookie_consent_output()`          | wp_footer          | 101      |

---

## Adding New Features

### New SCSS/JS Entry

1. Add to `vite.config.js` → `sassFiles[]` / `jsFiles[]`
2. Create file in `resources/styles/` or `resources/scripts/`
3. Enqueue in `Hook.php` or via action hook
4. Run `npm run build`

### New ACF Options Page

1. Create in `inc/acf-*.php`
2. Use `acf_add_options_page()` and `acf_add_local_field_group()`
3. Include in `functions.php` if needed

---

## Polylang Support

- Use `\HD_Helper::pll_text('VI text', 'EN text')` for translations
- Suffix fields with `_en` for English: `footer_menu1_en`
