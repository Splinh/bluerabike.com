<?php
require_once 'wp-load.php';

$token = 'splat_5e4146dc44b6f92bb8b3410f6c241dfcd1b1071a1d379b3361a19b786611da64';

echo "=== VALIDATE USER TOKEN ===\n";
$auth = apply_filters('splat_validate_consumer_token', null, $token);
if (is_wp_error($auth)) {
    echo "WP_Error: " . $auth->get_error_message() . "\n";
} elseif ($auth === null) {
    echo "Result is NULL (invalid token or hash mismatch)\n";
} else {
    echo "SUCCESS!\n";
    print_r($auth);
}
