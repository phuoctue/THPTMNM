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
            <h1 class="h3 fw-black mb-1"><i class="fas fa-lock me-2"></i>Đổi mật khẩu</h1>
            <p class="mb-0 text-white-50">Gửi trực tiếp tới <code>POST /api/profile/changePassword</code>.</p>
        </div>

        <div class="p-3 p-md-4">
            <div id="profilePasswordAlert" class="alert d-none" role="alert"></div>
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="profile-card p-4 h-100">
                        <div class="list-group list-group-flush">
                            <a href="/profile" class="list-group-item list-group-item-action"><i class="fas fa-user me-2"></i>Hồ sơ</a>
                            <a href="/profile/edit" class="list-group-item list-group-item-action"><i class="fas fa-pen me-2"></i>Chỉnh sửa</a>
                            <a href="/profile/changePassword" class="list-group-item list-group-item-action active"><i class="fas fa-lock me-2"></i>Đổi mật khẩu</a>
                            <a href="/profile/orders" class="list-group-item list-group-item-action"><i class="fas fa-receipt me-2"></i>Đơn hàng</a>
                        </div>
                        <hr>
                        <p class="text-muted mb-0 small">
                            Sau khi đổi mật khẩu, bạn sẽ bị đăng xuất và cần đăng nhập lại.
                        </p>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="profile-card p-4">
                        <h2 class="h5 fw-bold mb-3">Cập nhật mật khẩu</h2>
                        <form id="profilePasswordForm" novalidate>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Mật khẩu cũ</label>
                                <input type="password" name="old_password" id="old_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Mật khẩu mới</label>
                                <input type="password" name="new_password" id="new_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Xác nhận mật khẩu mới</label>
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                            </div>

                            <div class="alert alert-warning">
                                Mật khẩu mới phải khác mật khẩu cũ và có ít nhất 8 ký tự.
                            </div>

                            <div class="d-flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-primary" id="profilePasswordSubmitBtn">
                                    <span class="btn-label"><i class="fas fa-check me-1"></i>Đổi mật khẩu</span>
                                    <span class="spinner-border spinner-border-sm d-none ms-2" aria-hidden="true"></span>
                                </button>
                                <a href="/profile" class="btn btn-outline-secondary">Hủy</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script src="/assets/js/frontend/pages/profile-change-password.js" defer></script>

<?php include 'app/views/shares/footer.php'; ?>
