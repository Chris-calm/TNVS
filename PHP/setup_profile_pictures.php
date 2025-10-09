<?php
// Setup script to add profile picture support
require_once 'db_connect.php';

echo "<h2>Setting up Profile Picture Support</h2>";

try {
    // Check if profile_picture column exists
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'profile_picture'");
    
    if ($result->num_rows == 0) {
        // Add profile_picture column
        $conn->query("ALTER TABLE users ADD COLUMN profile_picture VARCHAR(255) DEFAULT NULL");
        echo "<p style='color: green;'>✅ Added profile_picture column to users table</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ Profile_picture column already exists</p>";
    }
    
    // Check if email column exists
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'email'");
    
    if ($result->num_rows == 0) {
        // Add email column
        $conn->query("ALTER TABLE users ADD COLUMN email VARCHAR(255) DEFAULT NULL");
        echo "<p style='color: green;'>✅ Added email column to users table</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ Email column already exists</p>";
    }
    
    // Create uploads/profiles directory if it doesn't exist
    $uploadDir = "../uploads/profiles/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
        echo "<p style='color: green;'>✅ Created uploads/profiles directory</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ Uploads/profiles directory already exists</p>";
    }
    
    echo "<h3 style='color: green;'>Setup Complete!</h3>";
    echo "<p>You can now:</p>";
    echo "<ul>";
    echo "<li>Upload profile pictures in Settings</li>";
    echo "<li>Change profile pictures for any user (Super Admin only)</li>";
    echo "<li>Profile pictures will appear in the header navigation</li>";
    echo "</ul>";
    echo "<p><a href='Settings.php'>Go to Settings</a> | <a href='Dashboard.php'>Go to Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
