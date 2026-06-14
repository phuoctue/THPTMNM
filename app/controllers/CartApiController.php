<?php

require_once 'app/config/database.php';
require_once 'app/libs/ApiResponse.php';
require_once 'app/models/CartModel.php';
require_once 'app/models/ProductModel.php';

class CartApiController
{
    private CartModel $cartModel;
    private ProductModel $productModel;

    public function __construct()
    {
        $db = (new Database())->getConnection();
        $this->cartModel = new CartModel($db);
        $this->productModel = new ProductModel($db);
    }

    public function index(): void
    {
        ApiResponse::success('Cart retrieved successfully', $this->cartSummary());
    }

    public function store(): void
    {
        $data = $this->requestData();
        $productId = (int) ($data['product_id'] ?? 0);
        $quantity = (int) ($data['quantity'] ?? 1);

        $errors = $this->validateItem($productId, $quantity);
        if (!empty($errors)) {
            ApiResponse::error('Validation failed', $errors, 422);
        }

        $product = $this->productModel->getProductById($productId);
        if (!$product) {
            ApiResponse::error('Product not found', null, 404);
        }

        $this->cartModel->addItem(
            $productId,
            (string) $product->name,
            (int) $product->price,
            (string) ($product->image ?? ''),
            $quantity
        );

        ApiResponse::success('Đã thêm vào giỏ hàng', $this->cartSummary());
    }

    public function update($productId): void
    {
        $productId = (int) $productId;
        $data = $this->requestData();
        $quantity = (int) ($data['quantity'] ?? 0);

        if ($productId <= 0) {
            ApiResponse::error('Validation failed', ['product_id' => 'Sản phẩm không hợp lệ'], 422);
        }
        if ($quantity <= 0) {
            ApiResponse::error('Validation failed', ['quantity' => 'Số lượng sản phẩm phải lớn hơn 0'], 422);
        }

        if (!$this->productModel->getProductById($productId)) {
            ApiResponse::error('Product not found', null, 404);
        }

        $this->cartModel->updateQty($productId, $quantity);

        ApiResponse::success('Cart updated successfully', $this->cartSummary());
    }

    public function destroy($productId): void
    {
        $productId = (int) $productId;
        if ($productId <= 0) {
            ApiResponse::error('Validation failed', ['product_id' => 'Sản phẩm không hợp lệ'], 422);
        }

        $this->cartModel->removeItem($productId);
        ApiResponse::success('Item removed successfully', $this->cartSummary());
    }

    public function clear(): void
    {
        $this->cartModel->clearCart();
        ApiResponse::success('Cart cleared successfully', $this->cartSummary());
    }

    public function total(): void
    {
        ApiResponse::success('Cart total retrieved successfully', [
            'total_qty' => $this->cartModel->getTotalQty(),
            'total_price' => $this->cartModel->getTotalPrice(),
        ]);
    }

    private function cartSummary(): array
    {
        return [
            'items' => array_values($this->cartModel->getCart()),
            'total_qty' => $this->cartModel->getTotalQty(),
            'total_price' => $this->cartModel->getTotalPrice(),
        ];
    }

    private function requestData(): array
    {
        if (!empty($_POST)) {
            return $_POST;
        }

        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);

        return is_array($json) ? $json : [];
    }

    private function validateItem(int $productId, int $quantity): array
    {
        $errors = [];

        if ($productId <= 0) {
            $errors['product_id'] = 'Sản phẩm không hợp lệ';
        }
        if ($quantity <= 0) {
            $errors['quantity'] = 'Số lượng sản phẩm phải lớn hơn 0';
        }

        return $errors;
    }
}
