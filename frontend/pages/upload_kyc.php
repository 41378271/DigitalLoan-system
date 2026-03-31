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
    <title>Upload KYC</title>
</head>
<body>

<h2>Upload KYC Document</h2>

<form id="kycForm" enctype="multipart/form-data">
    <label>Document Type:</label>
    <select name="doc_type" required>
        <option value="">-- Select --</option>
        <option value="national_id">National ID</option>
        <option value="passport">Passport</option>
        <option value="drivers_license">Driver's License</option>
        <option value="proof_of_address">Proof of Address</option>
    </select>
    <br><br>

    <label>Select File (PDF/JPG/PNG):</label>
    <input type="file" name="kyc_file" required />
    <br><br>

    <button type="submit">Upload</button>
</form>

<p id="msg"></p>

<script>
document.getElementById("kycForm").addEventListener("submit", async function(e){
  e.preventDefault();

  const formData = new FormData(this);

  try {
    const res = await fetch("/digital-loan-system/backend/api/kyc/upload.php", {
      method: "POST",
      body: formData
    });

    const text = await res.text();
    console.log("STATUS:", res.status);
    console.log("RAW RESPONSE:", text);

    let data;
    try {
      data = JSON.parse(text);
    } catch (e) {
      document.getElementById("msg").innerText =
        "Server returned non-JSON. Open Console (F12) to see RAW RESPONSE.";
      return;
    }

    document.getElementById("msg").innerText = data.message;
  } catch (err) {
    console.log("FETCH ERROR:", err);
    document.getElementById("msg").innerText = "Request failed. Check Console (F12).";
  }
});
</script>
<?php include "../partials/chatbot_widget.php"; ?>
</body>
</html>