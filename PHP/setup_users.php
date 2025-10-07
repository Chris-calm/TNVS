<?php
/**
 * User Setup Script
 * Run this script once to create users with hashed passwords
 * Access: http://localhost/PM_Project/PHP/setup_users.php
 */

require_once 'db_connect.php';

// Define users with their roles
$users = [
    [
        'username' => 'superadmin',
        'password' => 'super123',
        'role' => 'super_admin'
    ],
    [
        'username' => 'admin',
        'password' => 'admin123',
        'role' => 'admin'
    ],
    [
        'username' => 'employee',
        'password' => 'employee123',
        'role' => 'employee'
    ],
    [
        'username' => 'visitor',
        'password' => 'visitor123',
        'role' => 'visitor'
    ]
];

echo "<h2>User Setup Script</h2>";
echo "<p>Creating users with hashed passwords...</p>";
echo "<hr>";

// Clear existing users (optional - comment out if you want to keep existing users)
$conn->query("TRUNCATE TABLE users");
echo "<p style='color: orange;'>✓ Cleared existing users</p>";

// Insert users with hashed passwords
foreach ($users as $user) {
    $username = $user['username'];
    $password_hash = password_hash($user['password'], PASSWORD_DEFAULT);
    $role = $user['role'];
    
    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $password_hash, $role);
    
    if ($stmt->execute()) {
        echo "<p style='color: green;'>✓ Created user: <strong>$username</strong> (Role: $role) - Password: {$user['password']}</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to create user: $username - Error: " . $stmt->error . "</p>";
    }
    
    $stmt->close();
}

$conn->close();

echo "<hr>";
echo "<h3>Login Credentials:</h3>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr><th>Username</th><th>Password</th><th>Role</th></tr>";
foreach ($users as $user) {
    echo "<tr>";
    echo "<td>{$user['username']}</td>";
    echo "<td>{$user['password']}</td>";
    echo "<td>{$user['role']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<br><p><a href='index.php'>Go to Login Page</a></p>";
echo "<p style='color: red;'><strong>IMPORTANT:</strong> Delete this file (setup_users.php) after running it for security reasons!</p>";
?>
