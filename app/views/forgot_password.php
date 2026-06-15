<?php
require_once 'app/libs/ViewHelper.php';
$flash = ViewHelper::consumeFlash();
$errors = $errors ?? $flash['errors'];
$success = $success ?? $flash['success'];
$old_data = $old_data ?? $flash['old_data'];
$debugResetLink = $_SESSION['debug_reset_link'] ?? null;
unset($_SESSION['debug_reset_link']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu - My Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #020617 0%, #1d4ed8 50%, #0f172a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .card-wrap {
            width: min(100%, 760px);
            display: grid;
            grid-template-columns: .9fr 1.1fr;
            overflow: hidden;
            border-radius: 28px;
            box-shadow: 0 30px 80px rgba(2,6,23,.45);
            background: #fff;
        }
        .side {
            color: #fff;
            padding: 44px;
            background: linear-gradient(160deg, #2563eb 0%, #7c3aed 100%);
        }
        .main {
            padding: 44px;
        }
        .form-control {
            padding: 12px 16px;
            border-radius: 14px;
        }
        .btn-auth {
            border: 0;
            border-radius: 14px;
            padding: 12px 18px;
            font-weight: 800;
            background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
            color: #fff;
        }
        .btn-auth:hover { color: #fff; filter: brightness(1.05); }
        @media (max-width: 992px) {
            .card-wrap { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="card-wrap">
    <div class="side d-flex flex-column justify-content-between">
        <div>
            <div class="badge text-bg-warning text-dark mb-3">My Store</div>
            <h1 class="fw-bold">Quên mật khẩu?</h1>
            <p class="mb-0">Nhập email của bạn, hệ thống sẽ gửi liên kết đặt lại mật khẩu có thời hạn 1 giờ.</p>
        </div>
        <div class="small text-white-50 mt-5">
            <i class="fas fa-shield-alt me-1"></i> Token được lưu dưới dạng hash trong cơ sở dữ liệu.
        </div>
    </div>
    <div class="main">
        <h2 class="fw-bold mb-2">Khôi phục tài khoản</h2>
        <p class="text-muted mb-4">Điền email đã đăng ký để nhận liên kết đặt lại mật khẩu.</p>
        <?php require 'app/views/shares/flash.php'; ?>

        <?php if (!empty($debugResetLink)): ?>
            <div class="alert alert-info">
                <div class="fw-bold mb-1">Link reset local</div>
                <div class="mb-2">Môi trường local đã hiển thị trực tiếp link đặt lại mật khẩu:</div>
                <div class="input-group">
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($debugResetLink); ?>" readonly>
                    <a href="<?php echo htmlspecialchars($debugResetLink); ?>" class="btn btn-outline-primary" target="_blank" rel="noopener">Mở link</a>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" action="/auth/forgotPassword">
            <div class="mb-3">
                <label class="form-label fw-bold">Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($old_data['email'] ?? ''); ?>" required>
            </div>
            <button type="submit" class="btn btn-auth w-100">Gửi liên kết đặt lại</button>
        </form>
        <div class="text-center mt-4">
            <a href="/auth/login" class="text-decoration-none fw-bold">Quay lại đăng nhập</a>
        </div>
    </div>
</div>
</body>
</html>
