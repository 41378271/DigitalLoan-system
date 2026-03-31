<?php
session_start();
header("Content-Type: application/json");
require_once "../../config/db.php";

if (!isset($_SESSION['user_id'])) {
  echo json_encode(["success"=>false, "count"=>0]);
  exit;
}

$user_id = (int)$_SESSION['user_id'];

$stmt = $conn->prepare("SELECT COUNT(*) c FROM chat_messages WHERE user_id=? AND role='bot' AND is_read=0");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$count = (int)$stmt->get_result()->fetch_assoc()['c'];

echo json_encode(["success"=>true, "count"=>$count]);