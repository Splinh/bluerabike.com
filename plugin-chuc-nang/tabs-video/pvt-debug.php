<?php

/**
 * PVT Debug Helper
 * 
 * Add this code to wp-config.php or functions.php to debug:
 * define('PVT_DEBUG', true);
 * 
 * Then check debug.log for output
 * 
 * OR copy this file to theme folder and access via:
 * https://yoursite.com/wp-content/themes/your-theme/pvt-debug.php?product_id=XXX
 */

// Only run if accessed directly with a product_id
if (isset($_GET['product_id'])) {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

    $product_id = intval($_GET['product_id']);

    echo "<h1>PVT Debug for Product ID: {$product_id}</h1>";

    // Check if ACF is active
    echo "<h2>1. ACF Status</h2>";
    if (function_exists('get_field')) {
        echo "<p style='color:green'>✓ ACF is active</p>";
    } else {
        echo "<p style='color:red'>✗ ACF is NOT active</p>";
    }

    // Check if product exists
    echo "<h2>2. Product Status</h2>";
    $product = wc_get_product($product_id);
    if ($product) {
        echo "<p style='color:green'>✓ Product found: " . $product->get_name() . "</p>";
    } else {
        echo "<p style='color:red'>✗ Product NOT found</p>";
    }

    // Check ACF fields
    echo "<h2>3. ACF Field Values</h2>";

    $youtube_videos = get_field('youtube_videos', $product_id);
    $tiktok_videos = get_field('tiktok_videos', $product_id);
    $gallery_video = get_field('gallery_video_url', $product_id);

    echo "<h3>youtube_videos:</h3>";
    echo "<pre>" . print_r($youtube_videos, true) . "</pre>";

    echo "<h3>tiktok_videos:</h3>";
    echo "<pre>" . print_r($tiktok_videos, true) . "</pre>";

    echo "<h3>gallery_video_url:</h3>";
    echo "<pre>" . print_r($gallery_video, true) . "</pre>";

    // Check raw post meta
    echo "<h2>4. Raw Post Meta (ACF Data)</h2>";

    $all_meta = get_post_meta($product_id);
    $acf_meta = array_filter($all_meta, function ($key) {
        return strpos($key, 'youtube') !== false ||
            strpos($key, 'tiktok') !== false ||
            strpos($key, 'video') !== false ||
            strpos($key, '_youtube') !== false ||
            strpos($key, '_tiktok') !== false;
    }, ARRAY_FILTER_USE_KEY);

    echo "<pre>" . print_r($acf_meta, true) . "</pre>";

    // Check ACF field groups
    echo "<h2>5. Registered ACF Field Groups</h2>";
    if (function_exists('acf_get_field_groups')) {
        $groups = acf_get_field_groups();
        foreach ($groups as $group) {
            if (strpos($group['title'], 'Video') !== false || strpos($group['key'], 'pvt') !== false) {
                echo "<p><strong>{$group['title']}</strong> (key: {$group['key']})</p>";

                // Get fields in this group
                $fields = acf_get_fields($group['key']);
                if ($fields) {
                    echo "<ul>";
                    foreach ($fields as $field) {
                        echo "<li>{$field['label']} (name: {$field['name']}, key: {$field['key']})</li>";
                    }
                    echo "</ul>";
                }
            }
        }
    }

    // Check if plugin is detecting videos
    echo "<h2>6. Video Tab Visibility Check</h2>";
    $has_youtube = !empty($youtube_videos) && is_array($youtube_videos);
    $has_tiktok = !empty($tiktok_videos) && is_array($tiktok_videos);

    if ($has_youtube || $has_tiktok) {
        echo "<p style='color:green'>✓ Video tab SHOULD show (has video data)</p>";
    } else {
        echo "<p style='color:red'>✗ Video tab will NOT show (no video data found)</p>";
        echo "<p><strong>Possible causes:</strong></p>";
        echo "<ul>";
        echo "<li>ACF field names don't match (should be 'youtube_videos' and 'tiktok_videos')</li>";
        echo "<li>ACF repeater fields are empty or not saved properly</li>";
        echo "<li>Field group is not assigned to Products post type</li>";
        echo "</ul>";
    }

    exit;
}
?>

<!-- Instructions -->
<h1>PVT Debug Helper</h1>
<p>Access this file with a product_id parameter:</p>
<code>?product_id=123</code>