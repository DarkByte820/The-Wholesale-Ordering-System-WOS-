<?php
/**
 * Customer Profile Controller
 */

class CustomerProfileController {
    
    public static function getProfile() {
        $user = authenticateUser();
        
        $profile = new CustomerProfile();
        $profile_data = $profile->getProfileByUserId($user['userId']);
        
        if (!$profile_data) {
            Response::error("Profile not found", NOT_FOUND);
        }
        
        Response::success($profile_data, "Profile retrieved successfully");
    }
    
    public static function updateProfile() {
        $user = authenticateUser();
        $input = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($input['fullAddress']) || !isset($input['city'])) {
            Response::error("Missing required fields", BAD_REQUEST);
        }
        
        $profile = new CustomerProfile();
        $result = $profile->updateProfile(
            $user['userId'],
            $input['fullAddress'],
            $input['city'],
            $input['region'] ?? '',
            $input['postalCode'] ?? ''
        );
        
        if ($result) {
            Logger::info("Profile updated", ['userId' => $user['userId']]);
            Response::success(null, "Profile updated successfully");
        } else {
            Response::error("Failed to update profile", SERVER_ERROR);
        }
    }
    
    public static function addWholesaleDetails() {
        $user = authenticateUser();
        $input = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($input['businessName']) || !isset($input['businessLicense'])) {
            Response::error("Business name and license required", BAD_REQUEST);
        }
        
        $profile = new CustomerProfile();
        $result = $profile->addWholesaleDetails($user['userId'], $input['businessName'], $input['businessLicense']);
        
        if ($result) {
            Logger::info("Wholesale details added", ['userId' => $user['userId']]);
            Response::success(null, "Wholesale details added - pending verification");
        } else {
            Response::error("Failed to add wholesale details", SERVER_ERROR);
        }
    }
    
    public static function getWholesaleCustomers() {
        $user = authenticateUser();
        
        if ($user['role'] !== 'warehouse_admin' && $user['role'] !== 'system_admin') {
            Response::error("Insufficient permissions", FORBIDDEN);
        }
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : ITEMS_PER_PAGE;
        
        $profile = new CustomerProfile();
        $customers = $profile->getWholesaleCustomers($page, $limit);
        
        Response::success($customers, "Wholesale customers retrieved");
    }
    
    public static function verifyWholesaleCustomer() {
        $user = authenticateUser();
        
        if ($user['role'] !== 'system_admin') {
            Response::error("Only system admin can verify", FORBIDDEN);
        }
        
        $input = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($input['userId'])) {
            Response::error("User ID required", BAD_REQUEST);
        }
        
        $profile = new CustomerProfile();
        $result = $profile->updateVerificationStatus($input['userId'], 'Verified');
        
        if ($result) {
            Logger::info("Customer verified", ['userId' => $input['userId'], 'verifiedBy' => $user['userId']]);
            Response::success(null, "Customer verified successfully");
        } else {
            Response::error("Failed to verify customer", SERVER_ERROR);
        }
    }
}

?>