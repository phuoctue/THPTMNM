<?php

require_once 'app/config/database.php';
require_once 'app/libs/ApiResponse.php';
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
        $categories = $this->categoryModel->getCategories();
        ApiResponse::success('Categories retrieved successfully', $categories);
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
        $data = $this->getJsonInput();
        $errors = $this->validatePayload($data);

        if (!empty($errors)) {
            ApiResponse::error('Validation failed', $errors, 422);
        }

        $created = $this->categoryModel->addCategory(
            trim((string) $data['name']),
            trim((string)($data['description'] ?? ''))
        );

        if (!$created) {
            ApiResponse::error('Category creation failed', null, 400);
        }

        ApiResponse::success('Category created successfully', null, 201);
    }

    public function update($id): void
    {
        $category = $this->categoryModel->getCategoryById((int) $id);
        if (!$category) {
            ApiResponse::error('Category not found', null, 404);
        }

        $data = $this->getJsonInput();
        $errors = $this->validatePayload($data);

        if (!empty($errors)) {
            ApiResponse::error('Validation failed', $errors, 422);
        }

        $updated = $this->categoryModel->updateCategory(
            (int) $id,
            trim((string) $data['name']),
            trim((string)($data['description'] ?? ''))
        );

        if (!$updated) {
            ApiResponse::error('Category update failed', null, 400);
        }

        ApiResponse::success('Category updated successfully');
    }

    public function destroy($id): void
    {
        $deleted = $this->categoryModel->deleteCategory((int) $id);

        if (!$deleted) {
            ApiResponse::error('Category deletion failed', null, 400);
        }

        ApiResponse::success('Category deleted successfully');
    }

    private function getJsonInput(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        return is_array($data) ? $data : [];
    }

    private function validatePayload(array $data): array
    {
        $errors = [];
        $name = trim((string)($data['name'] ?? ''));

        if ($name === '') {
            $errors['name'] = 'Tên danh mục không được để trống';
        }

        return $errors;
    }
}
