<?php
require_once "../../config/db.php";
require_once "../../core/response.php";
require_once "../../core/auth_guard.php";

$user_id = requireBorrower();

$sql = "
    SELECT 
        id, 
        amount,
        term_months, 
        interest_rate,
        monthly_payment,
        total_repayable,
        remaining_balance AS remaining,
        purpose,
        status, 
        due_date,
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
    // Normalize floats for JSON
    $row['amount'] = (float)$row['amount'];
    $row['monthly_payment'] = (float)$row['monthly_payment'];
    $row['total_repayable'] = (float)$row['total_repayable'];
    $row['remaining'] = (float)$row['remaining'];
    $row['interest_rate'] = (float)$row['interest_rate'];
    
    // Calculate progress percentage
    if ($row['total_repayable'] > 0) {
        $paid = $row['total_repayable'] - $row['remaining'];
        $progress = round(($paid / $row['total_repayable']) * 100);
        $row['progress_percent'] = min(max($progress, 0), 100);
    } else {
        $row['progress_percent'] = 0;
    }
    
    $loans[] = $row;
}

jsonSuccess(["loans" => $loans]);
?>