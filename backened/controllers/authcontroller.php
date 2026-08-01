<?php
/**
 * Authentication Controller (FIXED VERSION)
 */

class AuthController {

    public static function register() {

        $input = json_decode(file_get_contents("php://input"), true);

        if (
            empty($input['name']) ||
            empty($input['email']) ||
            empty($input['phone']) ||
            empty($input['password']) ||
            empty($input['role'])
        ) {
            Response::error("Missing required fields", BAD_REQUEST);
        }

        if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            Response::error("Invalid email format", BAD_REQUEST);
        }

        $user = new User();

        if ($user->emailExists($input['email'])) {
            Response::error("Email already exists", BAD_REQUEST);
        }

        $user_id = $user->register(
            $input['name'],
            $input['email'],
            $input['phone'],
            $input['password'],
            $input['role']
        );

        if (!$user_id) {
            Response::error("Registration failed", SERVER_ERROR);
        }

        $user_data = $user->getUserById($user_id);

        $token = JWT::encode([
            'userId' => $user_id,
            'email'  => $input['email'],
            'role'   => $input['role']
        ]);

        Logger::info("User registered", ['email' => $input['email']]);

        Response::success([
            'userId' => $user_id,
            'user'   => $user_data,
            'token'  => $token
        ], "User registered successfully", CREATED);
    }


    public static function login() {

        $input = json_decode(file_get_contents("php://input"), true);

        if (empty($input['email']) || empty($input['password'])) {
            Response::error("Email and password required", BAD_REQUEST);
        }

        $user = new User();
        $user_data = $user->login($input['email'], $input['password']);

        if (!$user_data) {
            Logger::warning("Failed login attempt", ['email' => $input['email']]);
            Response::error("Invalid email or password", UNAUTHORIZED);
        }

        // SAFE KEY HANDLING (fixes your undefined index errors)
        $userId = $user_data['User_ID'] ?? null;
        $email  = $user_data['Email'] ?? $input['email'];
        $role   = $user_data['Role'] ?? null;

        $token = JWT::encode([
            'userId' => $userId,
            'email'  => $email,
            'role'   => $role
        ]);

        Logger::info("User logged in", ['email' => $input['email']]);

        Response::success([
            'userId' => $userId,
            'user'   => $user_data,
            'token'  => $token
        ], "Login successful");
    }

    public static function forgotPassword() {
        $input = json_decode(file_get_contents("php://input"), true);

        if (empty($input['email'])) {
            Response::error("Email is required", BAD_REQUEST);
        }

        $user = new User();
        $user_data = $user->getUserByEmail($input['email']);

        // Always return success to prevent email enumeration
        if (!$user_data) {
            Response::success([], "If the email exists, a reset link has been sent");
            return;
        }

        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $user->createResetToken($user_data['User_ID'], $token, $expiry);

        Logger::info("Password reset requested", ['email' => $input['email']]);

        Response::success([
            'token' => $token,
            'message' => 'Reset link sent to your email'
        ], "If the email exists, a reset link has been sent");
    }

    public static function resetPassword() {
        $input = json_decode(file_get_contents("php://input"), true);

        if (empty($input['token']) || empty($input['password'])) {
            Response::error("Token and new password are required", BAD_REQUEST);
        }

        if (strlen($input['password']) < 6) {
            Response::error("Password must be at least 6 characters", BAD_REQUEST);
        }

        $user = new User();
        $reset_data = $user->getResetToken($input['token']);

        if (!$reset_data) {
            Response::error("Invalid or expired reset token", BAD_REQUEST);
        }

        $success = $user->updatePassword($reset_data['User_ID'], $input['password']);

        if (!$success) {
            Response::error("Failed to reset password", SERVER_ERROR);
        }

        $user->deleteResetToken($input['token']);

        Logger::info("Password reset completed", ['user_id' => $reset_data['User_ID']]);

        Response::success([], "Password reset successfully");
    }

    public static function changePassword() {
        $input = json_decode(file_get_contents("php://input"), true);

        if (empty($input['currentPassword']) || empty($input['newPassword'])) {
            Response::error("Current and new password are required", BAD_REQUEST);
        }

        if (strlen($input['newPassword']) < 6) {
            Response::error("New password must be at least 6 characters", BAD_REQUEST);
        }

        // Get user from token
        $headers = getallheaders();
        $auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        $token = str_replace('Bearer ', '', $auth);

        if (!$token) {
            Response::error("Authentication required", UNAUTHORIZED);
        }

        $decoded = JWT::decode($token);
        if (!$decoded || !isset($decoded['userId'])) {
            Response::error("Invalid token", UNAUTHORIZED);
        }

        $user = new User();
        $user_data = $user->login(
            $decoded['email'],
            $input['currentPassword']
        );

        if (!$user_data) {
            Response::error("Current password is incorrect", UNAUTHORIZED);
        }

        $success = $user->updatePassword($decoded['userId'], $input['newPassword']);

        if (!$success) {
            Response::error("Failed to update password", SERVER_ERROR);
        }

        Logger::info("Password changed", ['user_id' => $decoded['userId']]);

        Response::success([], "Password changed successfully");
    }
}