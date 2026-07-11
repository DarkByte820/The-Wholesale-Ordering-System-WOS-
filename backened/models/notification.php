<?php
/**
 * Notifications Model
 * Handles System Notifications & Alerts
 */

class Notifications {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();

    }
    
    public function createNotification($user_id, $event_type, $message, $channel = 'All', $order_id = null) {
        $status = 'Pending';
        
        $stmt = $this->db->prepare("INSERT INTO Notifications (UserID, EventType, Message, NotificationChannel, Status, RelatedOrderID) VALUES (?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("issssi", $user_id, $event_type, $message, $channel, $status, $order_id);
        
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        
        error_log("Execute failed: " . $stmt->error);
        return false;
    }
    
    public function getNotifications($user_id, $page = 1, $limit = ITEMS_PER_PAGE) {
        $offset = ($page - 1) * $limit;
        
        $stmt = $this->db->prepare("SELECT * FROM Notifications WHERE UserID = ? ORDER BY Timestamp DESC LIMIT ? OFFSET ?");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return [];
        }
        
        $stmt->bind_param("iii", $user_id, $limit, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getUnreadNotifications($user_id) {
        $status = 'Pending';
        
        $stmt = $this->db->prepare("SELECT * FROM Notifications WHERE UserID = ? AND Status = ? ORDER BY Timestamp DESC");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return [];
        }
        
        $stmt->bind_param("is", $user_id, $status);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getUnreadCount($user_id) {
        $status = 'Pending';
        
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM Notifications WHERE UserID = ? AND Status = ?");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return 0;
        }
        
        $stmt->bind_param("is", $user_id, $status);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['count'] ?? 0;
    }
    
    public function markAsRead($notification_id) {
        $status = 'Read';
        
        $stmt = $this->db->prepare("UPDATE Notifications SET Status = ?, ReadDate = NOW() WHERE NotificationID = ?");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("si", $status, $notification_id);
        return $stmt->execute();
    }
    
    public function markAllAsRead($user_id) {
        $status = 'Read';
        $pending = 'Pending';
        
        $stmt = $this->db->prepare("UPDATE Notifications SET Status = ?, ReadDate = NOW() WHERE UserID = ? AND Status = ?");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("sis", $status, $user_id, $pending);
        return $stmt->execute();
    }
    
    public function sendNotificationByEvent($order_id, $event_type) {
        // Get order and user info
        $order_stmt = $this->db->prepare("SELECT UserID, Status, TotalAmount FROM `order` WHERE OrderID = ?");
        
        if (!$order_stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }
        
        $order_stmt->bind_param("i", $order_id);
        $order_stmt->execute();
        $order = $order_stmt->get_result()->fetch_assoc();
        
        if (!$order) {
            return false;
        }
        
        // Create appropriate Notifications based on event
        $messages = [
            'OrderConfirmation' => "Your order #$order_id has been confirmed. Amount: GHS {$order['TotalAmount']}",
            'Dispatch' => "Your order #$order_id has been dispatched and is on the way!",
            'Delivery' => "Your order #$order_id has been delivered. Thank you for shopping!",
            'PaymentStatus' => "Payment for order #$order_id has been received.",
            'Cancellation' => "Your order #$order_id has been cancelled. Stock has been restored."
        ];
        
        $message = $messages[$event_type] ?? "Update on order #$order_id";
        
        return $this->createNotification($order['UserID'], $event_type, $message, 'All', $order_id);
    }

}

?>