<?php

require_once 'app/config/database.php';
require_once 'app/libs/ApiResponse.php';
require_once 'app/models/ProductModel.php';

class ProductApiController
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
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = max(1, min(100, (int)($_GET['per_page'] ?? 10)));
        $offset = ($page - 1) * $perPage;

        $products = $this->productModel->getProducts($search, $perPage, $offset);
        $total = $this->productModel->countProducts($search);

        ApiResponse::success(
            'Products retrieved successfully',
            $products,
            200,
            [
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => (int) ceil($total / $perPage),
                ],
            ]
        );
    }

    public function show($id): void
    {
        $product = $this->productModel->getProductById((int) $id);

        if (!$product) {
            ApiResponse::error('Product not found', null, 404);
        }

        ApiResponse::success('Product retrieved successfully', $product);
    }

    public function store(): void
    {
        $data = $this->getJsonInput();
        $errors = $this->validatePayload($data);

        if (!empty($errors)) {
            ApiResponse::error('Validation failed', $errors, 422);
        }

        $image = $data['image'] ?? null;
        $created = $this->productModel->addProduct(
            trim((string) $data['name']),
            trim((string) $data['description']),
            $data['price'],
            $data['category_id'],
            $image
        );

        if (!$created) {
            ApiResponse::error('Product creation failed', null, 400);
        }

        ApiResponse::success('Product created successfully', null, 201);
    }

    public function update($id): void
    {
        $product = $this->productModel->getProductById((int) $id);
        if (!$product) {
            ApiResponse::error('Product not found', null, 404);
        }

        $data = $this->getJsonInput();
        $errors = $this->validatePayload($data, true);

        if (!empty($errors)) {
            ApiResponse::error('Validation failed', $errors, 422);
        }

        $image = array_key_exists('image', $data) ? $data['image'] : ($product->image ?? null);
        $updated = $this->productModel->updateProduct(
            (int) $id,
            trim((string) $data['name']),
            trim((string) $data['description']),
            $data['price'],
            $data['category_id'],
            $image
        );

        if (!$updated) {
            ApiResponse::error('Product update failed', null, 400);
        }

        ApiResponse::success('Product updated successfully');
    }

    public function destroy($id): void
    {
        $deleted = $this->productModel->deleteProduct((int) $id);

        if (!$deleted) {
            ApiResponse::error('Product deletion failed', null, 400);
        }

        ApiResponse::success('Product deleted successfully');
    }

    private function getJsonInput(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        return is_array($data) ? $data : [];
    }

    private function validatePayload(array $data, bool $isUpdate = false): array
    {
        $errors = [];
        $name = trim((string)($data['name'] ?? ''));
        $description = trim((string)($data['description'] ?? ''));
        $price = $data['price'] ?? null;
        $categoryId = $data['category_id'] ?? null;

        if ($name === '') {
            $errors['name'] = 'Tên sản phẩm không được để trống';
        }

        if ($description === '') {
            $errors['description'] = 'Mô tả không được để trống';
        }

        if ($price === null || $price === '' || !is_numeric($price) || (float) $price < 0) {
            $errors['price'] = 'Giá sản phẩm không hợp lệ';
        }

        if ($categoryId === null || $categoryId === '' || !is_numeric($categoryId) || (int) $categoryId <= 0) {
            $errors['category_id'] = 'Danh mục sản phẩm không hợp lệ';
        }

        return $errors;
    }
}
