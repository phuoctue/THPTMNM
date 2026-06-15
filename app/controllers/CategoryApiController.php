<?php

require_once 'app/config/database.php';
require_once 'app/libs/ApiResponse.php';
require_once 'app/libs/ApiRequest.php';
require_once 'app/middleware/AuthMiddleware.php';
require_once 'app/models/CategoryModel.php';
require_once 'app/models/ProductModel.php';

class CategoryApiController
{
    private CategoryModel $categoryModel;
    private ProductModel $productModel;

    public function __construct()
    {
        $db = (new Database())->getConnection();
        $this->categoryModel = new CategoryModel($db);
        $this->productModel = new ProductModel($db);
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
        AuthMiddleware::admin();

        $data = ApiRequest::body();
        $errors = $this->validatePayload($data);
        if (!empty($errors)) {
            ApiResponse::error('Validation failed', $errors, 422);
        }

        $id = $this->categoryModel->create([
            'name' => trim((string) $data['name']),
            'description' => trim((string) ($data['description'] ?? '')),
        ]);

        if (!$id) {
            ApiResponse::error('Category creation failed', null, 400);
        }

        ApiResponse::success('Category created successfully', [
            'id' => $id,
            'category' => $this->categoryModel->getCategoryById($id),
        ], 201);
    }

    public function update($id): void
    {
        AuthMiddleware::admin();

        $categoryId = (int) $id;
        if (!$this->categoryModel->exists($categoryId)) {
            ApiResponse::error('Category not found', null, 404);
        }

        $data = ApiRequest::body();
        $errors = $this->validatePayload($data);
        if (!empty($errors)) {
            ApiResponse::error('Validation failed', $errors, 422);
        }

        if (!$this->categoryModel->update($categoryId, [
            'name' => trim((string) $data['name']),
            'description' => trim((string) ($data['description'] ?? '')),
        ])) {
            ApiResponse::error('Category update failed', null, 400);
        }

        ApiResponse::success('Category updated successfully', [
            'category' => $this->categoryModel->getCategoryById($categoryId),
        ]);
    }

    public function destroy($id): void
    {
        AuthMiddleware::admin();

        $categoryId = (int) $id;
        if (!$this->categoryModel->exists($categoryId)) {
            ApiResponse::error('Category not found', null, 404);
        }

        if ($this->categoryModel->countProducts($categoryId) > 0) {
            ApiResponse::error('Cannot delete category because it still has products', null, 409);
        }

        if (!$this->categoryModel->delete($categoryId)) {
            ApiResponse::error('Category deletion failed', null, 400);
        }

        ApiResponse::success('Category deleted successfully');
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
