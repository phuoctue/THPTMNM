<?php
require_once 'app/libs/ViewHelper.php';
$flash = ViewHelper::consumeFlash();
$old_data = $flash['old_data'];
$errors = $flash['errors'];
$success = $flash['success'];

$fullNameErrors = ['Vui lòng nhập họ và tên', 'Họ và tên không được vượt quá 100 ký tự'];
$emailErrors = ['Vui lòng nhập email', 'Email không hợp lệ'];
$passwordErrors = ['Vui lòng nhập mật khẩu', 'Vui lòng xác nhận mật khẩu', 'Mật khẩu phải có ít nhất 6 ký tự', 'Mật khẩu không trùng khớp'];
$phoneErrors = ['Số điện thoại không hợp lệ'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký - My Store</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { 
            --primary: #4f46e5; 
            --primary-d: #3730a3; 
            --accent: #f59e0b; 
            --dark: #1e1b4b; 
            --light-bg: #f5f5ff; 
        }
        
        * { box-sizing: border-box; }
        
        body {
            margin: 0;
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .auth-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
        }

        .auth-header {
            background: linear-gradient(180deg, #5c53f0 0%, #4f46e5 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .auth-header h1 {
            font-size: 2rem;
            font-weight: 800;
            margin: 0 0 10px 0;
        }

        .auth-header p {
            font-size: 0.95rem;
            margin: 0;
            opacity: 0.9;
        }

        .auth-body {
            padding: 40px 30px;
        }

        .form-group label {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .form-control {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
            outline: none;
        }

        .form-control.is-invalid {
            border-color: #dc3545;
        }

        .form-control.is-invalid:focus {
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
        }

        .invalid-feedback {
            display: block;
            color: #dc3545;
            font-size: 0.85rem;
            margin-top: 5px;
            font-weight: 500;
        }

        .alert {
            border-radius: 8px;
            border: none;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-danger {
            background-color: #fee;
            color: #c33;
        }

        .alert-success {
            background-color: #efe;
            color: #3c3;
        }

        .btn-register {
            width: 100%;
            background: linear-gradient(180deg, #5c53f0 0%, #4f46e5 100%);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .btn-register:hover {
            background: linear-gradient(180deg, #4338ca 0%, #3730a3 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(79, 70, 229, 0.3);
        }

        .auth-footer {
            text-align: center;
            color: #6b7280;
            font-size: 0.95rem;
        }

        .auth-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 700;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        .password-field {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #999;
            background: none;
            border: none;
            padding: 0;
        }

        .toggle-password:hover {
            color: var(--primary);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        @media (max-width: 576px) {
            .auth-container {
                box-shadow: none;
            }

            .auth-header {
                padding: 30px 20px;
            }

            .auth-header h1 {
                font-size: 1.5rem;
            }

            .auth-body {
                padding: 30px 20px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="auth-container">
    <!-- HEADER -->
    <div class="auth-header">
        <h1><i class="fas fa-user-plus"></i> Đăng Ký</h1>
        <p>Tạo tài khoản mới để bắt đầu mua sắm</p>
    </div>

    <!-- BODY -->
    <div class="auth-body">
        <?php require 'app/views/shares/flash.php'; ?>

        <!-- FORM ĐĂNG KÝ -->
        <form method="POST" action="/auth/register" novalidate>
            
            <!-- HỌ VÀ TÊN -->
            <div class="form-group">
                <label for="full_name">
                    <i class="fas fa-user"></i> Họ và Tên
                </label>
                <input 
                    type="text" 
                    class="form-control <?php echo array_intersect($errors, $fullNameErrors) ? 'is-invalid' : ''; ?>" 
                    id="full_name" 
                    name="full_name" 
                    placeholder="Nhập họ và tên" 
                    value="<?php echo htmlspecialchars($old_data['full_name'] ?? ''); ?>"
                    required
                >
                <?php if (array_intersect($errors, $fullNameErrors)): ?>
                    <div class="invalid-feedback"><?php echo htmlspecialchars(array_values(array_intersect($errors, $fullNameErrors))[0]); ?></div>
                <?php endif; ?>
            </div>

            <!-- EMAIL -->
            <div class="form-group">
                <label for="email">
                    <i class="fas fa-envelope"></i> Email
                </label>
                <input 
                    type="email" 
                    class="form-control <?php echo array_intersect($errors, $emailErrors) ? 'is-invalid' : ''; ?>" 
                    id="email" 
                    name="email" 
                    placeholder="example@gmail.com" 
                    value="<?php echo htmlspecialchars($old_data['email'] ?? ''); ?>"
                    required
                >
                <?php if (array_intersect($errors, $emailErrors)): ?>
                    <div class="invalid-feedback"><?php echo htmlspecialchars(array_values(array_intersect($errors, $emailErrors))[0]); ?></div>
                <?php endif; ?>
            </div>

            <!-- MẬT KHẨU & XÁC NHẬN -->
            <div class="form-row">
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Mật khẩu
                    </label>
                    <div class="password-field">
                        <input 
                            type="password" 
                            class="form-control" 
                            id="password" 
                            name="password" 
                            placeholder="Tối thiểu 6 ký tự" 
                            required
                        >
                        <button type="button" class="toggle-password" onclick="togglePassword('password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">
                        <i class="fas fa-lock"></i> Xác nhận
                    </label>
                    <div class="password-field">
                        <input 
                            type="password" 
                            class="form-control" 
                            id="confirm_password" 
                            name="confirm_password" 
                            placeholder="Xác nhận mật khẩu" 
                            required
                        >
                        <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- SỐ ĐIỆN THOẠI -->
            <div class="form-group">
                <label for="phone">
                    <i class="fas fa-phone"></i> Số điện thoại
                </label>
                <input 
                    type="tel" 
                    class="form-control <?php echo array_intersect($errors, $phoneErrors) ? 'is-invalid' : ''; ?>" 
                    id="phone" 
                    name="phone" 
                    placeholder="0909123456" 
                    value="<?php echo htmlspecialchars($old_data['phone'] ?? ''); ?>"
                >
                <?php if (array_intersect($errors, $phoneErrors)): ?>
                    <div class="invalid-feedback"><?php echo htmlspecialchars(array_values(array_intersect($errors, $phoneErrors))[0]); ?></div>
                <?php endif; ?>
            </div>

            <!-- ĐỊA CHỈ -->
            <div class="form-group">
                <label for="address">
                    <i class="fas fa-map-marker-alt"></i> Địa chỉ
                </label>
                <textarea 
                    class="form-control" 
                    id="address" 
                    name="address" 
                    rows="3" 
                    placeholder="Nhập địa chỉ của bạn"
                ><?php echo htmlspecialchars($old_data['address'] ?? ''); ?></textarea>
            </div>

            <!-- NÚT ĐĂNG KÝ -->
            <button type="submit" class="btn-register">
                <i class="fas fa-user-plus"></i> Đăng Ký
            </button>
        </form>

        <!-- CHUYỂN SANG ĐĂNG NHẬP -->
        <div class="auth-footer" style="margin-top: 20px;">
            Đã có tài khoản? 
            <a href="/auth/login">
                <i class="fas fa-sign-in-alt"></i> Đăng nhập ngay
            </a>
        </div>
    </div>
</div>

<!-- JAVASCRIPT -->
<script>
    // HÀM CHUYỂN ĐỔI HIỂN THỊ/ẨN MẬT KHẨU
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const isPassword = field.type === 'password';
        field.type = isPassword ? 'text' : 'password';
    }

    // VALIDATION CLIENT-SIDE CƠ BẢN
    document.querySelector('form').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;

        // Kiểm tra mật khẩu ít nhất 6 ký tự
        if (password.length < 6) {
            e.preventDefault();
            alert('Mật khẩu phải có ít nhất 6 ký tự!');
            return;
        }

        // Kiểm tra mật khẩu trùng khớp
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Mật khẩu không trùng khớp!');
            return;
        }
    });
</script>

</body>
</html>
