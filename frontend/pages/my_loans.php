<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login_page.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <title>My Loans</title>
  <style>
    body{font-family: Arial, sans-serif; padding:20px;}
    h2{margin-top:0;}
    table{border-collapse:collapse;width:100%;max-width:1100px;}
    th,td{border:1px solid #ddd;padding:10px;text-align:left;}
    thead th{background:#f3f4f6;}
    .btn{padding:8px 10px;border-radius:6px;border:1px solid #ddd;background:#fff;cursor:pointer}
    .btn-primary{background:#111;color:#fff;border-color:#111}
    .btn-danger{background:#fff;color:#c0392b;border-color:#f1b0b7}
    .muted{color:#666;font-size:13px}

    #payDialog {
      position: fixed;
      left: 50%;
      top: 50%;
      transform: translate(-50%,-50%);
      background: #fff;
      border: 1px solid #ddd;
      padding: 18px;
      box-shadow: 0 8px 30px rgba(0,0,0,0.15);
      z-index: 9999;
      display:none;
      width: 320px;
      border-radius:8px;
    }

    #payDialog input{
      width:100%;padding:10px;margin-bottom:10px;
      border:1px solid #ccc;border-radius:6px;
    }

    #overlay {
      display:none;
      position:fixed;left:0;top:0;right:0;bottom:0;
      background:rgba(0,0,0,0.35);z-index:9998;
    }
  </style>
</head>
<body>

<h2>My Loans</h2>
<p class="muted">You can repay a loan using your wallet balance.</p>

<table id="loansTable">
  <thead>
    <tr>
      <th>ID</th>
      <th>Amount</th>
      <th>Remaining</th>
      <th>Term</th>
      <th>Status</th>
      <th>Date</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody>
    <tr><td colspan="7">Loading…</td></tr>
  </tbody>
</table>

<div id="overlay"></div>
<div id="payDialog">
  <strong>Pay Loan</strong><br><br>

  <div id="payLoanNote"></div><br>

  <input id="payAmount" type="number" step="0.01">

  Wallet: <span id="walletBalanceDisplay">-</span><br><br>

  <button id="payCancel" class="btn">Cancel</button>
  <button id="payConfirm" class="btn btn-primary">Pay</button>
</div>

<script>
const tbody = document.querySelector("#loansTable tbody");

let activeLoan = null;

async function fetchJSON(url, opts={}) {
  const r = await fetch(url, opts);
  return r.json();
}

function formatMoney(n){
  return "KES " + Number(n).toFixed(2);
}

async function loadLoans(){
  const data = await fetchJSON("../../backend/api/loans/my_loans.php");

  if(!data.success){
    tbody.innerHTML = "<tr><td colspan='7'>Error loading</td></tr>";
    return;
  }

  tbody.innerHTML = "";

  data.loans.forEach(l => {

    let remaining = Number(l.remaining ?? l.amount);

    // normalize
    if(remaining < 0.01) remaining = 0;

    const status = (l.status || "").toLowerCase();

    // clean label
    let label =
      status === "paid" ? "Paid" :
      status === "ongoing" ? "Ongoing" :
      status === "approved" ? "Approved" :
      status;

    let action = "—";

    // ✅ FIXED BUTTON LOGIC
    if (
      ["approved","active","ongoing","partially_paid","partially-paid"].includes(status)
      && remaining > 0
    ){
      action = `<button onclick="openPay(${l.id}, ${remaining})" class="btn btn-primary">Pay</button>`;
    }

    tbody.innerHTML += `
      <tr>
        <td>${l.id}</td>
        <td>${formatMoney(l.amount)}</td>
        <td>${formatMoney(remaining)}</td>
        <td>${l.term_months ?? ""}</td>
        <td>${label}</td>
        <td>${l.created_at ?? ""}</td>
        <td>${action}</td>
      </tr>
    `;
  });
}

async function openPay(id, remaining){
  activeLoan = {id, remaining};

  document.getElementById("payLoanNote").innerText =
    "Remaining: " + formatMoney(remaining);

  document.getElementById("payAmount").value = remaining;

  document.getElementById("overlay").style.display="block";
  document.getElementById("payDialog").style.display="block";

  const w = await fetchJSON("/digital-loan-system/backend/api/wallet/get_balance.php");

  if(w.success){
    document.getElementById("walletBalanceDisplay").innerText = formatMoney(w.balance);
  }
}

document.getElementById("payCancel").onclick = ()=>{
  document.getElementById("overlay").style.display="none";
  document.getElementById("payDialog").style.display="none";
};

document.getElementById("payConfirm").onclick = async ()=>{
  const amt = Number(document.getElementById("payAmount").value);

  if(!amt || amt<=0){
    alert("Invalid amount");
    return;
  }

  const form = new FormData();
  form.append("loan_id", activeLoan.id);
  form.append("amount", amt);

  const res = await fetch("/digital-loan-system/backend/api/wallet/pay_loan.php", {
    method:"POST",
    body:form
  });

  const data = await res.json();

  if(!data.success){
    alert(data.message);
    return;
  }

  alert("Payment successful");

  document.getElementById("overlay").style.display="none";
  document.getElementById("payDialog").style.display="none";

  loadLoans();
};

loadLoans();
</script>

<?php include "../partials/chatbot_widget.php"; ?>
</body>
</html>