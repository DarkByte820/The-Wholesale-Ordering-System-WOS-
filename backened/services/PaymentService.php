<?php
/**
 * Payment Service - FR11
 * FR11: Payment Processing - initiate payment through supported channels
 * Order payment status changes to paid only after successful verification
 */

class PaymentService {
    private $db;
    private $payment;
    private $order;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->payment = new Payment();
        $this->order = new Order();
    }
    
    /**
     * Initiate payment through different providers
     * Supported providers: MobileMoney, Card, BankTransfer, Manual
     */
    public function initiatePayment($orderId, $provider, $amount, $paymentMethod) {
        // Validate order exists
        $order = $this->order->getOrderById($orderId);
        if (!$order) {
            return ['success' => false, 'message' => 'Order not found'];
        }
        
        // Check if payment already exists
        $existingPayment = $this->payment->getPaymentByOrder($orderId);
        if ($existingPayment && $existingPayment['Status'] === 'Successful') {
            return ['success' => false, 'message' => 'Payment already completed for this order'];
        }
        
        // Create payment record
        $paymentResult = $this->payment->createPayment($orderId, $provider, $amount, $paymentMethod);
        
        if (!$paymentResult) {
            return ['success' => false, 'message' => 'Failed to initiate payment'];
        }
        
        // Route to appropriate payment gateway
        switch ($provider) {
            case 'MobileMoney':
                return $this->processMobileMoneyPayment($orderId, $amount, $paymentResult['transactionReference']);
            case 'Card':
                return $this->processCardPayment($orderId, $amount, $paymentResult['transactionReference']);
            case 'BankTransfer':
                return $this->processBankTransfer($orderId, $amount, $paymentResult['transactionReference']);
            case 'Manual':
                return $this->processManualPayment($orderId, $amount, $paymentResult['transactionReference']);
            default:
                return ['success' => false, 'message' => 'Invalid payment provider'];
        }
    }
    
    /**
     * Process Mobile Money Payment
     */
    private function processMobileMoneyPayment($orderId, $amount, $transactionRef) {
        // TODO: Integrate with Mobile Money API (MTN, Vodafone, etc.)
        // For now, return pending status
        
        Logger::info("Mobile Money payment initiated", [
            'orderId' => $orderId,
            'amount' => $amount,
            'reference' => $transactionRef
        ]);
        
        return [
            'success' => true,
            'provider' => 'MobileMoney',
            'message' => 'Payment request sent. Please complete payment on your phone.',
            'transactionReference' => $transactionRef,
            'status' => 'Pending'
        ];
    }
    
    /**
     * Process Card Payment
     */
    private function processCardPayment($orderId, $amount, $transactionRef) {
        // TODO: Integrate with Stripe or PayPal
        
        Logger::info("Card payment initiated", [
            'orderId' => $orderId,
            'amount' => $amount
        ]);
        
        return [
            'success' => true,
            'provider' => 'Card',
            'message' => 'Redirecting to payment gateway...',
            'transactionReference' => $transactionRef,
            'status' => 'Pending'
        ];
    }
    
    /**
     * Process Bank Transfer
     */
    private function processBankTransfer($orderId, $amount, $transactionRef) {
        Logger::info("Bank transfer initiated", [
            'orderId' => $orderId,
            'amount' => $amount
        ]);
        
        return [
            'success' => true,
            'provider' => 'BankTransfer',
            'message' => 'Bank transfer details have been sent to your email',
            'transactionReference' => $transactionRef,
            'status' => 'Pending'
        ];
    }
    
    /**
     * Process Manual Payment (Admin approval)
     */
    private function processManualPayment($orderId, $amount, $transactionRef) {
        Logger::info("Manual payment initiated", [
            'orderId' => $orderId,
            'amount' => $amount
        ]);
        
        return [
            'success' => true,
            'provider' => 'Manual',
            'message' => 'Manual payment recorded. Awaiting admin verification.',
            'transactionReference' => $transactionRef,
            'status' => 'Pending'
        ];
    }
    
    /**
     * FR11: Verify payment - Only after successful verification, mark order as paid
     */
    public function verifyPayment($transactionRef) {
        $result = $this->payment->verifyPayment($transactionRef);
        
        if ($result) {
            // Get payment details
            $paymentStmt = $this->db->prepare("SELECT OrderID FROM payment WHERE TransactionReference = ?");
            $paymentStmt->bind_param("s", $transactionRef);
            $paymentStmt->execute();
            $paymentData = $paymentStmt->get_result()->fetch_assoc();
            
            if ($paymentData) {
                // Update order payment status to Paid
                $this->order->updatePaymentStatus($paymentData['OrderID'], 'Paid');
                
                // Log audit
                $audit = new AuditLog();
                $audit->logAction(0, 'PAYMENT_VERIFIED', 'Payment', 0, NULL, $transactionRef);
                
                return [
                    'success' => true,
                    'message' => 'Payment verified successfully',
                    'orderId' => $paymentData['OrderID']
                ];
            }
        }
        
        return ['success' => false, 'message' => 'Payment verification failed'];
    }
    
    /**
     * Check payment status for an order
     */
    public function checkPaymentStatus($orderId) {
        $payment = $this->payment->getPaymentByOrder($orderId);
        
        if (!$payment) {
            return ['status' => 'None', 'message' => 'No payment initiated'];
        }
        
        return [
            'status' => $payment['Status'],
            'amount' => $payment['Amount'],
            'provider' => $payment['Provider'],
            'transactionReference' => $payment['TransactionReference'],
            'timestamp' => $payment['Timestamp']
        ];
    }
}
?>