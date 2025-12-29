<?php require_once __DIR__ . '/includes/auth.php';
require_login();
$uid = (int)$_SESSION['user_id'];
if (isset($_GET['all'])) {
    $conn->query("UPDATE notifications SET is_read=1 WHERE user_id=$uid");
}
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $conn->query("UPDATE notifications SET is_read=1 WHERE id=$id AND user_id=$uid");
}
header('Location: dashboard.php#notifications');
