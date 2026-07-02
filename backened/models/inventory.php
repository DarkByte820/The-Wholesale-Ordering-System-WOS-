<?php
/**
 * Inventory Model
 */

class Inventory {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }
    
    public function getInventory($page = 1, $limit = ITEMS_PER_PAGE) {
        $offset = ($page - 1) * $limit;
        
        $stmt = $this->db->prepare("SELECT i.*, p.Name, p.SKU FROM inventory i JOIN product p ON i.ProductID = p.ProductID LIMIT ? OFFSET ?");
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getInventoryById($inventory_id) {
        $stmt = $this->db->prepare("SELECT * FROM inventory WHERE InventoryID = ?");
        $stmt->bind_param("i", $inventory_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function getInventoryByProductId($product_id) {
        $stmt = $this->db->prepare("SELECT * FROM inventory WHERE ProductID = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function getLowStockItems() {
        $stmt = $this->db->prepare("SELECT i.*, p.Name, p.SKU FROM inventory i JOIN product p ON i.ProductID = p.ProductID WHERE i.StockQuantity <= i.ReorderLevel");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function updateStock($inventory_id, $quantity, $reorder_level = null, $reorder_qty = null) {
        if ($reorder_level !== null && $reorder_qty !== null) {
            $stmt = $this->db->prepare("UPDATE inventory SET StockQuantity = ?, ReorderLevel = ?, ReorderQuantity = ?, LastUpdated = NOW() WHERE InventoryID = ?");
            $stmt->bind_param("iiii", $quantity, $reorder_level, $reorder_qty, $inventory_id);
        } else {
            $stmt = $this->db->prepare("UPDATE inventory SET StockQuantity = ?, LastUpdated = NOW() WHERE InventoryID = ?");
            $stmt->bind_param("ii", $quantity, $inventory_id);
        }
        
        return $stmt->execute();
    }
    
    public function deductStock($product_id, $quantity) {
        $stmt = $this->db->prepare("UPDATE inventory SET StockQuantity = StockQuantity - ? WHERE ProductID = ? AND StockQuantity >= ?");
        $stmt->bind_param("iii", $quantity, $product_id, $quantity);
        return $stmt->execute();
    }
    
    public function restockItem($product_id, $quantity) {
        $stmt = $this->db->prepare("UPDATE inventory SET StockQuantity = StockQuantity + ?, LastRestockDate = CURDATE() WHERE ProductID = ?");
        $stmt->bind_param("ii", $quantity, $product_id);
        return $stmt->execute();
    }
}

?>