<?php

require_once 'app/config/database.php';
require_once 'app/libs/ApiResponse.php';
require_once 'app/libs/AuthHelper.php';
require_once 'app/models/DashboardModel.php';

class DashboardApiController
{
    private DashboardModel $dashboardModel;

    public function __construct()
    {
        $db = (new Database())->getConnection();
        $this->dashboardModel = new DashboardModel($db);
    }

    public function index(): void
    {
        if (!AuthHelper::isAdmin()) {
            ApiResponse::error('Forbidden', null, 403);
        }

        ApiResponse::success('Dashboard retrieved successfully', [
            'summary' => $this->dashboardModel->getSummary(),
            'recent_orders' => $this->dashboardModel->getRecentOrders(),
            'top_products' => $this->dashboardModel->getTopProducts(),
        ]);
    }
}
