<?php

/**
 * Custom Shop Filter - Replaces YITH Ajax Product Filter
 *
 * @author Gaudev
 */

namespace HD\Utilities;

defined('ABSPATH') || exit;

class ShopFilter
{
    private static ?ShopFilter $instance = null;

    public static function get_instance(): ShopFilter
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('wp_ajax_spl_shop_filter', [$this, 'filter_products']);
        add_action('wp_ajax_nopriv_spl_shop_filter', [$this, 'filter_products']);
        // Use priority 99 to run after WooCommerce enqueues hdwc-js at 98
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets'], 99);
    }

    /**
     * Enqueue filter assets - Localize script data only
     * JS and CSS are bundled via Vite in woocommerce.js/scss
     */
    public function enqueue_assets(): void
    {
        if (!is_shop() && !is_product_category() && !is_product_tag()) {
            return;
        }

        // Add inline script with filter config after hdwc-js
        $config = [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('spl_shop_filter_nonce'),
        ];

        wp_add_inline_script(
            'hdwc-js',
            'window.splShopFilter = ' . wp_json_encode($config) . ';',
            'before'
        );
    }

    /**
     * AJAX handler for filtering products
     */
    public function filter_products(): void
    {
        check_ajax_referer('spl_shop_filter_nonce', 'nonce');

        $min_price    = isset($_POST['min_price']) ? floatval($_POST['min_price']) : 0;
        $max_price    = isset($_POST['max_price']) ? floatval($_POST['max_price']) : 0;
        $category     = isset($_POST['category']) ? absint($_POST['category']) : 0;
        $attributes   = isset($_POST['attributes']) ? (array) $_POST['attributes'] : [];
        $orderby      = isset($_POST['orderby']) ? sanitize_text_field($_POST['orderby']) : 'menu_order';
        $paged        = isset($_POST['paged']) ? absint($_POST['paged']) : 1;
        $per_page     = isset($_POST['per_page']) ? absint($_POST['per_page']) : wc_get_default_products_per_row() * wc_get_default_product_rows_per_page();

        // Build query args
        $args = [
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => $per_page,
            'paged'          => $paged,
        ];

        // Tax query
        $tax_query = ['relation' => 'AND'];

        // Category filter
        if ($category > 0) {
            $tax_query[] = [
                'taxonomy' => 'product_cat',
                'field'    => 'term_id',
                'terms'    => $category,
            ];
        }

        // Attribute filters
        if (!empty($attributes)) {
            foreach ($attributes as $attr_name => $attr_values) {
                if (empty($attr_values)) {
                    continue;
                }
                $tax_query[] = [
                    'taxonomy' => 'pa_' . sanitize_title($attr_name),
                    'field'    => 'slug',
                    'terms'    => array_map('sanitize_title', (array) $attr_values),
                    'operator' => 'IN',
                ];
            }
        }

        // Only add tax_query if we have conditions
        if (count($tax_query) > 1) {
            $args['tax_query'] = $tax_query;
        }

        // Price filter using meta query
        if ($min_price > 0 || $max_price > 0) {
            $meta_query = ['relation' => 'AND'];

            if ($min_price > 0) {
                $meta_query[] = [
                    'key'     => '_price',
                    'value'   => $min_price,
                    'compare' => '>=',
                    'type'    => 'NUMERIC',
                ];
            }

            if ($max_price > 0) {
                $meta_query[] = [
                    'key'     => '_price',
                    'value'   => $max_price,
                    'compare' => '<=',
                    'type'    => 'NUMERIC',
                ];
            }

            $args['meta_query'] = $meta_query;
        }

        // Ordering
        $ordering = $this->get_ordering_args($orderby);
        $args['orderby'] = $ordering['orderby'];
        $args['order']   = $ordering['order'];

        if (isset($ordering['meta_key'])) {
            $args['meta_key'] = $ordering['meta_key'];
        }

        $query = new \WP_Query($args);

        ob_start();

        if ($query->have_posts()) {
            woocommerce_product_loop_start();
            while ($query->have_posts()) {
                $query->the_post();
                wc_get_template_part('content', 'product');
            }
            woocommerce_product_loop_end();
        } else {
            echo '<p class="woocommerce-info">' . esc_html__('Không tìm thấy sản phẩm nào phù hợp.', TEXT_DOMAIN) . '</p>';
        }

        $products_html = ob_get_clean();

        // Pagination
        ob_start();
        $this->render_pagination($query, $paged);
        $pagination_html = ob_get_clean();

        wp_reset_postdata();

        wp_send_json_success([
            'products'    => $products_html,
            'pagination'  => $pagination_html,
            'found_posts' => $query->found_posts,
            'max_pages'   => $query->max_num_pages,
        ]);
    }

    /**
     * Get ordering args based on orderby value
     */
    private function get_ordering_args(string $orderby): array
    {
        $args = [
            'orderby' => 'menu_order title',
            'order'   => 'ASC',
        ];

        switch ($orderby) {
            case 'date':
                $args['orderby'] = 'date ID';
                $args['order']   = 'DESC';
                break;

            case 'price':
                $args['orderby']  = 'meta_value_num';
                $args['order']    = 'ASC';
                $args['meta_key'] = '_price';
                break;

            case 'price-desc':
                $args['orderby']  = 'meta_value_num';
                $args['order']    = 'DESC';
                $args['meta_key'] = '_price';
                break;

            case 'popularity':
                $args['orderby']  = 'meta_value_num';
                $args['order']    = 'DESC';
                $args['meta_key'] = 'total_sales';
                break;

            case 'rating':
                $args['orderby']  = 'meta_value_num';
                $args['order']    = 'DESC';
                $args['meta_key'] = '_wc_average_rating';
                break;
        }

        return $args;
    }

    /**
     * Render pagination
     */
    private function render_pagination(\WP_Query $query, int $current_page): void
    {
        if ($query->max_num_pages <= 1) {
            return;
        }

?>
        <nav class="woocommerce-pagination">
            <?php
            echo paginate_links([
                'base'      => '%_%',
                'format'    => '?paged=%#%',
                'current'   => $current_page,
                'total'     => $query->max_num_pages,
                'prev_text' => '&laquo;',
                'next_text' => '&raquo;',
                'type'      => 'list',
            ]);
            ?>
        </nav>
    <?php
    }

    /**
     * Render filter sidebar
     */
    public static function render_filters(): void
    {
        // Get settings from ACF
        $enable_price = get_field('sf_enable_price', 'option') ?? true;
        $clear_text   = get_field('sf_clear_text', 'option') ?: __('Xóa bộ lọc', TEXT_DOMAIN);

        $min_price = 0;
        $max_price = self::get_max_product_price();

        // Get current filters from URL
        $current_min = isset($_GET['min_price']) ? floatval($_GET['min_price']) : $min_price;
        $current_max = isset($_GET['max_price']) ? floatval($_GET['max_price']) : $max_price;

    ?>
        <div class="spl-shop-filter" data-filter-container>
            <?php self::render_category_filter(); ?>

            <?php if ($enable_price) : ?>
                <!-- Price Filter -->
                <div class="filter-section filter-price">
                    <h4 class="filter-title"><?= __('Lọc theo giá', TEXT_DOMAIN) ?></h4>
                    <div class="price-slider-wrapper"
                        data-min="<?= esc_attr($min_price) ?>"
                        data-max="<?= esc_attr($max_price) ?>"
                        data-current-min="<?= esc_attr($current_min) ?>"
                        data-current-max="<?= esc_attr($current_max) ?>">

                        <!-- Display values -->
                        <div class="price-display">
                            <span class="price-value min-value"><?= number_format_i18n($current_min) ?>₫</span>
                            <span class="price-separator">-</span>
                            <span class="price-value max-value"><?= number_format_i18n($current_max) ?>₫</span>
                        </div>

                        <!-- Range slider track -->
                        <div class="price-slider">
                            <div class="slider-track"></div>
                            <div class="slider-range"></div>
                            <input type="range"
                                class="slider-input min-slider"
                                min="<?= esc_attr($min_price) ?>"
                                max="<?= esc_attr($max_price) ?>"
                                value="<?= esc_attr($current_min) ?>"
                                step="10000">
                            <input type="range"
                                class="slider-input max-slider"
                                min="<?= esc_attr($min_price) ?>"
                                max="<?= esc_attr($max_price) ?>"
                                value="<?= esc_attr($current_max) ?>"
                                step="10000">
                        </div>

                        <!-- Hidden inputs for form data -->
                        <input type="hidden" class="min-price" value="<?= esc_attr($current_min) ?>">
                        <input type="hidden" class="max-price" value="<?= esc_attr($current_max) ?>">
                    </div>
                </div>
            <?php endif; ?>

            <?php self::render_attribute_filters(); ?>

            <!-- Clear Filters -->
            <button type="button" class="filter-clear">
                <?= esc_html($clear_text) ?>
            </button>
        </div>
    <?php
    }

    /**
     * Render category filter
     */
    private static function render_category_filter(): void
    {
        // Get top-level categories
        $categories = get_terms([
            'taxonomy'   => 'product_cat',
            'hide_empty' => true,
            'parent'     => 0,
            'exclude'    => get_option('default_product_cat', 0),
        ]);

        if (empty($categories) || is_wp_error($categories)) {
            return;
        }

        // Get current category
        $current_cat = 0;
        if (is_product_category()) {
            $current_cat = get_queried_object_id();
        } elseif (isset($_GET['product_cat'])) {
            $current_cat = absint($_GET['product_cat']);
        }

    ?>
        <div class="filter-section filter-category" data-filter-type="category">
            <h4 class="filter-title"><?= __('Danh mục', TEXT_DOMAIN) ?></h4>
            <ul class="filter-list">
                <?php foreach ($categories as $cat) : ?>
                    <li>
                        <label class="filter-checkbox">
                            <input type="checkbox"
                                name="product_cat"
                                value="<?= esc_attr($cat->term_id) ?>"
                                <?php checked($cat->term_id, $current_cat) ?>>
                            <span class="checkmark"></span>
                            <span class="label-text"><?= esc_html($cat->name) ?></span>
                            <span class="count">(<?= esc_html($cat->count) ?>)</span>
                        </label>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
    }

    /**
     * Render attribute filters
     */
    private static function render_attribute_filters(): void
    {
        // Get enabled attributes from ACF settings
        $enabled_attributes = get_field('sf_enabled_attributes', 'option');
        $show_count         = get_field('sf_show_count', 'option') ?? true;

        // If no attributes selected, show none
        if (empty($enabled_attributes)) {
            return;
        }

        $attribute_taxonomies = wc_get_attribute_taxonomies();

        if (empty($attribute_taxonomies)) {
            return;
        }

        foreach ($attribute_taxonomies as $attribute) {
            // Skip if not enabled in settings
            if (!in_array($attribute->attribute_name, $enabled_attributes, true)) {
                continue;
            }

            $taxonomy = wc_attribute_taxonomy_name($attribute->attribute_name);
            $terms    = get_terms([
                'taxonomy'   => $taxonomy,
                'hide_empty' => true,
            ]);

            if (empty($terms) || is_wp_error($terms)) {
                continue;
            }

            $current_values = isset($_GET['filter_' . $attribute->attribute_name])
                ? explode(',', sanitize_text_field($_GET['filter_' . $attribute->attribute_name]))
                : [];

        ?>
            <div class="filter-section filter-attribute" data-attribute="<?= esc_attr($attribute->attribute_name) ?>">
                <h4 class="filter-title"><?= esc_html($attribute->attribute_label) ?></h4>
                <ul class="filter-list <?= count($terms) > 5 ? 'has-more' : '' ?>">
                    <?php foreach ($terms as $index => $term) : ?>
                        <li class="<?= $index >= 5 ? 'hidden-item' : '' ?>">
                            <label class="filter-checkbox">
                                <input type="checkbox"
                                    name="filter_<?= esc_attr($attribute->attribute_name) ?>[]"
                                    value="<?= esc_attr($term->slug) ?>"
                                    <?php checked(in_array($term->slug, $current_values, true)) ?>>
                                <span class="checkmark"></span>
                                <span class="label-text"><?= esc_html($term->name) ?></span>
                                <?php if ($show_count) : ?>
                                    <span class="count">(<?= esc_html($term->count) ?>)</span>
                                <?php endif; ?>
                            </label>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <?php if (count($terms) > 5) : ?>
                    <button type="button" class="show-more-btn" data-show-text="<?= esc_attr__('Xem thêm', TEXT_DOMAIN) ?>" data-hide-text="<?= esc_attr__('Thu gọn', TEXT_DOMAIN) ?>">
                        <?= __('Xem thêm', TEXT_DOMAIN) ?> <span class="more-count">(+<?= count($terms) - 5 ?>)</span>
                    </button>
                <?php endif; ?>
            </div>
<?php
        }
    }

    /**
     * Get max product price
     */
    private static function get_max_product_price(): float
    {
        global $wpdb;

        $max_price = $wpdb->get_var("
            SELECT MAX(CAST(meta_value AS DECIMAL(10,2)))
            FROM {$wpdb->postmeta}
            WHERE meta_key = '_price'
            AND meta_value != ''
        ");

        return $max_price ? ceil(floatval($max_price)) : 1000000;
    }
}

// Initialize
ShopFilter::get_instance();
