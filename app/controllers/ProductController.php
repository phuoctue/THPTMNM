<?php

require_once 'app/config/database.php';
require_once 'app/models/ProductModel.php';
require_once 'app/models/CategoryModel.php';

class ProductController
{
    private ProductModel $productModel;
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->productModel = new ProductModel($this->db);
    }

    public function index(): void
    {
        $isManagement = true;
        include 'app/views/product/list.php';
    }

    public function show($id): void
    {
        $productId = (int) $id;
        include 'app/views/product/show.php';
    }

    public function add(): void
    {
        include 'app/views/product/add.php';
    }

    public function edit($id): void
    {
        $productId = (int) $id;
        include 'app/views/product/edit.php';
    }

    public function save(): void
    {
        header('Location: /Product');
        exit;
    }

    public function update(): void
    {
        header('Location: /Product');
        exit;
    }

    public function delete($id): void
    {
        header('Location: /Product');
        exit;
    }
}
