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
        $stmt = $this->db->prepare("SELECT * FROM products WHERE Category_id = ? AND Status = 'Active' LIMIT ? OFFSET ?");
        $stmt->bind_param("sii", $category, $limit, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function createProduct($sku, $name, $description, $category_id, $unit_Price, $wholesale_Price, $unit) {
        $status = 'Active';
        $minWholesaleQty = 1;
        
        $stmt = $this->db->prepare("INSERT INTO products (SKU, Name, Description, Category_ID, Unit_Price, Wholesale_Price, Unit, Status, `min_wholesale_qty`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssddsss", $sku, $name, $description, $category_id, $unit_Price, $wholesale_Price, $unit, $status, $minWholesaleQty);
        
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        return false;
    }
    
    public function updateProduct($product_id, $name, $unit_Price, $wholesale_Price, $status) {
        $stmt = $this->db->prepare("UPDATE products SET Name = ?, Unit_Price = ?, Wholesale_Price = ?, Status = ? WHERE Product_ID = ?");
        $stmt->bind_param("sddsi", $name, $unit_Price, $wholesale_Price, $status, $product_id);
        return $stmt->execute();
    }
    public function deleteProduct($product_id) {
        $stmt = $this->db->prepare("UPDATE products SET Status = 'Inactive' WHERE Product_ID = ?");
        $stmt->bind_param("i", $product_id);
        return $stmt->execute();
    }
}

?>