<?php
session_start();
require_once "../../config/db.php";
header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
  echo json_encode(["success"=>false,"message"=>"Not logged in"]);
  exit;
}

$user_id = (int)$_SESSION['user_id'];

$stmt = $conn->prepare("SELECT id, title, message, is_read, created_at
                        FROM notifications
                        WHERE user_id=?
                        ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

$rows = [];
while($r = $res->fetch_assoc()) $rows[] = $r;

echo json_encode(["success"=>true,"rows"=>$rows]);