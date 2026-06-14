<?php

require_once 'app/config/database.php';
require_once 'app/models/CategoryModel.php';

class CategoryController
{
    private CategoryModel $categoryModel;
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->categoryModel = new CategoryModel($this->db);
    }

    public function index(): void
    {
        include 'app/views/category/list.php';
    }

    public function list(): void
    {
        $this->index();
    }

    public function add(): void
    {
        include 'app/views/category/add.php';
    }

    public function edit($id): void
    {
        $categoryId = (int) $id;
        include 'app/views/category/edit.php';
    }

    public function save(): void
    {
        header('Location: /Category');
        exit;
    }

    public function update(): void
    {
        header('Location: /Category');
        exit;
    }

    public function delete($id): void
    {
        header('Location: /Category');
        exit;
    }
}
