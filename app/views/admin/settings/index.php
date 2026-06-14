<?php include 'app/views/shares/header.php'; ?>

<style>
    .admin-shell {
        background: rgba(255,255,255,.76);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,.55);
        border-radius: 24px;
        box-shadow: var(--card-shadow);
        overflow: hidden;
    }
    .admin-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        color: #fff;
        padding: 1.25rem 1.5rem;
    }
    .admin-card {
        border-radius: 20px;
        border: 1px solid rgba(148,163,184,.15);
        background: #fff;
        box-shadow: 0 14px 28px rgba(15,23,42,.06);
    }
</style>

<main class="container">
    <section class="admin-shell">
        <div class="admin-hero">
            <h1 class="h3 fw-black mb-1"><i class="fas fa-cog me-2"></i>Cấu hình hệ thống</h1>
            <p class="mb-0 text-white-50">Dữ liệu được đọc và lưu qua <code>/api/settings</code>.</p>
        </div>

        <div class="p-3 p-md-4">
            <div id="adminSettingsAlert" class="alert d-none" role="alert"></div>
            <div id="adminSettingsLoading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
                <div class="mt-3 text-muted">Đang tải cấu hình...</div>
            </div>

            <div id="adminSettingsContent" class="d-none">
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="admin-card p-4">
                            <h2 class="h5 fw-bold mb-3">Thiết lập SMTP</h2>
                            <form id="adminSettingsForm" novalidate>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">APP_URL</label>
                                        <input type="text" name="APP_URL" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">MAIL_MAILER</label>
                                        <select name="MAIL_MAILER" class="form-select">
                                            <option value="smtp">smtp</option>
                                            <option value="sendmail">sendmail</option>
                                            <option value="mail">mail</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">MAIL_ENCRYPTION</label>
                                        <select name="MAIL_ENCRYPTION" class="form-select">
                                            <option value="tls">tls</option>
                                            <option value="ssl">ssl</option>
                                            <option value="">none</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">MAIL_HOST</label>
                                        <input type="text" name="MAIL_HOST" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">MAIL_PORT</label>
                                        <input type="number" name="MAIL_PORT" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">MAIL_USERNAME</label>
                                        <input type="email" name="MAIL_USERNAME" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">MAIL_PASSWORD</label>
                                        <input type="password" name="MAIL_PASSWORD" class="form-control" placeholder="Để trống để giữ nguyên">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">MAIL_FROM_ADDRESS</label>
                                        <input type="email" name="MAIL_FROM_ADDRESS" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">MAIL_FROM_NAME</label>
                                        <input type="text" name="MAIL_FROM_NAME" class="form-control">
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap gap-2 mt-4">
                                    <button type="submit" class="btn btn-primary" id="adminSettingsSubmitBtn">
                                        <span class="btn-label"><i class="fas fa-save me-1"></i>Lưu cấu hình</span>
                                        <span class="spinner-border spinner-border-sm d-none ms-2" aria-hidden="true"></span>
                                    </button>
                                    <a href="/Home" class="btn btn-outline-secondary">Về trang chủ</a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="admin-card p-4">
                            <h2 class="h5 fw-bold mb-3">Ghi chú</h2>
                            <ul class="text-muted mb-0">
                                <li>Đây là cấu hình SMTP và thông tin gửi mail.</li>
                                <li>Giữ mật khẩu trống nếu không muốn thay đổi.</li>
                                <li>Nên kiểm tra lại email verify sau khi cập nhật.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script src="/assets/js/frontend/pages/admin-settings.js" defer></script>

<?php include 'app/views/shares/footer.php'; ?>
