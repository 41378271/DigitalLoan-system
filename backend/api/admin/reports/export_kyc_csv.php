<?php
session_start();
require_once "../../../config/db.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
  http_response_code(403);
  echo "Not authorized";
  exit;
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=kyc_report.csv');

$out = fopen('php://output', 'w');
fputcsv($out, ["KYC ID","User","Email","Doc Type","Status","Uploaded At","Reviewed At","Reviewed By"]);

$sql = "SELECT k.id, u.full_name, u.email, k.doc_type, k.status, k.uploaded_at, k.reviewed_at, k.reviewed_by
        FROM kyc_documents k
        JOIN users u ON u.id = k.user_id
        ORDER BY k.uploaded_at DESC";
$res = $conn->query($sql);

if ($res) {
  while($r = $res->fetch_assoc()){
    fputcsv($out, [
      $r["id"], $r["full_name"], $r["email"], $r["doc_type"], $r["status"],
      $r["uploaded_at"], $r["reviewed_at"], $r["reviewed_by"]
    ]);
  }
}
fclose($out);