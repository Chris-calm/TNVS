<?php
// Development mode OTP system - Shows OTP codes without sending emails
session_start();
require_once 'db_connect.php';

echo "<h2>🔧 OTP Development Mode</h2>";
echo "<p>This page shows recent OTP codes for testing purposes.</p>";

// Check if we're showing OTP for a specific user
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    
    // Get the latest OTP for this user
    $stmt = $conn->prepare("SELECT otp_code, username, email, created_at, expires_at FROM user_otps WHERE user_id = ? AND is_used = FALSE ORDER BY created_at DESC LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $otp_data = $result->fetch_assoc();
        $is_expired = strtotime($otp_data['expires_at']) < time();
        
        echo "<div style='background: " . ($is_expired ? "#fee" : "#efe") . "; border: 1px solid " . ($is_expired ? "#fcc" : "#cfc") . "; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
        echo "<h3>Latest OTP for {$otp_data['username']}</h3>";
        echo "<p><strong>OTP Code:</strong> <span style='font-size: 24px; font-weight: bold; color: #4A90E2;'>{$otp_data['otp_code']}</span></p>";
        echo "<p><strong>Email:</strong> {$otp_data['email']}</p>";
        echo "<p><strong>Created:</strong> {$otp_data['created_at']}</p>";
        echo "<p><strong>Expires:</strong> {$otp_data['expires_at']}</p>";
        echo "<p><strong>Status:</strong> " . ($is_expired ? "❌ Expired" : "✅ Valid") . "</p>";
        echo "</div>";
        
        if (!$is_expired) {
            echo "<p><a href='verify_otp.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔑 Go to OTP Verification</a></p>";
        }
    } else {
        echo "<p style='color: orange;'>No active OTP found for this user.</p>";
    }
    $stmt->close();
}

// Show all recent OTPs
echo "<h3>📋 Recent OTP Codes (Last 10)</h3>";
$recent_otps = $conn->query("SELECT otp_code, username, email, created_at, expires_at, is_used FROM user_otps ORDER BY created_at DESC LIMIT 10");

if ($recent_otps && $recent_otps->num_rows > 0) {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'><th>Username</th><th>OTP Code</th><th>Email</th><th>Created</th><th>Status</th></tr>";
    
    while ($row = $recent_otps->fetch_assoc()) {
        $is_expired = strtotime($row['expires_at']) < time();
        $is_used = $row['is_used'];
        
        $status = "✅ Valid";
        $bg_color = "#f9f9f9";
        
        if ($is_used) {
            $status = "🔒 Used";
            $bg_color = "#e3f2fd";
        } else if ($is_expired) {
            $status = "❌ Expired";
            $bg_color = "#ffebee";
        }
        
        echo "<tr style='background: $bg_color;'>";
        echo "<td><strong>{$row['username']}</strong></td>";
        echo "<td style='font-size: 18px; font-weight: bold; color: #4A90E2;'>{$row['otp_code']}</td>";
        echo "<td>{$row['email']}</td>";
        echo "<td>{$row['created_at']}</td>";
        echo "<td>$status</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No OTP codes found.</p>";
}

// Show email log
echo "<h3>📧 Email Log</h3>";
$log_file = '../logs/email_log.txt';
if (file_exists($log_file)) {
    $log_content = file_get_contents($log_file);
    echo "<pre style='background: #f5f5f5; padding: 15px; border-radius: 5px; max-height: 300px; overflow-y: auto;'>";
    echo htmlspecialchars($log_content);
    echo "</pre>";
} else {
    echo "<p>No email log found.</p>";
}

echo "<h3>🚀 Quick Actions</h3>";
echo "<p>";
echo "<a href='index.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🔑 Test Login</a>";
echo "<a href='test_otp_system.php' style='background: #17a2b8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🧪 Test System</a>";
echo "<a href='Dashboard.php' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📊 Dashboard</a>";
echo "</p>";

echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin-top: 20px;'>";
echo "<h4>💡 Development Tips:</h4>";
echo "<ul>";
echo "<li><strong>Login Process:</strong> Login with superadmin/admin → Check this page for OTP → Enter OTP</li>";
echo "<li><strong>Email Issues:</strong> The system logs email attempts and works without actual email sending</li>";
echo "<li><strong>Testing:</strong> Use the OTP codes shown above to complete login verification</li>";
echo "<li><strong>Production:</strong> Configure proper SMTP or email service for real email sending</li>";
echo "</ul>";
echo "</div>";
?>
