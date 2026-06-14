<?php include 'app/views/shares/header.php'; ?>

<style>
    .cart-shell {
        background: rgba(255,255,255,.72);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,.55);
        border-radius: 24px;
        box-shadow: var(--card-shadow);
        overflow: hidden;
    }
    .cart-head {
        padding: 1.25rem 1.25rem 0;
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        align-items: center;
        flex-wrap: wrap;
    }
    .cart-table thead th {
        background: #0f172a;
        color: #fff;
        border-bottom: 0;
    }
    .cart-table td, .cart-table th {
        vertical-align: middle;
    }
    .cart-thumb {
        width: 72px;
        height: 72px;
        border-radius: 14px;
        object-fit: cover;
        background: #eef2ff;
        flex-shrink: 0;
    }
    .cart-total-box {
        border-radius: 20px;
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        color: #fff;
        box-shadow: var(--card-shadow);
    }
    .qty-input {
        max-width: 110px;
        border-radius: 12px;
    }
    .cart-empty {
        padding: 4rem 1rem;
        text-align: center;
        color: #64748b;
    }
    .cart-empty i {
        font-size: 3.5rem;
        color: #94a3b8;
        margin-bottom: 1rem;
    }
    .cart-row-action {
        min-width: 110px;
    }
</style>

<main class="container">
    <section class="cart-shell">
        <div class="cart-head">
            <div>
                <h1 class="h3 fw-black mb-1"><i class="fas fa-shopping-cart text-primary me-2"></i>Giỏ hàng</h1>
                <p class="text-muted mb-0">Dữ liệu được tải từ <code>/api/cart</code> và cập nhật bằng fetch.</p>
            </div>
            <a href="/Home" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-1"></i>Tiếp tục mua
            </a>
        </div>

        <div class="p-3 p-md-4">
            <div id="cartAlert" class="alert d-none" role="alert"></div>
            <div id="cartLoading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
                <div class="mt-3 text-muted">Đang tải giỏ hàng...</div>
            </div>

            <div id="cartEmptyState" class="cart-empty d-none">
                <i class="fas fa-box-open d-block"></i>
                <h2 class="h4 fw-bold mb-1">Giỏ hàng đang trống</h2>
                <p class="mb-4">Hãy thêm sản phẩm từ trang chủ để bắt đầu mua sắm.</p>
                <a href="/Home" class="btn btn-primary px-4">Đi tới trang chủ</a>
            </div>

            <div id="cartContent" class="d-none">
                <div class="table-responsive">
                    <table class="table align-middle cart-table mb-0">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Giá</th>
                                <th>Số lượng</th>
                                <th>Tổng</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="cartItems"></tbody>
                    </table>
                </div>

                <div class="row g-3 mt-2 align-items-stretch">
                    <div class="col-lg-7">
                        <div class="p-3 p-md-4 border rounded-4 bg-white h-100">
                            <div class="fw-bold mb-2">Ghi chú</div>
                            <p class="text-muted mb-0">Trang thanh toán sẽ lấy dữ liệu từ giỏ hàng hiện tại và gửi qua API `/api/orders`.</p>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="cart-total-box p-4 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-semibold">Tổng số lượng</span>
                                <strong id="cartTotalQty">0</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fw-semibold">Tổng tiền</span>
                                <strong class="fs-3" id="cartTotalPrice">0 ₫</strong>
                            </div>
                            <a href="/Cart/checkout" class="btn btn-warning w-100 fw-bold">
                                <i class="fas fa-credit-card me-1"></i>Thanh toán
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script src="/assets/js/frontend/pages/cart-page.js" defer></script>

<?php include 'app/views/shares/footer.php'; ?>
