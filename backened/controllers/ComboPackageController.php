<?php
/**
 * Combo Package Controller
 * Community Access Module
 */

class ComboPackageController {

    public static function getAllPackages() {

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : ITEMS_PER_PAGE;

        $combo = new ComboPackages(); 

        $packages = $combo->getAllPackages($page, $limit);

        Response::success($packages, "Packages retrieved successfully");
    }

    public static function getPackageById() {

        $package_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($package_id <= 0) {
            Response::error("Package ID required", BAD_REQUEST);
        }

        $combo = new ComboPackages();
        $package = $combo->getPackageById($package_id);

        if (!$package) {
            Response::error("Package not found", NOT_FOUND);
        }

        Response::success($package, "Package retrieved successfully");
    }

    public static function getByTargetGroup() {
        $target_group = 'done';
        
        echo "Target group 1: $target_group\n"; // Debugging line

        // $target_group = isset($_GET['target_group']) ? $_GET['target_group'] : null;
        $target_group = $_GET['target_group'];

        echo "Target group 2: $target_group\n"; // Debugging line
        if (!$target_group) {
            Response::error("target_group required", BAD_REQUEST);
        }

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : ITEMS_PER_PAGE;

        $combo = new ComboPackages();
        $packages = $combo->getPackagesByTargetGroup($target_group, $page, $limit);

        Response::success($packages, "Packages retrieved successfully");
    }

    public static function createPackage() {

        $user = authenticateUser();

        if (!in_array($user['role'], ['warehouse_admin', 'system_admin'])) {
            Response::error("Insufficient permissions", FORBIDDEN);
        }

        $input = json_decode(file_get_contents("php://input"), true);

        if (!$input) {
            Response::error("Invalid JSON input", BAD_REQUEST);
        }

        if (
            empty($input['name']) ||
            empty($input['target_group']) ||
            empty($input['price']) ||
            empty($input['items'])
        ) {
            Response::error("Missing required fields", BAD_REQUEST);
        }

        $combo = new ComboPackages();

        $package_id = $combo->createPackage(
        
            $input['name'],
            $input['description'] ?? '',
            $input['target_group'],
            $input['price'],
            $input['available_start'] ?? date('Y-m-d'),
            $input['available_end'] ?? null,
            $input['stock_limit'] ?? 1000
        );

        if (!$package_id) {
            Response::error("Failed to create package", SERVER_ERROR);
        }

        foreach ($input['items'] as $item) {
            if (!empty($item['productId']) && !empty($item['quantity'])) {
                $combo->addPackageItem(
                    $package_id,
                    $item['productId'],
                    $item['quantity']
                );
            }
        }

        Logger::info("Combo package created", [
            'packageId' => $package_id,
            'userId' => $user['userId'] ?? null
        ]);

        Response::success(['packageId' => $package_id], "Package created successfully", CREATED);
    }

    public static function publishPackage() {

        $user = authenticateUser();

        if (!in_array($user['role'], ['warehouse_admin', 'system_admin'])) {
            Response::error("Insufficient permissions", FORBIDDEN);
        }

        $input = json_decode(file_get_contents("php://input"), true);

        if (empty($input['packageId'])) {
            Response::error("Package ID required", BAD_REQUEST);
        }

        $combo = new ComboPackages();
        $result = $combo->publishPackage($input['packageId']);

        if ($result) {
            Logger::info("Package published", ['packageId' => $input['packageId']]);
            Response::success(null, "Package published successfully");
        } else {
            Response::error("Cannot publish package - check product stock", SERVER_ERROR);
        }
    }
}