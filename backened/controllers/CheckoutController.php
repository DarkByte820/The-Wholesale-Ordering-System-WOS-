<?php
/**
 * Checkout Controller
 * Handles checkout requests
 */

class CheckoutController
{
    /**
     * POST /api/checkout
     */
    public static function processCheckout()
    {
        try {

            $data = json_decode(file_get_contents("php://input"), true);

            if (!$data) {
                Response::error("Invalid request data", 400);
                return;
            }

            // Change this if your auth middleware stores the user differently
            $userId = $data['userId'] ?? 0;

            $service = new CheckoutService();

            $result = $service->processCheckout($userId, $data);

            if ($result['success']) {
                Response::success($result, "Checkout completed successfully");
            } else {
                Response::error(
                    $result['message'] ?? "Checkout failed",
                    400,
                    $result
                );
            }

        } catch (Exception $e) {

            Logger::error("Checkout Error", [
                'message' => $e->getMessage()
            ]);

            Response::error("Checkout failed", 500);
        }
    }

    /**
     * POST /api/checkout/summary
     */
    public static function getOrderSummary()
    {
        try {

            $data = json_decode(file_get_contents("php://input"), true);

            if (!isset($data['items'])) {
                Response::error("Items are required", 400);
                return;
            }

            $service = new CheckoutService();

            $summary = $service->getOrderSummary($data['items']);

            Response::success($summary);

        } catch (Exception $e) {

            Logger::error("Order Summary Error", [
                'message' => $e->getMessage()
            ]);

            Response::error("Unable to generate order summary", 500);
        }
    }
}