<?php
require_once "../../config/db.php";
require_once "../../core/response.php";
require_once "../../core/auth_guard.php";

$user_id = requireLogin();

// Block deactivated users
$chk = $conn->prepare("SELECT is_active FROM users WHERE id = ? LIMIT 1");
$chk->bind_param("i", $user_id);
$chk->execute();
$u = $chk->get_result()->fetch_assoc();

if (!$u) {
    jsonError("User not found.", 404);
}

if ((int)$u['is_active'] !== 1) {
    session_unset();
    session_destroy();
    jsonError("Account is deactivated. Please contact support.", 403);
}

// Fetch balance + currency
$stmt = $conn->prepare("SELECT balance, currency FROM wallets WHERE user_id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();

// If user somehow doesn't have a wallet (shouldn't happen with new registration flow, but good fallback)
if (!$res) {
    $ins = $conn->prepare("INSERT IGNORE INTO wallets (user_id, balance, currency) VALUES (?, 0.00, 'KES')");
    $ins->bind_param("i", $user_id);
    $ins->execute();
    
    $balance = 0.00;
    $currency = "KES";
} else {
    $balance  = isset($res['balance']) ? (float)$res['balance'] : 0.00;
    $currency = $res['currency'] ?? "KES";
}

jsonSuccess([
    "balance"  => $balance,
    "currency" => $currency
]);
?>