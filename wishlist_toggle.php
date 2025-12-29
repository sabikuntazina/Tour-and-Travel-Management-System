<?php require_once __DIR__ . '/includes/auth.php';
require_login();
$pid = (int)($_GET['package_id'] ?? 0);
if (!$pid) {
    header('Location: packages.php');
    exit;
}
$uid = (int)$_SESSION['user_id'];
$res = $conn->query("SELECT id FROM wishlist WHERE user_id=$uid AND package_id=$pid");
if ($res && $res->num_rows) {
    $conn->query("DELETE FROM wishlist WHERE user_id=$uid AND package_id=$pid");
} else {
    $conn->query("INSERT IGNORE INTO wishlist(user_id,package_id) VALUES($uid,$pid)");
}
$ref = $_SERVER['HTTP_REFERER'] ?? ('package.php?id=' . $pid);
header('Location: ' . $ref);
