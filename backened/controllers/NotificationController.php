<?php


class NotificationController {
    
    public static function getNotifications() {
        $user = authenticateUser();
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : ITEMS_PER_PAGE;
        
        $notification = new Notification();
        $notifications = $notification->getNotifications($user['userId'], $page, $limit);
        $unread_count = $notification->getUnreadCount($user['userId']);
        
        Response::success([
            'notifications' => $notifications,
            'unreadCount' => $unread_count
        ], "Notifications retrieved successfully");
    }
    
    public static function getUnreadNotifications() {
        $user = authenticateUser();
        
        $notification = new Notification();
        $unread = $notification->getUnreadNotifications($user['userId']);
        
        Response::success($unread, "Unread notifications retrieved");
    }
    
    public static function markAsRead() {
        $user = authenticateUser();
        $input = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($input['notificationId'])) {
            Response::error("Notification ID required", BAD_REQUEST);
        }
        
        $notification = new Notification();
        $result = $notification->markAsRead($input['notificationId']);
        
        if ($result) {
            Response::success(null, "Notification marked as read");
        } else {
            Response::error("Failed to mark notification", SERVER_ERROR);
        }
    }
    
    public static function markAllAsRead() {
        $user = authenticateUser();
        
        $notification = new Notification();
        $result = $notification->markAllAsRead($user['userId']);
        
        if ($result) {
            Response::success(null, "All notifications marked as read");
        } else {
            Response::error("Failed to mark notifications", SERVER_ERROR);
        }
    }
    public static function sendNotification() {
        $user = authenticateUser();
        $input = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($input['userId']) || !isset($input['message'])) {
            Response::error("User ID and message required", BAD_REQUEST);
        }
      
        try{
            $notification = new Notification();
            $result = $notification->createNotification($input['userId'], $input['eventType'], $input['message'], $input['channel'] ?? 'All', $input['orderId'] ?? null);
        }catch(Exception $e){
            Response::error("Invalid input", $e);
        }
        
        if ($result) {
            Response::success(null, "Notification sent successfully");
        } else {
            Response::error("Failed to send notification", SERVER_ERROR);
        }
    }
}

?>