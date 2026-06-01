<?php include 'app/views/shares/header.php'; ?>

<?php
$orderCount = is_array($orders ?? null) ? count($orders) : 0;
$totalRevenue = 0;
if (!empty($orders)) {
    foreach ($orders as $o) {
        $totalRevenue += (float)($o->total_price ?? 0);
    }
}

if (!function_exists('orderStatusBadgeClass')) {
    function orderStatusBadgeClass(string $status): string
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

if (!function_exists('paymentBadgeClass')) {
    function paymentBadgeClass(string $status): string
    {
        return match ($status) {
            'paid' => 'text-bg-success',
            default => 'text-bg-warning',
        };
    }
}
?>

<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary text-white" style="width: 42px; height: 42px;">
                    <i class="fas fa-receipt"></i>
                </span>
                <h2 class="mb-0 fw-bold">Danh sách đơn hàng</h2>
            </div>
            <p class="text-muted mb-0">Theo dõi trạng thái đơn hàng, thanh toán và chi tiết mua hàng.</p>
        </div>
        <a href="/Product" class="btn btn-outline-primary">
            <i class="fas fa-box me-1"></i>Về sản phẩm
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted mb-1">Tổng đơn hàng</div>
                    <div class="fs-3 fw-bold"><?php echo $orderCount; ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted mb-1">Tổng doanh thu</div>
                    <div class="fs-3 fw-bold text-success"><?php echo number_format($totalRevenue, 0, ',', '.'); ?> đ</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted mb-1">Trạng thái mới nhất</div>
                    <div class="fs-3 fw-bold text-primary">
                        <?php echo !empty($orders) ? htmlspecialchars((string)($orders[0]->status ?? 'pending')) : '---'; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Mã đơn</th>
                        <th>Khách hàng</th>
                        <th>Thanh toán</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th class="text-end pe-4">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="fas fa-inbox fa-2x mb-3 d-block"></i>
                            Chưa có đơn hàng nào.
                        </td>
                    </tr>
                <?php else: foreach ($orders as $o): ?>
                    <?php
                        $paymentMethod = strtoupper((string)($o->payment_method ?? 'cod'));
                        $paymentStatus = (string)($o->payment_status ?? 'unpaid');
                        $orderStatus = (string)($o->status ?? 'pending');
                    ?>
                    <tr>
                        <td class="ps-4 fw-semibold">#<?php echo (int)$o->id; ?></td>
                        <td>
                            <div class="fw-semibold"><?php echo htmlspecialchars((string)$o->customer_name); ?></div>
                            <small class="text-muted"><?php echo htmlspecialchars((string)$o->customer_phone); ?></small>
                        </td>
                        <td>
                            <span class="badge <?php echo paymentBadgeClass($paymentStatus); ?>">
                                <?php echo $paymentMethod; ?> / <?php echo htmlspecialchars($paymentStatus); ?>
                            </span>
                        </td>
                        <td class="fw-semibold text-success">
                            <?php echo number_format((float)$o->total_price, 0, ',', '.'); ?> đ
                        </td>
                        <td>
                            <span class="badge <?php echo orderStatusBadgeClass($orderStatus); ?>">
                                <?php echo htmlspecialchars($orderStatus); ?>
                            </span>
                        </td>
                        <td class="text-muted"><?php echo htmlspecialchars((string)$o->created_at); ?></td>
                        <td class="text-end pe-4">
                            <a href="/Cart/orderDetail/<?php echo (int)$o->id; ?>" class="btn btn-sm btn-primary">
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

<?php include 'app/views/shares/footer.php'; ?>
