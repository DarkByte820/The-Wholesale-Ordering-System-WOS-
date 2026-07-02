<?php
/**
 * Product Model
 */

class Product {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }
    
    public function getAllProducts($page = 1, $limit = ITEMS_PER_PAGE, $category = null) {
        $offset = ($page - 1) * $limit;
        
        if ($category) {
            $stmt = $this->db->prepare("SELECT * FROM products WHERE Category = ? AND Status = 'Active' LIMIT ? OFFSET ?");
            $stmt->bind_param("sii", $category, $limit, $offset);
        } else {
            $stmt = $this->db->prepare("SELECT * FROM products WHERE Status = 'Active' LIMIT ? OFFSET ?");
            $stmt->bind_param("ii", $limit, $offset);
        }
        
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getProductById($product_id) {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE ProductID = ? AND Status = 'Active'");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function searchProducts($search_term) {
        $search = "%$search_term%";
        $stmt = $this->db->prepare("SELECT * FROM products WHERE (Name LIKE ? OR Description LIKE ?) AND Status = 'Active' LIMIT 50");
        $stmt->bind_param("ss", $search, $search);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getProductsByCategory($category, $page = 1, $limit = ITEMS_PER_PAGE) {
        $offset = ($page - 1) * $limit;
        $stmt = $this->db->prepare("SELECT * FROM products WHERE Category = ? AND Status = 'Active' LIMIT ? OFFSET ?");
        $stmt->bind_param("sii", $category, $limit, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function createProduct($sku, $name, $description, $category, $unitPrice, $wholesalePrice, $unit, $supplier) {
        $status = 'Active';
        $minWholesaleQty = 1;
        
        $stmt = $this->db->prepare("INSERT INTO products (SKU, Name, Description, Category, UnitPrice, WholesalePrice, Unit, Status, MinimumWholesaleQty, Supplier) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssddsssi", $sku, $name, $description, $category, $unitPrice, $wholesalePrice, $unit, $status, $minWholesaleQty, $supplier);
        
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        return false;
    }
    
    public function updateProduct($product_id, $name, $unitPrice, $wholesalePrice, $status) {
        $stmt = $this->db->prepare("UPDATE products SET Name = ?, UnitPrice = ?, WholesalePrice = ?, Status = ? WHERE ProductID = ?");
        $stmt->bind_param("sddsi", $name, $unitPrice, $wholesalePrice, $status, $product_id);
        return $stmt->execute();
    }
}

?>