<?php
$logFile = 'D:/laragon/www/babyshop/error_log';
if (file_exists($logFile)) {
    $lines = file($logFile);
    $last_lines = array_slice($lines, -50);
    echo "=== Last 50 lines of D:/laragon/www/babyshop/error_log ===\n";
    echo implode("", $last_lines);
} else {
    echo "Error log file not found at $logFile\n";
}
