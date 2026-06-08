<?php

require_once 'app/config/database.php';
require_once 'app/libs/ApiResponse.php';
require_once 'app/libs/AuthHelper.php';
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
        if (!AuthHelper::isLoggedIn()) {
            ApiResponse::error('Unauthenticated', null, 401);
        }

        $user = AuthHelper::getCurrentUser();
        if (AuthHelper::isAdmin()) {
            $orders = $this->cartModel->getAllOrders();
            ApiResponse::success('Orders retrieved successfully', $this->attachItemsCount($orders));
        }

        $orders = $this->cartModel->getOrdersByCustomerEmail((string) ($user['email'] ?? ''));
        ApiResponse::success('Orders retrieved successfully', $orders);
    }

    public function show($id): void
    {
        $id = (int) $id;
        if ($id <= 0) {
            ApiResponse::error('Validation failed', ['id' => 'Đơn hàng không hợp lệ'], 422);
        }

        $order = $this->cartModel->getOrderById($id);
        if (!$order) {
            ApiResponse::error('Order not found', null, 404);
        }

        if (!AuthHelper::isLoggedIn()) {
            ApiResponse::error('Unauthenticated', null, 401);
        }

        if (!AuthHelper::isAdmin()) {
            $user = AuthHelper::getCurrentUser();
            $orderEmail = strtolower(trim((string) ($order->customer_email ?? '')));
            $userEmail = strtolower(trim((string) ($user['email'] ?? '')));

            if ($orderEmail === '' || $orderEmail !== $userEmail) {
                ApiResponse::error('Forbidden', null, 403);
            }
        }

        ApiResponse::success('Order retrieved successfully', [
            'order' => $order,
            'items' => $this->cartModel->getOrderItems($id),
        ]);
    }

    public function store(): void
    {
        $data = $this->getJsonInput();
        [$cartItems, $usedSessionCart, $hasExplicitItems] = $this->resolveOrderItems($data);
        $payload = $this->resolveCustomerPayload($data);
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

        ApiResponse::success('Order created successfully', [
            'order_id' => $orderId,
        ], 201);
    }

    public function update($id): void
    {
        if (!AuthHelper::isAdmin()) {
            ApiResponse::error('Forbidden', null, 403);
        }

        $id = (int) $id;
        if ($id <= 0) {
            ApiResponse::error('Validation failed', ['id' => 'Đơn hàng không hợp lệ'], 422);
        }

        $data = $this->getJsonInput();
        $status = trim((string) ($data['status'] ?? ''));
        $paymentStatus = trim((string) ($data['payment_status'] ?? ''));

        if ($status !== '') {
            $this->cartModel->updateOrderStatus($id, $status);
        }

        if ($paymentStatus !== '') {
            $updated = $this->cartModel->updateOrderPaymentStatus($id, $paymentStatus);
            if (!$updated) {
                ApiResponse::error('Validation failed', ['payment_status' => 'Trạng thái thanh toán không hợp lệ'], 422);
            }
        }

        ApiResponse::success('Order updated successfully');
    }

    public function destroy($id): void
    {
        if (!AuthHelper::isAdmin()) {
            ApiResponse::error('Forbidden', null, 403);
        }

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

    private function resolveCustomerPayload(array $data): array
    {
        $currentUser = AuthHelper::isLoggedIn() ? AuthHelper::getCurrentUser() : null;

        return [
            'customer_name' => trim((string) ($data['customer_name'] ?? ($currentUser['name'] ?? ''))),
            'customer_phone' => trim((string) ($data['customer_phone'] ?? '')),
            'customer_email' => trim((string) ($data['customer_email'] ?? ($currentUser['email'] ?? ''))),
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

    private function getJsonInput(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        return is_array($data) ? $data : [];
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
