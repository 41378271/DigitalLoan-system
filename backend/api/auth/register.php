<?php
require_once "../../config/db.php";
require_once "../../core/response.php";

$full_name = trim($_POST['full_name'] ?? '');
$email     = trim($_POST['email'] ?? '');
$phone     = trim($_POST['phone'] ?? '');
$password  = $_POST['password'] ?? '';

if ($full_name === '' || $password === '') {
    jsonError("Full name and password are required.");
}

if ($email === '' && $phone === '') {
    jsonError("Please provide an email or phone number.");
}

// Basic phone validation (Kenyan format starting with 07, 01 or 254)
if ($phone !== '' && !preg_match("/^(07|01|254)\d{8}$/", preg_replace('/[^0-9]/', '', $phone))) {
    jsonError("Invalid phone number format. Please enter a valid Kenyan number.");
}

$role = "borrower"; 
$hash = password_hash($password, PASSWORD_DEFAULT);

if ($email !== '') {
    $stmt = $conn->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        jsonError("Email is already registered.");
    }
}

if ($phone !== '') {
    $stmt = $conn->prepare("SELECT id FROM users WHERE phone=? LIMIT 1");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        jsonError("Phone number is already registered.");
    }
}

$conn->begin_transaction();

try {
    // 1. Insert User
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $full_name, $email, $phone, $hash, $role);
    
    if (!$stmt->execute()) {
        throw new Exception("Registration failed during user creation.");
    }
    
    $user_id = $stmt->insert_id;
    
    // 2. Auto-create Wallet
    $stmt_wallet = $conn->prepare("INSERT INTO wallets (user_id, balance, currency) VALUES (?, 0.00, 'KES')");
    $stmt_wallet->bind_param("i", $user_id);
    if (!$stmt_wallet->execute()) {
        throw new Exception("Registration failed during wallet creation.");
    }
    
    // 3. Log Audit Event
    $action = "USER_REGISTER";
    $desc = "New borrower registered";
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $stmt_audit = $conn->prepare("INSERT INTO audit_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
    $stmt_audit->bind_param("isss", $user_id, $action, $desc, $ip);
    $stmt_audit->execute();
    
    $conn->commit();
    
    // Auto-login
    session_start();
    session_regenerate_id(true); // Prevent session fixation
    $_SESSION['user_id'] = $user_id;
    $_SESSION['role']    = $role;
    $_SESSION['name']    = $full_name;
    
    jsonSuccess([
        "user_id" => $user_id,
        "role" => $role
    ], "Account created and logged in successfully.");

} catch (Exception $e) {
    $conn->rollback();
    jsonError("Registration failed: " . $e->getMessage(), 500);
}
?>