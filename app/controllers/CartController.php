<?php

require_once 'app/config/database.php';
require_once 'app/models/CartModel.php';
require_once 'app/models/ProductModel.php';

class CartController
{
    private CartModel $cartModel;
    private ProductModel $productModel;
    private $db;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->db = (new Database())->getConnection();
        $this->cartModel = new CartModel($this->db);
        $this->productModel = new ProductModel($this->db);
    }

    public function index(): void
    {
        include 'app/views/cart/index.php';
    }

    public function checkout(): void
    {
        include 'app/views/cart/checkout.php';
    }

    public function success(): void
    {
        include 'app/views/cart/success.php';
    }

    public function orders(): void
    {
        $orders = $this->cartModel->getAllOrders();
        include 'app/views/cart/orders.php';
    }

    public function orderDetail(int $id): void
    {
        $order = $this->cartModel->getOrderById($id);
        $items = $this->cartModel->getOrderItems($id);
        if (!$order) {
            die('Không tìm thấy đơn hàng.');
        }
        include 'app/views/cart/order_detail.php';
    }
}
