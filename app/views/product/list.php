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
    .admin-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: end;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1rem;
    }
    .admin-toolbar .form-control,
    .admin-toolbar .form-select {
        min-height: 46px;
        border-radius: 12px;
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
    .admin-table td, .admin-table th {
        vertical-align: middle;
    }
    .admin-thumb {
        width: 58px;
        height: 58px;
        border-radius: 14px;
        object-fit: cover;
        background: #eef2ff;
        flex-shrink: 0;
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
        <div class="admin-toolbar">
            <div>
                <h1 class="h3 fw-black mb-1"><i class="fas fa-box-open text-primary me-2"></i>Quản lý sản phẩm</h1>
                <p class="text-muted mb-0">Danh sách được load từ `/api/products` và thao tác bằng fetch.</p>
            </div>
            <a href="/Product/add" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Thêm sản phẩm
            </a>
        </div>

        <form id="adminProductFilterForm" class="row g-2 align-items-center mb-3">
            <div class="col-lg-5">
                <input type="search" name="search" class="form-control" placeholder="Tìm tên hoặc mô tả...">
            </div>
            <div class="col-lg-3">
                <select id="adminProductCategoryFilter" name="category_id" class="form-select">
                    <option value="">Tất cả danh mục</option>
                </select>
            </div>
            <div class="col-lg-2">
                <select name="sort_by" class="form-select">
                    <option value="created_at">Mới nhất</option>
                    <option value="name">Tên</option>
                    <option value="price">Giá</option>
                </select>
            </div>
            <div class="col-lg-2">
                <select name="sort_dir" class="form-select">
                    <option value="desc">Giảm dần</option>
                    <option value="asc">Tăng dần</option>
                </select>
            </div>
        </form>

        <div id="adminProductAlert" class="alert d-none" role="alert"></div>
        <div id="adminProductLoading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
            <div class="mt-3 text-muted">Đang tải sản phẩm...</div>
        </div>

        <div class="table-responsive d-none" id="adminProductTableWrap">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th style="width:90px;">Ảnh</th>
                        <th>Sản phẩm</th>
                        <th>Danh mục</th>
                        <th>Giá</th>
                        <th style="width:170px;">Hành động</th>
                    </tr>
                </thead>
                <tbody id="adminProductTableBody"></tbody>
            </table>
        </div>

        <div id="adminProductEmpty" class="admin-empty d-none">
            <i class="fas fa-box-open d-block"></i>
            <h2 class="h5 fw-bold mb-1">Chưa có sản phẩm nào</h2>
            <p class="mb-0">Bấm "Thêm sản phẩm" để tạo dữ liệu đầu tiên.</p>
        </div>

        <nav id="adminProductPagination" class="pagination-wrap"></nav>
    </section>
</main>

<script src="/assets/js/frontend/pages/admin/products.js" defer></script>

<?php include 'app/views/shares/footer.php'; ?>
