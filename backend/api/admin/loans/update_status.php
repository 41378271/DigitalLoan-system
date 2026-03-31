<?php
session_start();
require_once '../../../config/db.php';
header("Content-Type: application/json");

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    echo json_encode(["success" => false, "message" => "Not authorized"]);
    exit;
}

// Read JSON body (because your frontend uses fetch with application/json)
$input = json_decode(file_get_contents("php://input"), true);

$loan_id = (int)($input['loan_id'] ?? 0);
$status  = trim($input['status'] ?? "");

// allow only these statuses
$allowed = ["approved", "rejected", "pending"];
if ($loan_id <= 0 || !in_array($status, $allowed, true)) {
    echo json_encode(["success" => false, "message" => "Invalid input"]);
    exit;
}

$conn->begin_transaction();

try {
    // Get collateral status for this loan
    $stmtC = $conn->prepare("SELECT id, status FROM loan_collateral WHERE loan_id = ? LIMIT 1");
    $stmtC->bind_param("i", $loan_id);
    $stmtC->execute();
    $collateral = $stmtC->get_result()->fetch_assoc();

    if (!$collateral) {
        // No collateral found -> don't approve
        if ($status === "approved") {
            throw new Exception("Cannot approve: no collateral attached to this loan.");
        }
    } else {
        // If approving, enforce verification (recommended)
        if ($status === "approved" && $collateral['status'] !== "verified") {

            // OPTION A (STRICT): refuse approval until admin verifies collateral
            // throw new Exception("Cannot approve: collateral is not verified.");

            // OPTION B (AUTO): auto-verify collateral when approving (enabled)
            $stmtAuto = $conn->prepare("UPDATE loan_collateral SET status='verified' WHERE id=?");
            $stmtAuto->bind_param("i", $collateral['id']);
            $stmtAuto->execute();
        }
    }

    // Update loan status
    $stmt = $conn->prepare("UPDATE loans SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $loan_id);

    if (!$stmt->execute()) {
        throw new Exception("Failed to update loan status.");
    }

    $conn->commit();
    echo json_encode(["success" => true, "message" => "Status updated ✅"]);
    exit;

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
    exit;
}