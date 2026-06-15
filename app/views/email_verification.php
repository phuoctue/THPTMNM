<?php
require_once 'app/libs/AuthHelper.php';
require_once 'app/libs/ViewHelper.php';
AuthHelper::requireLogin();

$user = AuthHelper::getCurrentUser();
$flash = ViewHelper::consumeFlash();
$errors = $errors ?? $flash['errors'];
$success = $success ?? $flash['success'];
$debugVerificationLink = $_SESSION['debug_verification_link'] ?? null;
unset($_SESSION['debug_verification_link']);
?>
<?php require_once 'app/views/shares/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <?php require 'app/views/shares/flash.php'; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-bold"><i class="fas fa-envelope me-2 text-warning"></i>Xác thực email tài khoản</h5>
            </div>
            <div class="card-body p-4">
                <div class="alert alert-warning">
                    Tài khoản <strong><?php echo htmlspecialchars($user['email']); ?></strong> hiện đang <strong>chưa xác thực email</strong>.
                    Nếu bạn đang test local, hãy bấm nút bên dưới để gửi lại email và mở link xác thực trong cửa sổ mail tester của bạn.
                </div>

                <?php if (!empty($debugVerificationLink)): ?>
                    <div class="alert alert-info">
                        <div class="fw-bold mb-1">Link xác thực local</div>
                        <div class="mb-2">Do đang chạy môi trường local nên mình đã hiển thị trực tiếp link xác thực bên dưới:</div>
                        <div class="input-group">
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($debugVerificationLink); ?>" readonly>
                            <a class="btn btn-outline-primary" href="<?php echo htmlspecialchars($debugVerificationLink); ?>" target="_blank" rel="noopener">Mở link</a>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 h-100 bg-light">
                            <div class="fw-bold mb-1">Bước 1</div>
                            <div>Mở trang này và bấm gửi email xác thực.</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 h-100 bg-light">
                            <div class="fw-bold mb-1">Bước 2</div>
                            <div>Kiểm tra hộp thư, Spam, Mailpit/MailHog hoặc log mail local.</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 h-100 bg-light">
                            <div class="fw-bold mb-1">Bước 3</div>
                            <div>Bấm link xác thực trong email để mở khóa đầy đủ chức năng.</div>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a href="/auth/resendVerification" class="btn btn-warning">
                        <i class="fas fa-paper-plane me-1"></i> Gửi lại email xác thực
                    </a>
                    <a href="/profile" class="btn btn-outline-secondary">
                        Quay lại hồ sơ
                    </a>
                    <a href="/auth/logout" class="btn btn-outline-danger">
                        Đăng xuất
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'app/views/shares/footer.php'; ?>
