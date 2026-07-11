<?php
/**
 * Audit Log Model
 * Tracks Critical Actions & Changes
 */

class AuditLog {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }
    
    public function logAction($user_id, $action, $entity_type, $entity_id, $old_value = null, $new_value = null, $ip_address = null, $status = 'Success') {
        if (!$ip_address) {
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        }
        
        $device_info = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        $stmt = $this->db->prepare("INSERT INTO audit_log (User_ID, Action, EntityType, EntityID, OldValue, NewValue, IPAddress, DeviceInfo, Status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("issiisss", $user_id, $action, $entity_type, $entity_id, $old_value, $new_value, $ip_address, $device_info, $status);
        
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        
        error_log("Execute failed: " . $stmt->error);
        return false;
    }
    
    public function getAuditLogs($page = 1, $limit = ITEMS_PER_PAGE) {
        $offset = ($page - 1) * $limit;
        
        $stmt = $this->db->prepare("SELECT al.*, u.Name as UserName FROM audit_log al JOIN user u ON al.UserID = u.UserID ORDER BY al.Timestamp DESC LIMIT ? OFFSET ?");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return [];
        }
        
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getAuditLogsByUser($user_id, $page = 1, $limit = ITEMS_PER_PAGE) {
        $offset = ($page - 1) * $limit;
        
        $stmt = $this->db->prepare("SELECT * FROM audit_log WHERE UserID = ? ORDER BY Timestamp DESC LIMIT ? OFFSET ?");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return [];
        }
        
        $stmt->bind_param("iii", $user_id, $limit, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getAuditLogsByEntity($entity_type, $entity_id) {
        $stmt = $this->db->prepare("SELECT al.*, u.Name as UserName FROM audit_log al JOIN user u ON al.UserID = u.UserID WHERE al.EntityType = ? AND al.EntityID = ? ORDER BY al.Timestamp DESC");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return [];
        }
        
        $stmt->bind_param("si", $entity_type, $entity_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getAuditLogsByDateRange($start_date, $end_date) {
        $stmt = $this->db->prepare("SELECT al.*, u.Name as UserName FROM audit_log al JOIN user u ON al.UserID = u.UserID WHERE DATE(al.Timestamp) BETWEEN ? AND ? ORDER BY al.Timestamp DESC");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return [];
        }
        
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getAuditLogsByAction($action) {
        $stmt = $this->db->prepare("SELECT al.*, u.Name as UserName FROM audit_log al JOIN user u ON al.UserID = u.UserID WHERE al.Action = ? ORDER BY al.Timestamp DESC");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return [];
        }
        
        $stmt->bind_param("s", $action);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    public static function getAuditTrail() {
        $user = authenticateUser();
        
        if ($user['role'] !== 'SystemAdmin') {
            Response::error("Insufficient permissions", FORBIDDEN);
        }
        
        $db = new Database();
        $conn = $db->connect();
        
        $stmt = $conn->prepare("SELECT al.*, u.Name as UserName FROM audit_log al JOIN user u ON al.UserID = u.UserID ORDER BY al.Timestamp DESC");
        
        if (!$stmt) {
            Response::error("Database error", SERVER_ERROR);
        }
        
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        Response::success($result, "Audit trail retrieved successfully");
    }
}

?>