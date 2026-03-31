<?php
require_once "../../config/db.php";

// Log raw callback data for debugging
$callbackJSON = file_get_contents('php://input');
if (!is_dir(__DIR__ . "/../../logs")) {
    mkdir(__DIR__ . "/../../logs", 0777, true);
}
file_put_contents(__DIR__ . "/../../logs/mpesa_callback.log", date('[Y-m-d H:i:s] ') . $callbackJSON . PHP_EOL, FILE_APPEND);

$data = json_decode($callbackJSON, true);

if (!$data || !isset($data['Body']['stkCallback'])) {
    http_response_code(400);
    exit("Invalid JSON");
}

$callbackData = $data['Body']['stkCallback'];
$resultCode = $callbackData['ResultCode'];
$resultDesc = $callbackData['ResultDesc'];
$checkoutRequestID = $callbackData['CheckoutRequestID'];

$status = ($resultCode == 0) ? "Success" : "Failed";

$conn->begin_transaction();

try {
    // 1. Find the original request and lock it
    $stmtReq = $conn->prepare("SELECT id, user_id, amount, status FROM mpesa_requests WHERE checkout_request_id = ? FOR UPDATE");
    $stmtReq->bind_param("s", $checkoutRequestID);
    $stmtReq->execute();
    $req = $stmtReq->get_result()->fetch_assoc();

    if ($req && $req['status'] === 'Pending') {
        $req_id = $req['id'];
        $user_id = (int)$req['user_id'];
        $amount = (float)$req['amount'];

        // 2. Update the Request Status
        $stmtUp = $conn->prepare("UPDATE mpesa_requests SET status = ?, result_desc = ? WHERE id = ?");
        $stmtUp->bind_param("ssi", $status, $resultDesc, $req_id);
        $stmtUp->execute();

        // 3. Process Successful Payment
        if ($resultCode == 0) {
            // Get Mpesa Receipt Number from callback metadata
            $mpesaReceipt = "UNKNOWN";
            if (isset($callbackData['CallbackMetadata']['Item'])) {
                foreach ($callbackData['CallbackMetadata']['Item'] as $item) {
                    if ($item['Name'] === 'MpesaReceiptNumber') {
                        $mpesaReceipt = $item['Value'];
                    }
                }
            }

            // A) Update Wallet Balance
            $stmtW = $conn->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id = ?");
            $stmtW->bind_param("di", $amount, $user_id);
            $stmtW->execute();

            // B) Record Wallet Transaction
            $desc = "Deposit via M-Pesa (Ref: {$mpesaReceipt})";
            $stmtTx = $conn->prepare("INSERT INTO wallet_transactions (user_id, type, amount, currency, description) VALUES (?, 'deposit', ?, 'KES', ?)");
            $stmtTx->bind_param("ids", $user_id, $amount, $desc);
            $stmtTx->execute();

            // C) Send Notification
            $notif_msg = "Your M-Pesa deposit of KES " . number_format($amount) . " (Ref: {$mpesaReceipt}) was successful.";
            $stmtNotif = $conn->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, 'Deposit Successful', ?)");
            $stmtNotif->bind_param("is", $user_id, $notif_msg);
            $stmtNotif->execute();
        }
    }

    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    file_put_contents(__DIR__ . "/../../logs/mpesa_errors.log", date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL, FILE_APPEND);
}

// Respond to Safaricom
header('Content-Type: application/json');
echo json_encode(["ResultCode" => 0, "ResultDesc" => "Accepted"]);
?>