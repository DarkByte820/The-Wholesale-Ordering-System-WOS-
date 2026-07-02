<?php
/**
 * Combo Package Controller
 * Community Access Module
 */

class ComboPackageController {
    
    public static function getAllPackages() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : ITEMS_PER_PAGE;
        
        $combo = new ComboPackage();
        $packages = $combo->getAllPackages($page, $limit);
        
        Response::success($packages, "Packages retrieved successfully");
    }
    
    public static function getPackageById() {
        $package_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        
        if (!$package_id) {
            Response::error("Package ID required", BAD_REQUEST);
        }
        
        $combo = new ComboPackage();
        $package = $combo->getPackageById($package_id);
        
        if (!$package) {
            Response::error("Package not found", NOT_FOUND);
        }
        
        Response::success($package, "Package retrieved successfully");
    }
    
    public static function getByTargetGroup() {
        $target_group = isset($_GET['targetGroup']) ? $_GET['targetGroup'] : null;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : ITEMS_PER_PAGE;
        
        if (!$target_group) {
            Response::error("Target group required", BAD_REQUEST);
        }
        
        $combo = new ComboPackage();
        $packages = $combo->getPackagesByTargetGroup($target_group, $page, $limit);
        
        Response::success($packages, "Packages retrieved successfully");
    }
    
    public static function createPackage() {
        $user = authenticateUser();
        
        // Only warehouse admin can create packages
        if ($user['role'] !== 'warehouse_admin' && $user['role'] !== 'SystemAdmin') {
            Response::error("Insufficient permissions", FORBIDDEN);
        }
        
        $input = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($input['name']) || !isset($input['targetGroup']) || !isset($input['price']) || !isset($input['items'])) {
            Response::error("Missing required fields", BAD_REQUEST);
        }
        
        $combo = new ComboPackage();
        
        $package_id = $combo->createPackage(
            $input['name'],
            $input['description'] ?? '',
            $input['targetGroup'],
            $input['price'],
            $input['startDate'] ?? date('Y-m-d'),
            $input['endDate'] ?? null,
            $input['stockLimit'] ?? 1000
        );
        
        if (!$package_id) {
            Response::error("Failed to create package", SERVER_ERROR);
        }
        
        // Add items to package
        foreach ($input['items'] as $item) {
            if (isset($item['productId']) && isset($item['quantity'])) {
                $combo->addPackageItem($package_id, $item['productId'], $item['quantity']);
            }
        }
        
        Logger::info("Combo package created", ['packageId' => $package_id, 'userId' => $user['userId']]);
        
        Response::success(['packageId' => $package_id], "Package created successfully", CREATED);
    }
    
    public static function publishPackage() {
        $user = authenticateUser();
        
        if ($user['role'] !== 'warehouse_admin' && $user['role'] !== 'SystemAdmin') {
            Response::error("Insufficient permissions", FORBIDDEN);
        }
        
        $input = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($input['packageId'])) {
            Response::error("Package ID required", BAD_REQUEST);
        }
        
        $combo = new ComboPackage();
        $result = $combo->publishPackage($input['packageId']);
        
        if ($result) {
            Logger::info("Package published", ['packageId' => $input['packageId']]);
            Response::success(null, "Package published successfully");
        } else {
            Response::error("Cannot publish package - check product stock", SERVER_ERROR);
        }
    }
}

?>