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
    public function getDashboardMetrics()
{
    // Total Orders
    $ordersStmt = $this->db->prepare("SELECT COUNT(*) AS count FROM orders");
    if (!$ordersStmt) {
        die("Orders Query Error: " . $this->db->error);
    }
    $ordersStmt->execute();
    $totalOrders = $ordersStmt->get_result()->fetch_assoc()['count'];

    // Pending Orders
    $pendingStmt = $this->db->prepare("SELECT COUNT(*) AS count FROM orders WHERE Status='Pending'");
    if (!$pendingStmt) {
        die("Pending Orders Query Error: " . $this->db->error);
    }
    $pendingStmt->execute();
    $pendingOrders = $pendingStmt->get_result()->fetch_assoc()['count'];

    // Total Revenue
    $revenueStmt = $this->db->prepare("SELECT COALESCE(SUM(TotalAmount),0) AS total FROM orders WHERE PaymentStatus='Paid'");
    if (!$revenueStmt) {
        die("Revenue Query Error: " . $this->db->error);
    }
    $revenueStmt->execute();
    $totalRevenue = $revenueStmt->get_result()->fetch_assoc()['total'];

    // Low Stock Items
    $lowStockStmt = $this->db->prepare("SELECT COUNT(*) AS count FROM inventory WHERE Stock_Quantity <= ReorderLevel");
    if (!$lowStockStmt) {
        die("Low Stock Query Error: " . $this->db->error);
    }
    $lowStockStmt->execute();
    $lowStockItems = $lowStockStmt->get_result()->fetch_assoc()['count'];

    // Top Products
    $topStmt = $this->db->prepare("
        SELECT
            p.Name,
            COUNT(oi.Order_Item_ID) AS sales,
            SUM(oi.TotalPrice) AS revenue
        FROM order_items oi
        JOIN products p
            ON oi.ProductID = p.Product_ID
        GROUP BY oi.ProductID, p.Name
        ORDER BY sales DESC
        LIMIT 5
    ");

    if (!$topStmt) {
        die("Top Products Query Error: " . $this->db->error);
    }

    $topStmt->execute();
    $topProducts = $topStmt->get_result()->fetch_all(MYSQLI_ASSOC);

    return [
        'totalOrders'   => (int)$totalOrders,
        'pendingOrders' => (int)$pendingOrders,
        'totalRevenue'  => (float)$totalRevenue,
        'lowStockItems' => (int)$lowStockItems,
        'topProducts'   => $topProducts
    ];
}
    /**
     * Sales trend over time
     */
    public function getSalesTrend($days = 30) {
        $stmt = $this->db->prepare("SELECT 
            DATE(created_at) as date,
            COUNT(*) as orders,
            SUM(TotalAmount) as revenue
            FROM `orders`
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY DATE(created_at)
            ORDER BY date ASC");

        $stmt = $this->db->prepare("SELECT 
            DATE(created_at) as date,
            COUNT(*) as orders,
            SUM(TotalAmount) as revenue
            FROM `orders`
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY DATE(created_at)
            ORDER BY date ASC");

if (!$stmt) {
    die("Prepare failed: " . $this->db->error);
}

$stmt->bind_param("i", $days);
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
            ROUND(COUNT(*) * 100 / (SELECT COUNT(*) FROM `orders`), 2) as percentage
            FROM `orders`
            GROUP BY Status");
        
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>