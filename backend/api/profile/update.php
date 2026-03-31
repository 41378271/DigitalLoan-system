<?php
require_once "../../config/db.php";
require_once "../../core/response.php";
require_once "../../core/auth_guard.php";

$user_id = requireLogin();

$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');

$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';

if (empty($full_name) || empty($phone)) {
    jsonError("Full name and phone number are required.");
}

// Phone format validation
if ($phone !== '' && !preg_match("/^(07|01|254)\d{8}$/", preg_replace('/[^0-9]/', '', $phone))) {
    jsonError("Invalid phone number format.");
}

$conn->begin_transaction();

try {
    // Check conflicts
    $stmt = $conn->prepare("SELECT id, password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    // Email conflict checks
    if (!empty($email)) {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check->bind_param("si", $email, $user_id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            throw new Exception("Email is already registered by someone else.");
        }
    }
    
    // Phone conflict checks
    $check = $conn->prepare("SELECT id FROM users WHERE phone = ? AND id != ?");
    $check->bind_param("si", $phone, $user_id);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        throw new Exception("Phone number is already registered by someone else.");
    }
    
    // Password Update Logic
    $password_sql = "";
    $types = "sssi";
    $params = [$full_name, $email, $phone];
    
    if (!empty($new_password)) {
        if (empty($current_password)) {
            throw new Exception("You must provide your current password to set a new one.");
        }
        if (!password_verify($current_password, $user['password'])) {
            throw new Exception("Incorrect current password.");
        }
        
        $password_sql = ", password = ?";
        $hash = password_hash($new_password, PASSWORD_DEFAULT);
        $types = "ssssi";
        $params[] = $hash;
    }
    
    $params[] = $user_id;

    $sql = "UPDATE users SET full_name = ?, email = ?, phone = ? {$password_sql} WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    // Dynamic bind_param execution
    $stmt->bind_param($types, ...$params);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to update profile.");
    }

    $conn->commit();
    
    // Update session
    $_SESSION['name'] = $full_name;

    jsonSuccess([], "Profile updated successfully.");

} catch (Exception $e) {
    $conn->rollback();
    jsonError($e->getMessage());
}
?>
