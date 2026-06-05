<?php
define('WP_USE_THEMES', false);
require_once __DIR__ . '/wp-load.php';

$error_log = ini_get('error_log');
echo "PHP error_log configuration: $error_log\n";

if ($error_log && file_exists($error_log)) {
    $lines = file($error_log);
    $last_lines = array_slice($lines, -30);
    echo "\n=== Last 30 lines of $error_log ===\n";
    echo implode("", $last_lines);
} else {
    echo "Active error log file does not exist at path: $error_log\n";
}
