<?php

require_once 'app/config/database.php';
require_once 'app/libs/ApiResponse.php';
require_once 'app/models/ProductModel.php';

class HomeApiController
{
    private ProductModel $productModel;

    public function __construct()
    {
        $db = (new Database())->getConnection();
        $this->productModel = new ProductModel($db);
    }

    public function index(): void
    {
        $search = trim($_GET['search'] ?? '');
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = max(1, min(100, (int) ($_GET['per_page'] ?? 8)));
        $offset = ($page - 1) * $perPage;

        $products = $this->productModel->getProducts($search, $perPage, $offset);
        $total = $this->productModel->countProducts($search);

        ApiResponse::success('Home feed retrieved successfully', $products, 200, [
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => (int) ceil($total / $perPage),
            ],
        ]);
    }
}
