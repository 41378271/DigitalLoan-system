<?php
require_once "../../config/db.php";
require_once "../../core/response.php";
require_once "../../core/auth_guard.php";
require_once "config.php";
require_once "access_token.php";

$user_id = requireLogin();

$phone = trim($_POST['phone'] ?? '');
$amount = (float)($_POST['amount'] ?? 0);

if (empty($phone) || $amount <= 0) {
    jsonError("Valid phone number and amount are required.");
}

// Ensure 254 format
if (preg_match("/^0(7\d{8}|1\d{8})$/", $phone)) {
    $phone = "254" . substr($phone, 1);
} elseif (preg_match("/^\+254(7\d{8}|1\d{8})$/", $phone)) {
    $phone = substr($phone, 1);
}

$accessToken = generateAccessToken($consumerKey, $consumerSecret);
if (!$accessToken) {
    jsonError("Failed to authenticate with M-Pesa.", 500);
}

$timestamp = date('YmdHis');
$password = base64_encode($shortcode . $passkey . $timestamp);
$url = "https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest";

$data = [
    "BusinessShortCode" => $shortcode,
    "Password" => $password,
    "Timestamp" => $timestamp,
    "TransactionType" => "CustomerPayBillOnline",
    "Amount" => $amount,
    "PartyA" => $phone,
    "PartyB" => $shortcode,
    "PhoneNumber" => $phone,
    "CallBackURL" => $callbackUrl,
    "AccountReference" => "DigitalLoanSystem",
    "TransactionDesc" => "Wallet Deposit"
];

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_HTTPHEADER, array(
    "Content-Type: application/json",
    "Authorization: Bearer $accessToken"
));
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($curl);
$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

$result = json_decode($response, true);

if ($http_code === 200 && isset($result['CheckoutRequestID'])) {
    $checkout = $result['CheckoutRequestID'];
    $merchant = $result['MerchantRequestID'];

    // For safety, ensure table exists, otherwise create it briefly (schema update strategy)
    $conn->query("CREATE TABLE IF NOT EXISTS mpesa_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        phone VARCHAR(20),
        amount DECIMAL(15,2),
        checkout_request_id VARCHAR(100),
        merchant_request_id VARCHAR(100),
        status VARCHAR(20) DEFAULT 'Pending',
        result_desc TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $stmt = $conn->prepare("INSERT INTO mpesa_requests (user_id, phone, amount, checkout_request_id, merchant_request_id, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
    $stmt->bind_param("isdss", $user_id, $phone, $amount, $checkout, $merchant);
    $stmt->execute();

    jsonSuccess([
        "checkout_id" => $checkout
    ], "STK Push sent successfully. Please check your phone to enter your PIN.");
} else {
    $error_msg = $result['errorMessage'] ?? "Failed to initiate M-Pesa request. Please try again.";
    jsonError($error_msg, 500, ["mpesa_response" => $result]);
}
?>