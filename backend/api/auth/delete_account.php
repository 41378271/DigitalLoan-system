<?php
session_start();
require_once "../../config/db.php";
header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
  echo json_encode(["success"=>false, "message"=>"Not logged in"]);
  exit;
}

$user_id = (int)$_SESSION['user_id'];

// OPTIONAL SAFETY: prevent admin from deleting via borrower UI
if (($_SESSION['role'] ?? '') === 'admin') {
  echo json_encode(["success"=>false, "message"=>"Admins cannot delete account here"]);
  exit;
}

$conn->begin_transaction();

try {
  // delete related data (adjust if your schema differs)
  $stmt = $conn->prepare("DELETE FROM notifications WHERE user_id=?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();

  $stmt = $conn->prepare("DELETE FROM kyc_documents WHERE user_id=?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();

  $stmt = $conn->prepare("DELETE FROM loans WHERE user_id=?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();

  // finally delete user
  $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();

  $conn->commit();

  // destroy session
  $_SESSION = [];
  session_destroy();

  echo json_encode(["success"=>true, "message"=>"Account deleted"]);
} catch (Exception $e) {
  $conn->rollback();
  echo json_encode(["success"=>false, "message"=>"Delete failed"]);
}