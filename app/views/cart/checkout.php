<?php include 'app/views/shares/header.php'; ?>

<div class="container mt-4">
    <h2 class="mb-4"><i class="fas fa-credit-card text-success"></i> Thanh toán</h2>

    <?php if (!empty($_SESSION['checkout_error'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['checkout_error']); unset($_SESSION['checkout_error']); ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-7 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="mb-3">Thông tin giao hàng</h5>
                    <form action="/Cart/placeOrder" method="POST">
                        <div class="form-group">
                            <label>Họ tên *</label>
                            <input type="text" name="customer_name" class="form-control" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Số điện thoại *</label>
                                <input type="text" name="customer_phone" class="form-control" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Email</label>
                                <input type="email" name="customer_email" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Địa chỉ *</label>
                            <textarea name="customer_address" rows="3" class="form-control" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Ghi chú</label>
                            <textarea name="note" rows="3" class="form-control"></textarea>
                        </div>

                        <div class="form-group">
                            <label>Phương thức thanh toán</label>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="pmCod" name="payment_method" value="cod" class="custom-control-input" checked>
                                <label class="custom-control-label" for="pmCod">COD - Thanh toán khi nhan hang</label>
                            </div>
                            <div class="custom-control custom-radio mt-2">
                                <input type="radio" id="pmBanking" name="payment_method" value="banking" class="custom-control-input">
                                <label class="custom-control-label" for="pmBanking">Chuyển khoản ngân hàng</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-check-circle mr-1"></i> Đặt hàng ngay
                        </button>
                        <a href="/Cart" class="btn btn-outline-secondary btn-lg ml-2">Quay lại giỏ hàng</a>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="mb-3">Đơn hàng của bạn</h5>
                    <?php foreach ($cartItems as $item): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <div><?php echo htmlspecialchars($item['name']); ?> x <?php echo $item['quantity']; ?></div>
                            <strong><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?> đ</strong>
                        </div>
                    <?php endforeach; ?>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <h5 class="mb-0">Tổng cộng</h5>
                        <h4 class="text-primary mb-0"><?php echo number_format($totalPrice, 0, ',', '.'); ?> đ</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>

