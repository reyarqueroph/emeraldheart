<?php
/**
 * Simple SMTP Email Sender for Gmail
 */
class EmailSender {
    private $config;
    
    public function __construct($config) {
        $this->config = $config;
    }
    
    public function sendEmail($to, $subject, $htmlBody) {
        try {
            // Create socket connection
            $smtp = fsockopen(
                $this->config['smtp_host'],
                $this->config['smtp_port'],
                $errno,
                $errstr,
                30
            );
            
            if (!$smtp) {
                throw new Exception("Could not connect to SMTP server: $errstr ($errno)");
            }
            
            // Read server greeting
            $this->getResponse($smtp);
            
            // Send EHLO
            $this->sendCommand($smtp, "EHLO " . $_SERVER['SERVER_NAME'] . "\r\n");
            
            // Start TLS
            $this->sendCommand($smtp, "STARTTLS\r\n");
            
            // Enable crypto
            stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            
            // Send EHLO again after TLS
            $this->sendCommand($smtp, "EHLO " . $_SERVER['SERVER_NAME'] . "\r\n");
            
            // Authenticate
            $this->sendCommand($smtp, "AUTH LOGIN\r\n");
            $this->sendCommand($smtp, base64_encode($this->config['smtp_username']) . "\r\n");
            $this->sendCommand($smtp, base64_encode($this->config['smtp_password']) . "\r\n");
            
            // Send MAIL FROM
            $this->sendCommand($smtp, "MAIL FROM: <{$this->config['from_email']}>\r\n");
            
            // Send RCPT TO
            $this->sendCommand($smtp, "RCPT TO: <{$to}>\r\n");
            
            // Send DATA
            $this->sendCommand($smtp, "DATA\r\n");
            
            // Build email headers and body
            $headers = "From: {$this->config['from_name']} <{$this->config['from_email']}>\r\n";
            $headers .= "To: <{$to}>\r\n";
            $headers .= "Subject: {$subject}\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "\r\n";
            
            $message = $headers . $htmlBody . "\r\n.\r\n";
            
            // Send the email
            fputs($smtp, $message);
            $this->getResponse($smtp);
            
            // Send QUIT
            $this->sendCommand($smtp, "QUIT\r\n");
            
            fclose($smtp);
            return true;
            
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }
    
    private function sendCommand($smtp, $command) {
        fputs($smtp, $command);
        return $this->getResponse($smtp);
    }
    
    private function getResponse($smtp) {
        $response = '';
        while ($line = fgets($smtp, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) == ' ') {
                break;
            }
        }
        return $response;
    }
}
?>
