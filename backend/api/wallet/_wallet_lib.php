<?php
// backend/api/wallet/_wallet_lib.php
require_once __DIR__ . "/../../config/db.php";

function ensure_wallet($conn, $user_id){
  $stmt = $conn->prepare("INSERT IGNORE INTO wallets (user_id, balance, currency) VALUES (?, 0.00, 'KES')");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
}

function wallet_get($conn, $user_id){
  ensure_wallet($conn, $user_id);
  $stmt = $conn->prepare("SELECT balance, currency FROM wallets WHERE user_id=? LIMIT 1");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  return $stmt->get_result()->fetch_assoc();
}

function wallet_add_tx($conn, $user_id, $type, $amount, $balance_after, $ref=null){
  $currency = 'KES';
  $stmt = $conn->prepare("INSERT INTO wallet_transactions (user_id, type, amount, balance_after, currency, ref) VALUES (?,?,?,?,?,?)");
  $stmt->bind_param("isddss", $user_id, $type, $amount, $balance_after, $currency, $ref);
  $stmt->execute();
}

function json_out($arr){
  header("Content-Type: application/json");
  echo json_encode($arr);
  exit;
}