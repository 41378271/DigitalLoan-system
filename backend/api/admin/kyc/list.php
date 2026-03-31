<?php
session_start();
require_once '../../../config/db.php';
header("Content-Type: application/json");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["success" => false]);
    exit;
}

$sql = "SELECT k.id, k.doc_type, k.file_path, k.status, k.uploaded_at,
               u.full_name, u.email
        FROM kyc_documents k
        JOIN users u ON u.id = k.user_id
        ORDER BY k.uploaded_at DESC";

$result = $conn->query($sql);

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

echo json_encode(["success" => true, "rows" => $rows]);