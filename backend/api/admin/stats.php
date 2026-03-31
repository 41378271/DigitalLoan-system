<?php
require_once "../../config/db.php";
require_once "../../core/response.php";
require_once "../../core/auth_guard.php";

requireAdmin();

$stats = [
    "total_users" => 0,
    "active_loans" => 0,
    "total_disbursed" => 0.00,
    "total_collected" => 0.00,
    "pending_kyc" => 0
];

try {
    // Total Users
    $res = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'borrower'");
    if ($res) $stats['total_users'] = (int)$res->fetch_assoc()['count'];

    // Active Loans
    $res = $conn->query("SELECT COUNT(*) as count FROM loans WHERE status IN ('approved', 'disbursed')");
    if ($res) $stats['active_loans'] = (int)$res->fetch_assoc()['count'];

    // Total Disbursed (from wallets or loans)
    $res = $conn->query("SELECT SUM(amount) as total FROM wallet_transactions WHERE type = 'loan_disbursement'");
    if ($res) $stats['total_disbursed'] = (float)$res->fetch_assoc()['total'];

    // Total Collected
    $res = $conn->query("SELECT SUM(amount) as total FROM loan_payments");
    if ($res) $stats['total_collected'] = (float)$res->fetch_assoc()['total'];

    // Pending KYC
    $res = $conn->query("SELECT COUNT(*) as count FROM kyc_documents WHERE status = 'pending'");
    if ($res) $stats['pending_kyc'] = (int)$res->fetch_assoc()['count'];

    jsonSuccess(["stats" => $stats]);

} catch (Exception $e) {
    jsonError("Failed to load dashboard stats.", 500);
}
?>
