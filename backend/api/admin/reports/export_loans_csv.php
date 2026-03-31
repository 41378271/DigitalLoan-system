<?php
session_start();
require_once "../../../config/db.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
  http_response_code(403);
  echo "Not authorized";
  exit;
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=loans_report.csv');

$out = fopen('php://output', 'w');
fputcsv($out, ["Loan ID","Borrower","Email","Amount","Status","Created At"]);

$sql = "SELECT l.id, u.full_name, u.email, l.amount, l.status, l.created_at
        FROM loans l
        JOIN users u ON u.id = l.user_id
        ORDER BY l.created_at DESC";
$res = $conn->query($sql);

if ($res) {
  while($r = $res->fetch_assoc()){
    fputcsv($out, [$r["id"], $r["full_name"], $r["email"], $r["amount"], $r["status"], $r["created_at"]]);
  }
}
fclose($out);