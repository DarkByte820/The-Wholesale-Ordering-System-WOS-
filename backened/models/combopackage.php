<?php
/**
 * Combo Package Model
 * For Community Access Module (Students & Community Members)
 */

class ComboPackage {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }
    
    public function createPackage($name, $description, $target_group, $price, $start_date, $end_date, $stock_limit) {
        $status = 'Draft';
        
        $stmt = $this->db->prepare("INSERT INTO combo_packages (Name, Description, Target_Group, Price, Availability_Start, Availability_End, Status, StockLimit) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("sssdsssi", $name, $description, $target_group, $price, $start_date, $end_date, $status, $stock_limit);
        
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        
        error_log("Execute failed: " . $stmt->error);
        return false;
    }
    
    public function getAllPackages($page = 1, $limit = ITEMS_PER_PAGE) {
        $offset = ($page - 1) * $limit;
        
        $stmt = $this->db->prepare("SELECT * FROM combo_packages WHERE Status = 'Published' LIMIT ? OFFSET ?");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return [];
        }
        
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getPackageById($package_id) {
        $stmt = $this->db->prepare("SELECT * FROM combo_packages WHERE PackageID = ? AND Status = 'Published'");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("i", $package_id);
        $stmt->execute();
        $package = $stmt->get_result()->fetch_assoc();
        
        if ($package) {
            // Get package items
            $stmt2 = $this->db->prepare("SELECT pi.*, p.Name, p.SKU, p.UnitPrice FROM package_item pi JOIN product p ON pi.ProductID = p.ProductID WHERE pi.PackageID = ?");
            
            if ($stmt2) {
                $stmt2->bind_param("i", $package_id);
                $stmt2->execute();
                $package['items'] = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
            }
        }
        
        return $package;
    }
    
    public function getPackagesByTargetGroup($target_group, $page = 1, $limit = ITEMS_PER_PAGE) {
        $offset = ($page - 1) * $limit;
        
        $stmt = $this->db->prepare("SELECT * FROM combo_packages WHERE TargetGroup = ? AND Status = 'Published' LIMIT ? OFFSET ?");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return [];
        }
        
        $stmt->bind_param("sii", $target_group, $limit, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function addPackageItem($package_id, $product_id, $quantity) {
        $stmt = $this->db->prepare("INSERT INTO package_items (package_id, product_id, Quantity) VALUES (?, ?, ?)");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("iii", $package_id, $product_id, $quantity);
        return $stmt->execute();
    }
    
    public function publishPackage($package_id) {
        // Check if all products have stock
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as has_stock FROM (
                SELECT p.ProductID FROM package_item pi
                JOIN product p ON pi.ProductID = p.ProductID
                JOIN inventory i ON p.ProductID = i.ProductID
                WHERE pi.PackageID = ? AND i.StockQuantity > 0
            ) as items
        ");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("i", $package_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if (!$result['has_stock']) {
            return false;
        }
        
        $status = 'Published';
        $stmt2 = $this->db->prepare("UPDATE combo_package SET Status = ? WHERE PackageID = ?");
        
        if (!$stmt2) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt2->bind_param("si", $status, $package_id);
        return $stmt2->execute();
    }
    
    public function updatePackage($package_id, $name, $description, $price, $stock_limit) {
        $stmt = $this->db->prepare("UPDATE combo_packages SET Name = ?, Description = ?, Price = ?, StockLimit = ? WHERE PackageID = ?");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("ssdii", $name, $description, $price, $stock_limit, $package_id);
        return $stmt->execute();
    }
    
    public function getPackageItems($package_id) {
        $stmt = $this->db->prepare("SELECT pi.*, p.Name, p.SKU, p.UnitPrice FROM package_item pi JOIN product p ON pi.ProductID = p.ProductID WHERE pi.PackageID = ?");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return [];
        }
        
        $stmt->bind_param("i", $package_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

?>