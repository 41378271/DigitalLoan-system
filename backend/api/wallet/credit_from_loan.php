<?php
// backend/api/wallet/credit_from_loan.php
require_once __DIR__ . "/_wallet_lib.php";

function credit_wallet_for_approved_loan($conn, $user_id, $loan_id, $amount){
  $conn->begin_transaction();
  try{
    $w = wallet_get($conn, $user_id);
    $newBal = (float)$w["balance"] + (float)$amount;

    $stmt = $conn->prepare("UPDATE wallets SET balance=? WHERE user_id=?");
    $stmt->bind_param("di", $newBal, $user_id);
    $stmt->execute();

    wallet_add_tx($conn, $user_id, "loan_credit", (float)$amount, $newBal, "loan_id=".$loan_id);

    $conn->commit();
    return true;
  }catch(Exception $e){
    $conn->rollback();
    return false;
  }
}