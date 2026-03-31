<?php
require_once "../../config/db.php";
require_once "../../core/response.php";

$identifier = trim($_POST['identifier'] ?? '');
$password   = $_POST['password'] ?? '';

if (empty($identifier) || empty($password)) {
    jsonError("Identifier and password are required.");
}

$sql = "SELECT id, full_name, role, password, is_active FROM users WHERE phone = ? OR email = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $identifier, $identifier);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Keep error vague for security (don't reveal if user exists)
    jsonError("Invalid credentials.");
}

$user = $result->fetch_assoc();

// Block deactivated accounts
if ((int)$user['is_active'] === 0) {
    jsonError("Your account has been deactivated. Contact support for assistance.");
}

if (!password_verify($password, $user['password'])) {
    // Log failed attempt if needed in the future
    jsonError("Invalid credentials.");
}

// Start session securely
session_start();
session_regenerate_id(true); // Prevent session fixation attacks

// Save session values
$_SESSION['user_id'] = $user['id'];
$_SESSION['role']    = $user['role'];
$_SESSION['name']    = $user['full_name'];

// Log Audit Event
$action = "USER_LOGIN";
$desc = "Successful login";
$ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
$stmt_audit = $conn->prepare("INSERT INTO audit_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
$stmt_audit->bind_param("isss", $user['id'], $action, $desc, $ip);
$stmt_audit->execute();

jsonSuccess([
    "user_id" => $user['id'],
    "role" => $user['role']
], "Login successful.");
?>