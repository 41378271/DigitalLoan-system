<?php
require_once __DIR__ . "/backend/config/db.php";

/**
 * Migration using PDO to ensure it works in CLI environments where mysqli might be problematic.
 */

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "Connected to database successfully.\n";

    // 1. Add missing columns to loans table
    $columnsToAdd = [
        'monthly_payment' => "ALTER TABLE loans ADD COLUMN monthly_payment DECIMAL(15,2) DEFAULT 0.00 AFTER interest_rate",
        'total_repayable' => "ALTER TABLE loans ADD COLUMN total_repayable DECIMAL(15,2) DEFAULT 0.00 AFTER monthly_payment",
        'remaining_balance' => "ALTER TABLE loans ADD COLUMN remaining_balance DECIMAL(15,2) DEFAULT 0.00 AFTER total_repayable",
        'due_date' => "ALTER TABLE loans ADD COLUMN due_date DATE DEFAULT NULL AFTER status",
        'admin_comment' => "ALTER TABLE loans ADD COLUMN admin_comment TEXT DEFAULT NULL AFTER purpose"
    ];

    foreach ($columnsToAdd as $col => $sql) {
        $check = $pdo->query("SHOW COLUMNS FROM loans LIKE '$col'");
        if ($check->rowCount() == 0) {
            $pdo->exec($sql);
            echo "Added column 'loans.$col'.\n";
        } else {
            echo "Column 'loans.$col' already exists.\n";
        }
    }

    // 2. Create repayment schedule table if not exists
    $createSchedule = "
        CREATE TABLE IF NOT EXISTS loan_repayment_schedule (
            id INT(11) NOT NULL AUTO_INCREMENT,
            loan_id INT(11) NOT NULL,
            instalment_number INT(11) NOT NULL,
            due_date DATE NOT NULL,
            amount_due DECIMAL(15,2) NOT NULL,
            principal_component DECIMAL(15,2) NOT NULL,
            interest_component DECIMAL(15,2) NOT NULL,
            status ENUM('pending','paid','overdue') NOT NULL DEFAULT 'pending',
            paid_at TIMESTAMP NULL DEFAULT NULL,
            PRIMARY KEY (id),
            KEY loan_id (loan_id),
            CONSTRAINT fk_sch_loan_pdo FOREIGN KEY (loan_id) REFERENCES loans (id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    $pdo->exec($createSchedule);
    echo "Table 'loan_repayment_schedule' is ready.\n";

    // 3. Update Enum for loans status if needed
    // This is trickier to check, so we just try to modify it to be sure
    $pdo->exec("ALTER TABLE loans MODIFY COLUMN status ENUM('submitted','under_review','approved','rejected','disbursed','active','paid','overdue') NOT NULL DEFAULT 'submitted'");
    echo "Loans status enum updated.\n";

    // 4. Seed Mock Data
    echo "Seeding mock data...\n";
    
    // Create a mock borrower if not exists
    $pass = password_hash('password123', PASSWORD_DEFAULT);
    $checkUser = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
    $checkUser->execute(['0712345678']);
    
    if ($checkUser->rowCount() == 0) {
        $pdo->prepare("INSERT INTO users (full_name, phone, email, password, role, is_active) VALUES (?, ?, ?, ?, 'borrower', 1)")
            ->execute(['John Borrower', '0712345678', 'john@example.com', $pass]);
        $borrowerId = $pdo->lastInsertId();
        
        // Create wallet for borrower
        $pdo->prepare("INSERT IGNORE INTO wallets (user_id, balance, currency) VALUES (?, 5000.00, 'KES')")
            ->execute([$borrowerId]);
            
        echo "Mock borrower 'John Borrower' created (Pass: password123).\n";
    }

    echo "Migration completed successfully.\n";

} catch (PDOException $e) {
    if ($e->getCode() == 1049) {
        echo "Database '" . DB_NAME . "' does not exist. Attempting to create...\n";
        try {
            $tmpPdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
            $tmpPdo->exec("CREATE DATABASE " . DB_NAME);
            echo "Database created. Please run migration again.\n";
        } catch (Exception $e2) {
            echo "Failed to create database: " . $e2->getMessage() . "\n";
        }
    } else {
        echo "Migration failed: " . $e->getMessage() . "\n";
    }
}
