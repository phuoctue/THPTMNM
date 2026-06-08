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
        $data = $this->getJsonInput();
        $productId = (int) ($data['product_id'] ?? $_POST['product_id'] ?? 0);
        $quantity = max(1, (int) ($data['quantity'] ?? $_POST['quantity'] ?? 1));

        if ($productId <= 0) {
            ApiResponse::error('Validation failed', ['product_id' => 'Sản phẩm không hợp lệ'], 422);
        }

        $product = $this->productModel->getProductById($productId);
        if (!$product) {
            ApiResponse::error('Product not found', null, 404);
        }

        $this->cartModel->addItem(
            $productId,
            $product->name,
            (int) $product->price,
            $product->image ?? '',
            $quantity
        );

        ApiResponse::success('Đã thêm vào giỏ hàng', $this->cartSummary());
    }

    public function update($productId): void
    {
        $productId = (int) $productId;
        $data = $this->getJsonInput();
        $quantity = (int) ($data['quantity'] ?? $_POST['quantity'] ?? 0);

        if ($productId <= 0) {
            ApiResponse::error('Validation failed', ['product_id' => 'Sản phẩm không hợp lệ'], 422);
        }

        $this->cartModel->updateQty($productId, $quantity);

        ApiResponse::success('Giỏ hàng đã được cập nhật', $this->cartSummary());
    }

    public function destroy($productId): void
    {
        $productId = (int) $productId;
        if ($productId <= 0) {
            ApiResponse::error('Validation failed', ['product_id' => 'Sản phẩm không hợp lệ'], 422);
        }

        $this->cartModel->removeItem($productId);

        ApiResponse::success('Đã xóa sản phẩm khỏi giỏ hàng', $this->cartSummary());
    }

    public function clear(): void
    {
        $this->cartModel->clearCart();
        ApiResponse::success('Giỏ hàng đã được xóa', $this->cartSummary());
    }

    private function cartSummary(): array
    {
        return [
            'items' => $this->cartModel->getCart(),
            'total_qty' => $this->cartModel->getTotalQty(),
            'total_price' => $this->cartModel->getTotalPrice(),
        ];
    }

    private function getJsonInput(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        return is_array($data) ? $data : [];
    }
}
