<?php
/**
 * Ghana Warehouse Connect - Main Router
 * UPDATED WITH ALL NEW ENDPOINTS
 */

// Load Configuration
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/constants.php';

// Load Utilities
require_once __DIR__ . '/utils/Database.php';
require_once __DIR__ . '/utils/Response.php';
require_once __DIR__ . '/utils/JWT.php';
require_once __DIR__ . '/utils/Logger.php';
require_once __DIR__ . '/utils/Validator.php';
require_once __DIR__ . '/middleware/cors.php';
require_once __DIR__ . '/middleware/auth.php';

// Load Models
$models = ['User', 'Product', 'Order', 'Cart', 'Payment', 'Inventory', 'Delivery', 'ComboPackage', 'CustomerProfile', 'Invoice', 'Notification', 'AuditLog'];
foreach ($models as $model) {
    $file = __DIR__ . '/models/' . $model . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
}

// Load Controllers
$controllers = ['AuthController', 'ProductController', 'OrderController', 'CartController', 'PaymentController', 'DeliveryController', 'ComboPackageController', 'CustomerProfileController', 'InvoiceController', 'NotificationController', 'ReportController', 'AuditLogController'];
foreach ($controllers as $controller) {
    $file = __DIR__ . '/controllers/' . $controller . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
}

// Parse Request
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];
$request_path = str_replace('/backend/index.php', '', $request_uri);
$request_path = ltrim($request_path, '/');

