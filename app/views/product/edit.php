<?php include 'app/views/shares/header.php'; ?>

<?php
if (!function_exists('product_form_value')) {
    function product_form_value($product, string $key, mixed $default = null): mixed
    {
        if (is_array($product) && array_key_exists($key, $product)) {
            return $product[$key];
        }

        if (is_object($product) && isset($product->{$key})) {
            return $product->{$key};
        }

        return $default;
    }
}

if (!function_exists('category_form_value')) {
    function category_form_value($category, string $key, mixed $default = null): mixed
    {
        if (is_array($category) && array_key_exists($key, $category)) {
            return $category[$key];
        }

        if (is_object($category) && isset($category->{$key})) {
            return $category->{$key};
        }

        return $default;
    }
}
?>

<style>
.form-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: var(--card-shadow);
    padding: 2rem 2.5rem;
    max-width: 680px;
    margin: 0 auto;
}
.form-card h1 { font-size:1.6rem; font-weight:800; color:var(--dark); margin-bottom:1.5rem; }
.form-card h1 i { color:var(--accent); margin-right:.4rem; }
.form-group label { font-weight:700; color:#374151; margin-bottom:.35rem; }
.form-control { border-radius:8px; border:1.5px solid #e5e7eb; font-weight:600; transition:border-color .2s,box-shadow .2s; }
.form-control:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(79,70,229,.12); }
.img-current { border-radius:10px; border:2px solid #e5e7eb; max-height:160px; object-fit:cover; }
#imagePreviewBox { margin-top:.75rem; display:none; text-align:center; }
#imagePreviewBox img { max-height:160px; border-radius:10px; border:2px solid var(--primary); object-fit:cover; }
.btn-back { background:#f3f4f6; color:#374151; border:none; }
.btn-back:hover { background:#e5e7eb; color:#111; }
</style>

<div class="form-card">
    <h1><i class="fas fa-edit"></i> Sửa sản phẩm</h1>

    <form method="POST" action="/Product/update" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo (int) product_form_value($product, 'id', 0); ?>">
        <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars((string) product_form_value($product, 'image', '')); ?>">

        <div class="form-group">
            <label><i class="fas fa-tag mr-1 text-primary"></i> Tên sản phẩm</label>
            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars((string) product_form_value($product, 'name', '')); ?>" required>
        </div>

        <div class="form-group">
            <label><i class="fas fa-align-left mr-1 text-primary"></i> Mô tả</label>
            <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars((string) product_form_value($product, 'description', '')); ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label><i class="fas fa-dollar-sign mr-1 text-primary"></i> Giá (₫)</label>
                <input type="number" name="price" class="form-control" value="<?php echo htmlspecialchars((string) product_form_value($product, 'price', 0)); ?>" min="0" required>
            </div>

            <div class="form-group col-md-6">
                <label><i class="fas fa-layer-group mr-1 text-primary"></i> Danh mục</label>
                <select name="category_id" class="form-control">
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo (int) category_form_value($cat, 'id', 0); ?>" <?php if ((string) category_form_value($cat, 'id', 0) === (string) product_form_value($product, 'category_id', '')) echo 'selected'; ?>>
                            <?php echo htmlspecialchars((string) category_form_value($cat, 'name', '')); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label><i class="fas fa-image mr-1 text-primary"></i> Hình ảnh</label>
            <?php if (!empty((string) product_form_value($product, 'image', '')) && file_exists((string) product_form_value($product, 'image', ''))): ?>
                <div class="mb-2">
                    <p class="text-muted small mb-1">Hình hiện tại:</p>
                    <img src="/<?php echo htmlspecialchars((string) product_form_value($product, 'image', '')); ?>" class="img-current" alt="Current image">
                </div>
            <?php endif; ?>

            <input type="file" name="image" id="imageInput" class="form-control-file" accept="image/*">
            <small class="text-muted">Để trống nếu không muốn thay đổi hình.</small>

            <div id="imagePreviewBox">
                <p class="text-muted small mb-1">Hình mới:</p>
                <img id="imagePreview" src="#" alt="New preview">
            </div>
        </div>

        <div class="d-flex mt-3" style="gap:.75rem">
            <button type="submit" class="btn btn-warning px-4">
                <i class="fas fa-save mr-1"></i> Cập nhật
            </button>
            <a href="/Product" class="btn btn-back px-4">
                <i class="fas fa-arrow-left mr-1"></i> Quay lại
            </a>
        </div>
    </form>
</div>

<script>
document.getElementById('imageInput').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('imagePreview').src = e.target.result;
        document.getElementById('imagePreviewBox').style.display = 'block';
    };
    reader.readAsDataURL(file);
});
</script>

<?php include 'app/views/shares/footer.php'; ?>
