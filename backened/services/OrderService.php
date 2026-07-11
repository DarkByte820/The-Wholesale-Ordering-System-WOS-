<?php
/**
 * Order Service - FR13, FR14
 * FR13: Order Management - view and update order status
 * FR14: Inventory Deduction - automatically reduce stock after confirmation
 */

class OrderService {
    private $db;
    private $order;
    private $inventory;
    private $notification;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->order = new Order();
        $this->inventory = new Inventory();
        $this->notification = new Notifications();
    }
    
    /**
     * FR13: Get all orders for admin management
     */
    public function getAllOrders($page = 1, $limit = 10, $filters = []) {
        $offset = ($page - 1) * $limit;
        $where = "1=1";
        
        // Apply filters
        if (!empty($filters['status'])) {
            $status = $filters['status'];
            $where .= " AND Status = '$status'";
        }
        
        if (!empty($filters['paymentStatus'])) {
            $paymentStatus = $filters['paymentStatus'];
            $where .= " AND PaymentStatus = '$paymentStatus'";
        }
        
        if (!empty($filters['startDate'])) {
            $startDate = $filters['startDate'];
            $where .= " AND DATE(OrderDate) >= '$startDate'";
        }
        
        if (!empty($filters['endDate'])) {
            $endDate = $filters['endDate'];
            $where .= " AND DATE(OrderDate) <= '$endDate'";
        }
        
        $stmt = $this->db->prepare("SELECT o.*, u.Name, u.Email FROM `order` o 
                                   JOIN user u ON o.UserID = u.UserID 
                                   WHERE $where 
                                   ORDER BY o.OrderDate DESC 
                                   LIMIT ? OFFSET ?");
        
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * FR13: Update order status
     * Status flow: Pending → Confirmed → Packed → Dispatched → Delivered
     */
    public function updateOrderStatus($orderId, $newStatus, $adminId) {
        $order = $this->order->getOrderById($orderId);
        if (!$order) {
            return ['success' => false, 'message' => 'Order not found'];
        }
        
        $oldStatus = $order['Status'];
        
        // Validate status transition
        if (!$this->isValidStatusTransition($oldStatus, $newStatus)) {
            return ['success' => false, 'message' => 'Invalid status transition'];
        }
        
        // Update status
   $result = $this->order->updateOrderStatus($orderId, $newStatus);
        
        if ($result) {
            // FR14: When status is Confirmed, deduct inventory
            if ($newStatus === 'Confirmed' && $oldStatus === 'Pending') {
                $this->deductInventory($orderId);
            }
            
            // Send notification
            $message = "Your order #$orderId status has been updated to $newStatus";
            $this->notification->createNotification($order['UserID'], 'OrderConfirmation', $message, 'All', $orderId);
            
            // Log audit
            $audit = new AuditLog();
            $audit->logAction($adminId, 'UPDATE_ORDER_STATUS', 'Orders', $orderId, $oldStatus, $newStatus);
            
            return [
                'success' => true,
                'message' => "Order status updated to $newStatus",
                'previousStatus' => $oldStatus,
                'newStatus' => $newStatus
            ];
        }
        
        return ['success' => false, 'message' => 'Failed to update order status'];
    }
    
    /**
     * Validate status transitions
     */
    private function isValidStatusTransition($from, $to) {
        $validTransitions = [
            'Pending' => ['Confirmed', 'Cancelled'],
            'Confirmed' => ['Packed', 'Cancelled'],
            'Packed' => ['Dispatched'],
            'Dispatched' => ['Delivered', 'Failed'],
            'Delivered' => [],
            'Cancelled' => [],
            'Failed' => ['Cancelled']
        ];
        
        return isset($validTransitions[$from]) && in_array($to, $validTransitions[$from]);
    }
    
    /**
     * FR14: Inventory Deduction - Automatically reduce stock after confirmation
     * Prevents overselling - checks stock before deduction
     */
    private function deductInventory($orderId) {
        $items = $this->order->getOrderItems($orderId);
        
        foreach ($items as $item) {
            if ($item['ProductID']) {
                // Get current inventory
                $invData = $this->inventory->getInventoryByProductId($item['ProductID']);
                
                if (!$invData) {
                    Logger::error("Inventory not found for product", ['productId' => $item['ProductID']]);
                    continue;
                }
                
                // Check if enough stock
                if ($invData['StockQuantity'] < $item['Quantity']) {
                    Logger::warning("Insufficient stock for product", [
                        'productId' => $item['ProductID'],
                        'required' => $item['Quantity'],
                        'available' => $invData['StockQuantity']
                    ]);
                    continue;
                }
                
                // Deduct stock
                $newQuantity = $invData['StockQuantity'] - $item['Quantity'];
                $this->inventory->updateStock($invData['InventoryID'], $newQuantity);
                
                Logger::info("Stock deducted", [
                    'productId' => $item['ProductID'],
                    'quantity' => $item['Quantity'],
                    'previousStock' => $invData['StockQuantity'],
                    'newStock' => $newQuantity
                ]);
            }
        }
        
        return true;
    }
    
    /**
     * Cancel order and restore inventory
     */
    public function cancelOrder($orderId, $adminId) {
        $order = $this->order->getOrderById($orderId);
        if (!$order) {
            return ['success' => false, 'message' => 'Order not found'];
        }
        
        // Update status
        $this->order->updateOrderStatus($orderId, 'Cancelled');
        
        // Restore inventory if it was confirmed
        if ($order['Status'] === 'Confirmed' || $order['Status'] === 'Packed') {
            $this->restoreInventory($orderId);
        }
        
        // Send notification
        $message = "Your order #$orderId has been cancelled";
        $this->notification->createNotification($order['UserID'], 'Cancellation', $message, 'All', $orderId);
        
        // Log audit
        $audit = new AuditLog();
        $audit->logAction($adminId, 'CANCEL_ORDER', 'Order', $orderId, $order['Status'], 'Cancelled');
        
        return ['success' => true, 'message' => 'Order cancelled and inventory restored'];
    }
    
    /**
     * Restore inventory when order is cancelled
     */
    private function restoreInventory($orderId) {
       $items = $this->order->getOrderItems($orderId);
        Logger::info("Restoring inventory for order", ['orderId' => $orderId]);
        foreach ($items as $item) {
            if ($item['ProductID']) {
                $invData = $this->inventory->getInventoryByProductId($item['ProductID']);
                if ($invData) {
                    $newQuantity = $invData['StockQuantity'] + $item['Quantity'];
                    $this->inventory->updateStock($invData['InventoryID'], $newQuantity);
                }
            }
        }
    }
}
?>