// Route Dispatcher
try {
    // ========== AUTHENTICATION ROUTES ==========
    if ($request_path === 'api/auth/register' && $method === 'POST') {
        AuthController::register();
    }
    elseif ($request_path === 'api/auth/login' && $method === 'POST') {
        AuthController::login();
    }

    // ========== PRODUCT ROUTES ==========
    elseif ($request_path === 'api/products' && $method === 'GET') {
        ProductController::getAllProducts();
    }
    elseif (preg_match('#^api/products/(\d+)$#', $request_path, $matches) && $method === 'GET') {
        $_GET['id'] = $matches[1];
        ProductController::getProductById();
    }
    elseif (strpos($request_path, 'api/products/search') === 0 && $method === 'GET') {
        ProductController::search();
    }

    // ========== COMBO PACKAGE ROUTES (Community Access Module) ==========
    elseif ($request_path === 'api/combo-packages' && $method === 'GET') {
        ComboPackageController::getAllPackages();
    }
    elseif (preg_match('#^api/combo-packages/(\d+)$#', $request_path, $matches) && $method === 'GET') {
        $_GET['id'] = $matches[1];
        ComboPackageController::getPackageById();
    }
    elseif (strpos($request_path, 'api/combo-packages/target-group') === 0 && $method === 'GET') {
        ComboPackageController::getByTargetGroup();
    }
    elseif ($request_path === 'api/combo-packages' && $method === 'POST') {
        ComboPackageController::createPackage();
    }
    elseif ($request_path === 'api/combo-packages/publish' && $method === 'PUT') {
        ComboPackageController::publishPackage();
    }

    // ========== CART ROUTES ==========
    elseif ($request_path === 'api/cart' && $method === 'GET') {
        CartController::getCart();
    }
    elseif ($request_path === 'api/cart' && $method === 'POST') {
        CartController::addToCart();
    }
    elseif ($request_path === 'api/cart/update' && $method === 'PUT') {
        CartController::updateCartItem();
    }
    elseif (preg_match('#^api/cart/(\d+)$#', $request_path, $matches) && $method === 'DELETE') {
        $_POST['cartId'] = $matches[1];
        CartController::removeFromCart();
    }
    elseif ($request_path === 'api/cart/clear' && $method === 'POST') {
        CartController::clearCart();
    }

    // ========== ORDER ROUTES ==========
    elseif ($request_path === 'api/orders' && $method === 'POST') {
        OrderController::createOrder();
    }
    elseif ($request_path === 'api/orders' && $method === 'GET') {
        OrderController::getMyOrders();
    }
    elseif (preg_match('#^api/orders/(\d+)$#', $request_path, $matches) && $method === 'GET') {
        $_GET['id'] = $matches[1];
        OrderController::getOrderById();
    }
    elseif (strpos($request_path, 'api/orders/status') === 0 && $method === 'PUT') {
        OrderController::updateOrderStatus();
    }

    // ========== PAYMENT ROUTES ==========
    elseif ($request_path === 'api/payments/initiate' && $method === 'POST') {
        PaymentController::initiatePayment();
    }
    elseif ($request_path === 'api/payments/verify' && $method === 'POST') {
        PaymentController::verifyPayment();
    }
    elseif (preg_match('#^api/payments/(\d+)$#', $request_path, $matches) && $method === 'GET') {
        $_GET['id'] = $matches[1];
        PaymentController::getPaymentStatus();
    }

    // ========== INVOICE ROUTES ==========
    elseif (preg_match('#^api/invoices/(\d+)$#', $request_path, $matches) && $method === 'GET') {
        $_GET['id'] = $matches[1];
        InvoiceController::getInvoiceById();
    }
    elseif ($request_path === 'api/invoices/my-invoices' && $method === 'GET') {
        InvoiceController::getMyInvoices();
    }

    // ========== DELIVERY ROUTES ==========
    elseif ($request_path === 'api/delivery' && $method === 'GET') {
        DeliveryController::getDeliveryStatus();
    }
    elseif ($request_path === 'api/delivery/status' && $method === 'PUT') {
        DeliveryController::updateDeliveryStatus();
    }
    elseif ($request_path === 'api/delivery/proof' && $method === 'POST') {
        DeliveryController::recordProofOfDelivery();
    }

    // ========== CUSTOMER PROFILE ROUTES ==========
    elseif ($request_path === 'api/profile' && $method === 'GET') {
        CustomerProfileController::getProfile();
    }
    elseif ($request_path === 'api/profile' && $method === 'PUT') {
        CustomerProfileController::updateProfile();
    }
    elseif ($request_path === 'api/profile/wholesale-details' && $method === 'POST') {
        CustomerProfileController::addWholesaleDetails();
    }
    elseif ($request_path === 'api/admin/wholesale-customers' && $method === 'GET') {
        CustomerProfileController::getWholesaleCustomers();
    }
    elseif ($request_path === 'api/admin/verify-customer' && $method === 'PUT') {
        CustomerProfileController::verifyWholesaleCustomer();
    }

    // ========== NOTIFICATION ROUTES ==========
    elseif ($request_path === 'api/notifications' && $method === 'GET') {
        NotificationController::getNotifications();
    }
    elseif ($request_path === 'api/notifications/unread' && $method === 'GET') {
        NotificationController::getUnreadNotifications();
    }
    elseif ($request_path === 'api/notifications/mark-read' && $method === 'PUT') {
        NotificationController::markAsRead();
    }
    elseif ($request_path === 'api/notifications/mark-all-read' && $method === 'PUT') {
        NotificationController::markAllAsRead();
    }

    // ========== REPORT ROUTES ==========
    elseif ($request_path === 'api/reports/sales' && $method === 'GET') {
        ReportController::getSalesReport();
    }
    elseif ($request_path === 'api/reports/inventory' && $method === 'GET') {
        ReportController::getInventoryReport();
    }
    elseif ($request_path === 'api/reports/orders' && $method === 'GET') {
        ReportController::getOrdersReport();
    }
    elseif ($request_path === 'api/reports/delivery' && $method === 'GET') {
        ReportController::getDeliveryReport();
    }
    elseif ($request_path === 'api/reports/dashboard-metrics' && $method === 'GET') {
        ReportController::getDashboardMetrics();
    }

    // ========== AUDIT LOG ROUTES ==========
    elseif ($request_path === 'api/audit-logs' && $method === 'GET') {
        AuditLogController::getAuditLogs();
    }
    elseif (strpos($request_path, 'api/audit-logs/entity') === 0 && $method === 'GET') {
        AuditLogController::getAuditLogsByEntity();
    }
    elseif (strpos($request_path, 'api/audit-logs/date-range') === 0 && $method === 'GET') {
        AuditLogController::getAuditLogsByDateRange();
    }

    // ========== NOT FOUND ==========
    else {
        Response::error("Route not found: $request_path", NOT_FOUND);
    }

} catch (Exception $e) {
    Logger::error("Exception in router", ['message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
    Response::error("Server error", SERVER_ERROR);
}

?>