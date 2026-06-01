<?php
require_once 'app/libs/ViewHelper.php';
$flash = ViewHelper::consumeFlash();
$errors = $errors ?? $flash['errors'];
$success = $success ?? $flash['success'];
$old_data = $old_data ?? $flash['old_data'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu - My Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #020617 0%, #7c3aed 50%, #0f172a 100%);
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
            background: linear-gradient(160deg, #7c3aed 0%, #2563eb 100%);
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
            background: linear-gradient(135deg, #7c3aed 0%, #2563eb 100%);
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
            <h1 class="fw-bold">Đặt lại mật khẩu</h1>
            <p class="mb-0">Liên kết này chỉ có hiệu lực trong 1 giờ. Hãy tạo mật khẩu mới đủ mạnh để bảo vệ tài khoản.</p>
        </div>
        <div class="small text-white-50 mt-5">
            <i class="fas fa-lock me-1"></i> Mật khẩu mới sẽ ghi đè mật khẩu cũ ngay lập tức.
        </div>
    </div>
    <div class="main">
        <h2 class="fw-bold mb-2">Tạo mật khẩu mới</h2>
        <p class="text-muted mb-4">Nhập mật khẩu mới cho tài khoản của bạn.</p>
        <?php require 'app/views/shares/flash.php'; ?>
        <form method="POST" action="/auth/resetPassword/<?php echo htmlspecialchars($token ?? ''); ?>">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token ?? ''); ?>">
            <div class="mb-3">
                <label class="form-label fw-bold">Mật khẩu mới</label>
                <div class="input-group">
                    <input type="password" name="password" id="reset_password" class="form-control" required>
                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('reset_password')"><i class="fas fa-eye"></i></button>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label fw-bold">Xác nhận mật khẩu mới</label>
                <div class="input-group">
                    <input type="password" name="confirm_password" id="reset_confirm_password" class="form-control" required>
                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('reset_confirm_password')"><i class="fas fa-eye"></i></button>
                </div>
            </div>
            <button type="submit" class="btn btn-auth w-100">Đặt lại mật khẩu</button>
        </form>
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
