<?php
/**
 * Payment Controller
 */

class PaymentController {
    
    public static function initiatePayment() {
        $user = authenticateUser();
        $input = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($input['orderId']) || !isset($input['provider']) || !isset($input['amount'])) {
            Response::error("Missing required fields", BAD_REQUEST);
        }
        
        $payment = new Payment();
        $result = $payment->createPayment($input['orderId'], $input['provider'], $input['amount'], $input['paymentMethod'] ?? 'Other');
        
        if ($result) {
            Logger::info("Payment initiated", ['orderId' => $input['orderId'], 'provider' => $input['provider']]);
            Response::success($result, "Payment initiated", CREATED);
        } else {
            Response::error("Failed to initiate payment", SERVER_ERROR);
        }
    }
    
    public static function verifyPayment() {
        $input = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($input['transactionReference'])) {
            Response::error("Transaction reference required", BAD_REQUEST);
        }
        
        $payment = new Payment();
        $result = $payment->verifyPayment($input['transactionReference']);
        
        if ($result) {
            Logger::info("Payment verified", ['reference' => $input['transactionReference']]);
            Response::success(null, "Payment verified successfully");
        } else {
            Response::error("Failed to verify payment", SERVER_ERROR);
        }
    }
    
    public static function getPaymentStatus() {
        $payment_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        
        if (!$payment_id) {
            Response::error("Payment ID required", BAD_REQUEST);
        }
        
        $payment = new Payment();
        $data = $payment->getPayment($payment_id);
        
        if ($data) {
            Response::success($data, "Payment retrieved");
        } else {
            Response::error("Payment not found", NOT_FOUND);
        }
    }
}

?>