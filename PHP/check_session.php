<?php
/**
 * Session Check Helper
 * Include this file at the top of protected pages to ensure user is logged in
 * Usage: require_once 'check_session.php';
 */

if (!isset($_SESSION)) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: index.php");
    exit();
}

// Optional: Check for specific role
function check_role($allowed_roles) {
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        die("Access Denied: You don't have permission to access this page.");
    }
}

// Optional: Get current user info
function get_current_user() {
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'role' => $_SESSION['role']
    ];
}
?>
