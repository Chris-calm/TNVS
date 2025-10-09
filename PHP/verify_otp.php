<?php
session_start();
require_once 'db_connect.php';

// Redirect if no pending OTP verification
if (!isset($_SESSION['pending_otp_user_id'])) {
    header("Location: index.php");
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
            
            header("Location: Dashboard.php");
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
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .otp-container {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .otp-header {
            margin-bottom: 30px;
        }

        .otp-header h1 {
            font-size: 24px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .otp-header p {
            font-size: 14px;
            color: #7f8c8d;
        }

        .otp-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #4A90E2 0%, #0066CC 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 36px;
        }

        .form-group {
            margin-bottom: 24px;
            text-align: left;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .otp-input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 18px;
            text-align: center;
            letter-spacing: 2px;
            color: #2c3e50;
            transition: all 0.3s ease;
            background: #fafafa;
        }

        .otp-input:focus {
            outline: none;
            border-color: #4A90E2;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
        }

        .btn-verify {
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
            margin-bottom: 15px;
        }

        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(74, 144, 226, 0.4);
        }

        .btn-resend {
            background: none;
            border: none;
            color: #4A90E2;
            font-size: 14px;
            cursor: pointer;
            text-decoration: underline;
            margin-top: 10px;
        }

        .btn-resend:hover {
            color: #0066CC;
        }

        .error-message {
            background: #fee;
            border: 1px solid #fcc;
            color: #c33;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 20px;
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
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .timer {
            font-size: 12px;
            color: #7f8c8d;
            margin-top: 10px;
        }

        .back-link {
            margin-top: 20px;
        }

        .back-link a {
            color: #4A90E2;
            text-decoration: none;
            font-size: 14px;
        }

        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="otp-container">
        <div class="otp-header">
            <div class="otp-icon">📧</div>
            <h1>Verify Your Identity</h1>
            <p>We've sent a 6-digit code to your email</p>
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

        <form method="POST">
            <div class="form-group">
                <label for="otp">Enter OTP Code</label>
                <input type="text" id="otp" name="otp" class="otp-input" placeholder="000000" maxlength="6" required autofocus>
            </div>

            <button type="submit" class="btn-verify">Verify & Login</button>
        </form>

        <div class="timer">
            <p>Code expires in <span id="countdown">5:00</span></p>
        </div>

        <button type="button" class="btn-resend" onclick="resendOTP()">Didn't receive code? Resend</button>

        <div class="back-link">
            <a href="index.php">← Back to Login</a>
        </div>
    </div>

    <script>
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

        // Auto-format OTP input
        document.getElementById('otp').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        // Resend OTP function
        function resendOTP() {
            window.location.href = 'verify_otp.php?resend=1';
        }

        // Auto-submit when 6 digits are entered
        document.getElementById('otp').addEventListener('input', function(e) {
            if (this.value.length === 6) {
                // Small delay to show the complete code
                setTimeout(() => {
                    document.querySelector('form').submit();
                }, 500);
            }
        });
    </script>
</body>
</html>
