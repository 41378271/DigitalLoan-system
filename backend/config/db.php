<?php
/**
 * Database Configuration
 */
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'digital_loan_db');

// Enable strict MySQLi error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    // Return JSON error if connection fails so the API doesn't just output raw HTML
    header("Content-Type: application/json");
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed. Please check your configuration."
    ]);
    exit;
}
?>