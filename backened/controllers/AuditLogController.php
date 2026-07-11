<?php
/**
 * Audit Log Controller
 */

class AuditLogController {
    
    public static function getAuditLogs() {
        $user = authenticateUser();
        
        // Only admins can view audit logs
        if ($user['role'] !== 'SystemAdmin' && $user['role'] !== 'WarehouseAdmin') {
            Response::error("Insufficient permissions", FORBIDDEN);
        }
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : ITEMS_PER_PAGE;
        
        $audit = new AuditLog();
        $logs = $audit->getAuditLogs($page, $limit);
        
        Response::success($logs, "Audit logs retrieved successfully");
    }
    
    public static function getAuditLogsByEntity() {
        $user = authenticateUser();
        
        if ($user['role'] !== 'SystemAdmin' && $user['role'] !== 'WarehouseAdmin') {
            Response::error("Insufficient permissions", FORBIDDEN);
        }
        
        $entity_type = isset($_GET['entityType']) ? $_GET['entityType'] : null;
        $entity_id = isset($_GET['entityId']) ? (int)$_GET['entityId'] : null;
        
        if (!$entity_type || !$entity_id) {
            Response::error("Entity type and ID required", BAD_REQUEST);
        }
        
        $audit = new AuditLog();
        $logs = $audit->getAuditLogsByEntity($entity_type, $entity_id);
        
        Response::success($logs, "Entity audit logs retrieved");
    }
    
    public static function getAuditLogsByDateRange() {
        $user = authenticateUser();
        
        if ($user['role'] !== 'SystemAdmin' && $user['role'] !== 'WarehouseAdmin') {
            Response::error("Insufficient permissions", FORBIDDEN);
        }
        
        $start_date = isset($_GET['startDate']) ? $_GET['startDate'] : null;
        $end_date = isset($_GET['endDate']) ? $_GET['endDate'] : null;
        
        if (!$start_date || !$end_date) {
            Response::error("Start and end dates required", BAD_REQUEST);
        }
        
        $audit = new AuditLog();
        $logs = $audit->getAuditLogsByDateRange($start_date, $end_date);
        
        Response::success($logs, "Audit logs retrieved for date range");
    }
    public static function getAuditTrail() {
        $user = authenticateUser();
        
        if ($user['role'] !== 'SystemAdmin' && $user['role'] !== 'WarehouseAdmin') {
            Response::error("Insufficient permissions", FORBIDDEN);
        }
        
        $entity_type = isset($_GET['entityType']) ? $_GET['entityType'] : null;
        $entity_id = isset($_GET['entityId']) ? (int)$_GET['entityId'] : null;
        
        if (!$entity_type || !$entity_id) {
            Response::error("Entity type and ID required", BAD_REQUEST);
        }
        
        $audit = new AuditLog();
        $trail = $audit->getAuditTrail($entity_type, $entity_id);
        
        Response::success($trail, "Audit trail retrieved");
    }
}

?>