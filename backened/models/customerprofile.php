<?php
/**
 * Customer Profile Model
 * Stores Bundle & Wholesale Customer Details
 */

class CustomerProfile {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }
    
    public function createProfile($user_id, $customer_type, $full_address, $city, $region, $postal_code) {
        $verification_status = 'Unverified';
        
        $stmt = $this->db->prepare("INSERT INTO customer_profile (UserID, CustomerType, FullAddress, City, Region, PostalCode, VerificationStatus) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("isssss", $user_id, $customer_type, $full_address, $city, $region, $postal_code, $verification_status);
        
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        
        error_log("Execute failed: " . $stmt->error);
        return false;
    }
    
    public function getProfileByUserId($user_id) {
        $stmt = $this->db->prepare("SELECT * FROM customer_profile WHERE UserID = ?");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function updateProfile($user_id, $full_address, $city, $region, $postal_code) {
        $stmt = $this->db->prepare("UPDATE customer_profile SET FullAddress = ?, City = ?, Region = ?, PostalCode = ?, DateModified = NOW() WHERE UserID = ?");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("ssssi", $full_address, $city, $region, $postal_code, $user_id);
        return $stmt->execute();
    }
    
    public function updateVerificationStatus($user_id, $status) {
        $stmt = $this->db->prepare("UPDATE customer_profile SET VerificationStatus = ? WHERE UserID = ?");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("si", $status, $user_id);
        return $stmt->execute();
    }
    
    public function addWholesaleDetails($user_id, $business_name, $business_license) {
        $stmt = $this->db->prepare("UPDATE customer_profile SET BusinessName = ?, BusinessLicense = ?, VerificationStatus = 'Pending' WHERE UserID = ?");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("ssi", $business_name, $business_license, $user_id);
        return $stmt->execute();
    }
    
    public function getWholesaleCustomers($page = 1, $limit = ITEMS_PER_PAGE) {
        $offset = ($page - 1) * $limit;
        $customer_type = 'WholesaleCustomer';
        
        $stmt = $this->db->prepare("SELECT cp.*, u.Name, u.Email, u.Phone FROM customer_profile cp JOIN user u ON cp.UserID = u.UserID WHERE cp.CustomerType = ? LIMIT ? OFFSET ?");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return [];
        }
        
        $stmt->bind_param("sii", $customer_type, $limit, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getPendingVerifications() {
        $status = 'Pending';
        
        $stmt = $this->db->prepare("SELECT cp.*, u.Name, u.Email FROM customer_profile cp JOIN user u ON cp.UserID = u.UserID WHERE cp.VerificationStatus = ? AND cp.CustomerType = 'WholesaleCustomer'");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return [];
        }
        
        $stmt->bind_param("s", $status);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

?>