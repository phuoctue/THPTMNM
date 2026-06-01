<?php
require_once 'app/libs/AuthHelper.php';
require_once 'app/libs/ViewHelper.php';
\AuthHelper::requireAdmin();

$flash = ViewHelper::consumeFlash();
$errors = $errors ?? $flash['errors'];
$success = $success ?? $flash['success'];
?>
<?php require_once 'app/views/shares/header.php'; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Quản lý người dùng</h3>
        <p class="text-muted mb-0">Xem, chỉnh sửa, khóa/mở khóa và xóa tài khoản.</p>
    </div>
    <a href="/Home" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Về trang chủ</a>
</div>

<?php require 'app/views/shares/flash.php'; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
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
                <tbody>
                    <?php foreach (($users ?? []) as $item): ?>
                        <tr class="<?php echo !empty($item['deleted_at']) ? 'table-secondary' : ''; ?>">
                            <td>#<?php echo (int) $item['id']; ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle overflow-hidden d-inline-flex align-items-center justify-content-center text-white fw-bold"
                                         style="width: 40px; height: 40px; background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);">
                                        <?php if (!empty($item['avatar'])): ?>
                                            <img src="/<?php echo htmlspecialchars($item['avatar']); ?>" class="w-100 h-100 object-fit-cover" alt="Avatar">
                                        <?php else: ?>
                                            <?php echo strtoupper(substr($item['full_name'], 0, 1)); ?>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold"><?php echo htmlspecialchars($item['full_name']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($item['email']); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div><?php echo !empty($item['phone']) ? htmlspecialchars($item['phone']) : '-'; ?></div>
                                <small class="text-muted"><?php echo !empty($item['address']) ? htmlspecialchars($item['address']) : ''; ?></small>
                            </td>
                            <td>
                                <span class="badge text-bg-<?php echo $item['role'] === 'admin' ? 'dark' : 'primary'; ?>">
                                    <?php echo htmlspecialchars($item['role']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge text-bg-<?php echo ($item['status'] ?? 'active') === 'locked' ? 'danger' : 'success'; ?>">
                                    <?php echo htmlspecialchars($item['status'] ?? 'active'); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge text-bg-<?php echo !empty($item['email_verified_at']) ? 'success' : 'warning'; ?>">
                                    <?php echo !empty($item['email_verified_at']) ? 'Đã xác thực' : 'Chưa xác thực'; ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm flex-wrap" role="group">
                                    <a href="/admin/users/edit/<?php echo (int) $item['id']; ?>" class="btn btn-outline-primary">Sửa</a>
                                    <a href="/admin/users/toggleStatus/<?php echo (int) $item['id']; ?>" class="btn btn-outline-warning">Khóa/Mở</a>
                                    <a href="/admin/users/delete/<?php echo (int) $item['id']; ?>" class="btn btn-outline-danger" onclick="return confirm('Xóa vĩnh viễn người dùng này?');">Xóa</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'app/views/shares/footer.php'; ?>
