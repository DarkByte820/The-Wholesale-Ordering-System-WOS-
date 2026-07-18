<?php
/**
 * Notification Service - FR17
 * FR17: Notifications - send alerts for events
 * Event types: registration, order confirmation, payment, dispatch, delivery, cancellation, stock update
 */

class NotificationService {
    private $notification;
    private $emailNotifier;
    private $smsNotifier;
    
    public function __construct() {
        $this->notification = new Notification();
        $this->emailNotifier = new EmailNotifier();
        $this->smsNotifier = new SMSNotifier();
    }
    
    /**
     * FR17: Send notification based on event
     */
    public function sendNotification($userId, $eventType, $message, $channel = 'All', $orderId = null) {
        // Create notification record
        $notifId = $this->notification->createNotification($userId, $eventType, $message, $channel, $orderId);
        
        if (!$notifId) {
            return ['success' => false, 'message' => 'Failed to create notification'];
        }
        
        // Get user email/phone
        $userStmt = $GLOBALS['db']->prepare("SELECT Email, Phone FROM user WHERE UserID = ?");
        $userStmt->bind_param("i", $userId);
        $userStmt->execute();
        $user = $userStmt->get_result()->fetch_assoc();
        
        // Send through appropriate channels
        if ($channel === 'All' || $channel === 'Email') {
            $this->emailNotifier->send($user['Email'], $eventType, $message);
        }
        
        if ($channel === 'All' || $channel === 'SMS') {
            $this->smsNotifier->send($user['Phone'], $message);
        }
        
        return ['success' => true, 'notificationId' => $notifId];
    }
    
    /**
     * Send registration notification
     */
    public function sendRegistrationNotification($userId, $userName, $email) {
        $message = "Welcome $userName! Your account has been created successfully.";
        return $this->sendNotification($userId, 'Registration', $message, 'Email');
    }
    
    /**
     * Send order confirmation notification
     */
    public function sendOrderConfirmation($orderId, $userId, $totalAmount) {
        $message = "Your order #$orderId has been confirmed. Total: GHS " . number_format($totalAmount, 2);
        return $this->sendNotification($userId, 'OrderConfirmation', $message, 'All', $orderId);
    }
    
    /**
     * Send payment confirmation
     */
    public function sendPaymentConfirmation($orderId, $userId, $amount, $provider) {
        $message = "Payment of GHS " . number_format($amount, 2) . " via $provider for order #$orderId has been received.";
        return $this->sendNotification($userId, 'PaymentStatus', $message, 'All', $orderId);
    }
    
    /**
     * Send dispatch notification
     */
    public function sendDispatchNotification($orderId, $userId) {
        $message = "Your order #$orderId has been dispatched and is on the way!";
        return $this->sendNotification($userId, 'Dispatch', $message, 'All', $orderId);
    }
    
    /**
     * Send delivery notification
     */
    public function sendDeliveryNotification($orderId, $userId) {
        $message = "Your order #$orderId has been delivered. Thank you for shopping with us!";
        return $this->sendNotification($userId, 'Delivery', $message, 'All', $orderId);
    }
    
    /**
     * Send cancellation notification
     */
    public function sendCancellationNotification($orderId, $userId) {
        $message = "Your order #$orderId has been cancelled. Stock has been restored.";
        return $this->sendNotification($userId, 'Cancellation', $message, 'All', $orderId);
    }
    
    /**
     * Send stock update notification
     */
    public function sendStockUpdateNotification($userId, $productName) {
        $message = "$productName is now back in stock!";
        return $this->sendNotification($userId, 'StockUpdate', $message, 'Email');
    }
    
    /**
     * Send low stock alert to admin
     */
    public function sendLowStockAlert($adminId, $productName, $quantity) {
        $message = "ALERT: $productName stock is low ($quantity units remaining)";
        return $this->sendNotification($adminId, 'LowStock', $message, 'Email');
    }
}
?>