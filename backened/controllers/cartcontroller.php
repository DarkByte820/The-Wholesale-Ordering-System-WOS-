<?php
/**
 * Cart Controller
 */

class CartController {
    
    public static function getCart() {
        $user = authenticateUser();
        
        $cart = new Cart();
        $items = $cart->getCart($user['userId']);
        $total = $cart->getCartTotal($user['userId']);
        
        Response::success([
            'items' => $items,
            'total' => $total,
            'itemCount' => count($items)
        ], "Cart items retrieved");
    }
    
    public static function addToCart() {
        $user = authenticateUser();
        $input = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($input['productId']) || !isset($input['quantity'])) {
            Response::error("Product ID and quantity required", BAD_REQUEST);
        }
        
        // Validate product exists
        $product = new Product();
        $prod = $product->getProductById($input['productId']);
        if (!$prod) {
            Response::error("Product not found", NOT_FOUND);
        }
        
        $cart = new Cart();
        $result = $cart->addToCart($user['userId'], $input['productId'], $input['quantity']);
        
        if ($result) {
            Response::success(null, "Item added to cart", CREATED);
        } else {
            Response::error("Failed to add item to cart", SERVER_ERROR);
        }
    }
    
    public static function updateCartItem() {
        $user = authenticateUser();
        $input = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($input['cartId']) || !isset($input['quantity'])) {
            Response::error("Cart ID and quantity required", BAD_REQUEST);
        }
        
        $cart = new Cart();
        $result = $cart->updateQuantity($input['cartId'], $input['quantity']);
        
        if ($result) {
            Response::success(null, "Cart updated");
        } else {
            Response::error("Failed to update cart", SERVER_ERROR);
        }
    }
    
    public static function removeFromCart() {
        $user = authenticateUser();
        $input = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($input['cartId'])) {
            Response::error("Cart ID required", BAD_REQUEST);
        }
        
        $cart = new Cart();
        $result = $cart->removeFromCart($input['cartId']);
        
        if ($result) {
            Response::success(null, "Item removed from cart");
        } else {
            Response::error("Failed to remove item", SERVER_ERROR);
        }
    }
    
    public static function clearCart() {
        $user = authenticateUser();
        
        $cart = new Cart();
        $result = $cart->clearCart($user['userId']);
        
        if ($result) {
            Response::success(null, "Cart cleared");
        } else {
            Response::error("Failed to clear cart", SERVER_ERROR);
        }
    }
}

?>