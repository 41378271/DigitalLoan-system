<?php
session_start();
require_once "../../config/db.php";
header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
  echo json_encode(["success"=>false,"message"=>"Not logged in"]);
  exit;
}

$user_id = (int)$_SESSION['user_id'];
$stmt = $conn->prepare("SELECT COUNT(*) AS c FROM notifications WHERE user_id=? AND is_read=0");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$c = $stmt->get_result()->fetch_assoc()['c'] ?? 0;

echo json_encode(["success"=>true,"count"=>(int)$c]);