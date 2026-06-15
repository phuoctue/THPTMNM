<?php

require_once 'app/config/database.php';
require_once 'app/libs/ApiResponse.php';
require_once 'app/libs/ApiRequest.php';
require_once 'app/middleware/AuthMiddleware.php';
require_once 'app/models/CategoryModel.php';
require_once 'app/models/ProductModel.php';

class ProductApiController
{
    private ProductModel $productModel;
    private CategoryModel $categoryModel;

    public function __construct()
    {
        $db = (new Database())->getConnection();
        $this->productModel = new ProductModel($db);
        $this->categoryModel = new CategoryModel($db);
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
        $perPage = max(1, min(100, (int) ApiRequest::input('per_page', 10)));
        $offset = ($page - 1) * $perPage;

        $products = $this->productModel->getProducts($filters, $perPage, $offset);
        $total = $this->productModel->countProducts($filters);

        ApiResponse::success('Products retrieved successfully', $products, 200, [
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => (int) ceil($total / $perPage),
            ],
        ]);
    }

    public function show($id): void
    {
        $product = $this->productModel->getProductById((int) $id);
        if (!$product) {
            ApiResponse::error('Product not found', null, 404);
        }

        ApiResponse::success('Product retrieved successfully', $product);
    }

    public function search(): void
    {
        $search = trim((string) ApiRequest::input('q', ApiRequest::input('search', '')));
        $filters = ['search' => $search];
        $products = $this->productModel->getProducts($filters, 100, 0);

        ApiResponse::success('Products search completed successfully', $products);
    }

    public function filter(): void
    {
        $filters = [
            'category_id' => ApiRequest::input('category_id', ''),
            'min_price' => ApiRequest::input('min_price', ''),
            'max_price' => ApiRequest::input('max_price', ''),
        ];

        $products = $this->productModel->getProducts($filters, 100, 0);
        ApiResponse::success('Products filtered successfully', $products);
    }

    public function sort(): void
    {
        $direction = strtolower((string) ApiRequest::input('direction', ApiRequest::input('order', 'asc')));
        $filters = [
            'search' => trim((string) ApiRequest::input('search', '')),
            'category_id' => ApiRequest::input('category_id', ''),
            'min_price' => ApiRequest::input('min_price', ''),
            'max_price' => ApiRequest::input('max_price', ''),
        ];

        $products = $this->productModel->getProducts($filters, 1000, 0);
        usort($products, static function (array $left, array $right) use ($direction): int {
            $comparison = ((float) ($left['price'] ?? 0)) <=> ((float) ($right['price'] ?? 0));
            return $direction === 'desc' ? -$comparison : $comparison;
        });

        ApiResponse::success('Products sorted successfully', $products);
    }

    public function store(): void
    {
        AuthMiddleware::admin();

        $data = $this->normalizeProductInput();
        $errors = $this->validatePayload($data);
        $imagePath = null;

        if (!empty($errors)) {
            ApiResponse::error('Validation failed', $errors, 422);
        }

        if (!empty($_FILES['image']['name'])) {
            $upload = $this->handleImageUpload($_FILES['image']);
            if ($upload !== true) {
                ApiResponse::error('Validation failed', ['image' => $upload], 422);
            }
            $imagePath = $upload;
        }

        $productId = $this->productModel->create([
            'name' => trim((string) $data['name']),
            'description' => trim((string) ($data['description'] ?? '')),
            'price' => (float) $data['price'],
            'category_id' => (int) $data['category_id'],
            'image' => $imagePath,
        ]);

        if (!$productId) {
            ApiResponse::error('Product creation failed', null, 400);
        }

        ApiResponse::success('Product created successfully', [
            'id' => $productId,
            'product' => $this->productModel->getProductById($productId),
        ], 201);
    }

    public function update($id): void
    {
        AuthMiddleware::admin();

        $productId = (int) $id;
        $existing = $this->productModel->getProductById($productId);
        if (!$existing) {
            ApiResponse::error('Product not found', null, 404);
        }

        $data = $this->normalizeProductInput();
        $errors = $this->validatePayload($data);
        if (!empty($errors)) {
            ApiResponse::error('Validation failed', $errors, 422);
        }

        $payload = [
            'name' => trim((string) $data['name']),
            'description' => trim((string) ($data['description'] ?? '')),
            'price' => (float) $data['price'],
            'category_id' => (int) $data['category_id'],
        ];

        if (!empty($_FILES['image']['name'])) {
            $upload = $this->handleImageUpload($_FILES['image']);
            if ($upload !== true) {
                ApiResponse::error('Validation failed', ['image' => $upload], 422);
            }
            $payload['image'] = $upload;
        }

        if (!$this->productModel->update($productId, $payload)) {
            ApiResponse::error('Product update failed', null, 400);
        }

        ApiResponse::success('Product updated successfully', [
            'product' => $this->productModel->getProductById($productId),
        ]);
    }

    public function destroy($id): void
    {
        AuthMiddleware::admin();

        $deleted = $this->productModel->delete((int) $id);
        if (!$deleted) {
            ApiResponse::error('Product deletion failed', null, 400);
        }

        ApiResponse::success('Product deleted successfully');
    }

    private function normalizeProductInput(): array
    {
        $body = ApiRequest::body();

        return [
            'name' => $body['name'] ?? '',
            'description' => $body['description'] ?? '',
            'price' => $body['price'] ?? '',
            'category_id' => $body['category_id'] ?? '',
        ];
    }

    private function validatePayload(array $data): array
    {
        $errors = [];
        $name = trim((string) ($data['name'] ?? ''));
        $price = $data['price'] ?? null;
        $categoryId = (int) ($data['category_id'] ?? 0);

        if ($name === '') {
            $errors['name'] = 'Tên sản phẩm không được để trống';
        }

        if ($price === null || $price === '' || !is_numeric($price) || (float) $price <= 0) {
            $errors['price'] = 'Giá phải lớn hơn 0';
        }

        if ($categoryId <= 0 || !$this->categoryModel->exists($categoryId)) {
            $errors['category_id'] = 'Danh mục không hợp lệ';
        }

        return $errors;
    }

    private function handleImageUpload(array $file): string|bool
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return 'Tải ảnh thất bại';
        }

        if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
            return 'Ảnh không được vượt quá 5MB';
        }

        $allowedMime = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name'] ?? '');
        if (!isset($allowedMime[$mime])) {
            return 'Chỉ chấp nhận JPG, PNG, WEBP hoặc GIF';
        }

        $uploadDir = __DIR__ . '/../../uploads';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
            return 'Không thể tạo thư mục upload';
        }

        $filename = 'product_' . bin2hex(random_bytes(8)) . '.' . $allowedMime[$mime];
        $target = $uploadDir . DIRECTORY_SEPARATOR . $filename;
        if (!move_uploaded_file($file['tmp_name'], $target)) {
            return 'Không thể lưu ảnh';
        }

        return 'uploads/' . $filename;
    }
}
