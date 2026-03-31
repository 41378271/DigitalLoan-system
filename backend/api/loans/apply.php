<?php
require_once "../../config/db.php";
require_once "../../core/response.php";
require_once "../../core/auth_guard.php";
require_once "../../core/loan_calculator.php";

$user_id = requireBorrower();

$amount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0;
$term   = isset($_POST['term_months']) ? (int)$_POST['term_months'] : 0;
$purpose = trim($_POST['purpose'] ?? '');

if ($amount <= 0 || $term <= 0) {
    jsonError("Invalid loan amount or term.");
}

// Collateral validation
$collateral_type = trim($_POST['collateral_type'] ?? '');
$collateral_description = trim($_POST['collateral_description'] ?? '');
$collateral_value = isset($_POST['collateral_value']) ? (float)$_POST['collateral_value'] : 0;

if ($collateral_type === '' || $collateral_description === '' || $collateral_value <= 0) {
    jsonError("Collateral details are required.");
}

if ($collateral_value < $amount) {
    jsonError("Collateral value must be at least the loan amount.");
}

// Upload proof if present
$proof_file_name = null;
$proof_file_path = null;

if (isset($_FILES['collateral_proof']) && $_FILES['collateral_proof']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['collateral_proof'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    if ($file['size'] > $maxSize) {
        jsonError("Collateral proof is too large (max 5MB).");
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ["pdf", "jpg", "jpeg", "png"];
    if (!in_array($ext, $allowed)) {
        jsonError("Proof must be a PDF, JPG, or PNG file.");
    }

    $uploadDir = __DIR__ . "/../../uploads/collateral/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $safeName = "collateral_" . $user_id . "_" . bin2hex(random_bytes(8)) . "." . $ext;

    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $safeName)) {
        jsonError("Failed to upload proof file.");
    }

    $proof_file_name = $file['name'];
    $proof_file_path = "backend/uploads/collateral/" . $safeName;
}

// Perform Mathematics
$loanData = LoanCalculator::calculateEMI($amount, $term);

$conn->begin_transaction();

try {
    // 1. Insert Loan
    $sql = "INSERT INTO loans (user_id, amount, term_months, interest_rate, monthly_payment, total_repayable, remaining_balance, purpose, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'submitted')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ididddds", 
        $user_id, 
        $amount, 
        $term, 
        $loanData['interest_rate_pct'],
        $loanData['monthly_payment'],
        $loanData['total_repayable'],
        $loanData['total_repayable'], // remaining starts at full total payable
        $purpose
    );

    if (!$stmt->execute()) {
        throw new Exception("Failed to save loan application.");
    }
    
    $loan_id = $conn->insert_id;

    // 2. Insert Collateral
    $sql2 = "INSERT INTO loan_collateral (user_id, loan_id, collateral_type, description, estimated_value, proof_file_name, proof_file_path, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'pledged')";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("iissdss",
        $user_id,
        $loan_id,
        $collateral_type,
        $collateral_description,
        $collateral_value,
        $proof_file_name,
        $proof_file_path
    );

    if (!$stmt2->execute()) {
        throw new Exception("Failed to save collateral information.");
    }

    // 3. Generate and Insert Amortization Schedule
    $schedule = LoanCalculator::generateSchedule($amount, $term);
    
    $sch_sql = "INSERT INTO loan_repayment_schedule (loan_id, instalment_number, due_date, amount_due, principal_component, interest_component)
                VALUES (?, ?, ?, ?, ?, ?)";
    $sch_stmt = $conn->prepare($sch_sql);
    
    foreach ($schedule as $instalment) {
        $sch_stmt->bind_param("iisddd", 
            $loan_id,
            $instalment['instalment_number'],
            $instalment['due_date'],
            $instalment['amount_due'],
            $instalment['principal_component'],
            $instalment['interest_component']
        );
        if (!$sch_stmt->execute()) {
            throw new Exception("Failed to generate repayment schedule.");
        }
    }

    // 4. Create Notification
    $msg = "Your loan application for KES " . number_format($amount) . " has been submitted and is under review.";
    $notif_sql = "INSERT INTO notifications (user_id, title, message) VALUES (?, 'Loan Submitted', ?)";
    $notif_stmt = $conn->prepare($notif_sql);
    $notif_stmt->bind_param("is", $user_id, $msg);
    $notif_stmt->execute();

    $conn->commit();

    jsonSuccess([
        "loan_id" => $loan_id,
        "math" => $loanData
    ], "Loan application submitted successfully.");

} catch (Exception $e) {
    $conn->rollback();
    jsonError("Error applying for loan: " . $e->getMessage(), 500);
}
?>