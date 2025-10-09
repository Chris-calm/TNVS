<?php
// Direct email test for debugging
require_once 'email_config.php';

echo "<h2>🧪 Direct Email Test</h2>";

if (isset($_GET['send']) && $_GET['send'] === '1') {
    $test_email = 'zephyra013@gmail.com';
    $test_otp = '123456';
    
    echo "<h3>Testing Email Send...</h3>";
    echo "<p><strong>To:</strong> $test_email</p>";
    echo "<p><strong>OTP:</strong> $test_otp</p>";
    
    // Test the email function
    $result = sendOTPEmail($test_email, 'superadmin', $test_otp);
    
    if ($result) {
        echo "<p style='color: green;'>✅ Email function returned success!</p>";
        echo "<p>Check your Gmail inbox: $test_email</p>";
    } else {
        echo "<p style='color: red;'>❌ Email function returned failure</p>";
    }
    
    // Show the email log
    echo "<h3>📧 Email Log:</h3>";
    $log_file = '../logs/email_log.txt';
    if (file_exists($log_file)) {
        $log_content = file_get_contents($log_file);
        echo "<pre style='background: #f5f5f5; padding: 15px; border-radius: 5px;'>";
        echo htmlspecialchars($log_content);
        echo "</pre>";
    }
} else {
    echo "<p>Click the button below to test sending an email:</p>";
    echo "<p><a href='?send=1' style='background: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 16px;'>📧 Send Test Email</a></p>";
}

echo "<h3>🔧 Current Configuration:</h3>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr><th>Setting</th><th>Value</th></tr>";
echo "<tr><td>SMTP Host</td><td>smtp.gmail.com</td></tr>";
echo "<tr><td>SMTP Port</td><td>587</td></tr>";
echo "<tr><td>Username</td><td>zephyra013@gmail.com</td></tr>";
echo "<tr><td>Password</td><td>gebe gxph xazz ebhj</td></tr>";
echo "<tr><td>Target Email</td><td>zephyra013@gmail.com</td></tr>";
echo "</table>";

echo "<h3>💡 Troubleshooting Tips:</h3>";
echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px;'>";
echo "<h4>If emails are not being received:</h4>";
echo "<ol>";
echo "<li><strong>Check Gmail Spam/Junk folder</strong> - Automated emails often go there</li>";
echo "<li><strong>Verify App Password</strong> - Make sure 'gebe gxph xazz ebhj' is correct</li>";
echo "<li><strong>Check 2FA</strong> - Ensure 2-Factor Authentication is enabled on zephyra013@gmail.com</li>";
echo "<li><strong>Gmail Security</strong> - Check if Gmail blocked the login attempt</li>";
echo "<li><strong>Server Configuration</strong> - XAMPP might not support SMTP properly</li>";
echo "</ol>";
echo "</div>";

echo "<h3>🚀 Alternative Solutions:</h3>";
echo "<div style='background: #e3f2fd; border: 1px solid #90caf9; padding: 15px; border-radius: 5px;'>";
echo "<h4>For Development/Testing:</h4>";
echo "<ul>";
echo "<li><strong>Use Development Mode:</strong> <a href='otp_dev_mode.php'>View OTP codes without email</a></li>";
echo "<li><strong>Manual Testing:</strong> Check database for OTP codes directly</li>";
echo "<li><strong>Local Testing:</strong> Use the OTP system without actual email delivery</li>";
echo "</ul>";
echo "</div>";

echo "<p><a href='otp_dev_mode.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🔍 View OTP Codes</a>";
echo "<a href='index.php' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔑 Test Login</a></p>";
?>
