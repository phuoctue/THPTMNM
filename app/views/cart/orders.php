<?php include 'app/views/shares/header.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="fas fa-receipt text-primary"></i> Danh sách đơn hàng</h2>
        <a href="/Product" class="btn btn-outline-primary">Về sản phẩm</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Khách hàng</th>
                        <th>Thanh toán</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">Chưa có đơn hàng nào.</td></tr>
                <?php else: foreach ($orders as $o): ?>
                    <tr>
                        <td>#<?php echo (int)$o->id; ?></td>
                        <td><?php echo htmlspecialchars($o->customer_name); ?><br><small class="text-muted"><?php echo htmlspecialchars($o->customer_phone); ?></small></td>
                        <td><?php echo strtoupper($o->payment_method); ?> / <?php echo $o->payment_status; ?></td>
                        <td><?php echo number_format($o->total_price, 0, ',', '.'); ?> đ</td>
                        <td><?php echo htmlspecialchars($o->status); ?></td>
                        <td><?php echo htmlspecialchars($o->created_at); ?></td>
                        <td><a href="/Cart/orderDetail/<?php echo (int)$o->id; ?>" class="btn btn-sm btn-primary">Chi tiết</a></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>

