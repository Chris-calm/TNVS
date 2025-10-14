<?php
session_start();
require_once 'db_connect.php';

// Redirect if no pending OTP verification
if (!isset($_SESSION['pending_otp_user_id'])) {
    header("Location: ../index.php");
    exit();
}

$error_message = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $entered_otp = trim($_POST['otp']);
    $user_id = $_SESSION['pending_otp_user_id'];
    
    // Verify OTP
    $stmt = $conn->prepare("SELECT id, username, otp_code, expires_at FROM user_otps WHERE user_id = ? AND is_used = FALSE ORDER BY created_at DESC LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $otp_record = $result->fetch_assoc();
        
        // Check if OTP has expired
        if (strtotime($otp_record['expires_at']) < time()) {
            $error_message = "OTP has expired. Please request a new one.";
        } else if ($otp_record['otp_code'] === $entered_otp) {
            // OTP is valid - mark as used and login user
            $update_stmt = $conn->prepare("UPDATE user_otps SET is_used = TRUE WHERE id = ?");
            $update_stmt->bind_param("i", $otp_record['id']);
            $update_stmt->execute();
            $update_stmt->close();
            
            // Get user details and set session
            $user_stmt = $conn->prepare("SELECT id, username, role FROM users WHERE id = ?");
            $user_stmt->bind_param("i", $user_id);
            $user_stmt->execute();
            $user_result = $user_stmt->get_result();
            $user = $user_result->fetch_assoc();
            $user_stmt->close();
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            // Clear pending OTP session
            unset($_SESSION['pending_otp_user_id']);
            unset($_SESSION['pending_otp_username']);
            
            
            $stmt->close();
            $conn->close();
            
            // Multiple redirect methods to ensure it works
            header("Location: Dashboard.php");
            echo '<script>window.location.href = "Dashboard.php";</script>';
            echo '<meta http-equiv="refresh" content="0;url=Dashboard.php">';
            exit();
        } else {
            $error_message = "Invalid OTP. Please try again.";
        }
    } else {
        $error_message = "No valid OTP found. Please request a new one.";
    }
    
    $stmt->close();
}

