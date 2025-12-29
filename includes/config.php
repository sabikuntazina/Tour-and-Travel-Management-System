<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Dhaka');

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'travelnext');

define('SITE_NAME', 'TravelNext');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}
$conn->query("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db(DB_NAME);
$conn->set_charset('utf8mb4');

// Run migrations if core tables are missing
$chk = $conn->query("SHOW TABLES LIKE 'packages'");
if (!$chk || !$chk->num_rows) {
    $sqlFile = dirname(__DIR__) . '/database.sql';
    if (is_file($sqlFile)) {
        $sql = file_get_contents($sqlFile);
        // Split by semicolon followed by newline to avoid issues in text
        $stmts = preg_split('/;\s*\n/', $sql);
        foreach ($stmts as $stmt) {
            $stmt = trim($stmt);
            if ($stmt === '' || substr($stmt, 0, 2) == '--') continue;
            @$conn->query($stmt);
        }
    }
}

function base_url($path = '')
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $dir = rtrim(str_replace('\\', '/', dirname($script)), '/.');
    $base = $protocol . $host . $dir;
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}
