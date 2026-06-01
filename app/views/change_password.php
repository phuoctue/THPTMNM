<?php
require_once 'app/libs/AuthHelper.php';
require_once 'app/libs/ViewHelper.php';
AuthHelper::requireLogin();

$user = AuthHelper::getCurrentUser();
$flash = ViewHelper::consumeFlash();
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
                    <?php if (!empty($user['avatar'])): ?>
                        <img src="/<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar" class="w-100 h-100 object-fit-cover">
                    <?php else: ?>
                        <span class="text-white fw-bold" style="font-size: 2.2rem;"><?php echo strtoupper(substr($user['name'], 0, 1)); ?></span>
                    <?php endif; ?>
                </div>
                <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($user['name']); ?></h4>
                <p class="text-muted mb-0"><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
            <div class="list-group list-group-flush">
                <a href="/profile" class="list-group-item list-group-item-action"><i class="fas fa-user me-2"></i>Hồ sơ</a>
                <a href="/profile/edit" class="list-group-item list-group-item-action"><i class="fas fa-pen me-2"></i>Chỉnh sửa</a>
                <a href="/profile/changePassword" class="list-group-item list-group-item-action active"><i class="fas fa-lock me-2"></i>Đổi mật khẩu</a>
                <a href="/profile/orders" class="list-group-item list-group-item-action"><i class="fas fa-receipt me-2"></i>Đơn hàng</a>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <?php require 'app/views/shares/flash.php'; ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-bold"><i class="fas fa-lock me-2 text-primary"></i>Đổi mật khẩu</h5>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="/profile/changePassword">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Mật khẩu cũ</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="old_password" name="old_password" required>
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('old_password')"><i class="fas fa-eye"></i></button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Mật khẩu mới</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('new_password')"><i class="fas fa-eye"></i></button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Xác nhận mật khẩu mới</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('confirm_password')"><i class="fas fa-eye"></i></button>
                        </div>
                    </div>
                    <div class="alert alert-warning">
                        Sau khi đổi mật khẩu, bạn sẽ được đăng xuất và cần đăng nhập lại.
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" type="submit"><i class="fas fa-check me-1"></i>Cập nhật mật khẩu</button>
                        <a href="/profile" class="btn btn-outline-secondary">Hủy</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(id) {
    var field = document.getElementById(id);
    field.type = field.type === 'password' ? 'text' : 'password';
}
</script>

<?php require_once 'app/views/shares/footer.php'; ?>
