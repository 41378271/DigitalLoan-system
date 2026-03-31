<?php
require_once '../../config/db.php';
header("Content-Type: application/json");

// read JSON
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

// accept JSON or normal POST
$loan_id = $data['loan_id'] ?? ($_POST['loan_id'] ?? null);
$status  = $data['status']  ?? ($_POST['status']  ?? null);

if ($loan_id === null || $status === null) {
    echo json_encode(["success" => false, "message" => "loan_id and status are required"]);
    exit;
}

$loan_id = (int)$loan_id;

if ($loan_id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid loan_id"]);
    exit;
}

if (!in_array($status, ["approved", "rejected"])) {
    echo json_encode(["success" => false, "message" => "Invalid status"]);
    exit;
}

$stmt = $conn->prepare("UPDATE loans SET status=? WHERE id=?");
$stmt->bind_param("si", $status, $loan_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(["success" => true, "message" => "Loan updated"]);
} else {
    echo json_encode(["success" => false, "message" => "Loan not found or no change"]);
}