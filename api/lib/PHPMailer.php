<?php
/**
 * PHPMailer - PHP email creation and transport class.
 * Simplified version for Gmail SMTP
 */

class PHPMailer {
    public $Host = 'smtp.gmail.com';
    public $Port = 587;
    public $SMTPAuth = true;
    public $Username = '';
    public $Password = '';
    public $SMTPSecure = 'tls';
    public $From = '';
    public $FromName = '';
    public $Subject = '';
    public $Body = '';
    public $IsHTML = true;
    private $to = [];
    
    public function addAddress($email, $name = '') {
        $this->to[] = ['email' => $email, 'name' => $name];
    }
    
    public function send() {
        $headers = "MIME-Version: 1.0\r\n";
        if ($this->IsHTML) {
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        }
        $headers .= "From: {$this->FromName} <{$this->From}>\r\n";
        
        // Use SMTP
        $smtp = fsockopen($this->Host, $this->Port, $errno, $errstr, 30);
        if (!$smtp) {
            return false;
        }
        
        // SMTP conversation
        $this->smtpCommand($smtp, "EHLO localhost\r\n");
        $this->smtpCommand($smtp, "STARTTLS\r\n");
        
        // Upgrade to TLS
        stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        
        $this->smtpCommand($smtp, "EHLO localhost\r\n");
        $this->smtpCommand($smtp, "AUTH LOGIN\r\n");
        $this->smtpCommand($smtp, base64_encode($this->Username) . "\r\n");
        $this->smtpCommand($smtp, base64_encode($this->Password) . "\r\n");
        $this->smtpCommand($smtp, "MAIL FROM: <{$this->From}>\r\n");
        
        foreach ($this->to as $recipient) {
            $this->smtpCommand($smtp, "RCPT TO: <{$recipient['email']}>\r\n");
        }
        
        $this->smtpCommand($smtp, "DATA\r\n");
        
        $message = "Subject: {$this->Subject}\r\n";
        $message .= $headers . "\r\n";
        $message .= $this->Body . "\r\n.\r\n";
        
        $this->smtpCommand($smtp, $message);
        $this->smtpCommand($smtp, "QUIT\r\n");
        
        fclose($smtp);
        return true;
    }
    
    private function smtpCommand($smtp, $command) {
        fputs($smtp, $command);
        return fgets($smtp, 512);
    }
}
?>
