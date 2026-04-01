<?php
require_once __DIR__ . "/backend/config/db.php";

$tables = ['loans', 'loan_collateral', 'loan_repayment_schedule'];

foreach ($tables as $table) {
    echo "--- $table ---\n";
    $result = $conn->query("DESCRIBE $table");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo $row['Field'] . " - " . $row['Type'] . "\n";
        }
    } else {
        echo "Table not found.\n";
    }
}
?>
