<?php
require_once "../../config/db.php";
require_once "../../core/response.php";
require_once "../../core/auth_guard.php";

$user_id = requireBorrower();

$loan_id = isset($_POST['loan_id']) ? (int)$_POST['loan_id'] : 0;
$amount  = isset($_POST['amount'])  ? (float)$_POST['amount']  : 0.0;

if ($loan_id <= 0 || $amount <= 0) {
    jsonError("Invalid loan or payment amount.");
}

$conn->begin_transaction();

try {
    // 1. Lock Wallet
    $stmt = $conn->prepare("SELECT id, balance, currency FROM wallets WHERE user_id=? FOR UPDATE");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $wallet = $stmt->get_result()->fetch_assoc();

    if (!$wallet) {
        throw new Exception("Wallet not found.");
    }

    $wallet_id = (int)$wallet['id'];
    $wallet_balance = (float)$wallet['balance'];
    $currency = $wallet['currency'];

    // 2. Lock Loan
    $stmt = $conn->prepare("SELECT id, amount, remaining_balance, status FROM loans WHERE id=? AND user_id=? FOR UPDATE");
    $stmt->bind_param("ii", $loan_id, $user_id);
    $stmt->execute();
    $loan = $stmt->get_result()->fetch_assoc();

    if (!$loan) {
        throw new Exception("Loan not found or does not belong to you.");
    }

    $remaining = (float)$loan['remaining_balance'];

    if ($remaining <= 0 || $loan['status'] === 'paid') {
        throw new Exception("This loan is already fully paid.");
    }

    // 3. Cap Payment
    $payAmount = min($amount, $remaining);

    // 4. Check Wallet Balance
    if ($wallet_balance < $payAmount) {
        throw new Exception("Insufficient wallet balance. You have {$currency} " . number_format($wallet_balance, 2));
    }

    // 5. Update Wallet
    $newWallet = $wallet_balance - $payAmount;
    $stmt = $conn->prepare("UPDATE wallets SET balance=? WHERE user_id=?");
    $stmt->bind_param("di", $newWallet, $user_id);
    $stmt->execute();

    // 6. Update Loan
    $newRemaining = $remaining - $payAmount;
    if ($newRemaining <= 0.01) {
        $newRemaining = 0.00; // Floating point precision safety
    }

    $status = ($newRemaining == 0.00) ? "paid" : $loan['status'];

    $stmt = $conn->prepare("UPDATE loans SET remaining_balance=?, status=? WHERE id=? AND user_id=?");
    $stmt->bind_param("dsii", $newRemaining, $status, $loan_id, $user_id);
    $stmt->execute();

    // 7. Mark Repayment Schedule Instalments as Paid
    // Note: This is a simple cascade logic. It marks the oldest unpaid instalments as paid
    // up to the amount paid. If a partial payment is made, this logic assumes it covers portions.
    // For a strict production system, you would handle partial instalment payments explicitly.
    $stmt_sch = $conn->prepare("SELECT id, amount_due FROM loan_repayment_schedule WHERE loan_id=? AND status != 'paid' ORDER BY instalment_number ASC FOR UPDATE");
    $stmt_sch->bind_param("i", $loan_id);
    $stmt_sch->execute();
    $schedules = $stmt_sch->get_result()->fetch_all(MYSQLI_ASSOC);

    $amount_to_allocate = $payAmount;
    foreach ($schedules as $sch) {
        if ($amount_to_allocate <= 0) break;
        
        $due = (float)$sch['amount_due'];
        if ($amount_to_allocate >= $due * 0.99) { // Using 0.99 for floating point leniency
            // Mark fully paid
            $up_sch = $conn->prepare("UPDATE loan_repayment_schedule SET status='paid', paid_at=NOW() WHERE id=?");
            $up_sch->bind_param("i", $sch['id']);
            $up_sch->execute();
            $amount_to_allocate -= $due;
        } else {
            // Partial payment of an instalment (we don't mark it 'paid' yet, but could add a "partially_paid" status if needed)
            $amount_to_allocate = 0;
            break;
        }
    }

    // 8. Insert Wallet Transaction
    $desc = "Loan payment (loan #{$loan_id})";
    $stmt = $conn->prepare("INSERT INTO wallet_transactions (user_id, type, amount, currency, description) VALUES (?, 'loan_repayment', ?, ?, ?)");
    $stmt->bind_param("idss", $user_id, $payAmount, $currency, $desc);
    $stmt->execute();

    // 9. Insert Loan Payment Record
    $stmt = $conn->prepare("INSERT INTO loan_payments (loan_id, user_id, amount, method, note) VALUES (?, ?, ?, 'wallet', 'App Repayment')");
    $stmt->bind_param("iid", $loan_id, $user_id, $payAmount);
    $stmt->execute();
    
    // 10. Audit Log & Notification
    $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, description) VALUES (?, 'LOAN_REPAYMENT', 'Paid KES {$payAmount} towards loan #{$loan_id}')");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    $notif_msg = "We received your payment of KES " . number_format($payAmount) . " for Loan #{$loan_id}. Remaining balance: KES " . number_format($newRemaining);
    $notif_sql = "INSERT INTO notifications (user_id, title, message) VALUES (?, 'Payment Received', ?)";
    $notif_stmt = $conn->prepare($notif_sql);
    $notif_stmt->bind_param("is", $user_id, $notif_msg);
    $notif_stmt->execute();

    $conn->commit();

    jsonSuccess([
        "paid" => $payAmount,
        "remaining_balance" => $newRemaining,
        "wallet_balance" => $newWallet,
        "loan_status" => $status
    ], "Payment of {$currency} " . number_format($payAmount, 2) . " successful.");

} catch (Exception $e) {
    $conn->rollback();
    jsonError($e->getMessage(), 500);
}
?>