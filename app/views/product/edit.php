<?php
$productId = isset($productId) ? (int) $productId : (int) ($_GET['id'] ?? 0);
?>
<?php include 'app/views/shares/header.php'; ?>

<style>
    .form-shell {
        max-width: 760px;
        margin: 0 auto;
        background: rgba(255,255,255,.78);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,.55);
        border-radius: 24px;
        box-shadow: var(--card-shadow);
        padding: 1.5rem;
    }
    .form-shell .form-control,
    .form-shell .form-select,
    .form-shell .btn {
        border-radius: 12px;
    }
    #productImagePreview {
        display: none;
        max-height: 220px;
        border-radius: 16px;
        object-fit: cover;
        border: 1px solid #e5e7eb;
    }
</style>

<main class="container">
    <section class="form-shell">
        <div class="mb-4">
            <h1 class="h3 fw-black mb-1"><i class="fas fa-edit text-warning me-2"></i>Sửa sản phẩm</h1>
            <p class="text-muted mb-0">Cập nhật qua `/api/products/{id}` với upload ảnh bằng `FormData`.</p>
        </div>

        <div id="productFormAlert" class="alert d-none" role="alert"></div>

        <form id="adminProductForm" data-product-id="<?php echo (int) $productId; ?>" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label fw-bold">Tên sản phẩm</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Mô tả</label>
                <textarea name="description" class="form-control" rows="4"></textarea>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Giá</label>
                    <input type="number" name="price" class="form-control" min="0" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Danh mục</label>
                    <select name="category_id" class="form-select" id="adminProductCategorySelect" required>
                        <option value="">Đang tải...</option>
                    </select>
                </div>
            </div>
            <div class="mt-3">
                <label class="form-label fw-bold">Ảnh sản phẩm</label>
                <input type="file" name="image" class="form-control" accept="image/*" id="adminProductImageInput">
                <div class="mt-2 small text-muted">Bỏ trống nếu không đổi ảnh.</div>
                <img id="productImagePreview" class="img-fluid mt-3" alt="Preview">
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-warning px-4" id="adminProductSubmitBtn">
                    <span class="btn-label">Cập nhật sản phẩm</span>
                    <span class="spinner-border spinner-border-sm d-none ms-2" aria-hidden="true"></span>
                </button>
                <a href="/Product" class="btn btn-outline-secondary px-4">Quay lại</a>
            </div>
        </form>
    </section>
</main>

<script src="/assets/js/frontend/pages/admin/product-form.js" defer></script>

<?php include 'app/views/shares/footer.php'; ?>
