<?php include 'app/views/shares/header.php'; ?>

<style>
    .success-shell {
        max-width: 960px;
        margin: 0 auto;
        background: rgba(255,255,255,.78);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,.55);
        border-radius: 24px;
        box-shadow: var(--card-shadow);
        overflow: hidden;
    }
    .success-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        color: #fff;
        padding: 1.5rem;
        text-align: center;
    }
    .success-icon {
        font-size: 4rem;
        color: #22c55e;
    }
    .success-items {
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 14px 28px rgba(15,23,42,.06);
    }
    .success-item {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        align-items: center;
        padding: .9rem 0;
        border-bottom: 1px solid #eef2f7;
    }
    .success-item:last-child {
        border-bottom: 0;
    }
</style>

<main class="container">
    <section class="success-shell">
        <div class="success-hero">
            <i class="fas fa-check-circle success-icon d-block mb-2"></i>
            <h1 class="h3 fw-black mb-1">Đặt hàng thành công</h1>
            <p class="mb-0 text-white-50">Chi tiết đơn hàng sẽ được tải từ `/api/orders/{id}`.</p>
        </div>

        <div class="p-3 p-md-4">
            <div id="orderSuccessAlert" class="alert d-none" role="alert"></div>
            <div id="orderSuccessLoading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
                <div class="mt-3 text-muted">Đang tải đơn hàng...</div>
            </div>

            <div id="orderSuccessContent" class="d-none">
                <div class="row g-4">
                    <div class="col-lg-7">
                        <div class="success-items p-4">
                            <h2 class="h5 fw-bold mb-3">Chi tiết đơn hàng</h2>
                            <div id="orderSuccessItems"></div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="p-4 bg-white rounded-4 shadow-sm">
                            <div class="text-muted small mb-1">Mã đơn hàng</div>
                            <div class="h4 fw-black mb-3" id="orderSuccessId">#0</div>
                            <div class="text-muted small mb-1">Tổng thanh toán</div>
                            <div class="h3 fw-black text-success mb-3" id="orderSuccessTotal">0 ₫</div>
                            <div class="text-muted small mb-1">Thanh toán</div>
                            <div class="fw-bold mb-4" id="orderSuccessPayment">COD</div>
                            <div class="d-grid gap-2">
                                <a href="/Home" class="btn btn-primary"><i class="fas fa-shopping-bag me-1"></i>Tiếp tục mua sắm</a>
                                <a href="/Cart/orders" class="btn btn-outline-secondary">Xem danh sách đơn</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script src="/assets/js/frontend/pages/cart-success.js" defer></script>

<?php include 'app/views/shares/footer.php'; ?>
