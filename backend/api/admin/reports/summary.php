<?php
session_start();
require_once "../../../config/db.php";

header("Content-Type: application/json");

// check admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
  echo json_encode([
    "success" => false,
    "message" => "Not authorized"
  ]);
  exit;
}

// helper functions
function countQuery($conn, $sql){
  $res = $conn->query($sql);
  if(!$res) return 0;
  $row = $res->fetch_assoc();
  return (int)$row['c'];
}

function sumQuery($conn, $sql){
  $res = $conn->query($sql);
  if(!$res) return 0;
  $row = $res->fetch_assoc();
  return (float)($row['s'] ?? 0);
}

// summary
$summary = [
  "total_users" => countQuery($conn,"SELECT COUNT(*) c FROM users"),
  "total_kyc" => countQuery($conn,"SELECT COUNT(*) c FROM kyc_documents"),
  "pending_kyc" => countQuery($conn,"SELECT COUNT(*) c FROM kyc_documents WHERE status='pending'"),
  "approved_kyc" => countQuery($conn,"SELECT COUNT(*) c FROM kyc_documents WHERE status='approved'"),

  "total_loans" => countQuery($conn,"SELECT COUNT(*) c FROM loans"),
  "pending_loans" => countQuery($conn,"SELECT COUNT(*) c FROM loans WHERE status='pending'"),
  "approved_loans" => countQuery($conn,"SELECT COUNT(*) c FROM loans WHERE status='approved'"),

  "total_loan_amount" => sumQuery($conn,"SELECT SUM(amount) s FROM loans"),
  "approved_loan_amount" => sumQuery($conn,"SELECT SUM(amount) s FROM loans WHERE status='approved'"),
  "rejected_loan_amount" => sumQuery($conn,"SELECT SUM(amount) s FROM loans WHERE status='rejected'")
];

// latest loans
$latest_loans = [];
$res = $conn->query("
  SELECT l.id,l.amount,l.status,l.created_at,u.full_name,u.email
  FROM loans l
  JOIN users u ON u.id=l.user_id
  ORDER BY l.created_at DESC
  LIMIT 10
");

if($res){
  while($row=$res->fetch_assoc()){
    $latest_loans[]=$row;
  }
}

// monthly stats
$monthly=[];
$res=$conn->query("
  SELECT
    DATE_FORMAT(created_at,'%Y-%m') month,
    COUNT(*) total,
    SUM(amount) amount
  FROM loans
  GROUP BY month
  ORDER BY month DESC
  LIMIT 6
");

if($res){
  while($row=$res->fetch_assoc()){
    $monthly[]=$row;
  }
}

// output
echo json_encode([
  "success"=>true,
  "summary"=>$summary,
  "latest_loans"=>$latest_loans,
  "monthly"=>$monthly
]);