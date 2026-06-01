<?php
require_once 'app/libs/AuthHelper.php';
require_once 'app/libs/ViewHelper.php';
\AuthHelper::requireAdmin();

$flash = ViewHelper::consumeFlash();
$errors = $errors ?? $flash['errors'];
$success = $success ?? $flash['success'];
$oldData = $oldData ?? $flash['old_data'];
?>
<?php require_once 'app/views/shares/header.php'; ?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center p-4">
                <div class="mx-auto mb-3 rounded-circle overflow-hidden d-inline-flex align-items-center justify-content-center"
                     style="width: 110px; height: 110px; background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);">
                    <?php if (!empty($user['avatar'])): ?>
                        <img src="/<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar" class="w-100 h-100 object-fit-cover">
                    <?php else: ?>
                        <span class="text-white fw-bold" style="font-size: 2.2rem;"><?php echo strtoupper(substr($user['full_name'], 0, 1)); ?></span>
                    <?php endif; ?>
                </div>
                <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($user['full_name']); ?></h4>
                <p class="text-muted mb-0"><?php echo htmlspecialchars($user['email']); ?></p>
                <div class="mt-3">
                    <span class="badge text-bg-<?php echo $user['role'] === 'admin' ? 'dark' : 'primary'; ?>"><?php echo htmlspecialchars($user['role']); ?></span>
                    <span class="badge text-bg-<?php echo ($user['status'] ?? 'active') === 'locked' ? 'danger' : 'success'; ?>"><?php echo htmlspecialchars($user['status'] ?? 'active'); ?></span>
                </div>
            </div>
            <div class="list-group list-group-flush">
                <a href="/admin/users" class="list-group-item list-group-item-action"><i class="fas fa-list me-2"></i>Danh sách</a>
                <a href="/admin/users/edit/<?php echo (int) $user['id']; ?>" class="list-group-item list-group-item-action active"><i class="fas fa-user-edit me-2"></i>Chỉnh sửa</a>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <?php require 'app/views/shares/flash.php'; ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-bold"><i class="fas fa-user-cog me-2 text-primary"></i>Chỉnh sửa người dùng #<?php echo (int) $user['id']; ?></h5>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="/admin/users/edit/<?php echo (int) $user['id']; ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Họ và tên</label>
                            <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($oldData['full_name'] ?? $user['full_name']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($oldData['email'] ?? $user['email']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Số điện thoại</label>
                            <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($oldData['phone'] ?? $user['phone']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Vai trò</label>
                            <select name="role" class="form-select">
                                <option value="customer" <?php echo ($oldData['role'] ?? $user['role']) === 'customer' ? 'selected' : ''; ?>>customer</option>
                                <option value="admin" <?php echo ($oldData['role'] ?? $user['role']) === 'admin' ? 'selected' : ''; ?>>admin</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Trạng thái</label>
                            <select name="status" class="form-select">
                                <option value="active" <?php echo ($oldData['status'] ?? $user['status']) === 'active' ? 'selected' : ''; ?>>active</option>
                                <option value="locked" <?php echo ($oldData['status'] ?? $user['status']) === 'locked' ? 'selected' : ''; ?>>locked</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Địa chỉ</label>
                            <textarea name="address" rows="3" class="form-control"><?php echo htmlspecialchars($oldData['address'] ?? $user['address']); ?></textarea>
                        </div>
                    </div>

                    <div class="alert alert-info mt-4 mb-0">
                        Nếu muốn khóa chính tài khoản hiện tại, hệ thống sẽ chặn thao tác này.
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Lưu thay đổi</button>
                        <a href="/admin/users" class="btn btn-outline-secondary">Quay lại</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'app/views/shares/footer.php'; ?>