// Handle resend OTP request
if (isset($_GET['resend']) && $_GET['resend'] === '1') {
    require_once 'email_config.php';
    
    $user_id = $_SESSION['pending_otp_user_id'];
    $username = $_SESSION['pending_otp_username'];
    
    // Get user email
    $email_stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
    $email_stmt->bind_param("i", $user_id);
    $email_stmt->execute();
    $email_result = $email_stmt->get_result();
    $user_data = $email_result->fetch_assoc();
    $email_stmt->close();
    
    if ($user_data && $user_data['email']) {
        // Generate new OTP
        $new_otp = generateOTP();
        $expires_at = date('Y-m-d H:i:s', time() + 300); // 5 minutes from now
        
        // Store new OTP
        $otp_stmt = $conn->prepare("INSERT INTO user_otps (user_id, username, otp_code, email, expires_at) VALUES (?, ?, ?, ?, ?)");
        $otp_stmt->bind_param("issss", $user_id, $username, $new_otp, $user_data['email'], $expires_at);
        $otp_stmt->execute();
        $otp_stmt->close();
        
        // Send email
        if (sendOTPEmail($user_data['email'], $username, $new_otp)) {
            $success_message = "New OTP has been sent to your email.";
        } else {
            $error_message = "Failed to send OTP. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TNVS - Verify OTP</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif;
      margin: 0;
      padding: 0;
      height: 100vh;
      overflow: hidden;
    }

    #particles-js {
      position: absolute;
      width: 50%;
      height: 100%;
      top: 0;
      left: 0;
      z-index: 1;
    }

    #trianglify-canvas {
      position: absolute;
      width: 50%;
      height: 100%;
      top: 0;
      right: 0;
      z-index: 1;
    }

    .otp-container {
      display: flex;
      width: 100%;
      height: 100vh;
      overflow: hidden;
    }

    .otp-left {
      flex: 1;
      padding: 60px 50px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: flex-end;
      background: #f8f9fa;
      position: relative;
    }

    .otp-form-container {
      width: 100%;
      max-width: 400px;
      padding: 40px;
      position: relative;
      z-index: 3;
    }

    .otp-right {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: flex-start;
      padding: 60px 50px;
      color: #ffffff;
      position: relative;
      padding-left: 10px;
    }

    .right-text {
      text-align: left;
      z-index: 3;
      position: relative;
    }

    .right-text h2 {
      font-size: 36px;
      font-weight: 700;
      color: #ffffff;
      margin: 0;
      text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
      line-height: 1.2;
      transition: all 0.3s ease;
      cursor: default;
    }

    .right-text h2:hover {
      transform: translateY(-2px);
      text-shadow: 0 4px 15px rgba(0, 0, 0, 0.7);
    }

    .logo-section {
      text-align: center;
      margin-bottom: 40px;
    }

    .logo-container {
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
      transition: all 0.3s ease;
    }

    .logo-container:hover {
      transform: scale(1.05);
    }

    .logo-icon {
      width: 120px;
      height: 120px;
      object-fit: contain;
      transition: transform 0.3s ease;
      filter: drop-shadow(0 4px 15px rgba(0, 123, 255, 0.3));
    }

    .logo-container:hover .logo-icon {
      transform: rotate(2deg);
      filter: drop-shadow(0 6px 20px rgba(0, 123, 255, 0.4));
    }

    .logo-section h1 {
      font-size: 24px;
      font-weight: 600;
      color: rgb(12, 46, 94);
      margin-bottom: 8px;
    }

    .otp-header {
      text-align: center;
      margin-bottom: 30px;
    }

    .otp-header h2 {
      font-size: 20px;
      font-weight: 600;
      color: #1a365d;
      margin-bottom: 8px;
    }

    .otp-header p {
      font-size: 14px;
      color: #666;
    }

    .form-group {
      margin-bottom: 24px;
    }

    .form-group label {
      display: block;
      font-size: 14px;
      font-weight: 500;
      color: #1a365d;
      margin-bottom: 8px;
    }

    .otp-inputs {
      display: flex;
      gap: 12px;
      justify-content: center;
      margin-top: 8px;
    }

    .otp-inputs input {
      width: 50px;
      height: 50px;
      padding: 0;
      border: 2px solid #ddd;
      border-radius: 8px;
      font-size: 20px;
      font-weight: 600;
      color: #1a365d;
      text-align: center;
      transition: all 0.3s ease;
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(5px);
    }

    .otp-inputs input:hover {
      border-color: #007bff;
      background: rgba(255, 255, 255, 0.95);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0, 123, 255, 0.15);
    }

    .otp-inputs input:focus {
      outline: none;
      border-color: #007bff;
      background: rgba(255, 255, 255, 1);
      box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
      transform: translateY(-2px);
      border-width: 2px;
    }

    .otp-inputs input.filled {
      background: rgba(0, 123, 255, 0.1);
      border-color: #007bff;
    }

    .otp-inputs input.error {
      border-color: #dc3545;
      background: rgba(220, 53, 69, 0.1);
      animation: shake 0.5s ease-in-out;
    }

    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      25% { transform: translateX(-5px); }
      75% { transform: translateX(5px); }
    }

    .btn-verify {
      width: 100%;
      padding: 14px;
      background: #007bff;
      border: none;
      border-radius: 8px;
      color: #fff;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      margin-top: 10px;
    }

    .btn-verify:hover {
      background: #0056b3;
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
    }

    .btn-verify:active {
      transform: translateY(0);
    }

    .btn-resend {
      background: none;
      border: none;
      color: #007bff;
      font-size: 14px;
      cursor: pointer;
      text-decoration: underline;
      margin-top: 15px;
      transition: all 0.3s ease;
      display: block;
      text-align: center;
      width: 100%;
    }

    .btn-resend:hover {
      color: #0056b3;
      transform: translateY(-1px);
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

    .success-message {
      background: #efe;
      border: 1px solid #cfc;
      color: #3c3;
      padding: 12px 16px;
      border-radius: 8px;
      font-size: 14px;
      margin-top: 20px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .timer {
      font-size: 12px;
      color: #666;
      margin-top: 15px;
      text-align: center;
    }

    .back-link {
      margin-top: 20px;
      text-align: center;
    }

    .back-link a {
      color: #007bff;
      text-decoration: none;
      font-size: 14px;
      transition: all 0.3s ease;
    }

    .back-link a:hover {
      text-decoration: underline;
      transform: translateY(-1px);
    }

    @media (max-width: 768px) {
      .otp-container {
        flex-direction: column;
      }

      .otp-right {
        order: -1;
        padding: 40px 30px;
      }

      .otp-left {
        padding: 40px 30px;
      }
    }
  </style>
</head>
<body>
  <div id="particles-js"></div>
  <canvas id="trianglify-canvas"></canvas>
  <div class="otp-container">
    <div class="otp-left">
      <div class="otp-form-container">
        <div class="logo-section">
          <div class="logo-container">
            <img src="../PICTURES/TONVS_Logo_Transparent.png" alt="TNVS Logo" class="logo-icon">
          </div>
          <h1>Verify OTP</h1>
        </div>

        <div class="otp-header">
          <h2>Enter Verification Code</h2>
          <p>We've sent a 6-digit code to your email</p>
        </div>

        <form method="POST" id="otpForm">
          <div class="form-group">
            <label for="otp1">OTP Code *</label>
            <div class="otp-inputs">
              <input type="text" id="otp1" name="otp1" maxlength="1" required autofocus>
              <input type="text" id="otp2" name="otp2" maxlength="1" required>
              <input type="text" id="otp3" name="otp3" maxlength="1" required>
              <input type="text" id="otp4" name="otp4" maxlength="1" required>
              <input type="text" id="otp5" name="otp5" maxlength="1" required>
              <input type="text" id="otp6" name="otp6" maxlength="1" required>
            </div>
            <input type="hidden" id="otp" name="otp" value="">
          </div>

          <button type="submit" class="btn-verify">Verify & Login</button>
        </form>

        <div class="timer">
          <p>Code expires in <span id="countdown">5:00</span></p>
        </div>

        <button type="button" class="btn-resend" onclick="resendOTP()">Didn't receive code? Resend</button>

        <div class="back-link">
          <a href="../index.php">← Back to Login</a>
        </div>
      </div>
      
      <?php if ($error_message): ?>
        <div class="error-message">
          <span>⚠️</span>
          <span><?= htmlspecialchars($error_message) ?></span>
        </div>
      <?php endif; ?>
      
      <?php if ($success_message): ?>
        <div class="success-message">
          <span>✅</span>
          <span><?= htmlspecialchars($success_message) ?></span>
        </div>
      <?php endif; ?>
    </div>

    <div class="otp-right">
      <div class="right-text">
        <h2>Secure Access</h2>
        <h2>Verification</h2>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
  <script src="https://unpkg.com/trianglify@^4/dist/trianglify.bundle.js"></script>
  <script>
    particlesJS('particles-js', {
      "particles": {
        "number": {
          "value": 120,
          "density": {
            "enable": true,
            "value_area": 800
          }
        },
        "color": {
          "value": "#007bff"
        },
        "shape": {
          "type": "circle",
          "stroke": {
            "width": 0,
            "color": "#000000"
          }
        },
        "opacity": {
          "value": 0.4,
          "random": false,
          "anim": {
            "enable": false,
            "speed": 1,
            "opacity_min": 0.1,
            "sync": false
          }
        },
        "size": {
          "value": 4,
          "random": true,
          "anim": {
            "enable": false,
            "speed": 40,
            "size_min": 2,
            "sync": false
          }
        },
        "line_linked": {
          "enable": true,
          "distance": 150,
          "color": "#007bff",
          "opacity": 0.3,
          "width": 1
        },
        "move": {
          "enable": true,
          "speed": 3,
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
            "mode": "grab"
          },
          "onclick": {
            "enable": false,
            "mode": "push"
          },
          "resize": true
        },
        "modes": {
          "grab": {
            "distance": 200,
            "line_linked": {
              "opacity": 0.6
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

    // Initialize Trianglify for the right side
    function initTrianglify() {
      const canvas = document.getElementById('trianglify-canvas');
      const pattern = trianglify({
        width: window.innerWidth / 2,
        height: window.innerHeight,
        cellSize: 75,
        variance: 0.75,
        xColors: ['#1a365d', '#4a90e2'],
        yColors: 'match',
        fill: true,
        strokeWidth: 0,
        seed: null
      });
      
      pattern.toCanvas(canvas);
    }

    // Initialize Trianglify when page loads
    window.addEventListener('load', initTrianglify);
    
    // Reinitialize on window resize
    window.addEventListener('resize', initTrianglify);

    // Countdown timer
    let timeLeft = 300; // 5 minutes in seconds
    const countdownElement = document.getElementById('countdown');

    function updateCountdown() {
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        countdownElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        
        if (timeLeft <= 0) {
            countdownElement.textContent = "Expired";
            countdownElement.style.color = "#c33";
        } else {
            timeLeft--;
        }
    }

    // Update countdown every second
    setInterval(updateCountdown, 1000);
    updateCountdown(); // Initial call

    // OTP Input Handling
    const otpInputs = document.querySelectorAll('.otp-inputs input');
    const hiddenOtpInput = document.getElementById('otp');
    const otpForm = document.getElementById('otpForm');

    otpInputs.forEach((input, index) => {
        // Only allow numbers
        input.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
            
            // Add filled class when input has value
            if (this.value) {
                this.classList.add('filled');
            } else {
                this.classList.remove('filled');
            }
            
            // Auto-focus next input
            if (this.value && index < otpInputs.length - 1) {
                otpInputs[index + 1].focus();
            }
            
            // Update hidden input and check for auto-submit
            updateHiddenInput();
        });

        // Handle backspace
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && !this.value && index > 0) {
                otpInputs[index - 1].focus();
                otpInputs[index - 1].value = '';
                otpInputs[index - 1].classList.remove('filled');
                updateHiddenInput();
            }
        });

        // Handle paste
        input.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedData = e.clipboardData.getData('text').replace(/[^0-9]/g, '');
            
            if (pastedData.length === 6) {
                otpInputs.forEach((inp, i) => {
                    inp.value = pastedData[i] || '';
                    if (inp.value) {
                        inp.classList.add('filled');
                    }
                });
                updateHiddenInput();
                // Auto-submit after paste
                setTimeout(() => {
                    if (hiddenOtpInput.value.length === 6) {
                        otpForm.submit();
                    }
                }, 300);
            }
        });
    });

    function updateHiddenInput() {
        const otpValue = Array.from(otpInputs).map(input => input.value).join('');
        hiddenOtpInput.value = otpValue;
        
        // Auto-submit when all 6 digits are entered
        if (otpValue.length === 6) {
            setTimeout(() => {
                otpForm.submit();
            }, 300);
        }
    }

    // Clear all inputs and show error animation
    function showError() {
        otpInputs.forEach(input => {
            input.classList.add('error');
            input.classList.remove('filled');
            setTimeout(() => {
                input.classList.remove('error');
            }, 500);
        });
        // Focus first input after error
        setTimeout(() => {
            otpInputs[0].focus();
        }, 500);
    }

    // Resend OTP function
    function resendOTP() {
        // Clear all inputs
        otpInputs.forEach(input => {
            input.value = '';
            input.classList.remove('filled', 'error');
        });
        hiddenOtpInput.value = '';
        window.location.href = 'verify_otp.php?resend=1';
    }
  </script>
</body>
</html>
