<?php
/**
 * Order Model
 */

class Order {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }
    
    public function createOrder($UserID, $OrderType, $TotalAmount, $DeliveryAddress, $DeliveryCity, $CustomerPhone) {
    $status = 'Pending';
    $payment_status = 'Unpaid';

    $stmt = $this->db->prepare("INSERT INTO `orders` (UserID, OrderType, Status, TotalAmount, PaymentStatus, DeliveryAddress, DeliveryCity, CustomerPhone) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "issdssss",
        $UserID,
        $OrderType,
        $status,
        $TotalAmount,
        $PaymentStatus,
        $DeliveryAddress,
        $DeliveryCity,
        $CustomerPhone
    );

    if ($stmt->execute()) {
        return $this->db->insert_id;
    }

    return false;
}
    
    public function addOrderItem($orders_id, $product_id, $quantity, $unit_price) {
        $total_price = $quantity * $unit_price;
        
        $stmt = $this->db->prepare("INSERT INTO order_item (OrderID, ProductID, Quantity, UnitPrice, TotalPrice) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiidd", $orders_id, $product_id, $quantity, $unit_price, $total_price);
        
        return $stmt->execute();
    }
    
    public function getOrderById($order_id) {
        $stmt = $this->db->prepare("SELECT * FROM `orders` WHERE OrderID = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
  public function getOrdersByUser($user_id, $page = 1, $limit = ITEMS_PER_PAGE) {
    $offset = ($page - 1) * $limit;

    $sql = "SELECT * FROM orders WHERE UserID = ? ORDER BY OrderID DESC LIMIT ? OFFSET ?";

    $stmt = $this->db->prepare($sql);

    if ($stmt === false) {
        die("Prepare failed: " . $this->db->error);
    }

    $stmt->bind_param("iii", $user_id, $limit, $offset);
    $stmt->execute();

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
    
    public function updateOrderStatus($order_id, $status) {
        $stmt = $this->db->prepare("UPDATE `orders` SET Status = ? WHERE OrderID = ?");
        $stmt->bind_param("si", $status, $order_id);
        return $stmt->execute();
    }
    
    public function updatePaymentStatus($order_id, $payment_status) {
        $stmt = $this->db->prepare("UPDATE `orders` SET PaymentStatus = ? WHERE OrderID = ?");
        $stmt->bind_param("si", $payment_status, $order_id);
        return $stmt->execute();
    }
}

?>