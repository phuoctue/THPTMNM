<?php include 'app/views/shares/header.php'; ?>

<div class="container mt-4">
    <h2 class="mb-3"><i class="fas fa-file-invoice text-primary"></i> Chi tiết đơn #<?php echo (int)$order->id; ?></h2>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Khách hàng:</strong> <?php echo htmlspecialchars($order->customer_name); ?></p>
                    <p class="mb-1"><strong>SDT:</strong> <?php echo htmlspecialchars($order->customer_phone); ?></p>
                    <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($order->customer_email ?? ''); ?></p>
                    <p class="mb-1"><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order->customer_address); ?></p>
                </div>
                <div class="col-md-6">
                    <p class="mb-1"><strong>Thanh toán:</strong> <?php echo strtoupper($order->payment_method); ?> / <?php echo htmlspecialchars($order->payment_status); ?></p>
                    <p class="mb-1"><strong>Tổng tiền:</strong> <?php echo number_format($order->total_price, 0, ',', '.'); ?> đ</p>
                    <p class="mb-1"><strong>Ngày tạo:</strong> <?php echo htmlspecialchars($order->created_at); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5>Cập nhật trạng thái</h5>
            <form action="/Cart/updateStatus" method="POST" class="form-inline">
                <input type="hidden" name="order_id" value="<?php echo (int)$order->id; ?>">
                <select class="form-control mr-2" name="status">
                    <?php $statuses = ['pending','confirmed','shipping','done','cancelled']; ?>
                    <?php foreach ($statuses as $st): ?>
                        <option value="<?php echo $st; ?>" <?php echo $order->status === $st ? 'selected' : ''; ?>><?php echo $st; ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-primary">Lưu</button>
                <a href="/Cart/orders" class="btn btn-outline-secondary ml-2">Quay lại</a>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h5>Sản phẩm trong đơn</h5>
            <div class="table-responsive">
                <table class="table table-bordered mb-0">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Đơn giá</th>
                            <th>Số lượng</th>
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
</div>

<?php include 'app/views/shares/footer.php'; ?>

