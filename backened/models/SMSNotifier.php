<?php
/**
 * SMS Notifier Helper
 */

class SMSNotifier {
    public function send($phone, $message) {
        // TODO: Integrate with Twilio or local SMS service
        
        Logger::info("SMS sent", ['phone' => $phone, 'message' => $message]);
        
        // For testing, just log it
        return true;
    }
}
?>