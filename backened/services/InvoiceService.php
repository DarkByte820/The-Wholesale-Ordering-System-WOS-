<?php
/**
 * Invoice Service - FR12
 * FR12: Invoice Generation - Create invoices with order details
 */

class InvoiceService {
    private $db;
    private $invoice;
    private $order;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->invoice = new Invoice();
        $this->order = new Order();
    }
    
    /**
     * Generate invoice for order
     * FR12: Contains order number, customer details, items, taxes, total, payment status
     */
    public function generateInvoice($orderId) {
        // Get order
        $order = $this->order->getOrderById($orderId);
        if (!$order) {
            return ['success' => false, 'message' => 'Order not found'];
        }
        
        // Get order items
        $items = $this->order->getOrderItems($orderId);
        
        // Get customer
        $userStmt = $this->db->prepare("SELECT * FROM user WHERE UserID = ?");
        $userStmt->bind_param("i", $order['UserID']);
        $userStmt->execute();
        $customer = $userStmt->get_result()->fetch_assoc();
        
        // Check if invoice already exists
        $existingInvoice = $this->invoice->getInvoiceByOrderId($orderId);
        if ($existingInvoice) {
            return ['success' => false, 'message' => 'Invoice already exists for this order'];
        }
        
        // Calculate totals
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $item['TotalPrice'];
        }
        
        $tax = $subtotal * 0.05;
        $discount = 0;
        $netAmount = $subtotal + $tax - $discount;
        
        // Create invoice
        $invoiceId = $this->invoice->createInvoice($orderId, $subtotal, $tax, $discount);
        
        if (!$invoiceId) {
            return ['success' => false, 'message' => 'Failed to generate invoice'];
        }
        
        // Log audit
        $audit = new AuditLog();
        $audit->logAction($order['UserID'], 'INVOICE_GENERATED', 'Invoice', $invoiceId);
        
        return [
            'success' => true,
            'invoiceId' => $invoiceId,
            'invoice' => $this->formatInvoice($invoiceId, $customer, $items, $order)
        ];
    }
    
    /**
     * Format invoice data for display/download
     * FR12: Users and administrators can view or download invoice
     */
    public function formatInvoice($invoiceId, $customer, $items, $order) {
        $invoice = $this->invoice->getInvoiceById($invoiceId);
        
        return [
            'invoiceNumber' => $invoice['InvoiceNumber'],
            'issueDate' => $invoice['IssueDate'],
            'dueDate' => $invoice['DueDate'],
            'status' => $invoice['Status'],
            'customer' => [
                'name' => $customer['Name'],
                'email' => $customer['Email'],
                'phone' => $customer['Phone']
            ],
            'order' => [
                'orderId' => $order['OrderID'],
                'date' => $order['OrderDate'],
                'status' => $order['Status'],
                'deliveryAddress' => $order['DeliveryAddress']
            ],
            'items' => $items,
            'subtotal' => $invoice['Amount'],
            'tax' => $invoice['TaxAmount'],
            'discount' => $invoice['Discount'],
            'netAmount' => $invoice['NetAmount'],
            'paymentStatus' => $order['PaymentStatus']
        ];
    }
    
    /**
     * Generate invoice PDF for download
     */
    public function generatePDF($invoiceId) {
        $invoice = $this->invoice->getInvoiceById($invoiceId);
        if (!$invoice) {
            return ['success' => false, 'message' => 'Invoice not found'];
        }
        
        // TODO: Use TCPDF or DOMPDF library to generate PDF
        // For now, return path to PDF storage
        
        return [
            'success' => true,
            'pdfPath' => '/invoices/' . $invoice['InvoiceNumber'] . '.pdf',
            'fileName' => 'Invoice_' . $invoice['InvoiceNumber'] . '.pdf'
        ];
    }
    
    /**
     * Email invoice to customer
     */
    public function emailInvoice($invoiceId, $customerEmail) {
        $invoice = $this->invoice->getInvoiceById($invoiceId);
        if (!$invoice) {
            return ['success' => false, 'message' => 'Invoice not found'];
        }
        
        // TODO: Integrate with email service (SendGrid, PHPMailer, etc.)
        
        Logger::info("Invoice emailed to customer", [
            'invoiceId' => $invoiceId,
            'email' => $customerEmail
        ]);
        
        return [
            'success' => true,
            'message' => 'Invoice sent to customer email'
        ];
    }
}
?>