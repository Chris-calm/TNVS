<?php
// Working Gmail SMTP implementation
class GmailSMTP {
    private $smtp_host = 'smtp.gmail.com';
    private $smtp_port = 587;
    private $username;
    private $password;
    private $socket;
    
    public function __construct($username, $password) {
        $this->username = $username;
        $this->password = $password;
    }
    
    public function sendEmail($to, $subject, $message, $from_name = 'TNVS System') {
        try {
            // Create socket connection
            $this->socket = fsockopen($this->smtp_host, $this->smtp_port, $errno, $errstr, 30);
            
            if (!$this->socket) {
                throw new Exception("Failed to connect to SMTP server: $errstr ($errno)");
            }
            
            // Set timeout
            stream_set_timeout($this->socket, 30);
            
            // Read server greeting
            $this->readResponse();
            
            // Send EHLO
            $this->sendCommand("EHLO localhost");
            
            // Start TLS
            $this->sendCommand("STARTTLS");
            
            // Enable crypto
            if (!stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new Exception("Failed to enable TLS encryption");
            }
            
            // Send EHLO again after TLS
            $this->sendCommand("EHLO localhost");
            
            // Authenticate
            $this->sendCommand("AUTH LOGIN");
            $this->sendCommand(base64_encode($this->username));
            $this->sendCommand(base64_encode($this->password));
            
            // Send email
            $this->sendCommand("MAIL FROM: <{$this->username}>");
            $this->sendCommand("RCPT TO: <$to>");
            $this->sendCommand("DATA");
            
            // Email headers and body
            $email_data = "From: $from_name <{$this->username}>\r\n";
            $email_data .= "To: <$to>\r\n";
            $email_data .= "Subject: $subject\r\n";
            $email_data .= "MIME-Version: 1.0\r\n";
            $email_data .= "Content-Type: text/html; charset=UTF-8\r\n";
            $email_data .= "Date: " . date('r') . "\r\n";
            $email_data .= "\r\n";
            $email_data .= $message;
            $email_data .= "\r\n.";
            
            fwrite($this->socket, $email_data . "\r\n");
            $this->readResponse();
            
            // Quit
            $this->sendCommand("QUIT");
            
            fclose($this->socket);
            return true;
            
        } catch (Exception $e) {
            if ($this->socket) {
                fclose($this->socket);
            }
            error_log("Gmail SMTP Error: " . $e->getMessage());
            return false;
        }
    }
    
    private function sendCommand($command) {
        fwrite($this->socket, $command . "\r\n");
        return $this->readResponse();
    }
    
    private function readResponse() {
        $response = '';
        while (($line = fgets($this->socket, 515)) !== false) {
            $response .= $line;
            if (substr($line, 3, 1) == ' ') {
                break;
            }
        }
        
        $code = substr($response, 0, 3);
        if ($code >= 400) {
            throw new Exception("SMTP Error: $response");
        }
        
        return $response;
    }
}

// Function to send OTP via Gmail
function sendGmailOTP($to_email, $username, $otp, $config) {
    $gmail = new GmailSMTP($config['smtp_username'], $config['smtp_password']);
    
    $subject = "TNVS Login - Your OTP Code";
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; }
            .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; }
            .header { background: linear-gradient(135deg, #4A90E2 0%, #0066CC 100%); color: white; padding: 30px; text-align: center; }
            .content { padding: 30px; }
            .otp-box { background: #f8f9fa; border: 2px solid #4A90E2; border-radius: 10px; padding: 20px; text-align: center; margin: 20px 0; }
            .otp-code { font-size: 32px; font-weight: bold; color: #4A90E2; letter-spacing: 5px; margin: 10px 0; }
            .footer { background: #f8f9fa; padding: 20px; text-align: center; color: #666; font-size: 12px; }
            .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🔐 TNVS Security Verification</h1>
                <p>Transport Network Vehicle System</p>
            </div>
            <div class='content'>
                <h2>Hello, $username!</h2>
                <p>You have requested to login to the TNVS system. Please use the following One-Time Password (OTP) to complete your login:</p>
                
                <div class='otp-box'>
                    <p style='margin: 0; font-size: 16px; color: #666;'>Your OTP Code:</p>
                    <div class='otp-code'>$otp</div>
                    <p style='margin: 0; font-size: 14px; color: #666;'>Enter this code in the verification page</p>
                </div>
                
                <div class='warning'>
                    <p><strong>⚠️ Security Notice:</strong></p>
                    <ul style='text-align: left; margin: 10px 0;'>
                        <li>This code will expire in <strong>5 minutes</strong></li>
                        <li>Do not share this code with anyone</li>
                        <li>If you did not request this login, please ignore this email</li>
                    </ul>
                </div>
                
                <p>If you have any questions, please contact your system administrator.</p>
            </div>
            <div class='footer'>
                <p>This is an automated message from TNVS System.</p>
                <p>Please do not reply to this email.</p>
                <p>© 2024 Transport Network Vehicle System</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return $gmail->sendEmail($to_email, $subject, $message, $config['from_name']);
}
?>
