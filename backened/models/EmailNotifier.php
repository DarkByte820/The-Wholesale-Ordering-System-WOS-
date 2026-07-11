<?php
/**
 * Email Notifier Helper
 */

class EmailNotifier {
    private $fromEmail = 'noreply@gwc.com';
    private $fromName = 'Ghana Warehouse Connect';
    
    public function send($toEmail, $subject, $message) {
        // TODO: Integrate with SendGrid or PHPMailer
        
        $headers = "From: {$this->fromName} <{$this->fromEmail}>\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        Logger::info("Email sent", ['to' => $toEmail, 'subject' => $subject]);
        
        // For testing, just log it
        // In production, use mail() or SMTP service
        return true;
    }
}
?>