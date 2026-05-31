<?php
// Bảo vệ trang này - chỉ người đã đăng nhập mới vào được
require_once 'app/libs/AuthHelper.php';
require_once 'app/libs/ViewHelper.php';
AuthHelper::requireLogin();

$user = AuthHelper::getCurrentUser();
$userDetails = $userDetails ?? [];
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
                        <p style="color: #999; font-size: 0.9rem; margin: 5px 0 0 0;">
                            <?php echo $user['role'] === 'admin' ? '👑 Admin' : '👤 Khách hàng'; ?>
                        </p>
                    </div>

                    <hr style="margin: 15px 0;">

                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <a href="/profile" class="btn btn-sm" style="
                            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
                            color: white;
                            border-radius: 8px;
                            text-decoration: none;
                            padding: 10px;
                            text-align: center;
                            font-weight: 600;
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
                            background: #f5f5f5;
                            color: #333;
                            border-radius: 8px;
                            text-decoration: none;
                            padding: 10px;
                            text-align: center;
                            font-weight: 600;
                            border: 1px solid #ddd;
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

        <!-- MAIN PROFILE CONTENT -->
        <div class="col-md-9">
            <?php require 'app/views/shares/flash.php'; ?>

            <!-- PROFILE CARD -->
            <div class="card shadow-sm" style="border-radius: 10px; border: none;">
                <div class="card-header" style="
                    background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    border-radius: 10px 10px 0 0;
                    padding: 20px;
                    border: none;
                ">
                    <h5 style="margin: 0; font-weight: 700;">
                        <i class="fas fa-id-card"></i> Thông tin cá nhân
                    </h5>
                </div>

                <div class="card-body" style="padding: 30px;">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label style="font-weight: 600; color: #666; font-size: 0.9rem;">
                                📝 Họ và Tên
                            </label>
                            <p style="font-size: 1.1rem; color: #333; margin: 5px 0 0 0;">
                                <?php echo htmlspecialchars($userDetails['full_name'] ?? $user['name']); ?>
                            </p>
                        </div>

                        <div class="col-md-6 mb-4">
                            <label style="font-weight: 600; color: #666; font-size: 0.9rem;">
                                📧 Email
                            </label>
                            <p style="font-size: 1.1rem; color: #333; margin: 5px 0 0 0;">
                                <?php echo htmlspecialchars($user['email']); ?>
                            </p>
                        </div>

                        <div class="col-md-6 mb-4">
                            <label style="font-weight: 600; color: #666; font-size: 0.9rem;">
                                📞 Số điện thoại
                            </label>
                            <p style="font-size: 1.1rem; color: #333; margin: 5px 0 0 0;">
                                <?php echo $userDetails['phone'] ? htmlspecialchars($userDetails['phone']) : '<span style="color: #999;">Chưa cập nhật</span>'; ?>
                            </p>
                        </div>

                        <div class="col-md-6 mb-4">
                            <label style="font-weight: 600; color: #666; font-size: 0.9rem;">
                                🎯 Vai trò
                            </label>
                            <p style="font-size: 1.1rem; color: #333; margin: 5px 0 0 0;">
                                <?php 
                                if ($userDetails['role'] === 'admin') {
                                    echo '<span style="background: #ffe5e5; color: #c33; padding: 4px 10px; border-radius: 5px; font-weight: 600;">👑 Admin</span>';
                                } else {
                                    echo '<span style="background: #e5f0ff; color: #33c; padding: 4px 10px; border-radius: 5px; font-weight: 600;">👤 Khách hàng</span>';
                                }
                                ?>
                            </p>
                        </div>

                        <div class="col-12 mb-4">
                            <label style="font-weight: 600; color: #666; font-size: 0.9rem;">
                                📍 Địa chỉ
                            </label>
                            <p style="font-size: 1rem; color: #333; margin: 5px 0 0 0;">
                                <?php echo $userDetails['address'] ? htmlspecialchars($userDetails['address']) : '<span style="color: #999;">Chưa cập nhật</span>'; ?>
                            </p>
                        </div>

                        <div class="col-md-6">
                            <label style="font-weight: 600; color: #666; font-size: 0.9rem;">
                                📅 Ngày tạo tài khoản
                            </label>
                            <p style="font-size: 1rem; color: #333; margin: 5px 0 0 0;">
                                <?php echo date('d/m/Y H:i', strtotime($userDetails['created_at'])); ?>
                            </p>
                        </div>
                    </div>

                    <hr style="margin: 30px 0;">

                    <div style="display: flex; gap: 10px;">
                        <a href="/profile/edit" class="btn" style="
                            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
                            color: white;
                            border: none;
                            border-radius: 8px;
                            padding: 10px 20px;
                            font-weight: 600;
                            text-decoration: none;
                        ">
                            <i class="fas fa-edit"></i> Chỉnh sửa thông tin
                        </a>
                        <a href="/profile/changePassword" class="btn" style="
                            background: #f5f5f5;
                            color: #333;
                            border: 1px solid #ddd;
                            border-radius: 8px;
                            padding: 10px 20px;
                            font-weight: 600;
                            text-decoration: none;
                        ">
                            <i class="fas fa-lock"></i> Đổi mật khẩu
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'app/views/shares/footer.php'; ?>
