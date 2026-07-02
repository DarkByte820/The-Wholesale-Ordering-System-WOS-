<?php
/**
 * Input Validation Utility
 */

class Validator {
    
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    public static function validatePhone($phone) {
        // Ghana phone format: +233XXXXXXXXX or 0XXXXXXXXX
        return preg_match('/^(\+233|0)[0-9]{9}$/', $phone);
    }
    
    public static function validatePassword($password) {
        // Minimum 8 characters, at least one uppercase, one lowercase, one number
        return strlen($password) >= 8 && 
               preg_match('/[A-Z]/', $password) && 
               preg_match('/[a-z]/', $password) && 
               preg_match('/[0-9]/', $password);
    }
    
    public static function validateOrderData($data) {
        $errors = [];
        
        if (empty($data['orderType'])) {
            $errors[] = "Order type is required";
        }
        
        if (empty($data['items']) || !is_array($data['items'])) {
            $errors[] = "Items are required";
        }
        
        if (empty($data['deliveryAddress'])) {
            $errors[] = "Delivery address is required";
        }
        
        if (empty($data['customerPhone'])) {
            $errors[] = "Customer phone is required";
        } elseif (!self::validatePhone($data['customerPhone'])) {
            $errors[] = "Invalid phone number format";
        }
        
        return $errors;
    }
    
    public static function validateProductData($data) {
        $errors = [];
        
        if (empty($data['name'])) {
            $errors[] = "Product name is required";
        }
        
        if (empty($data['sku'])) {
            $errors[] = "SKU is required";
        }
        
        if (empty($data['category'])) {
            $errors[] = "Category is required";
        }
        
        if (empty($data['unitPrice']) || !is_numeric($data['unitPrice']) || $data['unitPrice'] < 0) {
            $errors[] = "Valid unit price is required";
        }
        
        if (empty($data['wholesalePrice']) || !is_numeric($data['wholesalePrice']) || $data['wholesalePrice'] < 0) {
            $errors[] = "Valid wholesale price is required";
        }
        
        return $errors;
    }
    
    public static function validateComboPackageData($data) {
        $errors = [];
        
        if (empty($data['name'])) {
            $errors[] = "Package name is required";
        }
        
        if (empty($data['targetGroup'])) {
            $errors[] = "Target group is required";
        }
        
        if (!isset($data['price']) || $data['price'] < 0) {
            $errors[] = "Valid price is required";
        }
        
        if (empty($data['items']) || !is_array($data['items'])) {
            $errors[] = "Package items are required";
        }
        
        return $errors;
    }
    
    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    
    public static function validateWholesaleOrder($data) {
        $errors = [];
        
        if (empty($data['items'])) {
            $errors[] = "Order items required";
        }
        
        // Items should meet minimum wholesale quantities
        if (is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                if (empty($item['productId']) || empty($item['quantity'])) {
                    $errors[] = "Each item must have product ID and quantity";
                }
                
                if ($item['quantity'] < 5) {
                    $errors[] = "Minimum wholesale quantity is 5 units";
                }
            }
        }
        
        return $errors;
    }
}

?>