 <?php
/**
 * Authentication Middleware
 */

function authenticateUser()
{
    // Get Authorization header
    $headers = getallheaders();

    if (!isset($headers['Authorization'])) {
        Response::error("Authorization token missing", UNAUTHORIZED);
    }

    $authHeader = $headers['Authorization'];

    // Expected format:
    // Authorization: Bearer TOKEN
    if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        Response::error("Invalid authorization format", UNAUTHORIZED);
    }

    $token = $matches[1];

    // Decode JWT
    $payload = JWT::decode($token);

    if (!$payload) {
        Response::error("Invalid or expired token", UNAUTHORIZED);
    }

    // Check required JWT data
    if (!isset($payload['userId'])) {
        Response::error("Invalid user token", UNAUTHORIZED);
    }

    return [
        'userId' => $payload['userId'],
        'email'  => $payload['email'] ?? null,
        'role'   => $payload['role'] ?? null
    ];
}

?>