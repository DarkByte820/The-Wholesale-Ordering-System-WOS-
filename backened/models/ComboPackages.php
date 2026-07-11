<?php

class ComboPackages {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function createPackage($name, $description, $target_group, $price, $start_date, $end_date, $stock_limit) {

        $status = 'Draft';
        
        $stmt = $this->db->prepare("
            INSERT INTO combo_packages
            (Name, Description, Target_Group, Price, Availability_Start, Availability_End, Status, StockLimit)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }

        $stmt->bind_param(
            "sssdsssi",
            $name,
            $description,
            $target_group,
            $price,
            $start_date,
            $end_date,
            $status,
            $stock_limit
        );

        if ($stmt->execute()) {
            return $this->db->insert_id;
        }

        error_log("Execute failed: " . $stmt->error);
        return false;
    }

    public function getAllPackages($page = 1, $limit = 10) {

        $offset = ($page - 1) * $limit;

        $stmt = $this->db->prepare("
            SELECT * FROM combo_packages
            WHERE Status = 'Published'
            LIMIT ? OFFSET ?
        ");

        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return [];
        }

        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getPackageById($package_id) {

        $stmt = $this->db->prepare("
            SELECT * FROM combo_packages
            WHERE PackageID = ? AND Status = 'Published'
        ");

        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }

        $stmt->bind_param("i", $package_id);
        $stmt->execute();

        $package = $stmt->get_result()->fetch_assoc();

        if ($package) {
            $package['items'] = $this->getPackageItems($package_id);
        }

        return $package;
    }

    public function getPackagesByTargetGroup($target_group, $page = 1, $limit = 10) {

        $offset = ($page - 1) * $limit;

        $stmt = $this->db->prepare("
            SELECT * FROM combo_packages
            WHERE Target_Group = ? AND Status = 'Published'
            LIMIT ? OFFSET ?
        ");

        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return [];
        }

        $stmt->bind_param("sii", $target_group, $limit, $offset);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function addPackageItem($package_id, $product_id, $quantity) {

        $stmt = $this->db->prepare("
            INSERT INTO package_items (PackageID, ProductID, Quantity)
            VALUES (?, ?, ?)
        ");

        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }

        $stmt->bind_param("iii", $package_id, $product_id, $quantity);

        return $stmt->execute();
    }

    public function getPackageItems($package_id) {

        $stmt = $this->db->prepare("
            SELECT pi.*, p.Name, p.SKU, p.UnitPrice
            FROM package_items pi
            JOIN product p ON pi.ProductID = p.ProductID
            WHERE pi.PackageID = ?
        ");

        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return [];
        }

        $stmt->bind_param("i", $package_id);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function publishPackage($package_id) {

        // FIXED STOCK CHECK (simple + safe)
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as cnt
            FROM package_items pi
            JOIN inventory i ON pi.product_id = i.Product_id
            WHERE pi.Package_id = ? AND i.Stock_Quantity > 0
        ");

        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }

        $stmt->bind_param("i", $package_id);
        $stmt->execute();

        $result = $stmt->get_result()->fetch_assoc();

        if ((int)$result['cnt'] <= 0) {
            return false;
        }

        $status = 'Published';

        $stmt2 = $this->db->prepare("
            UPDATE combo_packages
            SET Status = ?
            WHERE Package_id = ?
        ");

        if (!$stmt2) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }

        $stmt2->bind_param("si", $status, $package_id);

        return $stmt2->execute();
    }

    public function updatePackage($package_id, $name, $description, $price, $stock_limit) {

        $stmt = $this->db->prepare("
            UPDATE combo_packages
            SET Name = ?, Description = ?, Price = ?, StockLimit = ?
            WHERE PackageID = ?
        ");

        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }

        $stmt->bind_param("ssdii", $name, $description, $price, $stock_limit, $package_id);

        return $stmt->execute();
    }
}