<?php require_once __DIR__ . '/includes/auth.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contact.php');
    exit;
}
check_csrf();
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');
if (!$name || !$email || !$subject || !$message) {
    header('Location: contact.php');
    exit;
}
$stmt = $conn->prepare("INSERT INTO messages(name,email,phone,subject,message) VALUES(?,?,?,?,?)");
$stmt->bind_param('sssss', $name, $email, $phone, $subject, $message);
$stmt->execute();
header('Location: contact.php?sent=1');
