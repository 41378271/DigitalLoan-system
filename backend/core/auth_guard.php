<?php
/**
 * Role-Based Access Control Middleware
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/response.php';

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        jsonError("Unauthorized. Please log in.", 401);
    }
    return (int)$_SESSION['user_id'];
}

function requireRole($required_role) {
    requireLogin();
    
    $user_role = $_SESSION['role'] ?? '';
    
    if ($user_role !== $required_role) {
        jsonError("Forbidden. You do not have permission to perform this action.", 403);
    }
    
    return (int)$_SESSION['user_id'];
}

function requireAdmin() {
    return requireRole('admin');
}

function requireBorrower() {
    return requireRole('borrower');
}
?>
