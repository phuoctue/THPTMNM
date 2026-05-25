<?php
require_once('app/config/database.php');
require_once('app/models/ProductModel.php');
require_once('app/models/CategoryModel.php');

class ProductController {

    private $productModel;
    private $db;

    public function __construct() {
        $this->db           = (new Database())->getConnection();
        $this->productModel = new ProductModel($this->db);
    }

    public function index() {
        $search      = trim($_GET['search'] ?? '');
        $perPage     = 8;
        $currentPage = max(1, (int)($_GET['page'] ?? 1));
        $offset      = ($currentPage - 1) * $perPage;

        $total      = $this->productModel->countProducts($search);
        $totalPages = (int) ceil($total / $perPage);

        // Giới hạn currentPage không vượt quá totalPages
        if ($totalPages > 0 && $currentPage > $totalPages) {
            $currentPage = $totalPages;
            $offset      = ($currentPage - 1) * $perPage;
        }

        $products = $this->productModel->getProducts($search, $perPage, $offset);
        $isManagement = true;

        include 'app/views/product/list.php';
    }

    public function show($id) {
        $product = $this->productModel->getProductById($id);
        if (!$product) die('Không tìm thấy sản phẩm.');
        include 'app/views/product/show.php';
    }

    public function add() {
        $categories = (new CategoryModel($this->db))->getCategories();
        include 'app/views/product/add.php';
    }

    public function save() {
        $name        = trim($_POST['name']        ?? '');
        $description = trim($_POST['description'] ?? '');
        $price       = $_POST['price']       ?? 0;
        $category_id = $_POST['category_id'] ?? 0;

        if ($name === '' || (float)$price < 0) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'Dữ liệu sản phẩm không hợp lệ.']);
                return;
            }
            header('Location: /Product/add');
            return;
        }

        $image = $this->handleImageUpload('');

        $ok = $this->productModel->addProduct($name, $description, $price, $category_id, $image);
        if ($this->isAjax()) {
            $this->jsonResponse([
                'success' => (bool)$ok,
                'message' => $ok ? 'Đã thêm sản phẩm thành công.' : 'Không thể thêm sản phẩm.',
            ]);
            return;
        }
        header('Location: /Product');
    }

    public function edit($id) {
        $product    = $this->productModel->getProductById($id);
        $categories = (new CategoryModel($this->db))->getCategories();
        if (!$product) die('Không tìm thấy sản phẩm.');
        include 'app/views/product/edit.php';
    }

    public function update() {
        $id          = $_POST['id']          ?? 0;
        $name        = trim($_POST['name']        ?? '');
        $description = trim($_POST['description'] ?? '');
        $price       = $_POST['price']       ?? 0;
        $category_id = $_POST['category_id'] ?? 0;

        if ((int)$id <= 0 || $name === '' || (float)$price < 0) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'Dữ liệu cập nhật không hợp lệ.']);
                return;
            }
            header('Location: /Product');
            return;
        }

        $image = $this->handleImageUpload($_POST['existing_image'] ?? '');

        $ok = $this->productModel->updateProduct($id, $name, $description, $price, $category_id, $image);
        if ($this->isAjax()) {
            $this->jsonResponse([
                'success' => (bool)$ok,
                'message' => $ok ? 'Đã cập nhật sản phẩm.' : 'Không thể cập nhật sản phẩm.',
            ]);
            return;
        }
        header('Location: /Product');
    }

    public function delete($id) {
        $ok = $this->productModel->deleteProduct($id);
        if ($this->isAjax()) {
            $this->jsonResponse([
                'success' => (bool)$ok,
                'message' => $ok ? 'Đã xóa sản phẩm.' : 'Không thể xóa sản phẩm.',
            ]);
            return;
        }
        header('Location: /Product');
    }

    // ── Xử lý upload ảnh ──────────────────────────────────────────────────────
    private function handleImageUpload($existingImage) {
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            return $existingImage;
        }

        $file     = $_FILES['image'];
        $allowed  = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $mimeType = mime_content_type($file['tmp_name']);

        if (!in_array($mimeType, $allowed)) {
            return $existingImage;
        }

        $ext        = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newName    = uniqid('img_', true) . '.' . strtolower($ext);
        $target_dir = 'uploads/';

        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        $target = $target_dir . $newName;
        move_uploaded_file($file['tmp_name'], $target);

        return $target;
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
