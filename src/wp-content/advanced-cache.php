<?php
// FlyingPress

$config = array (
  'license_key' => '6f4fc2dd-43d2-4855-86df-ae3bc91084a5',
  'license_active' => true,
  'license_status' => 'active',
  'css_js_minify' => true,
  'css_rucss' => true,
  'css_rucss_include_selectors' => 
  array (
  ),
  'js_delay' => true,
  'js_delay_method' => 'defer',
  'js_delay_excludes' => 
  array (
  ),
  'js_delay_third_party' => true,
  'js_delay_third_party_excludes' => 
  array (
  ),
  'js_delay_selected' => false,
  'js_delay_selected_includes' => 
  array (
  ),
  'css_js_self_host_third_party' => true,
  'lazy_load' => true,
  'lazy_load_exclusions' => 
  array (
    0 => 'custom-logo',
  ),
  'properly_size_images' => true,
  'youtube_placeholder' => true,
  'self_host_gravatars' => true,
  'fonts_preload' => true,
  'fonts_optimize_google' => true,
  'fonts_display_swap' => true,
  'lazy_render' => true,
  'lazy_render_excludes' => 
  array (
  ),
  'cache_link_prefetch' => false,
  'cache_mobile' => true,
  'cache_logged_in' => true,
  'cache_refresh' => false,
  'cache_refresh_interval' => '2hours',
  'cache_bypass_urls' => 
  array (
    0 => '/gio-hang/',
    1 => '/thanh-toan/',
    2 => '/cart/',
    3 => '/checkout/',
  ),
  'cache_include_queries' => 
  array (
  ),
  'cache_bypass_cookies' => 
  array (
  ),
  'cdn' => false,
  'cdn_type' => 'custom',
  'cdn_url' => '',
  'cdn_file_types' => 'all',
  'flying_cdn_api_key' => '',
  'cf_api_key' => '',
  'cf_email' => '',
  'cf_zone_id' => '',
  'cf_page_caching' => false,
  'db_auto_clean' => false,
  'db_auto_clean_interval' => 'daily',
  'db_post_revisions' => false,
  'db_post_auto_drafts' => false,
  'db_post_trashed' => false,
  'db_comments_spam' => false,
  'db_comments_trashed' => false,
  'db_transients_expired' => false,
  'db_optimize_tables' => false,
  'bloat_disable_block_css' => true,
  'bloat_disable_dashicons' => false,
  'bloat_disable_emojis' => true,
  'bloat_disable_jquery_migrate' => true,
  'bloat_disable_xml_rpc' => true,
  'bloat_disable_rss_feed' => true,
  'bloat_disable_oembeds' => true,
  'bloat_post_revisions_control' => true,
  'bloat_heartbeat_control' => true,
  'cf_cache_ruleset_id' => '',
  'cf_cache_rule_id' => '',
  'cf_cache_file_rule_id' => '',
  'cf_rewrite_ruleset_id' => '',
  'cf_rewrite_rule_id' => '',
  'cache_ignore_queries' => 
  array (
    0 => 'adgroupid',
    1 => 'adid',
    2 => 'age-verified',
    3 => 'ao_noptimize',
    4 => 'campaignid',
    5 => 'cn-reloaded',
    6 => 'dm_i',
    7 => 'ef_id',
    8 => 'epik',
    9 => 'fb_action_ids',
    10 => 'fb_action_types',
    11 => 'fb_source',
    12 => 'fbclid',
    13 => 'gad_campaignid',
    14 => 'gad_source',
    15 => 'gbraid',
    16 => 'gclid',
    17 => 'gclsrc',
    18 => 'gdfms',
    19 => 'gdftrk',
    20 => 'gdffi',
    21 => '_ga',
    22 => '_gl',
    23 => 'kboard_id',
    24 => 'mkwid',
    25 => 'mc_cid',
    26 => 'mc_eid',
    27 => 'msclkid',
    28 => 'mtm_campaign',
    29 => 'mtm_cid',
    30 => 'mtm_content',
    31 => 'mtm_keyword',
    32 => 'mtm_medium',
    33 => 'mtm_source',
    34 => 'pcrid',
    35 => 'pk_campaign',
    36 => 'pk_cid',
    37 => 'pk_content',
    38 => 'pk_keyword',
    39 => 'pk_medium',
    40 => 'pk_source',
    41 => 'pp',
    42 => 'ref',
    43 => 'redirect_log_mongo_id',
    44 => 'redirect_mongo_id',
    45 => 'sb_referer_host',
    46 => 's_kwcid',
    47 => 'srsltid',
    48 => 'sscid',
    49 => 'trk_contact',
    50 => 'trk_msg',
    51 => 'trk_module',
    52 => 'trk_sid',
    53 => 'ttclid',
    54 => 'utm_campaign',
    55 => 'utm_content',
    56 => 'utm_expid',
    57 => 'utm_id',
    58 => 'utm_medium',
    59 => 'utm_source',
    60 => 'utm_term',
  ),
  'cache_include_cookies' => 
  array (
  ),
);

