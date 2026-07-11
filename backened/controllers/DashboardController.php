<?php
/**
 * Dashboard Controller
 */

class DashboardController
{
    /**
     * GET /api/dashboard
     */
    public static function getDashboard()
    {
        $service = new DashboardService();

        Response::success(
            $service->getDashboardMetrics(),
            "Dashboard loaded"
        );
    }

    /**
     * GET /api/dashboard/sales-trend
     */
    public static function salesTrend()
    {
        $days = $_GET['days'] ?? 30;

        $service = new DashboardService();

        Response::success(
            $service->getSalesTrend($days),
            "Sales trend"
        );
    }

    /**
     * GET /api/dashboard/order-status
     */
    public static function orderStatusBreakdown()
    {
        $service = new DashboardService();

        Response::success(
            $service->getOrderStatusBreakdown(),
            "Order status breakdown"
        );
    }
}