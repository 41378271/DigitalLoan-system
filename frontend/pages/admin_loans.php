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
  <title>Admin - Loan Applications</title>
</head>
<body>

<h2>Admin - Loan Applications</h2>
<p>Welcome, <?php echo htmlspecialchars($_SESSION['name'] ?? 'Admin'); ?> </p>

<table border="1" cellpadding="8" cellspacing="0" id="loansTable">
  <thead>
    <tr>
      <th>ID</th>
      <th>Borrower</th>
      <th>Phone</th>
      <th>Amount</th>
      <th>Term (months)</th>

      <th>Collateral</th>
      <th>Value (KES)</th>
      <th>Proof</th>
      <th>Collateral Status</th>

      <th>Status</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody></tbody>
</table>

<p id="msg"></p>

<script>
async function loadLoans(){
  const res = await fetch("../../backend/api/admin/loans/list.php");
  const data = await res.json();

  const tbody = document.querySelector("#loansTable tbody");
  tbody.innerHTML = "";

  if (!data.success) {
    document.getElementById("msg").innerText = data.message || "Failed to load loans";
    return;
  }

  data.loans.forEach(loan => {
    const row = document.createElement("tr");

    const proofHtml = loan.proof_file_path
      ? `<a href="../../${loan.proof_file_path}" target="_blank">View</a>`
      : "-";

    const collateralType = loan.collateral_type ?? "-";
    const collateralValue = loan.collateral_value ?? "-";
    const collateralStatus = loan.collateral_status ?? "-";

    // Disable approve unless collateral is verified
    const approveDisabled = (collateralStatus !== "verified") ? "disabled" : "";

    // Disable verify button if no collateral record or already verified
    const verifyDisabled = (!loan.collateral_id || collateralStatus === "verified") ? "disabled" : "";

    row.innerHTML = `
      <td>${loan.id}</td>
      <td>${loan.full_name ?? "-"}</td>
      <td>${loan.phone ?? "-"}</td>
      <td>${loan.amount}</td>
      <td>${loan.term_months}</td>

      <td>${collateralType}</td>
      <td>${collateralValue}</td>
      <td>${proofHtml}</td>
      <td>${collateralStatus}</td>

      <td>${loan.status}</td>
      <td>
        <button type="button" onclick="verifyCollateral(${loan.collateral_id})" ${verifyDisabled}>
          Verify Collateral
        </button>

        <button type="button" onclick="updateLoan(${loan.id}, 'approved')" ${approveDisabled}>
          Approve
        </button>

        <button type="button" onclick="updateLoan(${loan.id}, 'rejected')">
          Reject
        </button>
      </td>
    `;

    tbody.appendChild(row);
  });
}

async function verifyCollateral(collateralId){
  if (!collateralId) return;

  const res = await fetch("../../backend/api/admin/loans/verify_collateral.php", {
    method: "POST",
    headers: {"Content-Type": "application/json"},
    body: JSON.stringify({ collateral_id: collateralId })
  });

  const data = await res.json();
  document.getElementById("msg").innerText = data.message || "Done";
  loadLoans();
}

async function updateLoan(id, status){
  const res = await fetch("../../backend/api/admin/loans/update_status.php", {
    method: "POST",
    headers: {"Content-Type": "application/json"},
    body: JSON.stringify({ loan_id: id, status: status })
  });

  const data = await res.json();
  document.getElementById("msg").innerText = data.message || "Updated";
  loadLoans(); 
}

loadLoans();
</script>

<?php include "../partials/chatbot_widget.php"; ?>
</body>
</html>