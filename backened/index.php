<?php



/**
 * Ghana Warehouse Connect - Main Router 
 */

// ================== CONFIG ==================
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/constants.php';

// ================== CORE UTILITIES ==================
require_once __DIR__ . '/utils/Database.php';
require_once __DIR__ . '/utils/Response.php';
require_once __DIR__ . '/utils/JWT.php';
require_once __DIR__ . '/utils/Logger.php';
require_once __DIR__ . '/utils/Validator.php';

// ================== MIDDLEWARE ==================
require_once __DIR__ . '/middleware/cors.php';
require_once __DIR__ . '/middleware/auth.php';

// ================== AUTOLOADER (IMPORTANT FIX) ==================
spl_autoload_register(function ($class) {

    $paths = [
    __DIR__ . "/controllers/$class.php",
    __DIR__ . "/models/$class.php",
    __DIR__ . "/services/$class.php",
    __DIR__ . "/utils/$class.php",
    __DIR__ . "/middleware/$class.php"
];
    foreach ($paths as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// ================== REQUEST PARSING ==================
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// remove base path if needed
$request_path = str_replace('/backend/index.php', '', $request_uri);
$request_path = trim($request_path, '/');

// ================== ROUTER ==================
try {

    // ========== AUTH ==========
    if ($request_path === 'api/auth/register' && $method === 'POST') {
        AuthController::register();
    }
    elseif ($request_path === 'api/auth/login' && $method === 'POST') {
        AuthController::login();
    }

    // ========== PRODUCTS ==========
    elseif ($request_path === 'api/products' && $method === 'POST') {
        ProductController::createProduct();
    }
    elseif (preg_match('#^api/products/(\d+)$#', $request_path, $m) && $method === 'PUT') {
        ProductController::updateProduct();
    }
    elseif ($request_path === 'api/products/search' && $method === 'GET') {
        ProductController::searchProducts();

    }elseif (preg_match('#^api/products/category/([^/]+)$#', $request_path, $m) && $method === 'GET') {
        $_GET['category'] = $m[1];
        ProductController::getByCategory();

    }
    
    
    elseif (preg_match('#^api/products/(\d+)$#', $request_path, $m) && $method === 'DELETE') {
        $_GET['id'] = $m[1];
        ProductController::deleteProduct();
    }
    elseif (preg_match('#^api/products/(\d+)$#', $request_path, $m) && $method === 'GET') {
        $_GET['id'] = $m[1];
        ProductController::getProductById();
    }
    // ========== COMBO PACKAGES ==========
    elseif ($request_path === 'api/combo-packages' && $method === 'GET') {
        ComboPackageController::getAllPackages();
    }
    elseif (preg_match('#^api/combo-packages/(\d+)$#', $request_path, $m) && $method === 'GET') {
        $_GET['id'] = $m[1];
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

    // ========== CART ==========
    elseif ($request_path === 'api/cart' && $method === 'GET') {
        CartController::getCart();
    }
    elseif ($request_path === 'api/cart' && $method === 'POST') {
        CartController::addToCart();
    }

    // ========== ORDERS ==========
    elseif ($request_path === 'api/orders' && $method === 'POST') {
        OrderController::createOrder();
    }
    elseif ($request_path === 'api/orders' && $method === 'GET') {
        OrderController::getMyOrders();
    }

        // ========== PAYMENTS ==========
    elseif ($request_path === 'api/payments/initiate' && $method === 'POST') {
        PaymentController::initiatePayment();
    }
    elseif ($request_path === 'api/payments/verify' && $method === 'POST') {
        PaymentController::verifyPayment();
    }
    elseif (preg_match('#^api/payments/status/(\d+)$#', $request_path, $m) && $method === 'GET') {
        $_GET['orderId'] = $m[1];
        PaymentController::checkStatus();
    }

    // ========== CHECKOUT ==========
    elseif ($request_path === 'api/checkout' && $method === 'POST') {
        CheckoutController::processCheckout();
    }
    elseif ($request_path === 'api/checkout/summary' && $method === 'POST') {
        CheckoutController::getOrderSummary();
    }

    // ========== ORDER MANAGEMENT ==========
    elseif ($request_path === 'api/orders/all' && $method === 'GET') {
        OrderController::getAllOrders();
    }
    elseif ($request_path === 'api/orders/status' && $method === 'PUT') {
        OrderController::updateOrderStatus();
    }
    elseif ($request_path === 'api/orders/cancel' && $method === 'PUT') {
        OrderController::cancelOrder();
    }

    // ========== INVOICES ==========
    elseif ($request_path === 'api/invoices/generate' && $method === 'POST') {
        InvoiceController::generateInvoice();
    }
   elseif (preg_match('#^api/invoices/pdf/(\d+)$#', $request_path, $m) && $method === 'GET') {
    $_GET['invoice_Id'] = $m[1];
    InvoiceController::generatePDF();
}

    // ========== DASHBOARD ==========
    elseif ($request_path === 'api/dashboard' && $method === 'GET') {
        DashboardController::getDashboard();
    }
    elseif ($request_path === 'api/dashboard/sales-trend' && $method === 'GET') {
        DashboardController::salesTrend();
    }
    elseif ($request_path === 'api/dashboard/order-status' && $method === 'GET') {
        DashboardController::orderStatusBreakdown();
    }

    // ========== REPORTS ==========
    elseif ($request_path === 'api/reports/sales' && $method === 'GET') {
        ReportController::salesReport();
    }
    elseif ($request_path === 'api/reports/inventory' && $method === 'GET') {
        ReportController::inventoryReport();
    }
    elseif ($request_path === 'api/reports/customers' && $method === 'GET') {
        ReportController::customerReport();
    }
    elseif ($request_path === 'api/reports/payments' && $method === 'GET') {
        ReportController::paymentReport();
    }
    elseif ($request_path === 'api/reports/delivery' && $method === 'GET') {
        ReportController::deliveryReport();
    }
    elseif ($request_path === 'api/reports/low-stock' && $method === 'GET') {
        ReportController::lowStockReport();
    }

    // ========== NOTIFICATIONS ==========
    elseif ($request_path === 'api/notifications' && $method === 'POST') {
        NotificationController::sendNotification();
    }

    // ========== AUDIT ==========
    elseif ($request_path === 'api/audit' && $method === 'GET') {
        AuditLogController::getAuditTrail();
    }

    // ========== DEFAULT ==========
    else {
        Response::error("Route not found: $request_path", NOT_FOUND);
    }
    } catch (Exception $e) {

    Logger::error("Router Error", [
        'message' => $e->getMessage(),
        'file'    => $e->getFile(),
        'line'    => $e->getLine(),
        'trace'   => $e->getTraceAsString()
    ]);

    Response::error("Internal Server Error", SERVER_ERROR);
}

?>