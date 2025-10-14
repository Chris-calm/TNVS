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
      background: #fff;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding: 20px;
      overflow: hidden;
    }

    #particles-js {
      position: fixed;
      width: 100%;
      height: 100%;
      top: 0;
      left: 0;
      z-index: -1;
    }

    .login-container {
      display: flex;
      width: 100%;
      max-width: 1000px;
      background: rgba(74, 144, 226, 0.8);
      border-radius: 12px;
      box-shadow: 0 8px 32px rgba(74, 144, 226, 0.3);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(74, 144, 226, 0.2);
      overflow: hidden;
      min-height: 550px;
      position: relative;
      z-index: 1;
    }

    .login-left {
      flex: 1;
      padding: 60px 50px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      background: rgba(74, 144, 226, 0.6);
      backdrop-filter: blur(5px);
    }

    .login-right {
      flex: 1;
      background: rgba(255, 255, 255, 0.3);
      backdrop-filter: blur(10px);
      border-left: 1px solid rgba(255, 255, 255, 0.2);
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 60px 50px;
      color: #2c3e50;
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
      color: #fff;
      margin-bottom: 8px;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }

    .logo-section p {
      font-size: 14px;
      color: rgba(255, 255, 255, 0.9);
    }

    .form-group {
      margin-bottom: 24px;
    }

    .form-group label {
      display: block;
      font-size: 14px;
      font-weight: 500;
      color: #fff;
      margin-bottom: 8px;
    }

    .form-group input {
      width: 100%;
      padding: 12px 16px;
      border: 1px solid rgba(255, 255, 255, 0.3);
      border-radius: 8px;
      font-size: 15px;
      color: #fff;
      transition: all 0.3s ease;
      background: rgba(255, 255, 255, 0.2);
      backdrop-filter: blur(5px);
    }

    .form-group input:focus {
      outline: none;
      border-color: #fff;
      background: rgba(255, 255, 255, 0.3);
      box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.4);
    }

    .form-group input::placeholder {
      color: rgba(255, 255, 255, 0.7);
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
      color: #2c3e50;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .right-content p {
      font-size: 16px;
      line-height: 1.6;
      color: #34495e;
      opacity: 0.9;
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
      background: rgba(255, 255, 255, 0.4);
      padding: 12px 16px;
      border-radius: 8px;
      backdrop-filter: blur(10px);
      color: #2c3e50;
    }

    .feature-icon {
      width: 24px;
      height: 24px;
      background: rgba(74, 144, 226, 0.3);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 14px;
      color: #2c3e50;
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
  <div id="particles-js"></div>
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
        <img src="../PICTURES/Black and White Circular Art & Design Logo1.png" alt="TNVS Logo">
        <h2>TNVS</h2>
        <p>Transport Network Vehicle System</p>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
  <script>
    particlesJS('particles-js', {
      "particles": {
        "number": {
          "value": 80,
          "density": {
            "enable": true,
            "value_area": 800
          }
        },
        "color": {
          "value": "#4A90E2"
        },
        "shape": {
          "type": "circle",
          "stroke": {
            "width": 0,
            "color": "#000000"
          }
        },
        "opacity": {
          "value": 0.5,
          "random": false,
          "anim": {
            "enable": false,
            "speed": 1,
            "opacity_min": 0.1,
            "sync": false
          }
        },
        "size": {
          "value": 3,
          "random": true,
          "anim": {
            "enable": false,
            "speed": 40,
            "size_min": 0.1,
            "sync": false
          }
        },
        "line_linked": {
          "enable": true,
          "distance": 150,
          "color": "#4A90E2",
          "opacity": 0.4,
          "width": 1
        },
        "move": {
          "enable": true,
          "speed": 6,
          "direction": "none",
          "random": false,
          "straight": false,
          "out_mode": "out",
          "bounce": false,
          "attract": {
            "enable": false,
            "rotateX": 600,
            "rotateY": 1200
          }
        }
      },
      "interactivity": {
        "detect_on": "canvas",
        "events": {
          "onhover": {
            "enable": true,
            "mode": "repulse"
          },
          "onclick": {
            "enable": true,
            "mode": "push"
          },
          "resize": true
        },
        "modes": {
          "grab": {
            "distance": 400,
            "line_linked": {
              "opacity": 1
            }
          },
          "bubble": {
            "distance": 400,
            "size": 40,
            "duration": 2,
            "opacity": 8,
            "speed": 3
          },
          "repulse": {
            "distance": 200,
            "duration": 0.4
          },
          "push": {
            "particles_nb": 4
          },
          "remove": {
            "particles_nb": 2
          }
        }
      },
      "retina_detect": true
    });
  </script>
</body>
</html>
