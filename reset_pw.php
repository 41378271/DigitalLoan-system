<?php
require 'backend/config/db.php';
$pw = password_hash('password', PASSWORD_DEFAULT);
$conn->query("UPDATE users SET password = '$pw', is_active = 1 WHERE email IN ('admin@test.com', 'clintonmomanyi46@gmail.com')");
echo 'Passwords updated';
