<?php
// Bảo vệ trang này - chỉ người đã đăng nhập mới vào được
require_once 'app/libs/AuthHelper.php';
require_once 'app/libs/ViewHelper.php';
AuthHelper::requireLogin();

$user = AuthHelper::getCurrentUser();
$userDetails = $userDetails ?? [];
$flash = ViewHelper::consumeFlash();
$oldData = $oldData ?? $flash['old_data'];
$errors = $errors ?? $flash['errors'];
$success = $success ?? $flash['success'];

$fullNameError = 'Vui lòng nhập họ và tên';
$phoneError = 'Số điện thoại không hợp lệ';
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
                            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
                            color: white;
                            border-radius: 8px;
                            text-decoration: none;
                            padding: 10px;
                            text-align: center;
                            font-weight: 600;
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

        <!-- EDIT FORM -->
        <div class="col-md-9">
            <?php require 'app/views/shares/flash.php'; ?>

            <!-- EDIT PROFILE FORM -->
            <div class="card shadow-sm" style="border-radius: 10px; border: none;">
                <div class="card-header" style="
                    background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    border-radius: 10px 10px 0 0;
                    padding: 20px;
                    border: none;
                ">
                    <h5 style="margin: 0; font-weight: 700;">
                        <i class="fas fa-edit"></i> Chỉnh sửa thông tin cá nhân
                    </h5>
                </div>

                <div class="card-body" style="padding: 30px;">
                    <form method="POST" action="/profile/edit">
                        <!-- HỌ VÀ TÊN -->
                        <div class="form-group">
                            <label style="font-weight: 600; color: #333;">
                                👤 Họ và Tên <span style="color: red;">*</span>
                            </label>
                            <input 
                                type="text" 
                                class="form-control <?php echo in_array($fullNameError, $errors) || in_array('Họ và tên không được vượt quá 100 ký tự', $errors) ? 'is-invalid' : ''; ?>" 
                                name="full_name" 
                                value="<?php echo htmlspecialchars($oldData['full_name'] ?? $userDetails['full_name'] ?? ''); ?>"
                                placeholder="Nhập họ và tên"
                                required
                                style="border-radius: 8px; padding: 10px 15px;"
                            >
                            <?php if (in_array($fullNameError, $errors) || in_array('Họ và tên không được vượt quá 100 ký tự', $errors)): ?>
                                <div class="invalid-feedback">
                                    <?php echo htmlspecialchars(in_array('Họ và tên không được vượt quá 100 ký tự', $errors) ? 'Họ và tên không được vượt quá 100 ký tự' : $fullNameError); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- EMAIL (READ-ONLY) -->
                        <div class="form-group">
                            <label style="font-weight: 600; color: #333;">
                                📧 Email
                            </label>
                            <input 
                                type="email" 
                                class="form-control" 
                                value="<?php echo htmlspecialchars($user['email']); ?>"
                                readonly
                                style="border-radius: 8px; padding: 10px 15px; background: #f5f5f5;"
                            >
                            <small style="color: #999;">Email không thể thay đổi</small>
                        </div>

                        <!-- SỐ ĐIỆN THOẠI -->
                        <div class="form-group">
                            <label style="font-weight: 600; color: #333;">
                                📞 Số điện thoại
                            </label>
                            <input 
                                type="tel" 
                                class="form-control <?php echo in_array($phoneError, $errors) ? 'is-invalid' : ''; ?>" 
                                name="phone" 
                                value="<?php echo htmlspecialchars($oldData['phone'] ?? $userDetails['phone'] ?? ''); ?>"
                                placeholder="0909123456"
                                style="border-radius: 8px; padding: 10px 15px;"
                            >
                            <?php if (in_array($phoneError, $errors)): ?>
                                <div class="invalid-feedback"><?php echo htmlspecialchars($phoneError); ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- ĐỊA CHỈ -->
                        <div class="form-group">
                            <label style="font-weight: 600; color: #333;">
                                📍 Địa chỉ
                            </label>
                            <textarea 
                                class="form-control" 
                                name="address" 
                                rows="4"
                                placeholder="Nhập địa chỉ của bạn"
                                style="border-radius: 8px; padding: 10px 15px; font-family: inherit;"
                            ><?php echo htmlspecialchars($oldData['address'] ?? $userDetails['address'] ?? ''); ?></textarea>
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
                                <i class="fas fa-save"></i> Lưu thay đổi
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

<?php require_once 'app/views/shares/footer.php'; ?>
