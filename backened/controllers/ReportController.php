<?php
/**
 * Report Controller
 * Generates Sales, Inventory, Order & Delivery Reports
 */

class ReportController {
    
    public static function getSalesReport() {
        $user = authenticateUser();
        
        if ($user['role'] !== 'WarehouseAdmin' && $user['role'] !== 'SystemAdmin') {
            Response::error("Insufficient permissions", FORBIDDEN);
        }
        
        $start_date = isset($_GET['startDate']) ? $_GET['startDate'] : date('Y-m-01');
        $end_date = isset($_GET['endDate']) ? $_GET['endDate'] : date('Y-m-t');
        
        $db = new Database();
        $conn = $db->connect();
        
        $stmt = $conn->prepare("SELECT 
            COUNT(*) as total_orders,
            SUM(TotalAmount) as total_sales,
            AVG(TotalAmount) as avg_order_value,
            COUNT(CASE WHEN PaymentStatus = 'Paid' THEN 1 END) as paid_orders
            FROM `order` 
            WHERE OrderDate BETWEEN ? AND ?");
        
        if (!$stmt) {
            Response::error("Database error", SERVER_ERROR);
        }
        
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        Response::success($result, "Sales report generated successfully");
    }
    
    public static function getInventoryReport() {
        $user = authenticateUser();
        
        if ($user['role'] !== 'WarehouseAdmin' && $user['role'] !== 'SystemAdmin') {
            Response::error("Insufficient permissions", FORBIDDEN);
        }
        
        $db = new Database();
        $conn = $db->connect();
        
        $stmt = $conn->prepare("SELECT 
            COUNT(*) as total_products,
            SUM(StockQuantity) as total_stock,
            SUM(CASE WHEN StockQuantity <= ReorderLevel THEN 1 ELSE 0 END) as low_stock_items,
            SUM(CASE WHEN StockQuantity = 0 THEN 1 ELSE 0 END) as out_of_stock_items
            FROM inventory");
        
        if (!$stmt) {
            Response::error("Database error", SERVER_ERROR);
        }
        
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        Response::success($result, "Inventory report generated successfully");
    }
    
    public static function getOrdersReport() {
        $user = authenticateUser();
        
        if ($user['role'] !== 'WarehouseAdmin' && $user['role'] !== 'SystemAdmin') {
            Response::error("Insufficient permissions", FORBIDDEN);
        }
        
        $start_date = isset($_GET['startDate']) ? $_GET['startDate'] : date('Y-m-01');
        $end_date = isset($_GET['endDate']) ? $_GET['endDate'] : date('Y-m-t');
        
        $db = new Database();
        $conn = $db->connect();
        
        $stmt = $conn->prepare("SELECT 
            Status,
            COUNT(*) as count
            FROM `order`
            WHERE OrderDate BETWEEN ? AND ?
            GROUP BY Status");
        
        if (!$stmt) {
            Response::error("Database error", SERVER_ERROR);
        }
        
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        Response::success($result, "Orders report generated successfully");
    }
    
    public static function getDeliveryReport() {
        $user = authenticateUser();
        
        if ($user['role'] !== 'WarehouseAdmin' && $user['role'] !== 'SystemAdmin') {
            Response::error("Insufficient permissions", FORBIDDEN);
        }
        
        $db = new Database();
        $conn = $db->connect();
        
        $stmt = $conn->prepare("SELECT 
            Status,
            COUNT(*) as count
            FROM delivery
            GROUP BY Status");
        
        if (!$stmt) {
            Response::error("Database error", SERVER_ERROR);
        }
        
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        Response::success($result, "Delivery report generated successfully");
    }
    
    public static function getDashboardMetrics() {
        $user = authenticateUser();
        
        if ($user['role'] !== 'WarehouseAdmin' && $user['role'] !== 'SystemAdmin') {
            Response::error("Insufficient permissions", FORBIDDEN);
        }
        
        $db = new Database();
        $conn = $db->connect();
        
        // Total Orders
        $stmt1 = $conn->prepare("SELECT COUNT(*) as count FROM `order`");
        $stmt1->execute();
        $total_orders = $stmt1->get_result()->fetch_assoc()['count'];
        
        // Pending Orders
        $stmt2 = $conn->prepare("SELECT COUNT(*) as count FROM `order` WHERE Status = 'Pending'");
        $stmt2->execute();
        $pending_orders = $stmt2->get_result()->fetch_assoc()['count'];
        
        // Total Revenue
        $stmt3 = $conn->prepare("SELECT COALESCE(SUM(TotalAmount), 0) as total FROM `order` WHERE PaymentStatus = 'Paid'");
        $stmt3->execute();
        $total_revenue = $stmt3->get_result()->fetch_assoc()['total'];
        
        // Low Stock Items
        $stmt4 = $conn->prepare("SELECT COUNT(*) as count FROM inventory WHERE StockQuantity <= ReorderLevel");
        $stmt4->execute();
        $low_stock = $stmt4->get_result()->fetch_assoc()['count'];
        
        $data = [
            'totalOrders' => $total_orders,
            'pendingOrders' => $pending_orders,
            'totalRevenue' => (float)$total_revenue,
            'lowStockItems' => $low_stock
        ];
        
        Response::success($data, "Dashboard metrics retrieved");
    }
    public static function salesReport() {
        $user = authenticateUser();
        
        if ($user['role'] !== 'WarehouseAdmin' && $user['role'] !== 'SystemAdmin') {
            Response::error("Insufficient permissions", FORBIDDEN);
        }
        
        $start_date = isset($_GET['startDate']) ? $_GET['startDate'] : date('Y-m-01');
        $end_date = isset($_GET['endDate']) ? $_GET['endDate'] : date('Y-m-t');
        
        $reportService = new ReportService();
        $reportData = $reportService->getSalesReport($start_date, $end_date);
        
        Response::success($reportData, "Sales report generated successfully");
    }
    public static function inventoryReport() {
        $user = authenticateUser();
        
        if ($user['role'] !== 'WarehouseAdmin' && $user['role'] !== 'SystemAdmin') {
            Response::error("Insufficient permissions", FORBIDDEN);
        }
        
        $reportService = new ReportService();
        $reportData = $reportService->getInventoryReport();
        
        Response::success($reportData, "Inventory report generated successfully");
    }
    public static function customerReport() {
        $user = authenticateUser();
        $start_date = isset($_GET['startDate']) ? $_GET['startDate'] : date('Y-m-01');
        $end_date = isset($_GET['endDate']) ? $_GET['endDate'] : date('Y-m-t');

        if ($user['role'] !== 'WarehouseAdmin' && $user['role'] !== 'SystemAdmin') {
            Response::error("Insufficient permissions", FORBIDDEN);
        }
        
        $reportService = new ReportService();
        $reportData = $reportService->getCustomerReport($start_date, $end_date);
        
        Response::success($reportData, "Customer report generated successfully");
    }
    public static function paymentReport() {
        $user = authenticateUser();
        $start_date = isset($_GET['startDate']) ? $_GET['startDate'] : date('Y-m-01');
        $end_date = isset($_GET['endDate']) ? $_GET['endDate'] : date('Y-m-t');

        if ($user['role'] !== 'WarehouseAdmin' && $user['role'] !== 'SystemAdmin') {
            Response::error("Insufficient permissions", FORBIDDEN);
        }
       
        $reportService = new ReportService();
        $reportData = $reportService->getPaymentReport($start_date, $end_date);
        
        Response::success($reportData, "Payment report generated successfully");
    }
    public static function deliveryReport() {
        $user = authenticateUser();
        $start_date = isset($_GET['startDate']) ? $_GET['startDate'] : date('Y-m-01');
        $end_date = isset($_GET['endDate']) ? $_GET['endDate'] : date('Y-m-t');
        if ($user['role'] !== 'WarehouseAdmin' && $user['role'] !== 'SystemAdmin') {
            Response::error("Insufficient permissions", FORBIDDEN);
        }
        
        $reportService = new ReportService();
        $reportData = $reportService->getDeliveryReport($start_date, $end_date);
        
        Response::success($reportData, "Delivery report generated successfully");
    }
    public static function lowStockReport() {
        $user = authenticateUser();
        
        if ($user['role'] !== 'WarehouseAdmin' && $user['role'] !== 'SystemAdmin') {
            Response::error("Insufficient permissions", FORBIDDEN);
        }
        
        $reportService = new ReportService();
        $reportData = $reportService->getLowStockReport();
        
        Response::success($reportData, "Low stock report generated successfully");
    }
}

?>