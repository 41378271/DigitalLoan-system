<?php
session_start();
require_once __DIR__ . "/_wallet_lib.php";

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

  wallet_add_tx($conn, $user_id, "withdraw", $amount, $newBal, "manual_withdraw");

  $conn->commit();
  json_out(["success"=>true, "message"=>"Withdraw successful", "balance"=>$newBal, "currency"=>$w["currency"]]);
}catch(Exception $e){
  $conn->rollback();
  json_out(["success"=>false, "message"=>"Withdraw failed"]);
}