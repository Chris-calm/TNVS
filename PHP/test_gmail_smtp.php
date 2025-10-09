<?php
// Test Gmail SMTP functionality
require_once 'email_config.php';

echo "<h2>🧪 Gmail SMTP Test</h2>";

if (isset($_GET['send']) && $_GET['send'] === '1') {
    echo "<h3>Testing Gmail SMTP...</h3>";
    
    $test_email = 'zephyra013@gmail.com';
    $test_username = 'superadmin';
    $test_otp = '123456';
    
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<p><strong>Sending to:</strong> $test_email</p>";
    echo "<p><strong>Username:</strong> $test_username</p>";
    echo "<p><strong>OTP Code:</strong> $test_otp</p>";
    echo "<p><strong>SMTP Server:</strong> smtp.gmail.com:587</p>";
    echo "<p><strong>Authentication:</strong> zephyra013@gmail.com</p>";
    echo "</div>";
    
    echo "<p>⏳ Attempting to send email...</p>";
    
    // Test the email function
    $start_time = microtime(true);
    $result = sendOTPEmail($test_email, $test_username, $test_otp);
    $end_time = microtime(true);
    $duration = round(($end_time - $start_time), 2);
    
    echo "<div style='padding: 15px; border-radius: 5px; margin: 10px 0; " . 
         ($result ? "background: #d4edda; border: 1px solid #c3e6cb; color: #155724;" : "background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24;") . "'>";
    
    if ($result) {
        echo "<h4>✅ SUCCESS!</h4>";
        echo "<p>Email sent successfully in {$duration} seconds!</p>";
        echo "<p><strong>Check your Gmail inbox:</strong> $test_email</p>";
        echo "<p><strong>Also check:</strong> Spam/Junk folder</p>";
    } else {
        echo "<h4>❌ FAILED</h4>";
        echo "<p>Email sending failed after {$duration} seconds</p>";
        echo "<p>Check the logs below for details</p>";
    }
    echo "</div>";
    
    // Show the email log
    echo "<h3>📧 Email Log (Last 10 entries):</h3>";
    $log_file = '../logs/email_log.txt';
    if (file_exists($log_file)) {
        $log_lines = file($log_file);
        $recent_logs = array_slice($log_lines, -10);
        
        echo "<div style='background: #f5f5f5; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 12px;'>";
        foreach ($recent_logs as $line) {
            $color = '#000';
            if (strpos($line, '✅') !== false) $color = '#28a745';
            if (strpos($line, '❌') !== false) $color = '#dc3545';
            
            echo "<div style='color: $color; margin: 2px 0;'>" . htmlspecialchars(trim($line)) . "</div>";
        }
        echo "</div>";
    }
    
    echo "<h3>🎯 Next Steps:</h3>";
    if ($result) {
        echo "<div style='background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px;'>";
        echo "<p><strong>✅ Gmail SMTP is working!</strong></p>";
        echo "<p>Now test the complete OTP login flow:</p>";
        echo "<ol>";
        echo "<li>Go to <a href='index.php'>Login Page</a></li>";
        echo "<li>Login with: superadmin / super123</li>";
        echo "<li>Check your Gmail for the OTP code</li>";
        echo "<li>Enter the OTP to complete login</li>";
        echo "</ol>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px;'>";
        echo "<p><strong>❌ Gmail SMTP needs troubleshooting</strong></p>";
        echo "<p>Possible issues:</p>";
        echo "<ul>";
        echo "<li>App Password might be incorrect</li>";
        echo "<li>2-Factor Authentication not enabled</li>";
        echo "<li>Gmail security blocking the connection</li>";
        echo "<li>Firewall blocking SMTP ports</li>";
        echo "</ul>";
        echo "<p>You can still use <a href='otp_dev_mode.php'>Development Mode</a> for testing</p>";
        echo "</div>";
    }
    
} else {
    echo "<div style='background: #e3f2fd; border: 1px solid #90caf9; padding: 20px; border-radius: 5px;'>";
    echo "<h3>🔧 Gmail SMTP Configuration</h3>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Setting</th><th>Value</th><th>Status</th></tr>";
    echo "<tr><td>SMTP Host</td><td>smtp.gmail.com</td><td>✅</td></tr>";
    echo "<tr><td>SMTP Port</td><td>587 (TLS)</td><td>✅</td></tr>";
    echo "<tr><td>Username</td><td>zephyra013@gmail.com</td><td>✅</td></tr>";
    echo "<tr><td>App Password</td><td>gebe gxph xazz ebhj</td><td>✅</td></tr>";
    echo "<tr><td>Target Email</td><td>zephyra013@gmail.com</td><td>✅</td></tr>";
    echo "</table>";
    echo "</div>";
    
    echo "<p style='margin: 20px 0;'>Click the button below to test sending a real email to your Gmail:</p>";
    echo "<p><a href='?send=1' style='background: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 16px; display: inline-block;'>📧 Send Test Email to Gmail</a></p>";
    
    echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>⚠️ Important Notes:</h4>";
    echo "<ul>";
    echo "<li><strong>Check Spam Folder:</strong> Gmail might put automated emails in spam</li>";
    echo "<li><strong>App Password:</strong> Make sure you're using the App Password, not your regular Gmail password</li>";
    echo "<li><strong>2FA Required:</strong> Gmail App Passwords only work with 2-Factor Authentication enabled</li>";
    echo "<li><strong>Security:</strong> Gmail might block the first few attempts - check Gmail security notifications</li>";
    echo "</ul>";
    echo "</div>";
}

echo "<p style='margin-top: 30px;'>";
echo "<a href='otp_dev_mode.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🔍 Dev Mode</a>";
echo "<a href='index.php' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔑 Login</a>";
echo "</p>";
?>
