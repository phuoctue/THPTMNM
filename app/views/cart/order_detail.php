<?php include 'app/views/shares/header.php'; ?>

<?php
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

$paymentMethod = strtoupper((string)($order->payment_method ?? 'cod'));
$paymentStatus = (string)($order->payment_status ?? 'unpaid');
$orderStatus = (string)($order->status ?? 'pending');
$items = is_array($items ?? null) ? $items : [];
$grandTotal = 0;
$projectRoot = dirname(__DIR__, 3);
foreach ($items as $item) {
    $grandTotal += (float)($item->price ?? 0) * (int)($item->quantity ?? 0);
}
?>

<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary text-white" style="width: 42px; height: 42px;">
                    <i class="fas fa-file-invoice"></i>
                </span>
                <h2 class="mb-0 fw-bold">Chi tiết đơn #<?php echo (int)$order->id; ?></h2>
            </div>
            <p class="text-muted mb-0">Xem thông tin khách hàng, sản phẩm và cập nhật trạng thái đơn.</p>
        </div>
        <a href="/Cart/orders" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Quay lại
        </a>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="text-muted small mb-1">Thông tin khách hàng</div>
                            <h5 class="fw-bold mb-0"><?php echo htmlspecialchars((string)$order->customer_name); ?></h5>
                        </div>
                        <span class="badge <?php echo orderStatusBadgeClass($orderStatus); ?> fs-6">
                            <?php echo htmlspecialchars($orderStatus); ?>
                        </span>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 bg-light h-100">
                                <div class="text-muted small">Số điện thoại</div>
                                <div class="fw-semibold"><?php echo htmlspecialchars((string)$order->customer_phone); ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 bg-light h-100">
                                <div class="text-muted small">Email</div>
                                <div class="fw-semibold"><?php echo htmlspecialchars((string)($order->customer_email ?? '-')); ?></div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="p-3 rounded-3 bg-light">
                                <div class="text-muted small">Địa chỉ</div>
                                <div class="fw-semibold"><?php echo htmlspecialchars((string)$order->customer_address); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="text-muted small mb-2">Tóm tắt đơn hàng</div>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="p-3 rounded-3 bg-light h-100">
                                <div class="text-muted small">Thanh toán</div>
                                <div class="fw-semibold">
                                    <span class="badge <?php echo paymentBadgeClass($paymentStatus); ?>">
                                        <?php echo $paymentMethod; ?> / <?php echo htmlspecialchars($paymentStatus); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 rounded-3 bg-light h-100">
                                <div class="text-muted small">Ngày tạo</div>
                                <div class="fw-semibold"><?php echo htmlspecialchars((string)$order->created_at); ?></div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="p-3 rounded-3 bg-light">
                                <div class="text-muted small">Tổng tiền</div>
                                <div class="fw-bold fs-4 text-success"><?php echo number_format((float)$order->total_price, 0, ',', '.'); ?> đ</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
                <div>
                    <h5 class="fw-bold mb-1">Cập nhật trạng thái</h5>
                    <div class="text-muted">Chọn trạng thái mới cho đơn hàng và lưu lại.</div>
                </div>
            </div>

            <form action="/Cart/updateStatus" method="POST" class="row g-3 align-items-end">
                <input type="hidden" name="order_id" value="<?php echo (int)$order->id; ?>">
                <div class="col-md-8 col-lg-6">
                    <label class="form-label fw-semibold">Trạng thái đơn hàng</label>
                    <select class="form-select" name="status">
                        <?php $statuses = ['pending', 'confirmed', 'shipping', 'done', 'cancelled']; ?>
                        <?php foreach ($statuses as $st): ?>
                            <option value="<?php echo $st; ?>" <?php echo $orderStatus === $st ? 'selected' : ''; ?>>
                                <?php echo ucfirst($st); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 col-lg-6 d-flex gap-2">
                    <button class="btn btn-primary px-4">
                        <i class="fas fa-save me-1"></i>Lưu
                    </button>
                    <a href="/Cart/orders" class="btn btn-outline-secondary px-4">Quay lại</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <div>
                    <h5 class="fw-bold mb-1">Sản phẩm trong đơn</h5>
                    <div class="text-muted">Tổng cộng <?php echo count($items); ?> sản phẩm dòng.</div>
                </div>
                <div class="badge text-bg-light text-success fs-6 px-3 py-2">
                    Tổng tính lại: <?php echo number_format($grandTotal, 0, ',', '.'); ?> đ
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Sản phẩm</th>
                            <th>Đơn giá</th>
                            <th>Số lượng</th>
                            <th class="text-end pe-3">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Không có sản phẩm nào trong đơn.</td>
                            </tr>
                        <?php else: foreach ($items as $item): ?>
                            <?php
                                $lineTotal = (float)($item->price ?? 0) * (int)($item->quantity ?? 0);
                                $imagePath = trim((string)($item->display_image ?? $item->image ?? $item->product_image ?? ''));
                                $imageFile = $imagePath !== '' ? $projectRoot . DIRECTORY_SEPARATOR . ltrim($imagePath, '/\\') : '';
                                $hasImage = $imagePath !== '' && is_file($imageFile);
                            ?>
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-3 bg-light d-flex align-items-center justify-content-center overflow-hidden text-muted" style="width: 56px; height: 56px;">
                                            <?php if ($hasImage): ?>
                                                <img src="/<?php echo htmlspecialchars($imagePath); ?>" class="w-100 h-100 object-fit-cover" alt="<?php echo htmlspecialchars((string)$item->name); ?>">
                                            <?php else: ?>
                                                <i class="fas fa-image"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold"><?php echo htmlspecialchars((string)$item->name); ?></div>
                                            <small class="text-muted">Mã SP: #<?php echo (int)($item->product_id ?? 0); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td class="fw-semibold"><?php echo number_format((float)$item->price, 0, ',', '.'); ?> đ</td>
                                <td><span class="badge text-bg-secondary"><?php echo (int)$item->quantity; ?></span></td>
                                <td class="text-end pe-3 fw-semibold text-success"><?php echo number_format($lineTotal, 0, ',', '.'); ?> đ</td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>
