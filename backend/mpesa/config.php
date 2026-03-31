<?php
$consumerKey = "YOUR_CONSUMER_KEY";
$consumerSecret = "YOUR_CONSUMER_SECRET";
$shortcode = "174379"; // Example sandbox shortcode
$passkey = "YOUR_PASSKEY";
$callbackUrl = "https://yourdomain.com/mpesa_project/callback.php";

// Database connection
$conn = new mysqli("localhost", "root", "", "mpesa_project");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>