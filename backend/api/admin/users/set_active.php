<?php
session_start();
require_once "../../../config/db.php";
header("Content-Type: application/json");

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
  echo json_encode(["success" => false, "message" => "Not authorized"]);
  exit;
}

$user_id  = (int)($_POST['user_id'] ?? 0);
$is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : -1;

if ($user_id <= 0 || ($is_active !== 0 && $is_active !== 1)) {
  echo json_encode(["success" => false, "message" => "Invalid input"]);
  exit;
}

/* Fetch target user role */
$stmt = $conn->prepare("SELECT role FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();

if (!$user) {
  echo json_encode(["success" => false, "message" => "User not found"]);
  exit;
}

/* BLOCK ANY ADMIN DEACTIVATION */
if ($user['role'] === 'admin' && $is_active === 0) {
  echo json_encode([
    "success" => false,
    "message" => "Admin accounts cannot be deactivated."
  ]);
  exit;
}

/* Prevent admin from deactivating themselves */
if ($user_id === (int)$_SESSION['user_id'] && $is_active === 0) {
  echo json_encode([
    "success" => false,
    "message" => "You cannot deactivate your own account."
  ]);
  exit;
}

/*  Perform update */
$stmt = $conn->prepare("UPDATE users SET is_active=? WHERE id=?");
$stmt->bind_param("ii", $is_active, $user_id);

if ($stmt->execute()) {
  echo json_encode([
    "success" => true,
    "message" => $is_active ? "User reactivated." : "User deactivated."
  ]);
} else {
  echo json_encode(["success" => false, "message" => "Update failed."]);
}