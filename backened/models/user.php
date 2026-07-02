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
}

?>