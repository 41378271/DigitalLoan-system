<?php
session_start();
require_once "../../../config/db.php";
header("Content-Type: application/json");

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
  echo json_encode(["success" => false, "message" => "Not authorized"]);
  exit;
}

$q = trim($_GET['q'] ?? '');
$only = trim($_GET['only'] ?? 'all'); // all | active | deactivated

$sql = "SELECT id, full_name, email, phone, role, created_at, is_active
        FROM users
        WHERE 1=1";

$params = [];
$types = "";

// filter active
if ($only === "active") {
  $sql .= " AND is_active = 1";
} elseif ($only === "deactivated") {
  $sql .= " AND is_active = 0";
}

// search
if ($q !== "") {
  $sql .= " AND (full_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
  $like = "%".$q."%";
  $params[] = $like; $params[] = $like; $params[] = $like;
  $types .= "sss";
}

$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$res = $stmt->get_result();

$rows = [];
while ($r = $res->fetch_assoc()) $rows[] = $r;

echo json_encode(["success" => true, "users" => $rows]);