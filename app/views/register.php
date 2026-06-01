<?php
require_once 'app/libs/ViewHelper.php';
$flash = ViewHelper::consumeFlash();
$old_data = $old_data ?? $flash['old_data'];
$errors = $errors ?? $flash['errors'];
$success = $success ?? $flash['success'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - My Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            min-height: 100vh;
            background: radial-gradient(circle at top, #0f172a 0%, #111827 50%, #020617 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .auth-shell {
            width: min(100%, 1120px);
            display: grid;
            grid-template-columns: .9fr 1.1fr;
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 30px 80px rgba(2, 6, 23, .45);
            background: #fff;
        }
        .auth-aside {
            color: #fff;
            padding: 48px;
            background: linear-gradient(160deg, #7c3aed 0%, #2563eb 50%, #0f172a 100%);
        }
        .auth-aside h1 {
            font-weight: 800;
            font-size: clamp(2rem, 4vw, 3rem);
            margin-bottom: 18px;
        }
        .auth-card {
            padding: 48px;
        }
        .form-control {
            padding: 12px 16px;
            border-radius: 14px;
            border: 1px solid #dbe3f0;
        }
        .btn-auth {
            border: 0;
            border-radius: 14px;
            padding: 12px 18px;
            font-weight: 800;
            background: linear-gradient(135deg, #7c3aed 0%, #2563eb 100%);
            color: #fff;
        }
        .btn-auth:hover { color: #fff; filter: brightness(1.05); }
        .muted-link {
            color: #2563eb;
            text-decoration: none;
            font-weight: 700;
        }
        .muted-link:hover { text-decoration: underline; }
        @media (max-width: 992px) {
            .auth-shell { grid-template-columns: 1fr; }
            .auth-aside { padding: 32px; }
            .auth-card { padding: 32px; }
        }
    </style>
</head>
<body>
<div class="auth-shell">
    <div class="auth-aside d-flex flex-column justify-content-between">
        <div>
            <div class="badge text-bg-warning text-dark mb-3">My Store</div>
            <h1>Tạo tài khoản mới</h1>
            <p class="mb-0">Sau khi đăng ký, bạn sẽ nhận email xác thực và có thể quản lý hồ sơ, đổi mật khẩu, đặt lại mật khẩu an toàn.</p>
        </div>
        <div class="mt-5 small text-white-50">
            <i class="fas fa-envelope me-1"></i> Email verification được bật mặc định.
        </div>
    </div>

    <div class="auth-card">
        <h2 class="fw-bold mb-2">Đăng ký</h2>
        <p class="text-muted mb-4">Tạo tài khoản khách hàng mới.</p>

        <?php require 'app/views/shares/flash.php'; ?>

        <form method="POST" action="/auth/register">
            <div class="mb-3">
                <label class="form-label fw-bold">Họ và tên</label>
                <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($old_data['full_name'] ?? ''); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($old_data['email'] ?? ''); ?>" required>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Mật khẩu</label>
                    <div class="input-group">
                        <input type="password" name="password" id="register_password" class="form-control" required>
                        <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('register_password')"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Xác nhận mật khẩu</label>
                    <div class="input-group">
                        <input type="password" name="confirm_password" id="register_confirm_password" class="form-control" required>
                        <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('register_confirm_password')"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
            </div>

            <div class="mt-3 mb-3">
                <label class="form-label fw-bold">Số điện thoại</label>
                <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($old_data['phone'] ?? ''); ?>">
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Địa chỉ</label>
                <textarea name="address" rows="3" class="form-control"><?php echo htmlspecialchars($old_data['address'] ?? ''); ?></textarea>
            </div>

            <button class="btn btn-auth w-100" type="submit">Đăng ký</button>
        </form>

        <div class="text-center mt-4">
            <span class="text-muted">Đã có tài khoản?</span>
            <a href="/auth/login" class="muted-link">Đăng nhập ngay</a>
        </div>
    </div>
</div>

<script>
function togglePassword(id) {
    var field = document.getElementById(id);
    field.type = field.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>
