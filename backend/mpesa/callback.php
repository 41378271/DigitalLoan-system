<?php
include 'config.php';

// Get raw callback data
$callbackJSON = file_get_contents('php://input');
file_put_contents("mpesa_callback_log.json", $callbackJSON . PHP_EOL, FILE_APPEND);

$data = json_decode($callbackJSON, true);

$resultCode = $data['Body']['stkCallback']['ResultCode'] ?? null;
$resultDesc = $data['Body']['stkCallback']['ResultDesc'] ?? null;
$checkoutRequestID = $data['Body']['stkCallback']['CheckoutRequestID'] ?? null;

$status = ($resultCode == 0) ? "Success" : "Failed";

// Update payment record
$stmt = $conn->prepare("UPDATE payments SET result_code=?, result_desc=?, status=? WHERE checkout_request_id=?");
$stmt->bind_param("ssss", $resultCode, $resultDesc, $status, $checkoutRequestID);
$stmt->execute();

// Respond to Safaricom
header('Content-Type: application/json');
echo json_encode(["ResultCode" => 0, "ResultDesc" => "Accepted"]);
?>