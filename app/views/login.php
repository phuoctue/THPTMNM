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
    <title>Đăng nhập - My Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            min-height: 100vh;
            background: radial-gradient(circle at top, #1e3a8a 0%, #0f172a 52%, #020617 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .auth-shell {
            width: min(100%, 1080px);
            display: grid;
            grid-template-columns: 1.15fr .85fr;
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 30px 80px rgba(2, 6, 23, .45);
            background: #fff;
        }
        .auth-aside {
            color: #fff;
            padding: 48px;
            background: linear-gradient(160deg, #2563eb 0%, #1d4ed8 48%, #111827 100%);
        }
        .auth-aside h1 {
            font-weight: 800;
            font-size: clamp(2rem, 4vw, 3.2rem);
            margin-bottom: 18px;
        }
        .auth-aside p {
            color: rgba(255,255,255,.85);
        }
        .auth-card {
            padding: 48px;
        }
        .form-control, .form-check-input {
            border-radius: 14px;
        }
        .form-control {
            padding: 12px 16px;
            border: 1px solid #dbe3f0;
        }
        .btn-auth {
            border: 0;
            border-radius: 14px;
            padding: 12px 18px;
            font-weight: 800;
            background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%);
            color: #fff;
        }
        .btn-auth:hover {
            color: #fff;
            filter: brightness(1.05);
        }
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
            <h1>Đăng nhập để tiếp tục mua sắm</h1>
            <p class="mb-0">Quản lý tài khoản, theo dõi đơn hàng và bảo mật bằng xác thực email, remember me và phân quyền.</p>
        </div>
        <div class="mt-5 small text-white-50">
            <i class="fas fa-shield-alt me-1"></i> Bảo mật bằng password_hash, session an toàn và cookie HttpOnly.
        </div>
    </div>

    <div class="auth-card">
        <h2 class="fw-bold mb-2">Đăng nhập</h2>
        <p class="text-muted mb-4">Chào mừng bạn quay lại.</p>

        <?php require 'app/views/shares/flash.php'; ?>

        <form method="POST" action="/auth/login">
            <div class="mb-3">
                <label class="form-label fw-bold">Email</label>
                <input type="email" name="email" class="form-control" placeholder="example@gmail.com" value="<?php echo htmlspecialchars($old_data['email'] ?? ''); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Mật khẩu</label>
                <div class="input-group">
                    <input type="password" name="password" id="login_password" class="form-control" placeholder="Nhập mật khẩu" required>
                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('login_password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember_me" id="remember_me">
                    <label class="form-check-label" for="remember_me">Ghi nhớ đăng nhập</label>
                </div>
                <a href="/auth/forgotPassword" class="muted-link">Quên mật khẩu?</a>
            </div>

            <button class="btn btn-auth w-100" type="submit">Đăng nhập</button>
        </form>

        <div class="text-center mt-4">
            <span class="text-muted">Chưa có tài khoản?</span>
            <a href="/auth/register" class="muted-link">Đăng ký ngay</a>
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
