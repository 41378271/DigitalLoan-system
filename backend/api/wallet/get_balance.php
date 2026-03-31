<?php
session_start();
require_once "../../config/db.php";
header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
  echo json_encode(["success" => false, "message" => "Not logged in"]);
  exit;
}

$user_id = (int)$_SESSION['user_id'];

/**
 * Block deactivated users (prevents access even if they still have a session)
 */
$chk = $conn->prepare("SELECT is_active FROM users WHERE id = ? LIMIT 1");
$chk->bind_param("i", $user_id);
$chk->execute();
$u = $chk->get_result()->fetch_assoc();

if (!$u) {
  echo json_encode(["success" => false, "message" => "User not found"]);
  exit;
}

if ((int)$u['is_active'] !== 1) {
  // optional: destroy session to force logout
  session_unset();
  session_destroy();

  echo json_encode(["success" => false, "message" => "Account is deactivated. Please contact admin."]);
  exit;
}

/**
 *  Ensure wallet exists (now includes currency)
 * If currency has a default (KES), this still works fine.
 */
$stmt = $conn->prepare("INSERT IGNORE INTO wallets (user_id, balance, currency) VALUES (?, 0.00, 'KES')");
$stmt->bind_param("i", $user_id);
$stmt->execute();

/**
 * Fetch balance + currency
 */
$stmt = $conn->prepare("SELECT balance, currency FROM wallets WHERE user_id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();

$balance  = isset($res['balance']) ? (float)$res['balance'] : 0.00;
$currency = $res['currency'] ?? "KES";

echo json_encode([
  "success"  => true,
  "balance"  => $balance,
  "currency" => $currency
]);