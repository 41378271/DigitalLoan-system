<?php
session_start();
require_once "../../config/db.php";
header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
  echo json_encode(["success"=>false, "message"=>"Not logged in"]);
  exit;
}

$user_id = (int)$_SESSION['user_id'];

if (($_SESSION['role'] ?? '') === 'admin') {
  echo json_encode(["success"=>false, "message"=>"Admins cannot deactivate here"]);
  exit;
}

$stmt = $conn->prepare("UPDATE users SET is_active=0 WHERE id=?");
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {

  // logout user
  $_SESSION = [];
  if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
      $params["path"], $params["domain"],
      $params["secure"], $params["httponly"]
    );
  }
  session_destroy();

  echo json_encode(["success"=>true, "message"=>"Account deactivated. You are logged out."]);
} else {
  echo json_encode(["success"=>false, "message"=>"Failed to deactivate account"]);
}