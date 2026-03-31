<?php
require_once "../../config/db.php";
require_once "../../core/response.php";
require_once "../../core/auth_guard.php";

$user_id = requireLogin();
$loan_id = isset($_GET['loan_id']) ? (int)$_GET['loan_id'] : 0;

if ($loan_id <= 0) {
    jsonError("Invalid loan ID.");
}

// Verify ownership or admin
$role = $_SESSION['role'] ?? 'borrower';
if ($role !== 'admin') {
    $check = $conn->prepare("SELECT id FROM loans WHERE id = ? AND user_id = ?");
    $check->bind_param("ii", $loan_id, $user_id);
    $check->execute();
    if ($check->get_result()->num_rows === 0) {
        jsonError("Loan not found or unauthorized.", 403);
    }
}

$sql = "SELECT instalment_number, due_date, amount_due, principal_component, interest_component, status, paid_at
        FROM loan_repayment_schedule 
        WHERE loan_id = ? 
        ORDER BY instalment_number ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $loan_id);
$stmt->execute();
$result = $stmt->get_result();

$schedule = [];
while ($row = $result->fetch_assoc()) {
    $row['amount_due'] = (float)$row['amount_due'];
    $row['principal_component'] = (float)$row['principal_component'];
    $row['interest_component'] = (float)$row['interest_component'];
    $schedule[] = $row;
}

jsonSuccess(["schedule" => $schedule]);
?>
