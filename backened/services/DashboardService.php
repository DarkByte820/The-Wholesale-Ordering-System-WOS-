<?php
/**
 * Dashboard Service - FR20
 * FR20: Dashboard - key metrics and statistics
 */

class DashboardService {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }
    
    /**
     * FR20: Get dashboard metrics
     */
    public function getDashboardMetrics() {
        // Total Orders
        $ordersStmt = $this->db->prepare("SELECT COUNT(*) as count FROM `order`");
        $ordersStmt->execute();
        $totalOrders = $ordersStmt->get_result()->fetch_assoc()['count'];
        
        // Pending Orders
        $pendingStmt = $this->db->prepare("SELECT COUNT(*) as count FROM `order` WHERE Status = 'Pending'");
        $pendingStmt->execute();
        $pendingOrders = $pendingStmt->get_result()->fetch_assoc()['count'];
        
        // Total Revenue
        $revenueStmt = $this->db->prepare("SELECT COALESCE(SUM(TotalAmount), 0) as total FROM `order` WHERE PaymentStatus = 'Paid'");
        $revenueStmt->execute();
        $totalRevenue = $revenueStmt->get_result()->fetch_assoc()['total'];
        
        // Low Stock Items
        $lowStockStmt = $this->db->prepare("SELECT COUNT(*) as count FROM inventory WHERE StockQuantity <= ReorderLevel");
        $lowStockStmt->execute();
        $lowStockItems = $lowStockStmt->get_result()->fetch_assoc()['count'];
        
        // Top Products
        $topStmt = $this->db->prepare("SELECT p.Name, COUNT(oi.OrderItemID) as sales, SUM(oi.TotalPrice) as revenue
                                       FROM order_item oi
                                       JOIN product p ON oi.ProductID = p.ProductID
                                       GROUP BY oi.ProductID
                                       ORDER BY sales DESC
                                       LIMIT 5");
        $topStmt->execute();
        $topProducts = $topStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        return [
            'totalOrders' => (int)$totalOrders,
            'pendingOrders' => (int)$pendingOrders,
            'totalRevenue' => (float)$totalRevenue,
            'lowStockItems' => (int)$lowStockItems,
            'topProducts' => $topProducts
        ];
    }
    
    /**
     * Sales trend over time
     */
    public function getSalesTrend($days = 30) {
        $stmt = $this->db->prepare("SELECT 
            DATE(OrderDate) as date,
            COUNT(*) as orders,
            SUM(TotalAmount) as revenue
            FROM `order`
            WHERE OrderDate >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY DATE(OrderDate)
            ORDER BY date ASC");
        
        $stmt->bind_param("i", $days);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Order status breakdown
     */
    public function getOrderStatusBreakdown() {
        $stmt = $this->db->prepare("SELECT 
            Status,
            COUNT(*) as count,
            ROUND(COUNT(*) * 100 / (SELECT COUNT(*) FROM `order`), 2) as percentage
            FROM `order`
            GROUP BY Status");
        
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>