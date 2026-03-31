<?php
session_start();
require_once '../../config/db.php';

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}

$user_id = (int)$_SESSION['user_id'];

$amount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0;
$term   = isset($_POST['term_months']) ? (int)$_POST['term_months'] : 0;

if ($amount <= 0 || $term <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid input"]);
    exit;
}

// Collateral (REQUIRED)

$collateral_type = trim($_POST['collateral_type'] ?? '');
$collateral_description = trim($_POST['collateral_description'] ?? '');
$collateral_value = isset($_POST['collateral_value']) ? (float)$_POST['collateral_value'] : 0;

if ($collateral_type === '' || $collateral_description === '' || $collateral_value <= 0) {
    echo json_encode(["success" => false, "message" => "Collateral details are required"]);
    exit;
}

// Optional rule: collateral must be >= loan amount (you can remove if you want)
if ($collateral_value < $amount) {
    echo json_encode(["success" => false, "message" => "Collateral value must be at least the loan amount"]);
    exit;
}

// Handle proof upload (OPTIONAL)

$proof_file_name = null;
$proof_file_path = null;

if (isset($_FILES['collateral_proof']) && $_FILES['collateral_proof']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['collateral_proof'];

    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxSize) {
        echo json_encode(["success" => false, "message" => "Collateral proof too large (max 5MB)"]);
        exit;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ["pdf", "jpg", "jpeg", "png"];
    if (!in_array($ext, $allowed)) {
        echo json_encode(["success" => false, "message" => "Proof must be PDF/JPG/PNG"]);
        exit;
    }

    $uploadDir = __DIR__ . "/../../uploads/collateral/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $safeName = "collateral_" . $user_id . "_" . bin2hex(random_bytes(8)) . "." . $ext;

    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $safeName)) {
        echo json_encode(["success" => false, "message" => "Failed to upload proof file"]);
        exit;
    }

    $proof_file_name = $file['name'];
    $proof_file_path = "backend/uploads/collateral/" . $safeName; // used for linking from frontend
}


// Insert loan + collateral (TRANSACTION)

$conn->begin_transaction();

try {
    // Insert into loans (your original table)
    $sql = "INSERT INTO loans (user_id, amount, term_months) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("idi", $user_id, $amount, $term);

    if (!$stmt->execute()) {
        throw new Exception("Failed to insert loan");
    }

    $loan_id = $conn->insert_id;

    // Insert collateral (requires loan_collateral table)
    $sql2 = "INSERT INTO loan_collateral
                (user_id, loan_id, collateral_type, description, estimated_value, proof_file_name, proof_file_path, status)
             VALUES
                (?, ?, ?, ?, ?, ?, ?, 'pledged')";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param(
        "iissdss",
        $user_id,
        $loan_id,
        $collateral_type,
        $collateral_description,
        $collateral_value,
        $proof_file_name,
        $proof_file_path
    );

    if (!$stmt2->execute()) {
        throw new Exception("Failed to insert collateral");
    }

    $conn->commit();

    echo json_encode(["success" => true, "message" => "Loan application submitted with collateral"]);
    exit;

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["success" => false, "message" => "Error applying for loan"]);
    exit;
}