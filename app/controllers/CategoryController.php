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
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'Tên danh mục không được để trống.']);
                return;
            }
            $error = 'Tên danh mục không được để trống.';
            include 'app/views/category/add.php';
            return;
        }

        $ok = $this->categoryModel->addCategory($name, $description);
        if ($this->isAjax()) {
            $this->jsonResponse([
                'success' => (bool)$ok,
                'message' => $ok ? 'Đã thêm danh mục thành công.' : 'Không thể thêm danh mục.',
            ]);
            return;
        }
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
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'Tên danh mục không được để trống.']);
                return;
            }
            $category = $this->categoryModel->getCategoryById($id);
            $error    = 'Tên danh mục không được để trống.';
            include 'app/views/category/edit.php';
            return;
        }

        $ok = $this->categoryModel->updateCategory($id, $name, $description);
        if ($this->isAjax()) {
            $this->jsonResponse([
                'success' => (bool)$ok,
                'message' => $ok ? 'Đã cập nhật danh mục.' : 'Không thể cập nhật danh mục.',
            ]);
            return;
        }
        header('Location: /Category');
    }

    public function delete($id) {
        $ok = $this->categoryModel->deleteCategory($id);
        if ($this->isAjax()) {
            $this->jsonResponse([
                'success' => (bool)$ok,
                'message' => $ok ? 'Đã xóa danh mục.' : 'Không thể xóa danh mục.',
            ]);
            return;
        }
        header('Location: /Category');
    }

    private function isAjax(): bool {
        return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'
            || (($_POST['_ajax'] ?? '') === '1')
            || (($_GET['_ajax'] ?? '') === '1');
    }

    private function jsonResponse(array $data): void {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
?>
