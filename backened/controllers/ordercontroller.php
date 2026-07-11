<?php
/**
 * Order Controller
 */

class OrderController
{
    public static function createOrder()
    {
        $user = authenticateUser();
        $input = json_decode(file_get_contents("php://input"), true);

        if (
            !isset($input['OrderType']) ||
            !isset($input['items']) ||
            !isset($input['deliveryAddress']) ||
            !isset($input['CustomerPhone'])
        ) {
            Response::error("Missing required fields", BAD_REQUEST);
            return;
        }

        $totalAmount = 0;
        $order = new Order();
        $cart = new Cart();

        // Calculate total safely
        foreach ($input['items'] as $item) {
            if (
                isset($item['price']) &&
                isset($item['quantity'])
            ) {
                $totalAmount += ((float)$item['price'] * (int)$item['quantity']);
            }
        }

        $deliveryCity = $input['deliveryCity'] ?? 'Accra';

        $orderId = $order->createOrder(
            $user['userId'],
            $input['OrderType'],
            $totalAmount,
            $input['deliveryAddress'],
            $deliveryCity,
            $input['customerPhone']
        );

        if (!$orderId) {
            Response::error("Failed to create order", SERVER_ERROR);
            return;
        }

        // Add order items
        foreach ($input['items'] as $item) {
            if (
                isset($item['id']) &&
                isset($item['quantity']) &&
                isset($item['price'])
            ) {
                $order->addOrderItem(
                    $orderId,
                    $item['id'],
                    $item['quantity'],
                    $item['price']
                );
            }
        }

        // Clear cart safely (only if user exists)
        if (isset($user['userId'])) {
            $cart->clearCart($user['userId']);
        }

        // Delivery record
        $delivery = new Delivery();
        $delivery->createDelivery(
            $orderId,
            $input['deliveryAddress'],
            $deliveryCity,
            $input['customerPhone']
        );

        Logger::info("Order created", [
            'orderId' => $orderId,
            'userId' => $user['userId']
        ]);

        Response::success(
            [
                'orderId' => $orderId,
                'totalAmount' => $totalAmount
            ],
            "Order created successfully",
            CREATED
        );
    }

    public static function getMyOrders()
    {
        $user = authenticateUser();
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : ITEMS_PER_PAGE;

        $order = new Order();
        $orders = $order->getOrdersByUser($user['userId'], $page, $limit);

        Response::success($orders, "Orders retrieved successfully");
    }

    public static function getOrderById()
    {
        $user = authenticateUser();
        $orderId = isset($_GET['id']) ? (int)$_GET['id'] : null;

        if (!$orderId) {
            Response::error("Order ID required", BAD_REQUEST);
            return;
        }

        $order = new Order();
        $orderData = $order->getOrderById($orderId);

        if (!$orderData) {
            Response::error("Order not found", NOT_FOUND);
            return;
        }

        // FIXED: safe key check (your logs showed undefined array key issues)
        if (
            ($orderData['UserID'] ?? null) != $user['userId'] &&
            ($user['role'] ?? '') !== 'WarehouseAdmin'
        ) {
            Response::error("Unauthorized", FORBIDDEN);
            return;
        }
        

    }

public function getOrderItems($orderId) {

    global $conn;

    $sql = "SELECT * FROM order_items WHERE order_id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $orderId);
    $stmt->execute();

    $result = $stmt->get_result();
    $items = [];

    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }

    return $items;
}
    public static function updateOrderStatus()
    {
        authenticateUser();

        $input = json_decode(file_get_contents("php://input"), true);

        if (!isset($input['orderId']) || !isset($input['status'])) {
            Response::error("Order ID and status required", BAD_REQUEST);
            return;
        }

        $order = new Order();
        $result = $order->updateOrderStatus($input['orderId'], $input['status']);

        if (!$result) {
            Response::error("Failed to update order status", SERVER_ERROR);
            return;
        }

        Logger::info("Order status updated", [
            'orderId' => $input['orderId'],
            'status' => $input['status']
        ]);

        Response::success(null, "Order status updated");
    }

    public static function getAllOrders()
    {
        authenticateUser();

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : ITEMS_PER_PAGE;

        $order = new Order();
        $orders = $order->getAllOrders($page, $limit);

        Response::success($orders, "All orders retrieved successfully");
    }
    public static function cancelOrder()
    {
        $user = authenticateUser();
        $input = json_decode(file_get_contents("php://input"), true);

        if (!isset($input['orderId'])) {
            Response::error("Order ID required", BAD_REQUEST);
            return;
        }

        $order = new Order();
        $orderData = $order->getOrderById($input['orderId']);

        if (!$orderData) {
            Response::error("Order not found", NOT_FOUND);
            return;
        }

        if ($orderData['UserID'] != $user['userId']) {
            Response::error("Unauthorized", FORBIDDEN);
            return;
        }

        if ($orderData['Status'] === 'Cancelled') {
            Response::error("Order is already cancelled", BAD_REQUEST);
            return;
        }

        $result = $order->updateOrderStatus($input['orderId'], 'Cancelled');

        if (!$result) {
            Response::error("Failed to cancel order", SERVER_ERROR);
            return;
        }

        Logger::info("Order cancelled", [
            'orderId' => $input['orderId'],
            'userId' => $user['userId']
        ]);

        Response::success(null, "Order cancelled successfully");
    }
}