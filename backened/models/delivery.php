<?php
/**
 * Delivery Model
 */

class Delivery {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }
    
    public function createDelivery($order_id, $delivery_address, $delivery_city, $customer_contact) {
        $status = 'Assigned';
        
        $stmt = $this->db->prepare("INSERT INTO deliveries (OrderID, DeliveryAddress, City, CustomerContact, Status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $order_id, $delivery_address, $delivery_city, $customer_contact, $status);
        
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        return false;
    }
    
    public function assignDelivery($delivery_id, $personnel_id) {
        $stmt = $this->db->prepare("UPDATE delivery SET AssignedPersonID = ?, AssignedDate = NOW(), Status = 'Assigned' WHERE DeliveryID = ?");
        $stmt->bind_param("i    i", $personnel_id, $delivery_id);
        return $stmt->execute();
    }
    
    public function getDeliveryByOrder($order_id) {
        $stmt = $this->db->prepare("SELECT d.*, u.Name as PersonName, u.Phone FROM delivery d LEFT JOIN user u ON d.AssignedPersonID = u.UserID WHERE d.OrderID = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function updateDeliveryStatus($delivery_id, $status) {
        $stmt = $this->db->prepare("UPDATE delivery SET Status = ? WHERE DeliveryID = ?");
        $stmt->bind_param("si", $status, $delivery_id);
        return $stmt->execute();
    }
    
    public function recordProofOfDelivery($delivery_id, $receiver_name, $notes, $latitude = null, $longitude = null) {
        $stmt = $this->db->prepare("UPDATE delivery SET ReceiverName = ?, DeliveryNotes = ?, Latitude = ?, Longitude = ?, DeliveryTimestamp = NOW(), Status = 'Delivered' WHERE DeliveryID = ?");
        $stmt->bind_param("ssddi", $receiver_name, $notes, $latitude, $longitude, $delivery_id);
        return $stmt->execute();
    }
    
    public function getDeliveriesByPersonnel($personnel_id, $status = null) {
        if ($status) {
            $stmt = $this->db->prepare("SELECT d.*, o.TotalAmount FROM delivery d JOIN `order` o ON d.OrderID = o.OrderID WHERE d.AssignedPersonID = ? AND d.Status = ?");
            $stmt->bind_param("is", $personnel_id, $status);
        } else {
            $stmt = $this->db->prepare("SELECT d.*, o.TotalAmount FROM delivery d JOIN `order` o ON d.OrderID = o.OrderID WHERE d.AssignedPersonID = ?");
            $stmt->bind_param("i", $personnel_id);
        }
        
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

?>