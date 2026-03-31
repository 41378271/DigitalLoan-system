<?php
session_start();
require_once "../../config/db.php";

header("Content-Type: application/json");

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    echo json_encode(["success"=>false,"message"=>"Unauthorized"]);
    exit;
}

$sql = "
SELECT 
    l.id,
    l.amount,
    l.term_months,
    l.status,

    u.full_name,
    u.phone,

    c.id AS collateral_id,
    c.collateral_type,
    c.description,
    c.estimated_value AS collateral_value,
    c.proof_file_path,
    c.status AS collateral_status

FROM loans l
JOIN users u ON u.id = l.user_id
LEFT JOIN loan_collateral c ON c.loan_id = l.id

ORDER BY l.id DESC
";

$result = $conn->query($sql);

$loans = [];

while($row = $result->fetch_assoc()){
    $loans[] = $row;
}

echo json_encode([
    "success"=>true,
    "loans"=>$loans
]);