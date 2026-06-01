<?php
require_once 'app/libs/AuthHelper.php';
require_once 'app/libs/ViewHelper.php';
AuthHelper::requireLogin();

$user = AuthHelper::getCurrentUser();
$userDetails = $userDetails ?? [];
$flash = ViewHelper::consumeFlash();
$oldData = $oldData ?? $flash['old_data'];
$errors = $errors ?? $flash['errors'];
$success = $success ?? $flash['success'];
?>
<?php require_once 'app/views/shares/header.php'; ?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center p-4">
                <div class="mx-auto mb-3 rounded-circle overflow-hidden d-inline-flex align-items-center justify-content-center"
                     style="width: 110px; height: 110px; background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);">
                    <?php if (!empty($userDetails['avatar'])): ?>
                        <img src="/<?php echo htmlspecialchars($userDetails['avatar']); ?>" alt="Avatar" class="w-100 h-100 object-fit-cover">
                    <?php else: ?>
                        <span class="text-white fw-bold" style="font-size: 2.2rem;"><?php echo strtoupper(substr($user['name'], 0, 1)); ?></span>
                    <?php endif; ?>
                </div>
                <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($userDetails['full_name'] ?? $user['name']); ?></h4>
                <p class="text-muted mb-0"><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
            <div class="list-group list-group-flush">
                <a href="/profile" class="list-group-item list-group-item-action"><i class="fas fa-user me-2"></i>Hồ sơ</a>
                <a href="/profile/edit" class="list-group-item list-group-item-action active"><i class="fas fa-pen me-2"></i>Chỉnh sửa</a>
                <a href="/profile/changePassword" class="list-group-item list-group-item-action"><i class="fas fa-lock me-2"></i>Đổi mật khẩu</a>
                <a href="/profile/orders" class="list-group-item list-group-item-action"><i class="fas fa-receipt me-2"></i>Đơn hàng</a>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <?php require 'app/views/shares/flash.php'; ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-bold"><i class="fas fa-user-edit me-2 text-primary"></i>Chỉnh sửa hồ sơ</h5>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="/profile/edit" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Họ và tên</label>
                            <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($oldData['full_name'] ?? $userDetails['full_name'] ?? $user['name']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                            <small class="text-muted">Email không thể thay đổi</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Số điện thoại</label>
                            <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($oldData['phone'] ?? $userDetails['phone'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Ảnh đại diện</label>
                            <input type="file" name="avatar" class="form-control" accept="image/jpeg,image/png,image/webp">
                            <small class="text-muted">Chỉ nhận JPG, PNG, WEBP và tối đa 2MB.</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Địa chỉ</label>
                            <textarea name="address" rows="4" class="form-control"><?php echo htmlspecialchars($oldData['address'] ?? $userDetails['address'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Lưu thay đổi</button>
                        <a href="/profile" class="btn btn-outline-secondary">Hủy</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'app/views/shares/footer.php'; ?>
