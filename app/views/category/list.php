<?php include 'app/views/shares/header.php'; ?>

<style>
    .admin-shell {
        background: rgba(255,255,255,.74);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,.55);
        border-radius: 24px;
        box-shadow: var(--card-shadow);
        padding: 1.25rem;
    }
    .admin-table {
        background: #fff;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 14px 32px rgba(15,23,42,.06);
    }
    .admin-table thead th {
        background: #0f172a;
        color: #fff;
        border-bottom: 0;
    }
    .admin-empty {
        padding: 4rem 1rem;
        text-align: center;
        color: #64748b;
    }
    .admin-empty i {
        font-size: 3.25rem;
        color: #94a3b8;
        margin-bottom: 1rem;
    }
</style>

<main class="container">
    <section class="admin-shell">
        <div class="d-flex justify-content-between align-items-end flex-wrap gap-2 mb-3">
            <div>
                <h1 class="h3 fw-black mb-1"><i class="fas fa-tags text-primary me-2"></i>Quản lý danh mục</h1>
                <p class="text-muted mb-0">Danh mục được load từ `/api/categories`.</p>
            </div>
            <a href="/Category/add" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Thêm danh mục
            </a>
        </div>

        <div id="adminCategoryAlert" class="alert d-none" role="alert"></div>
        <div id="adminCategoryLoading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
            <div class="mt-3 text-muted">Đang tải danh mục...</div>
        </div>

        <div class="table-responsive d-none" id="adminCategoryTableWrap">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th style="width:90px;">ID</th>
                        <th>Tên danh mục</th>
                        <th>Mô tả</th>
                        <th style="width:170px;">Hành động</th>
                    </tr>
                </thead>
                <tbody id="adminCategoryTableBody"></tbody>
            </table>
        </div>

        <div id="adminCategoryEmpty" class="admin-empty d-none">
            <i class="fas fa-tags d-block"></i>
            <h2 class="h5 fw-bold mb-1">Chưa có danh mục nào</h2>
            <p class="mb-0">Bấm "Thêm danh mục" để tạo dữ liệu đầu tiên.</p>
        </div>
    </section>
</main>

<script src="/assets/js/frontend/pages/admin/categories.js" defer></script>

<?php include 'app/views/shares/footer.php'; ?>
