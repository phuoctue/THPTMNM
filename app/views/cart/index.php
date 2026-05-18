<?php
require_once('app/config/database.php');
require_once('app/models/CartModel.php');
require_once('app/models/ProductModel.php');

class CartController {

    private CartModel $cartModel;
    private ProductModel $productModel;
    private $db;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->db           = (new Database())->getConnection();
        $this->cartModel    = new CartModel($this->db);
        $this->productModel = new ProductModel($this->db);
    }

    // ── Xem giỏ hàng ──────────────────────────────────────────────────────
    public function index(): void {
        $cartItems  = $this->cartModel->getCart();
        $totalPrice = $this->cartModel->getTotalPrice();
        include 'app/views/cart/index.php';
    }

    // ── Thêm vào giỏ ──────────────────────────────────────────────────────
    public function add(): void {
        $productId = (int)($_POST['product_id'] ?? 0);
        $qty       = max(1, (int)($_POST['quantity'] ?? 1));

        $product = $this->productModel->getProductById($productId);
        if (!$product) {
            $this->jsonResponse(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
            return;
        }

        $this->cartModel->addItem(
            $productId,
            $product->name,
            (int)$product->price,
            $product->image ?? '',
            $qty
        );

        $this->jsonResponse([
            'success'   => true,
            'message'   => 'Đã thêm vào giỏ hàng!',
            'cartCount' => $this->cartModel->getTotalQty(),
        ]);
    }

    // ── Cập nhật số lượng ─────────────────────────────────────────────────
    public function update(): void {
        $productId = (int)($_POST['product_id'] ?? 0);
        $qty       = (int)($_POST['quantity']   ?? 0);
        $this->cartModel->updateQty($productId, $qty);

        $this->jsonResponse([
            'success'    => true,
            'cartCount'  => $this->cartModel->getTotalQty(),
            'totalPrice' => $this->cartModel->getTotalPrice(),
            'itemTotal'  => isset($this->cartModel->getCart()[$productId])
                            ? $this->cartModel->getCart()[$productId]['price']
                              * $this->cartModel->getCart()[$productId]['quantity']
                            : 0,
        ]);
    }

    // ── Xoá một item ──────────────────────────────────────────────────────
    public function remove(): void {
        $productId = (int)($_POST['product_id'] ?? 0);
        $this->cartModel->removeItem($productId);

        $this->jsonResponse([
            'success'    => true,
            'cartCount'  => $this->cartModel->getTotalQty(),
            'totalPrice' => $this->cartModel->getTotalPrice(),
        ]);
    }

    // ── Trang checkout ────────────────────────────────────────────────────
    public function checkout(): void {
        $cartItems  = $this->cartModel->getCart();
        $totalPrice = $this->cartModel->getTotalPrice();
        if (empty($cartItems)) {
            header('Location: /Cart');
            return;
        }
        include 'app/views/cart/checkout.php';
    }

    // ── Đặt hàng ──────────────────────────────────────────────────────────
    public function placeOrder(): void {
        $name    = trim($_POST['customer_name']    ?? '');
        $phone   = trim($_POST['customer_phone']   ?? '');
        $address = trim($_POST['customer_address'] ?? '');
        $note    = trim($_POST['note']             ?? '');

        if (!$name || !$phone || !$address) {
            $_SESSION['checkout_error'] = 'Vui lòng điền đầy đủ thông tin.';
            header('Location: /Cart/checkout');
            return;
        }

        $orderId = $this->cartModel->placeOrder($name, $phone, $address, $note);
        if ($orderId) {
            header("Location: /Cart/success?order_id={$orderId}");
        } else {
            $_SESSION['checkout_error'] = 'Giỏ hàng trống, không thể đặt hàng.';
            header('Location: /Cart/checkout');
        }
    }

    // ── Trang đặt hàng thành công ─────────────────────────────────────────
    public function success(): void {
        $orderId = (int)($_GET['order_id'] ?? 0);
        $order   = $this->cartModel->getOrderById($orderId);
        $items   = $this->cartModel->getOrderItems($orderId);
        include 'app/views/cart/success.php';
    }

    // ── Danh sách đơn hàng (admin) ────────────────────────────────────────
    public function orders(): void {
        $orders = $this->cartModel->getAllOrders();
        include 'app/views/cart/orders.php';
    }

    // ── Chi tiết đơn hàng (admin) ─────────────────────────────────────────
    public function orderDetail(int $id): void {
        $order = $this->cartModel->getOrderById($id);
        $items = $this->cartModel->getOrderItems($id);
        if (!$order) die('Không tìm thấy đơn hàng.');
        include 'app/views/cart/order_detail.php';
    }

    // ── Cập nhật trạng thái đơn (admin) ──────────────────────────────────
    public function updateStatus(): void {
        $id     = (int)($_POST['order_id'] ?? 0);
        $status = $_POST['status'] ?? 'pending';
        $this->cartModel->updateOrderStatus($id, $status);
        header("Location: /Cart/orderDetail/{$id}");
    }

    // ── Helper JSON ───────────────────────────────────────────────────────
    private function jsonResponse(array $data): void {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
?>