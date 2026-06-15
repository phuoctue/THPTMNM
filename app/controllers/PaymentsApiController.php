<?php

require_once 'app/config/database.php';
require_once 'app/libs/ApiRequest.php';
require_once 'app/libs/ApiResponse.php';
require_once 'app/middleware/AuthMiddleware.php';
require_once 'app/models/CartModel.php';
require_once 'app/models/PaymentModel.php';

class PaymentsApiController
{
    private CartModel $cartModel;
    private PaymentModel $paymentModel;

    public function __construct()
    {
        $db = (new Database())->getConnection();
        $this->cartModel = new CartModel($db);
        $this->paymentModel = new PaymentModel($db);
    }

    public function store(): void
    {
        $user = AuthMiddleware::user();
        $data = ApiRequest::body();

        $orderId = (int) ($data['order_id'] ?? 0);
        $method = strtolower((string) ($data['method'] ?? 'cod'));
        $amount = (int) ($data['amount'] ?? 0);
        $note = trim((string) ($data['note'] ?? ''));

        if ($orderId <= 0) {
            ApiResponse::error('Validation failed', ['order_id' => 'Đơn hàng không hợp lệ'], 422);
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

        if (!in_array($method, ['cod', 'bank_transfer', 'wallet', 'banking', 'e_wallet', 'ewallet'], true)) {
            ApiResponse::error('Validation failed', ['method' => 'Phương thức thanh toán không hợp lệ'], 422);
        }

        if ($amount <= 0) {
            $amount = (int) ($order->total_price ?? 0);
        }

        $normalizedMethod = in_array($method, ['banking', 'bank_transfer'], true) ? 'bank_transfer' : ($method === 'cod' ? 'cod' : 'wallet');
        $reference = 'MOCK-' . strtoupper(bin2hex(random_bytes(4)));
        $paymentId = $this->paymentModel->create([
            'order_id' => $orderId,
            'user_id' => (int) $user['id'],
            'method' => $normalizedMethod,
            'amount' => $amount,
            'provider' => $normalizedMethod,
            'transaction_code' => $reference,
            'status' => 'pending',
            'note' => $note,
        ]);

        if (!$paymentId) {
            ApiResponse::error('Payment creation failed', null, 400);
        }

        $this->cartModel->updateOrderPaymentStatus($orderId, 'unpaid');

        ApiResponse::success('Payment created successfully', [
            'payment_id' => $paymentId,
            'order_id' => $orderId,
            'method' => $normalizedMethod,
            'status' => 'pending',
            'transaction_code' => $reference,
            'mock_next_step' => $normalizedMethod === 'cod' ? 'collect-cash-on-delivery' : 'confirm-payment-manually',
        ], 201);
    }

    public function status($id): void
    {
        AuthMiddleware::admin();

        $paymentId = (int) $id;
        if ($paymentId <= 0) {
            ApiResponse::error('Validation failed', ['id' => 'Thanh toán không hợp lệ'], 422);
        }

        $data = ApiRequest::body();
        $status = strtolower(trim((string) ($data['status'] ?? '')));
        if (!in_array($status, ['pending', 'completed', 'failed'], true)) {
            ApiResponse::error('Validation failed', ['status' => 'Trạng thái không hợp lệ'], 422);
        }

        $payment = $this->paymentModel->getPaymentById($paymentId);
        if (!$payment) {
            ApiResponse::error('Payment not found', null, 404);
        }

        if (!$this->paymentModel->updateStatus($paymentId, $status)) {
            ApiResponse::error('Payment update failed', null, 400);
        }

        $orderId = (int) ($payment['order_id'] ?? 0);
        if ($orderId > 0) {
            $this->cartModel->updateOrderPaymentStatus($orderId, $status === 'completed' ? 'paid' : 'unpaid');
        }

        ApiResponse::success('Payment status updated successfully');
    }
}
