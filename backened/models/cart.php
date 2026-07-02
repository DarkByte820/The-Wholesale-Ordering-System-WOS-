<?php
/**
 * Cart Model
 */

class Cart {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }
    
    public function addToCart($user_id, $product_id, $quantity) {
        $stmt = $this->db->prepare("INSERT INTO cart (UserID, ProductID, Quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE Quantity = Quantity + ?");
        $stmt->bind_param("iiii", $user_id, $product_id, $quantity, $quantity);
        return $stmt->execute();
    }
    
    public function getCart($user_id) {
        $stmt = $this->db->prepare("SELECT c.CartID, c.ProductID, c.Quantity, p.Name, p.UnitPrice, p.SKU FROM cart c JOIN product p ON c.ProductID = p.ProductID WHERE c.UserID = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function updateQuantity($cart_id, $quantity) {
        if ($quantity <= 0) {
            return $this->removeFromCart($cart_id);
        }
        
        $stmt = $this->db->prepare("UPDATE cart SET Quantity = ? WHERE CartID = ?");
        $stmt->bind_param("ii", $quantity, $cart_id);
        return $stmt->execute();
    }
    
    public function removeFromCart($cart_id) {
        $stmt = $this->db->prepare("DELETE FROM cart WHERE CartID = ?");
        $stmt->bind_param("i", $cart_id);
        return $stmt->execute();
    }
    
    public function clearCart($user_id) {
        $stmt = $this->db->prepare("DELETE FROM cart WHERE UserID = ?");
        $stmt->bind_param("i", $user_id);
        return $stmt->execute();
    }
    
    public function getCartTotal($user_id) {
        $stmt = $this->db->prepare("SELECT SUM(c.Quantity * p.UnitPrice) as total FROM cart c JOIN product p ON c.ProductID = p.ProductID WHERE c.UserID = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['total'] ?? 0;
    }
}

?>