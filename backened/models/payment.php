<?php
/**
 * Payment Model
 */

class Payment {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }
    
    public function createPayment($order_id, $provider, $amount, $payment_method) {
        $status = 'Initiated';
        $transaction_ref = 'TXN-' . date('YmdHis') . '-' . rand(1000, 9999);
        
        $stmt = $this->db->prepare("INSERT INTO payments (Order_ID, Provider, Amount, Status, PaymentMethod, TransactionReference) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isdsss", $order_id, $provider, $amount, $status, $payment_method, $transaction_ref);
        
        if ($stmt->execute()) {
            return ['paymentId' => $this->db->insert_id, 'transactionReference' => $transaction_ref];
        }
        return false;
    }
    
    public function getPayment($payment_id) {
        $stmt = $this->db->prepare("SELECT * FROM payments WHERE PaymentID = ?");
        $stmt->bind_param("i", $payment_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function getPaymentByOrder($order_id) {
        $stmt = $this->db->prepare("SELECT * FROM payments WHERE OrderID = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function updatePaymentStatus($payment_id, $status) {
        $stmt = $this->db->prepare("UPDATE payments SET Status = ?, VerificationDate = NOW() WHERE PaymentID = ?");
        $stmt->bind_param("si", $status, $payment_id);
        return $stmt->execute();
    }
    
    public function verifyPayment($transaction_ref) {
        $status = 'Successful';
        $stmt = $this->db->prepare("UPDATE payments SET Status = ?, VerificationDate = NOW() WHERE TransactionReference = ?");
        $stmt->bind_param("ss", $status, $transaction_ref);
        
        if ($stmt->execute()) {
            // Update order payment status
            $payment = $this->getPaymentByRef($transaction_ref);
            if ($payment) {
                $order = new Order();
                $order->updatePaymentStatus($payment['OrderID'], 'Paid');
            }
            return true;
        }
        return false;
    }
    
    private function getPaymentByRef($transaction_ref) {
        $stmt = $this->db->prepare("SELECT * FROM payments WHERE TransactionReference = ?");
        $stmt->bind_param("s", $transaction_ref);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function checkPaymentStatus($orderId) {
        $stmt = $this->db->prepare("SELECT Status FROM payments WHERE Order_ID = ?");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result ? $result['Status'] : null;
    }
}

?>