<?php
// Setup script for OTP functionality
require_once 'db_connect.php';

echo "<h2>Setting up OTP System</h2>";

try {
    // Create OTP table
    $sql = "CREATE TABLE IF NOT EXISTS user_otps (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        username VARCHAR(50) NOT NULL,
        otp_code VARCHAR(10) NOT NULL,
        email VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at DATETIME NOT NULL,
        is_used BOOLEAN DEFAULT FALSE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_username (username),
        INDEX idx_otp_code (otp_code),
        INDEX idx_expires_at (expires_at)
    )";
    
    if ($conn->query($sql)) {
        echo "<p style='color: green;'>✅ Created user_otps table successfully</p>";
    } else {
        echo "<p style='color: red;'>❌ Error creating table: " . $conn->error . "</p>";
    }
    
    // Add email column to users table if it doesn't exist
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'email'");
    if ($result->num_rows == 0) {
        $conn->query("ALTER TABLE users ADD COLUMN email VARCHAR(255) DEFAULT NULL");
        echo "<p style='color: green;'>✅ Added email column to users table</p>";
        
        // Update default emails for existing users
        $conn->query("UPDATE users SET email = 'zephyra013@gmail.com' WHERE username = 'superadmin'");
        $conn->query("UPDATE users SET email = 'casimirochris19@gmail.com' WHERE username = 'admin'");
        echo "<p style='color: green;'>✅ Updated default emails for superadmin and admin</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ Email column already exists</p>";
        
        // Update emails if they're empty
        $conn->query("UPDATE users SET email = 'zephyra013@gmail.com' WHERE username = 'superadmin' AND (email IS NULL OR email = '')");
        $conn->query("UPDATE users SET email = 'casimirochris19@gmail.com' WHERE username = 'admin' AND (email IS NULL OR email = '')");
        echo "<p style='color: green;'>✅ Updated emails for superadmin and admin</p>";
    }
    
    echo "<h3 style='color: green;'>OTP System Setup Complete!</h3>";
    echo "<p>Features added:</p>";
    echo "<ul>";
    echo "<li>OTP table for storing verification codes</li>";
    echo "<li>Email addresses for superadmin and admin</li>";
    echo "<li>5-minute OTP expiration</li>";
    echo "<li>Email notifications for login attempts</li>";
    echo "</ul>";
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ol>";
    echo "<li>Configure email settings in email_config.php</li>";
    echo "<li>Test the OTP login system</li>";
    echo "</ol>";
    echo "<p><a href='index.php'>Go to Login</a> | <a href='Dashboard.php'>Go to Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
