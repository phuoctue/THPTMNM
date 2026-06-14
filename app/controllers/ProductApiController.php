<?php

require_once 'app/config/database.php';
require_once 'app/libs/ApiResponse.php';
require_once 'app/libs/AuthMiddleware.php';
require_once 'app/models/ProductModel.php';
require_once 'app/models/CategoryModel.php';

class ProductApiController
{
    private PDO $db;
    private ProductModel $productModel;
    private CategoryModel $categoryModel;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->productModel = new ProductModel($this->db);
        $this->categoryModel = new CategoryModel($this->db);
    }

    public function index(): void
    {
        $filters = [
            'search' => trim((string) ($_GET['search'] ?? '')),
            'category_id' => (int) ($_GET['category_id'] ?? 0),
            'min_price' => $_GET['min_price'] ?? null,
            'max_price' => $_GET['max_price'] ?? null,
            'sort_by' => $_GET['sort_by'] ?? 'created_at',
            'sort_dir' => $_GET['sort_dir'] ?? 'desc',
            'page' => (int) ($_GET['page'] ?? 1),
            'per_page' => (int) ($_GET['per_page'] ?? 12),
        ];

        $products = $this->productModel->getProducts($filters);
        $total = $this->productModel->countProducts($filters);
        $page = max(1, (int) $filters['page']);
        $perPage = max(1, min(100, (int) $filters['per_page']));

        ApiResponse::success('Products retrieved successfully', $products, 200, [
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => (int) max(1, ceil($total / $perPage)),
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

    public function store(): void
    {
        $this->requireAdmin();
        $data = $this->requestData();
        $errors = $this->validatePayload($data, false);

        if (!empty($errors)) {
            ApiResponse::error('Validation failed', $errors, 422);
        }

        $imagePath = $this->handleImageUpload();
        $created = $this->productModel->addProduct(
            trim((string) $data['name']),
            trim((string) ($data['description'] ?? '')),
            (float) $data['price'],
            (int) $data['category_id'],
            $imagePath
        );

        if (!$created) {
            ApiResponse::error('Product creation failed', null, 400);
        }

        ApiResponse::success('Product created successfully', [
            'id' => (int) $this->db->lastInsertId(),
        ], 201);
    }

    public function update($id): void
    {
        $this->requireAdmin();
        $id = (int) $id;
        $product = $this->productModel->getProductById($id);

        if (!$product) {
            ApiResponse::error('Product not found', null, 404);
        }

        $data = $this->requestData();
        $errors = $this->validatePayload($data, true);
        if (!empty($errors)) {
            ApiResponse::error('Validation failed', $errors, 422);
        }

        $imagePath = $product->image ?? null;
        if ($this->hasUploadedImage()) {
            $uploaded = $this->handleImageUpload();
            if ($uploaded !== null) {
                $imagePath = $uploaded;
            }
        }

        $updated = $this->productModel->updateProduct(
            $id,
            trim((string) $data['name']),
            trim((string) ($data['description'] ?? '')),
            (float) $data['price'],
            (int) $data['category_id'],
            $imagePath
        );

        if (!$updated) {
            ApiResponse::error('Product update failed', null, 400);
        }

        ApiResponse::success('Product updated successfully', $this->productModel->getProductById($id));
    }

    public function destroy($id): void
    {
        $this->requireAdmin();
        $deleted = $this->productModel->deleteProduct((int) $id);

        if (!$deleted) {
            ApiResponse::error('Product deletion failed', null, 400);
        }

        ApiResponse::success('Product deleted successfully');
    }

    private function requireAdmin(): void
    {
        $payload = AuthMiddleware::authenticate();
        if (($payload['role'] ?? '') !== 'admin') {
            ApiResponse::error('Forbidden', null, 403);
        }
    }

    private function requestData(): array
    {
        if (!empty($_POST)) {
            return $_POST;
        }

        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);

        return is_array($json) ? $json : [];
    }

    private function validatePayload(array $data, bool $isUpdate): array
    {
        $errors = [];
        $name = trim((string) ($data['name'] ?? ''));
        $price = $data['price'] ?? null;
        $categoryId = (int) ($data['category_id'] ?? 0);

        if ($name === '') {
            $errors['name'] = 'Tên sản phẩm không được để trống';
        }

        if ($price === null || $price === '' || !is_numeric($price) || (float) $price <= 0) {
            $errors['price'] = 'Giá phải là số và lớn hơn 0';
        }

        if ($categoryId <= 0 || !$this->categoryModel->getCategoryById($categoryId)) {
            $errors['category_id'] = 'Danh mục sản phẩm phải hợp lệ';
        }

        if ($this->hasUploadedImage()) {
            $fileError = $this->validateUpload();
            if ($fileError !== '') {
                $errors['image'] = $fileError;
            }
        }

        return $errors;
    }

    private function hasUploadedImage(): bool
    {
        return !empty($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    }

    private function validateUpload(): string
    {
        if (empty($_FILES['image']) || ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return 'Tải ảnh thất bại';
        }

        $file = $_FILES['image'];
        if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
            return 'Ảnh sản phẩm không được vượt quá 2MB';
        }

        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $mime = @mime_content_type($file['tmp_name']) ?: '';
        if (!in_array($mime, $allowed, true)) {
            return 'Hình ảnh phải là JPG, PNG, WEBP hoặc GIF';
        }

        return '';
    }

    private function handleImageUpload(): ?string
    {
        if (!$this->hasUploadedImage()) {
            return null;
        }

        $error = $this->validateUpload();
        if ($error !== '') {
            ApiResponse::error('Validation failed', ['image' => $error], 422);
        }

        $file = $_FILES['image'];
        $mime = @mime_content_type($file['tmp_name']) ?: 'image/jpeg';
        $extension = match ($mime) {
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            default => 'jpg',
        };

        $uploadDir = __DIR__ . '/../../uploads';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
            ApiResponse::error('Upload failed', ['image' => 'Không thể tạo thư mục upload'], 500);
        }

        $fileName = 'img_' . uniqid('', true) . '.' . $extension;
        $target = $uploadDir . '/' . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $target)) {
            ApiResponse::error('Upload failed', ['image' => 'Không thể lưu ảnh'], 500);
        }

        return 'uploads/' . $fileName;
    }
}
