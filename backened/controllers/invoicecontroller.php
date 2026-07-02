<?php
/**
 * Invoice Controller
 */

class InvoiceController {
    
    public static function getInvoiceByOrder() {
        $user = authenticateUser();
        $order_id = isset($_GET['orderId']) ? (int)$_GET['orderId'] : null;
        
        if (!$order_id) {
            Response::error("Order ID required", BAD_REQUEST);
        }
        
        $invoice = new Invoice();
        $invoice_data = $invoice->getInvoiceByOrderId($order_id);
        
        if (!$invoice_data) {
            Response::error("Invoice not found", NOT_FOUND);
        }
        
        Response::success($invoice_data, "Invoice retrieved successfully");
    }
    
    public static function getInvoiceById() {
        $invoice_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        
        if (!$invoice_id) {
            Response::error("Invoice ID required", BAD_REQUEST);
        }
        
        $invoice = new Invoice();
        $invoice_data = $invoice->getInvoiceById($invoice_id);
        
        if (!$invoice_data) {
            Response::error("Invoice not found", NOT_FOUND);
        }
        
        Response::success($invoice_data, "Invoice retrieved successfully");
    }
    
    public static function getMyInvoices() {
        $user = authenticateUser();
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : ITEMS_PER_PAGE;
        
        $invoice = new Invoice();
        $invoices = $invoice->getInvoicesByUser($user['userId'], $page, $limit);
        
        Response::success($invoices, "Invoices retrieved successfully");
    }
}

?>