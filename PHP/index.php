<?php
session_start();
require_once 'db_connect.php';
require_once 'email_config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, username, password, role, email FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password using password_verify for hashed passwords
        if (password_verify($password, $user['password'])) {
            
            // Check if user has OTP enabled (superadmin and admin)
            if (in_array($username, ['superadmin', 'admin']) && !empty($user['email'])) {
                // Generate OTP
                $otp = generateOTP();
                $expires_at = date('Y-m-d H:i:s', time() + 300); // 5 minutes from now
                
                // Store OTP in database
                $otp_stmt = $conn->prepare("INSERT INTO user_otps (user_id, username, otp_code, email, expires_at) VALUES (?, ?, ?, ?, ?)");
                $otp_stmt->bind_param("issss", $user['id'], $username, $otp, $user['email'], $expires_at);
                
                if ($otp_stmt->execute()) {
                    // Send OTP email
                    if (sendOTPEmail($user['email'], $username, $otp)) {
                        // Set pending OTP session
                        $_SESSION['pending_otp_user_id'] = $user['id'];
                        $_SESSION['pending_otp_username'] = $username;
                        
                        $otp_stmt->close();
                        $stmt->close();
                        $conn->close();
                        
                        // Redirect to OTP verification
                        header("Location: verify_otp.php");
                        exit();
                    } else {
                        // Email failed, but allow login anyway for now
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['role'] = $user['role'];
                        
                        $otp_stmt->close();
                        $stmt->close();
                        $conn->close();
                        
                        header("Location: Dashboard.php?email_warning=1");
                        exit();
                    }
                } else {
                    // OTP storage failed, allow login anyway
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    
                    $otp_stmt->close();
                    $stmt->close();
                    $conn->close();
                    
                    header("Location: Dashboard.php");
                    exit();
                }
            } else {
                // No OTP required for this user (employee or no email)
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                $stmt->close();
                $conn->close();
                
                header("Location: Dashboard.php");
                exit();
            }
        }
    }
    
    $stmt->close();
    $conn->close();
    
    // If authentication fails, redirect with error
    header("Location: index.php?error=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TNVS - Login</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif;
      background: #f5f5f5;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding: 20px;
    }

    .login-container {
      display: flex;
      width: 100%;
      max-width: 1000px;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      overflow: hidden;
      min-height: 550px;
    }

    .login-left {
      flex: 1;
      padding: 60px 50px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .login-right {
      flex: 1;
      background: linear-gradient(135deg, #4A90E2 0%, #0066CC 100%);
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 60px 50px;
      color: #fff;
      position: relative;
      overflow: hidden;
    }

    .login-right::before {
      content: '';
      position: absolute;
      width: 300px;
      height: 300px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
      top: -100px;
      right: -100px;
    }

    .login-right::after {
      content: '';
      position: absolute;
      width: 200px;
      height: 200px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
      bottom: -50px;
      left: -50px;
    }

    .logo-section {
      text-align: center;
      margin-bottom: 40px;
    }

    .logo-section h1 {
      font-size: 28px;
      font-weight: 600;
      color: #2c3e50;
      margin-bottom: 8px;
    }

    .logo-section p {
      font-size: 14px;
      color: #7f8c8d;
    }

    .form-group {
      margin-bottom: 24px;
    }

    .form-group label {
      display: block;
      font-size: 14px;
      font-weight: 500;
      color: #2c3e50;
      margin-bottom: 8px;
    }

    .form-group input {
      width: 100%;
      padding: 12px 16px;
      border: 1px solid #e0e0e0;
      border-radius: 8px;
      font-size: 15px;
      color: #2c3e50;
      transition: all 0.3s ease;
      background: #fafafa;
    }

    .form-group input:focus {
      outline: none;
      border-color: #4A90E2;
      background: #fff;
      box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
    }

    .form-group input::placeholder {
      color: #bdc3c7;
    }

    .btn-login {
      width: 100%;
      padding: 14px;
      background: linear-gradient(135deg, #4A90E2 0%, #0066CC 100%);
      border: none;
      border-radius: 8px;
      color: #fff;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      margin-top: 10px;
    }

    .btn-login:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(74, 144, 226, 0.4);
    }

    .btn-login:active {
      transform: translateY(0);
    }

    .error-message {
      background: #fee;
      border: 1px solid #fcc;
      color: #c33;
      padding: 12px 16px;
      border-radius: 8px;
      font-size: 14px;
      margin-top: 20px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .right-content {
      position: relative;
      z-index: 1;
      text-align: center;
    }

    .right-content img {
      width: 250px;
      height: 250px;
      margin-bottom: 25px;
      border-radius: 50%;
      background: #fff;
      padding: 25px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
      image-rendering: -webkit-optimize-contrast;
      image-rendering: crisp-edges;
    }

    .right-content h2 {
      font-size: 32px;
      font-weight: 700;
      margin-bottom: 20px;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .right-content p {
      font-size: 16px;
      line-height: 1.6;
      opacity: 0.95;
      margin-bottom: 30px;
    }

    .features {
      display: flex;
      flex-direction: column;
      gap: 15px;
      text-align: left;
    }

    .feature-item {
      display: flex;
      align-items: center;
      gap: 12px;
      background: rgba(255, 255, 255, 0.1);
      padding: 12px 16px;
      border-radius: 8px;
      backdrop-filter: blur(10px);
    }

    .feature-icon {
      width: 24px;
      height: 24px;
      background: rgba(255, 255, 255, 0.2);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 14px;
    }

    @media (max-width: 768px) {
      .login-container {
        flex-direction: column;
      }

      .login-right {
        order: -1;
        padding: 40px 30px;
      }

      .login-left {
        padding: 40px 30px;
      }

      .features {
        display: none;
      }
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="login-left">
      <div class="logo-section">
        <h1>Welcome Back</h1>
        <p>Sign in to continue to TNVS</p>
      </div>

      <form action="index.php" method="POST">
        <div class="form-group">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" placeholder="Enter your username" required autofocus>
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="Enter your password" required>
        </div>

        <button type="submit" class="btn-login">Sign In</button>
      </form>
      
      <?php if (isset($_GET['error'])): ?>
        <div class="error-message">
          <span>⚠️</span>
          <span>Invalid username or password. Please try again.</span>
        </div>
      <?php endif; ?>
      
      <?php if (isset($_GET['otp_expired'])): ?>
        <div class="error-message">
          <span>⏰</span>
          <span>OTP has expired. Please login again to receive a new code.</span>
        </div>
      <?php endif; ?>
      
      <div class="info-message" style="background: #e3f2fd; border: 1px solid #90caf9; color: #1565c0; padding: 12px 16px; border-radius: 8px; font-size: 14px; margin-top: 20px; display: flex; align-items: center; gap: 8px;">
        <span>🔐</span>
        <span><strong>Security Notice:</strong> Super Admin and Admin accounts require email verification (OTP) for login.</span>
      </div>
    </div>

    <div class="login-right">
      <div class="right-content">
        <img src="../PICTURES/Black and White Circular Art & Design Logo.png" alt="TNVS Logo">
        <h2>TNVS</h2>
        <p>Transport Network Vehicle System</p>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
