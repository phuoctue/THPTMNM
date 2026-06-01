<?php
require_once 'app/libs/AuthHelper.php';
AuthHelper::requireLogin();

$user = AuthHelper::getCurrentUser();
$orders = is_array($orders ?? null) ? $orders : [];

if (!function_exists('profileOrderStatusBadgeClass')) {
    function profileOrderStatusBadgeClass(string $status): string
    {
        return match ($status) {
            'confirmed' => 'text-bg-info',
            'shipping' => 'text-bg-primary',
            'done' => 'text-bg-success',
            'cancelled' => 'text-bg-danger',
            default => 'text-bg-secondary',
        };
    }
}

if (!function_exists('profilePaymentBadgeClass')) {
    function profilePaymentBadgeClass(string $status): string
    {
        return match ($status) {
            'paid' => 'text-bg-success',
            default => 'text-bg-warning',
        };
    }
}

$totalOrders = count($orders);
$totalSpent = 0;
foreach ($orders as $order) {
    $totalSpent += (float)($order->total_price ?? 0);
}
?>

<?php require_once 'app/views/shares/header.php'; ?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-4">
                    <div class="mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                         style="width: 84px; height: 84px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); font-size: 2rem;">
                        <?php echo strtoupper(substr((string)($user['name'] ?? 'U'), 0, 1)); ?>
                    </div>
                    <h5 class="fw-bold mb-1"><?php echo htmlspecialchars((string)($user['name'] ?? '')); ?></h5>
                    <div class="text-muted mb-3"><?php echo htmlspecialchars((string)($user['email'] ?? '')); ?></div>
                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        <span class="badge text-bg-<?php echo ($user['role'] ?? 'customer') === 'admin' ? 'dark' : 'primary'; ?>">
                            <?php echo htmlspecialchars((string)($user['role'] ?? 'customer')); ?>
                        </span>
                        <span class="badge text-bg-success">active</span>
                    </div>
                    <hr class="my-4">
                    <div class="list-group list-group-flush">
                        <a href="/profile" class="list-group-item list-group-item-action border-0">
                            <i class="fas fa-user me-2"></i>Hồ sơ
                        </a>
                        <a href="/profile/edit" class="list-group-item list-group-item-action border-0">
                            <i class="fas fa-edit me-2"></i>Chỉnh sửa
                        </a>
                        <a href="/profile/changePassword" class="list-group-item list-group-item-action border-0">
                            <i class="fas fa-lock me-2"></i>Đổi mật khẩu
                        </a>
                        <a href="/profile/orders" class="list-group-item list-group-item-action border-0 active">
                            <i class="fas fa-receipt me-2"></i>Đơn hàng
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-9">
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
                <div>
                    <h2 class="fw-bold mb-1">Lịch sử đơn hàng</h2>
                    <p class="text-muted mb-0">Theo dõi các đơn hàng được đặt bằng email tài khoản hiện tại.</p>
                </div>
                <a href="/" class="btn btn-outline-primary">
                    <i class="fas fa-home me-1"></i>Về trang chủ
                </a>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted mb-1">Tổng đơn hàng</div>
                            <div class="fs-3 fw-bold"><?php echo $totalOrders; ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted mb-1">Tổng chi tiêu</div>
                            <div class="fs-3 fw-bold text-success"><?php echo number_format($totalSpent, 0, ',', '.'); ?> đ</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted mb-1">Tài khoản</div>
                            <div class="fs-3 fw-bold text-primary"><?php echo htmlspecialchars((string)($user['role'] ?? 'customer')); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-white border-0 py-3 px-4">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="fw-bold fs-5">
                            <i class="fas fa-receipt text-primary me-2"></i>Danh sách đơn hàng
                        </div>
                        <span class="badge text-bg-light text-secondary px-3 py-2">
                            <?php echo $totalOrders; ?> đơn
                        </span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Mã đơn</th>
                                <th>Ngày đặt</th>
                                <th>Thanh toán</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th>Sản phẩm</th>
                                <th class="text-end pe-4">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-box-open fa-2x d-block mb-3"></i>
                                    Bạn chưa có đơn hàng nào.
                                </td>
                            </tr>
                        <?php else: foreach ($orders as $order): ?>
                            <?php
                                $paymentMethod = strtoupper((string)($order->payment_method ?? 'cod'));
                                $paymentStatus = (string)($order->payment_status ?? 'unpaid');
                                $orderStatus = (string)($order->status ?? 'pending');
                            ?>
                            <tr>
                                <td class="ps-4 fw-semibold">#<?php echo (int)$order->id; ?></td>
                                <td class="text-muted"><?php echo htmlspecialchars((string)($order->created_at ?? '')); ?></td>
                                <td>
                                    <span class="badge <?php echo profilePaymentBadgeClass($paymentStatus); ?>">
                                        <?php echo $paymentMethod; ?> / <?php echo htmlspecialchars($paymentStatus); ?>
                                    </span>
                                </td>
                                <td class="fw-semibold text-success"><?php echo number_format((float)$order->total_price, 0, ',', '.'); ?> đ</td>
                                <td>
                                    <span class="badge <?php echo profileOrderStatusBadgeClass($orderStatus); ?>">
                                        <?php echo htmlspecialchars($orderStatus); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge text-bg-light text-dark">
                                        <?php echo (int)($order->item_count ?? 0); ?> sản phẩm
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="/Cart/orderDetail/<?php echo (int)$order->id; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye me-1"></i>Chi tiết
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'app/views/shares/footer.php'; ?>
