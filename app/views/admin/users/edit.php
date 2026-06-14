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
    .avatar-preview {
        width: 110px;
        height: 110px;
        border-radius: 50%;
        object-fit: cover;
        background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
    }
</style>

<main class="container">
    <section class="admin-shell">
        <div class="admin-hero">
            <h1 class="h3 fw-black mb-1"><i class="fas fa-user-edit me-2"></i>Chỉnh sửa người dùng</h1>
            <p class="mb-0 text-white-50">Cập nhật qua <code>PUT /api/users/{id}</code>.</p>
        </div>

        <div class="p-3 p-md-4">
            <div id="adminUserEditAlert" class="alert d-none" role="alert"></div>
            <div id="adminUserEditLoading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
                <div class="mt-3 text-muted">Đang tải thông tin người dùng...</div>
            </div>

            <div id="adminUserEditContent" class="d-none">
                <div class="row g-4">
                    <div class="col-lg-4">
                        <div class="admin-card p-4 h-100 text-center">
                            <img id="adminUserAvatar" class="avatar-preview mb-3 d-none" alt="Avatar">
                            <div id="adminUserAvatarFallback" class="avatar-preview d-inline-flex align-items-center justify-content-center text-white fw-bold mb-3" style="font-size: 2.2rem;">U</div>
                            <h3 class="h5 fw-bold mb-1" id="adminUserName">-</h3>
                            <div class="text-muted mb-3" id="adminUserEmailText">-</div>

                            <div class="list-group list-group-flush text-start">
                                <a href="/admin/users" class="list-group-item list-group-item-action"><i class="fas fa-list me-2"></i>Danh sách</a>
                                <a href="#" class="list-group-item list-group-item-action active"><i class="fas fa-user-edit me-2"></i>Chỉnh sửa</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="admin-card p-4">
                            <h2 class="h5 fw-bold mb-3">Thông tin tài khoản</h2>
                            <form id="adminUserEditForm" novalidate>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Họ và tên</label>
                                        <input type="text" name="full_name" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Email</label>
                                        <input type="email" name="email" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Số điện thoại</label>
                                        <input type="text" name="phone" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Vai trò</label>
                                        <select name="role" class="form-select">
                                            <option value="customer">customer</option>
                                            <option value="admin">admin</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Trạng thái</label>
                                        <select name="status" class="form-select">
                                            <option value="active">active</option>
                                            <option value="locked">locked</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Địa chỉ</label>
                                        <textarea name="address" rows="4" class="form-control"></textarea>
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap gap-2 mt-4">
                                    <button type="submit" class="btn btn-primary" id="adminUserEditSubmitBtn">
                                        <span class="btn-label"><i class="fas fa-save me-1"></i>Lưu thay đổi</span>
                                        <span class="spinner-border spinner-border-sm d-none ms-2" aria-hidden="true"></span>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" id="adminUserDeleteBtn">
                                        <i class="fas fa-trash me-1"></i>Xóa người dùng
                                    </button>
                                    <a href="/admin/users" class="btn btn-outline-secondary">Quay lại</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script src="/assets/js/frontend/pages/admin-user-edit.js" defer></script>

<?php include 'app/views/shares/footer.php'; ?>
