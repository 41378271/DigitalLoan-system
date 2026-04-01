<?php
require_once "../../config/db.php";
require_once "../../core/response.php";

$full_name = trim($_POST['full_name'] ?? '');
$email     = trim($_POST['email'] ?? '');
$phone     = trim($_POST['phone'] ?? '');
$password  = $_POST['password'] ?? '';
$secret_key = $_POST['secret_key'] ?? '';

// You can change this secret key as needed for extra security
$ADMIN_SECRET = "KashFlowAdmin2026!";

if ($secret_key !== $ADMIN_SECRET) {
    jsonError("Invalid administrator secret key.");
}

if ($full_name === '' || $password === '' || $email === '') {
    jsonError("Full name, email, and password are required for admin accounts.");
}

$role = "admin"; 
$hash = password_hash($password, PASSWORD_DEFAULT);

// Check if email already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    jsonError("Email is already registered.");
}

$conn->begin_transaction();

try {
    // 1. Insert Admin User
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $full_name, $email, $phone, $hash, $role);
    
    if (!$stmt->execute()) {
        throw new Exception("Registration failed during admin creation.");
    }
    
    $user_id = $stmt->insert_id;
    
    // 2. Log Audit Event
    $action = "ADMIN_REGISTER";
    $desc = "New administrator registered via secret route";
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $stmt_audit = $conn->prepare("INSERT INTO audit_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
    $stmt_audit->bind_param("isss", $user_id, $action, $desc, $ip);
    $stmt_audit->execute();
    
    $conn->commit();
    
    jsonSuccess([
        "user_id" => $user_id,
        "role" => $role
    ], "Administrator account created successfully. You can now log in.");

} catch (Exception $e) {
    $conn->rollback();
    jsonError("Admin registration failed: " . $e->getMessage(), 500);
}
?>