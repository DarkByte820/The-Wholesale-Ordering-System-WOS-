<?php
/**
 * Product Controller
 */

class ProductController {
    
    public static function getAllProducts() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : ITEMS_PER_PAGE;
        $category = isset($_GET['category']) ? $_GET['category'] : null;
        
        $product = new Product();
        $products = $product->getAllProducts($page, $limit, $category);
        
        Response::success($products, "Products retrieved successfully");
    }
    
    public static function getProductById() {
        $product_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        
        if (!$product_id) {
            Response::error("Product ID required", BAD_REQUEST);
        }
        
        $product = new Product();
        $product_data = $product->getProductById($product_id);
        
        if (!$product_data) {
            Response::error("Product not found", NOT_FOUND);
        }
        
        Response::success($product_data, "Product retrieved successfully");
    }
    
    public static function search() {
        $search_term = isset($_GET['q']) ? $_GET['q'] : null;
        
        if (!$search_term || strlen($search_term) < 2) {
            Response::error("Search term too short", BAD_REQUEST);
        }
        
        $product = new Product();
        $results = $product->searchProducts($search_term);
        
        Response::success($results, "Search results");
    }
    
    public static function getByCategory() {
        $category = isset($_GET['category']) ? $_GET['category'] : null;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : ITEMS_PER_PAGE;
        
        if (!$category) {
            Response::error("Category required", BAD_REQUEST);
        }
        
        $product = new Product();
        $results = $product->getProductsByCategory($category, $page, $limit);
        
        Response::success($results, "Products in category retrieved");
    }
}

?>