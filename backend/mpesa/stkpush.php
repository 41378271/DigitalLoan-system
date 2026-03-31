<?php
include 'config.php';
include 'access_token.php';

$phone = $_POST['phone'];
$amount = $_POST['amount'];

$accessToken = generateAccessToken($consumerKey, $consumerSecret);

$timestamp = date('YmdHis');
$password = base64_encode($shortcode . $passkey . $timestamp);

$url = "https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest";

$data = array(
    "BusinessShortCode" => $shortcode,
    "Password" => $password,
    "Timestamp" => $timestamp,
    "TransactionType" => "CustomerPayBillOnline",
    "Amount" => $amount,
    "PartyA" => $phone,
    "PartyB" => $shortcode,
    "PhoneNumber" => $phone,
    "CallBackURL" => $callbackUrl,
    "AccountReference" => "ProjectPayment",
    "TransactionDesc" => "System Payment"
);

$payload = json_encode($data);

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_HTTPHEADER, array(
    "Content-Type: application/json",
    "Authorization: Bearer $accessToken"
));
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($curl);
curl_close($curl);

$result = json_decode($response, true);

// Save initial request
if (isset($result['CheckoutRequestID'])) {
    $checkout = $result['CheckoutRequestID'];
    $merchant = $result['MerchantRequestID'];

    $stmt = $conn->prepare("INSERT INTO payments (phone, amount, checkout_request_id, merchant_request_id, status) VALUES (?, ?, ?, ?, ?)");
    $status = "Pending";
    $stmt->bind_param("sdsss", $phone, $amount, $checkout, $merchant, $status);
    $stmt->execute();

    echo "STK Push sent successfully. Check your phone.";
} else {
    echo "Error sending STK Push.<br>";
    echo "<pre>";
    print_r($result);
    echo "</pre>";
}
?>