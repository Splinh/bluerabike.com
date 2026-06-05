<?php

/**
 * Video Popup Modal Template
 * 
 * @package Product_Video_Tabs
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- Video Popup Modal -->
<div id="pvt-video-popup-modal" class="pvt-video-popup-modal">
    <div class="pvt-video-popup-overlay"></div>
    <div class="pvt-video-popup-content">
        <button class="pvt-video-popup-close" type="button" aria-label="<?php esc_attr_e('Close', 'product-video-tabs'); ?>">&times;</button>
        <div class="pvt-video-popup-iframe-wrapper"></div>
    </div>
</div>