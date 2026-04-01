<?php
session_start();
require_once __DIR__ . "/_wallet_lib.php";
require_once __DIR__ . "/../../communication.php";

if(!isset($_SESSION['user_id'])){
  json_out(["success"=>false, "message"=>"Not logged in"]);
}
$user_id = (int)$_SESSION['user_id'];

$amount = (float)($_POST["amount"] ?? 0);
if($amount <= 0){
  json_out(["success"=>false, "message"=>"Invalid amount"]);
}

$conn->begin_transaction();
try{
  $w = wallet_get($conn, $user_id);
  $bal = (float)$w["balance"];

  if($amount > $bal){
    $conn->rollback();
    json_out(["success"=>false, "message"=>"Insufficient balance"]);
  }

  $newBal = $bal - $amount;

  $stmt = $conn->prepare("UPDATE wallets SET balance=? WHERE user_id=?");
  $stmt->bind_param("di", $newBal, $user_id);
  $stmt->execute();

  $ref = "WDL-" . strtoupper(bin2hex(random_bytes(4)));
  wallet_add_tx($conn, $user_id, "withdraw", $amount, $newBal, $ref);

  // Get user email for notification
  $stmt_u = $conn->prepare("SELECT email FROM users WHERE id = ?");
  $stmt_u->bind_param("i", $user_id);
  $stmt_u->execute();
  $user_data = $stmt_u->get_result()->fetch_assoc();
  $email = $user_data['email'] ?? null;

  $msg = "A withdrawal of KES " . number_format($amount, 2) . " has been successfully processed from your wallet. Reference: $ref. New balance: KES " . number_format($newBal, 2);
  
  // Save In-System Notification
  saveNotification($user_id, $msg, "Withdrawal Successful");

  // Send Email Notification
  if ($email) {
      sendEmail($email, "Withdrawal Successful - KashFlow", $msg);
  }

  $conn->commit();
  json_out(["success"=>true, "message"=>"Withdraw successful", "balance"=>$newBal, "currency"=>$w["currency"], "ref"=>$ref]);
}catch(Exception $e){
  $conn->rollback();
  json_out(["success"=>false, "message"=>"Withdraw failed: " . $e->getMessage()]);
}