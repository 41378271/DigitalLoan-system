<?php
session_start();
require_once "../../config/db.php";
header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
  echo json_encode(["success"=>false, "message"=>"Not logged in"]);
  exit;
}

$user_id = (int)$_SESSION['user_id'];

$loan_id = (int)($_POST['loan_id'] ?? 0);
$amount  = (float)($_POST['amount'] ?? 0);

if ($loan_id <= 0 || $amount <= 0) {
  echo json_encode(["success"=>false, "message"=>"Loan ID and valid amount required"]);
  exit;
}

$conn->begin_transaction();

try {
  // 1) Lock wallet row
  $w = $conn->prepare("SELECT id, balance, currency FROM wallets WHERE user_id=? FOR UPDATE");
  $w->bind_param("i", $user_id);
  $w->execute();
  $wallet = $w->get_result()->fetch_assoc();

  if (!$wallet) {
    throw new Exception("Wallet not found.");
  }

  $wallet_balance = (float)$wallet['balance'];
  $currency = $wallet['currency'] ?? 'KES';

  // 2) Lock loan row (must belong to this user)
  $l = $conn->prepare("
    SELECT id, remaining_balance, status
    FROM loans
    WHERE id=? AND user_id=?
    FOR UPDATE
  ");
  $l->bind_param("ii", $loan_id, $user_id);
  $l->execute();
  $loan = $l->get_result()->fetch_assoc();

  if (!$loan) {
    throw new Exception("Loan not found.");
  }

  // If you use different statuses, adjust here
  $status = strtolower($loan['status'] ?? '');
  if (in_array($status, ['paid', 'closed', 'completed'])) {
    throw new Exception("This loan is already fully paid.");
  }

  $remaining = (float)$loan['remaining_balance'];
  if ($remaining <= 0) {
    throw new Exception("This loan has no remaining balance.");
  }

  // 3) Cap payment to remaining balance
  if ($amount > $remaining) {
    $amount = $remaining;
  }

  // 4) Check wallet funds
  if ($wallet_balance < $amount) {
    throw new Exception("Insufficient wallet balance.");
  }

  // 5) Deduct wallet
  $new_wallet_balance = $wallet_balance - $amount;
  $u1 = $conn->prepare("UPDATE wallets SET balance=?, updated_at=NOW() WHERE user_id=?");
  $u1->bind_param("di", $new_wallet_balance, $user_id);
  $u1->execute();

  // 6) Update loan remaining balance
  $new_remaining = $remaining - $amount;

  // If you have interest/penalties, apply them before this step (not included here)
  $new_status = ($new_remaining <= 0.00001) ? 'paid' : $loan['status'];

  $u2 = $conn->prepare("UPDATE loans SET remaining_balance=?, status=? WHERE id=? AND user_id=?");
  $u2->bind_param("dsii", $new_remaining, $new_status, $loan_id, $user_id);
  $u2->execute();

  // 7) Insert loan payment record
  $p = $conn->prepare("INSERT INTO loan_payments (loan_id, user_id, amount, method, note) VALUES (?, ?, ?, 'wallet', 'Loan repayment')");
  $p->bind_param("iid", $loan_id, $user_id, $amount);
  $p->execute();

  // 8) Insert wallet transaction record (assumes wallet_transactions exists)
  // Adjust columns if your wallet_transactions table differs.
  $t = $conn->prepare("
    INSERT INTO wallet_transactions (user_id, type, amount, currency, description, created_at)
    VALUES (?, 'loan_payment', ?, ?, CONCAT('Loan #', ?, ' repayment'), NOW())
  ");
  $t->bind_param("idsi", $user_id, $amount, $currency, $loan_id);
  $t->execute();

  $conn->commit();

  echo json_encode([
    "success" => true,
    "message" => "Payment successful.",
    "paid_amount" => $amount,
    "currency" => $currency,
    "wallet_balance" => $new_wallet_balance,
    "loan_remaining" => $new_remaining,
    "loan_status" => $new_status
  ]);
} catch (Exception $e) {
  $conn->rollback();
  echo json_encode(["success"=>false, "message"=>$e->getMessage()]);
}