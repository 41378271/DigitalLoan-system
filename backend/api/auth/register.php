<?php
session_start();
require_once "../../config/db.php";
header("Content-Type: application/json");

$full_name = trim($_POST['full_name'] ?? '');
$email     = trim($_POST['email'] ?? '');
$phone     = trim($_POST['phone'] ?? '');
$password  = $_POST['password'] ?? '';

if ($full_name === '' || $password === '') {
  echo json_encode(["success"=>false, "message"=>"Full name and password required"]);
  exit;
}
if ($email === '' && $phone === '') {
  echo json_encode(["success"=>false, "message"=>"Provide email or phone"]);
  exit;
}

$role = "borrower"; 

$hash = password_hash($password, PASSWORD_DEFAULT);

if ($email !== '') {
  $stmt = $conn->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(["success"=>false, "message"=>"Email already registered"]);
    exit;
  }
}
if ($phone !== '') {
  $stmt = $conn->prepare("SELECT id FROM users WHERE phone=? LIMIT 1");
  $stmt->bind_param("s", $phone);
  $stmt->execute();
  if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(["success"=>false, "message"=>"Phone already registered"]);
    exit;
  }
}


$stmt = $conn->prepare("INSERT INTO users (full_name,email,phone,password,role) VALUES (?,?,?,?,?)");
$stmt->bind_param("sssss", $full_name, $email, $phone, $hash, $role);

if (!$stmt->execute()) {
  echo json_encode(["success"=>false, "message"=>"Registration failed"]);
  exit;
}

$user_id = $stmt->insert_id;


$_SESSION['user_id'] = $user_id;
$_SESSION['role']    = $role;
$_SESSION['name']    = $full_name;

echo json_encode([
  "success" => true,
  "message" => "Account created and logged in",
  "user_id" => $user_id,
  "role" => $role
]);