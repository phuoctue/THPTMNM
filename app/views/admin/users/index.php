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
            <h1 class="h3 fw-black mb-1"><i class="fas fa-users me-2"></i>Quản lý người dùng</h1>
            <p class="mb-0 text-white-50">Danh sách được tải từ <code>/api/users</code>.</p>
        </div>

        <div class="p-3 p-md-4">
            <div id="adminUsersAlert" class="alert d-none" role="alert"></div>
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                <div>
                    <h2 class="h5 fw-bold mb-1">Danh sách tài khoản</h2>
                    <div class="text-muted">Sửa, khóa/mở khóa và xóa người dùng trực tiếp qua API.</div>
                </div>
                <a href="/Home" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Về trang chủ</a>
            </div>

            <div id="adminUsersLoading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
                <div class="mt-3 text-muted">Đang tải người dùng...</div>
            </div>

            <div id="adminUsersContent" class="admin-card d-none overflow-hidden">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Người dùng</th>
                                <th>Liên hệ</th>
                                <th>Vai trò</th>
                                <th>Trạng thái</th>
                                <th>Xác thực</th>
                                <th class="text-end">Hành động</th>
                            </tr>
                        </thead>
                        <tbody id="adminUsersTableBody"></tbody>
                    </table>
                </div>
            </div>

            <div id="adminUsersEmpty" class="alert alert-info d-none mb-0">
                Chưa có người dùng nào.
            </div>
        </div>
    </section>
</main>

<script src="/assets/js/frontend/pages/admin-users.js" defer></script>

<?php include 'app/views/shares/footer.php'; ?>
