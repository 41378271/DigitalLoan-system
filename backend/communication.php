<?php
require_once __DIR__ . '/config/db.php';

/* 
   SAVE IN-SYSTEM NOTIFICATION
 */
function saveNotification($user_id, $message, $title = "Notification") {
    global $conn;
    if (!$conn) return;

    $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $title, $message);
    $stmt->execute();
}

/* 
   SEND EMAIL (SMTP BASIC)
 */
function sendEmail($to, $subject, $message) {
    $headers = "From: no-reply@loanapp.com";

    if(mail($to, $subject, $message, $headers)) {
        return true;
    } else {
        return false;
    }
}

/* 
   SEND SMS (Africa's Talking API)
 */
function sendSMS($phone, $message) {
    $username = "YOUR_USERNAME";
    $apiKey   = "YOUR_API_KEY";

    $url = "https://api.africastalking.com/version1/messaging";

    $data = [
        "username" => $username,
        "to" => $phone,
        "message" => $message
    ];

    $headers = [
        "apiKey: $apiKey",
        "Content-Type: application/x-www-form-urlencoded"
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}

/* 
   MAIN FUNCTION (ALL CHANNELS)
 */
function sendNotification($user_id, $email, $phone, $message) {
    
    // Save in system
    saveNotification($user_id, $message);

    // Send Email
    sendEmail($email, "Loan System Notification", $message);

    // Send SMS
    sendSMS($phone, $message);
}
?>