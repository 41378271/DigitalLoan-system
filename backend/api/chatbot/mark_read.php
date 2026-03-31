<?php
session_start();
header("Content-Type: application/json");
require_once "../../config/db.php";

if (!isset($_SESSION['user_id'])) {
  echo json_encode(["success"=>false, "message"=>"Not logged in"]);
  exit;
}

$user_id = (int)$_SESSION['user_id'];

$stmt = $conn->prepare("UPDATE chat_messages SET is_read=1 WHERE user_id=? AND role='bot'");
$stmt->bind_param("i", $user_id);
$stmt->execute();

echo json_encode(["success"=>true]);