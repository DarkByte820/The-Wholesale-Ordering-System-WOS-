<?php
/**
 * Delivery Service - FR15, FR16
 * FR15: Delivery Assignment - assign orders to delivery personnel
 * FR16: Delivery Confirmation - update status and capture proof
 */

class DeliveryService {
    private $db;
    private $delivery;
    private $notification;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->delivery = new Delivery();
        $this->notification = new Notification();
    }
    
    /**
     * FR15: Assign delivery to personnel
     */
    public function assignDelivery($deliveryId, $personnelId, $adminId) {
        $result = $this->delivery->assignDelivery($deliveryId, $personnelId);
        
        if ($result) {
            // Get delivery details
            $deliveryStmt = $this->db->prepare("SELECT OrderID, CustomerContact FROM delivery WHERE DeliveryID = ?");
            $deliveryStmt->bind_param("i", $deliveryId);
            $deliveryStmt->execute();
            $delivData = $deliveryStmt->get_result()->fetch_assoc();
            
            // Get personnel details
            $persStmt = $this->db->prepare("SELECT Name, Phone FROM user WHERE UserID = ?");
            $persStmt->bind_param("i", $personnelId);
            $persStmt->execute();
            $personnel = $persStmt->get_result()->fetch_assoc();
            
            // Send notification to delivery personnel
            $this->notification->createNotification($personnelId, 'Delivery', 
                "New delivery assigned to you. Order #{$delivData['OrderID']}. Contact: {$delivData['CustomerContact']}", 'SMS');
            
            // Log audit
            $audit = new AuditLog();
            $audit->logAction($adminId, 'ASSIGN_DELIVERY', 'Delivery', $deliveryId, NULL, $personnelId);
            
            return [
                'success' => true,
                'message' => "Delivery assigned to {$personnel['Name']}",
                'personnelName' => $personnel['Name'],
                'personnelPhone' => $personnel['Phone']
            ];
        }
        
        return ['success' => false, 'message' => 'Failed to assign delivery'];
    }
    
    /**
     * FR15: Get deliveries for personnel
     * Delivery personnel can only view their assigned deliveries
     */
    public function getMyDeliveries($personnelId, $status = null) {
        return $this->delivery->getDeliveriesByPersonnel($personnelId, $status);
    }
    
    /**
     * FR16: Update delivery status
     */
    public function updateDeliveryStatus($deliveryId, $status, $personnelId) {
        // Validate status
        $validStatuses = ['Assigned', 'InTransit', 'OutForDelivery', 'Delivered', 'Failed', 'Cancelled'];
        if (!in_array($status, $validStatuses)) {
            return ['success' => false, 'message' => 'Invalid status'];
        }
        
        $result = $this->delivery->updateDeliveryStatus($deliveryId, $status);
        
        if ($result) {
            // Log audit
            $audit = new AuditLog();
            $audit->logAction($personnelId, 'UPDATE_DELIVERY_STATUS', 'Delivery', $deliveryId, NULL, $status);
            
            // Get delivery details for notification
            $deliveryStmt = $this->db->prepare("SELECT OrderID FROM delivery WHERE DeliveryID = ?");
            $deliveryStmt->bind_param("i", $deliveryId);
            $deliveryStmt->execute();
            $delivery = $deliveryStmt->get_result()->fetch_assoc();
            
            // Get order for customer notification
            $orderStmt = $this->db->prepare("SELECT UserID FROM `order` WHERE OrderID = ?");
            $orderStmt->bind_param("i", $delivery['OrderID']);
            $orderStmt->execute();
            $order = $orderStmt->get_result()->fetch_assoc();
            
            // Send notification to customer
            $statusMessage = "Your order #{$delivery['OrderID']} delivery status: $status";
            $this->notification->createNotification($order['UserID'], 'Delivery', $statusMessage, 'All', $delivery['OrderID']);
            
            return ['success' => true, 'message' => "Delivery status updated to $status"];
        }
        
        return ['success' => false, 'message' => 'Failed to update delivery status'];
    }
    
    /**
     * FR16: Record proof of delivery
     * Capture receiver name, timestamp, notes, location, photo/signature
     */
    public function recordProofOfDelivery($deliveryId, $proofData, $personnelId) {
        // Validate proof data
        if (empty($proofData['receiverName'])) {
            return ['success' => false, 'message' => 'Receiver name is required'];
        }
        
        // Optional: Upload signature/photo
        $photoPath = null;
        if (!empty($proofData['signature'])) {
            // TODO: Handle file upload for signature/photo
            $photoPath = $this->uploadProofPhoto($proofData['signature']);
        }
        
        // Record proof
        $result = $this->delivery->recordProofOfDelivery(
            $deliveryId,
            $proofData['receiverName'],
            $proofData['notes'] ?? '',
            $proofData['latitude'] ?? null,
            $proofData['longitude'] ?? null
        );
        
        if ($result) {
            // Get delivery details
            $deliveryStmt = $this->db->prepare("SELECT OrderID FROM delivery WHERE DeliveryID = ?");
            $deliveryStmt->bind_param("i", $deliveryId);
            $deliveryStmt->execute();
            $delivery = $deliveryStmt->get_result()->fetch_assoc();
            
            // Update order status to Delivered
            $orderStmt = $this->db->prepare("UPDATE `order` SET Status = 'Delivered' WHERE OrderID = ?");
            $orderStmt->bind_param("i", $delivery['OrderID']);
            $orderStmt->execute();
            
            // Get customer for notification
            $custStmt = $this->db->prepare("SELECT UserID FROM `order` WHERE OrderID = ?");
            $custStmt->bind_param("i", $delivery['OrderID']);
            $custStmt->execute();
            $customer = $custStmt->get_result()->fetch_assoc();
            
            // Send delivery confirmation notification
            $this->notification->createNotification($customer['UserID'], 'Delivery',
                "Your order #{$delivery['OrderID']} has been delivered. Thank you!", 'All', $delivery['Order_ID']);
            
            // Log audit
            $audit = new AuditLog();
            $audit->logAction($personnelId, 'RECORD_PROOF_DELIVERY', 'Delivery', $deliveryId);
            
            return [
                'success' => true,
                'message' => 'Proof of delivery recorded',
                'orderId' => $delivery['OrderID']
            ];
        }
        
        return ['success' => false, 'message' => 'Failed to record proof of delivery'];
    }
    
    /**
     * Upload signature/photo proof
     */
    private function uploadProofPhoto($fileData) {
        // TODO: Implement file upload handling
        return '/uploads/proofs/proof_' . time() . '.jpg';
    }
}
?>