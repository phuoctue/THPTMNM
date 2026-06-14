<?php include 'app/views/shares/header.php'; ?>

<style>
    .form-shell {
        max-width: 680px;
        margin: 0 auto;
        background: rgba(255,255,255,.78);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,.55);
        border-radius: 24px;
        box-shadow: var(--card-shadow);
        padding: 1.5rem;
    }
    .form-shell .form-control,
    .form-shell .btn {
        border-radius: 12px;
    }
</style>

<main class="container">
    <section class="form-shell">
        <div class="mb-4">
            <h1 class="h3 fw-black mb-1"><i class="fas fa-plus-circle text-primary me-2"></i>Thêm danh mục</h1>
            <p class="text-muted mb-0">Tạo mới qua `/api/categories`.</p>
        </div>

        <div id="categoryFormAlert" class="alert d-none" role="alert"></div>

        <form id="adminCategoryForm" data-category-id="0">
            <div class="mb-3">
                <label class="form-label fw-bold">Tên danh mục</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Mô tả</label>
                <textarea name="description" class="form-control" rows="4"></textarea>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4" id="adminCategorySubmitBtn">
                    <span class="btn-label">Lưu danh mục</span>
                    <span class="spinner-border spinner-border-sm d-none ms-2" aria-hidden="true"></span>
                </button>
                <a href="/Category" class="btn btn-outline-secondary px-4">Quay lại</a>
            </div>
        </form>
    </section>
</main>

<script src="/assets/js/frontend/pages/admin/category-form.js" defer></script>

<?php include 'app/views/shares/footer.php'; ?>
