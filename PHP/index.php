<?php
session_start();
$valid_username = "admin";
$valid_password = "12345";

$password_hash = password_hash($valid_password, PASSWORD_DEFAULT);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($username === $valid_username && $password === $valid_password) {
        $_SESSION['username'] = $username;
        header("Location: Dashboard.php");
        exit();
    } else {
        header("Location: index.php?error=1");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Transport Network Vehicle System - Login</title>
  <link rel="stylesheet" href="../CSS/login.css">
</head>
<body>
  <div class="login-container">
    <div class="login-form">
      <h2>LOGIN</h2>
      <form action="Dashboard.php" method="POST">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" placeholder="Enter username" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" placeholder="Enter password" required>

        <button type="submit" class="btn-login">LOGIN</button>
      </form>
    </div>

    <div class="login-banner">
      <h3>TNVS</h3>
      <img src="../PICTURES/Black and White Circular Art & Design Logo.png" alt="System Logo">
    </div>
  </div>
</body>
</html>
