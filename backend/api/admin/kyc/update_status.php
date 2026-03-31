<?php
session_start();
require_once '../../../config/db.php';
header("Content-Type: application/json");

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    echo json_encode(["success" => false, "message" => "Not authorized"]);
    exit;
}

$admin_id = (int)$_SESSION['user_id'];
$id = (int)($_POST['id'] ?? 0);
$status = trim($_POST['status'] ?? "");
$admin_comment = trim($_POST['admin_comment'] ?? "");

$allowed = ["approved", "rejected", "pending"];
if ($id <= 0 || !in_array($status, $allowed)) {
    echo json_encode(["success" => false, "message" => "Invalid input"]);
    exit;
}

/*  Update KYC record */
$stmt = $conn->prepare("
    UPDATE kyc_documents
    SET status = ?, 
        admin_comment = ?, 
        reviewed_at = NOW(), 
        reviewed_by = ?
    WHERE id = ?
");
$stmt->bind_param("ssii", $status, $admin_comment, $admin_id, $id);

if (!$stmt->execute()) {
    echo json_encode(["success" => false, "message" => "Update failed ❌"]);
    exit;
}

/*Get user_id of this KYC*/
$userStmt = $conn->prepare("SELECT user_id FROM kyc_documents WHERE id = ?");
$userStmt->bind_param("i", $id);
$userStmt->execute();
$userRow = $userStmt->get_result()->fetch_assoc();

$target_user_id = (int)($userRow['user_id'] ?? 0);

/* Insert notification*/
if ($target_user_id > 0) {

    $title = "KYC Update";

    $message = "Your KYC has been " . strtoupper($status) . ".";

    if (!empty($admin_comment)) {
        $message .= " Comment: " . $admin_comment;
    }

    $notifStmt = $conn->prepare("
        INSERT INTO notifications (user_id, title, message)
        VALUES (?, ?, ?)
    ");
    $notifStmt->bind_param("iss", $target_user_id, $title, $message);
    $notifStmt->execute();
}

echo json_encode([
    "success" => true,
    "message" => "KYC status updated ✅ Notification sent."
]);