<?php include 'app/views/shares/header.php'; ?>

<style>
    .profile-shell {
        background: rgba(255,255,255,.76);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,.55);
        border-radius: 24px;
        box-shadow: var(--card-shadow);
        overflow: hidden;
    }
    .profile-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        color: #fff;
        padding: 1.25rem 1.5rem;
    }
    .profile-card {
        border-radius: 20px;
        border: 1px solid rgba(148,163,184,.15);
        background: #fff;
        box-shadow: 0 14px 28px rgba(15,23,42,.06);
    }
</style>

<main class="container">
    <section class="profile-shell">
        <div class="profile-hero">
            <h1 class="h3 fw-black mb-1"><i class="fas fa-receipt me-2"></i>Đơn hàng của tôi</h1>
            <p class="mb-0 text-white-50">Dữ liệu lấy từ <code>/api/profile/orders</code>.</p>
        </div>

        <div class="p-3 p-md-4">
            <div id="profileOrdersAlert" class="alert d-none" role="alert"></div>
            <div id="profileOrdersLoading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
                <div class="mt-3 text-muted">Đang tải đơn hàng...</div>
            </div>

            <div id="profileOrdersContent" class="d-none">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="profile-card p-3 h-100">
                            <div class="text-muted mb-1">Tổng đơn hàng</div>
                            <div class="fs-3 fw-bold" id="profileOrdersCount">0</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="profile-card p-3 h-100">
                            <div class="text-muted mb-1">Tổng chi tiêu</div>
                            <div class="fs-3 fw-bold text-success" id="profileOrdersSpent">0 ₫</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="profile-card p-3 h-100">
                            <div class="text-muted mb-1">Tài khoản</div>
                            <div class="fs-3 fw-bold text-primary" id="profileOrdersRole">customer</div>
                        </div>
                    </div>
                </div>

                <div class="profile-card overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Mã đơn</th>
                                    <th>Ngày đặt</th>
                                    <th>Thanh toán</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Sản phẩm</th>
                                    <th class="text-end pe-4">Hành động</th>
                                </tr>
                            </thead>
                            <tbody id="profileOrdersTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="profileOrdersEmpty" class="alert alert-info d-none mb-0">
                Bạn chưa có đơn hàng nào.
            </div>
        </div>
    </section>
</main>

<script src="/assets/js/frontend/pages/profile-orders.js" defer></script>

<?php include 'app/views/shares/footer.php'; ?>
