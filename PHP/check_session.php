<?php
/**
 * Session Check Helper
 * Include this file at the top of protected pages to ensure user is logged in
 * Usage: require_once 'check_session.php';
 */

if (!isset($_SESSION)) {
    session_start();
}

// Include database connection
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    // Clear any existing session data
    session_unset();
    session_destroy();
    header("Location: ../index.php?error=session_expired");
    exit();
}

// Optional: Check for specific role
function check_role($allowed_roles) {
    if (!is_array($allowed_roles)) {
        $allowed_roles = array($allowed_roles);
    }
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        header("HTTP/1.1 403 Forbidden");
        echo "<div style='text-align: center; margin-top: 50px;'>";
        echo "<h2>Access Denied</h2>";
        echo "<p>You don't have permission to access this page.</p>";
        echo "<a href='../Dashboard.php'>Return to Dashboard</a>";
        echo "</div>";
        exit();
    }
}

// Optional: Get current user info
function get_session_user() {
    return array(
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'role' => $_SESSION['role']
    );
}

// Optional: Session timeout check (30 minutes)
function check_session_timeout() {
    $timeout_duration = 1800; // 30 minutes in seconds
    
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
        session_unset();
        session_destroy();
        header("Location: ../index.php?error=session_timeout");
        exit();
    }
    $_SESSION['last_activity'] = time();
}
?>
