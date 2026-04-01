<?php
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db = 'digital_loan_db';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    echo "Connected to MySQL host successfully!\n";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
