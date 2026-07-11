<?php
/**
 * Checkout Service - FR10
 * Handles checkout process: delivery details, order summary, payment initiation
 */

class CheckoutService {
    private $db;
    private $order;
    private $invoice;
    private $delivery;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->order = new Order();
        $this->invoice = new Invoice();
        $this->delivery = new Delivery();
    }
    
    /**
     * Validate checkout data
     * FR10: Order cannot be placed when required checkout fields are missing
     */
    public function validateCheckout($data) {
        $errors = [];
        
        // Required fields
        if (empty($data['orderType'])) {
            $errors[] = "Order type is required";
        }
        
        if (empty($data['items']) || !is_array($data['items']) || count($data['items']) === 0) {
            $errors[] = "Order items are required";
        }
        
        if (empty($data['deliveryAddress'])) {
            $errors[] = "Delivery address is required";
        }
        
        if (empty($data['deliveryCity'])) {
            $errors[] = "Delivery city is required";
        }
        
        if (empty($data['customerPhone'])) {
            $errors[] = "Customer phone number is required";
        } else {
            if (!preg_match('/^(\+233|0)[0-9]{9}$/', $data['customerPhone'])) {
                $errors[] = "Invalid phone number format";
            }
        }
        
        // Validate each item
        foreach ($data['items'] as $item) {
            if (empty($item['id']) || empty($item['quantity']) || empty($item['price'])) {
                $errors[] = "Each item must have ID, quantity, and price";
            }
            
            if ($item['quantity'] <= 0) {
                $errors[] = "Item quantity must be greater than 0";
            }
            
            if ($item['price'] < 0) {
                $errors[] = "Item price cannot be negative";
            }
        }
        
        return $errors;
    }
    
    /**
     * Get order summary before checkout
     */
    public function getOrderSummary($items) {
        $summary = [
            'items' => [],
            'subtotal' => 0,
            'tax' => 0,
            'discount' => 0,
            'total' => 0,
            'itemCount' => 0
        ];
        
        foreach ($items as $item) {
            $itemTotal = $item['quantity'] * $item['price'];
            
            $summary['items'][] = [
                'productId' => $item['id'],
                'name' => $item['name'] ?? 'Product',
                'quantity' => $item['quantity'],
                'unitPrice' => $item['price'],
                'totalPrice' => $itemTotal
            ];
            
            $summary['subtotal'] += $itemTotal;
            $summary['itemCount']++;
        }
        
        // Calculate tax (5%)
        $summary['tax'] = $summary['subtotal'] * 0.05;
        
        // Calculate total
        $summary['total'] = $summary['subtotal'] + $summary['tax'] - $summary['discount'];
        
        return $summary;
    }
    
    /**
     * Process checkout - Create order and supporting records
     * FR10: Submit delivery details, confirm order summary
     */
    public function processCheckout($userId, $checkoutData) {
        // Validate
        $errors = $this->validateCheckout($checkoutData);
        if (!empty($errors)) {
            return [
                'success' => false,
                'errors' => $errors
            ];
        }
        
        // Get summary
        $summary = $this->getOrderSummary($checkoutData['items']);
        
        // Create order
        $orderId = $this->order->createOrder(
            $userId,
            $checkoutData['orderType'],
            $summary['total'],
            $checkoutData['deliveryAddress'],
            $checkoutData['deliveryCity'],
            $checkoutData['customerPhone']
        );
        
        if (!$orderId) {
            return [
                'success' => false,
                'message' => 'Failed to create order'
            ];
        }
        
        // Add order items
        foreach ($checkoutData['items'] as $item) {
            $this->order->addOrderItem(
                $orderId,
                $item['id'],
                $item['quantity'],
                $item['price']
            );
        }
        
        // Create delivery record
        $this->delivery->createDelivery(
            $orderId,
            $checkoutData['deliveryAddress'],
            $checkoutData['deliveryCity'],
            $checkoutData['customerPhone']
        );
        
        // Create invoice
        $this->invoice->createInvoice(
            $orderId,
            $summary['subtotal'],
            $summary['tax'],
            $summary['discount']
        );
        
        // Log audit
        $audit = new AuditLog();
        $audit->logAction($userId, 'CHECKOUT_COMPLETED', 'Order', $orderId);
        
        return [
            'success' => true,
            'orderId' => $orderId,
            'summary' => $summary
        ];
    }
}
?>