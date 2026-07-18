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
        $where = "WHERE o.created_at BETWEEN '$startDate' AND '$endDate'";
        
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
            FROM `orders` o $where");
        
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    /**
     * FR19: Inventory Report - stock levels, low stock items
     */
    public function getInventoryReport() {
        $stmt = $this->db->prepare("SELECT 
            COUNT(*) as total_products,
            SUM(i.stock_quantity) as total_stock,
            SUM(CASE WHEN i.stock_quantity <= i.Reorder_level THEN 1 ELSE 0 END) as low_stock_items,
            SUM(CASE WHEN i.stock_quantity = 0 THEN 1 ELSE 0 END) as out_of_stock_items,
            p.Category_id,
            COUNT(*) as product_count,
            SUM(i.stock_quantity) as category_stock
            FROM inventory i
            JOIN products p ON i.Product_ID = p.Product_ID
            GROUP BY p.Category_id");
        
        $stmt->execute();
        $categoryReport = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Summary
        $summaryStmt = $this->db->prepare("SELECT 
            COUNT(*) as total_products,
            SUM(i.stock_quantity) as total_stock,
            SUM(CASE WHEN i.stock_quantity <= i.Reorder_level THEN 1 ELSE 0 END) as low_stock_items
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
    public function getCustomerReport($start_Date, $end_Date) {
        $stmt = $this->db->prepare("SELECT 
            u.User_ID,
            u.Name,
            u.Email,
            u.Role,
            COUNT(o.OrderID) as total_orders,
            SUM(o.TotalAmount) as total_spent,
            MAX(o.created_at) as last_order_date
            FROM users u
            LEFT JOIN `orders` o ON u.User_ID = o.UserID 
            WHERE o.created_at BETWEEN ? AND ?
            GROUP BY u.User_ID
            ORDER BY total_spent DESC");
        
        $stmt->bind_param("ss", $start_Date, $end_Date);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * FR19: Payment Report - payment methods, status breakdown
     */
    public function getPaymentReport($start_Date, $end_Date) {
        $stmt = $this->db->prepare("SELECT 
            p.Provider,
            p.Status,
            COUNT(*) as count,
            SUM(p.Amount) as total_amount,
            AVG(p.Amount) as avg_amount
            FROM payments p
            WHERE p.created_at BETWEEN ? AND ?
            GROUP BY p.Provider, p.Status
            ORDER BY total_amount DESC");
        
        $stmt->bind_param("ss", $start_Date, $end_Date);
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
            AVG(TIMESTAMPDIFF(HOUR, d.created_at, d.delivered_at)) as avg_delivery_hours,
            COUNT(CASE WHEN d.Status = 'Delivered' THEN 1 END) as successful_deliveries
            FROM deliveries d
            WHERE d.created_at BETWEEN ? AND ?
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
            p.Product_ID,
            p.SKU,
            p.Name,
            i.Stock_Quantity,
            i.ReorderLevel,
            i.Reserved_Quantity
            FROM inventory i
            JOIN products p ON i.Product_ID = p.Product_ID
            WHERE i.stock_quantity <= i.ReorderLevel
            ORDER BY i.stock_quantity ASC");
        
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>