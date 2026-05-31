<?php
require_once 'app/libs/ViewHelper.php';
$errors = isset($error) ? [$error] : [];
$success = '';
?>

<?php include 'app/views/shares/header.php'; ?>

<style>
.form-card { background:#fff; border-radius:16px; box-shadow:var(--card-shadow); padding:2rem 2.5rem; max-width:560px; margin:0 auto; }
.form-card h1 { font-size:1.6rem; font-weight:800; color:var(--dark); margin-bottom:1.5rem; }
.form-card h1 i { color:var(--accent); margin-right:.4rem; }
.form-group label { font-weight:700; color:#374151; }
.form-control { border-radius:8px; border:1.5px solid #e5e7eb; font-weight:600; transition:border-color .2s,box-shadow .2s; }
.form-control:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(79,70,229,.12); }
.btn-back { background:#f3f4f6; color:#374151; border:none; }
.btn-back:hover { background:#e5e7eb; }
</style>

<div class="form-card">
    <h1><i class="fas fa-edit"></i> Sửa danh mục</h1>

    <?php require 'app/views/shares/flash.php'; ?>

    <form method="POST" action="/Category/update">

        <input type="hidden" name="id" value="<?php echo $category->id; ?>">

        <div class="form-group">
            <label><i class="fas fa-tag mr-1 text-primary"></i> Tên danh mục</label>
            <input type="text" name="name" class="form-control"
                   value="<?php echo htmlspecialchars($category->name); ?>" required>
        </div>

        <div class="form-group">
            <label><i class="fas fa-align-left mr-1 text-primary"></i> Mô tả</label>
            <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($category->description ?? ''); ?></textarea>
        </div>

        <div class="d-flex mt-3" style="gap:.75rem">
            <button type="submit" class="btn btn-warning px-4">
                <i class="fas fa-save mr-1"></i> Cập nhật
            </button>
            <a href="/Category" class="btn btn-back px-4">
                <i class="fas fa-arrow-left mr-1"></i> Quay lại
            </a>
        </div>

    </form>
</div>

<?php include 'app/views/shares/footer.php'; ?>
