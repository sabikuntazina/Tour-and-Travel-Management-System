<?php require_once __DIR__ . '/includes/auth.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}
check_csrf();
$uid = (int)$_SESSION['user_id'];
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$pass = $_POST['password'] ?? '';
if (!$name || !$email) {
    header('Location: dashboard.php');
    exit;
}
$emailEsc = $conn->real_escape_string($email);
$exist = $conn->query("SELECT id FROM users WHERE email='$emailEsc' AND id<>$uid");
if ($exist && $exist->num_rows) {
    header('Location: dashboard.php');
    exit;
}
if ($pass !== '') {
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET name=?, email=?, password=? WHERE id=?");
    $stmt->bind_param('sssi', $name, $email, $hash, $uid);
} else {
    $stmt = $conn->prepare("UPDATE users SET name=?, email=? WHERE id=?");
    $stmt->bind_param('ssi', $name, $email, $uid);
}
$stmt->execute();
header('Location: dashboard.php');
