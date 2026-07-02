<?php
/**
 * Delivery Controller
 */

class DeliveryController {
    
    public static function getDeliveryStatus() {
        $order_id = isset($_GET['orderId']) ? (int)$_GET['orderId'] : null;
        
        if (!$order_id) {
            Response::error("Order ID required", BAD_REQUEST);
        }
        
        $delivery = new Delivery();
        $data = $delivery->getDeliveryByOrder($order_id);
        
        if ($data) {
            Response::success($data, "Delivery status retrieved");
        } else {
            Response::error("Delivery not found", NOT_FOUND);
        }
    }
    
    public static function updateDeliveryStatus() {
        $user = authenticateUser();
        $input = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($input['deliveryId']) || !isset($input['status'])) {
            Response::error("Delivery ID and status required", BAD_REQUEST);
        }
        
        $delivery = new Delivery();
        $result = $delivery->updateDeliveryStatus($input['deliveryId'], $input['status']);
        
        if ($result) {
            Logger::info("Delivery status updated", ['deliveryId' => $input['deliveryId']]);
            Response::success(null, "Delivery status updated");
        } else {
            Response::error("Failed to update delivery status", SERVER_ERROR);
        }
    }
    
    public static function recordProofOfDelivery() {
        $user = authenticateUser();
        $input = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($input['deliveryId']) || !isset($input['receiverName'])) {
            Response::error("Delivery ID and receiver name required", BAD_REQUEST);
        }
        
        $delivery = new Delivery();
        $result = $delivery->recordProofOfDelivery(
            $input['deliveryId'],
            $input['receiverName'],
            $input['notes'] ?? '',
            $input['latitude'] ?? null,
            $input['longitude'] ?? null
        );
        
        if ($result) {
            Logger::info("Proof of delivery recorded", ['deliveryId' => $input['deliveryId']]);
            Response::success(null, "Proof of delivery recorded");
        } else {
            Response::error("Failed to record proof of delivery", SERVER_ERROR);
        }
    }
}

?>