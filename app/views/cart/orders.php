<?php include 'app/views/shares/header.php'; ?>

<style>
    .orders-shell {
        background: rgba(255,255,255,.76);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,.55);
        border-radius: 24px;
        box-shadow: var(--card-shadow);
        overflow: hidden;
    }
    .orders-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        color: #fff;
        padding: 1.25rem 1.5rem;
    }
    .orders-card {
        border-radius: 20px;
        border: 1px solid rgba(148,163,184,.15);
        background: #fff;
        box-shadow: 0 14px 28px rgba(15,23,42,.06);
    }
</style>

<main class="container">
    <section class="orders-shell">
        <div class="orders-hero">
            <h1 class="h3 fw-black mb-1"><i class="fas fa-receipt me-2"></i>Danh sách đơn hàng</h1>
            <p class="mb-0 text-white-50">Dữ liệu được tải trực tiếp từ <code>/api/orders</code>.</p>
        </div>

        <div class="p-3 p-md-4">
            <div id="ordersAlert" class="alert d-none" role="alert"></div>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="orders-card p-3 h-100">
                        <div class="text-muted mb-1">Tổng đơn hàng</div>
                        <div class="fs-3 fw-bold" id="ordersTotalCount">0</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="orders-card p-3 h-100">
                        <div class="text-muted mb-1">Tổng doanh thu</div>
                        <div class="fs-3 fw-bold text-success" id="ordersTotalRevenue">0 ₫</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="orders-card p-3 h-100">
                        <div class="text-muted mb-1">Trạng thái mới nhất</div>
                        <div class="fs-3 fw-bold text-primary" id="ordersLatestStatus">---</div>
                    </div>
                </div>
            </div>

            <div id="ordersLoading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
                <div class="mt-3 text-muted">Đang tải đơn hàng...</div>
            </div>

            <div id="ordersContent" class="orders-card d-none overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Mã đơn</th>
                                <th>Khách hàng</th>
                                <th>Thanh toán</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th class="text-end pe-4">Hành động</th>
                            </tr>
                        </thead>
                        <tbody id="ordersTableBody"></tbody>
                    </table>
                </div>
            </div>

            <div id="ordersEmptyState" class="alert alert-info d-none mb-0">
                Chưa có đơn hàng nào.
            </div>
        </div>
    </section>
</main>

<script src="/assets/js/frontend/pages/orders.js" defer></script>

<?php include 'app/views/shares/footer.php'; ?>
