<?php
require_once('app/config/database.php');
require_once('app/models/CategoryModel.php');

class CategoryController {

    private $categoryModel;
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
        $this->categoryModel = new CategoryModel($this->db);
    }

    public function index() {
        $categories = $this->categoryModel->getCategories();
        include 'app/views/category/list.php';
    }

    public function list() {
        $this->index();
    }

    public function add() {
        include 'app/views/category/add.php';
    }

    public function save() {
        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($name === '') {
            $error = 'Tên danh mục không được để trống.';
            include 'app/views/category/add.php';
            return;
        }

        $this->categoryModel->addCategory($name, $description);
        header('Location: /Category');
    }

    public function edit($id) {
        $category = $this->categoryModel->getCategoryById($id);
        if (!$category) {
            die('Không tìm thấy danh mục.');
        }
        include 'app/views/category/edit.php';
    }

    public function update() {
        $id          = $_POST['id'] ?? 0;
        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($name === '') {
            $category = $this->categoryModel->getCategoryById($id);
            $error    = 'Tên danh mục không được để trống.';
            include 'app/views/category/edit.php';
            return;
        }

        $this->categoryModel->updateCategory($id, $name, $description);
        header('Location: /Category');
    }

    public function delete($id) {
        $this->categoryModel->deleteCategory($id);
        header('Location: /Category');
    }
}
?>
