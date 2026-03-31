<!DOCTYPE html>
<html>
<head>
  <title>Test Admin Review</title>
</head>
<body>
  <h2>Approve/Reject Loan (Test)</h2>

  <form method="POST" action="../../backend/api/admin/review_loan.php">
    <label>Loan ID:</label><br>
    <input name="loan_id" required><br><br>

    <label>Status:</label><br>
    <select name="status">
      <option value="approved">approved</option>
      <option value="rejected">rejected</option>
    </select><br><br>

    <button type="submit">Update</button>
  </form>
</body>
</html>