<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'digital_loan_db';

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    echo "Connected to MySQL host successfully!\n";
    
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $db");
    echo "Database '$db' ensured.\n";
    
    $pdo->exec("USE $db");
    echo "Using database '$db'.\n";
    
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
