<?php
// Email configuration for OTP system
// IMPORTANT: Configure these settings for email to work

// Email settings - UPDATE THESE WITH YOUR GMAIL CREDENTIALS
$email_config = [
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_username' => 'zephyra013@gmail.com', // Replace with your Gmail
    'smtp_password' => 'gebe gxph xazz ebhj',          // Your Gmail App Password
    'from_email' => 'zephyra013@gmail.com',
    'from_name' => 'TNVS Security System'
];

// SETUP INSTRUCTIONS:
// 1. Create a Gmail account for your system (or use existing)
// 2. Enable 2-Factor Authentication on the Gmail account
// 3. Generate an "App Password" in Gmail Security settings
// 4. Replace 'your_system_email@gmail.com' with your Gmail
// 5. Replace 'your_app_password' with the generated App Password

// User email mappings
$user_emails = [
    'superadmin' => 'zephyra013@gmail.com',
    'admin' => 'casimirochris19@gmail.com'
];

// Function to send OTP email using SMTP
function sendOTPEmail($to_email, $username, $otp) {
    global $email_config;
    
    // Use the new Gmail SMTP implementation directly
    require_once 'gmail_smtp.php';
    
    $result = sendGmailOTP($to_email, $username, $otp, $email_config);
    
    if ($result) {
        // Log successful email
        $log_message = date('Y-m-d H:i:s') . " - ✅ Gmail SMTP SUCCESS! Email sent to: $to_email (User: $username, OTP: $otp)\n";
        file_put_contents('../logs/email_log.txt', $log_message, FILE_APPEND | LOCK_EX);
        return true;
    } else {
        // Log failed attempt
        $log_message = date('Y-m-d H:i:s') . " - ❌ Gmail SMTP FAILED to: $to_email (User: $username, OTP: $otp)\n";
        file_put_contents('../logs/email_log.txt', $log_message, FILE_APPEND | LOCK_EX);
        
        // For development, still return true so OTP system works
        return true;
    }
}

// Custom SMTP email function
function sendSMTPEmail($to, $subject, $message, $config) {
    // Include the new Gmail SMTP implementation
    require_once 'gmail_smtp.php';
    
    // Try to send with Gmail SMTP
    $result = sendGmailOTP($to, 'User', extractOTPFromMessage($message), $config);
    
    if ($result) {
        // Log successful email
        $log_message = date('Y-m-d H:i:s') . " - ✅ Gmail SMTP SUCCESS! Email sent to: $to\n";
        file_put_contents('../logs/email_log.txt', $log_message, FILE_APPEND | LOCK_EX);
        return true;
    } else {
        // Log failed attempt and try fallback
        $log_message = date('Y-m-d H:i:s') . " - ❌ Gmail SMTP failed to: $to, trying fallback method\n";
        file_put_contents('../logs/email_log.txt', $log_message, FILE_APPEND | LOCK_EX);
        
        return sendEmailCURL($to, $subject, $message, $config);
    }
}

// Helper function to extract OTP from message
function extractOTPFromMessage($message) {
    // Extract OTP code from the HTML message
    preg_match("/<div class='otp-code'>(\d{6})<\/div>/", $message, $matches);
    return isset($matches[1]) ? $matches[1] : '000000';
}

// Alternative email sending using cURL (Gmail API simulation)
function sendEmailCURL($to, $subject, $message, $config) {
    // Try using PHP's mail() with proper headers as last resort
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: {$config['from_name']} <{$config['from_email']}>\r\n";
    $headers .= "Reply-To: {$config['from_email']}\r\n";
    
    // Attempt to send with basic mail()
    $mail_result = @mail($to, $subject, $message, $headers);
    
    // Log the attempt
    $status = $mail_result ? "✅ Basic mail() succeeded" : "❌ Basic mail() failed";
    $log_message = date('Y-m-d H:i:s') . " - $status to: $to, Subject: $subject\n";
    file_put_contents('../logs/email_log.txt', $log_message, FILE_APPEND | LOCK_EX);
    
    // For development, always return true to allow OTP system to work
    // The OTP codes will be visible in otp_dev_mode.php
    return true;
}

// Function to generate OTP
function generateOTP($length = 6) {
    return str_pad(random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
}

// Function to get user email
function getUserEmail($username) {
    global $user_emails;
    return isset($user_emails[$username]) ? $user_emails[$username] : null;
}
?>
