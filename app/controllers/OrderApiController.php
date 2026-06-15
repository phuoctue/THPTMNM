<?php

require_once 'app/config/database.php';
require_once 'app/libs/ApiRequest.php';
require_once 'app/libs/ApiResponse.php';
require_once 'app/middleware/AuthMiddleware.php';
require_once 'app/models/CartModel.php';
require_once 'app/models/PaymentModel.php';

class OrderApiController
{
    private CartModel $cartModel;
    private PaymentModel $paymentModel;

    public function __construct()
    {
        $db = (new Database())->getConnection();
        $this->cartModel = new CartModel($db);
        $this->paymentModel = new PaymentModel($db);
    }

    public function index(): void
    {
        $user = AuthMiddleware::user();

        if (($user['role'] ?? '') === 'admin') {
            $orders = $this->cartModel->getAllOrders();
            ApiResponse::success('Orders retrieved successfully', $this->attachItemsCount($orders));
        }

        $orders = $this->cartModel->getOrdersByCustomerEmail((string) ($user['email'] ?? ''));
        ApiResponse::success('Orders retrieved successfully', $orders);
    }

    public function show($id): void
    {
        $orderId = (int) $id;
        if ($orderId <= 0) {
            ApiResponse::error('Validation failed', ['id' => 'Đơn hàng không hợp lệ'], 422);
        }

        $order = $this->cartModel->getOrderById($orderId);
        if (!$order) {
            ApiResponse::error('Order not found', null, 404);
        }

        $user = AuthMiddleware::user();
        if (($user['role'] ?? '') !== 'admin') {
            $orderEmail = strtolower(trim((string) ($order->customer_email ?? '')));
            $userEmail = strtolower(trim((string) ($user['email'] ?? '')));
            if ($orderEmail === '' || $orderEmail !== $userEmail) {
                ApiResponse::error('Forbidden', null, 403);
            }
        }

        ApiResponse::success('Order retrieved successfully', [
            'order' => $order,
            'items' => $this->cartModel->getOrderItems($orderId),
            'payment' => $this->paymentModel->getPaymentByOrderId($orderId),
        ]);
    }

    public function store(): void
    {
        $user = AuthMiddleware::user();
        $data = ApiRequest::body();
        [$cartItems, $usedSessionCart, $hasExplicitItems] = $this->resolveOrderItems($data);
        $payload = $this->resolveCustomerPayload($data, $user);
        $errors = $this->validatePayload($payload, $cartItems);

        if (!empty($errors)) {
            ApiResponse::error('Validation failed', $errors, 422);
        }

        $orderId = $this->cartModel->placeOrderFromItems(
            $payload['customer_name'],
            $payload['customer_phone'],
            $payload['customer_email'],
            $payload['customer_address'],
            $payload['note'],
            $payload['payment_method'],
            $cartItems
        );

        if (!$orderId && !$hasExplicitItems) {
            $orderId = $this->cartModel->placeOrder(
                $payload['customer_name'],
                $payload['customer_phone'],
                $payload['customer_email'],
                $payload['customer_address'],
                $payload['note'],
                $payload['payment_method']
            );
        }

        if (!$orderId) {
            ApiResponse::error('Order creation failed', null, 400);
        }

        if ($usedSessionCart) {
            $this->cartModel->clearCart();
        }

        if (!empty($data['payment_method']) && strtolower((string) $data['payment_method']) !== 'cod') {
            $paymentId = $this->paymentModel->create([
                'order_id' => $orderId,
                'user_id' => (int) $user['id'],
                'method' => strtolower((string) $data['payment_method']),
                'amount' => (int) $this->cartModel->getTotalPrice(),
                'provider' => strtolower((string) $data['payment_method']),
                'transaction_code' => 'MOCK-' . strtoupper(bin2hex(random_bytes(4))),
                'status' => 'pending',
                'note' => 'Mock payment created from order checkout',
            ]);

            ApiResponse::success('Order created successfully', [
                'order_id' => $orderId,
                'payment_id' => $paymentId,
                'payment_status' => 'pending',
            ], 201);
        }

        ApiResponse::success('Order created successfully', [
            'order_id' => $orderId,
        ], 201);
    }

