<?php
require_once "../../../config/db.php";
require_once "../../../core/response.php";
require_once "../../../core/auth_guard.php";

$admin_id = requireAdmin();

$json = file_get_contents("php://input");
$input = empty($json) ? $_POST : json_decode($json, true);

$loan_id = (int)($input['loan_id'] ?? 0);
$status  = trim($input['status'] ?? "");
$comment = trim($input['comment'] ?? "");

$allowed = ["approved", "rejected", "under_review", "disbursed"];

if ($loan_id <= 0 || !in_array($status, $allowed, true)) {
    jsonError("Invalid loan ID or status.");
}

$conn->begin_transaction();

try {
    // 1. Lock Loan for update to prevent double disbursement
    $stmt = $conn->prepare("SELECT user_id, amount, status FROM loans WHERE id = ? FOR UPDATE");
    $stmt->bind_param("i", $loan_id);
    $stmt->execute();
    $loan = $stmt->get_result()->fetch_assoc();

    if (!$loan) {
        throw new Exception("Loan not found.");
    }
    
    if ($loan['status'] === $status) {
        throw new Exception("Loan is already marked as {$status}.");
    }
    
    // Safety check against double approval/disbursement
    if (in_array($loan['status'], ['approved', 'disbursed']) && $status === 'approved') {
        throw new Exception("Loan has already been approved and disbursed.");
    }

    $borrower_id = (int)$loan['user_id'];
    $amount = (float)$loan['amount'];

    // 2. Perform actions based on new status
    if ($status === 'approved') {
        // Disburse funds directly to Wallet
        $stmtW = $conn->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id = ?");
        $stmtW->bind_param("di", $amount, $borrower_id);
        $stmtW->execute();
        
        // Record Wallet Transaction
        $desc = "Loan disbursement (Loan #{$loan_id})";
        $stmtTx = $conn->prepare("INSERT INTO wallet_transactions (user_id, type, amount, currency, description) VALUES (?, 'loan_disbursement', ?, 'KES', ?)");
        $stmtTx->bind_param("ids", $borrower_id, $amount, $desc);
        $stmtTx->execute();
        
        // Update notification message
        $notif_msg = "Great news! Your loan of KES " . number_format($amount) . " has been approved and the funds have been disbursed to your wallet.";
        
        // Auto-verify collateral if not already verified
        $stmtCol = $conn->prepare("UPDATE loan_collateral SET status = 'verified' WHERE loan_id = ? AND status = 'pledged'");
        $stmtCol->bind_param("i", $loan_id);
        $stmtCol->execute();
        
    } elseif ($status === 'rejected') {
        $notif_msg = "Your loan application for KES " . number_format($amount) . " was rejected.";
        if (!empty($comment)) {
            $notif_msg .= " Reason: " . $comment;
        }
    } else {
        $notif_msg = "Your loan application status has been updated to: " . str_replace("_", " ", $status) . ".";
    }

    // 3. Update Loan Status
    $stmtU = $conn->prepare("UPDATE loans SET status = ?, admin_comment = ? WHERE id = ?");
    $stmtU->bind_param("ssi", $status, $comment, $loan_id);
    $stmtU->execute();

    // 4. Create Notification
    $stmtN = $conn->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, 'Loan Status Updated', ?)");
    $stmtN->bind_param("is", $borrower_id, $notif_msg);
    $stmtN->execute();
    
    // 5. Audit Log
    $action_text = strtoupper($status);
    $stmtA = $conn->prepare("INSERT INTO audit_logs (user_id, action, description) VALUES (?, ?, ?)");
    $audit_desc = "Admin marked Loan #{$loan_id} as {$status}";
    $stmtA->bind_param("iss", $admin_id, $action_text, $audit_desc);
    $stmtA->execute();

    $conn->commit();
    jsonSuccess([], "Loan status updated to {$status} successfully.");

} catch (Exception $e) {
    $conn->rollback();
    jsonError($e->getMessage(), 500);
}
?>