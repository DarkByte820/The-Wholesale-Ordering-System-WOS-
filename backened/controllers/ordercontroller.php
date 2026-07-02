<?php
/**
 * Order Controller
 */

class OrderController {
    
    public static function createOrder() {
        $user = authenticateUser();
        $input = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($input['orderType']) || !isset($input['items']) || !isset($input['deliveryAddress']) || !isset($input['customerPhone'])) {
            Response::error("Missing required fields", BAD_REQUEST);
        }
        
        $total_amount = 0;
        $order = new Order();
        $product = new Product();
        $cart = new Cart();
        
        // Calculate total
        foreach ($input['items'] as $item) {
            if (isset($item['id']) && isset($item['price']) && isset($item['quantity'])) {
                $total_amount += $item['price'] * $item['quantity'];
            }
        }
        
        $delivery_city = isset($input['deliveryCity']) ? $input['deliveryCity'] : 'Accra';
        
        $order_id = $order->createOrder(
            $user['userId'],
            $input['orderType'],
            $total_amount,
            $input['deliveryAddress'],
            $delivery_city,
            $input['customerPhone']
        );
        
        if (!$order_id) {
            Response::error("Failed to create orders", SERVER_ERROR);
        }
        
        // Add items to order
        foreach ($input['items'] as $item) {
            if (isset($item['id']) && isset($item['quantity']) && isset($item['price'])) {
                $order->addOrderItem($order_id, $item['id'], $item['quantity'], $item['price']);
            }
        }
        
        // Clear cart
        $cart->clearCart($user['userId']);
        
        // Create delivery record
        $delivery = new Delivery();
        $delivery->createDelivery($order_id, $input['deliveryAddress'], $delivery_city, $input['customerPhone']);
        
        Logger::info("Order created", ['orderId' => $order_id, 'userId' => $user['userId']]);
        
        Response::success(['orderId' => $order_id, 'totalAmount' => $total_amount], "Order created successfully", CREATED);
    }
    
    public static function getMyOrders() {
        $user = authenticateUser();
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : ITEMS_PER_PAGE;
        
        $order = new Order();
        $orders = $order->getOrdersByUser($user['userId'], $page, $limit);
        
        Response::success($orders, "Orders retrieved successfully");
    }
    
    public static function getOrderById() {
        $user = authenticateUser();
        $order_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        
        if (!$order_id) {
            Response::error("Order ID required", BAD_REQUEST);
        }
        
        $order = new Order();
        $order_data = $order->getOrderById($order_id);
        
        if (!$order_data) {
            Response::error("Order not found", NOT_FOUND);
        }
        
        // Check authorization
        if ($order_data['UserID'] != $user['userId'] && $user['role'] !== 'WarehouseAdmin') {
            Response::error("Unauthorized", FORBIDDEN);
        }
        
        $order_items = $order->getOrderItems($order_id);
        $order_data['items'] = $order_items;
        
        Response::success($order_data, "Order retrieved successfully");
    }
    
    public static function updateOrderStatus() {
        authenticateUser(); // Just verify token
        $input = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($input['orderId']) || !isset($input['status'])) {
            Response::error("Order ID and status required", BAD_REQUEST);
        }
        
        $order = new Order();
        $result = $order->updateOrderStatus($input['orderId'], $input['status']);
        
        if ($result) {
            Logger::info("Order status updated", ['orderId' => $input['orderId'], 'status' => $input['status']]);
            Response::success(null, "Order status updated");
        } else {
            Response::error("Failed to update order status", SERVER_ERROR);
        }
    }
}

?>