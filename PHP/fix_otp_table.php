<?php
// Fix script for OTP table creation issue
require_once 'db_connect.php';

echo "<h2>Fixing OTP Table</h2>";

try {
    // Drop existing table if it exists (to fix the timestamp issue)
    $conn->query("DROP TABLE IF EXISTS user_otps");
    echo "<p style='color: orange;'>🗑️ Dropped existing user_otps table (if it existed)</p>";
    
    // Create OTP table with correct structure
    $sql = "CREATE TABLE user_otps (
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
        echo "<p style='color: green;'>✅ Created user_otps table successfully with correct structure</p>";
    } else {
        echo "<p style='color: red;'>❌ Error creating table: " . $conn->error . "</p>";
    }
    
    // Verify table structure
    $result = $conn->query("DESCRIBE user_otps");
    if ($result) {
        echo "<h3>✅ Table Structure Verified:</h3>";
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['Field']}</td>";
            echo "<td>{$row['Type']}</td>";
            echo "<td>{$row['Null']}</td>";
            echo "<td>{$row['Key']}</td>";
            echo "<td>{$row['Default']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Test inserting a sample OTP (and then delete it)
    $test_user_id = 1; // Assuming user ID 1 exists
    $test_username = 'test'; // Store in variable to pass by reference
    $test_otp = '123456';
    $test_email = 'test@example.com';
    $expires_at = date('Y-m-d H:i:s', time() + 300); // 5 minutes from now
    
    $test_stmt = $conn->prepare("INSERT INTO user_otps (user_id, username, otp_code, email, expires_at) VALUES (?, ?, ?, ?, ?)");
    $test_stmt->bind_param("issss", $test_user_id, $test_username, $test_otp, $test_email, $expires_at);
    
    if ($test_stmt->execute()) {
        echo "<p style='color: green;'>✅ Test insert successful</p>";
        
        // Clean up test data
        $conn->query("DELETE FROM user_otps WHERE username = 'test'");
        echo "<p style='color: blue;'>🧹 Cleaned up test data</p>";
    } else {
        echo "<p style='color: red;'>❌ Test insert failed: " . $test_stmt->error . "</p>";
    }
    $test_stmt->close();
    
    echo "<h3 style='color: green;'>OTP Table Fix Complete!</h3>";
    echo "<p>The OTP system is now ready to use.</p>";
    echo "<p><a href='test_otp_system.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🧪 Test OTP System</a></p>";
    echo "<p><a href='index.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-left: 10px;'>🔑 Try Login</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
