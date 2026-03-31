<?php
header("Content-Type: application/json");
session_start();
require_once '../../config/db.php';

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$doc_type = trim($_POST['doc_type'] ?? "");

if ($doc_type === "") {
    echo json_encode(["success" => false, "message" => "Document type required"]);
    exit;
}

if (!isset($_FILES['kyc_file']) || $_FILES['kyc_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(["success" => false, "message" => "File upload error"]);
    exit;
}

$file = $_FILES['kyc_file'];

// Limits
$maxSize = 5 * 1024 * 1024; // 5MB
if ($file['size'] > $maxSize) {
    echo json_encode(["success" => false, "message" => "File too large (max 5MB)"]);
    exit;
}

// Validate extension
$originalName = $file['name'];
$ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
$allowedExt = ["pdf", "jpg", "jpeg", "png"];

if (!in_array($ext, $allowedExt)) {
    echo json_encode(["success" => false, "message" => "Invalid file type. Use PDF/JPG/PNG"]);
    exit;
}

// Generate safe file name
$safeName = "kyc_" . $user_id . "_" . bin2hex(random_bytes(8)) . "." . $ext;

$uploadDir = __DIR__ . "/../../uploads/kyc/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$destination = $uploadDir . $safeName;

// Move upload
if (!move_uploaded_file($file['tmp_name'], $destination)) {
    echo json_encode(["success" => false, "message" => "Failed to save file"]);
    exit;
}

// Store path (relative)
$dbPath = "backend/uploads/kyc/" . $safeName;

// Optional: if user uploads again, keep history OR set old pending to replaced.
// We keep history by default.

$stmt = $conn->prepare("INSERT INTO kyc_documents (user_id, doc_type, file_name, file_path, status) VALUES (?, ?, ?, ?, 'pending')");
$stmt->bind_param("isss", $user_id, $doc_type, $originalName, $dbPath);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "KYC uploaded successfully ✅ Pending review"]);
} else {
    echo json_encode(["success" => false, "message" => "DB save failed"]);
}