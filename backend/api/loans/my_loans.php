<?php
session_start();
require_once '../../config/db.php';

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// Detect correct remaining column
$remaining_column = null;

$possible_columns = [
    "remaining",
    "remaining_amount",
    "balance",
    "outstanding",
    "loan_balance"
];

foreach ($possible_columns as $col) {
    $check = $conn->query("SHOW COLUMNS FROM loans LIKE '$col'");
    if ($check && $check->num_rows > 0) {
        $remaining_column = $col;
        break;
    }
}

// If no remaining column found, fallback to amount
if (!$remaining_column) {
    $remaining_column = "amount";
}

// Build query dynamically
$sql = "
    SELECT 
        id, 
        amount, 
        {$remaining_column} AS remaining,
        term_months, 
        status, 
        created_at 
    FROM loans 
    WHERE user_id = ?
    ORDER BY created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

$loans = [];

while ($row = $result->fetch_assoc()) {

    //  Normalize remaining (fix float issues)
    $row['remaining'] = round((float)$row['remaining'], 2);

    $loans[] = $row;
}

echo json_encode([
    "success" => true,
    "loans" => $loans
]);
?>