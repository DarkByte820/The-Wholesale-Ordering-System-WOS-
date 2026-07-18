<?php
/**
 * Invoice Controller
 */

class InvoiceController {
    
    public static function getInvoiceByOrder() {
        $user = authenticateUser();
        $order_id = isset($_GET['order_Id']) ? (int)$_GET['order_Id'] : null;
        
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
    public static function generateInvoice() {
        $user = authenticateUser();

        $order_id = isset($_GET['order_Id']) ? (int)$_GET['order_Id'] : null;

        if (!$order_id) {
            Response::error("Order ID required", BAD_REQUEST);
        }
        
        $invoice = new Invoice();
        $result = $invoice->generateInvoice($user['user_Id']);
        
            Response::success($result, "Invoice generated successfully");
            Response::error(
                $result['message'] ?? "Invoice generation failed",
                400,
                $result
            );
    }
    
 public static function generatePDF() {
    $user = authenticateUser();

    $order_id = isset($_GET['invoice_Id']) ? (int)$_GET['invoice_Id'] : null;

    if (!$order_id) {
        Response::error("Order ID required", BAD_REQUEST);
    }

    $invoice = new Invoice();
    $pdf_data = $invoice->generatePDF($order_id, $user['userId']);

    if ($pdf_data) {
        header('Content-Type: application/json');
        echo $pdf_data;
    } else {
        Response::error("Invoice not found", NOT_FOUND);
    }
}
    public static function emailInvoice() {
        $user = authenticateUser();
        $invoice_id = isset($_GET['invoice_id']) ? (int)$_GET['invoice_id'] : null;
        
        if (!$invoice_id) {
            Response::error("Invoice ID required", BAD_REQUEST);
        }
        
        $invoice = new Invoice();
        $result = $invoice->emailInvoice($invoice_id, $user['userId']);
        
        if ($result['success']) {
            Response::success(null, "Invoice emailed successfully");
        } else {
            Response::error(
                $result['message'] ?? "Failed to email invoice",
                400,
                $result
            );
        }
    }
}

?>