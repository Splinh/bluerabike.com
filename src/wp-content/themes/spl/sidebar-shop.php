<?php

\defined('ABSPATH') || die;

$object            = get_queried_object();
$product_cat_child = \HD_Helper::getField('product_cat_child', $object);

?>
<div class="cell-sidebar">
    <div class="sidebar-wrapper">
        <?php if ($product_cat_child) : ?>
            <aside class="sidebar-inner">
                <p class="sidebar-sub-title"><?= __('Danh mục', TEXT_DOMAIN) ?></p>
                <p class="sidebar-title"><?= $object?->name ?></p>
                <ul class="sidebar-menu-list">
                    <?php
                    foreach ($product_cat_child as $child_term_id) :
                        $child_term = \HD_Helper::getTerm($child_term_id, 'product_cat');
                        $product_count = $child_term->count;
                    ?>
                        <li><a href="<?= get_term_link($child_term, 'product_cat') ?>" title="<?= esc_attr($child_term?->name) ?>"><?= $child_term?->name ?> (<?= number_format_i18n($product_count) ?>)</a></li>
                    <?php endforeach; ?>
                </ul>
            </aside>
        <?php endif; ?>
        <aside class="sidebar-inner">
            <p class="sidebar-sub-title"><?= __('Bộ lọc', TEXT_DOMAIN) ?></p>
            <div class="spl-filters">
                <?php \HD\Utilities\ShopFilter::render_filters(); ?>
            </div>
        </aside>
    </div>
</div>