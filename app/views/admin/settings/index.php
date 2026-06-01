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

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Cấu hình SMTP</h3>
        <p class="text-muted mb-0">Thiết lập email xác thực, quên mật khẩu và reset password ngay trong admin.</p>
    </div>
</div>

<?php require 'app/views/shares/flash.php'; ?>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="/admin/settings">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">APP_URL</label>
                            <input type="text" name="APP_URL" class="form-control" value="<?php echo htmlspecialchars($oldData['APP_URL'] ?? $settings['APP_URL'] ?? ''); ?>" placeholder="https://yourdomain.com">
                            <small class="text-muted">Dùng để tạo link xác thực trong email.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">MAIL_MAILER</label>
                            <select name="MAIL_MAILER" class="form-select">
                                <?php $mailer = $oldData['MAIL_MAILER'] ?? $settings['MAIL_MAILER'] ?? 'smtp'; ?>
                                <option value="smtp" <?php echo $mailer === 'smtp' ? 'selected' : ''; ?>>smtp</option>
                                <option value="sendmail" <?php echo $mailer === 'sendmail' ? 'selected' : ''; ?>>sendmail</option>
                                <option value="mail" <?php echo $mailer === 'mail' ? 'selected' : ''; ?>>mail</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">MAIL_ENCRYPTION</label>
                            <select name="MAIL_ENCRYPTION" class="form-select">
                                <?php $enc = $oldData['MAIL_ENCRYPTION'] ?? $settings['MAIL_ENCRYPTION'] ?? 'tls'; ?>
                                <option value="tls" <?php echo $enc === 'tls' ? 'selected' : ''; ?>>tls</option>
                                <option value="ssl" <?php echo $enc === 'ssl' ? 'selected' : ''; ?>>ssl</option>
                                <option value="" <?php echo $enc === '' ? 'selected' : ''; ?>>none</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">MAIL_HOST</label>
                            <input type="text" name="MAIL_HOST" class="form-control" value="<?php echo htmlspecialchars($oldData['MAIL_HOST'] ?? $settings['MAIL_HOST'] ?? ''); ?>" placeholder="smtp.gmail.com">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">MAIL_PORT</label>
                            <input type="number" name="MAIL_PORT" class="form-control" value="<?php echo htmlspecialchars($oldData['MAIL_PORT'] ?? $settings['MAIL_PORT'] ?? 587); ?>" placeholder="587">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">MAIL_USERNAME</label>
                            <input type="email" name="MAIL_USERNAME" class="form-control" value="<?php echo htmlspecialchars($oldData['MAIL_USERNAME'] ?? $settings['MAIL_USERNAME'] ?? ''); ?>" placeholder="your-email@gmail.com">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">MAIL_PASSWORD</label>
                            <input type="password" name="MAIL_PASSWORD" class="form-control" placeholder="Để trống để giữ nguyên mật khẩu hiện tại">
                            <small class="text-muted">Không hiển thị lại mật khẩu đang lưu. Nếu để trống, hệ thống sẽ giữ giá trị cũ.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">MAIL_FROM_ADDRESS</label>
                            <input type="email" name="MAIL_FROM_ADDRESS" class="form-control" value="<?php echo htmlspecialchars($oldData['MAIL_FROM_ADDRESS'] ?? $settings['MAIL_FROM_ADDRESS'] ?? ''); ?>" placeholder="no-reply@yourdomain.com">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">MAIL_FROM_NAME</label>
                            <input type="text" name="MAIL_FROM_NAME" class="form-control" value="<?php echo htmlspecialchars($oldData['MAIL_FROM_NAME'] ?? $settings['MAIL_FROM_NAME'] ?? 'My Store'); ?>" placeholder="My Store">
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Lưu SMTP</button>
                        <a href="/profile" class="btn btn-outline-secondary">Quay lại</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3">Ghi chú triển khai</h5>
                <ul class="text-muted mb-0">
                    <li>Local: có thể dùng `sendmail` của Laragon hoặc SMTP thật.</li>
                    <li>Production: nên dùng SMTP thật với App Password.</li>
                    <li>APP_URL phải là domain thật để link email đúng.</li>
                    <li>Đổi cấu hình xong, thử gửi lại email xác thực.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once 'app/views/shares/footer.php'; ?>
