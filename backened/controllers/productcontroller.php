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
}