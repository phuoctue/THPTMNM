<?php

require_once 'app/config/database.php';
require_once 'app/libs/ApiRequest.php';
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
        $filters = [
            'search' => trim((string) ApiRequest::input('search', '')),
            'category_id' => ApiRequest::input('category_id', ''),
            'min_price' => ApiRequest::input('min_price', ''),
            'max_price' => ApiRequest::input('max_price', ''),
        ];
        $page = max(1, (int) ApiRequest::input('page', 1));
        $perPage = max(1, min(100, (int) ApiRequest::input('per_page', 8)));
        $offset = ($page - 1) * $perPage;

        $products = $this->productModel->getProducts($filters, $perPage, $offset);
        $total = $this->productModel->countProducts($filters);

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
