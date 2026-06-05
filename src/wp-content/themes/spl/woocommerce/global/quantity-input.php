<?php
/**
 * Product quantity inputs with +/- buttons
 *
 * @package flavor
 */

defined('ABSPATH') || exit;

// Get input values
$input_id = uniqid('quantity_');
$input_name = isset($args['input_name']) ? $args['input_name'] : 'quantity';
$input_value = isset($args['input_value']) ? $args['input_value'] : 1;
$min_value = isset($args['min_value']) ? $args['min_value'] : apply_filters('woocommerce_quantity_input_min', 1, $product);
$max_value = isset($args['max_value']) ? $args['max_value'] : apply_filters('woocommerce_quantity_input_max', '', $product);
$step = isset($args['step']) ? $args['step'] : apply_filters('woocommerce_quantity_input_step', 1, $product);
$pattern = isset($args['pattern']) ? $args['pattern'] : apply_filters('woocommerce_quantity_input_pattern', has_filter('woocommerce_stock_amount', 'intval') ? '[0-9]*' : '');
$inputmode = isset($args['inputmode']) ? $args['inputmode'] : apply_filters('woocommerce_quantity_input_inputmode', has_filter('woocommerce_stock_amount', 'intval') ? 'numeric' : '');
$placeholder = isset($args['placeholder']) ? $args['placeholder'] : apply_filters('woocommerce_quantity_input_placeholder', '', $product);
$classes = isset($args['classes']) ? $args['classes'] : [];
$product_name = isset($args['product_name']) ? $args['product_name'] : '';

// Add custom class
$classes[] = 'qty-input-wrap';
?>
<div class="quantity-wrapper">
    <button type="button" class="qty-btn qty-minus" onclick="handleQtyBtn(this, 'minus')" aria-label="<?php esc_attr_e('Decrease quantity', 'flavor'); ?>">−</button>
    <label class="screen-reader-text" for="<?php echo esc_attr($input_id); ?>">
        <?php echo esc_html($product_name ? sprintf(__('%s quantity', 'flavor'), $product_name) : __('Quantity', 'flavor')); ?>
    </label>
    <input
        type="number"
        id="<?php echo esc_attr($input_id); ?>"
        class="input-text qty text <?php echo esc_attr(implode(' ', $classes)); ?>"
        name="<?php echo esc_attr($input_name); ?>"
        value="<?php echo esc_attr($input_value); ?>"
        title="<?php echo esc_attr_x('Qty', 'Product quantity input tooltip', 'flavor'); ?>"
        size="4"
        min="<?php echo esc_attr($min_value); ?>"
        max="<?php echo esc_attr(0 < $max_value ? $max_value : ''); ?>"
        step="<?php echo esc_attr($step); ?>"
        placeholder="<?php echo esc_attr($placeholder); ?>"
        inputmode="<?php echo esc_attr($inputmode); ?>"
        autocomplete="off"
        <?php if (!empty($pattern)) : ?>pattern="<?php echo esc_attr($pattern); ?>"<?php endif; ?>
    />
    <button type="button" class="qty-btn qty-plus" onclick="handleQtyBtn(this, 'plus')" aria-label="<?php esc_attr_e('Increase quantity', 'flavor'); ?>">+</button>
</div>
<?php
// Only add script once
if (!defined('QTY_WRAPPER_SCRIPT_LOADED')) {
    define('QTY_WRAPPER_SCRIPT_LOADED', true);
?>
<script>
function handleQtyBtn(btn, action) {
    var wrapper = btn.closest('.quantity-wrapper');
    if (!wrapper) return;

    var input = wrapper.querySelector('input.qty');
    if (!input) return;

    var value = parseInt(input.value, 10) || 1;
    var min = parseInt(input.min, 10) || 1;
    var max = parseInt(input.max, 10) || 9999;
    var step = parseInt(input.step, 10) || 1;

    if (action === 'plus') {
        if (!max || value < max) {
            value += step;
        }
    } else if (action === 'minus') {
        if (value > min) {
            value -= step;
        }
    }

    input.value = value;
    input.dispatchEvent(new Event('change', { bubbles: true }));
    
    // Update button states
    var minusBtn = wrapper.querySelector('.qty-minus');
    var plusBtn = wrapper.querySelector('.qty-plus');
    if (minusBtn) minusBtn.disabled = value <= min;
    if (plusBtn) plusBtn.disabled = max && value >= max;
}
</script>
<?php } ?>