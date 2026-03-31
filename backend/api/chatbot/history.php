<?php
session_start();
header("Content-Type: application/json");
require_once "../../config/db.php";

if (!isset($_SESSION['user_id'])) {
  echo json_encode(["success"=>false, "message"=>"Not logged in"]);
  exit;
}

$user_id = (int)$_SESSION['user_id'];

$stmt = $conn->prepare("SELECT role, message, created_at FROM chat_messages WHERE user_id=? ORDER BY id DESC LIMIT 30");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$rows = array_reverse($rows);

echo json_encode(["success"=>true, "messages"=>$rows]);