<?php
require_once 'app/libs/AuthHelper.php';
require_once 'app/libs/ViewHelper.php';
AuthHelper::requireLogin();

$user = AuthHelper::getCurrentUser();
$flash = ViewHelper::consumeFlash();
$errors = $errors ?? $flash['errors'];
$success = $success ?? $flash['success'];
$userDetails = $userDetails ?? [];
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
                <p class="text-muted mb-3"><?php echo htmlspecialchars($user['email']); ?></p>
                <div class="d-flex flex-wrap justify-content-center gap-2">
                    <span class="badge text-bg-<?php echo $user['role'] === 'admin' ? 'dark' : 'primary'; ?>">
                        <?php echo $user['role'] === 'admin' ? 'Admin' : 'Customer'; ?>
                    </span>
                    <span class="badge text-bg-<?php echo AuthHelper::isEmailVerified() ? 'success' : 'warning'; ?>">
                        <?php echo AuthHelper::isEmailVerified() ? 'Đã xác thực email' : 'Chưa xác thực email'; ?>
                    </span>
                    <span class="badge text-bg-<?php echo ($user['status'] ?? 'active') === 'locked' ? 'danger' : 'success'; ?>">
                        <?php echo htmlspecialchars($user['status'] ?? 'active'); ?>
                    </span>
                </div>

                <div class="list-group list-group-flush mt-4 text-start">
                    <a href="/profile" class="list-group-item list-group-item-action active"><i class="fas fa-user me-2"></i>Hồ sơ</a>
                    <a href="/profile/edit" class="list-group-item list-group-item-action"><i class="fas fa-pen me-2"></i>Chỉnh sửa</a>
                    <a href="/profile/changePassword" class="list-group-item list-group-item-action"><i class="fas fa-lock me-2"></i>Đổi mật khẩu</a>
                    <a href="/profile/orders" class="list-group-item list-group-item-action"><i class="fas fa-receipt me-2"></i>Đơn hàng</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <?php require 'app/views/shares/flash.php'; ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-bold"><i class="fas fa-id-card me-2 text-primary"></i>Thông tin cá nhân</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label text-muted">Họ và tên</label>
                        <div class="fw-semibold"><?php echo htmlspecialchars($userDetails['full_name'] ?? $user['name']); ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Email</label>
                        <div class="fw-semibold"><?php echo htmlspecialchars($user['email']); ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Số điện thoại</label>
                        <div class="fw-semibold"><?php echo !empty($userDetails['phone']) ? htmlspecialchars($userDetails['phone']) : '<span class="text-muted">Chưa cập nhật</span>'; ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Địa chỉ</label>
                        <div class="fw-semibold"><?php echo !empty($userDetails['address']) ? htmlspecialchars($userDetails['address']) : '<span class="text-muted">Chưa cập nhật</span>'; ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Vai trò</label>
                        <div class="fw-semibold"><?php echo htmlspecialchars($user['role']); ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Ngày tạo</label>
                        <div class="fw-semibold"><?php echo !empty($userDetails['created_at']) ? date('d/m/Y H:i', strtotime($userDetails['created_at'])) : '-'; ?></div>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex flex-wrap gap-2">
                    <a href="/profile/edit" class="btn btn-primary"><i class="fas fa-pen me-1"></i>Chỉnh sửa hồ sơ</a>
                    <a href="/profile/changePassword" class="btn btn-outline-primary"><i class="fas fa-lock me-1"></i>Đổi mật khẩu</a>
                    <?php if (!AuthHelper::isEmailVerified()): ?>
                        <a href="/auth/emailVerification" class="btn btn-warning"><i class="fas fa-envelope me-1"></i>Xác thực email</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'app/views/shares/footer.php'; ?>
