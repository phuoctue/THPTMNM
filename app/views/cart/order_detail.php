<?php include 'app/views/shares/header.php'; ?>

<style>
    .order-detail-shell {
        background: rgba(255,255,255,.76);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,.55);
        border-radius: 24px;
        box-shadow: var(--card-shadow);
        overflow: hidden;
    }
    .order-detail-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        color: #fff;
        padding: 1.25rem 1.5rem;
    }
    .order-detail-card {
        border-radius: 20px;
        border: 1px solid rgba(148,163,184,.15);
        background: #fff;
        box-shadow: 0 14px 28px rgba(15,23,42,.06);
    }
</style>

<main class="container">
    <section class="order-detail-shell">
        <div class="order-detail-hero">
            <h1 class="h3 fw-black mb-1"><i class="fas fa-file-invoice me-2"></i>Chi tiết đơn hàng</h1>
            <p class="mb-0 text-white-50">Trang này tải dữ liệu từ <code>/api/orders/{id}</code>.</p>
        </div>

        <div class="p-3 p-md-4">
            <div id="orderDetailAlert" class="alert d-none" role="alert"></div>
            <div id="orderDetailLoading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
                <div class="mt-3 text-muted">Đang tải chi tiết đơn hàng...</div>
            </div>

            <div id="orderDetailContent" class="d-none">
                <div class="row g-4 mb-4">
                    <div class="col-xl-7">
                        <div class="order-detail-card p-4 h-100">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <div class="text-muted small mb-1">Mã đơn hàng</div>
                                    <h2 class="h4 fw-black mb-0" id="orderDetailId">#0</h2>
                                </div>
                                <span class="badge fs-6" id="orderDetailStatusBadge">pending</span>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 bg-light h-100">
                                        <div class="text-muted small">Khách hàng</div>
                                        <div class="fw-semibold" id="orderDetailCustomerName">-</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 bg-light h-100">
                                        <div class="text-muted small">Số điện thoại</div>
                                        <div class="fw-semibold" id="orderDetailPhone">-</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 bg-light h-100">
                                        <div class="text-muted small">Email</div>
                                        <div class="fw-semibold" id="orderDetailEmail">-</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 bg-light h-100">
                                        <div class="text-muted small">Ngày tạo</div>
                                        <div class="fw-semibold" id="orderDetailCreatedAt">-</div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="p-3 rounded-3 bg-light">
                                        <div class="text-muted small">Địa chỉ</div>
                                        <div class="fw-semibold" id="orderDetailAddress">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-5">
                        <div class="order-detail-card p-4 h-100">
                            <div class="text-muted small mb-2">Tóm tắt</div>
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="p-3 rounded-3 bg-light h-100">
                                        <div class="text-muted small">Thanh toán</div>
                                        <div class="fw-semibold">
                                            <span class="badge" id="orderDetailPaymentBadge">COD / unpaid</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 rounded-3 bg-light h-100">
                                        <div class="text-muted small">Số sản phẩm</div>
                                        <div class="fw-semibold fs-4" id="orderDetailItemsCount">0</div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="p-3 rounded-3 bg-light">
                                        <div class="text-muted small">Tổng tiền</div>
                                        <div class="fw-bold fs-3 text-success" id="orderDetailTotal">0 ₫</div>
                                    </div>
                                </div>
                            </div>

                            <div id="orderDetailAdminBox" class="mt-4 d-none">
                                <hr>
                                <h3 class="h5 fw-bold mb-3">Cập nhật đơn hàng</h3>
                                <form id="orderDetailForm" class="vstack gap-3">
                                    <div>
                                        <label class="form-label fw-semibold">Trạng thái đơn hàng</label>
                                        <select class="form-select" name="status" id="orderDetailStatus">
                                            <option value="pending">Pending</option>
                                            <option value="confirmed">Confirmed</option>
                                            <option value="shipping">Shipping</option>
                                            <option value="done">Done</option>
                                            <option value="cancelled">Cancelled</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label fw-semibold">Trạng thái thanh toán</label>
                                        <select class="form-select" name="payment_status" id="orderDetailPaymentStatus">
                                            <option value="">Giữ nguyên</option>
                                            <option value="unpaid">Unpaid</option>
                                            <option value="paid">Paid</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary fw-bold">
                                        <i class="fas fa-save me-1"></i>Lưu thay đổi
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="order-detail-card p-4">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                        <div>
                            <h3 class="h5 fw-bold mb-1">Sản phẩm trong đơn</h3>
                            <div class="text-muted">Danh sách sản phẩm được lấy trực tiếp từ API.</div>
                        </div>
                        <a href="/Cart/orders" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Quay lại
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Sản phẩm</th>
                                    <th>Đơn giá</th>
                                    <th>Số lượng</th>
                                    <th class="text-end pe-3">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody id="orderDetailItems"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script src="/assets/js/frontend/pages/order-detail.js" defer></script>

<?php include 'app/views/shares/footer.php'; ?>
