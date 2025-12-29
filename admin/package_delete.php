<?php require_once __DIR__ . '/header.php';
$id = (int)($_GET['id'] ?? 0);
if ($id) {
    $conn->query("DELETE FROM packages WHERE id=$id");
}
header('Location: packages.php');
exit;
