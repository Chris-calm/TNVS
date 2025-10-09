<?php
// Proper SMTP mailer for Gmail
function sendGmailSMTP($to, $subject, $message, $config) {
    $smtp_server = $config['smtp_host'];
    $smtp_port = $config['smtp_port'];
    $smtp_username = $config['smtp_username'];
    $smtp_password = $config['smtp_password'];
    $from_email = $config['from_email'];
    $from_name = $config['from_name'];
    
    // Create socket connection
    $socket = fsockopen($smtp_server, $smtp_port, $errno, $errstr, 30);
    
    if (!$socket) {
        return false;
    }
    
    // Read initial response
    $response = fgets($socket, 515);
    
    // Send EHLO command
    fputs($socket, "EHLO localhost\r\n");
    $response = fgets($socket, 515);
    
    // Start TLS
    fputs($socket, "STARTTLS\r\n");
    $response = fgets($socket, 515);
    
    // Enable crypto
    stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
    
    // Send EHLO again after TLS
    fputs($socket, "EHLO localhost\r\n");
    $response = fgets($socket, 515);
    
    // Authenticate
    fputs($socket, "AUTH LOGIN\r\n");
    $response = fgets($socket, 515);
    
    fputs($socket, base64_encode($smtp_username) . "\r\n");
    $response = fgets($socket, 515);
    
    fputs($socket, base64_encode($smtp_password) . "\r\n");
    $response = fgets($socket, 515);
    
    // Check if authentication was successful
    if (strpos($response, '235') === false) {
        fclose($socket);
        return false;
    }
    
    // Send email
    fputs($socket, "MAIL FROM: <$from_email>\r\n");
    $response = fgets($socket, 515);
    
    fputs($socket, "RCPT TO: <$to>\r\n");
    $response = fgets($socket, 515);
    
    fputs($socket, "DATA\r\n");
    $response = fgets($socket, 515);
    
    // Email headers and body
    $email_data = "From: $from_name <$from_email>\r\n";
    $email_data .= "To: <$to>\r\n";
    $email_data .= "Subject: $subject\r\n";
    $email_data .= "MIME-Version: 1.0\r\n";
    $email_data .= "Content-Type: text/html; charset=UTF-8\r\n";
    $email_data .= "\r\n";
    $email_data .= $message;
    $email_data .= "\r\n.\r\n";
    
    fputs($socket, $email_data);
    $response = fgets($socket, 515);
    
    // Quit
    fputs($socket, "QUIT\r\n");
    $response = fgets($socket, 515);
    
    fclose($socket);
    
    // Check if email was accepted
    return strpos($response, '250') !== false;
}
?>