    public function cancel($id): void
    {
        $user = AuthMiddleware::user();
        $orderId = (int) $id;
        if ($orderId <= 0) {
            ApiResponse::error('Validation failed', ['id' => 'Đơn hàng không hợp lệ'], 422);
        }

        $order = $this->cartModel->getOrderById($orderId);
        if (!$order) {
            ApiResponse::error('Order not found', null, 404);
        }

        if (($user['role'] ?? '') !== 'admin') {
            $orderEmail = strtolower(trim((string) ($order->customer_email ?? '')));
            $userEmail = strtolower(trim((string) ($user['email'] ?? '')));
            if ($orderEmail === '' || $orderEmail !== $userEmail) {
                ApiResponse::error('Forbidden', null, 403);
            }
        }

        if (!in_array(($order->status ?? 'pending'), ['pending', 'confirmed'], true)) {
            ApiResponse::error('Only pending or confirmed orders can be cancelled', null, 409);
        }

        $this->cartModel->updateOrderStatus($orderId, 'cancelled');
        ApiResponse::success('Order cancelled successfully');
    }

    public function status($id): void
    {
        AuthMiddleware::admin();

        $orderId = (int) $id;
        if ($orderId <= 0) {
            ApiResponse::error('Validation failed', ['id' => 'Đơn hàng không hợp lệ'], 422);
        }

        $data = ApiRequest::body();
        $status = trim((string) ($data['status'] ?? ''));
        if (!in_array($status, ['pending', 'confirmed', 'shipping', 'done', 'cancelled'], true)) {
            ApiResponse::error('Validation failed', ['status' => 'Trạng thái không hợp lệ'], 422);
        }

        $this->cartModel->updateOrderStatus($orderId, $status);
        ApiResponse::success('Order status updated successfully');
    }

    public function update($id): void
    {
        $this->status($id);
    }

    public function destroy($id): void
    {
        AuthMiddleware::admin();

        $orderId = (int) $id;
        if ($orderId <= 0) {
            ApiResponse::error('Validation failed', ['id' => 'Đơn hàng không hợp lệ'], 422);
        }

        $deleted = $this->cartModel->deleteOrder($orderId);
        if (!$deleted) {
            ApiResponse::error('Order deletion failed', null, 400);
        }

        ApiResponse::success('Order deleted successfully');
    }

    private function resolveCustomerPayload(array $data, array $user): array
    {
        $paymentMethod = strtolower((string) ($data['payment_method'] ?? 'cod'));
        if (!in_array($paymentMethod, ['cod', 'bank_transfer', 'wallet', 'banking', 'e_wallet', 'ewallet'], true)) {
            $paymentMethod = 'cod';
        }

        return [
            'customer_name' => trim((string) ($data['customer_name'] ?? ($user['full_name'] ?? ''))),
            'customer_phone' => trim((string) ($data['customer_phone'] ?? ($user['phone'] ?? ''))),
            'customer_email' => trim((string) ($data['customer_email'] ?? ($user['email'] ?? ''))),
            'customer_address' => trim((string) ($data['customer_address'] ?? ($user['address'] ?? ''))),
            'note' => trim((string) ($data['note'] ?? '')),
            'payment_method' => $paymentMethod,
        ];
    }

    private function validatePayload(array $payload, array $items): array
    {
        $errors = [];

        if ($payload['customer_name'] === '') {
            $errors['customer_name'] = 'Vui lòng nhập tên khách hàng';
        }
        if ($payload['customer_phone'] === '') {
            $errors['customer_phone'] = 'Vui lòng nhập số điện thoại';
        }
        if ($payload['customer_address'] === '') {
            $errors['customer_address'] = 'Vui lòng nhập địa chỉ';
        }
        if ($payload['customer_email'] !== '' && !filter_var($payload['customer_email'], FILTER_VALIDATE_EMAIL)) {
            $errors['customer_email'] = 'Email không hợp lệ';
        }
        if (empty($items) && empty($_SESSION['cart'])) {
            $errors['items'] = 'Giỏ hàng trống';
        }

        return $errors;
    }

    private function resolveOrderItems(array $data): array
    {
        if (!empty($data['items']) && is_array($data['items'])) {
            return [$data['items'], false, true];
        }

        if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
            return [array_values($_SESSION['cart']), true, false];
        }

        return [[], false, false];
    }

    private function attachItemsCount(array $orders): array
    {
        $result = [];
        foreach ($orders as $order) {
            $orderArray = is_object($order) ? (array) $order : (array) $order;
            if (isset($orderArray['id'])) {
                $orderArray['items'] = $this->cartModel->getOrderItems((int) $orderArray['id']);
            }
            $result[] = $orderArray;
        }

        return $result;
    }
}
