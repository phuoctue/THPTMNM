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
    .profile-avatar {
        width: 110px;
        height: 110px;
        border-radius: 50%;
        object-fit: cover;
        background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
    }
</style>

<main class="container">
    <section class="profile-shell">
        <div class="profile-hero">
            <h1 class="h3 fw-black mb-1"><i class="fas fa-id-card me-2"></i>Hồ sơ cá nhân</h1>
            <p class="mb-0 text-white-50">Dữ liệu được tải từ <code>/api/profile</code>.</p>
        </div>

        <div class="p-3 p-md-4">
            <div id="profileAlert" class="alert d-none" role="alert"></div>
            <div id="profileLoading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
                <div class="mt-3 text-muted">Đang tải thông tin hồ sơ...</div>
            </div>

            <div id="profileContent" class="d-none">
                <div class="row g-4">
                    <div class="col-lg-4">
                        <div class="profile-card p-4 h-100">
                            <div class="text-center">
                                <img id="profileAvatar" class="profile-avatar mb-3 d-none" alt="Avatar">
                                <div id="profileAvatarFallback" class="profile-avatar d-inline-flex align-items-center justify-content-center text-white fw-bold mb-3" style="font-size: 2.2rem;">
                                    U
                                </div>
                                <h3 class="h4 fw-bold mb-1" id="profileName">-</h3>
                                <div class="text-muted mb-3" id="profileEmail">-</div>
                                <div class="d-flex flex-wrap justify-content-center gap-2">
                                    <span class="badge text-bg-primary" id="profileRoleBadge">customer</span>
                                    <span class="badge text-bg-warning" id="profileVerifiedBadge">unverified</span>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="list-group list-group-flush">
                                <a href="/profile" class="list-group-item list-group-item-action active"><i class="fas fa-user me-2"></i>Hồ sơ</a>
                                <a href="/profile/edit" class="list-group-item list-group-item-action"><i class="fas fa-pen me-2"></i>Chỉnh sửa</a>
                                <a href="/profile/changePassword" class="list-group-item list-group-item-action"><i class="fas fa-lock me-2"></i>Đổi mật khẩu</a>
                                <a href="/profile/orders" class="list-group-item list-group-item-action"><i class="fas fa-receipt me-2"></i>Đơn hàng</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="profile-card p-4 h-100">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                                <div>
                                    <h2 class="h5 fw-bold mb-1">Thông tin tài khoản</h2>
                                    <div class="text-muted">Thông tin lấy từ API và cập nhật đồng bộ ngay sau khi thay đổi.</div>
                                </div>
                                <a href="/profile/edit" class="btn btn-primary">
                                    <i class="fas fa-pen me-1"></i>Chỉnh sửa
                                </a>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 bg-light h-100">
                                        <div class="text-muted small">Họ và tên</div>
                                        <div class="fw-semibold" id="profileFullName">-</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 bg-light h-100">
                                        <div class="text-muted small">Số điện thoại</div>
                                        <div class="fw-semibold" id="profilePhone">-</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 bg-light h-100">
                                        <div class="text-muted small">Vai trò</div>
                                        <div class="fw-semibold" id="profileRoleText">-</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 bg-light h-100">
                                        <div class="text-muted small">Ngày tạo</div>
                                        <div class="fw-semibold" id="profileCreatedAt">-</div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="p-3 rounded-3 bg-light">
                                        <div class="text-muted small">Địa chỉ</div>
                                        <div class="fw-semibold" id="profileAddress">-</div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-2 mt-4">
                                <a href="/profile/changePassword" class="btn btn-outline-primary">
                                    <i class="fas fa-lock me-1"></i>Đổi mật khẩu
                                </a>
                                <a href="/profile/orders" class="btn btn-outline-secondary">
                                    <i class="fas fa-receipt me-1"></i>Xem đơn hàng
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script src="/assets/js/frontend/pages/profile-index.js" defer></script>

<?php include 'app/views/shares/footer.php'; ?>
