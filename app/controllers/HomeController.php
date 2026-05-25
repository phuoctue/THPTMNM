<?php
require_once('app/config/database.php');
require_once('app/models/ProductModel.php');

class HomeController {
    private $productModel;
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
        $this->productModel = new ProductModel($this->db);
    }

    public function index(): void {
        $search = trim($_GET['search'] ?? '');
        $perPage = 8;
        $currentPage = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($currentPage - 1) * $perPage;

        $total = $this->productModel->countProducts($search);
        $totalPages = (int) ceil($total / $perPage);
        if ($totalPages > 0 && $currentPage > $totalPages) {
            $currentPage = $totalPages;
            $offset = ($currentPage - 1) * $perPage;
        }

        $products = $this->productModel->getProducts($search, $perPage, $offset);
        include 'app/views/home/index.php';
    }
}
?>
