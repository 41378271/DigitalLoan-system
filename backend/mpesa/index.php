<!DOCTYPE html>
<html>
<head>
    <title>M-Pesa STK Push</title>
</head>
<body>
    <h2>M-Pesa Payment</h2>
    <form action="stkpush.php" method="POST">
        <label>Phone Number (2547XXXXXXXX):</label><br>
        <input type="text" name="phone" required><br><br>

        <label>Amount:</label><br>
        <input type="number" name="amount" required><br><br>

        <button type="submit">Pay Now</button>
    </form>
</body>
</html>