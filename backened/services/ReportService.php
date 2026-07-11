<?php
/**
 * Report Service - FR19
 * FR19: Reports - Generate sales, inventory, customer, payment, delivery reports
 */

class ReportService {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }
    
    /**
     * FR19: Sales Report - Filter by date, status, amount
     */
    public function getSalesReport($startDate, $endDate, $filters = []) {
        $where = "WHERE o.OrderDate BETWEEN '$startDate' AND '$endDate'";
        
        if (!empty($filters['paymentStatus'])) {
            $where .= " AND o.PaymentStatus = '{$filters['paymentStatus']}'";
        }
        
        if (!empty($filters['orderStatus'])) {
            $where .= " AND o.Status = '{$filters['orderStatus']}'";
        }
        
        $stmt = $this->db->prepare("SELECT 
            COUNT(*) as total_orders,
            SUM(o.TotalAmount) as total_sales,
            AVG(o.TotalAmount) as avg_order_value,
            COUNT(CASE WHEN o.PaymentStatus = 'Paid' THEN 1 END) as paid_orders,
            COUNT(CASE WHEN o.Status = 'Delivered' THEN 1 END) as delivered_orders
            FROM `order` o $where");
        
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    /**
     * FR19: Inventory Report - stock levels, low stock items
     */
    public function getInventoryReport() {
        $stmt = $this->db->prepare("SELECT 
            COUNT(*) as total_products,
            SUM(i.StockQuantity) as total_stock,
            SUM(CASE WHEN i.StockQuantity <= i.ReorderLevel THEN 1 ELSE 0 END) as low_stock_items,
            SUM(CASE WHEN i.StockQuantity = 0 THEN 1 ELSE 0 END) as out_of_stock_items,
            p.Category,
            COUNT(*) as product_count,
            SUM(i.StockQuantity) as category_stock
            FROM inventory i
            JOIN product p ON i.ProductID = p.ProductID
            GROUP BY p.Category");
        
        $stmt->execute();
        $categoryReport = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Summary
        $summaryStmt = $this->db->prepare("SELECT 
            COUNT(*) as total_products,
            SUM(i.StockQuantity) as total_stock,
            SUM(CASE WHEN i.StockQuantity <= i.ReorderLevel THEN 1 ELSE 0 END) as low_stock_items
            FROM inventory i");
        
        $summaryStmt->execute();
        $summary = $summaryStmt->get_result()->fetch_assoc();
        
        return [
            'summary' => $summary,
            'byCategory' => $categoryReport
        ];
    }
    
    /**
     * FR19: Customer Report - customer activity, order frequency
     */
    public function getCustomerReport($startDate, $endDate) {
        $stmt = $this->db->prepare("SELECT 
            u.UserID,
            u.Name,
            u.Email,
            u.Role,
            COUNT(o.OrderID) as total_orders,
            SUM(o.TotalAmount) as total_spent,
            MAX(o.OrderDate) as last_order_date
            FROM user u
            LEFT JOIN `order` o ON u.UserID = o.UserID 
            WHERE o.OrderDate BETWEEN ? AND ?
            GROUP BY u.UserID
            ORDER BY total_spent DESC");
        
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * FR19: Payment Report - payment methods, status breakdown
     */
    public function getPaymentReport($startDate, $endDate) {
        $stmt = $this->db->prepare("SELECT 
            p.Provider,
            p.Status,
            COUNT(*) as count,
            SUM(p.Amount) as total_amount,
            AVG(p.Amount) as avg_amount
            FROM payment p
            WHERE p.Timestamp BETWEEN ? AND ?
            GROUP BY p.Provider, p.Status
            ORDER BY total_amount DESC");
        
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * FR19: Delivery Report - delivery status, performance metrics
     */
    public function getDeliveryReport($startDate, $endDate) {
        $stmt = $this->db->prepare("SELECT 
            d.Status,
            COUNT(*) as count,
            AVG(TIMESTAMPDIFF(HOUR, d.AssignedDate, d.DeliveryTimestamp)) as avg_delivery_hours,
            COUNT(CASE WHEN d.Status = 'Delivered' THEN 1 END) as successful_deliveries
            FROM delivery d
            WHERE d.AssignedDate BETWEEN ? AND ?
            GROUP BY d.Status");
        
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Low stock report
     */
    public function getLowStockReport() {
        $stmt = $this->db->prepare("SELECT 
            p.ProductID,
            p.SKU,
            p.Name,
            i.StockQuantity,
            i.ReorderLevel,
            i.ReorderQuantity
            FROM inventory i
            JOIN product p ON i.ProductID = p.ProductID
            WHERE i.StockQuantity <= i.ReorderLevel
            ORDER BY i.StockQuantity ASC");
        
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>