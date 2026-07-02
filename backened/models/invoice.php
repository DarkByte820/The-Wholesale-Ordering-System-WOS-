<?php
/**
 * Invoice Model
 * Handles Invoice Generation & Tracking
 */

class Invoice {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }
    
    public function createInvoice($order_id, $amount, $tax_amount = 0, $discount = 0) {
        $invoice_number = 'INV-' . date('YmdHis') . '-' . rand(1000, 9999);
        $net_amount = $amount + $tax_amount - $discount;
        $status = 'Issued';
        $issue_date = date('Y-m-d');
        
        $stmt = $this->db->prepare("INSERT INTO invoice (OrderID, InvoiceNumber, Amount, TaxAmount, Discount, NetAmount, IssueDate, Status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("isdddsss", $order_id, $invoice_number, $amount, $tax_amount, $discount, $net_amount, $issue_date, $status);
        
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        
        error_log("Execute failed: " . $stmt->error);
        return false;
    }
    
    public function getInvoiceByOrderId($order_id) {
        $stmt = $this->db->prepare("SELECT * FROM invoice WHERE OrderID = ?");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function getInvoiceById($invoice_id) {
        $stmt = $this->db->prepare("SELECT * FROM invoice WHERE InvoiceID = ?");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("i", $invoice_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function getInvoiceByNumber($invoice_number) {
        $stmt = $this->db->prepare("SELECT * FROM invoice WHERE InvoiceNumber = ?");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("s", $invoice_number);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function updateInvoiceStatus($invoice_id, $status) {
        $stmt = $this->db->prepare("UPDATE invoice SET Status = ?, DateModified = NOW() WHERE InvoiceID = ?");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("si", $status, $invoice_id);
        return $stmt->execute();
    }
    
    public function getInvoicesByUser($user_id, $page = 1, $limit = ITEMS_PER_PAGE) {
        $offset = ($page - 1) * $limit;
        
        $stmt = $this->db->prepare("SELECT i.* FROM invoice i JOIN `order` o ON i.OrderID = o.OrderID WHERE o.UserID = ? ORDER BY i.IssueDate DESC LIMIT ? OFFSET ?");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return [];
        }
        
        $stmt->bind_param("iii", $user_id, $limit, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getInvoicesByDateRange($start_date, $end_date) {
        $stmt = $this->db->prepare("SELECT * FROM invoice WHERE IssueDate BETWEEN ? AND ? ORDER BY IssueDate DESC");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return [];
        }
        
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

?>