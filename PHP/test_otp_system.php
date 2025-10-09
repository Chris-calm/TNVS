<?php
// Test script for OTP system
require_once 'db_connect.php';
require_once 'email_config.php';

echo "<h2>OTP System Test & Configuration</h2>";

// Check if OTP table exists
$table_check = $conn->query("SHOW TABLES LIKE 'user_otps'");
if ($table_check->num_rows == 0) {
    echo "<p style='color: red;'>❌ OTP table not found. Please run <a href='setup_otp.php'>setup_otp.php</a> first.</p>";
    exit();
}

echo "<h3>✅ Database Setup</h3>";
echo "<p>OTP table exists and ready.</p>";

// Check user emails
echo "<h3>📧 User Email Configuration</h3>";
$users_result = $conn->query("SELECT username, email FROM users WHERE username IN ('superadmin', 'admin')");
while ($user = $users_result->fetch_assoc()) {
    $email_status = !empty($user['email']) ? "✅ " . $user['email'] : "❌ No email set";
    echo "<p><strong>{$user['username']}:</strong> $email_status</p>";
}

// Test email configuration
echo "<h3>📮 Email System Test</h3>";

if (isset($_GET['test_email']) && $_GET['test_email'] === '1') {
    $test_otp = generateOTP();
    $test_email = 'zephyra013@gmail.com'; // Test with superadmin email
    
    echo "<p>Attempting to send test OTP to: $test_email</p>";
    echo "<p>Test OTP Code: <strong>$test_otp</strong></p>";
    
    if (sendOTPEmail($test_email, 'superadmin', $test_otp)) {
        echo "<p style='color: green;'>✅ Test email sent successfully!</p>";
        echo "<p>Check the inbox for: $test_email</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to send test email.</p>";
        echo "<p><strong>Possible issues:</strong></p>";
        echo "<ul>";
        echo "<li>Email configuration not set up in email_config.php</li>";
        echo "<li>Gmail App Password not configured</li>";
        echo "<li>Server doesn't support mail() function</li>";
        echo "<li>Firewall blocking email ports</li>";
        echo "</ul>";
    }
}

// Show current configuration
echo "<h3>⚙️ Current Configuration</h3>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr><th>Setting</th><th>Value</th><th>Status</th></tr>";

$smtp_configured = ($email_config['smtp_username'] !== 'your_system_email@gmail.com');
$password_configured = ($email_config['smtp_password'] !== 'your_app_password');

echo "<tr><td>SMTP Host</td><td>{$email_config['smtp_host']}</td><td>✅</td></tr>";
echo "<tr><td>SMTP Port</td><td>{$email_config['smtp_port']}</td><td>✅</td></tr>";
echo "<tr><td>SMTP Username</td><td>{$email_config['smtp_username']}</td><td>" . ($smtp_configured ? "✅" : "❌ Not configured") . "</td></tr>";
echo "<tr><td>SMTP Password</td><td>" . str_repeat('*', strlen($email_config['smtp_password'])) . "</td><td>" . ($password_configured ? "✅" : "❌ Not configured") . "</td></tr>";
echo "<tr><td>From Email</td><td>{$email_config['from_email']}</td><td>" . ($smtp_configured ? "✅" : "❌ Not configured") . "</td></tr>";
echo "</table>";

// Configuration instructions
echo "<h3>🔧 Setup Instructions</h3>";
if (!$smtp_configured || !$password_configured) {
    echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px;'>";
    echo "<h4>Email Configuration Required:</h4>";
    echo "<ol>";
    echo "<li><strong>Edit email_config.php:</strong>";
    echo "<ul>";
    echo "<li>Replace 'your_system_email@gmail.com' with your Gmail address</li>";
    echo "<li>Replace 'your_app_password' with your Gmail App Password</li>";
    echo "</ul></li>";
    echo "<li><strong>Gmail Setup:</strong>";
    echo "<ul>";
    echo "<li>Enable 2-Factor Authentication on your Gmail</li>";
    echo "<li>Go to Google Account → Security → App passwords</li>";
    echo "<li>Generate a new App Password for 'Mail'</li>";
    echo "<li>Use this App Password (not your regular password)</li>";
    echo "</ul></li>";
    echo "</ol>";
    echo "</div>";
} else {
    echo "<p style='color: green;'>✅ Email configuration appears to be set up!</p>";
    echo "<p><a href='?test_email=1' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🧪 Send Test Email</a></p>";
}

// Login flow explanation
echo "<h3>🔐 OTP Login Flow</h3>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
echo "<h4>How it works:</h4>";
echo "<ol>";
echo "<li><strong>User Login:</strong> SuperAdmin or Admin enters username/password</li>";
echo "<li><strong>Password Verification:</strong> System verifies credentials</li>";
echo "<li><strong>OTP Generation:</strong> 6-digit code generated and stored in database</li>";
echo "<li><strong>Email Sent:</strong> OTP sent to user's registered email</li>";
echo "<li><strong>OTP Verification:</strong> User enters OTP code within 5 minutes</li>";
echo "<li><strong>Login Complete:</strong> Access granted to dashboard</li>";
echo "</ol>";

echo "<h4>Security Features:</h4>";
echo "<ul>";
echo "<li>OTP expires after 5 minutes</li>";
echo "<li>One-time use (cannot reuse same OTP)</li>";
echo "<li>Only for SuperAdmin and Admin roles</li>";
echo "<li>Employee accounts login normally (no OTP required)</li>";
echo "<li>Fallback: If email fails, login still works</li>";
echo "</ul>";
echo "</div>";

// Test accounts
echo "<h3>👤 Test Accounts</h3>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr><th>Username</th><th>Password</th><th>Email</th><th>OTP Required</th></tr>";
echo "<tr><td>superadmin</td><td>super123</td><td>zephyra013@gmail.com</td><td>✅ Yes</td></tr>";
echo "<tr><td>admin</td><td>admin123</td><td>casimirochris19@gmail.com</td><td>✅ Yes</td></tr>";
echo "<tr><td>employee</td><td>emp123</td><td>-</td><td>❌ No</td></tr>";
echo "</table>";

echo "<h3>🚀 Quick Actions</h3>";
echo "<p>";
echo "<a href='index.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🔑 Test Login</a>";
echo "<a href='setup_otp.php' style='background: #ffc107; color: black; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>⚙️ Run Setup</a>";
echo "<a href='Dashboard.php' style='background: #17a2b8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📊 Dashboard</a>";
echo "</p>";
?>
