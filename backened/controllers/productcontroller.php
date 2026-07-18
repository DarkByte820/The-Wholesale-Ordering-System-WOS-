<?php
/**
 * Product Controller
 */

class ProductController
{
    public static function getAllProducts()
    {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : ITEMS_PER_PAGE;
        $category = isset($_GET['category']) ? $_GET['category'] : null;

        $product = new Product();
        $products = $product->getAllProducts($page, $limit, $category);

        Response::success($products, "Products retrieved successfully");
    }

    public static function getProductById()
    {
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            Response::error("Invalid or missing Product ID", BAD_REQUEST);
            return;
        }

        $productId = (int) $_GET['id'];

        $product = new Product();
        $productData = $product->getProductById($productId);

        if (!$productData) {
            Response::error("Product not found", NOT_FOUND);
            return;
        }

        Response::success($productData, "Product retrieved successfully");
    }

    public static function search()
    {
        $searchTerm = isset($_GET['q']) ? trim($_GET['q']) : null;

        if (!$searchTerm || strlen($searchTerm) < 2) {
            Response::error("Search term too short", BAD_REQUEST);
            return;
        }

        $product = new Product();
        $results = $product->searchProducts($searchTerm);

        Response::success($results, "Search results");
    }

    public static function getByCategory()
    {
        $category = isset($_GET['category']) ? $_GET['category'] : null;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : ITEMS_PER_PAGE;

        if (!$category) {
            Response::error("Category required", BAD_REQUEST);
            return;
        }

        $product = new Product();
        $results = $product->getProductsByCategory($category, $page, $limit);

        Response::success($results, "Products in category retrieved");
    }

    public static function createProduct()
    {
        $user = authenticateUser();

        if ($user['role'] !== 'warehouse_admin' && $user['role'] !== 'system_admin') {
            Response::error("Insufficient permissions", FORBIDDEN);
        }

        $input = json_decode(file_get_contents("php://input"), true);

        $requiredFields = ['sku', 'name', 'description', 'category_id', 'unitPrice', 'wholesalePrice', 'unit'];
        foreach ($requiredFields as $field) {
            if (!isset($input[$field])) {
                Response::error("Missing required field: $field", BAD_REQUEST);
                return;
            }
        }

        $product = new Product();
        $productId = $product->createProduct(
            $input['sku'],
            $input['name'],
            $input['description'],
            $input['category_id'],
            (float)$input['unit_Price'],
            (float)$input['wholesale_Price'],
            $input['unit']
        );

        if ($productId) {
            Response::success(['productId' => $productId], "Product created successfully");
        } else {
            Response::error("Failed to create product", SERVER_ERROR);
        }
    }

    public static function updateProduct()
    {
        $user = authenticateUser();

        if ($user['role'] !== 'warehouse_admin' && $user['role'] !== 'system_admin') {
            Response::error("Insufficient permissions", FORBIDDEN);
        }

        $input = json_decode(file_get_contents("php://input"), true);

        if (!isset($input['product_Id']) || !is_numeric($input['product_Id'])) {
            Response::error("Invalid or missing Product ID", BAD_REQUEST);
            return;
        }

        $productId = (int)$input['product_Id'];

        $product = new Product();
        $result = $product->updateProduct($productId, $input['name'], (float)$input['unit_Price'], (float)$input['wholesale_Price'], $input['status']);

        if ($result) {
            Response::success(null, "Product updated successfully");
        } else {
            Response::error("Failed to update product", SERVER_ERROR);
        }
    }
    public static function deleteProduct()
    {
        $user = authenticateUser();

        if ($user['role'] !== 'warehouse_admin' && $user['role'] !== 'system_admin') {
            Response::error("Insufficient permissions", FORBIDDEN);
        }

        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            Response::error("Invalid or missing Product ID", BAD_REQUEST);
            return;
        }

        $productId = (int)$_GET['id'];

        $product = new Product();
        $result = $product->deleteProduct($productId);

        if ($result) {
            Response::success(null, "Product deleted successfully");
        } else {
            Response::error("Failed to delete product", SERVER_ERROR);
        }
    }
    public static function getProductsByCategory()
    {
        $category = isset($_GET['category']) ? $_GET['category'] : null;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : ITEMS_PER_PAGE;

        if (!$category) {
            Response::error("Category required", BAD_REQUEST);
            return;
        }

        $product = new Product();
        $results = $product->getProductsByCategory($category, $page, $limit);

        Response::success($results, "Products in category retrieved");
    }
    public static function getProductByIdForUpdate()
    {
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            Response::error("Invalid or missing Product ID", BAD_REQUEST);
            return;
        }

        $productId = (int)$_GET['id'];

        $product = new Product();
        $productData = $product->getProductById($productId);

        if (!$productData) {
            Response::error("Product not found", NOT_FOUND);
            return;
        }

        Response::success($productData, "Product retrieved successfully for update");
    }
    public static function searchProducts()
    {
        $input = json_decode(file_get_contents("php://input"), true);

        $searchTerm = isset($input['q']) ? trim($input['q']) : null;

        // if (!$searchTerm || strlen($searchTerm) < 2) {
        //     Response::error("Search term too short", BAD_REQUEST);
        //     return;
        // }

        $product = new Product();
        $results = $product->searchProducts($searchTerm);

        Response::success($results, "Search results");
    }


}