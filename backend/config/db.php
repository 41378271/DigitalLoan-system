<?php
/**
 * Database Configuration
 */
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'digital_loan_db');

// Enable strict MySQLi error reporting if the extension is available
if (function_exists('mysqli_report')) {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
}

// Only initialize mysqli if the class exists
$conn = null;
if (class_exists('mysqli')) {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            $conn = null;
        } else {
            $conn->set_charset("utf8mb4");
        }
    } catch (Exception $e) {
        $conn = null;
    }
}
?>