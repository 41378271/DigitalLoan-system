<?php
session_start();
require_once __DIR__ . "/_wallet_lib.php";

if(!isset($_SESSION['user_id'])){
  json_out(["success"=>false, "message"=>"Not logged in"]);
}
$user_id = (int)$_SESSION['user_id'];

$limit = (int)($_GET["limit"] ?? 20);
if($limit <= 0 || $limit > 100) $limit = 20;

ensure_wallet($conn, $user_id);

$stmt = $conn->prepare("
  SELECT id, type, amount, balance_after, ref, created_at
  FROM wallet_transactions
  WHERE user_id=?
  ORDER BY id DESC
  LIMIT ?
");
$stmt->bind_param("ii", $user_id, $limit);
$stmt->execute();

$rows = [];
$res = $stmt->get_result();
while($r = $res->fetch_assoc()){
  $rows[] = $r;
}

json_out(["success"=>true, "transactions"=>$rows]);