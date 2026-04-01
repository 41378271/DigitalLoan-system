<?php
require 'backend/config/db.php';
$conn->query("ALTER TABLE audit_logs ADD COLUMN ip_address VARCHAR(45) NULL AFTER description");
if ($conn->error) {
    echo "Error: " . $conn->error;
} else {
    echo "Column added";
}
