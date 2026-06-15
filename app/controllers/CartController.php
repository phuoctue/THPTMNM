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

    public function index(): void {
        $cartItems  = $this->cartModel->getCart();
        $totalPrice = $this->cartModel->getTotalPrice();
        include 'app/views/cart/index.php';
    }

    public function add(): void {
        $productId = (int)($_POST['product_id'] ?? 0);
        $qty       = max(1, (int)($_POST['quantity'] ?? 1));
        $isAjax    = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'
                     || (($_POST['_ajax'] ?? '') === '1');

        $product = $this->productModel->getProductById($productId);
        if (!$product) {
            if ($isAjax) {
                $this->jsonResponse(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
                return;
            }

            $_SESSION['cart_error'] = 'Sản phẩm không tồn tại hoặc đã bị xóa.';
            $redirectTo = $_POST['redirect_to'] ?? ($_SERVER['HTTP_REFERER'] ?? '/Product');
            if (!is_string($redirectTo) || $redirectTo === '' || strpos($redirectTo, '/') !== 0 || strpos($redirectTo, '//') === 0) {
                $redirectTo = '/Product';
            }
            header('Location: ' . $redirectTo);
            exit;
            return;
        }

        $this->cartModel->addItem(
            $productId,
            (string)($product['name'] ?? ''),
            (int)($product['price'] ?? 0),
            (string)($product['image'] ?? ''),
            $qty
        );

        if ($isAjax) {
            $this->jsonResponse([
                'success'   => true,
                'message'   => 'Đã thêm vào giỏ hàng!',
                'cartCount' => $this->cartModel->getTotalQty(),
            ]);
            return;
        }

        $redirectTo = $_POST['redirect_to'] ?? ($_SERVER['HTTP_REFERER'] ?? '/Product');
        if (!is_string($redirectTo) || $redirectTo === '' || strpos($redirectTo, '/') !== 0 || strpos($redirectTo, '//') === 0) {
            $redirectTo = '/Product';
        }

        header('Location: ' . $redirectTo);
        exit;
    }

    public function update(): void {
        $productId = (int)($_POST['product_id'] ?? 0);
        $qty       = (int)($_POST['quantity']   ?? 0);
        $this->cartModel->updateQty($productId, $qty);

        $isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'
                  || (($_POST['_ajax'] ?? '') === '1');
        if ($isAjax) {
            $cart = $this->cartModel->getCart();
            $itemTotal = isset($cart[$productId]) ? ((int)$cart[$productId]['price'] * (int)$cart[$productId]['quantity']) : 0;
            $this->jsonResponse([
                'success'    => true,
                'cartCount'  => $this->cartModel->getTotalQty(),
                'totalPrice' => $this->cartModel->getTotalPrice(),
                'itemTotal'  => $itemTotal,
                'removed'    => !isset($cart[$productId]),
            ]);
            return;
        }

        header('Location: /Cart');
        exit;
    }

    public function remove(): void {
        $productId = (int)($_POST['product_id'] ?? 0);
        $this->cartModel->removeItem($productId);

        $isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'
                  || (($_POST['_ajax'] ?? '') === '1');
        if ($isAjax) {
            $this->jsonResponse([
                'success'    => true,
                'cartCount'  => $this->cartModel->getTotalQty(),
                'totalPrice' => $this->cartModel->getTotalPrice(),
            ]);
            return;
        }

        header('Location: /Cart');
        exit;
    }

    public function checkout(): void {
        $cartItems  = $this->cartModel->getCart();
        $totalPrice = $this->cartModel->getTotalPrice();
        $nextOrderId = $this->cartModel->getNextOrderId();
        if (empty($cartItems)) {
            header('Location: /Cart');
            return;
        }
        include 'app/views/cart/checkout.php';
    }

    public function placeOrder(): void {
        $name          = trim($_POST['customer_name']    ?? '');
        $phone         = trim($_POST['customer_phone']   ?? '');
        $email         = trim($_POST['customer_email']   ?? '');
        $address       = trim($_POST['customer_address'] ?? '');
        $note          = trim($_POST['note']             ?? '');
        $paymentMethod = $_POST['payment_method']        ?? 'cod';

        if (!$name || !$phone || !$address) {
            $_SESSION['errors'] = ['Vui lòng điền đầy đủ thông tin bắt buộc.'];
            $_SESSION['old_data'] = compact('name', 'phone', 'email', 'address', 'note', 'paymentMethod');
            header('Location: /Cart/checkout');
            return;
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['errors'] = ['Email không hợp lệ.'];
            $_SESSION['old_data'] = compact('name', 'phone', 'email', 'address', 'note', 'paymentMethod');
            header('Location: /Cart/checkout');
            return;
        }

        if (!in_array($paymentMethod, ['cod', 'banking'], true)) {
            $paymentMethod = 'cod';
        }

        $orderId = $this->cartModel->placeOrder($name, $phone, $email, $address, $note, $paymentMethod);
        if ($orderId) {
            header("Location: /Cart/success?order_id={$orderId}");
        } else {
            $_SESSION['errors'] = ['Giỏ hàng trống, không thể đặt hàng.'];
            header('Location: /Cart/checkout');
        }
    }

    public function success(): void {
        $orderId = (int)($_GET['order_id'] ?? 0);
        $order   = $this->cartModel->getOrderById($orderId);
        $items   = $this->cartModel->getOrderItems($orderId);
        include 'app/views/cart/success.php';
    }

    public function orders(): void {
        $orders = $this->cartModel->getAllOrders();
        include 'app/views/cart/orders.php';
    }

    public function orderDetail(int $id): void {
        $order = $this->cartModel->getOrderById($id);
        $items = $this->cartModel->getOrderItems($id);
        if (!$order) die('Không tìm thấy đơn hàng.');
        include 'app/views/cart/order_detail.php';
    }

    public function updateStatus(): void {
        $id     = (int)($_POST['order_id'] ?? 0);
        $status = $_POST['status'] ?? 'pending';
        $this->cartModel->updateOrderStatus($id, $status);
        header("Location: /Cart/orderDetail/{$id}");
    }

    private function jsonResponse(array $data): void {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
?>