if (!headers_sent()) {
  // Set response cache headers
  header('x-flying-press-cache: MISS');
  header('x-flying-press-source: PHP');
}

// Skip WP CLI requests
if (defined('WP_CLI') && WP_CLI) {
  return false;
}

// Check if its preload request
if (isset($_SERVER['HTTP_X_FLYING_PRESS_PRELOAD'])) {
  unset($_COOKIE['wordpress_logged_in_1']);
  return false;
}

// Check if the request method is HEAD or GET
if (!isset($_SERVER['REQUEST_METHOD']) || !in_array($_SERVER['REQUEST_METHOD'], ['HEAD', 'GET'])) {
  return false;
}

// Check if current page has any cookies set that should not be cached
foreach ($config['cache_bypass_cookies'] as $cookie) {
  if (preg_grep("/$cookie/i", array_keys($_COOKIE))) {
    return false;
  }
}

// Default file name is "index.php"
$file_name = 'index';

// Check if the user is logged in
$is_user_logged_in = preg_grep('/^wordpress_logged_in_/i', array_keys($_COOKIE));
if ($is_user_logged_in && !$config['cache_logged_in']) {
  return false;
}

// Append "-logged-in" to the file name if the user is logged in
$file_name .= $is_user_logged_in ? '-logged-in' : '';

// Add user role to cache file name
$file_name .= isset($_COOKIE['fp_logged_in_roles']) ? '-' . $_COOKIE['fp_logged_in_roles'] : '';

// Include Cookies
$cookies_to_include = $config['cache_include_cookies'] ?? [];

// If any of the include cookies exist append it to the file name
foreach ($cookies_to_include as $cookie_name) {
  if (isset($_COOKIE[$cookie_name])) {
    $file_name .= '-' . $_COOKIE[$cookie_name];
  }
}

// Check if user agent is mobile and append "mobile" to the file name
$is_mobile =
  isset($_SERVER['HTTP_USER_AGENT']) &&
  preg_match(
    '/Mobile|Android|Silk\/|Kindle|BlackBerry|Opera (Mini|Mobi)/i',
    $_SERVER['HTTP_USER_AGENT']
  );
$file_name .= $config['cache_mobile'] && $is_mobile ? '-mobile' : '';

// Remove ignored query string parameters and generate hash of the remaining
$query_strings = array_diff_key($_GET, array_flip($config['cache_ignore_queries']));
$file_name .= !empty($query_strings) ? '-' . md5(serialize($query_strings)) : '';

// File paths for cache files
$host = $_SERVER['HTTP_HOST'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = urldecode($path);
$cache_file_path = WP_CONTENT_DIR . "/cache/flying-press/$host/$path/$file_name.html.gz";

// Check if the gzipped cache file exists
if (!file_exists($cache_file_path)) {
  return false;
}

// Set the necessary headers
ini_set('zlib.output_compression', 0);
header('Content-Encoding: gzip');

// CDN cache headers
header("Cache-Tag: $host");
header('CDN-Cache-Control: max-age=2592000');
header('Cache-Control: no-cache, must-revalidate');

// Set cache HIT response header
header('x-flying-press-cache: HIT');

// Add Last modified response header
$cache_last_modified = filemtime($cache_file_path);
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $cache_last_modified) . ' GMT');

// Get last modified since from request header
$http_modified_since = isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])
  ? strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])
  : 0;

// If file is not modified during this time, send 304
if ($http_modified_since >= $cache_last_modified) {
  header($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified', true, 304);
  exit();
}

header('Content-Type: text/html; charset=UTF-8');

readfile($cache_file_path);
exit();
