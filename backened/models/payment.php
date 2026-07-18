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
    $reference = 'TXN-' . date('YmdHis') . '-' . rand(1000, 9999);

    $sql = "INSERT INTO payments
            (order_id, Provider, Amount, Status, Payment_Method, reference)
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $this->db->prepare($sql);

    if (!$stmt) {
        die("Prepare failed: " . $this->db->error);
    }

    $stmt->bind_param(
        "isdsss",
        $order_id,
        $provider,
        $amount,
        $status,
        $payment_method,
        $reference
    );

    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }

    return [
        'payment_Id' => $this->db->insert_id,
        'reference' => $reference
    ];
}
    
    
    public function getPayment($payment_id) {
        $stmt = $this->db->prepare("SELECT * FROM payments WHERE Payment_id = ?");
        $stmt->bind_param("i", $payment_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function getPaymentByOrder($order_id) {
        $stmt = $this->db->prepare("SELECT * FROM payments WHERE Order_id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function updatePaymentStatus($payment_id, $status) {
        $stmt = $this->db->prepare("UPDATE payments SET Status = ?, verified_at = NOW() WHERE Payment_id = ?");
        $stmt->bind_param("si", $status, $payment_id);
        return $stmt->execute();
    }
    
    public function verifyPayment($transaction_ref) {
        $status = 'Successful';
        $stmt = $this->db->prepare("UPDATE payments SET Status = ?, verified_at = NOW() WHERE reference = ?");
        $stmt->bind_param("ss", $status, $transaction_ref);
        
        if ($stmt->execute()) {
            // Update order payment status
        $stmt->bind_param("ss", $status, $transaction_ref);
            $payment = $this->getPaymentByRef($transaction_ref);
            if ($payment) {
                $order = new Order();
                $order->updatePaymentStatus($payment['Order_id'], 'Paid');
            }
            return true;
        }
        return false;
    }
    
    private function getPaymentByRef($reference) {
        $stmt = $this->db->prepare("SELECT * FROM payments WHERE reference = ?");
        $stmt->bind_param("s", $reference);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function checkPaymentStatus($order_Id) {
        $stmt = $this->db->prepare("SELECT Status FROM payments WHERE Order_id = ?");
        $stmt->bind_param("i", $order_Id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result ? $result['Status'] : null;
    }
    public  static function paymentMethods() {
        return ['Mobile Money', 'Credit Card', 'Bank Transfer'];
    }
}

?>