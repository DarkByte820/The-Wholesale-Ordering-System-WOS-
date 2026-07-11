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
}