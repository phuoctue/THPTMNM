<?php

require_once 'app/config/database.php';
require_once 'app/libs/ApiResponse.php';
require_once 'app/libs/AuthMiddleware.php';
require_once 'app/models/CartModel.php';

class OrderApiController
{
    private CartModel $cartModel;

    public function __construct()
    {
        $db = (new Database())->getConnection();
        $this->cartModel = new CartModel($db);
    }

    public function index(): void
    {
        $payload = AuthMiddleware::authenticate();

        if (($payload['role'] ?? '') === 'admin') {
            ApiResponse::success('Orders retrieved successfully', $this->attachItemsCount($this->cartModel->getAllOrders()));
        }

        ApiResponse::success('Orders retrieved successfully', $this->cartModel->getOrdersByCustomerEmail((string) ($payload['email'] ?? '')));
    }

    public function show($id): void
    {
        $payload = AuthMiddleware::authenticate();
        $id = (int) $id;
        if ($id <= 0) {
            ApiResponse::error('Validation failed', ['id' => 'Đơn hàng không hợp lệ'], 422);
        }

        $order = $this->cartModel->getOrderById($id);
        if (!$order) {
            ApiResponse::error('Order not found', null, 404);
        }

        if (!$this->canAccessOrder($order, $payload)) {
            ApiResponse::error('Forbidden', null, 403);
        }

        ApiResponse::success('Order retrieved successfully', [
            'order' => $order,
            'items' => $this->cartModel->getOrderItems($id),
        ]);
    }

    public function store(): void
    {
        $payload = $this->requireAuth();
        $data = $this->requestData();
        [$cartItems, $fromSession] = $this->resolveOrderItems($data);
        $orderPayload = $this->resolveCustomerPayload($data, $payload);
        $errors = $this->validatePayload($orderPayload, $cartItems);

        if (!empty($errors)) {
            ApiResponse::error('Validation failed', $errors, 422);
        }

        $orderId = $this->cartModel->placeOrderFromItems(
            $orderPayload['customer_name'],
            $orderPayload['customer_phone'],
            $orderPayload['customer_email'],
            $orderPayload['customer_address'],
            $orderPayload['note'],
            $orderPayload['payment_method'],
            $cartItems
        );

        if (!$orderId) {
            ApiResponse::error('Order creation failed', null, 400);
        }

        if ($fromSession) {
            $this->cartModel->clearCart();
        }

        ApiResponse::success('Order created successfully', [
            'order_id' => $orderId,
        ], 201);
    }

    public function update($id): void
    {
        $this->requireAdmin();
        $id = (int) $id;
        if ($id <= 0) {
            ApiResponse::error('Validation failed', ['id' => 'Đơn hàng không hợp lệ'], 422);
        }

        $data = $this->requestData();
        $status = trim((string) ($data['status'] ?? ''));
        $paymentStatus = trim((string) ($data['payment_status'] ?? ''));

        if ($status !== '') {
            $this->cartModel->updateOrderStatus($id, $status);
        }

        if ($paymentStatus !== '') {
            if (!$this->cartModel->updateOrderPaymentStatus($id, $paymentStatus)) {
                ApiResponse::error('Validation failed', ['payment_status' => 'Trạng thái thanh toán không hợp lệ hoặc đơn hàng đã thanh toán'], 422);
            }
        }

        ApiResponse::success('Order updated successfully');
    }

    public function destroy($id): void
    {
        $this->requireAdmin();
        $id = (int) $id;
        if ($id <= 0) {
            ApiResponse::error('Validation failed', ['id' => 'Đơn hàng không hợp lệ'], 422);
        }

        $deleted = $this->cartModel->deleteOrder($id);
        if (!$deleted) {
            ApiResponse::error('Order deletion failed', null, 400);
        }

        ApiResponse::success('Order deleted successfully');
    }

    public function cancel($id): void
    {
        $payload = $this->requireAuth();
        $id = (int) $id;
        $order = $this->cartModel->getOrderById($id);
        if (!$order) {
            ApiResponse::error('Order not found', null, 404);
        }

        if (!$this->canAccessOrder($order, $payload)) {
            ApiResponse::error('Forbidden', null, 403);
        }

        $this->cartModel->updateOrderStatus($id, 'cancelled');
        ApiResponse::success('Order cancelled successfully');
    }

    public function payment($id): void
    {
        $payload = $this->requireAuth();
        $id = (int) $id;
        $order = $this->cartModel->getOrderById($id);
        if (!$order) {
            ApiResponse::error('Order not found', null, 404);
        }

        if (!$this->canAccessOrder($order, $payload)) {
            ApiResponse::error('Forbidden', null, 403);
        }

        if (($order->payment_status ?? 'unpaid') === 'paid') {
            ApiResponse::error('Order already paid', null, 409);
        }

        $data = $this->requestData();
        $method = trim((string) ($data['payment_method'] ?? ($order->payment_method ?? 'cod')));
        if (!in_array($method, ['cod', 'banking'], true)) {
            $method = 'cod';
        }

        $paymentStatus = $method === 'cod' ? 'unpaid' : 'paid';
        $this->cartModel->updateOrderPaymentStatus($id, $paymentStatus);

        ApiResponse::success('Payment updated successfully', [
            'order_id' => $id,
            'payment_status' => $paymentStatus,
            'payment_method' => $method,
        ]);
    }

    private function requireAuth(): array
    {
        return AuthMiddleware::authenticate();
    }

    private function canAccessOrder(object $order, array $payload): bool
    {
        if (($payload['role'] ?? '') === 'admin') {
            return true;
        }

        return strtolower(trim((string) ($order->customer_email ?? ''))) === strtolower(trim((string) ($payload['email'] ?? '')));
    }

    private function resolveCustomerPayload(array $data, array $payload): array
    {
        return [
            'customer_name' => trim((string) ($data['customer_name'] ?? ($payload['name'] ?? ''))),
            'customer_phone' => trim((string) ($data['customer_phone'] ?? '')),
            'customer_email' => trim((string) ($data['customer_email'] ?? ($payload['email'] ?? ''))),
            'customer_address' => trim((string) ($data['customer_address'] ?? '')),
            'note' => trim((string) ($data['note'] ?? '')),
            'payment_method' => in_array(($data['payment_method'] ?? 'cod'), ['cod', 'banking'], true)
                ? (string) ($data['payment_method'] ?? 'cod')
                : 'cod',
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
        if (empty($items)) {
            $errors['items'] = 'Giỏ hàng trống';
        }

        return $errors;
    }

    private function resolveOrderItems(array $data): array
    {
        if (!empty($data['items']) && is_array($data['items'])) {
            return [array_values($data['items']), false];
        }

        $cart = $this->cartModel->getCart();
        if (!empty($cart)) {
            return [array_values($cart), true];
        }

        return [[], false];
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
