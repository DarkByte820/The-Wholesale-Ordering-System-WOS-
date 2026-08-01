<?php
/**
 * Authentication Middleware
 */

function authenticateUser() {
    $headers = getallheaders();
    
    if (!isset($headers['Authorization'])) {
        Response::error("Authorization header missing", UNAUTHORIZED);
    }
    
    $auth = $headers['Authorization'];
    $arr = explode(" ", $auth);
    
    if (count($arr) !== 2 || $arr[0] !== 'Bearer') {
        Response::error("Invalid authorization format", UNAUTHORIZED);
    }
    
    $token = $arr[1];
    $payload = JWT::decode($token);
    
    if (!$payload) {
        Response::error("Invalid or expired token", UNAUTHORIZED);
    }
    
    return $payload;
}

function authorizeRole($required_role) {
    $user = authenticateUser();
    
    if ($user['role'] !== $required_role && $user['role'] !== 'system_admin') {
        Response::error("Insufficient permissions", FORBIDDEN);
    }
    
    return $user;
}

?>