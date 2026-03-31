<?php
session_start();
require_once '../../../config/db.php';
header("Content-Type: application/json");

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    echo json_encode(["success" => false, "message" => "Not authorized"]);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);
$collateral_id = (int)($input['collateral_id'] ?? 0);

if ($collateral_id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid collateral id"]);
    exit;
}

$stmt = $conn->prepare("UPDATE loan_collateral SET status='verified' WHERE id=?");
$stmt->bind_param("i", $collateral_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Collateral verified ✅"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to verify collateral ❌"]);
}