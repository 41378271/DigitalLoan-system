<?php
session_start();
require_once '../../config/db.php';

header("Content-Type: application/json");

$identifier = $_POST['identifier'] ?? '';
$password   = $_POST['password'] ?? '';

if (!$identifier || !$password) {
    echo json_encode(["success" => false, "message" => "Identifier and password required"]);
    exit;
}

$sql = "SELECT * FROM users WHERE (phone = ? OR email = ?) AND is_active = 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $identifier, $identifier);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "User not found"]);
    exit;
}

$user = $result->fetch_assoc();
// Block deactivated accounts
if (isset($user['is_active']) && (int)$user['is_active'] === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Your account has been deactivated. Contact admin."
    ]);
    exit;
}

if (!password_verify($password, $user['password'])) {
    echo json_encode(["success" => false, "message" => "Invalid password"]);
    exit;
}

//  Save session values
$_SESSION['user_id'] = $user['id'];
$_SESSION['role']    = $user['role'];
$_SESSION['name']    = $user['full_name'];

echo json_encode([
    "success" => true,
    "message" => "Login successful",
    "user_id" => $user['id'],
    "role" => $user['role']
]);