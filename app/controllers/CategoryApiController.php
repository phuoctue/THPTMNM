<?php

require_once 'app/config/database.php';
require_once 'app/libs/ApiResponse.php';
require_once 'app/libs/AuthMiddleware.php';
require_once 'app/models/CategoryModel.php';

class CategoryApiController
{
    private CategoryModel $categoryModel;

    public function __construct()
    {
        $db = (new Database())->getConnection();
        $this->categoryModel = new CategoryModel($db);
    }

    public function index(): void
    {
        ApiResponse::success('Categories retrieved successfully', $this->categoryModel->getCategories());
    }

    public function show($id): void
    {
        $category = $this->categoryModel->getCategoryById((int) $id);
        if (!$category) {
            ApiResponse::error('Category not found', null, 404);
        }

        ApiResponse::success('Category retrieved successfully', $category);
    }

    public function store(): void
    {
        $this->requireAdmin();
        $data = $this->requestData();
        $errors = $this->validatePayload($data);

        if (!empty($errors)) {
            ApiResponse::error('Validation failed', $errors, 422);
        }

        $created = $this->categoryModel->addCategory(
            trim((string) $data['name']),
            trim((string) ($data['description'] ?? ''))
        );

        if (!$created) {
            ApiResponse::error('Category creation failed', null, 400);
        }

        ApiResponse::success('Category created successfully', null, 201);
    }

    public function update($id): void
    {
        $this->requireAdmin();
        $id = (int) $id;
        $category = $this->categoryModel->getCategoryById($id);
        if (!$category) {
            ApiResponse::error('Category not found', null, 404);
        }

        $data = $this->requestData();
        $errors = $this->validatePayload($data);
        if (!empty($errors)) {
            ApiResponse::error('Validation failed', $errors, 422);
        }

        $updated = $this->categoryModel->updateCategory(
            $id,
            trim((string) $data['name']),
            trim((string) ($data['description'] ?? ''))
        );

        if (!$updated) {
            ApiResponse::error('Category update failed', null, 400);
        }

        ApiResponse::success('Category updated successfully');
    }

    public function destroy($id): void
    {
        $this->requireAdmin();
        $id = (int) $id;
        if ($this->categoryModel->hasProducts($id)) {
            ApiResponse::error('Không thể xóa danh mục khi vẫn còn sản phẩm thuộc danh mục này', null, 409);
        }

        $deleted = $this->categoryModel->deleteCategory($id);
        if (!$deleted) {
            ApiResponse::error('Category deletion failed', null, 400);
        }

        ApiResponse::success('Category deleted successfully');
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

    private function validatePayload(array $data): array
    {
        $errors = [];
        $name = trim((string) ($data['name'] ?? ''));

        if ($name === '') {
            $errors['name'] = 'Tên danh mục không được để trống';
        }

        return $errors;
    }
}
