<?php
/**
 * Audit Log Controller
 */

class AuditLogController {
    
    public static function getAuditLogs() {
        $user = authenticateUser();
        
        // Only admins can view audit logs
        if ($user['role'] !== 'system_admin' && $user['role'] !== 'warehouse_admin') {
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
        
        if ($user['role'] !== 'system_admin' && $user['role'] !== 'warehouse_admin') {
            Response::error("Insufficient permissions", FORBIDDEN);
        }
        
        $input = json_decode(file_get_contents("php://input"), true);
        $entity_type = isset($input['entityType']) ? $input['entityType'] : null;
        $entity_id = isset($input['entityId']) ? (int)$input['entityId'] : null;
        
        if (!$entity_type || !$entity_id) {
            Response::error("Entity type and ID required", BAD_REQUEST);
        }
        
        $audit = new AuditLog();
        $logs = $audit->getAuditLogsByEntity($entity_type, $entity_id);
        
        Response::success($logs, "Entity audit logs retrieved");
    }
    
    public static function getAuditLogsByDateRange() {
        $user = authenticateUser();
        
        if ($user['role'] !== 'system_admin' && $user['role'] !== 'warehouse_admin') {
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
        


        if ($user['role'] !== 'system_admin' && $user['role'] !== 'warehouse_admin') {
            Response::error("Insufficient permissions", FORBIDDEN);
        }

        $input = json_decode(file_get_contents("php://input"), true);
        
        $entity_type = isset($input['entityType']) ? $input['entityType'] : null;
        $entity_id = isset($input['entityId']) ? (int)$input['entityId'] : null;
        
        if (!$entity_type || !$entity_id) {
            Response::error("Entity type and ID required", BAD_REQUEST);
        }
        
        $audit = new AuditLog();
        $trail = $audit->getAuditTrail($entity_type, $entity_id);
    
        Response::success($trail, "Audit trail retrieved");
    }
}

?>