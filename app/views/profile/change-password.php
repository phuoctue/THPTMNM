<?php
// Bảo vệ trang này - chỉ người đã đăng nhập mới vào được
require_once 'app/libs/AuthHelper.php';
require_once 'app/libs/ViewHelper.php';
AuthHelper::requireLogin();

$user = AuthHelper::getCurrentUser();
$flash = ViewHelper::consumeFlash();
$errors = $errors ?? $flash['errors'];
$success = $success ?? $flash['success'];
?>

<?php require_once 'app/views/shares/header.php'; ?>

<div style="margin-top: 20px;">
    <div class="row">
        <!-- SIDEBAR PROFILE MENU -->
        <div class="col-md-3">
            <div class="card shadow-sm" style="border-radius: 10px; border: none;">
                <div class="card-body">
                    <div style="text-align: center; margin-bottom: 20px;">
                        <div style="
                            width: 80px;
                            height: 80px;
                            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                            border-radius: 50%;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            color: white;
                            font-size: 32px;
                            font-weight: 800;
                            margin: 0 auto 15px;
                        ">
                            <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                        </div>
                        <h5 style="margin: 0; font-weight: 700;">
                            <?php echo htmlspecialchars($user['name']); ?>
                        </h5>
                    </div>

                    <hr style="margin: 15px 0;">

                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <a href="/profile" class="btn btn-sm" style="
                            background: #f5f5f5;
                            color: #333;
                            border-radius: 8px;
                            text-decoration: none;
                            padding: 10px;
                            text-align: center;
                            font-weight: 600;
                            border: 1px solid #ddd;
                        ">
                            <i class="fas fa-user"></i> Hồ sơ
                        </a>
                        <a href="/profile/edit" class="btn btn-sm" style="
                            background: #f5f5f5;
                            color: #333;
                            border-radius: 8px;
                            text-decoration: none;
                            padding: 10px;
                            text-align: center;
                            font-weight: 600;
                            border: 1px solid #ddd;
                        ">
                            <i class="fas fa-edit"></i> Chỉnh sửa
                        </a>
                        <a href="/profile/changePassword" class="btn btn-sm" style="
                            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
                            color: white;
                            border-radius: 8px;
                            text-decoration: none;
                            padding: 10px;
                            text-align: center;
                            font-weight: 600;
                        ">
                            <i class="fas fa-lock"></i> Đổi mật khẩu
                        </a>
                        <a href="/profile/orders" class="btn btn-sm" style="
                            background: #f5f5f5;
                            color: #333;
                            border-radius: 8px;
                            text-decoration: none;
                            padding: 10px;
                            text-align: center;
                            font-weight: 600;
                            border: 1px solid #ddd;
                        ">
                            <i class="fas fa-receipt"></i> Đơn hàng
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- CHANGE PASSWORD FORM -->
        <div class="col-md-9">
            <?php require 'app/views/shares/flash.php'; ?>

            <!-- CHANGE PASSWORD FORM -->
            <div class="card shadow-sm" style="border-radius: 10px; border: none;">
                <div class="card-header" style="
                    background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    border-radius: 10px 10px 0 0;
                    padding: 20px;
                    border: none;
                ">
                    <h5 style="margin: 0; font-weight: 700;">
                        <i class="fas fa-lock"></i> Đổi mật khẩu
                    </h5>
                    <p style="margin: 8px 0 0 0; font-size: 0.9rem; opacity: 0.9;">
                        Cập nhật mật khẩu của bạn để bảo vệ tài khoản
                    </p>
                </div>

                <div class="card-body" style="padding: 30px;">
                    <form method="POST" action="/profile/changePassword">
                        <!-- MẬT KHẨU CŨ -->
                        <div class="form-group">
                            <label style="font-weight: 600; color: #333;">
                                🔐 Mật khẩu cũ <span style="color: red;">*</span>
                            </label>
                            <div style="position: relative;">
                                <input 
                                    type="password" 
                                    class="form-control <?php echo in_array('Mật khẩu cũ không chính xác hoặc có lỗi hệ thống', $errors) ? 'is-invalid' : ''; ?>" 
                                    id="old_password"
                                    name="old_password" 
                                    placeholder="Nhập mật khẩu cũ"
                                    required
                                    style="border-radius: 8px; padding: 10px 15px; padding-right: 40px;"
                                >
                                <button type="button" style="
                                    position: absolute;
                                    right: 15px;
                                    top: 50%;
                                    transform: translateY(-50%);
                                    background: none;
                                    border: none;
                                    color: #999;
                                    cursor: pointer;
                                    padding: 0;
                                " onclick="togglePassword('old_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- MẬT KHẨU MỚI -->
                        <div class="form-group">
                            <label style="font-weight: 600; color: #333;">
                                🔐 Mật khẩu mới <span style="color: red;">*</span>
                            </label>
                            <div style="position: relative;">
                                <input 
                                    type="password" 
                                    class="form-control" 
                                    id="new_password"
                                    name="new_password" 
                                    placeholder="Tối thiểu 6 ký tự"
                                    required
                                    style="border-radius: 8px; padding: 10px 15px; padding-right: 40px;"
                                >
                                <button type="button" style="
                                    position: absolute;
                                    right: 15px;
                                    top: 50%;
                                    transform: translateY(-50%);
                                    background: none;
                                    border: none;
                                    color: #999;
                                    cursor: pointer;
                                    padding: 0;
                                " onclick="togglePassword('new_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small style="color: #999; display: block; margin-top: 5px;">
                                ℹ️ Mật khẩu phải chứa ít nhất 6 ký tự
                            </small>
                        </div>

                        <!-- XÁC NHẬN MẬT KHẨU MỚI -->
                        <div class="form-group">
                            <label style="font-weight: 600; color: #333;">
                                🔐 Xác nhận mật khẩu mới <span style="color: red;">*</span>
                            </label>
                            <div style="position: relative;">
                                <input 
                                    type="password" 
                                    class="form-control" 
                                    id="confirm_password"
                                    name="confirm_password" 
                                    placeholder="Nhập lại mật khẩu mới"
                                    required
                                    style="border-radius: 8px; padding: 10px 15px; padding-right: 40px;"
                                >
                                <button type="button" style="
                                    position: absolute;
                                    right: 15px;
                                    top: 50%;
                                    transform: translateY(-50%);
                                    background: none;
                                    border: none;
                                    color: #999;
                                    cursor: pointer;
                                    padding: 0;
                                " onclick="togglePassword('confirm_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- WARNING BOX -->
                        <div style="
                            background: #fff3cd;
                            border-left: 4px solid #ffc107;
                            padding: 15px;
                            border-radius: 5px;
                            margin: 20px 0;
                        ">
                            <strong style="color: #856404;">⚠️ Lưu ý quan trọng:</strong>
                            <p style="color: #856404; margin: 8px 0 0 0; font-size: 0.95rem;">
                                • Mật khẩu mới phải khác mật khẩu cũ<br>
                                • Sau khi đổi, bạn sẽ cần đăng nhập lại<br>
                                • Giữ bí mật mật khẩu của bạn
                            </p>
                        </div>

                        <!-- BUTTON -->
                        <div style="display: flex; gap: 10px; margin-top: 30px;">
                            <button type="submit" class="btn" style="
                                background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
                                color: white;
                                border: none;
                                border-radius: 8px;
                                padding: 10px 30px;
                                font-weight: 600;
                                cursor: pointer;
                            ">
                                <i class="fas fa-check"></i> Đổi mật khẩu
                            </button>
                            <a href="/profile" class="btn" style="
                                background: #f5f5f5;
                                color: #333;
                                border: 1px solid #ddd;
                                border-radius: 8px;
                                padding: 10px 30px;
                                font-weight: 600;
                                text-decoration: none;
                            ">
                                <i class="fas fa-times"></i> Hủy
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// HÀM HIỂN THỊ/ẨN MẬT KHẨU
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const type = field.type === 'password' ? 'text' : 'password';
    field.type = type;
}

// VALIDATION CLIENT-SIDE
document.querySelector('form').addEventListener('submit', function(e) {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const oldPassword = document.getElementById('old_password').value;

    // Kiểm tra mật khẩu mới ít nhất 6 ký tự
    if (newPassword.length < 6) {
        e.preventDefault();
        alert('Mật khẩu mới phải có ít nhất 6 ký tự!');
        return;
    }

    // Kiểm tra xác nhận trùng khớp
    if (newPassword !== confirmPassword) {
        e.preventDefault();
        alert('Mật khẩu xác nhận không trùng khớp!');
        return;
    }

    // Kiểm tra mật khẩu mới khác mật khẩu cũ
    if (newPassword === oldPassword) {
        e.preventDefault();
        alert('Mật khẩu mới phải khác mật khẩu cũ!');
        return;
    }
});
</script>

<?php require_once 'app/views/shares/footer.php'; ?>
