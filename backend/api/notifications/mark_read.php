<?php
session_start();
require_once "../../config/db.php";
header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
  echo json_encode(["success"=>false,"message"=>"Not logged in"]);
  exit;
}

$user_id = (int)$_SESSION['user_id'];
$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
  echo json_encode(["success"=>false,"message"=>"Invalid id"]);
  exit;
}

$stmt = $conn->prepare("UPDATE notifications SET is_read=1 WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $id, $user_id);

echo json_encode(["success"=>$stmt->execute(),"message"=>"Marked as read"]);