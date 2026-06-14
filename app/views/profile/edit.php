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
            <h1 class="h3 fw-black mb-1"><i class="fas fa-pen me-2"></i>Chỉnh sửa hồ sơ</h1>
            <p class="mb-0 text-white-50">Cập nhật qua <code>PUT /api/profile/update</code>.</p>
        </div>

        <div class="p-3 p-md-4">
            <div id="profileEditAlert" class="alert d-none" role="alert"></div>
            <div id="profileEditLoading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
                <div class="mt-3 text-muted">Đang tải hồ sơ...</div>
            </div>

            <div id="profileEditContent" class="d-none">
                <div class="row g-4">
                    <div class="col-lg-4">
                        <div class="profile-card p-4 h-100 text-center">
                            <img id="profileEditAvatarPreview" class="profile-avatar mb-3 d-none" alt="Avatar preview">
                            <div id="profileEditAvatarFallback" class="profile-avatar d-inline-flex align-items-center justify-content-center text-white fw-bold mb-3" style="font-size: 2.2rem;">U</div>
                            <h3 class="h5 fw-bold mb-1" id="profileEditName">-</h3>
                            <div class="text-muted mb-3" id="profileEditEmail">-</div>

                            <div class="list-group list-group-flush text-start">
                                <a href="/profile" class="list-group-item list-group-item-action"><i class="fas fa-user me-2"></i>Hồ sơ</a>
                                <a href="/profile/edit" class="list-group-item list-group-item-action active"><i class="fas fa-pen me-2"></i>Chỉnh sửa</a>
                                <a href="/profile/changePassword" class="list-group-item list-group-item-action"><i class="fas fa-lock me-2"></i>Đổi mật khẩu</a>
                                <a href="/profile/orders" class="list-group-item list-group-item-action"><i class="fas fa-receipt me-2"></i>Đơn hàng</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="profile-card p-4">
                            <h2 class="h5 fw-bold mb-3">Thông tin chỉnh sửa</h2>
                            <form id="profileEditForm" enctype="multipart/form-data" novalidate>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Họ và tên</label>
                                        <input type="text" name="full_name" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Email</label>
                                        <input type="email" name="email" class="form-control" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Số điện thoại</label>
                                        <input type="text" name="phone" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Ảnh đại diện</label>
                                        <input type="file" name="avatar" class="form-control" accept="image/jpeg,image/png,image/webp">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Địa chỉ</label>
                                        <textarea name="address" rows="4" class="form-control"></textarea>
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap gap-2 mt-4">
                                    <button type="submit" class="btn btn-primary" id="profileEditSubmitBtn">
                                        <span class="btn-label"><i class="fas fa-save me-1"></i>Lưu thay đổi</span>
                                        <span class="spinner-border spinner-border-sm d-none ms-2" aria-hidden="true"></span>
                                    </button>
                                    <a href="/profile" class="btn btn-outline-secondary">Hủy</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script src="/assets/js/frontend/pages/profile-edit.js" defer></script>

<?php include 'app/views/shares/footer.php'; ?>
