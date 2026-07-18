<?php
/**
 * Payment Controller
 */

class PaymentController {
    
   public static function initiatePayment()
{
    authenticateUser();

    $input = json_decode(file_get_contents("php://input"), true);

    if (!$input) {
        Response::error("Invalid JSON data", BAD_REQUEST);
    }

    if (
        empty($input['order_Id']) ||
        empty($input['provider']) ||
        empty($input['amount'])
    ) {
        Response::error("orderId, provider and amount are required", BAD_REQUEST);
    }

    $payment = new Payment();

    $result = $payment->createPayment(
        (int)$input['order_Id'],
        trim($input['provider']),
        (float)$input['amount'],
        $input['payment_Method'] ?? 'Mobile Money'
    );

    if (!$result) {
        Response::error("Unable to initiate payment", SERVER_ERROR);
    }

    Response::success(
        [
            "paymentId" => $result["payment_Id"],
            "transactionReference" => $result["reference"],
            "status" => "Initiated"
        ],
        "Payment initiated successfully",
        CREATED
    );
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

    public static function checkStatus() {
        $input = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($input['transactionReference']) || !isset($input['orderId'])) {
            Response::error("Transaction reference and order ID required", BAD_REQUEST);
        }
        
        $payment = new Payment();
        $status = $payment->checkPaymentStatus($input['orderId']);
        
        if ($status) {
            Response::success(['status' => $status], "Payment status retrieved");
        } else {
            Response::error("Failed to retrieve payment status", SERVER_ERROR);
        }
    }
}



?>