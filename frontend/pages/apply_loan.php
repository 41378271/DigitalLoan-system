<?php 
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login_page.php");
    exit;
}

require_once "../../backend/config/db.php";

$user_id = (int)$_SESSION['user_id'];

// Check latest KYC status
$stmt = $conn->prepare("
    SELECT status 
    FROM kyc_documents 
    WHERE user_id = ?
    ORDER BY uploaded_at DESC 
    LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

$kyc_status = $row['status'] ?? 'not_uploaded';

if ($kyc_status !== 'approved') {
    die("❌ You cannot apply for a loan until your KYC is approved.");
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Apply Loan</title>
</head>
<body>

<h2>Apply for Loan</h2>

<form id="loanForm" enctype="multipart/form-data">
  <label>Amount:</label><br>
  <input type="number" name="amount" required min="1" step="0.01"><br><br>

  <label>Term (months):</label><br>
  <input type="number" name="term_months" required min="1"><br><br>

  <hr>

  <h3>Collateral / Security (Required)</h3>

  <label>Collateral Type:</label><br>
  <select name="collateral_type" required>
    <option value="">-- Select --</option>
    <option value="phone">Phone</option>
    <option value="motorbike">Motorbike</option>
    <option value="laptop">Laptop</option>
    <option value="land_title">Land Title</option>
    <option value="guarantor">Guarantor</option>
    <option value="other">Other</option>
  </select><br><br>

  <label>Description:</label><br>
  <input type="text" name="collateral_description" required
         placeholder="Describe the security item clearly"><br><br>

  <label>Estimated Value (KES):</label><br>
  <input type="number" name="collateral_value" required min="1" step="0.01"><br><br>

  <label>Upload Proof (optional: PDF/JPG/PNG):</label><br>
  <input type="file" name="collateral_proof" accept=".pdf,.jpg,.jpeg,.png"><br><br>

  <button type="submit">Apply</button>
</form>

<p id="msg"></p>

<script>
document.getElementById("loanForm").addEventListener("submit", async function(e){
    e.preventDefault();

    const formData = new FormData(this);

    const res = await fetch("../../backend/api/loans/apply.php", {
        method: "POST",
        body: formData
    });

    const data = await res.json();
    document.getElementById("msg").innerText = data.message;

    // optional: clear form after success
    // if (data.success) this.reset();
});
</script>

<?php include "../partials/chatbot_widget.php"; ?>
</body>
</html>