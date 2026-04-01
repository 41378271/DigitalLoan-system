<?php
require_once __DIR__ . "/backend/config/db.php";

$errors = [];
$success = [];

// --- 1. Add missing columns to loans table ---
$cols = [
    "monthly_payment"  => "ALTER TABLE loans ADD COLUMN monthly_payment DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER interest_rate",
    "total_repayable"  => "ALTER TABLE loans ADD COLUMN total_repayable DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER monthly_payment",
    "due_date"         => "ALTER TABLE loans ADD COLUMN due_date DATE NULL AFTER total_repayable",
    "updated_at"       => "ALTER TABLE loans ADD COLUMN updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP AFTER created_at",
];

foreach ($cols as $col => $sql) {
    // check if column already exists
    $res = $conn->query("SHOW COLUMNS FROM loans LIKE '$col'");
    if ($res && $res->num_rows > 0) {
        $success[] = "Column 'loans.$col' already exists — skipped.";
    } else {
        if ($conn->query($sql)) {
            $success[] = "Column 'loans.$col' added successfully.";
        } else {
            $errors[] = "Error adding 'loans.$col': " . $conn->error;
        }
    }
}

// --- 2. Create loan_repayment_schedule if missing ---
$res = $conn->query("SHOW TABLES LIKE 'loan_repayment_schedule'");
if ($res && $res->num_rows > 0) {
    $success[] = "Table 'loan_repayment_schedule' already exists — skipped.";
} else {
    $createSchedule = "
        CREATE TABLE loan_repayment_schedule (
            id                 INT AUTO_INCREMENT PRIMARY KEY,
            loan_id            INT NOT NULL,
            instalment_number  INT NOT NULL,
            due_date           DATE NOT NULL,
            amount_due         DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            principal_component DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            interest_component DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            status             ENUM('pending','paid','overdue') NOT NULL DEFAULT 'pending',
            paid_at            TIMESTAMP NULL,
            created_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (loan_id) REFERENCES loans(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    if ($conn->query($createSchedule)) {
        $success[] = "Table 'loan_repayment_schedule' created successfully.";
    } else {
        $errors[] = "Error creating 'loan_repayment_schedule': " . $conn->error;
    }
}

// --- 3. Update status enum to include 'active' and 'paid' if missing ---
// MySQL MODIFY COLUMN to add new enum values safely
$newEnum = "ALTER TABLE loans MODIFY COLUMN status ENUM('submitted','under_review','approved','rejected','disbursed','active','partially_paid','paid','defaulted') NOT NULL DEFAULT 'submitted'";
if ($conn->query($newEnum)) {
    $success[] = "Loans status enum extended.";
} else {
    $errors[] = "Error updating loans status enum: " . $conn->error;
}

echo "=== Migration Results ===\n\n";
foreach ($success as $s) echo "✓ $s\n";
if (!empty($errors)) {
    echo "\n=== ERRORS ===\n";
    foreach ($errors as $e) echo "✗ $e\n";
}
?>
