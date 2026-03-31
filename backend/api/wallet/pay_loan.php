<?php 
session_start();
require_once __DIR__ . "/../../config/db.php";
header("Content-Type: application/json");

function tableExists(mysqli $conn, string $table): bool {
  $table = $conn->real_escape_string($table);
  $sql = "SHOW TABLES LIKE '{$table}'";
  $res = $conn->query($sql);
  return $res && $res->num_rows > 0;
}

function columnExists(mysqli $conn, string $table, string $column): bool {
  $sql = "SELECT 1
          FROM INFORMATION_SCHEMA.COLUMNS
          WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = ?
            AND COLUMN_NAME = ?
          LIMIT 1";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ss", $table, $column);
  $stmt->execute();
  $res = $stmt->get_result();
  return $res && $res->num_rows > 0;
}

function jsonFail(string $msg, int $code = 200) {
  http_response_code($code);
  echo json_encode(["success"=>false, "message"=>$msg]);
  exit;
}

if (!isset($_SESSION['user_id'])) jsonFail("Not logged in", 401);

$user_id = (int)$_SESSION['user_id'];
$loan_id = isset($_POST['loan_id']) ? (int)$_POST['loan_id'] : 0;
$amount  = isset($_POST['amount'])  ? (float)$_POST['amount']  : 0.0;

if ($loan_id <= 0) jsonFail("Invalid loan_id");
if ($amount <= 0)  jsonFail("Invalid amount");

$conn->begin_transaction();

try {

  // LOCK WALLET
  $stmt = $conn->prepare("SELECT id, balance FROM wallets WHERE user_id=? FOR UPDATE");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $wallet = $stmt->get_result()->fetch_assoc();

  if (!$wallet) jsonFail("Wallet not found");

  $wallet_id = (int)$wallet['id'];
  $wallet_balance = (float)$wallet['balance'];

  // LOCK LOAN
  $stmt = $conn->prepare("SELECT id, amount, remaining, status FROM loans WHERE id=? AND user_id=? FOR UPDATE");
  $stmt->bind_param("ii", $loan_id, $user_id);
  $stmt->execute();
  $loan = $stmt->get_result()->fetch_assoc();

  if (!$loan) {
    $conn->rollback();
    jsonFail("Loan not found");
  }

  $remaining = (float)$loan['remaining'];

  if ($remaining <= 0) {
    $conn->rollback();
    jsonFail("Loan already paid");
  }

  // CAP PAYMENT
  $payAmount = min($amount, $remaining);

  // CHECK WALLET
  if ($wallet_balance < $payAmount) {
    $conn->rollback();
    jsonFail("Insufficient balance");
  }

  // UPDATE WALLET
  $newWallet = $wallet_balance - $payAmount;
  $stmt = $conn->prepare("UPDATE wallets SET balance=? WHERE user_id=?");
  $stmt->bind_param("di", $newWallet, $user_id);
  $stmt->execute();

  // 🔥 FIXED: NORMALIZE REMAINING VALUE
  $newRemaining = $remaining - $payAmount;

  if ($newRemaining <= 0.01) {
    $newRemaining = 0.00;
  }

  $status = ($newRemaining == 0.00) ? "paid" : "ongoing";

  // UPDATE LOAN (THIS WAS YOUR MAIN ISSUE)
  $stmt = $conn->prepare("UPDATE loans SET remaining=?, status=? WHERE id=? AND user_id=?");
  $stmt->bind_param("dsii", $newRemaining, $status, $loan_id, $user_id);
  $stmt->execute();

  // INSERT TRANSACTION
  $desc = "Loan payment (loan #{$loan_id})";

  $stmt = $conn->prepare("
    INSERT INTO wallet_transactions (wallet_id, user_id, type, amount, description, created_at)
    VALUES (?, ?, 'withdraw', ?, ?, NOW())
  ");
  $stmt->bind_param("iids", $wallet_id, $user_id, $payAmount, $desc);
  $stmt->execute();

  // OPTIONAL: SAVE PAYMENT RECORD
  if (tableExists($conn, "loan_payments")) {
    $stmt = $conn->prepare("
      INSERT INTO loan_payments (loan_id, user_id, amount, created_at)
      VALUES (?, ?, ?, NOW())
    ");
    $stmt->bind_param("iid", $loan_id, $user_id, $payAmount);
    $stmt->execute();
  }

  $conn->commit();

  echo json_encode([
    "success" => true,
    "message" => "Payment successful",
    "paid" => $payAmount,
    "remaining" => $newRemaining,
    "wallet" => $newWallet
  ]);

} catch (Exception $e) {
  $conn->rollback();
  echo json_encode([
    "success"=>false,
    "message"=>"Error: " . $e->getMessage()
  ]);
}