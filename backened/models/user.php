<?php
/**
 * User Model
 */

class User {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }
    
    public function register($name, $email, $phone, $password, $role) {
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        
        $stmt = $this->db->prepare("INSERT INTO users (Name, Email, Phone, Password_Hash, Role, Status) VALUES (?, ?, ?, ?, ?, 'Active')");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("sssss", $name, $email, $phone, $password_hash, $role);
        
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        
        error_log("Execute failed: " . $stmt->error);
        return false;
    }
    
    public function login($email, $password) {
        $stmt = $this->db->prepare("SELECT User_ID, Name, Email, Password_Hash, Role FROM users WHERE Email = ? AND Status = 'Active'");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return false;
        }
        
        $user = $result->fetch_assoc();
        
        if (!password_verify($password, $user['Password_Hash'])) {
            return false;
        }
        
        unset($user['Password_Hash']);
        return $user;
    }
    
    public function getUserById($user_id) {
        $stmt = $this->db->prepare("SELECT User_ID, Name, Email, Phone, Role, Status FROM users WHERE User_ID = ?");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function emailExists($email) {
        $stmt = $this->db->prepare("SELECT User_ID FROM users WHERE Email = ?");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public function getUserByEmail($email) {
        $stmt = $this->db->prepare("SELECT User_ID, Name, Email FROM users WHERE Email = ? AND Status = 'Active'");
        if (!$stmt) return false;
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function updatePassword($user_id, $password) {
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("UPDATE users SET Password_Hash = ? WHERE User_ID = ?");
        if (!$stmt) return false;
        $stmt->bind_param("si", $password_hash, $user_id);
        return $stmt->execute();
    }

    public function createResetToken($user_id, $token, $expiry) {
        $stmt = $this->db->prepare("INSERT INTO password_resets (User_ID, Token, Expires_At) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE Token = ?, Expires_At = ?");
        if (!$stmt) return false;
        $stmt->bind_param("isssi", $user_id, $token, $expiry, $token, $expiry);
        return $stmt->execute();
    }

    public function getResetToken($token) {
        $stmt = $this->db->prepare("SELECT User_ID, Expires_At FROM password_resets WHERE Token = ? AND Expires_At > NOW()");
        if (!$stmt) return false;
        $stmt->bind_param("s", $token);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function deleteResetToken($token) {
        $stmt = $this->db->prepare("DELETE FROM password_resets WHERE Token = ?");
        if (!$stmt) return false;
        $stmt->bind_param("s", $token);
        return $stmt->execute();
    }
}

?>