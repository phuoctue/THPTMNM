<?php
require_once('app/config/database.php');
require_once('app/models/DashboardModel.php');

class DashboardController {
    private $dashboardModel;

    public function __construct() {
        $db = (new Database())->getConnection();
        $this->dashboardModel = new DashboardModel($db);
    }

    public function index(): void {
        $summary = $this->dashboardModel->getSummary();
        $recentOrders = $this->dashboardModel->getRecentOrders();
        $topProducts = $this->dashboardModel->getTopProducts();
        include 'app/views/dashboard/index.php';
    }
}
?>
