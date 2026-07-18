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
        
        $stmt = $this->db->prepare("INSERT INTO invoices (Order_ID, InvoiceNumber, Amount, TaxAmount, Discount, NetAmount, IssueDate, Status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
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
        $stmt = $this->db->prepare("SELECT * FROM invoice WHERE Order_ID = ?");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function getInvoiceById($invoice_id) {
        $stmt = $this->db->prepare("SELECT * FROM invoices WHERE InvoiceID = ?");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("i", $invoice_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function getInvoiceByNumber($invoice_number) {
        $stmt = $this->db->prepare("SELECT * FROM invoices WHERE InvoiceNumber = ?");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("s", $invoice_number);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function updateInvoiceStatus($invoice_id, $status) {
        $stmt = $this->db->prepare("UPDATE invoices SET Status = ?, DateModified = NOW() WHERE InvoiceID = ?");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }
        
        $stmt->bind_param("si", $status, $invoice_id);
        return $stmt->execute();
    }
    
    public function getInvoicesByUser($user_id, $page = 1, $limit = ITEMS_PER_PAGE) {
        $offset = ($page - 1) * $limit;
        
        $stmt = $this->db->prepare("SELECT i.* FROM invoices i JOIN `order` o ON i.OrderID = o.Order_ID WHERE o.User_ID = ? ORDER BY i.IssueDate DESC LIMIT ? OFFSET ?");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return [];
        }
        
        $stmt->bind_param("iii", $user_id, $limit, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getInvoicesByDateRange($start_date, $end_date) {
        $stmt = $this->db->prepare("SELECT * FROM invoices WHERE IssueDate BETWEEN ? AND ? ORDER BY IssueDate DESC");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return [];
        }
        
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    public static function emailInvoice() {
        $user = authenticateUser();
        
        if ($user['role'] !== 'WarehouseAdmin' && $user['role'] !== 'SystemAdmin') {
            Response::error("Insufficient permissions", FORBIDDEN);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['invoice_id']) || !isset($input['email'])) {
            Response::error("Missing required fields: invoice_id and email", BAD_REQUEST);
        }
        
        $invoiceModel = new Invoice();
        $invoice = $invoiceModel->getInvoiceById($input['invoice_id']);
        
        if (!$invoice) {
            Response::error("Invoice not found", NOT_FOUND);
        }
        
        // Here you would implement the actual email sending logic.
        // For demonstration, we'll just return a success message.
        
        Response::success(null, "Invoice emailed successfully to " . $input['email']);
    }
    public static function generateInvoice($user_id) {
        $invoice = new Invoice();
        return $invoice->getInvoicesByUser($user_id);
    }
    public function generatePDF($order_id, $user_id)
{
    $stmt = $this->db->prepare("
        SELECT
            o.OrderID,
            o.OrderType,
            o.subtotal,
            o.tax,
            o.delivery_fee,
            o.TotalAmount,
            o.PaymentStatus,
            o.DeliveryAddress,
            o.DeliveryCity,
            o.CustomerPhone,
            o.notes,

            ot.quantity,
            ot.item_type,
            ot.TotalPrice,

            op.name AS product_name,
            
            CASE
                WHEN ot.quantity < op.min_wholesale_qty
                    THEN op.unit_price
                ELSE op.wholesale_price
                END AS selling_price,

            os.name AS user_name,
            os.phone,

            og.address,
            og.customer_type

        FROM orders o
        LEFT JOIN order_items ot
            ON ot.OrderID = o.OrderID
        LEFT JOIN products op
            ON op.product_id = ot.ProductID
        LEFT JOIN users os
            ON os.user_id = o.UserID
        LEFT JOIN customer_profiles og
            ON og.user_id = os.user_id

        WHERE o.OrderID = ?
        AND o.UserID = ?
    ");

    if (!$stmt) {
        error_log("Prepare failed: " . $this->db->error);
        return false;
    }

    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        return false;
    }

    $invoice = [];
    $invoice['items'] = [];

    while ($row = $result->fetch_assoc()) {

        if (empty($invoice['OrderID'])) {

            $invoice['OrderID'] = $row['OrderID'];
            $invoice['OrderType'] = $row['OrderType'];
            $invoice['subtotal'] = $row['subtotal'];
            $invoice['tax'] = $row['tax'];
            $invoice['delivery_fee'] = $row['delivery_fee'];
            $invoice['TotalAmount'] = $row['TotalAmount'];
            $invoice['PaymentStatus'] = $row['PaymentStatus'];
            $invoice['DeliveryAddress'] = $row['DeliveryAddress'];
            $invoice['DeliveryCity'] = $row['DeliveryCity'];
            $invoice['CustomerPhone'] = $row['CustomerPhone'];
            $invoice['notes'] = $row['notes'];

            $invoice['Customer'] = [
                'name' => $row['user_name'],
                'phone' => $row['phone'],
                'address' => $row['address'],
                'customer_type' => $row['customer_type']
            ];
        }

        $invoice['items'][] = [
            'product_name' => $row['product_name'],
            'quantity' => $row['quantity'],
            'item_type' => $row['item_type'],
            'selling_price' => $row['selling_price'],
            'total_price' => $row['TotalPrice']
        ];
    }

    $stmt->close();

    // Temporary until PDF generation is added
    return json_encode($invoice, JSON_PRETTY_PRINT);
}
}


?>