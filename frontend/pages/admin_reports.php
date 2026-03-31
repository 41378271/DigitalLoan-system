<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
  header("Location: login_page.php");
  exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title><b>Admin Reports<b></title>
  <style>
   
    body{
      font-family: Arial, sans-serif;
      padding: 20px;
      background: #fff;
    }

    .container{
      max-width: 1200px;
      margin: 0 auto;
    }

    h2{ margin: 0 0 10px 0; }
    h3{ margin-top: 22px; }

    .actions{
      margin: 12px 0 18px 0;
      display:flex;
      gap:12px;
      flex-wrap:wrap;
      align-items:center;
    }

    .actions a{
      text-decoration:none;
      border:1px solid #ddd;
      padding:8px 12px;
      border-radius:10px;
      background:#f8f9fa;
      color:#000;
      display:inline-flex;
      align-items:center;
      gap:6px;
    }

    .actions a:hover{
      background:#eef1f4;
    }

    .cards{
      display:grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap:12px;
      margin: 15px 0;
    }

    .card{
      border:1px solid #ddd;
      padding:14px;
      border-radius:12px;
      background:#f8f9fa;
      box-shadow:0 2px 4px rgba(0,0,0,0.08);
    }

    table{
      border-collapse:collapse;
      width:100%;
      margin-top:12px;
      background:#fff;
    }

    th,td{
      border:1px solid #ddd;
      padding:10px;
      text-align:left;
    }

    thead th{
      background:#f3f4f6;
    }

    tbody tr:hover{
      background:#fafafa;
    }

   
    .table-wrap{
      overflow-x:auto;
    }
  </style>
</head>
<body>

<div class="container">

<h2>Reports</h2>

<div class="actions">
  <a href="admin_dashboard.php">⬅ Back to Dashboard</a>
  <a href="/digital-loan-system/backend/api/admin/reports/export_loans_csv.php" target="_blank">⬇ Export Loans (CSV)</a>
  <a href="/digital-loan-system/backend/api/admin/reports/export_kyc_csv.php" target="_blank">⬇ Export KYC (CSV)</a>
</div>

<div class="cards" id="cards">
  <div class="card">Loading summary...</div>
</div>

<h3>Latest Loans</h3>
<div class="table-wrap">
<table>
  <thead>
    <tr>
      <th>ID</th>
      <th>Borrower</th>
      <th>Amount</th>
      <th>Status</th>
      <th>Date</th>
    </tr>
  </thead>
  <tbody id="loansBody"></tbody>
</table>
</div>

<h3>Monthly Statistics</h3>
<div class="table-wrap">
<table>
  <thead>
    <tr>
      <th>Month</th>
      <th>Total Loans</th>
      <th>Total Amount</th>
    </tr>
  </thead>
  <tbody id="monthlyBody"></tbody>
</table>
</div>

<script>
function fmtMoney(v){
  const n = Number(v || 0);
  return n.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

async function loadReports(){
  const res = await fetch("/digital-loan-system/backend/api/admin/reports/summary.php");
  const data = await res.json();

  if(!data.success){
    document.getElementById("cards").innerHTML = "<div class='card'>Not authorized / error</div>";
    return;
  }

  const s = data.summary;

  document.getElementById("cards").innerHTML = `
    <div class="card"><b>Total Users</b><br>${s.total_users}</div>
    <div class="card"><b>Total KYC</b><br>${s.total_kyc}</div>
    <div class="card"><b>Pending KYC</b><br>${s.pending_kyc}</div>
    <div class="card"><b>Approved KYC</b><br>${s.approved_kyc}</div>

    <div class="card"><b>Total Loans</b><br>${s.total_loans}</div>
    <div class="card"><b>Pending Loans</b><br>${s.pending_loans}</div>
    <div class="card"><b>Approved Loans</b><br>${s.approved_loans}</div>

    <div class="card"><b>Total Loan Amount</b><br>${fmtMoney(s.total_loan_amount)}</div>
    <div class="card"><b>Approved Amount</b><br>${fmtMoney(s.approved_loan_amount)}</div>
    <div class="card"><b>Rejected Amount</b><br>${fmtMoney(s.rejected_loan_amount)}</div>
  `;

  // Latest loans
  const tbody = document.getElementById("loansBody");
  tbody.innerHTML = "";

  data.latest_loans.forEach(r => {
    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td>${r.id}</td>
      <td>${r.full_name} (${r.email})</td>
      <td>${fmtMoney(r.amount)}</td>
      <td>${r.status}</td>
      <td>${r.created_at}</td>
    `;
    tbody.appendChild(tr);
  });

  // Monthly stats
  const monthlyBody = document.getElementById("monthlyBody");
  monthlyBody.innerHTML = "";

  if (Array.isArray(data.monthly) && data.monthly.length) {
    data.monthly.forEach(m => {
      monthlyBody.innerHTML += `
        <tr>
          <td>${m.month}</td>
          <td>${m.total}</td>
          <td>${fmtMoney(m.amount)}</td>
        </tr>
      `;
    });
  } else {
    monthlyBody.innerHTML = `<tr><td colspan="3">No monthly data found.</td></tr>`;
  }
}

loadReports();
</script>

</div> <!-- /.container -->

</body>
</html>