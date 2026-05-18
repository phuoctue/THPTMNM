<?php include 'app/views/shares/header.php'; ?>

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <i class="fas fa-shopping-cart text-primary"></i>
            Giỏ hàng
        </h2>

        <a href="/Product" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left"></i>
            Tiếp tục mua
        </a>
    </div>

    <?php if (empty($cartItems)): ?>

        <div class="alert alert-info text-center p-5">
            <h4>Giỏ hàng đang trống 🛒</h4>
            <p>Hãy thêm sản phẩm vào giỏ hàng</p>
        </div>

    <?php else: ?>

        <div class="card shadow-sm border-0">
            <div class="table-responsive">

                <table class="table align-middle mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Giá</th>
                            <th width="150">Số lượng</th>
                            <th>Tổng</th>
                            <th width="80"></th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php foreach ($cartItems as $id => $item): ?>

                        <tr>

                            <!-- Hình + tên -->
                            <td>
                                <div class="d-flex align-items-center">

                                    <?php if (!empty($item['image'])): ?>
                                        <img src="/<?php echo $item['image']; ?>"
                                             width="70"
                                             height="70"
                                             style="object-fit:cover;border-radius:10px;">
                                    <?php endif; ?>

                                    <div class="ml-3">
                                        <div class="font-weight-bold">
                                            <?php echo htmlspecialchars($item['name']); ?>
                                        </div>
                                    </div>

                                </div>
                            </td>

                            <!-- Giá -->
                            <td>
                                <?php echo number_format($item['price'], 0, ',', '.'); ?> ₫
                            </td>

                            <!-- Số lượng -->
                            <td>

                                <form action="/Cart/update" method="POST">

                                    <input type="hidden"
                                           name="product_id"
                                           value="<?php echo $id; ?>">

                                    <div class="d-flex">

                                        <input type="number"
                                               name="quantity"
                                               value="<?php echo $item['quantity']; ?>"
                                               min="1"
                                               class="form-control mr-2">

                                        <button class="btn btn-primary">
                                            <i class="fas fa-sync"></i>
                                        </button>

                                    </div>

                                </form>

                            </td>

                            <!-- Tổng -->
                            <td class="font-weight-bold text-primary">
                                <?php
                                echo number_format(
                                    $item['price'] * $item['quantity'],
                                    0,
                                    ',',
                                    '.'
                                );
                                ?> ₫
                            </td>

                            <!-- Xoá -->
                            <td>

                                <form action="/Cart/remove" method="POST">

                                    <input type="hidden"
                                           name="product_id"
                                           value="<?php echo $id; ?>">

                                    <button class="btn btn-danger btn-sm"
                                            onclick="return confirm('Xóa sản phẩm?')">

                                        <i class="fas fa-trash"></i>

                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>
                </table>

            </div>
        </div>

        <!-- Tổng tiền -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-body d-flex justify-content-between align-items-center">

                <h4 class="mb-0">
                    Tổng tiền:
                </h4>

                <h3 class="text-primary mb-0 font-weight-bold">
                    <?php echo number_format($totalPrice, 0, ',', '.'); ?> ₫
                </h3>

            </div>
        </div>

        <!-- Nút thanh toán -->
        <div class="text-right mt-4">

            <a href="/Cart/checkout" class="btn btn-lg btn-success">
                <i class="fas fa-credit-card"></i>
                Thanh toán
            </a>

        </div>

    <?php endif; ?>

</div>

<?php include 'app/views/shares/footer.php'; ?>