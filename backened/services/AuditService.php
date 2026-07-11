<?php
/**
 * Audit Service - FR22
 * FR22: Audit Logging - record critical actions
 */

class AuditService {
    private $auditLog;
    
    public function __construct() {
        $this->auditLog = new AuditLog();
    }
    
    /**
     * FR22: Log user login
     */
    public function logLogin($userId, $success = true) {
        $action = $success ? 'LOGIN' : 'FAILED_LOGIN';
        return $this->auditLog->logAction($userId, $action, 'User', $userId, NULL, 'Login attempt');
    }
    
    /**
     * FR22: Log product changes
     */
    public function logProductChange($userId, $productId, $oldData, $newData) {
        return $this->auditLog->logAction($userId, 'UPDATE_PRODUCT', 'Product', $productId, json_encode($oldData), json_encode($newData));
    }
    
    /**
     * FR22: Log price changes
     */
    public function logPriceChange($userId, $productId, $oldPrice, $newPrice) {
        return $this->auditLog->logAction($userId, 'CHANGE_PRICE', 'Product', $productId, $oldPrice, $newPrice);
    }
    
    /**
     * FR22: Log order status updates
     */
    public function logOrderStatusChange($userId, $orderId, $oldStatus, $newStatus) {
        return $this->auditLog->logAction($userId, 'UPDATE_ORDER_STATUS', 'Order', $orderId, $oldStatus, $newStatus);
    }
    
    /**
     * FR22: Log payment verification
     */
    public function logPaymentVerification($adminId, $paymentId, $transactionRef) {
        return $this->auditLog->logAction($adminId, 'VERIFY_PAYMENT', 'Payment', $paymentId, 'Initiated', 'Verified');
    }
    
    /**
     * FR22: Log user role changes
     */
    public function logRoleChange($adminId, $userId, $oldRole, $newRole) {
        return $this->auditLog->logAction($adminId, 'CHANGE_USER_ROLE', 'User', $userId, $oldRole, $newRole);
    }
    
    /**
     * Get audit trail for entity
     */
    public function getAuditTrail($entityType, $entityId) {
        return $this->auditLog->getAuditLogsByEntity($entityType, $entityId);
    }
}
?>