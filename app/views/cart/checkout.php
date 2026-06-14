<?php include 'app/views/shares/header.php'; ?>

<style>
    .checkout-shell {
        background: rgba(255,255,255,.76);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,.55);
        border-radius: 24px;
        box-shadow: var(--card-shadow);
        overflow: hidden;
    }
    .checkout-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        color: #fff;
        padding: 1.25rem 1.5rem;
    }
    .checkout-card {
        border-radius: 20px;
        border: 1px solid rgba(148,163,184,.15);
        background: #fff;
        box-shadow: 0 14px 28px rgba(15,23,42,.06);
    }
    .checkout-item {
        display: flex;
        gap: .85rem;
        align-items: center;
    }
    .checkout-thumb {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        object-fit: cover;
        background: #eef2ff;
        flex-shrink: 0;
    }
    .checkout-total {
        border-radius: 18px;
        background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
        color: #111827;
    }
    .payment-pill {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        padding: .5rem .75rem;
        border-radius: 999px;
        border: 1px solid #dbe3f0;
        cursor: pointer;
        user-select: none;
    }
    .payment-pill input { margin: 0; }
</style>

<main class="container">
    <section class="checkout-shell">
        <div class="checkout-hero">
            <h1 class="h3 fw-black mb-1"><i class="fas fa-credit-card me-2"></i>Thanh toán</h1>
            <p class="mb-0 text-white-50">Form này sẽ gửi trực tiếp vào `POST /api/orders`.</p>
        </div>

        <div class="p-3 p-md-4">
            <div id="checkoutAlert" class="alert d-none" role="alert"></div>
            <div id="checkoutLoading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
                <div class="mt-3 text-muted">Đang tải thông tin thanh toán...</div>
            </div>

            <div id="checkoutContent" class="d-none">
                <div class="row g-4">
                    <div class="col-lg-7">
                        <div class="checkout-card p-4">
                            <h2 class="h5 fw-bold mb-3">Thông tin giao hàng</h2>
                            <form id="checkoutForm" novalidate>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Họ tên *</label>
                                    <input type="text" name="customer_name" class="form-control" required>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Số điện thoại *</label>
                                        <input type="text" name="customer_phone" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Email</label>
                                        <input type="email" name="customer_email" class="form-control">
                                    </div>
                                </div>
                                <div class="mt-3 mb-3">
                                    <label class="form-label fw-bold">Địa chỉ *</label>
                                    <textarea name="customer_address" rows="3" class="form-control" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Ghi chú</label>
                                    <textarea name="note" rows="3" class="form-control"></textarea>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold d-block">Phương thức thanh toán</label>
                                    <label class="payment-pill me-3 mb-2">
                                        <input type="radio" name="payment_method" value="cod" checked>
                                        <span>COD</span>
                                    </label>
                                    <label class="payment-pill mb-2">
                                        <input type="radio" name="payment_method" value="banking">
                                        <span>Chuyển khoản</span>
                                    </label>
                                </div>

                                <button type="submit" class="btn btn-dark btn-lg w-100 fw-bold" id="checkoutSubmitBtn">
                                    <span class="btn-label">Đặt hàng ngay</span>
                                    <span class="spinner-border spinner-border-sm d-none ms-2" aria-hidden="true"></span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="checkout-card p-4 h-100">
                            <h2 class="h5 fw-bold mb-3">Đơn hàng của bạn</h2>
                            <div id="checkoutItems" class="vstack gap-3"></div>
                            <hr>
                            <div class="checkout-total p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold">Tổng cộng</span>
                                    <strong class="fs-4" id="checkoutTotalPrice">0 ₫</strong>
                                </div>
                                <div class="small mt-1">Mã đơn dự kiến: <strong id="checkoutOrderCode">DH0</strong></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="checkoutEmptyState" class="alert alert-info d-none mt-3 mb-0">
                Giỏ hàng đang trống. Hãy thêm sản phẩm trước khi thanh toán.
            </div>
        </div>
    </section>
</main>

<script src="/assets/js/frontend/pages/checkout-page.js" defer></script>

<?php include 'app/views/shares/footer.php'; ?>
