<?php include 'app/views/shares/header.php'; ?>

<div class="container mt-4">
    <?php if (!$order): ?>
        <div class="alert alert-danger">Không tìm thấy đơn hàng.</div>
        <a href="/Product" class="btn btn-primary">Về trang sản phẩm</a>
    <?php else: ?>
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body p-5">
                <i class="fas fa-check-circle text-success" style="font-size:64px;"></i>
                <h2 class="mt-3">Đặt hàng thành công!</h2>
                <p class="text-muted mb-1">Mã đơn hàng: <strong>#<?php echo $order->id; ?></strong></p>
                <p class="text-muted">Tổng thanh toán: <strong><?php echo number_format($order->total_price, 0, ',', '.'); ?> đ</strong></p>
                <p class="mb-4">Phương thức thanh toán: <strong><?php echo strtoupper($order->payment_method); ?></strong></p>
                <a href="/Product" class="btn btn-primary">Tiếp tục mua sắm</a>
                <a href="/Cart/orders" class="btn btn-outline-secondary ml-2">Xem danh sách đơn</a>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-4">
            <div class="card-body">
                <h5>Chi tiết đơn hàng</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Đơn giá</th>
                                <th>SL</th>
                                <th>Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item->name); ?></td>
                                <td><?php echo number_format($item->price, 0, ',', '.'); ?> đ</td>
                                <td><?php echo (int)$item->quantity; ?></td>
                                <td><?php echo number_format($item->price * $item->quantity, 0, ',', '.'); ?> đ</td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'app/views/shares/footer.php'; ?>

