<?php require_once __DIR__ . '/includes/auth.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}
check_csrf();
$pid = (int)($_POST['package_id'] ?? 0);
$rating = max(1, min(5, (int)($_POST['rating'] ?? 5)));
$comment = trim($_POST['comment'] ?? '');
if (!$pid || !$comment) {
    header('Location: index.php');
    exit;
}
$uid = (int)$_SESSION['user_id'];
$stmt = $conn->prepare("INSERT INTO reviews(user_id,package_id,rating,comment,status) VALUES(?,?,?,?, 'Pending')");
$stmt->bind_param('iiis', $uid, $pid, $rating, $comment);
$stmt->execute();
header('Location: package.php?id=' . $pid);
