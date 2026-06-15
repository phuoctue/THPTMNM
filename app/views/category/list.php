<?php include 'app/views/shares/header.php'; ?>

<?php
if (!function_exists('category_value')) {
    function category_value($category, string $key, mixed $default = null): mixed
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
.cat-table-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: var(--card-shadow);
    overflow: hidden;
}

.cat-table-card table {
    margin-bottom: 0;
}

.cat-table-card thead th {
    background: var(--dark);
    color: #fff;
    font-weight: 700;
    border: none;
    padding: .9rem 1.2rem;
    font-size: .85rem;
    text-transform: uppercase;
    letter-spacing: .5px;
}

.cat-table-card tbody td {
    vertical-align: middle;
    padding: .85rem 1.2rem;
    border-color: #f1f1ff;
}

.cat-table-card tbody tr:hover td {
    background: #fafbff;
}

.badge-cat {
    background: #eef0ff;
    color: var(--primary);
    font-size: .75rem;
    font-weight: 700;
    border-radius: 20px;
    padding: .25rem .75rem;
}
</style>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="page-title mb-0" style="font-size:1.8rem;font-weight:800;color:var(--dark);">
        <i class="fas fa-tags" style="color:var(--primary)"></i> Danh mục
    </h1>
    <a href="/Category/add" class="btn btn-primary">
        <i class="fas fa-plus mr-1"></i> Thêm danh mục
    </a>
</div>

<?php if (empty($categories)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-tags fa-3x mb-3 d-block"></i>
        <p class="font-weight-700">Chưa có danh mục nào.</p>
        <a href="/Category/add" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Thêm ngay
        </a>
    </div>
<?php else: ?>
    <div class="cat-table-card">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th style="width:60px">ID</th>
                    <th>Tên danh mục</th>
                    <th>Mô tả</th>
                    <th style="width:160px">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                <tr>
                    <td><span class="badge-cat"><?php echo (int) category_value($cat, 'id', 0); ?></span></td>
                    <td class="font-weight-700"><?php echo htmlspecialchars((string) category_value($cat, 'name', '')); ?></td>
                    <td class="text-muted" style="font-size:.9rem">
                        <?php echo htmlspecialchars((string) category_value($cat, 'description', '—')); ?>
                    </td>
                    <td>
                        <a href="/Category/edit/<?php echo (int) category_value($cat, 'id', 0); ?>"
                           class="btn btn-warning btn-sm mr-1">
                            <i class="fas fa-edit"></i> Sửa
                        </a>
                        <a href="/Category/delete/<?php echo (int) category_value($cat, 'id', 0); ?>"
                           class="btn btn-danger btn-sm">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include 'app/views/shares/footer.php'; ?>
