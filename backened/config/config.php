<?php
/**
 * Ghana Warehouse Connect
 * Configuration File
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ghana_warehouse_connect');

// API Configuration
define('API_URL', 'http://localhost:8080/api/');
define('FRONTEND_URL', 'http://localhost:8080/');

// JWT Configuration
define('JWT_SECRET', 'your_super_secret_jwt_key_change_in_production_12345');
define('JWT_EXPIRY', 86400); // 24 hours

// Response Codes
define('SUCCESS', 200);
define('CREATED', 201);
define('BAD_REQUEST', 400);
define('UNAUTHORIZED', 401);
define('FORBIDDEN', 403);
define('NOT_FOUND', 404);
define('SERVER_ERROR', 500);

// Pagination
define('ITEMS_PER_PAGE', 10);

// Error Handling
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php-error.log');

// Set Headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle CORS Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Create logs directory if it doesn't exist
$logs_dir = __DIR__ . '/../logs';
if (!is_dir($logs_dir)) {
    mkdir($logs_dir, 0755, true);
}

?>