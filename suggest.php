<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helpers.php';
header('Content-Type: application/json; charset=utf-8');

$q = trim($_GET['q'] ?? '');
$out = [];
if ($q !== '') {
  // Prepare LIKE value safely
  $like = "%" . $q . "%";
  // Collect distinct locations
  if ($stmt = $conn->prepare("SELECT DISTINCT location FROM packages WHERE location LIKE ? ORDER BY location ASC LIMIT 8")) {
    $stmt->bind_param('s', $like);
    if ($stmt->execute()) {
      $res = $stmt->get_result();
      while ($row = $res->fetch_assoc()) {
        $out[] = (string)$row['location'];
      }
    }
    $stmt->close();
  }
  // Collect distinct titles
  if ($stmt = $conn->prepare("SELECT DISTINCT title FROM packages WHERE title LIKE ? ORDER BY title ASC LIMIT 8")) {
    $stmt->bind_param('s', $like);
    if ($stmt->execute()) {
      $res = $stmt->get_result();
      while ($row = $res->fetch_assoc()) {
        $out[] = (string)$row['title'];
      }
    }
    $stmt->close();
  }
  // Deduplicate and trim to 8 suggestions
  $out = array_values(array_unique(array_filter(array_map('trim', $out))));
  if (count($out) > 8) {
    $out = array_slice($out, 0, 8);
  }
}

echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
